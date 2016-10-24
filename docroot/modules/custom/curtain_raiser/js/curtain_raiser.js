jQuery(function($) {'use strict',

  $('#curtain-raiser-inauguration-form').on('submit', function(e){
    e.preventDefault();
    $("#curtain1").animate({width:20},6000);
    $("#curtain2").animate({width:20},6000);
    $(".container-ing").addClass('pointer-none');
    $(".content-ing").fadeOut();
  });

});
