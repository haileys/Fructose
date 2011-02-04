<?php

require_once 'phpcall.php';

class F_PDOError extends F_Error
{
	public function F_info($block)
	{
		return isset($this->__INFO) ? $this->__INFO : new F_NilClass;
	}
	public static function SF_new($block, $msg = NULL)
	{
		$err = new F_PDOError;
		$err->__MESSAGE = $msg !== NULL ? $msg->F_to_s(NULL) : new F_NilClass;
		return $err;
	}
}

class F_PDO
{
	public static function SF_mysql($block, $hash)
	{
		$host = $hash->__operator_get(NULL, F_Symbol::__from_symbol('host'))->F_to_s(NULL)->__STRING;
		$name = $hash->__operator_get(NULL, F_Symbol::__from_symbol('name'))->F_to_s(NULL)->__STRING;
		$user = $hash->__operator_get(NULL, F_Symbol::__from_symbol('user'))->F_to_s(NULL);
		$pass = $hash->__operator_get(NULL, F_Symbol::__from_symbol('pass'))->F_to_s(NULL);
		

		return F_PDO::SF_new(NULL, F_String::__from_string('mysql:host=' 
			. (empty($host) ? 'localhost' : $host) 
			. (empty($name) ? '' : ';dbname=' . $name))
			
			, (_isTruthy($user->F_empty_QUES_(NULL)) ? NULL : $user )
			, (_isTruthy($pass->F_empty_QUES_(NULL)) ? NULL : $pass ));
	}
	public static function SF_new($block, $dsn, $user = NULL, $pass = NULL, $opts = NULL)
	{
		$_dsn = $dsn->F_to_s(NULL)->__STRING;
		$_user = $user !== NULL ? $user->F_to_s(NULL)->__STRING : NULL;
		$_pass = $pass !== NULL ? $pass->F_to_s(NULL)->__STRING : NULL;
		$_opts = NULL;
		if($opts !== NULL)
			foreach($opts->__PAIRS as $p)
				$_opts[$p->__ARRAY[0]->F_to_s(NULL)->__STRING] = $p->__ARRAY[1]->F_to_s(NULL)->__STRING;
				
		try
		{
			if($_user === NULL)
				$this->__PDO = new PDO($_dsn);
			elseif($_user !== NULL)
				$this->__PDO = new PDO($_dsn, $_user);
			elseif($_pass !== NULL)
				$this->__PDO = new PDO($_dsn, $_user, $_pass);
			elseif($_opts !== NULL)
				$this->__PDO = new PDO($_dsn, $_user, $_pass, $_opts);
		}
		catch(PDOException $e)
		{
			throw new ErrorCarrier(F_PDOError::SF_new(NULL, F_String::__from_string($e->getMessage())));
		}
		
		return $this;
	}
	public function F_begin_transaction($block)
	{
		return F_TrueClass::__from_bool($this->__PDO->beginTransaction());
	}
	public function F_commit($block)
	{
		return F_TrueClass::__from_bool($this->__PDO->commit());
	}
	public function F_rollback($block)
	{
		return F_TrueClass::__from_bool($this->__PDO->rollBack());
	}
	public function F_transaction($block)
	{
		$this->begin_transaction(NULL);
		if(_isTruthy($block(NULL, $this)))
			$this->commit(NULL);
		else 
			$this->rollback(NULL);
		return new F_NilClass;
	}
	public function F_error_code($block)
	{
		$code = $this->__PDO->errorCode();
		if($code === NULL)
			return new F_NilClass;
		return F_String::__from_string($code);
	}
	public function F_error_info($block)
	{
		$info = $this->__PDO->errorInfo();
		$hash = F_Hash::SF_new(NULL);
		$hash->__operator_set(NULL, F_Symbol::__from_symbol('state'), F_String::__from_string($info[0]));
		if($info[1] !== NULL)
			$hash->__operator_set(NULL, F_Symbol::__from_symbol('code'), F_String::__from_string($info[1]));
		if($info[2] !== NULL)
			$hash->__operator_set(NULL, F_Symbol::__from_symbol('msg'), F_String::__from_string($info[2]));
		return $hash;
	}
	public function F_query($block, $query)
	{
		$params = array_map(array_slice(func_get_args(), 2), create_function('$x', 'return $x->F_to_s(NULL)->__STRING;'));
		
		if(count($params) === 1 && is_a($params[0], 'F_Array'))
			$params = $params->__ARRAY;
			
		$stmt = $this->__PDO->prepare($query->F_to_s(NULL)->__STRING);
		if(!$stmt->execute($params))
		{
			$err = F_PDOError::SF_new(NULL, "An error occurred in PDO#query");
			$err->__INFO = $this->F_error_info(NULL);
			throw new ErrorCarrier($err);
		}
		
		if($block !== NULL)
			return $block(NULL, F_PDOResults::__from_stmt($stmt));
			
		return F_PDOResults::__from_stmt($stmt);
	}
}
class F_PDOResults extends F_Enumerable
{
    public static function __from_stmt($stmt)
	{
		$r = new F_PDOResults;
		$r->__STMT = $stmt;
		return $r;
	}
	public function F_each($block)
	{
		while($row = $this->__STMT->fetch(PDO::FETCH_BOTH))
			$block(NULL, F_PDOResultRow::__from_row($row));
		return new F_NilClass;
	}
	public function F_size($block)
	{
		return F_Number::__from_number($this->__STMT->rowCount());
	}
	public function F_single($block)
	{
		$row = $this->__STMT->fetch(PDO::FETCH_NUM);
		return marshal2fruc($row[0]);
	}
}
class F_PDOResultRow extends F_Enumerable
{
	public static function __from_row($row)
	{
		$r = new F_PDOResultRow;
		$r->__ROW = $row;
		return $r;
	}
	public function __operator_arrayget($block, $idx)
	{
		if(is_a($idx, 'F_Number'))
			return marshal2fruc($this->__ROW[$idx->__NUMBER]);
			
		return marshal2fruc($this->__ROW[$idx->F_to_s(NULL)->__STRING]);
	}
	public function F_each($block)
	{
		foreach($this->__ROW as $k=>$v)
			if(is_string($k))
				$block(NULL, F_String::__from_string($k), marshal2fruc($v));
		return new F_NilClass;
	}
}