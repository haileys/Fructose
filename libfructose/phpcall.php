<?php

class F_PHPObject
{
	public static function __from_object($obj)
	{
		$phpobj = new F_PHPObject;
		$phpobj->__OBJECT = $obj;
		return $phpobj;
	}
	public function F_call($block, $sym)
	{
		$frucargs = array_slice(func_get_args(), 2);
		$phpargs = array();
		foreach($frucargs as $frucarg)
			$phpargs[] = __marshal2php($frucarg);
		return __marshal2fruc(call_user_func_array(array($this->__OBJECT, $sym->__SYMBOL), $phpargs));
	}
	public function F_attr($block, $sym)
	{
		$attrname = $sym->__SYMBOL;
		return __marshal2fruc($this->__OBJECT->$attrname);
	}
	public function F_attr_set($block, $sym, $val)
	{
		$attrname = $sym->__SYMBOL;
		$this->__OBJECT->$attrname = __marshal2php($val);
		return $val;
	}
}
function __marshal2fruc($phpval)
{
	if(is_bool($phpval))
		return F_TrueClass::__from_bool($phpval);
	if(is_numeric($phpval) && !is_string($phpval))
		return F_Number::__from_number($phpval);
	if(is_string($phpval))
		return F_String::__from_string($phpval);
	if(is_object($phpval))
		return F_PHPObject::__from_object($phpval);
	if(is_array($phpval))
	{
		// determine if assoc or numeric
		$isAssoc = FALSE;
		foreach(array_keys($phpval) as $k)
		{
			if(!is_numeric($k))
			{
				$isAssoc = TRUE;
				break;
			}
		}
		if(!$isAssoc)
			return F_Array::__from_array(array_map('__marshal2fruc', $phpval));
		
		foreach($phpval as $k=>$v)
		{
			$newarr[] = __marshal2fruc($k);
			$newarr[] = __marshal2fruc($v);
		}
		
		return F_Hash::__from_flatpairs($newarr);
	}
	return new F_NilClass;
}
function __marshal2php($frucval)
{
	if(method_exists($frucval, "toPHP"))
		return $frucval->toPHP();
	return NULL;
}
function F_phpcall($block, $func)
{
	$frucargs = array_slice(func_get_args(), 2);
	$phpargs = array();
	foreach($frucargs as $frucarg)
		$phpargs[] = __marshal2php($frucarg);
	return __marshal2fruc(call_user_func_array($func->__SYMBOL, $phpargs));
}