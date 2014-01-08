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
                    <style type="text/css">
                        .podlove-episode-numbering-item {
                            display: inline-block;
                            margin-right: 2%;
                            line-height: 26px;
                        }
                        .podlove-global-number, .podlove-season-number, .podlove-season-mnemonic {
                            font-family: Consolas;
                            background-color: #E7E7E7;
                            margin: 0px 2px 0px 2px;
                        }
                        #podlove-episode-numbering-preview {
                            margin-top: 15px;
                        }
                        .podlove-episode-numbering-item input, .podlove-episode-numbering-item select {
                            width: 50px;
                            display: inline-block;
                            height: 26px;
                        }
                        .podlove-episode-numbering-item label {
                            display: inline-block;
                            margin-right: 10px;
                        }
                        .podlove-episode-numbering-item.episode_number {
                            width: 170px !important;
                        }
                        .podlove-episode-numbering-item.episode_season {
                            width: 150px !important;
                        }
                        .podlove-episode-numbering-item.episode_global_number {
                            width: 250px !important;
                        }
                    </style>

                    <script type="text/javascript">
                        var PODLOVE = PODLOVE || {};
                        var podcast_mnemonic = "<?php echo $podcast->mnemonic; ?>";
                        var season_mnemonic = "";

                        PODLOVE.fill_string_left = function( number, length, fill ) {
                            number = number.toString();
                            
                            if ( !fill ) { fill = '0'; } // found @ http://stv.whtly.com/2009/02/27/simple-jquery-string-padding-function/
                            
                            while ( number.length < length ) {
                                number = fill + number;
                            }

                            return number;
                        }
                        
                        PODLOVE.episode_numbering_preview = function() {
                            (function($) {

                                $("select#_podlove_meta_podlove_episode_season option:selected").each(function() {
                                    season_mnemonic = $(this).data('mnemonic');
                                });

                                var episode_global_number = $("#_podlove_meta_podlove_episode_global_number").val();
                                var episode_number = $("#_podlove_meta_podlove_episode_number").val();
                                var episode_season = $("#_podlove_meta_podlove_episode_season").val();

                                if( episode_global_number !== '' ) {
                                    $(".podlove-global-number").html( podcast_mnemonic + PODLOVE.fill_string_left( episode_global_number, 3 ) );
                                } else {
                                    $(".podlove-global-number").html('');
                                }

                                if( episode_number !== '' ) {
                                    $(".podlove-season-number").html( "S" + PODLOVE.fill_string_left( episode_season, 2 ) + "E" + PODLOVE.fill_string_left( episode_number, 2 ) );
                                } else {
                                     $(".podlove-season-number").html('');
                                }

                                if( episode_number !== '' ) {
                                    $(".podlove-season-mnemonic").html( season_mnemonic + PODLOVE.fill_string_left( episode_number, 2 ) );
                                 } else {
                                    $(".podlove-season-mnemonic").html('');
                                }

                            }(jQuery));
                        }

                        jQuery(document).ready(function() {
                            PODLOVE.episode_numbering_preview();

                            jQuery("#_podlove_meta_podlove_episode_season").change(function() {
                                 PODLOVE.episode_numbering_preview();
                            });

                            jQuery("#_podlove_meta_podlove_episode_number").change(function() {
                                 PODLOVE.episode_numbering_preview();
                            });

                            jQuery("#_podlove_meta_podlove_episode_global_number").change(function() {
                                 PODLOVE.episode_numbering_preview();
                            });
                        });

                    </script>

                    <div class="podlove-episode-numbering-item">
                        <label for="_podlove_meta_podlove_episode_season episode_season">Season</label>
                        <select name="_podlove_meta[_podlove_episode_season]" id="_podlove_meta_podlove_episode_season" class="chosen" >
                            <?php
                                foreach ( $seasons as $season_key => $season ) {
                                   printf( '<option value="%s"%s data-mnemonic="%s">%s</option>',
                                            $season->id,
                                            $season_id == $season->id ? ' selected' : '',
                                            $season->mnemonic,
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
                        This Episode will be numbered as:
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
}