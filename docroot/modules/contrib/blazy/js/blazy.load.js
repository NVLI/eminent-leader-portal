/**
 * @file
 * Provides bLazy loader.
 */

(function ($, Drupal, drupalSettings, window, document) {

  'use strict';

  /**
   * Attaches blazy behavior to HTML element identified by [data-blazy].
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.blazy = {
    attach: function (context) {
      var me = Drupal.blazy;
      var $blazy = $('[data-blazy]', context);
      var globals = me.globalSettings();

      if (!$blazy.length) {
        me.init = new Blazy(globals);
      }

      $blazy.once('blazy').each(function () {
        var $elm = $(this);
        var data = $elm.data('blazy') || {};
        var options = $.extend({}, globals, data);

        me.init = new Blazy(options);

        $elm.data('blazy', options);

        me.resizing(function () {
          me.windowWidth = window.innerWidth || document.documentElement.clientWidth || $(window).width();

          $elm.trigger('resizing', [me.windowWidth]);
        })();
      });
    }
  };

  /**
   * Blazy methods.
   *
   * @namespace
   */
  Drupal.blazy = {
    init: null,
    windowWidth: 0,
    globalSettings: function () {
      var me = this;
      var settings = drupalSettings.blazy || {};
      var commons = {
        dimensions: false,
        success: function (elm) {
          me.clearing(elm);
        },
        error: function (elm, msg) {
          me.clearing(elm);
        }
      };

      return $.extend(settings, commons);
    },

    updateRatio: function (elm, ratio, data) {
      var me = this;
      var pad = null;

      if (data.dimensions) {
        $.each(data.dimensions, function (key, v) {
          pad = me.windowWidth >= key ? v : null;
        });

        if (pad !== null) {
          ratio.css({
            paddingBottom: pad + '%'
          });
        }
      }
    },

    clearing: function (elm) {
      var me = this;
      var blazyClasses;
      var $elm = $(elm);
      var $blazy = $elm.closest('[data-blazy]');
      var $ratio = $elm.closest('.media--ratio');
      var data = $blazy.data('blazy');

      window.clearTimeout(blazyClasses);
      blazyClasses = window.setTimeout(function () {
        $elm.closest('.media--loading').removeClass('media--loading').addClass('media--loaded');
      }, 200);

      if (data && $ratio.length) {
        me.updateRatio($elm, $ratio, data);

        $blazy.on('resizing', function () {
          me.updateRatio($elm, $ratio, data);
        });
      }
    },

    // Thanks to https://github.com/louisremi/jquery-smartresize
    resizing: function (c, t) {
      window.onresize = function () {
        window.clearTimeout(t);
        t = window.setTimeout(c, 200);
      };
      return c;
    }

  };

}(jQuery, Drupal, drupalSettings, this, this.document));
