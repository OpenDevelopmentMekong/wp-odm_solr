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
            <input type="text" class="full-width-search-box" id="search_field" name="query" placeholder="<?php _e('Type your search here', 'odm'); ?>" value="<?php echo $_GET["s"] ?>" />
          </div>

          <!-- ================ Accordian ================= --> 
          <div id="accordion">

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
                    $resultset = WP_Odm_Solr_CKAN_Manager()->query($s,$key);
                  } else {
                    $resultset = WP_Odm_Solr_WP_Manager()->query($s,$key);
                  }

                  $resultcount = ($resultset) ? $resultset->getNumFound() : 0;
                  
                  if ($resultcount > 0): 
            ?>
                    <h3><?php echo $value . " (" . $resultcount . ")" ?></h3>
                    <div class=".single_content_result">
            <?php
                      foreach ($resultset as $document):
            ?>
                        <!-- RESULT LIST -->
                        <div id="solr_results">
                          <div class="solr_result">
                            <?php if ($type == 'ckan'): ?>

                              <!-- CKAN RESULT -->
                              <h4><a href="<?php echo wpckan_get_link_to_dataset($document->id) ?>"><?php echo $document->title ?></a></h4>
                              <p><?php echo strip_tags(substr($document->notes,0,400)) ?></p>
                              <p><?php echo "<b>contry</b>: " . $document->extras_odm_spatial_range ?> <?php echo "<b>language</b>: " . $document->extras_odm_language ?> <?php echo "<b>topics</b>: " . $document->extras_taxonomy ?> <?php echo "<b>keywords</b>: " . $document->extras_odm_keywords ?></p>
                            
                            <?php else: ?>

                                <!-- WP RESULT -->
                                <h4><a href="<?php echo $document->permalink ?>"><?php echo $document->title ?></a></h4>
                                <p><?php echo strip_tags(substr($document->content,0,400)) ?></p>
                                <p><?php if (isset($document->country_site)) echo "<b>country</b>: " . $document->country_site ?> <?php if (is_array($document->odm_language)) echo "<b>language</b>: " . implode(", ",$document->odm_language)  ?> <?php if (is_array($document->categories)) echo "<b>topics</b>: " . implode(", ",$document->categories) ?> <?php if (is_array($document->tags)) echo "<b>keywords</b>: " . implode(", ",$document->tags) ?></p>

                            <?php endif; ?>
                          </div>
                        </div>
            <?php
                      endforeach;
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
				collapsible: true, active: false
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
