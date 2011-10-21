(function ($) {
  
  
  // show hide something 
  $(document).ready(function() {  // call again after typing stops  
    $('a.toggle').click(function() { 
       $(this).parent('div.original_text').toggleClass('shown', 'hidden'); 
    });  
  }); 
  
   
 $(document).ready(function() { 
    // call once on page load 
    adserver_form_timer_reset();
    // call again after keystrokes
    $('#edit-title').keyup(adserver_form_timer_reset);  
    $('#edit-adserve-display-desc-und-0-value').keyup(adserver_form_timer_reset);
    $('#edit-adserve-display-url-und-0-value').keyup(adserver_form_timer_reset);  
    $('#edit-title').attr('maxlength', 25);
    $('#edit-adserve-display-url-und-0-value').attr('maxlength', 35);
    
    
    if (!$('#edit-adserve-weight-und-0-value').val()) {
      $('#edit-adserve-weight-und-0-value').val('1');
    }
    if (!$('#edit-adserve-group-und-0-value').val()) {
      $('#edit-adserve-group-und-0-value').val('Default Group');
    }
      
     // sample ad for development
    if (!$('#edit-title').val()) {
      $('#edit-title').val('Best Product Ever');
    }
    if (!$('#edit-adserve-display-desc-und-0-value').val()) {
      $('#edit-adserve-display-desc-und-0-value').val('The best product ever, now for a one time price of only $5');
    }
    if (!$('#edit-adserve-display-url-und-0-value').val()) {
      $('#edit-adserve-display-url-und-0-value').val('www.checkitout.com');
    }
    if (!$('#edit-adserve-target-url-und-0-value').val()) {
      $('#edit-adserve-target-url-und-0-value').val('http://checkitout.com/myoffer_squeezepage.php');
    }
    
   
    
 });  
  
 function adserver_form_timer_reset() {
   clearTimeout($.data(this, 'timer'));
   var wait = setTimeout(adserver_form_check_text, 50);
   $(this).data('timer', wait); 
 }
  
 // fetch generated ad 
 function adserver_form_check_text() {  
   var title = $('#edit-title').val();  
   var text = $('#edit-adserve-display-desc-und-0-value').val();  
   var url = $('#edit-adserve-display-url-und-0-value').val();  
   var adlen = text.length + title.length; 
   $('#adserve-title-len').text(title.length);
   if (adlen > 95) {
     $('#adserve-disp-text-len').css('color', 'red');  
     $('#adserve-disp-text-len').text(adlen + ' !'); 
   } else  {
     $('#adserve-disp-text-len').css('color', 'green'); 
     $('#adserve-disp-text-len').text(adlen);
   } 
   
 
   if (isUrl(url)) {
     $('#adserve-disp-url-len').text(url.length);
     $('#adserve-disp-url-len').css('color', 'green'); 
   } else {
     $('#adserve-disp-url-len').text('bad url');
     $('#adserve-disp-url-len').css('color', 'red'); 
   }
   var ad = '<div class="adserver-ad"></div>';  
   if (adlen < 96) {
    ad ='<div class="adserver-ad">';
    ad += '<div class="title">' + title + '</div>';
    ad +=  '<div class="url">' + url + '</div>'; 
    ad +=  '<div class="text">' + text + '</div>';
    ad +=  '</div>';  
   }  
   $('#adserve-ad-example').html(ad);
 
  
 }  
     
 function isUrl(s) {
   // for some reaon this never fails
   s = 'http://' + s;
   var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
   return regexp.test(s);
 }

})(jQuery);
