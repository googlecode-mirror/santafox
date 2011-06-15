<?PHP


/**
 * Индексатор для поискового движка.
 *
 */
class Indexator
{
    var $tag_koeffs = array
        (
            'title'     => 30.0,
            'h1'        => 20.0,
            'h2'        =>  5.0,
            'h3'        =>  3.0,
            'h4'        =>  2.0,
            'strong'    =>  1.8,
            'b'         =>  1.8,
            'bold'      =>  1.8,
            'em'        =>  1.8,
            'i'         =>  1.5,
            'italic'    =>  1.5
        );

    var $stop_words;
    var $urls = array();
    var $parsed_url_keys = array();
    var $parsed_url_ids = array();

    var $db;
    var $documentparser;

    var $current_url;
    var $current_url_id;

    var $lingua_stem_ru;
    var $stem_cache;

    var $hash;

    var $new_doc;

    var $file_tmp = '/upload/tmp_indexator_state.tmp';

    /**
     * Конструктор.
     *
     * @param String $prefix - префикс для таблиц поискового движка
     * @return Indexator
     */
    function Indexator($prefix)
    {
        //setlocale (LC_ALL, array ('ru_RU.CP1251', 'rus_RUS.1251'));
        /* @var $db SearchDb */
        mb_regex_encoding("UTF-8");
        $this->prefix = $prefix;
        $this->db = new searchdb($prefix);
        $this->lingua_stem_ru = new Lingua_Stem_Ru();
        $this->stop_words = Indexator::get_stop_words();
    }



    /********************** public **************************/

    /**
     * Индексировать/переиндексировать сайт,
     * например $indexator->index_site('http://artprom.ap');
     *
     * @param String $site_root
     */
    function index_site($site_root, $cookie_header = false)
    {
        global $kernel;
        error_reporting(E_ALL);

        //
        /* @var $db SearchDb */
        set_time_limit(0);
        //$start_time = time();

        $state = $this->load_state();
        if ($state == false)
            $this->urls[] = $site_root;
        else
        {
            $this->urls             = $state['urls'];
            $this->parsed_url_ids   = $state['parsed_url_ids'];
            $this->parsed_url_keys  = $state['parsed_url_keys'];
        }

        $html = '';
        $i = 1;
        //$kernel->pub_console_show('te"s"t');
        //die;
        while ((2+2 == 4))
        {
            $i++;
            $url = $this->get_next_url();

            if ($url === false)
                break;

            //Выедем, что индексируем

            $kernel->pub_console_show("Индексирую урл ".$url);
            flush();
            //$time_s = time();

            //Собственно индексация
            $this->index_url($url, $cookie_header);

            //Выведем сколько мы потратили на индексацию
            //$time_e = time();
            //$kernel->pub_console_show("Затрачено: ".($time_e - $time_s)." сек.");
            //flush();

            //
            ///if (time() > $start_time + 5)
            if ($i > 1)
            {
                $this->save_state();
                if (isset($_SERVER['HTTP_HOST']))//только если через веб
                    $kernel->pub_redirect_refresh('start_index');
            }
        }
        $kernel->pub_console_show("Убираем игнорируемые...");
        $this->db->remove_ignored_from_index();

        $kernel->pub_console_show("Индексация завершена");
        $this->delete_index_for_old_urls();
        $this->delete_doubles();
        $this->db->optimize_tables();
        $this->delete_state();
        $kernel->pub_redirect_refresh('index');
        return $html;
    }


    function stop_and_refresh()
    {

        //print '<meta http-equiv="refresh" content="0">';
        //die;

    }

    function save_state()
    {
        global $kernel;

        $state = array();
        $state['urls'] = $this->urls;
        $state['parsed_url_ids'] = $this->parsed_url_ids;
        $state['parsed_url_keys'] = $this->parsed_url_keys;

        $kernel->pub_file_save($this->file_tmp, serialize($state));
        //$kernel->pub_module_serial_set($state);
    }

    function load_state()
    {
        global $kernel;


        $filename = '../..'.$this->file_tmp;
        //$kernel->debug($filename,true);
        //$kernel->debug("--",true);
        //$kernel->debug(file_exists($filename),true);
        //$kernel->debug("--",true);

        if (!file_exists($filename))
            return false;

        $file = file_get_contents($filename);
        if ($file == false)
            return false;

        //$kernel->debug($file,true);
        //die();

        $arr = @unserialize($file);
        return $arr;
        /*
        $state = $kernel->pub_module_serial_get();
        //$kernel->debug($state, true);
        //die;
        if ((isset($state['urls'])) && (isset($state['parsed_url_ids'])) && (isset($state['parsed_url_keys'])))
           return $state;
        else
           return false;
        */
    }

    function delete_state()
    {
        global $kernel;
        $kernel->pub_file_delete($this->file_tmp);

        $kernel->pub_module_serial_set(array());
    }


    /**
     * Индексировать один конкретный урл,
     * например $indexator->index_url('http://artprom.ap/sitemap.html');
     *
     * @param String $url
     */
    function index_url($url, $cookie_header = false)
    {
        global $kernel;

        if ($cookie_header)
        {
            $curl_downloader = new CurlDownloader();
            $curl_downloader->add_header($cookie_header);
            $result = $curl_downloader->get($url);
            $contents = $result->responsecontent->content;

        }
        else
            $contents = @file_get_contents($url);

        $contents = preg_replace("/<!--.{0,1024}?-->/", "", $contents);


        if ($contents === false)
            return false;

        $this->hash = md5($contents);

        $this->current_url = $url;
        $this->current_url_id = $this->get_url_id($this->current_url);
        $this->parsed_url_ids[] = $this->current_url_id;


        //$kernel->debug($this->current_url, true);
        //$kernel->debug($this->current_url_id, true);
        //$kernel->debug($this->parsed_url_ids, true);
        //$url_id   = $this->get_url_id($this->current_url);
        //$this->parsed_url_ids[] = $url_id;

        $changed = true;
        if (!$this->new_doc)
        {
            $contents_hash = $this->db->get_contents_hash($this->current_url_id);
            if ($contents_hash == $this->hash)
                $changed = false;
        }



        $this->stem_cache = array();

        if (preg_match("'\\.pdf$'", $url))
        {
            if (!$changed)
                return;
            $pdfparser = new PdfParser($contents);
            $pdfparser->parse();
            if (!$pdfparser->encrypted)
            {
                //print "Not encrypted!";
                //print_r($pdfparser);
                if (preg_match("'/([^/]+?)$'", $url, $matches))
                    $contents = "<title>$matches[1]</title>";
                else
                    $contents = "";

                $contents .= "<body>".htmlspecialchars($pdfparser->get_text())."</body>";
                $format_id = Searcher::format2format_id("pdf");
                //highlight_string($contents);
            }
            else
            {
                print "(Encrypted!!)";
                return false;
            }
        }
        else
            $format_id = Searcher::format2format_id("html");

        $this->documentparser = new HtmlParser($contents);


        //$this->current_url = $url;
        $links = $this->documentparser->get_links($this->current_url);
        //$kernel->debug($links, true);
        //$html .= "\n";
        foreach ($links as $link)
        {
            if (!in_array($link, $this->urls))
            {
                if (preg_match("/\"/", $link))
                {
                    continue;
                }
                $url_parts = parse_url($url);



                $link_parts = @parse_url($link);
                if (!$link_parts)
                {
                    $kernel->pub_console_show("Документ содержит неправильную ссылку: <b>".$link."</b>");
                    flush();
                    continue;
                }
                if ($url_parts['host'] == $link_parts['host'])
                {
                    if (preg_match("'/data/content/'", $link) && !preg_match("'\\.(html|pdf|txt)$'i", $link))
                        continue;

                    $this->urls[] = $link;
                    //$html .= "<!-- $link -->\n";
                }
            }
        }
        //$html .= "\n";
        //$kernel->debug($format_id, true);
        if ($changed)
            $this->index_html($format_id);

        return '';
    }

    function add_url_to_parse($url)
    {
        $this->urls[] = $url;
/*      $fp = fopen("tmp_search_state", "a");
        flock($fp, LOCK_EX);
        fwrite($fp, $url."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
*/  }




    static function get_stop_words()
    {
        $stop_words = array('в', 'и', 'на', 'к', 'с', 'у', 'из', 'от', 'о', 'за', 'по', 'при', 'а', 'для', 'как',
                            'a', 'an', 'is', 'are', 'there');
        return $stop_words;
    }




    /**
     * Очистить индекс для конкретного урла
     *
     * @param String $url
     */
    function empty_url_index($url)
    {
        $url_id   = $this->get_url_id($url);
        $this->db->empty_url_data_from_index($url_id);
    }


    function is_installed()
    {
        /* @var $db SearchDb */
        return $this->db->is_installed();
    }

    function install()
    {
        $this->db->install();
    }



    /***************** private ****************************************************/

    function delete_index_for_old_urls()
    {
        /* @var $db SearchDb */
        $this->db->delete_index_for_old_urls($this->parsed_url_ids);
    }

    function delete_doubles()
    {
        /* @var $db SearchDb */
        $this->db->delete_doubles();
    }


    function get_next_url()
    {

        $url_keys = array_keys($this->urls);

        $non_parsed_keys = array_diff($url_keys, $this->parsed_url_keys);
        $non_parsed_keys = array_values($non_parsed_keys);
        if (count($non_parsed_keys) > 0)
        {
            $key = $non_parsed_keys[0];
            $this->parsed_url_keys[] = $key;
            return $this->urls[$key];
        }
        else
            return false;
    }



    function index_html($format_id)
    {
        $url_id = $this->current_url_id;

        /* @var $db searchdb */
        $words_and_tags = $this->documentparser->get_words_and_its_tags();
        //global $kernel;
//      $kernel->debug($words_and_tags, true);
        if (count($words_and_tags) == 0)
            return;

        $weights = $this->get_weights($words_and_tags);
        $snippeds = $this->get_snippeds($words_and_tags);

        $words = array_keys($weights);
        $word_ids = $this->get_word_ids($words);

        $this->db->update_doc_data($url_id, serialize($snippeds), $this->hash, $format_id);
        $this->db->empty_url_data_from_index($url_id);

        $this->db->lock_index();
        foreach ($words as $word)
        {
            $word_id = $word_ids[(string)$word];
            $weight = (int)round($weights[(string)$word]*1000);
            $this->db->add_to_index($url_id, $word_id, $weight);
        }
        $this->db->unlock_tables();
    }



    function get_snippeds($words_and_tags)
    {

        $text = "";
        $title = "";
        foreach ($words_and_tags as $word)
        {
            if (empty($title) && in_array("title", $word['tags']))
                $title = trim($word['text']);

            $text .= " ".$word['text'];
        }


        $text = " ".$text." ";

        /* @var $htmlparser HtmlParser */
        $word_symbols = $this->documentparser->get_word_symbols();
        $non_word_symbols = "[^$word_symbols]";

        $low_text = $this->documentparser->strtolower($text);
        $positions = array();
        foreach ($this->stem_cache as $word => $stem)
        {

            if (!isset($positions[(string)$stem]))
                $positions[(string)$stem] = array();

            $word_length = strlen($word);
            if (preg_match_all("'$non_word_symbols($word)$non_word_symbols'su", $low_text, $matches, PREG_OFFSET_CAPTURE))
            {
                foreach ($matches[1] as $match)
                {
                    $position = $match[1];
                    $positions[(string)$stem][$position] = $word_length;
                }
            }
        }

        //Проверим, если тайт пустой, то вместо него внесём урл
        if (empty($title))
        {
            $title = $this->current_url;

            //global $kernel;
            //$kernel->pub_console_show("Текст:".$snippeds['text']."; Тайтл:".$snippeds['title']);
            //$kernel->pub_console_show("!!! - Пусто - !!!");
        }

        $snippeds['text'] = $text;
        $snippeds['positions'] = $positions;
        $snippeds['title']  = $title;


        return $snippeds;
    }





    function get_weights($words_and_tags)
    {
        $total_words = 0;

        /* @var $lingua_stem_ru Lingua_Stem_ru */

        $weights = array();

        foreach ($words_and_tags as $word_and_tags)
        {
            $words = $word_and_tags['words'];

            foreach ($words as $word)
            {
                if (in_array($word, $this->stop_words) || mb_strlen($word) > 50)
                    continue;

                $koeff = 1;
                foreach ($word_and_tags['tags'] as $tag)
                    $koeff *= $this->tag_koeffs[$tag];

                if (!isset($this->stem_cache[(string)$word]))
                {
                    $stem = $this->lingua_stem_ru->stem_word($word);
                    $this->stem_cache[(string)$word] = $stem;
                }
                else
                    $stem = $this->stem_cache[(string)$word];

                if (!isset($weights[(string)$stem]))
                    $weights[(string)$stem] = $koeff;
                else
                    $weights[(string)$stem] += $koeff;
            }
            arsort($weights);
        }

        $sum = array_sum($weights);
        foreach ($weights as $stem => $weight)
            $weights[(string)$stem] /= $sum;

        return $weights;
    }


    function get_word_ids($words)
    {
        /* @var $db searchdb */
        //print "words";
        //print_r($words);
        $existing_word_ids = $this->db->get_word_ids($words);
        $existing_words = array_keys($existing_word_ids);
        $new_words = array_diff($words, $existing_words);
        $new_word_ids = $this->db->add_words($new_words);

        $ids = $existing_word_ids;
        foreach ($new_word_ids as $word => $id)
            $ids[(string)$word] = $id;

        return $ids;

    }


    function get_url_id($url)
    {
        /* @var $db searchdb */
        $url_id = $this->db->get_url_id($url);
        if ($url_id === false)
        {
            $url_id = $this->db->add_url($url, $this->hash);
            $this->new_doc = true;
        }
        else
            $this->new_doc = false;

        return $url_id;
    }

    function count_pages()
    {
        return $this->db->count_pages();
    }

    function count_words()
    {
        return $this->db->count_words();
    }


}



?>