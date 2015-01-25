<?php
require_once('config.php');
require_once(API_PATH.'/vk_ajax.php');


switch(@$_POST['op']) {
	case 'cache_clear':
		if(!SA)
			jsonError();

		_cacheClear();

		jsonSuccess();
		break;

	case 'client_sel':
		if(!preg_match(REGEXP_WORDFIND, win1251($_POST['val'])))
			$_POST['val'] = '';
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			$_POST['client_id'] = 0;
		$val = win1251($_POST['val']);
		$client_id = intval($_POST['client_id']);
		$sql = "SELECT *
				FROM `louvers_client`
				WHERE !`deleted`".
			(!empty($val) ? " AND (`fio` LIKE '%".$val."%' OR `telefon` LIKE '%".$val."%' OR `adres` LIKE '%".$val."%')" : '').
			($client_id > 0 ? " AND `id`<=".$client_id : '')."
				ORDER BY `id` DESC
				LIMIT 50";
		$q = query($sql);
		$send['spisok'] = array();
		while($r = mysql_fetch_assoc($q)) {
			$unit = array(
				'uid' => $r['id'],
				'title' => utf8(htmlspecialchars_decode($r['fio'])),
				'adres' => utf8(htmlspecialchars_decode($r['adres']))
			);
			$content = array();
			if($r['telefon'])
				$content[] = $r['telefon'];
			if($r['adres'])
				$content[] = $r['adres'];
			if(!empty($content))
				$unit['content'] = utf8($r['fio'].'<span>'.implode('<br />', $content).'</span>');
			$send['spisok'][] = $unit;
		}
		jsonSuccess($send);
		break;
	case 'client_add':
		$org = win1251(htmlspecialchars(trim($_POST['org'])));
		$fio = win1251(htmlspecialchars(trim($_POST['fio'])));
		$telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));
		if(empty($org) && empty($fio))
			jsonError();
		$sql = "INSERT INTO `louvers_client` (
					`org`,
					`fio`,
					`telefon`,
					`adres`,
					`viewer_id_add`
				) VALUES (
					'".addslashes($org)."',
					'".addslashes($fio)."',
					'".addslashes($telefon)."',
					'".addslashes($adres)."',
					".VIEWER_ID."
				)";
		query($sql);

		jsonSuccess();
		break;
	case 'client_spisok':
		$_POST['find'] = win1251($_POST['find']);
		$data = client_data($_POST);
		if(empty($_POST['page']))
			$send['result'] = utf8($data['result']);
		$send['spisok'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'client_edit':
		if(!$client_id = _isnum($_POST['client_id']))
			jsonError();

		$org = win1251(htmlspecialchars(trim($_POST['org'])));
		$fio = win1251(htmlspecialchars(trim($_POST['fio'])));
		$telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
		$adres = win1251(htmlspecialchars(trim($_POST['adres'])));

		if(empty($org) && empty($fio))
			jsonError();

		$sql = "SELECT * FROM `louvers_client` WHERE !`deleted` AND `id`=".$client_id;
		if(!$client = query_assoc($sql))
			jsonError();

		query("UPDATE `louvers_client`
			   SET  `org`='".addslashes($org)."',
					`fio`='".addslashes($fio)."',
					`telefon`='".addslashes($telefon)."',
					`adres`='".addslashes($adres)."'
			   WHERE `id`=".$client_id);

		$send = array(
			'id' => $client_id,
			'org' => $org,
			'fio' => $fio,
			'telefon' => $telefon,
			'adres' => $adres,

			'balans' => clientBalansUpdate($client_id),
			'viewer_id_add' => $client['viewer_id_add'],
			'dtime_add' => $client['dtime_add'],
			'deleted' => 0
		);
		$send['html'] = clientInfoGet($send);
		foreach($send as $i => $v)
			$send[$i] = utf8($v);
		jsonSuccess($send);
		break;
	case 'client_del':
		if(!$client_id = _isnum($_POST['id']))
			jsonError();

		if(!query_value("SELECT COUNT(`id`) FROM `louvers_client` WHERE !`deleted` AND `id`=".$client_id))
			jsonError();

		query("UPDATE `louvers_client` SET `deleted`=1 WHERE `id`=".$client_id);
		jsonSuccess();
		break;

	case 'zayav_add':
		if(!$client_id = _isnum($_POST['client_id']))
			jsonError();
		if(!$product = zayav_product_test($_POST['product']))
			jsonError();

		$comm = win1251(htmlspecialchars(trim($_POST['comm'])));

		$sql = "INSERT INTO `louvers_zayav` (
					`client_id`,
					`status_day`,
					`viewer_id_add`
				) VALUES (
					".$client_id.",
					CURRENT_TIMESTAMP,
					".VIEWER_ID."
				)";
		query($sql);
		$send['id'] = mysql_insert_id();

		foreach($product as $r) {
			$sql = "INSERT INTO `louvers_zayav_product` (
						`zayav_id`,
						`category_id`,
						`category_sub_id`,
						`size_x`,
						`size_y`,
						`cloth_name`,
						`cloth_color`,
						`count`
					) VALUES (
						".$send['id'].",
						".$r[0].",
						".$r[1].",
						".$r[2].",
						".$r[3].",
						".$r[4].",
						".$r[5].",
						".$r[6]."
					)";
			query($sql);
		}

		_vkCommentAdd('louvers_zayav', $send['id'], $comm);

		jsonSuccess($send);
		break;

	case 'stock_add'://Внесение новой позиции в склад
		$category_id = _isnum($_POST['category_id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(!$measure = _isnum($_POST['measure']))
			jsonError();
		$expense = _isnum($_POST['expense']);

		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `louvers_stock` (
					`category_id`,
					`name`,
					`measure`,
					`expense`
				) VALUES (
					".$category_id.",
					'".addslashes($name)."',
					'".$measure."',
					'".$expense."'
				)";
		query($sql);

		$data = stock_spisok();
		$send['result'] = utf8($data['result']);
		$send['spisok'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'stock_spisok':
		$_POST['find'] = win1251($_POST['find']);
		$data = stock_spisok($_POST);
		if($data['filter']['page'] == 1)
			$send['result'] = utf8($data['result']);
		$send['spisok'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
}

jsonError();