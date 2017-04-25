
<?php
  $is_regional = odm_country_manager()->get_current_country() == 'mekong';
  $cols_filters = $is_regional ? 'two' : 'two';
  $cols_button = $is_regional ? 'three' : 'five';
 ?>

<div class="single-filter <?php echo $cols_filters ?> columns">
  <label for="search_field_old"><?php _e('Text search', 'wp-odm_solr'); ?></label>
  <input id="search_field_old" name="s" type="text" value="<?php echo $_GET["s"]?>" placeholder="<?php _e("Search datasets, topics, news articles...","wp-odm_solr"); ?>" data-solr-host="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host'); ?>" data-solr-scheme="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme'); ?>" data-solr-path="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path'); ?>" data-solr-core-wp="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_wp'); ?>" data-solr-core-ckan="<?php echo $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core_ckan'); ?>"></input>

</div>

<!-- CONTENT TYPE FILTER -->
<div class="single-filter <?php echo $cols_filters ?> columns">
  <label for="type"><?php _e('Content type', 'wp-odm_solr'); ?></label>
  <select id="type" name="type" class="filter_box" data-placeholder="<?php _e('Select type', 'wp-odm_solr'); ?>">
    <option value="all" <?php if ($param_type == "all") echo "selected"; ?>><?php _e('All','wp-odm_solr') ?></option>
    <!-- <option value="dataset" <?php if ($param_type == "dataset") echo "selected"; ?>><?php _e('Dataset','wp-odm_solr') ?></option>
    <option value="library_record" <?php if ($param_type == "library_record") echo "selected"; ?>><?php _e('Library publications','wp-odm_solr') ?></option>
    <option value="laws_record" <?php if ($param_type == "laws_record") echo "selected"; ?>><?php _e('Laws','wp-odm_solr') ?></option>
    <option value="agreement" <?php if ($param_type == "agreement") echo "selected"; ?>><?php _e('Agreement','wp-odm_solr') ?></option> -->
    <option value="map-layer" <?php if ($param_type == "map-layer") echo "selected"; ?>><?php _e('Maps','wp-odm_solr') ?></option>
    <option value="news-article" <?php if ($param_type == "news-article") echo "selected"; ?>><?php _e('News','wp-odm_solr') ?></option>
    <option value="topic" <?php if ($param_type == "topic") echo "selected"; ?>><?php _e('Topics','wp-odm_solr') ?></option>
    <option value="profiles" <?php if ($param_type == "profiles") echo "selected"; ?>><?php _e('Profiles','wp-odm_solr') ?></option>
    <option value="announcement" <?php if ($param_type == "announcement") echo "selected"; ?>><?php _e('Announcements','wp-odm_solr') ?></option>
    <option value="site-update" <?php if ($param_type == "site-update") echo "selected"; ?>><?php _e('Site updates','wp-odm_solr') ?></option>
  </select>
</div>

<!-- TAXONOMY FILTER -->
<div class="single-filter <?php echo $cols_filters ?> columns">
  <label for="taxonomy"><?php _e('Topic', 'wp-odm_solr'); ?></label>
  <select id="taxonomy" name="taxonomy" class="filter_box" data-placeholder="<?php _e('Select term', 'wp-odm_solr'); ?>">
    <option value="all" selected><?php _e('All','wp-odm_solr') ?></option>
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
  <label for="country"><?php _e('Country', 'wp-odm_solr'); ?></label>
  <select id="country" name="country" class="filter_box" data-placeholder="<?php _e('Select country', 'wp-odm_solr'); ?>">
    <option value="all" selected><?php _e('All','wp-odm_solr') ?></option>
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
  <label for="language"><?php _e('Language', 'wp-odm_solr'); ?></label>
  <select id="language" name="language" class="filter_box" data-placeholder="<?php _e('Select language', 'wp-odm_solr'); ?>">
    <option value="all"  selected><?php _e('All','wp-odm_solr') ?></option>
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
  <label for="license"><?php _e('License', 'wp-odm_solr'); ?></label>
  <select id="license" name="license" class="filter_box" data-placeholder="<?php _e('Select license', 'wp-odm_solr'); ?>">
    <option value="all" selected><?php _e('All','wp-odm_solr') ?></option>
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
  <input class="button search_button" type="submit" value="<?php _e('Search Filter', 'wp-odm_solr'); ?>"/>
</div>

</form>
