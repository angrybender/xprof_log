$(function()
{
	var $component = $('.component.form-filter-log');
	var group_template = $component.find('.template-group').html();
	var arg_sub_from_tpl = $component.find('.template-args-type').html();

	var api = $component.find('form').attr('action');
	var fields = [];
	var types = [];

	var fields_translate = {
		'function_name' : 	'Имя функции',
		'function_line' : 	'Строка вызова',
		'call_file' : 		'Файл вызова',
		'entry_file' : 		'Точка входа',
		'time' : 			'Время вызова',
		'arg' : 			'Аргументы вызываемой функции'
	};

	var __string = function(name) {
		return "<input type='text' name='"+name+"' class='form-control'>";
	};

	var fields_generators = {
		function_name : __string,
		function_line : __string,
		call_file : 	__string,
		entry_file :	__string,
		time :			function(name)
		{
			return "<input type='text' name='"+name+"[from]' class='form-control width-half' placeholder='с'> <input type='text' name='"+name+"[to]' class='form-control width-half' placeholder='по'>";
		},

		arg :  			function(name)
		{
			return arg_sub_from_tpl.replace(/{name}/g, name);
		}
	};

	function field_set_generator(type, id)
	{
		id = id + '[' + type + ']';
		return fields_generators[type](id);
	}

	function select(items, name, value)
	{
		var html = [];
		var active_value = value || null;
		for (var value in items) {
			html.push("<option {selected} value='{value}'>{title}</option>"
				.replace(/{value}/g, value)
				.replace(/{title}/g, items[value])
				.replace(/{selected}/g, active_value == value ? 'selected' : '')
			);
		}

		return "<select class='form-control' name='" + name + "'>" + html.join(" ") + '</select>';
	}

	var group_id = 0;
	function add_group()
	{
		group_id++;
		var html = group_template.replace(/{field_select}/g, field_select_cache.replace(/{field_id}/g, group_id));
		$component.find('form .inner').append(html);

		var $select = $component.find('form .inner .form-group:last select');
		var params = field_set_generator($select[0].value, $select[0].name);
		$component.find('form .inner .form-group:last .field_params').html(params);
	}

	function init()
	{
		add_group();
	}

	var field_select_cache = select(fields_translate, 'filter[field_{field_id}][type]');

	$.getJSON(api, function(types_and_fields) {
		fields = types_and_fields.fields;
		types = types_and_fields.types;

		init();

		$component.show();
	});

	$component
		.on('click', '.control-add', function(){
			add_group();
			return false;
		})
		.on('click', '.control-remove', function(){

			if ($component.find('form .form-group').length == 1) return false;

			$(this).parent().remove();
			return false;
		})
		.on('change', '.field_type select', function(){
			var params = field_set_generator(this.value, this.name);
			$(this).closest('.form-group').find('.field_params').html(params);
		});
});