/**
 * @file
 * Provides Slick loader.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Attaches slick behavior to HTML element identified by CSS selector .slick.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.slick = {
    attach: function (context) {
      var me = this;
      $('.slick:not(.unslick)', context).once('slick').each(function () {
        var that = this;
        var t = $('> .slick__slider', that);
        var a = $('> .slick__arrow', that);
        var o = $.extend({}, drupalSettings.slick, t.data('slick'));
        var r = $('.slide--0 .media--ratio', t);

        // Fixed for broken slick with Blazy, aspect ratio, hidden containers.
        if (r.length && r.is(':hidden')) {
          r.removeClass('media--ratio').addClass('js-media--ratio');
        }

        // Build the Slick.
        me.beforeSlick(t, a, o);
        t.slick(me.globals(t, a, o));
        me.afterSlick(t, o);
      });
    },

    /**
     * The event must be bound prior to slick being called.
     *
     * @param {HTMLElement} t
     *   The slick HTML element.
     * @param {HTMLElement} a
     *   The slick arrow HTML element.
     * @param {object} o
     *   The slick options object.
     */
    beforeSlick: function (t, a, o) {
      var me = this;
      var breakpoint;

      me.randomize(t, o);

      t.on('init.slick', function (e, slick) {
        // Populate defaults + globals into each breakpoint.
        var sets = slick.options.responsive || null;
        if (sets && sets.length > -1) {
          for (breakpoint in sets) {
            if (sets.hasOwnProperty(breakpoint)
              && sets[breakpoint].settings !== 'unslick') {
              slick.breakpointSettings[sets[breakpoint].breakpoint] = $.extend(
                {},
                drupalSettings.slick,
                me.globals(t, a, o),
                sets[breakpoint].settings);
            }
          }
        }
      });

      t.on('setPosition.slick', function (e, slick) {
        me.setPosition(t, a, slick);
      });

      if (o.lazyLoad === 'blazy' && typeof Drupal.blazy !== 'undefined') {
        t.on('beforeChange.slick', function () {
          var $src = $('[data-src]', t);
          var $srcset = $('[data-srcset]', t);

          // Enforces lazyload ahead to smoothen the UX.
          if ($src.length) {
            Drupal.blazy.init.load($src);
          }
          if ($srcset.length) {
            Drupal.blazy.loadSrcset($srcset);
          }
        });
      }
    },

    /**
     * The event must be bound after slick being called.
     *
     * @param {HTMLElement} t
     *   The slick HTML element.
     * @param {object} o
     *   The slick options object.
     */
    afterSlick: function (t, o) {
      var me = this;
      var slick = t.slick('getSlick');
      var $ratio = $('.js-media--ratio', t);

      // Arrow down jumper.
      t.parent().on('click.slick.load', '.slick-down', function (e) {
        e.preventDefault();
        var b = $(this);
        $('html, body').stop().animate({
          scrollTop: $(b.data('target')).offset().top - (b.data('offset') || 0)
        }, 800, o.easing || 'swing');
      });

      if (o.mouseWheel) {
        t.on('mousewheel.slick.load', function (e, delta) {
          e.preventDefault();
          return (delta < 0) ? t.slick('slickNext') : t.slick('slickPrev');
        });
      }

      // Fixed for broken slick with Blazy, aspect ratio, hidden containers.
      if ($ratio.length) {
        // t[0].slick.refresh();
        t.trigger('resize');
        $ratio.addClass('media--ratio').removeClass('js-media--ratio');
      }

      t.trigger('afterSlick', [me, slick, slick.currentSlide]);
    },

    /**
     * Randomize slide orders, for ads/products rotation within cached blocks.
     *
     * @param {HTMLElement} t
     *   The slick HTML element.
     * @param {object} o
     *   The slick options object.
     */
    randomize: function (t, o) {
      if (o.randomize && !t.hasClass('slick-initiliazed')) {
        t.children().sort(function () {
          return 0.5 - Math.random();
        })
        .each(function () {
          t.append(this);
        });
      }
    },

    /**
     * Fixed for known issues with the slick-current and arrows.
     *
     * Still kept after v1.5.8 (8/4) as 'slick-current' fails with asNavFor:
     *   - Create asNavFor with the total <= slidesToShow and centerMode.
     *   - Drag the main large display, or click its arrows, thumbnail
     *     slick-current class is not updated/ synched, always stucked at 0.
     *
     * @param {HTMLElement} t
     *   The slick HTML object.
     * @param {HTMLElement} a
     *   The slick arrow HTML object.
     * @param {object} slick
     *   The slick instance object.
     *
     * @todo drop if any core fix after v1.5.8 (8/4).
     *
     * @return {string}
     *   The visibility of slick arrows controlled by CSS class visually-hidden.
     */
    setPosition: function (t, a, slick) {
      // Be sure the most complex slicks are taken care of as well, e.g.:
      // asNavFor with the main display containing nested slicks.
      if (t.attr('id') === slick.$slider.attr('id')) {
        var o = slick.options;
        // Must take care for asNavFor instances, with/without slick-wrapper,
        // with/without block__no_wrapper/ views_view_no_wrapper, etc.
        var w = t.parent().parent('.slick-wrapper').length
          ? t.parent().parent('.slick-wrapper') : t.parent('.slick');

        $('.slick-slide', w).removeClass('slick-current');
        $('[data-slick-index="' + slick.currentSlide + '"]', w).addClass('slick-current');

        // Removes padding rules, if no value is provided to allow non-inline.
        if (!o.centerPadding || o.centerPadding === '0') {
          slick.$list.css('padding', '');
        }

        // Do not remove arrows, to allow responsive have different options.
        return slick.slideCount <= o.slidesToShow || o.arrows === false
          ? a.addClass('visually-hidden') : a.removeClass('visually-hidden');
      }
    },

    /**
     * Declare global options explicitly to copy into responsive settings.
     *
     * @param {HTMLElement} t
     *   The slick HTML element.
     * @param {HTMLElement} a
     *   The slick arrow HTML element.
     * @param {object} o
     *   The slick options object.
     *
     * @return {object}
     *   The global options common for both main and responsive displays.
     */
    globals: function (t, a, o) {
      return {
        slide: o.slide,
        lazyLoad: o.lazyLoad,
        dotsClass: o.dotsClass,
        rtl: o.rtl,
        appendDots: o.appendDots === '.slick__arrow'
          ? a : (o.appendDots || $(t)),
        prevArrow: $('.slick-prev', a),
        nextArrow: $('.slick-next', a),
        appendArrows: a,
        customPaging: function (slick, i) {
          var tn = slick.$slides.eq(i).find('[data-thumb]') || null;
          var alt = Drupal.t(tn.attr('alt')) || '';
          var img = '<img alt="' + alt + '" src="' + tn.data('thumb') + '">';
          var dotsThumb = tn.length && o.dotsClass.indexOf('thumbnail') > 0 ?
            '<div class="slick-dots__thumbnail">' + img + '</div>' : '';
          return dotsThumb + slick.defaults.customPaging(slick, i);
        }
      };
    }
  };

})(jQuery, Drupal, drupalSettings);
