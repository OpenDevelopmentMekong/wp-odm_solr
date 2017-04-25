<?php

  use Analog\Analog;
  use Analog\Handler;

  define("WP_ODM_SOLR_DEFAULT_LOG_PATH","/tmp/wp_odm_solr.log");
  define("WP_ODM_SOLR_CHECK_REQS",True);

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
      if (array_key_exists($lang,$json)):
        $to_return = $json[$lang];
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

    if (!$GLOBALS['wp_odm_solr_options']->get_option('wpckan_setting_log_enabled')) return;

    $bt = debug_backtrace();
    $caller = array_shift($bt);

    if (!empty($GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_log_path')))
      Analog::handler(Handler\File::init ($GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_log_path')));
    else
      Analog::handler(Handler\File::init (WP_ODM_SOLR_DEFAULT_LOG_PATH));

    Analog::log ( "[ " . $caller['file'] . " | " . $caller['line'] . " ] " . $text );
  }

  function compareScoresDesc($a, $b)
  {
      return $a->score > $b->score ? -1 : 1;
  }

  function wp_odm_merge_results_and_sort_by_score($wp_results,$ckan_results) {

    $merged = array();
    if (!is_array($wp_results) && is_array($ckan_results)):
      $merged = $ckan_results;
    elseif (!is_array($ckan_results) && is_array($wp_results)):
      $merged = $wp_results;
    elseif (is_array($ckan_results) && is_array($wp_results)):
      $merged = array_merge($wp_results, $ckan_results);
      $merged = usort($merged, 'compareScoresDesc');
    endif;

    return $merged;
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
