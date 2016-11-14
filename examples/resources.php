<?php

require_once '../vendor/autoload.php';

use Parse\ParseQuery;

if ($argc < 2) {
	die("Usage: php {$argv[0]} <url>\n");
}

$page = ParseQuery::fetch($argv[1]);

$resources = [];

foreach ($page->find('script, style, link[rel=stylesheet], img') as $element) {
	$resources[] = [
		'tag' => $element->tagName,
		'type' => $element->type,
		'src' => $element->src ?: $element->href,
		'content' => html_entity_decode($element->textContent),
	];
}

echo json_encode($resources, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
