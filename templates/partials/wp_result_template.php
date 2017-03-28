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
<div class="data_meta_wrapper sixteen columns">
  <!-- Language -->
  <?php if (!empty($document->odm_language)): ?>
    <div class="data_languages data_meta">
      <span>
        <?php
        foreach ($document->odm_language as $lang):
          $path_to_flag = odm_language_manager()->get_path_to_flag_image($lang);
          if (!empty($path_to_flag)): ?>
          <img class="lang_flag" alt="<?php echo $lang ?>" src="<?php echo $path_to_flag; ?>"></img>
        <?php
          endif;
        endforeach; ?>
      </span>
    </div>
  <?php endif; ?>
  <?php
    if (!empty($document->country_site)): ?>
      <i class="fa fa-globe"></i>
      <span>
        <?php _e($document->country_site, "wp-odm_solr") ?>
      </span>
    <?php
      endif;
    if (!empty($document->categories)): ?>
      <i class="fa fa-tags"></i>
      <span>
        <?php echo implode(", ",$document->categories); ?>
      </span>
  <?php
    endif;?>
</div>
