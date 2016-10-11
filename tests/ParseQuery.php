<?php

require_once '../ParseQuery.php';

function dump($nodes, $method = 'outerHtml', $params = [])
{
	$ret = '';
	foreach ($nodes as $node) {
		$ret .= "\"".call_user_func_array([$node, $method], $params)."\"\n";
	}
	return $ret."====\n";
}

function assert_length($nodes, $expect)
{
	$length = $nodes->length();
	assert($length === $expect, "expect $expect, return $length nodes:\n".dump($nodes));
}

// fetch and loadHtml
$html = ParseQuery::fetch('fixtures/page1.html');
assert_length($html, 1);

// find
$nop = $html->find('nop');
$list = $html->find('#list');
$items = $list->find('.item');
$anchor = $html->find('a');

assert_length($nop, 0);
assert_length($nop->find('a'), 0);
assert_length($list, 1);
assert_length($items, 3);
assert_length($anchor, 6);

// filter
assert_length($list->filter('.item'), 0);
assert_length($items->filter('.item1, .item3'), 2);

// children
assert_length($html->children(), 1);
assert_length($html->children('.item'), 0);
assert_length($list->children('.item1, .item3'), 2);

// closest
assert_length($list->closest('*'), 1);
assert_length($list->closest('.item, #list'), 1);
assert_length($list->closest('#list'), 1);
assert_length($items->closest('#list'), 1);
assert_length($items->closest('.item, #list'), 3);
assert_length($items->closest('#list, .item'), 3);
assert_length($anchor->closest('.item'), 3);

// parents
assert_length($list->parents(), 2);
assert_length($list->parents('body'), 1);
assert_length($list->parents('.list'), 0);

// parent
assert_length($items->parent(), 1);
assert_length($anchor->parent(), 6);

// prev
assert_length($anchor->prev(), 0);
assert_length($items->prev(), 2);

// next
assert_length($anchor->next(), 3);
assert_length($items->next(), 2);

// prop
assert('$anchor->prop("tagName") === "a"');

// attr
assert('$anchor->attr() === ["href" => "#1"]');
assert('$anchor->attr("href") === "#1"');

// text
assert('$anchor->text() === "link1"');
assert('$anchor->next()->text() === "sublink1.1"');

// html
assert('$anchor->html() === "link1"');
assert('$anchor->next()->html() === "<a href=\"#1.1\">sublink1.1</a>"');

// outerHtml
assert('$anchor->outerHtml() === "<a href=\"#1\">link1</a>"');

//__get
assert('$anchor->outerHtml === "<a href=\"#1\">link1</a>"');
assert('$anchor->textContent === "link1"');
assert('$anchor->href === "#1"');

//__toString
assert('(string)$anchor === "6 in [a{link1}, a{sublink1.1}, a{link2}, a{sublink2.1}, a{link3}, a{sublink3.1}]"');

echo "ParseQuery done\n";