<?php

namespace Parse;

/**
 * PHP DOM node and xpath wrapper
 *
 * Implements base interface like jQuery
 */
class XPathQuery implements \IteratorAggregate, \Countable
{
	/** @var \DOMNode[] Array of DOMNode, use get() method to read */
	private $nodes = [];

	/** @var \DOMXPath Set by Constructor */
	private $xpath = null;

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
	 * @param self|\DOMNode|\DOMNode[]|\DOMNodeList $nodes
	 * @param \DOMXPath $xpath
	 */
	public function __construct($nodes = null, \DOMXPath $xpath = null)
	{
		if ($nodes instanceof self) {
			$this->nodes = $nodes->get();
		} elseif ($nodes instanceof \DOMNode) {
			$this->nodes = [$nodes];
		} elseif ($nodes instanceof \DOMNodeList) {
			$this->nodes = iterator_to_array($nodes, false);
		} elseif (is_array($nodes)) {
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
	 * Load html string
	 *
	 * @param string $html
	 * @param string $encoding
	 *
	 * @return self
	 */
	public static function loadHtml($html, $encoding = 'utf-8')
	{
		$html = mb_convert_encoding($html, 'html-entities', $encoding);

		$doc = new \DOMDocument();
		@$doc->loadHTML($html);

		return new static($doc);
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

			if ($node instanceof \DOMElement && $node->hasAttribute($name))
				return $node->getAttribute($name);
		}

		return null;
	}
}