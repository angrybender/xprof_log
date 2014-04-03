<?php
/**
 *
 * @author k.vagin
 */

namespace Models;


class LogDumps extends \ActiveRecord\Model
{
	# explicit table name since our table is not "books"
   static $table_name = 'log_dumps';

   # explicit pk since our pk is not "id"
   static $primary_key = 'id_dump';
} 