<?php get_header(); ?>

<?php
  include_once dirname(plugin_dir_path(__FILE__)).'/utils/solr-wp-manager.php';
  include_once dirname(plugin_dir_path(__FILE__)).'/utils/solr-ckan-manager.php';
 ?>
 
 <?php

  $param_query = !empty($_GET['s']) ? $_GET['s'] : null;
  $param_license = !empty($_GET['license']) ? $_GET['license'] : null;
  $param_taxonomy = isset($_GET['taxonomy']) ? $_GET['taxonomy'] : null;
  $param_language = isset($_GET['language']) ? $_GET['language'] : null;
  $param_page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
  $param_country = odm_country_manager()->get_current_country() == 'mekong' && isset($_GET['country']) ? $_GET['country'] : odm_country_manager()->get_current_country();
	$param_sorting = isset($_GET['sorting']) ? $_GET['sorting'] : 'score';

  //================ Filter Values ===================== //

  $taxonomy_list = odm_taxonomy_manager()->get_taxonomy_list();
  $countries = odm_country_manager()->get_country_codes();
  $languages = odm_language_manager()->get_supported_languages();
  $license_list = wpckan_get_license_list();

  //================ Build query attributes ===================== //
  
  $attrs = [];
  $control_attrs = array(
    "sorting" => $param_sorting
  );
  
  //Taxonomy
  if (!empty($param_taxonomy) && $param_taxonomy != 'all') {
    $attrs["extras_taxonomy"] = $param_taxonomy;
  }

  // Language
  if (!empty($param_language) && $param_language != 'all') {
    $attrs["extras_odm_language"] = $param_language;
  }

  // Country
  if (!empty($param_country) && $param_country != 'mekong' && $param_country != 'all') {
    $attrs["extras_odm_spatial_range"] = $countries[$param_country]['iso2'];
  }

  //License
  if (!empty($param_license) && $param_license != 'all') {
    $attrs['license_id'] = $param_license;
  }
  
?>

<?php

  //================ Search types ===================== //
  
  $supported_search_types = array(
    'ckan' => array(
      'dataset' => array(
        'title' => 'Datasets',
        'icon' => 'fa fa-database'
      ),
      'library_record' => array(
        'title' =>'Library publications',
        'icon' => 'fa fa-book'
      ),
      'laws_record' => array(
        'title' =>'Laws',
        'icon' => 'fa fa-gavel'
      ),
      'agreement' => array(
        'title' =>'Agreements',
        'icon' => 'fa fa-handshake-o'
      )
    ),
    'wp' => array(
      'map-layer' => array(
        'title' => 'Maps',
        'icon' => 'fa fa-map-marker'
      ), 
      'news-article' => array(
        'title' => 'News articles',
        'icon' => 'fa fa-newspaper-o'
      ), 
      'topic' => array(
        'title' => 'Topics',
        'icon' => 'fa fa-list'
      ), 
      'profiles' => array(
        'title' => 'Profiles',
        'icon' => 'fa fa-briefcase'
      ), 
      'story' => array(
        'title' => 'Story',
        'icon' => 'fa fa-lightbulb-o'
      ), 
      'announcement' => array(
        'title' => 'Announcements',
        'icon' => 'fa fa-bullhorn'
      ), 
      'site-update' => array(
        'title' => 'Site updates',
        'icon' => 'fa fa-flag'
      ),
    )
  ); ?>
  
  <?php 
  
    //================ Run queries and gather both results and facets ===================== //
    
    $results = [];
    $facets = [];
    foreach ($supported_search_types as $type => $search_types):
      foreach ($search_types as $key => $value): 
        $result = null;
        if ($type == 'ckan'):
          $attrs["dataset_type"] = $key;
          $attrs["capacity"] = "public";
          $result = WP_Odm_Solr_CKAN_Manager()->query($s,$attrs);        
        else:
          $attrs["type"] = $key;
          $result = WP_Odm_Solr_WP_Manager()->query($s,$attrs);      
        endif;      
      
        $results[$key] = $result["resultset"];            
        foreach ($result["facets"] as $facet_key => $facet):         
          if (!isset($facets[$facet_key])):
            $facets[$facet_key] = [];
          endif;
          foreach ($facet as $facet_value => $count):
            if (!isset($facets[$facet_key][$facet_value])):
              $facets[$facet_key][$facet_value] = 0;
            endif;
            $facets[$facet_key][$facet_value] += $count;
          endforeach;
        endforeach;
      endforeach;
    endforeach;
  ?>

<section class="container">

  <?php
    if (!WP_Odm_Solr_WP_Manager()->ping_server() || !WP_Odm_Solr_CKAN_Manager()->ping_server()):  ?>
    <div class="row">
      <div class="sixteen columns">
          <p class="error"><?php _e("wp-odm_solr plugin is not properly configured. Please contact the system's administrator","wp-odm_solr"); ?></p>
      </div>
    </div>
    <?php
    else: 
      
      //================ show filters ===================== // ?>

		<div class="row">
      <div class="four columns data-advanced-filters">
        <form>
        <h2><i class="fa fa-filter"></i> Filters</h2>
        
        <!-- TAXONOMY FILTER -->
        <div class="single-filter">
          <label for="taxonomy"><?php _e('Topic', 'odm'); ?></label>
          <select id="taxonomy" name="taxonomy" data-placeholder="<?php _e('Select term', 'odm'); ?>">
            <option value="all" selected><?php _e('All','odm') ?></option>
            <?php
              foreach($taxonomy_list as $value):
                if (array_key_exists("vocab_taxonomy",$facets)):
                  $taxonomy_facets = $facets["vocab_taxonomy"];
                  if (array_key_exists($value,$taxonomy_facets)):
                    $available_records = $taxonomy_facets[$value];
                    if ($available_records > 0): ?>
                    <option value="<?php echo $value; ?>" <?php if($value == $param_taxonomy) echo 'selected'; ?>><?php echo $value . " (" . $available_records . ")"; ?></option>
            <?php
                    endif;
                  endif;
                endif;
              endforeach; ?>
          </select>
        </div>
        <!-- END OF TAXONOMY FILTER -->

        <!-- COUNTRY FILTER -->
        <?php if (odm_country_manager()->get_current_country() == 'mekong'): ?>
        <div class="single-filter">
          <label for="country"><?php _e('Country', 'odm'); ?></label>
          <select id="country" name="country" data-placeholder="<?php _e('Select country', 'odm'); ?>">
            <option value="all" selected><?php _e('All','odm') ?></option>
            <?php
              foreach($countries as $key => $value):
                if ($key != 'mekong'):
                  if (array_key_exists("extras_odm_spatial_range",$facets)):
                    $spatial_range_facets = $facets["extras_odm_spatial_range"];
                    $country_codes = odm_country_manager()->get_country_codes();
                    $country_code = $country_codes[$key]["iso2"];
                    if (array_key_exists($country_code,$spatial_range_facets)):
                      $available_records = $spatial_range_facets[$country_code];
                      if ($available_records > 0): ?>
                  <option value="<?php echo $key; ?>" <?php if($key == $param_country) echo 'selected'; ?> <?php if (odm_country_manager()->get_current_country() != 'mekong' && $key != odm_country_manager()->get_current_country()) echo 'disabled'; ?>><?php echo odm_country_manager()->get_country_name($key) . " (" . $available_records . ")"; ?></option>
              <?php
                      endif;
                    endif;
                  endif;
                endif; ?>
                <?php
              endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <!-- END OF COUNTRY FILTER  -->

        <!-- LANGUAGE FILTER -->
        <div class="single-filter">
          <label for="language"><?php _e('Language', 'odm'); ?></label>
          <select id="language" name="language" data-placeholder="<?php _e('Select language', 'odm'); ?>">
            <option value="all"  selected><?php _e('All','odm') ?></option>
            <?php
              foreach($languages as $key => $value):
                if (array_key_exists("extras_odm_language",$facets)):
                  $language_facets = $facets["extras_odm_language"];
                  if (array_key_exists($key,$language_facets)):
                    $available_records = $language_facets[$key];
                    if ($available_records > 0): ?>
            <option value="<?php echo $key; ?>" <?php if($key == $param_language) echo 'selected'; ?>><?php echo $value . " (" . $available_records . ")" ?></option>
            <?php
                    endif;
                  endif;
                endif;
              endforeach; ?>
          </select>
        </div>
        <!-- END OF LANGUAGE FILTER -->

        <!-- LICENSE FILTER -->
        <div class="single-filter">
          <label for="license"><?php _e('License', 'odm'); ?></label>
          <select id="license" name="license" data-placeholder="<?php _e('Select license', 'odm'); ?>">
            <option value="all" selected><?php _e('All','odm') ?></option>
            <?php
              foreach($license_list as $license):
                if (array_key_exists("license_id",$facets)):
                  $license_facets = $facets["license_id"];
                  if (array_key_exists($license->id,$license_facets)):
                    $available_records = $license_facets[$license->id];
                    if ($available_records > 0): ?>
                <option value="<?php echo $license->id; ?>" <?php if($license->id == $param_license) echo 'selected'; ?>><?php echo $license->title . " (" . $available_records . ")" ?></option>
            <?php
                    endif;
                  endif;
                endif;
              endforeach; ?>
          </select>
        </div>
				<!-- END OF LICENSE FILTER -->

				<!-- SORTING FUNCTION -->
				<h3><i class="fa fa-sort"></i> Sorting</h3>
				<div class="single-filter">
          <label for="sorting"><?php _e('Sort by', 'odm'); ?></label>
          <select id="sorting" name="sorting" data-placeholder="<?php _e('Sort by', 'odm'); ?>">
            <option <?php if($param_sorting == "score") echo 'selected'; ?> value="score"><?php _e('Relevance','odm') ?></option>
          	<option <?php if($param_sorting == "metadata_modified") echo 'selected'; ?> value="metadata_modified"><?php _e('Date modified','odm') ?></option>
          </select>
        </div>
				<!-- END OF LICENSE FILTER -->

        <div class="single-filter">
          <input class="button" type="submit" value="<?php _e('Search Filter', 'odm'); ?>"/>          
        </div>

  		</div>

			<div class="eleven columns">
        <input id="search_field" name="s" type="text" class="search_field" id="search_field" value="<?php echo $_GET["s"]?>" data-solr-host="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host'); ?>" data-solr-scheme="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme'); ?>" data-solr-path="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path'); ?>" data-solr-core-wp="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_wp'); ?>" data-solr-core-ckan="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_ckan'); ?>"></input>
        </form>
        <div id="accordion" class="solr_results">				
          
    <?php
    
      //================ show results ===================== // 
      
			foreach ($supported_search_types as $type => $search_types):
        foreach ($search_types as $key => $value): 
          
          $resultset = $results[$key];
          $resultcount = ($resultset) ? $resultset->getNumFound() : 0;
                
          if (isset($resultset) && $resultcount > 0): ?>

						<h3><i class="<?php echo $value['icon'] ?>"></i> <?php echo $value['title'] . " (" . $resultcount . ")" ?></h3>
            <div class="single_content_result">
						<?php
							foreach ($resultset as $document):
								?>              
                  <div class="solr_result single_result_container row">
                    <?php 
                    if ($type == 'ckan'): 
                      $title = wp_odm_solr_parse_multilingual_ckan_content($document->title_translated,odm_language_manager()->get_current_language(),$document->title);
                      $title = wp_odm_solr_highlight_search_words($s,$title);
                     ?>
  										<h4 class="data_title twelve columns">
                        <a href="<?php echo wpckan_get_link_to_dataset($document->id) ?>">
                          <?php echo $title ?>
                        </a>
                      </h4>
                      <div class="data_format four columns">
                        <?php $resource_formats = array_unique($document->res_format); ?>
                        <?php foreach ($resource_formats as $format): ?>
                          <span class="meta-label <?php echo strtolower($format); ?>"><?php echo strtolower($format); ?></span>
                        <?php endforeach ?>
                      </div>
                      <?php
                        $description = wp_odm_solr_parse_multilingual_ckan_content($document->notes_translated,odm_language_manager()->get_current_language(),$document->notes);
                        $description = strip_tags($description);
                        $description = substr($description,0,400);
                        $description = wp_odm_solr_highlight_search_words($s,$description);
                       ?>
                      <p class="data_description sixteen columns">
                      <?php
                        echo $description;
                        if (strlen($description) >= 400):
                          echo "...";
                        endif;
                        ?>
                      </p>
                        <div class="data_meta_wrapper sixteen columns">
                          <!-- Country -->
                        <?php if (!empty($document->extras_odm_spatial_range)): ?>
                          <div class="country_indicator data_meta">
                            <i class="fa fa-globe"></i>
                            <span>
                              <?php
                                $odm_country_arr = json_decode($document->extras_odm_spatial_range);
                                $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ", $odm_country_arr));
                                _e($hihglighted_value, "wp-odm_solr") ?>
                            </span>
                          </div>
                        <?php endif; ?>
                        <!-- Language -->
                        <?php if (!empty($document->extras_odm_language)): ?>
                          <div class="data_languages data_meta">
                            <?php $odm_lang_arr = json_decode($document->extras_odm_language); ?>
                            <span>
                              <?php foreach ($odm_lang_arr as $lang): ?>
                                <img class="lang_flag" alt="<?php echo $lang ?>" src="<?php echo odm_language_manager()->get_path_to_flag_image($lang); ?>"></img>
                              <?php endforeach; ?>
                            </span>
                          </div>
                        <?php endif; ?>
                        <!-- Topics -->
                        <?php if (!empty($document->vocab_taxonomy)): ?>
                          <div class="data_meta">
                            <i class="fa fa-tags"></i>
                            <span>
                              <?php
                                $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->vocab_taxonomy));
                                _e($hihglighted_value, "wp-odm_solr") ?>
                            </span>
                          </div>
                        <?php endif; ?>
                        <!-- Keywords -->
                        <?php if (!empty($document->extras_odm_keywords)): ?>
                          <div class="data_meta">
                            <i class="fa fa-tags"></i>
                            <?php
                              $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->extras_odm_keywords));
                              _e($hihglighted_value, "wp-odm_solr") ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>  
                  <?php 
                    else: 
                      ?>
                      <div class="solr_result single_result_container row">
                        <?php
                          $title = wp_odm_solr_parse_multilingual_wp_content($document->title,odm_language_manager()->get_current_language(),$document->title);
                          $title = wp_odm_solr_highlight_search_words($s,$title);
                         ?>
                        <h4 class="data_title">
                          <a href="<?php echo $document->permalink ?>">
                            <?php echo $title ?>
                          </a>
                        </h4>
                        <?php
                          $description = wp_odm_solr_parse_multilingual_wp_content($document->content,odm_language_manager()->get_current_language(),$document->content);
                          $description = strip_shortcodes($description);
                          $description = strip_tags($description);
                          $description = substr($description,0,400);
                          $description = wp_odm_solr_highlight_search_words($s,$description);
                         ?>
                        <p class="data_description">
                          <?php
                          echo $description;
                          if (strlen($description) >= 400):
                            echo "...";
                          endif;
                          ?>
                        </p>
                        <div class="data_meta">
                          <?php
                            if (!empty($document->country_site)): ?>
                              <i class="fa fa-globe"></i>
                              <span>
                                <?php
                                  $hihglighted_value = wp_odm_solr_highlight_search_words($s,$document->country_site);
                                  _e($hihglighted_value, "wp-odm_solr") ?>  
                              </span>
                          <?php
                            endif;
                            if (!empty($document->odm_language)): ?>
                              <i class="fa fa-language"></i>
                              <span>
                                <?php
                                  $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->odm_language));
                                  _e($hihglighted_value, "wp-odm_solr") ?>  
                              </span>
                          <?php
                            endif;
                            if (!empty($document->categories)): ?>
                              <i class="fa fa-tags"></i>
                              <span>
                                <?php
                                  $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->categories));
                                  _e($hihglighted_value, "wp-odm_solr") ?>  
                              </span>
                          <?php
                            endif;
                            if (!empty($document->tags)): ?>
                              <i class="fa fa-tags"></i>
                              <span>
                                <?php
                                  $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->tags));
                                  _e($hihglighted_value, "wp-odm_solr") ?>  
                              </span>
                          <?php
                            endif;?>
                        </div>
                      </div>
                    </div>
                <?php
                    endif;
					    endforeach; ?>
                
              <div class="view_all_link">
                <a href="#">View all <?php echo $resultcount . " " . strtolower($value['title']) . " results" ?></a>
              </div>
            </div>    
          <?php
          endif;
  		endforeach;
    endforeach; ?>
				</div>
			</div>
		</div>

    <?php
      endif; ?>
  </section>

	<script>

    jQuery(document).ready(function() {

      jQuery( "#accordion" ).accordion({
        collapsible: true, 
        active: false, 
        header: "h3",
        heightStyle: "content"
      });

      jQuery('#search_field').keydown(function(event) {
        if (event.keyCode == 13) {
            window.location.href = "/?s=" + jQuery('#search_field').val();
            return false;
         }
      });

      jQuery('#search_field').autocomplete({
        source: function( request, response ) {
          var host = jQuery('#search_field').data("solr-host");
          var scheme = jQuery('#search_field').data("solr-scheme");
          var path = jQuery('#search_field').data("solr-path");
          var core_wp = jQuery('#search_field').data("solr-core-wp");
          var core_ckan = jQuery('#search_field').data("solr-core-ckan");
          var url = scheme + "://" + host  + path + core_wp + "/suggest";

          jQuery.ajax({
            url: url,
            data: {'wt':'json', 'q':request.term, 'json.wrf': 'callback'},
            dataType: "jsonp",
            jsonpCallback: 'callback',
            contentType: "application/json",
            success: function( data ) {
              var options = [];
              if (data){
                if(data.spellcheck){
                  var spellcheck = data.spellcheck;
                  if (spellcheck.suggestions){
                    var suggestions = spellcheck.suggestions;
                    if (suggestions[1]){
                      var suggestionObject = suggestions[1];
                      options = suggestionObject.suggestion;
                    }
                  }
                }
              }
              response( options );
            }
          });
        },
        minLength: 2,
        select: function( event, ui ) {
          var terms = this.value.split(" ");
          terms.pop();
          terms.push( ui.item.value );
          this.value = terms.join( " " );
          return false;
        }
      });
    });

	</script>

<?php get_footer(); ?>
