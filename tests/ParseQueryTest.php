<?php

namespace Parse\Tests;

use PHPUnit\Framework\TestCase;
use Parse\ParseQuery;

/**
 * Test jQuery-like select and process DOM nodes
 */
class ParseQueryTest extends TestCase
{
	/**
	 * Test local fetch
	 */
	public function testFetch()
	{
		$document = ParseQuery::fetch(__DIR__.'/fixtures/page1.html');

		$this->assertInstanceOf(ParseQuery::class, $document);
		$this->assertInstanceOf(\DOMDocument::class, $document->get(0));
		$this->assertCount(1, $document);

		return $document;
	}

	/**
	 * Test find
	 *
	 * @depends testFetch
	 */
	public function testFind(ParseQuery $document)
	{
		$nop = $document->find('nop');
		$list = $document->find('#list');
		$items = $list->find('.item');
		$anchor = $document->find('a');

		$this->assertCount(0, $nop);
		$this->assertCount(0, $nop->find('a'));
		$this->assertCount(1, $list);
		$this->assertCount(3, $items);
		$this->assertCount(6, $anchor);

		return (object)compact('document', 'nop', 'list', 'items', 'anchor');
	}

	/**
	 * Test filter
	 *
	 * @depends testFind
	 */
	public function testFilter($results)
	{
		$this->assertCount(0, $results->list->filter('.item'));
		$this->assertCount(2, $results->items->filter('.item1, .item3'));
	}

	/**
	 * Test children
	 *
	 * @depends testFind
	 */
	public function testChildren($results)
	{
		$this->assertCount(1, $results->document->children());
		$this->assertCount(0, $results->document->children('.item'));
		$this->assertCount(2, $results->list->children('.item1, .item3'));
	}

	/**
	 * Test closest
	 *
	 * @depends testFind
	 */
	public function testClosest($results)
	{
		$this->assertCount(1, $results->list->closest('*'));
		$this->assertCount(1, $results->list->closest('.item, #list'));
		$this->assertCount(1, $results->list->closest('#list'));
		$this->assertCount(1, $results->items->closest('#list'));
		$this->assertCount(3, $results->items->closest('.item, #list'));
		$this->assertCount(3, $results->items->closest('#list, .item'));
		$this->assertCount(3, $results->anchor->closest('.item'));
	}

	/**
	 * Test parents
	 *
	 * @depends testFind
	 */
	public function testParents($results)
	{
		$this->assertCount(2, $results->list->parents());
		$this->assertCount(1, $results->list->parents('body'));
		$this->assertCount(0, $results->list->parents('.list'));
	}

	/**
	 * Test parent
	 *
	 * @depends testFind
	 */
	public function testParent($results)
	{
		$this->assertCount(1, $results->items->parent());
		$this->assertCount(6, $results->anchor->parent());
	}

	/**
	 * Test prev
	 *
	 * @depends testFind
	 */
	public function testPrev($results)
	{
		$this->assertCount(0, $results->anchor->prev());
		$this->assertCount(2, $results->items->prev());
	}

	/**
	 * Test next
	 *
	 * @depends testFind
	 */
	public function testNext($results)
	{
		$this->assertCount(3, $results->anchor->next());
		$this->assertCount(2, $results->items->next());
	}

	/**
	 * Test get first node prop
	 *
	 * @depends testFind
	 */
	public function testProp($results)
	{
		$this->assertSame('a', $results->anchor->prop('tagName'));
		$this->assertNull($results->anchor->prop('src'));
	}

	/**
	 * Test get first node attr
	 *
	 * @depends testFind
	 */
	public function testAttr($results)
	{
		$this->assertSame(['href' => '#1'], $results->anchor->attr());
		$this->assertSame('#1', $results->anchor->attr('href'));
		$this->assertNull($results->anchor->attr('src'));
	}

	/**
	 * Test get first node text
	 *
	 * @depends testFind
	 */
	public function testText($results)
	{
		$this->assertSame('link1', $results->anchor->text());
		$this->assertSame('sublink1.1', $results->anchor->next()->text());
	}

	/**
	 * Test get first node html
	 *
	 * @depends testFind
	 */
	public function testHtml($results)
	{
		$this->assertSame('link1', $results->anchor->html());
		$this->assertSame('<a href="#1.1">sublink1.1</a>', $results->anchor->next()->html());
	}

	/**
	 * Test get first node outerHtml
	 *
	 * @depends testFind
	 */
	public function testOuterHtml($results)
	{
		$this->assertSame('<a href="#1">link1</a>', $results->anchor->outerHtml());
	}

	/**
	 * Test length is same as count
	 *
	 * @depends testFind
	 */
	public function testLength($results)
	{
		$this->assertSame(count($results->nop), $results->nop->length());
		$this->assertSame(count($results->list), $results->list->length());
		$this->assertSame(count($results->items), $results->items->length());
		$this->assertSame(count($results->anchor), $results->anchor->length());
	}

	/**
	 * Test __toString
	 *
	 * @depends testFind
	 */
	public function testMagicToString($results)
	{
		$this->assertSame('6 in [a{link1}, a{sublink1.1}, a{link2}, a{sublink2.1}, a{link3}, a{sublink3.1}]', (string)$results->anchor);
	}
}