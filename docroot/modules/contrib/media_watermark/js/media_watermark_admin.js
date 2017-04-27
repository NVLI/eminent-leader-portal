/**
 * @file
 * Media watermark module javascript file.
 *
 * Contains javascript for admin interface of media watermark module.
 */

(function ($) {
  Drupal.behaviors.mediaWatermarkAdmin = {
    attach: function (context, settings) {
      $('#edit-watermarks-images .image-hidden', context).hide();
      var val = $('#edit-watermarks-names :selected', context).val();
      $('#edit-watermarks-images #image-' + val, context).show();
      $('#edit-watermarks-names', context).change(function () {
        var val = $('#edit-watermarks-names :selected', context).val();
        $('#edit-watermarks-images .image-hidden', context).hide();
        $('#edit-watermarks-images #image-' + val, context).show();
      });
    }
  };
})(jQuery);
