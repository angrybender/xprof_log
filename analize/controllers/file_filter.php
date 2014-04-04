<?php
/**
 *
 * @author k.vagin
 */

include_once __DIR__ . '/../libs/SearchFields.php';

class File_filter
{
	public function get()
	{
		return $this->get_fields();
	}

	private function get_fields()
	{
		return array(
			'fields' => SearchFields::$available_fields,
			'types'  => SearchFields::$types
		);
	}
}