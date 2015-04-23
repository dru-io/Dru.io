<?php
/**
 * @file
 * HybridAuth Facebook specific settings.
 */

/**
 * Plugin declaration.
 */
$plugin = array(
  'configuration_form_callback' => 'hybridauth_provider_facebook_configuration_form_callback',
  'configuration_callback' => 'hybridauth_provider_facebook_configuration_callback',
);

function hybridauth_provider_facebook_configuration_form_callback(&$form, $provider_id) {
  unset($form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_key']);

  $form['vtabs']['application']['#description'] = t('<p>Enter your application ID and private key. You can get these by creating a new application at <a href="!apps_uri">!apps_uri</a>.</p>'
    . '<p>You must set <strong>App Domain</strong> to something like <strong>example.com</strong> to cover <strong>*.example.com</strong>.</p>'
    . '<p>You must set <strong>Site URL</strong> to <strong>%site_uri</strong>.</p>',
    array(
      '!apps_uri' => 'https://developers.facebook.com/apps',
      '%site_uri' => url('<front>', array('absolute' => TRUE)),
    )
  );

  $form['vtabs']['window_settings']['hybridauth_provider_' . $provider_id . '_display'] = array(
    '#type' => 'radios',
    '#title' => t('Display type'),
    '#options' => array(
      'page' => t('Page'),
      'popup' => t('Popup'),
    ),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_display', ''),
    '#description' => t('Determines how the authentication window is rendered.'),
  );
  $form['vtabs']['advanced'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced settings'),
  );
  $form['vtabs']['advanced']['hybridauth_provider_' . $provider_id . '_scope'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Scope'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_scope', hybridauth_provider_facebook_scope_default()),
    '#options' => array(
      // See http://developers.facebook.com/docs/reference/api/permissions/
      // Regular permissions.
      'user_about_me'             => '<strong>user_about_me</strong> -- ' . t('Provides access to the "About Me" section of the profile in the about property'),
      'user_activities'           => '<strong>user_activities</strong> -- ' . t("Provides access to the user's list of activities as the activities connection"),
      'user_birthday'             => '<strong>user_birthday</strong> -- ' . t('Provides access to the birthday with year as the birthday_date property'),
      'user_checkins'             => '<strong>user_checkins</strong> -- ' . t("Provides read access to the authorized user's check-ins that the user can see."),
      'user_education_history'    => '<strong>user_education_history</strong> -- ' . t('Provides access to education history as the education property'),
      'user_events'               => '<strong>user_events</strong> -- ' . t('Provides access to the list of events the user is attending as the events connection'),
      'user_groups'               => '<strong>user_groups</strong> -- ' . t('Provides access to the list of groups the user is a member of as the groups connection'),
      'user_hometown'             => '<strong>user_hometown</strong> -- ' . t("Provides access to the user's hometown in the hometown property"),
      'user_interests'            => '<strong>user_interests</strong> -- ' . t("Provides access to the user's list of interests as the interests connection"),
      'user_likes'                => '<strong>user_likes</strong> -- ' . t('Provides access to the list of all of the pages the user has liked as the likes connection'),
      'user_location'             => '<strong>user_location</strong> -- ' . t("Provides access to the user's current location as the location property"),
      'user_notes'                => '<strong>user_notes</strong> -- ' . t("Provides access to the user's notes as the notes connection"),
      'user_online_presence'      => '<strong>user_online_presence</strong> -- ' . t("Provides access to the user's online/offline presence"),
      'user_photos'               => '<strong>user_photos</strong> -- ' . t('Provides access to the photos the user has uploaded, and photos the user has been tagged in'),
      'user_questions'            => '<strong>user_questions</strong> -- ' . t('Provides access to the questions the user or friend has asked'),
      'user_relationships'        => '<strong>user_relationships</strong> -- ' . t("Provides access to the user's family and personal relationships and relationship status"),
      'user_relationship_details' => '<strong>user_relationship_details</strong> -- ' . t("Provides access to the user's relationship preferences"),
      'user_religion_politics'    => '<strong>user_religion_politics</strong> -- ' . t("Provides access to the user's religious and political affiliations"),
      'user_status'               => '<strong>user_status</strong> -- ' . t("Provides access to the user's most recent status message"),
      'user_videos'               => '<strong>user_videos</strong> -- ' . t('Provides access to the videos the user has uploaded, and videos the user has been tagged in'),
      'user_website'              => '<strong>user_website</strong> -- ' . t("Provides access to the user's web site URL"),
      'user_work_history'         => '<strong>user_work_history</strong> -- ' . t('Provides access to work history as the work property'),
      'email'                     => '<strong>email</strong> -- ' . t("Provides access to the user's primary email address in the email property. Do not spam users. Your use of email must comply both with Facebook policies and with the CAN-SPAM Act."),

      // Extended permissions.
      'read_friendlists'     => '<strong>read_friendlists</strong> -- ' . t("Provides access to any friend lists the user created. All user's friends are provided as part of basic data, this extended permission grants access to the lists of friends a user has created, and should only be requested if your application utilizes lists of friends."),
      'read_insights'        => '<strong>read_insights</strong> -- ' . t('Provides read access to the Insights data for pages, applications, and domains the user owns.'),
      'read_mailbox'         => '<strong>read_mailbox</strong> -- ' . t("Provides the ability to read from a user's Facebook Inbox."),
      'read_requests'        => '<strong>read_requests</strong> -- ' . t("Provides read access to the user's friend requests"),
      'read_stream'          => '<strong>read_stream</strong> -- ' . t("Provides access to all the posts in the user's News Feed and enables your application to perform searches against the user's News Feed"),
      'xmpp_login'           => '<strong>xmpp_login</strong> -- ' . t('Provides applications that integrate with Facebook Chat the ability to log in users.'),
      'ads_management'       => '<strong>ads_management</strong> -- ' . t('Provides the ability to manage ads and call the Facebook Ads API on behalf of a user.'),
      'create_event'         => '<strong>create_event</strong> -- ' . t("Enables your application to create and modify events on the user's behalf"),
      'manage_friendlists'   => '<strong>manage_friendlists</strong> -- ' . t("Enables your app to create and edit the user's friend lists."),
      'manage_notifications' => '<strong>manage_notifications</strong> -- ' . t('Enables your app to read notifications and mark them as read. This permission will be required to all access to notifications after October 22, 2011.'),
      'offline_access'       => '<strong>offline_access</strong> -- ' . t('Enables your app to perform authorized requests on behalf of the user at any time. By default, most access tokens expire after a short time period to ensure applications only make requests on behalf of the user when the are actively using the application. This permission makes the access token returned by our OAuth endpoint long-lived.'),
      'publish_checkins'     => '<strong>publish_checkins</strong> -- ' . t('Enables your app to perform checkins on behalf of the user.'),
      'publish_stream'       => '<strong>publish_stream</strong> -- ' . t("Enables your app to post content, comments, and likes to a user's stream and to the streams of the user's friends. With this permission, you can publish content to a user's feed at any time, without requiring offline_access. However, please note that Facebook recommends a user-initiated sharing model."),
      'rsvp_event'           => '<strong>rsvp_event</strong> -- ' . t("Enables your application to RSVP to events on the user's behalf"),
      'sms'                  => '<strong>sms</strong> -- ' . t('Enables your application to send messages to the user and respond to messages from the user via text message'),
      'publish_actions'      => '<strong>publish_actions</strong> -- ' . t('Enables your application to publish user scores and achievements.'),
    ),
  );
}

function hybridauth_provider_facebook_configuration_callback(&$config, $provider_id) {
  if (empty($config['scope'])) {
    $config['scope'] = hybridauth_provider_facebook_scope_default();
  }
}

function hybridauth_provider_facebook_scope_default() {
  return array('email', 'user_about_me', 'user_birthday', 'user_hometown', 'user_website', 'offline_access');
}
