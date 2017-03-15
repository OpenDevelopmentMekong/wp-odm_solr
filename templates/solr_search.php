<?php get_header(); ?>

<?php
  include_once dirname(plugin_dir_path(__FILE__)).'/utils/solr-wp-manager.php';
  include_once dirname(plugin_dir_path(__FILE__)).'/utils/solr-ckan-manager.php';
 ?>

<section class="container">
		<div class="row">
      <div class="four columns">

        <h2><i class="fa fa-filter"></i> Filters</h2>


  		</div>

			<div class="twelve columns">
        <input type="text" class="search_field" id="search_field" value="<?php echo $_GET["s"] ?> "></input>
        <div id="accordion">

					<?php
						$supported_ckan_types = array(
							'dataset' => 'Datasets',
							'library_record' => 'Library publications',
							'laws_record' => 'Laws',
							'agreement' => 'Agreements'
						);

						foreach( $supported_ckan_types as $key => $value):
							$resultset = WP_Odm_Solr_CKAN_Manager()->query($s,$key);
					?>

						<h3><?php echo $value . " (" . $resultset->getNumFound() . ")" ?></h3>
						<div>
						<?php
							foreach ($resultset as $document):

								?>

								<div id="solr_results">
									<div class="solr_result">
										<h4><a href="<?php echo wpckan_get_link_to_dataset($document->id) ?>"><?php echo wp_odm_solr_highlight_search_words($s,$document->title) ?></a></h4>
										<p><?php echo strip_tags(substr(wp_odm_solr_highlight_search_words($s,$document->notes),0,400)) ?></p>
										<p><?php echo "<b>contry</b>: " . $document->extras_odm_spatial_range ?> <?php echo "<b>language</b>: " . $document->extras_odm_language ?> <?php echo "<b>topics</b>: " . $document->extras_taxonomy ?></p>
										<p></p>
										<p></p>
									</div>
								</div>

								<?php
							endforeach;
						 ?>
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
						$resultset = WP_Odm_Solr_WP_Manager()->query($s,$key);
					?>

						<h3><?php echo $value . " (" . $resultset->getNumFound() . ")" ?></h3>
						<div>
						<?php
							foreach ($resultset as $document):

								?>

								<div id="solr_results">
									<div class="solr_result">
										<h4><a href="<?php echo $document->permalink ?>"><?php echo wp_odm_solr_highlight_search_words($s,$document->title) ?></a></h4>
										<p><?php echo strip_tags(substr(wp_odm_solr_highlight_search_words($s,$document->content),0,400)) ?></p>
										<p><?php if (isset($document->country_site)) echo "<b>country</b>: " . $document->country_site ?> <?php if (is_array($document->odm_language)) echo "<b>language</b>: " . implode(", ",$document->odm_language)  ?> <?php if (is_array($document->categories)) echo "<b>topics</b>: " . implode(", ",$document->categories) ?></p>
									</div>
								</div>

								<?php
							endforeach;
						 ?>
					 </div>

					<?php
					endforeach;
					?>
				</div>
			</div>
		</div>
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
