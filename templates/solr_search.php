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
    else: ?>

		<div class="row">
      <div class="four columns">

        <h2><i class="fa fa-filter"></i> Filters</h2>

  		</div>

			<div class="twelve columns">
        <input type="text" class="search_field" id="search_field" value="<?php echo $_GET["s"]?>" data-solr-host="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host'); ?>" data-solr-scheme="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme'); ?>" data-solr-path="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path'); ?>" data-solr-core-wp="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_wp'); ?>" data-solr-core-ckan="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_ckan'); ?>"></input>
        <div id="accordion" class="solr_results">
					<?php
						$supported_ckan_types = array(
							'dataset' => 'Datasets',
							'library_record' => 'Library publications',
							'laws_record' => 'Laws',
							'agreement' => 'Agreements'
						);

						foreach( $supported_ckan_types as $key => $value):
							$result = WP_Odm_Solr_CKAN_Manager()->query($s,$key);
              $resultset = $result["resultset"]; 
              if (isset($resultset)):
            ?>

						<h3><?php echo $value . " (" . $resultset->getNumFound() . ")" ?></h3>
            <div>
						<?php
              if ($resultset->getNumFound() == 0): ?>
                <div class="solr_no_result">
                  <?php _e("No record found","wp-odm_solr"); ?>
                </div>
              <?php
              else:
  							foreach ($resultset as $document):
  								?>
  									<div class="solr_result">
                      <?php
                        $title = wp_odm_solr_parse_multilingual_ckan_content($document->title_translated,odm_language_manager()->get_current_language(),$document->title);
                        $title = wp_odm_solr_highlight_search_words($s,$title);
                       ?>
  										<h4>
                        <a href="<?php echo wpckan_get_link_to_dataset($document->id) ?>">
                          <?php echo $title ?>
                        </a>
                      </h4>
                      <?php
                        $description = wp_odm_solr_parse_multilingual_ckan_content($document->notes_translated,odm_language_manager()->get_current_language(),$document->notes);
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
                          if (!empty($document->extras_odm_spatial_range)): ?>
                            <b><?php _e("Country", "wp-odm_solr") ?></b>:
                            <?php
                              $hihglighted_value = wp_odm_solr_highlight_search_words($s,$document->extras_odm_spatial_range);
                              _e($hihglighted_value, "wp-odm_solr") ?>
                        <?php
                          endif;
                          if (!empty($document->extras_odm_language)): ?>
                            <b><?php _e("Language", "wp-odm_solr") ?></b>:
                            <?php
                              $hihglighted_value = wp_odm_solr_highlight_search_words($s,$document->extras_odm_language);
                              _e($hihglighted_value, "wp-odm_solr") ?>
                        <?php
                          endif;
                          if (!empty($document->vocab_taxonomy)): ?>
                            <b><?php _e("Topics", "wp-odm_solr") ?></b>:
                            <?php
                              $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->vocab_taxonomy));
                              _e($hihglighted_value, "wp-odm_solr") ?>
                        <?php
                          endif;
                          if (!empty($document->extras_odm_keywords)): ?>
                            <b><?php _e("Keywords", "wp-odm_solr") ?></b>:
                            <?php
                              $hihglighted_value = wp_odm_solr_highlight_search_words($s,implode(", ",$document->extras_odm_keywords));
                              _e($hihglighted_value, "wp-odm_solr") ?>
                        <?php
                          endif;?>
                      </p>
  									</div>
  								<?php
  							endforeach;
              endif; ?>
              </div>
            <?php
              endif;
 						endforeach;
 			 		?>

					<?php

  					$supported_wp_types = array(
  						'map-layer' => 'Maps',
  						'news-article' => 'News articles',
  						'topic' => 'Topics',
  						'profiles' => 'Profiles',
  						'story' => 'Story',
  						'announcement' => 'Announcements',
  						'site-update' => 'Site updates'
  					);

  					foreach( $supported_wp_types as $key => $value):
  						$result = WP_Odm_Solr_WP_Manager()->query($s,$key);
              $resultset = $result["resultset"];
              if (isset($resultset)):
  					?>

  						<h3><?php echo $value . " (" . $resultset->getNumFound() . ")" ?></h3>
              <div>
  						<?php
                if ($resultset->getNumFound() == 0): ?>
                  <div class="solr_no_result">
                    <?php _e("No record found","wp-odm_solr"); ?>
                  </div>
                <?php
                else:
    							foreach ($resultset as $document):
    								?>
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
    								<?php
    							endforeach;
                endif; ?>
                </div>
              <?php
              endif;
  					endforeach;
					?>
				</div>
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
