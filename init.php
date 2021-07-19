<?php
ini_set('default_charset', 'UTF-8'); //default_charset

date_default_timezone_set('Asia/Shanghai'); //timezone

//app name
define('APP_NAME', 'php_script');
define('URL_APP_SHORT', 'php_script');
define('URL_APP', @$_SERVER['REQUEST_SCHEME'] . '://' . @$_SERVER['HTTP_HOST'] . '/' . URL_APP_SHORT);

//env
define('ENV', 'dev');	//define('ENV', 'prod');

//env
define('ENV_SIDE', 'backend');	//define('ENV_SIDE', 'frontend');

//debug
define('DEBUG', true);

//strace frames limit
define('STRACE_LIMIT', 3);

define('DIR_MODE', 0755);
define('FILE_MODE', 0644);

//app dir
define('DIR_APP', __DIR__);
define('DIR_LOG', __DIR__ . DIRECTORY_SEPARATOR . 'log');
define('DIR_STATIC', __DIR__ . DIRECTORY_SEPARATOR . 'static');

//app dir
define('DIR_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'));
//app pathinfo
define('PATH_APP', str_replace('\\', '/', substr_replace(DIR_APP, '', 0, strlen(DIR_ROOT))));    //相对于域名的path


//log file size
define('LOG_FILE_SIZE', 5242880); //5M

//error handling
if (defined('DEBUG') && DEBUG === true) {
	error_reporting(E_ALL & ~E_DEPRECATED);
} else {
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & E_STRICT);
}

if (defined('AGENT') && AGENT === 'http' && defined('ENV') && ENV === 'dev') {
	ini_set('display_errors', '1');
} else {
	if (!defined('AGENT')) {
		define('AGENT', 'app');
	}
	ini_set('display_errors', 0);
	ini_set('log_errors', 1);
	ini_set('ignore_repeated_errors', 1);
	$log_dir = defined('DIR_LOG') ? DIR_LOG : __DIR__ . DIRECTORY_SEPARATOR . 'log';
	if (!is_dir($log_dir) && mkdir($log_dir, DIR_MODE, true) === false) {
		error_log('Can\'t create log dir');
	} else {
		$log_file = $log_dir . DIRECTORY_SEPARATOR . AGENT . '_error.log';
		if (!is_file($log_file)) {
			touch($log_file);
		}
		ini_set('error_log', $log_file);
	}
}