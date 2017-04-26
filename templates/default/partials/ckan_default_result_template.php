<div class="solr_result single_result_container">

  <?php
  $title = wp_odm_solr_parse_multilingual_ckan_content($document->extras_title_translated,odm_language_manager()->get_current_language(),$document->title);
  $title = wp_odm_solr_highlight_search_words($s,$title);
  ?>

  <h4 class="data_title sixteen columns">
    <a target="_blank" href="<?php echo wpckan_get_link_to_dataset($document->id) ?>">
      <?php echo $title ?>
    </a>
  </h4>

</div>
