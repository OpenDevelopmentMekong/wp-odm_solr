<ul>
<?php
foreach ($all_search_types as $key => $value):
  $count = ($results[$key]) ? $results[$key]->getNumFound() : 0;
  if ($count > 0): ?>
  <li>
    <div class="result_link_list <?php if ($param_type == $key) echo "data-number-results-medium" ?>">
      <?php
        $new_url = construct_url($_SERVER['REQUEST_URI'], 'type', $key);
        $new_url = construct_url($new_url, 'page', 0);
        ?>
      <a href="<?php echo $new_url ?>">
        <i class="<?php echo $value['icon']; ?>"></i> 
        <?php echo __($value['title'],'wp-odm_solr') . " (".$count.")" ; ?>
      </a>
    </div>
  </li>
<?php
    endif;
  endforeach; ?>
</ul>
