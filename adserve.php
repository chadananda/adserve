<?php

/*
 * This stub generated a JS ad as well as records clicks without waking Drupal 
 *
 */
 
 /*
  * user inserts token like [adserve:format:(op)styleclass]
  *  Note: if stylclass is requested, it will be saved with image impression and no inline formatting 
  * will be added (except width and height)
  *  Note: also save format with impression, that way we can measure ctr across formats and styles
  * 
  * drupal translates it to a js script request like:
  * <div class="adserver leaderboard styleclass">
  *  <script type="text/javascript"
  *   src=".../adserver.php?p=234&f=leaderboard&s=&url="></script> 
  * </div> 
  * 
  */
 
 $nid = _adserve_pgid(); 
 $ip = _adserve_ip();
 $style = _adserve_style();
 $format = _adserve_format();
 $pgurl = _adserve_pgurl();
 $stub_url = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
 $force_adid = _adserve_adid();

 $impid = _adserve_impid();
//  echo "nid: $nid, ip: $ip, style: $style, format: $format, force_adid: $force_adid <br> pgurl: $pgurl <br> stub_url: $stub_url"; exit;
 $is_click = !$nid && $impid;



if ($is_click) {
  _adserve_drupal_bootstrap_db(); 
  // look up the impression  
  $imp = db_query('SELECT * FROM {adserve_impression} WHERE impid=:impid', array(':impid' => $impid))->fetchAssoc();
  $ad = db_query('SELECT * FROM {adserve_ad} WHERE adid=:adid', array(':adid' => $imp['adid']))->fetchAssoc();
  // sent redirect message back to browser 
  _adserve_redirect_and_continue($ad['url']);
  // if impression already clicked, just about
  if ($imp['clicked']) exit;
  // if IP does not match, something is fishy, abort
  if ($imp['ip'] != $ip) exit;
  // this impression is too stale, abort
  if ($imp['imp_date'] < strtotime('-1 hour')) exit;
  // if IP has clicked too recently, this might be automated, abort
  if(db_query('SELECT impid FROM {adserve_impression} WHERE clicked>0 AND imp_date>:recent', 
    array(':recent' => strtotime('-5 minute')))->FetchField()) exit; 
  // ****************
  // update impression record to mark it clicked
  db_update('adserve_impression')
    ->fields(array(
      'clicked' => 1, 
      ))
    ->condition('impid', $impid)
    ->execute();  
  // now update ad total_clicks, total_ctr etc. 
  $total_clicks = db_query('SELECT count(*) FROM {adserve_impression} WHERE clicked>0 AND imp_date>:from',
    array(':from' => strtotime('-90 day')))->fetchField();
  $total_impressions = db_query('SELECT count(*) FROM {adserve_impression} WHERE imp_date>:from',
    array(':from' => strtotime('-90 day')))->fetchField();
  $total_ctr = round($total_clicks / $total_impressions * 100, 2);
  db_update('adserve_ad')
    ->fields(array(
      'total_ctr' => $total_ctr, 
      'total_clicks' => $total_clicks,
      ))
    ->condition('adid', $ad['adid'])
    ->execute();  
}
else { // this is an impression, return javascript ad
  if (!$format) $format = 'leaderboard';
  _adserve_drupal_bootstrap_db(); 
  
  // pick number of ads based on template   
  include_once('adserve_format.class.php');
  $ad_count = adserve_format::ad_count($format);
  // pick the top ads based on a weighted randomized rotation
  $ads = _adserve_random_top_ads($ad_count, $force_adid); 
 
  // Record or fetch pgid and impid 
  if (!$force_adid) {
   $pgid = _adserve_get_pgid($pgurl, $nid);
   //echo "pgid: $pgid <br>";
   foreach ($ads as $key=>$ad) {
     $impid =  _adserver_ad_impression($ad, $pgid, $ip, $format, $style); 
     $ads[$key]['impid'] = $impid;
     //echo "impid: $impid <br>";
   }
  }
  //exit;
   
  // build the ad block
  $block = adserve_format::ad_block($ads, $format, $stub_url, $style);   
  //echo "<textarea style='width:100%; height: 300px'>{$block}</textarea>";  exit;
  
  _adserve_output_jsonp($block); 
}  

function _adserve_output_jsonp($data){
 header('content-type: Access-Control-Allow-Origin: *');
 header('content-type: Access-Control-Allow-Methods: GET');
 header('Cache-Control: no-cache, must-revalidate');
 //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
 //header('Content-type: application/json');  
 header('Content-Type: text/javascript; charset=UTF-8');
 echo  'adserveCall' . '(' . json_encode(array(
   'data' => $data,
   'id'   => _adserve_id(), 
 )) .');';  
 exit;
}

function _adserve_output_js($js){
  // header("Content-type: application/x-javascript");
 header('Content-Type: text/javascript; charset=UTF-8');
 echo  $js;  
 exit;
}

/*  TODO: figure out how to get weighted randomized results
 * 
 */
function _adserve_random_top_ads(&$count, $force_adid=0) { 
  // figure out how to pick top ads but randomize a little 
  if ($force_adid) { $ads[] = db_query('SELECT * From {adserve_ad} WHERE adid=:adid', 
     array(':adid' => $force_adid))->fetchAssoc();  
  }
  else { 
    // for testing, we'll just grab the ones with the highest CTR // LIMIT :ad_count, 
    //  array(':ad_count' => $ad_count)
    $result = db_query('SELECT * FROM {adserve_ad} ORDER BY total_ctr DESC'); 
    foreach ($result as $ad) $ads[] = get_object_vars($ad);
    // 80% of the time we show the top ads. 20% of the time we just randomize
    // this gives new and other ads an occasional chance to shine while normally showing
    // the best performing ads.
    if (rand(1,100)<20) shuffle($ads);  
  } 
  if (count($ads)>$count) $ads = array_slice($ads, 0, $count);
   else while (count($ads)<$count) $ads[] = $ads[array_rand($ads)];   
  $count = count($ads);
  return $ads;
}

function  _adserve_get_pgid($pgurl, $nid) { 
  $domain = parse_url($pgurl, PHP_URL_HOST);
  $path = parse_url($pgurl, PHP_URL_PATH);  
  if (!$domain || !$path) return 0;
  
  $pgid = db_query("SELECT pgid FROM {adserve_page} WHERE domain=:domain AND path=:path",
    array(":domain" => $domain, ":path" => $path))->FetchField();
  if ($pgid) return $pgid;  
     
  $pgid = db_insert('adserve_page')
      ->fields(array(
        'domain' => $domain,
        'path' => $path,
        'nid' => $nid,
      ))
      ->execute();  
  return $pgid;  
}

function _adserver_ad_impression($ad, $pgid, $ip, $format, $style) {
 if (!$pgid) return 0;
 if (!$style) $style = 'default';
 // if same request just came on this page, from this user, give same impid
 $impid = db_query("SELECT impid FROM {adserve_impression} WHERE ".
   " adid=:adid AND ip=:ip AND format=:format AND style=:style AND pgid=:pgid AND ".
   " imp_date>:from_date ", array(
     ':adid'      => $ad['adid'],
     ':ip'        => $ip,
     ':format'    => $format,
     ':style'     => $style,
     ':pgid'      => $pgid,
     ':from_date' => strtotime("- 3  minute"),
   ))->FetchField();
 if ($impid) return $impid; 
 // otherwise, add it
 $impid = db_insert('adserve_impression')
  ->fields(array(
    'adid' => $ad['adid'],
    'pgid' => $pgid,
    'imp_date' => strtotime('now'),
    'format' => $format,
    'style' => $style,
    'ip' => $ip, 
  ))
  ->execute(); 
  return $impid;  
}


/*
 * Tools  ============================================
*/


function _adserve_drupal_bootstrap_db() {
  if (!$depth = count(explode('/', substr(getcwd(), strpos(getcwd(), '/sites/', 0)+1)))) return FALSE;  
  chdir(str_repeat('../', $depth)); 
  define('DRUPAL_ROOT', getcwd());
  require_once DRUPAL_ROOT . '/includes/bootstrap.inc'; 
  require_once DRUPAL_ROOT . '/includes/common.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);   
  return TRUE;
} 

function _adserve_pgid() {
  $pgid = (int) urldecode($_GET['p']);
  if ($pgid>0) return $pgid;
}

function _adserve_impid() {
  $impid = (int) urldecode($_GET['imp']);
  if (($impid>0) && ($impid<1000000000)) return $impid; // sanity check
}

function _adserve_ip() {
  $ip = trim($_SERVER['REMOTE_ADDR']);
  $ip = sprintf("%u", ip2long($ip));
  return $ip;
}

function _adserve_style() {
  $style = urldecode($_GET['s']);
  //if ((strlen($style>2) && (strlen($style)<30))) 
  return $style; // sanity check
}

function _adserve_format() {
  $format = trim(strtolower(urldecode($_GET['f'])));
  //include_once('adserve_format.class.php');
  //if (adserve_format::ad_tempates($format)) 
  return $format; 
}

function _adserve_pgurl() {
  $pgurl = trim(strtolower(urldecode($_GET['url']))); 
  //if (valid_url($url, TRUE)) return $pgurl; 
  // validate
  return $pgurl;
}

function _adserve_adid() {
  $adid = (int) trim(strtolower(urldecode($_GET['adid']))); 
  //if (valid_url($url, TRUE)) return $pgurl; 
  // validate
  return $adid;
}

function _adserve_id() {
  $adid = trim(strtolower(urldecode($_GET['id'])));  
  return $adid;
}
function _adserve_callback() {
  $callback = trim(urldecode($_GET['callback']));  
  return $callback;
}

function _adserve_redirect_and_continue($url) {
  header( "Location: ".$url ) ;
  ob_end_clean(); //arr1s code
  header("Connection: close");
  ignore_user_abort();
  ob_start();
  header("Content-Length: 0");
  ob_end_flush();
  flush(); // end arr1s code
  session_write_close(); // as pointed out by Anonymous
 } 

