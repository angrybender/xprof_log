<?php
/**
 *
 * @author k.vagin
 */

namespace Models;


class FunctionsCall extends \ActiveRecord\Model
{
	# explicit table name since our table is not "books"
   static $table_name = 'functions_call';

   # explicit pk since our pk is not "id"
   static $primary_key = 'id_function';
} 