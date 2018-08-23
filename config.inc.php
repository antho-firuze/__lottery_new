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
	1 => 'api.ayoavram.com',
	2 => 'app.ayoavram.com',
	3 => 'ayoavram.com',
	4 => 'www.ayoavram.com',
	5 => 'localhost:8080',
];
if (!in_array(HTTP_HOST, $domain))
	$f->bare_response(FALSE, ['message' => "Domain name <strong>$http_host</strong> is not available !"]);
	// $f->debug("Domain name <strong>$http_host</strong> is not available !", 'json');

/* Define default path. Implement on $route['default_controller'] */
$path = [
	$domain[1] => 'v1',
	$domain[2] => 'backend',
	$domain[3] => 'frontend',
	$domain[4] => 'frontend',
	$domain[5] => 'frontend',
];
define('PATH', $path[$http_host]);

/* Define BASE_URL. Implement on $config['base_url'] */
define('BASE_URL', PROTOCOL.HTTP_HOST.SEPARATOR); 
define('JSONRPC_URL', PROTOCOL.HTTP_HOST.SEPARATOR.'jsonrpc'); 

/* Cache Folder */
// define('CACHE_FOLDER', 'var/cache');
// if (!file_exists(CACHE_FOLDER) && !is_dir(CACHE_FOLDER)) {
	// mkdir(CACHE_FOLDER);         
// } 

/* BACKEND CONSTANT VARIABLES */
// define('APPS_LNK', BASE_URL.'systems');
// define('PAGE_LNK', BASE_URL.'systems/x_page');
// define('AUTH_LNK', BASE_URL.'systems/x_auth');
// define('ROLE_SELECTOR_LNK', BASE_URL.'systems/x_role_selector');
// define('LOGIN_LNK', BASE_URL.'systems/x_login');
// define('LOGOUT_LNK', BASE_URL.'systems/x_logout');
// define('U_CONFIG_LNK', BASE_URL.'systems/a_user_config');
// define('SRCMENU_LNK', BASE_URL.'systems/x_srcmenu');
// define('PROFILE_LNK', BASE_URL.'systems/x_profile');
// define('RELOAD_LNK', BASE_URL.'systems/x_reload');
// define('FORGOT_LNK', BASE_URL.'systems/x_forgot');
// define('RESET_LNK', BASE_URL.'systems/x_reset');
// define('X_INFO_LNK', BASE_URL.'systems/x_info');
/* FRONTEND CONSTANT VARIABLES */
// define('HOME_LNK', BASE_URL.'frontend');
// define('INFOLST_LNK', BASE_URL.'frontend/infolist');