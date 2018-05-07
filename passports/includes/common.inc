<?php
/**
 * @file
 * Contains the theme's common helper/untility functions.
 */

/**
 * Make generate an element's required attribute using valid HTML5.
 *
 * By default drupal outputs it like this: required="true", which is invalid, so
 * we generate ourt string and unset the existing value.
 * We have to unset the existing value because the drupal_attributes() function
 * mishandles booleans. See https://www.drupal.org/project/drupal/issues/2847484
 * 
 * 
 * @param array $attributes
 *   An associative array containing attributes to apply to a form element.
 *
 * @return string
 *   An empty string or ' required'.
 *
 * @see drupal_attributes()
 */
function passports_generate_required_attribute(&$attributes) {
  $required = '';
  if (isset($attributes['required'])) {
    if ($attributes['required'] == 'true' || $attributes['required'] == 'required') {
      $required = ' required';   
    }
    unset($attributes['required']);
  }
  return $required;
}

/**
 * Determine whether or not the current page is readable using readspeaker.
 *
 * @return boolean
 *   Whether or not the page should show the readspeaker button.
 */
function passports_is_readable_page() {
  // Currently just for node pages.
  if (arg(0) == 'node' && is_numeric(arg(1)) && !arg(2)) {
    return TRUE;
  }

  return FALSE;
}
