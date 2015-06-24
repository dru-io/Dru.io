/**
 * @file
 * Druio_projects module js.
 */

'use strict';
(function ($, Drupal, window, document, undefined) {

  Drupal.behaviors.druio_projects = {
    attach: function (context, settings) {
      $('#add-project-button').once('druio_projects').on('click', function() {
        var project = prompt('Пожалуйста укажите машинное имя проекта, или ссылку на проект с Drupal.org.');

        if (project != null) {
          $.ajax({
            type: 'POST',
            url: '/druio/project/ajax-add',
            data: 'project=' + project,
            success: function(data){
              alert(data);
            }
          });
        }

        return false;
      });
    }
  };

})(jQuery, Drupal, this, this.document);
