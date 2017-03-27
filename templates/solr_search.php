<?php get_header(); ?>

<?php
  include_once dirname(plugin_dir_path(__FILE__)).'/utils/solr-wp-manager.php';
  include_once dirname(plugin_dir_path(__FILE__)).'/utils/solr-ckan-manager.php';
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
      ?>
     
  		<div class="row">
        <!-- =========== Filters ================ -->
        <div class="four columns data-advanced-filters">

          <h2><i class="fa fa-filter"></i> Filters</h2>
          
          <!-- TAXONOMY FILTER -->
          <div class="single-filter">
            <label for="taxonomy"><?php _e('Topic', 'odm'); ?></label>
            <select name="taxonomy" id="taxonomy">
              <option value="all" selected><?php _e('All','odm') ?></option>
            </select>
          </div>
          <!-- TAXONOMY FILTER -->

          <div class="single-filter">
            <input class="button" type="submit" value="<?php _e('Search Filter', 'odm'); ?>"/>
          </div>

    		</div>
        <!-- =========== End of Filters ================ -->

  			<div class="eleven columns">
          <!-- Full Width Search box --> 
          <div class="search_bar">
            <input type="text" class="search_field" id="search_field" value="<?php echo $_GET["s"]?>" data-solr-host="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host'); ?>" data-solr-scheme="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme'); ?>" data-solr-path="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path'); ?>" data-solr-core-wp="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_wp'); ?>" data-solr-core-ckan="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_ckan'); ?>"></input>
          </div>

          <!-- ================ Accordian ================= --> 
          <div id="accordion" class="solr_results">

  					<?php
  						$supported_search_types = array(
                'ckan' => array(
                  'dataset' => 'Datasets',
                  'library_record' => 'Library publications',
                  'laws_record' => 'Laws',
                  'agreement' => 'Agreements'
                ),
                'wp' => array(
                  'map-layer' => 'Maps',
                  'news-article' => 'News articles',
                  'topic' => 'Topics',
                  'profiles' => 'Profiles',
                  'story' => 'Story',
                  'announcement' => 'Announcements',
                  'site-update' => 'Site updates'
                )
  						);
            ?>
            <?php 
              foreach ($supported_search_types as $type => $search_types): 

                foreach ($search_types as $key => $value): 
                  if ($type == 'ckan') {
                    $attrs = array(
                      "dataset_type" => $key
                    );
                    $result = WP_Odm_Solr_CKAN_Manager()->query($s,$attrs);
                    $resultset = $result["resultset"]; 
                  } else {
                    $attrs = array(
                      "type" => $key
                    );
                    $result = WP_Odm_Solr_WP_Manager()->query($s,$attrs);
                    $resultset = $result["resultset"]; 
                  }

                  $resultcount = ($resultset) ? $resultset->getNumFound() : 0;
                  
                  if (isset($resultset)):
            ?>
                    <h3><?php echo $value . " (" . $resultcount . ")" ?></h3>
                    <div class="single_content_result">
                    <?php
                      if ($resultset->getNumFound() == 0): ?>
                        <div class="solr_no_result">
                          <?php _e("No record found","wp-odm_solr"); ?>
                        </div>
            <?php
                      else:
                      foreach ($resultset as $document):
            ?>
                        <!-- RESULT LIST -->
                        <div id="solr_results">
                          <div class="solr_result single_result_container">
                            <?php if ($type == 'ckan'): ?>

                              <!-- CKAN RESULT -->

                              <!-- Title -->
                              <?php
                                $title = wp_odm_solr_parse_multilingual_ckan_content($document->title_translated,odm_language_manager()->get_current_language(),$document->title);
                                $title = wp_odm_solr_highlight_search_words($s,$title);
                              ?>
                              <h4 class="data_title">
                                <a href="<?php echo wpckan_get_link_to_dataset($document->id) ?>">
                                  <?php echo $title ?>
                                </a>
                              </h4>

                              <!-- Description -->
                              <?php
                                $description = wp_odm_solr_parse_multilingual_ckan_content($document->notes_translated,odm_language_manager()->get_current_language(),$document->notes);
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
                              <div class="data_meta_wrapper">
                                <!-- Country -->
                                <?php if (!empty($document->extras_odm_spatial_range)): ?>
                                  <div class="country_indicator data_meta">
                                    <i class="fa fa-globe"></i>
                                    <span>
                                      <?php
                                        $hihglighted_value = wp_odm_solr_highlight_search_words($s,$document->extras_odm_spatial_range);
                                        _e($hihglighted_value, "wp-odm_solr") ?>
                                    </span>
                                  </div>
                                <?php endif; ?>
                                <!-- Language -->
                                <?php if (!empty($document->extras_odm_language)): ?>
                                  <div class="data_meta">
                                    <i class="fa fa-language"></i>
                                    <span>
                                      <?php
                                        $hihglighted_value = wp_odm_solr_highlight_search_words($s,$document->extras_odm_language);
                                        _e($hihglighted_value, "wp-odm_solr") ?>  
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
                                    <b><?php _e("Keywords", "wp-odm_solr") ?></b>:
                                    <?php
                                      $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->extras_odm_keywords));
                                      _e($hihglighted_value, "wp-odm_solr") ?>
                                  </div>
                                <?php endif; ?>
                              </div>
                            
                            <?php else: ?>

                                <!-- WP RESULT -->
                                <div class="solr_result">
                                  <?php
                                    $title = wp_odm_solr_parse_multilingual_wp_content($document->title,odm_language_manager()->get_current_language(),$document->title);
                                    $title = wp_odm_solr_highlight_search_words($s,$title);
                                   ?>
                                  <h4>
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
                                  <p>
                                    <?php
                                    echo $description;
                                    if (strlen($description) >= 400):
                                      echo "...";
                                    endif;
                                    ?>
                                  </p>
                                  <p>
                                    <?php
                                      if (!empty($document->country_site)): ?>
                                        <b><?php _e("Country", "wp-odm_solr") ?></b>:
                                        <?php
                                          $hihglighted_value = wp_odm_solr_highlight_search_words($s,$document->country_site);
                                          _e($hihglighted_value, "wp-odm_solr") ?>
                                    <?php
                                      endif;
                                      if (!empty($document->odm_language)): ?>
                                        <b><?php _e("Language", "wp-odm_solr") ?></b>:
                                        <?php
                                          $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->odm_language));
                                          _e($hihglighted_value, "wp-odm_solr") ?>
                                    <?php
                                      endif;
                                      if (!empty($document->categories)): ?>
                                        <b><?php _e("Topics", "wp-odm_solr") ?></b>:
                                        <?php
                                          $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->categories));
                                          _e($hihglighted_value, "wp-odm_solr") ?>
                                    <?php
                                      endif;
                                      if (!empty($document->tags)): ?>
                                        <b><?php _e("Keywords", "wp-odm_solr") ?></b>:
                                        <?php
                                          $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->tags));
                                          _e($hihglighted_value, "wp-odm_solr") ?>
                                    <?php
                                      endif;?>
                                  </p>
                                </div>

                            <?php endif; ?>
                          </div>
                        </div>
            <?php
                      endforeach;
                      endif;
            ?>
                    </div>
            <?php 
                  endif; 
          
                endforeach; 
            
              endforeach; 
            ?>

  				</div>
          <!-- End of Accordian -->
  			</div>
  		</div>
    
    <?php 
      endif; ?>
	</section>

	<script>

    jQuery(document).ready(function() {

      jQuery( "#accordion" ).accordion({
        collapsible: true, active: false, header: "h3"
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
