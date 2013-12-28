<?php 
namespace Podlove\Modules\Newsletter;

use Podlove\Settings;

use Podlove\Modules\Newsletter\Shortcodes;

use Podlove\Modules\Newsletter\Model\Subscription;
use Podlove\Modules\Newsletter\Model\NewsletterVerification;

use Podlove\Modules\Newsletter\Settings\NewsletterSettings;

use Podlove\DomDocumentFragment;

class Newsletter extends \Podlove\Modules\Base {

	protected $module_name = 'Newsletter';
	protected $module_description = 'Allows users to subscribe to an E-mail Newsletter.';
	protected $module_group = 'web publishing';

	public function load() {

		// Activation hooks
		add_action( 'podlove_module_was_activated_newsletter', array( $this, 'was_activated' ) );

		// Send Email on Published Episode
		add_action( 'publish_podcast', array( $this, 'send_newsletter' ) );

		// register settings page
		add_action( 'podlove_register_settings_pages', function( $settings_parent ) {
			new \Podlove\Modules\Newsletter\Settings\NewsletterSettings( $settings_parent );
		});

		// Register Rewrite for subscription
		add_action( 'wp', array( $this, 'fetch_verification' ) );  
		add_action( 'wp', array( $this, 'subscribe' ) );  

		// Add Shortcodes
		new Shortcodes;

		// Register Options
		$this->register_option( 'newsletter_template_title', 'string', array(
			'label'       => __( 'Subject', 'podlove' ),
			'description' => 'The subject of the Newsletter E-mail.',
			'html'        => array( 'class' => 'regular-text' )
		) );

		$this->register_option( 'newsletter_template_text', 'text', array(
			'label'       => __( 'Text', 'podlove' ),
			'description' => 'The text of the Newsletter E-mail.',
			'html'        => array(
				'cols' => '50',
				'rows' => '4',
				'class' => 'autogrow'
			)
		) );
		
	}

	public function subscribe() {
		$message = '';

		if( isset( $_POST['podlove-newsletter-subscription-name'] ) &&
			isset( $_POST['podlove-newsletter-subscription-email'] ) ) {

			$subscription = new NewsletterVerification;
			$subscription->name = $_POST['podlove-newsletter-subscription-name'];
			$subscription->email = $_POST['podlove-newsletter-subscription-email'];
			$subscription->IP = $_SERVER['REMOTE_ADDR'];
			$subscription->verification_hash = uniqid();
			$subscription->subscription_date = current_time( 'mysql' );

			$subscription->save();

		}	

		return $message;
	}

	public function fetch_verification() {
		if( isset( $_GET['podlove-newsletter-verification'] ) && !empty( $_GET['podlove-newsletter-verification'] ) ) {
			
		}
	}

	public function page() {

	}

	public function was_activated( $module_name ) {
		Subscription::build();
		NewsletterVerification::build();
	}

	public function send_newsletter() {
		// Set HTML Content Type
		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		// Set newsletter@url / Podcastname as sender
		add_filter( 'wp_mail_from', array( $this, 'set_email_adress' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'set_email_name' ) );

		//wp_mail( PLACE ARRAY HERE, 'A new subject', 'This is an test email!' );
	}

	public function set_email_content_type( $original_email_contentype ) {
		return 'text/html';
	}

	public function set_email_adress( $original_email_adress ) {
		return 'newsletter@' . $_SERVER['HTTP_HOST'];
	}

	public function set_email_name( $original_email_name ) {
		return get_bloginfo('name');
	}

}

?>