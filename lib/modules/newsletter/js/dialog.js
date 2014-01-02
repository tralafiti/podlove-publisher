var PODLOVE = PODLOVE || {};

(function($) {

	PODLOVE.dialog_box = function ( color, message ) {
			$( "body" ).prepend( '<div id="podlove-dialog-box" class="podlove-dialog-box ' + color +'"></div>' );
			$("#podlove-dialog-box").html(message);
			$("#podlove-dialog-box").delay( 7000 ).slideUp( 300 );
	}

}(jQuery));

