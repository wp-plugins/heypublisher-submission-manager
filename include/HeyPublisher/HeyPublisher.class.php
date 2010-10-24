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

}
