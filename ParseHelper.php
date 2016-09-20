<?php

class ParseHelper
{
	public static function headers($headers)
	{
		if (is_string($headers))
			return $headers;

		$headers = array_map(function($key, $value){
			return is_int($key) ? $value : $key.': '.$value;
		}, array_keys($headers), $headers);

		return implode("\r\n", $headers);
	}

	public static function fetch($url, array $options = [])
	{
		$http = (isset($options['http']) && is_array($options['http']) ? $options['http'] : []);

		$http['method'] = (isset($options['method']) ? strtoupper($options['method']) : 'GET');
		$http['content'] = (isset($options['body']) ? $options['body'] : '');
		$http['header'] = (isset($options['headers']) ? static::headers($options['headers']) : '');

		if (is_array($http['content'])) {
			$http['content'] = http_build_query($http['content']);
		}

		$context = stream_context_create([
			'http' => $http,
		]);

		return file_get_contents($url, false, $context);
	}

	public static function htmlXPath($html)
	{
		$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		
		$doc = new DOMDocument();
		@$doc->loadHTML($html);

		$xpath = new DOMXpath($doc);
		return $xpath;
	}

	public static function css2XPath($selector)
	{
		$replace = [
			'\s*,\s*' => '|.//',
			'\s*>\s*' => '/',
			'\s*~\s*' => '/following-sibling::',
			'\s*\+\s*' => '/following-sibling::*[1]/self::',
			'\s+' => '//',
			'\#([^\/|]+)' => '[@id = "\1"]',
			'\.([^\/|]+)' => '[contains(concat(" ", @class, " "), " \1 ")]',
			'(^|\/|::)\[' => '\1*[',
		];

		foreach ($replace as $pattern => $replacement) {
			$selector = preg_replace("/$pattern/", $replacement, $selector);
		}
		
		return './/'.$selector;
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