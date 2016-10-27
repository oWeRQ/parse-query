<?php

namespace Parse\Tests;

use PHPUnit\Framework\TestCase;
use Parse\SelectorHelper;

/**
 * Test convert CSS selectors to XPath expression
 */
class SelectorHelperTest extends TestCase
{
	/**
	 * Test convert CSS selectors
	 *
	 * @dataProvider selectorsProvider
	 */
	public function testSelector($selector, $expected)
	{
		$expression = SelectorHelper::toXPath($selector);

		$this->assertEquals($expected, $expression);

		return array($expression, $selector);
	}

	/**
	 * Test expected expression valid
	 *
	 * @depends testSelector
	 * @dataProvider selectorsProvider
	 */
	public function testExpression($selector, $expression)
	{
		$xpath = new \DOMXpath(new \DOMDocument());
		$xpath->query($expression);
	}

	/**
	 * Load selectors from fixtures
	 */
	public function selectorsProvider()
	{
		return array_merge(
			include(__DIR__.'/fixtures/selectors-plain.php'),
			include(__DIR__.'/fixtures/selectors-pseudo.php'),
			include(__DIR__.'/fixtures/selectors-condition.php'),
			include(__DIR__.'/fixtures/selectors-complex.php')
		);
	}
}
