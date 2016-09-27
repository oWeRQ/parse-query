<?php

class XPathQuery implements IteratorAggregate
{
	private $nodes = [];
	private $xpath;

	public function getIterator()
	{
		$nodes = [];

		foreach ($this->nodes as $node) {
			$nodes[] = new static($node, $this->xpath);
		}

		return new ArrayIterator($nodes);
	}

	public function __construct($nodes = null, $xpath = null)
	{
		if ($nodes instanceof static) {
			$nodes = $nodes->get();
		} elseif (is_object($nodes)) {
			$nodes = [$nodes];
		}

		$this->nodes = (array)$nodes;

		if (!$xpath && !empty($this->nodes)) {
			$this->xpath = new DOMXPath(reset($this->nodes)->ownerDocument);
		} else {
			$this->xpath = $xpath;
		}
	}

	public function length()
	{
		return count($this->nodes);
	}

	public function get($index = null)
	{
		if ($index === null)
			return $this->nodes;

		if (empty($this->nodes[$index]))
			return null;

		return $this->nodes[$index];
	}

	public function eq($index)
	{
		return new static(static::get($index), $this->xpath);
	}

	public function map($callback)
	{
		$result = array_filter(array_map($callback, $this->nodes), function($value){
			return ($value !== null);
		});

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
}