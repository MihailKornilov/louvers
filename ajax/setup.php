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

	case 'categorysub_add':
		if(!$category_id = _isnum($_POST['category_id']))
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `louvers_setup_category_sub` (
					`category_id`,
					`name`
				) VALUES (
					".$category_id.",
					'".addslashes($name)."'
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'category_sub');
		GvaluesCreate();

		$send['html'] = utf8(setup_category_sub_spisok($category_id));
		jsonSuccess($send);
		break;
	case 'categorysub_edit':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_category_sub` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `louvers_setup_category_sub`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'category_sub');
		GvaluesCreate();

		$send['html'] = utf8(setup_category_sub_spisok($r['category_id']));
		jsonSuccess($send);
		break;
	case 'categorysub_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_category_sub` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "DELETE FROM `louvers_setup_category_sub` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'category_sub');
		GvaluesCreate();

		$send['html'] = utf8(setup_category_sub_spisok($r['category_id']));
		jsonSuccess($send);
		break;

	case 'cloth_name_add':
		if(!$category_sub_id = _isnum($_POST['category_sub_id']))
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `louvers_setup_cloth_name` (
					`category_sub_id`,
					`name`
				) VALUES (
					".$category_sub_id.",
					'".addslashes($name)."'
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'cloth_name');
		GvaluesCreate();

		$send['html'] = utf8(setup_cloth_name($category_sub_id));
		jsonSuccess($send);
		break;
	case 'cloth_name_edit':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_cloth_name` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `louvers_setup_cloth_name`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'cloth_name');
		GvaluesCreate();

		$send['html'] = utf8(setup_cloth_name($r['category_sub_id']));
		jsonSuccess($send);
		break;
	case 'cloth_name_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_cloth_name` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "DELETE FROM `louvers_setup_cloth_name` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'cloth_name');
		GvaluesCreate();

		$send['html'] = utf8(setup_cloth_name($r['category_sub_id']));
		jsonSuccess($send);
		break;

	case 'cloth_color_add':
		if(!$category_sub_id = _isnum($_POST['category_sub_id']))
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `louvers_setup_cloth_color` (
					`category_sub_id`,
					`name`
				) VALUES (
					".$category_sub_id.",
					'".addslashes($name)."'
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'cloth_color');
		GvaluesCreate();

		$send['html'] = utf8(setup_cloth_color($category_sub_id));
		jsonSuccess($send);
		break;
	case 'cloth_color_edit':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_cloth_color` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `louvers_setup_cloth_color`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'cloth_color');
		GvaluesCreate();

		$send['html'] = utf8(setup_cloth_color($r['category_sub_id']));
		jsonSuccess($send);
		break;
	case 'cloth_color_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_cloth_color` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "DELETE FROM `louvers_setup_cloth_color` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'cloth_color');
		GvaluesCreate();

		$send['html'] = utf8(setup_cloth_color($r['category_sub_id']));
		jsonSuccess($send);
		break;
}

jsonError();