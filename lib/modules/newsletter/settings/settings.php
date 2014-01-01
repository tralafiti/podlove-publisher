<?php
namespace Podlove\Modules\Newsletter\Settings;

use Podlove\Model;

class Settings {

	static $pagehook;
	
	public function __construct( $handle ) {
		add_action( 'admin_init', array( $this, 'process_form' ) );
	}
	
	public function process_form() {

		if( isset( $_POST['podlove_module_newsletter'] ) ) {
			$options = new \Podlove\Modules\Newsletter\Newsletter();
			$options->data = $_POST['podlove_module_newsletter'];
			$options->save();
			\Podlove\Modules\Newsletter\Newsletter::redirect( 'podlove_newsletter_settings' );
		}
	}

	public function page() {

			?>
			<form method="post">
				<?php settings_fields( \Podlove\Modules\Newsletter\Newsletter::$pagehook ); ?>

				<?php
				$newsletter = \Podlove\Modules\Newsletter\Newsletter::get_instance();

				$form_attributes = array(
					'context'    => 'podlove_module_newsletter',
					'form'       => false
				);

				\Podlove\Form\build_for( $newsletter, $form_attributes, function ( $form ) {
					$wrapper = new \Podlove\Form\Input\TableWrapper( $form );
					$newsletter = $form->object;

					$wrapper->subheader(
						__( 'Description', 'podlove' ),
						__( 'The Newsletter module allows your listeners to subscribe to a Newsletter which announces new Episodes.
							 The process of subscription and the way the send E-mails will look, can be directly influenced
							 by using the templates below. For modification a list of all tags is listed in Podlove documentation.
							 ', 'podlove' )
					);

					$wrapper->subheader(
						__( 'General', 'podlove' ),
						__( '', 'podlove' )
					);

					$wrapper->string( 'email', array(
						'label'       => __( 'E-mail', 'podlove' ),
						'description' => __( 'This will be the sender E-mail address. Make shure the address has always the format of something@yourdomain.com', 'podlove' ),
						'html' => array( 'class' => 'regular-text' )
					) );

					$wrapper->subheader(
						__( 'Announcement', 'podlove' ),
						__( '', 'podlove' )
					);

					$wrapper->string( 'announcementsubject', array(
						'label'       => __( 'Subject (Announcement)', 'podlove' ),
						'description' => __( 'This Subject will be used if new a episode is published.', 'podlove' ),
						'html' => array( 'class' => 'regular-text' )
					) );

					$wrapper->text( 'announcementtext', array(
						'label'       => __( 'Content (Announcement)', 'podlove' ),
						'description' => __( 'This will be the text used if new a episode is published.', 'podlove' ),
						'html'        => array(
							'cols' => '50',
							'rows' => '4',
							'placeholder' => __( '', 'podlove' )
						)
					) );

					$wrapper->subheader(
						__( 'Subscription &amp; Verification', 'podlove' ),
						__( '', 'podlove' )
					);

					$wrapper->string( 'subscriptionsubject', array(
						'label'       => __( 'Subject (Subscription)', 'podlove' ),
						'description' => __( 'This will be the subject used if someones subscribes to the newsletter.', 'podlove' ),
						'html' => array( 'class' => 'regular-text' )
					) );

					$wrapper->text( 'subscriptiontext', array(
						'label'       => __( 'Content (Subscription)', 'podlove' ),
						'description' => __( 'This will be the text used if someones subscribes to the newsletter. <em>Make shure that you allways incldue the verification link!</em>', 'podlove' ),
						'html'        => array(
							'cols' => '50',
							'rows' => '4',
							'placeholder' => __( '', 'podlove' )
						)
					) );

					$wrapper->string( 'verificationsubject', array(
						'label'       => __( 'Subject (Verification)', 'podlove' ),
						'description' => __( 'This will be the subject used for the verification E-mail.', 'podlove' ),
						'html' => array( 'class' => 'regular-text' )
					) );

					$wrapper->text( 'verificationtext', array(
						'label'       => __( 'Content (Verification)', 'podlove' ),
						'description' => __( 'This will be the content used for the verification E-mail.', 'podlove' ),
						'html'        => array(
							'cols' => '50',
							'rows' => '4',
							'placeholder' => __( '', 'podlove' )
						)
					) );

					$wrapper->string( 'unsubscribesubject', array(
						'label'       => __( 'Subject (Unsubscribe)', 'podlove' ),
						'description' => __( 'This will be the subject used if someones unsubscribes from the newsletter.', 'podlove' ),
						'html' => array( 'class' => 'regular-text' )
					) );

					$wrapper->text( 'unsubscribetext', array(
						'label'       => __( 'Content (Unsubscribe)', 'podlove' ),
						'description' => __( 'This will be the text used if someones unsubscribes from the newsletter.', 'podlove' ),
						'html'        => array(
							'cols' => '50',
							'rows' => '4',
							'placeholder' => __( '', 'podlove' )
						)
					) );

				});
				?>
			</form>
			<script type="text/javascript">

			var subject_field_height = 30;
			var text_field_height = 200;

			// Apply CodeMirror on all template fields
			apply_CodeMirror( 'podlove_module_newsletter_announcementsubject', 'episode', 0, subject_field_height );
			apply_CodeMirror( 'podlove_module_newsletter_announcementtext', 'episode', 1, text_field_height );
			apply_CodeMirror( 'podlove_module_newsletter_subscriptionsubject', '', 0, subject_field_height  );
			apply_CodeMirror( 'podlove_module_newsletter_subscriptiontext', '', 1, text_field_height  );
			apply_CodeMirror( 'podlove_module_newsletter_verificationsubject', '', 0, subject_field_height  );
			apply_CodeMirror( 'podlove_module_newsletter_verificationtext', '', 1, text_field_height  );
			apply_CodeMirror( 'podlove_module_newsletter_unsubscribesubject', '', 0, subject_field_height  );
			apply_CodeMirror( 'podlove_module_newsletter_unsubscribetext', '', 1, text_field_height  );

			var podcast_tags = [	"{podcastTitle}",
									"{podcastSubtitle}",
									"{podcastSummary}",
									"{podcastCover}"
					  		   ];

			var episode_tags = [	"{linkedEpisodeTitle}",
									"{episodeTitle}",
									"{episodeSubtitle}",
									"{episodeCover}",
									"{episodeLink}",
									"{episodeSummary}",
									"{episodeDuration}",
									"{unsubscribeLink}"
					  		   ];

			var action_tags = [ "{actionLink}" ];

			function apply_CodeMirror(id, relation, linenumber, height) {

				var podlove_template_content = document.getElementById(id);
				var podlove_template_editor = CodeMirror.fromTextArea(podlove_template_content, {
					mode: "htmlmixed",
					lineNumbers: linenumber,
					theme: "default",
					indentUnit: 4,
					lineWrapping: true,
					extraKeys: {
						"'>'": function(cm) { cm.closeTag(cm, '>'); },
						"'/'": function(cm) { cm.closeTag(cm, '/'); },
						"'{'": function(cm) {
							CodeMirror.simpleHint(cm, function(cm) {
								return {
									list: relation == 'episode' ? podcast_tags.concat(episode_tags) : podcast_tags.concat(action_tags), // Either action link or Episode will be added
									from: cm.getCursor()
								};
							});
						}
					},
					onCursorActivity: function() {
						podlove_template_editor.matchHighlight("CodeMirror-matchhighlight");
					}
				});		

				podlove_template_editor.setSize(null, height);
			}
			</script>
			<style type="text/css">
			span.CodeMirror-matchhighlight {
				background: #e9e9e9;
			}
			.CodeMirror-focused span.CodeMirror-matchhighlight {
				background: #e7e4ff; !important
			}
			.CodeMirror-scroll {
				border: 1px solid #CCC;
			}
			</style>
			<?php
	}

}