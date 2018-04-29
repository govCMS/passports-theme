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
 * Override or insert variables into the node templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("node" in this case.)
 */
function passports_process_node(&$variables, $hook) {
  $node = $variables['node'];
  // For a node, use an article wrapper, except on full node pages
  // where the article tag is in the page template.
  // This will also apply to display suite templates.
  if ($variables['view_mode'] === 'full') {
    $variables['layout_wrapper'] = 'div';
  } else {
    $variables['layout_wrapper'] = 'article';
  }
}

/**
 * Implements passports_preprocess_menu_link().
 */
function passports_preprocess_menu_link(&$variables, $hook) {
  $element = &$variables['element'];

  // Identify the active menu item to screen readers.
  if ($element['#original_link']['link_path'] == $_GET['q'] || ($element['#original_link']['link_path'] == '<front>' && drupal_is_front_page())) {
    $element['#localized_options']['attributes']['aria-current'][] = 'page';
  }
}

/**
 * Override or insert variables into the superfish menu item link temmplates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("superfish_menu_item_link" in this
 *   case.)
 */
function passports_preprocess_superfish_menu_item_link(&$variables, $hook) {
  // Identify the active menu item to screen readers.
  if ($variables['menu_item']['link']['link_path'] == $_GET['q'] || ($variables['menu_item']['link']['link_path'] == '<front>' && drupal_is_front_page())) {
    $variables['link_options']['attributes']['aria-current'][] = 'page';
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

  if ($block->module == 'menu_block') {
    // Since we are making these use nav elements in the template, remove the
    // redundant navigation role.
    if (isset($variables['attributes_array']['role'])) {
      unset($variables['attributes_array']['role']);
    }
  }

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
    // We want variable wrapper elements for accessibility reasons.
    // The aside view mode is used for displaying blocks in the aside entity
    // reference field.
    if ($variables['view_mode'] == 'aside') {
      $variables['wrapper_element'] = 'aside';
      $variables['title_element'] = 'h2';
    }
    else {
      $variables['wrapper_element'] = 'article';
      $variables['title_element'] = 'h3';
    }
    // If it's a single image without a title then it is not a proper article or
    // aside so leave it as a div.
    if ($bean->type == 'image' && !$bean->title) {
      $variables['wrapper_element'] = 'div';
    } 

    // Add a title class.
    $variables['title_attributes_array']['class'][] = 'bean-title';
    $variables['title_attributes_array']['class'][] = 'block-title';

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
 * Returns HTML for a textfield form element.
 *
 * @param $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #title, #value, #description, #size, #maxlength,
 *     #required, #attributes, #autocomplete_path.
 *
 * @ingroup themeable
 */
function passports_textfield($variables) {
  $element = $variables['element'];
  if (empty($element['#attributes']['type'])) {
    $element['#attributes']['type'] = 'text';
  }
  element_set_attributes($element, array('id', 'name', 'value', 'size', 'maxlength'));
  _form_set_class($element, array('form-text'));

  $extra = '';
  if ($element['#autocomplete_path'] && !empty($element['#autocomplete_input'])) {
    drupal_add_library('system', 'drupal.autocomplete');
    $element['#attributes']['class'][] = 'form-autocomplete';

    $attributes = array();
    $attributes['type'] = 'hidden';
    $attributes['id'] = $element['#autocomplete_input']['#id'];
    $attributes['value'] = $element['#autocomplete_input']['#url_value'];
    $attributes['disabled'] = 'disabled';
    $attributes['class'][] = 'autocomplete';
    $extra = '<input' . drupal_attributes($attributes) . ' />';
  }

  $output = '<input' . drupal_attributes($element['#attributes']) . ' />';

  return $output . $extra;
}

/**
 * Returns HTML for a query pager.
 *
 * Menu callbacks that display paged query results should call theme('pager') to
 * retrieve a pager control so that users can view other results. Format a list
 * of nearby pages with additional query results.
 *
 * @param $variables
 *   An associative array containing:
 *   - tags: An array of labels for the controls in the pager.
 *   - element: An optional integer to distinguish between multiple pagers on
 *     one page.
 *   - parameters: An associative array of query string parameters to append to
 *     the pager links.
 *   - quantity: The number of pages in the list.
 *
 * @ingroup themeable
 */
function passports_pager($variables) {
  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $quantity = $variables['quantity'];
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // first is the first page listed by this pager piece (re quantity)
  $pager_first = $pager_current - $pager_middle + 1;
  // last is the last page listed by this pager piece (re quantity)
  $pager_last = $pager_current + $quantity - $pager_middle;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.

  // Prepare for generation loop.
  $i = $pager_first;
  if ($pager_last > $pager_max) {
    // Adjust "center" if at end of query.
    $i = $i + ($pager_max - $pager_last);
    $pager_last = $pager_max;
  }
  if ($i <= 0) {
    // Adjust "center" if at start of query.
    $pager_last = $pager_last + (1 - $i);
    $i = 1;
  }
  // End of generation loop preparation.

  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('« first')), 'element' => $element, 'parameters' => $parameters));
  $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : t('last »')), 'element' => $element, 'parameters' => $parameters));

  if ($pager_total[$element] > 1) {
    if ($li_first) {
      $items[] = array(
        'class' => array('pager-first'),
        'data' => $li_first,
      );
    }
    if ($li_previous) {
      $items[] = array(
        'class' => array('pager-previous'),
        'data' => $li_previous,
      );
    }

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      if ($i > 1) {
        $items[] = array(
          'class' => array('pager-ellipsis'),
          'data' => '…',
        );
      }
      // Now generate the actual pager piece.
      for (; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $items[] = array(
            'class' => array('pager-item'),
            'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($pager_current - $i), 'parameters' => $parameters)),
          );
        }
        if ($i == $pager_current) {
          $items[] = array(
            'class' => array('pager-current'),
            'aria-current' => 'page',
            'data' => $i,
          );
        }
        if ($i > $pager_current) {
          $items[] = array(
            'class' => array('pager-item'),
            'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $pager_current), 'parameters' => $parameters)),
          );
        }
      }
      if ($i < $pager_max) {
        $items[] = array(
          'class' => array('pager-ellipsis'),
          'data' => '…',
        );
      }
    }
    // End generation.
    if ($li_next) {
      $items[] = array(
        'class' => array('pager-next'),
        'data' => $li_next,
      );
    }
    if ($li_last) {
      $items[] = array(
        'class' => array('pager-last'),
        'data' => $li_last,
      );
    }
    return '<nav class="pager-nav"><h2 class="element-invisible">' . t('Pages') . '</h2>' . theme('item_list', array(
      'items' => $items,
      'attributes' => array('class' => array('pager')),
    )) . '</nav>';
  }
}


/**
 * Output a form element in plain text format.
 */
function passports_webform_element_text($variables) {
  $element = $variables['element'];
  $value = $variables['element']['#children'];

  $output = '';
  $is_group = webform_component_feature($element['#webform_component']['type'], 'group');

  // Output the element title.
  if (isset($element['#title'])) {
    if ($is_group) {
      $output .= '==' . $element['#title'] . '==';
    }
    elseif (!in_array(drupal_substr($element['#title'], -1), array('?', ':', '!', '%', ';', '@'))) {
      $output .= $element['#title'] . ':';
    }
    else {
      $output .= $element['#title'];
    }
  }

  // Wrap long values at 65 characters, allowing for a few fieldset indents.
  // It's common courtesy to wrap at 75 characters in e-mails.
  if ($is_group && drupal_strlen($value) > 65) {
    $value = wordwrap($value, 65, "\n");
    $lines = explode("\n", $value);
    foreach ($lines as $key => $line) {
      $lines[$key] = '  ' . $line;
    }
    $value = implode("\n", $lines);
  }

  // Add the value to the output. Add a newline before the response if needed.
  $output .= (strpos($value, "\n") === FALSE ? ' ' : "\n") . $value;

  // Indent fieldsets.
  if ($is_group) {
    $lines = explode("\n", $output);
    foreach ($lines as $number => $line) {
      if (strlen($line)) {
        $lines[$number] = '  ' . $line;
      }
    }
    $output = implode("\n", $lines);
    $output .= "\n";
  }

  if ($output) {
    $output .= "\n";
    // Add an additional line break to enhance readability.
    $output .= "\n";
  }

  return $output;
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
 * Determine whether or not the current page is readable using readspeaker.
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
        'extTarget'     => 0,
        // The class to add to external links. Using 'ext' will place an icon
        // next to external links. Set to FALSE to not add a class.
        'extClass'      => 'ext',
        // Invisible text to display after the link text of external links.
        // This is visible to screen readers, for accessibility purposses.
        'extLabel'      => t('(link is external)'),
        // If TRUE, images wrapped in an anchor tag will be treated as
        // external links.
        'extImgClass'   => FALSE,
        // Add the icon before or after links. Options: append|prepend.
        'extIconPlacement' => 'append',
        // Exclude all subdomains. Otherwise, just www or no subdomain.
        'extSubdomains' => TRUE,
        // A regular expression for links that you wish to exclude from being
        // considered external. To match the "href" property of links.
        'extExclude'    => '',
        // A regular expression for internal links that you wish to be
        // considered external. To match the "href" property of links.
        'extInclude'    => '',
        // Exclude links inside elements matching this comma-separated list
        // of CSS selectors. E.g. #block-block-2 .content, ul.menu.
        'extCssExclude' => '#header, .breadcrumbs-wrapper',
        // Only include links inside elements matching this comma-separated
        // list of CSS selectors. E.g. #block-block-2 .content, ul.menu.
        'extCssExplicit' =>'',
        // Display a pop-up warning when any external link is clicked.
        'extAlert'      => '',
        // The text in the pop-up, if it is enabled.
        'extAlertText'  => t('This link will take you to an external web site. We are not responsible for their content.'),
        // The class to add to mailto links. Using 'mailto' will place an icon
        // next to mailto links. Set to FALSE to not add a class.
        'mailtoClass'   => 'mailto',
        // Invisible text to display after the link text of mailto links.
        // This is visible to screen readers, for accessibility purposses.
        'mailtoLabel'   => t('(link sends e-mail)'),
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
