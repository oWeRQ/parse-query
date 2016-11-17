<?php

namespace Parse;

/**
 * CSS selector helper
 */
class SelectorHelper
{
	/**
	 * Convert CSS selector with pseudo and conditions to XPath expression
	 *
	 * @param string $selector CSS Selector
	 * @param string $prefix XPath expression
	 *
	 * @return string
	 */
	public static function toXPath($selector, $prefix = 'descendant::')
	{
		$holders = [];

		$selector = static::holdXPathPseudo($selector, $holders);
		$selector = static::holdXPathConditions($selector, $holders);

		$selector = static::toXPathPlain($selector, $prefix);

		foreach (array_reverse($holders) as $holder => $value) {
			$selector = str_replace($holder, $value, $selector);
		}

		return $selector;
	}

	/**
	 * Convert plain CSS selector to XPath expression
	 *
	 * @param string $selector CSS Selector
	 * @param string $prefix XPath expression
	 *
	 * @return string
	 */
	public static function toXPathPlain($selector, $prefix = 'descendant::')
	{
		$xpaths = [];

		$selectors = preg_split('/\s*,\s*/', trim($selector));

		foreach ($selectors as $selector) {
			$xpath = static::toXPathSingle($selector);
			$xpaths[] = ($xpath[0] !== '/' ? $prefix.$xpath : substr($xpath, 1));
		}

		return implode('|', $xpaths);
	}

	/**
	 * Convert single CSS selector to XPath expression
	 *
	 * @param string $selector CSS Selector
	 *
	 * @return string
	 */
	public static function toXPathSingle($selector)
	{
		$replace = [
			'\s*>\s*' => '/',
			'\s*~\s*' => '/following-sibling::',
			'\s*\+\s*' => '/following-sibling::*[1]/self::',
			'\s+' => '/descendant::',
			'\#([\w_-]+)' => '[@id="\1"]',
			//'\.([\w_-]+)' => '[contains(concat(" ",normalize-space(@class)," ")," \1 ")]',
			'\.([\w_-]+)' => '[contains(concat(" ",@class," ")," \1 ")]',
			'(?<=^|\||\/|::)(?![*\w\/])' => '*',
		];

		foreach ($replace as $pattern => $replacement) {
			$selector = preg_replace("/$pattern/", $replacement, $selector);
		}

		return $selector;
	}

	/**
	 * Replace with holders pseudo
	 *
	 * @param string $selector CSS Selector
	 * @param string[] $holders Holders array
	 *
	 * @return string
	 */
	public static function holdXPathPseudo($selector, array &$holders)
	{
		$pattern = ':([-\w]+)\(([^()]*)\)';

		$selector = preg_replace_callback("/$pattern/", function($match) use(&$holders){
			list(, $func, $value) = $match + array_fill(0, 3, null);

			switch ($func) {
				case 'not': $cond = '[not('.static::toXPath($value, 'self::').')]'; break;
				case 'has': $cond = '['.static::toXPath($value, 'descendant::').']'; break;
				case 'eq': $cond = '['.($value < 0 ? 'last()' : '').($value + 1 ?: '').']'; break;
				case 'lt': $cond = '[position()'.($value < 0 ? '-last()' : '').'<'.($value + 1).']'; break;
				case 'gt': $cond = '[position()'.($value < 0 ? '-last()' : '').'>'.($value + 1).']'; break;
				case 'contains': $cond = '[contains(text(),'.$value.')]'; break;
				default: $cond = '['.$func.'('.$value.')]';
			}

			$holder = '{'.count($holders).'}';
			$holders[$holder] = $cond;
			return $holder;
		}, $selector, -1, $count);

		if ($count > 0) {
			$selector = static::holdXPathPseudo($selector, $holders);
		}

		return $selector;
	}

	/**
	 * Replace with holders conditions
	 *
	 * @param string $selector CSS Selector
	 * @param string[] $holders Holders array
	 *
	 * @return string
	 */
	public static function holdXPathConditions($selector, array &$holders)
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

			switch ($op) {
				case null: $cond = "[$attr]"; break;
				case '=': $cond = "[$attr=$value]"; break;
				case '~=': $cond = "[contains(concat(' ',$attr,' '),concat(' ',$value,' '))]"; break;
				case '^=': $cond = "[starts-with($attr,$value)]"; break;
				case '$=': $cond = "[ends-with($attr,$value)]"; break;
				case '*=': $cond = "[contains($attr,$value)]"; break;
			}

			$holder = '{'.count($holders).'}';
			$holders[$holder] = $cond;
			return $holder;
		}, $selector);

		return $selector;
	}
}