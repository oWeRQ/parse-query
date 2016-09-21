<?php

require_once '../ParseQuery.php';

$dateQuery = './/div[@id="new_sd_list"]/div[@style="float:right;font-family:arial;font-size:18px;color:#000000"]';

foreach (ParseQuery::fetch('http://www.lostfilm.tv')->xpathQuery($dateQuery) as $date) {
	$title = $date->next();
	$name = $title->next()->next()->find('.torrent_title');

	$item = [
		'date' => trim($date->text()),
		'title' => trim($title->text()),
		'name' => trim($name->text()),
	];

	echo implode(' - ', $item)."\n";
}