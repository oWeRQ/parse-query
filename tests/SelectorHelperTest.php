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
	 * @param $selector CSS selector
	 * @param $expression XPath expression
	 *
	 * @dataProvider selectorsProvider
	 */
	public function testSelector($selector, $expression)
	{
		$this->assertEquals($expression, SelectorHelper::toXPath($selector));
	}

	/**
	 * Test expected expression valid
	 *
	 * @param $selector CSS selector
	 * @param $expression XPath expression
	 *
	 * @dataProvider selectorsProvider
	 */
	public function testExpression($selector, $expression)
	{
		$xpath = new \DOMXpath(new \DOMDocument());
		$xpath->query($expression);
	}

	/**
	 * Load selectors from fixtures
	 *
	 * @return array[]
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
