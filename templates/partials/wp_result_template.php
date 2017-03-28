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

