<?php

$site_admin = in_array('administrator',  wp_get_current_user()->roles);
// $max_posts_to_index_per_type = 100;
// $post_types_to_index = array(
// 	'news-article','topic','dashboard','dataviz','profiles','tabular','announcement','site-update','story','map-layer'
// );

if(!is_user_logged_in() && !$site_admin):

  echo('You do not have access to this functionality');

else:
  
  $min_id = isset($_GET["min_id"]) ? $_GET["min_id"] : 0;
  $num_posts = isset($_GET["num_posts"]) ? $_GET["num_posts"] : 50;
	
  //echo "Clearing WP index" . nl2br("\n");
  //Odm_Solr_WP_Manager()->clear_index();

	//foreach ( $post_types_to_index as $post_type):

		$current_post_number = 0;

		$args = array(
			'posts_per_page' => $num_posts,
      'offset'         => $min_id,
      'orderby'        => 'ID',
      'order'        => 'ASC',
		);

		$posts = get_posts($args);

		echo("Batch of " . count($posts) . " posts found with post type:" . $post_type . nl2br("\n"));

		foreach ( $posts as $post):

			echo("Indexing post with title:" . $post->post_title . nl2br("\n"));
			Odm_Solr_WP_Manager()->index_post($post);

		endforeach;

		wp_reset_postdata();		

		echo("Indexed " . count($posts) . " of type " . $post_type . nl2br("\n"));
    
    if (count($posts) == 0):
      echo("Indexing complete, no posts found");
      header("HTTP/1.0 404 Not Found");
    endif;

	//endforeach;

endif;

?>
