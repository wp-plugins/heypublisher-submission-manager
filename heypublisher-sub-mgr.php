<?php
/*
Plugin Name: HeyPublisher Submission Manager
Plugin URI: http://loudlever.com
Description: This plugin allows you as a publisher or blog owner to accept unsolicited submissions from writers without having to create an account for them.  You can define reading periods, acceptable genres, and other filters to ensure you only receive the submissions that meet your publication's needs.
Version: 1.1.0
Author: Loudlever, Inc.
Author URI: http://www.loudlever.com


  $Id: heypublisher-sub-mgr.php 101 2010-05-13 20:16:34Z rluck $

  Copyright 2010 Loudlever, Inc. (wordpress@loudlever.com)

  Permission is hereby granted, free of charge, to any person
  obtaining a copy of this software and associated documentation
  files (the "Software"), to deal in the Software without
  restriction, including without limitation the rights to use,
  copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the
  Software is furnished to do so, subject to the following
  conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
  OTHER DEALINGS IN THE SOFTWARE.

*/

/* 
 Older versions of PHP may not define DIRECTORY_SEPARATOR so define it here,
 just in case.
*/ 
if(!defined('DIRECTORY_SEPARATOR')) {
  define('DIRECTORY_SEPARATOR','/');
}
/**
*  DEFINITIONS
*/
define('HEY_DIR', dirname(plugin_basename(__FILE__)));

/*
---------------------------------------------------------------------------------
  OPTION SETTINGS
---------------------------------------------------------------------------------
*/  

// Configs specific to the plugin
// Build Number (must be a integer)
define('HEY_BASE_URL', get_option('siteurl').'/wp-content/plugins/'.HEY_DIR.'/');
define("HEYPUB_PLUGIN_BUILD_NUMBER", "28");  // This controls whether or not we get upgrade prompt
define("HEYPUB_PLUGIN_BUILD_DATE", "2010-07-22");  
// Version Number (can be text)
define("HEYPUB_PLUGIN_VERSION", "1.1.0");
// Path to the version of Snoopy we're using - included in this package
// Relative to the 
define('HEYPUB_PLUGIN_ERROR_CONTACT','Please contact <a href="mailto:wordpress@loudlever.com?subject=plugin%20error">wordpress@loudlever.com</a> to report this error');
define('HEYPUB_PLUGIN_NOT_AUTHENTICATED_ACTION','heypub_show_menu_options');

define('HEYPUB_PLUGIN_FULLPATH', WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.HEY_DIR.DIRECTORY_SEPARATOR);

// How to connect to the service
// define('HEYPUB_FEEDBACK_URL_VALUE','http://getsatisfaction.com/hey');      
define('HEYPUB_FEEDBACK_EMAIL_VALUE','wordpress@loudlever.com?subject=HeyPublisher%20Wordpress%20Plugin');
define('HEYPUB_SVC_URL_STYLE_GUIDE','http://www.loudlever.com/docs/plugins/wordpress/style_guide');     # designates the URL of the style guide
define('HEYPUB_SVC_URL_BASE','http://heypublisher.com/api/v1');                 # designates the base URL and version of API
define('HEYPUB_SVC_URL_SUBMIT_FORM','submissions');           
define('HEYPUB_SVC_URL_AUTHENTICATE','publishers/fetch_or_create');           # initial plugin authentication
define('HEYPUB_SVC_URL_GET_PUBLISHER','publishers/show');                     # update the options
define('HEYPUB_SVC_URL_UPDATE_PUBLISHER','publishers/update_publisher');      # update the options
define('HEYPUB_SVC_URL_GET_GENRES','publishers/fetch_categories');            # fetch categories publisher accepts
define('HEYPUB_SVC_URL_GET_PUB_TYPES','publishers/fetch_publisher_types');    # fetch publisher types
define('HEYPUB_SVC_URL_GET_SUBMISSIONS','submissions/fetch_pending_submissions');           # fetch all pending submissions
define('HEYPUB_SVC_URL_RESPOND_TO_SUBMISSION','submissions/submission_action');             # accept/reject/publish action
define('HEYPUB_SVC_READ_SUBMISSION','submissions/show');                      # fetch a single submission for reading.  also sets the 'read' status

# if this changes, plugin will not work.  You have been warned
define('HEYPUB_SVC_TOKEN_VALUE','534ba1c699ca9310d7acf4832e12bed87c4d5917c5063c58382e9766bca11800');  

// Locally stored option keys
define('HEYPUB_PLUGIN_OPT_INSTALL', '_heypub_plugin_opt_install');
define('HEYPUB_PLUGIN_OPT_CONFIG', '_heypub_plugin_options');


define('HEYPUB_OPT_PLUGIN_VERSION_LAST', "_heypub_opt_plugin_version_last");
define('HEYPUB_SVC_URL','_heypub_service_url');
define('HEYPUB_OPT_PLUGIN_VERSION_CURRENT', "_heypub_opt_plugin_version_current");
define('HEYPUB_OPT_PLUGIN_VERSION_DATE', "_heypub_opt_plugin_version_date");
define('HEYPUB_OPT_SVC_PUBLISHER','_heypub_opt_svc_publisher');
define('HEYPUB_OPT_SVC_ISVALIDATED','_heypub_opt_svc_isvalidated');
define('HEYPUB_OPT_SVC_USER_OID','_heypub_opt_svc_user_oid');
define('HEYPUB_OPT_SVC_PUBLISHER_OID','_heypub_opt_svc_publisher_oid');

// messages for sending to the User
define('HEYPUB_OPT_MSG_REJECT','_heypub_opt_msg_reject');     # Text of rejection notice


// Data we need synced between two sites
define('HEYPUB_OPT_PUBLICATION_NAME','_heypub_opt_publication_name');
define('HEYPUB_OPT_PUBLICATION_URL','_heypub_opt_publication_url');
define('HEYPUB_OPT_EDITOR_NAME','_heypub_opt_editor_name');
define('HEYPUB_OPT_EDITOR_EMAIL','_heypub_opt_editor_email');

define('HEYPUB_OPT_SUBMISSION_GUIDE_URL', '_heypub_opt_sub_guide_url');
define('HEYPUB_OPT_READING_PERIOD', '_heypub_opt_reading_period');
define('HEYPUB_OPT_ACCEPTING_SUBS', '_heypub_opt_accepting_subs');
define('HEYPUB_OPT_SIMULTANEOUS_SUMBMISSIONS', '_heypub_opt_simultaneous_sumbmissions');
define('HEYPUB_OPT_MULTIPLE_SUMBMISSIONS', '_heypub_opt_multiple_sumbmissions');

define('HEYPUB_OPT_PAYING_MARKET', '_heypub_opt_paying_market');
define('HEYPUB_OPT_PAYING_MARKET_RANGE', '_heypub_opt_paying_market_range');
// Address info
define('HEYPUB_OPT_PUBLICATION_ADDRESS','_heypub_opt_publication_address');
define('HEYPUB_OPT_PUBLICATION_CITY','_heypub_opt_publication_city');
define('HEYPUB_OPT_PUBLICATION_STATE','_heypub_opt_publication_state');
define('HEYPUB_OPT_PUBLICATION_ZIP','_heypub_opt_publication_zip');
define('HEYPUB_OPT_PUBLICATION_COUNTRY','_heypub_opt_publication_country');

// Info about the Page that will be created to house submissions, if needed.
define('HEYPUB_SUBMISSION_PAGE_TITLE','Submission Form');
define('HEYPUB_SUBMISSION_PAGE_REPLACER','[__HEYPUBLISHER_SUBMISSION_FORM_GOES_HERE__]');
define('HEYPUB_OPT_SUBMISSION_PAGE_ID','_heypub_opt_submission_page_id');
define('HEYPUB_OPT_SUBMISSION_GUIDE_ID','_heypub_opt_submission_guide_id');

// These keys are stored in the usermeta table and are NOT 'deleted' when we uninstall the plugin
// This way we can always track which users/posts have been affected by HP plugin
define('HEYPUB_USER_META_KEY_AUTHOR_ID','_heypub_user_meta_key_author_id');
define('HEYPUB_POST_META_KEY_SUB_ID','_heypub_post_meta_key_sub_id');

// Initiate the callbacks
add_action('admin_menu', 'RegisterHeyPublisherAdminMenu');

/**
* Load all of the plugin files
*/
require_once(HEYPUB_PLUGIN_FULLPATH.'include'.DIRECTORY_SEPARATOR.'HeyPublisherXML'.DIRECTORY_SEPARATOR.'HeyPublisherXML.class.php');
$hp_xml = new HeyPublisherXML;

// These files are required for basic functions
require_once(HEYPUB_PLUGIN_FULLPATH.'include'.DIRECTORY_SEPARATOR.'heypub-template-functions.php');

// Only need this pages if you're modifying the plugin
require_once(HEYPUB_PLUGIN_FULLPATH.'admin'.DIRECTORY_SEPARATOR.'heypub-main.php');
require_once(HEYPUB_PLUGIN_FULLPATH.'admin'.DIRECTORY_SEPARATOR.'heypub-options.php');
require_once(HEYPUB_PLUGIN_FULLPATH.'admin'.DIRECTORY_SEPARATOR.'heypub-uninstall.php');

// required for managing submissions
require_once(HEYPUB_PLUGIN_FULLPATH.'admin'.DIRECTORY_SEPARATOR.'heypub-submissions.php');


/**
*  Configure and Register the Admin Menu
*  Invoke the hook, sending function name
*/
function RegisterHeyPublisherAdminMenu(){
  global $hp_xml;
    $admin_menu = add_menu_page('HeyPublisher','HeyPublisher', 8, HEY_DIR, 'heypub_menu_main', HEY_BASE_URL.'images/heypub-icon.png');
    add_action("admin_print_styles-$admin_menu", 'HeyPublisherAdminHeader' );

  if ($hp_xml->is_validated) {
      // Submission Queue
      $admin_sub = add_submenu_page(HEY_DIR , 'HeyPublisher Submissions', 'Submissions', 'edit_others_posts', 'heypub_show_menu_submissions', 'heypub_show_menu_submissions');
      add_action("admin_print_styles-$admin_sub", 'HeyPublisherAdminHeader' );
      add_action("admin_print_scripts-$admin_sub", 'HeyPublisherAdminInit');
      // capture when a submission is published
      add_action('publish_post','heypub_publish_post');
      // capture when a submission is deleted from the posts
      add_action('delete_post','heypub_reject_post');
  }
    // Configure Options
    $admin_opts = add_submenu_page( HEY_DIR , 'Configure HeyPublisher', 'Plugin Options', 'manage_options', 'heypub_show_menu_options', 'heypub_show_menu_options');
    add_action("admin_print_styles-$admin_opts", 'HeyPublisherAdminHeader' );
    add_action("admin_print_scripts-$admin_opts", 'HeyPublisherAdminInit');

    // Uninstall Plugin
    $admin_unin = add_submenu_page( HEY_DIR , 'Uninstall HeyPublisher', 'Uninstall Plugin', 'manage_options', 'heypub_menu_uninstall', 'heypub_menu_uninstall');
    add_action("admin_print_styles-$admin_unin", 'HeyPublisherAdminHeader' );

}

function HeyPublisherAdminHeader() {
?>
  <!-- HeyPublisher Header -->
  <link rel='stylesheet' href='<?php echo HEY_BASE_URL; ?>include/css/heypublisher.css' type='text/css' />
<?php  
}
function HeyPublisherAdminInit() {
  wp_enqueue_script('heypublisher', WP_PLUGIN_URL . '/heypublisher-submission-manager/include/js/heypublisher.js',array('prototype')); 
}

/*
-------------------------------------------------------------------------------
Initialize / Upgrade
-------------------------------------------------------------------------------
*/

function heypub_init(){
  global $hp_xml;
  // Update/upgrade options!
  // Fresh install!
  if(get_option(HEYPUB_OPT_PLUGIN_VERSION_CURRENT) == false) {
    add_option(HEYPUB_OPT_PLUGIN_VERSION_CURRENT,HEYPUB_PLUGIN_BUILD_NUMBER);
    add_option(HEYPUB_OPT_PLUGIN_VERSION_DATE,HEYPUB_PLUGIN_BUILD_DATE);
    // new way of initializing
    $hp_xml->initialize_plugin();
  }

  // Initialize the option hashes
  // Set Defaults
  add_option(HEYPUB_OPT_PUBLICATION_NAME,get_bloginfo('name'));
  add_option(HEYPUB_OPT_PUBLICATION_URL,get_bloginfo('url'));
  add_option(HEYPUB_OPT_EDITOR_NAME,'Editor');
  add_option(HEYPUB_OPT_EDITOR_EMAIL,get_bloginfo('admin_email'));
  add_option(HEYPUB_OPT_EDITOR_EMAIL,get_bloginfo('admin_email'));
  add_option(HEYPUB_OPT_ACCEPTING_SUBS,'0');
  add_option(HEYPUB_OPT_READING_PERIOD,'0');
  add_option(HEYPUB_OPT_SIMULTANEOUS_SUMBMISSIONS,'0');
  add_option(HEYPUB_OPT_MULTIPLE_SUMBMISSIONS,'0');
  add_option(HEYPUB_OPT_PAYING_MARKET,'0');

  // placeholders for address info
  add_option(HEYPUB_OPT_PUBLICATION_ADDRESS,false);
  add_option(HEYPUB_OPT_PUBLICATION_CITY,false);
  add_option(HEYPUB_OPT_PUBLICATION_STATE,false);
  add_option(HEYPUB_OPT_PUBLICATION_ZIP,false);
  add_option(HEYPUB_OPT_PUBLICATION_COUNTRY,false);

  // Service Defaults
  add_option(HEYPUB_OPT_SVC_PUBLISHER,0);
  add_option(HEYPUB_OPT_SVC_ISVALIDATED,0);
  add_option(HEYPUB_OPT_SVC_USER_OID,0);
  add_option(HEYPUB_OPT_SVC_PUBLISHER_OID,0);

  // Update build number
  if(get_option(HEYPUB_OPT_PLUGIN_VERSION_CURRENT) != HEYPUB_PLUGIN_BUILD_NUMBER) {
    update_option(HEYPUB_OPT_PLUGIN_VERSION_LAST,get_option(HEYPUB_OPT_PLUGIN_VERSION_CURRENT));
    update_option(HEYPUB_OPT_PLUGIN_VERSION_CURRENT,HEYPUB_PLUGIN_BUILD_NUMBER);
  }
}
?>
