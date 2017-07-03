<?php $query_var_name = $is_search_page ? 'query' : 's'; ?>

<div class="search-input adv-nav-input three columns">
  <label for="search_field"><?php _e('Text search', 'wp-odm_solr'); ?></label>
  <input id="search_field" name="<?php echo $query_var_name; ?>" type="text" class="full-width search_field" value="<?php echo $param_query?>" placeholder="<?php _e("Search datasets, topics, news articles...",'wp-odm_solr'); ?>" data-solr-host="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host'); ?>" data-solr-scheme="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme'); ?>"  data-solr-path="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path'); ?>" data-solr-core-unified="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_unified'); ?>" data-odm-current-lang="<?php echo odm_language_manager()->get_current_language(); ?>" data-odm-current-country="<?php echo odm_country_manager()->get_current_country_code(); ?>" data-odm-show-regional-contents="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_regional_contents_enabled'); ?>"></input>
  <div id="spell"><b><?php _e("Did you mean?","wp-odm_solr");?></b></div>
</div>

<!-- TAXONOMY FILTER -->
<div class="search-input adv-nav-input two columns">
  <label for="taxonomy"><?php _e('Topic', 'wp-odm_solr'); ?></label>
  <select id="taxonomy" name="taxonomy" class="full-width filter_box" data-placeholder="<?php _e('Select term', 'wp-odm_solr'); ?>">
    <option value="all" <?php if (isset($param_taxonomy) || $param_taxonomy == 'all') echo 'selected'; ?>><?php _e('All','wp-odm_solr') ?></option>
      <?php
      if (array_key_exists("vocab_taxonomy",$facets[$param_type])):
        $top_tier_taxonomic_terms_keys = array_keys($top_tier_taxonomic_terms);
        sort($top_tier_taxonomic_terms_keys,SORT_STRING);
        foreach($top_tier_taxonomic_terms_keys as $top_tier_term):
          $available_records = 0;
          $taxonomy_facets = $facets[$param_type]["vocab_taxonomy"];
          foreach ($taxonomy_facets as $value => $count):
            $corresponding_top_tier = odm_taxonomy_manager()->get_top_tier_term_for_subterm($value);
            if (isset($corresponding_top_tier) && $corresponding_top_tier == $top_tier_term && $count > $available_records):
              $available_records = $count;
            endif;
          endforeach;

          if ($available_records > 0):
            $selected = ($top_tier_term == $param_taxonomy);
            $translated_top_tier_term = __($top_tier_term,'wp-odm_solr');?>
            <option value="<?php echo $top_tier_term; ?>" <?php if($selected) echo 'selected'; ?>>
              <?php
                echo $translated_top_tier_term;
                // if (!$selected):
                //   echo " (" . $available_records . ")";
                // endif; ?>
            </option>
          <?php
          endif;
        endforeach;
      endif; ?>
  </select>
</div>
<!-- END OF TAXONOMY FILTER -->

<!-- COUNTRY FILTER -->
<div class="search-input adv-nav-input two columns">
  <label for="country"><?php _e('Country', 'wp-odm_solr'); ?></label>
  <select multiple id="country" name="country[]" class="full-width filter_box" data-placeholder="<?php _e('Select country', 'wp-odm_solr'); ?>">
    <?php
      foreach($country_codes_iso2 as $country_code):
        if (array_key_exists("extras_odm_spatial_range",$facets[$param_type])):
          $spatial_range_facets = $facets[$param_type]["extras_odm_spatial_range"];
          if (array_key_exists($country_code,$spatial_range_facets)):
            $available_records = $spatial_range_facets[$country_code];
            if ($available_records > 0):
              $country_name = odm_country_manager()->get_country_name_by_country_code($country_code);
              $selected = in_array($country_code,$param_country); ?>
              <option value="<?php echo $country_code; ?>" <?php if($selected) echo 'selected'; ?>>
                <?php
                  _e($country_name,'wp-odm_solr');
                  if (!$selected):
                    echo " (" . $available_records . ")";
                  endif; ?>
              </option>
            <?php
            endif;
          endif;
        endif; ?>
        <?php
      endforeach; ?>
  </select>
</div>
<!-- END OF COUNTRY FILTER  -->

<!-- LANGUAGE FILTER -->
<div class="adv-nav-input two columns">
  <label for="language"><?php _e('Language', 'wp-odm_solr'); ?></label>
  <select multiple id="language" name="language[]" class="filter_box" data-placeholder="<?php _e('Select language', 'wp-odm_solr'); ?>"> -->
    <?php
      foreach($languages as $key => $value):
        if (array_key_exists("extras_odm_language",$facets[$param_type])):
          $language_facets = $facets[$param_type]["extras_odm_language"];
          if (array_key_exists($key,$language_facets)):
            $available_records = $language_facets[$key];
            if ($available_records > 0):
              $selected = in_array($key,$param_language); ?>
              <option value="<?php echo $key; ?>" <?php if($selected) echo 'selected'; ?>>
                <?php
                  _e($value,'wp-odm_solr');
                  if (!$selected):
                    echo " (" . $available_records . ")";
                  endif; ?>
              </option>
            <?php
            endif;
          endif;
        endif;
      endforeach; ?>
  </select>
</div>
<!-- END OF LANGUAGE FILTER -->

<!-- LICENSE FILTER -->
<div class="adv-nav-input two columns">
  <label for="license"><?php _e('License', 'wp-odm_solr'); ?></label>
  <select multiple id="license" name="license[]" class="full-width filter_box" data-placeholder="<?php _e('Select license', 'wp-odm_solr'); ?>">
    <?php
      foreach($license_list as $license):
        if (array_key_exists("license_id",$facets[$param_type])):
          $license_facets = $facets[$param_type]["license_id"];
          if (array_key_exists($license->id,$license_facets)):
            $available_records = $license_facets[$license->id];
            if ($available_records > 0):
              $selected = in_array($license->id,$param_license); ?>
              <option value="<?php echo $license->id; ?>" <?php if($selected) echo 'selected'; ?>>
                <?php
                  _e($license->title,'wp-odm_solr');
                  if (!$selected):
                    echo " (" . $available_records . ")";
                  endif; ?>
              </option>
            <?php
            endif;
          endif;
        endif;
      endforeach; ?>
  </select>
</div>
<!-- END OF LICENSE FILTER -->

<!-- YEAR FILTER -->
<div class="adv-nav-input two columns">
  <label for="metadata_created"><?php _e('Year', 'wp-odm_solr'); ?></label>
  <select id="metadata_created" name="metadata_created" class="full-width filter_box" data-placeholder="<?php _e('Select year', 'wp-odm_solr'); ?>">
    <option value="all" <?php if (isset($param_metadata_created) || $param_metadata_created == 'all') echo 'selected'; ?>><?php _e('All','wp-odm_solr') ?></option>
    <?php
        if (array_key_exists("metadata_created",$facets[$param_type])):
          $year_facets = $facets[$param_type]["metadata_created"];
          $available_year_values = array_keys($year_facets);
          sort($available_year_values,SORT_STRING);
          foreach ($available_year_values as $year):
            $count = $year_facets[$year];
            $selected = $year == $param_metadata_created; ?>
            <option value="<?php echo $year; ?>" <?php if($selected) echo 'selected'; ?>><?php echo $year . " (" . $count . ")"; ?></option>
        <?php
          endforeach;
        endif; ?>
  </select>
</div>
<!-- END OF YEAR FILTER -->

<div class="three columns align-right">
  <button class="full-width search-button" type="submit"><i class="fa fa-search" aria-hidden="true"></i> <?php _e('Search','wp-odm_solr') ?></button>
  <a href="?<?php echo $query_var_name ?>="><?php _e('Clear','wp-odm_solr') ?></a>
</div>
