<?php

function foo()
{
	return 42;
}

function fact($n)
{
	if($n <= 1)
		return 1;
	return $n * fact($n - 1);
}

class MyClass
{
    function foobar()
	{
		echo $this->msg . "\n";
	}
}
function myClassFactory()
{
	return new MyClass;
}