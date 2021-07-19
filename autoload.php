<?php

function autoload($class)
{
	static $vendors = ['chillerlan'];
	
	$class = trim($class, '/\\');
	$classPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $class) . '.php';
	
	$path = __DIR__ . DIRECTORY_SEPARATOR . $classPath;
	if (is_file($path)) {
		return require_once $path;
	}
	
	if ($slashPos = strpos($classPath, DIRECTORY_SEPARATOR, 1)) {
		//需要进一步映射
		$vendor = substr($classPath, 0, $slashPos);
		if (in_array($vendor, $vendors)) {
			$path = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $classPath;
			if (is_file($path)) {
				return require_once $path;
			}
		}
	}
	
	return 0;
}

spl_autoload_register('autoload');




