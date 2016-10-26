<?php

require_once '../vendor/autoload.php';

use Parse\ParseQuery;

if ($argc < 2) {
	die("Usage: php {$argv[0]} <query> [page]\n");
}

$query = urlencode($argv[1]);
if ($argc > 2) {
	$query .= '&p='.$argv[2];
}

$page = ParseQuery::fetch('https://yandex.ru/search/?text='.$query);

foreach ($page->find('.serp-item') as $i => $item) {
	$link = $item->find('.organic__url, .serp-item__title-link');
	$text = $item->find('.organic__text');
	
	echo ($i + 1).". ".$link->text()." (".$link->attr('href').")\n   ";
	echo str_replace("\n", "\n   ", $text->text())."\n\n";
}
