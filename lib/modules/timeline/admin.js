var PODLOVE = PODLOVE || {};

(function($) {
	var form_base_name = "<?php echo $form_base_name ?>";
	var i = 0;

	$(document).ready(function() {

		$(".chapters-form-button").on( 'click', function(e) {
			$(".chapters-form-button").addClass("inactive-box");
			$(this).removeClass("inactive-box");
			$(".chapters-form-container").addClass("hidden");
			$( '#' + $(this).data('container') ).removeClass("hidden");
		} );

		$("#chapters-form table").podloveDataTable({
			rowTemplate: "#chapter-row-template",
			data: PODLOVE.Chapters,
			sortableHandle: ".reorder-handle",
			addRowHandle: "#add_new_chapters_wrapper",
			deleteHandle: ".chapter_remove",
			onRowLoad: function(o) {
				replacements = {
					id: i,
					chapter: i+1,
					beginning: get_human_readable_time( check_for_empty_string(o.entry.start_time) ),
					title: check_for_empty_string(o.entry.title),
					url: check_for_empty_string(o.entry.url)
				};

				o.row = append_table_row(replacements, i);
				i++;
			},
			onRowAdd: function(o, init) {
				var row = $("#contributors_table_body tr:last");
				// Focus new chapter
				if ( ! init) {
					prev_counter = i - 1;
					$("#_podlove_meta_timeline_chapters_" + prev_counter + "_beginning").focus();
				}
			},
			onRowDelete: function(tr) {
				sort_chapters_by_position();
			},
			onRowMove: function () {
				sort_chapters_by_position();	
			}
		});
	});

	sort_chapters_by_position = function() {
		i = 1;
		$('#chapters_table_body > tr').each( function() {
			$(this).find("td.podlove_chapters_position").text(i);
			i++;
		});
	};

	append_table_row = function (options, counter) {
		var row_template = $("#chapter-row-template").html();

		$.each(options, function (search, replace) {
			var regex = new RegExp("{{"+search+"}}","g");
			row_template = row_template.replace(regex, replace);
		});

		return row_template;						
	};

	check_for_empty_string = function (string) {
		if ( string === null )
			return "";

		if ( typeof string === 'undefined' )
			return "";

		return string;
	};

	get_human_readable_time = function (time) {
		time = time * 1000;
		ms = String(time % 1000);
		s  = String(Math.floor((time / 1000) % 60));
		m  = String(Math.floor((time / 1000 / 60 ) % 60));
		h  = String(Math.floor(time / 1000 / 60 / 60));

		while ( h.length < 2 ) {
			h = "0" + h;
		}
		while ( m.length  < 2 ) m  = "0" + m;
		while ( s.length  < 2 ) s  = "0" + s;
		while ( ms.length < 3 ) ms = "0" + ms;

		return h + ":" + m + ":" + s + "." + ms;
	};

}(jQuery));