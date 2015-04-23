<?php
/**
 * @file
 * Description of HybridAuth API functions and hooks.
 */

/**
 * Use this function to get HybridAuth instance or error code.
 */
function hybridauth_get_instance() {

}

/**
 * Alter HybridAuth provider configuration.
 * @param $config
 * @param $provider_id
 */
function hook_hybridauth_provider_config_alter(&$config, $provider_id) {

}

/**
 * Alter the generated username for the user being created by HybridAuth.
 * @param string $name
 * @param array $data
 *   HybridAuth identity data.
 */
function hook_hybridauth_username_alter(&$name, $data) {

}

/**
 * Alter the values returned by HybridAuth library.
 * @param array $profile
 */
function hook_hybridauth_profile_alter(&$profile) {

}

/**
 * Alter the user info for the user being created by HybridAuth.
 * @param array $userinfo
 *   Information to create a new user.
 * @param array $data
 *   HybridAuth identity data.
 */
function hook_hybridauth_userinfo_alter(&$userinfo, $data) {

}

/**
 * Should we show the registration form or not?
 * You should also implement hook_form_hybridauth_additional_info_form_alter()
 * to add your form elements, validate and submit callbacks to this form.
 * See hybridauth_bonus module for example usage.
 * @param array $data
 *   HybridAuth identity data.
 */
function hook_hybridauth_registration_form($data) {

}

/**
 * This hook allows other modules to block new registrations through HybridAuth
 * based on the data received from authentication provider.
 * Return translated message to show to the user if the registration should be
 * blocked. Return just TRUE if you don't want to show any message.
 * @param array $data
 *   HybridAuth identity data.
 */
function hook_hybridauth_registration_block($data) {
  
}

/**
 * Invoked when added new HybridAuth identity to user account.
 * @param object $account
 *   User account object.
 * @param array $data
 *   HybridAuth identity data.
 */
function hook_hybridauth_identity_added($account, $data) {

}

/**
 * Invoked when deleted HybridAuth identity from user account.
 * @param object $account
 *   User account object.
 * @param array $data
 *   HybridAuth identity data.
 */
function hook_hybridauth_identity_deleted($account, $data) {

}

/**
 * Invoked when a new user account is about to be created through HybridAuth.
 * @param object $account
 *   User account object.
 * @param array $data
 *   HybridAuth identity data.
 */
function hook_hybridauth_user_preinsert($account, $data) {

}

/**
 * Invoked when a new user account is created through HybridAuth.
 * @param object $account
 *   User account object.
 * @param array $data
 *   HybridAuth identity data.
 */
function hook_hybridauth_user_insert($account, $data) {

}

/**
 * Invoked when a user has logged in through HybridAuth.
 * @param object $account
 *   User account object.
 * @param array $data
 *   HybridAuth identity data.
 */
function hook_hybridauth_user_login($account, $data) {

}
