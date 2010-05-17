<?php
/**
* HeyPublisherXML class for publishing/parsing XML
*
*/
class HeyPublisherXML {

  var $svc_url = HEYPUB_SVC_URL_BASE;
  var $curl = false;
  var $error = false;
  var $user_oid = false;
  var $pub_oid = false;
  var $submission_status = array(
    'unread' => 'New',
    'read' => 'Under Review',
    'accepted' => 'Accepted for Publication',
    'rejected' => 'Rejected',
    'published' => 'Published');
  var $is_validated = false;

  var $config = array();
  var $install = array();
  /**
  * Ensure the CURL constructor is getting created/destroyed properly
  */
  public function __construct() {
    $this->curl = curl_init();
    $this->set_is_validated();
    $this->config = get_option(HEYPUB_PLUGIN_OPT_CONFIG);
    $this->install = get_option(HEYPUB_PLUGIN_OPT_INSTALL);
  }   

  public function __destruct() {
    curl_close($this->curl);
    if ($this->install) {
      update_option(HEYPUB_PLUGIN_OPT_INSTALL,$this->install);
    }
    if ($this->config) {
      update_option(HEYPUB_PLUGIN_OPT_CONFIG,$this->config);
    }
  }
  
  public function get_category_mapping() {
    if ($this->config[categories]) {
      return $this->config[categories];
    }
    else {
      return array();
    }
  }

  public function set_category_mapping($map) {
    $this->config[categories] = $map;
    return;
  }
  
  public function initialize_plugin() {
    $this->init_install_options();
    add_option(HEYPUB_PLUGIN_OPT_INSTALL,$this->install);
    $this->init_config_options();
    add_option(HEYPUB_PLUGIN_OPT_CONFIG,$this->config);
  }
  
  private function init_install_options(){
    $this->install = array(
      'version_last'    => null,
      'version_current' => null,
      'install_date'    => null,
      'is_validated'    => null,
      'user_oid'        => null,
      'publisher_oid'   => null
    );
  }

  private function init_config_options() {
    $this->config = array(
      'categories' => array()
    );
  }

  public function set_is_validated() {
    $this->user_oid = get_option(HEYPUB_OPT_SVC_USER_OID);
    $this->pub_oid = get_option(HEYPUB_OPT_SVC_PUBLISHER_OID);
    if ($this->user_oid && $this->pub_oid) { $this->is_validated = get_option(HEYPUB_OPT_SVC_ISVALIDATED); }
  }
  
  public function send($path,$post) {
    $return = false;
    $url = sprintf("%s/%s",$this->svc_url,$path);
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this->curl, CURLOPT_TIMEOUT, 4);
    curl_setopt ($this->curl, CURLOPT_HTTPHEADER, array(
        "Accept: application/xml",
        "Content-Type: application/xml; charset=utf-8",
        'Content-Length: ' . strlen($post),
        'Connection: close'));
    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);
    // Execute the request and also time the transaction
    $result = curl_exec($this->curl);
    // Check for errors
    if ( curl_errno($this->curl) ) {
      $this->error = 'HeyPublisher Service ERROR : ' . curl_error($this->curl);
    } 
    else {
     $http_code = (int)curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
     switch($http_code){
       case 200:
        $return = $result;
         break;
       default:
         $this->error = 'HeyPublisher POST ERROR Code : ' . $http_code;
         break;
     }
    }   
    if (FALSE != $this->error) {
      $trace=debug_backtrace();
      $this_func = array_shift($trace);
      $caller=array_shift($trace);
      $this->error = sprintf('%s<br/>BAD RETURN: %s()',$this->error,$caller['function']);
    }
    return $return;
  }

  /**
  * This is the only method which does not call prepare_request_xml - as we have a custom <account> section
  */
  function authenticate($user) {
    $return = false;
    // authentication is based upon username, password, and token
    $xml_ops = array(
      'token'         => HEYPUB_SVC_TOKEN_VALUE,
      'publishername' => get_option(HEYPUB_OPT_PUBLICATION_NAME),
      'url'           => get_option(HEYPUB_OPT_PUBLICATION_URL),
      'email'         => $user['username'],
      'password'      => $user['password']);

    $xml_parts = '';
    foreach($xml_ops as $key=>$val) {
      $xml_parts .= "<$key>".htmlentities($val)."</$key>";
    }

    $post = "<?xml version='1.0' encoding='UTF-8'?><request><account>$xml_parts</account></request>";
    $ret = $this->send(HEYPUB_SVC_URL_AUTHENTICATE,$post);
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    } 
    else {
      $xml = new SimpleXMLElement($ret);
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
      if ($xml->account->oid && $xml->publisher->oid) {
        $this->user_oid = sprintf('%s',$xml->account->oid);
        $this->pub_oid = sprintf('%s',$xml->publisher->oid);
        $return = true;  # calling code will need to get the oids out of the class directly
      }
      else {
        $err = $xml->error->message;
        if ($err) { 
          $this->error = "$err";
        } else {
          $this->error = 'Did not receive authentication from HeyPublisher.com';
        }
        $this->print_webservice_errors();
      }
    }
    return $return;
  }

  function update_publisher_categories($post) {
    $ret = null;
    if ($post[accepting_subs] && $post[genres_list]) {
      $cat_array = array();
      foreach ($post[genres_list] as $name => $id) {
        $cat_array[] = sprintf('<category>%s</category>', $id);
      }
      if (FALSE != $cat_array) {
        $ret = sprintf('<categories>%s</categories>',join('',$cat_array));
      }
    }
    return $ret;
  }

  function update_publisher_reading_period($post) {
    $bool = $this->boolean($post[reading_period]);
    if ($post[reading_period]) {
      $start = $post['start_date'];
      $end = $post['end_date'];
      $ret = "<reading_period><reading_start_date>$start</reading_start_date><reading_end_date>$end</reading_end_date></reading_period>";
    } else {
      $ret = "<reading_period>$bool</reading_period>";
    }
    return $ret;
  }
  /**
  * convert boolean vals into strings reading 'true' or 'false'
  */
  function boolean($val) {
    if (isset($val) and $val != FALSE) {
      return 'true';
    } else {
      return 'false';
    }
  }
  function update_publisher_paying_market($post) {
    $bool = $this->boolean($post[paying_market]);
    if ($post[paying_market]) {
      $val = $post[paying_market_range];
      $ret = "<paying_market><paying_market_amount>$val</paying_market_amount></paying_market>";
    } else {
      $ret = "<paying_market>$bool</paying_market>";
    }
    return $ret;
  }
  
  function prepare_request_xml($post,$suppress_publisher=false) {
    $account = $this->get_account_request_header();
    if (FALSE == $suppress_publisher) {
      $publisher = $this->get_publisher_request_header();
    }
    $ret = sprintf('<?xml version="1.0" encoding="UTF-8"?><request>%s%s%s</request>',$account,$publisher,$post);
    return $ret;
  }
  
  function get_publisher_request_header() {
    $ret = <<<EOF
    <publisher>
        <oid>$this->pub_oid</oid>
    </publisher>
EOF;
    return $ret;
  }

  function get_account_request_header() {
    $ret = <<<EOF
    <account>
        <oid>$this->user_oid</oid>
    </account>
EOF;
    return $ret;
  }

  function update_publisher($post) {
    $categories = $this->update_publisher_categories($post);
    $reading = $this->update_publisher_reading_period($post);
    $simulsubs = $this->boolean($post[simu_subs]);
    $multisubs = $this->boolean($post[multi_subs]);
    $accepting_subs  = $this->boolean(1);  # always true for now
    $paying = $this->update_publisher_paying_market($post);
    $post = <<<EOF
<publisher>
    <oid>$this->pub_oid</oid>
    <publishertype_id>$post[pub_type]</publishertype_id>
    <name>$post[pub_name]</name>
    <url>$post[pub_url]</url>
    <sub_guideline>$post[guide]</sub_guideline>
    <editor>$post[pub_editor_name]</editor>
    <editor_email>$post[pub_editor_email]</editor_email>
    <accepts_simultaneous_submissions>$simulsubs</accepts_simultaneous_submissions>
    <accepts_multiple_submissions>$multisubs</accepts_multiple_submissions>
    <now_accepting_submissions>$accepting_subs</now_accepting_submissions>
    <address>$post[address]</address>
    <city>$post[city]</city>
    <state>$post[state]</state>
    <zipcode>$post[zipcode]</zipcode>
    <country>$post[country]</country>
    $categories
    $reading
    $paying
</publisher>
EOF;

    $ret = $this->send(HEYPUB_SVC_URL_UPDATE_PUBLISHER,$this->prepare_request_xml($post,true));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    } 
    else {
      $xml = new SimpleXMLElement($ret);
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
      if ($xml->success->message) {
        $ret = $xml->success->message;
        $return = "$ret";
      }
      else {
        $err = $xml->error->message;
        if ($err) { 
          $this->error = "$err";
        } else {
          $this->error = 'Error updating publisher data at HeyPublisher.com';
        }
        $this->print_webservice_errors();
      }
    }
    return $return;
  }

function get_publisher_info() {
  $post = '';

  $ret = $this->send(HEYPUB_SVC_URL_GET_PUBLISHER,$this->prepare_request_xml($post));
  if (FALSE == $ret) {
    $this->print_webservice_errors();
  } 
  else {
    $xml = new SimpleXMLElement($ret);
    // printf("<pre>RAW XML = %s</pre>",htmlentities($ret));
    # this is an object, convert to string
    if ($xml->success->message) {
      foreach ($xml->publisher->children() as $x) {
        $name = $x->getName();
        $return["$name"] = "$x";
      } 
    }
    else {
      $err = $xml->error->message;
      if ($err) { 
        $this->error = "$err";
      } else {
        $this->error = 'Error retrieving publisher info from HeyPublisher.com';
      }
      $this->print_webservice_errors();
    }
  }
  return $return;
}


  function normalize_submission_status($val) {
    if ($this->submission_status["$val"]) {
      return $this->submission_status["$val"];
    } else {
      return 'Unknown';
    }
  }
  
  function get_recent_submissions() {
    $post = <<<EOF
<submissions>
    <sort>date</sort>
    <sort_direction>DESC</sort_direction>
    <filter>unread</filter>
</submissions>
EOF;

    $ret = $this->send(HEYPUB_SVC_URL_GET_SUBMISSIONS,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    } 
    else {
      // printf("<pre>RAW XML = %s</pre>",htmlentities($ret));
      $xml = new SimpleXMLElement($ret);
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
      if ($xml->success->message) {
        $cnt = $xml->success->records;
        if ("$cnt" > 0) {
          $hash = array();
          foreach ($xml->submission as $x) {
            $hash["$x->id"] = $x;
          }
        }
        if ($hash != FALSE) {
          $return = $hash;
        }
      }
      else {
        $err = $xml->error->message;
        if ($err) { 
          $this->error = "$err";
        } else {
          $this->error = 'Error updating publisher data at HeyPublisher.com';
        }
        $this->print_webservice_errors();
      }
    }
    return $return;
  }

  function get_submission_by_id($id) {
    $post = <<<EOF
<submission>
    <id>$id</id>
</submission>
EOF;

    $ret = $this->send(HEYPUB_SVC_READ_SUBMISSION,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    } 
    else {
      $xml = new SimpleXMLElement($ret);
      // printf("<pre>RAW XML = %s</pre>",htmlentities($ret));
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
      if ($xml->success->message) {
        $return = $xml->submission;
      }
      else {
        $err = $xml->error->message;
        if ($err) { 
          $this->error = "$err";
        } else {
          $this->error = 'Error retrieving submission for reading from HeyPublisher.com';
        }
        $this->print_webservice_errors();
      }
    }
    return $return;
  }


  function print_webservice_errors() {
?>
    <div id='heypub_error'>
      <h2>Error Encountered</h2>
      <p><?php echo $this->error; ?></p>
      <p><b><?php echo HEYPUB_PLUGIN_ERROR_CONTACT; ?></b></p>
    </div>
<?php
  }

  /**
  * Pass in the SimpleXML element object and the 'key' wanting to fetch
  */
  function get_attribute_value_by_name($elem,$key){
    foreach ($elem->attributes() as $a => $b) {
      if ($a == $key) {
        return $b;
      }
    }
    return false;
  }


  /**
  * Fetch the hash of 'all' publisher types, plus the publisher type associated with this publication suitable for making drop-down list with.
  */
  function get_my_publisher_types_as_hash() {
      $return = false;
      $post = <<<EOF
<publisher_types>
    <sort>name</sort>
    <sort_direction>ASC</sort_direction>
    <filter>both</filter>
</publisher_types>
EOF;
    $ret = $this->send(HEYPUB_SVC_URL_GET_PUB_TYPES,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    } 
    else {
      $xml = new SimpleXMLElement($ret);
      // printf("<pre>RAW XML = %s</pre>",htmlentities($ret));
      # this is an object, convert to string
        if ($xml->success->message) {
          // First get ALL of the possible categories
          foreach ($xml->all->publisher_type as $x) {
            $id = $this->get_attribute_value_by_name($x,'id');
            if ($id) {
              $return["$x"] = array('name' => "$x", 'id' => "$id");
            }
          } 
          // We man not yet have submission categories defined remotely (if this is an initial install) - so account for that.
          if ($xml->mine->publisher_type) {
            foreach ($xml->mine->publisher_type as $x) {
              $id = $this->get_attribute_value_by_name($x,'id');
              if ($id) {
                $return["$x"]['has'] = 1;
              }
            } 
          }
        }
        else {
          $err = $xml->error->message;
          if ($err) { 
            $this->error = "$err";
          } else {
            $this->error = 'Error getting publisher data from HeyPublisher.com';
          }
          $this->print_webservice_errors();
        }
      }
      ksort($return);
      // printf("<pre>Hash = %s</pre>",print_r($return,1));
      return $return;
  }

  /**
  * Fetch the hash of 'all' categories, plus the categories this publisher belongs to, and return as a hash
  * suitable for making checkboxes with.
  */
  function get_my_categories_as_hash() {
      $return = false;
      $post = <<<EOF
<categories>
    <sort>name</sort>
    <sort_direction>ASC</sort_direction>
    <filter>both</filter>
</categories>
EOF;
    $ret = $this->send(HEYPUB_SVC_URL_GET_GENRES,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    } 
    else {
      $xml = new SimpleXMLElement($ret);
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
        if ($xml->success->message) {
          // First get ALL of the possible categories
          foreach ($xml->all->category as $x) {
            $id = $this->get_attribute_value_by_name($x,'id');
            if ($id) {
              $return["$x"] = array('name' => "$x", 'id' => "$id");
            }
          } 
          // We man not yet have submission categories defined remotely (if this is an initial install) - so account for that.
          if ($xml->mine->category) {
            foreach ($xml->mine->category as $x) {
              $id = $this->get_attribute_value_by_name($x,'id');
              if ($id) {
                $return["$x"]['has'] = 1;
              }
            } 
          }
        }
        else {
          $err = $xml->error->message;
          if ($err) { 
            $this->error = "$err";
          } else {
            $this->error = 'Error getting publisher data from HeyPublisher.com';
          }
          $this->print_webservice_errors();
        }
      }
      ksort($return);
      // printf("<pre>Hash = %s</pre>",print_r($return,1));
      return $return;
  }

  // Reject the submissions
  function submission_action($id,$action) {
      $return = false;
      if (!$this->submission_status[$action]) {
        $this->error = sprintf('%s is an invalid action',$action);
        return $return;
      }
      // We'll add this in the next version
      // $msg = get_option(HEYPUB_OPT_MSG_REJECT);
      $msg = false;
      if ($msg) {
        $notify = sprintf('<notify_author><message><![CDATA[ %s  ]]></message></notify_author>', $msg);
      } else {
        $notify = '<notify_author>false</notify_author>';
      }
      $post = <<<EOF
<submission>
    <id>$id</id>
    <action>$action</action>
    $notify
</submission>
EOF;
    $ret = $this->send(HEYPUB_SVC_URL_RESPOND_TO_SUBMISSION,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    } 
    else {
      $xml = new SimpleXMLElement($ret);
      // printf("<pre>RAW XML = %s</pre>",htmlentities($ret));
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
        if ($xml->success->message) {
          $ret = $xml->success->message;
          $return = "$ret";
        }
        else {
          $err = $xml->error->message;
          if ($err) { 
            $this->error = "$err";
          } else {
            $this->error = 'Error updating submission status at HeyPublisher.com';
          }
          $this->print_webservice_errors();
        }
      }
      return $return;
  }
}
?>