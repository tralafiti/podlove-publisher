<?php 
namespace Podlove\Modules\Newsletter;

use \Podlove\Model;

/**
 * Register all newsletter shortcodes.
 */
class Shortcodes {

	public function __construct() {
		// Display Subscription Form
		add_shortcode( 'podlove-newsletter-form', array( $this, 'newsletter_form') );
	}

	public function newsletter_form() {
		$form = '';
		$subscription_message = Newsletter::subscribe();
		$verification_message = Newsletter::verification();

		$form .= "	<form method=\"post\">
						<input type=\"text\" id=\"podlove-newsletter-subscription-email\" name=\"podlove-newsletter-subscription-email\" />
						<input type=\"submit\" value=\"Subscribe\" />
					</form>
		";

		if( !empty( $subscription_message ) || !empty( $verification_message ) )
			echo "<script type='text/javascript'> 
					jQuery( document ).ready( function() { 
						PODLOVE.dialog_box(" . $subscription_message . $verification_message .");
					});
				</script>";

		return $form;
	}

}

?>