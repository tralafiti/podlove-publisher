<?php
namespace Podlove\Model;

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
	public function get_podcasts() {
		global $wpdb;
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		$podcasts = array();

		foreach ($blog_ids as $blog_id) {
			switch_to_blog( $blog_id );
			$podcasts[] = \Podlove\Model\Podcast::get_instance();
		}
		return $podcasts;
	}

}

$network = Network::get_instance();
$network->property( 'title' );
$network->property( 'subtitle' );
$network->property( 'description' );
$network->property( 'url' );
$network->property( 'logo' );