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
		$this->nodes = [];
		$this->xpath = ParseHelper::htmlXPath($html);
	}

	public function push(DOMNode $node)
	{
		$this->nodes[] = $node;
	}

	public function length()
	{
		return count($this->nodes);
	}

	public function get($index)
	{
		if (empty($this->nodes) || empty($this->nodes[$index]))
			return null;

		return $this->nodes[$index];
	}

	public function eq($index)
	{
		return new static(static::get($index), $this->xpath);
	}

	public function find($selector)
	{
		$result = new static(null, $this->xpath);

		$expression = ParseHelper::css2XPath($selector);

		foreach ($this->nodes ?: [null] as $context) {
			foreach ($this->xpath->query($expression, $context) as $node) {
				$result->push($node);
			}
		}

		return $result;
	}

	public function children()
	{
		$result = new static(null, $this->xpath);

		foreach ($this->nodes as $context) {
			foreach ($context->childNodes as $node) {
				if ($node->nodeType !== 3) {
					$result->push($node);
				}
			}
		}

		return $result;
	}

	public function prev()
	{
		$result = new static(null, $this->xpath);

		foreach ($this->nodes as $node) {
			while ($node = $node->previousSibling) {
				if ($node->nodeType !== 3) {
					$result->push($node);
					break;
				}
			}
		}

		return $result;
	}

	public function next()
	{
		$result = new static(null, $this->xpath);

		foreach ($this->nodes as $node) {
			while ($node = $node->nextSibling) {
				if ($node->nodeType !== 3) {
					$result->push($node);
					break;
				}
			}
		}

		return $result;
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