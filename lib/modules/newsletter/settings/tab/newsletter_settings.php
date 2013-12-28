<?php 
namespace Podlove\Modules\Newsletter\Settings\Tab;
use \Podlove\Settings\Settings;
use \Podlove\Settings\Expert\Tab;

class NewsletterSettings extends Tab {

	public function init() {
		$this->page_type = 'custom';
		add_action( 'podlove_expert_settings_page', array( $this, 'register_page' ) );
	}

	public function register_page() {
		$this->object = $this->getObject();
		if( $_GET['page'] == 'podlove_newsletter_settings' )
				$this->object->page();		
	}

	public function getObject() {
		return new \Podlove\Modules\Newsletter\Settings\Settings( 'podlove_newsletter_settings' );
	}

}