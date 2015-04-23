(function ($) {

Drupal.behaviors.configurationTabs = {
  attach: function (context) {
    // Adding the summary to the vertical tabs
    $('fieldset', context).each(function (context) {
      var overridden = '';

      // Look for overrides in the fieldset
      if ($(this).hasClass('overridden')) {
        overridden = ' :: <em>NEEDS ATTENTION</em>';
      }

      var summary = this.id;
      $(this).drupalSetSummary(function (context) {
        return summary.substr(5) + overridden;
      });
    });
  }
};

Drupal.theme.verticalTab = function (settings) {
  var overridden = '';
  var tab = {};

  // If the original fieldset has overidden applied, use it here too.
  if (settings.fieldset.hasClass('overridden')) {
    var overridden = 'overridden';
  }

  tab.item = $('<li class="vertical-tab-button '+ overridden +'" tabindex="-1"></li>')
    .append(tab.link = $('<a href="#"></a>')
      .append(tab.title = $('<strong></strong>').text(settings.title))
      .append(tab.summary = $('<span class="summary"></span>')
    )
  );
  return tab;
};

})(jQuery);
