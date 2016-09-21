<?php

require_once '../ParseQuery.php';

foreach (ParseQuery::fetch('http://bash.im/abysstop')->find('.quote > .text') as $quote) {
	echo $quote->text()."\n\n====\n\n";
}
