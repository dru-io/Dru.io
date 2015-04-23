<?php

/**
 * @file
 * Admin page callbacks for the Search API Solr module.
 */

/**
 * Form constructor for the Solr files overview.
 *
 * @param SearchApiServer $server
 *   The server for which files should be displayed.
 *
 * @ingroup forms
 */
function search_api_solr_solr_config_form($form, &$form_state, SearchApiServer $server) {
  $form['title']['#markup'] = '<h2>' . t('List of configuration files found:') . '</h2>';

  try {
    // Retrieve the list of available files.
    $files_list = search_api_solr_server_get_files($server);

    if (empty($files_list)) {
      $form['info']['#markup'] = t('No files found.');
      return $form;
    }

    $form['files'] = array(
      '#type'  => 'vertical_tabs',
    );

    // Generate a fieldset for each file.
    foreach ($files_list as $file_name => $file_info) {
      $file_date = format_date(strtotime($file_info['modified']));
      $escaped_file_name = check_plain($file_name);

      $form['files'][$file_name] = array(
        '#title' => $escaped_file_name,
        '#type'  => 'fieldset',
      );

      $data = '<h3>' . $escaped_file_name . '</h3>';
      $data .= '<p><em>' . t('Last modified: @time.', array('@time' => $file_date)) . '</em></p>';

      if ($file_info['size'] > 0) {
        $file_data = $server->getFile($file_name);
        $data .= '<pre><code>' . check_plain($file_data->data) . '</code></pre>';
      }
      else {
        $data .= '<p><em>' . t('The file is empty.') . '</em></p>';
      }

      $form['files'][$file_name]['data']['#markup'] = $data;
    }
  }
  catch (SearchApiException $e) {
    watchdog_exception('search_api_solr', $e, '%type while retrieving config files of Solr server @server: !message in %function (line %line of %file).', array('@server' => $server->name));
    $form['info']['#markup'] = t('An error occured while trying to load the list of files.');
  }

  return $form;
}
