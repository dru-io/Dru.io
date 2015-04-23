var BUE = window.BUE = window.BUE || {preset: {}, templates: {}, instances: [], preprocess: {}, postprocess: {}};
BUE.postprocess.acebueditor = function (E, $) {
  E.showAceEditorDialog = function () {
    var title = Drupal.t('Insert code');
    var content = $('<textarea class="aceeditor-textarea"></textarea>');
    BUE.dialog.open(title, content, 'fadeIn');
  };
};