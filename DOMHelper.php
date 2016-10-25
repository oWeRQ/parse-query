<?php

/**
 * Php DOM helper
 */
class DOMHelper
{
	/**
	 * Load html and return DOMXpath
	 *
	 * @param string $html Html
	 * @param boolean $isUtf8 Is convert utf8 to html entities
	 *
	 * @return \DOMXpath
	 */
	public static function htmlXPath($html, $isUtf8 = true)
	{
		if ($isUtf8) {
			$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		}
		
		$doc = new DOMDocument();
		@$doc->loadHTML($html);

		return new DOMXpath($doc);
	}

	/**
	 * Get outer html
	 *
	 * @param DOMNode $node Element or text node or document
	 *
	 * @return string
	 */
	public static function outerHtml(DOMNode $node)
	{
		if ($node->nodeType === XML_TEXT_NODE)
			return $node->textContent;

		if ($node instanceof DOMDocument)
			return $node->saveHTML();

		return $node->ownerDocument->saveHTML($node);
	}

	/**
	 * Get inner html
	 *
	 * @param DOMNode $node Element or text node
	 *
	 * @return string
	 */
	public static function innerHtml(DOMNode $node)
	{
		if ($node->nodeType === XML_TEXT_NODE)
			return $node->textContent;

		$html = '';

		foreach ($node->childNodes as $child) {
			$html .= $node->ownerDocument->saveHTML($child);
		}

		return $html;
	}

	/**
	 * Get all attributes
	 *
	 * @param DOMNode $node
	 *
	 * @return string[]
	 */
	public static function getAttributes(DOMNode $node)
	{
		$attributes = [];

		foreach ($node->attributes as $attribute) {
			$attributes[$attribute->name] = $attribute->value;
		}

		return $attributes;
	}

	/**
	 * Printable node representation
	 *
	 * @param DOMElement $node
	 *
	 * @return string
	 */
	public static function nodeToString(DOMElement $node)
	{
		$id = $node->getAttribute('id');
		$class = $node->getAttribute('class');
		$text = trim(preg_replace('/\s+/', ' ', $node->textContent)) ?: $node->getAttribute('value');

		if (strlen($text) > 10) {
			$text = substr($text, 0, 10).'...';
		}

		return $node->tagName.($id ? '#'.$id : '').($class ? '.'.str_replace(' ', '.', $class) : '').($text ? '{'.$text.'}' : '');
	}
}