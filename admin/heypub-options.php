<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

function heypub_show_menu_options() {
  global $hp_xml;
  //   Possibly process form post
  heypub_update_options();
  
?> 
  <div class="wrap">
    <?php heypub_display_page_title('HeyPublisher Options'); ?>    
    <div id="hey-content">
    <form method="post" action="admin.php?page=heypub_show_menu_options">
<?php
  if(function_exists('wp_nonce_field')){ wp_nonce_field('heypub-save-options'); }

  //  if user is not validated, they must validate first
  if (!$hp_xml->is_validated) {
?>
    <h3>HeyPublisher Account Info</h3>
    <p>If your publication is <a href="http://heypublisher.com/publishers/search?category_id=0&keywords=<?php printf('%s',urlencode(get_option(HEYPUB_OPT_PUBLICATION_NAME))); ?>" target=_new>listed in HeyPublisher's database</a>, please enter your publication's name and URL below <i>exactly</i> as it appears on HeyPublisher.</p>
    <p>If your publication is not already in our database, tell us the name and URL of your publication (the defaults listed below are based upon your Wordpress settings).</p>
    <p><p><b>IMPORTANT:</b> Please provide an email address and desired password below.  We will use this information to create an 'administrator' account in our system, allowing you to manage your publication's listing from the <a href='http://heypublisher.com/'  target='_new'>HeyPublisher.com website</a>, as well as from this plugin.</p>
    
  <label class='heypub' for='heypub_pub_name'>Publication Name</label>
  <input type="text" name="heypub_user[pub_name]" id="heypub_pub_name" class='heypub' value="<?php echo get_option(HEYPUB_OPT_PUBLICATION_NAME); ?>" />
<br/>
  <label class='heypub' for='heypub_pub_url'>Publication URL</label>
  <input type="text" name="heypub_user[pub_url]" id="heypub_pub_url" class='heypub' value="<?php echo get_option(HEYPUB_OPT_PUBLICATION_URL); ?>" />
<br/>
    <label class='heypub' for='heypub_username'>Your Email Address</label>
    <input type="text" name="heypub_user[username]" id="heypub_username" class='heypub' value="<?php echo get_option(HEYPUB_OPT_EDITOR_EMAIL); ?>"/>
<br/>
  <label class='heypub' for='heypub_password'>Password</label>
  <input type="password" name="heypub_user[password]" id="heypub_password" class='heypub' autocomplete="off"
  />
  
<?php 
  }
  else {  // User is validated
    $cats = $hp_xml->get_my_categories_as_hash();
    $pub_types = $hp_xml->get_my_publisher_types_as_hash();
    $opts = array(
      'name' =>       get_option(HEYPUB_OPT_PUBLICATION_NAME),
      'url' =>        get_option(HEYPUB_OPT_PUBLICATION_URL),
      'editor' =>     get_option(HEYPUB_OPT_EDITOR_NAME),
      'email' =>      get_option(HEYPUB_OPT_EDITOR_EMAIL),
      'accepting_subs' =>     get_option(HEYPUB_OPT_ACCEPTING_SUBS),
      'reading_period' => get_option(HEYPUB_OPT_READING_PERIOD),
      'simu_subs' =>  get_option(HEYPUB_OPT_SIMULTANEOUS_SUMBMISSIONS),
      'multi_subs'  =>    get_option(HEYPUB_OPT_MULTIPLE_SUMBMISSIONS),  
      'paying_market' =>  get_option(HEYPUB_OPT_PAYING_MARKET),  
      'address' =>  get_option(HEYPUB_OPT_PUBLICATION_ADDRESS),  
      'city' =>  get_option(HEYPUB_OPT_PUBLICATION_CITY),  
      'state' =>  get_option(HEYPUB_OPT_PUBLICATION_STATE),  
      'zipcode' =>  get_option(HEYPUB_OPT_PUBLICATION_ZIP),  
      'country' =>  get_option(HEYPUB_OPT_PUBLICATION_COUNTRY)  ,
      'submission_page_id' => get_option(HEYPUB_OPT_SUBMISSION_PAGE_ID),
      'submission_guide_id' => get_option(HEYPUB_OPT_SUBMISSION_GUIDE_ID)
      );

      $link_url = 'admin.php?page=heypub_show_menu_options&action=create_form_page';
      if(function_exists('wp_nonce_url')){
        $link_url = wp_nonce_url($link_url,'create_form');
      }
      $cols = 2;
      
?>
    <h3>Publication Information</h3>
    <input type="hidden" name="heypub_opt[isvalidated]" value="1" />
    <p>How do you want your publication information presented at HeyPublisher.com?</p>
    <label class='heypub' for='heypub_pub_type'>Publication Type</label>
    <select name="heypub_opt[pub_type]" id="heypub_pub_type">
<?php
    foreach ($pub_types as $id=>$hash){
      printf('<option value="%s" %s>%s</option>',$hash[id],($hash[has]) ? "selected=selected" : null, $hash[name]);
    }
?>    
    </select>
  <br/>
    <label class='heypub' for='heypub_pub_name'>Publication Name</label>
    <input type="text" name="heypub_opt[pub_name]" id="heypub_pub_name" class='heypub' value="<?php echo $opts['name']; ?>" />
  <br/>
    <label class='heypub' for='heypub_pub_url'>Publication URL</label>
    <input type="text" name="heypub_opt[pub_url]" id="heypub_pub_url" class='heypub' value="<?php echo $opts['url']; ?>" />
  <br/>
    <label class='heypub' for='heypub_pub_editor'>Publication Editor</label>
    <input type="text" name="heypub_opt[pub_editor_name]" id="heypub_pub_editor" class='heypub' value="<?php echo  $opts['editor']; ?>" />
  <br/>
    <label class='heypub' for='heypub_pub_email'>Editor's Email Address</label>
    <input type="text" name="heypub_opt[pub_editor_email]" id="heypub_pub_email" class='heypub' value="<?php echo $opts['email']; ?>" />
  <p>Providing a physical address can help "local" writers find you more easily.</p>
    <label class='heypub' for='heypub_pub_address'>Street Address</label>
    <input type="text" name="heypub_opt[address]" id="heypub_pub_address" class='heypub' value="<?php echo $opts['address']; ?>" />
  <br/>
    <label class='heypub' for='heypub_pub_city'>City</label>
    <input type="text" name="heypub_opt[city]" id="heypub_pub_city" class='heypub' value="<?php echo $opts['city']; ?>" />
  <br/>
    <label class='heypub' for='heypub_pub_state'>State/Region</label>
    <input type="text" name="heypub_opt[state]" id="heypub_pub_state" class='heypub' value="<?php echo $opts['state']; ?>" />
  <br/>
    <label class='heypub' for='heypub_pub_zip'>Zip Code</label>
    <input type="text" name="heypub_opt[zipcode]" id="heypub_pub_zip" class='heypub' value="<?php echo $opts['zipcode']; ?>" />
  <br/>
    <label class='heypub' for='heypub_pub_country'>Country</label>
    <input type="text" name="heypub_opt[country]" id="heypub_pub_country" class='heypub' value="<?php echo $opts['country']; ?>" />
    
  <h3>Submission Form</h3>
<?php
    if (!$opts[submission_page_id]) {
?>
  <p>Select the page that will contain your submission form.</p>
  <p> If you haven't yet created this page, don't worry.  Just 
  <a href="<?php echo "$link_url"; ?>">CLICK HERE &raquo; </a> and we'll create the page now.  You can change the content and title of this page at any time.</p>
<?php
  } else {
?>
    <p>This is the page that contains your submission form.</p>
<?php    
  }
?>  
  <p>Ensure that the following code is contained somewhere in the page.</p>
  <blockquote><b><?php echo HEYPUB_SUBMISSION_PAGE_REPLACER; ?></b></blockquote>
  <p>This code will be replaced by the actual submission form when users go to the page.</p>
    <label class='heypub' for='heypub_submission_page'>Submission Form Page</label>
    <select name="heypub_opt[submission_page_id]" id="heypub_submission_page" class='heypub'> 
     <option value="">-- Select --</option> 
<?php 
      $pages = get_pages(); 
      foreach ($pages as $p) {
        printf('<option value="%s" %s>%s</option>', $p->ID, ($p->ID == $opts[submission_page_id]) ? 'selected=selected' : null, $p->post_title);
      }
?>
    </select>

      <h3>Submission Guidelines</h3>
      <p>If your submission guidelines are also posted online, select the page here.</p>
      <p>HeyPublisher will index your posted submission guidelines, making them searchable by writers world-wide.</p>
      <p>If you do not want writers to read your submission guidelines before submitting, leave this blank.</p>
      <label class='heypub' for='heypub_sub_guide'>Submission Guidelines Page</label>
      <select name="heypub_opt[submission_guide_id]" id="heypub_sub_guide" class='heypub'> 
       <option value="">-- NONE --</option> 
<?php 
      $pages = get_pages(); 
      foreach ($pages as $p) {
        printf('<option value="%s" %s>%s</option>', $p->ID, ($p->ID == $opts[submission_guide_id]) ? 'selected=selected' : null, $p->post_title);
      }
?>
      </select>
      
      <h3>Submission Criteria</h3>
      <p>What are the submissiion criteria for your publication?</p>
      <p>What are the genres of work you accept from writers? Do you accept simultaneous submissions?  Do you accept multiple submissions?</p>
<!-- Genres -->
      <label class='heypub' for='heypub_accepting_subs'>Currently Accepting Submissions?</label>
      <select name="heypub_opt[accepting_subs]" id="heypub_accepting_subs" onchange="heypub_select_toggle('heypub_accepting_subs','heypub_show_genres_list');">
      <option value='0' <?php if($opts['accepting_subs'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['accepting_subs'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>
      
      <div id='heypub_show_genres_list' <?php if(!$opts['accepting_subs']) { echo "style='display:none;' "; }?>>
      <!-- Content Specific for the Genres -->
      <h2>Select all genres your publication accepts.</h2>
      <table id='heypub_category_list' cellspacing='0' border='0' cellpadding='0'>
      <tr>
<?php
      for ($x=0;$x<$cols;$x++) {
        print "<th>Genre</th<th>Your Category</th>";
      }
?>      
      </tr>
      <tr>
<?php
      $cnt = 0;
      foreach ($cats as $id=>$hash) {
        if ($cnt % $cols == 0) { $cnt = 0; print "</tr><tr>"; }
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
      </tr></table>
      </div>    	

      <br clear='both'>

<!-- Reading Periods - Not yet Used
      <label class='heypub' for='heypub_reading_period'>Have a Reading Period?</label>
      <select name="heypub_opt[reading_period]" id="heypub_reading_period" onchange="heypub_select_toggle('heypub_reading_period','heypub_reading_period_list');">
      <option value='0' <?php if($opts['reading_period'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['reading_period'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>
      <div id='heypub_reading_period_list' <?php if(!$opts['reading_period']) { echo "style='display:none;' "; }?>>
      <!-- Content Specific for the Reading periods -->
      <p>This is content for the reading periods list- lots of stuff here</p>
      </div>    	
      
      <br clear='both'>
-->
      
<!-- Simu Subs -->
      <label class='heypub' for='heypub_simu_subs'>Accept Simultaneous Submissions?</label>
      <select name="heypub_opt[simu_subs]" id="heypub_simu_subs">
      <option value='0' <?php if($opts['simu_subs'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['simu_subs'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>

<br clear='both'>

<!-- Multi Subs -->
      <label class='heypub' for='heypub_multi_subs'>Accept Multiple Submissions?</label>
      <select name="heypub_opt[multi_subs]" id="heypub_multi_subs">
      <option value='0' <?php if($opts['multi_subs'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['multi_subs'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>
        
      <h3>Miscellaneous</h3>
      <p>Does your publication pay writers for publishing their works?</p>
      <label class='heypub' for='heypub_paying_market'>Paying Market?</label>
      <select name="heypub_opt[paying_market]" id="heypub_paying_market" onchange="heypub_select_toggle('heypub_paying_market','heypub_paying_market_range_display');">>
      <option value='0' <?php if($opts['paying_market'] == '0') echo "selected=selected"; ?>>No</option>
      <option value='1' <?php if($opts['paying_market'] == '1') echo "selected=selected"; ?>>Yes</option>
      </select>
      <div id='heypub_paying_market_range_display' <?php if(!$opts['paying_market']) { echo "style='display:none;' "; }?>>
      <!-- Content Specific for the Paying Markets -->
      <label class='heypub' for='heypub_paying_market_range'>Payment Amount?</label>
      <input type="text" name="heypub_opt[paying_market_range]" id="heypub_paying_market_range" class='heypub' value="<?php echo get_option(HEYPUB_OPT_PAYING_MARKET_RANGE); ?>" />
      <br/><small class='heypub-input-helper'>(ie: "Various", or "$100 for short fiction less than 5,000 words")</small>
      </div>
   

<br/>
<hr>
<?php
  }  // end of else case
?>  
    <br/>
    
    <table border="0"><tr>

    <td>
    <input type="hidden" name="save_settings" value="0" />
    <input type="submit" name="save_button" id="save_button" value="Save &raquo;" />
	</form>
    </td>

    <td>
    <form method="post" action="admin.php?page=heypub_show_menu_options">
    <input type="submit" name="refresh" value="Refresh" />
    </form>
    </td>

    </tr></table>
    </div>
   </div> 
   <?php
}

function heypub_get_category_mapping($id,$show) {
  global $hp_xml;
  // $id is the remote category id from HP
  // All categories for this install:
  $categories =  get_categories(array('orderby' => 'name','order' => 'ASC')); 
  $map = $hp_xml->get_category_mapping();
  
  $select = '<select id="chk_%s" name="heypub_opt[category_map][%s]" %s><option value=""> -- Select --</option>\n%s</select>';
  $options = array();
  foreach ($categories as $cat=>$hash) {
      $options[] = sprintf('<option value="%s" %s>%s</option>', $hash->cat_ID, ($map[$id] == $hash->cat_ID) ? 'selected=selected' : null, $hash->cat_name);
   }
  $ret = sprintf($select,$id,$id,($show) ? null : 'style="display:none;"',join("\n",$options));
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
  // printf("<pre>Pre Map => %s\nGenres => %s\nPost Map => %s\n\nPOST => %s</pre>",print_r($map,1), print_r($genres,1), print_r($result,1), print_r($post,1));
  $hp_xml->set_category_mapping($result);
  return;
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

    if (isset($_POST['heypub_user'])) {
      // need to validate username and password against HeyPublisher and if valid save isvalidated boolean
      $user = $_POST['heypub_user'];
      // store the username and password they provided
      update_option(HEYPUB_OPT_PUBLICATION_NAME,$user['pub_name']);
      update_option(HEYPUB_OPT_PUBLICATION_URL,$user['pub_url']);
      // Call out to the the webservice to validate
      if ($hp_xml->authenticate($user)) {
        update_option(HEYPUB_OPT_SVC_ISVALIDATED,date('Y-m-d'));
        update_option(HEYPUB_OPT_SVC_USER_OID,$hp_xml->user_oid);  
        update_option(HEYPUB_OPT_SVC_PUBLISHER_OID,$hp_xml->pub_oid);  
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
          update_option(HEYPUB_OPT_ACCEPTING_SUBS,$has_genres);
          update_option(HEYPUB_OPT_PUBLICATION_NAME,$pub['name']);
          update_option(HEYPUB_OPT_PUBLICATION_URL,$pub['url']);
          update_option(HEYPUB_OPT_EDITOR_NAME,$pub['editor']);
          update_option(HEYPUB_OPT_EDITOR_EMAIL,$pub['editor_email']);
          update_option(HEYPUB_OPT_MULTIPLE_SUMBMISSIONS,$pub['accepts_multiple_submissions']);
          update_option(HEYPUB_OPT_SIMULTANEOUS_SUMBMISSIONS,$pub['accepts_simultaneous_submissions']);
          if ($pub['paying_market_amount']) {
            update_option(HEYPUB_OPT_PAYING_MARKET_RANGE,$pub['paying_market_amount']);
            update_option(HEYPUB_OPT_PAYING_MARKET,'1');
          } else {
            update_option(HEYPUB_OPT_PAYING_MARKET,0);
          }
          update_option(HEYPUB_OPT_PUBLICATION_ADDRESS,$pub['address']);
          update_option(HEYPUB_OPT_PUBLICATION_CITY,$pub['city']);
          update_option(HEYPUB_OPT_PUBLICATION_STATE,$pub['state']);
          update_option(HEYPUB_OPT_PUBLICATION_ZIP,$pub['zipcode']);
          update_option(HEYPUB_OPT_PUBLICATION_COUNTRY,$pub['country']);
        }
      }
    }
    elseif (isset($_POST['heypub_opt']) && $_POST['heypub_opt']['isvalidated'] == '1') {
      // Processing a form post of Option Updates
      // Get options from the post
      $opts = $_POST['heypub_opt'];
      // update the category mapping
      heypub_set_category_mapping($opts);
    
      // update rest of vars
      update_option(HEYPUB_OPT_SUBMISSION_GUIDE_URL,$opts['guide']);
      update_option(HEYPUB_OPT_READING_PERIOD,$opts['reading_period']);
      update_option(HEYPUB_OPT_ACCEPTING_SUBS,$opts['accepting_subs']);
      update_option(HEYPUB_OPT_SIMULTANEOUS_SUMBMISSIONS,$opts['simu_subs']);
      update_option(HEYPUB_OPT_MULTIPLE_SUMBMISSIONS,$opts['multi_subs']);
      update_option(HEYPUB_OPT_PAYING_MARKET,$opts['paying_market']);
      if ($opts['paying_market'] == '1') {
        update_option(HEYPUB_OPT_PAYING_MARKET_RANGE,$opts['paying_market_range']);
      } else {
        update_option(HEYPUB_OPT_PAYING_MARKET_RANGE,false);  // need to ensure this gets zeored out
      }
      // Publication info
      update_option(HEYPUB_OPT_PUBLICATION_NAME,$opts['pub_name']);
      update_option(HEYPUB_OPT_PUBLICATION_URL,$opts['pub_url']);
      update_option(HEYPUB_OPT_EDITOR_NAME,$opts['pub_editor_name']);
      update_option(HEYPUB_OPT_EDITOR_EMAIL,$opts['pub_editor_email']);
      // Address info
      update_option(HEYPUB_OPT_PUBLICATION_ADDRESS,$opts['address']);
      update_option(HEYPUB_OPT_PUBLICATION_CITY,$opts['city']);
      update_option(HEYPUB_OPT_PUBLICATION_STATE,$opts['state']);
      update_option(HEYPUB_OPT_PUBLICATION_ZIP,$opts['zipcode']);
      update_option(HEYPUB_OPT_PUBLICATION_COUNTRY,$opts['country']);

      //  Did the user select a page to use as the submission form?
      update_option(HEYPUB_OPT_SUBMISSION_PAGE_ID,$opts[submission_page_id]);
      update_option(HEYPUB_OPT_SUBMISSION_GUIDE_ID,$opts[submission_guide_id]);
      // get the URL and send this value to HP
      $opts['guide'] = get_permalink($opts[submission_guide_id]);
      
      // now attempt to sync with HeyPublisher.com
      $success = $hp_xml->update_publisher($opts);
      if ($success) {
        $message = 'Your changes have been saved and syncronized with HeyPublisher.com!';
      } else {
        $message = 'Your changes have been saved locally, but have NOT been syncronized with HeyPublisher.com!';
      }
    }
  }
  elseif(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'create_form_page')) {
    print "we're in the refer<br>";
     check_admin_referer('create_form');
     $page_id = heypub_create_submission_page();
     // Ensure this id is saved to db
     update_option(HEYPUB_OPT_SUBMISSION_PAGE_ID,$page_id);
     $message = sprintf("A Submission Form page has been created. <a href='%s'>View page &raquo;</a><br/>",get_permalink($page_id));
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
  update_option(HEYPUB_OPT_SUBMISSION_PAGE_ID,$post_ID);
  return $post_ID;
}

?>
