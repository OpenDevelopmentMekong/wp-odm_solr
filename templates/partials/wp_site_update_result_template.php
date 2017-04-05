<?php

  if (function_exists("switch_to_blog")):
    switch_to_blog($document->blogid);
  endif;

  $fetched_post = get_post($document->id);
  odm_get_template('post-list-single-1-cols',array(
  					"post" => get_post(),
  					"show_meta" => true,
  					"show_source_meta" => true,
						"show_thumbnail" => true,
						"show_excerpt" => true,
						"show_summary_translated_by_odc_team" => true,
            "show_solr_meta" => true,
            "solr_search_result" => $document,
						"header_tag" => true
  			),true);

  if (function_exists("restore_current_blog")):
    restore_current_blog();
  endif;

?>
