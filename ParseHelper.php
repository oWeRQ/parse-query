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

		file_put_contents($filename, json_encode($data));
	}

	public static function fetch($url, array $options = [])
	{
		$name = md5($url).'.'.md5(json_encode($options));
		$cache = (isset($options['cache']) ? $options['cache'] : 'default');

		if (($response = static::cacheGet($name, $cache)) !== false)
			return (object)$response;

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

		$text = file_get_contents($url, false, $context);
		$headers = static::parseHeaders($http_response_header);
		preg_match('/^HTTP\/\d+\.\d+ (\d+)\s*(.*)$/', array_shift($headers), $status);

		$response = [
			'status' => $status[1],
			'statusText' => $status[2],
			'headers' => $headers,
			'text' => $text,
		];

		$charset = null;

		if (isset($headers['Content-Type']) && preg_match('/^([^;]+)(; charset=([-\w]+))?/', $headers['Content-Type'], $matches)) {
			$response['contentType'] = $matches[1];
			$response['charset'] = $matches[3];

			if ($response['contentType'] === 'application/json') {
				$response['json'] = json_decode($response['text'], true);
			}
		}

		if (isset($options['charset'])) {
			$response['charset'] = $options['charset'];
		}

		if ($response['charset']) {
			$response['text'] = mb_convert_encoding($response['text'], mb_internal_encoding(), $response['charset']);
		}

		static::cachePut($name, $response, $cache);

		return (object)$response;
	}

	public static function htmlXPath($html)
	{
		$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		
		$doc = new DOMDocument();
		@$doc->loadHTML($html);

		return new DOMXpath($doc);
	}

	public static function css2XPath($selector, $context = 'descendant::')
	{
		$selector = $context.trim($selector);
		$holders = [];

		$attr = '/\[\s*([-_\w]+)\s*([~|^$*]?=)\s*[\'"]?([^\]\'"]*)[\'"]?\s*\]/';
		$selector = preg_replace_callback($attr, function($m) use(&$holders){
			if ($m[2] === '=') $value = "[@{$m[1]}=\"{$m[3]}\"]";
			elseif ($m[2] === '~=') $value = "[contains(concat(\" \", @{$m[1]}, \" \"), \" {$m[3]} \")]";
			elseif ($m[2] === '^=') $value = "[starts-with(@{$m[1]}, \"{$m[3]}\")]";
			elseif ($m[2] === '$=') $value = "[ends-with(@{$m[1]}, \"{$m[3]}\")]";
			elseif ($m[2] === '*=') $value = "[contains(@{$m[1]}, \"{$m[3]}\")]";
			else $value = '';

			$key = '{'.count($holders).'}';
			$holders[$key] = $value;
			return $key;
		}, $selector);

		$replace = [
			'\s*,\s*' => '|'.$context,
			'\s*>\s*' => '/',
			'\s*~\s*' => '/following-sibling::',
			'\s*\+\s*' => '/following-sibling::*[1]/self::',
			'\s+' => '//',
			'\#([^\/|.]+)' => '[@id="\1"]',
			'\.([^\/|#]+)' => '[contains(concat(" ", @class, " "), " \1 ")]',
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