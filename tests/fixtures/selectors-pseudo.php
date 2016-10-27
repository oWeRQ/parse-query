<?php

return [
	// pseudo
	[':not(b)', 'descendant::*[not(self::b)]'],
	[':has(b)', 'descendant::*[descendant::b]'],
	[':eq(0)', 'descendant::*[1]'],
	[':contains("hello")', 'descendant::*[contains(text(),"hello")]'],
	[':not(:has(b))', 'descendant::*[not(self::*[descendant::b])]'],
	[':has(:not(b))', 'descendant::*[descendant::*[not(self::b)]]'],
	[':not(a):has(b)', 'descendant::*[not(self::a)][descendant::b]'],
];