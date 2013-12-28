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
						__( 'General', 'podlove' ),
						__( '', 'podlove' )
					);

					$wrapper->string( 'email', array(
						'label'       => __( 'E-mail', 'podlove' ),
						'description' => __( 'This E-mail address will appear.', 'podlove' ),
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
			<?php
	}

}