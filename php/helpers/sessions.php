<?php
namespace is\Helpers;

class Sessions {

	static public function cookie($name, $set = false){
		
		/*
		*  Обобщенная функция регистрации/удаления куки
		*  на входе нужно указать имя куки
		*  второй необязательный параметр служит триггером, меняющим поведение функции
		*  
		*  false или не указан - стереть куки, если вместо имени передан массив, будут удалены все указанные в нем куки
		*  true - проверка значения, если задано то возвращает его, если не задано, возвращает false
		*  если указано любое, кроме false и true - присвоить это значение куки
		*  
		*  Функция удобна тем, что сразу присваивает значение куки, без необходимости перезагружать страницу,
		*  а также выполняет все необходимые проверки
		*/
		
		if (is_array($name)) {
			// un
			foreach ($name as $item) {
				setcookie($item, '', time() - 3600, '/');
				unset($_COOKIE[$item]);
			}
			unset($item);
		} elseif ($set === false) {
			// un
			setcookie($name, '', time() - 3600, '/');
			unset($_COOKIE[$name]);
		} elseif ($set === true) {
			// get
			return $name === true ? $_COOKIE : (!empty($_COOKIE[$name]) ? $_COOKIE[$name] : null);
		} else {
			// set
			setcookie($name, $set, 0, '/');
			$_COOKIE[$name] = $set;
		}
		
	}

	static public function setCookie($name, $set){
		
		/*
		*  Функция регистрации куки
		*  на входе нужно указать имя куки
		*  второе значение - присвоить это значение куки
		*/
		
		setcookie($name, $set, 0, '/');
		$_COOKIE[$name] = $set;
		
	}

	static public function getCookie($name){
		
		/*
		*  Функция проверки куки
		*  если задано то возвращает его, если не задано, возвращает false
		*/
		
		return $name === true ? $_COOKIE : (!empty($_COOKIE[$name]) ? $_COOKIE[$name] : null);
		
	}
	
	static public function unCookie($name){
		
		/*
		*  Функция удаления куки
		*  на входе нужно указать имя куки
		*  если вместо имени передан массив, будут удалены все указанные в нем куки
		*/
		
		if (is_array($name)) {
			foreach ($name as $item) {
				setcookie($item, '', time() - 3600, '/');
				unset($_COOKIE[$item]);
			}
			unset($item);
		} else {
			setcookie($name, '', time() - 3600, '/');
			unset($_COOKIE[$name]);
		}
		
	}


	static public function ipReal() {
		
		// функция получения реального ip-адреса посетителя
		
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		} elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}
		return $ip;
		
	}

	static public function ipRange($ip, $ip_base) {
		
		// функция проверки ip на присутствие в заданном диапазоне
		
		$ip = self::ipConvert($ip);
		if (empty($ip) || empty($ip_base) || !is_array($ip_base)) { return false; }
		
		foreach ($ip_base as $item) {
			
			if (strpos($item, '.') !== false && strpos($item, ':') === false) {
				$ip_type = '4';
			} elseif (strpos($item, '.') === false && strpos($item, ':') !== false) {
				$ip_type = '6';
			}
			
			if (strpos($item, '-') !== false) {
				$item = explode('-', $item);
				$ip_min = self::ipConvert($item[0], $ip_type);
				$ip_max = self::ipConvert($item[1], $ip_type);
			} elseif (strpos($item, '*') !== false) {
				$ip_min = self::ipConvert(str_replace('*', '0', $item), $ip_type);
				$ip_max = self::ipConvert(str_replace('*', '255', $item), $ip_type);
			} elseif (strpos($item, '/') !== false) {
				if ($ip_type == '4') {
					$item = self::ipConvertCIDR4($item);
					$ip_min = $item[0];
					$ip_max = $item[1];
				} elseif ($ip_type == '6') {
					$item = self::ipConvertCIDR6($item);
					$ip_min = $item[0];
					$ip_max = $item[1];				
				}
			} else {
				$ip_min = self::ipConvert($item, $ip_type);
				$ip_max = self::ipConvert($item, $ip_type);
			}
			
			if (
				$ip_min && $ip_max &&
				$ip_min <= $ip && $ip <= $ip_max
			) {
				return true;
			}
			
		}
		
		unset($item, $ip_base, $ip_type, $ip);
		return false;
		
	}

	static public function ipConvert($ip, $ip_type = null) {
		
		// служебная функция преобразования ip адреса
		
		$ip = trim($ip);
		
		if (preg_match('/00|\s/', $ip)) {
			$ip = preg_replace('/\s+/', '', $ip);
			$ip = preg_replace('/(\.|\:)(?=\.|\:)/', '$1_', $ip);
			$ip = preg_replace('/_/', '0', $ip);
			$ip = preg_replace('/(\.|\:|^)[0]{1,3}(\w)/', '$1$2', $ip);
		}
		
		if (!$ip_type) {
			if (strpos($ip, '.') !== false && strpos($ip, ':') === false) {
				$ip_type = 'A4';
			} elseif (strpos($ip, '.') === false && strpos($ip, ':') !== false) {
				$ip_type = 'A16';
			}
		} else {
			$ip_type = 'A' . ($ip_type == '6' ? '16' : '4');
		}
		
		if (!empty($ip_type)) {
			return current( unpack( $ip_type, inet_pton( $ip ) ) );
		} else {
			return false;
		}
		
	}

	static public function ipConvertCIDR4($ipv4) {
		
		// служебная функция преобразования ip адреса из формата cidr для ipv4
		
		if ($ip = strpos($ipv4,'/')) {
			$n_ip = (1<<(32-substr($ipv4,1+$ip)))-1;
			$ip_dec = ip2long(substr($ipv4,0,$ip));
		}
		
		$ip_min = $ip_dec &~ $n_ip;
		$ip_max = $ip_min + $n_ip;
		
		return [
			current( unpack( 'A4', inet_pton( long2ip($ip_min) ) ) ),
			current( unpack( 'A4', inet_pton( long2ip($ip_max) ) ) )
		];
		
	}

	static public function ipConvertCIDR6($ip) {
		
		// служебная функция преобразования ip адреса из формата cidr для ipv6
		
		if(!preg_match('~^([0-9a-f:]+)[[:punct:]]([0-9]+)$~i', trim($ip), $v_Slices)){
			return false;
		}
		if(!filter_var($v_FirstAddress = $v_Slices[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
			return false;
		}
		
		$v_PrefixLength = intval($v_Slices[2]);
		if($v_PrefixLength > 128){
			return false;
		}
		
		$v_SuffixLength = 128 - $v_PrefixLength;
		
		$v_FirstAddressBin = inet_pton($v_FirstAddress);
		
		$v_NetworkMaskHex = str_repeat('1', $v_PrefixLength) . str_repeat('0', $v_SuffixLength);
		$v_NetworkMaskHex_parts = str_split($v_NetworkMaskHex, 8);
		foreach($v_NetworkMaskHex_parts as &$v_NetworkMaskHex_part){
			$v_NetworkMaskHex_part = base_convert($v_NetworkMaskHex_part, 2, 16);
			$v_NetworkMaskHex_part = str_pad($v_NetworkMaskHex_part, 2, '0', STR_PAD_LEFT);
		}
		$v_NetworkMaskHex = implode(null, $v_NetworkMaskHex_parts);
		$v_NetworkMaskBin = inet_pton(implode(':', str_split($v_NetworkMaskHex, 4)));
		
		$v_LastAddressBin = $v_FirstAddressBin | ~$v_NetworkMaskBin;
		
		return [$v_FirstAddressBin, $v_LastAddressBin];
		
	}

}

?>