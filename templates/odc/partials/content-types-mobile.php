<label for="type"><?php _e('Content type:', 'wp-odm_solr'); ?></label>
<select id="type" name="type" class="full-width filter_box" onchange="this.form.submit()">
  <?php
  foreach ($all_search_types as $key => $value):
    $count = ($results[$key]) ? $results[$key]->getNumFound() : 0;
    if ($count > 0): ?>
      <option value="<?php echo $key; ?>" <?php if ($param_type == $key) echo 'selected'; ?>>
        <?php
          _e($value['title'],'wp-odm_solr'); ?>
      </option>
  <?php
      endif;
    endforeach; ?>
</select>
