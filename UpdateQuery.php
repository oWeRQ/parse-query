<?php

require_once 'ParseQuery.php';

class UpdateQuery extends ParseQuery
{
	public function attr($name, $value = null)
	{
		if ($value === null) {
			return parent::attr($name);
		}

		foreach ($this->get() as $node) {
			$node->setAttribute($name, $value);
		}

		return $this;
	}

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

	public function remove()
	{
		foreach ($this->get() as $node) {
			$node->parentNode->removeChild($node);
		}

		return $this;
	}

	public function __empty()
	{
		foreach ($this->get() as $node) {
			DOMHelper::removeChildNodes($node);
		}

		return $this;
	}

	public function __call($name, array $arguments)
	{
		if ($name === 'empty') {
			return $this->__empty();
		}
	}
}