<?php
require_once('config.php');

_hashRead();
_header();
_mainLinks();

switch($_GET['p']) {
	case 'client':
		switch(@$_GET['d']) {
			case 'info': $html .= client_info(); break;
			default:
				$v = array();
				if(HASH_VALUES) {
					$ex = explode('.', HASH_VALUES);
					foreach($ex as $r) {
						$arr = explode('=', $r);
						$v[$arr[0]] = $arr[1];
					}
				} else {
					foreach($_COOKIE as $k => $val) {
						$arr = explode('client_', $k);
						if(isset($arr[1]))
							$v[$arr[1]] = $val;
					}
				}
				$v['find'] = unescape(@$v['find']);
				$html .= client_list($v);
		}
		break;
	case 'zayav':
		break;
		switch(@$_GET['d']) {
			case 'add': $html .= zayav_add(); break;
			case 'info':
				if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
					$html .= '�������� �� ����������';
					break;
				}
				$html .= zayav_info(intval($_GET['id']));
				break;
			default:
				setcookie('zback_spisok', 1, time() + 3600, '/');
				$v = array();
				if(HASH_VALUES) {
					$ex = explode('.', HASH_VALUES);
					foreach($ex as $r) {
						$arr = explode('=', $r);
						$v[$arr[0]] = $arr[1];
					}
				} else {
					foreach($_COOKIE as $k => $val) {
						$arr = explode('zayav_', $k);
						if(isset($arr[1]))
							$v[$arr[1]] = $val;
					}
				}
				$v['find'] = unescape(@$v['find']);
				$html .= zayav_list($v);
		}
		break;
	case 'stock': $html .= stock(); break;
	case 'report': $html .= report(); break;
	case 'setup': $html .= setup(); break;

	default: header('Location:'.URL.'&p=zayav');
}

_footer();
mysql_close();
echo $html;
exit;