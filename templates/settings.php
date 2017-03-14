<div class="wrap">
    <h2>wp-odm_solr - A plugin for indexing WP content into solr</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('wp_odm_solr-group'); ?>
        <?php @do_settings_fields('wp_odm_solr-group'); ?>

        <?php
          $solr_host = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_host');
          $solr_port = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_port');
          $solr_scheme = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_scheme');
          $solr_path = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_path');
          $solr_core = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_core');
          $solr_user = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_user');
          $solr_pwd = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_solr_pwd');
          $valid_connection_read = wp_odm_solr_validate_settings_read($solr_host,$solr_port,$solr_path,$solr_core,$solr_scheme,$solr_user,$solr_pwd);
        ?>

        <table class="form-table">
          <th scope="row"><label><h3><?php _e('Connecting to Solr','wp-odm_solr') ?></h3></label></th>
          <tr valign="top">
              <th scope="row"><label for="wp_odm_solr_setting_solr_host"><?php _e('Host','wp-odm_solr') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wp_odm_solr_setting_solr_host" id="wp_odm_solr_setting_solr_host" value="<?php echo $solr_host ?>"/>
                <p class="description"><?php _e('Specify host without protocol, just domain. example: solr.opendevelopmentmekong.net ','wp-odm_solr') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="wp_odm_solr_setting_solr_port"><?php _e('Port','wp-odm_solr') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wp_odm_solr_setting_solr_port" id="wp_odm_solr_setting_solr_port" value="<?php echo $solr_port ?>"/>
                <p class="description"><?php _e('Specify the port under which the solr instance is available','wp-odm_solr') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="wp_odm_solr_setting_solr_scheme"><?php _e('Scheme','wp-odm_solr') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wp_odm_solr_setting_solr_scheme" id="wp_odm_solr_setting_solr_scheme" value="<?php echo $solr_scheme ?>"/>
                <p class="description"><?php _e('http or https','wp-odm_solr') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="wp_odm_solr_setting_solr_path"><?php _e('Path','wp-odm_solr') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wp_odm_solr_setting_solr_path" id="wp_odm_solr_setting_solr_path" value="<?php echo $solr_path ?>"/>
                <p class="description"><?php _e('Usually /solr/','wp-odm_solr') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="wp_odm_solr_setting_solr_core"><?php _e('Core','wp-odm_solr') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wp_odm_solr_setting_solr_core" id="wp_odm_solr_setting_solr_core" value="<?php echo $solr_core ?>"/>
                <p class="description"><?php _e('Example: wordpress_content','wp-odm_solr') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="wp_odm_solr_setting_solr_user"><?php _e('User','wp-odm_solr') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wp_odm_solr_setting_solr_user" id="wp_odm_solr_setting_solr_user" value="<?php echo $solr_user ?>"/>
                <p class="description"><?php _e('Username for authentication','wp-odm_solr') ?>.</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="wp_odm_solr_setting_solr_pwd"><?php _e('Password','wp-odm_solr') ?></label></th>
              <td>
                <input class="full-width" type="text" name="wp_odm_solr_setting_solr_pwd" id="wp_odm_solr_setting_solr_pwd" value="<?php echo $solr_pwd ?>"/>
                <p class="description"><?php _e('Password for authentication','wp-odm_solr') ?>.</p>
              </td>
          </tr>
          <!-- Connection status -->
          <tr valign="top">
            <th scope="row"><label><?php _e('Connection status','wp-odm_solr') ?></label></th>
            <td>
              <?php if ($valid_connection_read){ ?>
                <p class="ok"><?php _e('Ping succeded.','wp-odm_solr') ?></p>
              <?php } else { ?>
                <p class="error"><?php _e('Problem connecting to Solr instance. Please, check the specified config.','wp-odm_solr') ?></p>
              <?php } ?>
            </td>
          </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
