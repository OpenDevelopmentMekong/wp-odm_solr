<?php get_header(); ?>

    <?php
      include_once dirname(dirname(plugin_dir_path(__FILE__))).'/utils/solr-wp-manager.php';
      include_once dirname(dirname(plugin_dir_path(__FILE__))).'/utils/solr-ckan-manager.php';

      global $post;
      $is_search_page = false;

      if (isset($post)):
        $configured_supported_types = get_post_meta($post->ID, '_solr_pages_attributes_supported_types', true);
        $supported_types_override =  !empty($configured_supported_types) ? explode(",",$configured_supported_types) : null;
        $is_search_page = get_post_type($post->ID) == 'search-pages';
      endif;

      $param_query = !empty($_GET['s']) ? $_GET['s'] : null;
      if (!isset($param_query)):
        $param_query = !empty($_GET['query']) ? $_GET['query'] : null;
      endif;

      $param_type = isset($_GET['type']) ? $_GET['type'] : null;
      $param_license = isset($_GET['license']) ? $_GET['license'] : array();
      $param_organization = isset($_GET['organization']) ? $_GET['organization'] : array();
      $param_taxonomy = isset($_GET['taxonomy']) ? $_GET['taxonomy'] : 'all';
      $param_language = isset($_GET['language']) ? $_GET['language'] : array();
      $param_page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
      $param_page_solr = (isset($_GET['page']) && (int)$_GET['page'] > 0) ? ((int)$_GET['page'] -1) : 0;
      $param_country = odm_country_manager()->get_current_country() == 'mekong' && isset($_GET['country']) ? $_GET['country'] : array();
    	$param_sorting = isset($_GET['sorting']) ? $_GET['sorting'] : 'score';
      $param_metadata_modified = isset($_GET['metadata_modified']) ? $_GET['metadata_modified'] : 'all';
      $param_metadata_created = isset($_GET['metadata_created']) ? $_GET['metadata_created'] : 'all';

      //================ Filter Values ===================== //

      $taxonomy_list = odm_taxonomy_manager()->get_taxonomy_list();
      $country_codes_iso2 = odm_country_manager()->get_country_codes_iso2_list();
      $languages = odm_language_manager()->get_supported_languages();
      $license_list = wpckan_get_license_list();
      $organization_list = wpckan_get_organization_list(wpckan_get_ckan_domain());;
      $top_tier_taxonomic_terms = odm_taxonomy_manager()->get_taxonomy_top_tier();

      //================ Build query attributes ===================== //

      $attrs = [];
      $control_attrs = array(
        "sorting" => $param_sorting,
        "limit" => 12,
        "page" => $param_page_solr
      );

      //================ Search types ===================== //

      $all_search_types = array(
        'all' => array(
          'title' => 'All',
          'icon' => 'fa fa-asterisk',
          'archive_url' => null
        ),
        'dataset' => array(
          'title' => 'Datasets',
          'icon' => 'fa fa-database',
          'archive_url' => '/data'
        ),
        'library_record' => array(
          'title' =>'Publications',
          'icon' => 'fa fa-book',
          'archive_url' => null
        ),
        'laws_record' => array(
          'title' =>'Laws',
          'icon' => 'fa fa-gavel',
          'archive_url' => null
        ),
        'agreement' => array(
          'title' =>'Agreements',
          'icon' => 'fa fa-handshake-o',
          'archive_url' => null
        ),
        'map-layer' => array(
          'title' => 'Maps',
          'icon' => 'fa fa-map-marker',
          'archive_url' => '/layers'
        ),
        'news-article' => array(
          'title' => 'News',
          'icon' => 'fa fa-newspaper-o',
          'archive_url' => '/news'
        ),
        'topic' => array(
          'title' => 'Topics',
          'icon' => 'fa fa-list',
          'archive_url' => '/topics'
        ),
        'profiles' => array(
          'title' => 'Profiles',
          'icon' => 'fa fa-briefcase',
          'archive_url' => '/profiles'
        ),
        'story' => array(
          'title' => 'Stories',
          'icon' => 'fa fa-lightbulb-o',
          'archive_url' => '/story'
        ),
        'announcement' => array(
          'title' => 'Announcements',
          'icon' => 'fa fa-bullhorn',
          'archive_url' => '/announcements'
        )/*,
        'site-update' => array(
          'title' => 'Site updates',
          'icon' => 'fa fa-flag',
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
        "vocab_taxonomy" => "vocab_taxonomy",
        "odm_spatial_range" => "extras_odm_spatial_range",
        "extras_odm_spatial_range" => "extras_odm_spatial_range",
        "odm_language" => "extras_odm_language",
        "extras_odm_language" => "extras_odm_language",
        "tags" => "extras_odm_keywords",
        "extras_odm_keywords" => "extras_odm_keywords",
        "license_id" => "license_id",
        "metadata_modified" => "metadata_modified",
        "metadata_created" => "metadata_created",
        "organization" => "organization"
      );

      // -------------- Get all results --------------- //
      foreach ($all_search_types as $key => $value):
        $attrs = [];
        $result = null;

        //Taxonomy
        if (isset($param_taxonomy) && $param_taxonomy != 'all') {
          $attrs["vocab_taxonomy"] = $param_taxonomy;
        }

        // Language
        if (!empty($param_language)) {
          $attrs["extras_odm_language"] = $param_language;
        }

        // Country
        if (!empty($param_country)) {
          $attrs["extras_odm_spatial_range"] = $param_country;
        }

        //License
        if (!empty($param_license)) {
          $attrs['license_id'] = $param_license;
        }
        
        //organization
        if (!empty($param_organization)){
          $attrs['organization'] = $param_organization;
        }

        //metadata_modified
        if (isset($param_metadata_modified) && $param_metadata_modified !== 'all'){
          $attrs['metadata_modified'] = $param_metadata_modified;
        }

        //metadata_created
        if (isset($param_metadata_created) && $param_metadata_created !== 'all'){
          $attrs['metadata_created'] = $param_metadata_created;
        }
        
        //metadata_modified
        if (isset($param_metadata_modified) && $param_metadata_modified !== 'all'){
          $attrs['metadata_modified'] = $param_metadata_modified;
        }

        $attrs["capacity"] = "public";

        if ($key != 'all'):
          $attrs["dataset_type"] = $key;
        endif;

        $result = WP_Odm_Solr_UNIFIED_Manager()->query($param_query,$attrs,$control_attrs);

        $results[$key] = $result["resultset"];
        $facets[$key] = $result["facets"];
      endforeach; ?>

    <section class="container">

      <?php
        if (!WP_Odm_Solr_UNIFIED_Manager()->ping_server()):  ?>
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

      		<div class="row">
            <div class="four columns">
              <div class="result_links">
              <h4><?php _e('Search Results for','wp-odm_solr'); ?> "<?php _e($param_query,'wp-odm_solr'); ?>"</h4>
              <?php
                foreach ($all_search_types as $key => $value):
                  $count = ($results[$key]) ? $results[$key]->getNumFound() : 0;
                  if ($count > 0): ?>

                  <div class="result_link_list <?php if ($param_type == $key) echo "data-number-results-medium" ?>">
                    <?php
                      $new_url = construct_url($_SERVER['REQUEST_URI'], 'type', $key);
                      $new_url = construct_url($new_url, 'page', 0);
                      ?>
                    <a href="<?php echo $new_url ?>">
                      <i class="<?php echo $value['icon']; ?>"></i>
                      <?php echo __($value['title'],'wp-odm_solr') . " (".$count.")"; ?>
                    </a>
                  </div>

              <?php
                  endif;
                endforeach
              ?>
              </div>
              <div class="data-advanced-filters">
                <form>
                <input type="hidden" name="type" value="<?php echo $param_type;?>"></input>
                <?php include plugin_dir_path(__FILE__). 'partials/filters.php'; ?>
              </div>

              <?php
                if (isset($param_type) && isset($all_search_types[$param_type])):

                  if (isset($supported_type['archive_url'])): ?>
                    <div class="result_links hideOnMobile">
                      <a href="<?php echo $supported_type['archive_url'] ?>"><h4><?php _e("Explore more",'wp-odm_solr') ?> <?php _e($supported_type['title'],'wp-odm_solr') ?></h4></a>
                    </div>
              <?php
                  endif;
                endif;
               ?>
        		</div>
            <!-- ============== Search input ============= -->
      			<div class="twelve columns">

              <div class="row">
                <div class="sixteen columns solr_results search-results">
                  <?php
                  
                    if ($is_search_page):
                      if (have_posts()):
                        $content = apply_filters('the_content', $post->post_content);
                      endif; ?>

                      <div class="search-page-content">
                        <?php echo $content; ?>
                      </div>

                  <?php
                    endif;

                    $query_var_name = $is_search_page ? 'query' : 's'; ?>
                    <input id="search_field" name="<?php echo $query_var_name; ?>" type="text" class="full-width-search-box search_field" value="<?php echo $param_query?>" placeholder="<?php _e("Search datasets, topics, News...",'wp-odm_solr'); ?>" data-solr-host="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host'); ?>" data-solr-scheme="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme'); ?>"  data-solr-path="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path'); ?>" data-solr-core-unified="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_unified'); ?>" data-odm-current-lang="<?php echo odm_language_manager()->get_current_language(); ?>" data-odm-current-country="<?php echo odm_country_manager()->get_current_country_code(); ?>" data-odm-show-regional-contents="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_regional_contents_enabled'); ?>"></input>
                  </form>

                  <?php
                  $content_resultset = array_key_exists($param_type,$results) ? $results[$param_type] : null;
                  $content_resultcount = ($content_resultset) ? $content_resultset->getNumFound() : 0;
                  ?>

                  <p id="spell"><b><?php _e("Did you mean?","wp-odm_solr");?></b></p>

                  <h4>
                  <?php
                    $type_title = $param_type == "all"  ? __("Records","wp-odm_solr") : $all_search_types[$param_type]["title"];
                    echo $content_resultcount . ' '
                              . $type_title
                              . __(' found for','wp-odm_solr') . ' "' . $param_query. '"'; ?>
                  </h4>

                  <?php
                  if (isset($content_resultset) && $content_resultcount > 0):
                    foreach ($content_resultset as $document):
                      if(in_array($document->dataset_type,array("dataset","library_record","laws_record","agreement"))):
                        include plugin_dir_path(__FILE__). 'partials/ckan_result_template.php';
                      elseif ($document->dataset_type == 'map-layer' && $param_type != "all"):
                        include plugin_dir_path(__FILE__). 'partials/wp_map_layer_result_template.php';
                      elseif ($document->dataset_type == 'news-article' && $param_type != "all"):
                        include plugin_dir_path(__FILE__). 'partials/wp_news_article_result_template.php';
                      elseif ($document->dataset_type == 'topic' && $param_type != "all"):
                        include plugin_dir_path(__FILE__). 'partials/wp_topic_result_template.php';
                      elseif ($document->dataset_type == 'profiles' && $param_type != "all"):
                        include plugin_dir_path(__FILE__). 'partials/wp_profiles_result_template.php';
                      elseif ($document->dataset_type == 'story' && $param_type != "all"):
                        include plugin_dir_path(__FILE__). 'partials/wp_story_result_template.php';
                      elseif ($document->dataset_type == 'announcement' && $param_type != "all"):
                        include plugin_dir_path(__FILE__). 'partials/wp_announcement_result_template.php';
                      elseif ($document->dataset_type == 'site-update' && $param_type != "all"):
                        include plugin_dir_path(__FILE__). 'partials/wp_site_update_result_template.php';
                      else:
                        include plugin_dir_path(__FILE__). 'partials/wp_result_template.php';
                      endif;
                    endforeach;
                  endif; ?>
                </div>
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
                endif; ?>

      			</div> <!-- end of twelve columns -->
      		</div> <!-- end of row -->

      <?php
          endif; ?>
      </section> <!-- end of container -->

      <?php
        wp_register_script('search-page-utils-js', plugins_url('wp-odm_solr/js/utils.js'));
        wp_enqueue_script('search-page-utils-js');
        wp_register_script('search-page-js', plugins_url('wp-odm_solr/js/search_page.js'), array('jquery'));
        wp_enqueue_script('search-page-js'); ?>

<?php get_footer(); ?>
