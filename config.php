<?php
define('DOCUMENT_ROOT', dirname(__FILE__));
define('NAMES', 'cp1251');

require_once(DOCUMENT_ROOT.'/syncro.php');
require_once(API_PATH.'/vk.php');
_appAuth();

require_once(DOCUMENT_ROOT.'/view/main.php');

_dbConnect();
_getSetupGlobal();
_getVkUser();


function _getSetupGlobal() {//Получение глобальных данных
	$key = CACHE_PREFIX.'setup_global';
	$g = xcache_get($key);
	if(empty($g)) {
		$sql = "SELECT * FROM `setup_global` LIMIT 1";
		$g = mysql_fetch_assoc(query($sql));
		xcache_set($key, $g, 86400);
	}
	define('VERSION', $g['version']);
	define('G_VALUES', $g['g_values']);
}//_getSetupGlobal()
function _getVkUser() {//Получение данных о пользователе
	$u = _viewer();
}//_getVkUser()
