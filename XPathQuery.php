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
			$this->nodes = $nodes->get();
		} elseif ($nodes instanceof DOMNode) {
			$this->nodes = [$nodes];
		} else {
			foreach ((array)$nodes as $node) {
				if (!$node instanceof DOMNode)
					continue;

				foreach ($this->nodes as $prev) {
					if ($node === $prev)
						continue 2;
				}

				$this->nodes[] = $node;
			}
		}

		if (!$xpath && !empty($this->nodes)) {
			$this->xpath = new DOMXPath($this->nodes[0]->ownerDocument ?: $this->nodes[0]);
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

		if ($index < 0)
			$index += count($this->nodes);

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
		$result = array_filter(array_map($callback, $this->nodes, array_keys($this->nodes)), function($value){
			return ($value !== null);
		});

		return new static($result, $this->xpath);
	}

	public function xpathQuery($expression, $limit = 0)
	{
		$result = [];

		foreach ($this->nodes as $context) {
			foreach ($this->xpath->query($expression, $context) as $node) {
				$result[] = $node;

				if (--$limit === 0)
					break 2;
			}
		}

		return new static($result, $this->xpath);
	}
}