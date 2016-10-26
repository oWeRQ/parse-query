<?php

require_once 'DOMHelper.php';
require_once 'RequestHelper.php';
require_once 'SelectorHelper.php';
require_once 'XPathQuery.php';

/**
 * jQuery like select and process DOM nodes
 */
class ParseQuery extends XPathQuery
{
	/**
	 * Fetch like JS (see RequestHelper::fetch())
	 *
	 * @param string $url Request url or file path
	 * @param array $options Fetch-like options
	 *
	 * @return self
	 */
	public static function fetch($url, array $options = [])
	{
		return static::loadHtml(RequestHelper::fetch($url, $options)->text);
	}

	/**
	 * Load html string
	 *
	 * @param string $html
	 *
	 * @return self
	 */
	public static function loadHtml($html)
	{
		$xpath = DOMHelper::htmlXPath($html);
		return new static($xpath->document, $xpath);
	}

	/**
	 * Find descendants match selector of each
	 *
	 * @param string $selector CSS Selector
	 *
	 * @return self
	 */
	public function find($selector)
	{
		return $this->xpath(SelectorHelper::toXPath($selector));
	}

	/**
	 * Filter match selector
	 *
	 * @param string $selector CSS Selector
	 *
	 * @return self
	 */
	public function filter($selector)
	{
		return $this->xpath(SelectorHelper::toXPath($selector, 'self::'));
	}

	/**
	 * Children of each
	 *
	 * @param string|null $selector CSS Selector
	 *
	 * @return self
	 */
	public function children($selector = null)
	{
		return $this->xpath($selector ? SelectorHelper::toXPath($selector, '') : '*');
	}

	/**
	 * Closest ancestor of each
	 *
	 * @param string $selector CSS Selector
	 *
	 * @return self
	 */
	public function closest($selector)
	{
		return $this->xpath('('.SelectorHelper::toXPath($selector, 'ancestor-or-self::').')[last()]');
	}

	/**
	 * Parents of each
	 *
	 * @param string|null $selector CSS Selector
	 *
	 * @return self
	 */
	public function parents($selector = null)
	{
		return $this->xpath($selector ? SelectorHelper::toXPath($selector, 'ancestor::') : 'ancestor::*');
	}

	/**
	 * Parent of each
	 *
	 * @return self
	 */
	public function parent()
	{
		return $this->map(function($node){
			return $node->parentNode;
		});
	}

	/**
	 * Previous sibling of each
	 *
	 * @return self
	 */
	public function prev()
	{
		return $this->map(function($node){
			while ($node = $node->previousSibling) {
				if ($node->nodeType !== 3)
					return $node;
			}
		});
	}

	/**
	 * Next sibling of each
	 *
	 * @return self
	 */
	public function next()
	{
		return $this->map(function($node){
			while ($node = $node->nextSibling) {
				if ($node->nodeType !== 3)
					return $node;
			}
		});
	}

	/**
	 * First node property value
	 *
	 * @param string $name Property name
	 *
	 * @return string|null
	 */
	public function prop($name)
	{
		return ($node = $this->get(0)) && property_exists($node, $name) ? $node->$name : null;
	}

	/**
	 * First node attribute(s) value
	 *
	 * @param string|null $name Attribute name
	 *
	 * @return string[]|string|null
	 */
	public function attr($name = null)
	{
		if ($node = $this->get(0)) {
			if (!$name)
				return DOMHelper::getAttributes($node);

			if ($node->hasAttribute($name))
				return $node->getAttribute($name);
		}

		return null;
	}

	/**
	 * First node textContent property value
	 *
	 * @return string
	 */
	public function text()
	{
		return $this->prop('textContent');
	}

	/**
	 * First node innerHtml value
	 *
	 * @return string
	 */
	public function html()
	{
		return ($node = $this->get(0)) ? DOMHelper::innerHtml($node) : null;
	}

	/**
	 * First node outerHtml value
	 *
	 * @return string
	 */
	public function outerHtml()
	{
		return ($node = $this->get(0)) ? DOMHelper::outerHtml($node) : null;
	}

	/**
	 * Get nodes count
	 *
	 * @return int
	 */
	public function length()
	{
		return $this->count();
	}

	/**
	 * Magic convert to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->count().' in ['.implode(', ', array_map(['DOMHelper', 'nodeToString'], $this->get())).']';
	}
}