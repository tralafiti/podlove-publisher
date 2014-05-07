<?php
namespace Podlove\Modules\Categories;
use \Podlove\Model;

class Categories extends \Podlove\Modules\Base {

	protected $module_name = 'Categories';
	protected $module_description = 'Enable categories for episodes.';
	protected $module_group = 'metadata';

	public function load() {
		add_filter( 'podlove_post_type_args', function ( $args ) {
			$args['taxonomies'][] = 'category';
			return $args;
		} );

		add_action( 'category_edit_form_fields', array( $this, 'category_edit_form_fields' ) );
		add_action( 'edited_category', array( $this, 'save_category_extra_fields' ) );

		/** Check if a category is present. If so, check if the holy
		 *  trinity of Title, Subtitle and Summary needs to be overwritten.
		 */
		add_filter( 'podlove_rss_feed_description', function( $feed_description ) {
			$overwrite_options = Categories::get_overwrite_options();

			if ( empty( $overwrite_options['description'] ) )
				return $feed_description;

			return $overwrite_options['description'];
		});
		add_filter( 'podlove_manipulate_feed_title', function( $feed_title ) {
			$overwrite_options = Categories::get_overwrite_options();

			if ( empty( $overwrite_options['feed_title'] ) )
				return $feed_title;

			return $overwrite_options['feed_title'];
		});
		add_filter( 'podlove_manipulate_feed_description', function( $feed_summary ) {
			$overwrite_options = Categories::get_overwrite_options();

			if ( empty( $overwrite_options['feed_summary'] ) )
				return $feed_summary;

			return $overwrite_options['feed_summary'];
		});
		add_filter( 'podlove_feed_itunes_subtitle', function( $feed_subtitle ) {
			$overwrite_options = Categories::get_overwrite_options();

			if ( empty( $overwrite_options['feed_subtitle'] ) )
				return $feed_subtitle;

			return "<itunes:subtitle>" . $overwrite_options['feed_subtitle'] . "</itunes:subtitle>";
		});
		add_filter( 'podlove_feed_itunes_summary', function( $feed_summary ) {
			$overwrite_options = Categories::get_overwrite_options();

			if ( empty( $overwrite_options['feed_summary'] ) )
				return $feed_summary;

			return "<itunes:summary>" . $overwrite_options['feed_summary'] . "</itunes:summary>";
		});
	}

	public static function get_overwrite_options() {
		$category = Categories::get_category();

		if ( is_null( $category ) )
			return NULL;

		$overwrite_options = get_option( 'podlove_category_extension_' . $category->term_id );
		$overwrite_options['description'] = $category->description;

		return $overwrite_options;
	}

	public static function get_category() {
		global $wp_query;

		if ( !get_query_var('category_name') )
			return NULL;

		$category = get_category_by_slug( get_query_var('category_name') );

		return $category;
	}

	public function category_edit_form_fields() {
		$podlove_feed_extension = get_option( 'podlove_category_extension_' . $_REQUEST['tag_ID'] );
		?>
		<tr class="form-field">
			<th valign="top" scope="row">
				<label for="catpic"><?php _e( 'Category Feed Title', 'podlove' ); ?></label>
			</th>
			<td>
				<input type="text" id="podlove_feed_title" name="podlove[feed_title]" class="regular-text required" 
				 value="<?php echo ( isset( $podlove_feed_extension['feed_title'] ) ? $podlove_feed_extension['feed_title'] : '' ); ?>" />
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top" scope="row">
				<label for="catpic"><?php _e( 'Category Feed Subtitle', 'podlove' ); ?></label>
			</th>
			<td>
				<input type="text" id="podlove_feed_subtitle" name="podlove[feed_subtitle]" class="regular-text required"
				 value="<?php echo ( isset( $podlove_feed_extension['feed_subtitle'] ) ? $podlove_feed_extension['feed_subtitle'] : '' ); ?>" />
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top" scope="row">
				<label for="catpic"><?php _e( 'Category Feed Summary', 'podlove' ); ?></label>
			</th>
			<td>
				<textarea class="autogrow" cols="40" rows="3" name="podlove[feed_summary]"><?php echo ( isset( $podlove_feed_extension['feed_summary'] ) ? $podlove_feed_extension['feed_summary'] : '' ); ?></textarea>
			</td>
		</tr>
		<?php
	}

	public function save_category_extra_fields( $category_id ) {
		if ( isset( $_POST['podlove'] ) ) {
			$c = $category_id;
			$podlove_feed_extension = get_option( "podlove_category_extension_$c" );
			$podlove_feed_keys = array_keys( $_POST['podlove'] );
				foreach ( $podlove_feed_keys as $key ) {
		 			if ( isset($_POST['podlove'][$key]) ) {
						$podlove_feed_extension[$key] = $_POST['podlove'][$key];
		 			}
		   		}
		       update_option( "podlove_category_extension_$c", $podlove_feed_extension );
		   }
	}

}