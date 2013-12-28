<?php
namespace Podlove\Modules\Newsletter\Settings;

use Podlove\Model;

class Subscriptions {

	static $pagehook;
	
	public function __construct( $handle ) {
		add_action( 'admin_init', array( $this, 'process_form' ) );
	}
	
	public static function get_action_link( $subscription, $title, $action = 'edit', $class = 'link' ) {
		$request = ( isset( $_REQUEST['podlove_tab'] ) ? "&amp;podlove_tab=".$_REQUEST['podlove_tab'] : '' );
		return sprintf(
			'<a href="?page=%s%s&amp;action=%s&amp;subscription=%s" class="%s">' . $title . '</a>',
			$_REQUEST['page'],
			$request,
			$action,
			$subscription->id,
			$class
		);
	}
	
	public function process_form() {

		if ( ! isset( $_REQUEST['subscription'] ) )
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
		if ( isset($_GET["action"]) AND $_GET["action"] == 'confirm_delete' AND isset( $_REQUEST['subscription'] ) ) {
			 $subscription = \Podlove\Modules\Newsletter\Model\Subscription::find_by_id( $_REQUEST['subscription'] );
			?>
			<div class="updated">
				<p>
					<strong>
						<?php echo sprintf( __( 'You selected to delete the subscription of "%s". Please confirm this action.', 'podlove' ), $subscription->title ) ?>
					</strong>
				</p>
				<p>
					<?php echo self::get_action_link( $subscription, __( 'Delete subscription permanently', 'podlove' ), 'delete', 'button' ) ?>
					<?php echo self::get_action_link( $subscription, __( 'Don\'t change anything', 'podlove' ), 'keep', 'button-primary' ) ?>
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
	 * Process form: save/update a subscription
	 */
	private function save() {
		if ( ! isset( $_REQUEST['subscription'] ) )
			return;
			
		$subscription = \Podlove\Modules\Newsletter\Model\Subscription::find_by_id( $_REQUEST['subscription'] );
		$subscription->update_attributes( $_POST['podlove_newsletter_subscription'] );
		
		self::redirect( 'index', $subscription->id );
	}
	
	/**
	 * Process form: create a subscription
	 */
	private function create() {
		global $wpdb;
		
		$contributor = new \Podlove\Modules\Newsletter\Model\Subscription;
		$contributor->update_attributes( $_POST['podlove_contributor_subscription'] );

		self::redirect( 'index' );
	}
	
	/**
	 * Process form: delete a subscription
	 */
	private function delete() {
		if ( ! isset( $_REQUEST['subscription'] ) )
			return;

		\Podlove\Modules\Newsletter\Model\Subscription::find_by_id( $_REQUEST['subscription'] )->delete();
		
		self::redirect( 'index' );
	}
	
	/**
	 * Helper method: redirect to a certain page.
	 */
	private function redirect( $action, $subscription_id = NULL ) {
		$page   = 'admin.php?page=' . $_REQUEST['page'];
		$show   = ( $subscription_id ) ? '&subscription=' . $subscription_id : '';
		$action = '&action=' . $action;
		$tab = '&podlove_tab=subscriptions';
		
		wp_redirect( admin_url( $page . $show . $action . $tab ) );
		exit;
	}
	
	private function view_template() {
		?>
		<h2>
			<a href="?page=<?php echo $_REQUEST['page']; ?>&amp;podlove_tab=subscriptions&amp;action=new" class="add-new-h2"><?php echo __( 'Add New', 'podlove' ); ?></a>
		</h2>
		<?php
		$table = new \Podlove\Modules\Newsletter\Subscription_List_Table();
		$table->prepare_items();
		$table->display();
	}
	
	private function new_template() {
		$subscription = new \Podlove\Modules\Newsletter\Model\Subscription;
		?>
		<h3><?php echo __( 'Add New Subscription', 'podlove' ); ?></h3>
		<?php
		$this->form_template( $subscription, 'create', __( 'Add New Subscription', 'podlove' ) );
	}
	
	private function edit_template() {
		$subscription = \Podlove\Modules\Newsletter\Model\Subscription::find_by_id( $_REQUEST['subscription'] );
		echo '<h3>' . sprintf( __( 'Edit Subscription: %s', 'podlove' ), $subscription->title ) . '</h3>';
		$this->form_template( $subscription, 'save' );
	}
	
	private function form_template( $subscription, $action, $button_text = NULL ) {

		$form_args = array(
			'context' => 'podlove_contributor_subscription',
			'hidden'  => array(
				'subscription' => $subscription->id,
				'action' => $action
			)
		);

		\Podlove\Form\build_for( $subscription, $form_args, function ( $form ) {
			$wrapper = new \Podlove\Form\Input\TableWrapper( $form );

			$subscription = $form->object;

			$wrapper->string( 'email', array(
				'label'       => __( 'E-mail', 'podlove' ),
				'html'        => array( 'class' => 'required' )
			) );

		} );
	}
}
