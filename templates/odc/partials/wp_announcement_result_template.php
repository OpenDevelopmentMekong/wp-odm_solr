<?php

  $meta_fields = odm_country_manager()->get_current_country() == "mekong" ? array("language","country","date","categories","tags") : array("language","date","categories","tags");

  if (function_exists("switch_to_blog")):
    switch_to_blog($document->blogid);
  endif;

  $fetched_post = get_post($document->index_id);
  odm_get_template('post-list-single-2-cols',array(
  	"post" => $fetched_post,
    "show_post_type" => true,
  	"show_meta" => true,
    "meta_fields" => $meta_fields,
  	"show_source_meta" => true,
  	"show_thumbnail" => true,
  	"show_excerpt" => true,
  	"show_summary_translated_by_odc_team" => true,
    "show_solr_meta" => false,
    "max_num_topics" => 5,
    "max_num_tags" => 5,
    "highlight_words_query" => $param_query,
    "solr_search_result" => $document,
  	"header_tag" => true,
    "extra_classes" => "solr_result_two_cols",
    "order" => $param_sorting
	),true);

  if (function_exists("restore_current_blog")):
    restore_current_blog();
  endif;

?>
