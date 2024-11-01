<?php
namespace WPA;

class Article {
	protected $_article;

	public function __construct($post) {
		$this->_article = $post;
	}

	public static function prepareArray(array $array) {
		foreach($array as $k => $v)
			$array[$k] = new self($v);
		return $array;
	}

	public function attrs() {
		return new Attrs();
	}

	public function __get($name) {
		if(isset($this->_article->$name))
			return $this->_article->$name;
	}
}
