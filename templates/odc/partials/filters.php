<?php $query_var_name = $is_search_page ? 'query' : 's'; ?>

<div class="search-input adv-nav-input four columns">
  <label for="field_s"><?php _e('Text search', 'wp-odm_solr'); ?></label>
  <input id="field_s" name="<?php echo $query_var_name; ?>" type="text" value="<?php echo $param_query?>" placeholder="<?php _e("Search datasets, topics, news articles...",'wp-odm_solr'); ?>" data-solr-host="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host'); ?>" data-solr-scheme="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme'); ?>" data-solr-path="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path'); ?>" data-solr-core-wp="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_wp'); ?>" data-solr-core-ckan="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_ckan'); ?>"></input>
</div>

<!-- TAXONOMY FILTER -->
<div class="adv-nav-input four columns">
  <label for="taxonomy"><?php _e('Topic', 'wp-odm_solr'); ?></label>
  <select id="taxonomy" name="taxonomy" class="filter_box" data-placeholder="<?php _e('Select term', 'wp-odm_solr'); ?>">
    <option value="all" <?php if (isset($param_taxonomy) || $param_taxonomy == 'all') echo 'selected'; ?>><?php _e('All','wp-odm_solr') ?></option>
      <?php
      if (array_key_exists("vocab_taxonomy",$facets[$param_type])):
        foreach(array_keys($top_tier_taxonomic_terms) as $top_tier_term):
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

<!-- LANGUAGE FILTER -->
<div class="adv-nav-input four columns">
  <label for="language"><?php _e('Language', 'wp-odm_solr'); ?></label>
  <select multiple id="language" name="language[]" class="filter_box" data-placeholder="<?php _e('Select language', 'wp-odm_solr'); ?>">
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

<div class="four columns">
  <input class="button" type="submit" value="<?php _e('Search Filter', 'wp-odm_solr'); ?>"/>
</div>
