<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */

/**
 * The ReadSpeaker customer ID for the passports website.
 */
define('PASSPORTS_READSPEAKER_CUSTOMER_ID', 6248);

/**
 * Split functions out into include files to help with maintainability since we
 * have a lot of overrides.
 */
include_once dirname(__FILE__) . '/includes/common.inc';
include_once dirname(__FILE__) . '/includes/alter.inc';
include_once dirname(__FILE__) . '/includes/process.inc';
include_once dirname(__FILE__) . '/includes/theme.inc';

/**
 * Implements hook_theme().
 */
function passports_theme($existing, $type, $theme, $path) {
  $path = drupal_get_path('theme', 'passports') . '/templates';
  return array(
    'readspeaker_button' => array(
      'path' => $path,
      'template' => 'readspeaker-button',
      'variables' => array(
        'read_url' => '',
        'read_id' => 'content',
        'language' => 'en_au',
        'customer_id' => PASSPORTS_READSPEAKER_CUSTOMER_ID,
      ),
    ),
  );
}
