/**
 * @file
 * Example of JavaScript file for theme.
 *
 * You can edit it or write on your own.
 */

(function ($, Drupal, window, document, undefined) {

  Drupal.behaviors.druio = {
    attach: function (context, settings) {

      // Set target="_blank" for links in content section.
      var $article = $('#content > article', context);
      $('section.question__right a, section.post__content a', $article)
        .not('.category a, .projects a, ul.links a')
        .attr('target', '_blank');

      $('section.question__right img, ' +
        'section.post__content img,' +
        '.answer img,' +
        '.comments img', $article).one("load", function () {
        var $this = $(this);
        if ($this.width() != $this[0].naturalWidth) {
          if (0 == $(this).parent('a').length) {
            var $link_wrapper = $('<a class="lightcase" href="' + $this.attr('src') + '"></a>');
            $this.wrap($link_wrapper);
            Drupal.behaviors.Lightcase.attach($link_wrapper, settings);
          }
        }
      }).each(function () {
        if (this.complete) $(this).load();
      });

      // Attach highlightjs.
      $('code, pre code').each(function (i, block) {
        hljs.highlightBlock(block);
      });

      // Fix wrapper for Rate module.
      $('.rate-widget').each(function () {
        $(this).addClass($(this).attr('id'));
      });

      // Activate Select2
      $('select').select2({
        theme: 'druio'
      });

      $('#make-reply').click(function () {
        $(this).hide();
        $('form.node-answer-form').fadeIn();
      });

    }
  };

})(jQuery, Drupal, this, this.document);
