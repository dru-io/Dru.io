(function ($) {
  Drupal.behaviors.configuration_ui = {
    attach: function(context, settings) {

      var components = $("#configuration-ui-tracking-form span.component:not(.processed)").length;

      // Configuration management form
      var in_sync = Drupal.t('In Sync');
      $('#configuration-ui-tracking-form span.component:not(.processed)').each(function() {
        $(this).addClass('processed');
        var component = $(this).attr('rel');

        $.getJSON(Drupal.settings.basePath + 'admin/config/system/configuration/status/' + component, function(data) {
          var items = [];

          $.each(data['status'], function(key, val) {
            var span = $("span[rel='" + key + "']")

            span.addClass('processed').html(val);
            if (val != in_sync) {
              span.addClass('not_in_sync');
              if (data['diff']) {
                span.addClass('processed').html('<a href ="' + Drupal.settings.basePath + 'admin/config/system/configuration/diff/' + key + '">' + val + '</a>');
              }
            }
          });

          if (!--components) {
            $('#configuration-ui-tracking-form').addClass('status-check-completed');
            updateCheckedCount(context);
          }

        });
      });



      $("fieldset.configuration td .form-checkbox").bind('click', function() {

        $("fieldset.configuration input.form-checkbox").each(function() {
          $(this).attr('disabled', 'disabled');
        });

        var current_checkbox = $(this);
        var include_dependencies = $("input[name='include_dependencies']:checked").length;
        var include_optionals = $("input[name='include_optionals']:checked").length;
        if (include_optionals || include_dependencies) {
          var url = 'dependencies_optionals';
          if (!include_optionals) {
            url = 'dependencies';
          }
          if (!include_dependencies) {
            url = 'optionals';
          }
          var original_value = current_checkbox.parents('td').next().html();
          current_checkbox.parents('td').next().html(original_value + ' ' + Drupal.t('(Finding dependencies...)'));
          $.getJSON(Drupal.settings.basePath + 'admin/config/system/configuration/view/' + $(this).val() + '/' + url, function(data) {

            $.each(data, function(index, array) {
              if (current_checkbox.is(':checked')) {
                $("input[value='" + array + "']").attr("checked", "checked");
              }
              else{
                $("input[value='" + array + "']").attr("checked", "");
              }

            });
            current_checkbox.parents('td').next().html(original_value);
            updateCheckedCount(context);

            $("fieldset.configuration input.form-checkbox").each(function() {
              $(this).removeAttr('disabled');
            });

          });
        }
      });

      updateCheckedCount(context);
    }
  }

  Drupal.behaviors.configurationFieldsetSummaries = {
    attach: updateCheckedCount
  }

  function updateCheckedCount(context) {
    var needs_attention = 0;
    $("fieldset.configuration").each(function(){
      var id = '#' + this.id;
      $(this, context).drupalSetSummary(function (context) {
        var count = $(id + ' table .form-item :input[type="checkbox"]:checked').length;

        needs_attention = $(id + ' span.not_in_sync').length;

        if (needs_attention > 0) {
          return Drupal.t('@count selected <strong>(@attention needs attention)</strong>', {'@count': count, '@attention': needs_attention});
        }
        else {
          return Drupal.t('@count selected', {'@count': count});
        }
      });
    });
  }
})(jQuery);
