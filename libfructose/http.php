<?php

foreach(array("get" => $_GET, "post" => $_POST, "request" => $_REQUEST, "cookie" => $_COOKIE) as $sg=>$superglobal)
{
	global $http_reqarrs;
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