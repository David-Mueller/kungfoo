<?php

error_reporting(0);
ini_set('display_errors', 0);

define('APP_DIR', realpath(__DIR__.'/..'));
define('HTTP_SUBDIR', ''); // are we running from within a subdir?

if (is_file(__DIR__.'/config_server.php')) {
	include_once __DIR__.'/config_server.php';
}

// load global functions
include_once(APP_DIR.'/src/globalFunctions.php');