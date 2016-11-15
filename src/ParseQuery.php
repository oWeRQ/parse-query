<?php

namespace Parse;

/**
 * jQuery-like select and process DOM nodes
 */
class ParseQuery extends XPathQuery
{
	/**
	 * Fetch JS-like
	 *
	 * @param string $url Request url or file path
	 * @param array $options Fetch-like options
	 *
	 * @return self
	 *
	 * @see RequestHelper::fetch() Shortcut
	 */
	public static function fetch($url, array $options = [])
	{
		return static::loadHtml(RequestHelper::fetch($url, $options)->text);
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
	 * @param string $selector CSS Selector
	 *
	 * @return self
	 */
	public function parent($selector = null)
	{
		return ($selector ? $this->xpath(SelectorHelper::toXPath($selector, 'parent::')) : parent::parent());
	}

	/**
	 * Previous sibling of each
	 *
	 * @param string $selector CSS Selector
	 *
	 * @return self
	 */
	public function prev($selector = null)
	{
		return ($selector ? $this->xpath(SelectorHelper::toXPath($selector, 'preceding-sibling::*[1]/self::')) : parent::prev());
	}

	/**
	 * Next sibling of each
	 *
	 * @param string $selector CSS Selector
	 *
	 * @return self
	 */
	public function next($selector = null)
	{
		return ($selector ? $this->xpath(SelectorHelper::toXPath($selector, 'following-sibling::*[1]/self::')) : parent::next());
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
		return $this->count().' in ['.implode(', ', array_map([DOMHelper::class, 'nodeToString'], $this->get())).']';
	}
}