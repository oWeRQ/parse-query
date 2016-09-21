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

	public static function fetch($url, array $options = [])
	{
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

		$response = file_get_contents($url, false, $context);
		$headers = static::parseHeaders($http_response_header);

		preg_match('/^HTTP\/(\d+\.\d+) (\d+)\s*(.*)$/', array_shift($headers), $matches);
		$status = [
			'version' => $matches[1],
			'code' => $matches[2],
			'message' => $matches[3],
		];

		if (isset($options['charset'])) {
			$charset = $options['charset'];
		} elseif (isset($headers['Content-Type']) && preg_match('/charset=([-\w]+)/', $headers['Content-Type'], $matches)) {
			$charset = $matches[1];
		} else {
			return $response;
		}

		return mb_convert_encoding($response, mb_internal_encoding(), $charset);
	}

	public static function htmlXPath($html)
	{
		$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		
		$doc = new DOMDocument();
		@$doc->loadHTML($html);

		return new DOMXpath($doc);
	}

	public static function css2XPath($selector, $context = './/')
	{
		$selector = $context.trim($selector);

		$replace = [
			'\s*,\s*' => '|'.$context,
			'\s*>\s*' => '/',
			'\s*~\s*' => '/following-sibling::',
			'\s*\+\s*' => '/following-sibling::*[1]/self::',
			'\s+' => '//',
			'\#([^\/|.]+)' => '[@id = "\1"]',
			'\.([^\/|#]+)' => '[contains(concat(" ", @class, " "), " \1 ")]',
			'(^|\/|::|\|)\[' => '\1*[',
		];

		foreach ($replace as $pattern => $replacement) {
			$selector = preg_replace("/$pattern/", $replacement, $selector);
		}
		
		return $selector;
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