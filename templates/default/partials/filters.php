
<?php
  $is_regional = odm_country_manager()->get_current_country() == 'mekong';
  $cols_filters = $is_regional ? 'two' : 'three';
  $cols_button = $is_regional ? 'five' : 'four';
 ?>

<div class="single-filter <?php echo $cols_filters ?> columns">
  <label for="search_field_old"><?php _e('Text search', 'odm'); ?></label>
  <input id="search_field_old" name="s" type="text" value="<?php echo $_GET["s"]?>" placeholder="<?php _e("Search datasets, topics, news articles...","wp-odm_solr"); ?>" data-solr-host="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host'); ?>" data-solr-scheme="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme'); ?>" data-solr-path="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path'); ?>" data-solr-core-wp="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_wp'); ?>" data-solr-core-ckan="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_ckan'); ?>"></input>

</div>
<!-- TAXONOMY FILTER -->
<div class="single-filter <?php echo $cols_filters ?> columns">
  <label for="taxonomy"><?php _e('Topic', 'odm'); ?></label>
  <select id="taxonomy" name="taxonomy" class="filter_box" data-placeholder="<?php _e('Select term', 'odm'); ?>">
    <option value="all" selected><?php _e('All','odm') ?></option>
    <?php
      foreach($taxonomy_list as $value):
        if (array_key_exists("vocab_taxonomy",$facets)):
          $taxonomy_facets = $facets["vocab_taxonomy"];
          if (array_key_exists($value,$taxonomy_facets)):
            $available_records = $taxonomy_facets[$value];
            if ($available_records > 0): ?>
            <option value="<?php echo $value; ?>" <?php if($value == $param_taxonomy) echo 'selected'; ?>><?php echo $value . " (" . $available_records . ")"; ?></option>
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
<div class="single-filter <?php echo $cols_filters ?> columns">
  <label for="country"><?php _e('Country', 'odm'); ?></label>
  <select id="country" name="country" class="filter_box" data-placeholder="<?php _e('Select country', 'odm'); ?>">
    <option value="all" selected><?php _e('All','odm') ?></option>
    <?php
      foreach($countries as $key => $value):
        if ($key != 'mekong'):
          if (array_key_exists("extras_odm_spatial_range",$facets)):
            $spatial_range_facets = $facets["extras_odm_spatial_range"];
            $country_codes = odm_country_manager()->get_country_codes();
            $country_code = $country_codes[$key]["iso2"];
            if (array_key_exists($country_code,$spatial_range_facets)):
              $available_records = $spatial_range_facets[$country_code];
              if ($available_records > 0): ?>
          <option value="<?php echo $key; ?>" <?php if($key == $param_country) echo 'selected'; ?> <?php if (odm_country_manager()->get_current_country() != 'mekong' && $key != odm_country_manager()->get_current_country()) echo 'disabled'; ?>><?php echo odm_country_manager()->get_country_name($key) . " (" . $available_records . ")"; ?></option>
              <?php
              endif;
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
<div class="single-filter <?php echo $cols_filters ?> columns">
  <label for="language"><?php _e('Language', 'odm'); ?></label>
  <select id="language" name="language" class="filter_box" data-placeholder="<?php _e('Select language', 'odm'); ?>">
    <option value="all"  selected><?php _e('All','odm') ?></option>
    <?php
      foreach($languages as $key => $value):
        if (array_key_exists("extras_odm_language",$facets)):
          $language_facets = $facets["extras_odm_language"];
          if (array_key_exists($key,$language_facets)):
            $available_records = $language_facets[$key];
            if ($available_records > 0): ?>
    <option value="<?php echo $key; ?>" <?php if($key == $param_language) echo 'selected'; ?>><?php echo $value . " (" . $available_records . ")" ?></option>
            <?php
            endif;
          endif;
        endif;
      endforeach; ?>
  </select>
</div>
<!-- END OF LANGUAGE FILTER -->

<!-- LICENSE FILTER -->
<div class="single-filter <?php echo $cols_filters ?> columns">
  <label for="license"><?php _e('License', 'odm'); ?></label>
  <select id="license" name="license" class="filter_box" data-placeholder="<?php _e('Select license', 'odm'); ?>">
    <option value="all" selected><?php _e('All','odm') ?></option>
    <?php
      foreach($license_list as $license):
        if (array_key_exists("license_id",$facets)):
          $license_facets = $facets["license_id"];
          if (array_key_exists($license->id,$license_facets)):
            $available_records = $license_facets[$license->id];
            if ($available_records > 0): ?>
        <option value="<?php echo $license->id; ?>" <?php if($license->id == $param_license) echo 'selected'; ?>><?php echo $license->title . " (" . $available_records . ")" ?></option>
            <?php
            endif;
          endif;
        endif;
      endforeach; ?>
  </select>
</div>
<!-- END OF LICENSE FILTER -->

<div class="single-filter <?php echo $cols_button ?> columns">
  <input class="button search_button" type="submit" value="<?php _e('Search Filter', 'odm'); ?>"/>
</div>

</form>
