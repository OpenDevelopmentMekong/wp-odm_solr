<?php

  global $post;
  $post = get_post($document->id);

  global $wp_query;
	$args = array(
		"p" => $document->id,
    "post_type" => "map-layer",
		"posts_per_page" => 1
	);
	$search_results = new WP_Query($args);

  while (have_posts()) : the_post();
		odm_get_template('post-grid-single-4-cols',array(
			"post" => get_post(),
			"show_meta" => false
	),true);
	endwhile;
