<?php
namespace Podlove\Modules\Newsletter\Settings;

use Podlove\Model;

class NewsletterSettings {

	static $pagehook;
	
	public function __construct( $handle ) {
		
		NewsletterSettings::$pagehook = add_submenu_page(
			/* $parent_slug*/ $handle,
			/* $page_title */ 'Newsletter Settings',
			/* $menu_title */ 'Newsletter Settings',
			/* $capability */ 'administrator',
			/* $menu_slug  */ 'podlove_newsletter_settings',
			/* $function   */ array( $this, 'page' )
		);

	}
	
	function page() {
		?>
		<div class="wrap">
			<h2>
				Newsletter
				<a href="?page=<?php echo $_REQUEST['page']; ?>&amp;podlove_tab=roles&amp;action=new" class="add-new-h2"><?php echo __( 'Add New', 'podlove' ); ?></a>
			</h2>
			<?php
			echo __('Use roles to assign a certain type of activity to a single contributor independent of any assigned group. A role might be helpful to mark somebody as being the main presenter of a show or a guest. Use roles sparingly as most of the times, groups might the more valuable way to structure contributors.', 'podlove');
			$table = new \Podlove\Modules\Newsletter\Subscription_List_Table();
			$table->prepare_items();
			$table->display();
			?>
		</div>
		<?php
	}
	
}