<?php
namespace Podlove\Modules\Newsletter\Settings;

use Podlove\Model;
use \Podlove\Settings\Expert\Tab;
use \Podlove\Settings\Expert\Tabs;

class NewsletterSetting {

	static $pagehook;
	
	public function __construct( $handle ) {
		
		NewsletterSetting::$pagehook = add_submenu_page(
			/* $parent_slug*/ $handle,
			/* $page_title */ 'Newsletter Settings',
			/* $menu_title */ 'Newsletter Settings',
			/* $capability */ 'administrator',
			/* $menu_slug  */ 'podlove_newsletter_settings',
			/* $function   */ array( $this, 'page' )
		);

		$tabs = new Tabs( __( 'Newsletter', 'podlove' ) );
		$tabs->addTab( new \Podlove\Modules\Newsletter\Settings\Tab\NewsletterSettings( __( 'Settings', 'podlove' ), true ) );
		$tabs->addTab( new \Podlove\Modules\Newsletter\Settings\Tab\Subscriptions( __( 'Subscriptions', 'podlove' ) ) );
		$tabs->addTab( new \Podlove\Modules\Newsletter\Settings\Tab\Verifications( __( 'Verifications', 'podlove' ) ) );
		$this->tabs = $tabs;
		$this->tabs->initCurrentTab();

		foreach ($this->tabs->getTabs() as $tab) {
			add_action( 'admin_init', array( $tab->getObject(), 'process_form' ) );
		}
	}
	
	function page() {
		?>
		<div class="wrap">
			<?php
			screen_icon( 'podlove-podcast' );
			echo $this->tabs->getTabsHTML();
			echo $this->tabs->getCurrentTabPage();
			?>
		</div>	
		<?php
	}
	

}