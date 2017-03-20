<?php

require_once dirname(dirname(__FILE__)) . '/utils/wp_odm_solr_utils.php';

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

  public function testHighlightSearchWords(){
    $query = "land concession";
    $to_highlight = "This is a land concession";
    $arguments = wp_odm_solr_highlight_search_words($query,$to_highlight);
    $this->assertContains($arguments,"This is a <b>land</b> <b>concession</b>");
  }

  public function testHighlightSearchWordsCaseInsensitive(){
    $query = "land concession";
    $to_highlight = "This is a Land Concession";
    $arguments = wp_odm_solr_highlight_search_words($query,$to_highlight);
    $this->assertContains($arguments,"This is a <b>Land</b> <b>Concession</b>");
  }

  public function testHighlightSearchWordsPartial(){
    $query = "land topic";
    $to_highlight = "This topic is about Land-tenure";
    $arguments = wp_odm_solr_highlight_search_words($query,$to_highlight);
    $this->assertContains($arguments,"This <b>topic</b> is about <b>Land</b>-tenure");
  }


}
