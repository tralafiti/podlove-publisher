<?php
namespace Podlove\Storage\WordpressStorage;

use \Podlove\Model;
use \Podlove\Model\Episode;
use \Podlove\Model\EpisodeAsset;
use \Podlove\Model\MediaFile;

class MediaMetaBox {

	public function __construct() {
		add_action('add_meta_boxes_podcast', [$this, 'add_meta_box']);
		add_action('save_post_podcast', [$this, 'save_post']);
	}

	public function add_meta_box() {
		add_meta_box(
			/* $id       */ 'podlove_podcast_media',
			/* $title    */ __( 'Podcast Media', 'podlove' ),
			/* $callback */ [$this, 'meta_box_callback'],
			/* $page     */ 'podcast',
			/* $context  */ 'advanced',
			/* $priority */ 'high'
		);
	}

	public function save_post($post_id)
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
			return;
		
		if (empty($_POST['podlove_noncename']) || ! wp_verify_nonce($_POST['podlove_noncename'], \Podlove\PLUGIN_FILE))
			return;
		
		if ('podcast' !== $_POST['post_type'])
			return;

		if (!current_user_can('edit_post', $post_id))
			return;

		if (!isset($_POST['_podlove_media']))
			return;

		$media_attachments = (array) $_POST['_podlove_media'];
		update_post_meta($post_id, 'podlove_media_attachments', $media_attachments);

		foreach ($media_attachments as $asset_id => $attachment_id) {

			$attachment_meta = wp_get_attachment_metadata($attachment_id);
			$episode = Episode::find_or_create_by_post_id($post_id);
			$asset = EpisodeAsset::find_by_id($asset_id);

			if (!$file = MediaFile::find_by_episode_id_and_episode_asset_id($episode->id, $asset->id)) {
				$file = new MediaFile;
				$file->episode_id = $episode->id;
				$file->episode_asset_id = $asset->id;
			}
			
			if ($attachment_meta) {
				$file->size = $attachment_meta['filesize'];
				$file->save();
			} else {
				$file->delete();
			}
			
		}
	}

	public function meta_box_callback($post) {

		$post_id = $post->ID;

		$podcast = Model\Podcast::get();
		$episode = Model\Episode::find_or_create_by_post_id($post_id);

		$attachment_map = get_post_meta($post_id, 'podlove_media_attachments', true);
		if (!$attachment_map) {
			$attachment_map = [];
		}

		$assets = Model\EpisodeAsset::all();
		?>

<script type="text/javascript">
var podlove_media_attachment_data = {};
</script>
<table class="media_file_table" border="0" cellspacing="0">
<thead>
	<tr>
		<th>Asset</th>
		<th>URL</th>
		<th>Size</th>
		<th>Upload</th>
	</tr>
</thead>
<tbody>
<?php foreach ($assets as $asset): ?>
	<?php
	// get attachment for asset
	$attachment_id = $attachment_map[$asset->id];
	if ($attachment_id) {
		$attachment = wp_prepare_attachment_for_js($attachment_id);
	} else {
		$attachment = [];
	}
	?>
	<tr class="media_file_row podlove_storage_upload attachment-<?php echo $attachment['id'] ?>">
		<td>
			<?php echo $asset->title ? $asset->title : $asset->title() ?>
		</td>
		<td class="podlove-permalink">
		<?php if ($attachment): ?>
			<script type="text/javascript">
			podlove_media_attachment_data[<?php echo $attachment['id'] ?>] = <?php echo $attachment ? json_encode($attachment) : 'null' ?>;
			</script>
		<?php endif ?>
		</td>
		<td class="podlove-size"></td>
		<td>
			<div class="podlove_media_upload">
				<button class="button podlove_episode_media_upload_button">Upload Media</button>
				<input type="hidden" value="<?php echo $attachment['id'] ?>" class="podlove-media-field" name="_podlove_media[<?php echo $asset->id ?>]" />
			</div>
		</td>
	</tr>
<?php endforeach ?>
</tbody>
</table>


<script type="text/javascript">
(function($) {

	var upload_button = null;

	function render_attachment(attachment) {
		var wrapper   = $(".media_file_row.attachment-" + attachment.id),
			icon      = wrapper.find(".podlove-icon"),
			permalink = wrapper.find(".podlove-permalink"),
			size      = wrapper.find(".podlove-size"),
			upload    = wrapper.find(".podlove_media_upload")
		;

		icon.html('<img src="' + attachment.image.src + '" />');
		permalink.html('<a href="' + attachment.url + '">' + attachment.filename + '</a>');
		size.html(attachment.filesizeHumanReadable);
	}

	function init_media_select() {
		var params = {	
			frame:   'select',
			library: { type: 'audio' },
			button:  { text: 'Select Media' },
			className: 'media-frame',
			title: 'Episode Media',
			state: 'podlove_episode_media_state'
		},
		  file_frame = wp.media(params),
		  library = new wp.media.controller.Library({
			id:         params['state'],
			priority:   20,
			filterable: false,
			searchable: true,
			content: 'upload',
			library:    wp.media.query( file_frame.options.library ),
			multiple:   false,
			editable:   false,
			displaySettings: false,
			allowLocalEdits: false
		});

		file_frame.states.add([library]);
		file_frame.on('select update insert', function(e) {
			var state      = file_frame.state(), 
				attachment = state.get('selection').first().toJSON(),
				value      = attachment.id,
				wrapper = upload_button.closest(".media_file_row"),
				input = wrapper.find("input.podlove-media-field")
				;

			// @todo: remove all classes starting with 'attachment-'

			wrapper.addClass('attachment-' + attachment.id);

			input.val(value);

			render_attachment(attachment);
		});

		$(".podlove_episode_media_upload_button").on('click', function(e) {
			e.preventDefault();
			upload_button = $(this);
			file_frame.open();
		});
	}

	$(document).ready(function () {
		init_media_select();

		if (podlove_media_attachment_data) {
			$.each(podlove_media_attachment_data, function (attachment_id, attachment) {
				render_attachment(podlove_media_attachment_data[attachment_id]);
			});
		};
	});

})(jQuery);	 
</script>

<style type="text/css">
#advanced-sortables { margin-top: 20px; }
.podlove_media_upload {
	width: 100%;
	/*border: 4px dashed #b4b9be;*/
	/*background: #f1f1f1;*/
	text-align: center;
	/*padding: 20px;*/
	box-sizing: border-box;
}

.media_file_table th {
	text-align: left;
	padding-left: 5px;
}
</style>
		<?php
	}

}