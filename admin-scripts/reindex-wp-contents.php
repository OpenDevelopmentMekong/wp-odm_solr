<?php

include_once dirname(dirname(__FILE__)).'/utils/solr-wp-manager.php';

$is_site_admin = in_array('administrator',  wp_get_current_user()->roles);

$num_posts = isset($_GET["num_posts"]) ? $_GET["num_posts"] : 50;
$offset = isset($_GET["offset"]) ? $_GET["offset"] : 0;
$clear = isset($_GET["clear"]) ? $_GET["clear"] : false;
$supported_post_types = array('news-article','topic','dashboard','dataviz','profiles','tabular','announcement','site-update','story','map-layer');

if(!is_user_logged_in() && !$is_site_admin):

  echo('You do not have access to this functionality');

else:

  if ($clear):
    echo "Clearing WP index" . nl2br("\n");
    WP_Odm_Solr_WP_Manager()->clear_index();    
  endif;

	$args = array(
    'post_type'      => $supported_post_types,
		'posts_per_page' => $num_posts,
    'offset'         => $offset,
    'orderby'         => 'ID',
    'order'         => 'ASC',
    'status'         => 'publish',
    'meta_key' => 'solr_indexed_at',
	  'meta_value' => time(),
	  'meta_compare' => '<='
	);

	$posts = get_posts($args);

	echo("Batch of " . count($posts) . nl2br("\n"));

	foreach ( $posts as $post):
		echo("Indexing post with ID: " . $post->ID ." and title:" . $post->post_title . " and type " . $post->post_type . nl2br("\n"));
		WP_Odm_Solr_WP_Manager()->index_post($post);
    update_post_meta( $post->ID, "solr_indexed_at", time());
	endforeach;

	wp_reset_postdata();

  echo("Posts Indexed " . count($posts) . nl2br("\n"));

endif;

?>
