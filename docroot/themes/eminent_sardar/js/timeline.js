(function ($, Drupal) {

  Drupal.behaviors.Eminent = {
    attach: function (context, settings) {
      if ($('.searchresult-form .views-exposed-form').length == 0) {
        $('.search-filters').remove();
      }
      $('#header .navbar-right .views-exposed-form').addClass('search');
      $('#header .navbar-right .views-exposed-form form').attr('role', 'form');
      function equalgridheight(group) {
        var tallest = 0;
        group.each(function() {
            var thisHeight = $(this).height();
            if(thisHeight > tallest) {
                tallest = thisHeight;
            }
        });
        group.height(tallest);
      };
      if($('.grid-equal-height').length) {
        equalgridheight($('.grid-equal-height'));
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
