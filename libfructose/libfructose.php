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

class ReturnFromBlock extends Exception
{
	public $val;
	
	function __construct($val)
	{
		$this->val = $val;
	}
}

$_operator_lookup["!"] = "__operator_not";
$_operator_lookup["~"] = "__operator_bitwisenot";
$_operator_lookup["+@"] = "__operator_unaryplus";
$_operator_lookup["**"] = "__operator_exp";
$_operator_lookup["-@"] = "__operator_unaryminus";
$_operator_lookup["*"] = "__operator_mul";
$_operator_lookup["/"] = "__operator_div";
$_operator_lookup["%"] = "__operator_mod";
$_operator_lookup["+"] = "__operator_add";
$_operator_lookup["-"] = "__operator_sub";
$_operator_lookup["<<"] = "__operator_lshift";
$_operator_lookup[">>"] = "__operator_rshift";
$_operator_lookup["&"] = "__operator_bitwiseand";
$_operator_lookup["|"] = "__operator_bitwiseor";
$_operator_lookup["^"] = "__operator_xor";
$_operator_lookup["<"] = "__operator_lt";
$_operator_lookup["<="] = "__operator_lte";
$_operator_lookup[">"] = "__operator_gt";
$_operator_lookup[">="] = "__operator_gte";
$_operator_lookup["=="] = "__operator_eq";
$_operator_lookup["==="] = "__operator_stricteq";
$_operator_lookup["!="] = "__operator_neq";
$_operator_lookup["=~"] = "__operator_match";
$_operator_lookup["!~"] = "__operator_notmatch";
$_operator_lookup["<=>"] = "__operator_spaceship";
$_operator_lookup["[]"] = "__operator_arrayget";
$_operator_lookup["[]="] = "__operator_arrayset";

function _rmethod_to_php($method)
{
	global $_operator_lookup;
	if(isset($_operator_lookup[$method]))
		return $_operator_lookup[$method];
	
	return "F_" . str_replace("?", "_QUES_", str_replace("!", "_EXCL", str_replace("=", "__set", $method)));
}

function _isTruthy($obj)
{
	$class = get_class($obj);
	return $class !== 'F_NilClass' && $class !== 'F_FalseClass';
}

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
		return new F_NilClass;
	}
	
	public function F_class($block)
	{
		return F_Symbol::__from_string(get_class($this));
	}
	
	public function F_respond_to_QUES_($block, $sym, $include_private = NULL)
	{
		if($include_private === NULL)
			$include_private = new F_FalseClass;
		
		if(method_exists($this, _rmethod_to_php($sym->__SYMBOL)))
			return new F_TrueClass;
		
		if(method_exists($this, "F_respond_to_missing_QUES_"))
			return $this->F_respond_to_missing_QUES_($sym, $include_private);
			
		return new F_FalseClass;
	}
	
	public function F_send($block, $sym)
	{
		$args = func_get_args();
		array_splice($args, 1, 1);
		return call_user_func_array(array($this, $sym), $args);
	}
	
	public function __call($name, $args)
	{
		if(isset($_dyn_methods[$name]))
			return call_user_func_array($_dyn_methods[$name], $args);
		
		if(get_class($this) === 'F_Object')
			return call_user_func_array($name, $args);
		
		echo "No such method " . substr(get_class($this), 2) . "#" . substr($name, 2) . "\n";
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
	public static $_states = array();
	public function F_all_QUES_($block)
	{
		if($block === NULL)
			$block = create_function('','$a = func_get_args(); return $a[1];');
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = true;
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s"; if(! _isTruthy($f(NULL, $a[1]))) { F_Enumerable::$_states[%d] = false; }', $block, $state)));
		return F_TrueClass::__from_bool(F_Enumerable::$_states[$state]);
	}
	public function F_any_QUES_($block)
	{
		if($block === NULL)
			$block = create_function('','$a = func_get_args(); return $a[1];');
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = false;
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s"; if(_isTruthy($f(NULL, $a[1]))) { F_Enumerable::$_states[%d] = true; }', $block, $state)));
		return F_TrueClass::__from_bool(F_Enumerable::$_states[$state]);
	}
	public function F_collect($block)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array();
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s"; F_Enumerable::$_states[%d][] = $f(NULL, $a[1]);', $block, $state)));
		return F_Array::__from_array(F_Enumerable::$_states[$state]);
	}
	public function F_count($block, $item = NULL)
	{
		if($block === NULL && $item === NULL)
		{
			if(_isTruthy($this->F_respond_to_QUES_(NULL, F_Symbol::__from_string('size'))))
				return $this->F_size(NULL);
			$state = count(F_Enumerable::$_states);
			F_Enumerable::$_states[$state] = 0;
			$this->F_each(create_function('',sprintf('F_Enumerable::$_states[%d]++;', $state)));
			return F_Number::__from_number(F_Enumerable::$_states[$state]);
		}
		if($item !== NULL)
		{
			$state = count(F_Enumerable::$_states);
			F_Enumerable::$_states[$state] = array('item' => $item, 'count' => 0);
			$this->F_each(create_function('',sprintf('$a = func_get_args(); if(_isTruthy(F_Enumerable::$_states[%d]["item"]->__operator_eq(NULL, $a[1]))) { F_Enumerable::$_states[%d]["count"]++; }', $state, $state)));
			return F_Number::__from_number(F_Enumerable::$_states[$state]['count']);
		}
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = 0;
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s"; if(_isTruthy($f(NULL, $a[1]))) { F_Enumerable::$_states[%d]++; }', $block, $state)));
		return F_Number::__from_number(F_Enumerable::$_states[$state]);
	}
	public function F_find($block)
	{
		try
		{
			$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s"; if(_isTruthy($f(NULL, $a[1]))) { throw new ReturnFromBlock($a[1]); }', $block)));
		}
		catch(ReturnFromBlock $rfb)
		{
			return $rfb->val;
		}
		
		return new F_NilClass;
	}
	public function F_drop($block, $n)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array('n' => $n->__NUMBER, 'arr' => array());
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $state = %d;
		if(--F_Enumerable::$_states[$state]["n"] < 0)
		{
			F_Enumerable::$_states[$state]["arr"][] = $a[1];
		}', $state)));
		return F_Array::__from_array(F_Enumerable::$_states[$state]['arr']);
	}
	public function F_drop_while($block)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array('dropping' => true, 'arr' => array());
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s";
		if(!F_Enumerable::$_states[%d]["dropping"] || !_isTruthy($f(NULL,$a[1]))) {
			F_Enumerable::$_states[%d]["dropping"] = false;
			F_Enumerable::$_states[%d]["arr"][] = $a[1];
		}', $block, $state, $state, $state)));
		return F_Array::__from_array(F_Enumerable::$_states[$state]['arr']);
	}
	public function F_to_a($block)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array();
		$this->F_each(create_function('',sprintf('$a = func_get_args(); F_Enumerable::$_states[%d][] = $a[1];', $state)));
		return F_Array::__from_array(F_Enumerable::$_states[$state]);
	}
	public function F_select($block)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array();
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s"; if(_isTruthy($f(NULL, $a[1]))) { F_Enumerable::$_states[%d][] = $a[1]; }', $block, $state)));
		return F_Array::__from_array(F_Enumerable::$_states[$state]);
	}
	public function F_first($block)
	{
		try
		{
			$this->F_each(create_function('','$a = func_get_args(); throw new ReturnFromBlock($a[1]);'));
		}
		catch(ReturnFromBlock $rfb)
		{
			return $rfb->val;
		}
		
		return new F_NilClass;
	}
	public function F_include_QUES_($block, $obj)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = $obj;
		try
		{
			$this->F_each(create_function('',sprintf('$a = func_get_args(); if(_isTruthy($a[1]->__operator_eq(NULL, F_Enumerable::$_states[%d]))) { throw new ReturnFromBlock($a[1]); }', $state, $obj)));
		}
		catch(ReturnFromBlock $rfb)
		{
			return $rfb->val;
		}
		
		return new F_NilClass;
	}
	public function F_reduce($block, $sym = NULL)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = NULL;
		
		if($block === NULL && $sym !== NULL)
			$block = create_function('',sprintf('$a = func_get_args(); return call_user_func(array($a[1],"%s"), NULL, $a[2]);', _rmethod_to_php($sym->__SYMBOL)));
			
		if($block === NULL)
		{
			// @TODO
			// throw some exception
		}
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $state = %d; $f = "%s";
		if(F_Enumerable::$_states[$state] === NULL)
		{
			F_Enumerable::$_states[$state] = $a[1];
		}
		else
		{
			F_Enumerable::$_states[$state] = $f(NULL, F_Enumerable::$_states[$state], $a[1]);
		}', $state, $block)));
		return F_Enumerable::$_states[$state] === NULL ? new F_NilClass : F_Enumerable::$_states[$state];
	}
	public function _F_minmax($block, $compare)
	{
		if($block === NULL)
			$block = create_function('', '$a = func_get_args(); return $a[1]->__operator_spaceship(NULL, $a[2]);');
		
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = NULL;
		
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $state = %d; $f = "%s"; 
		if(F_Enumerable::$_states[$state] === NULL)
		{
			F_Enumerable::$_states[$state] = $a[1];
		}
		else
		{
			if($f(NULL, F_Enumerable::$_states[$state], $a[1])->__NUMBER ' . $compare . ' 0)
			{
				F_Enumerable::$_states[$state] = $a[1];
			}
		}', $state, $block)));
		return F_Enumerable::$_states[$state] === NULL ? new F_NilClass : F_Enumerable::$_states[$state];
	}
	public function F_max($block)
	{
		return $this->_F_minmax($block, '<');
	}
	public function F_min($block)
	{
		return $this->_F_minmax($block, '>');
	}
	public function F_minmax($block)
	{
		return F_Array::__from_array(array($this->F_min($block), $this->F_max($block)));
	}
	public function F_none_QUES_($block)
	{
		return $this->F_any_QUES_($block) ? new F_FalseClass : new F_TrueClass;
	}
	public function F_one_QUES_($block)
	{
		if($block === NULL)
			$block = create_function('','$a = func_get_args(); return $a[1];');
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = 0;
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s"; if(_isTruthy($f(NULL, $a[1]))) { F_Enumerable::$_states[%d]++; }', $block, $state)));
		return F_TrueClass::__from_bool(F_Enumerable::$_states[$state] === 1);
	}
	public function F_partition($block)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array('t' => array(), 'f' => array());
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s"; F_Enumerable::$_states[%d][ _isTruthy($f(NULL, $a[1])) ? "t" : "f" ][] = $a[1];', $block, $state)));
		return F_Array::__from_array(F_Enumerable::$_states[$state]);
	}
	public function F_reject($block)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array();
		$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s"; if(!_isTruthy($f(NULL, $a[1]))) { F_Enumerable::$_states[%d][] = $a[1]; }', $block, $state)));
		return F_Array::__from_array(F_Enumerable::$_states[$state]);
	}
	public function F_reverse_each($block)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array();
		$this->F_each(create_function('',sprintf('$a = func_get_args(); array_unshift(F_Enumerable::$_states[%d], $a[1]);', $state)));
		return F_Array::__from_array(F_Enumerable::$_states[$state])->F_each($block);
	}
	public function F_sort($block)
	{
		if($block === NULL)
			$block = create_function('', '$a = func_get_args(); return $a[1]->__operator_spaceship(NULL, $a[2]);');
		$a = $this->F_to_a(NULL);
		usort($a->__ARRAY, create_function('$a,$b', sprintf('$f = "%s"; return $f(NULL, $a, $b)->__NUMBER;', $block)));
		return $a;
	}
	public function F_take($block, $n)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array('n' => $n->__NUMBER, 'arr' => array());
		try
		{
			$this->F_each(create_function('',sprintf('$a = func_get_args(); $state = %d; if(--F_Enumerable::$_states[$state]["n"] < 0)
			{
				throw new ReturnFromBlock(NULL);
			}
			else
			{
				F_Enumerable::$_states[$state]["arr"][] = $a[1];
			}', $state)));
		}
		catch(ReturnFromBlock $rfb)
		{ }
		return F_Array::__from_array(F_Enumerable::$_states[$state]['arr']);
	}
	public function F_take_while($block)
	{
		$state = count(F_Enumerable::$_states);
		F_Enumerable::$_states[$state] = array();
		try
		{
			$this->F_each(create_function('',sprintf('$a = func_get_args(); $f = "%s";
			if(!_isTruthy($f(NULL,$a[1]))) {
				throw new ReturnFromBlock(NULL);
			}
			F_Enumerable::$_states[%d][] = $a[1];
			', $block, $state)));
		}
		catch(ReturnFromBlock $rfb)
		{ }
		return F_Array::__from_array(F_Enumerable::$_states[$state]);
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
	
	public function __operator_bitwiseand($block, $array)
	{
		// @TODO
		// this is a O(n^2) intersection for now. i'll optimize it later
		$intersect = array();
		for($i = 0; $i < count($this->__ARRAY); $i++)
			for($j = 0; $j < count($array->__ARRAY); $j++)
				if(_isTruthy($this->__ARRAY[$i]->__operator_eq($array->__ARRAY[$j])))
					$intersect[] = $this->__ARRAY[$i];
					
		// @TODO
		// ruby-doc says this operator returns the intersection without dupes.
		// for now i'll just return dupes
		return F_Array::__from_array($intersect);
	}
	
	public function __operator_mul($block, $operand)
	{
		if($index->F_class()->__SYMBOL === 'F_String')
			return $this->F_join(NULL, $operand);
		
		$arr = array();
		for($i = 0; $i < $operand->__NUMBER; $i++)
			$arr = array_merge($arr, $this->__ARRAY);
			
		return F_Array::__from_array($arr);
	}
	
	public function __operator_add($block, $operand)
	{
		return F_Array::__from_array(array_merge($this->__ARRAY, $operand->__ARRAY));
	}
	
	public function __operator_sub($block, $operand)
	{
		$new = array();
		for($i = 0; $i < count($this->__ARRAY); $i++)
			if(!_isTruthy($operand->F_include_QUES_(NULL, $this->__ARRAY[$i])))
				$new[] = $this->__ARRAY[$i];
		return F_Array::__from_array($new);
	}
	
	public function __operator_lshift($block, $operand)
	{
		$this->__ARRAY[] = $operand;
		return $this;
	}
	
	public function __operator_eq($block, $operand)
	{
		if(count($this->__ARRAY) !== count($operand->__ARRAY))
			return new F_FalseClass;
			
		for($i = 0; $i < count($this->__ARRAY); $i++)
			if(!_isTruthy($this->__ARRAY[$i]->__operator_eq(NULL, $operand->__ARRAY[$i])))
				return new F_FalseClass;
				
		return new F_TrueClass;
	}
	
	public function __operator_arrayget($block, $index)
	{
		$idx = (int)$index->__NUMBER;
		if($idx < 0)
			$idx += count($this->__ARRAY);
		if($idx < 0)
		{
			// @TODO throw IndexError
			return;
		}
		if($idx >= count($this->__ARRAY))
			return new F_NilClass;
		return $this->__ARRAY[$idx];
	}
	public function __operator_arrayset($block, $index, $val)
	{
		$idx = (int)$index->__NUMBER;
		if($idx < 0)
			$idx += count($this->__ARRAY);
		if($idx < 0)
		{
			// @TODO throw IndexError
			return;
		}
		if($idx >= count($this->__ARRAY))
		{
			for($i = count($this->__ARRAY); $i < $idx; $i++)
				$this->__ARRAY[$i] = new F_NilClass;
		}
		$this->__ARRAY[$idx] = $val;
		return $val;
	}
	
	public function F_clear($block)
	{
		$this->__ARRAY = array();
		return $this;
	}
	
	public function F_compact($block)
	{
		$new = array();
		for($i = 0; $i < count($this->__ARRAY); $i++)
			if(get_class($this->__ARRAY[$i]) !== 'F_NilClass')
				$new[] = $this->__ARRAY[$i];
		return F_Array::__from_array($new);
	}
	
	public function F_compact_EXCL($block)
	{
		$new = array();
		$changed = false;
		for($i = 0; $i < count($this->__ARRAY); $i++)
			if(get_class($this->__ARRAY[$i]) !== 'F_NilClass')
			{
				$changed = true;
				$new[] = $this->__ARRAY[$i];
			}
		$this->__ARRAY = $new;
		if(!$changed)
			return new F_NilClass;
		return $this;
	}
	
	public function F_concat($block, $ary)
	{
		return $this->__operator_add(NULL, $ary);
	}
	
	public function F_count($block)
	{
		return F_Number::__from_number(count($this->__ARRAY));
	}
	
	public function F_delete($block, $val)
	{
		$new = array();
		$changed = false;
		for($i = 0; $i < count($this->__ARRAY); $i++)
			if(!_isTruthy($this->__ARRAY[$i]->__operator_eq(NULL, $val)))
			{
				$changed = true;
				$new[] = $this->__ARRAY[$i];
			}
		if($changed)
			return $val;
		else
		{
			if($block !== NULL)
				return $block(NULL);
			
			return new F_NilClass;
		}
	}
	
	public function F_delete_at($block, $index)
	{
		$idx = (int)$index->__NUMBER;
		if($idx < 0)
			$idx += count($this->__ARRAY);
		if($idx < 0)
			return new F_NilClass;
		
		$val = $this->__operator_arrayget(NULL, $index);
		array_splice($this->__ARRAY, $idx, 1);
		return $val;
	}
	
	public function F_delete_if($block)
	{
		$new = array();
		for($i = 0; $i < count($this->__ARRAY); $i++)
			if(!_isTruthy($block(NULL, $this->__ARRAY[$i])))
				$new[] = $this->__ARRAY[$i];
		$this->__ARRAY = $new;
		return $this;
	}
	
	public function F_empty_QUES_($block)
	{
		return F_TrueClass::__from_bool(count($this->__ARRAY) === 0);
	}
	
	public function F_include_QUES_($block, $val)
	{
		for($i = 0; $i < count($this->__ARRAY); $i++)
			if(_isTruthy($this->__ARRAY[$i]->__operator_eq(NULL, $val)))
				return new F_TrueClass;
		return new F_FalseClass;
	}
	
	public function F_index($block, $val)
	{
		for($i = 0; $i < count($this->__ARRAY); $i++)
			if(_isTruthy($this->__ARRAY[$i]->__operator_eq(NULL, $val)))
				return F_Number::__from_number($i);
		return new F_NilClass;
	}
	
	public function F_replace($block, $ary)
	{
		$this->__ARRAY = $ary->__ARRAY;
		return $this;
	}
	
	public function F_insert($block, $index)
	{
		$objs = func_get_args();
		array_splice($objs, 0, 2);
		
		$idx = (int)$index->__NUMBER;
		if($idx < 0)
			$idx += count($this->__ARRAY);
		if($idx < 0)
		{
			// @TODO throw IndexError
			return;
		}
	}
	
	public function F_each($block)
	{
		foreach($this->__ARRAY as $i)
			$block(NULL, $i);
		return new F_NilClass;
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
	public function F_to_s($block,$base = NULL)
	{
		if($base === NULL)
			$base = F_Number::__from_number(10);
		return F_String::__from_string(base_convert((string)($this->__NUMBER), 10, $base->__NUMBER));
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
	public function F_capitalize($block)
	{
		return $this->F_to_s(NULL)->F_capitalize(NULL)->F_to_sym(NULL);
	}
	public function F_downcase($block)
	{
		return $this->F_to_s(NULL)->F_downcase(NULL)->F_to_sym(NULL);
	}
	public function F_upcase($block)
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
		return F_String::__from_string($this->__STRING . $operand->F_to_s(NULL)->__STRING);
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
		return $val;
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
		return F_Number::__from_number(strcmp(strtolower($this->__STRING), strtolower($operand->F_to_s(NULL)->__STRING)));
	}
	public function F_clear($block)
	{
		$this->__STRING = "";
		return new F_NilClass;
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
