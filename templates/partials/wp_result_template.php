<div class="solr_result single_result_container row">
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
    <!-- Country -->
    <?php if (!empty($document->odm_spatial_range)): ?>
      <div class="country_indicator data_meta">
        <i class="fa fa-globe"></i>
        <span>
          <?php
            foreach ($document->odm_spatial_range as $country_code):
              $country_name = odm_country_manager()->get_country_name_by_country_code($country_code);
              if (!empty($country_name)):
                _e($country_name, "wp-odm_solr");
                if ($country_code !== end($document->odm_spatial_range)):
                  echo ', ';
                endif;
              endif;
            endforeach; ?>
        </span>
      </div> 
    <?php
      endif;
      if (!empty($document->categories)): ?>
        <i class="fa fa-tags"></i>
        <span>
          <?php
            $categories = (array) $document->categories;
            foreach ($categories as $category):
              _e($category, "wp-odm_solr") ;
              if ($category !== end($categories)):
                echo ", ";
              endif;
            endforeach;?>
        </span>
    <?php
      endif;
      if (!empty($document->tags)): ?>
        <i class="fa fa-tags"></i>
        <span>
          <?php
            $tags = (array) $document->tags;
            foreach ($tags as $tag):
              _e($tag, "wp-odm_solr") ;
              if ($tag !== end($tags)):
                echo ", ";
              endif;
            endforeach;?>          
        </span>
    <?php
      endif;?>
  </div>
</div>
