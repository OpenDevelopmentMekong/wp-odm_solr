<?php
  $post_id = $document->id;
  $post = get_post($post_id);

 odm_get_template('post-grid-single-4-cols',array(
 	            "post" => $post,
 	            "show_meta" => false)
 	          , true); ?>
