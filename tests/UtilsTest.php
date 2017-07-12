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

  public function testParseAttrsFromStringCorrect(){
    $to_parse = '?s=land&type=topic&taxonomy=Land&country=km&language=all&license=CC-BY-SA-4.0';
    $result = wp_odm_solr_parse_attrs_from_string($to_parse);
    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('dataset_type',$result);
    $this->assertArrayHasKey('vocab_taxonomy',$result);
    $this->assertArrayHasKey('extras_odm_spatial_range',$result);
    $this->assertArrayHasKey('extras_odm_language',$result);
    $this->assertArrayHasKey('license_id',$result);
    $this->assertEquals($result["dataset_type"],"topic");
    $this->assertEquals($result["vocab_taxonomy"],"Land");
    $this->assertEquals($result["extras_odm_language"],"all");
    $this->assertEquals($result["extras_odm_spatial_range"],"km");
    $this->assertEquals($result["license_id"],"CC-BY-SA-4.0");
  }

  public function testParseAttrsFromStringCorrectArray(){
    $to_parse = '?s=land&type=topic&taxonomy=Land&country=km&language[]=en&language[]=km&license[]=CC-BY-SA-4.0';
    $result = wp_odm_solr_parse_attrs_from_string($to_parse);
    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('extras_odm_language',$result);
    $this->assertTrue(is_array($result["extras_odm_language"]));
    $this->assertEquals($result["extras_odm_language"],array("en","km"));
  }

  public function testParseAttrsFromStringCorrectLong(){
    $to_parse = '?type=dataset&s=land&taxonomy=all&country%5B%5D=th&license%5B%5D=CC-BY-4.0&metadata_created=all&sorting=score';
    $result = wp_odm_solr_parse_attrs_from_string($to_parse);
    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('dataset_type',$result);
    $this->assertArrayHasKey('vocab_taxonomy',$result);
    $this->assertArrayHasKey('extras_odm_spatial_range',$result);
    $this->assertArrayHasKey('license_id',$result);
    $this->assertEquals($result["dataset_type"],"dataset");
    $this->assertEquals($result["vocab_taxonomy"],"all");
    $this->assertTrue(is_array($result["extras_odm_spatial_range"]));
    $this->assertTrue(is_array($result["license_id"]));
  }

  public function testParseControlAttrsFromStringCorrect(){
    $to_parse = '?s=land&sorting=metadata_created&page=1';
    $result = wp_odm_solr_parse_control_attrs_from_string($to_parse);
    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('sorting',$result);
    $this->assertArrayHasKey('page',$result);
    $this->assertEquals($result["sorting"],"metadata_created");
    $this->assertEquals($result["page"],1);
  }

  public function testParseQueryFromStringCorrect(){
    $to_parse = '?s=land&sorting=metadata_created&page=1';
    $result = wp_odm_solr_parse_query_from_string($to_parse);
    $this->assertFalse(is_array($result));
    $this->assertEquals($result,"land");
  }

  public function testParseQueryFromStringCorrectLong(){
    $to_parse = '?type=dataset&s=land&taxonomy=all&country%5B%5D=th&license%5B%5D=CC-BY-4.0&metadata_created=all&sorting=score';
    $result = wp_odm_solr_parse_query_from_string($to_parse);
    $this->assertFalse(is_array($result));
    $this->assertEquals($result,"land");
  }

}
