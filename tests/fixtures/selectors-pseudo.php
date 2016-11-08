<?php

return [
	// pseudo
	[':not(b)', 'descendant::*[not(self::b)]'],
	[':has(b)', 'descendant::*[descendant::b]'],
	[':eq(0)', 'descendant::*[1]'],
	[':eq(1)', 'descendant::*[2]'],
	[':eq(-1)', 'descendant::*[last()]'],
	[':eq(-2)', 'descendant::*[last()-1]'],
	[':lt(0)', 'descendant::*[position()<1]'],
	[':lt(1)', 'descendant::*[position()<2]'],
	[':lt(-1)', 'descendant::*[position()-last()<0]'],
	[':lt(-2)', 'descendant::*[position()-last()<-1]'],
	[':gt(0)', 'descendant::*[position()>1]'],
	[':gt(1)', 'descendant::*[position()>2]'],
	[':gt(-1)', 'descendant::*[position()-last()>0]'],
	[':gt(-2)', 'descendant::*[position()-last()>-1]'],
	[':contains("hello")', 'descendant::*[contains(text(),"hello")]'],
	[':not(:has(b))', 'descendant::*[not(self::*[descendant::b])]'],
	[':has(:not(b))', 'descendant::*[descendant::*[not(self::b)]]'],
	[':not(a):has(b)', 'descendant::*[not(self::a)][descendant::b]'],
	[':last()', 'descendant::*[last()]'],
];