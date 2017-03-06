/**
 * @file
 * Custom js files for the theme.
 */

jQuery(function ($) {'use strict',

  $(document).ready(function () {

    var clickEvent = false;
    $('#playlist').carousel({
      interval:   4000
      }).on('click', '.list-group li', function () {
        clickEvent = true;
        $('.list-group li').removeClass('active');
        $(this).addClass('active');
      }).on('slid.bs.carousel', function (e) {
        if (!clickEvent) {
          var count = $('.list-group').children().length - 1;
          var current = $('.list-group li.active');
          current.removeClass('active').next().addClass('active');
          var id = parseInt(current.data('slide-to'));
          if (count == id) {
            $('.list-group li').first().addClass('active');
          }
        }
        clickEvent = false;
      });
  });

  // Accordian.
  $('.accordion-toggle').on('click', function () {
  $(this).closest('.panel-group').children().each(function () {
  $(this).find('>.panel-heading').removeClass('active');
   });

   $(this).closest('.panel-heading').toggleClass('active');
  });

  // Play video on load.
  $(window).on("load", function () {
    Modernizr.on('videoautoplay', function (result) {
      if (result) {

      }
      else {
        $("#myCarousel").carousel(1);
      }
    });
  });

  // Hide video after play.
  $('#bannervideo').on('ended',function () {
    $("#myCarousel").carousel(1);
  });

  // Fix the top bar on scroll.
  var lastScrollTop = 0;
  $(window).on("scroll", function () {

    var st = $(this).scrollTop();
    if (st > 50) {
      if (st <= lastScrollTop) {
        $('#header').fadeIn('slow');
      }
      else {
        $('#header').fadeOut('slow');
      }
      lastScrollTop = st;
    }
  });

  // Full screen view for the media page.
  var fullscreen = false;
  $('.resize-btn').on("click", function () {
    if (!fullscreen) {
      $('.media-preview').css({
        'position': 'fixed',
        'top': 0,
        'bottom': 0,
        'left': 0,
        'right': 0,
        'margin-bottom': 0,
        'z-index': 9999
      });

      $('.resize-btn').css({
        'position': 'fixed',
        'bottom': 0,
        'right': 0,
      });

      $('.resize-btn').html('<span class="glyphicon glyphicon-resize-small" title="Exit full screen"></span>');

      fullscreen = true;
    }
    else {
      $('.media-preview').css({
        'position': 'relative',
        'margin-bottom': '70px',
        'z-index': 0
      });

      $('.resize-btn').css({
        'position': 'relative'
      });

      $('.resize-btn').html('<span class="glyphicon glyphicon-resize-full" title="View full screen."></span>');

      fullscreen = false;
    }

  });

  // Fix search show on the responsive.
  $(window).on("load resize ", function () {
    if ($(window).width() < 768) {

    /* $('#header .search').toggleClass('responsive-search')*/
    };
  });

  // Initialize slick clone height.
   $.fn.cloneheight = function (addheightto) {
    var $this = $(this);
    $this.each(function () {
      $(window).on("load resize", function () {
        $this.imagesLoaded(function () {
         if ($(window).width() > 973) {
          var height = $this.height();
          var container = addheightto;
          // $(this).css('position','relative');
         $(container).css("height", height + "px");
         }
         });
      });
    });
  };
  $('#main-slider .slider-item img').cloneheight("#main-slider .slider-item");

  // Trigger the quiz manualy on scroll.
  function isVisible($el) {
    var winTop = $(window).scrollTop();
    var winBottom = winTop + $(window).height();
    var elTop = $el.offset().top;
    var elBottom = elTop + $el.height();
    return ((elBottom <= winBottom) && (elTop >= winTop));
  }
  $(document).on('scroll', function () {
    if ($('#conatcat-info').length > 0) {
      if (isVisible($("#conatcat-info"))) {
          $(document).off('scroll');
          $('#launchquiz').modal('show');
      }
    }
  });

  // Dyanmically distribute height for the slider.
  $(window).on("load resize", function () {
    var cloneHeight = $('.quotes-by-sardar-holder').height();
    $('.share-feedbackholder').css('height', cloneHeight + 'px');
  });

  // Initialize slick quiz for the quiz block in the home page.
  if ($('#slickQuiz').length > 0) {
    $('#slickQuiz').slickQuiz();
    $('.startQuiz').click(function () {
      $('.quizHeader, .quizName').hide();
    });
  }

  // Added the slider home page to show the quotes of Sardar Patel.
  $(".quotes-by-sardar").slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: true,
    speed: 1000,
    touchMove: true,
    dots: true,
    autoplay: false
  });

  // Initialize the eqaul height on pages.
  $('.equal_height').matchHeight();

  // Added the exibition slider home page to show the lists of excibition.
  $(".sliderexcibition").slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    arrows: false,
    touchMove: true,
    dots: true,
    autoplay: true,
    responsive: [{
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
    }]
  });

  // Auto play the home page video.
  $('.search-results-tab .col-md-4:nth-child(3n+3)').after('<div class="clearfix"></div>');

  // Added the slider home page to show the quotes of Sardar Patel.
  $(".triggersearchfilter").click(function () {
    $(this).siblings('.search-filter').toggleClass('hidden show', 1000)
    if ($('.search-filter').hasClass('show')) {
      $(this).html("Close Filters &nbsp; <i class='fa fa-caret-up'></i>");
    }
    else {
      $(this).html("Show Filters &nbsp; <i class='fa fa-caret-down'></i>");
    }
  });
  // Added the slider home page to show the quotes of Sardar Patel.
  $(".quoteslider").slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: true,
    touchMove: true
  });

  // Added the slider in the exibition page with content over slider.
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

  // Vertical center the contents in the hopage slider.
  $.fn.VerticalCenter = function (container) {
    var $this = $(this)
    $(window).on("load resize ", function () {
      if ($(window).width() > 973) {
        $('.slider-item img').imagesLoaded(function () {
          var element = $this;
          var containerel = $(container)
          var elementHeight = $(element).height();
          var containerHeight = $(container).height();
          var marginTop = (containerHeight - elementHeight) / 2;
          $this.css('margin-top', marginTop + 'px');
        });
      }
    });
  };

  $('.slidercontent').VerticalCenter('.slider-item img');

  // Initiat WOW JS.
  new WOW().init();

  // Portfolio filter.
  $(window).load(function () {'use strict';
    var $portfolio_selectors = $('.portfolio-filter >li>a');
    var $portfolio = $('.portfolio-items');
    $portfolio.isotope({
     itemSelector : '.portfolio-item',
     layoutMode : 'fitRows'
    });

    $portfolio_selectors.on('click', function () {
      $portfolio_selectors.removeClass('active');
      $(this).addClass('active');
      var selector = $(this).attr('data-filter');
      $portfolio.isotope({ filter: selector });
      return false;
    });
  });

  // Contact form.
  var form = $('#main-contact-form');
  form.submit(function (event) {
    event.preventDefault();
    var form_status = $('<div class="form_status"></div>');
    $.ajax({
      url: $(this).attr('action'),

      beforeSend: function () {
        form.prepend(form_status.html('<p><i class="fa fa-spinner fa-spin"></i> Email is sending...</p>').fadeIn());
      }
    }).done(function (data) {
      form_status.html('<p class="text-success">' + data.message + '</p>').delay(3000).fadeOut();
    });
  });

  // Goto top.
  $('.gototop').click(function (event) {
    event.preventDefault();
    $('html, body').animate({
      scrollTop: $("body").offset().top
    }, 500);
  });

  // Pretty Photo.
  $("a[rel^='prettyPhoto']").prettyPhoto({
    social_tools: false
  });

  // Stop sliding bootstrap carousel.
  $('#exibition_details #playlist').carousel({
    interval: false
  });

});
