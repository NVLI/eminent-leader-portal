/**
 * @file
 * Slick quiz js.
 */

(function ($, Drupal) {

  Drupal.behaviors.Mirador = {
    attach: function (context, settings) {
      var quizConf = settings.DrupalSlickQuiz.slick_conf;
      var quizConfig = JSON.parse(quizConf);
      $('#slickQuiz').slickQuiz({json: quizConfig});
    }
  };
})(jQuery, Drupal);
