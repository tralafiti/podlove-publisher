<?php
namespace Podlove\Modules\Newsletter\Settings;

use Podlove\Model;

class Verifications {

	static $pagehook;
	
	public function __construct( $handle ) {
		add_action( 'admin_init', array( $this, 'process_form' ) );
	}
	
	public static function get_action_link( $verification, $title, $action = 'edit', $class = 'link' ) {
		$request = ( isset( $_REQUEST['podlove_tab'] ) ? "&amp;podlove_tab=".$_REQUEST['podlove_tab'] : '' );
		return sprintf(
			'<a href="?page=%s%s&amp;action=%s&amp;verification=%s" class="%s">' . $title . '</a>',
			$_REQUEST['page'],
			$request,
			$action,
			$verification->id,
			$class
		);
	}
	
	public function process_form() {

		if ( ! isset( $_REQUEST['verification'] ) )
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
		if ( isset($_GET["action"]) AND $_GET["action"] == 'confirm_delete' AND isset( $_REQUEST['verification'] ) ) {
			 $verification = \Podlove\Modules\Newsletter\Model\NewsletterVerification::find_by_id( $_REQUEST['verification'] );
			?>
			<div class="updated">
				<p>
					<strong>
						<?php echo sprintf( __( 'You selected to delete the verification of "%s". Please confirm this action.', 'podlove' ), $verification->email ) ?>
					</strong>
				</p>
				<p>
					<?php echo self::get_action_link( $verification, __( 'Delete verification permanently', 'podlove' ), 'delete', 'button' ) ?>
					<?php echo self::get_action_link( $verification, __( 'Don\'t change anything', 'podlove' ), 'keep', 'button-primary' ) ?>
				</p>
			</div>
			<?php
		}
		
		?>
		<div class="wrap">
			<?php
				if(isset($_GET["action"])) {
					switch ( $_GET["action"] ) {
						case 'new':   $this->new_template();  break;
						case 'edit':  $this->edit_template(); break;
						default:      $this->view_template(); break;
					}
				} else {
					$this->view_template();
				}
			?>
		</div>	
		<?php
	}
	
	/**
	 * Process form: save/update a verification
	 */
	private function save() {
		if ( ! isset( $_REQUEST['verification'] ) )
			return;
			
		$verification = \Podlove\Modules\Newsletter\Model\NewsletterVerification::find_by_id( $_REQUEST['verification'] );
		$verification->update_attributes( $_POST['podlove_newsletter_verification'] );
		
		self::redirect( 'index', $verification->id );
	}
	
	/**
	 * Process form: create a verification
	 */
	private function create() {
		global $wpdb;
		
		$verification = new \Podlove\Modules\Newsletter\Model\NewsletterVerification;
		$verification->subscription_date = current_time( 'mysql' );
		// leave IP blank, as the admin added the user
		$verification->verification_hash = uniqid();
		$verification->update_attributes( $_POST['podlove_newsletter_verification'] );

		self::redirect( 'index' );
	}
	
	/**
	 * Process form: delete a verification
	 */
	private function delete() {
		if ( ! isset( $_REQUEST['verification'] ) )
			return;

		\Podlove\Modules\Newsletter\Model\NewsletterVerification::find_by_id( $_REQUEST['verification'] )->delete();
		
		self::redirect( 'index' );
	}
	
	/**
	 * Helper method: redirect to a certain page.
	 */
	private function redirect( $action, $verification_id = NULL ) {
		$page   = 'admin.php?page=' . $_REQUEST['page'];
		$show   = ( $verification_id ) ? '&verification=' . $verification_id : '';
		$action = '&action=' . $action;
		$tab = '&podlove_tab=verifications';
		
		wp_redirect( admin_url( $page . $show . $action . $tab ) );
		exit;
	}
	
	private function view_template() {
		?>
		<h2>
			<a href="?page=<?php echo $_REQUEST['page']; ?>&amp;podlove_tab=verifications&amp;action=new" class="add-new-h2"><?php echo __( 'Add New', 'podlove' ); ?></a>
		</h2>
		<?php
		$table = new \Podlove\Modules\Newsletter\Verification_List_Table();
		$table->prepare_items();
		$table->display();
	}
	
	private function new_template() {
		$verification = new \Podlove\Modules\Newsletter\Model\NewsletterVerification;
		?>
		<h3><?php echo __( 'Add New verification', 'podlove' ); ?></h3>
		<?php
		$this->form_template( $verification, 'create', __( 'Add New verification', 'podlove' ) );
	}
	
	private function edit_template() {
		$verification = \Podlove\Modules\Newsletter\Model\NewsletterVerification::find_by_id( $_REQUEST['verification'] );
		echo '<h3>' . sprintf( __( 'Edit verification: %s', 'podlove' ), $verification->title ) . '</h3>';
		$this->form_template( $verification, 'save' );
	}
	
	private function form_template( $verification, $action, $button_text = NULL ) {

		$form_args = array(
			'context' => 'podlove_newsletter_verification',
			'hidden'  => array(
				'verification' => $verification->id,
				'action' => $action
			)
		);

		\Podlove\Form\build_for( $verification, $form_args, function ( $form ) {
			$wrapper = new \Podlove\Form\Input\TableWrapper( $form );

			$verification = $form->object;

			$wrapper->string( 'email', array(
				'label'       => __( 'E-mail', 'podlove' ),
				'html'        => array( 'class' => 'required' )
			) );

		} );
	}
}
