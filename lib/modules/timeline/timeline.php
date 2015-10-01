<?php
namespace Podlove\Modules\Timeline;

use \Podlove\Modules\Timeline\Model;

class Timeline extends \Podlove\Modules\Base {

	protected $module_name = 'Timeline';
	protected $module_description = 'Adds support for the Podlove Timeline';
	protected $module_group = 'system';

	public static function is_core() {
		return true;
	}

	public function load() {
		add_filter( 'podlove_episode_form_data', array($this, 'extend_episode_form'), 10, 2 );
		add_filter( 'podlove_episode_data_filter', array($this, 'save_chapters'), 10, 2 );
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
	}

	public static function save_chapters($episode_data_filter) {

		$episode = \Podlove\Model\Episode::find_one_by_post_id($_POST['post_ID']);
		$chapters = \Podlove\Modules\Timeline\Model\Item::find_all_by_type_and_episode($episode->id, 'chapter');

		foreach ($chapters as $chapter) {
			$chapter_entry = \Podlove\Modules\Timeline\Model\Item::find_by_id($chapter->id);
			$extended_information = \Podlove\Modules\Timeline\Model\ExtendedAttributes::find_all_by_property('timeline_id', $chapter->id);

			foreach ($extended_information as $information) {
				$information->delete();
			}
			$chapter_entry->delete();
		}

		if ( ! isset($_POST['_podlove_meta']['timeline_chapters']) )
			return;

		$position = 0;

		foreach ($_POST['_podlove_meta']['timeline_chapters'] as $chapter) {
			$timeline_element = new \Podlove\Modules\Timeline\Model\Item;
			$chapter_element = new \Podlove\Modules\Timeline\Model\ExtendedAttributes;

			$timeline_element->episode_id = $episode->id;
			$timeline_element->position = $position;
			$timeline_element->title = $chapter['title'];
			$timeline_element->start_time = \Podlove\NormalPlayTime\Parser::parse($chapter['beginning']) / 1000;
			$timeline_element->type = 'chapter';
			$timeline_element->cuemark = 1;
			$timeline_element->save();

			$chapter_element->timeline_id = $timeline_element->id;
			$chapter_element->attribute_key = 'url';
			$chapter_element->attribute_value = $chapter['url'];
			$chapter_element->save();

			$position++;
		}
	}

	public static function extend_episode_form($form_data, $episode) {
		$form_data[] = array(
				'type' => 'callback',
				'key'  => 'chapters_form_table',
				'options' => array(
					'label'    => __( 'Chapters', 'podlove' ),
					'callback' => array('\Podlove\Modules\Timeline\Timeline', 'episode_form_extension')
				),
				'position' => 850
			);

		return $form_data;
	}

	public static function episode_form_extension() {
		//\Podlove\Modules\Timeline\Model\Item::build();
		//\Podlove\Modules\Timeline\Model\ExtendedAttributes::build();
		//self::migrate_chapters();
		
		$episode = \Podlove\Model\Episode::find_one_by_post_id(get_the_ID());
		$chapters = \Podlove\Modules\Timeline\Model\Item::find_all_by_type_and_episode($episode->id, 'chapter');
		$form_base_name = '_podlove_meta[timeline_chapters]';
		
		$existing_chapters = array();
		foreach ($chapters as $chapter) {
			$existing_chapters[] = array(
					'start_time' => $chapter->start_time,
					'title' => $chapter->title,
					'url' => $chapter->get_extended_attribute('url')
				);	
		}
		?>
		<div class="chapters-form-tabs">
			<button type="button" id="chapters-visual" data-container="chapters-form-container-visual" class="chapters-form-button"><?php _e('Visual', 'podlove'); ?></button>
			<button type="button" id="chapters-raw" data-container="chapters-form-container-text" class="chapters-form-button inactive-box"><?php _e('Text', 'podlove'); ?></button>
		</div>

		<div id="chapters-form" class="">
			<div id="chapters-form-container-text" class="chapters-form-container hidden">
				Fubar
			</div>
			<div id="chapters-form-container-visual" class="chapters-form-container">
				<table class="podlove_alternating" border="0" cellspacing="0">
					<thead>
						<tr>
							<th style="width: 60px;"><?php _e('Chapter', 'podlove'); ?></th>
							<th style="width: 105px;"><?php _e('Beginning', 'podlove'); ?></th>
							<th><?php _e('Title', 'podlove'); ?></th>
							<th><?php _e('URL', 'podlove'); ?></th>
							<th style="width: 60px">Remove</th>
							<th style="width: 30px"></th>
						</tr>
					</thead>
					<tbody id="chapters_table_body" style="min-height: 50px;"></tbody>
				</table>

				<div id="add_new_chapters_wrapper">
					<input class="button" id="add_new_chapters_button" value="+" type="button" />
				</div>

				<script type="text/template" id="chapter-row-template">
				<tr class="media_file_row">
					<td class="podlove_chapters_position" style="text-align: center;">
						{{chapter}}
					</td>
					<td>
						<input type="text" id="_podlove_meta_timeline_chapters_{{id}}_beginning" name="<?php echo $form_base_name ?>[{{id}}][beginning]" placeholder="00:00:00.000" value="{{beginning}}" />
					</td>
					<td>
						<input type="text" id="_podlove_meta_timeline_chapters_{{id}}_title" name="<?php echo $form_base_name ?>[{{id}}][title]" placeholder="Intro" value="{{title}}" />
					</td>
					<td>
						<input type="text" id="_podlove_meta_timeline_chapters_{{id}}_url" name="<?php echo $form_base_name ?>[{{id}}][url]" placeholder="http(s)://" value="{{url}}" />
					</td>	
					<td>
						<span class="chapter_remove">
							<i class="clickable podlove-icon-remove"></i>
						</span>
					</td>
					<td class="move column-move"><i class="reorder-handle podlove-icon-reorder"></i></td>
				</tr>
				</script>
				<script type="text/javascript">
					var PODLOVE = PODLOVE || {};
					PODLOVE.Chapters = <?php echo json_encode($existing_chapters) ?>;
				</script>
			</div>
		</div>
		<?php
	}

	public static function migrate_chapters() {
		$episodes = \Podlove\Model\Episode::all();

		foreach ($episodes as $episode) {
			$chapters = \Podlove\Chapters\Parser\Mp4chaps::parse($episode->chapters);
			$position = 0;

			if ( $chapters )
				foreach ($chapters as $chapter) {
					$timeline = new \Podlove\Modules\Timeline\Model\Item;
					$timeline->episode_id = $episode->id;
					$timeline->title = $chapter->get_title();
					$timeline->start_time = $chapter->get_raw_time() / 1000;
					$timeline->type = 'chapter';
					$timeline->position = $position;
					$timeline->cuemark = 1;
					$timeline->save();

					$timeline_chapter = new \Podlove\Modules\Timeline\Model\ExtendedAttributes;
					$timeline_chapter->timeline_id = $timeline->id;
					$timeline_chapter->attribute_key = 'url';
					$timeline_chapter->attribute_value = $chapter->get_link();
					$timeline_chapter->save();

					$position++;
				}
		}
	}

	public function admin_print_styles() {
		wp_register_style(
			'podlove_timeline_admin_style',
			$this->get_module_url() . '/admin.css',
			false,
			\Podlove\get_plugin_header( 'Version' )
		);
		wp_enqueue_style('podlove_timeline_admin_style');

		wp_register_script(
			'podlove_timeline_admin_script',
			$this->get_module_url() . '/admin.js',
			array( 'jquery', 'jquery-ui-tabs' ),
			\Podlove\get_plugin_header( 'Version' )
		);
		wp_enqueue_script('podlove_timeline_admin_script');
	}
}