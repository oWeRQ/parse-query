<?php

namespace Parse\Tests;

use PHPUnit\Framework\TestCase;
use Parse\RequestHelper;

/**
 * Test HTTP request
 */
class RequestHelperTest extends TestCase
{
	/**
	 * Test build headers
	 *
	 * @param mixed $headers
	 *
	 * @dataProvider headersProvider
	 */
	public function testBuildHeaders($headers)
	{
		$this->assertEquals("HTTP/1.1 200 OK\r\nContentType: application/json\r\nContent-Length: 0\r\nContent-Length: 100", RequestHelper::buildHeaders($headers));
	}

	/**
	 * Test parse headers
	 *
	 * @param mixed $headers
	 *
	 * @dataProvider headersProvider
	 */
	public function testParseHeaders($headers)
	{
		$this->assertEquals(["HTTP/1.1 200 OK", "ContentType" => "application/json", "Content-Length" => [0, 100]], RequestHelper::parseHeaders($headers));
	}

	/**
	 * Same headers in mixed notation
	 *
	 * @return mixed[]
	 */
	public function headersProvider()
	{
		return [
			[["HTTP/1.1 200 OK", "ContentType" => "application/json", "Content-Length" => [0, 100]]],
			[["HTTP/1.1 200 OK", "ContentType: application/json", "Content-Length: 0", "Content-Length: 100"]],
			["HTTP/1.1 200 OK\r\nContentType: application/json\r\nContent-Length: 0\r\nContent-Length: 100"],
		];
	}

	/**
	 * Test get default cache dir
	 */
	public function testCacheDir()
	{
		$this->assertEquals('cache/', RequestHelper::cacheDir());
	}

	/**
	 * Test put data to cache
	 */
	public function testCachePut()
	{
		$name = 'test.default';

		RequestHelper::cachePut($name, ['foo' => 'bar']);

		return $name;
	}

	/**
	 * Test get data from cache
	 *
	 * @param string $name
	 *
	 * @depends testCachePut
	 */
	public function testCacheGet($name)
	{
		$this->assertEquals(['foo' => 'bar'], RequestHelper::cacheGet($name));
	}

	/**
	 * Test create stream_context_create() options
	 */
	public function testContextOptions()
	{
		$this->assertEquals(['http' => [
			'method' => 'GET',
			'content' => '',
			'header' => '',
		]], RequestHelper::contextOptions([]));

		$this->assertEquals(['http' => [
			'method' => 'POST',
			'content' => 'foo=bar',
			'header' => 'foo: bar',
			'follow_location' => 1,
		]], RequestHelper::contextOptions([
			'method' => 'post',
			'body' => ['foo' => 'bar'],
			'headers' => ['foo' => 'bar'],
			'http' => ['follow_location' => 1]
		]));
	}

	/**
	 * Test get local contents
	 *
	 * @depends testCachePut
	 */
	public function testGetContents()
	{
		$existFilename = RequestHelper::cacheDir().'test.default.json';
		$notExistFilename = $existFilename.'.not-exist';

		$this->assertEquals([
			'text' => '{"foo":"bar"}',
			'error' => false,
			'headers' => [],
		], RequestHelper::getContents($existFilename));

		$this->assertEquals([
			'text' => false,
			'error' => 'file_get_contents('.$notExistFilename.'): failed to open stream: No such file or directory',
			'headers' => [],
		], RequestHelper::getContents($notExistFilename));
	}

	/**
	 * Test process getContents() response
	 */
	public function testProcessResponse()
	{
		$response = RequestHelper::processResponse([
			'headers' => [
				0 => 'HTTP/1.1 200 OK',
				'Content-Type' => 'application/json; charset=UTF-8',
			],
			'text' => '{"foo":"bar"}',
		]);

		$this->assertEquals([
			'headers' => ['Content-Type' => 'application/json; charset=UTF-8'],
			'text' => '{"foo":"bar"}',
			'status' => '200',
			'statusText' => 'OK',
			'contentType' => 'application/json',
			'charset' => 'UTF-8',
			'json' => ['foo' => 'bar'],
		], $response);

		$response = RequestHelper::processResponse([
			'headers' => [
				0 => 'HTTP/1.1 200 OK',
				'Content-Type' => [
					'application/json; charset=iso-8859-1',
					'application/json; charset=UTF-8',
				],
			],
			'text' => '{"foo":"bar"}',
		], [
			'contentType' => 'text/plain',
			'charset' => 'windows-1251',
		]);

		$this->assertEquals([
			'headers' => ['Content-Type' => [
				'application/json; charset=iso-8859-1',
				'application/json; charset=UTF-8',
			]],
			'text' => '{"foo":"bar"}',
			'status' => '200',
			'statusText' => 'OK',
			'contentType' => 'text/plain',
			'charset' => 'windows-1251',
		], $response);
	}

	/**
	 * Test fetch placeholder api
	 *
	 * @todo Add cache test
	 */
	public function _testFetch()
	{
		$root = 'http://jsonplaceholder.typicode.com';

		$this->assertEquals(200, RequestHelper::fetch("$root/posts", ['cache' => 'no-cache'])->status);

		$this->assertEquals(["content" => "Test", "id" => 101], RequestHelper::fetch("$root/posts", [
			'method' => 'post',
			'body' => [
				'content' => 'Test',
			],
		])->json);

		$this->assertEquals('OK', RequestHelper::fetch("$root/posts/1", [
			'method' => 'delete',
		])->statusText);
	}
}