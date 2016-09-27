<?php

class ParseHelper
{
	public static function buildHeaders($headers)
	{
		if (is_string($headers))
			return $headers;

		$headers = array_map(function($key, $value){
			return is_int($key) ? $value : $key.': '.$value;
		}, array_keys($headers), $headers);

		return implode("\r\n", $headers);
	}

	public static function parseHeaders($rawHeaders)
	{
		if (!is_array($rawHeaders)) {
			$rawHeaders = explode("\r\n", $rawHeaders);
		}

		$headers = [];

		foreach ($rawHeaders as $rawKey => $rawValue) {
			if (is_int($rawKey)) {
				$rawHeader = explode(': ', $rawValue, 2);

				if (count($rawHeader) === 1) {
					$headers[] = $rawValue;
				} else {
					list($key, $value) = $rawHeader;

					if (array_key_exists($key, $headers)) {
						$headers[$key] = (array)$headers[$key];
						$headers[$key][] = $value;
					} else {
						$headers[$key] = $value;
					}
				}
			} else {
				$headers[$rawKey] = $rawValue;
			}
		}

		return $headers;
	}

	public static function cacheDir()
	{
		$dirname = 'cache/';

		if (!file_exists($dirname)) {
			mkdir($dirname);
		}

		return $dirname;
	}

	public static function cacheGet($name, $type = 'default', $time = 3600)
	{
		if ($type === 'no-cache')
			return false;

		$filename = static::cacheDir().$name.'.json';

		if (file_exists($filename) && ($type === 'force-cache' || filemtime($filename) > time() - $time)) {
			return json_decode(file_get_contents($filename), true);
		}

		return false;
	}

	public static function cachePut($name, $data, $type = 'default')
	{
		if ($type === 'no-cache')
			return;

		$filename = static::cacheDir().$name.'.json';

		$json = json_encode($data);

		if (!empty($json)) {
			file_put_contents($filename, $json);
		}
	}

	public static function fetch($url, array $options = [])
	{
		$response = [
			'error' => false,
			'status' => false,
			'statusText' => false,
			'headers' => [],
			'contentType' => false,
			'charset' => false,
			'text' => false,
			'json' => false,
		];

		$name = md5($url).'.'.md5(json_encode($options));
		$cache = (isset($options['cache']) ? $options['cache'] : 'default');

		if (($responseCache = static::cacheGet($name, $cache)) !== false)
			return (object)array_merge($response, (array)$responseCache);

		$http = (isset($options['http']) && is_array($options['http']) ? $options['http'] : []);

		$http['method'] = (isset($options['method']) ? strtoupper($options['method']) : 'GET');
		$http['content'] = (isset($options['body']) ? $options['body'] : '');
		$http['header'] = (isset($options['headers']) ? static::buildHeaders($options['headers']) : '');

		if (is_array($http['content'])) {
			$http['content'] = http_build_query($http['content']);
		}

		$context = stream_context_create([
			'http' => $http,
		]);

		$response['text'] = @file_get_contents($url, false, $context);
		$response['error'] = ($response['text'] === false ? error_get_last()['message'] : false);
		$response['headers'] = isset($http_response_header) ? static::parseHeaders($http_response_header) : [];

		preg_match('/^HTTP\/\d+\.\d+ (\d+)\s*(.*)$/', array_shift($response['headers']), $status);
		if (count($status) > 2) {
			list(, $response['status'], $response['statusText']) = $status;
		}

		if (isset($response['headers']['Content-Type']) && is_array($response['headers']['Content-Type'])) {
			$response['headers']['Content-Type'] = end($response['headers']['Content-Type']);
		}

		if (isset($response['headers']['Content-Type']) && preg_match('/^([^;]+)(?:;\s*charset=([-\w]+))?/', $response['headers']['Content-Type'], $matches)) {
			if (isset($matches[1])) {
				$response['contentType'] = $matches[1];
			}
			if (isset($matches[2])) {
				$response['charset'] = $matches[2];
			}
		}

		if (isset($options['contentType'])) {
			$response['contentType'] = $options['contentType'];
		}

		if (isset($options['charset'])) {
			$response['charset'] = $options['charset'];
		}

		if ($response['charset']) {
			$response['text'] = mb_convert_encoding($response['text'], mb_internal_encoding(), $response['charset']);
		}

		if ($response['contentType'] === 'application/json') {
			$response['json'] = json_decode($response['text'], true);
		}

		if ($http['method'] === 'GET') {
			static::cachePut($name, $response, $cache);
		}

		return (object)$response;
	}

	public static function htmlXPath($html)
	{
		$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		
		$doc = new DOMDocument();
		@$doc->loadHTML($html);

		return new DOMXpath($doc);
	}

	public static function css2XPathConditions($selector, &$holders)
	{
		$pattern = implode('\s*', [
			'\[',
			'([-+_\w()]+)', // attr
			'(?:([~|^$*]?=)', // op
			'([\'"]?)([^\]\'"]*)\3', // quote and value
			')?\]',
		]);

		$selector = preg_replace_callback("/$pattern/", function($match) use(&$holders){
			list($cond, $attr, $op, $quote, $value) = $match + array_fill(0, 5, null);

			if (!is_numeric($attr) && strpos($attr, '(') === false) {
				$attr = '@'.$attr;
			}

			$value = '"'.$value.'"';

			if ($op === null) $cond = "[$attr]";
			elseif ($op === '=') $cond = "[$attr=$value]";
			elseif ($op === '~=') $cond = "[contains(concat(' ',$attr,' '),concat(' ',$value,' '))]";
			elseif ($op === '^=') $cond = "[starts-with($attr,$value)]";
			elseif ($op === '$=') $cond = "[ends-with($attr,$value)]";
			elseif ($op === '*=') $cond = "[contains($attr,$value)]";

			$holder = '{'.count($holders).'}';
			$holders[$holder] = $cond;
			return $holder;
		}, $selector);

		return $selector;
	}

	public static function css2XPath($selector, $context = 'descendant::')
	{
		$selector = $context.trim($selector);
		$holders = [];

		$selector = static::css2XPathConditions($selector, $holders);

		$replace = [
			'\s*,\s*' => '|'.$context,
			'\s*>\s*' => '/',
			'\s*~\s*' => '/following-sibling::',
			'\s*\+\s*' => '/following-sibling::*[1]/self::',
			'\s+' => '//',
			'\#([^\/|{.]+)' => '[@id="\1"]',
			'\.([^\/|{#]+)' => '[contains(concat(" ",@class," ")," \1 ")]',
			'(^|\/|::|\|)([^*\/\w])' => '\1*\2',
		];

		foreach ($replace as $pattern => $replacement) {
			$selector = preg_replace("/$pattern/", $replacement, $selector);
		}

		return strtr($selector, $holders);
	}

	public static function innerHtml(DOMNode $element)
	{
		if ($element->nodeType === 3)
			return $element->textContent;

		$html = '';

		foreach ($element->childNodes as $child) {
			$html .= $element->ownerDocument->saveHTML($child);
		}

		return $html;
	}
}