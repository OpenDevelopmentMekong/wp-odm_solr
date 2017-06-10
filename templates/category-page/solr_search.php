<?php get_header(); ?>

<?php
  include_once dirname(dirname(plugin_dir_path(__FILE__))).'/utils/solr-wp-manager.php';
  include_once dirname(dirname(plugin_dir_path(__FILE__))).'/utils/solr-ckan-manager.php';

  global $post, $wp_query;
  $is_search_page = false;
  $term = $wp_query->queried_object;
  
  $param_query = "";
  $s = "";
  $param_type = isset($_GET['type']) ? $_GET['type'] : null;
  $param_taxonomy = $term->name;
  $param_page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
  $param_page_solr = (isset($_GET['page']) && (int)$_GET['page'] > 0) ? ((int)$_GET['page'] -1) : 0;
  $param_country = odm_country_manager()->get_current_country() == 'mekong' && isset($_GET['country']) ? $_GET['country'] : array();
  if (empty($param_country) && odm_country_manager()->get_current_country() != 'mekong'):
    $param_country = array(odm_country_manager()->get_current_country_code());
  endif;
	$param_sorting = isset($_GET['sorting']) ? $_GET['sorting'] : 'score';

  //================ Filter Values ===================== //

  $taxonomy_list = odm_taxonomy_manager()->get_taxonomy_list();
  $country_codes_iso2 = odm_country_manager()->get_country_codes_iso2_list();
  $languages = odm_language_manager()->get_supported_languages();
  $license_list = wpckan_get_license_list();
  $top_tier_taxonomic_terms = odm_taxonomy_manager()->get_taxonomy_top_tier();

  //================ Build query attributes ===================== //

  $attrs = [];
  $control_attrs = array(
    "sorting" => $param_sorting,
    "limit" => 20,
    "page" => $param_page_solr
  );

  //================ Search types ===================== //

  $all_search_types = array(
    'dataset' => array(
      'title' => 'Datasets',
      'icon' => 'fa fa-database',
      'type' => 'ckan',
      'archive_url' => '/data'
    ),
    'library_record' => array(
      'title' =>'Library publications',
      'icon' => 'fa fa-book',
      'type' => 'ckan',
      'archive_url' => null
    ),
    'laws_record' => array(
      'title' =>'Laws',
      'icon' => 'fa fa-gavel',
      'type' => 'ckan',
      'archive_url' => null
    ),
    'agreement' => array(
      'title' =>'Agreements',
      'icon' => 'fa fa-handshake-o',
      'type' => 'ckan',
      'archive_url' => null
    ),
    'map-layer' => array(
      'title' => 'Maps',
      'icon' => 'fa fa-map-marker',
      'type' => 'wp',
      'archive_url' => '/layers'
    ),
    'news-article' => array(
      'title' => 'News articles',
      'icon' => 'fa fa-newspaper-o',
      'type' => 'wp',
      'archive_url' => '/news'
    ),
    'topic' => array(
      'title' => 'Topics',
      'icon' => 'fa fa-list',
      'type' => 'wp',
      'archive_url' => '/topics'
    ),
    'profiles' => array(
      'title' => 'Profiles',
      'icon' => 'fa fa-briefcase',
      'type' => 'wp',
      'archive_url' => '/profiles'
    ),
    'story' => array(
      'title' => 'Stories',
      'icon' => 'fa fa-lightbulb-o',
      'type' => 'wp',
      'archive_url' => '/story'
    ),
    'announcement' => array(
      'title' => 'Announcements',
      'icon' => 'fa fa-bullhorn',
      'type' => 'wp',
      'archive_url' => '/announcements'
    )/*,
    'site-update' => array(
      'title' => 'Site updates',
      'icon' => 'fa fa-flag',
      'type' => 'wp',
      'archive_url' => '/updates'
    )*/
  );

  if (isset($supported_types_override) && !empty($supported_types_override)):
    foreach ($all_search_types as $key => $value):
      if (!in_array($key,$supported_types_override)):
        unset($all_search_types[$key]);
      endif;
    endforeach;
  endif;

  //================ Run queries and gather both results and facets ===================== //

  $results = [];
  $facets = [];

  $facets_mapping = array(
    "categories" => "vocab_taxonomy",
    "odm_spatial_range" => "extras_odm_spatial_range",
    "odm_language" => "extras_odm_language",
    "tags" => "extras_odm_keywords",
    "vocab_taxonomy" => "vocab_taxonomy",
    "extras_odm_spatial_range" => "extras_odm_spatial_range",
    "extras_odm_language" => "extras_odm_language",
    "extras_odm_keywords" => "extras_odm_keywords",
    "license_id" => "license_id",
    "metadata_modified" => "metadata_modified"
  );

  // -------------- Get all results --------------- //
  foreach ($all_search_types as $key => $value):
    $attrs = [];
    $result = null;

    if ($value['type'] == 'ckan'):
      //Taxonomy
      if (isset($param_taxonomy) && $param_taxonomy != 'all') {
        $attrs["vocab_taxonomy"] = $param_taxonomy;
      }

      // Language
      if (!empty($param_language)) {
        $attrs["extras_odm_language"] = $param_language;
      }

      // Country
      if (!empty($param_country) && $param_country != 'mekong' && $param_country != 'all') {
        $attrs["extras_odm_spatial_range"] = $param_country;
      }

      //License
      if (!empty($param_license)) {
        $attrs['license_id'] = $param_license;
      }

      //metadata_modified
      if (isset($param_metadata_modified) && $param_metadata_modified != 'all'){
        $attrs['metadata_modified'] = $param_metadata_modified;
      }

      $attrs["dataset_type"] = $key;
      $attrs["capacity"] = "public";
      $result = WP_Odm_Solr_CKAN_Manager()->query($param_query,$attrs,$control_attrs);

    else:

      //Taxonomy
      if (isset($param_taxonomy) && $param_taxonomy != 'all') {
        $attrs["categories"] = $param_taxonomy;
      }

      // Language
      if (!empty($param_language)) {
        $attrs["odm_language"] = $param_language;
      }

      // Country
      if (!empty($param_country) && $param_country != 'mekong' && $param_country != 'all') {
        $attrs["odm_spatial_range"] = $param_country;
      }

      //metadata_modified
      if (isset($param_metadata_modified) && $param_metadata_modified != 'all'){
        $attrs['metadata_modified'] = $param_metadata_modified;
      }

      $attrs["type"] = $key;
      $result = WP_Odm_Solr_WP_Manager()->query($param_query,$attrs,$control_attrs);
    endif;

    $results[$key] = $result["resultset"];
    $facets[$key] = $result["facets"];
  endforeach; ?>

<section class="container">
  <?php
    if (!WP_Odm_Solr_WP_Manager()->ping_server() || !WP_Odm_Solr_CKAN_Manager()->ping_server()):  ?>
      <div class="row">
        <div class="sixteen columns">
            <p class="error">
              <?php _e("wp-odm_solr plugin is not properly configured. Please contact the system's administrator",'wp-odm_solr'); ?>
            </p>
        </div>
      </div>
  <?php
    else:

      // -------------- Define top param type --------------- //
      if (!isset($param_type) || (isset($param_type) && array_key_exists($param_type,$results) && $results[$param_type]->getNumFound() == 0)):
        foreach ($all_search_types as $key => $value):
          if (isset($results[$key]) && $results[$key]->getNumFound() > 0):
            $param_type = $key;
            break;
          endif;
        endforeach;
      endif;

      // -------------- Define facets --------------- //
      if (array_key_exists($param_type,$facets)):
        foreach ($facets[$param_type] as $facet_key => $facet):
          $facet_key_mapped = $facets_mapping[$facet_key];
          if (!isset($facets[$param_type][$facet_key_mapped])):
            $facets[$param_type][$facet_key_mapped] = [];
          endif;
          foreach ($facet as $facet_value => $count):
            if ($facet_key_mapped == "vocab_taxonomy"):
              foreach ($top_tier_taxonomic_terms as $top_tier_term => $children):
                if (in_array($facet_value,$children) || $facet_value == $top_tier_term):
                  $facet_value = $top_tier_term;
                  break;
                endif;
              endforeach;
            endif;
            $facets[$param_type][$facet_key_mapped][$facet_value] = $count;
          endforeach;
        endforeach;
      endif; ?>

      <?php
      $content_resultset = array_key_exists($param_type,$results) ? $results[$param_type] : null;
      $content_resultcount = ($content_resultset) ? $content_resultset->getNumFound() : 0;
      ?>
      
      <div class="advanced-nav-filters ">
        <div class="row">
          <div class="sixteen columns">
            <div class="content-type-tabs">
              <?php include plugin_dir_path(__FILE__). 'partials/content-types.php'; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row solr_results search-results">
        <?php
        if (isset($content_resultset) && $content_resultcount > 0):
          foreach ($content_resultset as $document): ?>

            <?php
            if($all_search_types[$param_type]['type'] == 'ckan'):
              include plugin_dir_path(__FILE__). 'partials/ckan_result_template.php';
            else:
              if ($param_type == 'map-layer'):
                include plugin_dir_path(__FILE__). 'partials/wp_map_layer_result_template.php';
              elseif ($param_type == 'news-article'):
                include plugin_dir_path(__FILE__). 'partials/wp_news_article_result_template.php';
              elseif ($param_type == 'topic'):
                include plugin_dir_path(__FILE__). 'partials/wp_topic_result_template.php';
              elseif ($param_type == 'profiles'):
                include plugin_dir_path(__FILE__). 'partials/wp_profiles_result_template.php';
              elseif ($param_type == 'story'):
                include plugin_dir_path(__FILE__). 'partials/wp_story_result_template.php';
              elseif ($param_type == 'announcement'):
                include plugin_dir_path(__FILE__). 'partials/wp_announcement_result_template.php';
              elseif ($param_type == 'site-update'):
                include plugin_dir_path(__FILE__). 'partials/wp_site_update_result_template.php';
              else:
                include plugin_dir_path(__FILE__). 'partials/wp_result_template.php';
              endif;
            endif;
          endforeach;
        endif; ?>
      </div>

      <?php
        if (isset($content_resultset) && $content_resultcount > 0):
          $total_pages = ceil($content_resultset->getNumFound()/$control_attrs['limit']);
          if ($total_pages > 1): ?>
            <div class="row">
              <div class="pagination sixteen columns">
                <?php
                odm_get_template('pagination_solr', array(
                              "current_page" => $param_page,
                              "total_pages" => $total_pages
                            ),true); ?>
              </div>
            </div>
        <?php
          endif;
        endif;
      endif; ?>
  </section> <!-- end of container -->
    

