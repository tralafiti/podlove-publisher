var PODLOVE = PODLOVE || {};
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
            season_number = $(this).data('number')
        });

        var episode_global_number = $("#_podlove_meta_podlove_episode_global_number").val();
        var episode_number = $("#_podlove_meta_podlove_episode_number").val();
        var episode_season = season_number;

        if( episode_global_number !== '' && podcast_mnemonic !== '' ) {
            $(".podlove-global-number").html( podcast_mnemonic + PODLOVE.fill_string_left( episode_global_number, 3 ) );
        } else {
            $(".podlove-global-number").html('');
        }

        if( episode_number !== '' ) {
            $(".podlove-season-number").html( "S" + PODLOVE.fill_string_left( episode_season, 2 ) + "E" + PODLOVE.fill_string_left( episode_number, 2 ) );
        } else {
             $(".podlove-season-number").html('');
        }

        if( episode_number !== '' && season_mnemonic !== '' ) {
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