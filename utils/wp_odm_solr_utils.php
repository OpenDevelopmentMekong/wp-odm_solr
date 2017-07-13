<?php

  use Analog\Analog;
  use Analog\Handler;

  define("WP_ODM_SOLR_DEFAULT_LOG_PATH","/tmp/wp_odm_solr.log");
  define("WP_ODM_SOLR_CHECK_REQS",True);

  function wp_odm_solr_parse_query_from_string($param_string) {
    $query = parse_url($param_string, PHP_URL_QUERY);
    parse_str($query, $parts);

    return $parts["s"];
  }

  function wp_odm_solr_parse_attrs_from_string($param_string) {
    $query = parse_url($param_string, PHP_URL_QUERY);
    parse_str($query, $parts);
    $attrs = array();

    if (isset($parts["type"])):
      $attrs["dataset_type"] = $parts["type"];
    endif;
    if (isset($parts["license"]) && $parts["license"] !== "all"):
      $attrs["license_id"] = $parts["license"];
    endif;
    if (isset($parts["taxonomy"])):
      $attrs["vocab_taxonomy"] = $parts["taxonomy"];
    endif;
    if (isset($parts["language"]) && $parts["language"] !== "all"):
      $attrs["extras_odm_language"] = $parts["language"];
    endif;
    if (isset($parts["country"]) && $parts["country"] !== "all"):
      $attrs["extras_odm_spatial_range"] = $parts["country"];
    endif;
    if (isset($parts["metadata_created"]) && $parts["metadata_created"] !== "all"):
      $attrs["metadata_created"] = $parts["metadata_created"];
    endif;
    if (isset($parts["metadata_modified"]) && $parts["metadata_modified"] !== "all"):
      $attrs["metadata_modified"] = $parts["metadata_modified"];
    endif;
    return $attrs;
  }

  function wp_odm_solr_parse_control_attrs_from_string($param_string) {
    $query = parse_url($param_string, PHP_URL_QUERY);
    parse_str($query, $parts);
    $control_attrs = array();

    if (isset($parts["page"])):
      $control_attrs["page"] = $parts["page"];
    endif;
    if (isset($parts["sorting"])):
      $control_attrs["sorting"] = $parts["sorting"];
    endif;
    return $control_attrs;
  }

  function wp_odm_solr_parse_multilingual_wp_content($to_parse,$lang,$fallback) {

    $to_return = $to_parse;

    if ($fallback):
      $to_return = $fallback;
    endif;

    $translated = apply_filters('translate_text', $to_parse, $lang);
    if ($translated):
      $to_return = $translated;
    endif;

    return $to_return;
  }

  function wp_odm_solr_parse_multilingual_ckan_content($to_parse,$lang,$fallback) {

    $to_return = null;
    $json = json_decode($to_parse,true);

    if (!isset($to_return) && isset($fallback)):
      $to_return = $fallback;
    endif;

    if ($json):
      if (array_key_exists($lang,$json) && !empty($json[$lang])):
        $to_return = $json[$lang];
      elseif (array_key_exists("en",$json)  && !empty($json["en"])):
        $to_return = $json["en"];
      endif;
    endif;

    return $to_return;
  }

  function wp_odm_solr_highlight_search_words($search_query,$to_highlight) {

    $splitted_words = explode(" ",$search_query);

    $highlighted = $to_highlight;
    foreach ($splitted_words as $word):
      $pos = stripos($to_highlight,$word);
      if (!empty($word) && $pos !== FALSE):
        $orig_word = substr($to_highlight,$pos,strlen($word));
        $highlighted = str_ireplace($orig_word,"<b>" . $orig_word . "</b>",$highlighted);
      endif;
    endforeach;

    return $highlighted;
  }

  function wp_odm_solr_log($text) {

    if (!$GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_log_enabled')) return;

    $bt = debug_backtrace();
    $caller = array_shift($bt);

    if (!empty($GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_log_path')))
      Analog::handler(Handler\File::init ($GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_log_path')));
    else
      Analog::handler(Handler\File::init (WP_ODM_SOLR_DEFAULT_LOG_PATH));

    Analog::log ( "[ " . $caller['file'] . " | " . $caller['line'] . " ] " . $text );
  }

  function wp_solr_print_date($date_string, $format = "Y-m-d"){

    try {
      $date = new \DateTime($date_string);
      return $date->format($format);
    } catch (\Exception $e) {
      wp_odm_solr_log($e->getMessage());
    }

    try {
      $date = DateTime::createFromFormat('U',(float)$date_string);
      $date->format($format);
      return $date;
    } catch (\Exception $e) {
      wp_odm_solr_log($e->getMessage());
    }

    return null;

  }

  function wp_solr_get_image_url_from_ckan_result($document){

    $image_formats = array("png","jpeg","jpg");

    $count = 0;
    foreach ($document->res_format as $format):
      if (in_array(strtolower($format),$image_formats)):
        return $document->res_url[$count];
      endif;
      $count ++;
    endforeach;

    return null;
  }

  function wp_solr_get_search_page_template($template)
  {
      include sprintf('%s/templates/'. $template . '/solr_search.php', dirname(dirname(__FILE__)));
  }

  function compareScoresDesc($a, $b)
  {
      return $a->score > $b->score ? -1 : 1;
  }

  /**
   * Construct Url
   *
   * @return string
   * @author
   **/
  function construct_url($current_url, $key, $value) {

    $url_parts = parse_url($current_url);
    if (isset($url_parts['query'])) {
      parse_str($url_parts['query'], $params);
    } else {
      $params = [];
    }

    $params[$key] = $value;

    $url_parts['query'] = http_build_query($params);

    return $url_parts['path'] . '?' . $url_parts['query'];

  }

?>
