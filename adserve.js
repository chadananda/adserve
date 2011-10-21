

(function ($) { 
    $('div.adserve').each(function(){
      var format = $(this).attr('data-format');
      var style =  $(this).attr('data-style'); 
      var adid =  $(this).attr('data-adid'); 
      var ad = this; 
      // var ad_div = $(this).find('div.ad');
      var url = adserve_url + '&f=' + format + '&s=' + style + '&callback=x'; 
      if (adid) url += '&adid=' + adid;
      $(ad).html(url);
      $.getJSON(url, '', function(data, textStatus, jqXHR){  
        $(ad).html(data); 
      }); 
    }); 
  }(jQuery)); 
