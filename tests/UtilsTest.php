<?php

require_once dirname(dirname(__FILE__)) . '/utils/wp_odm_solr_utils.php';

class UtilsTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {

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

  public function testParseMultilingualCkanContent(){
    $to_parse = '{"en":"some english text","km":"some khmer text"}';
    $fallback = null;
    $lang = "en";
    $result = wp_odm_solr_parse_multilingual_ckan_content($to_parse,$lang,$fallback);
    $this->assertContains($result,"some english text");
  }

  public function testParseMultilingualCkanContentKm(){
    $to_parse = '{"en":"some english text","km":"some khmer text"}';
    $fallback = null;
    $lang = "km";
    $result = wp_odm_solr_parse_multilingual_ckan_content($to_parse,$lang,$fallback);
    $this->assertContains($result,"some khmer text");
  }

  public function testParseMultilingualCkanContentFallback(){
    $to_parse = '{"en":"some english text","km":"some khmer text"}';
    $fallback = 'some text';
    $lang = "fr";
    $result = wp_odm_solr_parse_multilingual_ckan_content($to_parse,$lang,$fallback);
    $this->assertContains($result,"some english text");
  }
  
  public function testParseMultilingualCkanNoContentFallback(){
    $to_parse = '{"de":"some german text","km":"some khmer text"}';
    $fallback = 'some text';
    $lang = "fr";
    $result = wp_odm_solr_parse_multilingual_ckan_content($to_parse,$lang,$fallback);
    $this->assertContains($result,"some text");
  }

  public function testParseMultilingualCkanContentFallbackNoValidJson(){
    $to_parse = '{"en":"some english text","km","some khmer text"}';
    $fallback = 'some text';
    $lang = "en";
    $result = wp_odm_solr_parse_multilingual_ckan_content($to_parse,$lang,$fallback);
    $this->assertContains($result,"some text");
  }

  public function testParseMultilingualCkanContentFallbackNoFallback(){
    $to_parse = '{"en":"some english text","km","some khmer text"}';
    $fallback = null;
    $lang = "en";
    $result = wp_odm_solr_parse_multilingual_ckan_content($to_parse,$lang,$fallback);
    $this->assertNull($result);
  }

  // public function testParseMultilingualWpContent(){
  //   $to_parse = '[:en]some english text[:km]some khmer text[:]';
  //   $fallback = null;
  //   $lang = "en";
  //   $result = wp_odm_solr_parse_multilingual_wp_content($to_parse,$lang,$fallback);
  //   $this->assertContains($result,"some english text");
  // }


}
