<?php 
namespace Podlove\Modules\EpisodeNumbering;
use \Podlove\Model;
use \Podlove\Http;

use \Podlove\Modules\EpisodeNumbering\Model\Season;

class Episode_Numbering extends \Podlove\Modules\Base {

    protected $module_name = 'Episode numbering';
    protected $module_description = 'Enable episode numbering and seasons.';
    protected $module_group = 'metadata';
	
    public function load()
    {
    	add_action( 'podlove_module_was_activated_episode_numbering', array( $this, 'was_activated' ) );

    	add_action( 'podlove_podcast_form', array( $this, 'podcast_form_extension' ), 10, 2 );
    	add_action( 'podlove_episode_form', array( $this, 'episode_form_extension' ), 10, 2 );

    	// register settings page
    	add_action( 'podlove_register_settings_pages', function( $settings_parent ) {
    		new Settings\Seasons( $settings_parent );
    	});

    	// Add mnemonic property to podcast model
    	$podcast = \Podlove\Model\Podcast::get_instance();
    	$podcast->property( 'mnemonic' );
    }

    public function was_activated( $module_name )
    {
    	Season::build();
    }

    public function episode_form_extension( $wrapper )
    {
    	$wrapper->callback( 'podlove_episode_numbering', array(
    		'label'    => __( 'Numbering', 'podlove' ),
    		'callback' => function() {
    			echo "fuu";
    		}
    	) );
    }

    public function podcast_form_extension( $wrapper, $podcast )
	{
    	$wrapper->string( 'mnemonic', array(
			'label'    => __( 'Mnemonic', 'podlove' ),
			'description' => __( 'Used to call episodes by their global episode number.' )
		) );
	}
}