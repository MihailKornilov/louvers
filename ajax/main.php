<?php
require_once('config.php');
require_once(API_PATH.'/vk_ajax.php');


switch(@$_POST['op']) {
	case 'cache_clear':
		if(!SA)
			jsonError();
		query("UPDATE `setup_global` SET `version`=`version`+1");
		_cacheClear();
		jsonSuccess();
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
}

jsonError();