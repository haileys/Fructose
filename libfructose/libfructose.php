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
}

class F_TrueClass extends F_Object
{
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
