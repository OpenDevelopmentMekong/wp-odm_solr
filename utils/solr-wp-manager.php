<?php

 use Solarium\Solarium;
 use Solarium\Exception;
 use Solarium\Exception\HttpException;
 use Solarium\QueryType\Select\Query\Query as Select;

 include_once plugin_dir_path(__FILE__).'wp_odm_solr_options.php';

 $GLOBALS['wp_odm_solr_options'] = new WpOdmSolr_Options();
/*
 * OpenDev
 * Solr Manager
 */

class WP_Odm_Solr_WP_Manager {

  var $client = null;
  var $server_config = null;

	function __construct() {

    wp_odm_solr_log('solr-wp-manager __construct');

    $solr_host = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host');
    $solr_port = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_port');
    $solr_scheme = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme');
    $solr_path = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path');
    $solr_core_wp = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_wp');
    $solr_user = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_user');
    $solr_pwd = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_pwd');

    $this->server_config = array(
      'endpoint' => array(
          $solr_host => array(
              'host' => $solr_host,
              'port' => $solr_port,
              'path' => $solr_path,
  						'core' => $solr_core_wp,
  						'scheme' => $solr_scheme,
              'username' => $solr_user,
              'password' => $solr_pwd
          )
      )
  	);

    try {
  		$this->client = new \Solarium\Client($this->server_config);
    } catch (HttpException $e) {
      wp_odm_solr_log('solr-wp-manager __construct Error: ' . $e);
    }

	}

  function ping_server(){

    wp_odm_solr_log('solr-wp-manager ping_server');

    if (!isset($this->client)):
      return false;
    endif;

    try {
      $ping = $this->client->createPing();
      $result = $this->client->ping($ping);
    } catch (HttpException $e) {
      wp_odm_solr_log('solr-wp-manager ping_server Error: ' . $e);
      return false;
    }

    return true;
  }

	function index_post($post){

    wp_odm_solr_log('solr-wp-manager index_post ' . serialize($post));

    $result = null;

    try {
      $update = $this->client->createUpdate();

      $languages = array();
        if (strpos("<!--:en-->",$post->content) > -1 or strpos("[:en]",$post->content) > -1):
            array_push($languages,"en");
        endif;
        if (strpos("<!--:km-->",$post->content) > -1 or strpos("[:km]",$post->content) > -1):
            array_push($languages,"km");
        endif;
        if (strpos("<!--:my-->",$post->content) > -1 or strpos("[:my]",$post->content) > -1):
            array_push($languages,"my");
        endif;
        if (strpos("<!--:la-->",$post->content) > -1 or strpos("[:la]",$post->content) > -1):
            array_push($languages,"la");
        endif;
        if (strpos("<!--:th-->",$post->content) > -1 or strpos("[:th]",$post->content) > -1):
            array_push($languages,"th");
        endif;
        if (strpos("<!--:vi-->",$post->content) > -1 or strpos("[:vi]",$post->content) > -1):
            array_push($languages,"vi");
        endif;

  		$doc = $update->createDocument();
  		$doc->id = $post->ID;
  		$doc->blogid = get_current_blog_id();
      $doc->country_site = odm_country_manager()->get_current_country();
      $doc->odm_spatial_range = odm_country_manager()->get_current_country_code();
      $doc->odm_language = $languages;
      $doc->license_id = "CC-BY-4.0";
  		$doc->blogdomain = get_site_url();
  		$doc->title = $post->post_title;
  		$doc->permalink = get_permalink($post);
  		$doc->author = $post->post_author;
  		$doc->content = $post->post_content;
  		$doc->excerpt = $post->post_excerpt;
  		$doc->type = $post->post_type;
  		$doc->categories = wp_get_post_categories($post->ID, array('fields' => 'names'));
  		$doc->tags = wp_get_post_tags($post->ID, array('fields' => 'names'));
  		$date = new DateTime($post->post_date);
  		$doc->date = $date->format('Y-m-d\TH:i:s\Z');
  		$modified = new DateTime($post->post_modified);
  		$doc->modified = $modified->format('Y-m-d\TH:i:s\Z');
  		$update->addDocument($doc);
  		$update->addCommit();
  		$result = $this->client->update($update);
    } catch (HttpException $e) {
      wp_odm_solr_log('solr-wp-manager index_post Error: ' . $e);
    }

    return $result;
  }

	function clear_index(){

    wp_odm_solr_log('solr-wp-manager clear_index');

    $result = null;

    try {
  		$update = $this->client->createUpdate();
  		$update->addDeleteQuery('title:*');
  		$update->addCommit();
  		$result = $this->client->update($update);
    } catch (HttpException $e) {
      wp_odm_solr_log('solr-wp-manager clear_index Error: ' . $e);
    }

		return $result;
  }

	function query($text, $attrs = null, $control_attrs = null){

    wp_odm_solr_log('solr-wp-manager query: ' . $text . " attrs: " . serialize($attrs)  . " control_attrs: " . serialize($control_attrs));

    $result = array(
      "resultset" => null,
      "facets" => array(
        "categories" => array(),
        "tags" => array(),
        "odm_spatial_range" => array(),
        "odm_language" => array(),
        "license_id" => array()
      ),
    );

    try {

      $query = $this->client->createSelect();
      if (!empty($text)):
        $query->setQuery($text);
      endif;

      if (isset($control_attrs["page"]) && isset($control_attrs["limit"])):
        $start = $control_attrs["page"] * $control_attrs["limit"];
        $rows = $control_attrs["limit"];
        $query->setStart($start)->setRows($rows);
      endif;

      if (isset($attrs)):
        foreach ($attrs as $key => $value):
          if ($key == "categories"):
            $taxonomy_top_tier = odm_taxonomy_manager()->get_taxonomy_top_tier();
            if (array_key_exists($value,$taxonomy_top_tier)):
              $value = "(\"" . implode("\" OR \"", $taxonomy_top_tier[$value]) . "\")";
            endif;  
          else:
            if (is_array($value)):
              $value = "(\"" . implode("\" AND \"", $value) . "\")";
            endif;
          endif;
          $query->createFilterQuery($key)->setQuery($key . ':' . $value);
        endforeach;
      endif;

      $current_country = odm_country_manager()->get_current_country();
      if ( $current_country != "mekong" && !array_key_exists("odm_spatial_range",$attrs)):
  			$query->createFilterQuery('odm_spatial_range')->setQuery('odm_spatial_range:' . $current_country);
  		endif;

      if (!empty($text)):
        $fields_to_query = 'tags^5 categories^4 title^2 content^1';
        $dismax = $query->getDisMax();
        $dismax->setQueryFields($fields_to_query);
      endif;

      $facetSet = $query->getFacetSet();
      foreach ($result["facets"] as $key => $objects):
        $facetSet->createFacetField($key)->setField($key);
      endforeach;

      if (isset($control_attrs["sorting"])):
        $query->addSort($control_attrs["sorting"], 'desc');
      endif;

      wp_odm_solr_log('solr-wp-manager executing query: ' . serialize($query));

  		$resultset = $this->client->select($query);
      $result["resultset"] = $resultset;

      foreach ($result["facets"] as $key => $objects):
        $facet = $resultset->getFacetSet()->getFacet($key);
        if (isset($facet)):
          $result["facets"][$key] = [];
          foreach($facet as $value => $count) {
            $result["facets"][$key][$value] = $count;
          }
        endif;
      endforeach;

    } catch (HttpException $e) {
      wp_odm_solr_log('solr-wp-manager query Error: ' . $e);
    }

		return $result;
	}

}

$GLOBALS['WP_Odm_Solr_WP_Manager'] = new WP_Odm_Solr_WP_Manager();

function WP_Odm_Solr_WP_Manager() {
	return $GLOBALS['WP_Odm_Solr_WP_Manager'];
}

?>
