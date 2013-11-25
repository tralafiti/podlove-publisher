<?php
namespace Podlove\Settings\Network;

class Network {

	static $pagehook;
	
	public function __construct( $handle ) {
		
		Network::$pagehook = add_submenu_page(
			/* $parent_slug*/ $handle,
			/* $page_title */ 'Network',
			/* $menu_title */ 'Network',
			/* $capability */ 'administrator',
			/* $menu_slug  */ 'podlove_settings_network_handle',
			/* $function   */ array( $this, 'page' )
		);

		if( isset( $_GET['action'] ) && $_GET['action'] == 'save' && !empty( $_POST ) ) {
			update_site_option( 'podlove_network', $_POST['podlove_network'] );
		}

	}

	function page() {
		?>
		<div class="wrap">
			<?php screen_icon( 'podlove-podcast' ); ?>
			<h2><?php echo __( 'Network Settings' ) ?></h2>

			<form method="post" action="admin.php?page=podlove_settings_network_handle&amp;action=save">
				<?php settings_fields( Network::$pagehook ); ?>

				<?php
				$network = \Podlove\Model\Network::get_instance();

				$form_attributes = array(
					'context'    => 'podlove_network',
					'form'       => false
				);

				\Podlove\Form\build_for( $network, $form_attributes, function ( $form ) {
					$wrapper = new \Podlove\Form\Input\TableWrapper( $form );
					$network = $form->object;

					$wrapper->subheader(
						__( 'Description', 'podlove' ),
						__( 'If you have configured a <a href="http://codex.wordpress.org/Create_A_Network">
							WordPress Network</a>, Podlove allows you to configure a Podcast network.', 'podlove' )
					);

					$wrapper->string( 'title', array(
						'label'       => __( 'Title', 'podlove' ),
						'html'        => array( 'class' => 'regular-text required' )
					) );

					$wrapper->string( 'subtitle', array(
						'label'       => __( 'Subtitle', 'podlove' ),
						'html' => array( 'class' => 'regular-text' )
					) );

					$wrapper->text( 'description', array(
						'label'       => __( 'Description', 'podlove' ),
						'description' => __( '', 'podlove' ),
						'html'        => array( 'rows' => 3, 'cols' => 40, 'class' => 'autogrow' )
					) );

					$wrapper->image( 'logo', array(
						'label'        => __( 'Logo', 'podlove' ),
						'description'  => __( 'JPEG or PNG.', 'podlove' ),
						'html'         => array( 'class' => 'regular-text' ),
						'image_width'  => 300,
						'image_height' => 300
					) );

					$wrapper->string( 'url', array(
						'label'       => __( 'Network URL', 'podlove' ),
						'description' => __( '', 'podlove' ),
						'html' => array( 'class' => 'regular-text' )
					) );
				});
				?>
			</form>
		</div>
		<?php
	}

}