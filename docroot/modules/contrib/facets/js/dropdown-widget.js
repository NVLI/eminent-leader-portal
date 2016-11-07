/**
 * @file
 * Transforms links into a dropdown list.
 */

(function ($) {

  'use strict';

  Drupal.facets = Drupal.facets || {};
  Drupal.behaviors.facetsDropdownWidget = {
    attach: function (context, settings) {
      Drupal.facets.makeDropdown();
    }
  };

  /**
   * Turns all facet links into a dropdown with options for every link.
   */
  Drupal.facets.makeDropdown = function () {
    // Find all dropdown facet links and turn them into an option.
    $('.js-facets-dropdown-links').once('facets-dropdown-transform').each(function () {
      var $links = $(this).find('.facet-item a');
      var $dropdown = $('<select class="facets-dropdown" />');

      // Add empty text option first.
      var default_option_label = $(this).data('facet-default-option-label');
      var $default_option = $('<option />')
        .attr('value', '')
        .text(default_option_label);
      $dropdown.append($default_option);

      var has_active = false;
      $links.each(function () {
        var $link = $(this);
        var active = $link.hasClass('is-active');
        var $option = $('<option />')
          .attr('value', $link.attr('href'));
        if (active) {
          has_active = true;
          // Set empty text value to this link to unselect facet.
          $default_option.attr('value', $link.attr('href'));

          $option.attr('selected', 'selected');
          $link.find('.js-facet-deactivate').remove();
        }
        $option.html($link.html());
        $dropdown.append($option);
      });

      // Go to the selected option when it's clicked.
      $dropdown.on('change', function () {
        window.location.href = $(this).val();
      });

      // Append empty text option.
      if (!has_active) {
        $default_option.attr('selected', 'selected');
      }

      // Append dropdown.
      $(this).html($dropdown);
    });
  };

})(jQuery);
