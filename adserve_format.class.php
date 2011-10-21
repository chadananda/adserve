<?php


class adserve_format {
  
  // pass in an array of ads, returns HTML ad block
  function ad_block($ads, $format, $stub_url, $style='') {   
    $ad_info = self::ad_tempates($format);  
    foreach ($ad_info as $key=>$value) $replace_items['%' . $key] = $value;
    
    if (count($ads) >= $ad_info['ad_count']) {
      foreach($ads as $ad) { 
        $search = array_merge($replace_items, array( 
          '%title'       => $ad['display_title'], 
          '%text'        => $ad['display_text'], 
          '%url'         => $ad['display_url'],
          '%target_url'  => $stub_url . '?' . 'imp=' . $ad['impid'],
        )); 
        $ad_template = $style ? $ad_info['ad_styled'] : $ad_info['ad_default'];
        $formatted_ads[] = str_replace(array_keys($search), array_values($search), $ad_template);   
        if (count($formatted_ads) >= $ad_info['ad_count']) break; 
      } 
      //drupal_set_message("<pre>". print_r($formatted_ads, 1) . "</pre>"); 
      // wrap it up in a little box
      $search = array( 
        '%ads' => implode("\n", $formatted_ads),
        '%format' => $format,
        '%style' => $style,
      ); 
      $box_template =  $style ? $ad_info['box_styled'] : $ad_info['box_default'];
      $box = str_replace(array_keys($search), array_values($search), $box_template); 
      return $box;
    }
  }
  
  // pass in an array of ads, returns JS ad block
  function ad_block_js($ads, $stub_url, $format, $style='') {
    $result = '';
    $ad_block = self::ad_block($ads, $stub_url, $format, $style); 
    //$result .= "<script>\n";
    $result .= "  var adserver_ad = " . json_encode($ad_block) . ';' . "\n";  
    $result .= "  document.write(adserver_ad); \n";
    //$result .= "</script>";
    return $result;
  }
  
  /*
   * 
Leaderboard (728 x 90) - 2 or 4 ads inline - url at end
Banner (468 x 60) - 2 ads block, no url
Half Banner (234x60) - one ad block, no url
Button (125x125) - one ad block
Skyscraper (120x600) - four ads block
Wide Skyscraper (160x600) - four ads block
Small Rectangle (180x150) - one ad block
Vertical Banner (120 x 240) - two ads block
Small Square (200 x 200) - two ads block
Square (250 x 250) - three ads block
Medium Rectangle (300 x 250) - four ads block, url at bottom
Large Rectangle (336 x 280) - four ads block, url on title
  */
  function ad_tempates($format = 'leaderboard') {
    $styles = array(
      'leaderboard' => array( // Leaderboard (728 x 90) - 2 or 4 ads inline - url at end
         'width' => 728,
         'height' => 90,
         'ad_count' => 4,
         'title_style' => 'color:#1122CC; text-decoration:underline; font-size:16px; font-weight:400; line-height:15.5px;',
         'text_style' => 'font-size:13px; font-weight:400; color:#222222; line-height:16px;',  
         'url_style' => 'font-size:13px; font-weight:300; color:#009933;',  
         
         'box_default' => '<div class="ad" style="position:absolute; width:728px; height:90px; padding:2px;">%ads</div>',
         'box_styled' => '<div class="ad %format %style" style="position:absolute">%ads</div>',
         
         'ad_default' => '<div style="padding:1px; margin:0; display:block;"><a href="%target_url">'.
                      '<span style="%title_style">%title</span> '.
                      '<span style="%text_style">%text</span> '.
                      '<span style="%url_style">%url</span></a></div>',
         'ad_styled'  => '<div class="ad_item"><a href="%target_url"><span class="title">%title</span> '.
                      '<span class="text">%text</span> <span class="urle">%url</span></a></div>',            
                      
                      
       ), 
      'single' => array( // Leaderboard (728 x 90) - 2 or 4 ads inline - url at end
         'width' => 728,
         'height' => 25,
         'ad_count' => 1,
         'title_style' => 'color:#1122CC; text-decoration:underline; font-size:16px; font-weight:400; line-height:15.5px;',
         'text_style' => 'font-size:13px; font-weight:400; color:#222222; line-height:16px;',  
         'url_style' => 'font-size:13px; font-weight:300; color:#009933;',  
         
         'box_default' => '<div class="ad" style="position:absolute; width:728px; height:25px; padding:2px;">%ads</div>',
         'box_styled' => '<div class="ad %format %style" style="position:absolute">%ads</div>',
         
         'ad_default' => '<div style="padding:1px; margin:0; display:block;"><a href="%target_url">'.
                      '<span style="%title_style">%title</span> '.
                      '<span style="%text_style">%text</span> '.
                      '<span style="%url_style">%url</span></a></div>',
         'ad_styled'  => '<div class="ad_item"><a href="%target_url"><span class="title">%title</span> '.
                      '<span class="text">%text</span> <span class="urle">%url</span></a></div>',            
                      
                      
       ), 
       
    );  
    
    
    return $styles[$format];
    //else drupal_set_message("Format {$format} not found in styles", 'warning');
  }
  
  function ad_count($format) {
    $template = self::ad_tempates($format);
    return $template['ad_count'];
  }
  
  
   
  
}
