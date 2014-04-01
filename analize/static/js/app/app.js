function getBodyScrollTop() {
	return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
}


function get_list_of_files(list) {
	var files = {};
	for (var i in list) {
		if (list[i].file) files[list[i].file] = list[i].file;
	}

	return files;
}

$(function() {

	var top_delta = 35;
	$.getJSON('/api.php?method=func_list', function(list) {
		var html = "";
		var times = [];
		var last_top = -1;
		var left = 0;
		var top = 0;

		for (i in list) {

			top = list[i].time;
			if (last_top > 0 && top - last_top <= top_delta) {
				left = 50 - left;
			}
			else {
				left = 0;
			}

			last_top = top;

			html = html + "<div data-file='{file}' data-line='{line}' class='calle' style='top: {top}px; left: {left}%'>{file} : {name}</div>"
				.replace(/{time}/g, 		list[i].time)
				.replace(/{name}/g, 		list[i].name)
				.replace(/{top}/g, 			top)
				.replace(/{left}/g, 		left)
				.replace(/{line}/g, 		list[i].line)
				.replace(/{file}/g, 		list[i].file);
		}

		$('.component.function-list').html(html);
	});

	$('.component.function-list').on('click', '.calle', function() {
		$.getJSON(
			'/api.php',
			{method : 'get_php_file_view', full_path : $(this).data('file'), line: $(this).data('line')},
			function(html) {
				$('.component.file-source-viewer').trigger('show', [html.file]);
			}
		);
	});
	$('.component.file-source-viewer')
		.click(function() {
			$(this).trigger('hide');
		})
		.bind('hide', function() {
			$(this).hide();
			$('.component.function-list').css('left', 10);
		})
		.bind('show', function(e, html) {
			$(this).html(html);
			$(this).css({
				display : 'block',
				top 	: getBodyScrollTop() + 10
			});

			var curr_pos = $(this).find('.line.curr-line').offset().top - $(this).offset().top;
			window.scrollTo(0, getBodyScrollTop() + curr_pos);

			//$('.component.function-list').css('left', '-25%');
		});
});
