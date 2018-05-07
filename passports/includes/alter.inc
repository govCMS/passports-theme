<?php
/**
 * @file
 * Contains the theme's _alter() hook implementations.
 */

/**
 * Implements hook_entity_view_alter().
 */
function passports_entity_view_alter(&$build, $type) {
  if ($type == 'bean') {
    // The aside view mode is used for the aside entity reference block.
    // In that case, because the bean is rendered directly we don't get the
    // usual block based contextual links, so we add them here manually.
    if ($build['#view_mode'] == 'aside') {
      $build['#contextual_links']['block'] = array(
        'admin/structure/block/manage',
        array('bean', $build['#entity']->Identifier()),
      );
      $build['#contextual_links']['bean'] = array(
        'block', array($build['#entity']->Identifier(), 'edit')
      );
    }
  }
}

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Alter the search API site search block form.
 */
function passports_form_search_api_page_search_form_site_search_alter(&$form, &$form_state, $form_id) {
  $form['keys_4']['#attributes']['placeholder'] = t('Search');
  $form['keys_4']['#attributes']['role'] = 'searchbox';
  $form['keys_4']['#attributes']['type'] = 'search';
  $form['#attributes']['role'] = 'search';
}

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Alter the search API site search page form.
 */
function passports_form_search_api_page_search_form_alter(&$form, &$form_state, $form_id) {
  $form['form']['keys_4']['#attributes']['placeholder'] = t('Search');
  $form['form']['keys_4']['#attributes']['role'] = 'searchbox';
  $form['form']['keys_4']['#attributes']['type'] = 'search';
  $form['#attributes']['role'] = 'search';
}

/**
 * Implements hook_wysiwyg_editor_settings_alter().
 */
function passports_wysiwyg_editor_settings_alter(&$settings, $context) {
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
 * Implements hook_page_alter().
 */
function passports_page_alter(&$page) {
  $path = drupal_get_path('theme', 'passports');

  if (passports_is_readable_page()) {
    $page['page_bottom']['#attached']['js'][] = array(
      'type' => 'file',
      'scope' => 'footer',
      'group' => JS_THEME,
      // The main ReadSpeaker.js file loads a number of other JS and CSS files
      // from within the readspeaker js directory however it does so with
      // paths relative to itself. That means that if this gets aggregated and
      // the JS then loads from sites/default/files then it all falls apart.
      // So disable preprocess for this one, which means it will never get
      // aggregated.
      'preprocess' => FALSE,
      'data' => url($path . '/js/readspeaker/ReadSpeaker.js', array(
        'query' => array(
          'pids' => 'embhl',
        ),
        'absolute' => TRUE,
      )),
    );
  }

  // Replicate the external link module's functionality.
  // Currenlty we're using the JS from version 7.x-1.20.
  // See https://www.drupal.org/project/extlink
  $page['page_bottom']['#attached']['js'][] = array(
    'type' => 'file',
    'scope' => 'footer',
    'group' => JS_THEME,
    'data' => $path . '/js/extlink.js',
  );

  // Settings used. Since the module isn't installed and we don't have its
  // variables, just hard code the options.
  $page['page_bottom']['#attached']['js'][] = array(
    'type' => 'setting',
    'scope' => 'footer',
    'group' => JS_THEME,
    'data' => array(
      'extlink' => array(
        // Open external links in a new window.
        'extTarget' => 0,
        // The class to add to external links. Using 'ext' will place an icon
        // next to external links. Set to FALSE to not add a class.
        'extClass' => 'ext',
        // Invisible text to display after the link text of external links.
        // This is visible to screen readers, for accessibility purposses.
        'extLabel' => t('(link is external)'),
        // If TRUE, images wrapped in an anchor tag will be treated as
        // external links.
        'extImgClass' => FALSE,
        // Add the icon before or after links. Options: append|prepend.
        // Note: This setting will not do anything until we can update to the JS
        // from version 7.19+ of the extlink module, which is not yet stable.
        // @see https://www.drupal.org/project/extlink/issues/2914838
        'extIconPlacement' => 'append',
        // Exclude all subdomains. Otherwise, just www or no subdomain.
        'extSubdomains' => TRUE,
        // A regular expression for links that you wish to exclude from being
        // considered external. To match the "href" property of links.
        'extExclude' => '',
        // A regular expression for internal links that you wish to be
        // considered external. To match the "href" property of links.
        'extInclude' => '',
        // Exclude links inside elements matching this comma-separated list
        // of CSS selectors. E.g. #block-block-2 .content, ul.menu.
        'extCssExclude' => '.region-page-top, #header, .breadcrumbs-wrapper',
        // Only include links inside elements matching this comma-separated
        // list of CSS selectors. E.g. #block-block-2 .content, ul.menu.
        'extCssExplicit' => '',
        // Display a pop-up warning when any external link is clicked.
        'extAlert' => '',
        // The text in the pop-up, if it is enabled.
        'extAlertText' => t('This link will take you to an external web site. We are not responsible for their content.'),
        // The class to add to mailto links. Using 'mailto' will place an icon
        // next to mailto links. Set to FALSE to not add a class.
        'mailtoClass' => 'mailto',
        // Invisible text to display after the link text of mailto links.
        // This is visible to screen readers, for accessibility purposses.
        'mailtoLabel' => t('(link sends e-mail)'),
      ),
    ),
  );
}

/**
 * Implements hook_js_alter().
 */
function passports_js_alter(&$javascript) {
  // The jquery placeholder plugin was for some reason causing a javascript
  // error in IE9. Since we don't really care if users on old version of IE
  // don't get placeholder text we will just remove it.
  foreach ($javascript as $path => $info) {
    if (substr($path, -21) == 'jquery.placeholder.js') {
      unset($javascript[$path]);
    }
  }
}

/**
 * Implements hook_file_view_alter().
 */
function passports_file_view_alter($build, $type) {
  // When viewing a file page.
  if (arg(0) == 'file' && is_numeric(arg(1)) && !arg(2)) {
    $file = $build['#file'];
    // For the main image that is being loaded.
    if ($file->fid == arg(1) && $build['#view_mode'] == 'full') {
      // Instead of returning the file view page, return the actual file.
      $headers = file_download_headers($file->uri);
      if (count($headers)) {
        // Set the file name, so if a user downloads the file it doesn't save
        // with the file ID from the URL as the name.
        $path_parts = pathinfo($file->uri);
        $headers['Content-Disposition'] = 'inline; filename="' . $path_parts['basename'] . '"';
        file_transfer($file->uri, $headers);
      }
      drupal_exit();
    }
  }
}
