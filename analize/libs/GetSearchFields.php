<?php
/**
 *
 * @author k.vagin
 */

class SearchFields
{
	public static $available_fields = array(
		'SUPERGLOBALS' => array(
			'_SERVER' 	=> 'vector',
			'_SESSION' 	=> 'vector',
			'_GET' 		=> 'vector',
			'_POST' 	=> 'vector',
			'_COOKIE'  	=> 'vector',
		),
		'FUNC_CALL' => array(
			'NAME' 		=> 'scalar',
			'FILE' 		=> 'scalar',
			'FILE_S' 	=> 'scalar',
			'ARG' 		=> 'any'
		)
	);
} 