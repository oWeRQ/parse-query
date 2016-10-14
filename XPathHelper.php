<?php

class XPathHelper
{
	public static function toXPath($selector, $prefix = 'descendant::')
	{
		$holders = [];

		$selector = static::xpathPseudo($selector, $holders);
		$selector = static::xpathConditions($selector, $holders);

		$selector = static::toXPathPlain($selector, $prefix);

		foreach (array_reverse($holders) as $holder => $value) {
			$selector = str_replace($holder, $value, $selector);
		}

		return $selector;
	}

	public static function toXPathPlain($selector, $prefix = 'descendant::')
	{
		$selector = $prefix.trim($selector);

		$replace = [
			'\s*,\s*' => '|'.$prefix,
			'\s*>\s*' => '/',
			'\s*~\s*' => '/following-sibling::',
			'\s*\+\s*' => '/following-sibling::*[1]/self::',
			'\s+' => '/descendant::',
			'\#([^\/|{.]+)' => '[@id="\1"]',
			'\.([^\/|{#]+)' => '[contains(concat(" ",@class," ")," \1 ")]',
			'(^|\/|::|\|)([^*\/\w])' => '\1*\2',
		];

		foreach ($replace as $pattern => $replacement) {
			$selector = preg_replace("/$pattern/", $replacement, $selector);
		}

		return $selector;
	}

	public static function xpathPseudo($selector, &$holders)
	{
		$pattern = ':([-\w]+)\(([^()]*)\)';

		$selector = preg_replace_callback("/$pattern/", function($match) use(&$holders){
			list($cond, $func, $value) = $match + array_fill(0, 3, null);

			$cond = '';

			if ($func === 'not') $cond = "[$func(".static::toXPath($value, 'self::').")]";
			elseif ($func === 'has') $cond = "[".static::toXPath($value, 'descendant::')."]";
			elseif ($func === 'eq') $cond = "[".($value + 1)."]";
			elseif ($func === 'contains') $cond = "[$func(text(),$value)]";
			else $cond = "[$func($value)]";

			$holder = '{'.count($holders).'}';
			$holders[$holder] = $cond;
			return $holder;
		}, $selector, -1, $count);

		if ($count > 0) {
			$selector = static::xpathPseudo($selector, $holders);
		}

		return $selector;
	}

	public static function xpathConditions($selector, &$holders)
	{
		$pattern = implode('\s*', [
			'\[',
			'([-+_\w()]+)', // attr
			'(?:([~|^$*]?=)', // op
			'([\'"]?)(.*?)\3', // quote and value
			')?\]',
		]);

		$selector = preg_replace_callback("/$pattern/", function($match) use(&$holders){
			list($cond, $attr, $op, $quote, $value) = $match + array_fill(0, 5, null);

			if (!is_numeric($attr) && strpos($attr, '(') === false) {
				$attr = '@'.$attr;
			}

			$value = (strpos($value, '"') === false) ? '"'.$value.'"' : '\''.$value.'\'';

			if ($op === null) $cond = "[$attr]";
			elseif ($op === '=') $cond = "[$attr=$value]";
			elseif ($op === '~=') $cond = "[contains(concat(' ',$attr,' '),concat(' ',$value,' '))]";
			elseif ($op === '^=') $cond = "[starts-with($attr,$value)]";
			elseif ($op === '$=') $cond = "[ends-with($attr,$value)]";
			elseif ($op === '*=') $cond = "[contains($attr,$value)]";

			$holder = '{'.count($holders).'}';
			$holders[$holder] = $cond;
			return $holder;
		}, $selector);

		return $selector;
	}
}