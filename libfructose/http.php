<?php

$http_reqarrs = array();

foreach(array("get", "post", "request", "cookie") as $sg)
{
	$superglobal = '$_' . strtoupper($sg);
	$pairs = array();
	foreach($$superglobal as $k=>$v)
	{
		if(is_array($v))
		{
			$val = array();
			foreach($v as $_k=>$_v)
				$val[] = F_Array::__from_array(array(F_Symbol::__from_symbol($_k), F_String::__from_string($_v)));
		}
		else
		{
			$val = F_String::__from_string($v);
		}
		$pairs[] = F_Array::__from_array(array(F_Symbol::__from_symbol($k)), $val);
	}
	$http_reqarrs[$sg] = F_Hash::__from_pairs($pairs);
	F_Object::__add_global_method('F_' . $sg, create_function('', 'global $http_reqarrs; return $http_reqarrs["' . $sg . '"];'));
}