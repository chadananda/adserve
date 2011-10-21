<?php



function adserve_admin_settings() { 
  
  
  
  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );

  return $form;
}
function adserve_admin_settings_submit($form, &$form_state) {  
  $input = $form_state['input'];
 
}


function adserve_basic_report() {
  drupal_add_css(drupal_get_path('module', 'adserve') . '/adserve.css');
  drupal_add_js(drupal_get_path('module', 'adserve') . '/adserve.js');
  $result = "<h2> AdServe Activity </h2>";
  
  
  return $result;
}


function adserve_cronjob() { 
  $crossdog_last_checked = variable_get('crossdog_last_checked', strtotime("10 days ago")); 
  if ($crossdog_last_checked < strtotime("20 minutes ago")) {
    
    
    //variable_set('crossdog_last_checked', strtotime("now"));
  }
}
