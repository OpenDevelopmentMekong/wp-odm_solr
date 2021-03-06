<?php

 use Solarium\Solarium;
 use Solarium\Exception;
 use Solarium\Exception\HttpException;
 use Solarium\QueryType\Select\Query\Query as Select;

 include_once plugin_dir_path(__FILE__).'wp_odm_solr_options.php';

 $GLOBALS['wp_odm_solr_options'] = new WpOdmSolr_Options();

class WP_Odm_Solr_CKAN_Manager {

  var $client = null;
  var $server_config = null;
  var $show_regional_contents = false;
  var $only_en_local_lang = false;

	function __construct() {

    wp_odm_solr_log('solr-ckan-manager __construct');

    $solr_host = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host');
    $solr_port = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_port');
    $solr_scheme = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme');
    $solr_path = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path');
    $solr_core_ckan = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_ckan');
    $solr_user = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_user');
    $solr_pwd = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_pwd');
    $this->show_regional_contents = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_regional_contents_enabled');
    $this->only_en_local_lang = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_only_en_and_local_lang');
    
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
        "license_id" => array(),
        "metadata_created" => array(),
        "metadata_modified" => array(),
        "organization" => array()
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
          if ($key == "metadata_modified" || $key == "metadata_created" && isset($value) && $value !== "all"):
            $value = "[ " . $value . "-01-01T00:00:00Z TO " . $value . "-12-31T23:59:59Z]";
          endif;
          if ($key == "vocab_taxonomy"):
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
        if ($this->only_en_local_lang):
          $query->createFilterQuery('extras_odm_language')->setQuery('extras_odm_language: ("en" AND "' . $local_language_code . '")');
        else:
          $query->createFilterQuery('extras_odm_language')->setQuery('extras_odm_language: ("en" OR "' . $local_language_code . '")');
        endif;
  		endif;

      if (!empty($text)):
        $query->setQuery($text);
      endif;

      if (!empty($text)):
        $fields_to_query = 'extras_odm_keywords^6 vocab_taxonomy^5 title^2 extras_title_translated^2 extras_notes_translated^1 notes^1 extras_odm_spatial_range^1 extras_odm_province^1';
        if (isset($attrs["dataset_type"])):
          $typeFilter = $attrs["dataset_type"];
          if ($typeFilter == 'library_record'):
            $fields_to_query .= ' extras_document_type^4 extras_extras_marc21_260c^4 extras_marc21_020^4 extras_marc21_022^4';
          elseif ($typeFilter == 'laws_record'):
            $fields_to_query .= ' extras_odm_document_type^4 extras_odm_promulgation_date^4';
          elseif ($typeFilter == 'agreement'):
            $fields_to_query .= ' extras_odm_agreement_signature_date^4';
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
      wp_odm_solr_log('solr-ckan-manager query Error: ' . $e);
    }

		return $result;
	}

  function query_by_params($param_string){

    $query = wp_odm_solr_parse_query_from_string($param_string);
    $attrs = wp_odm_solr_parse_attrs_from_string($param_string);
    $control_attrs = wp_odm_solr_parse_control_attrs_from_string($param_string);

    return $this->query($query, $attrs, $control_attrs);
  }

  function delete_dataset($dataset_id){

    wp_odm_solr_log('solr-ckan-manager delete_dataset');

    $result = null;

    try {
  		$update = $this->client->createUpdate();
  		$update->addDeleteQuery('id:' . $post_id);
  		$update->addCommit();
  		$result = $this->client->update($update);
    } catch (HttpException $e) {
      wp_odm_solr_log('solr-ckan-manager delete_dataset Error: ' . $e);
    }

		return $result;
  }

}

$GLOBALS['WP_Odm_Solr_CKAN_Manager'] = new WP_Odm_Solr_CKAN_Manager();

function WP_Odm_Solr_CKAN_Manager() {
	return $GLOBALS['WP_Odm_Solr_CKAN_Manager'];
}

?>
