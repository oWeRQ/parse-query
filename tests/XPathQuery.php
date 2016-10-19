<?php

require_once '../XPathQuery.php';

//$doc = ParseHelper::htmlXPath(file_get_contents('fixtures/page1.html'))->document;
$doc = new DOMDocument();
$doc->loadHTMLFile('fixtures/page1.html');

// __construct
$html = new XPathQuery($doc);

// xpathQuery
$smalls = $html->xpath('descendant::small');
$anchors = $smalls->xpath('descendant::a');

// count
assert($html->count() === 1);
assert($smalls->count() === 3);
assert($anchors->count() === 3);

// get
assert($html->get() === [$doc]);
assert($html->get(0) === $doc);
assert($html->get(-1) === $doc);
assert($smalls->get(0) === $smalls->get(-3));
assert($smalls->get(3) === null);
assert($smalls->get(-4) === null);

// eq
assert($html->eq(0) !== $html->eq(0));
assert($html->eq(0)->get(0) === $html->eq(0)->get(0));

// getIterator
$iterator = $smalls->getIterator();
assert($iterator instanceof ArrayIterator);
assert($iterator->count() === 3);
assert($iterator->current()->get(0) === $smalls->get(0));

// map
$smallsOddChild = $smalls->map(function($node, $i){
	if ($i % 2 === 1)
		return;

	return $node->firstChild;
});
assert($smallsOddChild->count() === 2);
assert($smallsOddChild->get(0)->tagName === 'a');
assert($smallsOddChild->get(1)->textContent === 'sublink3.1');

//__get
assert($anchors->textContent === "sublink1.1");
assert($anchors->href === "#1.1");

echo "XPathQuery done\n";