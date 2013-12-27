<?php 
namespace Podlove\Modules\Newsletter;

use Podlove\Settings;

use Podlove\Modules\Newsletter\Model\Subscription;
use Podlove\Modules\Newsletter\Model\NewsletterVerification;

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