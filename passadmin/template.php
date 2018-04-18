<?php

/**
 * Implements hook_wysiwyg_editor_settings_alter().
 */
function passadmin_wysiwyg_editor_settings_alter(&$settings, $context) {
  if ($context['profile']->editor == 'ckeditor') {
    $settings['templates_files'] = array(
      url(drupal_get_path('theme', 'passports') . '/js/ckeditor.templates.js', array('absolute' => TRUE)),
    );
    $settings['templates'] = 'passports';
    $settings['templates_replaceContent'] = FALSE;

    // Settings specifically for the video text format.
    if ($context['profile']->format == 'video') {
      // Don't automatically wrap everything in the enterMode element (currently
      // the p tag).
      $settings['autoParagraph'] = FALSE;
      // Don't fill empty tags with a non-breaking space.
      $settings['fillEmptyBlocks'] = FALSE;
    }
  }
}

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Alter the ctools_block_content_type_edit_form form. This form is used when
 * adding/editing a block in the panels administration interface.
 */
function passadmin_form_ctools_block_content_type_edit_form_alter(&$form, &$form_state, $form_id) {
  // Get the block info.
  // See _ctools_block_get_module_delta().
  list($module, $delta) = explode('-', $form_state['subtype_name'], 2);

  if ($module == 'bean') {
    // Don't allow the title to be overridden for beans since we do not use the
    // panels pane title in our templates, we print the bean title in the bean
    // template instead.
    $form['override_title']['#access'] = FALSE;
    $form['override_title_text']['#access'] = FALSE;
    $form['override_title_heading']['#access'] = FALSE;
    $form['override_title_markup']['#access'] = FALSE;
  }
}

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Alter the media_wysiwyg_format_form form.
 * This is the form that the user sees when they have uploaded/selected a file
 * in the media popup from the WYSIWYG and then get to choose the display mode
 * to use to render the file, override alt text, etc.
 */
function passadmin_form_media_wysiwyg_format_form_alter(&$form, &$form_state, $form_id) {
  $file = $form_state['file'];

  // There is a bug in the media module where if you use the edit file button
  // on this form, then save the file, the media browser then loads the site's
  // home page instead of goin back to this form again.
  // This is a workaround for that issue, updating the edit file link to return
  // the user via the destination query parameter.
  // See https://www.drupal.org/project/media/issues/2955935
  // Replicate the link as per the media_wysiwyg_format_form_view_mode()
  // function in the media_wysiwyg.pages.inc file.
  $link_options = array(
    'attributes' => array(
      'class' => 'button',
      'title' => t('Use for replace fox or edit file fields.'),
    ),
    'query' => array(
      // Bring the user back when they're done editing.
      'destination' => 'media/' . $file->fid . '/format-form',
    ),
  );
  if (!empty($_GET['render'])) {
    $link_options['query']['render'] = $_GET['render'];
    $link_options['query']['destination'] .= '?render=' . $_GET['render'];
  }

  $form['preview']['#suffix'] = '</div><div class="label-wrapper"><label class="media-filename">' . check_plain($file->filename) . '</label></div></div><div class="edit-file-link">' . l(t('Edit file'), 'file/' . $file->fid . '/edit', $link_options) . '</div></div>';
}

/**
 * Implements of hook_element_info_alter().
 *
 * This mimics the better formats module.
 *
 * @see https://www.drupal.org/project/better_formats
 */
function passadmin_element_info_alter(&$type) {
  // Our process callback must run immediately after filter_process_format().
  $filter_process_format_location = array_search('filter_process_format', $type['text_format']['#process']);
  $replacement = array('filter_process_format', 'passadmin_filter_process_format');
  array_splice($type['text_format']['#process'], $filter_process_format_location, 1, $replacement);
}

/**
 * Process callback for form elements that have a text format selector attached.
 *
 * This callback runs after filter_process_format() and performs additional
 * modifications to the form element.
 *
 * @see filter_process_format()
 * @see passadmin_element_info_alter()
 */
function passadmin_filter_process_format($element) {
  // Before we make any modifications to the element, record whether or not
  // filter_process_format() has determined that (for security reasons) the
  // user is not allowed to make any changes to this field. (This will happen
  // if the user does not have permission to use the currently-assigned text
  // format.)
  $access_denied_for_security = isset($element['format']['#access']) && !$element['format']['#access'];

  // Now hide several parts of the element for cosmetic reasons (depending on
  // the permissions of the current user).
  $show_selection = user_access('administer content types');

  if (!$show_selection) {
    $element['format']['format']['#access'] = FALSE;
  }

  // If the element represents a field attached to an entity, we may need to
  // adjust the allowed text format options. However, we don't want to touch
  // this if filter_process_format() has determined that (for security reasons)
  // the user is not allowed to make any changes; in that case, Drupal core
  // will hide the format selector and force the field to be saved with its
  // current values, and we should not do anything to alter that process.
  if (isset($element['#entity_type']) && !$access_denied_for_security) {
    $instance_info = field_info_instance($element['#entity_type'], $element['#field_name'], $element['#bundle']);
    // Set the format to video for the video embed field.
    if ($element['#field_name'] == 'field_video_embed') {
      // Need to only do this on create forms.
      if (!empty($element['#entity']) && !empty($element['#entity_type'])) {
        list($eid, $vid, $bundle) = entity_extract_ids($element['#entity_type'], $element['#entity']);
        if (empty($eid)) {
          if (isset($element['format']['format']['#options']['video'])) {
            $element['format']['format']['#default_value'] = 'video';
            // Restrict the user from being able to change it.
            $element['format']['format']['#access'] = FALSE;
            $show_selection = FALSE;
          }
        }
      }
    }
  }

  // If the user is not supposed to see the text format selector, hide all
  // guidelines except those associated with the default format. We need to do
  // this at the end, since the above code may have altered the default format.
  if (!$show_selection && isset($element['format']['format']['#default_value'])) {
    foreach (element_children($element['format']['guidelines']) as $format) {
      if ($format != $element['format']['format']['#default_value']) {
        $element['format']['guidelines'][$format]['#access'] = FALSE;
      }
    }
  }

  return $element;
}

function passadmin_menu_attribute_info_alter(&$attributes) {
  // Add a lang attribute.
  $attributes['lang'] = array(
    'label' => t('Lang'),
    'enabled' => 1,
    'default' => '',
    'scope' => array(
      'attributes',
    ),
    'form' => array(
      '#type' => 'textfield',
      '#title' => t('Lang'),
      '#description' => t('Enter a language code for the text within this menu item, if it is not English. Use <a href="@url">ISO 639-1 language codes</a>.', array('@url' => 'https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes')),
      '#default_value' => '',
    ),
  );
  // Add a hreflang attribute.
  $attributes['hreflang'] = array(
    'label' => t('HREF Lang'),
    'enabled' => 1,
    'default' => '',
    'scope' => array(
      'attributes',
    ),
    'form' => array(
      '#type' => 'textfield',
      '#title' => t('HREF Lang'),
      '#description' => t('Enter a language code for the text that is on the page you are linking to, if it is not English. Use <a href="@url">ISO 639-1 language codes</a>.', array('@url' => 'https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes')),
      '#default_value' => '',
    ),
  );
}
