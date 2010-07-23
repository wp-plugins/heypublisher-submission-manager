<?php
/**
* Script called by main menu option
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }


/**
* Helper to consistently get the page title and logo displayed.
* This function prints to the screen.
*/
function heypub_display_page_title($title) {
?>  
  <h2><?php echo $title; ?></h2>
  <div id='logo'><a href='http://www.heypublisher.com' target='_new' title='Visit HeyPublisher on the Web'><img src='<?php echo HEY_BASE_URL.'/images/heypub-logo.png'; ?>' border='0'><br/>Visit HeyPublisher.com</a><br/>
    <a href='mailto:<?php echo HEYPUB_FEEDBACK_EMAIL_VALUE; ?>'>Email Us</a></div>
<?php  
}

// Show the page
//
function heypub_menu_main()  {
	global $wpdb,$wp_roles, $hp_xml;

    // Initilise the plugin for the first time here. This gets called when you click the HeyPublisher link.
    // Doing it here means you can delete all the options!
    heypub_init();

	// get feed_messages
  require_once(ABSPATH . WPINC . '/rss.php');
  
  if(!isset($wp_roles)) {
  	$wp_roles = new WP_Roles();
  }
  $roles = $wp_roles->role_objects;

?>
  <div class="wrap">
    <?php heypub_display_page_title('HeyPublisher Overview'); ?>
    <div id="hey-content">
      <h3>Welcome</h3>

      <p>HeyPublisher allows you to accept unsolicited submissions from writers who are not registered users of your blog, magazine, or online Wordpress-powered site.</p>
      <p>HeyPublisher is the premier online site for writers to discover new writing markets.  By using this plugin you join a large and well-respected group of online publishers.  Best of all, you help ensure copyright protection for both the author and your site, as HeyPublisher provides independent 3rd party auditing of all submission transactions.</p>
      <p>As the publisher of <b><i><?php bloginfo('name'); ?></i></b> you control:
      <ul class='heypub-list'>
        <li>the reading periods during which you will accept submissions</li>
        <li>the genres of work you will accept</li>
        <li>whether or not to accept simultaneous submissions</li>
        <li>whether or not to accept previously published works</li>
        <li><i>... and much, much more ... </i></li>
      </ul>
      </p>

  	  <h3>Plugin Statistics</h3>
  <table class='list'>
    <tr>
      <th>Plugin Version</th>
      <th>Build #</th>
      <th>Build Date</th>
      <th>Plugin Validated</th>
    </tr>
    <tr>
      <td><?php echo HEYPUB_PLUGIN_VERSION; ?></td>
      <td><?php echo get_option(HEYPUB_OPT_PLUGIN_VERSION_CURRENT); ?></td>
      <td><?php echo get_option(HEYPUB_OPT_PLUGIN_VERSION_DATE); ?></td>
      <td>
<?php 
  if (!$hp_xml->is_validated) {
    echo "<a href='". heypub_get_authentication_url() . "'>CLICK HERE to VALIDATE</a>";
  } else {
    echo $hp_xml->is_validated;
  }
?>
      </td>
    </tr>
  </table>
  
  <h3>How to Control the Style of the Submission Form</h3>
  <p>This plugin uses your current theme's <!-- (<i><?php echo get_current_theme(); ?></i>) --> stylesheet to control the layout of the submission form.  If you want to customize how the submission form looks, please <a href="<?php echo HEYPUB_SVC_URL_STYLE_GUIDE; ?>" target=_new title='Click to open the style guide in a new window'>read the style guide</a>.</p> 
  
  </div>
<?php
}


/**
* Display the latest stats on the Dashboard 
* This functionality will be coming later...
*
* @pending
*/
function heypub_dashboard() {
  return false;
}

function heypub_not_authenticated($page) {
?>  
  <div class="wrap">
    <?php heypub_display_page_title('Not Authenticated!'); ?>
    <div id="hey-content">
      It appears you have not yet authenticated.  Please <a href='<?php heypub_get_authentication_url($page);?>'>CLICK HERE</a> to authenticate.</p>
    </div>
  </div>
<?php  
}

function heypub_get_authentication_url($page=false) {
  if ($page == FALSE) {
    $page = HEYPUB_PLUGIN_NOT_AUTHENTICATED_ACTION;
  }
  $url = sprintf('%s/%s?page=%s',get_bloginfo('wpurl'),'wp-admin/admin.php',$page);
  return $url;
}


/**
* Initialize the upgrade of the plugin
*/
function heypub_upgrade_notice() {
    $ver_cur = get_option(HEYPUB_OPT_PLUGIN_VERSION_CURRENT);
    if($ver_cur != false && $ver_cur != HEYPUB_PLUGIN_BUILD_NUMBER) { 
?>
        <div id="message" class="updated" ><p>You've recently upgraded HeyPublisher Submission Manager. To finalise the upgrade process, <a href="admin.php?page=heypub_show_menu_submissions">please visit the plugin configuration page</a>.</p></div>
<?php 
    }
}

?>