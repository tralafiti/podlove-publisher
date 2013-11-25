<?php
namespace Podlove\Settings\Network;
use \Podlove\Model;

class Dashboard {

	static $pagehook;

	public function __construct() {

		// use \Podlove\Podcast_Post_Type::NETWORK_SETTINGS_PAGE_HANDLE to replace
		// default first item name
		Dashboard::$pagehook = add_submenu_page(
			/* $parent_slug*/ \Podlove\Podcast_Post_Type::NETWORK_SETTINGS_PAGE_HANDLE,
			/* $page_title */ __( 'Dashboard', 'podlove' ),
			/* $menu_title */ __( 'Dashboard', 'podlove' ),
			/* $capability */ 'administrator',
			/* $menu_slug  */ \Podlove\Podcast_Post_Type::NETWORK_SETTINGS_PAGE_HANDLE,
			/* $function   */ array( $this, 'settings_page' )
		);

		add_action( Dashboard::$pagehook, function () {

			wp_enqueue_script( 'postbox' );
			add_screen_option( 'layout_columns', array(
				'max' => 2, 'default' => 2
			) );

			wp_register_script(
				'cornify-js',
				\Podlove\PLUGIN_URL . '/js/admin/cornify.js'
			);
			wp_enqueue_script( 'cornify-js' );
		} );
	}

	public static function settings_page() {
		add_meta_box( Dashboard::$pagehook . '_about', __( 'About', 'podlove' ), '\Podlove\Settings\Dashboard::about_meta', Dashboard::$pagehook, 'side' );		
		add_meta_box( Dashboard::$pagehook . '_network_overview', __( 'Podcast Network', 'podlove' ), '\Podlove\Settings\Network\Dashboard::network_overview', Dashboard::$pagehook, 'normal' );

		do_action( 'podlove_network_dashboard_meta_boxes' );

		?>
		<div class="wrap">
			<?php screen_icon( 'podlove-podcast' ); ?>
			<h2><?php echo __( 'Podlove Network Dashboard', 'podlove' ); ?></h2>

			<div id="poststuff" class="metabox-holder has-right-sidebar">
				
				<!-- sidebar -->
				<div id="side-info-column" class="inner-sidebar">
					<?php do_action( 'podlove_settings_before_sidebar_boxes' ); ?>
					<?php do_meta_boxes( Dashboard::$pagehook, 'side', NULL ); ?>
					<?php do_action( 'podlove_settings_after_sidebar_boxes' ); ?>
				</div>

				<!-- main -->
				<div id="post-body" class="has-sidebar">
					<div id="post-body-content" class="has-sidebar-content">
						<?php do_action( 'podlove_settings_before_main_boxes' ); ?>
						<?php do_meta_boxes( Dashboard::$pagehook, 'normal', NULL ); ?>
						<?php do_meta_boxes( Dashboard::$pagehook, 'additional', NULL ); ?>
						<?php do_action( 'podlove_settings_after_main_boxes' ); ?>						
					</div>
				</div>

				<br class="clear"/>

			</div>

			<!-- Stuff for opening / closing metaboxes -->
			<script type="text/javascript">
			jQuery( document ).ready( function( $ ){
				// close postboxes that should be closed
				$( '.if-js-closed' ).removeClass( 'if-js-closed' ).addClass( 'closed' );
				// postboxes setup
				postboxes.add_postbox_toggles( '<?php echo \Podlove\Podcast_Post_Type::NETWORK_SETTINGS_PAGE_HANDLE; ?>' );
			} );
			</script>

			<form style='display: none' method='get' action=''>
				<?php
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				?>
			</form>

		</div>
		<?php
	}

	public function network_overview() {
		
		global $wpdb;
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		$podcasts = array();
		
		foreach ($blog_ids as $blog_id) {
			switch_to_blog( $blog_id );
			$podcast_data = \Podlove\Model\Podcast::get_instance();
			$number_of_episodes = count( \Podlove\Model\Episode::all() );
			$number_of_contributors = count( \Podlove\Modules\Contributors\Model\Contributor::all() );

			$podcast_entry = array(	"ID" => $blog_id,
									"title" => $podcast_data->title,
									"episodes" => $number_of_episodes,
									"contributors" => $number_of_contributors,
									"domain" => site_url(),
									"cover" => $podcast_data->cover_image );

			$podcasts[] = $podcast_entry;
		}

		$podcast_table = new PodcastNetworkTable();
		$podcast_table->prepare_items( $podcasts ); 

		$podcast_table->display(); 

	}
}

class PodcastNetworkTable extends \WP_List_Table {

	public function get_columns(){
	  $columns = array(
	  	'cover' => '',
	    'title' => 'Title',
	    'episodes'    => 'Episodes',
	    'contributors' => 'Contributors',
	    'domain'      => 'Domain'
	  );
	  return $columns;
	}

	public function prepare_items( $podcasts ) {
	  $columns = $this->get_columns();
	  $hidden = array();
	  $sortable = array();
	  $this->_column_headers = array($columns, $hidden, $sortable);
	  $this->items = $podcasts;
	}

	public function column_default( $item, $column_name ) {
	  switch( $column_name ) { 
	  	case 'cover':
	  		if( $item[ $column_name ] == "" ) {
	  			return '';
	  		} else {
	  			return '<img src="'. $item[ $column_name ] .'" alt="' . $item[ 'title' ] . 'Cover" style="width: 50px;" />';
	  		}
	  	break;
	    case 'title':
	    	return "<a href='" . $item[ 'domain' ] . "'>" . $item[ $column_name ] . "</a>";
	    break;
	    case 'episodes':
	    case 'contributors':
		    return $item[ $column_name ];
		break;
	    case 'domain':
	    	return "<a href='" . $item[ $column_name ] . "'>" . $item[ $column_name ] . "</a>";
	    break;
	  }
	}

}
