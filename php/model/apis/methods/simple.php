<?php

namespace is\Model\Apis\Methods;

use is\Model\Apis\Method;
use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Match;

class Simple extends Method {
	
	public function simple() {
		echo print_r($this -> getData(), 1);
	}
	
}

?>