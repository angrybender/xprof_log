<?php
/**
 *
 * @author k.vagin
 */

class Storage {

	private static $instance = null;

	public static function get()
	{
		if (!self::$instance) {
			$cfg = ActiveRecord\Config::instance();
			$cfg->set_model_directory(__DIR__ . '/../models');
			$cfg->set_connections(
				array(
					'development' => include __DIR__ . '/../configs/db.php',
					//'test' => 'mysql://username:password@localhost/test_database_name',
					//'production' => 'mysql://username:password@localhost/production_database_name',
				)
			);

			self::$instance = true;
		}
	}
} 