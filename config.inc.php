<?php defined('FCPATH') OR exit('No direct script access allowed'); 

require(APPPATH.'libraries'.DIRECTORY_SEPARATOR.'F.php');
$f = new F;

define('SEPARATOR', '/');
/* Time Zone */ 
define('TIME_ZONE', 'Asia/Jakarta'); 
@date_default_timezone_set(TIME_ZONE);

/* Override php.ini config */
if (function_exists('ini_set')) {
    @ini_set('max_execution_time', 300);
    @ini_set('date.timezone', TIME_ZONE);
}

/* Base URL */ 
$protocol = isset($_SERVER["HTTPS"]) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
define('PROTOCOL', $protocol);

$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
define('HTTP_HOST', $http_host);

/* List available domain */
$domain = [
	1 => 'localhost:9090',
	2 => 'ip_public',
];
if (!in_array(HTTP_HOST, $domain))
	$f->bare_response(FALSE, ['message' => "Domain name <strong>$http_host</strong> is not available !"]);
	// $f->debug("Domain name <strong>$http_host</strong> is not available !", 'json');

/* Define default path. Implement on $route['default_controller'] */
$path = [
	$domain[1] => 'frontend',
	$domain[2] => 'backend',
];
define('PATH', $path[$http_host]);

/* Define BASE_URL. Implement on $config['base_url'] */
define('BASE_URL', PROTOCOL.HTTP_HOST.SEPARATOR); 
define('JSONRPC_URL', PROTOCOL.HTTP_HOST.SEPARATOR.'jsonrpc'); 

/* Init TMP/CACHE Folder */
$tmp = '__tmp';
if (!file_exists($tmp) && !is_dir($tmp)) { mkdir($tmp); } 
