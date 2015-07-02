(function ($) {
  Drupal.behaviors.ocuploadTextarea = {
    attach: function (context, settings) {
      if (!Drupal.settings.ocupload || !Drupal.settings.ocupload.allowedExt) {
        return;
      }

      $('textarea.ocupload-drop', context).once('ocupload-drop').each(function () {
        var textarea = this;

        // Lazy create and configure Flow.js object
        if (!Drupal.ocupload.textareaPlugin.flow) {
          Drupal.ocupload.textareaPlugin.createFlow();
        }

        // Process textarea
        if (Drupal.ocupload.textareaPlugin.flow.support) {
          Drupal.ocupload.textareaPlugin.flow.assignDrop(textarea);

          // Hack for IE. IE loses textarea selection on drag start.
          if (Drupal.ocupload.textareaPlugin.isIE) {
            $(textarea).bind('blur', Drupal.ocupload.textareaPlugin.saveSelection);
          }
        }
      });
    }
  };

  Drupal.ocupload = Drupal.ocupload || {};
  Drupal.ocupload.textareaPlugin = Drupal.ocupload.textareaPlugin || {};
  Drupal.ocupload.textareaPlugin.isIE = document.documentMode ? true : false;

  /**
   * Create and configure Flow.js object.
   */
  Drupal.ocupload.textareaPlugin.createFlow = function () {
    Drupal.ocupload.textareaPlugin.flow = Drupal.ocupload.createFlow();

    if (!Drupal.ocupload.textareaPlugin.flow.support) {
      return false;
    }

    Drupal.ocupload.textareaPlugin.flow.on('filesSubmitted', Drupal.ocupload.textareaPlugin.onFilesSubmitted);
    Drupal.ocupload.textareaPlugin.flow.on('fileSuccess', Drupal.ocupload.textareaPlugin.onFileSuccess);
    Drupal.ocupload.textareaPlugin.flow.on('complete', Drupal.ocupload.textareaPlugin.onComplete);

    return true;
  };

  /**
   * Get selected text in textarea.
   */
  Drupal.ocupload.textareaPlugin.getSelectedText = function (element) {
    if (element instanceof jQuery) {
      element = element[0];
    }
    return element.value.substring(element.selectionStart, element.selectionEnd);
  };

  /**
   * Save selection info in element data attribute.
   */
  Drupal.ocupload.textareaPlugin.saveSelection = function (event) {
    var textarea = this;

    $(textarea).data('ocuploadSelection', {
      selectedText: Drupal.ocupload.textareaPlugin.getSelectedText(textarea),
      selectionStart: textarea.selectionStart,
      selectionEnd: textarea.selectionEnd,
    });
  };

  /**
   * Files selected handler.
   */
  Drupal.ocupload.textareaPlugin.onFilesSubmitted = function (files, event) {
    var $textarea = $(event.target).closest('.form-item').find('textarea');
    var selectedText = Drupal.ocupload.textareaPlugin.getSelectedText($textarea);

    // Hack for IE. Restore selection from data
    if (Drupal.ocupload.textareaPlugin.isIE) {
      selectedText = $textarea.data('ocuploadSelection').selectedText;
    }

    Drupal.ocupload.textareaPlugin.flow.opts.query.selectedText = selectedText;
    Drupal.ocupload.textareaPlugin.flow.upload();

    $textarea[0].disabled = true;

    // Save textarea id in global var, because event 'complete' not contains this information
    Drupal.ocupload.textareaPlugin.activeTextareaId = $textarea.attr('id');
  };

  /**
   * File uploaded handler.
   */
  Drupal.ocupload.textareaPlugin.onFileSuccess = function (file, response, chunk) {
    if (!Drupal.ocupload.checkResponse(response)) {
      return;
    }

    response = $.parseJSON(response);

    if (response.status) {
      var $textarea = $('#' + Drupal.ocupload.textareaPlugin.activeTextareaId);
      var textarea = $textarea[0];
      var selectionStart = textarea.selectionStart;
      var selectionEnd = textarea.selectionEnd;
      var insertedText = response.data;

      // Hack for IE
      if (Drupal.ocupload.textareaPlugin.isIE) {
        var selection = $textarea.data('ocuploadSelection');
        selectionStart = selection.selectionStart;
        selectionEnd = selection.selectionEnd;

        textarea.disabled = false;
        textarea.focus();
      }

      if (selectionStart == selectionEnd) {
        insertedText += "\n";
      }

      textarea.value = textarea.value.substring(0, selectionStart)
        + insertedText
        + textarea.value.substring(selectionEnd, textarea.value.length);

      var cursorPosition = selectionStart + insertedText.length;
      textarea.selectionStart = cursorPosition;
      textarea.selectionEnd = cursorPosition;

      // Hack for IE
      if (Drupal.ocupload.textareaPlugin.isIE) {
        textarea.disabled = true;
        $textarea.data('ocuploadSelection', {
          selectionStart: cursorPosition,
          selectionEnd: cursorPosition,
        })
      }
    }
    else {
      alert(response.data);
    }
  };

  /**
   * Files uploaded handler.
   */
  Drupal.ocupload.textareaPlugin.onComplete = function () {
    var $textarea = $('#' + Drupal.ocupload.textareaPlugin.activeTextareaId);
    $textarea[0].disabled = false;
    $textarea.focus();
  };
})(jQuery);
