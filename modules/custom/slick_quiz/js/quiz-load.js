// Put all your page JS here
(function ($, Drupal) {

  Drupal.behaviors.Mirador = {
    attach: function (context, settings) {
      var quizConf = settings.DrupalSlickQuiz.slick_conf;
      var quizConfig = JSON.parse(quizConf);
      alert(quizConfig); console.log(quizConfig);
      $('#slickQuiz').slickQuiz({json: quizConfig});
    }
  };
})(jQuery, Drupal);
