<?php

return [
	// complex
	'h1 > *, h2 > *' => 'descendant::h1/*|descendant::h2/*',
	'h1 ~ *, h2 ~ *' => 'descendant::h1/following-sibling::*|descendant::h2/following-sibling::*',
	'h1 + *, h2 + *' => 'descendant::h1/following-sibling::*[1]/self::*|descendant::h2/following-sibling::*[1]/self::*',
	'h1 + form > * .error[id] ~ input[type=text][disabled], [enabled][type=submit]' => 'descendant::h1/following-sibling::*[1]/self::form/*/descendant::*[contains(concat(" ",@class," ")," error ")][@id]/following-sibling::input[@type="text"][@disabled]|descendant::*[@enabled][@type="submit"]',
	'a[title ~= "#hash"][title ~= "#tags"]' => 'descendant::a[contains(concat(\' \',@title,\' \'),concat(\' \',"#hash",\' \'))][contains(concat(\' \',@title,\' \'),concat(\' \',"#tags",\' \'))]',
	':not([type=submit])' => 'descendant::*[not(self::*[@type="submit"])]',
	':not(.submit)' => 'descendant::*[not(self::*[contains(concat(" ",@class," ")," submit ")])]',

	// non css
	'[0]' => 'descendant::*[0]',
	'[1]' => 'descendant::*[1]',
	'[last()-1]' => 'descendant::*[last()-1]',
	'[text()=hello]' => 'descendant::*[text()="hello"]',
	'[text()*=hello]' => 'descendant::*[contains(text(),"hello")]',
];