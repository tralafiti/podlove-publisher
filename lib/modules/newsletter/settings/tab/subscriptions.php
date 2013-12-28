<?php 
namespace Podlove\Modules\Newsletter\Settings\Tab;
use \Podlove\Settings\Settings;
use \Podlove\Settings\Expert\Tab;

class Subscriptions extends Tab {

	public function init() {
		$this->page_type = 'custom';
		add_action( 'podlove_expert_settings_page', array( $this, 'register_page' ) );
	}

	public function register_page() {
		$this->object = $this->getObject();
		$this->object->page();
	}

	public function getObject() {
		return new \Podlove\Modules\Newsletter\Settings\Subscriptions( 'podlove_newsletter_settings' );
	}


}