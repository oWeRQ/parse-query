<?php

require_once '../RequestHelper.php';

$root = 'http://jsonplaceholder.typicode.com';

assert(RequestHelper::fetch("$root/posts")->status == 200);

assert(RequestHelper::fetch("$root/posts", [
	'method' => 'post',
	'body' => [
		'content' => 'Test',
	],
])->json == ["content" => "Test", "id" => 101]);

assert(RequestHelper::fetch("$root/posts/1", [
	'method' => 'delete',
])->statusText == "OK");

$headersTests = [
	["ContentType" => "application/json", "Content-Length" => 0],
	["ContentType: application/json", "Content-Length: 0"],
	"ContentType: application/json\r\nContent-Length: 0",
];

foreach ($headersTests as $headers) {
	$buildHeaders = RequestHelper::buildHeaders($headers);
	$parseHeaders = RequestHelper::parseHeaders($buildHeaders);
	assert($parseHeaders == $headersTests[0]);
}

echo "RequestHelper done\n";