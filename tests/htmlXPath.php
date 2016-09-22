<?php

require_once '../ParseHelper.php';

$html = <<<HTML
<div id="list">
	<span class="item item1">
		<a href="#1">link1</a>
		<small><a href="#1.1">sublink1.1</a></small>
	</span>
	<span class="item item2">
		<a href="#2">link2</a>
		<small><a href="#2.1">sublink2.1</a></small>
	</span>
	<span class="item item3">
		<a href="#3">link3</a>
		<small><a href="#3.1">sublink3.1</a></small>
	</span>
</div>
<form action="https://www.google.ru/search">
	<input type="hidden" name="start" value="0">
	<input type="text" name="q" value="test">
	<input type="submit" value="Search">
</form>
HTML;

$xpath = ParseHelper::htmlXPath($html);

$selectors = [
	'#list > .item3 a',
	'.item1 ~ *',
	'.item1 > a ~ *',
	'.item1 ~ .item3',
	'input[type=text]',
	'input[type ~= "submit"]',
];

foreach ($selectors as $selector) {
	$expression = ParseHelper::css2XPath($selector);
	$nodes = $xpath->query($expression);

	echo "selector: $selector\n";
	echo "expression: $expression\n";
	echo "length: {$nodes->length}\n\n";

	foreach ($nodes as $node) {
		echo "tag: {$node->tagName}\n";
		echo "content: '{$node->textContent}'\n\n";
	}

	echo "===\n\n";
}
