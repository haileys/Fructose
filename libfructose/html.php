<?php

function F_tag($block, $tag, $attrs = NULL)
{
	echo "<" . $tag->__SYMBOL;
	if($attrs !== NULL)
	{
		foreach($attrs->__PAIRS as $p)
		{
			echo " " . $p->__ARRAY[0]->__SYMBOL . "='";
			echo htmlspecialchars($p->__ARRAY[1]->F_to_s(NULL)->__STRING);
			echo "'";
		}
	}
	if($block === NULL)
	{
		echo " />";
		return new F_NilClass;
	}
	echo ">";
	$block(NULL);
	echo "</" . $tag->__SYMBOL . ">";
}
function F_text($block, $text)
{
	echo htmlspecialchars($text->F_to_s(NULL)->__STRING);
}
function F_html($block, $html)
{
	echo $html->F_to_s(NULL)->__STRING;
}