<?php

/**
 * @file
 * Admin page callbacks for druio_gitter module.
 */

/**
 * Form constructor for the gitter settings form.
 */
function druio_gitter_settings_form($form, &$form_state) {

  $urls = variable_get('druio_gitter_webhook_urls');

  $form['help'] = array(
    '#type' => 'markup',
    '#markup' => t('Webhook URLs are used for sending activity messages to Gitter chat rooms.'),
  );

  $form['druio_gitter_webhook_urls']['#tree'] = TRUE;

  foreach (druio_gitter_rooms() as $room_id => $room_label) {
    $form['druio_gitter_webhook_urls'][$room_id] = array(
      '#type' => 'textfield',
      '#title' => $room_label,
      '#default_value' => isset($urls[$room_id]) ? $urls[$room_id] : NULL,
    );
  }

  return system_settings_form($form);
}

/**
 * Send activity form builder.
 */
function druio_gitter_send_activity_form($form, &$form_state) {

  $urls = variable_get('druio_gitter_webhook_urls', array());

  $form['room_id'] = array(
    '#type' => 'select',
    '#title' => t('Gitter room ID'),
    '#options' => array_intersect_key(druio_gitter_rooms(), array_filter($urls)),
    '#required' => TRUE,
  );

  $form['type'] = array(
    '#type' => 'radios',
    '#title' => t('Message type'),
    '#options' => array('normal' => t('Normal'), 'error' => t('Error')),
    '#default_value' => 'normal',
    '#required' => TRUE,
  );

  $form['message'] = array(
    '#type' => 'textarea',
    '#title' => t('Activity message'),
    '#description' => t('The message that you want to be displayed (in markdown).'),
    '#rows' => 20,
    '#required' => TRUE,
  );

  $form['actions']['message'] = array(
    '#type' => 'submit',
    '#value' => t('Send'),
  );

  return $form;
}

/**
 * Activity message form submit handler.
 */
function druio_gitter_send_activity_form_submit($form, &$form_state) {
  druio_gitter_send_acitvity_message(
    $form_state['values']['room_id'],
    $form_state['values']['message'],
    $form_state['values']['type']
  );
}
