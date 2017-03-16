<?php

require_once dirname(dirname(__FILE__)) . '/utils/wp_od_solr_utils.php';

class UtilsTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $GLOBALS['wp_odm_solr_options'] = $this->getMockBuilder(Wpckan_Options::class)
                                   ->setMethods(['get_option']);
  }

  public function tearDown()
  {
    parent::tearDown();
  }

}
