<?php

require_once 'ParseHelper.php';
require_once 'XPathQuery.php';

class ParseQuery extends XPathQuery
{
	public static function fetch($url, array $options = [])
	{
		return static::loadHtml(ParseHelper::fetch($url, $options)->text);
	}

	public static function loadHtml($html)
	{
		$xpath = ParseHelper::htmlXPath($html);
		return new static($xpath->document, $xpath);
	}

	public function find($selector)
	{
		return $this->xpathQuery(ParseHelper::css2XPath($selector));
	}

	public function filter($selector)
	{
		return $this->xpathQuery(ParseHelper::css2XPath($selector, 'self::'));
	}

	public function children($selector = null)
	{
		return $this->xpathQuery($selector ? ParseHelper::css2XPath($selector, '') : '*');
	}

	public function closest($selector)
	{
		return $this->xpathQuery(str_replace('|', '[1]|', ParseHelper::css2XPath($selector, 'ancestor-or-self::')).'[1]');
	}

	public function parents($selector = null)
	{
		return $this->xpathQuery($selector ? ParseHelper::css2XPath($selector, 'ancestor::') : 'ancestor::*');
	}

	public function parent()
	{
		return $this->map(function($node){
			return $node->parentNode;
		});
	}

	public function prev()
	{
		return $this->map(function($node){
			while ($node = $node->previousSibling) {
				if ($node->nodeType !== 3)
					return $node;
			}
		});
	}

	public function next()
	{
		return $this->map(function($node){
			while ($node = $node->nextSibling) {
				if ($node->nodeType !== 3)
					return $node;
			}
		});
	}

	public function prop($name)
	{
		return ($node = $this->get(0)) ? $node->$name : null;
	}

	public function attr($name = null)
	{
		return ($node = $this->get(0)) ? ($name ? $node->getAttribute($name) : ParseHelper::getAttributes($node)) : null;
	}

	public function text()
	{
		return ($node = $this->get(0)) ? $node->textContent : null;
	}

	public function html()
	{
		return ($node = $this->get(0)) ? ParseHelper::innerHtml($node) : null;
	}

	public function outerHtml()
	{
		return ($node = $this->get(0)) ? $node->ownerDocument->saveHTML($node) : null;
	}

	public function __get($name)
	{
		if (method_exists($this, $name))
			return $this->$name();

		if ($node = $this->get(0)) {
			if (isset($node->$name))
				return $node->$name;

			if ($node->hasAttribute($name))
				return $node->getAttribute($name);
		}

		return null;
	}

	public function __toString()
	{
		return '['.implode(', ', array_map(function($node){
			$id = $node->getAttribute('id');
			$class = $node->getAttribute('class');
			$text = trim(preg_replace('/\s+/', ' ', $node->textContent));

			if (empty($text)) {
				$text = $node->getAttribute('value');
			}

			if (strlen($text) > 10) {
				$text = substr($text, 0, 10).'...';
			}

			return $node->tagName.($id ? '#'.$id : '').($class ? '.'.str_replace(' ', '.', $class) : '').($text ? '{'.$text.'}' : '');
		}, $this->get())).']';
	}
}