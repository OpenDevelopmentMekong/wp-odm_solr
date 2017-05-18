<?php
/**
 * Plugin Name: wp-odm_solr
 * Plugin URI: http://github.com/OpenDevelopmentMekong/wp-odm_solr
 * Description: ODI Internal Wordpress plugin for indexing created/updated WP contents automatically into a solr index
 * Version: 2.2.18
 * Author: Alex Corbi (mail@lifeformapps.com)
 * Author URI: http://www.lifeformapps.com
 * License: GPLv3
 */
 require 'vendor/autoload.php';
 include_once plugin_dir_path(__FILE__).'utils/wp_odm_solr_utils.php';
 include_once plugin_dir_path(__FILE__).'utils/solr-wp-manager.php';
 include_once plugin_dir_path(__FILE__).'utils/solr-ckan-manager.php';

 // Require post types
 require_once plugin_dir_path(__FILE__).'post-types/search-pages.php';

if (!class_exists('WpOdmSolr')) {

    class WpOdmSolr
    {

      private static $instance;

      private static $post_type;

      public static function get_instance()
      {
          if (null == self::$instance) {
              self::$instance = new self();
          }

          if (null == self::$post_type) {
            self::$post_type = new Odm_Solr_Pages_Post_Type();
          }

          return self::$instance;
      }

        /**
         * Construct the plugin object.
         */
        public function __construct()
        {
            add_action('admin_init', array(&$this, 'wp_odm_solr_admin_init'));
            add_action('admin_menu', array(&$this, 'wp_odm_solr_add_menu'));
            add_action('init', array(&$this, 'wp_odm_solr_register_plugin_styles'));
            add_action('save_post', array(&$this, 'wp_odm_solr_save_post'));
            add_action('admin_notices', array($this, 'check_requirements'));
            add_action('init', array($this, 'load_text_domain'));
            add_filter('template_include',array($this,'wp_odm_solr_search_template'));
            add_action('export_wp', array($this,'wp_odm_solr_increase_export_memory_limit'));
            add_shortcode( 'admin_scripts_reindex_wp_contents', array($this,'reindex_wp_contents'));
        }

        function reindex_wp_contents() {
          ob_start();
          include( dirname(__FILE__) . '/admin-scripts/reindex-wp-contents.php' );
          $output = ob_get_contents();
          ob_end_clean();
          return $output;
        }

        public function wp_odm_solr_increase_export_memory_limit() {
          ini_set('memory_limit', '1024M');
        }

        public function wp_odm_solr_search_template($template){
            global $wp_query;
            if (!$wp_query->is_search):
                return $template;
            endif;

            $template = $GLOBALS['wp_odm_solr_options']->get_option('wp_odm_solr_setting_template');
            $template = isset($template) ? $template : 'default';
            $template_file = $template == 'default' ? 'default' : 'new';

            return dirname( __FILE__ ) . '/templates/' . $template_file . '/solr_search.php';
        }

        public function load_text_domain()
        {
          load_plugin_textdomain( 'wp-odm_solr', false,  dirname( plugin_basename( __FILE__ ) ) . '/i18n' );
        }

        function check_requirements(){

          if (WP_ODM_SOLR_CHECK_REQS == True):
            if (!WP_Odm_Solr_CKAN_Manager()->ping_server()):
              echo '<div class="error"><p>CKAN index seems to be unresponsive or missconfigured, please check.</p></div>';
            endif;

            if (!WP_Odm_Solr_WP_Manager()->ping_server()):
              echo '<div class="error"><p>WP index seems to be unresponsive or missconfigured, please check.</p></div>';
            endif;
          endif;

        }

        public function wp_odm_solr_register_plugin_styles($hook)
        {
            wp_register_style('wp_odm_solr_style', plugin_dir_url(__FILE__).'css/wp_odm_solr_style.css');
            wp_enqueue_style('wp_odm_solr_style');
        }

        public function wp_odm_solr_save_post($post_ID)
        {
          wp_odm_solr_log('wp_odm_solr_save_post');

          $post = get_post($post_ID);

          if ($post->post_status == "publish"):
            WP_Odm_Solr_WP_Manager()->index_post($post);
          endif;
        }

        /**
         * Activate the plugin.
         */
        public static function activate()
        {
            // Do nothing
        }

        /**
         * Deactivate the plugin.
         */
        public static function deactivate()
        {
            // Do nothing
        }

        /**
         * hook into WP's admin_init action hook.
         */
        public function wp_odm_solr_admin_init()
        {
            $this->init_settings();
        }

        /**
         * Initialize some custom settings.
         */
        public function init_settings()
        {
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_solr_host');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_solr_port');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_solr_path');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_solr_core_wp');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_solr_core_ckan');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_solr_schema');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_solr_user');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_solr_pwd');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_solr_scheme');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_log_path');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_template');
            register_setting('wp_odm_solr-group', 'wp_odm_solr_setting_log_enabled');
        }

        /**
         * add a menu.
         */
        public function wp_odm_solr_add_menu()
        {
            add_options_page('wp-odm_solr Settings', 'wp-odm_solr', 'manage_options', 'wp-odm_solr', array(&$this, 'plugin_settings_page'));
        }

        /**
         * Menu Callback.
         */
        public function plugin_settings_page()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            include sprintf('%s/templates/settings.php', dirname(__FILE__));
        }
    }
}

if (class_exists('WpOdmSolr')) {
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('wp-odm_solr', 'activate'));
    register_deactivation_hook(__FILE__, array('wp-odm_solr', 'deactivate'));
}

add_action('plugins_loaded', array('WpOdmSolr', 'get_instance'));
