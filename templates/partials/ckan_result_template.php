<?php
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
