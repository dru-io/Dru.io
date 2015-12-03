/*global Drupal: false, jQuery: false */
/*jslint devel: true, browser: true, maxerr: 50, indent: 2 */
(function ($) {
  "use strict";

  Drupal.behaviors.hybridauth_vtabs_SettingsSummary = {};
  Drupal.behaviors.hybridauth_vtabs_SettingsSummary.attach = function(context, settings) {
    /* Make sure this behavior is processed only if drupalSetSummary is defined. */
    if (typeof jQuery.fn.drupalSetSummary == 'undefined') {
      return;
    }

    $('#edit-fset-providers', context).drupalSetSummary(function(context) {
      var vals = [];

      $('input', context).each(function (index, Element) {
        if ($(this).is(':checked')) {
          vals.push($.trim($(this).closest('td').next().text()));
        }
      });

      return vals.join(', ');
    });

    $('#edit-fset-fields', context).drupalSetSummary(function(context) {
      var vals = [];

      $('input', context).each(function (index, Element) {
        if ($(this).is(':checked')) {
          vals.push($.trim($(this).next().text()));
        }
      });

      return vals.join(', ');
    });

    $('#edit-fset-widget', context).drupalSetSummary(function(context) {
      var vals = [];

      var value = $('#edit-hybridauth-widget-title', context).attr('value');
      var label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-widget-title"]', context).text()) + '</span>';
      if (value) {
        vals.push(label + ': ' + value);
      }
      else {
        vals.push(label + ': ' + Drupal.t('None'));
      }

      var widget_type;
      label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-widget-type"]', context).text()) + '</span>';
      var list = [];
      $('#edit-hybridauth-widget-type', context).find('label').each(function(index, Element) {
        var label_for = $(this).attr('for');
        if ($('#' + label_for).is(':checked')) {
          list.push($.trim($(this).text()));
          widget_type = $('#' + label_for).val();
        }
      });
      vals.push(label + ': ' + list.join(', '));

      if (widget_type == 'link') {
        value = $('#edit-hybridauth-widget-link-text', context).attr('value');
        label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-widget-link-text"]', context).text()) + '</span>';
        if (value) {
          vals.push(label + ': ' + value);
        }
        else {
          vals.push(label + ': ' + Drupal.t('None'));
        }
      }

      if (widget_type == 'link' || widget_type == 'button') {
        value = $('#edit-hybridauth-widget-link-title', context).attr('value');
        label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-widget-link-title"]', context).text()) + '</span>';
        if (value) {
          vals.push(label + ': ' + value);
        }
        else {
          vals.push(label + ': ' + Drupal.t('None'));
        }
      }

      label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-widget-icon-pack"]', context).text()) + '</span>';
      value = $('#edit-hybridauth-widget-icon-pack', context).find('option:selected').text();
      vals.push(label + ': ' + value);

      label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-widget-weight"]', context).text()) + '</span>';
      value = $('#edit-hybridauth-widget-weight', context).attr('value');
      if (value) {
        vals.push(label + ': ' + value);
      }
      else {
        vals.push(label + ': ' + Drupal.t('None'));
      }

      return vals.join('<br />');
    });

    $('#edit-fset-account', context).drupalSetSummary(function(context) {
      var vals = [];

      $('label', context).each(function (index, Element) {
        var label_for = $(this).attr('for');
        if ((label_for == 'edit-hybridauth-disable-username-change' || label_for == 'edit-hybridauth-remove-password-fields'
          || label_for == 'edit-hybridauth-pictures' || label_for == 'edit-hybridauth-override-realname'
          || label_for == 'edit-hybridauth-registration-username-change' || label_for == 'edit-hybridauth-registration-password')
          && $('#' + label_for).is(':checked')) {
          vals.push($.trim($(this).text()));
        }
        var label, value;
        if (label_for == 'edit-hybridauth-email-verification' || label_for == 'edit-hybridauth-register') {
          label = '<span style="font-weight:bold;">' + $.trim($(this).text()) + '</span>';
          $('#' + label_for, context).find('label').each(function(index, Element) {
            var label_for = $(this).attr('for');
            if ($('#' + label_for).is(':checked')) {
              value = $.trim($(this).text());
            }
          });
          vals.push(label + ': ' + value);
        }
        /*if (label_for == 'edit-hybridauth-username' || label_for == 'edit-hybridauth-display-name') {
          label = '<span style="font-weight:bold;">' + $(this).text() + '</span>';
          value = $('#' + label_for).val();
          vals.push(label + ': ' + value);
        }*/
      });

      return vals.join('<br />');
    });

    $('#edit-fset-forms', context).drupalSetSummary(function(context) {
      var vals = [];

      var label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-forms"]', context).text()) + '</span>';
      var list = [];
      $('#edit-hybridauth-forms', context).find('label').each(function(index, Element) {
        var label_for = $(this).attr('for');
        if ($('#' + label_for).is(':checked')) {
          list.push($.trim($(this).text()));
        }
      });
      vals.push(label + ': ' + list.join(', '));

      return vals.join('<br />');
    });

    $('#edit-fset-other', context).drupalSetSummary(function(context) {
      var vals = [];

      var value = $('#edit-hybridauth-destination', context).attr('value');
      var label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-destination"]', context).text()) + '</span>';
      if (value) {
        vals.push(label + ': ' + value);
      }
      else {
        vals.push(label + ': ' + Drupal.t('return to the same page'));
      }
      value = $('#edit-hybridauth-destination-error', context).attr('value');
      label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-destination-error"]', context).text()) + '</span>';
      if (value) {
        vals.push(label + ': ' + value);
      }
      else {
        vals.push(label + ': ' + Drupal.t('return to the same page'));
      }

      label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-duplicate-emails"]', context).text()) + '</span>';
      var list = [];
      $('#edit-hybridauth-duplicate-emails', context).find('label').each(function(index, Element) {
        var label_for = $(this).attr('for');
        if ($('#' + label_for).is(':checked')) {
          list.push($.trim($(this).text()));
        }
      });
      vals.push(label + ': ' + list.join(', '));

      value = $('#edit-hybridauth-proxy', context).attr('value');
      label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-proxy"]', context).text()) + '</span>';
      if (value) {
        vals.push(label + ': ' + value);
      }
      else {
        vals.push(label + ': ' + Drupal.t('None'));
      }

      label = '<span style="font-weight:bold;">' + $.trim($('label[for="edit-hybridauth-debug"]', context).text()) + '</span>';
      if ($('#edit-hybridauth-debug', context).is(':checked')) {
        vals.push(label + ': ' + Drupal.t('Enabled'));
      }
      else {
        vals.push(label + ': ' + Drupal.t('Disabled'));
      }

      return vals.join('<br />');
    });
  };

})(jQuery);
