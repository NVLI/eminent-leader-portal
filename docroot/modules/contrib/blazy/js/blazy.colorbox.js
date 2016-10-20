/**
 * @file
 *
 * @todo bring in a little portion of Slick carousel goodness for easy styling.
 */

(function ($, Drupal, drupalSettings, window) {

  'use strict';

  Drupal.behaviors.blazyColorbox = {
    attach: function (context) {

      $('.blazy__colorbox', context).once('blazy-colorbox').each(function () {
        var t = $(this);
        var media = t.data('media') || {};
        var runtimeOptions = {
          rel: media.rel || null,
          title: function () {
            var $caption = t.next('.litebox-caption');
            return $caption.length ? $caption.html() : '';
          }
        };

        if (drupalSettings.colorbox.mobiledetect && window.matchMedia) {
          // Disable Colorbox for small screens.
          var mq = window.matchMedia('(max-device-width: ' + drupalSettings.colorbox.mobiledevicewidth + ')');
          if (mq.matches) {
            return;
          }
        }

        t.colorbox($.extend({}, drupalSettings.colorbox, runtimeOptions));
      });
    }
  };

})(jQuery, Drupal, drupalSettings, this);
