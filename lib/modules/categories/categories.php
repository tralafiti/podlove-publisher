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

		add_filter( 'podlove_manipulate_feed_title', function( $feed_title ) {
			return "asd";
		});

		add_filter( 'podlove_manipulate_feed_description', function( $feed_summary ) {
			return "ololol";
		});

		add_filter( 'podlove_feed_itunes_subtitle', function( $foo ) {
			return "<itunes:subtitle>FOOO</itunes:subtitle>";
		});

		add_filter( 'podlove_feed_itunes_summary', function( $foo ) {
			return "<itunes:summary>FOOO</itunes:summary>";
		});
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