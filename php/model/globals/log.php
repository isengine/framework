<?php

namespace is\Model\Globals;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Sessions;
use is\Helpers\Local;

use is\Parents;
use is\Parents\Path;

class Log extends Parents\Globals {
	
	protected $name;
	protected $path;
	
	public function initialize($name = null, $path = null) {
		
		if (!$name) {
			$date = new \DateTime();
			$name = Sessions::ipReal() . $date -> format('-Y.m.d-H.i.s.u') . '.log';
			unset($date);
		}
		
		$this -> setName($name);
		$this -> setPath($path);
		
	}
	
	public function setName($name) {
		$this -> name = $name;
	}
	
	public function setPath($name) {
		$path = new Path($name);
		$this -> path = $path -> real;
		unset($path);
	}
	
	public function close() {
		
		if (!$this -> path || !$this -> name) {
			return null;
		}
		
		$result = Local::writeFile($this -> path . $this -> name, $this -> data);
		
		if ($result) {
			$this -> data = [];
		}
		
	}
	
	public function summary() {
		
		$this -> data[] = '# Summary';
		$this -> data[] = 'uri : ' . $_SERVER['REQUEST_URI'];
		$this -> data[] = 'referrer : ' . $_SERVER['HTTP_REFERER'];
		$this -> data[] = 'method : ' . $_SERVER['REQUEST_METHOD'];
		$this -> data[] = 'protocol : ' . $_SERVER['SERVER_PROTOCOL'];
		$this -> data[] = 'ip : ' . $_SERVER['REMOTE_ADDR'];
		$this -> data[] = 'agent : ' . $_SERVER['HTTP_USER_AGENT'];
		$this -> data[] = '# Resources';
		$this -> data[] = 'time : ' . (new \DateTime()) -> format('Y.m.d-H.i.s.u');
		$this -> data[] = 'speed : ' . number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3, null, null) . ' sec';
		$this -> data[] = 'memory : ' . number_format(memory_get_usage() / 1024, 3, null, ' ') . ' Kb';
		$this -> data[] = 'peak : ' . number_format(memory_get_peak_usage() / 1024, 3, null, ' ') . ' Kb';
		$this -> data[] = '# Session';
		$this -> data[] = json_encode($_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		$this -> data[] = '# Cookies';
		$this -> data[] = json_encode($_COOKIE, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		$this -> data[] = '# Get data';
		$this -> data[] = json_encode($_GET, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		$this -> data[] = '# Post data';
		$this -> data[] = json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		
	}
	
}

//$log = Log::getInstance();
//$log -> initialize();
//$log -> setPath('log');
//$log -> summary();
//$log -> resetData();
//$log -> close();

?>