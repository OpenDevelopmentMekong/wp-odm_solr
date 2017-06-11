<?php

$is_site_admin = in_array('administrator',  wp_get_current_user()->roles);

$num_posts = isset($_GET["num_posts"]) ? $_GET["num_posts"] : 50;
$offset = isset($_GET["offset"]) ? $_GET["offset"] : 0;
$post_type = 'topic';

if(!is_user_logged_in() && !$is_site_admin):

  echo('You do not have access to this functionality');

else:

	//echo "Clearing WP index" . nl2br("\n");

  //Odm_Solr_WP_Manager()->clear_index();

			$args = array(
		    'post_type'      => array('news-article','topic','dashboard','dataviz','profiles','tabular','announcement','site-update','story','map-layer'),
				'posts_per_page' => $num_posts,
        'offset'         => $offset,
			);

			$posts = get_posts($args);

			#echo("Batch of " . count($posts) . " posts found with post type:" . $post_type . nl2br("\n"));

			foreach ( $posts as $post):

				echo("Indexing post with title:" . $post->post_title . " and type " . $post->post_type . nl2br("\n"));
				//Odm_Solr_WP_Manager()->index_post($post);

			endforeach;

			wp_reset_postdata();

		  echo("Indexed " . count($posts) . " of type " . $post_type . nl2br("\n"));


endif;

?>
