<?php

set_sgs(array("get" => $_GET, "post" => $_POST, "request" => $_REQUEST, "cookie" => $_COOKIE, "server" => $_SERVER));

function set_sgs($arr)
{
	foreach($arr as $sg=>$superglobal)
	{
		global $http_reqarrs;
		global $_globals;
		$pairs = array();
		foreach($superglobal as $k=>$v)
		{
			if(is_array($v))
			{
				$val = array();
				foreach($v as $_k=>$_v)
				{
					$str = F_String::__from_string($_v);
					$str->F_taint(NULL);
					$val[] = F_Array::__from_array(array(F_Symbol::__from_string($_k), $str));
				}
			}
			else
			{
				$val = F_String::__from_string($v);
				$val->F_taint(NULL);
			}
			$pairs[] = F_Array::__from_array(array(F_Symbol::__from_string($k), $val));
		}
		$_globals['F_' . $sg] = F_Hash::__from_pairs($pairs);
	}
}

class F_HttpSession extends F_Object
{
	public function __operator_arrayget($block, $key)
	{
		@session_start();
		$k = $key->F_to_s(NULL)->__STRING;
		if(!isset($_SESSION[$k]))
			return new F_NilClass;
		return $_SESSION[$k];
	}
	public function __operator_arrayset($block, $key, $val)
	{
		@session_start();
		$_SESSION[$key->F_to_s(NULL)->__STRING] = $val;
		return $val;
	}
	public function F_delete($block, $key)
	{
		@session_start();
		unset($_SESSION[$key->F_to_s(NULL)->__STRING]);
		return new F_NilClass;
	}
	public function F_regenerate_id($block)
	{
		return F_TrueClass::__from_bool(session_regenerate_id());
	}
	public function F_id($block)
	{
		if(session_id() === '')
			return new F_NilClass;
		return F_String::__from_string(session_id());
	}
	public function F_id__set($block, $val)
	{
		session_id($val->F_to_s(NULL)->__STRING);
		return $val;
	}
	public function F_destroy_EXCL_($block)
	{
		session_destroy();
		return new F_NilClass;
	}
}

$GLOBALS['_globals']['F_session'] = new F_HttpSession;
