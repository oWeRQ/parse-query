<?php

require_once '../ParseQuery.php';

$pq = new ParseQuery;

$pq->loadHtml(
<<<HTML
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
HTML
);

foreach ($pq->find('.item1, .item3')->find('a') as $node) {
	var_dump((new ParseQuery($node))->html());
}

foreach ($pq->find('#list')->children() as $node) {
	var_dump((new ParseQuery($node))->html());
}

foreach ($pq->find('.item > a')->next() as $node) {
	var_dump((new ParseQuery($node))->outerHtml());
}

foreach ($pq->find('small')->prev() as $node) {
	var_dump((new ParseQuery($node))->outerHtml());
}

foreach ($pq->find('.item')->filter('.item2') as $node) {
	var_dump((new ParseQuery($node))->outerHtml());
}
