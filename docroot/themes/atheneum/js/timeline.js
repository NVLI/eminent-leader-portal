(function ($, Drupal) {

  Drupal.behaviors.Eminent = {
    attach: function (context, settings) {
      $('#header .navbar-right .views-exposed-form form').attr('role', 'form');
      if ($('.exhibitions-wrapper').length > 0) {
        $('a.search-tab').removeClass("active");
        $('#exhibition-tab').addClass('active');
      }
      if ($('.timelines-wrapper').length > 0) {
        $('a.search-tab').removeClass("active");
        $('#timeline-tab').addClass('active');
      }
      if ($('.media-wrapper').length > 0) {
        $('a.search-tab').removeClass("active");
        $('#media-tab').addClass('active');
      }
      if ($('.quotes-wrapper').length > 0) {
        $('a.search-tab').removeClass("active");
        $('#quote-tab').addClass('active');
      }
      function equalgridheight(group) {
        var tallest = 0;
        group.each(function() {
          var thisHeight = $(this).height();
          if(thisHeight > tallest) {
              tallest = thisHeight;
          }
        });
        if (tallest == 0) {
          tallest = 400;
        }
        group.height(tallest);
      };
      if($('.grid-equal-height').length) {
        equalgridheight($('.grid-equal-height'));
      }
      if($('.media-grid-equal-height').length) {
        equalgridheight($('.media-grid-equal-height'));
      }
    }
  };
})(jQuery, Drupal);


jQuery(document).ready(function($){

	var timelineBlocks = $('.cd-timeline-block'),
		offset = 0.8;

	//hide timeline blocks which are outside the viewport
	hideBlocks(timelineBlocks, offset);

	//on scolling, show/animate timeline blocks when enter the viewport
	$(window).on('scroll', function(){
		(!window.requestAnimationFrame)
			? setTimeout(function(){ showBlocks(timelineBlocks, offset); }, 100)
			: window.requestAnimationFrame(function(){ showBlocks(timelineBlocks, offset); });
	});

	function hideBlocks(blocks, offset) {
		blocks.each(function(){
			( $(this).offset().top > $(window).scrollTop()+$(window).height()*offset ) && $(this).find('.cd-timeline-img, .cd-timeline-content').addClass('is-hidden');
		});
	}

	function showBlocks(blocks, offset) {
		blocks.each(function(){
			( $(this).offset().top <= $(window).scrollTop()+$(window).height()*offset && $(this).find('.cd-timeline-img').hasClass('is-hidden') ) && $(this).find('.cd-timeline-img, .cd-timeline-content').removeClass('is-hidden').addClass('bounce-in');
		});
	}
});
