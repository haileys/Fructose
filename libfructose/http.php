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

function F_create_session($block)
{
	if (session_id() == '')
	{
		session_start();
	}
	
	set_sgs(array("session" => $_SESSION));
}

function F_destroy_session($block)
{
	if (session_id() != '')
	{
		session_unset();
		session_destroy();
	}
}

function F_set_session_var($block, $k, $v)
{
	$_SESSION[$k->toPHP()] = $v->toPHP();
}