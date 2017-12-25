<?php
/**
 * Administer page druio_telegram
 */
function druio_telegram_config_form($form, &$form_state){
  $form['druio_telegram_apikey'] = array(
	'#required' => TRUE,
	'#type' => 'textfield',
	'#title' => t('Bot apikey:'),
	'#default_value' => variable_get('druio_telegram_apikey'),
	'#description' => t('Enter apikey telegram bot. ') .
					  l('Details how to get bot apikey.',
						'https://core.telegram.org/bots#botfather',
						array('attributes' => array('target' => '_blank'))),
  );

  $form['druio_telegram_channel_name'] = array(
	'#required' => TRUE,
	'#type' => 'textfield',
	'#title' => t('Channel name:'),
	'#default_value' => variable_get('druio_telegram_channel_name'),
	'#description' => t('Enter telegram channel name. Example: <u>@lib365</u>'),
  );

  return system_settings_form($form);
}

function druio_telegram_config_form_validate($form, &$form_state) {
  if (!preg_match('/^(@+[a-z0-9\-\_\s]+)/i',trim($form_state['values']['druio_telegram_channel_name'])))
	form_set_error('pr_pas', t('Please, insert correct channel name. Example: <u>@lib365</u>'));
}