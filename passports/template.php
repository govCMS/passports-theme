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
 * Override or insert variables into the page templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("page" in this case.)
 */
function passports_preprocess_page(&$variables, $hook) {
  $variables['readspeaker_button'] = '';
  if (passports_is_readable_page()) {
    $variables['readspeaker_button'] = theme('readspeaker_button');
  }
}

/**
 * Override or insert variables into the field templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("field" in this case.)
 */
function passports_preprocess_field(&$variables, $hook) {
  if ($variables['element']['#field_name'] == "node_link") {
    $variables['items'][0]['#markup'] = l(t('Read more') . '<span class="element-invisible"> about ' . check_plain($variables['element']['#object']->title) . '</span>', 'node/' . $variables['element']['#object']->nid, array('html' => TRUE));
  }
}


/**
 * Override or insert variables into the block templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
function passports_preprocess_block(&$variables, $hook) {
  $block = $variables['block'];
  if ($block->module == 'bean' && isset($variables['elements']['bean'][$block->delta]['#entity'])) {
    // Get the bean object.
    $bean = $variables['elements']['bean'][$block->delta]['#entity'];

    // Add bean type classes to the block.
    $variables['classes_array'][] = drupal_html_class('block-bean-' . $bean->type);
    // Add template suggestions for bean types.
    $variables['theme_hook_suggestions'][] = 'block__bean__' . $bean->type;
  }
}

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
 * Override or insert variables into the entity templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("entity" in this case.)
 */
function passports_preprocess_entity(&$variables) {
  if ($variables['entity_type'] == 'bean') {
    $bean = $variables['bean'];

    // Add a title class.
    $variables['title_attributes_array']['class'][] = 'bean-title';

    // For some reason the bean module sets the title to the administration
    // title if there isn't one, but we don't want that so put it back how it
    // should be.
    if (!$bean->title && $variables['title']) {
      $variables['title'] = '';
    }

    // Move the contextual links for the sake of our link wrapping.
    $variables['contextual_links'] = '';
    if (isset($variables['title_suffix']['contextual_links'])) {
      $variables['contextual_links'] = $variables['title_suffix']['contextual_links'];
      unset($variables['title_suffix']['contextual_links']);
    }

    // Get the URL from the link field if there is one.
    $variables['link_to'] = '';
    if ($items = field_get_items('bean', $bean, 'field_link_to')) {
      $variables['link_to'] = theme('link_formatter_link_plain', array('element' => $items[0]));
    }
  }
}

/**
 * Process variables for panels-pane.tpl.php
 *
 * The $variables array contains the following arguments:
 * - $file
 * - $view_mode
 *
 * @see file_entity.tpl.php
 */
function passports_preprocess_panels_pane(&$variables, $hook) {
  if ($variables['pane']->type == 'block' && isset($variables['content']['bean'])) {
    if ($bean = reset($variables['content']['bean'])) {
      $bean = $bean['#entity'];

      // Add a template suggestion to allow us to override panels panes at the
      // bean type level.
      $variables['theme_hook_suggestions'][] = 'panels_pane__block__' . str_replace('-', '_', $bean->type);
      // Add bean type classes for styling.
      $variables['classes_array'][] = 'pane-bean-' . drupal_html_class($bean->type);
    }
  }
}

/**
 * Override or insert variables into the Search API Page result templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("search_api_page_result" in this
 *   case.)
 */
function passports_preprocess_search_api_page_result(&$variables, $hook) {
  $item = $variables['item'];
  
  // Do our own info array.
  $info = array();
  $info['date'] = '<span class="last-updated"><span class="label">' . t('Last updated: ') . '</span>' . format_date($item->changed, 'govcms_month_day_year') . '</span>';
  $info['read_more'] = '<span class="read-more">' . l(t('Read more') . '<span class="element-invisible"> about ' . check_plain($item->title) . '</span>', $variables['url']['path'], array('html' => TRUE)) . '</span>';

  // Provide separated and grouped meta information.
  $variables['info_split'] = $info;
  $variables['info'] = implode(' ', $info);
}

/**
 * Override or insert variables into the breadcrumb temmplates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("breadcrumb" in this case.)
 */
function passports_preprocess_breadcrumb(&$variables, $hook) {
  // On the search results page, add the search terms to the breadcrumb.
  if (arg(0) == 'search' && arg(1) && !arg(2)) {
    $end = count($variables['breadcrumb']) - 1;
    $variables['breadcrumb'][$end] .= ': ' . check_plain(arg(1));
  }
}

/**
 * Implements theme_form_required_marker().
 */
function passports_form_required_marker($variables) {
  // This is also used in the installer, pre-database setup.
  $t_function = get_t();
  $attributes = array(
    'class' => 'form-required',
    'title' => $t_function('This field is required.'),
  );
  return '<span' . drupal_attributes($attributes) . '>*</span>';
}

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Alter the search API site search block form.
 */
function passports_form_search_api_page_search_form_site_search_alter(&$form, &$form_state, $form_id) {
  $form['keys_4']['#attributes']['placeholder'] = t('Search');
}

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Alter the search API site search page form.
 */
function passports_form_search_api_page_search_form_alter(&$form, &$form_state, $form_id) {
  $form['form']['keys_4']['#attributes']['placeholder'] = t('Search');
}

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Alter the feedback form.
 */
function passports_form_webform_client_form_76_alter(&$form, &$form_state, $form_id) {
  $form['submitted']['form_number']['#prefix'] = '<div class="numbers-wrapper">';
  $form['submitted']['passport_number']['#suffix'] = '</div>';
  $form['submitted']['form_number']['#suffix'] = '<span class="form-number-divider">Or</span>';
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

/**
 * Preprocess variables for the readspeaker ubtton template.
 *
 * Note that this is prefixed with template_ because we are defining this
 * template in this theme.
 *
 * @see readspeaker-button.tpl.php
 */
function passports_preprocess_readspeaker_button(&$variables) {
  $variables['url'] = url('https://app-eu.readspeaker.com/cgi-bin/rsent', array(
    'query' => array(
      'customerid' => $variables['customer_id'],
      'lang' => $variables['language'],
      'readid' => $variables['read_id'],
      // Default to current page.
      'url' => $variables['read_url'] ? $variables['read_url'] : url(current_path(), array('absolute' => TRUE)),
    ),
  ));
}

/**
 * Determine whether or not th ecurrent page is readable using readspeaker.
 */
function passports_is_readable_page() {
  // Currently just for node pages.
  if (arg(0) == 'node' && is_numeric(arg(1)) && !arg(2)) {
    return TRUE;
  }

  return FALSE;
}

/**
 * Implements hook_page_alter().
 */
function passports_page_alter(&$page) {
  if (passports_is_readable_page()) {
    $path = drupal_get_path('theme', 'passports');

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
