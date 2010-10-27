<?php
/**
* HeyPublisher class is the root class from which all plugin functionality is extended
*
*/
class HeyPublisher {

  public function __construct() {

  }   

  public function __destruct() {

  }

  public function page_title_with_logo($title) {
    $ret = $this->page_title($title);
    $ret .= $this->page_logo();
    return $ret;
  }
  
  public function page_title($title) {
    return "<h2>$title</h2>";
  }
  
  public function page_logo() {
    global $hp_xml;
    $format = "<div id='heypub_logo'>%s</div>";
    $content = <<<EOF
  <a href='http://heypublisher.com' target='_blank' title='Visit HeyPublisher.com'>
    <img src='{$_CONSTANTS['HEY_BASE_URL']}/images/logo.jpg' border='0'>
    <br/>Visit HeyPublisher.com</a>
    <br/>
    <a href='mailto:{$_CONSTANTS['HEY_BASE_URL']}'>Email Us</a>
EOF;
    $seo = 'foo';
//  $seo = $hp_xml->get_config_option('seo_url');
    if ($seo) {
      $content .= <<<EOF
<hr>
<b><a target='_blank' href="$seo">See Your Site in Our Database</a></b>
EOF;
    }
    $ret = sprintf($format,$content);
    return $ret;
  }

  public function page_layout($content) {
    $ret = <<<EOF
<div class="wrap">
    $content
</div>
EOF;
    return $ret;
  }
  
  // This is a non-printing function.  Output will be returned as a string
  // Two input params : 
  // - the contextual publisher object
  // - the submission object
  public function other_publisher_link($obj,$sub) {
    $string_format = '<ul>%s</ul>';
    // loop through values in the object
    $all = '';
    foreach ($obj as $key=>$val) {
      $str = '';
      if ($val->url != '') {
        $str .= sprintf("<b><a target=_blank href='%s'>%s</a></b>",$val->url,$val->name);
      } else {
        $str .= sprintf("<b>%s</b>",$val->name);
      }
      if ($val->date != '') {
        $str .= sprintf("&nbsp;<small>[%s]</small>",$val->date);
      }
      if ($val->editor != '' && $val->email != '') {
        $str .= sprintf("<span>edited by <a href='mailto:%s?subject=Question about \"%s\" by %s %s'>%s</a></a></span>",
            $val->email,$sub->title,$sub->author->first_name, $sub->author->last_name,$val->editor);
      }
      $all .= sprintf('<li>%s</li>',$str);
    }
    $string = sprintf('<ul>%s</ul>',$all);
    return $string;
  }
  
  public function get_dashboard_stats() {
    global $hp_xml;
    if (!$hp_xml->is_validated) {
      $data = sprintf("<td colspan='4'><i>Plugin needs to be validated first &nbsp;&nbsp;<a href='%s'>CLICK HERE to VALIDATE</a></i></td>",
        heypub_get_authentication_url());
    } else {
      $p = $hp_xml->get_publisher_info();
      if ($p[total_open_subs]) {
        $p[total_open_subs] = $this->submission_summary_link($p[total_open_subs]);
    }
    $data = <<<EOF
<td class="first b">$p[total_subs]</td>
<td class='t'>Total Submissions Received</td>
<td class='b'>$p[total_open_subs]</td>
<td class='last t waiting'>Pending Review</td>
</tr>
<tr>
<td class="first b">$p[total_published_subs]</td>
<td class='t approved'>Accepted ($p[published_rate] %)</td>
<td class='b'>$p[total_rejected_subs]</td>
<td class='last t spam'>Rejected ($p[rejected_rate] %)</td>
EOF;
    }
    return $data;
  }
  
  // for version >= 3.0 stats are displayed in their own dashboard widget
  public function right_now_widget() {
    $data = $this->get_dashboard_stats();
    $str = <<<EOF
<div class='table' id='dashboard_right_now'>
<table>
  <tr class='first'>$data</tr>
</table>
</div>
EOF;
    return $str;
  }
    
  public function make_donation_link($text_only=false) {
    $format = "<a href='".HEYPUB_DONATE_URL."' target='_blank' title='Thank You for Donating to HeyPublisher'>%s</a>";
    if ($text_only) {
      $str = sprintf($format,'Make a Donation');
    } else {
      $str = sprintf($format,"<img id='heypub_donate' style='vertical-align:middle;' src='".HEY_BASE_URL."/images/donate.jpg' border='0'>");
    }
    return $str;
  }

  public function submission_summary_link($text='See All Submissions') {
    $str = sprintf("<a href='admin.php?page=heypub_show_menu_submissions'>%s</a>",$text);
    return $str;
  }
}