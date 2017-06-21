<h3><i class="fa fa-filter"></i> Filters</h3>

<!-- TAXONOMY FILTER -->
<div class="single-filter">
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
<?php if (odm_country_manager()->get_current_country() == 'mekong'): ?>
<div class="single-filter">
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
<?php endif; ?>
<!-- END OF COUNTRY FILTER  -->

<!-- LANGUAGE FILTER -->
<div class="single-filter">
  <label for="language"><?php _e('Language', 'wp-odm_solr'); ?></label>
  <select multiple id="language" name="language[]" class="full-width filter_box" data-placeholder="<?php _e('Select language', 'wp-odm_solr'); ?>">
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
<div class="single-filter">
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

<!-- SORTING FUNCTION -->
<h3><i class="fa fa-sort"></i> <?php _e('Sorting','wp-odm_solr'); ?></h3>
<div class="single-filter">
  <label for="sorting"><?php _e('Sort by', 'wp-odm_solr'); ?></label>
  <select id="sorting" name="sorting" class="full-width filter_box" data-placeholder="<?php _e('Sort by', 'wp-odm_solr'); ?>">
    <option <?php if($param_sorting == "score") echo 'selected'; ?> value="score"><?php _e('Relevance','wp-odm_solr') ?></option>
  	<option <?php if($param_sorting == "metadata_created") echo 'selected'; ?> value="metadata_created"><?php _e('Creation date','wp-odm_solr') ?></option>
    <option <?php if($param_sorting == "metadata_modified") echo 'selected'; ?> value="metadata_modified"><?php _e('Modification date','wp-odm_solr') ?></option>
  </select>
</div>
<!-- END OF LICENSE FILTER -->

<div class="full-width single-filter">
  <input class="button" type="submit" value="<?php _e('Search Filter', 'wp-odm_solr'); ?>"/>
</div>
