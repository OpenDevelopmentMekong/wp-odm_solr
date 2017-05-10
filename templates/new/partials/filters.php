<h3><i class="fa fa-filter"></i> Filters</h3>

<!-- TAXONOMY FILTER -->
<div class="single-filter">
  <label for="taxonomy"><?php _e('Topic', 'wp-odm_solr'); ?></label>
  <select multiple id="taxonomy" name="taxonomy[]" class="filter_box" data-placeholder="<?php _e('Select term', 'wp-odm_solr'); ?>">
    <?php
      foreach($taxonomy_list as $value):
        if (array_key_exists("vocab_taxonomy",$facets)):
          $taxonomy_facets = $facets["vocab_taxonomy"];
          if (array_key_exists($value,$taxonomy_facets)):
            $available_records = $taxonomy_facets[$value];
            if ($available_records > 0): ?>
              <option value="<?php echo $value; ?>" <?php if(in_array($value,$param_taxonomy)) echo 'selected'; ?>><?php echo $value . " (" . $available_records . ")"; ?></option>
            <?php
            endif;
          endif;
        endif;
      endforeach; ?>
  </select>
</div>
<!-- END OF TAXONOMY FILTER -->

<!-- COUNTRY FILTER -->
<?php if (odm_country_manager()->get_current_country() == 'mekong'): ?>
<div class="single-filter">
  <label for="country"><?php _e('Country', 'wp-odm_solr'); ?></label>
  <select multiple id="country" name="country[]" class="filter_box" data-placeholder="<?php _e('Select country', 'wp-odm_solr'); ?>">
    <?php
      foreach($country_codes_iso2 as $country_code):
        if (array_key_exists("extras_odm_spatial_range",$facets)):
          $spatial_range_facets = $facets["extras_odm_spatial_range"];
          if (array_key_exists($country_code,$spatial_range_facets)):
            $available_records = $spatial_range_facets[$country_code];
            if ($available_records > 0): 
              $country_name = odm_country_manager()->get_country_name_by_country_code($country_code); ?>
              <option value="<?php echo $country_code; ?>" <?php if(in_array($country_code,$param_country)) echo 'selected'; ?>><?php echo $country_name . " (" . $available_records . ")"; ?></option>
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
  <select multiple id="language" name="language[]" class="filter_box" data-placeholder="<?php _e('Select language', 'wp-odm_solr'); ?>">
    <?php
      foreach($languages as $key => $value):
        if (array_key_exists("extras_odm_language",$facets)):
          $language_facets = $facets["extras_odm_language"];
          if (array_key_exists($key,$language_facets)):
            $available_records = $language_facets[$key];
            if ($available_records > 0): ?>
              <option value="<?php echo $key; ?>" <?php if(in_array($key,$param_language)) echo 'selected'; ?>><?php echo $value . " (" . $available_records . ")" ?></option>
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
  <select multiple id="license" name="license[]" class="filter_box" data-placeholder="<?php _e('Select license', 'wp-odm_solr'); ?>">
    <?php
      foreach($license_list as $license):
        if (array_key_exists("license_id",$facets)):
          $license_facets = $facets["license_id"];
          if (array_key_exists($license->id,$license_facets)):
            $available_records = $license_facets[$license->id];
            if ($available_records > 0): ?>
              <option value="<?php echo $license->id; ?>" <?php if(in_array($license->id,$param_license)) echo 'selected'; ?>><?php echo $license->title . " (" . $available_records . ")" ?></option>
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
  <select id="sorting" name="sorting" class="filter_box" data-placeholder="<?php _e('Sort by', 'wp-odm_solr'); ?>">
    <option <?php if($param_sorting == "score") echo 'selected'; ?> value="score"><?php _e('Relevance','wp-odm_solr') ?></option>
  	<option <?php if($param_sorting == "metadata_modified") echo 'selected'; ?> value="metadata_modified"><?php _e('Date modified','wp-odm_solr') ?></option>
  </select>
</div>
<!-- END OF LICENSE FILTER -->

<div class="single-filter">
  <input class="button" type="submit" value="<?php _e('Search Filter', 'wp-odm_solr'); ?>"/>
</div>
