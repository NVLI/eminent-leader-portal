jQuery(function($) {'use strict',

  //#main-slider
  /*$(function(){
    $('#main-slider.carousel').carousel({
      interval: 8000
    });
  });*/


  // accordian
  $('.accordion-toggle').on('click', function(){
  $(this).closest('.panel-group').children().each(function(){
  $(this).find('>.panel-heading').removeClass('active');
   });

   $(this).closest('.panel-heading').toggleClass('active');
  });


/***********************************************************************
*******fix the top bar on scroll*/
  $(window).on("load resize scroll", function () {
    if ($(window).width() > 768) {
        if ($(window).scrollTop() > 500) {
          $('#header').addClass('navbar-fixed-top  wow fadeInDown');
        }
        if ($(window).scrollTop() < 500) {
          $('#header').removeClass('navbar-fixed-top  wow fadeInDown');
        }
    };
  });


/***********************************************************************
*******fix search show on the responsive*/
  $(window).on("load resize ", function () {
    if ($(window).width() < 768) {

    /* $('#header .search').toggleClass('responsive-search')*/
    };
  });

/***********************************************************************
*******Initialize search i toggle header */
  $('#header i.fa-search').click(function(){
    $(this).closest('.search').toggleClass('active');
  });
/***********************************************************************
*******Initialize slick clone height to */
   $.fn.cloneheight = function ( addheightto ) {
    var $this = $(this);
    $this.each(function() {
      $(window).on("load resize", function () {
        $this.imagesLoaded( function() {
         if ($(window).width() > 973) {
          var height = $this.height();
          var container = addheightto;
           $(this).css('position','relative');
         $(container).css("height", height + "px");
         }
         });
      });
    });
  };
  $('#main-slider .slider-item img').cloneheight("#main-slider .slider-item");



/***********************************************************************
*******Trigger the quiz manualy on scroll*/
$(document).on('scroll', function() {
    if( $(this).scrollTop() >= 800 ) {
        $(document).off('scroll');
        $('#launchquiz').modal('show');
    }
});
/***********************************************************************
*******Dyanmically distribute height for the slider*/
  $(window).on("load resize", function () {
    var cloneHeight = $('.quotes-by-sardar-holder').height();
    $('.share-feedbackholder').css('height', cloneHeight + 'px');
  });



/***********************************************************************
*******Dyanmically distribute height for the slider*/

/*  $(window).on("load resize", function () {
    var winh = $(window).height();
    var winhper = ($(window).height() * 50)/100;
    console.log("winh" + winhper)
    $('#main-slider .slider-item').css('height', winhper + 'px')
  });

$.fn.centercontent = function () {
  var $this = $(this);
  $(window).on("load resize", function () {
    $this.each(function () {
      var containerHeight = $this.height();
      var ChildrenHeight = $this.find('.content-holder').height();
      console.log(containerHeight);
      console.log(ChildrenHeight);
      var margin = (containerHeight - ChildrenHeight) / 2;
//      console.log(margin);
      $this.find('.content-holder').css('padding-top', margin + "px");
    });
  });
};
$('#main-slider').centercontent()*/

/***********************************************************************
*******Initialize slick quiz for the quiz block in the home page*/
if ($('#slickQuiz').length > 0) {
  $('#slickQuiz').slickQuiz();
  $('.startQuiz').click(function(){
    $('.quizHeader, .quizName').hide();
  });
}
/***********************************************************************
*******Added the slider home page to show the quotes of Sardar Patel*/
$(".quotes-by-sardar").slick({
   slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    touchMove: true,
    dots: true,
    autoplay: true
});
/***********************************************************************
*******initialize the eqaul height on pages*/

$('.equal_height').matchHeight();
/***********************************************************************
*******Added the exibition slider home page to show the lists of excibition*/
$(".sliderexcibition").slick({
   slidesToShow: 4,
    slidesToScroll: 1,
    arrows: false,
    touchMove: true,
    dots: true,
    autoplay: true,
  responsive: [
      {
        breakpoint: 1900,
        settings: {
          slidesToShow: 4,
          slidesToScroll: 1,
          infinite: false,
          dots: true
        }
      },{
        breakpoint: 980,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          infinite: false,
          dots: true
        }
      },{
        breakpoint: 400,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
          infinite: false,
          dots: true
        }
      }
    ]

});
/***********************************************************************
*******Auto play the home page video*/
$('.search-results-tab .col-md-4:nth-child(3n+3)').after('<div class="clearfix"></div>');

/***********************************************************************
*******Auto play the home page video*/
/*jQuery( document ).ready(function($) {
  $('#header .navbar-right .views-exposed-form').addClass('search');
  $('#header .navbar-right .views-exposed-form form').attr('role', 'form');
  if ($('#vid').length > 0) {
    var vid = document.getElementById("vid");
    function playVid() {
      vid.play();
    }
    playVid();
  }
});*/
/***********************************************************************
*******Added the slider home page to show the quotes of Sardar Patel*/
  $(".triggersearchfilter").click(function(){
    $(this).siblings('.search-filter').toggleClass('hidden show', 1000)
    if ($('.search-filter').hasClass('show')) {
      $(this).html("<i class='fa fa-times-circle'></i> &nbsp; Close Filters");
    } else {
      $(this).html("<i class='fa fa-filter'></i> &nbsp; Show Filters");
    }
  });
/***********************************************************************
*******Added the slider home page to show the quotes of Sardar Patel*/
$(".quoteslider").slick({
   slidesToShow: 1,
    slidesToScroll: 1,
    arrows: true,
    touchMove: true
});


/*$("#main-slider").slick({
   slidesToShow: 1,
    slidesToScroll: 0,
    arrows: false,
    touchMove: false
});*/

/***********************************************************************
*******Added the slider in the exibition page with  content over slider*/
  $('.exibition_slider').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    touchMove: true,
    fade: true,
    asNavFor: '.exibition_pager'
  });

  $('.exibition_pager').slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    asNavFor: '.exibition_slider',
    autoplaySpeed: 2000,
    focusOnSelect: true,
    responsive: [
      {
        breakpoint: 1900,
        settings: {
          slidesToShow: 4,
          slidesToScroll: 1,
          infinite: false,
          dots: false
        }
      },{
        breakpoint: 980,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          infinite: false,
          dots: false
        }
      },{
        breakpoint: 400,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
          infinite: false,
          dots: false
        }
      }
    ]
  });


/***********************************************************************
*******Vertical center the contents in the hopage slider*/

$.fn.VerticalCenter = function(container) {
    var $this = $(this)
    $(window).on("load resize ", function () {
      if ($(window).width() > 973) {
        $('.slider-item img').imagesLoaded( function() {
      var element = $this;
      var containerel = $(container)
      var elementHeight = $(element).height();
      var containerHeight = $(container).height();
      var marginTop =  (containerHeight - elementHeight) / 2;
      $this.css('margin-top', marginTop + 'px');
    });
    }
  });
};

$('.slidercontent').VerticalCenter('.slider-item img');


/***********************************************************************
******* send the header aboe the slider in homepage*/
/*$(window).on("load resize scroll", function () {
    if ($(window).width() > 768) {
        var navbarHeight = $('header#header').height();
        $('#main-slider').css('margin-top', '-' + navbarHeight + 'px');
    };
  });*/




  //Initiat WOW JS
  new WOW().init();

  // portfolio filter
  $(window).load(function(){'use strict';
  var $portfolio_selectors = $('.portfolio-filter >li>a');
  var $portfolio = $('.portfolio-items');
  $portfolio.isotope({
   itemSelector : '.portfolio-item',
   layoutMode : 'fitRows'
  });

  $portfolio_selectors.on('click', function(){
   $portfolio_selectors.removeClass('active');
   $(this).addClass('active');
   var selector = $(this).attr('data-filter');
   $portfolio.isotope({ filter: selector });
   return false;
  });
  });

  // Contact form
  var form = $('#main-contact-form');
  form.submit(function(event){
    event.preventDefault();
    var form_status = $('<div class="form_status"></div>');
    $.ajax({
      url: $(this).attr('action'),

      beforeSend: function(){
        form.prepend( form_status.html('<p><i class="fa fa-spinner fa-spin"></i> Email is sending...</p>').fadeIn() );
      }
    }).done(function(data){
      form_status.html('<p class="text-success">' + data.message + '</p>').delay(3000).fadeOut();
    });
  });


  //goto top
  $('.gototop').click(function(event) {
    event.preventDefault();
    $('html, body').animate({
      scrollTop: $("body").offset().top
    }, 500);
  });

  //Pretty Photo
  $("a[rel^='prettyPhoto']").prettyPhoto({
    social_tools: false
  });
});
