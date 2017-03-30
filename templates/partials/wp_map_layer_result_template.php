<?php

  if (function_exists("switch_to_blog")):
    switch_to_blog($document->blogid);
  endif;

  $fetched_post = get_post($document->id);
  odm_get_template('post-grid-single-4-cols',array(
 	            "post" => $fetched_post,
 	            "show_meta" => false)
 	          , true);

  if (function_exists("restore_current_blog")):
    restore_current_blog();
  endif;

?>
