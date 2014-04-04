<?php
/**
 *
 * @author k.vagin
 */

namespace Models;


class ProjectsFiles extends \ActiveRecord\Model
{
	# explicit table name since our table is not "books"
   static $table_name = 'projects_files';

   # explicit pk since our pk is not "id"
   static $primary_key = 'id_file';

	public static function add($full_path)
	{
		$file = self::find(array(
			'full_path' => $full_path
		));

		if (!empty($file)) {
			return $file->id_file;
		}

		$file = new ProjectsFiles(array(
			'full_path' => $full_path
		));
		$file->save();

		return $file->id_file;
	}
} 