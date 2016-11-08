<?php

namespace Parse;

/**
 * PHP DOM helper
 */
class DOMHelper
{
	/**
	 * Load HTML and return DOMXPath
	 *
	 * @param string $html Html
	 * @param boolean $isUtf8 Is convert utf8 to html entities
	 *
	 * @return \DOMXPath
	 */
	public static function htmlXPath($html, $isUtf8 = true)
	{
		if ($isUtf8) {
			$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		}
		
		$doc = new \DOMDocument();
		@$doc->loadHTML($html);

		return new \DOMXPath($doc);
	}

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
			return $node->saveHTML();

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
			return $node->saveHTML();

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
	 * @param DOMElement $node
	 *
	 * @return string
	 */
	public static function nodeToString(\DOMElement $node)
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