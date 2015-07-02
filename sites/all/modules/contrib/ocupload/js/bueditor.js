BUE.postprocess.ocupload = function (E, $) {
  // Upload button click handler
  E.showFileSelectionDialog = function () {
    if (!Drupal.ocupload.bueditorPlugin.flow.support) {
      alert(Drupal.t('Your browser not support HTML5 File API'));
      return;
    }

    var $button = $(this.buttons[this.bindex]);
    $button.parent().click();
  }

  // Search upload button
  var $button;
  for (var i = 0; i < E.tpl.buttons.length; i++) {
    if ($.trim(E.tpl.buttons[i][1]) == 'js: E.showFileSelectionDialog();') {
      $button = $('#bue-' + E.index + '-button-' + i);
      break;
    }
  }
  if (!$button) {
    return;
  }

  if (!Drupal.settings.ocupload || !Drupal.settings.ocupload.allowedExt) {
    $button.remove();
    return;
  }

  // Lazy create and configure Flow.js object
  if (!Drupal.ocupload.bueditorPlugin.flow) {
    Drupal.ocupload.bueditorPlugin.createFlow();
  }

  // Process upload button
  if (Drupal.ocupload.bueditorPlugin.flow.support) {
    $buttonWrapper = $button.wrap('<span class="ocupload-button-wrapper"></span>').parent();
    Drupal.ocupload.bueditorPlugin.flow.assignBrowse($buttonWrapper[0]);
    Drupal.ocupload.bueditorPlugin.flow.assignDrop($buttonWrapper[0]);
  }
};

(function ($) {
  Drupal.ocupload = Drupal.ocupload || {};
  Drupal.ocupload.bueditorPlugin = Drupal.ocupload.bueditorPlugin || {};

  /**
   * Create and configure Flow.js object.
   */
  Drupal.ocupload.bueditorPlugin.createFlow = function () {
    Drupal.ocupload.bueditorPlugin.flow = Drupal.ocupload.createFlow();

    if (!Drupal.ocupload.bueditorPlugin.flow.support) {
      return false;
    }

    Drupal.ocupload.bueditorPlugin.flow.on('filesSubmitted', Drupal.ocupload.bueditorPlugin.onFilesSubmitted);
    Drupal.ocupload.bueditorPlugin.flow.on('fileSuccess', Drupal.ocupload.bueditorPlugin.onFileSuccess);
    Drupal.ocupload.bueditorPlugin.flow.on('complete', Drupal.ocupload.bueditorPlugin.onComplete);

    return true;
  };

  /**
   * Get BUEditor instance by textarea id.
   */
  Drupal.ocupload.bueditorPlugin.getBueditorInstance = function (textareaId) {
    var instance;
    $.each(BUE.instances, function (index, value) {
      if (value.textArea.id == textareaId) {
        instance = value;
      }
    });
    return instance;
  };

  /**
   * Files selected handler.
   */
  Drupal.ocupload.bueditorPlugin.onFilesSubmitted = function (files, event) {
    var $textarea = $(event.target).closest('.form-item').find('textarea');
    var editorInstance = Drupal.ocupload.bueditorPlugin.getBueditorInstance($textarea.attr('id'));

    Drupal.ocupload.bueditorPlugin.flow.opts.query.selectedText = editorInstance.getSelection();
    Drupal.ocupload.bueditorPlugin.flow.upload();

    editorInstance.textArea.disabled = true;

    // Save textarea id in global var, because event 'complete' not contains this information
    Drupal.ocupload.bueditorPlugin.activeTextareaId = $textarea.attr('id');
  };

  /**
   * File uploaded handler.
   */
  Drupal.ocupload.bueditorPlugin.onFileSuccess = function (file, response, chunk) {
    if (!Drupal.ocupload.checkResponse(response)) {
      return;
    }

    response = $.parseJSON(response);

    if (response.status) {
      var editorInstance = Drupal.ocupload.bueditorPlugin.getBueditorInstance(Drupal.ocupload.bueditorPlugin.activeTextareaId);
      var insertedData = editorInstance.getSelection() ? response.data : response.data + "\n";
      editorInstance.replaceSelection(insertedData, 'end');
    }
    else {
      alert(response.data);
    }
  };

  /**
   * Files uploaded handler.
   */
  Drupal.ocupload.bueditorPlugin.onComplete = function () {
    var editorInstance = Drupal.ocupload.bueditorPlugin.getBueditorInstance(Drupal.ocupload.bueditorPlugin.activeTextareaId);
    editorInstance.textArea.disabled = false;
    editorInstance.focus();
  };
})(jQuery);
