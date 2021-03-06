// $Id: heypublisher.js 95 2010-05-07 23:14:18Z dimaxsvn $
// 
// Javascript library for HeyPublisher Wordpress plugin
// 
// Copyright (c) 2010 - Loudlever, Inc.
// Author - Richard Luck <richard.luck@loudlever.com>
// 
// This Library assumes Prototype is installed.

function heypub_auto_check(src,form) {
  var checked = false;
  if ($(src).checked == true) { checked = true; }
	for (var i = 0; i < document.getElementById(form).elements.length; i++) {
	  document.getElementById(form).elements[i].checked = checked;
	}
}

function heypub_toggle(chk,div) {
  // alert('input = checkbox id '+chk+' and div '+div);
  if ($(chk).checked == true) {
    $(div).show();
  } else {
    $(div).hide();
  }
  
  return false;
}

function heypub_select_toggle(sel,div) {
  if ($(sel).value == '1') {
    $(div).show();
  } else {
    $(div).hide();
  }
  return false;
}

function heypub_click_toggle(div) {
 if ($(div).visible()) {
   $(div).hide();
 } else {
   $(div).show();
 }
 return false;
  
}

function heypub_click_check(src,dest) {
  // alert("passed in " + $(src).id + " and looking for " + dest);
  if ($(src).checked == true) {
    $(dest).show();
  } else {
    $(dest).hide();
  }
}

function heypub_toggle_tabs(on) {
  var div = "heypub_"+on+"_info";
  var tab = "heypub_"+on+"_tab";
  var keys = new Array('p','c','s','n','m');
	for (var i = 0; i < keys.length; i++) {
    if (on != keys[i]) {
      if ($("heypub_"+keys[i]+"_info")) {
        $("heypub_"+keys[i]+"_info").hide();
      }
      if ($("heypub_"+keys[i]+"_tab")) {
        $("heypub_"+keys[i]+"_tab").removeClassName('heypub-tab-pressed');
      }    
    } 
  }
  $(div).show();
  $(tab).addClassName('heypub-tab-pressed');
}

