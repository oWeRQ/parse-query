<?php

require_once '../ParseHelper.php';

$xpath = ParseHelper::htmlXPath(file_get_contents('fixtures/page1.html'));

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

echo "done\n";