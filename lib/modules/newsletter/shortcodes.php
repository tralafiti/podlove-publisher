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
		$message = Newsletter::subscribe();

		$form .= "	<form method=\"post\">
						<input type=\"text\" id=\"podlove-newsletter-subscription-email\" name=\"podlove-newsletter-subscription-email\" />
						<input type=\"submit\" value=\"Subscribe\" />
					</form>
		";
		echo "<em style='color: red'>" . $message . "</em>";

		return $form;
	}

}

?>