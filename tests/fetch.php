<?php

require_once '../ParseHelper.php';

$root = 'http://jsonplaceholder.typicode.com';

var_dump(ParseHelper::fetch("$root/posts")->status);

var_dump(ParseHelper::fetch("$root/posts", [
	'method' => 'post',
	'body' => [
		'content' => 'Test',
	],
])->json);

var_dump(ParseHelper::fetch("$root/posts/1", [
	'method' => 'delete',
])->statusText);
