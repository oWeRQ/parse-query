<?php

namespace Parse\Tests;

use PHPUnit\Framework\TestCase;
use Parse\XPathQuery;

/**
 * Test PHP DOM node and xpath wrapper
 */
class XPathQueryTest extends TestCase
{
	/**
	 * Test construct from DOMDocument
	 */
	public function testConstruct()
	{
		$doc = new \DOMDocument();
		$doc->loadHTMLFile(__DIR__.'/fixtures/page1.html');

		$queryFromDoc = new XPathQuery($doc);

		$queryFromQuery = new XPathQuery($queryFromDoc);
		$this->assertSame($queryFromDoc->documentElement, $queryFromQuery->documentElement);
		$this->assertNotSame($queryFromDoc, $queryFromQuery);

		$queryFromNode = new XPathQuery($doc->documentElement);
		$this->assertSame('html', $queryFromNode->tagName);

		$queryFromNodeArray = new XPathQuery([$doc->documentElement]);
		$this->assertSame('html', $queryFromNodeArray->tagName);

		$queryFromNodeList = new XPathQuery($doc->getElementsByTagName('html'));
		$this->assertSame('html', $queryFromNodeList->tagName);

		return $queryFromDoc;
	}

	/**
	 * Test xpathQuery invalid expression
	 *
	 * @param XPathQuery $page
	 *
	 * @expectedException Exception
	 * @depends testConstruct
	 */
	public function testXpathQuery(XPathQuery $document)
	{
		$document->xpathQuery('self::');
	}

	/**
	 * Test xpath chain
	 *
	 * @depends testConstruct
	 */
	public function testXpath(XPathQuery $document)
	{
		$smalls = $document->xpath('descendant::small');
		$this->assertInstanceOf(XPathQuery::class, $smalls);

		$anchors = $smalls->xpath('descendant::a');
		$this->assertInstanceOf(XPathQuery::class, $anchors);

		return compact('document', 'smalls', 'anchors');
	}

	/**
	 * Test found nodes count
	 *
	 * @depends testXpath
	 */
	public function testCount($queries)
	{
		$this->assertSame(1, $queries['document']->count());
		$this->assertSame(3, $queries['smalls']->count());
		$this->assertSame(3, $queries['anchors']->count());
	}

	/**
	 * Test get nodes
	 *
	 * @depends testXpath
	 */
	public function testGet($queries)
	{
		$document = $queries['document']->get(0);

		$this->assertInstanceOf(\DOMDocument::class, $document);
		$this->assertSame([$document], $queries['document']->get());
		$this->assertSame($document, $queries['document']->get(-1));
		$this->assertSame($queries['smalls']->get(0), $queries['smalls']->get(-3));
		$this->assertNull($queries['smalls']->get(3));
		$this->assertNull($queries['smalls']->get(-4));
	}

	/**
	 * Test eq object
	 *
	 * @depends testConstruct
	 */
	public function testEq(XPathQuery $document)
	{
		$this->assertNotSame($document->eq(0), $document->eq(0));
		$this->assertSame($document->eq(0)->get(0), $document->eq(0)->get(0));
		$this->assertSame($document->eq(0)->get(0), $document->eq(-1)->get(0));
	}

	/**
	 * Test iterator
	 *
	 * @depends testXpath
	 */
	public function testGetIterator($queries)
	{
		$iterator = $queries['smalls']->getIterator();

		$this->assertInstanceOf(\ArrayIterator::class, $iterator);
		$this->assertSame(3, $iterator->count());
		$this->assertSame($iterator->current()->get(0), $queries['smalls']->get(0));
	}

	/**
	 * Test map nodes
	 *
	 * @depends testXpath
	 */
	public function testMap($queries)
	{
		$smallsOddChild = $queries['smalls']->map(function($node, $i){
			if ($i % 2 === 1)
				return;

			return $node->firstChild;
		});

		$this->assertSame(2, $smallsOddChild->count());
		$this->assertSame('a', $smallsOddChild->get(0)->tagName);
		$this->assertSame('sublink3.1', $smallsOddChild->get(1)->textContent);
	}

	/**
	 * Test __get method
	 *
	 * @depends testXpath
	 */
	public function testMagicGet($queries)
	{
		$this->assertSame("sublink1.1", $queries['anchors']->textContent);
		$this->assertSame("#1.1", $queries['anchors']->href);
		$this->assertNull($queries['anchors']->undef);
	}
}