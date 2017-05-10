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

class WP_Odm_Solr_CKAN_Manager {

  var $client = null;
  var $server_config = null;

	function __construct() {

    wp_odm_solr_log('solr-ckan-manager __construct');

    $solr_host = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host');
    $solr_port = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_port');
    $solr_scheme = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme');
    $solr_path = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path');
    $solr_core_ckan = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_ckan');
    $solr_user = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_user');
    $solr_pwd = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_pwd');

    $this->server_config = array(
      'endpoint' => array(
          $solr_host => array(
              'host' => $solr_host,
              'port' => $solr_port,
              'path' => $solr_path,
  						'core' => $solr_core_ckan,
  						'scheme' => $solr_scheme,
              'username' => $solr_user,
              'password' => $solr_pwd
          )
      )
  	);

    try {
      $this->client = new \Solarium\Client($this->server_config);
    } catch (HttpException $e) {
      wp_odm_solr_log('solr-ckan-manager __construct Error: ' . $e);
    }

	}

  function ping_server(){

    wp_odm_solr_log('solr-ckan-manager ping_server');

    if (!isset($this->client)):
      return false;
    endif;

    try {
      $ping = $this->client->createPing();
      $result = $this->client->ping($ping);
    } catch (HttpException $e) {
      wp_odm_solr_log('solr-ckan-manager ping_server Error: ' . $e);
      return false;
    }

    return true;
  }

	function query($text, $attrs = null, $control_attrs = null){

    wp_odm_solr_log('solr-ckan-manager query: ' . $text . " attrs: " . serialize($attrs) . " control_attrs: " . serialize($control_attrs));

    $result = array(
      "resultset" => null,
      "facets" => array(
        "vocab_taxonomy" => array(),
        "extras_odm_keywords" => array(),
        "extras_odm_spatial_range" => array(),
        "extras_odm_language" => array(),
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
          if ($key == "vocab_taxonomy"):
            $taxonomy_top_tier = odm_taxonomy_manager()->get_taxonomy_top_tier();
            $selected_terms = array_intersect(array_keys($taxonomy_top_tier),array_values($value));
            $terms_to_search_for = array();
            foreach ($selected_terms as $term):
              $terms_to_add = $taxonomy_top_tier[$term];
              foreach ($terms_to_add as $to_add):
                array_push($terms_to_search_for,$to_add);
              endforeach;              
            endforeach;
            $value = $terms_to_search_for;          
          endif;
          if (is_array($value)):
            $value = "(\"" . implode("\" OR \"", $value) . "\")";
          endif;
          $query->createFilterQuery($key)->setQuery($key . ':' . $value);
        endforeach;
      endif;

      $current_country = odm_country_manager()->get_current_country();
      if ( $current_country != "mekong" && !array_key_exists("extras_odm_spatial_range",$attrs)):
        $current_country_code = odm_country_manager()->get_current_country_code();
  			$query->createFilterQuery('extras_odm_spatial_range')->setQuery('extras_odm_spatial_range:' . $current_country_code);
  		endif;

      if (!empty($text)):
        $fields_to_query = 'extras_odm_keywords^5 vocab_taxonomy^6 title^2 extras_title_translated^2 extras_notes_translated^1 notes^1 extras_odm_spatial_range^1 extras_odm_province^1';
        if (isset($attrs["dataset_type"])):
          $typeFilter = $attrs["dataset_type"];
          if ($typeFilter == 'library_record'):
            $fields_to_query .= ' extras_document_type^1 extras_extras_marc21_260c^1 extras_marc21_020^1 extras_marc21_022^1';
          elseif ($typeFilter == 'laws_record'):
            $fields_to_query .= ' extras_odm_document_type^1 extras_odm_promulgation_date^1';
          elseif ($typeFilter == 'agreement'):
            $fields_to_query .= ' extras_odm_agreement_signature_date^1';
          endif;
        endif;

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

      wp_odm_solr_log('solr-ckan-manager executing query: ' . serialize($query));

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
      wp_odm_solr_log('solr-ckan-manager query Error: ' . $e);
    }

		return $result;
	}

}

$GLOBALS['WP_Odm_Solr_CKAN_Manager'] = new WP_Odm_Solr_CKAN_Manager();

function WP_Odm_Solr_CKAN_Manager() {
	return $GLOBALS['WP_Odm_Solr_CKAN_Manager'];
}

?>
