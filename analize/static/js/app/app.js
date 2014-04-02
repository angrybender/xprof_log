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

function render_catalog_bars() {

	var prev_section = null;
	var start_box = 0;
	var height_box = 0;
	var html = "";

	var scale = ModelFunctions[ModelFunctions.length-1].time/ModelView.scale;
	var scroll_pos = getBodyScrollTop();
	var w_height = $(window).height();

	for (var i in ModelFunctions) {
		if ((i && prev_section !== ModelFunctions[i].catalog) || (i == ModelFunctions.length-1)) { // если поменялся уровень или дошли до конца
			height_box = ModelFunctions[i].time/scale - start_box;

			if (start_box + height_box > scroll_pos && start_box-scroll_pos<w_height) {
				html = html + "<div class='bar-section' style='top:{top}px; height: {height}px; background: {bag_color}' data-id='{m_id}'></div>"
					.replace(/{top}/g, start_box)
					.replace(/{height}/g, height_box)
					.replace(/{m_id}/g, i)
					.replace(/{bag_color}/g, get_color_by_index(ModelCatalogs[prev_section]))
			}

			start_box = ModelFunctions[i].time/scale;
			prev_section = ModelFunctions[i].catalog;
		}
	}
	$('.component.time-line-bar').height(ModelView.scale).html(html);
}



var ModelFunctions = [];
var ModelCatalogs = {};
var ModelView = {
	scale : 3000,
	type  : 'catalog'
};

$(function() {

	(function(){
		var timer=0;
		$(window).scroll(function() {
			if (!ModelFunctions) return;

			clearTimeout(timer);
			timer = setTimeout(render_catalog_bars, 250);
		});
	})();

	$.getJSON('/api.php?method=func_list', function(list) {
		var i;

		var catalog_index_max = 0;
		for (i in list) {
			if (list[i].file) {
				list[i].catalog = get_path_of_file_full_path(list[i].file);
				if (!ModelCatalogs[list[i].catalog]) {
					ModelCatalogs[list[i].catalog] = catalog_index_max;
					catalog_index_max++;
				}
			}
			else {
				list[i].catalog = null;
			}
		}

		ModelFunctions= list;
		render_catalog_bars();
	});

	(function(){

		var obj;
		var show_title = function() {
			var $this = $(obj);
			var m_id = $this.data('id')*1;
			if (ModelFunctions[m_id]) {
				$this.html("<span>{catalog}</span>".replace(/{catalog}/g, ModelFunctions[m_id].catalog))
			}
		}

		var timer=0;
		$('.component.time-line-bar')
			.on('mouseenter', '.bar-section', function() {
				$(obj).find('span').remove();
				obj = this;
				clearTimeout(timer);
				timer = setTimeout(show_title, 500);
			})
			.bind('mouseleave', function(){
				clearTimeout(timer);
				$(obj).find('span').remove();
			});
	})();

	(function(){

		$('.component.scale-toolbar .plus').click(function(){
			if (!ModelFunctions) return;

			ModelView.scale = ModelView.scale*1.5;
			render_catalog_bars();
		});

		$('.component.scale-toolbar .minus').click(function(){
			if (!ModelFunctions) return;

			ModelView.scale = ModelView.scale/1.5;
			if (ModelView.scale<3000) ModelView.scale = 3000;
			render_catalog_bars();
		});
	})();
});
