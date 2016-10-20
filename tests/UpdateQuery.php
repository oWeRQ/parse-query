<?php

require_once '../UpdateQuery.php';

$page = UpdateQuery::fetch('fixtures/page1.html');
$form = $page->find('form');
$anchor = $page->find('a');

// attr
$action = 'test';
assert('$form->attr("action") !== $action');
$form->attr('action', $action);
assert('$form->attr("action") === $action');

// text
$text = ' test text ';
assert('$form->text() !== $text');
$form->text($text);
assert('$form->text() === $text');

// html
$html = ' test html ';
assert('$form->html() !== $html');
$form->html($html);
assert('$form->html() === $html');

// html
$html = '<p> test <br> html </p>';
assert('$form->html() !== $html');
$form->html($html);
assert('$form->html() === $html');

// empty
$form->empty();
assert('$form->html() === ""');

// remove
$form = $page->find('form');
assert('$form->length() === 1');
$form->remove();
$form = $page->find('form');
assert('$form->length() === 0');

echo "UpdateQuery done\n";