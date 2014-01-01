<?php
namespace Podlove\Modules\Newsletter;

class Verification_List_Table extends \Podlove\List_Table {
	
	function __construct(){
		global $status, $page;
		        
		// Set parent defaults
		parent::__construct( array(
		    'singular'  => 'verification',   // singular name of the listed records
		    'plural'    => 'verifications',  // plural name of the listed records
		    'ajax'      => false       // does this table support ajax?
		) );
	}

	public function column_email( $verification ) {
		$actions = array(
			'edit'   => Settings\Verifications::get_action_link( $verification, __( 'Edit', 'podlove' ) ),
			'delete' => Settings\Verifications::get_action_link( $verification, __( 'Delete', 'podlove' ), 'confirm_delete' )
		);

		return sprintf( '%s<br />%s',
		    Settings\Verifications::get_action_link( $verification, $verification->email ),
		    $this->row_actions( $actions )
		) . '<input type="hidden" class="subscription_id" value="' . $verification->id . '">';
	}

	public function column_date( $verification ) {
		return $verification->subscription_date;
	}

	public function column_ip( $verification ) {
		if ( $verification->ip == '' )
			return '-';
		
		return $verification->ip;
	}

	public function get_columns(){
		$columns = array(
			'email'             => __( 'E-mail', 'podlove' ),
			'ip'              => __( 'IP', 'podlove' ),
			'date'              => __( 'Subscription Date', 'podlove' )
		);
		return $columns;
	}

	public function display() {
		parent::display();
		?>
		<style type="text/css">
			td.column-email, th.column-email { width: 300px; }
			td.column-date, th.column-date {  }
		</style>
		<?php
	}

	public function search_form() {
		?>
		<form method="post">
		  <?php $this->search_box('search', 'search_id'); ?>
		</form>
		<?php
	}

	public function get_sortable_columns() {
	  $sortable_columns = array(
	    'email'                => array('email',true),
	    'ip'                 => array('ip',false),
	    'date'                 => array('subscription_date',false)
	  );
	  return $sortable_columns;
	}	

	public function prepare_items() {

		// number of items per page
		$per_page = get_user_meta( get_current_user_id(), 'podlove_verifications_per_page', true);
		if( empty($per_page) ) {
			$per_page = 10;
		}

		// define column headers
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// look for order options
		if( isset($_GET['orderby'])  ) {
			$orderby = 'ORDER BY ' . $_GET['orderby'];
		} else{
			$orderby = 'ORDER BY subscription_date';
		}

		// look how to sort
		if( isset($_GET['order'])  ) {
			$order = $_GET['order'];
		} else{
			$order = 'DESC';
		}
		
		// retrieve data
		if( !isset($_POST['s']) ) {
			$data = \Podlove\Modules\Newsletter\Model\NewsletterVerification::all( $orderby . ' ' . $order );
		} else if ( empty($_POST['s']) ) {
			$data = \Podlove\Modules\Newsletter\Model\NewsletterVerification::all( $orderby . ' ' . $order );
		} else {
	 	 	$search   = $_POST['s'];
			$data     = \Podlove\Modules\Newsletter\Model\NewsletterVerification::all(
				'WHERE 
				`email` LIKE \'%' . $search . '%\' OR
				`ip` LIKE \'%' . $search . '%\' OR
				`subscription_date` LIKE \'%' . $search . '%\'
				' . $orderby . ' ' . $order
			);
		}
		
		// get current page
		$current_page = $this->get_pagenum();
		// get total items
		$total_items = count( $data );
		// extrage page for current page only
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ) , $per_page );
		// add items to table
		$this->items = $data;
		
		// register pagination options & calculations
		$this->set_pagination_args( array(
		    'total_items' => $total_items,
		    'per_page'    => $per_page,
		    'total_pages' => ceil( $total_items / $per_page )
		) );

		// Search box
		$this->search_form();
	}

	function no_items() {
		$url = sprintf( '?page=%s&podlove_tab=verifications&action=%s', $_REQUEST['page'], 'new' );
		?>
		<div style="margin: 20px 10px 10px 5px">
	 		<span class="add-new-h2" style="background: transparent">
			<?php _e( 'No items found.' ); ?>
			</span>
			<a href="<?php echo $url ?>" class="add-new-h2">
	 		<?php _e( 'Add New' ) ?>
	 		</a>
	 	</div>
	 	<?php
	 }
}
