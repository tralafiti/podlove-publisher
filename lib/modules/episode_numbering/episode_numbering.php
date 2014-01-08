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

        add_action( 'save_post', array( $this, 'save_post_meta' ) );

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

    public function save_post_meta( $post_id )
    {
        update_post_meta( $post_id, '_podlove_meta_podlove_episode_season', $_POST['_podlove_meta']['_podlove_episode_season'] );
        update_post_meta( $post_id, '_podlove_meta_podlove_episode_number', $_POST['_podlove_meta']['_podlove_episode_number'] );
        update_post_meta( $post_id, '_podlove_meta_podlove_episode_global_number', $_POST['_podlove_meta']['_podlove_episode_global_number'] );
    }

    public function was_activated( $module_name )
    {
    	Season::build();

        // Builld Season 1
        $season1 = new \Podlove\Modules\EpisodeNumbering\Model\Season;
        $season1->number = '1';
        $season1->save();
    }

    public function episode_form_extension( $wrapper )
    {
    	$wrapper->callback( '_podlove_episode_season', array(
    		'label'    => __( 'Numbering', 'podlove' ),
    		'description' => __( 'Used to call episodes by their global episode number.' ),
            'callback'  => function() {

                $seasons = array_reverse( \Podlove\Modules\EpisodeNumbering\Model\Season::all() );
                $episodes = \Podlove\Model\Episode::all();

                $post_id = get_the_ID();

                // Unset all Episodes that are drafts
                foreach ( $episodes as $episode_key => $episode ) {
                    $episode_status = get_post( $episode->post_id )->post_status;
                    if ( $episode_status == 'auto-draft' || 
                         $episode_status == 'inherit' ||
                         $episode_status == 'trash' )
                        unset( $episodes[$episode_key] );
                }

                // The global episode number is the number of episodes + 1!
                $number_of_episodes = count( $episodes ) + 1; 

                $season_id = get_post_meta( $post_id, '_podlove_meta_podlove_episode_season', true );
                $episode_number = get_post_meta( $post_id, '_podlove_meta_podlove_episode_number', true );
                $global_episode_number = get_post_meta( $post_id, '_podlove_meta_podlove_episode_global_number', true );

                ?>
                    <style type="text/css">
                        .podlove-episode-numbering-item {
                            width: 30% !important;
                            display: inline-block;
                            margin-right: 2%;
                        }
                    </style>

                    <script type="text/javascript">

                    </script>

                    <div class="podlove-episode-numbering-item">
                        <label for="_podlove_meta_podlove_episode_season">Season</label>
                        <select name="_podlove_meta[_podlove_episode_season]" id="_podlove_meta_podlove_episode_season" class="chosen" >
                            <?php
                                foreach ( $seasons as $season_key => $season ) {
                                   printf( '<option value="%s"%s>%s</option>',
                                            $season->id,
                                            $season_id == $season->id ? ' selected' : '',
                                            $season->number ); 
                                }
                            ?>
                        </select>
                    </div>

                    <div class="podlove-episode-numbering-item">
                        <label for="_podlove_meta_podlove_episode_number">Episode Number</label>
                        <input type="text" name="_podlove_meta[_podlove_episode_number]" id="_podlove_meta_podlove_episode_number" value="<?php echo $episode_number; ?>" >
                    </div>

                    <div class="podlove-episode-numbering-item">
                        <label for="_podlove_meta_podlove_episode_global_number">Global Episode Number</label>
                        <input type="text" name="_podlove_meta[_podlove_episode_global_number]" id="_podlove_meta_podlove_episode_global_number" value="<?php echo ( $global_episode_number == '' ? $number_of_episodes : $global_episode_number ); ?>" >
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
}