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

		// Fetch unsubscribe hash
		add_action( 'wp', array( $this, 'unsubscribe' ) );

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

	public static function subscribe() {
		$message = '';

		if( isset( $_POST['podlove-newsletter-subscription-email'] ) ) {  // Without $_POST fields, there is no activity

			if( empty( $_POST['podlove-newsletter-subscription-email'] ) )
				return "Please fill in an E-mail adress!"; // Without an E-mail adress we cannot continue

			if( strpos( $_POST['podlove-newsletter-subscription-email'], '@' ) === FALSE || 
				strpos( $_POST['podlove-newsletter-subscription-email'], '.' ) === FALSE )
				return "Please fill in a valid E-mail adress!"; // As it is an email there should be at last one @ and on .

			$verification_hash = uniqid();
			$user_ip = $_SERVER['REMOTE_ADDR'];
			$user_email = $_POST['podlove-newsletter-subscription-email'];
			$current_time = current_time( 'mysql' );
			$current_address = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

			// Check for multiple subscriptions with one adress
			$email_in_verification_process = NewsletterVerification::find_one_by_property('email', $user_email);
			if ( is_object( $email_in_verification_process ) ) {
				$current_time_object = new \DateTime( $current_time );
				$last_subscription = new \DateTime( $email_in_verification_process->subscription_date );
				$subscription_interval = date_diff( $last_subscription, $current_time_object );

				if( $subscription_interval->format('%h') < '1' && $subscription_interval->format('%i') < '30' ) // Time limit for new subscription is 30min
					return "You already subscribed to the Newsletter, but you still need to verify your E-mail adress. If you lost this E-mail please try subscription again 
							in 30min.";
			}

			// Check for SPAM!
			$ip_in_verification_process = NewsletterVerification::find_all_by_property('IP', $user_ip);
			// After the current IP is used 10 times, we stop accepting new subscriptions from that IP adress for 1 day if the E-mail adresses are not verified.
			if( is_array( $ip_in_verification_process ) && count( $ip_in_verification_process ) >= 100 )
				return "You peformed subscription at least 100 times. Please verify the entered E-mail adresses first or wait 1 day until you add further addresses to the newsletter."; 

			// Check if already subscribed
			$check_for_subscription = Subscription::find_one_by_property('email', $user_email);
			if( is_object( $check_for_subscription ) )
				return "You already subscribed to the newsletter!";

			// Add a new subscription to the database. This still needs to be verified within 48h
			$subscription = new NewsletterVerification;
			$subscription->email = $user_email;
			$subscription->IP = $user_ip;
			$subscription->verification_hash = $verification_hash;
			$subscription->subscription_date = $current_time;
			$subscription->save();

			// Prepare the verification E-mail
			$verification_link = "http://" . $current_address . ( strpos($current_address, '?') ? "&amp;" : "?" ) ."podlove-newsletter-verification=" . $verification_hash;
			$to = $_POST['podlove-newsletter-subscription-email'];
			$subject = get_bloginfo('name') . " Newsletter: Please verify your subscription";
			$text = "Hi there,<p>
					 please verify your subsription to the " . get_bloginfo('name') . " Newsletter 
					 by following that link: <a href='" . $verification_link . "' target='_blank'>
					 " . $verification_link . "</a>.</p>
					 If you did not subscribe to that newsletter please ask " . $_SERVER['REMOTE_ADDR'] 
					 . " why that was done for you.";

			$email = new self;
			$email->send_newsletter( $to, $subject, $text );
		}

		return $message;
	}

	public static function verification() {
		if( isset( $_GET['podlove-newsletter-verification'] ) && !empty( $_GET['podlove-newsletter-verification'] ) ) {

			$blog_address = get_bloginfo('url');
			$verification_hash = $_GET['podlove-newsletter-verification'];
			
			// Checking if hash can be used or was already used
			$subscription = NewsletterVerification::find_one_by_property('verification_hash', $verification_hash);
			if( !is_object( $subscription ) )
				return 'Verification cannot be completed, as verification hash you are using was already used or is not valid anymore.';

			$unsubscribe_hash = uniqid();

			$verified_subscription = new Subscription;
			$verified_subscription->email = $subscription->email;
			$verified_subscription->subscription_date = $subscription->subscription_date;
			$verified_subscription->unsubscribe_hash = $unsubscribe_hash;
			$verified_subscription->save();

			$subscription->delete();

			$unsubscribe_link = $blog_address . ( strpos($blog_address, '?') ? "&amp;" : "?" ) ."podlove-newsletter-unsubscribe=" . $unsubscribe_hash;
			$to = $subscription->email;
			$subject = get_bloginfo('name') . " Newsletter: Your subscription";
			$text = "Hi there,<p>
					 You just subscribed to the " . get_bloginfo('name') . " Newsletter.
					 </p>If you want to unsubscribe follow this link: <a href'" . $unsubscribe_link . "' target='_blank'>" . $unsubscribe_link . "</a>";

			$email = new self;
			$email->send_newsletter( $to, $subject, $text );

			return "Success! You are now subscribed to the " . get_bloginfo('name') . " Newsletter.";

		}
	}

	public function unsubscribe() {
		if( isset( $_GET['podlove-newsletter-unsubscribe'] ) && !empty( $_GET['podlove-newsletter-unsubscribe'] ) ) {
			$subscription = Subscription::find_one_by_property('unsubscribe_hash', $_GET['podlove-newsletter-unsubscribe']);
			if( is_object( $subscription ) ) {
				$to = $subscription->email;
				$subject = get_bloginfo('name') . " Newsletter: Your subscription";
				$text = "Hi there,<p>
						 You just unsubscribed from the " . get_bloginfo('name') . " Newsletter. You will no longer receive Newsletter messages from the " . get_bloginfo('name') . " Podcast.";

				$email = new self;
				$email->send_newsletter( $to, $subject, $text );

				$subscription->delete();
			}
		}
	}

	public function was_activated( $module_name ) {
		Subscription::build();
		NewsletterVerification::build();
	}

	public function send_newsletter( $to, $subject, $text ) {
		// Set HTML Content Type
		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ) );
		// Set newsletter@url / Podcastname as sender
		add_filter( 'wp_mail_from', array( $this, 'set_email_adress' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'set_email_name' ) );

		wp_mail( $to, $subject, $text );
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