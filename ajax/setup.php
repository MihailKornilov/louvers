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

	case 'feature_add':
		if(!$category_sub_id = _isnum($_POST['category_sub_id']))
			jsonError();
		if(!$name_id = _isnum($_POST['name_id']))
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$kod = win1251(htmlspecialchars(trim($_POST['kod'])));
		if(empty($kod))
			jsonError();

		$sql = "INSERT INTO `louvers_setup_feature` (
					`category_sub_id`,
					`name_id`,
					`name`,
					`kod`
				) VALUES (
					".$category_sub_id.",
					".$name_id.",
					'".addslashes($name)."',
					'".addslashes($kod)."'
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'feature');
		GvaluesCreate();

		$send['html'] = utf8(setup_feature_spisok($category_sub_id));
		jsonSuccess($send);
		break;
	case 'feature_edit':
		if(!$id = _isnum($_POST['id']))
			jsonError();
		if(!$category_sub_id = _isnum($_POST['category_sub_id']))
			jsonError();
		if(!$name_id = _isnum($_POST['name_id']))
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$kod = win1251(htmlspecialchars(trim($_POST['kod'])));
		if(empty($kod))
			jsonError();

		$sql = "UPDATE `louvers_setup_feature`
				SET `name_id`=".$name_id.",
					`name`='".addslashes($name)."',
					`kod`='".addslashes($kod)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'feature');
		GvaluesCreate();

		$send['html'] = utf8(setup_feature_spisok($category_sub_id));
		jsonSuccess($send);
		break;
	case 'feature_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_feature` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "SELECT COUNT(*) FROM `louvers_setup_feature_color` WHERE `feature_id`=".$id;
		if(query_value($sql))
			jsonError();

		$sql = "DELETE FROM `louvers_setup_feature` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'feature');
		GvaluesCreate();

		$send['html'] = utf8(setup_feature_spisok($r['category_sub_id']));
		jsonSuccess($send);
		break;

	case 'feature_color_add':
		if(!$feature_id = _isnum($_POST['feature_id']))
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();
		$kod = win1251(htmlspecialchars(trim($_POST['kod'])));
		if(empty($kod))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_feature` WHERE `id`=".$feature_id;
		if(!$r = query_assoc($sql))
			jsonError();


		$sql = "INSERT INTO `louvers_setup_feature_color` (
					`feature_id`,
					`name`,
					`kod`
				) VALUES (
					".$feature_id.",
					'".addslashes($name)."',
					'".addslashes($kod)."'
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'feature_color');
		GvaluesCreate();

		$send['html'] = utf8(setup_feature_spisok($r['category_sub_id']));
		jsonSuccess($send);
		break;
	case 'feature_color_edit':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$kod = win1251(htmlspecialchars(trim($_POST['kod'])));

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_feature_color` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_feature` WHERE `id`=".$r['feature_id'];
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `louvers_setup_feature_color`
				SET `name`='".addslashes($name)."',
					`kod`='".addslashes($kod)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'feature_color');
		GvaluesCreate();

		$send['html'] = utf8(setup_feature_spisok($r['category_sub_id']));
		jsonSuccess($send);
		break;
	case 'feature_color_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_feature_color` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "SELECT * FROM `louvers_setup_feature` WHERE `id`=".$r['feature_id'];
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "DELETE FROM `louvers_setup_feature_color` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'feature_color');
		GvaluesCreate();

		$send['html'] = utf8(setup_feature_spisok($r['category_sub_id']));
		jsonSuccess($send);
		break;
}

jsonError();