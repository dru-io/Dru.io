var BUE = window.BUE = window.BUE || {preset: {}, templates: {}, instances: [], preprocess: {}, postprocess: {}};

BUE.postprocess.acebueditor = function (E, $) {
  var ace_src = Drupal.settings.basePath + Drupal.settings.ace_editor.ace_src;
  ace.config.set('basePath', ace_src);

  E.showAcedCodeDialog = function () {
    var title = Drupal.t('Insert code');
    var $form = $('<form class="ace-editor-wrapper"><div id="ace-editor"></div></form>');
    var $button = $('<input type="button" id="ace-insert" class="form-submit" value="' + Drupal.t('OK') + '" />');
    $button.click(function() {
      var insert_text = editor_session.getValue().replace("<?php\n\n", '');
      insert_text = "~~~php\n" + insert_text + "\n~~~";
      E.replaceSelection(insert_text);
      E.dialog.close();
    });
    $form.append($button);
    E.dialog.open(title, $form, 'fadeIn');

    var editor = ace.edit('ace-editor');
    var editor_session = editor.session;
    var initial_value = E.getSelection();
    if(initial_value.indexOf('<?php') == -1) {
      initial_value = "<?php\n\n" + initial_value;
    }
    editor.setValue(initial_value);
    editor.gotoLine(editor.session.getLength() + 1);
    editor.setOptions({
      minLines: 10,
      maxLines: 20,
      theme: 'ace/theme/twilight',
      mode: 'ace/mode/php'
    });
  };
};
