/**
 * @file
 * Jquery for the curtain raiser inauguration effect.
 */

 jQuery(function ($) {'use strict',

  $('#curtain-raiser-inauguration-form').on('submit', function (e) {
    e.preventDefault();
    var pwd = $('#edit-inaugurate-password').val();
    var path = "ajax/inauguration/validate/" + pwd;
    $.ajax({
        type : 'GET',
        url : '/' + path,
        encode : true
    })
    .done(function (data) {
      if (data.success) {
        $("#curtain1").animate({width:20},6000);
        $("#curtain2").animate({width:20},6000);
        $(".container-ing").addClass('pointer-none');
        $(".content-ing").fadeOut();
        // $('.container-ing').fireworks();
        $("#myCarousel").carousel(0);
        $('#bannervideo').get(0).play()

      }
      else {
        $('.form-item-inaugurate-password').prepend("<div class = 'error'>Please enter the right password</div>");
      }
    });
  });

});
