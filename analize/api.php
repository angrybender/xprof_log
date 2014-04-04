<?php
/**
 *
 * @author k.vagin
 */

$routes = array(
	'func_list' 			=> array('func_list'),
	'get_php_file_view' 	=> array('get_php_file_view'),
	'file_list' 			=> array('file_list'),
	'file_filter' 			=> array('file_filter'),
);

$action = isset($_GET['method']) ? $_GET['method'] : '';
if (!isset($routes[$action])) {
	return header('err', false, 404);
}

require_once __DIR__ . '/controllers/' . $action . '.php';
$class_name = ucfirst($action);
$controller = new $class_name();

$req_method = strtoupper($_SERVER['REQUEST_METHOD']);
try {
	if ($req_method === 'GET') {
		$result = $controller->get($_GET);
	}
	elseif ($req_method === 'POST') {
		$result = $controller->post($_POST);
	}
	elseif ($req_method === 'PUT') {
		$result = $controller->put($_POST);
	}
	elseif ($req_method === 'DELETE') {
		$result = $controller->delete($_GET);
	}

	echo json_encode($result);
}
catch (\Exception $e) {
	return header($e->getMessage(), false, 500);
}

