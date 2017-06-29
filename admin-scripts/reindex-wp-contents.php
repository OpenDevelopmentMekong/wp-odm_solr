<?php

include_once dirname(dirname(__FILE__)).'/utils/solr-wp-manager.php';

$is_site_admin = in_array('administrator',  wp_get_current_user()->roles);

$num_posts = isset($_GET["num_posts"]) ? $_GET["num_posts"] : 500;
$supported_post_types = array('news-article','topic','dashboard','dataviz','profiles','tabular','announcement','site-update','story','map-layer');

$args = array(
  'post_type'      => $supported_post_types,
	'posts_per_page' => $num_posts,
  'orderby'         => 'rand',
  'status'         => 'publish'
);

$posts = get_posts($args);

echo("Batch of " . count($posts) . nl2br("\n"));

foreach ( $posts as $post):
	echo("Indexing post with ID: " . $post->ID ." and title:" . $post->post_title . " and type " . $post->post_type . nl2br("\n"));
	WP_Odm_Solr_WP_Manager()->index_post($post);
endforeach;

wp_reset_postdata();

echo("Posts Indexed " . count($posts) . nl2br("\n"));

?>
