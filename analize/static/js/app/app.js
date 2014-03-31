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

	var min_height = 15;
	$.getJSON('/api.php?method=func_list', function(list) {
		var html = "";
		var times = [];
		var last_time = 0;

		var min_time = 999999999999;
		for (var i in list) {
			if (i > 0 && list[i].time - last_time < min_time) {
				min_time = list[i].time - last_time;
			}

			if (i > 0) {
				list[i-1].ex_time = list[i].time - last_time;
			}

			last_time = list[i].time;
		}

		for (i in list) {
			if (!list[i].ex_time) {
				list[i].ex_time = min_time;
			}

			html = html + "<div data-file='{file}' data-line='{line}' class='calle' style='line-height: {height}px; height: {height}px'>{name} / {file}</div>"
				.replace(/{time}/g, 		list[i].time)
				.replace(/{name}/g, 		list[i].name)
				.replace(/{height}/g, 		min_height + list[i].ex_time/min_time)
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
