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
        <input type="text" class="search_field" id="search_field" value="<?php echo $_GET["s"] ?> "></input>
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
              $resultset = $result["resultset"]; ?>

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
                        if (strlen($description) == 400):
                          echo "...";
                        endif;
                        ?>
                      </p>
                      <p>
                        <?php
                          if (!empty($document->extras_odm_spatial_range)): ?>
                            <b><?php _e("Country", "wp-odm_solr") ?></b>: <?php _e($document->extras_odm_spatial_range, "wp-odm_solr") ?>
                        <?php
                          endif;
                          if (!empty($document->extras_odm_language)): ?>
                            <b><?php _e("Language", "wp-odm_solr") ?></b>: <?php echo $document->extras_odm_language ?>
                        <?php
                          endif;
                          if (!empty($document->vocab_taxonomy)): ?>
                            <b><?php _e("Topics", "wp-odm_solr") ?></b>: <?php echo implode(", ",$document->vocab_taxonomy)?>
                        <?php
                          endif;
                          if (!empty($document->extras_odm_keywords)): ?>
                            <b><?php _e("Keywords", "wp-odm_solr") ?></b>: <?php echo implode(", ",$document->extras_odm_keywords)?>
                        <?php
                          endif;?>
                      </p>
  									</div>
  								<?php
  							endforeach;
              endif; ?>
              </div>
            <?php
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
                        if (strlen($description) == 400):
                          echo "...";
                        endif;
                        ?>
                      </p>
                      <p>
                        <?php
                          if (!empty($document->country_site)): ?>
                            <b><?php _e("Country", "wp-odm_solr") ?></b>: <?php _e($document->country_site, "wp-odm_solr") ?>
                        <?php
                          endif;
                          if (!empty($document->odm_language)): ?>
                            <b><?php _e("Language", "wp-odm_solr") ?></b>: <?php echo implode(", ",$document->odm_language)?>
                        <?php
                          endif;
                          if (!empty($document->categories)): ?>
                            <b><?php _e("Topics", "wp-odm_solr") ?></b>: <?php echo implode(", ",$document->categories)?>
                        <?php
                          endif;
                          if (!empty($document->tags)): ?>
                            <b><?php _e("Keywords", "wp-odm_solr") ?></b>: <?php echo implode(", ",$document->tags)?>
                        <?php
                          endif;?>
                      </p>
        						</div>
    								<?php
    							endforeach;
                endif; ?>
                </div>
              <?php 
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
    });

	</script>

<?php get_footer(); ?>
