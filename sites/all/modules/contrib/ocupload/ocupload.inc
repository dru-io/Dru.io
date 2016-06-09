<?php

/**
 * @file
 * One Click Upload includes.
 */

/**
 * Admin page with templates list.
 */
function ocupload_config_page() {
  $build = array();

  // Table
  $table_data = array();
  foreach (ocupload_templates() as $template) {
    $actions = array(
      l(t('edit'), 'admin/config/content/ocupload/edit/' . $template->tid),
      l(t('delete'), 'admin/config/content/ocupload/delete/' . $template->tid),
    );
    $table_data[] = array($template->mask, implode(' | ', $actions));
  }
  $build['templates'] = array(
    '#theme' => 'table',
    '#header' => array(t('Mask'), t('Operations')),
    '#rows' => $table_data,
    '#prefix' => '<h3>' . t('Templates') . '</h3>',
    '#suffix' => '<br />',
  );

  // Form
  $build['form'] = drupal_get_form('ocupload_form_settings');
  $build['form']['#prefix'] = '<h3>' . t('Options') . '</h3>';

  return $build;
}

/**
 * Form module settings.
 */
function ocupload_form_settings($form, &$form_state) {
  $form['ocupload_textarea_dragndrop'] = array(
    '#type' => 'checkbox',
    '#title' => t('Drag & Drop files inside textareas'),
    '#description' => t('Add support Drag & Drop files inside textareas.'),
    '#default_value' => variable_get('ocupload_textarea_dragndrop', 1),
  );

  return system_settings_form($form);
}

/**
 * Form add/edit template.
 */
function ocupload_form_template($form, &$form_state, $template = NULL) {
  if (!$template) {
    $template = (object)array(
      'tid' => 0,
      'mask' => '',
      'path' => 'public://inline',
      'filename' => '',
      'max_filesize' => 0,
      'template' => '<a href="!filepath">!filename</a>',
      'template_select' => '<a href="!filepath">!text</a>',
      'image_style' => '',
      'image_style_original' => '',
      'link_to_original' => 0,
      'link_template' => '<a href="!filepath" target="_blank">!image</a>',
      'link_only_big' => 0,
      'max_dimensions' => '',
      'field' => '',
      'transliterate' => 1,
    );
    drupal_set_title(t('Add template'));
  }

  $token_tree_link = t('Can use tokens');
  if (module_exists('token')) {
    $token_tree_link = theme('token_tree_link', array(
      'text' => $token_tree_link,
      'token_types' => array('user'),
    ));
  }

  $form['tid'] = array(
    '#type' => 'value',
    '#value' => $template->tid,
  );

  $form['mask'] = array(
    '#type' => 'textfield',
    '#title' => t('File extensions'),
    '#description' => t('Comma separated list of file extensions which should be handled within this template. Example: <code>jpg,gif,png</code>'),
    '#default_value' => $template->mask,
    '#size' => 40,
    '#required' => TRUE,
  );

  $form['path'] = array(
    '#type' => 'textfield',
    '#title' => t('Upload path'),
    '#description' => t('Do not include preceding or trailing slashes. Example: <code>public://images</code>') . '<br />' . $token_tree_link . '.',
    '#default_value' => $template->path,
    '#size' => 40,
    '#required' => TRUE,
  );

  $form['filename'] = array(
    '#type' => 'textfield',
    '#title' => t('Filename'),
    '#description' => t('New filename without extension. Leave blank if you don\'t want to rename files.') . ' ' . $token_tree_link . '.',
    '#default_value' => $template->filename,
    '#size' => 40,
  );

  $form['max_filesize'] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum upload size'),
    '#default_value' => $template->max_filesize ? format_size($template->max_filesize, 'en') : 0,
    '#description' => t('Enter a value like "512" (bytes), "80 KB" (kilobytes) or "50 MB" (megabytes) in order to restrict the allowed file size.'),
    '#size' => 10,
  );

  $form['template'] = array(
    '#type' => 'textfield',
    '#title' => t('Template to insert into editor'),
    '#default_value' => $template->template,
    '#maxlength' => NULL,
    '#size' => 100,
    '#required' => TRUE,
  );

  $form['template_select'] = array(
    '#type' => 'textfield',
    '#title' => t('Template to replace selected text'),
    '#default_value' => $template->template_select,
    '#maxlength' => NULL,
    '#size' => 100,
    '#required' => TRUE,
  );

  $form['help'] = array(
    '#type' => 'fieldset',
    '#title' => t('Replacement patterns'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  $form['help']['text'] = array(
    '#markup' => '
      !filepath     — ' . t('full path to file') . '<br />
      !filename     — ' . t('file name after upload') . '<br />
      !originalname — ' . t('original file name') . '<br />
      !fileext      — ' . t('file extension') . '<br />
      !filesize     — ' . t('file size') . '<br />
      !text         — ' . t('selected text') . '<br />
    ',
  );

  if (module_exists('image')) {
    $form['image_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Options for images'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['image_settings']['image_style'] = array(
      '#type' => 'select',
      '#title' => t('Use image style'),
      '#options' => image_style_options(),
      '#default_value' => $template->image_style,
    );

    $form['image_settings']['link_to_original'] = array(
      '#type' => 'checkbox',
      '#title' => t('Add link to original image'),
      '#default_value' => $template->link_to_original,
      '#states' => array(
        'invisible' => array(
          'select[name="image_style"]' => array('value' => ''),
        ),
      ),
    );

    $form['image_settings']['link_template'] = array(
      '#type' => 'textfield',
      '#title' => t('Link template'),
      '#default_value' => $template->link_template,
      '#maxlength' => NULL,
      '#states' => array(
        'visible' => array(
          'input[name="link_to_original"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['image_settings']['link_only_big'] = array(
      '#type' => 'checkbox',
      '#title' => t('Add link only for those images which size exceeds the size, specified in the style'),
      '#default_value' => $template->link_only_big,
      '#states' => array(
        'visible' => array(
          'input[name="link_to_original"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['image_settings']['use_image_style_original'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use image style for original image'),
      '#default_value' => (bool)$template->image_style_original,
      '#states' => array(
        'visible' => array(
          'input[name="link_to_original"]' => array('checked' => TRUE),
        )
      ),
    );

    $form['image_settings']['image_style_original'] = array(
      '#type' => 'select',
      '#title' => t('Image style for original image'),
      '#options' => image_style_options(FALSE),
      '#default_value' => $template->image_style_original,
      '#states' => array(
        'visible' => array(
          'input[name="use_image_style_original"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['image_settings']['max_dimensions'] = array(
      '#type' => 'textfield',
      '#title' => 'Maximum image resolution',
      '#description' => t('The maximum allowed image size expressed as WIDTHxHEIGHT (e.g. 640x480). Leave blank for no restriction. If a larger image is uploaded, it will be resized to reflect the given width and height. Resizing images on upload will cause the loss of <a href="http://en.wikipedia.org/wiki/Exchangeable_image_file_format">EXIF data</a> in the image.'),
      '#default_value' => $template->max_dimensions,
      '#size' => 10,
    );
  }

  $options = array();
  foreach (field_info_field_map() as $field_name => $field_info) {
    if ($field_info['type'] == 'file' || $field_info['type'] == 'image') {
      $options[$field_name] = $field_name;
    }
  }
  $form['field'] = array(
    '#type' => 'select',
    '#title' => t('Entity field'),
    '#description' => t('The entity file or image field in which uploaded files will be stored.'),
    '#options' => $options,
    '#empty_option' => t('<none>'),
    '#default_value' => $template->field,
  );

  $transliteration_module_exists = module_exists('transliteration');
  $transliteration_module_link = l(
    'Transliteration',
    'https://www.drupal.org/project/transliteration',
    array('external' => TRUE)
  );
  $form['transliterate'] = array(
    '#type' => 'checkbox',
    '#title' => t('Transliterate file names'),
    '#description' => t('Check to transliterate file names when saving. Recommended to avoid file URL issues, and requires the !transliteration module.',
      array(
        '!transliteration' => $transliteration_module_link,
      )
    ),
    '#default_value' => $transliteration_module_exists && $template->transliterate,
    '#disabled' => !$transliteration_module_exists,
  );

  $form['roles'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Roles'),
    '#description' => t('Checked roles are allowed to upload files of this type.'),
    '#options' => array_map('check_plain', user_roles()),
    '#default_value' => array_keys(user_roles(FALSE, 'upload files use template ' . $template->tid)),
  );

  $form['actions'] = array(
    '#type' => 'actions',
  );

  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => $template->tid ? t('Save template') : t('Add template'),
  );

  return $form;
}

/**
 * Form submit.
 */
function ocupload_form_template_submit($form, &$form_state) {
  $template = array(
    'tid'             => $form_state['values']['tid'],
    'mask'            => str_replace(' ', '', trim($form_state['values']['mask'])),
    'path'            => $form_state['values']['path'],
    'filename'        => $form_state['values']['filename'],
    'max_filesize'    => parse_size($form_state['values']['max_filesize']),
    'template'        => $form_state['values']['template'],
    'template_select' => $form_state['values']['template_select'],
    'field'           => $form_state['values']['field'],
    'transliterate'   => $form_state['values']['transliterate'],
  );

  if (module_exists('image')) {
    $template += array(
      'image_style'          => $form_state['values']['image_style'],
      'link_to_original'     => $form_state['values']['link_to_original'],
      'link_template'        => $form_state['values']['link_template'],
      'link_only_big'        => $form_state['values']['link_only_big'],
      'image_style_original' => $form_state['values']['use_image_style_original'] ? $form_state['values']['image_style_original'] : '',
      'max_dimensions'       => $form_state['values']['max_dimensions'],
    );
  }

  ocupload_template_save($template);

  // Save permissions
  foreach ($form_state['values']['roles'] as $rid => $enabled) {
    user_role_change_permissions($rid, array('upload files use template ' . $template['tid'] => $enabled));
  }

  drupal_set_message($form_state['values']['tid'] ? t('Template saved') : t('Template added'));
  $form_state['redirect'] = 'admin/config/content/ocupload';
}

/**
 * Confirm form delete teamplate.
 */
function ocupload_delete_confirm($form, &$form_state, $template) {
  $form['tid'] = array(
    '#type' => 'value',
    '#value' => $template->tid,
  );

  return confirm_form(
    $form,
    t('Do you really want to delete template') . ' ' . $template->tid . '?',
    'admin/config/content/ocupload'
  );
}

/**
 * Confirm form submit.
 */
function ocupload_delete_confirm_submit($form, &$form_state) {
  ocupload_template_delete($form_state['values']['tid']);
  drupal_set_message(t('Template deleted'));
  $form_state['redirect'] = 'admin/config/content/ocupload';
}

/**
 * Upload file.
 */
function ocupload_upload() {
  global $user;

  // Check errors
  if (empty($_FILES)) {
    return _ocupload_answer(FALSE, t('File are missing'));
  }
  if ($_FILES['file']['error'] != 0) {
    return _ocupload_answer(FALSE, t('Error #@error in file @filename', array(
      '@error' => $_FILES['file']['error'],
      '@filename' => $_FILES['file']['name'],
    )));
  }

  $file_name = $_FILES['file']['name'];
  $file_extension = _ocupload_get_file_extension($file_name);
  $template = _ocupload_get_appropriate_template($file_extension);

  // Check errors
  if (!$template) {
    return _ocupload_answer(FALSE, t('This file type can not be upload'));
  }
  if ($template->max_filesize && $_FILES['file']['size'] > $template->max_filesize) {
    return _ocupload_answer(FALSE, t('Too large file size'));
  }

  // Change filename
  if ($template->filename) {
    $file_name = token_replace($template->filename, array('user' => $user)) . '.' . $file_extension;
    $file_name = drupal_basename($file_name);
  }

  // Transliterate filename
  if ($template->transliterate && module_exists('transliteration')) {
    $file_name = transliteration_clean_filename($file_name);
  }

  // Process upload path
  $upload_path = token_replace($template->path, array('user' => $user));
  if (!file_uri_scheme($upload_path)) {
    $upload_path = file_default_scheme() . '://' . $upload_path;
  }
  file_prepare_directory($upload_path, FILE_CREATE_DIRECTORY);

  // Process temporary path
  $temporary_path = 'temporary://ocupload';
  file_prepare_directory($temporary_path, FILE_CREATE_DIRECTORY);

  $file_destination = file_destination($upload_path . '/' . $file_name, FILE_EXISTS_RENAME);

  _ocupload_load_flowphp();
  \Flow\Uploader::pruneChunks($temporary_path, 3600);

  // Save file or chunk
  if (!\Flow\Basic::save($file_destination, $temporary_path)) {
    return _ocupload_answer(TRUE, t('Chunk saved'));
  }

  $file = new stdClass();
  $file->uid      = $user->uid;
  $file->status   = 0;
  $file->filename = $_FILES['file']['name'];
  $file->uri      = $file_destination;
  $file->filemime = file_get_mimetype($file_destination);
  $file->filesize = filesize($file_destination);

  // File validate
  $validators = array();
  if ($template->max_filesize) {
    $validators['file_validate_size'] = array($template->max_filesize);
  }
  if (_ocupload_is_image($file->filename)) {
    $validators['file_validate_is_image'] = array();
    if ($template->max_dimensions) {
      $validators['file_validate_image_resolution'] = array($template->max_dimensions);
    }
  }
  if ($errors = file_validate($file, $validators)) {
    file_unmanaged_delete($file->uri);
    $error_message = t('The specified file %name could not be uploaded.', array('%name' => $file->filename));
    foreach ($errors as $error) {
      $error_message .= "\n- $error";
    }
    return _ocupload_answer(FALSE, $error_message);
  }

  drupal_alter('ocupload_uploaded_file', $file);
  file_save($file);

  $selected_text = !empty($_POST['selectedText']) ? $_POST['selectedText'] : '';
  $cur_template = $selected_text ? $template->template_select : $template->template;
  $filepath = $file->uri;

  // If upload image and template image style not empty
  if ($selected_text == '' && _ocupload_is_image($file->filename) && $template->image_style && module_exists('image')) {
    $filepath = image_style_url($template->image_style, $file->uri);

    if ($template->link_to_original) {
      $wrap_template = TRUE;
      if ($template->link_only_big) {
        $image_style = image_style_load($template->image_style);
        $image_info = image_get_info($file->uri);
        $styled_image_dimensions = array(
          'width' => $image_info['width'],
          'height' => $image_info['height'],
        );

        image_style_transform_dimensions($template->image_style, $styled_image_dimensions);

        if ($image_info['width'] <= $styled_image_dimensions['width'] && $image_info['height'] <= $styled_image_dimensions['height']) {
          $wrap_template = FALSE;
        }
      }
      if ($wrap_template) {
        $original_image_uri = $file->uri;
        if ($template->image_style_original) {
          $original_image_uri = image_style_url($template->image_style_original, $original_image_uri);
        }
        $cur_template = strtr($template->link_template, array(
          '!filepath' => _ocupload_get_local_url($original_image_uri),
          '!image' => $cur_template,
        ));
      }
    }
  }

  $data = strtr($cur_template, array(
    '!filepath'     => _ocupload_get_local_url($filepath),
    '!filename'     => drupal_basename($file->uri),
    '!originalname' => $file->filename,
    '!fileext'      => $file_extension,
    '!filesize'     => format_size($file->filesize),
    '!text'         => $selected_text,
  ));

  // Save files info in cache
  $cid = 'ocupload:' . check_plain($_POST['formId']) . ':' . $user->uid;
  if ($cache = cache_get($cid)) {
    $files = $cache->data;
  }
  $field_name = check_plain($_POST['fieldName']);
  $files[$field_name][$file->fid] = drupal_basename($file->uri);
  cache_set($cid, $files, 'cache', REQUEST_TIME + DRUPAL_MAXIMUM_TEMP_FILE_AGE);

  return _ocupload_answer(TRUE, $data);
}

/**
 * Add/save template.
 */
function ocupload_template_save(&$template) {
  $template_array = (array)$template;
  $primary_keys = !empty($template_array['tid']) ? 'tid' : array();
  return drupal_write_record('ocupload_templates', $template, $primary_keys);
}

/**
 * Delete template.
 */
function ocupload_template_delete($tid) {
  db_delete('ocupload_templates')->condition('tid', $tid)->execute();
}

/**
 * Return file extension.
 */
function _ocupload_get_file_extension($filename) {
  return drupal_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Return local url by uri. Example:
 * "_ocupload_get_local_url('public://images/logo.jpg')" return
 * "/sites/default/files/images/logo.jpg"
 */
function _ocupload_get_local_url($uri) {
  $url = file_create_url($uri);
  if (strpos($url, $GLOBALS['base_root']) === 0) {
    return drupal_substr($url, drupal_strlen($GLOBALS['base_root']));
  }
  return $url;
}

/**
 * Return TRUE if file extension equal jpg,gif,png,bmp.
 */
function _ocupload_is_image($filename) {
  $extension = _ocupload_get_file_extension($filename);
  return in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'bmp'));
}

/**
 * Return answer in json format.
 */
function _ocupload_answer($status, $data) {
  drupal_json_output(array(
    'status' => $status,
    'data' => $data,
  ));
}

/**
 * Element validate callback for the maximum upload size field.
 * Ensure a size that can be parsed by parse_size() has been entered.
 */
function _ocupload_check_max_filesize($element, &$form_state) {
  if (!empty($element['#value']) && !is_numeric(parse_size($element['#value']))) {
    form_error($element, t('The "!name" option must contain a valid value. You may either leave the text field empty or enter a string like "512" (bytes), "80 KB" (kilobytes) or "50 MB" (megabytes).', array('!name' => t($element['title']))));
  }
}

/**
 * Return appropriate template for current user.
 */
function _ocupload_get_appropriate_template($file_extension) {
  static $cache;

  if (!isset($cache[$file_extension])) {
    foreach (ocupload_templates() as $template) {
      $extensions = explode(',', $template->mask);
      if (in_array($file_extension, $extensions) && user_access('upload files use template ' . $template->tid)) {
        $cache[$file_extension] = $template;
      }
    }
  }

  return $cache[$file_extension];
}

/**
 * Return value by input name.
 */
function _ocupload_field_value_by_html_name($form_state, $field_name) {
  $parts = explode('[', $field_name);
  $value = &$form_state['values'];

  foreach ($parts as $part) {
    $part = rtrim($part, ']');
    if (isset($value[$part])) {
      $value = &$value[$part];
    }
    else {
      $message = 'Uploaded files will not be saved. Field "@field_name" does not exist in "@form_id" form state. Contact site administrator.';
      $message_vars = array(
        '@field_name' => $field_name,
        '@form_id' => $form_state['build_info']['form_id'],
      );
      drupal_set_message(t($message, $message_vars), 'error');
      watchdog('ocupload', $message, $message_vars, WATCHDOG_ERROR);
      return;
    }
  }

  return $value;
}
