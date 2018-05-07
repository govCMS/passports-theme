<?php
/**
 * @file
 * Contains the theme's preprocess and process functions.
 */

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
    $variables['ds_content_wrapper'] = 'div';
  } else {
    $variables['layout_wrapper'] = 'article';
    $variables['ds_content_wrapper'] = 'article';
  }

  if (!$variables['page'] && $variables['title']) {
    $variables['header_wrapper'] = 'header';
  } else {
    $variables['header_wrapper'] = 'div';
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

  // If we have no exceprt, the default output of the Search API Pages module
  // is not great. So override it.
  if (empty($variables['result']['excerpt'])) {
    // Since we don't have smart trim or similar, then just using the node
    // teaser or other view mode is not useful, since the media module or a
    // content editor might add a whole bunch of HTML tags in the start that
    // break useful truncation of content.
    // So replicate the default behaviour from search_api_pages and just strip
    // out some markup.
    // @see template_preprocess_search_api_page_result().
    $index = $variables['index'];
    $item = $variables['item'];
    $wrapper = $index->entityWrapper($item, FALSE);

    $fields = $index->options['fields'];
    $fields = array_intersect_key($fields, drupal_map_assoc($index->getFulltextFields()));
    $fields = search_api_extract_fields($wrapper, $fields);
    $text = '';
    $length = 0;
    foreach ($fields as $field_name => $field) {
      if (search_api_is_list_type($field['type']) || !isset($field['value'])) {
        continue;
      }
      $val_length = drupal_strlen($field['value']);
      if ($val_length > $length) {
        $text = $field['value'];
        $length = $val_length;

        $format = NULL;
        if (($pos = strrpos($field_name, ':')) && substr($field_name, $pos + 1) == 'value') {
          $tmp = $wrapper;
          try {
            foreach (explode(':', substr($field_name, 0, $pos)) as $part) {
              if (!isset($tmp->$part)) {
                $tmp = NULL;
              }
              $tmp = $tmp->$part;
            }
          }
          catch (EntityMetadataWrapperException $e) {
            $tmp = NULL;
          }
          if ($tmp && $tmp->type() == 'text_formatted' && isset($tmp->format)) {
            $format = $tmp->format->value();
          }
        }
      }
    }
    if ($text && function_exists('text_summary')) {
      // Strip markup out so we don't get crazy results.
      $text = filter_xss($text, array('em', 'strong'));
      $text = text_summary($text, $format);
      $variables['snippet'] = $text;
    }
  }

  // Do our own info array.
  $info = array();
  $info['date'] = '<span class="last-updated"><span class="label">' . t('Last updated: ') . '</span>' . format_date($item->changed, 'govcms_month_day_year') . '</span>';
  $info['read_more'] = '<span class="read-more">' . l(t('Read more') . '<span class="element-invisible"> about ' . check_plain($item->title) . '</span>', $variables['url']['path'], array('html' => TRUE)) . '</span>';

  // Provide separated and grouped meta information.
  $variables['info_split'] = $info;
  $variables['info'] = implode(' ', $info);
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
 * Preprocess variables for the readspeaker button template.
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
