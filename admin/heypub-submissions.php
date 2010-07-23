<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/* @todo filters: form ids, posts with edits, with no edits, by user, by IP  */

// Show the moderation menu
//
function heypub_show_menu_submissions() {
  global $wpdb, $wp_roles, $hp_xml;
  
  heypub_submission_handler();
  
}

function heypub_submission_actions($nounce,$inc_cancel=false) {
?>  
  <div class="alignleft actions">
  <select name="action">
    <option value="-1" selected="selected">-- Select Action --</option>
    <option value="accept">Accept Submission</option>
    <option value="review">Save for Later Review</option>
    <option value="reject">Reject Submission</option>
  </select>
  <input type="submit" value="Apply" name="doaction" id="doaction" />
  <?php wp_nonce_field($nounce); ?>
<?php
  if ($inc_cancel) {
    print '&nbsp;<a href="admin.php?page=heypub_show_menu_submissions">Cancel</a>';
  }
?>
  </div>
<?php  
}

/**
* Display the 'Local' description for this category - or the HP value if the internal mapping has not been set
*/ 
function heypub_get_display_category($id,$default) {
  global $hp_xml;
  // $id is the remote category id from HP
  // All categories for this install:
  $categories =  get_categories(array('orderby' => 'name','order' => 'ASC')); 
  $map = $hp_xml->get_category_mapping();
  $display = $default;
  if ($map) {
    foreach ($categories as $cat=>$hash) {
      if (($map["$id"]) && ($map["$id"] == $hash->cat_ID)) {
        $display = $hash->cat_name;
      }
    }
  }
  return $display;
}


function heypub_list_submissions() {  
  global $hp_xml;
  // This is a SimpleXML object being returned, with key the sub-id
  $subs = $hp_xml->get_recent_submissions();
  $form_post_url = sprintf('%s/%s',get_bloginfo('wpurl'),'wp-admin/admin.php?page=heypub_show_menu_submissions');
  $cats = $hp_xml->get_my_categories_as_hash();
  
?>
   <div class="wrap">
   <?php heypub_display_page_title('Submissions'); ?>
   <div id="hey-content">
    <p>Below are the most recent submissions sent to <b><i><?php bloginfo('name'); ?></i></b> by HeyPublisher writers.</p>
    <p>To read the submission, click on the title.  This will open the submission in a new window.</p>
    <p>To view the author's bio, click on the author's name.  The bio will display immediately below.  Click again on the author's name to hide their bio.</p>
    <p>If you are unable to see the author's bio it means the author did not provide one when submitting their work.</p>
    
<form id="posts-filter" action="<?php echo $form_post_url; ?>" method="post">
<table class="widefat post fixed" cellspacing="0" id='heypub_submissions'>
<thead>
	<tr>
  	<th id='heypub_sub_cb' class='checkbox'><input type="checkbox" onclick="heypub_auto_check(this,'posts-filter');"/></th>
  	<th>Title</th>
  	<th>Genre</th>
  	<th>Author</th>
  	<th>Email</th>
  	<th>Submission Date</th>
  	<th>Status</th>
	</tr>
</thead>

<tfoot />
<tbody>
<?php 
if(!empty($subs)) { 
  foreach($subs as $x => $hash) { 
    $count++; 
    $class = null;
    if(($count%2) != 0) { $class = 'alternate'; } 
    $url = sprintf('%s/wp-admin/admin.php?page=heypub_show_menu_submissions&show=%s',get_bloginfo('wpurl'),"$x");
    if ("$hash->status" == 'accepted') {
      // link to the editor screen
      $post_id = heypub_get_post_id_by_submission_id("$x");
      $url = sprintf('%s/wp-admin/post.php?action=edit&post=%s',get_bloginfo('wpurl'),$post_id);
    }
?>

    <tr id='post-<?php echo "$x"; ?>' class='<?php echo $class; ?>' valign="top">
      <th scope="row"><input type="checkbox" name="post[]" id='heypub_sub_id' value="<?php echo "$x"; ?>" /></th>
      <td class="heypub_list_title"><a href="<?php echo $url; ?>" title="Review Submission"><?php echo "$hash->title"; ?></a></td>
      <td><?php printf("%s", heypub_get_display_category($hash->category->id,$hash->category->name)); ?></td>
      <td class="heypub_list_title">
<?php if ($hash->author->bio != '') { ?>
      <a href="#" title="View Author Bio" onclick="heypub_click_toggle('post-bio-<?php echo "$x"; ?>');">
        <?php printf("%s %s", $hash->author->first_name, $hash->author->last_name); ?></a>
<?php } else { 
        printf("%s %s", $hash->author->first_name, $hash->author->last_name);
      }
?>        
      </td>
      <td><?php printf('<a href="mailto:%s?subject=Your%%20submission%%20to%%20%s">%s</a>',$hash->author->email,get_bloginfo('name'),$hash->author->email); ?></td>
      <td><?php printf("%s", $hash->submission_date); ?></td>
      <td><?php printf("%s", $hp_xml->normalize_submission_status($hash->status)); ?></td>
    </tr>
<?php if ($hash->author->bio != '') { ?>
    <tr id='post-bio-<?php echo "$x"; ?>' style='display:none;'  class='<?php echo $class; ?>'>
      <td colspan='2'>&nbsp;</td>
      <td colspan='3'><div class='heypub_author_bio_preview'><?php printf("%s", $hash->author->bio); ?></div></td>
      <td>&nbsp;</td>
    </tr>
<?php } 
  } 
} 
else {
?>
    <tr><td colspan=6 class='heypub_no_subs'>No Submissions At This Time</td></tr>
<?php 
} 
?>
</tbody>
</table>

<?php
if ( $page_links )
	echo "<div class='tablenav-pages'>$page_links_text</div>";
?>
         
<?php 
if(count($subs) > 0) { 
  heypub_submission_actions('heypub-bulk-submit');
} 
?>
  <br/>
  <h3>Bulk Actions Explained</h3>
  <div id='heypub_instructions'>
    <h4>You can perform the following bulk actions on the listed submissions:</h4>
    <table id='heypub_instructions_list'>
    <tr>
      <td>Accept Submission</td><td>Will copy submission over as a 'Post' in 'Pending' status.  Use this option if you intend to publish the submission.</td>
    </tr>
    <tr>
      <td>Save for Later Review</td><td>Marks the submission as under review in the HeyPublisher system, but does not copy it over into your Wordpress installation.  <b>If you do not accept simultaneous submissions, this also prevents the author from sending the work to another publisher while you are reviewing it.</b></td>
    </tr>
    <tr>
      <td>Reject Submission</td><td>Will inform the author that you do not intend to publish their work at this time and they are free to submit it to another publisher.</td>
    </tr>
    </table>
  </div>

    <br/>

    </form>
    </div> <!-- content -->
</div> <!-- wrap -->

<?php
}

function heypub_show_submission($id) {
  global $hp_xml;

  // Reading a submission marks it as 'read' in HeyPublisher
  if ($hp_xml->submission_action($id,'read')) {
    $sub = $hp_xml->get_submission_by_id($id);
    $form_post_url = sprintf('%s/%s',get_bloginfo('wpurl'),'wp-admin/admin.php?page=heypub_show_menu_submissions');
?>    
  <div class="wrap">
    <br/>
    <form id="posts-filter" action="<?php echo $form_post_url; ?>" method="post">
      <br clear='both'> 
      <?php heypub_display_page_title('Preview Submission: ' . $sub->title); ?>
      <div id="hey-content">
        <h3><?php printf('%s', $sub->category); ?> by <?php printf("%s %s", $sub->author->first_name, $sub->author->last_name); ?> 
        (<?php printf('<a href="mailto:%s?subject=Your%%20submission%%20to%%20%s">%s</a>',$sub->author->email,get_bloginfo('name'),$sub->author->email); ?>)</h3>
        <div id='heypub_submission_body'>
          <?php printf('%s',$sub->body); ?>
        </div>

        <h3>Author Bio</h3>
        <div class='heypub_author_bio_show'>
          <?php printf('%s',$sub->author->bio); ?>
        </div>
        <p>
          <small>Submitted on: <?php echo $sub->submission_date; ?></small>
        </p>
        <input type='hidden' name="post[]" value="<?php echo "$id"; ?>" />
        <?php heypub_submission_actions('heypub-bulk-submit',1); ?>
      </div>
    </from>
  </div>
<?php    
  }
}

function pluralize_submission_message($cnt) {
  if ($cnt == 1) {
    return '1 submission';
  } else {
    return sprintf('%s submissions',$cnt);
  }
}

// Read Handler - Marking these records for later review
function heypub_read_submission($req) {
  global $hp_xml;
  check_admin_referer('heypub-bulk-submit');  
  $post = $req[post]; 
  $cnt = 0;
  foreach ($post as $key) {
    if ($hp_xml->submission_action($key,'read')) {
      $cnt++;
    }
  }
  $message = sprintf('%s successfully saved for later review',pluralize_submission_message($cnt));
  return $message;
}

function heypub_get_post_id_by_submission_id($id) {
  global $wpdb;
  // $id is the HP post id
  $post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", HEYPUB_POST_META_KEY_SUB_ID,$id));
  if ($post_id) { return $post_id; }
  return false;
}

// This will return the HP key if the post id is found
function heypub_get_submission_id_by_post_id($post_id) {
  global $wpdb;
  // $id is the HP post id
  $hp_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s AND post_id = %s", HEYPUB_POST_META_KEY_SUB_ID, $post_id));
  if ($hp_id) { 
    return $hp_id;
  }
  return false;
}
// this will cause HP to run a reject action
function heypub_reject_post($post_id) {
  global $hp_xml;
  if ($hp_id = heypub_get_submission_id_by_post_id($post_id)) {
    $hp_xml->submission_action($hp_id,'rejected');
  }
}

function heypub_publish_post($post_id) {
  global $hp_xml;
  if ($hp_id = heypub_get_submission_id_by_post_id($post_id)) {
    $hp_xml->submission_action($hp_id,'published');
  }
  return true;
}

// Rejection Handler - these posts may or may not be in the db
function heypub_reject_submission($req) {
  global $hp_xml;
  check_admin_referer('heypub-bulk-submit');  
  $post = $req[post]; 
  $cnt = 0;
  foreach ($post as $key) {
    if ($hp_xml->submission_action($key,'rejected')) {
      $cnt++;
      // need to see if this post has been previously 'accepted'
      if ($post_id = heypub_get_post_id_by_submission_id($key)) {
        // we force deletes
        wp_delete_post( $post_id, true );
      }
    }
  }

  $message = sprintf('%s successfully rejected',pluralize_submission_message($cnt));
  return $message;
}

function heypub_create_or_update_author($a) {
  $user_id = false;
  if ($a) {
    // does this author already exist?  If so, find them.  Username = user->email
    $user_name = $a->email;
    // fetch the user id by username and/or email address
    $user_id = heypub_get_author_id_by_email( $user_name );
    if ( !$user_id ) {
    	$random_password = wp_generate_password( 12, false );
    	$user_id = wp_create_user( $user_name, $random_password, $user_name );
    } 
    // update the user's bio, too - if we have it.
  	heypub_update_author_info($user_id,'description',sprintf("%s",$a->bio));
    //  right now - this is the only unique key we will share with plugins.  OIDs coming soon...
  	heypub_update_author_info($user_id,HEYPUB_USER_META_KEY_AUTHOR_ID,sprintf("%s",$a->email));
    // And the user's first/last name if we have it
  	if ($a->full_name) {
  	  wp_update_user( array ('ID' => $user_id, 'display_name' => $a->full_name) ) ;
    	heypub_update_author_info($user_id,'first_name',sprintf("%s",$a->first_name));
    	heypub_update_author_info($user_id,'last_name',sprintf("%s",$a->last_name));
    }
  }
  return $user_id;
}

function heypub_create_or_update_post($user_id,$status,$sub) {
  global $hp_xml;
  $post_id = heypub_get_post_id_by_title("$sub->title",$user_id) ;
  $category = 1;  // the 'uncategorized' category
  $map = $hp_xml->get_category_mapping();
  // printf("<pre>Sub object looks like : %s</pre>",print_r($sub,1));
  $cat = sprintf("%s",$sub->category->id);
  if ($map[$cat]) {
    $category = $map[$cat]; // local id
  }
  // this piece does not exist - create it
  if (!$post_id) {
    $post = array();
    $post['post_title'] = $sub->title;
    $post['post_content'] = $sub->body;
    $post['post_status'] = $status;
    $post['post_author'] = $user_id;
    $post['post_category'] = array($category);  # this should always be an array.
    // printf("<pre>POST category  : %s</pre>",print_r($post[post_category],1));
    // Insert the post into the database
    $post_id = wp_insert_post( $post );
  }
  // ensure meta data is updated
  update_post_meta($post_id, HEYPUB_POST_META_KEY_SUB_ID, "$sub->id");
  return $post_id;
}

function heypub_get_post_id_by_title($title,$user_id){
  global $wpdb;
  $post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_author = %s",$title,$user_id));
  return $post_id;
}

function heypub_get_author_id_by_email($email) {
  global $wpdb;
  $user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_email= %s","$email"));
  return $user_id;
}

function heypub_update_author_info($uid,$key,$val) {
  global $wp_version;
  // The function changed in WP 3.0!!
  // Conver to an int
  $test = $wp_version+=0;
  if ($val) {
    if ($test >= 3) {
      update_user_meta($uid,$key,"$val");
    } else {
      update_usermeta($uid,$key,"$val");
    }	
  }
}

// Accept Handler - these posts may or may not be in the db already
function heypub_accept_submission($req) {
  global $hp_xml;
  check_admin_referer('heypub-bulk-submit');  
  $post = $req[post]; 
  $cnt = 0;
  foreach ($post as $id) {
    if ($hp_xml->submission_action($id,'accepted')) {
      $cnt++;
      $sub = $hp_xml->get_submission_by_id($id);
      if ($sub->author) {
        $user_id = heypub_create_or_update_author($sub->author);
        $post_id = heypub_create_or_update_post($user_id,'pending',$sub);
      }
    }
  }

  $message = sprintf('%s successfully accepted.  %s been moved to your Posts as well.',pluralize_submission_message($cnt),
  ($cnt > 1) ? "These works have" : "This work has" );
  return $message;
}

// Handle operations for this form
//
function heypub_submission_handler() {
  global $hp_xml;
  $message = "";

  // printf("<pre>request = %s</pre>",print_r($_REQUEST,1));
  if (!$hp_xml->is_validated) {
    heypub_not_authenticated();
    return;
  }
  if (isset($_REQUEST[show])) {
    heypub_show_submission($_REQUEST[show]);
    return;
  }
  elseif (isset($_REQUEST[action]) && ($_REQUEST[action] == 'reject')) {
    $message = heypub_reject_submission($_REQUEST);
  }
  elseif (isset($_REQUEST[action]) && ($_REQUEST[action] == 'review')) {
    $message = heypub_read_submission($_REQUEST);
  }
  elseif (isset($_REQUEST[action]) && ($_REQUEST[action] == 'accept')) {
    $message = heypub_accept_submission($_REQUEST);
  }

  if(!empty($message)) { ?>
    <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php 
  }
  heypub_list_submissions();
}

?>
