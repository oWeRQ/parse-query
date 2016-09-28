<?php

require_once '../ParseQuery.php';

if ($argc < 2) {
	die("Usage: php {$argv[0]} <url>\n");
}

$page = ParseQuery::fetch($argv[1]);

$formsData = [];

foreach ($page->find('form') as $i => $form) {
	$formData = $form->attr();
	$formData['fields'] = [];

	foreach ($form->find('input, textarea, select, button') as $input) {
		$tag = $input->prop('tagName');
		$name = $input->attr('name');
		$comment = $input->attr('placeholder');

		if (empty($name))
			continue;

		switch ($tag) {
			case ('textarea'):
				$type = $tag;
				$value = $input->text();
				break;
			case ('select'):
				$type = $tag;
				$value = $input->find('option[selected]')->attr('value');
				$comment = implode(', ', array_map(function($option){
					return $option->getAttribute('value').': '.$option->textContent;
				}, $input->find('option')->get()));
				break;
			default:
				$type = $input->attr('type') ?: $tag;
				$value = $input->attr('value');
				break;
		}

		$formData['fields'][] = [
			'type' => $type,
			'name' => $name,
			'value' => $value,
			'comment' => $comment,
		];
	}

	$formsData[] = $formData;
}

echo json_encode($formsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
