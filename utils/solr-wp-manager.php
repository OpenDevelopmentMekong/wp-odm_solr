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
  var $show_regional_contents = false;

	function __construct() {

    wp_odm_solr_log('solr-wp-manager __construct');

    $solr_host = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host');
    $solr_port = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_port');
    $solr_scheme = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme');
    $solr_path = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path');
    $solr_core_wp = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_wp');
    $solr_user = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_user');
    $solr_pwd = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_pwd');
    $this->show_regional_contents = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_regional_contents_enabled');

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

      $languages = array("en");
      // if (strpos($post->post_content,"<!--:en-->") > -1 || strpos($post->post_content,"[:en]") > -1):
      //   array_push($languages,"en");
      // endif;
      if (strpos($post->post_content,"<!--:km-->") > -1 || strpos($post->post_content,"[:km]") > -1):
        array_push($languages,"km");
      endif;
      if (strpos($post->post_content,"<!--:my-->") > -1 || strpos($post->post_content,"[:my]") > -1):
        array_push($languages,"my");
      endif;
      if (strpos($post->post_content,"<!--:la-->") > -1 || strpos($post->post_content,"[:la]") > -1):
        array_push($languages,"la");
      endif;
      if (strpos($post->post_content,"<!--:th-->") > -1 || strpos($post->post_content,"[:th]") > -1):
        array_push($languages,"th");
      endif;
      if (strpos($post->post_content,"<!--:vi-->") > -1 || strpos($post->post_content,"[:vi]") > -1):
        array_push($languages,"vi");
      endif;

  		$doc = $update->createDocument();
      $doc->capacity = "public";
  		$doc->id = $post->ID;
      $doc->index_id = $post->ID;
  		$doc->blogid = get_current_blog_id();
      $doc->country_site = odm_country_manager()->get_current_country();
      $doc->odm_spatial_range = odm_country_manager()->get_current_country_code();
      $doc->extras_odm_spatial_range = odm_country_manager()->get_current_country_code();
      $doc->odm_language = $languages;
      $doc->extras_odm_language = $languages;
      $doc->license_id = "CC-BY-4.0";
  		$doc->blogdomain = get_site_url();
  		$doc->title = $post->post_title;
  		$doc->permalink = get_permalink($post);
  		$doc->author = $post->post_author;
  		$doc->content = $post->post_content;
      $doc->notes = $post->post_content;
  		$doc->excerpt = $post->post_excerpt;
  		$doc->type = $post->post_type;
      $doc->dataset_type = $post->post_type;
  		$doc->categories = wp_get_post_categories($post->ID, array('fields' => 'names'));
      $doc->vocab_taxonomy = wp_get_post_categories($post->ID, array('fields' => 'names'));
  		$doc->tags = wp_get_post_tags($post->ID, array('fields' => 'names'));
      $doc->extras_odm_keywords = wp_get_post_tags($post->ID, array('fields' => 'names'));
  		$date = new DateTime($post->post_date);
  		$doc->date = $date->format('Y-m-d\TH:i:s\Z');
      $doc->metadata_created = $date->format('Y-m-d\TH:i:s\Z');
  		$modified = new DateTime($post->post_modified);
  		$doc->modified = $modified->format('Y-m-d\TH:i:s\Z');
      $doc->metadata_modified = $modified->format('Y-m-d\TH:i:s\Z');
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

  function delete_post($post_id){

    wp_odm_solr_log('solr-wp-manager delete_post');

    $result = null;

    try {
  		$update = $this->client->createUpdate();
  		$update->addDeleteQuery('id:' . $post_id);
  		$update->addCommit();
  		$result = $this->client->update($update);
    } catch (HttpException $e) {
      wp_odm_solr_log('solr-wp-manager delete_post Error: ' . $e);
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
        "license_id" => array(),
        "metadata_created" => array(),
        "metadata_modified" => array()
      ),
    );

    try {

      $query = $this->client->createSelect();

      if (isset($control_attrs["page"]) && isset($control_attrs["limit"])):
        $start = $control_attrs["page"] * $control_attrs["limit"];
        $rows = $control_attrs["limit"];
        $query->setStart($start)->setRows($rows);
      endif;

      if (isset($attrs)):
        foreach ($attrs as $key => $value):
          if ($key == "metadata_modified" || $key == "metadata_created"):
            $value = "[ " . $value . "-01-01T00:00:00Z TO " . $value . "-12-31T23:59:59Z]";
          endif;
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
      if ( $current_country !== "mekong" && !array_key_exists("extras_odm_spatial_range",$attrs)):
        $current_country_code = odm_country_manager()->get_current_country_code();
        if ($this->show_regional_contents):
          $query->createFilterQuery('extras_odm_spatial_range')->setQuery('extras_odm_spatial_range:("mekong" OR "' . $current_country_code . '")');
          $text = $text . " " . $current_country;
        else:
          $query->createFilterQuery('extras_odm_spatial_range')->setQuery('extras_odm_spatial_range:' . $current_country_code);
        endif;
  		endif;

      if ( $current_country !== "mekong" && !array_key_exists("extras_odm_language",$attrs)):
        $local_language_code = odm_language_manager()->get_the_language_code_by_site();
  			$query->createFilterQuery('extras_odm_language')->setQuery('extras_odm_language: ("en" OR "' . $local_language_code . '")');
  		endif;

      if (!empty($text)):
        $query->setQuery($text);
      endif;

      if (!empty($text)):
        $fields_to_query = 'tags^6 categories^5 title^2 content^1';
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

            if ($key == "metadata_modified" || $key == "metadata_created"):
              $value = wp_solr_print_date($value,"Y");
              if (!isset($result["facets"][$key][$value])):
                $result["facets"][$key][$value] = 0;
              endif;
              $result["facets"][$key][$value] += $count;
            else:
              $result["facets"][$key][$value] = $count;
            endif;
          }
        endif;
      endforeach;

    } catch (HttpException $e) {
      wp_odm_solr_log('solr-wp-manager query Error: ' . $e);
    }

		return $result;
	}
  
  function query_by_params($param_string){
    
    $parts = parse_url($param_string);
    $attrs = wp_odm_solr_parse_attrs_from_string($param_string);
    $control_attrs = wp_odm_solr_parse_control_attrs_from_string($param_string);

    return query($parts["s"], $attrs, $control_attrs);
  }

}

$GLOBALS['WP_Odm_Solr_WP_Manager'] = new WP_Odm_Solr_WP_Manager();

function WP_Odm_Solr_WP_Manager() {
	return $GLOBALS['WP_Odm_Solr_WP_Manager'];
}

?>
