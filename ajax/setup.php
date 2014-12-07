<?php
require_once('config.php');

switch(@$_POST['op']) {
	case 'stock_add':
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();
		$sql = "INSERT INTO `louvers_setup_stock_category` (
					`name`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					"._maxSql('louvers_setup_stock_category')."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'stock_category');
		GvaluesCreate();

		$send['html'] = utf8(setup_stock_spisok());
		jsonSuccess($send);
		break;
	case 'stock_edit':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_stock_category` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `louvers_setup_stock_category`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'stock_category');
		GvaluesCreate();

		$send['html'] = utf8(setup_stock_spisok());
		jsonSuccess($send);
		break;
	case 'stock_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_stock_category` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "DELETE FROM `louvers_setup_stock_category` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'stock_category');
		GvaluesCreate();

		$send['html'] = utf8(setup_stock_spisok());
		jsonSuccess($send);
		break;
}

jsonError();