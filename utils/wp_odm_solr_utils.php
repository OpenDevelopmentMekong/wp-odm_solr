<?php

  use Solarium\Solarium;
  use Solarium\QueryType\Select\Query\Query as Select;

  function wp_odm_solr_validate_settings_read($solr_host,$solr_port,$solr_path,$solr_core,$solr_scheme,$solr_user,$solr_pwd){

    $server_config = array(
      'endpoint' => array(
          'localhost' => array(
              'host' => $solr_host,
              'port' => $solr_port,
              'path' => $solr_path,
  						'core' => $solr_core,
  						'scheme' => $solr_scheme
          )
      )
  	);

    try {
      $client = new \Solarium\Client($server_config);
      $client->getEndpoint()->setAuthentication($solr_user,$solr_pwd);
      $ping = $client->createPing();
      $result = $client->ping($ping);
    } catch (Solarium\Exception $e) {
      return false;
    }

    return true;
  }

?>
