<?php

require_once 'ParseHelper.php';

class ParseQuery implements IteratorAggregate
{
	protected $nodes = [];
	protected $xpath;

	public function getIterator() {
		return new ArrayIterator($this->nodes);
	}

	public function __construct($nodes = null, $xpath = null)
	{
		if (is_object($nodes))
			$nodes = [$nodes];

		$this->nodes = (array)$nodes;
		$this->xpath = $xpath;
	}

	public static function fetch($url, array $options = [])
	{
		$instance = new static;
		$instance->loadHtml(ParseHelper::fetch($url, $options));
		return $instance;
	}

	public function loadHtml($html)
	{
		$this->xpath = ParseHelper::htmlXPath($html);
		$this->nodes = [$this->xpath->query('.')->item(0)];
	}

	public function push(DOMNode $node)
	{
		$this->nodes[] = $node;
	}

	public function length()
	{
		return count($this->nodes);
	}

	public function get($index = null)
	{
		if ($index === null)
			return $this->nodes;

		if (empty($this->nodes) || empty($this->nodes[$index]))
			return null;

		return $this->nodes[$index];
	}

	public function eq($index)
	{
		return new static(static::get($index), $this->xpath);
	}

	public function map($callback)
	{
		$result = array_filter(array_map($callback, $this->nodes));
		return new static($result, $this->xpath);
	}

	public function xpathQuery($expression)
	{
		$result = [];

		foreach ($this->nodes as $context) {
			foreach ($this->xpath->query($expression, $context) as $node) {
				$result[] = $node;
			}
		}

		return new static($result, $this->xpath);
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
		if ($selector == null)
			return $this->xpathQuery('*');

		return $this->xpathQuery(ParseHelper::css2XPath($selector, ''));
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
		return ($node = $this->get(0)) ? (string)$node[$name] : null;
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