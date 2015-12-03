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
    '#prefix' => t('Version 2.4 Facebook Login API. Reference and source: @link', array('@link' => 'http://developers.facebook.com/docs/facebook-login/permissions/v2.4')),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_scope', hybridauth_provider_facebook_scope_default()),
    '#options' => array(
      // See http://developers.facebook.com/docs/facebook-login/permissions/v2.4#reference

      // Default permissions, provided without approval by Facebook.
      'public_profile'               => '<strong>public_profile</strong> -- ' . t("Provides access to a person's basic information, including first name, last name, profile picture, gender and age range.") . ' <em>' . t("This permission is always enabled and provided without approval by Facebook.") . '</em>',
      // Permissions provided without approval by Facebook.
      'email'                        => '<strong>email</strong> -- ' . t("Provides access to the person's primary email address.") . ' <em>' . t("This permission is provided without approval by Facebook.") . '</em>',
      'user_friends'                 => '<strong>user_friends</strong> -- ' . t("Provides access to a person's list of friends that also use your app.") . ' <em>' . t("This permission is provided without approval by Facebook.") . '</em>',
      // Extended permissions, approval by Facebook required.
      'manage_notifications'         => '<strong>manage_notifications</strong> -- ' . t("Enables your app to read a person's notifications and mark them as read."),
      'manage_pages'                 => '<strong>manage_pages</strong> -- ' . t("Enables your app to retrieve Page Access Tokens for the Pages and Apps that the person administrates."),
      'publish_actions'              => '<strong>publish_actions</strong> -- ' . t("Provides access to publish Posts, Open Graph actions, achievements, scores and other activity on behalf of a person using your app."),
      'publish_pages'                => '<strong>publish_pages</strong> -- ' . t("When you also have the manage_pages permission, gives your app the ability to post, comment and like as any of the Pages managed by a person using your app."),
      'read_custom_friendlists'      => '<strong>read_custom_friendlists</strong> -- ' . t("Provides access to the names of custom lists a person has created to organize their friends. This is useful for rendering an audience selector when someone is publishing stories to Facebook from your app."),
      'read_insights'                => '<strong>read_insights</strong> -- ' . t("Provides read-only access to the Insights data for Pages, Apps and web domains the person owns."),
      'read_mailbox'                 => '<strong>read_mailbox</strong> -- ' . t("Provides the ability to read the messages in a person's Facebook Inbox through the inbox edge and the thread node."),
      'read_page_mailboxes'          => '<strong>read_page_mailboxes</strong> -- ' . t("Provides the ability to read from the Page Inboxes of the Pages managed by a person. This permission is often used alongside the manage_pages permission."),
      'read_stream'                  => '<strong>read_stream</strong> -- ' . t("Provides access to read the posts in a person's News Feed, or the posts on their Profile."),
      'rsvp_event'                   => '<strong>rsvp_event</strong> -- ' . t("Provides the ability to set a person's attendee status on Facebook Events (e.g. attending, maybe, or declined)."),
      'user_about_me'                => '<strong>user_about_me</strong> -- ' . t("Provides access to a person's personal description (the 'About Me' section on their Profile) through the bio property on the User object."),
      'user_actions.books'           => '<strong>user_actions.books</strong> -- ' . t("Provides access to all common books actions published by any app the person has used. This includes books they've read, want to read, rated or quoted."),
      'user_actions.fitness'         => '<strong>user_actions.fitness</strong> -- ' . t("Provides access to all common Open Graph fitness actions published by any app the person has used. This includes runs, walks and bikes actions."),
      'user_actions.music'           => '<strong>user_actions.music</strong> -- ' . t("Provides access to all common Open Graph music actions published by any app the person has used. This includes songs they've listened to, and playlists they've created."),
      'user_actions.news'            => '<strong>user_actions.news</strong> -- ' . t("Provides access to all common Open Graph news actions published by any app the person has used which publishes these actions. This includes news articles they've read or news articles they've published."),
      'user_actions.video'           => '<strong>user_actions.video</strong> -- ' . t("Provides access to all common Open Graph video actions published by any app the person has used which publishes these actions. This includes videos they've watched, videos they've rated and videos they want to watch."),
      'user_actions:{app_namespace}' => '<strong>user_actions:{app_namespace}</strong> -- ' . t("Provides access to all of the person's custom Open Graph actions in the given app."),
      'user_birthday'                => '<strong>user_birthday</strong> -- ' . t("Access the date and month of a person's birthday. This may or may not include the person's year of birth, dependent upon their privacy settings and the access token being used to query this field."),
      'user_education_history'       => '<strong>user_education_history</strong> -- ' . t("Provides access to a person's education history through the education field on the User object."),
      'user_events'                  => '<strong>user_events</strong> -- ' . t("Provides read-only access to the Events a person is hosting or has RSVP'd to."),
      'user_games_activity'          => '<strong>user_games_activity</strong> -- ' . t("Provides access to read a person's game activity (scores, achievements) in any game the person has played."),
      'user_groups'                  => '<strong>user_groups</strong> -- ' . t("Enables your app to read the Groups a person is a member of through the groups edge on the User object."),
      'user_hometown'                => '<strong>user_hometown</strong> -- ' . t("Provides access to a person's hometown location through the hometown field on the User object. This is set by the user on the Profile."),
      'user_likes'                   => '<strong>user_likes</strong> -- ' . t("Provides access to the list of all Facebook Pages and Open Graph objects that a person has liked. This list is available through the likes edge on the User object."),
      'user_location'                => '<strong>user_location</strong> -- ' . t("Provides access to a person's current city through the location field on the User object. The current city is set by a person on their Profile."),
      'user_managed_groups'          => '<strong>user_managed_groups</strong> -- ' . t("Enables your app to read the Groups a person is an admin of through the groups edge on the User object."),
      'user_photos'                  => '<strong>user_photos</strong> -- ' . t("Provides access to the photos a person has uploaded or been tagged in. This is available through the photos edge on the User object."),
      'user_posts'                   => '<strong>user_posts</strong> -- ' . t("Provides access to the posts on a person's Timeline. Includes their own posts, posts they are tagged in, and posts other people make on their Timeline."),
      'user_relationship_details'    => '<strong>user_relationship_details</strong> -- ' . t("Provides access to a person's relationship interests as the interested_in field on the User object."),
      'user_relationships'           => '<strong>user_relationships</strong> -- ' . t("Provides access to a person's relationship status, significant other and family members as fields on the User object."),
      'user_religion_politics'       => '<strong>user_religion_politics</strong> -- ' . t("Provides access to a person's religious and political affiliations."),
      'user_status'                  => '<strong>user_status</strong> -- ' . t("Provides access to a person's statuses. These are posts on Facebook which don't include links, videos or photos."),
      'user_tagged_places'           => '<strong>user_tagged_places</strong> -- ' . t("Provides access to the Places a person has been tagged at in photos, videos, statuses and links."),
      'user_videos'                  => '<strong>user_videos</strong> -- ' . t("Provides access to the videos a person has uploaded or been tagged in."),
      'user_website'                 => '<strong>user_website</strong> -- ' . t("Provides access to the person's personal website URL via the website field on the User object."),
      'user_work_history'            => '<strong>user_work_history</strong> -- ' . t("Provides access to a person's work history and list of employers via the work field on the User object."),
    ),
  );
}

function hybridauth_provider_facebook_configuration_callback(&$config, $provider_id) {
  if (empty($config['scope'])) {
    $config['scope'] = hybridauth_provider_facebook_scope_default();
  }
}

function hybridauth_provider_facebook_scope_default() {
  return array(
    'public_profile',
    'email',
    'user_friends',
    'user_about_me',
    'user_birthday',
    'user_hometown',
    'user_website',
  );
}
