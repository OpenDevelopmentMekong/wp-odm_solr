<?php

if (!class_exists('Odm_Solr_Pages_Post_Type')) {

  class Odm_Solr_Pages_Post_Type
  {
    public function __construct()
    {
      add_action('init', array($this, 'register_post_type'));
      add_action('save_post', array($this, 'save_post_data'));
      add_action('add_meta_boxes', array($this, 'add_meta_box'));
      add_filter('single_template', array($this, 'get_solr_pages_template'));
    }

    public function get_solr_pages_template($single_template)
    {
      global $post;

      $template = get_post_meta($post->ID, '_solr_pages_attributes_template_layout', true);
      $template = isset($template) ? $template : 'odm';

      if ($post->post_type == 'search-pages') {
        $single_template = dirname(plugin_dir_path(__FILE__)).'/templates/' . $template . '/solr_search.php';
      }

      return $single_template;
    }

    public function register_post_type()
    {
        $labels = array(
        'name' => __('Search pages', 'post type general name', 'wp-odm_solr'),
        'singular_name' => __('Search page', 'post type singular name', 'wp-odm_solr'),
        'menu_name' => __('Search pages', 'admin menu for tabular pages', 'wp-odm_solr'),
        'name_admin_bar' => __('Search pages', 'add new on admin bar', 'wp-odm_solr'),
        'add_new' => __('Add new', 'search page', 'wp-odm_solr'),
        'add_new_item' => __('Add new search page', 'wp-odm_solr'),
        'new_item' => __('New search page', 'wp-odm_solr'),
        'edit_item' => __('Edit search page', 'wp-odm_solr'),
        'view_item' => __('View search page', 'wp-odm_solr'),
        'all_items' => __('All search pages', 'wp-odm_solr'),
        'search_items' => __('Search search pages', 'wp-odm_solr'),
        'parent_item_colon' => __('Parent search pages:', 'wp-odm_solr'),
        'not_found' => __('No search page found.', 'wp-odm_solr'),
        'not_found_in_trash' => __('No search page found in trash.', 'wp-odm_solr'),
        );

        $args = array(
          'labels'             => $labels,
          'public'             => true,
          'publicly_queryable' => true,
          'show_ui'            => true,
          'show_in_menu'       => true,
		      'menu_icon'          => '',
          'query_var'          => 'solr_',
          'rewrite'            => array( 'slug' => 'search' ),
          'capability_type'    => 'page',
          'has_archive'        => false,
          'hierarchical'       => false,
          'menu_position'      => 5,
          //'taxonomies'         => array('category', 'language', 'post_tag'),
          'supports' => array('title', 'editor', 'page-attributes', 'revisions', 'author', 'thumbnail')
        );

        register_post_type('search-pages', $args);
    }

		public function add_meta_box()
    {
      // Profile settings
      add_meta_box(
       'search-pages_options',
       __('Option for search pages', 'wp-odm_solr'),
       array($this, 'solr_pages_options_box'),
       'search-pages',
       'advanced',
       'high'
      );

      add_meta_box(
       'solr_pages_template_layout',
       __('Template layout', 'wp-odm_solr'),
       array($this, 'solr_pages_layout_settings_box'),
       'search-pages',
       'side',
       'low'
      );

		}

		public function solr_pages_options_box($post = false)
    {
        $supported_types = get_post_meta($post->ID, '_solr_pages_attributes_supported_types', true); ?>

			  <h4><?php _e('Supported content types', 'wp-odm_solr');?></h4>
			  <input class="full-width" type="text" id="_solr_pages_attributes_supported_types" name="_solr_pages_attributes_supported_types" placeholder="dataset, library_record, laws_record, agreement" value="<?php echo $supported_types; ?>"></input>
        <p class="description"><?php _e('Please add the document types that should be supported on this page', 'wp-odm_solr'); ?></p>

   <?php
    }

    public function solr_pages_layout_settings_box($post = false)
    {
        $template = get_post_meta($post->ID, '_solr_pages_attributes_template_layout', true); ?>
        <div id="solr_pages_template_layout_settings_box">
         <h4><?php _e('Choose template layout', 'wp-odm_solr');?></h4>
         <select id="_solr_pages_attributes_template_layout" name="_solr_pages_attributes_template_layout">
            <option value="default" <?php if ($template == "default"): echo "selected"; endif; ?>>Default</option>
            <option value="odc" <?php if ($template == "odc"): echo "selected"; endif; ?>>ODC 2.2</option>
            <option value="odm" <?php if ($template == "odm"): echo "selected"; endif; ?>>ODM 2.2</option>
          </select>
        </div>
    <?php
    }

    public function save_post_data($post_id)
    {
        global $post;
        if (isset($post->ID) && get_post_type($post->ID) == 'search-pages') {

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (defined('DOING_AJAX') && DOING_AJAX) {
                return;
            }

            if (false !== wp_is_post_revision($post_id)) {
                return;
            }

            if (isset($_POST['_solr_pages_attributes_supported_types'])) {
                update_post_meta($post_id, '_solr_pages_attributes_supported_types', str_replace(" ","",$_POST['_solr_pages_attributes_supported_types']));
            }

            if (isset($_POST['_solr_pages_attributes_template_layout'])) {
                update_post_meta($post_id, '_solr_pages_attributes_template_layout', $_POST['_solr_pages_attributes_template_layout']);
            }

            if (!current_user_can('edit_post')) {
                return;
            }

        }
      }
    }
}

?>
