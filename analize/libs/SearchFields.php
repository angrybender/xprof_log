<?php
/**
 *
 * @author k.vagin
 */

class SearchFields
{
	public static $available_fields = array(
		'function_name' => 	'array|string',
		'function_line' =>	'array|int',
		'call_file'		=>	'array|string',
		'entry_file'	=>	'string',
		'time'			=> array(
			'from'	=> 'int',
			'to'	=> 'int'
		),
		'arg'			=> 'array|argument',
	);

	public static $types = array(
		'argument' => array(
			'type' => 'string',
			'key' => 'string',
			'value' => 'string',
			'number' => 'int',
		),
	);
} 