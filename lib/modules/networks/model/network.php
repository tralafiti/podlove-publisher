<?php 
namespace Podlove\Modules\Networks\Model;

use \Podlove\Model\Base;

/**
 * Simplified Singleton model for network data.
 *
 * There is only one Network, that's why this is a singleton.
 * Data handling is still similar to the other models. Storage is different.
 */
class Network {

	/**
	 * Singleton instance container.
	 * @var \Podlove\Model\Network|NULL
	 */
	private static $instance = NULL;

	/**
	 * Contains property values.
	 * @var  array
	 */
	private $data = array();

	/**
	 * Contains property names.
	 * @var array
	 */
	protected $properties = array();

	private $blog_id = NULL;

	/**
	 * Singleton.
	 * 
	 * @return \Podlove\Model\Network
	 */
	static public function get_instance() {

		// whenever the blog is switched, we need to reload all network data
		if ( ! isset( self::$instance ) || self::$instance->blog_id != get_current_blog_id() ) {

			$properties = isset( self::$instance ) ? self::$instance->properties : false;
			self::$instance = new self;
			self::$instance->blog_id = get_current_blog_id();

			// only take properties from preexisting instances
			if ( $properties )
				self::$instance->properties = $properties;
		}

		return self::$instance;
	}

	protected function __construct() {
		$this->data = array();
		$this->fetch();
	}
	
	private function set_property( $name, $value ) {
		$this->data[ $name ] = $value;
	}
	
	public function __get( $name ) {
		if ( $this->has_property( $name ) ) {
			return $this->get_property( $name );
		} else {
			return $this->$name;
		}
	}
	
	private function get_property( $name ) {
		if ( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		} else {
			return NULL;
		}
	}

	/**
	 * Return a list of property dictionaries.
	 * 
	 * @return array property list
	 */
	private function properties() {
		
		if ( ! isset( $this->properties ) )
			$this->properties = array();
		
		return $this->properties;
	}
	
	/**
	 * Does the given property exist?
	 * 
	 * @param string $name name of the property to test
	 * @return bool True if the property exists, else false.
	 */
	public function has_property( $name ) {
		return in_array( $name, $this->property_names() );
	}
	
	/**
	 * Return a list of property names.
	 * 
	 * @return array property names
	 */
	public function property_names() {
		return array_map( function ( $p ) { return $p['name']; } , $this->properties );
	}

	/**
	 * Define a property with by name.
	 * 
	 * @param string $name Name of the property / column
	 */
	public function property( $name ) {

		if ( ! isset( $this->properties ) )
			$this->properties = array();

		array_push( $this->properties, array( 'name' => $name ) );
	}

	/**
	 * Save current state to database.
	 */
	public function save() {
		update_site_option( 'podlove_network', $this->data );
	}

	/**
	 * Load network data.
	 */
	private function fetch() {
		$this->data = get_site_option( 'podlove_network', array() );
	}

	/**
	 * Get all the Podcasts!
	 */
	public function get_podcasts( $sortby = "title", $sort = 'ASC' ) {
		global $wpdb;
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		$podcasts = array();

		foreach ($blog_ids as $blog_id) {
			switch_to_blog( $blog_id );
			$podcasts[ $blog_id ] = \Podlove\Model\Podcast::get_instance();
		}

		uasort( $podcasts, function ( $a, $b ) use ( $sortby, $sort ) {
			return strnatcmp( $a->$sortby, $b->$sortby );
		});

		if( $sort == 'DESC' ) {
			krsort( $podcasts );
		}

		return $podcasts;		
	}

	public function get_statistics() {
		$podcasts = self::get_podcasts();

		$total_episodes = array();
		$total_contributors = array();
		$total_podcasts = count( $podcasts );

		foreach ($podcasts as $blog_id => $podcast_data) {
			switch_to_blog( $blog_id );
			$total_episodes[] = count( \Podlove\Model\Episode::all() );
			$total_contributors[] = count( \Podlove\Modules\Contributors\Model\Contributor::all() );
		}

		return array(	'total_podcasts'		=> $total_podcasts,
						'total_episodes' 		=> array_sum( $total_episodes ),
						'total_contributors' 	=> array_sum( $total_contributors )
		);
	}

	public function latest_episodes( $number_of_episodes = "10", $orderby = "date", $order = "desc" ) {
		global $wpdb;
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		$prefix = $wpdb->get_blog_prefix(0);
		$prefix = str_replace( '1_', '' , $prefix );
		$query = "";
		$episodes = array();

		// Generate mySQL Query
		foreach ( $blog_ids as $blog_key => $blog_id ) {
			if( $blog_key == 0 ) {
			    $post_table = $prefix . "posts";
			} else {
			    $post_table = $prefix . $blog_id . "_posts";
			}

			$post_table = esc_sql( $post_table );
	        $blog_table = esc_sql( $prefix . 'blogs' );

	        $query .= "(SELECT $post_table.ID, $post_table.post_title, $post_table.post_date, $blog_table.blog_id FROM $post_table, $blog_table\n";
	        $query .= "WHERE $post_table.post_type = 'podcast'";
	        $query .= "AND $post_table.post_status = 'publish'";
	        $query .= "AND $blog_table.blog_id = {$blog_id})";

	        if( $blog_id !== end($blog_ids) ) 
	           $query .= "UNION\n";
	        else
	           $query .= "ORDER BY post_date DESC LIMIT 0, $number_of_episodes";		
		}

      	$recent_posts = $wpdb->get_results( $query );

      	foreach ($recent_posts as $post) {
   			switch_to_blog( $post->blog_id );
   			$episodes[] = array ( 	'episode' => \Podlove\Model\Episode::find_one_by_post_id( $post->ID ),
   									'blog_id' => $post->blog_id );
      	}

      	return $episodes;
	}

}

$network = Network::get_instance();
$network->property( 'title' );
$network->property( 'subtitle' );
$network->property( 'description' );
$network->property( 'url' );
$network->property( 'logo' );