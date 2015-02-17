<?php
function _hashRead() {
	$_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 'zayav';
	if(empty($_GET['hash'])) {
		define('HASH_VALUES', false);
		if(APP_START) {// �������������� ��������� ���������� ��������
			$_GET['p'] = isset($_COOKIE['p']) ? $_COOKIE['p'] : $_GET['p'];
			$_GET['d'] = isset($_COOKIE['d']) ? $_COOKIE['d'] : '';
			$_GET['d1'] = isset($_COOKIE['d1']) ? $_COOKIE['d1'] : '';
			$_GET['id'] = isset($_COOKIE['id']) ? $_COOKIE['id'] : '';
		} else
			_hashCookieSet();
		return;
	}
	$ex = explode('.', $_GET['hash']);
	$r = explode('_', $ex[0]);
	unset($ex[0]);
	define('HASH_VALUES', empty($ex) ? false : implode('.', $ex));
	$_GET['p'] = $r[0];
	unset($_GET['d']);
	unset($_GET['d1']);
	unset($_GET['id']);
	switch($_GET['p']) {
		case 'client':
			if(isset($r[1]))
				if(preg_match(REGEXP_NUMERIC, $r[1])) {
					$_GET['d'] = 'info';
					$_GET['id'] = intval($r[1]);
				}
			break;
		case 'zayav':
			if(isset($r[1]))
				if(preg_match(REGEXP_NUMERIC, $r[1])) {
					$_GET['d'] = 'info';
					$_GET['id'] = intval($r[1]);
				} else {
					$_GET['d'] = $r[1];
					if(isset($r[2]))
						$_GET['id'] = intval($r[2]);
				}
			break;
		default:
			if(isset($r[1])) {
				$_GET['d'] = $r[1];
				if(isset($r[2]))
					$_GET['d1'] = $r[2];
			}
	}
	_hashCookieSet();
}//_hashRead()
function _hashCookieSet() {
	setcookie('p', $_GET['p'], time() + 2592000, '/');
	setcookie('d', isset($_GET['d']) ? $_GET['d'] : '', time() + 2592000, '/');
	setcookie('d1', isset($_GET['d1']) ? $_GET['d1'] : '', time() + 2592000, '/');
	setcookie('id', isset($_GET['id']) ? $_GET['id'] : '', time() + 2592000, '/');
}//_hashCookieSet()
function _mainLinks() {
	global $html;
	$links = array(
		array(
			'name' => '�������',
			'page' => 'client',
			'show' => 1
		),
		array(
			'name' => '������',
			'page' => 'zayav',
			'show' => 1
		),
		array(
			'name' => '�����',
			'page' => 'stock',
			'show' => 1
		),
		array(
			'name' => '������',
			'page' => 'report',
			'show' => 1
		),
		array(
			'name' => '���������',
			'page' => 'setup',
			'show' => 1
		)
	);

	$send = '<a href="//vk.com/app3978722" target="_blank" id="go-evrookna">&raquo; Evrookna</a>'.
		'<div id="mainLinks">';
	foreach($links as $l)
		if($l['show'])
			$send .= '<a href="'.URL.'&p='.$l['page'].'"'.($l['page'] == $_GET['p'] ? 'class="sel"' : '').'>'.$l['name'].'</a>';
	$send .= '</div>';
	$html .= $send;
}//_mainLinks()
function _cacheClear() {
	xcache_unset(CACHE_PREFIX.'category');
	xcache_unset(CACHE_PREFIX.'category_sub');
	xcache_unset(CACHE_PREFIX.'setup_global');
	GvaluesCreate();

	query("UPDATE `setup_global` SET `version`=`version`+1");
}//_cacheClear()

function _header() {
	global $html;
	$html =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.

		'<head>'.
		'<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
		'<title>������ - ���������� '.API_ID.'</title>'.

		_api_scripts().

		'<script type="text/javascript" src="'.APP_HTML.'/js/G_values.js?'.G_VALUES.'"></script>'.

		'<link rel="stylesheet" type="text/css" href="'.APP_HTML.'/css/main'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" />'.
		'<script type="text/javascript" src="'.APP_HTML.'/js/main'.(DEBUG ? '' : '.min').'.js?'.VERSION.'"></script>'.

		'</head>'.
		'<body>'.
		'<div id="frameBody">'.
			'<iframe id="frameHidden" name="frameHidden"></iframe>';
}//_header()

function GvaluesCreate() {//����������� ����� G_values.js
	$save = 'function _toAss(s){var a=[];for(var n=0;n<s.length;a[s[n].uid]=s[n].title,n++);return a}'.
		"\n".'var CATEGORY_SPISOK='.query_selJson("SELECT `id`,`name` FROM `louvers_setup_category` ORDER BY `sort` ASC").','.
		"\n".'CATEGORY_SUB_SPISOK='.Gvalues_obj('louvers_setup_category_sub').','.
		"\n".'FEATURE_SPISOK='._selJson(_feature()).','.
		"\n".'MEASURE_SPISOK=[{uid:1,title:"��"},{uid:2,title:"�"}];';

	$fp = fopen(APP_PATH.'/js/G_values.js', 'w+');
	fwrite($fp, $save);
	fclose($fp);

	query("UPDATE `setup_global` SET `g_values`=`g_values`+1");
	xcache_unset(CACHE_PREFIX.'setup_global');
}//GvaluesCreate()
function Gvalues_obj($table, $category_id='category_id') {//������������� ������ ������������
	$sql = "SELECT * FROM `".$table."` ORDER BY `".$category_id."`,`name`";
	$q = query($sql);
	$sub = array();
	while($r = mysql_fetch_assoc($q)) {
		if(!isset($sub[$r[$category_id]]))
			$sub[$r[$category_id]] = array();
		$sub[$r[$category_id]][] = '{uid:'.$r['id'].',title:"'.$r['name'].'"}';
	}
	$v = array();
	foreach($sub as $n => $sp)
		$v[] = $n.':['.implode(',', $sp).']';
	return '{'.implode(',', $v).'}';
}

function _statusColor($id) {
	$arr = array(
		'0' => 'ffffff',
		'1' => 'E8E8FF',
		'2' => 'CCFFCC',
		'3' => 'FFDDDD'
	);
	return $arr[$id];
}//_statusColor()
function _category($category_id=false) {//������ ��������� �������
	if(!defined('CATEGORY_LOADED') || $category_id === false) {
		$key = CACHE_PREFIX.'category';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT `id`,`name` FROM `louvers_setup_category` ORDER BY `name`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = $r['name'];
			xcache_set($key, $arr, 86400);
		}
		if(!defined('CATEGORY_LOADED')) {
			foreach($arr as $id => $name)
				define('CATEGORY_'.$id, $name);
			define('CATEGORY_0', '');
			define('CATEGORY_LOADED', true);
		}
	}
	return $category_id !== false ? constant('CATEGORY_'.$category_id) : $arr;
}//_category()
function _categorySub($category_id=false) {//������ ������� ��� ������
	if(!defined('CATEGORY_SUB_LOADED') || $category_id === false) {
		$key = CACHE_PREFIX.'category_sub';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT `id`,`name` FROM `louvers_setup_category_sub` ORDER BY `category_id`,`name`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = $r['name'];
			xcache_set($key, $arr, 86400);
		}
		if(!defined('CATEGORY_SUB_LOADED')) {
			foreach($arr as $id => $name)
				define('CATEGORY_SUB_'.$id, $name);
			define('CATEGORY_SUB_0', '');
			define('CATEGORY_SUB_LOADED', true);
		}
	}
	return $category_id !== false ? constant('CATEGORY_SUB_'.$category_id) : $arr;
}//_categorySub()
function _feature($id=0) {
	$arr = array(
		1 => '�����',
		2 => '�����',
		3 => '�������',
		4 => '�������'
	);
	return $id ? $arr[$id] : $arr;
}//_feature()

function _clientLink($arr, $fio=0, $tel=0) {//���������� ����� � ������ ������� � ������ ��� ������� �� id
	$clientArr = array(is_array($arr) ? 0 : $arr);
	$ass = array();
	if(is_array($arr)) {
		foreach($arr as $r)
			if(!empty($r['client_id'])) {
				$clientArr[$r['client_id']] = $r['client_id'];
				$ass[$r['client_id']][] = $r['id'];
			}
		unset($clientArr[0]);
	}
	if(!empty($clientArr)) {
		$sql = "SELECT *
		        FROM `louvers_client`
				WHERE `id` IN (".implode(',', $clientArr).")";
		$q = query($sql);
		if(!is_array($arr)) {
			if($r = mysql_fetch_assoc($q))
				return $fio ? $r['fio'] : '<a href="'.URL.'&p=client&d=info&id='.$r['id'].'" class="'.($r['deleted'] ? ' deleted' : '').($tel && $r['telefon'] ? _tooltip($r['telefon'], -2, 'l') : '">').$r['fio'].'</a>';
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id) {
				$arr[$id]['client_link'] = '<a href="'.URL.'&p=client&d=info&id='.$r['id'].'" class="'.($r['deleted'] ? ' deleted' : '').($tel && $r['telefon'] ? _tooltip($r['telefon'], -2, 'l') : '">').$r['fio'].'</a>';
				$arr[$id]['client_fio'] = $r['fio'];
				$arr[$id]['client_tel'] = $r['telefon'];
			}
	}
	return $arr;
}//_clientLink()
function clientBalansUpdate($client_id) {//���������� ������� �������
//	$prihod = query_value("SELECT SUM(`sum`) FROM `money` WHERE !`deleted` AND `client_id`=".$client_id);
//	$acc = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE !`deleted` AND `client_id`=".$client_id);
//	$balans = $prihod - $acc;
//	query("UPDATE `client` SET `balans`=".$balans." WHERE `id`=".$client_id);
	$balans = 0;
	query("UPDATE `client` SET `balans`=".$balans." WHERE `id`=".$client_id);
	return $balans;
}//clientBalansUpdate()

function clientFilter($v) {
	$default = array(
		'page' => 1,
		'find' => ''
	);
	$filter = array(
		'page' => _isnum(@$v['page']) ? $v['page'] : 1,
		'find' => trim(@$v['find']),
		'clear' => ''
	);
	foreach($default as $k => $r)
		if($r != $filter[$k]) {
			$filter['clear'] = '<a id="filter_clear">�������� ������</a>';
			break;
		}
	return $filter;
}//clientFilter()
function client_data($filter=array()) {
	$filter = clientFilter($filter);
	$cond = "!`deleted`";
	$reg = '';
	$regEngRus = '';
	if($filter['find']) {
		$engRus = _engRusChar($filter['find']);
		$cond .= " AND (`fio` LIKE '%".$filter['find']."%'
					 OR `telefon` LIKE '%".$filter['find']."%'
					 OR `adres` LIKE '%".$filter['find']."%'
					 ".($engRus ?
				"OR `fio` LIKE '%".$engRus."%'
						OR `telefon` LIKE '%".$engRus."%'
						OR `adres` LIKE '%".$engRus."%'"
				: '')."
					 )";
		$reg = '/('.$filter['find'].')/i';
		if($engRus)
			$regEngRus = '/('.$engRus.')/i';
	} else {

	}

	$all = query_value("SELECT COUNT(`id`) AS `all` FROM `louvers_client` WHERE ".$cond." LIMIT 1");
	if(!$all)
		return array(
			'all' => 0,
			'result' => '�������� �� �������.'.$filter['clear'],
			'spisok' => '<div class="_empty">�������� �� �������.</div>',
			'filter' => $filter
		);

	$page = $filter['page'];
	$limit = 20;
	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT *
			FROM `louvers_client`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$spisok[$r['id']] = $r;
		unset($spisok[$r['id']]['adres']);
		if(!empty($filter['find'])) {
			if(preg_match($reg, $r['fio']))
				$spisok[$r['id']]['fio'] = preg_replace($reg, '<em>\\1</em>', $r['fio'], 1);
			if(preg_match($reg, $r['telefon']))
				$spisok[$r['id']]['telefon'] = preg_replace($reg, '<em>\\1</em>', $r['telefon'], 1);
			if(preg_match($reg, $r['adres']))
				$spisok[$r['id']]['adres'] = preg_replace($reg, '<em>\\1</em>', $r['adres'], 1);
			if($regEngRus && preg_match($regEngRus, $r['fio']))
				$spisok[$r['id']]['fio'] = preg_replace($regEngRus, '<em>\\1</em>', $r['fio'], 1);
			if($regEngRus && preg_match($regEngRus, $r['telefon']))
				$spisok[$r['id']]['telefon'] = preg_replace($regEngRus, '<em>\\1</em>', $r['telefon'], 1);
			if($regEngRus && preg_match($regEngRus, $r['adres']))
				$spisok[$r['id']]['adres'] = preg_replace($regEngRus, '<em>\\1</em>', $r['adres'], 1);
		}
	}
/*
	$sql = "SELECT
				`client_id` AS `id`,
				COUNT(`id`) AS `count`
			FROM `zayav`
			WHERE !`deleted`
			  AND `client_id` IN (".implode(',', array_keys($spisok)).")
			GROUP BY `client_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['zayav_count'] = $r['count'];
*/
	$send = array(
		'all' => $all,
		'spisok' => '',
		'result' => '������'._end($all, ' ', '� ').$all.' ������'._end($all, '', '�', '��').
			$filter['clear'],
		'filter' => $filter
	);
	foreach($spisok as $r)
		$send['spisok'] .=
			'<div class="unit'.(isset($r['comm']) ? ' i' : '').'">'.
		($r['balans'] != 0 ?
				'<div class="balans">������: <b style=color:#'.($r['balans'] < 0 ? 'A00' : '090').'>'.round($r['balans'], 2).'</b></div>'
		: '').
				'<table>'.
					'<tr><td class="label">�����������:<td><a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['org'].'</a>'.
   ($r['telefon'] ? '<tr><td class="label">�������:<td>'.$r['telefon'] : '').
(isset($r['adres']) ? '<tr><td class="label">�����:<td>'.$r['adres'] : '').
				'</table>'.
			'</div>';
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="_next" val="'.($page + 1).'"><span>�������� ��� '.$c.' ������'._end($c, '�', '�', '��').'</span></div>';
	}
	return $send;
}//client_data()
function client_list($v) {
	$data = client_data($v);
	$v = $data['filter'];
	return
	'<div id="client">'.
		'<div id="find"></div>'.
		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate"><a>����� ������</a></div>'.
		'</table>'.
	'</div>'.
	'<script type="text/javascript">'.
		'var C={'.
			'find:"'.$v['find'].'"'.
		'};'.
	'</script>';
}//client_list()

function clientInfoGet($client) {
	return
		($client['deleted'] ? '<div class="_info">������ �����</div>' : '').
		'<div class="org">'.$client['org'].'</div>'.
		'<table class="tab">'.
			'<tr><td class="label">���������� ����:<td>'.$client['fio'].
			'<tr><td class="label">�������:<td>'.$client['telefon'].
			'<tr><td class="label">�����:  <td>'.$client['adres'].
			'<tr><td class="label">������: <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.round($client['balans'], 2).'</b>'.
		'</table>'.
		'<div class="dtime_add">������� ��'.(_viewer($client['viewer_id_add'], 'sex') == 1 ? '����' : '��').' '
			._viewer($client['viewer_id_add'], 'name').' '.
			FullData($client['dtime_add'], 1).
		'</div>';
}
function client_info() {
	$client_id = _isnum(@$_GET['id']);
	if(!$client_id)
		return _noauth('������� �� ����������');

	$sql = "SELECT * FROM `louvers_client` WHERE `id`=".$client_id;
	if(!$client = query_assoc($sql))
		return _noauth('������� �� ����������');

	if(!SA && $client['deleted'])
		return _noauth('������ �����');

	return
		'<script type="text/javascript">'.
			'var CLIENT={'.
				'id:'.$client_id.','.
				'org:"'.$client['org'].'",'.
				'fio:"'.$client['fio'].'",'.
				'telefon:"'.$client['telefon'].'",'.
				'adres:"'.$client['adres'].'"'.
			'};'.
		'</script>'.
		'<div id="client-info">'.
			'<table class="tabLR">'.
				'<tr><td class="left">'.clientInfoGet($client).
					'<td class="right">'.
						'<div class="rightLink">'.
						(!$client['deleted'] ?
							'<a class="zayav_add"><b>����� ������</b></a>'.
							'<a class="cedit">�������������</a>'.
							'<a class="cdel">������� �������</a>'
						: '').
						'</div>'.
			'</table>'.
		'</div>';
}//client_info()


function zayav_product_test($product) {// �������� ������������ ������ ������� ��� �������� � ����
	if(empty($product))
		return false;
	$send = array();
	foreach(explode(';', $product) as $r) {
		$i = explode(':', $r);
		if(!_isnum($i[0]))
			return false;
		if(!preg_match(REGEXP_NUMERIC, $i[1]))
			return false;
		if(!$i[2] = _isnum($i[2]))
			return false;
		if(!$i[3] = _isnum($i[3]))
			return false;
		if(!preg_match(REGEXP_NUMERIC, $i[4]))
			return false;
		if(!preg_match(REGEXP_NUMERIC, $i[5]))
			return false;
		if(!_isnum($i[6]))
			return false;
		$send[] = $i;
	}
	return empty($send) ? false : $send;
}//zayav_product_test()
function zayav_product_spisok($zayav) {
	$sql = "SELECT * FROM `louvers_zayav_product` WHERE `zayav_id` IN (".implode(',', array_keys($zayav)).") ORDER BY `zayav_id`,`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(empty($zayav[$r['zayav_id']]['product_ids']))
			$zayav[$r['zayav_id']]['product_ids'] = array();
		$zayav[$r['zayav_id']]['product_ids'][] = array(
			'category_id' => $r['category_id'],
			'category_sub_id' => $r['category_sub_id'],
			'count' => $r['count']
		);
	}
	foreach($zayav as $id => $z) {
		$tab = '';
		$json = array();
		foreach($z['product_ids'] as $r) {
			$tab .=
				'<tr><td>'._category($r['category_id']) .
						($r['category_sub_id'] ? ' '._categorySub($r['category_sub_id']) : '').':' .
					'<td>'.$r['count'].' ��.';
			$json[] = $r['id'].','.
					  $r['category_id'].','.
					  $r['category_sub_id'].','.
					  $r['size_x'].','.
					  $r['size_y'].','.
					  $r['cloth_name'].','.
					  $r['cloth_color'].','.
					  $r['count'];
		}
		$zayav[$id]['product'] = '<table class="product">'.$tab.'</table>';
		$zayav[$id]['json'] = implode(',', $json);
	}

	return $zayav;
}//zayav_product_spisok()
function zayav() {
	$data = zayav_spisok();
	return
	'<div id="zayav">'.
		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate" class="zayav_add"><a>����� ������</a></div>'.
		'</table>'.
	'</div>';
}//zayav()
function zayavFilter($v) {
	return array(
		'page' => _isnum(@$v['page']) ? $v['page'] : 1,
		'limit' => _isnum(@$v['limit']) ? $v['limit'] : 20,
		'client_id' => _isnum(@$v['client']),
		'status' => _isnum(@$v['status'])
	);
}//zayavFilter()
function zayav_spisok($v=array()) {
	$filter = zayavFilter($v);

	$cond = "!`deleted`";

	if($filter['client_id'])
		$cond .= " AND `client_id`=".$filter['client_id'];

	$clear = '<a class="filter_clear">������� ������� ������</a>';
	$send['all'] = query_value("SELECT COUNT(`id`) AS `all` FROM `louvers_zayav` WHERE ".$cond." LIMIT 1");
	if($send['all'] == 0)
		return array(
			'all' => 0,
			'result' => $clear.'������ �� �������',
			'spisok' => '<div class="_empty">������ �� �������.</div>'
		);

	$send['result'] = $clear.'�������'._end($send['all'], '�', '�').' '.$send['all'].' ����'._end($send['all'], '��', '��', '��');

	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `louvers_zayav`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['id']] = $r;

	$zayav = _clientLink($zayav, 0, 1);
	$zayav = zayav_product_spisok($zayav);

	$send['spisok'] = '';
	foreach($zayav as $r) {
		unset($r['client_tel']);
		$send['spisok'] .=
			'<div class="zayav_unit" style="background-color:#'._statusColor($r['status']).'" val="'.$r['id'].'">'.
				($r['deleted'] ? '<div class="zdel">������ �������</div>' : '').
				'<div class="dtime">'.
					'#'.(isset($r['find_id']) ? $r['find_id'] : $r['id']).'<br />'.
					FullData($r['dtime_add'], 1).
				'</div>'.
				'<a class="name">������ �'.$r['id'].'</a>'.
				'<table class="ztab">'.
					'<tr><td class="label">������:<td>'.$r['client_link'].
					'<tr><td class="label top">�������:<td>'.$r['product'].
				'</table>'.
			'</div>';
	}

	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<div class="_next" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' ����'._end($c, '��', '��', '��').'</span>'.
			'</div>';
	}

	return $send;
}//zayav_spisok()


function zayav_info($zayav_id) {
	$sql = "SELECT * FROM `louvers_zayav` WHERE `id`=".$zayav_id." LIMIT 1";
	if(!$z = mysql_fetch_assoc(query($sql)))
		return _noauth('������ �� ����������.');

	$client = query_assoc("SELECT * FROM `louvers_client` WHERE !`deleted` AND `id`=".$z['client_id']);

	$product = zayav_product_spisok(array($zayav_id => $z));
	$z = $product[$zayav_id];

	return
		'<script type="text/javascript">'.
			'var ZAYAV={'.
				'id:'.$zayav_id.','.
				'client_fio:"'.$client['fio'].'",'.
				'product:['.zayav_product_spisok($z['id'], 'json').']'.
			'};'.
		'</script>'.

		'<div class="zayav-info">'.
		'<div id="dopLinks">'.
			'<a class="link sel zinfo">����������</a>'.
		(!$z['deleted'] ?
			'<a class="link edit">��������������</a>'.
			'<a class="link acc-add">���������</a>'.
			'<a class="link income-add">������ �����</a>'.
			'<a class="delete">������� ������</a>'
		: '').
		'</div>'.
		($z['deleted'] ? '<div class="_info">������ ������</div>' : '').
		'<div class="content">'.
			'<TABLE class="tabmain"><TR>'.
				'<TD class="mainleft">'.
					'<div class="headName">������ #'.$zayav_id.'</div>'.
					'<table class="tabInfo">'.
						'<tr><td class="label">������:<td>'._clientLink($z['client_id'], 0, 1).
						'<tr><td class="label top">�������:<td>'.$z['product'].
						'<tr><td class="label">������:'.
							'<td><div style="background-color:#'._statusColor($z['status']).'" class="status">'.
									'������'.
								'</div>'.
					'</table>'.
			'</TABLE>'.

		'<div class="dtime_add">������ ��'.(_viewer($z['viewer_id_add'], 'sex') == 1 ? '����' : '��').' '.
			_viewer($z['viewer_id_add'], 'name').' '.
			FullDataTime($z['dtime_add']).
		'</div>'.

		_vkComment('louvers_zayav', $z['id']).
		'</div>'.
	'</div>';
}//zayav_info()



function stockFilter($v) {
	return array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? intval($v['page']) : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? intval($v['limit']) : 100,
		'sort' => !empty($v['sort']) && preg_match(REGEXP_NUMERIC, $v['sort']) ? intval($v['sort']) : 0,
		'find' => !empty($v['find']) ? trim($v['find']) : ''
	);
}//stockFilter()
function stock_spisok($v=array()) {
	$filter = stockFilter($v);
	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`c`.`id`";

	if(!empty($filter['find'])) {
		$engRus = _engRusChar($filter['find']);
		$cond .= " AND (`name` LIKE '%".$filter['find']."%' OR `name` LIKE '%".$engRus."%')";
		$reg = '/('.$filter['find'].')/i';
	}
	$sort = "`c`.`id` DESC";

	$all = query_value("SELECT COUNT(`c`.`id`) FROM `louvers_stock` `c` WHERE ".$cond);
	if(!$all)
		return array(
			'all' => 0,
			'result' => '������������� �� �������',
			'spisok' => '<div class="_empty">������������� �� �������</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => '�������'._end($all, '� ', '� ').$all.' ������'._end($all, '�', '�', '�'),
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT
	            `c`.*
			FROM `louvers_stock` `c`
			WHERE ".$cond."
			ORDER BY ".$sort."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(!empty($filter['find'])) {
			if(preg_match($reg, $r['name']))
				$r['name'] = preg_replace($reg, '<em>\\1</em>', $r['name'], 1);
		}
		$spisok[$r['id']] = $r;
	}

	$send['spisok'] =
		$page == 1 ?
			'<table class="_spisok _money">'.
				'<tr><th>������������'.
					'<th>���.'
			: '';
	foreach($spisok as $id => $r) {
		$send['spisok'] .=
			'<tr val="'.$id.'">'.
				'<td><span class="type">:</span> '.
					'<a href="'.URL.'&p=stock&d=info&id='.$id.'" class="name">'.$r['name'].'</a>'.
				'<td class="avai">';
	}

	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'">'.
			'<td colspan="4">'.
				'<span>�������� ��� '.$c.' ������'._end($c, '�', '�', '�').'</span>';
	}

	$send['spisok'] .= $page == 1 ?  '</table>' : '';

	return $send;
}//stock_spisok()
function stock() {
	$data = stock_spisok();
	$filter = $data['filter'];
	return
	'<div id="stock">'.
		'<div id="stock-head">'.
			'<table id="head-t"><tr>'.
				'<td id="td-find"><div id="find"></div>'.
				'<td><div class="vkButton"><button>������ ����� �������</button></div>'.
			'</table>'.
		'</div>'.
		'<div class="result">'.$data['result'].'</div>'.
		'<div id="spisok">'.$data['spisok'].'</div>'.
	'</div>'.
	'<script type="text/javascript">'.
		'var STOCK={'.
			'find:""'.
		'};'.
	'</script>';
}//stock()

function stock_info() {
	if(!$id = _isnum(@$_GET['id']))
		return _noauth('�������� �� ����������');

	$sql = "SELECT * FROM `louvers_stock` WHERE `id`=".$id;
	if(!$si = query_assoc($sql))
		return _noauth('�������� �� ����������');
	_pre($si);
	$avai = 0;
	return
	'<div id="stock-info">'.
		'<table class="si-tab">'.
			'<tr><td class="left">'.
					'<div class="name">'.'</div>'.
					'<div class="avai'.($avai ? '' : ' no').'">'.($avai ? '� ������� '.$avai.' ��.' : '��� � �������.').'</div>'.
					'<div class="added">��������� � ������� '.FullData($si['dtime_add'], 1).'</div>'.
					'<div class="headBlue">��������</div>'.
					'<div class="move">'.'</div>'.
				'<td class="right">'.
					'<div id="foto">'.

					'</div>'.
					'<div class="rightLink">'.
						'<a class="edit">�������������</a>'.
						'<a class="avai_add">������ �������</a>'.
					'</div>'.
		'</table>'.
	'</div>';

}//stock_info()

function report() {
	return 'report';
}//report()



// ---===! setup !===--- ������ ��������

function setup() {
	$pages = array(
		'category' => '��������� �������'
	);

	$d = empty($_GET['d']) ? 'category' : $_GET['d'];

	switch($d) {
		default: $d = 'category';
		case 'category': $left = setup_category(); break;
		case 'categorysub':
			$d = 'category';
			if($id = _isnum(@$_GET['id']))
				$left = setup_category_sub($id);
			else $left = '�������� id.';
			break;
		case 'feature':
			$d = 'category';
			if($id = _isnum(@$_GET['id']))
				$left = setup_feature($id);
			else $left = '�������� id.';
			break;
	}
	$links = '';
	foreach($pages as $p => $name)
		$links .= '<a href="'.URL.'&p=setup&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';
	return
		'<div id="setup">'.
			'<table class="tabLR">'.
				'<tr><td class="left">'.$left.
					'<td class="right"><div class="rightLink">'.$links.'</div>'.
			'</table>'.
		'</div>';
}//setup()

function setup_category() {
	return
	'<div id="setup_category">'.
		'<div class="headName">��������� ��������� �������<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_category_spisok().'</div>'.
	'</div>';
}//setup_category()
function setup_category_spisok() {
	$sql = "SELECT `c`.*,
				   COUNT(`cs`.`id`) AS `sub`
			FROM `louvers_setup_category` AS `c`
			  LEFT JOIN `louvers_setup_category_sub` AS `cs`
			  ON `c`.`id`=`cs`.`category_id`
			GROUP BY `c`.`id`
			ORDER BY `c`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$income = array();
	while($r = mysql_fetch_assoc($q))
		$income[$r['id']] = $r;

	$send =
		'<table class="_spisok">'.
			'<tr><th class="name">������������'.
				'<th class="sub">������������'.
				'<th class="set">'.
		'</table>'.
		'<dl class="_sort" val="louvers_setup_category">';
	foreach($income as $id => $r) {
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name"><a href="'.URL.'&p=setup&d=categorysub&id='.$id.'">'.$r['name'].'</a>'.
					'<td class="sub">'.($r['sub'] ? $r['sub'] : '').
					'<td class="set">'.
						'<div class="img_edit'._tooltip('��������', -33).'</div>'.
						'<div class="img_del'._tooltip('�������', -29).'</div>'.
			'</table>';
	}
	$send .= '</dl>';
	return $send;
}//setup_category_spisok()

function setup_category_sub($id) {
	$sql = "SELECT * FROM `louvers_setup_category` WHERE `id`=".$id;
	if(!$r = query_assoc($sql))
		return '������������ �� ����������.';

	return
	'<script type="text/javascript">var CATEGORY_ID='.$id.';</script>'.
	'<div id="setup_categorysub">'.
		'<a class="back" href="'.URL.'&p=setup&d=category"><< ����� � ���������� �������</a>'.
		'<div class="headName">'.$r['name'].'<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_category_sub_spisok($id).'</div>'.
	'</div>';
}//setup_categorysub()
function setup_category_sub_spisok($id) {
	$sql = "SELECT *
			FROM `louvers_setup_category_sub`
			WHERE `category_id`=".$id."
			ORDER BY `name`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$send = '<table class="_spisok">'.
		'<tr><th>������������'.
			'<th>���-��<br />������'.
			'<th>';
	while($r = mysql_fetch_assoc($q))
		$send .= '<tr val="'.$r['id'].'">'.
			'<td class="name"><a href="'.URL.'&p=setup&d=feature&id='.$r['id'].'">'.$r['name'].'</a>'.
			'<td class="zayav">'.
			'<td><div class="img_edit"></div>'.
				'<div class="img_del"></div>';
	$send .= '</table>';
	return $send;
}//setup_categorysub_spisok()

function setup_feature($id) {
	$sql = "SELECT * FROM `louvers_setup_category_sub` WHERE `id`=".$id;
	if(!$sub = query_assoc($sql))
		return '������������ �� ����������.';

	$sql = "SELECT * FROM `louvers_setup_category` WHERE `id`=".$sub['category_id'];
	if(!$cat = query_assoc($sql))
		return '��������� �� ����������.';

	return
	'<script type="text/javascript">var CATEGORY_SUB_ID='.$id.';</script>'.
	'<div id="setup_feature">'.
		'<a class="back" href="'.URL.'&p=setup&d=categorysub&id='.$cat['id'].'"><< ����� � <b>'.$cat['name'].'</b></a>'.
		'<div class="headName">'.$cat['name'].' - '.$sub['name'].'</div>'.
		'<div class="kod_head">�������������� ��������������:<a class="add" id="feature_add">��������</a></div>'.
		'<div id="feature_spisok">'.setup_feature_spisok($id).'</div>'.
	'</div>';
}//setup_feature()
function setup_feature_spisok($id) {
	$sql = "SELECT *
			FROM `louvers_setup_feature`
			WHERE `category_sub_id`=".$id."
			ORDER BY `name_id`,`name`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '<em>�� ����������.</em>';
	$feature = array();
	while($r = mysql_fetch_assoc($q)) {
		$r['color'] = array();
		$feature[$r['id']] = $r;
	}

	$sql = "SELECT *
			FROM `louvers_setup_feature_color`
			WHERE `feature_id` IN (".(implode(',', array_keys($feature))).")
			ORDER BY `feature_id`,`name`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$feature[$r['feature_id']]['color'][] =
			'<div class="color">'.
				'<input type="hidden" id="color_name_'.$r['id'].'" value="'.addslashes($r['name']).'" />'.
				'<input type="hidden" id="color_kod_'.$r['id'].'" value="'.addslashes($r['kod']).'" />'.
				'<span>'.$r['name'].'</span>���: '.$r['kod'].
				'<a class="color_edit" val="'.$r['id'].'">��������</a>'.
				'<a class="color_del" val="'.$r['id'].'">�������</a>'.
			'</div>';

	$send = '<table class="_spisok">';
	foreach($feature as $r)
		$send .=
			'<input type="hidden" id="name_id_'.$r['id'].'" value="'.$r['name_id'].'" />'.
			'<input type="hidden" id="name_'.$r['id'].'" value="'.addslashes($r['name']).'" />'.
			'<input type="hidden" id="kod_'.$r['id'].'" value="'.$r['kod'].'" />'.
			'<tr val="'.$r['id'].'">'.
				'<td class="name">'.
					'<u>'._feature($r['name_id']).'</u><b>'.$r['name'].'</b>���: '.$r['kod'].
					'<a class="color_add">+ ����</a>'.
					implode('', $r['color']).
				'<td class="edit">'.
					'<div class="img_edit'._tooltip('��������', -32).'</div>'.
					(empty($r['color']) ? '<div class="img_del'._tooltip('�������', -29).'</div>' : '');
	$send .= '</table>';
	return $send;
}//setup_feature_spisok()
