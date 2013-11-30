<?php 
namespace Podlove\Modules\Networks\Model;

use \Podlove\Model\Base;

/**
 * Simplified Singleton model for network data.
 *
 * There is only one Network, that's why this is a singleton.
 * Data handling is still similar to the other models. Storage is different.
 */
class Network extends Base {

	/**
	 * Override Base::table_name() to get the right prefix
	 */
	public static function table_name() {
		global $wpdb;
		
		// Switching to the first blog in network (contains network tables) (It is always 1!)
		switch_to_blog(1);
		// get name of implementing class
		$table_name = get_called_class();
		// replace backslashes from namespace by underscores
		$table_name = str_replace( '\\', '_', $table_name );
		// remove Models subnamespace from name
		$table_name = str_replace( 'Model_', '', $table_name );
		// all lowercase
		$table_name = strtolower( $table_name );
		// prefix with $wpdb prefix
		return $wpdb->prefix . $table_name;
	}

	/** 
	*  Ftech Podcast
	*/

	public static function get_podcast( $id ) {
		switch_to_blog( $id );
		return \Podlove\Model\Podcast::get_instance();
	}

	/**
	 * Fetch all Podcasts
	 */
	public static function get_all_blogs() {
		global $wpdb;
		return $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
	}

	/**
	 * Fetch all Podcasts ordered
	 */
	public static function get_all_podcasts_ordered( $sortby = "title", $sort = 'ASC' ) {
		$blog_ids = static::get_all_blogs();

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

}

Network::property( 'title', 'VARCHAR(255)' );
Network::property( 'subtitle', 'TEXT' );
Network::property( 'description', 'TEXT' );
Network::property( 'url', 'TEXT' );
Network::property( 'logo', 'TEXT' );
Network::property( 'podcasts', 'TEXT' );