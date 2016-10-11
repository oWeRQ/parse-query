<?php

return [
	// base
	'h1' => 'descendant::h1',
	'h1, p' => 'descendant::h1|descendant::p',
	'h1 p' => 'descendant::h1/descendant::p',
	'h1 > p' => 'descendant::h1/p',
	'h1 ~ p' => 'descendant::h1/following-sibling::p',
	'h1 + p' => 'descendant::h1/following-sibling::*[1]/self::p',

	// id & class
	'#id' => 'descendant::*[@id="id"]',
	'.class' => 'descendant::*[contains(concat(" ",@class," ")," class ")]',
	'#id.class' => 'descendant::*[@id="id"][contains(concat(" ",@class," ")," class ")]',
	'div#id.class' => 'descendant::div[@id="id"][contains(concat(" ",@class," ")," class ")]',
];
