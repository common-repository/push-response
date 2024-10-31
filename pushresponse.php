<?php
/*
Plugin Name: Push Response
Plugin URI: https://pushresponse.com
Description: Push Response lets you send push notifications from your desktop or mobile website to your users.
Visit <a href="https://pushreponse.com/">PushResponse</a> for more details.
Author: PushResponse
Version: 1.2
Author URI: https://pushresponse.net
*/

  // Exit if accessed directly
  defined('ABSPATH') || exit;


  //************* GLOBALS *************

  $I18N = array(
    'title' => 'Push Response',
    'settings' => 'Settings',
    'your_lists' => 'Your Lists',
    'no_lists' => 'You haven\'t added any lists yet.',
    'list_id' => 'List ID',
    'buttons' => array(
      'add_list' => 'Add',
      'remove_list' => 'Remove'
    ),
    'list_properties' => array(
      'domain' => 'Domain',
      'id' => 'ID',
    ),
    'actions' => array(
      'add' => 'Add List',
      'edit' => 'Edit',
      'remove' => 'Remove',
    ),
    'get_started_message' => array(
      'main' => 'Push Response has been successfully installed.',
      'secondary' => 'Now you can go to plugin settings and connect your push lists.'
    )
  );

  $ASSETS = array(
    'main_javascript' => array(
      'name' => 'pushresponse_javascript',
      'path' => 'javascripts/main.js',
      'type' => 'script'
    ),
    'main_stylesheet' => array(
      'name' => 'pushresponse_stylesheeet',
      'path' => 'stylesheets/main.css',
      'type' => 'style'
    ),
    'font_awesome' => array(
      'name' => 'pushresponse_font_awesome',
      'path' =>  'vendor/font-awesome/css/font-awesome.min.css',
      'type' => 'style'
    )
  );

  $PUSHRESPONSE_SETTINGS_SECTION_NAME = 'pushresponse_settings';
  $PUSHRESPONSE_LISTS_SETTING_NAME = 'pushresponse_setting_lists';
  $PUSHRESPONSE_USER_STARTED_TO_USE_PLUGIN_SETTING_NAME = 'pushresponse-user-started-using-plugin';

  $lists = json_decode(get_option($PUSHRESPONSE_LISTS_SETTING_NAME), true);
  $user_started_using_plugin = get_option($PUSHRESPONSE_USER_STARTED_TO_USE_PLUGIN_SETTING_NAME);


  //************* HOOKS *************

  add_action('wp_footer', 'pushresponse_append_scripts');
  add_action('admin_menu', 'pushresponse_add_settings_page');
  add_action('admin_init', 'pushresponse_register_settings');
  add_action('admin_notices', 'pushresponse_show_get_started_message');


  //************* MAIN FUNCTIONS *************

  function pushresponse_append_scripts() {
    global $lists;

    foreach ($lists as $list) {
      echo '<script src="' . $list['responder_url'] . '"></script>';
    }
  }

  function include_assets() {
    global $ASSETS;

    foreach ($ASSETS as $asset) {
      $asset_url = plugins_url($asset['path'], __FILE__);

      if ($asset['type'] == 'script') {
        wp_enqueue_script($asset['name'], $asset_url, array(), null, true);
      } else if ($asset['type'] == 'style') {
        wp_enqueue_style($asset['name'], $asset_url, array(), null, 'all');
      }
    }
  }


  //************* PLUGIN SETTINGS *************

  function pushresponse_add_settings_page() {
    global $I18N;

    add_options_page(
      $I18N['title'],
      $I18N['title'],
      'manage_options',
      $PUSHRESPONSE_SETTINGS_SECTION_NAME,
      'pushresponse_get_settings_page_html'
    );

    include_assets();
  }

  function pushresponse_register_settings() {
    global $PUSHRESPONSE_SETTINGS_SECTION_NAME;
    global $PUSHRESPONSE_LISTS_SETTING_NAME;
    global $PUSHRESPONSE_USER_STARTED_TO_USE_PLUGIN_SETTING_NAME;

    register_setting(
      $PUSHRESPONSE_SETTINGS_SECTION_NAME,
      $PUSHRESPONSE_LISTS_SETTING_NAME
    );

    register_setting(
      $PUSHRESPONSE_SETTINGS_SECTION_NAME,
      $PUSHRESPONSE_USER_STARTED_TO_USE_PLUGIN_SETTING_NAME
    );
  }

  function pushresponse_get_settings_page_html() {
    global $PUSHRESPONSE_SETTINGS_SECTION_NAME;
    global $PUSHRESPONSE_LISTS_SETTING_NAME;
    global $PUSHRESPONSE_USER_STARTED_TO_USE_PLUGIN_SETTING_NAME;
    global $I18N;
    global $lists;

    ?>

    <div class="pushresponse-wrapper">
      <h1><?php echo $I18N['title'] . ' ' . $I18N['settings'] ?></h1>

      <div class="pushresponse-container">
        <h3><?php echo $I18N['your_lists']; ?></h3>
        <?php
          if (count($lists) > 0) {
            echo '<div class="pushresponse-lists-wrapper">';
            echo '<table class="pushresponse-lists-table" cellspacing="0">
              <tr class="pushrespnse-table-header">
                <th class="pushresponse-table-cell pushresponse-table-cell-header pushresponse-text-left">' . $I18N['list_properties']['domain'] . '</th>
                <th class="pushresponse-table-cell pushresponse-table-cell-header pushresponse-text-left">' . $I18N['list_properties']['id'] . '</th>
                <th class="pushresponse-table-cell pushresponse-table-cell-header pushresponse-text-center">' . $I18N['actions']['edit'] . '</th>
                <th class="pushresponse-table-cell pushresponse-table-cell-header pushresponse-text-center">' . $I18N['actions']['remove'] . '</th>
              </tr>';

            foreach ($lists as $list) {
              echo get_list_template($list);
            }

            echo '</table>';
            echo '</div>';
          } else {
            echo '<div class="pushresponse-line"></div>';
            echo '<div class="pushresponse-no-lists">' . $I18N['no_lists'] . '</div>';
          }
         ?>
      </div>
      <div class="pushresponse-container">
        <h3><?php echo $I18N['actions']['add']; ?></h3>
          <div class="pushresponse-settings-form-wrapper">
            <form class="pushresponse-settings-form" method="post" action="options.php">
              <?php settings_fields($PUSHRESPONSE_SETTINGS_SECTION_NAME); ?>
              <?php
                echo "<input class='pushresponse-lists-data' type='hidden' name='" . $PUSHRESPONSE_LISTS_SETTING_NAME . "' value='" . json_encode($lists) . "'>";
                echo "<input class='pushresponse-lists-data' type='hidden' name='" . $PUSHRESPONSE_USER_STARTED_TO_USE_PLUGIN_SETTING_NAME . "' value='1'>";
              ?>
              <div class="pushresponse-line">
                <input class="pushresponse-text-input pushresponse-new-list-id" type="text" name="new-list-id" placeholder="<?php echo $I18N['list_id']; ?>"/>
              </div>
              <div class="pushresponse-line">
                <button type="button" class="button-primary pushreponse-add-list-button"><?php echo $I18N['buttons']['add_list']; ?></button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php
  }

  function get_list_template($list) {
    global $I18N;

    return
      '<tr class="pushresponse-table-row" data-id="' . $list['id'] . '">
        <td class="pushresponse-table-cell pushresponse-text-left">' . $list['domain'] . '</td>
        <td class="pushresponse-table-cell pushresponse-text-left">' . $list['id'] . '</td>
        <td class="pushresponse-table-cell pushresponse-text-center">
          <a href="https://messageresponse.net/campaigns/web/' . $list['id'] . '/edit" target="_blank" class="pushresponse-icon-button pushresponse-edit-list-button fa fa-pencil" data-id="' . $list['id'] . '" title="' . $I18N['actions']['edit'] . '"></a>
        </td>
        <td class="pushresponse-table-cell pushresponse-text-center">
          <button type="button" class="pushresponse-icon-button pushresponse-remove-list-button fa fa-trash" data-id="' . $list['id'] . '" title="' . $I18N['actions']['remove'] . '"></button>
        </td>
      </tr>';
  }


  //************* GET STARTED MESSAGE *************

  function pushresponse_show_get_started_message() {
    global $I18N;
    global $user_started_using_plugin;

    if (!is_admin()) {
      return;
    }

    if (!$user_started_using_plugin) {
      echo '<div class="notice notice-success"><p><strong>' . $I18N['get_started_message']['main'] . '</strong></p>
        <p>' . $I18N['get_started_message']['secondary'] .'</p></div>';
    }
  }
?>
