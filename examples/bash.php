<?php

require_once '../vendor/autoload.php';

use Parse\ParseQuery;

foreach (ParseQuery::fetch('http://bash.im/abysstop')->find('.quote > .text') as $quote) {
	echo $quote->text()."\n\n====\n\n";
}
