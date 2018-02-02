<?php

  $meta_fields = odm_country_manager()->get_current_country() == "mekong" ? array("date","country","language") : array("date","language");

  if (function_exists("switch_to_blog")):
    switch_to_blog($document->blogid);
  endif;

  $post_id = $document->wp_id || $document->index_id;
  if ( 'publish' === get_post_status($post_id)):
    $fetched_post = get_post($post_id);
    odm_get_template('post-grid-single-4-cols',array(
  		"post" => $fetched_post,
      "show_post_type" => true,
  		"show_meta" => true,
      "meta_fields" => $meta_fields)
    ,true);
  endif;

  if (function_exists("restore_current_blog")):
    restore_current_blog();
  endif;

?>
