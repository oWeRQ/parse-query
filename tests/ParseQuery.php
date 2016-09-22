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

function dump($nodes, $method = 'outerHtml', $params = [])
{
	foreach ($nodes as $node) {
		echo "\"".call_user_func_array([$node, $method], $params)."\"\n";
	}
	echo "====\n";
}

dump($pq->find('.item1, .item3')->find('a'), 'html');
dump($pq->find('#list')->children(), 'html');
dump($pq->find('.item > a')->next(), 'outerHtml');
dump($pq->find('small')->prev(), 'outerHtml');

foreach ($pq->find('.item')->filter('.item2') as $node) {
	dump((new ParseQuery($node))->find('a'), 'outerHtml');
}

foreach ($pq->find('.item')->filter('.item2') as $node) {
	dump($node->find('a'), 'attr', ['href']);
}
