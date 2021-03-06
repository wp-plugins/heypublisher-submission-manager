<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

function heypub_show_menu_options() {
  global $hp_xml,$hp_base;
  //   Possibly process form post
  heypub_update_options();
  
?> 
<div class="wrap">
  <h2>HeyPublisher Options</h2>
  <div id='heypub_header'>
    <?php heypub_display_page_logo(); ?>
    <div id="heypub_content">
    <form method="post" action="admin.php?page=heypub_show_menu_options">
<?php
  if(function_exists('wp_nonce_field')){ wp_nonce_field('heypub-save-options'); }
  $opts = $hp_xml->config;
  // printf("<pre>Stored OPTS are: %s</pre>",print_r($opts,1));
  //  if user is not validated, they must validate first
  if (!$hp_xml->is_validated) {
?>
    <h3>HeyPublisher Account Info</h3>
    <p>If your publication is <a href="<?php echo HEYPUB_DOMAIN; ?>/publishers/search/all/none/<?php printf('%s',$hp_xml->searchable($opts['name'])); ?>" target=_new>listed in HeyPublisher's database</a>, please enter your publication's name and URL below <i>exactly</i> as it appears on HeyPublisher.</p>
    <p>If your publication is not already in our database, tell us the name and URL of your publication (the defaults listed below are based upon your Wordpress settings).</p>
    <p><p><b>IMPORTANT:</b> Please provide an email address and desired password below.  We will use this information to create an 'administrator' account in our system, allowing you to manage your publication's listing from the <a href='http://heypublisher.com/'  target='_new'>HeyPublisher.com website</a>, as well as from this plugin.</p>
    
  <label class='heypub' for='hp_name'>Publication Name</label>
  <input type="text" name="hp_user[name]" id="hp_name" class='heypub' value="<?php echo $opts['name']; ?>" />
<br/>
  <label class='heypub' for='hp_url'>Publication URL</label>
  <input type="text" name="hp_user[url]" id="hp_url" class='heypub' value="<?php echo $opts['url']; ?>" />
<br/>
    <label class='heypub' for='hp_username'>Your Email Address</label>
    <input type="text" name="hp_user[username]" id="hp_username" class='heypub' value="<?php echo $opts['editor_email']; ?>"/>
<br/>
  <label class='heypub' for='hp_password'>Password</label>
  <input type="password" name="hp_user[password]" id="hp_password" class='heypub' autocomplete="off"
  />
  
<?php 
  }
  else {  // User is validated
    $cats = $hp_xml->get_my_categories_as_hash();
    $pub_types = $hp_xml->get_my_publisher_types_as_hash();
    $link_url = 'admin.php?page=heypub_show_menu_options&action=create_form_page';
    if(function_exists('wp_nonce_url')){
      $link_url = wp_nonce_url($link_url,'create_form');
    }
    $cols = 2;
?>
  <!-- Navigation Block -->
  <div class="heypub-nav-tabs roundy">
    <div class="tabs-container">
<?php 
    echo $hp_base->tabbed_nav('p','Publication');
    echo $hp_base->tabbed_nav('c','Contact');
    echo $hp_base->tabbed_nav('s','Submission');
    echo $hp_base->tabbed_nav('n','Notification');
    echo $hp_base->tabbed_nav('m','Miscellaneous');
?>
  	</div>
  </div>
  
  
<!-- Publication Block -->
<div id='heypub_p_info'>
    <h3>Publication Information</h3>
    <input type="hidden" name="heypub_opt[isvalidated]" value="1" />
    <p>Tell us more about your publication.</p>
    <label class='heypub' for='hp_type'>Publication Type</label>
    <select name="heypub_opt[pub_type]" id="hp_type">
<?php
    foreach ($pub_types as $id=>$hash){
      printf('<option value="%s" %s>%s</option>',$hash[id],($hash[has]) ? "selected=selected" : null, $hash[name]);
    }
?>    
    </select>
  <br/>
    <label class='heypub' for='hp_name'>Publication Name</label>
    <input type="text" name="heypub_opt[name]" id="hp_name" class='heypub' value="<?php echo htmlentities(stripslashes($opts['name'])); ?>" />
  <br/>
    <label class='heypub' for='hp_url'>Publication URL</label>
    <input type="text" name="heypub_opt[url]" id="hp_url" class='heypub' value="<?php echo stripslashes($opts['url']); ?>" />
  <br/>
    <label class='heypub' for='hp_established'>Year Established</label>
    <input type="text" name="heypub_opt[established]" id="hp_established" class='heypub' value="<?php echo stripslashes($opts['established']); ?>" />
  <br/>
    <label class='heypub' for='hp_circulation'>Monthly Circulation (Visitors)</label>
    <input type="text" name="heypub_opt[circulation]" id="hp_circulation" class='heypub' value="<?php echo stripslashes($opts['circulation']); ?>" /> (000's)

  <h3>Social Media Information</h3>
    <p>Tell us how to find your Facebook Fan Page and follow your tweets on Twitter.</p>
    <br/>
      <label class='heypub' for='hp_facebook'>Facebook Fan Page URL</label>
      <input type="text" name="heypub_opt[facebook]" id="hp_facebook" class='heypub' value="<?php echo stripslashes($opts['facebook']); ?>" />
    <br/>
      <label class='heypub' for='hp_twitter'>Twitter ID</label>
      @<input type="text" name="heypub_opt[twitter]" id="hp_twitter" class='heypub_twitter' value="<?php echo stripslashes($opts['twitter']); ?>" />


</div>

<div id='heypub_c_info' style='display:none;'>
  <h3>Publication Contact Information</h3>
    <p>If a writer needs to contact you about a submission, where should we send the email?</p>
    <label class='heypub' for='hp_editor_name'>Publication Editor</label>
    <input type="text" name="heypub_opt[editor_name]" id="hp_editor_name" class='heypub' value="<?php echo  stripslashes($opts['editor_name']); ?>" />
  <br/>
    <label class='heypub' for='hp_editor_email'>Editor's Email Address</label>
    <input type="text" name="heypub_opt[editor_email]" id="hp_editor_email" class='heypub' value="<?php echo stripslashes($opts['editor_email']); ?>" />
  <p>Providing a physical address can help "local" writers find you more easily.</p>
    <label class='heypub' for='hp_address'>Street Address</label>
    <input type="text" name="heypub_opt[address]" id="hp_address" class='heypub' value="<?php echo stripslashes($opts['address']); ?>" />
  <br/>
    <label class='heypub' for='hp_city'>City</label>
    <input type="text" name="heypub_opt[city]" id="hp_city" class='heypub' value="<?php echo stripslashes($opts['city']); ?>" />
  <br/>
    <label class='heypub' for='hp_state'>State/Region</label>
    <input type="text" name="heypub_opt[state]" id="hp_state" class='heypub' value="<?php echo stripslashes($opts['state']); ?>" />
  <br/>
    <label class='heypub' for='hp_zipcode'>Zip Code</label>
    <input type="text" name="heypub_opt[zipcode]" id="hp_zipcode" class='heypub' value="<?php echo stripslashes($opts['zipcode']); ?>" />
  <br/>
    <label class='heypub' for='hp_country'>Country</label>
<?php
  require_once(HEYPUB_PLUGIN_FULLPATH.'include'.DIRECTORY_SEPARATOR.'country_list.php');
    echo '<select name="heypub_opt[country]" id="hp_country" class="heypub">';
    foreach ($countries as $key=>$val) {
      $sel = '';
      if ($key == $opts['country'] || $val == $opts['country']) { // we have some old stle country entries.
        $sel = "selected='selected'";
      }
      echo "<option value='$key' $sel>$val</option>";
    }
    echo '</select>';
?>
</div>    

<div id='heypub_s_info' style='display:none;'>

  <h3>Submission Form</h3>
<?php
    if (!$opts[sub_page_id]) {
?>
  <p>Select the page that will contain your submission form.</p>
  <p> If you haven't yet created this page, don't worry.  Just 
  <a href="<?php echo "$link_url"; ?>">CLICK HERE &raquo; </a> and we'll create the page now.  You can change the content and title of this page at any time.</p>
<?php
  } else {
?>
    <p>The submission form is where writers will submit their work.</p>
<?php    
  }
?>  
  <p>Ensure that the following code is contained somewhere in this page.</p>
  <blockquote class='heypub'><b><?php echo HEYPUB_SUBMISSION_PAGE_REPLACER; ?></b></blockquote>
  <p>This code will be replaced by the actual submission form when users go to the page.</p>
    <label class='heypub' for='hp_submission_page'>Submission Form Page</label>
    <select name="heypub_opt[sub_page_id]" id="hp_submission_page" class='heypub'> 
     <option value="">-- Select --</option> 
<?php 
      $pages = get_pages(); 
      foreach ($pages as $p) {
        printf('<option value="%s" %s>%s</option>', $p->ID, ($p->ID == $opts[sub_page_id]) ? 'selected=selected' : null, $p->post_title);
      }
?>
    </select>

      <h3>Submission Guidelines</h3>
      <p>If your submission guidelines are also posted online, select the page here.</p>
      <p>HeyPublisher will index your posted submission guidelines, making them searchable by writers world-wide.</p>
      <p>If you do not want writers to read your submission guidelines before submitting, leave this blank.</p>
      <label class='heypub' for='hp_sub_guide'>Submission Guidelines Page</label>
      <select name="heypub_opt[sub_guide_id]" id="hp_sub_guide" class='heypub'> 
       <option value="">-- NONE --</option> 
<?php 
      $pages = get_pages(); 
      foreach ($pages as $p) {
        printf('<option value="%s" %s>%s</option>', $p->ID, ($p->ID == $opts[sub_guide_id]) ? 'selected=selected' : null, $p->post_title);
      }
?>
      </select>
      
      <a name='simu_subs'> </a>
      <h3>Submission Criteria</h3>
      <p>What are the types of work you accept from writers? Do you accept simultaneous submissions?  Do you accept multiple submissions?</p>
<!-- Genres -->
      <label class='heypub' for='hp_accepting_subs'>Currently Accepting Submissions?</label>
      <select name="heypub_opt[accepting_subs]" id="hp_accepting_subs" onchange="heypub_select_toggle('hp_accepting_subs','heypub_show_genres_list');">
      <option value='0' <?php if($opts['accepting_subs'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['accepting_subs'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>
      
      <div id='heypub_show_genres_list' <?php if(!$opts['accepting_subs']) { echo "style='display:none;' "; }?>>
      <!-- Content Specific for the Genres -->
      <h2>Select all categories of work your publication accepts.</h2>
      <table id='heypub_category_list' cellspacing='0' border='0' cellpadding='0'>
				<thead>
      		<tr>
<?php
      for ($x=0;$x<$cols;$x++) {
        print "<th>Genre</th><th>Your Category</th>";
      }
?>      
      	</tr>
			</thead>
			<tfoot/>
			<tbody>
      	<tr>
<?php
      $cnt = 0;
      $count = 1;
      // printf("<pre>Cats: %s</pre>",print_r($cats,1));
      foreach ($cats as $id=>$hash) {
        $count++; 
        $class = null;
        if(($count%($cols*2)) != 0) { $class = 'alternate';} 
        if ($cnt % $cols == 0) { $cnt = 0; printf("</tr><tr class='%s'>",$class); }
        printf('
          <td>%s &nbsp; <input id="cat_%s"type="checkbox" name="heypub_opt[genres_list][]" value="%s" %s onclick="heypub_click_check(this,\'chk_%s\');"/></td>
          <td>%s</td>',
            $hash[name],$hash[id],$hash[id],($hash[has]) ? "checked=checked" : null,$hash[id],heypub_get_category_mapping($hash[id],$hash[has]));
        $cnt ++;
      }
      if ($cnt < $cols) {
        for ($x=($cols-$cnt);$x<$cols;$x++) {
          print "<td>&nbsp;</td>";
        }
      }
?>
      	</tr>
			</tbody>
		</table>
     </div>    	

     <br clear='both'>

<!-- Simu Subs -->
      <label class='heypub' for='hp_simu_subs'>Accept Simultaneous Submissions?</label>
      <select name="heypub_opt[simu_subs]" id="hp_simu_subs">
      <option value='0' <?php if($opts['simu_subs'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['simu_subs'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>

<br clear='both'>

<!-- Multi Subs -->
      <label class='heypub' for='hp_multi_subs'>Accept Multiple Submissions?</label>
      <select name="heypub_opt[multi_subs]" id="hp_multi_subs">
      <option value='0' <?php if($opts['multi_subs'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['multi_subs'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>

<br clear='both'>

<!-- Reprint Subs -->
      <label class='heypub' for='hp_reprint_subs'>Accept Reprints?</label>
      <select name="heypub_opt[reprint_subs]" id="hp_reprint_subs">
      <option value='0' <?php if($opts['reprint_subs'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['reprint_subs'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>
</div>    

<!-- Notification Options -->
<div id='heypub_n_info' style='display:none;'>
      <h3>Author Notification Options</h3>
      <p>By default, this plugin will send an email to the author when their submission transitions through each of the <a href='http://blog.heypublisher.com/docs/plugins/wordpress/submission-states/' target='_blank'>states in the submission cycle</a>.</p>
<p>If you want to supress sending any of these notifications, indicate so below by setting the value to 'No'.</p>
<p>Additionally, you can customize the emails sent to the author via the <a href='admin.php?page=heypub_response_templates' target=_top>Response Templates</a> screen.</p>
<?php
// We'll introduce this later - for now it's hard-coded to yes
// 
    // <br clear='both'>
      // <?php echo $hp_base->get_yes_no_checkbox('Submitted?','notify_submitted',$opts['notify_submitted'],'Sent when the writer first submits their work for consideration.'); 
?>
    
    <br clear='both'>
    <input type='hidden' name='notify_submitted' value='1'>
      <?php echo $hp_base->get_yes_no_checkbox('Read?','notify_read',$opts['notify_read'],'Sent when the submission is first read by an Editor.'); ?>
    <br clear='both'>
      <?php echo $hp_base->get_yes_no_checkbox('Under Review?','notify_under_consideration',$opts['notify_under_consideration'],'Sent when the submission is being held for consideration.'); ?>
    <br clear='both'>
      <?php echo $hp_base->get_yes_no_checkbox('Accepted?','notify_accepted',$opts['notify_accepted'],'Sent if the submission is Accepted for publication.');?>
    <br clear='both'>
      <?php echo $hp_base->get_yes_no_checkbox('Rejected?','notify_rejected',$opts['notify_rejected'],'Sent if the submission is Rejected by an Editor.');?>
    <br clear='both'>
      <?php echo $hp_base->get_yes_no_checkbox('Published?','notify_published',$opts['notify_published'],'Sent when a submission is Published.  Sent on the actual publication date if scheduled.');?>

</div>    

<!-- Payment Options -->
<div id='heypub_m_info' style='display:none;'>
        
      <h3>Payment Options</h3>
      <p>Does your publication pay writers for publishing their work?</p>
      <label class='heypub' for='hp_paying_market'>Paying Market?</label>
      <select name="heypub_opt[paying_market]" id="hp_paying_market" onchange="heypub_select_toggle('hp_paying_market','hyepub_paying_market_range_display');">>
      <option value='0' <?php if($opts['paying_market'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['paying_market'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>
      <div id='hyepub_paying_market_range_display' <?php if(!$opts['paying_market']) { echo "style='display:none;' "; }?>>
      <!-- Content Specific for the Paying Markets -->
      <label class='heypub' for='hp_paying_market_range'>Payment Amount?</label>
      <input type="text" name="heypub_opt[paying_market_range]" id="hp_paying_market_range" class='heypub' value="<?php echo stripslashes($opts['paying_market_range']); ?>" />
      <br/><small class='heypub-input-helper'>(ie: "Various", or "$100 for short fiction less than 5,000 words")</small>
      </div>

<!-- MISC -->
  <h3>Miscellaneous</h3>
  <p>Turn off HTML clean-up in submissions?  Select YES if you are getting strange characters in the body of the submission.<br/>  In most cases, however, you will want to leave this set to No.</p>
  <label class='heypub' for='hp_no_tidy'>Turn Off HTML Clean-Up?</label>
  <select name="heypub_opt[turn_off_tidy]" id="hp_turn_off_tidy">
  <option value='0' <?php if($opts['turn_off_tidy'] == '0') echo "selected=selected"; ?>>No</option>
  <option value='1' <?php if($opts['turn_off_tidy'] == '1') echo "selected=selected"; ?>>Yes</option>
  </select>
</div>
</div>
<?php
/*
  // Future : Download of original document is coming ....
  <p>Display link to download original document?  Select yes if you want your editors to have the ability to download the original Word document.  This link will be displayed in the side bar when viewing the submission.</p>
  <label class='heypub' for='hp_doc_download'>Display Download Link?</label>
  <select name="heypub_opt[display_download_link]" id="hp_doc_download">
  <option value='0' <?php if($opts['display_download_link'] == '0') echo "selected=selected"; ?>>No</option>
  <option value='1' <?php if($opts['display_download_link'] == '1') echo "selected=selected"; ?>>Yes</option>
  </select>
*/
?>
<br/>
<hr>
<?php
  }  // end of else case
?>  
    <br/>
    <div class="alignleft actions">
    	<input type="hidden" name="save_settings" value="0" />
    	<input type="submit" name="save_button" id="save_button" value="Save &raquo;" />
		</div>
	</form>
 </div>
</div> 
<?php
}

// 
function heypub_get_category_mapping($id,$show) {
  global $hp_xml, $hp_base;
  // $id is the remote category id from HP
  // All categories for this install:
  // $categories =  $hp_base->get_categories(); 
  $map = $hp_xml->get_category_mapping();
  $list = wp_dropdown_categories(
    array(
      'selected' => ($map[$id]) ? $map[$id] : 0, 
      'id' => "chk_$id", 
      'hide_empty' => 0, 
      'name' => "heypub_opt[category_map][$id]", 
      'orderby' => 'name', 
      'hierarchical' => true, 
      'echo' => 0,
      'show_option_none' => __('--- Select ---')
      )
    );
  $ret = sprintf('<div id="chk_%s" %s>%s</div>', $id, ($show) ? null : 'style="display:none;"', $list);  
  return $ret;
}

function heypub_set_category_mapping($post) {
  global $hp_xml;
  $result = array();
  if ($post[accepting_subs]) {
    $map = $post[category_map];
    $genres = $post[genres_list];
    foreach ($genres as $x) {
      if ($map[$x]) {
        $result[$x] = $map[$x];
      }
    }
  } 
  // printf("<pre>Category Mapping : %s</pre>",print_r($result,1));
  return $result;
}

/**
* Update all of the page options sent by the form post
*/
function heypub_update_options() {
   global $hp_xml;
   
  // printf("<pre>In heypub_update_options()\nREQ: %s\naction = %s</pre>",print_r($_REQUEST,1),$_REQUEST['action']); 
   $message = null;
  if(isset($_REQUEST['save_settings'])) {

      check_admin_referer('heypub-save-options');

    if (isset($_POST['hp_user'])) {
      // need to validate username and password against HeyPublisher and if valid save isvalidated boolean
      $user = $_POST['hp_user'];
      // store the username and password they provided
      $hp_xml->set_config_option('name',$user['name']);
      $hp_xml->set_config_option('url',$user['url']);
      // Call out to the the webservice to validate
      if ($hp_xml->authenticate($user)) {
        $hp_xml->set_install_option('is_validated',date('Y-m-d'));
        $hp_xml->set_install_option('user_oid',$hp_xml->user_oid);  
        $hp_xml->set_install_option('publisher_oid',$hp_xml->pub_oid);  
        $hp_xml->set_is_validated();  // ensures that this page load has correct value
        
        
        // Fetch Publisher INFO from Webservice and pre-populate the layout, if we can
        $pub = $hp_xml->get_publisher_info();
        $message = 'Account validation succeeded!<br/>You can now configure your account.';
        if ($pub) {
          
          $cats = $hp_xml->get_my_categories_as_hash();
          $has_genres = '0';
          foreach ($cats as $id=>$hash) {
            if ($hash[has]) { $has_genres = '1'; }
          }
          
          $message .= "<br/><br/>To help you get started we've pre-populated the form with information we already have.";
          $hp_xml->set_config_option_bulk($pub);
          // now only the boolean overrides
          $hp_xml->set_config_option('accepting_subs',$has_genres);
          
          if (!$pub['paying_market'] == '0') {
            $hp_xml->set_config_option('paying_market_range',null);
          } 
        }
      }
    }
    elseif (isset($_POST['heypub_opt']) && $_POST['heypub_opt']['isvalidated'] == '1') {
      // Processing a form post of Option Updates
      // Get options from the post
      $opts = $_POST['heypub_opt'];
      //  Bulk update the form post
      $hp_xml->set_config_option_bulk($opts);
      // update the category mapping
      $cats = heypub_set_category_mapping($opts);
      $hp_xml->set_config_option('categories',$cats);
      if ($cats) {
        $hp_xml->set_config_option('accepting_subs','1');
      }
      
      if (!$opts['paying_market']) {
        $hp_xml->set_config_option('paying_market_range',null);
      } 

      // get the URL for the sub guidelines
      $opts['guide'] = get_permalink($opts['sub_guide_id']);
      // get the URL for the sub form itself
      $opts['submission_url'] = get_permalink($opts['sub_page_id']);
      // Blog's RSS feed is:
      $opts['rss'] = get_bloginfo('rss2_url');
      // now attempt to sync with HeyPublisher.com
      $success = $hp_xml->update_publisher($opts);
      // fetch the info back because we want to store seo_url and other stats locally.
      $p = $hp_xml->get_publisher_info();
      if ($p) {
        $hp_xml->set_config_option('seo_url',$p[seo_url]);
        $hp_xml->set_config_option('homepage_first_validated_at',$p[homepage_first_validated_at]);
        $hp_xml->set_config_option('homepage_last_validated_at',$p[homepage_last_validated_at]);
        $hp_xml->set_config_option('guide_first_validated_at',$p[guide_first_validated_at]);
        $hp_xml->set_config_option('guide_last_validated_at',$p[guide_last_validated_at]);
      }
      if ($success) {
        $message = 'Your changes have been saved and syncronized with HeyPublisher.com!';
      } else {
        $message = 'Your changes have been saved locally, but have NOT been syncronized with HeyPublisher.com!';
      }
    }
  }
  elseif(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'create_form_page')) {
    // print "we're in the refer<br>";
     check_admin_referer('create_form');
     $page_id = heypub_create_submission_page();
     // Ensure this id is saved to db
     $hp_xml->set_config_option('sub_page_id',$page_id);
     $message = sprintf("A Submission Form page has been created for you. <a href='%s' target='_blank'>View page &raquo;</a><br/>",get_permalink($page_id));
  }
    // all actions lead here
    
    if ($message) {
?>
      <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php 
  } 
  return;
}

/**
* Create the 'Page' in Wordpress for displaying the HeyPublisher submission form
*/
function heypub_create_submission_page() {
  global $current_user;

  $title = HEYPUB_SUBMISSION_PAGE_TITLE;
  $content = HEYPUB_SUBMISSION_PAGE_REPLACER;

  // Create the page
  $post = array (
    "post_content"   => $content,
    "post_title"     => $title,
    "post_author"    => $current_user->ID,
    "post_status"    => 'publish',
    "post_type"      => "page"
  );
  $post_ID = wp_insert_post($post);
  return $post_ID;
}

?>
