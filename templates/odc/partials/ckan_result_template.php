<div class="solr_result_two_cols single_result_container eight columns">
  <?php
  $title = wp_odm_solr_parse_multilingual_ckan_content($document->extras_title_translated,odm_language_manager()->get_current_language(),$document->title);
  $title = wp_odm_solr_highlight_search_words($s,$title);
  ?>
  <h4 class="data_title ten columns">
    <a target="_blank" href="<?php echo wpckan_get_link_to_dataset($document->id) ?>">
      <?php echo $title ?>
    </a>
  </h4>

  <div class="data_format six columns">
    <?php $resource_formats = array_unique($document->res_format); ?>
    <?php foreach ($resource_formats as $format): ?>
      <span class="meta-label <?php echo strtolower($format); ?>"><?php echo strtolower($format); ?></span>
    <?php endforeach ?>
  </div>
  <?php
    $description = wp_odm_solr_parse_multilingual_ckan_content($document->extras_notes_translated,odm_language_manager()->get_current_language(),$document->notes);
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
    <!-- Language -->
    <?php if (!empty($document->extras_odm_language)): ?>
      <div class="data_languages data_meta">
        <?php $odm_lang_arr = json_decode($document->extras_odm_language,true); ?>
        <span>
          <?php
          if (is_array($odm_lang_arr) && !empty($odm_lang_arr)):
            foreach ($odm_lang_arr as $lang):
              $path_to_flag = odm_language_manager()->get_path_to_flag_image($lang);
              if (!empty($path_to_flag)): ?>
              <img class="lang_flag" alt="<?php echo $lang ?>" src="<?php echo $path_to_flag; ?>"></img>
            <?php
              endif;
            endforeach;
          endif; ?>
        </span>
      </div>
    <?php endif; ?>
    <!-- Country -->
    <?php if (odm_country_manager()->get_current_country() == "mekong" && !empty($document->extras_odm_spatial_range)): ?>
      <div class="country_indicator data_meta">

        <span>
          <?php
            $odm_country_arr = json_decode($document->extras_odm_spatial_range,true);
            if (is_array($odm_country_arr) && !empty($odm_country_arr)): ?>
              <i class="fa fa-globe"></i>
              <?php
              foreach ($odm_country_arr as $country_code):
                $country_name = odm_country_manager()->get_country_name_by_country_code($country_code);
                if (!empty($country_name)):
                  _e($country_name, 'wp-odm_solr');
                  if ($country_code !== end($odm_country_arr)):
                    echo ', ';
                  endif;
                endif;
              endforeach;
            endif; ?>
        </span>
      </div>
    <?php endif; ?>
    <!-- Topics -->
    <?php if (!empty($document->vocab_taxonomy)): ?>
      <div class="data_meta">
        <i class="fa fa-folder-o"></i>
        <span>
          <?php
            $topics = (array) $document->vocab_taxonomy;
            foreach ($topics as $topic):
              _e($topic, 'wp-odm_solr') ;
              if ($topic !== end($topics)):
                echo ", ";
              endif;
            endforeach;?>
        </span>
      </div>
    <?php endif; ?>
    <!-- Keywords -->
    <?php if (!empty($document->extras_odm_keywords)): ?>
      <div class="data_meta">
        <i class="fa fa-tags"></i>
        <?php
          $keywords = (array) $document->extras_odm_keywords;
          foreach ($keywords as $keyword):
            _e($keyword, 'wp-odm_solr') ;
            if ($keyword !== end($keywords)):
              echo ", ";
            endif;
          endforeach;?>
      </div>
    <?php endif; ?>
  </div>
</div>
