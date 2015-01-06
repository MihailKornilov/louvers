<?php
require_once('config.php');

switch(@$_POST['op']) {
	case 'category_add':
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();
		$sql = "INSERT INTO `louvers_setup_category` (
					`name`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					"._maxSql('louvers_setup_category')."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'category');
		GvaluesCreate();

		$send['html'] = utf8(setup_category_spisok());
		jsonSuccess($send);
		break;
	case 'category_edit':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_category` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `louvers_setup_category`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'category');
		GvaluesCreate();

		$send['html'] = utf8(setup_category_spisok());
		jsonSuccess($send);
		break;
	case 'category_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_category` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "DELETE FROM `louvers_setup_category` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'category');
		GvaluesCreate();

		$send['html'] = utf8(setup_category_spisok());
		jsonSuccess($send);
		break;
}

jsonError();