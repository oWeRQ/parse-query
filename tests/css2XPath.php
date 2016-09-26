<?php

require_once '../ParseHelper.php';

$tests = [
	// base
	'h1' => 'descendant::h1',
	'h1, p' => 'descendant::h1|descendant::p',
	'h1 p' => 'descendant::h1//p',
	'h1 > p' => 'descendant::h1/p',
	'h1 ~ p' => 'descendant::h1/following-sibling::p',
	'h1 + p' => 'descendant::h1/following-sibling::*[1]/self::p',

	// id & class
	'#id' => 'descendant::*[@id="id"]',
	'.class' => 'descendant::*[contains(concat(" ",@class," ")," class ")]',
	'#id.class' => 'descendant::*[@id="id"][contains(concat(" ",@class," ")," class ")]',
	'div#id.class' => 'descendant::div[@id="id"][contains(concat(" ",@class," ")," class ")]',

	// condition
	'[0]' => 'descendant::*[0]',
	'[1]' => 'descendant::*[1]',
	'[type]' => 'descendant::*[@type]',
	'input[type]' => 'descendant::input[@type]',
	'input[type=text]' => 'descendant::input[@type="text"]',
	'input[class~=text]' => 'descendant::input[contains(concat(" ",@class," ")," text ")]',
	'input[class^=text]' => 'descendant::input[starts-with(@class,"text")]',
	'input[class$=text]' => 'descendant::input[ends-with(@class,"text")]',
	'input[class*=text]' => 'descendant::input[contains(@class,"text")]',

	// condition spaces and quotes
	'input[  type  ]' => 'descendant::input[@type]',
	'input[  type  =  "  text  "  ]' => 'descendant::input[@type="  text  "]',
	//'input[  type  =  "  text\'s  "  ]' => 'descendant::input[@type="  text\'s  "]',

	// complex
	'h1 > *, h2 > *' => 'descendant::h1/*|descendant::h2/*',
	'h1 ~ *, h2 ~ *' => 'descendant::h1/following-sibling::*|descendant::h2/following-sibling::*',
	'h1 + *, h2 + *' => 'descendant::h1/following-sibling::*[1]/self::*|descendant::h2/following-sibling::*[1]/self::*',
	'h1 + form > * .error[id] ~ input[type=text][disabled], [enabled][type=submit]' => 'descendant::h1/following-sibling::*[1]/self::form/*//*[contains(concat(" ",@class," ")," error ")][@id]/following-sibling::input[@type="text"][@disabled]|descendant::*[@enabled][@type="submit"]',
	'a[title ~= "#hash"][title ~= "#tags"]' => 'descendant::a[contains(concat(" ",@title," ")," #hash ")][contains(concat(" ",@title," ")," #tags ")]',
];

$passed = $failed = 0;

$start = microtime(true);

foreach ($tests as $selector => $expect) {
	$expression = ParseHelper::css2XPath($selector);

	if (!assert($expression === $expect, $selector)) {
		$failed++;
		echo "\n'".$selector."' => '".$expression."',\n";
	} else {
		$passed++;
	}
}

$time = round(microtime(true) - $start, 4);

echo "passed: $passed, failed: $failed, time: $time s\n";