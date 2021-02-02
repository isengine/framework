<?php

namespace is\Parents;

use is\Helpers\System;
use is\Helpers\Strings;

class Path extends Data {
	
	public $url;
	public $real;
	
	public function __construct($path = null) {
		
		if ($path) {
			$path = $this -> convertSlashes($path);
			$root = str_replace(['/', '\\'], DS, $_SERVER['DOCUMENT_ROOT']) . DS;
			if (Strings::find($path, $root) !== 0) {
				$path = $this -> convertToReal($path);
			}
		}
		
		if (!$path) {
			$this -> reset();
		} elseif (
			Strings::find($path, 'http') === 0 ||
			Strings::match($path, '//')
		) {
			$this -> setPathUrl($path);
		} else {
			$this -> setPathReal($path);
		}
		
	}
	
	public function setPathReal($path) {
		$this -> real = realpath($path) . DS;
		$this -> setUrl();
	}
	
	public function setPathUrl($path) {
		$this -> url = $path . '/';
		$this -> setReal();
	}
	
	public function reset() {
		$this -> real = null;
		$this -> url = null;
		$this -> update();
	}
	
	private function update() {
		if (!$this -> url) {
			$this -> url = '/';
		}
		$root = str_replace(['/', '\\'], DS, $_SERVER['DOCUMENT_ROOT']) . DS;
		if (Strings::find($this -> real, $root) === false) {
			$this -> real = $root;
		}
	}
	
	private function convertSlashes($item) {
		$item = preg_replace('/^[\\/]/ui', '', $item);
		$item = preg_replace('/[\\/]$/ui', '', $item);
		return $item;
	}
	
	private function convertToReal($item) {
		return str_replace(':', DS, $item);
	}
	
	private function convertToUrl($item) {
		return str_replace(':', '/', $item);
	}
	
	public function setUrl() {
		$this -> url = mb_substr(str_replace(DS, '/', $this -> real), mb_strlen($_SERVER['DOCUMENT_ROOT']));
		$this -> update();
	}
	
	public function setReal() {
		$path = $this -> url;
		$len = mb_strlen($_SERVER['HTTP_HOST']);
		$pos = mb_strpos($path, $_SERVER['HTTP_HOST']);
		if ($pos === false) {
			$pos = mb_strpos($path, '//');
		}
		$result = $_SERVER['DOCUMENT_ROOT'] . mb_substr($path, $pos !== false ? $pos + $len : $pos);
		$this -> real = str_replace(['/', '\\'], DS, $result);
		$this -> update();
	}
	
	public function getUrl($path) {
		
		$real = $this -> convertToReal($path);
		$real = $this -> real . $item;
		
		if (file_exists($real)) {
			$item = $this -> convertToUrl($path);
			$item = $this -> url . $item;
			return $item;
		}
		
	}
	
	public function getReal($path) {
		
		$item = $this -> convertToReal($path);
		$item = $this -> real . $item;
		
		if (file_exists($item)) {
			return $item;
		}
		
	}
	
	public function include($path = null) {
		if (System::set($path)) {
			
			$item = $this -> convertToReal($path);
			$item = $this -> real . $item . '.php';
			
			if (file_exists($item)) {
				require_once $item;
			}
			
		} elseif (System::typeData($this -> data)) {
			
			$this -> eachData($a, function($item){
				$item = $this -> convertToReal($item);
				$item = $this -> real . $item . '.php';
				
				if (file_exists($item)) {
					require_once $item;
				}
			});
			
		}
	}
	
	public function print($path = null) {
		if (System::set($path)) {
			
			$real = $this -> convertToReal($path);
			$real = $this -> real . $item;
			
			if (file_exists($real)) {
				$item = $this -> convertToUrl($path);
				$item = $this -> url . $item;
				echo $item;
			}
			
		} elseif (System::typeData($this -> data)) {
			
			$this -> eachData($a, function($path){
				
				$real = $this -> convertToReal($path);
				$real = $this -> real . $item;
				
				if (file_exists($real)) {
					$item = $this -> convertToUrl($path);
					$item = $this -> url . $item;
					echo $item;
				}
				
			});
			
		}
	}
	
}

?>