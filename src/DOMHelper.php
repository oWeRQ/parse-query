<?php

namespace Parse;

/**
 * PHP DOM helper
 */
class DOMHelper
{
	/**
	 * Get outer HTML
	 *
	 * @param DOMNode $node Element or text node or document
	 *
	 * @return string
	 */
	public static function outerHtml(\DOMNode $node)
	{
		if ($node->nodeType === XML_TEXT_NODE)
			return $node->textContent;

		if ($node instanceof \DOMDocument)
			return $node->saveHTML($node);

		return $node->ownerDocument->saveHTML($node);
	}

	/**
	 * Get inner HTML
	 *
	 * @param DOMNode $node Element or text node or document
	 *
	 * @return string
	 */
	public static function innerHtml(\DOMNode $node)
	{
		if ($node->nodeType === XML_TEXT_NODE)
			return $node->textContent;

		if ($node instanceof \DOMDocument)
			return $node->saveHTML($node);

		$html = '';

		foreach ($node->childNodes as $child) {
			$html .= $node->ownerDocument->saveHTML($child);
		}

		return $html;
	}

	/**
	 * Set inner HTML
	 *
	 * @param DOMNode $node Element or text node or document
	 * @param string $value
	 *
	 * @return boolean
	 */
	public static function setInnerHtml(\DOMNode $node, $value)
	{
		if ($node instanceof \DOMDocument) {
			return @$node->loadHTML($value);
		}

		static::removeChildNodes($node);

		$fragment = $node->ownerDocument->createDocumentFragment();

		if (@$fragment->appendXML($value)) {
			$node->appendChild($fragment);

			return true;
		} else {
			$doc = new \DOMDocument();

			if (@$doc->loadHTML($value) && $body = $doc->getElementsByTagName('body')->item(0)) {
				foreach ($body->childNodes as $child) {
					$node->appendChild($node->ownerDocument->importNode($child, true));
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Remove child nodes
	 *
	 * @param DOMNode $node Element or text node or document
	 *
	 * @return void
	 */
	public static function removeChildNodes(\DOMNode $node)
	{
		for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
			$node->removeChild($node->childNodes->item($i));
		}
	}

	/**
	 * Get all attributes
	 *
	 * @param DOMNode $node
	 *
	 * @return string[]
	 */
	public static function getAttributes(\DOMNode $node)
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
	 * @param DOMNode $node
	 *
	 * @return string
	 */
	public static function nodeToString(\DOMNode $node)
	{
		$text = trim(preg_replace('/\s+/', ' ', $node->textContent));

		if ($node instanceof \DOMElement) {
			$id = $node->getAttribute('id');
			$class = $node->getAttribute('class');
			$text = $text ?: $node->getAttribute('value');
		} else {
			$id = $class = null;
		}

		if (strlen($text) > 10) {
			$text = substr($text, 0, 10).'...';
		}

		return str_replace('#', '@', $node->nodeName).($id ? '#'.$id : '').($class ? '.'.str_replace(' ', '.', $class) : '').($text ? '{'.$text.'}' : '');
	}
}