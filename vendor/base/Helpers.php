<?php
namespace vendor\base;

class Helpers
{
	public static function array_merge(&$array, $key, $attach_array, $attach_key, $rewrite = false)
	{
		$array = (array)$array;
		$key = (string)$key;
		$attach_array = (array)$attach_array;
		$attach_key = (string)$attach_key;
		
		if (!$array || !$key || !$attach_array || !$attach_key) {
			return;
		}
		
		$tmp = [];
		foreach ($attach_array as $v) {
			if (key_exists($attach_key, $v)) {
				$tmp[$v[$attach_key]] = $v;
			}
		}
		foreach ($array as &$v) {
			if (key_exists($key, $v) && isset($tmp[$v[$key]])) {
				if ($rewrite) {
					$v = $tmp[$v[$key]] + $v;
				} else {
					$v += $tmp[$v[$key]];
				}
			}
		}
	}
	
	public static function array_keys($array, $castToStr = true)
	{
		$keys = [];
		foreach ($array as $k => $v) {
			$keys[] = $castToStr ? (string)$k : $k;
		}
		return $keys;
	}
	
	public static function array_rewrite(&$array1, $array2)
	{
		$rewrite = array_intersect_key($array2, $array1);
		foreach ($rewrite as $k => $v) {
			$array1[$k] = $v;
		}
	}


    /**
     * @param array $array       循环的数组
     * @param array $add_array   要加入的数组
     * @param string $where_key   根据什么字段判断
     * @param string $del_key     删除指定的key
     * @param array $default     默认key
     * @return null
     */
    public static function array_set_col(&$array, $add_array = [], $where_key, $del_key = null, $default = [])
    {

        foreach ($array as $key => &$val) {

            $where_val = $val[$where_key];

            if (!$where_val || !isset($add_array[$where_val])) {
                // 配置默认key
                $val += $default;
                continue;
            }

            $info = $add_array[$where_val];

            if (is_array($del_key)) {
                foreach ($del_key as $item) {
                    unset($info[$item]);
                }
            } else {
                unset($info[$del_key]);
            }

            $val += $info;
        }
    }

    /**
     *  是否采用覆盖模式
     * @param $rows
     * @param $col
     * @return array
     */
	public static function array_index($rows, $col)
	{
		return array_column($rows, null, $col);
	}
	
	public static function array_assemble($rows, $col, $keepKey = false)
	{
		$res = [];
		foreach ($rows as $k => $row) {
			if ($keepKey) {
				$res[$row[$col]][$k] = $row;
			} else {
				$res[$row[$col]][] = $row;
			}
		}
		return $res;
	}
	
	public static function array_diff_assoc($array1, $array2)
	{
		foreach ($array1 as $k => $v) {
			if (key_exists($k, $array2) && $array2[$k] === $v) {
				unset($array1[$k]);
			}
		}
		return $array1;
	}
	
	public static function when($when, $vals)
	{
		//and =
		if (isset($when['when'])) {
			foreach ($when['when'] as $f => $tv) {
				if (!key_exists($f, $vals) || $vals[$f] != $tv) {
					return false;
				}
			}
		}
		//or <>
		if (isset($when['!when'])) {
			foreach ($when['!when'] as $f => $tv) {
				if (key_exists($f, $vals) && $vals[$f] == $tv) {
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * @return int the number of bytes in the given string.
	 */
	public static function byteLen($string)
	{
		return mb_strlen($string, '8bit');
	}
	
	/**
	 * @return string the extracted part of string, or FALSE on failure or an empty string.
	 * @see http://www.php.net/manual/en/function.substr.php
	 */
	public static function byteSubstr($string, $start, $length = null)
	{
		return mb_substr($string, $start, $length === null ? mb_strlen($string, '8bit') : $length, '8bit');
	}
	
	public static function mb_rtrim($str, $chars)
	{
		if (!$chars) {
			return $str;
		}
		
		$len = mb_strlen($chars);
		$charArr = [];
		for ($i = 0; $i < $len; $i++) {
			$charArr[] = mb_substr($chars, $i, 1);
		}
		
		$len = mb_strlen($str);
		while ($len) {
			$len--;
			$char = mb_substr($str, $len);
			if (in_array($char, $charArr)) {
				$str = mb_substr($str, 0, $len);
			} else {
				break;
			}
		}
		return $str;
	}

    public static function HttpCurl($url, $params = [], $method = 'GET')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        } elseif ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        } else if ($method == 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        }
        if (!empty($params)) {
            $params = json_encode($params, 256);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Content-Length:' . strlen($params)]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        $res = curl_exec($ch);
//        if ($res === null) {
////            var_dump(curl_error($ch));
////        }
        curl_close($ch);

        return $res ? json_decode($res, true) : null;
    }

    public static function create_uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr ( $chars, 0, 8 ) . '-'
            . substr ( $chars, 8, 4 ) . '-'
            . substr ( $chars, 12, 4 ) . '-'
            . substr ( $chars, 16, 4 ) . '-'
            . substr ( $chars, 20, 12 );
        return $prefix.$uuid ;
    }
}