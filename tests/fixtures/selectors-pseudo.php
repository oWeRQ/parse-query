<?php

return [
	// pseudo
	':not(b)' => 'descendant::*[not(self::b)]',
	':has(b)' => 'descendant::*[descendant::b]',
	':eq(0)' => 'descendant::*[1]',
	':contains("hello")' => 'descendant::*[contains(text(),"hello")]',
];