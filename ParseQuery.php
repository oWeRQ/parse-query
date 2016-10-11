<?php

require_once 'DOMHelper.php';
require_once 'RequestHelper.php';
require_once 'XPathHelper.php';
require_once 'XPathQuery.php';

class ParseQuery extends XPathQuery
{
	public static function fetch($url, array $options = [])
	{
		return static::loadHtml(RequestHelper::fetch($url, $options)->text);
	}

	public static function loadHtml($html)
	{
		$xpath = DOMHelper::htmlXPath($html);
		return new static($xpath->document, $xpath);
	}

	public function find($selector)
	{
		return $this->xpath(XPathHelper::toXPath($selector));
	}

	public function filter($selector)
	{
		return $this->xpath(XPathHelper::toXPath($selector, 'self::'));
	}

	public function children($selector = null)
	{
		return $this->xpath($selector ? XPathHelper::toXPath($selector, '') : '*');
	}

	public function closest($selector)
	{
		return $this->xpath('('.XPathHelper::toXPath($selector, 'ancestor-or-self::').')[last()]');
	}

	public function parents($selector = null)
	{
		return $this->xpath($selector ? XPathHelper::toXPath($selector, 'ancestor::') : 'ancestor::*');
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
		return ($node = $this->get(0)) ? ($name ? $node->getAttribute($name) : DOMHelper::getAttributes($node)) : null;
	}

	public function text()
	{
		return ($node = $this->get(0)) ? $node->textContent : null;
	}

	public function html()
	{
		return ($node = $this->get(0)) ? DOMHelper::innerHtml($node) : null;
	}

	public function outerHtml()
	{
		return ($node = $this->get(0)) ? DOMHelper::outerHtml($node) : null;
	}

	public function __toString()
	{
		return $this->length().' in ['.implode(', ', array_map(['DOMHelper', 'nodeToString'], $this->get())).']';
	}
}