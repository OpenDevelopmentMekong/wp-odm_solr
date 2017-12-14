<?php

include_once dirname(dirname(__FILE__)).'/utils/solr-unified-manager.php';

$is_site_admin = in_array('administrator',  wp_get_current_user()->roles);

$args = array(
  'post_type'      => $supported_post_types,
	'posts_per_page' => $num_posts,
  'orderby'         => 'rand',
  'status'         => 'publish'
);

/*if(!is_user_logged_in()):

  echo('You do not have access to this functionality');

else:*/ ?>


<input id="search_field" name="<?php echo $query_var_name; ?>" type="text" class="full-width-search-box search_field" value="<?php echo $param_query?>" placeholder="<?php _e("Search datasets, topics, News...",'wp-odm_solr'); ?>" data-solr-host="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host'); ?>" data-solr-scheme="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme'); ?>"  data-solr-path="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path'); ?>" data-solr-core-unified="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_unified'); ?>" data-odm-current-lang="<?php echo odm_language_manager()->get_current_language(); ?>" data-odm-current-country="<?php echo odm_country_manager()->get_current_country_code(); ?>" data-odm-show-regional-contents="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_regional_contents_enabled'); ?>"></input>
<div id="search_results"></div>

<?php
  //endif;
 ?>

 <?php
   wp_register_script('search-ckan-records', plugins_url('wp-odm_solr/js/search_ckan_records.js'));
   wp_enqueue_script('search-ckan-records'); ?>
