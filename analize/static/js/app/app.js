function getBodyScrollTop() {
	return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
}

var color_table = [
	'#8B0000', '#ADFF2F', '#DDA0DD'
];
function get_color_by_index(index) {
	index = index % color_table.length;

	return color_table[index];
}

function get_list_of_files(list) {
	var files = {};
	var result = [];
	for (var i in list) {
		if (list[i].file) {
			if (!files[list[i].file]) {
				files[list[i].file] = 1;
				result.push(list[i].file);
			}
		}
	}

	return result;
}

function get_list_of_catalogs(files) {
	var catalogs = {};
	var result = [];
	var paths = [];
	var path = "";

	if (files.length == 0) {
		return [];
	}

	var DS = files[0].substr(0, 1)
	for (var i in files) {
		paths = files[i].split(DS);
		path = paths.slice(0, -1).join(DS); // cut the filename

		if (!catalogs[path]) {
			catalogs[path] = 1;
			result.push(path);
		}
	}

	return result;
}

function get_path_of_file_full_path(file_full_path) {
	var DS = file_full_path.substr(0, 1);
	return file_full_path.split(DS).slice(0, -1).join(DS) + DS;
}

$(function() {

	var top_delta = 35;
	$.getJSON('/api.php?method=func_list', function(list) {
		var html = "";
		var times = [];
		var last_top = -1;
		var left = 0;
		var top = 0;
		var i;

		var catalog_index = {};
		var catalog_index_max = 0;
		var prev_section = null;
		var start_box = 0;
		var height_box = 0;
		html = "";
		for (i in list) {
			if (list[i].file) {
				list[i].catalog = get_path_of_file_full_path(list[i].file);
				if (!catalog_index[list[i].catalog]) {
					catalog_index[list[i].catalog] = catalog_index_max;
					catalog_index_max++;
				}
			}
			else {
				list[i].catalog = null;
			}

			var scale = list[list.length-1].time/3000;
			if ((i && prev_section !== list[i].catalog) || (i == list.length-1)) { // если поменялся уровень или дошли до конца
				height_box = list[i].time/scale - start_box;

				if (height_box>=1) {
					html = html + "<div class='bar-section' style='top:{top}px; height: {height}px; background: {bag_color}' data-path='{path}'></div>"
						.replace(/{top}/g, start_box)
						.replace(/{height}/g, height_box)
						.replace(/{path}/g, prev_section)
						.replace(/{bag_color}/g, get_color_by_index(catalog_index[prev_section]))
				}

				start_box = list[i].time/scale;
				prev_section = list[i].catalog;
			}
		}
		$('.component.time-line-bar').html(html);

		//console.log("ALL count: ", list.length, " GROUP count: ", gcnt);
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
