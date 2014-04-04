<?php
/**
 *
 * @author k.vagin
 */

namespace Models;


class VarsParsed extends \ActiveRecord\Model
{
	# explicit table name since our table is not "books"
   static $table_name = 'vars_parsed';

   # explicit pk since our pk is not "id"
   static $primary_key = 'var_parsed_id';
} 