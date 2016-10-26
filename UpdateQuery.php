<?php

require_once 'ParseQuery.php';

/**
 * jQuery like select, process and update DOM nodes
 */
class UpdateQuery extends ParseQuery
{
	/**
	 * Set attribute for every
	 *
	 * @param string $name Attribute name
	 * @param string $value Attribute value
	 *
	 * @return self
	 */
	public function attr($name = null, $value = null)
	{
		if ($value === null) {
			return parent::attr($name);
		}

		foreach ($this->get() as $node) {
			$node->setAttribute($name, $value);
		}

		return $this;
	}

	/**
	 * Set textContent for every
	 *
	 * @param string $value Text value
	 *
	 * @return self
	 */
	public function text($value = null)
	{
		if ($value === null) {
			return parent::text();
		}

		foreach ($this->get() as $node) {
			$node->textContent = $value;
		}

		return $this;
	}

	/**
	 * Set inner html for every
	 *
	 * @param string $value Html value
	 *
	 * @return self
	 */
	public function html($value = null)
	{
		if ($value === null) {
			return parent::html();
		}

		foreach ($this->get() as $node) {
			DOMHelper::setInnerHtml($node, $value);
		}

		return $this;
	}

	/**
	 * Remove from dom
	 *
	 * @return self
	 */
	public function remove()
	{
		foreach ($this->get() as $node) {
			$node->parentNode->removeChild($node);
		}

		return $this;
	}

	/**
	 * Remove all childs for every
	 *
	 * @return self
	 */
	public function __empty()
	{
		foreach ($this->get() as $node) {
			DOMHelper::removeChildNodes($node);
		}

		return $this;
	}

	/**
	 * Workaround reserved function names
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call($name, array $arguments)
	{
		if ($name === 'empty') {
			return $this->__empty();
		}
	}
}