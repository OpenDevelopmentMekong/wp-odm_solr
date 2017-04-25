<?php

  if (function_exists("switch_to_blog")):
    switch_to_blog($document->blogid);
  endif;

  $fetched_post = get_post($document->id);
  odm_get_template('post-list-single-2-cols',array(
			"post" => $fetched_post,
			"show_meta" => true,
			"show_source_meta" => false,
			"show_thumbnail" => true,
      "show_post_type" => true,
			"show_excerpt" => true,
			"show_summary_translated_by_odc_team" => false,
      "show_solr_meta" => false,
      "solr_search_result" => $document,
			"header_tag" => false
	),true);

  if (function_exists("restore_current_blog")):
    restore_current_blog();
  endif;

?>
