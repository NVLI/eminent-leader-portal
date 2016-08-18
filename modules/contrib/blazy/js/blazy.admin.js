/**
 * @file
 * Provides admin utilities.
 *
 * @todo move to Blazy for re-usablity across Blazy, Slick, Mason, GridStack...
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.blazyAdmin = {
    attach: function (context) {
      var $form = $('.form--slick', context);

      $('.description', $form).once('blazy-tooltip').each(function () {
        var tip = $(this);
        if (!tip.siblings('.hint').length) {
          tip.closest('.form-item').append('<span class="hint">?</span>');
        }
      });

      $form.once('blazy-admin').each(function () {
        var t = $(this);
        $('.details-legend-prefix', t).removeClass('element-invisible');

        t.on('click', '.form-checkbox', function () {
          var t = $(this);
          if (t.prop('checked')) {
            t.addClass('on');
          }
          else {
            t.removeClass('on');
          }
        });

        t.on('mouseenter', '.hint', function () {
          $(this).closest('.form-item').addClass('is-hovered');
        });
        t.on('mouseleave', '.hint', function () {
          $(this).closest('.form-item').removeClass('is-hovered');
        });
        t.on('click', '.hint', function () {
          $('.form-item.is-selected', t).removeClass('is-selected');
          $(this).parent().toggleClass('is-selected');
        });
        t.on('click', '.description', function () {
          $(this).closest('.is-selected').removeClass('is-selected');
        });
        t.on('focus', '.js-expandable', function () {
          $(this).parent().addClass('is-focused');
        });
        t.on('blur', '.js-expandable', function () {
          $(this).parent().removeClass('is-focused');
        });

      });
    }
  };

})(jQuery, Drupal);
