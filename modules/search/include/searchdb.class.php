<?PHP

class searchdb
{
	public $mysql_result;

	public $docs_table_name;
	public $words_table_name;
	public $index_table_name;
	public $ignored_table_name;
	//var $hash_table_name;

	function searchdb($prefix)
	{
		$this->docs_table_name  = $prefix."_docs";
		$this->words_table_name = $prefix."_words";
		$this->index_table_name = $prefix."_index";
		$this->ignored_table_name = $prefix."_ignored";
	//	$this->hash_table_name = $prefix."_hash";
	}

	function query($query)
	{
		global $kernel;
		$this->mysql_result = $kernel->runSQL($query);
	}


	function install()
	{

		$query =
		"CREATE TABLE IF NOT EXISTS $this->docs_table_name
		(
			id INT AUTO_INCREMENT NOT NULL,
			doc TEXT,
			doc_hash char(32),
			contents_hash char(32),
			format_id tinyint,
			snipped MEDIUMBLOB,

			primary key(id),
			unique(doc_hash)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT = 1

		";
		$this->query($query);


		$query =
		"CREATE TABLE IF NOT EXISTS $this->words_table_name
		(
			id INT AUTO_INCREMENT NOT NULL,
			word VARCHAR(50) BINARY,

			primary key(id),
			unique(word)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT = 1
		";
		$this->query($query);


		$query =
		"CREATE TABLE IF NOT EXISTS $this->index_table_name
		(
			id 			INT AUTO_INCREMENT NOT NULL,
			doc_id 		INT,
			word_id 	INT,
			weight		INT, # вес, умноженный на тысячу и округлённый

			primary key(id),
			key(doc_id, word_id),
			key(word_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT = 1
		";
		$this->query($query);

		$query =
		"CREATE TABLE IF NOT EXISTS $this->ignored_table_name
		(
          `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
          `word` varchar(255) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `word` (`word`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT = 1
		";
		$this->query($query);



	}


	function uninstall()
	{
		$this->query("DROP TABLE IF EXISTS $this->docs_table_name");
		$this->query("DROP TABLE IF EXISTS  $this->words_table_name");
		$this->query("DROP TABLE IF EXISTS  $this->index_table_name");
		$this->query("DROP TABLE IF EXISTS  $this->ignored_table_name");
	}


	function array2str($arr)
	{
		$str_parts = array();
		foreach ($arr as $value)
			$str_parts[] = "'".mysql_real_escape_string($value)."'";
		return join(", ", $str_parts);
	}


	function delete_doubles()
	{
	    $sql = "SELECT id, count(*) as cnt
	                   FROM $this->docs_table_name
	                   GROUP BY contents_hash
	                   HAVING cnt > 1";

	    $this->query($sql);
	    $ids2delete = array();
	    while ($row = mysql_fetch_assoc($this->mysql_result))
	        $ids2delete[] = $row['id'];

	    $ids_str = $this->array2str($ids2delete);
	    if (!empty($ids_str))
	    {
	    	$sql = "DELETE FROM $this->docs_table_name
	                       WHERE id IN ($ids_str)";
	    	$this->query($sql);
	    }
	}


	function get_word_ids($words)
	{
		if (count($words) == 0)
			return array();

		$words_str = $this->array2str($words);
		$this->query("SELECT id, word FROM $this->words_table_name WHERE word IN ($words_str)");

		$result = array();
		while ($row = mysql_fetch_assoc($this->mysql_result))
			$result[$row['word']] = $row['id'];

		return $result;
	}

	function add_words($words)
	{
		if (count($words) == 0)
			return array();

		$ids = array();
		$this->query("LOCK TABLE $this->words_table_name WRITE");
		foreach ($words as $word)
		{
			$this->query("INSERT INTO $this->words_table_name VALUES (NULL, '$word')");
			$ids[$word] = mysql_insert_id();
		}
		$this->query("UNLOCK TABLES");

		return $ids;
	}


	function get_url_id($url)
	{
		$doc_hash = md5($url);
		$this->query("SELECT id FROM $this->docs_table_name WHERE doc_hash = '$doc_hash'");
		if (mysql_num_rows($this->mysql_result) == 0)
			return false;
		else
			return mysql_result($this->mysql_result, 0);
	}


	function add_url($url, $contents_hash)
	{
		$doc_hash = md5($url);
		$url = mysql_real_escape_string($url);
		$this->query("INSERT INTO $this->docs_table_name VALUES (NULL, '$url', '$doc_hash', '$contents_hash', -2, '')");
		return mysql_insert_id();
	}


	function get_contents_hash($url_id)
	{
		$this->query("SELECT contents_hash FROM $this->docs_table_name WHERE id = $url_id LIMIT 1");
		if (mysql_num_rows($this->mysql_result) == 0)
			return false;
		else
			return mysql_result($this->mysql_result, 0);

	}


	function empty_url_data_from_index($url_id)
	{
		$this->query("DELETE FROM $this->index_table_name WHERE doc_id = $url_id");
	}

	function lock_index()
	{
		$this->query("LOCK TABLES $this->index_table_name WRITE");
	}

	function unlock_tables()
	{
		$this->query("UNLOCK TABLES");
	}

	function add_to_index($doc_id, $word_id, $weight)
	{
		$this->query("INSERT INTO $this->index_table_name VALUES (NULL, $doc_id, $word_id, $weight)");

	}


	function delete_index_for_old_urls($new_url_ids)
	{
		$new_url_ids_str = $this->array2str($new_url_ids);
		$this->query("DELETE FROM $this->index_table_name WHERE doc_id NOT IN ($new_url_ids_str)");
	}




	function search($word_ids, $limit=0, $length=20, $operation='or', $format_id = false)
	{

		if (count($word_ids) == 0)
			return array();



		if ($operation == 'and')
			$addition = "HAVING kolvo = ".count($word_ids);
		else
			$addition = "";

		if ($format_id !== false)
			$addition2 = "AND d.format_id = $format_id";
		else
			$addition2 = "";


		$word_ids_str = $this->array2str($word_ids);

		$query = "

			SELECT SQL_CALC_FOUND_ROWS i.doc_id, d.doc, d.snipped, sum(i.weight) as relevance, count(*) as kolvo
			FROM $this->index_table_name i, $this->docs_table_name d
			WHERE i.word_id IN ($word_ids_str) AND i.doc_id = d.id  $addition2
			GROUP BY i.doc_id
			$addition
			ORDER BY kolvo DESC, relevance DESC
			LIMIT $limit, $length
			";
		//$time = time();
		$this->query($query);

//		print "<!-- \n\n\n\n\n$query\n ".(time()-$time)."\n\n\n-->";
		$result = array();
		while ($row = mysql_fetch_assoc($this->mysql_result))
			$result[] = $row;

		return $result;
	}



	function found_rows()
	{
		$this->query("SELECT found_rows()");
		return mysql_result($this->mysql_result, 0);
	}



	function is_installed()
	{
		$query = "SHOW TABLES";
		$this->query($query);
		//$exists = false;
		$need_tables = array($this->docs_table_name, $this->index_table_name, $this->words_table_name);

		$exist_tables = array();
		while ($row = mysql_fetch_row($this->mysql_result))
			$exist_tables[] = $row[0];

		$difference = array_diff($need_tables, $exist_tables);
		if (count($difference) > 0)
			return false;
		else
			return true;
	}



	function update_doc_data($url_id, $snipped, $contents_hash, $format_id)
	{
		$snipped = mysql_real_escape_string($snipped);
		$query = "UPDATE $this->docs_table_name
					SET
						snipped = '$snipped', contents_hash='$contents_hash', format_id='$format_id'
					WHERE
						id = $url_id
					LIMIT 1";
		$this->query($query);
	}



	function optimize_tables()
	{
		$this->query("LOCK TABLES $this->docs_table_name WRITE, $this->index_table_name WRITE");
		$this->query("OPTIMIZE TABLE $this->docs_table_name");
		$this->query("OPTIMIZE TABLE $this->index_table_name");
		$this->query("UNLOCK TABLES");
	}

	function count_pages()
	{
	    global $kernel;
        $total = 0;
	    $result = $kernel->runSQL("SELECT count(*) AS count FROM `".$this->docs_table_name."`");
        if ($row = mysql_fetch_assoc($result))
            $total = $row['count'];
	    return $total;
	}

	function count_words()
	{
	    global $kernel;
        $total = 0;
	    $result = $kernel->runSQL("SELECT count(*) AS count FROM `".$this->words_table_name."`");
        if ($row = mysql_fetch_assoc($result))
            $total = $row['count'];
	    return $total;
	}

	function get_ignored_strings($fulldata=true)
	{
	    global $kernel;
	    $result = $kernel->runSQL("SELECT * FROM ".$this->ignored_table_name." ORDER BY `word`");
	    $ret = array();
	    while ($row = mysql_fetch_assoc($result))
	    {
	        if ($fulldata)
	            $ret[] = $row;
	        else
	            $ret[] = $row['word'];
	    }
	    return $ret;
	}

	function delete_ignored_string($id)
	{
	    global $kernel;
	    $kernel->runSQL("DELETE FROM ".$this->ignored_table_name." WHERE id=".$id);
	}

	function add_ignored_string($string)
	{
	    global $kernel;
	    $kernel->runSQL("REPLACE INTO ".$this->ignored_table_name." (`word`) VALUES ('".$string."')");
	}

    function get_all_indexed_docs()
    {
	    global $kernel;
	    $result = $kernel->runSQL("SELECT id,doc FROM ".$this->docs_table_name);
	    $ret = array();
	    while ($row = mysql_fetch_assoc($result))
	    {
	        $ret[] = $row;
	    }
        mysql_free_result($result);
	    return $ret;
    }

    function remove_ignored_from_index()
    {
        global $kernel;
        $docs = $this->get_all_indexed_docs();
        $istrings = $this->get_ignored_strings(false);
        foreach ($docs as $doc)
        {
            foreach ($istrings as $istring)
            {
                if (strpos($doc['doc'], $istring)!==false)
                {//этот урл - игнорируемый
                    $kernel->runSQL("DELETE FROM ".$this->docs_table_name." WHERE id=".$doc['id']);
                    $kernel->runSQL("DELETE FROM ".$this->index_table_name." WHERE doc_id=".$doc['id']);
                }
            }
        }
    }
}

?>