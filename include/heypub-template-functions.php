<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

add_filter('the_content', 'heypub_display_form');

/**
* Parse the content and if it matches our place-holder, display our form instead
*/
function heypub_display_form($content='') {
  
  
   if (preg_match(HEYPUB_SUBMISSION_PAGE_REPLACER, $content) > 0 ) {
      $sub_form = heypub_display_submission_form();
      $content = str_replace(HEYPUB_SUBMISSION_PAGE_REPLACER,$sub_form,trim($content));
   }
  return $content;
}

function heypub_display_submission_form() {
  if (get_option(HEYPUB_OPT_ACCEPTING_SUBS)) {
    $src = get_permalink(get_option(HEYPUB_OPT_SUBMISSION_PAGE_ID));
    $url = sprintf("%s/%s/submit/%s",HEYPUB_SVC_URL_BASE, HEYPUB_SVC_URL_SUBMIT_FORM, get_option(HEYPUB_OPT_SVC_PUBLISHER_OID));
    $style = sprintf("<link rel='stylesheet' href='%sinclude/css/heypublisher.css' type='text/css' />", HEY_BASE_URL);
    $css = get_bloginfo('stylesheet_url');
    $css = urlencode($css);
    $src = urlencode($src);
  $ret = <<<EOF
  $style
<iframe id='heypub_submission_iframe' src='$url?css=$css&orig=$src' frameborder='0' scrolling='vertical'></iframe>

EOF;
  }
  else {
    $ret = '<h3 id="heypub_not_accepting_submissions">We are currently not accepting submissions.<br/>Please check back later.</h3>';
  }
  return $ret;
}

?>