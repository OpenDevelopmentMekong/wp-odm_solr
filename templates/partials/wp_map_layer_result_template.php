<?php

  switch_to_blog($document->blogid);

  $fetched_post = get_post($document->id);
  odm_get_template('post-grid-single-4-cols',array(
 	            "post" => $fetched_post,
 	            "show_meta" => false)
 	          , true);

  restore_current_blog();
?>
