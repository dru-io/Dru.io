(function ($) {
  Drupal.ocupload = Drupal.ocupload || {};

  /**
   * Create and configure Flow.js object.
   */
  Drupal.ocupload.createFlow = function () {
    // Create Flow.js instance
    var flow = new Flow({
      target: Drupal.settings.basePath + 'ocupload/upload',
      testChunks: false,
      chunkSize: 5*1024*1024,
      simultaneousUploads: 1
    });

    if (!flow.support) {
      return flow;
    }

    flow.on('fileAdded', Drupal.ocupload.onFileAdded);
    flow.on('filesSubmitted', Drupal.ocupload.onFilesSubmitted);
    flow.on('fileProgress', Drupal.ocupload.onFileProgress);
    flow.on('fileSuccess', Drupal.ocupload.onFileSuccess);
    flow.on('error', Drupal.ocupload.onError);
    flow.on('complete', Drupal.ocupload.onComplete);

    return flow;
  };

  /**
   * Return true if response in JSON format.
   */
  Drupal.ocupload.checkResponse = function (response) {
    return $.trim(response.substring(0, 1)) == '{';
  };

  /**
   * File added handler.
   */
  Drupal.ocupload.onFileAdded = function (file, event) {
    if ($.inArray(file.getExtension(), Drupal.settings.ocupload.allowedExt) == -1) {
      alert(Drupal.t('You can not upload files of type .@file_ext', {'@file_ext':file.getExtension()}));
      return false;
    }
  };

  /**
   * Files selected handler.
   */
  Drupal.ocupload.onFilesSubmitted = function (files, event) {
    var flow = this;
    var $textarea = $(event.target).closest('.form-item').find('textarea');
    var $queue = $('#upload-queue');

    if ($queue.length == 0) {
      $queue = $('<div id="upload-queue"></div>').appendTo('body');
    }

    $.each(files, function (index, file) {
      $queue.prepend('<div id="queue-' + file.uniqueIdentifier + '">' + file.name + '</div>');
    });

    flow.opts.query.fieldName = $textarea.attr('name');
    flow.opts.query.formId = $textarea.closest('form').find('input[name="form_id"]').val();
  };

  /**
   * File upload progress handler.
   */
  Drupal.ocupload.onFileProgress = function (file, chunk) {
    var $fileQueue = $('#queue-' + file.uniqueIdentifier);
    $fileQueue.css({
      'background': 'url(' + Drupal.settings.basePath + 'misc/progress.gif) repeat-x 0 center',
      'color': 'white'
    });
  };

  /**
   * File uploaded handler.
   */
  Drupal.ocupload.onFileSuccess = function (file, response, chunk) {
    var $fileQueue = $('#queue-' + file.uniqueIdentifier);
    $fileQueue.hide('fast', function () {
      $fileQueue.remove();
    });

    if (!Drupal.ocupload.checkResponse(response)) {
      alert(Drupal.t('Server response came not in JSON format: @response', {'@response':response}));
    }
  };

  /**
   * Upload error handler.
   */
  Drupal.ocupload.onError = function (message, file, chunk) {
    alert(Drupal.t('Upload error: @message', {'@message': message}))
  };

  /**
   * Files uploaded handler.
   */
  Drupal.ocupload.onComplete = function () {
    var flow = this;
    flow.cancel();
  };
})(jQuery);

// Translate string because plugin.js not visible in locale_js_alter()
// Drupal.t('Upload file');
// Drupal.t('Your browser not support HTML5 File API');
