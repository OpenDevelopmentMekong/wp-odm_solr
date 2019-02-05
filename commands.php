<?php

include_once dirname(__FILE__).'/utils/solr-wp-manager.php';

function odm_solr_index_posts($args) {

  function _internal($page) {
    $num_posts = 200;
    $supported_post_types = array('news-article', 'topic', 'dashboard', 'dataviz',
				  'profiles', 'tabular', 'announcement',
				  'site-update', 'story', 'map-layer');

    $post_args = array(
		       'post_type'      => $supported_post_types,
		       'posts_per_page' => $num_posts,
		       'orderby'        => 'date',
		       'order'          => 'ASC',
		       'offset'         => $page*$num_posts,
		       'status'         => 'publish'
		       );

    // note 'offset': n gets by page

    $posts = get_posts($post_args);

    WP_CLI::log("Batch of " . count($posts) . "\n");
    foreach ($posts as $post) {
      WP_CLI::log("Indexing post with ID: " . $post->ID ." and title:" 
	   . $post->post_title . " and type " . $post->post_type . "\n");
      WP_Odm_Solr_WP_Manager()->index_post($post);
    }
    return count($posts);
  }
  
  $page = 0;
  while (_internal($page)) {
      $page++;
  }
  

  WP_CLI::success($args[0]);
}
?>
