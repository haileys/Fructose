<?php

/*

Copyright (c) 2011 Charlie Somerville

This software is provided 'as-is', without any express or implied
warranty. In no event will the authors be held liable for any damages
arising from the use of this software.

Permission is granted to anyone to use this software for any purpose,
including commercial applications, and to alter it and redistribute it
freely, subject to the following restrictions:

   1. The origin of this software must not be misrepresented; you must not
   claim that you wrote the original software. If you use this software
   in a product, an acknowledgment in the product documentation would be
   appreciated but is not required.

   2. Altered source versions must be plainly marked as such, and must not be
   misrepresented as being the original software.

   3. This notice may not be removed or altered from any source
   distribution.
   
*/

class F_Object
{
	public $_instance_vars = array();
	public static $_class_vars = array();

	public function F_to_s()
	{
		return F_String::__from_string("Object");
	}

	public function F_puts($o)
	{
		echo $o->F_to_s()->__STRING . "\n";
	}
	
	public function F_class()
	{
		return get_class($this);
	}
}

class F_Number extends F_Object
{
	public static function __from_number($num)
	{
		$obj = new F_Number;
		$obj->__NUMBER = $num;
		return $obj;
	}
	public function __operator_mod($operand)
	{
		return F_Number::__from_number($this->__NUMBER % $operand->__NUMBER);
	}
	public function __operator_bitwiseand($operand)
	{
		return F_Number::__from_number($this->__NUMBER & $operand->__NUMBER);
	}
	public function __operator_mul($operand)
	{
		return F_Number::__from_number($this->__NUMBER * $operand->__NUMBER);
	}
	public function __operator_exp($operand)
	{
		return F_Number::__from_number(pow($this->__NUMBER, $operand->__NUMBER));
	}
	public function __operator_add($operand)
	{
		return F_Number::__from_number($this->__NUMBER + $operand->__NUMBER);
	}
	public function __operator_sub($operand)
	{
		return F_Number::__from_number($this->__NUMBER - $operand->__NUMBER);
	}
	public function __operator_unaryminus()
	{
		return F_Number::__from_number(-$this->__NUMBER);
	}
	public function __operator_div($operand)
	{
		return F_Number::__from_number($this->__NUMBER / $operand->__NUMBER);
	}
	public function __operator_lt($operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER < $operand->__NUMBER);
	}
	public function __operator_lshift($operand)
	{
		return F_Number::__from_number($this->__NUMBER << $operand->__NUMBER);
	}
	public function __operator_lte($operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER <= $operand->__NUMBER);
	}
	public function __operator_lte($operand)
	{
		if($this->__NUMBER < $operand->__NUMBER)
			return F_Number::__from_number(-1);
		if($this->__NUMBER > $operand->__NUMBER)
			return F_Number::__from_number(-1);
		return F_Number::__from_number(0);
	}
	public function __operator_eq($operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER == $operand->__NUMBER);
	}
	public function __operator_stricteq($operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER === $operand->__NUMBER);
	}
	public function __operator_gt($operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER > $operand->__NUMBER);
	}
	public function __operator_rshift($operand)
	{
		return F_Number::__from_number($this->__NUMBER >> $operand->__NUMBER);
	}
	public function __operator_gte($operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER >= $operand->__NUMBER);
	}
	public function __operator_arrayget($index)
	{
		if($this->__NUMBER & (1 << $index->__NUMBER))
			return F_Number::__from_number(1);
		return F_Number::__from_number(0);
	}
	public function __operator_xor($operand)
	{
		return F_Number::__from_number($this->__NUMBER ^ $operand->__NUMBER);
	}
	public function F_abs($operand)
	{
		return F_Number::__from_number(abs($this->__NUMBER));
	}
	public function F_even_QUES_()
	{
		return F_TrueClass::__from_bool(($this->__NUMBER % 2) == 0);
	}
	public function F_odd_QUES_()
	{
		return F_TrueClass::__from_bool(($this->__NUMBER % 2) == 1);
	}
	public function F_next()
	{
		return F_Number::__from_number($this->__NUMBER + 1);
	}
	public function F_to_s($base = 10)
	{
		return F_String::__from_string(base_convert((string)$this->__NUMBER, 10, $base));
	}
	public function F_zero_QUES_()
	{
		return F_TrueClass::__from_bool($this->__NUMBER == 0);
	}
	public function __operator_or($operand)
	{
		return F_Number::__from_number($this->__NUMBER | $operand->__NUMBER);
	}
	public function __operator_bitwisenot($operand)
	{
		return F_Number::__from_number(~$this->__NUMBER);
	}
}

class F_TrueClass extends F_Object
{
	public static function __from_bool($bool)
	{
		if($bool)
			return new F_TrueClass;
		else
			return new F_FalseClass;
	}
	
	public function __operator_bitwiseand($operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return new F_FalseClass;

		return $this;
	}
	public function __operator_xor($operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return $this;

		return new F_FalseClass;
	}
	public function __operator_bitwiseor($operand)
	{
		return $this;
	}
	public function F_to_s()
	{
		return String::__from_string("true");
	}
}

class F_FalseClass extends F_Object
{
	public function __operator_bitwiseand($operand)
	{
		return $this;
	}
	public function __operator_xor($operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return $this;

		return new F_TrueClass;
	}
	public function __operator_bitwiseor($operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return $this;

		return new F_TrueClass;
	}
	public function F_to_s()
	{
		return String::__from_string("false");
	}
}

class F_NilClass extends F_Object
{
	public function __operator_bitwiseand($operand)
	{
		return new F_FalseClass;
	}
	public function __operator_xor($operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return new F_FalseClass;

		return new F_TrueClass;
	}
	public function F_inspect()
	{
		return F_String::__from_string("nil");
	}
	public function F_nil_QUES_()
	{
		return new F_TrueClass;
	}
	public function F_to_s()
	{
		return F_String::__from_string("");
	}
	public function __operator_bitwiseor($operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return new F_FalseClass;

		return new F_TrueClass;
	}
}

class F_String extends F_Object
{
	public static function __from_string($str)
	{
		$sobj = new F_String;
		$sobj->__STRING = $str;
		return $sobj;
	}

	public function F_to_s()
	{
		return $this;
	}

	public function __operator_plus($operand)
	{
		return F_String::__from_string($this->__STRING . $operand->F_to_s()->__STRING);
	}

	public function __operator_lshift($operand)
	{
		$this->__STRING .= $operand->F_to_s()->__STRING;
		return $this;
	}
}
