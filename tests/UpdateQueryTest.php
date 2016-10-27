<?php

namespace Parse\Tests;

use PHPUnit\Framework\TestCase;
use Parse\UpdateQuery;

/**
 * Test jQuery-like select, process and update DOM nodes
 */
class UpdateQueryTest extends TestCase
{
	/**
	 * Test local fetch
	 */
	public function testFetch()
	{
		$document = UpdateQuery::fetch(__DIR__.'/fixtures/page1.html');

		$this->assertInstanceOf(UpdateQuery::class, $document);
		$this->assertInstanceOf(\DOMDocument::class, $document->get(0));
		$this->assertCount(1, $document);

		$form = $document->find('form');

		return $form;
	}

	/**
	 * Test update attr
	 *
	 * @depends	testFetch
	 */
	public function testAttr(UpdateQuery $form)
	{
		$action = 'test';
		$this->assertNotSame($action, $form->attr('action'));
		$form->attr('action', $action);
		$this->assertSame($action, $form->attr('action'));
	}

	/**
	 * Test update textContent
	 *
	 * @depends	testFetch
	 */
	public function testText(UpdateQuery $form)
	{
		$text = ' test text ';
		$this->assertNotSame($text, $form->text());
		$form->text($text);
		$this->assertSame($text, $form->text());
	}

	/**
	 * Test update innerHtml
	 *
	 * @depends	testFetch
	 */
	public function testHtml(UpdateQuery $form)
	{
		$html = ' test html ';
		$this->assertNotSame($html, $form->html());
		$form->html($html);
		$this->assertSame($html, $form->html());

		$html = '<p> test <br> html </p>';
		$this->assertNotSame($html, $form->html());
		$form->html($html);
		$this->assertSame($html, $form->html());
	}

	/**
	 * Test remove childs
	 *
	 * @depends	testFetch
	 */
	public function testEmpty(UpdateQuery $form)
	{
		$form->empty();
		$this->assertEmpty($form->html());
	}

	/**
	 * Test remove nodes
	 *
	 * @depends	testFetch
	 */
	public function testRemove(UpdateQuery $form)
	{
		$this->assertCount(1, $form->parent());
		$form->remove();
		$this->assertCount(0, $form->parent());
	}
}