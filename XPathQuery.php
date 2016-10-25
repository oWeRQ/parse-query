<?php

class XPathQuery implements \IteratorAggregate, \Countable
{
	/**
	 * Array of DOMNode
	 *
	 * @var \DOMNode[] $nodes
	 */
	private $nodes = [];

	/**
	 * Set by Constructor
	 *
	 * @var \DOMXPath $xpath
	 */
	private $xpath;

	/**
	 * Get interator with self objects per node
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		$nodes = [];

		foreach ($this->nodes as $node) {
			$nodes[] = new static($node, $this->xpath);
		}

		return new \ArrayIterator($nodes);
	}

	/**
	 * Constructor
	 *
	 * @param \XPathQuery|\DOMNode|\DOMNode[]|\DOMNodeList $nodes
	 * @param \DOMXPath|null $xpath
	 */
	public function __construct($nodes = null, \DOMXPath $xpath = null)
	{
		if ($nodes instanceof self) {
			$this->nodes = $nodes->get();
		} elseif ($nodes instanceof \DOMNode) {
			$this->nodes = [$nodes];
		} elseif (is_array($nodes) || $nodes instanceof \DOMNodeList) {
			$uniqueNodes = [];

			foreach ($nodes as $node) {
				if ($node instanceof \DOMNode) {
					$uniqueNodes[spl_object_hash($node)] = $node;
				}
			}

			$this->nodes = array_values($uniqueNodes);
		}

		if (!$xpath && $this->nodes) {
			$this->xpath = new \DOMXPath($this->nodes[0]->ownerDocument ?: $this->nodes[0]);
		} else {
			$this->xpath = $xpath;
		}
	}

	/**
	 * Get nodes count
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->nodes);
	}

	/**
	 * Get node by index
	 *
	 * @param int $index If negative return from end
	 *
	 * @return \DOMNode
	 */
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

	/**
	 * Get new instance with node by index
	 *
	 * @param int $index If negative return from end
	 *
	 * @return self
	 */
	public function eq($index)
	{
		return new static(static::get($index), $this->xpath);
	}

	/**
	 * Get new instance with map result
	 *
	 * @param callable $callback Callback($node, $index)
	 *
	 * @return self
	 */
	public function map(callable $callback)
	{
		$result = array_map($callback, $this->nodes, array_keys($this->nodes));

		return new static($result, $this->xpath);
	}

	/**
	 * Get new instance with xpathQuery result of all nodes
	 *
	 * @param string $expression XPath expression
	 * @param int $limit Max result nodes
	 *
	 * @return self
	 */
	public function xpath($expression, $limit = 0)
	{
		$result = [];

		foreach ($this->nodes as $context) {
			foreach ($this->xpathQuery($expression, $context) as $node) {
				$result[] = $node;

				if (--$limit === 0)
					break 2;
			}
		}

		return new static($result, $this->xpath);
	}

	/**
	 * Call XPath query with Exception
	 *
	 * @param string $expression XPath expression
	 * @param \DOMNode $contextnode Optional contextnode for relative XPath queries
	 * @param boolean $registerNodeNS Automatic registration of the context node
	 *
	 * @throws \Exception On any E_WARNING
	 *
	 * @return \DOMNodeList
	 */
	public function xpathQuery($expression, \DOMNode $contextnode = null, $registerNodeNS = true)
	{
		set_error_handler(function($errno, $errstr) use($expression){
			restore_error_handler();
			throw new \Exception($errstr.' "'.$expression.'"', 0);
		}, \E_WARNING);

		$result = $this->xpath->query($expression, $contextnode, $registerNodeNS);

		restore_error_handler();

		return $result;
	}

	/**
	 * Magic get property or attribute
	 *
	 * @param string $name First node property or attribute name
	 *
	 * @return string|null
	 */
	public function __get($name)
	{
		if ($node = $this->get(0)) {
			if (property_exists($node, $name))
				return $node->$name;

			if ($node->hasAttribute($name))
				return $node->getAttribute($name);
		}

		return null;
	}
}