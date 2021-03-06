<div class="post-list-item solr_result_two_cols single_result_container sixteen columns">
  <?php
  $title = wp_odm_solr_parse_multilingual_ckan_content($document->extras_title_translated,odm_language_manager()->get_current_language(),$document->title);
  $title = wp_odm_solr_highlight_search_words($s,$title);
  $link_to_dataset = wpckan_get_link_to_dataset($document->name,$_SERVER['QUERY_STRING']);
  ?>
  <h4 class="data_title ten columns">
    <a target="_blank" href="<?php echo $link_to_dataset ?>">
			<i class="<?php echo get_post_type_icon_class($document->dataset_type); ?>"></i>
      <?php echo $title ?>
    </a>
  </h4>

  <div class="data_format six columns">
    <?php $resource_formats = array_unique($document->res_format); ?>
    <?php foreach ($resource_formats as $format): ?>
      <span class="meta-label <?php echo strtolower($format); ?>"><a href="<?php echo $link_to_dataset ?>"><?php echo strtolower($format); ?></a></span>
    <?php endforeach ?>
  </div>

  <div class="post-meta sixteen columns">
    <ul>
    <!-- Language -->
    <?php if (!empty($document->extras_odm_language)): ?>
      <li class="data_languages data_meta">
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
      </li>
    <?php endif; ?>
    <!-- Country -->
    <?php if (odm_country_manager()->get_current_country() == "mekong" && !empty($document->extras_odm_spatial_range)): ?>
      <li class="country_indicator data_meta">

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
      </li>
    <?php endif; ?>
    <!-- Date -->
    <li class="data_meta">
      <?php if ($param_sorting == "metadata_modified"):
        $metadata_date = $document->metadata_modified; ?>
        <i class="fa fa-pencil"></i>
      <?php else:
        $metadata_date = $document->metadata_created; ?>
        <i class="fa fa-clock-o"></i>
      <?php endif; ?>
      <span>
        <?php
          if (odm_language_manager()->get_current_language() == 'km'):
            $date = wp_solr_print_date($metadata_date,"j.M.Y");
					  echo convert_date_to_kh_date($date);
					else:
            echo wp_solr_print_date($metadata_date,"j F Y");
					endif; ?>
      </span>
    </li>
    <!-- Author (coorporate) -->
    <?php
      if (!empty($document->extras_marc21_110)): ?>
        <li class="data_meta">
          <i class="fa fa-building-o"></i>
    <?php
        echo $document->extras_marc21_110; ?>
        </li>
    <?php
      endif; ?>
    <!-- Author -->
    <?php
      if (!empty($document->extras_marc21_100)): ?>
        <li class="data_meta">
          <i class="fa fa-user-circle-o"></i>
      <?php
          echo $document->extras_marc21_100; ?>
        </li>
    <?php
      endif; ?>
    <!-- Source -->
    <?php
      $source = wp_odm_solr_parse_multilingual_ckan_content($document->extras_odm_source,odm_language_manager()->get_current_language(),"");
      if (!empty($source)):
        $shortened_source = shorten_string_words($source,10);
        if (strlen($shortened_source) != strlen($source)):
          $shortened_source .= "...";
        endif; ?>
        <li class="data_meta">
          <b><?php _e('Source:','wp-odm_solr') ?></b> <?php echo $shortened_source; ?>
        </li>
    <?php
      endif; ?>
    <!-- Topics -->
    <?php if (!empty($document->vocab_taxonomy)): ?>
      <li class="data_meta">
        <i class="fa fa-folder-o"></i>
        <span>
          <?php
            $topics = (array) $document->vocab_taxonomy;
            foreach ($topics as $topic): ?>
              <a href="<?php echo generate_link_to_category_from_name($topic) ?>"><?php _e($topic, 'wp-odm_solr'); ?></a>
              <?php
              if ($topic !== end($topics)):
                echo ", ";
              endif;
            endforeach;?>
        </span>
      </li>
    <?php endif; ?>
    <!-- Keywords -->
    <?php if (!empty($document->extras_odm_keywords)): ?>
      <li class="data_meta">
        <i class="fa fa-tags"></i>
        <?php
          $keywords = (array) $document->extras_odm_keywords;
          foreach ($keywords as $keyword):
            _e($keyword, 'wp-odm_solr') ;
            if ($keyword !== end($keywords)):
              echo ", ";
            endif;
          endforeach;?>
      </li>
    <?php endif; ?>
    </ul>
  </div>

  <div class="item-content sixteen columns">
    <p class="data_description">

      <?php
        $thumbnail_image_url = wp_solr_get_image_url_from_ckan_result($document);
        if (isset($thumbnail_image_url)):?>
          <img src="<?php echo $thumbnail_image_url ?>"></img>
      <?php
        endif; ?>

      <?php
        $description = wp_odm_solr_parse_multilingual_ckan_content($document->extras_notes_translated,odm_language_manager()->get_current_language(),$document->notes);
        $description = strip_tags($description);
        $description = substr($description,0,400);
        $description = wp_odm_solr_highlight_search_words($s,$description);
        echo $description;
        if (strlen($description) >= 400):
          echo "...";
        endif;
        ?>
    </p>
  </div>

</div>
