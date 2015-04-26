<?php
/**
 * @file
 * druio_events.features.user_permission.inc
 */

/**
 * Implements hook_user_default_permissions().
 */
function druio_events_user_default_permissions() {
  $permissions = array();

  // Exported permission: 'flag will_go2_event'.
  $permissions['flag will_go2_event'] = array(
    'name' => 'flag will_go2_event',
    'roles' => array(
      'administrator' => 'administrator',
      'authenticated user' => 'authenticated user',
      'moderator' => 'moderator',
    ),
    'module' => 'flag',
  );

  // Exported permission: 'unflag will_go2_event'.
  $permissions['unflag will_go2_event'] = array(
    'name' => 'unflag will_go2_event',
    'roles' => array(
      'administrator' => 'administrator',
      'authenticated user' => 'authenticated user',
      'moderator' => 'moderator',
    ),
    'module' => 'flag',
  );

  return $permissions;
}
