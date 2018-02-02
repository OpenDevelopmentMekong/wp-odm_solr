<?php

  $meta_fields = odm_country_manager()->get_current_country() == "mekong" ? array("language","country","date","categories","tags") : array("language","date","categories","tags");

  if (function_exists("switch_to_blog")):
    switch_to_blog($document->blogid);
  endif;

  $post_id = isset($document->wp_id) ? $document->wp_id : $document->index_id;
  if ( 'publish' === get_post_status($post_id)):
    $fetched_post = get_post($post_id);
    odm_get_template('post-list-single-1-cols',array(
      "post" => $fetched_post,
      "show_post_type" => true,
    	"show_meta" => true,
      "meta_fields" => $meta_fields,
      "max_num_topics" => 5,
      "max_num_tags" => 5,
    	"show_source_meta" => true,
    	"show_thumbnail" => true,
    	"show_excerpt" => true,
    	"show_summary_translated_by_odc_team" => true,
      "show_solr_meta" => false,
      "highlight_words_query" => $param_query,
      "solr_search_result" => $document,
    	"header_tag" => true,
      "order" => $param_sorting
  	),true);
  endif;

  if (function_exists("restore_current_blog")):
    restore_current_blog();
  endif;

?>
