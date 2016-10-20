<?php

require_once '../DOMHelper.php';

$xpath = DOMHelper::htmlXPath(file_get_contents('fixtures/page1.html'));

assert($xpath instanceof DOMXPath);

$root = $xpath->document->documentElement;

assert($root instanceof DOMElement);
assert($root->ownerDocument instanceof DOMDocument);
assert(!$root->isSameNode($root->ownerDocument));
assert($root->isSameNode($root->ownerDocument->documentElement));
assert($root->tagName === 'html');

$self = $xpath->query('descendant::html', $root)->item(0);

assert($self === null);

$form = $xpath->query('descendant::form', $root)->item(0);

assert($form instanceof DOMElement);
assert($form->tagName === 'form');

$xpath = DOMHelper::htmlXPath(file_get_contents('fixtures/page2.html'), true);

$dt = $xpath->query('descendant::dt')->item(0);
$dd = $xpath->query('descendant::dd')->item(0);

assert($dt->textContent === 'Hello world!');
assert($dd->textContent === 'Привет мир!');

$html = '<b>hello<br>world</b>';
DOMHelper::setInnerHtml($dd, $html);
assert('DOMHelper::innerHtml($dd) === $html');

//var_dump($xpath->document->saveHTML());
//var_dump($xpath->document->saveHTML($dd));

/*
$items = $xpath->query('descendant::dl/*');
foreach ($items as $i => $item) {
	var_dump($item);
}
*/

echo "DOMHelper done\n";