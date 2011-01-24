<?php

/*

Copyright (c) 2011 Hailey Somerville

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
	public $_dyn_methods = array();
	public static $_class_vars = array();

	public function F_to_s($block)
	{
		return F_String::__from_string("Object");
	}

	public function F_puts($block,$o)
	{
		echo $o->F_to_s(NULL)->__STRING . "\n";
	}
	
	public function F_class($block)
	{
		return get_class($this);
	}
	
	public function __call($name, $args)
	{
		if(isset($_dyn_methods[$name]))
			return call_user_func_array($_dyn_methods[$name], $args);
		
		if(get_class($this) === 'F_Object')
			return call_user_func_array($name, $args);
		
		echo "No such method " . substr(get_class($this), 2) . "#" . substr($name, 2);
		debug_print_backtrace();
		die;
	}
	
	public function __add_method($name, $fn)
	{
		$_dyn_methods[$name] = $fn;
	}
}

class F_Enumerable extends F_Object
{
	private static $_all_callback_state = true;
	private static $_all_callback_block = NULL;
	
	public static function all_callback($obj)
	{
		$block = F_Enumerable::$_all_callback_block;
		$val = $block($obj);
		if(get_class($val) == 'F_NilClass' || get_class($val) == 'F_FalseClass')
		{
			F_Enumerable::$_all_callback_state = false;
		}
	}
	public function F_all_QUES_($block)
	{
		F_Enumerable::$_all_callback_state = true;
		F_Enumerable::$_all_callback_block = $block;
		$this->F_each(create_function('','$a = func_get_args(); return F_Enumerable::all_callback($a[1]);'));
		if($this->_all_callback_state)
		{
			return new F_TrueClass;
		}
		else
		{
			return new F_FalseClass;
		}
	}
}

class F_Array extends F_Enumerable
{
	public static function __from_array($arr)
	{
		$a = new F_Array;
		$a->__ARRAY = $arr;
		return $a;
	}
	
	public static function S__operator_arrayget($block)
	{
		$a = func_get_args();
		array_shift($a); // remove $block
		return F_Array::__from_array($a);
	}
	
	public function F_each($block)
	{
		foreach($this->__ARRAY as $i)
			$block(NULL, $i);
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
	public function __operator_mod($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER % $operand->__NUMBER);
	}
	public function __operator_bitwiseand($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER & $operand->__NUMBER);
	}
	public function __operator_mul($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER * $operand->__NUMBER);
	}
	public function __operator_exp($block,$operand)
	{
		return F_Number::__from_number(pow($this->__NUMBER, $operand->__NUMBER));
	}
	public function __operator_add($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER + $operand->__NUMBER);
	}
	public function __operator_sub($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER - $operand->__NUMBER);
	}
	public function __operator_unaryminus($block)
	{
		return F_Number::__from_number(-$this->__NUMBER);
	}
	public function __operator_div($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER / $operand->__NUMBER);
	}
	public function __operator_lt($block,$operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER < $operand->__NUMBER);
	}
	public function __operator_lshift($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER << $operand->__NUMBER);
	}
	public function __operator_lte($block,$operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER <= $operand->__NUMBER);
	}
	public function __operator_spaceship($block,$operand)
	{
		if($this->__NUMBER < $operand->__NUMBER)
			return F_Number::__from_number(-1);
		if($this->__NUMBER > $operand->__NUMBER)
			return F_Number::__from_number(1);
		return F_Number::__from_number(0);
	}
	public function __operator_eq($block,$operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER == $operand->__NUMBER);
	}
	public function __operator_stricteq($block,$operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER === $operand->__NUMBER);
	}
	public function __operator_gt($block,$operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER > $operand->__NUMBER);
	}
	public function __operator_rshift($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER >> $operand->__NUMBER);
	}
	public function __operator_gte($block,$operand)
	{
		return F_TrueClass::__from_bool($this->__NUMBER >= $operand->__NUMBER);
	}
	public function __operator_arrayget($block,$index)
	{
		if($this->__NUMBER & (1 << $index->__NUMBER))
			return F_Number::__from_number(1);
		return F_Number::__from_number(0);
	}
	public function __operator_xor($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER ^ $operand->__NUMBER);
	}
	public function F_abs($block)
	{
		return F_Number::__from_number(abs($this->__NUMBER));
	}
	public function F_even_QUES_($block)
	{
		return F_TrueClass::__from_bool(($this->__NUMBER % 2) == 0);
	}
	public function F_odd_QUES_($block)
	{
		return F_TrueClass::__from_bool(($this->__NUMBER % 2) == 1);
	}
	public function F_next($block)
	{
		return F_Number::__from_number($this->__NUMBER + 1);
	}
	public function F_to_s($block,$base = 10)
	{
		return F_String::__from_string(base_convert((string)$this->__NUMBER, 10, $base));
	}
	public function F_zero_QUES_($block)
	{
		return F_TrueClass::__from_bool($this->__NUMBER == 0);
	}
	public function __operator_bitwiseor($block,$operand)
	{
		return F_Number::__from_number($this->__NUMBER | $operand->__NUMBER);
	}
	public function __operator_bitwisenot($block,$operand)
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
	
	public function __operator_bitwiseand($block,$operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return new F_FalseClass;

		return $this;
	}
	public function __operator_xor($block,$operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return $this;

		return new F_FalseClass;
	}
	public function __operator_bitwiseor($block,$operand)
	{
		return $this;
	}
	public function F_to_s($block)
	{
		return F_String::__from_string("true");
	}
}

class F_FalseClass extends F_Object
{
	public function __operator_bitwiseand($block,$operand)
	{
		return $this;
	}
	public function __operator_xor($block,$operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return $this;

		return new F_TrueClass;
	}
	public function __operator_bitwiseor($block,$operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return $this;

		return new F_TrueClass;
	}
	public function F_to_s($block)
	{
		return F_String::__from_string("false");
	}
}

class F_NilClass extends F_Object
{
	public function __operator_bitwiseand($block,$operand)
	{
		return new F_FalseClass;
	}
	public function __operator_xor($block,$operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return new F_FalseClass;

		return new F_TrueClass;
	}
	public function F_inspect($block)
	{
		return F_String::__from_string("nil");
	}
	public function F_nil_QUES_($block)
	{
		return new F_TrueClass;
	}
	public function F_to_s($block)
	{
		return F_String::__from_string("");
	}
	public function __operator_bitwiseor($block,$operand)
	{
		$type = get_class($operand);
		if($type === "F_NilClass" || $type === "F_FalseClass")
			return new F_FalseClass;

		return new F_TrueClass;
	}
}

class F_Symbol extends F_Object
{
	public static function __from_string($sym)
	{
		$obj = new F_Symbol;
		$obj->__SYMBOL = $sym;
		return $obj;
	}
	public function __operator_spaceship($block,$operand)
	{
		return F_Number::__from_number(strcmp($this->__SYMBOL, $operand->__SYMBOL));
	}
	public function __operator_eq($block,$operand)
	{
		if(get_class($operand) !== 'F_Symbol' && !is_subclass_of($operand, 'F_Symbol'))
			return new F_FalseClass;
			
		return F_TrueClass::__from_bool($this->__SYMBOL === $operand->__SYMBOL);
	}
	public function __operator_stricteq($block,$operand)
	{
		return $this->__operator_eq($operand);
	}
	public function F_to_s($block)
	{
		return F_String::__from_string($this->__SYMBOL);
	}
	public function __operator_match($block,$operand)
	{
		return $this->F_to_s(NULL)->__operator_match($operand);
	}
	public function F_capitalize($block,$operand)
	{
		return $this->F_to_s(NULL)->F_capitalize(NULL)->F_to_sym(NULL);
	}
	public function F_downcase($block,$operand)
	{
		return $this->F_to_s(NULL)->F_downcase(NULL)->F_to_sym(NULL);
	}
	public function F_upcase($block,$operand)
	{
		return $this->F_to_s(NULL)->F_upcase(NULL)->F_to_sym(NULL);
	}
	public function F_empty_QUES_($block)
	{
		return F_TrueClass::__from_bool($this->__SYMBOL === '');
	}
	public function F_to_sym($block)
	{
		return $this;
	}
	public function F_length($block)
	{
		return $this->F_to_s(NULL)->F_length(NULL);
	}
	public function __operator_arrayget($block,$index)
	{
		return $this->F_to_s(NULL)->__operator_arrayget(NULL, $index);
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
	public function F_to_s($block)
	{
		return $this;
	}
	public function F_to_sym($block)
	{
		return F_Symbol::__from_string($this->__STRING);
	}
	public function __operator_add($block,$operand)
	{
		return F_String::__from_string($this->__STRING . $operand->F_to_s()->__STRING);
	}
	public function __operator_mod($block,$operand)
	{
		if(get_class($operand) == 'F_Array' || is_subclass_of($operand, 'F_Array'))
		{
			$operands = array();
			foreach($operand->__ARRAY as $v)
				$operands[] = $v->F_to_s();
				
			return F_String::__from_string(vsprintf($this->__STRING, $operands));
		}
		else if(get_class($operand) == 'F_Hash' || is_subclass_of($operand, 'F_Hash'))
		{
			// @TODO
		}
		return F_String::__from_string(sprintf($this->__STRING, $operand->F_to_s(NULL)->__STRING));
	}
	public function __operator_mul($block,$operand)
	{
		return F_String::__from_string(str_repeat($this->__STRING, (int)$operand->__NUMBER < 0 ? 0 : (int)$operand->__NUMBER));
	}
	public function __operator_spaceship($block,$operand)
	{
		return F_Number::__from_number(strcmp($this->__STRING, $operand->__STRING));
	}
	public function __operator_eq($block,$operand)
	{
		if(get_class($operand) !== 'F_String' && !is_subclass_of($operand, 'F_String'))
			return new F_FalseClass;
			
		return F_TrueClass::__from_bool($this->__STRING === $operand->__STRING);
	}
	public function __operator_stricteq($block,$operand)
	{
		return $this->__operator_eq($operand);
	}
	public function __operator_match($block,$operand)
	{
		if(get_class($operand) === 'F_Regexp' || is_subclass_of($operand, 'F_Regexp'))
		{
			$matches = array();
			if(!preg_match($operand->__REGEX, $this->__STRING))
				return new F_NilClass;
			
			return F_Number::__from_number(strpos($this->__STRING, $matches[0]));
		}
		
		return $operand->__operator_match($this);
	}
	public function __operator_arrayget($block,$operand, $operand2 = NULL)
	{
		if(get_class($operand) === 'F_Number' || is_subclass_of($operand, 'F_Number'))
		{
			$offset = (int)$operand->__NUMBER;
			$length = strlen($this->__STRING);
			if($offset > $length)
				$offset += $length;
			if($offset < 0 || $offset >= $length)
				return new F_NilClass;
				
			if($operand2 === NULL)
			{					
				return F_String::__from_string($this->__STRING[$offset]);
			}
			else if(get_class($operand2) === 'F_Number' || is_subclass_of($operand2, 'F_Number'))
			{
				if($operand2->__NUMBER < 0)
					return new F_NilClass;
				
				return F_String::__from_string($this->__STRING, $offset, $operand2->__NUMBER);
			}
			else
				return new F_NilClass;
		}
		else if(get_class($operand) === 'F_Regexp' || is_subclass_of($operand, 'F_Regexp'))
		{
			$matches = array();
			if(!preg_match($operand->__REGEX, $this->__STRING))
				return new F_NilClass;
			$index = $operand2 !== NULL ? (int)$operand2->__NUMBER : 0;
			if(count($matches) > $index)
				return new F_NilClass;
			
			return F_String::__from_string($matches[$index]);
		}
		else if(get_class($operand) === 'F_String' || is_subclass_of($operand, 'F_String'))
		{
			if(strpos($this->__STRING, $operand->__STRING) !== FALSE)
				return $operand;
				
			return new F_NilClass;
		}
		
		return new F_NilClass;
	}
	public function __operator_arrayset($block,$operand, $val)
	{
		$this->__string[(int)$operand->__NUMBER] = $val->__STRING;
	}
	public function F_capitalize($block)
	{
		return F_String::__from_string(ucfirst(strtolower($this->__STRING)));
	}
	public function F_capitalize_EXCL_($block)
	{
		$new = ucfirst(strtolower($this->__STRING));
		if($this->__STRING === $new)
			return new F_NilClass;
		
		$this->__STRING = $new;
		return $this;
	}
	public function F_casecmp($block,$operand)
	{
		return new F_Number(strcmp(strtolower($this->__STRING), strtolower($operand->F_to_s(NULL)->__STRING)));
	}
	public function F_clear($block)
	{
		$this->__STRING = "";
	}
	public function F_crypt($block,$operand)
	{
		return F_String::__from_string(crypt($this->__STRING, $operand->F_to_s(NULL)->__STRING));
	}
	public function F_downcase($block)
	{
		return F_String::__from_string(strtolower($this->__STRING));
	}
	public function F_downcase_EXCL_($block)
	{
		$new = strtolower($this->__STRING);
		if($this->__STRING === $new)
			return new F_NilClass;
		
		$this->__STRING = $new;
		return $this;
	}
	public function F_empty_QUES_($block)
	{
		return F_TrueClass::__from_bool($this->__STRING === '');
	}
	public function F_eql_QUES_($block,$operand)
	{
		return F_TrueClass::__from_bool($this->__STRING === $operand->F_to_s(NULL)->__STRING);
	}
	public function F_hash($block)
	{
		return F_String::__from_string(sha1($this->__STRING));
	}
	public function F_include_QUES_($block,$operand)
	{
		return F_TrueClass::__from_bool(strpos($this->__STRING, $operand->F_to_s(NULL)->__STRING) !== FALSE);
	}
	public function F_length($block)
	{
		return F_Number::__from_number(strlen($this->__STRING));
	}
	public function F_split($block,$pattern)
	{
		$parts = explode($pattern->F_to_s(NULL)->__STRING, $this->__STRING);
		$arr = array();
		foreach($parts as $part)
		{
			$arr[] = F_String::__from_string($part);
		}
		return F_Array::__from_array($arr);
	}
	public function F_to_n($block)
	{
		return F_Number::__from_number($this->__STRING);
	}
	public function F_upcase($block)
	{
		return F_String::__from_string(strtoupper($this->__STRING));
	}
	public function F_upcase_EXCL_($block)
	{
		$new = strtoupper($this->__STRING);
		if($this->__STRING === $new)
			return new F_NilClass;
		
		$this->__STRING = $new;
		return $this;
	}
	public function __operator_lshift($block,$operand)
	{
		$this->__STRING .= $operand->F_to_s(NULL)->__STRING;
		return $this;
	}
}
