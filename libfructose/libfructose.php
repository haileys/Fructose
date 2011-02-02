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

class ReturnFromBlock extends Exception
{
	public $val;
	function __construct($val)
	{
		$this->val = $val;
	}
}
class ErrorCarrier extends Exception
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
	public static $_dyn_global_methods = array();
	public static $_class_vars = array();

	public $_tainted = FALSE;
	public $_untrusted = FALSE;
	
	public function F_to_s($block)
	{
		return F_String::__from_string("Object");
	}
	public function F_puts($block,$o)
	{
		echo $o->F_to_s(NULL)->__STRING . "\n";
		return new F_NilClass;
	}
	public function F_require($block, $str)
	{
		$path = $str->F_to_s(NULL)->__STRING;
		if(@file_get_contents($path) !== FALSE)
		{
			require_once $path;
			return new F_TrueClass;
		}
		if(@file_get_contents($path . '.php') !== FALSE)
		{
			require_once $path . '.php';
			return new F_TrueClass;
		}
		if(@file_get_contents($path . '.fruc.php') !== FALSE)
		{
			require_once $path . '.fruc.php';
			return new F_TrueClass;
		}
		return new F_FalseClass;
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
		return call_user_func_array(array($this, _rmethod_to_php($sym->__SYMBOL)), $args);
	}
	public function __call($name, $args)
	{
		if(isset($this->_dyn_methods[$name]))
			return call_user_func_array($this->_dyn_methods[$name], $args);
			
		if(isset($this->_dyn_global_methods[$name]))
			return call_user_func_array($this->_dyn_global_methods[$name], $args);
		
		if(get_class($this) === 'F_Object')
			return call_user_func_array($name, $args);
		
		echo "No such method " . substr(get_class($this), 2) . "#" . substr($name, 2) . "\n";
		debug_print_backtrace();
		die;
	}
	public function __add_method($name, $fn)
	{
		$this->_dyn_methods[$name] = $fn;
	}
	public function __operator_notmatch($block, $operand)
	{
		// foo ^ true == !foo
		return $this->__operator_match(NULL, $operand)->__operator_xor(new F_TrueClass);
	}
	public function __operator_stricteq($block, $operand)
	{
		return $this->__operator_eq($operand);
	}
	public function F_clone($block)
	{
		$new = clone $this;
		if(_isTruthy($new->F_respond_to_QUES_(NULL, F_Symbol::__from_string("initialize_copy"))))
			$new->F_initialize_copy(NULL, $this);
		return $new;
	}
	public function F_dup($block)
	{
		$new = clone $this;
		if(_isTruthy($new->F_respond_to_QUES_(NULL, F_Symbol::__from_string("initialize_copy"))))
			$new->F_initialize_copy(NULL, $this);
		return $new;
	}
	public function __operator_eq($block, $operand)
	{
		return F_TrueClass::__from_bool($this === $operand);
	}
	public function F_equal_QUES_($block, $operand)
	{
		return F_TrueClass::__from_bool($this === $operand);
	}
	public function F_inspect($block)
	{
		return $this->F_to_s(NULL);
	}
	public function F_instance_of_QUES_($block, $sym)
	{
		return F_TrueClass::__from_bool(get_class($this) === _rmethod_to_php($sym->__SYMBOL));
	}
	public function F_is_a_QUES_($block, $sym)
	{
		return F_TrueClass::__from_bool(is_a($this, _rmethod_to_php($sym->__SYMBOL)));
	}
	public function F_nil_QUES_($block)
	{
		return new F_FalseClass;
	}
	public function F_taint($block)
	{
		$this->_tainted = TRUE;
	}
	public function F_untaint($block)
	{
		$this->_tainted = FALSE;
	}
	public function F_tainted_QUES_($block)
	{
		return F_TrueClass::__from_bool($this->_tainted);
	}
	public function F_tap($block)
	{
		$block(NULL, $this);
		return $this;
	}
	public function F_trust($block)
	{
		$this->_untrusted = FALSE;
	}
	public function F_untrust($block)
	{
		$this->_untrusted = TRUE;
	}
	public function F_untrusted_QUES_($block)
	{
		return F_TrueClass::__from_bool($this->_untrusted);
	}
	public function F_raise($block, $err)
	{		
		if(is_a($err, 'F_Error'))
			throw new ErrorCarrier($err);
			
		throw new ErrorCarrier(F_Error::SF_new(NULL, $err));
	}
}
class F_Error extends F_Object
{
	public static function SF_new($block, $msg = NULL)
	{
		$err = new F_Error;
		$err->__MESSAGE = $msg !== NULL ? $msg->F_to_s(NULL) : new F_NilClass;
		return $err;
	}
	public function F_message($block)
	{
		return $this->__MESSAGE;
	}
	public function F_message__set($block, $val)
	{
		return ($this->__MESSAGE = $val);
	}
	public function F_to_s($block)
	{
		return _isTruthy($this->__MESSAGE) ? $this->__MESSAGE : $this->F_class(NULL);
	}
	public function F_inspect($block)
	{
		$ex = $this->F_class(NULL)->F_to_s(NULL);
		$ex->__operator_lshift(NULL, F_String::__from_string(": "));
		$ex->__operator_lshift(NULL, $this->__MESSAGE);
		return $ex;
	}
}
class F_StopIteration extends F_Error
{
	public static function SF_new($block, $msg = NULL)
	{
		$err = new F_StopIteration;
		$err->__MESSAGE = $msg !== NULL ? $msg->F_to_s(NULL) : new F_NilClass;
		return $err;
	}
}
class F_IOError extends F_Error
{
	public static function SF_new($block, $msg = NULL)
	{
		$err = new F_IOError;
		$err->__MESSAGE = $msg !== NULL ? $msg->F_to_s(NULL) : new F_NilClass;
		return $err;
	}
}
class F_File extends F_Enumerable
{
	public static function SF_new($block, $filename, $m = NULL)
	{
		$mode = $m === NULL ? "r" : $m->F_to_s(NULL)->__STRING;
		$file = new F_File;
		$file->__HANDLE = fopen($filename->F_to_s(NULL)->__STRING, $mode);
		$file->__CLOSED = FALSE;
		if($block === NULL)
			return $file;
		
		$block(NULL, $file);
		
		if(!$file->__CLOSED)
			$file->F_close(NULL);
	}
	public function F_close($block)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File already closed")));
		fclose($this->__HANDLE);
		$file->__CLOSED = TRUE;
		return new F_NilClass;
	}
	public function F_closed_QUES_($block)
	{
		return F_TrueClass::__from_bool($this->__CLOSED);
	}
	public function __operator_lshift($block, $obj)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		fwrite($this->__HANDLE, $obj->F_to_s(NULL)->__STRING);
		return $this;
	}
	public function F_each($block)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
			
		if($block !== NULL)
		{
			while(($line = fgets($this->__HANDLE)) !== FALSE)
				$block(NULL, F_String::__from_string($line));
			return new F_NilClass;
		}
		
		while(($line = fgets($this->__HANDLE)) !== FALSE)
			$lines[] = F_String::__from_string($line);
		return F_Enumerator::__from_array($lines);
	}
	public function F_eof_QUES_($block)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		return F_TrueClass::__from_bool(feof($this->__HANDLE));
	}
	public function F_flush($block)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		fflush($this->__HANDLE);
		return new F_NilClass;
	}
	public function F_getc($block)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		if(feof($this->__HANDLE))
			return new F_NilClass;
		return F_String::__from_string(fgetc($this->__HANDLE));
	}
	public function F_gets($block)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		if(feof($this->__HANDLE))
			return new F_NilClass;
		return F_String::__from_string(fgets($this->__HANDLE));
	}
	public function F_tell($block)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		return F_Number::__from_number(ftell($this->__HANDLE));
	}
	public function F_read($block, $length, $buffer = NULL)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		if(feof($this->__HANDLE))
			return new F_NilClass;
			
		$buff = F_String::__from_string(fread($this->__HANDLE, $length->__NUMBER));
		if($buffer !== NULL)
		{
			$buffer->__operator_lshift(NULL, $buff);
			return $buffer;
		}
		
		return $buff;
	}
	public function F_rewind($block)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		fseek($this->__HANDLE, 0);
		return F_Number::__from_number(0);
	}
	public function F_seek($block, $offset, $whence = NULL)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));

		$w = SEEK_SET;
		if(_isTruthy($whence->__operator_eq(NULL, F_Symbol::__from_string("cur"))))
			$w = SEEK_CUR;
		elseif(_isTruthy($whence->__operator_eq(NULL, F_Symbol::__from_string("end"))))
			$w = SEEK_END;
			
		fseek($this->__HANDLE, $offset->__NUMBER, $w);
		return F_Number::__from_number(0);
	}
	public function F_stat($block)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		
		$stats = array();
		foreach(fstat($this->__HANDLE) as $k=>$v)
		{
			$stats[] = F_Symbol::__from_string($k);
			$stats[] = F_Number::__from_number($v);
		}
		
		return F_Hash::__from_flatpairs($stats);
	}
	public function F_write($block, $obj)
	{
		if($this->__CLOSED)
			throw new ErrorCarrier(F_IOError::SF_new(NULL, F_String::__from_string("File closed")));
		fwrite($this->__HANDLE, $obj->F_to_s(NULL)->__STRING);
		return $this;
	}
}
class F_Enumerator extends F_Object
{
	public static function __from_array($array)
	{
		$e = new F_Enumerator;
		$e->__ARRAY = $array;
		$e->__INDEX = 0;
		return $e;
	}
	public function F_next($block)
	{
		if($this->__INDEX === count($this->__ARRAY))
			throw new ErrorCarrier(F_StopIteration::SF_new(NULL));
		return $this->__ARRAY[$this->__INDEX++];
	}
	public function F_peek($block)
	{
		return $this->__ARRAY[$this->__INDEX];
	}
}
class F_Proc extends F_Object
{
	public static function SF_new($block)
	{
		$p = new F_Proc;
		$p->__BLOCK = $block;
		return $p;
	}
	public function __operator_eq($block, $operand)
	{
		return F_TrueClass::__from_bool($this->__BLOCK === $operand->__BLOCK);
	}
	public function __operator_stricteq($block, $operand)
	{
		$blockfn = $this->__BLOCK;
		return $blockfn(NULL, $operand);
	}
	public function F_call($block)
	{
		$args = func_get_args();
		return call_user_func_array($this->__BLOCK, $args);
	}
	public function F_to_s($block)
	{
		return F_String::__from_string($this->__BLOCK);
	}
}
class F_Random extends F_Object
{
	static $seed = 0;
	public static function SF_seed($block, $seed = NULL)
	{
		$n = $seed !== NULL ? (int)$seed->__NUMBER : time();
		mt_srand($n);
		$old_seed = F_Random::$seed;
		F_Random::$seed = $n;
		return F_Number::__from_number($old_seed);
	}
	public static function SF_rand($max = NULL)
	{
		$m = $max !== NULL ? $max->__NUMBER : 1.0;
		return F_Number::__from_number((mt_rand() / mt_getrandmax())  * $m);
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
	public function toPHP()
	{
		$arr = array();
		foreach($this->__ARRAY as $v)
		{
			if(method_exists($v, "toPHP"))
				$arr[] = $v->toPHP();
			else
				$arr[] = NULL;
		}
		return $arr;
	}
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
		if($block !== NULL)
		{
			foreach($this->__ARRAY as $i)
				$block(NULL, $i);
			return new F_NilClass;
		}
		
		return F_Enumerator::__from_array($this->__ARRAY);
	}
}
class F_Range extends F_Enumerable
{
	public function toPHP()
	{
		$arr = array();
		$this->enumerate_range();
		foreach($this->__RANGE as $v)
		{
			if(method_exists($v, "toPHP"))
				$arr[] = $v->toPHP();
			else
				$arr[] = NULL;
		}
		return $arr;
	}
	public static function SF_new($block, $begin, $end, $exclusive = NULL)
	{
		$r = new F_Range;
		$r->__BEGIN = $begin;
		$r->__END = $end;
		$r->__EXCLUSIVE = $exclusive === NULL ? FALSE : _isTruthy($exclusive);
		return $r;
	}
	private function enumerate_range()
	{
		if(isset($this->__RANGE))
			return;
				
		$i = $this->__BEGIN;
		while(true)
		{
			if(_isTruthy($this->__END->__operator_eq(NULL, $i)))
			{
				if(!$this->__EXCLUSIVE)
					$this->__RANGE[] = $i;
				break;
			}
			$this->__RANGE[] = $i;
			
			if(!_isTruthy($i->F_respond_to_QUES_(NULL, F_Symbol::__from_string('succ'))))
			{
				throw new ErrorCarrier(F_Error::SF_new(NULL, F_String::__from_string('Item in range does not respond to #succ')));
			}
				
			$i = $i->F_succ(NULL);
		}
	}
	public function __operator_eq($block, $operand)
	{
		return F_TrueClass::__from_bool(_isTruthy($this->__BEGIN->__operator_eq($operand->__BEGIN))
										&& _isTruthy($this->__END->__operator_eq($operand->__END))
										&& $this->__EXCLUSIVE === $operand->__EXCLUSIVE);
	}
	public function __operator_stricteq($block, $operand)
	{
		if(_isTruthy($operand->F_respond_to_QUES_(F_Symbol::__from_string("<=>"))))
		{
			if($operand->__operator_spaceship($this->__BEGIN)->__NUMBER >= 0
				&& $operand->__operator_spaceship($this->__END)->__NUMBER <= 0)
			{
				return new F_TrueClass;
			}
			else
			{
				return new F_FalseClass;
			}
		}
		// we're going to have to enumerate the range to see if $operand included
		$this->enumerate_range();
		foreach($this->__RANGE as $i)
			if(_isTruthy($i->__operator_eq($operand)))
				return new F_TrueClass;
		return new F_FalseClass;
	}
	public function F_begin($block)
	{
		return $this->__BEGIN;
	}
	public function F_cover_QUES_($block, $val)
	{
		return $this->__operator_stricteq(NULL, $val);
	}
	public function F_include_QUES_($block, $val)
	{
		return $this->__operator_stricteq(NULL, $val);
	}
	public function F_each($block)
	{
		$this->enumerate_range();
		if($block === NULL)
			return F_Enumerator::__from_array($this->__RANGE);
		
		foreach($this->__RANGE as $i)
			$block(NULL, $i);
	}
	public function F_end($block)
	{
		return $this->__END;
	}
	public function F_exclude_end_QUES_($block)
	{
		return F_TrueClass::__from_bool($this->__EXCLUSIVE);
	}
	public function F_to_a($block)
	{
		$this->enumerate_range();
		return F_Array::__from_array($this->__RANGE);
	}
}
class F_Hash extends F_Enumerable
{
	public function toPHP()
	{
		$arr = array();
		foreach($this->__PAIRS as $kv)
		{
			$k = $kv->__ARRAY[0]->F_to_s(NULL)->__STRING;
			$v = $kv->__ARRAY[1];
			
			if(method_exists($v, "toPHP"))
				$arr[$k] = $v->toPHP();
			else
				$arr[$k] = NULL;
		}
		return $arr;
	}
	public static function __from_pairs($pairs)
	{
		$hash = new F_Hash;
		$hash->__DEFAULT = new F_NilClass;
		$hash->__PAIRS = $pairs;
		return $hash;
	}
	public static function __from_flatpairs($flatpairs)
	{
		$hash = new F_Hash;
		$hash->__DEFAULT = new F_NilClass;
		
		for($i = 0; $i < count($flatpairs); $i += 2)
			$hash->__PAIRS[] = F_Array::__from_array(array($flatpairs[$i], $flatpairs[$i+1]));
			
		return $hash;
	}
	public static function __from_assoc($assoc)
	{
		$hash = new F_Hash;
		$hash->__DEFAULT = new F_NilClass;
		
		foreach($assoc as $k => $v)
			$hash->__PAIRS[] = F_Array::__from_array(array(F_Symbol::__from_string($k), F_String::__from_string($v)));
			
		return $hash;
	}
	public static function SF_new($block, $obj = NULL)
	{
		$hsh = new F_Hash;
		if($block === NULL && $obj === NULL)
		{
			$hsh->__DEFAULT = new F_NilClass;
		}
		elseif($obj !== NULL)
		{
			$hsh->__DEFAULT = $obj;
		}
		else
		{
			$hsh->__DEFAULT = $block;
		}
		$hsh->__PAIRS = array();
		return $hsh;
	}
	public function __operator_arrayget($block, $key)
	{
		foreach($this->__PAIRS as $pair)
		{
			if(_isTruthy($pair->__ARRAY[0]->__operator_eq(NULL, $key)))
				return $pair->__ARRAY[1];
		}
		return $this->F_default(NULL, $key);
	}
	public function __operator_arrayset($block, $key, $val)
	{
		foreach($this->__PAIRS as $pair)
		{
			if(_isTruthy($pair->__ARRAY[0]->__operator_eq(NULL, $key)))
			{
				$pair->__ARRAY[1] = $val;
				return $pair->__ARRAY[0];
			}
		}
		$this->__PAIRS[] = F_Array::__from_array(array($key->F_dup(NULL), $val));
		return $val;
	}
	public function F_assoc($block, $key)
	{
		foreach($this->__PAIRS as $pair)
		{
			if(_isTruthy($pair->__ARRAY[0]->__operator_eq(NULL, $key)))
				return $pair->__ARRAY[1];
		}
		return new F_NilClass;
	}
	public function F_clear($block)
	{
		$this->__PAIRS = array();
	}
	public function F_default($block, $key = NULL)
	{
		if(is_string($this->__DEFAULT) && $key !== NULL)
		{
			$blockfn = $this->__DEFAULT;
			return $blockfn(NULL, $this, $key);
		}
		return $this->__DEFAULT;
	}
	public function F_default__set($block, $default)
	{
		$this->__DEFAULT = $default;
	}
	public function F_delete($block, $key)
	{
		$new_pairs = array();
		$val = NULL;
		foreach($this->__PAIRS as $pair)
		{
			if(!_isTruthy($pair->__ARRAY[0]->__operator_eq(NULL, $key)))
				$new_pairs[] = $pair;
			else
				$val = $pair->__ARRAY[1];
		}
		if($val === NULL)
		{
			if($block === NULL)
				return new F_NilClass;
			return $block(NULL, $key);
		}
		else
		{
			$this->__PAIRS = $new_pairs;
			return $val;
		}
	}
	public function F_delete_if($block)
	{
		$new_pairs = array();
		$old_pairs = array();
		foreach($this->__PAIRS as $pair)
		{
			if(!_isTruthy($block(NULL, $pair->__ARRAY[0], $pair->__ARRAY[1])))
				$new_pairs[] = $pair;
			else
				$old_pairs[] = $pair;
		}
		$this->__PAIRS = $new_pairs;
		return F_Hash::__from_pairs($old_pairs);
	}
	public function F_each($block)
	{
		if($block !== NULL)
		{
			foreach($this->__PAIRS as $pair)
				$block(NULL, $pair->__ARRAY[0], $pair->__ARRAY[1]);
		}
		
		return F_Enumerator::__from_array($this->__PAIRS);
	}
	public function F_each_key($block)
	{
		foreach($this->__PAIRS as $pair)
			$block(NULL, $pair->__ARRAY[0]);
	}
	public function F_each_value($block)
	{
		foreach($this->__PAIRS as $pair)
			$block(NULL, $pair->__ARRAY[1]);
	}
	public function F_empty_QUES_($block)
	{
		return F_TrueClass::__from_bool(count($this->__PAIRS) === 0);
	}
	public function F_has_key_QUES_($block, $key)
	{
		foreach($this->__PAIRS as $pair)
			if(_isTruthy($pair->__ARRAY[0]->__operator_eq(NULL, $key)))
				return new F_TrueClass;
		return new F_FalseClass;
	}
	public function F_has_value_QUES_($block, $val)
	{
		foreach($this->__PAIRS as $pair)
			if(_isTruthy($pair->__ARRAY[1]->__operator_eq(NULL, $val)))
				return new F_TrueClass;
		return new F_FalseClass;
	}
	public function F_to_s($block)
	{
		$str = "{ ";
		$first = TRUE;
		foreach($this->__PAIRS as $pairs)
		{
			if(!$first)
				$str .= ", ";
			$first = FALSE;
			$str .= $pairs->__ARRAY[0]->F_to_s(NULL) . " => " . $pairs->__ARRAY[1]->F_to_s(NULL);
		}
		$str .= " }";
		return F_String::__from_string($str);
	}
	public function F_inspect($block)
	{
		return $this->F_to_s(NULL);
	}
	public function F_invert($block)
	{
		$new_pairs = array();
		foreach($this->__PAIRS as $pair)
			$new_pairs[] = F_Array::__from_array(array($pair->__ARRAY[1], $pair->__ARRAY[0]));
		
		return F_Hash::__from_pairs($new_pairs);
	}
	public function F_keep_if($block)
	{
		$new_pairs = array();
		$old_pairs = array();
		foreach($this->__PAIRS as $pair)
		{
			if(_isTruthy($block(NULL, $pair->__ARRAY[0], $pair->__ARRAY[1])))
				$new_pairs[] = $pair;
			else
				$old_pairs[] = $pair;
		}
		$this->__PAIRS = $new_pairs;
		return F_Hash::__from_pairs($old_pairs);
	}
	public function F_key($block, $val)
	{
		foreach($this->__PAIRS as $pair)
		{
			if(_isTruthy($pair->__ARRAY[1]->__operator_eq(NULL, $val)))
				return $pair->__ARRAY[0];
		}
		return new F_NilClass;
	}
	public function F_keys($block)
	{
		$arr = array();
		foreach($this->__PAIRS as $pair)
			$arr[] = $pair->__ARRAY[0];
		return F_Array::__from_array($arr);
	}
	public function F_size($block)
	{
		return F_Number::__from_number(count($this->__PAIRS));
	}
	public function F_merge($block, $other)
	{
		$new = F_Hash::SF_new(NULL);
		foreach($this->__PAIRS as $pair)
			$new->__operator_arrayset(NULL, $pair->__ARRAY[0], $pair->__ARRAY[1]);
		foreach($other->__PAIRS as $pair)
			$new->__operator_arrayset(NULL, $pair->__ARRAY[0], $pair->__ARRAY[1]);
		return $new;
	}
	public function F_merge_EXCL_($block, $other)
	{
		foreach($other->__PAIRS as $pair)
			$this->__operator_arrayset(NULL, $pair->__ARRAY[0], $pair->__ARRAY[1]);
		return $this;
	}
	public function F_reject($block)
	{
		$new_pairs = array();
		foreach($this->__PAIRS as $pair)
		{
			if(!_isTruthy($block(NULL, $pair->__ARRAY[0], $pair->__ARRAY[1])))
				$new_pairs[] = $pair;
		}
		return F_Hash::__from_pairs($new_pairs);
	}
	public function F_shift($block)
	{
		return array_shift($this->__PAIRS);
	}
	public function F_to_hash($block)
	{
		return $this;
	}
}
class F_Number extends F_Object
{
	public function toPHP()
	{
		return $this->__NUMBER;
	}
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
	public function F_succ($block)
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
	public function toPHP()
	{
		return TRUE;
	}
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
	public function toPHP()
	{
		return FALSE;
	}
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
	public function toPHP()
	{
		return NULL;
	}
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
	public function toPHP()
	{
		return $this->__SYMBOL;
	}
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
	public function toPHP()
	{
		return $this->__STRING;
	}
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
	public function F_succ($block)
	{
		$str = $this->__STRING;
		$str++;
		return F_String::__from_string($str);
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