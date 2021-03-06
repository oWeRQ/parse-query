<?php

namespace Parse;

/**
 * HTTP request helper
 */
class RequestHelper
{
	/**
	 * Build headers to string
	 *
	 * @param string|string[] $headers String or string-array or hash-array
	 *
	 * @return string
	 */
	public static function buildHeaders($headers)
	{
		if (is_string($headers))
			return $headers;

		$headers = array_map(function($key, $value){
			if (is_array($value)) {
				$headers = [];

				foreach ($value as $headerValue) {
					$headers[] = $key.': '.$headerValue;
				}
			
				return implode("\r\n", $headers);
			}

			return is_int($key) ? $value : $key.': '.$value;
		}, array_keys($headers), $headers);

		return implode("\r\n", $headers);
	}

	/**
	 * Parse headers (e.g. $http_response_header)
	 *
	 * Example
     *
	 * Input:
	 * ['Content-Type' => 'text/plain', 'Content-Type: text/html']
	 *
	 * Output:
	 * ['Content-Type' => ['text/plain', 'text/html']]
	 *
	 * @param string|string[] $rawHeaders String or string-array or hash-array
	 *
	 * @return mixed[]
	 */
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

	/**
	 * Parse Content-Type header
	 *
	 * @param mixed $header Content-Type header value string or array
	 *
	 * @return string[]
	 */
	public static function parseContentType($header)
	{
		if (is_array($header)) {
			$header = end($header);
		}

		$result = [
			'contentType' => null,
			'charset' => null,
		];

		if (preg_match('/^([^;]+)(?:;\s*charset=([-\w]+))?/', $header, $matches)) {
			list(, $result['contentType'], $result['charset']) = $matches + array_fill(0, 3, null);
		}

		return $result;
	}

	/**
	 * Get available to write dir for request cache
	 *
	 * @return string
	 */
	public static function cacheDir()
	{
		$dirname = 'cache/';

		if (!file_exists($dirname)) {
			mkdir($dirname);
		}

		return $dirname;
	}

	/**
	 * Get response data from cache
	 *
	 * @param string $name Record name
	 * @param string $type Values: default|no-cache|force-cache
	 * @param int $time Seconds
	 *
	 * @return array|false
	 */
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

	/**
	 * Put response data to cache
	 *
	 * @param string $name Record name
	 * @param array $data Record data
	 * @param string $type Values: default|no-cache|force-cache
	 *
	 * @return void
	 */
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

	/**
	 * Convert fetch-like options to stream_context_create() options
	 * 
	 * @param array $options Fetch-like options
	 * 
	 * @return array
	 */
	public static function contextOptions(array $options = [])
	{
		$http = (isset($options['http']) && is_array($options['http']) ? $options['http'] : []);

		$http['method'] = (isset($options['method']) ? strtoupper($options['method']) : 'GET');
		$http['content'] = (isset($options['body']) ? $options['body'] : '');
		$http['header'] = (isset($options['headers']) ? static::buildHeaders($options['headers']) : '');

		if (is_array($http['content'])) {
			$http['content'] = http_build_query($http['content']);
		}

		return [
			'http' => $http,
		];
	}

	/**
	 * Get contents with error and headers
	 *
	 * @param string $url Request url or file path
	 * @param array $contextOptions Options for stream_context_create()
	 *
	 * @return mixed[]
	 */
	public static function getContents($url, array $contextOptions = [])
	{
		$response = [];

		$response['text'] = @file_get_contents($url, false, stream_context_create($contextOptions));
		$response['error'] = ($response['text'] === false ? error_get_last()['message'] : false);
		$response['headers'] = isset($http_response_header) ? static::parseHeaders($http_response_header) : [];

		return $response;
	}

	/**
	 * Process response
	 *
	 * @param array $response Has keys: string text, array error, array headers
	 * @param array $options Fetch-like options
	 *
	 * @return mixed[]
	 */
	public static function processResponse(array $response, array $options = [])
	{
		if (isset($response['headers'][0]) && preg_match('/^HTTP\/\d+\.\d+ (\d+)\s*(.*)$/', $response['headers'][0], $status)) {
			unset($response['headers'][0]);
			list(, $response['status'], $response['statusText']) = $status;
		}

		if (isset($response['headers']['Content-Type'])) {
			$response += static::parseContentType($response['headers']['Content-Type']);
		}

		if (isset($options['contentType'])) {
			$response['contentType'] = $options['contentType'];
		}

		if (isset($options['charset'])) {
			$response['charset'] = $options['charset'];
		} else {
			$response['charset'] = mb_internal_encoding();
		}

		if (!empty($response['contentType']) && $response['contentType'] === 'application/json') {
			$response['json'] = json_decode($response['text'], true);
		}

		return $response;
	}

	/**
	 * Fetch JS-like
	 *
	 * @param string $url Request url or file path
	 * @param array $options Fetch-like options: cache, http, method, body, headers, contentType, charset
	 *
	 * @return \stdClass Has properties: error, status, statusText, headers, contentType, charset, text, json
	 */
	public static function fetch($url, array $options = [])
	{
		$contextOptions = static::contextOptions($options);

		$cacheName = md5($url).'.'.md5(json_encode($contextOptions));
		$cacheType = (isset($options['cache']) ? $options['cache'] : 'default');

		if (($response = static::cacheGet($cacheName, $cacheType)) === false) {
			$response = static::getContents($url, $contextOptions);

			if ($response['headers'] && $contextOptions['http']['method'] === 'GET') {
				static::cachePut($cacheName, $response, $cacheType);
			}
		}

		return (object)static::processResponse($response, $options);
	}
}