<?php
namespace Podlove\Modules\EpisodeNumbering\Settings;

use Podlove\Model;
use Podlove\Modules\EpisodeNumbering\Model\Season;

class Seasons {

	static $pagehook;
	
	public function __construct( $handle ) {
		
		Seasons::$pagehook = add_submenu_page(
			/* $parent_slug*/ $handle,
			/* $page_title */ 'Seasons',
			/* $menu_title */ 'Seasons',
			/* $capability */ 'administrator',
			/* $menu_slug  */ 'podlove_seasons_settings_handle',
			/* $function   */ array( $this, 'page' )
		);

		$pagehook = self::$pagehook;

		add_action( 'admin_init', array( $this, 'process_form' ) );
	}

	public static function get_action_link( $season, $title, $action = 'edit', $type = 'link' ) {
		return sprintf(
			'<a href="?page=%s&action=%s&season=%s"%s>' . $title . '</a>',
			$_REQUEST['page'],
			$action,
			$season->id,
			$type == 'button' ? ' class="button"' : ''
		);
	}

	public function scripts_and_styles() {

		if ( ! isset( $_REQUEST['page'] ) )
			return;

		if ( $_REQUEST['page'] != 'podlove_seasons_settings_handle' )
			return;

		\Podlove\require_code_mirror();
	}

	/**
	 * Process form: save/update a format
	 */
	private function save() {
		if ( ! isset( $_REQUEST['season'] ) )
			return;
			
		$season = \Podlove\Modules\EpisodeNumbering\Model\Season::find_by_id( $_REQUEST['season'] );
		$season->update_attributes( $_POST['podlove_season'] );
		
		$this->redirect( 'index', $season->id );
	}
	
	/**
	 * Process form: create a format
	 */
	private function create() {
		global $wpdb;
		
		$season = new \Podlove\Modules\EpisodeNumbering\Model\Season;
		$season->update_attributes( $_POST['podlove_season'] );

		$this->redirect( 'index' );
	}
	
	/**
	 * Process form: delete a format
	 */
	private function delete() {
		if ( ! isset( $_REQUEST['season'] ) )
			return;

		\Podlove\Modules\EpisodeNumbering\Model\Season::find_by_id( $_REQUEST['season'] )->delete();
		
		$this->redirect( 'index' );
	}
	
	/**
	 * Helper method: redirect to a certain page.
	 */
	private function redirect( $action, $season_id = NULL ) {
		$page   = 'admin.php?page=' . $_REQUEST['page'];
		$show   = ( $season_id ) ? '&season=' . $season_id : '';
		$action = '&action=' . $action;
		
		wp_redirect( admin_url( $page . $show . $action ) );
		exit;
	}

	public function process_form() {

		if ( ! isset( $_REQUEST['season'] ) )
			return;

		$action = ( isset( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : NULL;

		if ( $action === 'save' ) {
			$this->save();
		} elseif ( $action === 'create' ) {
			$this->create();
		} elseif ( $action === 'delete' ) {
			$this->delete();
		}
	}

	public function page() {

		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : NULL;

		if( isset( $_REQUEST['season'] ) )
			$season = \Podlove\Modules\EpisodeNumbering\Model\Season::find_by_id( $_REQUEST['season'] );

		if ( $action == 'confirm_delete' && isset( $_REQUEST['season'] ) ) {
			?>
			<div class="updated">
				<p>
					<strong>
						<?php echo sprintf( __( 'Are you sure you want to delete season %s?', 'podlove' ), $season->number ) ?>
					</strong>
				</p>
				<p>
					<?php echo self::get_action_link( \Podlove\Modules\EpisodeNumbering\Model\Season::find_by_id( (int) $_REQUEST['season'] ), __( 'Delete permanently', 'podlove' ), 'delete', 'button' ) ?>
				</p>
			</div>
			<?php
		}
		?>
		<div class="wrap">
			<?php screen_icon( 'podlove-podcast' ); ?>
			<h2><?php echo __( 'Seasons', 'podlove' ); ?> <a href="?page=<?php echo $_REQUEST['page']; ?>&amp;action=new" class="add-new-h2"><?php echo __( 'Add New', 'podlove' ); ?></a></h2>
			<?php
			$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : NULL;
			switch ( $action ) {
				case 'new':   $this->new_season();  break;
				case 'edit':  $this->edit_season(); break;
				case 'index': $this->view_season(); break;
				default:      $this->view_season(); break;
			}
			?>
		</div>	
		<?php
	}

	private function view_season() {

		echo __( 'DESCRIPTION' );

		$table = new \Podlove\Modules\EpisodeNumbering\Season_List_Table();
		$table->prepare_items();
		$table->display();
		?>
		<?php
	}

	private function new_season() {
		$season = new \Podlove\Modules\EpisodeNumbering\Model\Season;
		?>
		<h3><?php echo __( 'Add New Season', 'podlove' ); ?></h3>
		<?php
		$this->form_season( $season, 'create', __( 'Add New Season', 'podlove' ) );
	}

	private function form_season( $season, $action, $button_text = NULL ) {

		$form_args = array(
			'context' => 'podlove_season',
			'hidden'  => array(
				'season' => $season->id,
				'action' => $action
			)
		);

		\Podlove\Form\build_for( $season, $form_args, function ( $form ) use ( $season ) {
			$f = new \Podlove\Form\Input\TableWrapper( $form );

			if( $season->number !== '1' ) {
				$f->number( 'number', array(
					'label'       => __( 'Number', 'podlove' ),
					'description' => __( '', 'podlove' ),
					'html' => array( 'class' => 'regular-text required' )
				) );
			}

			$f->string( 'mnemonic', array(
				'label'       => __( 'Mnemonic', 'podlove' ),
				'description' => __( '', 'podlove' ),
				'html' => array( 'class' => 'regular-text required' )
			) );

			$f->text( 'description', array(
				'label'       => __( 'Description', 'podlove' ),
				'description' => __( '', 'podlove' )
			) );

		});

	}

	
	private function edit_season() {
		$season = \Podlove\Modules\EpisodeNumbering\Model\Season::find_by_id( $_REQUEST['season'] );
		echo '<h3>' . sprintf( __( 'Edit Season: %s', 'podlove' ), $season->number ) . '</h3>';
		$this->form_season( $season, 'save' );
	}

}