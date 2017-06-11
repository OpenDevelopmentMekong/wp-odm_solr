<?php

$site_admin = in_array('administrator',  wp_get_current_user()->roles);
// $max_posts_to_index_per_type = 100;
// $post_types_to_index = array(
// 	'news-article','topic','dashboard','dataviz','profiles','tabular','announcement','site-update','story','map-layer'
// );

if(!is_user_logged_in() && !$site_admin):

  echo('You do not have access to this functionality');

else:
  
  $min_id = isset($_GET["min_id"]) ? $_GET["min_id"] : 1;
  $num_posts = isset($_GET["num_posts"]) ? $_GET["num_posts"] : 50;
	
  //echo "Clearing WP index" . nl2br("\n");
  //Odm_Solr_WP_Manager()->clear_index();

	//foreach ( $post_types_to_index as $post_type):

		$args = array(
			'post__in' => array(1124229)
		);

		$posts = get_posts($args);

		echo("Batch of " . count($posts) . " posts found" . nl2br("\n"));

		foreach ( $posts as $post):

			echo("Indexing post with title:" . $post->post_title . " and type: " . $post->post_type . nl2br("\n"));
			Odm_Solr_WP_Manager()->index_post($post);

		endforeach;

		wp_reset_postdata();		

		echo("Indexed " . count($posts) . nl2br("\n"));
    
    if (count($posts) == 0):
      echo("Indexing complete, no posts found");
    endif;

	//endforeach;

endif;

?>
