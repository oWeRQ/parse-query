<?php

require_once '../ParseHelper.php';

$tests = [
	"ContentType: application/json\r\nContent-Length: 0",
	["ContentType: application/json", "Content-Length: 0"],
	["ContentType" => "application/json", "Content-Length" => 0],
];

foreach ($tests as $headers) {
	$buildHeaders = ParseHelper::buildHeaders($headers);
	$parseHeaders = ParseHelper::parseHeaders($buildHeaders);
	var_dump($buildHeaders);
	var_dump($parseHeaders);
}