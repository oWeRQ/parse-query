<?php

namespace Parse\Tests;

use PHPUnit\Framework\TestCase;
use Parse\DOMHelper;

/**
 * Test PHP DOM operations
 */
class DOMHelperTest extends TestCase
{
	/**
	 * Test load HTML and get DOMXPath
	 */
	public function testHtmlXPath()
	{
		$html = file_get_contents(__DIR__.'/fixtures/page1.html');
		$xpath = DOMHelper::htmlXPath($html);

		$this->assertInstanceOf(\DOMXPath::class, $xpath);

		return $xpath;
	}

	/**
	 * Test root element behavior
	 *
	 * @depends testHtmlXPath
	 */
	public function testRoot(\DOMXPath $xpath)
	{
		$root = $xpath->document->documentElement;

		$this->assertInstanceOf(\DOMElement::class, $root);
		$this->assertInstanceOf(\DOMDocument::class, $root->ownerDocument);
		$this->assertFalse($root->isSameNode($root->ownerDocument));
		$this->assertTrue($root->isSameNode($root->ownerDocument->documentElement));
		$this->assertEquals('html', $root->tagName);
	}

	/**
	 * Test XPath query behavior
	 *
	 * @depends testHtmlXPath
	 */
	public function testQuery(\DOMXPath $xpath)
	{
		$root = $xpath->document->documentElement;

		$self = $xpath->query('descendant::html', $root)->item(0);

		$this->assertNull($self);

		$form = $xpath->query('descendant::form', $root)->item(0);

		$this->assertInstanceOf(\DOMElement::class, $form);
		$this->assertEquals('form', $form->tagName);
	}

	/**
	 * Test get outer HTML
	 *
	 * @depends testHtmlXPath
	 */
	public function testOuterHtml(\DOMXPath $xpath)
	{
		$small = $xpath->query('descendant::small[1]')->item(0);

		$this->assertEquals('<small><a href="#1.1">sublink1.1</a></small>', DOMHelper::outerHtml($small));
	}

	/**
	 * Test get inner HTML
	 *
	 * @depends testHtmlXPath
	 */
	public function testInnerHtml(\DOMXPath $xpath)
	{
		$small = $xpath->query('descendant::small[1]')->item(0);

		$this->assertEquals('<a href="#1.1">sublink1.1</a>', DOMHelper::innerHtml($small));
	}
	
	/**
	 * Test set inner HTML
	 */
	public function testSetInnerHtml()
	{
		// Load new document
		$xpath = $this->testHtmlXPath();

		$doc = $xpath->document;
		$list = $xpath->query('descendant::div[@id="list"]')->item(0);

		$html = '<b>hello<br>world</b>';

		$result = DOMHelper::setInnerHtml($list, $html);
		$this->assertEquals($html, DOMHelper::innerHtml($list));

		$result = DOMHelper::setInnerHtml($list, '<#not valid');
		$this->assertFalse($result);
		$this->assertEmpty(DOMHelper::innerHtml($list));

		$result = DOMHelper::setInnerHtml($doc, $html);
		$body = $doc->getElementsByTagName('body')->item(0);
		$this->assertEquals($html, DOMHelper::innerHtml($body));
	}

	/**
	 * Test remove child nodes
	 */
	public function testRemoveChildNodes()
	{
		// Load new document
		$xpath = $this->testHtmlXPath();

		$list = $xpath->query('descendant::div[@id="list"]')->item(0);

		$this->assertTrue($list->hasChildNodes());
		DOMHelper::removeChildNodes($list);
		$this->assertFalse($list->hasChildNodes());
	}
	
	/**
	 * Test get all attributes
	 *
	 * @depends testHtmlXPath
	 */
	public function testGetAttributes(\DOMXPath $xpath)
	{
		$input = $xpath->query('descendant::input[@type="text"]')->item(0);

		$this->assertEquals([
			'type' => 'text',
			'name' => 'q',
			'value' => 'test',
		], DOMHelper::getAttributes($input));
	}
	
	/**
	 * Test printable node representation
	 *
	 * @depends testHtmlXPath
	 */
	public function testNodeToString(\DOMXPath $xpath)
	{
		$submit = $xpath->query('descendant::input[@type="submit"]')->item(0);
		$this->assertEquals('input{Search}', DOMHelper::nodeToString($submit));

		$list = $xpath->query('descendant::div[@id="list"]')->item(0);
		$this->assertEquals('div#list{link1 subl...}', DOMHelper::nodeToString($list));

		$item1 = $xpath->query('descendant::span[@class="item item1"]')->item(0);
		$this->assertEquals('span.item.item1{link1 subl...}', DOMHelper::nodeToString($item1));
	}
}

