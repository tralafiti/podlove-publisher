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
        add_action( 'admin_print_styles', array( $this, 'scripts_and_styles' ) );

        add_action( 'save_post', array( $this, 'save_post_meta' ) );

        // Register Form Extensions
    	add_action( 'podlove_podcast_form', array( $this, 'podcast_form_extension' ), 10, 2 );
    	add_action( 'podlove_episode_form', array( $this, 'episode_form_extension' ), 10, 2 );

    	// Register settings page
    	add_action( 'podlove_register_settings_pages', function( $settings_parent ) {
    		new Settings\Seasons( $settings_parent );
    	});

    	// Add mnemonic property to podcast model
    	$podcast = \Podlove\Model\Podcast::get_instance();
    	$podcast->property( 'mnemonic' );
    }

    public function save_post_meta( $post_id )
    {
        if( isset( $_POST['_podlove_meta'] ) )
        {
            update_post_meta( $post_id, '_podlove_meta_podlove_episode_season', $_POST['_podlove_meta']['_podlove_episode_season'] );
            update_post_meta( $post_id, '_podlove_meta_podlove_episode_number', $_POST['_podlove_meta']['_podlove_episode_number'] );
            update_post_meta( $post_id, '_podlove_meta_podlove_episode_global_number', $_POST['_podlove_meta']['_podlove_episode_global_number'] );
        }
    }

    public function was_activated( $module_name )
    {
    	Season::build();

        // Add Season 1. This season will always exist and cannot be deleted.
        $season1 = new \Podlove\Modules\EpisodeNumbering\Model\Season;
        $season1->number = '1';
        $season1->save();
    }

    public function episode_form_extension( $wrapper )
    {
    	$wrapper->callback( '_podlove_episode_season', array(
    		'label'    => __( 'Indexing', 'podlove' ),
    		'description' => __( 'Used to call episodes by their global episode number.' ),
            'callback'  => function() {

                $seasons = array_reverse( \Podlove\Modules\EpisodeNumbering\Model\Season::all() );
                $episodes = \Podlove\Model\Episode::all();
                $podcast = \Podlove\Model\Podcast::get_instance();

                $post_id = get_the_ID();

                $season_id = get_post_meta( $post_id, '_podlove_meta_podlove_episode_season', true );
                $episode_number = get_post_meta( $post_id, '_podlove_meta_podlove_episode_number', true );
                $global_episode_number = get_post_meta( $post_id, '_podlove_meta_podlove_episode_global_number', true );

                $current_season_id = $seasons[0]->id;
                $current_season_episode_counter = 0;

                // Unset all Episodes that are drafts & fetch Episode Number
                foreach ( $episodes as $episode_key => $episode ) {
                    $episode_status = get_post( $episode->post_id )->post_status;
                    if ( $episode_status == 'auto-draft' || 
                         $episode_status == 'inherit' ||
                         $episode_status == 'trash' )
                    {
                        unset( $episodes[$episode_key] );
                    } else 
                    {
                        if ( $current_season_id == get_post_meta( $episode->post_id, '_podlove_meta_podlove_episode_season', true ) )
                            $current_season_episode_counter++;
                    }
                }

                // The global episode number is the number of episodes + 1!
                $number_of_episodes = count( $episodes ) + 1; 

                ?>
                    <script type="text/javascript">
                        var podcast_mnemonic = "<?php echo $podcast->mnemonic; ?>";
                    </script>

                    <div class="podlove-episode-numbering-item">
                        <label for="_podlove_meta_podlove_episode_season episode_season">Season</label>
                        <select name="_podlove_meta[_podlove_episode_season]" id="_podlove_meta_podlove_episode_season" class="chosen" >
                            <?php
                                foreach ( $seasons as $season_key => $season ) {
                                   printf( '<option value="%s"%s data-mnemonic="%s" data-number="%s">%s</option>',
                                            $season->id,
                                            $season_id == $season->id ? ' selected' : '',
                                            $season->mnemonic,
                                            $season->number,
                                            $season->number ); 
                                }
                            ?>
                        </select>
                    </div>

                    <div class="podlove-episode-numbering-item episode_number">
                        <label for="_podlove_meta_podlove_episode_number">Episode Number</label>
                        <input type="number" min="1" step="1" pattern="\d+" name="_podlove_meta[_podlove_episode_number]" id="_podlove_meta_podlove_episode_number" value="<?php echo ( $episode_number == '' ? $current_season_episode_counter + 1 : $episode_number ); ?>" >
                    </div>

                    <div class="podlove-episode-numbering-item episode_global_number">
                        <label for="_podlove_meta_podlove_episode_global_number">Global Episode Number</label>
                        <input type="number" min="1" step="1" pattern="\d+" name="_podlove_meta[_podlove_episode_global_number]" id="_podlove_meta_podlove_episode_global_number" value="<?php echo ( $global_episode_number == '' ? $number_of_episodes : $global_episode_number ); ?>" >
                    </div>

                    <div id="podlove-episode-numbering-preview">
                        This Episode will be indexed as:
                        <span class="podlove-global-number"></span>
                        <span class="podlove-season-number"></span>
                        <span class="podlove-season-mnemonic"></span>
                    </div>
                <?php
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

    public function scripts_and_styles() {
        wp_register_script( 'podlove-episode-numbering-admin-script', \Podlove\PLUGIN_URL . '/lib/modules/episode_numbering/js/admin.js', array( 'jquery-ui-autocomplete' ) );
        wp_enqueue_script( 'podlove-episode-numbering-admin-script' );

        wp_register_style( 'podlove-episode-numbering-admin-style',  \Podlove\PLUGIN_URL . '/lib/modules/episode_numbering/css/admin.css', false, \Podlove\get_plugin_header( 'Version' ) );
        wp_enqueue_style('podlove-episode-numbering-admin-style');
    }

}