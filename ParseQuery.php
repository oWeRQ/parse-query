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
		return new static($xpath->query('.')->item(0), $xpath);
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

	public function attr($name)
	{
		return ($node = $this->get(0)) ? $node->getAttribute($name) : null;
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
}