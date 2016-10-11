<?php

return [
	// condition
	'[type]' => 'descendant::*[@type]',
	'input[type]' => 'descendant::input[@type]',
	'input[type=text]' => 'descendant::input[@type="text"]',
	'input[class~=text]' => 'descendant::input[contains(concat(\' \',@class,\' \'),concat(\' \',"text",\' \'))]',
	'input[class^=text]' => 'descendant::input[starts-with(@class,"text")]',
	'input[class$=text]' => 'descendant::input[ends-with(@class,"text")]',
	'input[class*=text]' => 'descendant::input[contains(@class,"text")]',

	// condition spaces and quotes
	'input[  type  ]' => 'descendant::input[@type]',
	'input[  type  =  "  text  "  ]' => 'descendant::input[@type="  text  "]',
	//'input[  type  =  "  text\'s  "  ]' => 'descendant::input[@type="  text\'s  "]',
];