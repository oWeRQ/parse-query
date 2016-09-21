<?php

require_once '../ParseQuery.php';

if ($argc < 2) {
	die("Usage: php {$argv[0]} <query> [page]\n");
}

$query = urlencode($argv[1]);
if ($argc > 2) {
	$query .= '&start='.(($argv[2] + 1) * 10);
}

$page = ParseQuery::fetch('https://www.google.ru/search?q='.$query);

foreach ($page->find('#search ol > *') as $i => $item) {
	$link = $item->find('h3 > a');
	$text = $item->find('span.st');

	parse_str(parse_url($link->attr('href'), PHP_URL_QUERY), $href);
	
	echo ($i + 1).". ".$link->text()." (".$href['q'].")\n   ";
	echo str_replace("\n", "\n   ", $text->text())."\n\n";
}
