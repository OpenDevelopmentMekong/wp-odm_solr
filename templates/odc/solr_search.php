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
      $param_taxonomy = isset($_GET['taxonomy']) ? $_GET['taxonomy'] : 'all';
      $param_language = isset($_GET['language']) ? $_GET['language'] : array();
      $param_organization = isset($_GET['organization']) ? $_GET['organization'] : array();
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
      $organization_list = wpckan_get_organization_list(wpckan_get_ckan_domain());
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
        'all' => array(
          'title' => 'All',
          'icon' => 'fa fa-asterisk',
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

        $attrs["capacity"] = "public";

        if ($key != 'all'):
          $attrs["dataset_type"] = $key;
        endif;

        $result = WP_Odm_Solr_WP_Manager()->query($param_query,$attrs,$control_attrs);

        $results[$key] = $result["resultset"];
        $facets[$key] = $result["facets"];
      endforeach; ?>

    <section class="container">
      <?php
        if (!WP_Odm_Solr_WP_Manager()->ping_server()):  ?>
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

          <form>
            <div class="advanced-nav-filters ">
              <div class="row panel">
                <input type="hidden" name="type" value="<?php echo $param_type;?>"></input>
                <?php
                  if (odm_country_manager()->get_current_country() === "mekong"):
                    include plugin_dir_path(__FILE__). 'partials/filters_regional.php';
                  else:
                    include plugin_dir_path(__FILE__). 'partials/filters.php';
                  endif;
                  ?>
              </div>

              <div class="row">
                <div class="sixteen columns">
                  <div class="content-type-tabs-odc hideOnMobile">
                    <?php include plugin_dir_path(__FILE__). 'partials/content-types.php'; ?>
                  </div>
                  <div class="content-type-tabs-odc-mobile hideOnDesktopAndTablet">
                    <?php include plugin_dir_path(__FILE__). 'partials/content-types-mobile.php'; ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="eleven columns">
                <h4>
                <?php
                  $type_title = $param_type == "all"  ? __("Records","wp-odm_solr") : $all_search_types[$param_type]["title"];
                  echo $content_resultcount . ' '
                            . $type_title
                            . __(' found for','wp-odm_solr') . ' "' . $param_query. '"'; ?>
                </h4>
              </div>

              <div class="five columns">
                <div class="align-right">
                  <label class="left-label" for="sorting"><?php _e('Sort by', 'wp-odm_solr'); ?> </label>
                  <select id="sorting" name="sorting" data-placeholder="<?php _e('Sort by', 'wp-odm_solr'); ?>" onchange="this.form.submit()">
                    <option <?php if($param_sorting == "score") echo 'selected'; ?> value="score"><?php _e('Relevance','wp-odm_solr') ?></option>
                    <option <?php if($param_sorting == "metadata_created") echo 'selected'; ?> value="metadata_created"><?php _e('Creation date','wp-odm_solr') ?></option>
                    <option <?php if($param_sorting == "metadata_modified") echo 'selected'; ?> value="metadata_modified"><?php _e('Modification date','wp-odm_solr') ?></option>
                  </select>
                </div>
              </div>
            </div>

          </form>

          <div class="row solr_results search-results">
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

      <?php
        wp_register_script('search-page-utils-js', plugins_url('wp-odm_solr/js/utils.js'));
        wp_enqueue_script('search-page-utils-js');
        wp_register_script('search-page-js', plugins_url('wp-odm_solr/js/search_page.js'), array('jquery'));
        wp_enqueue_script('search-page-js'); ?>

<?php get_footer(); ?>
