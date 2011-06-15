<?php

//
// Парсит шаблоны вида
// <!-- @xxx -->
//

class ApTemplate
{
	var $tmpl;
	var $parts;

	function ApTemplate($filename)
	{
		$this->tmpl = file_get_contents($filename);
		$this->parse();
	}


	/**
	* @return String
	* @param String $key - например, 'begin'
	* @desc Публичный метод, возвращает значение части шаблона по ключу.
	*/
	function get_part($key)
	{
		if (isset($this->parts[$key]))
			return $this->parts[$key];
		else
			return "";
	}


	function parse()
	{
		$tmpl = $this->tmpl;
		$parts = preg_split("/<!--\s*?\@([a-zA-Z0-9]+?)\s*?-->/i", $tmpl);

		$arr = array();
		preg_match_all("/<!--\s*?\@([a-zA-Z0-9]+?)\s*?-->/", $tmpl, $matches);

		foreach ($matches[1] as $i => $word)
			$arr[$word] = $parts[$i+1];
		$this->parts = $arr;
	}

}


?>