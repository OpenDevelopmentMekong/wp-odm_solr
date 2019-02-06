<?php

include_once dirname(__FILE__).'/utils/solr-wp-manager.php';

class odm_solr_commands {

  /**
   * Reindexes all the posts on a site
   */
  function reindex_posts($args) {

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

      WP_CLI::log("Batch of " . count($posts));
      foreach ($posts as $post) {
	WP_CLI::log("Indexing post with ID: " . $post->ID ." and title:" 
		    . $post->post_title . " and type " . $post->post_type);
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

  /**
   * Reindexes all the sites in the network
   **/
  function reindex_network($args) {
    $response = WP_CLI::launch_self('site list', array(), array( 'format' => 'json' ), false, true );
    $sites = json_decode( $response->stdout );
    $unused = array();
    $used = array();
    foreach( $sites as $site ) {
      WP_CLI::log( "Reindexing {$site->url} ..." );
      $response = WP_CLI::launch_self('odm-solr reindex_posts',
				      array(),
				      array( 'url' => $site->url, 'format' => 'json' ), false, true );
    }
  }

}?>
