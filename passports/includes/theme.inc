<?php
/**
 * @file
 * Contains the theme's theme function overrides.
 */


/**
 * Returns HTML for a webform element.
 */
function passports_webform_element($variables) {
  $element = $variables['element'];

  if (in_array($element['#type'], array('radios', 'date', 'webform_time'))) {
    // Don't print title here as we're printing it as a fieldset legend in the
    // respective theme functions of these elements in this file.
    $element['#title_display'] = 'none';
  }

  $output = '<div ' . drupal_attributes($element['#wrapper_attributes']) . '>' . "\n";
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . webform_filter_xss($element['#field_prefix']) . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . webform_filter_xss($element['#field_suffix']) . '</span>' : '';

  // Generate description for above or below the field.
  $above = !empty($element['#webform_component']['extra']['description_above']);
  $description = array(
    FALSE => '',
    TRUE => !empty($element['#description']) ? ' <p class="description">' . $element['#description'] . "</p>\n" : '',
  );

  // If #children does not contain an element with a matching @id, do not
  // include @for in the label.
  if (isset($variables['element']['#id']) && strpos($element['#children'], ' id="' . $variables['element']['#id'] . '"') === FALSE) {
    $variables['element']['#id'] = NULL;
  }

  switch ($element['#title_display']) {
    case 'inline':
      $output .= $description[$above];
      $description[$above] = '';
      // FALL THRU.
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $variables);
      $output .= ' ' . $description[$above] . $prefix . $element['#children'] . $suffix . "\n";
      break;

    case 'after':
      $output .= ' ' . $description[$above] . $prefix . $element['#children'] . $suffix;
      $output .= ' ' . theme('form_element_label', $variables) . "\n";
      break;

    case 'none':
    case 'attribute':
      // Output no label and no required marker, only the children.
      $output .= ' ' . $description[$above] . $prefix . $element['#children'] . $suffix . "\n";
      break;
  }

  $output .= $description[!$above];
  $output .= "</div>\n";

  return $output;
}

/**
 * Returns HTML for a form element.
 */
function passports_form_element($variables) {
  $element = &$variables['element'];

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
  );

  if ($element['#type'] == 'radios') {
    // Don't print title here as we're printing it in a fieldset in
    // passports_radios().
    $element['#title_display'] = 'none';
  }

  // Add element #id for #type 'item'.
  if (isset($element['#markup']) && !empty($element['#id'])) {
    $attributes['id'] = $element['#id'];
  }
  // Add element's #type and #name as class to aid with JS/CSS selectors.
  $attributes['class'] = array('form-item');
  if (!empty($element['#type'])) {
    $attributes['class'][] = 'form-type-' . strtr($element['#type'], '_', '-');
  }
  if (!empty($element['#name'])) {
    $attributes['class'][] = 'form-item-' . strtr($element['#name'], array(' ' => '-', '_' => '-', '[' => '-', ']' => ''));
  }
  // Add a class for disabled elements to facilitate cross-browser styling.
  if (!empty($element['#attributes']['disabled'])) {
    $attributes['class'][] = 'form-disabled';
  }
  $output = '<div' . drupal_attributes($attributes) . '>' . "\n";

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
  }
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>' : '';

  switch ($element['#title_display']) {
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $variables);
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;

    case 'after':
      $output .= ' ' . $prefix . $element['#children'] . $suffix;
      $output .= ' ' . theme('form_element_label', $variables) . "\n";
      break;

    case 'none':
    case 'attribute':
      // Output no label and no required marker, only the children.
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;
  }

  if (!empty($element['#description'])) {
    $output .= '<p class="description">' . $element['#description'] . "</p>\n";
  }

  $output .= "</div>\n";

  return $output;
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
 * Returns HTML for a rangefield form element.
 */
function passports_rangefield($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'range';
  element_set_attributes($element, array('id', 'name', 'value', 'step', 'min', 'max'));
  _form_set_class($element, array('form-text', 'form-range'));

  $required = passports_generate_required_attribute($element['#attributes']);
  $output = '<input' . drupal_attributes($element['#attributes']) . $required . ' />';

  return $output;
}

/**
 * Returns HTML for a numberfield form element.
 */
function passports_numberfield($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'number';
  element_set_attributes($element, array('id', 'name', 'value', 'step', 'min', 'max', 'placeholder'));
  _form_set_class($element, array('form-text', 'form-number'));

  $required = passports_generate_required_attribute($element['#attributes']);
  $output = '<input' . drupal_attributes($element['#attributes']) . $required . ' />';

  return $output;
}

/**
 * Returns HTML for an urlfield form element.
 */
function passports_urlfield($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'url';
  element_set_attributes($element, array('id', 'name', 'value', 'size', 'maxlength', 'placeholder'));
  _form_set_class($element, array('form-text', 'form-url'));

  $extra = elements_add_autocomplete($element);
  $required = passports_generate_required_attribute($element['#attributes']);
  $output = '<input' . drupal_attributes($element['#attributes']) . $required . ' />';

  return $output . $extra;
}

/**
 * Theme a webform date element.
 */
function passports_webform_date($variables) {
  $element = $variables['element'];

  $required_marker = !empty($element['#required']) ? ' ' . theme('form_required_marker', array(
    'element' => $element,
  )) : '';

  $element['year']['#attributes']['class'][] = 'year';
  $element['month']['#attributes']['class'][] = 'month';
  $element['day']['#attributes']['class'][] = 'day';

  // Add error classes to all items within the element.
  if (form_get_error($element)) {
    $element['year']['#attributes']['class'][] = 'error';
    $element['month']['#attributes']['class'][] = 'error';
    $element['day']['#attributes']['class'][] = 'error';
  }

  // Add HTML5 required attribute, if needed.
  if ($element['#required']) {
    $element['year']['#attributes']['required'] = 'required';
    $element['month']['#attributes']['required'] = 'required';
    $element['day']['#attributes']['required'] = 'required';
  }

  $class = array('webform-container-inline');
  // Add the JavaScript calendar if available (provided by Date module package).
  if (!empty($element['#datepicker'])) {
    $class[] = 'webform-datepicker';
    $calendar_class = array('webform-calendar');
    if ($element['#start_date']) {
      $calendar_class[] = 'webform-calendar-start-' . $element['#start_date'];
    }
    if ($element['#end_date']) {
      $calendar_class[] = 'webform-calendar-end-' . $element['#end_date'];
    }
    $calendar_class[] = 'webform-calendar-day-' . variable_get('date_first_day', 0);

    $calendar = theme('webform_calendar', array('component' => $element['#webform_component'], 'calendar_classes' => $calendar_class));
  }

  $output = '';
  // The webform module specifically looks for a div when applying the calendar
  // javascript, so keep their div and add the fieldset inside.
  $output .= '<div class="' . implode(' ', $class) . '">';
  $output .= '<fieldset class="webform-component-fieldset date-fieldset">';
  if (!empty($element['#title'])) {
    if ($element['#title_display'] == 'element-invisible') {
      $output .= '<legend class="element-invisible">';
    } else {
      $output .= '<legend>'; 
    }
    $output .= $element['#title'] . $required_marker;
    $output .= '</legend>';
  }
  $output .= drupal_render_children($element);
  $output .= isset($calendar) ? $calendar : '';
  $output .= '</fieldset>';
  $output .= '</div>';

  return $output;
}

/**
 * Theme a webform time element.
 */
function passports_webform_time($variables) {
  $element = $variables['element'];
  $element['hour']['#attributes']['class'][] = 'hour';
  $element['minute']['#attributes']['class'][] = 'minute';

  // Add error classes to all items within the element.
  if (form_get_error($element)) {
    $element['hour']['#attributes']['class'][] = 'error';
    $element['minute']['#attributes']['class'][] = 'error';
  }

  // Add HTML5 required attribute, if needed.
  if ($element['#required']) {
    $element['hour']['#attributes']['required'] = 'required';
    $element['minute']['#attributes']['required'] = 'required';
    if (!empty($element['ampm'])) {
      $element['ampm']['am']['#attributes']['required'] = 'required';
      $element['ampm']['pm']['#attributes']['required'] = 'required';
    }
  }
  $output = '';
  $output .= '<fieldset class="webform-container-inline webform-component-fieldset time-fieldset webform-timefield">';
  if (!empty($element['#title'])) {
    if ($element['#title_display'] == 'element-invisible') {
      $output .= '<legend class="element-invisible">';
    } else {
      $output .= '<legend>';
    }
    $output .= $element['#title'] . $required_marker;
    $output .= '</legend>';
  }
  $output .= drupal_render($element['hour']) . drupal_render($element['minute']) . drupal_render($element['ampm']);
  $output .= '</fieldset>';

  return $output;
}

/**
 * Returns HTML for a telfield form element.
 */
function passports_telfield($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'tel';
  element_set_attributes($element, array('id', 'name', 'value', 'size', 'maxlength', 'placeholder'));
  _form_set_class($element, array('form-text', 'form-tel'));

  $extra = elements_add_autocomplete($element);
  $required = passports_generate_required_attribute($element['#attributes']);
  $output = '<input' . drupal_attributes($element['#attributes']) . $required . ' />';

  return $output . $extra;
}

/**
 * Returns HTML for an emailfield form element.
 */
function passports_emailfield($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'email';
  element_set_attributes($element, array('id', 'name', 'value', 'size', 'maxlength', 'placeholder'));
  _form_set_class($element, array('form-text', 'form-email'));

  $extra = elements_add_autocomplete($element);
  $required = passports_generate_required_attribute($element['#attributes']);
  $output = '<input' . drupal_attributes($element['#attributes']) . $required . ' />';

  return $output . $extra;
}

/**
 * Returns HTML for a searchfield form element.
 */
function passports_searchfield($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'search';
  element_set_attributes($element, array('id', 'name', 'value', 'size', 'maxlength', 'placeholder'));
  _form_set_class($element, array('form-text', 'form-search'));

  $extra = elements_add_autocomplete($element);
  $required = passports_generate_required_attribute($element['#attributes']);
  $output = '<input' . drupal_attributes($element['#attributes']) . $required . ' />';

  return $output . $extra;
}

/**
 * Returns HTML for a select form element.
 */
function passports_select($variables) {
  $element = $variables['element'];
  // Check if array already has an empty select option.
  if (!isset($element['#options'][''])) {
    // Note that it is not valid to have an empty option so we add some text.
    $blank_option = $element['#required'] ? t('Please select an option') : '&nbsp;';
    $element['#options'] = array('' => $blank_option) + $element['#options'];
  }
  element_set_attributes($element, array(
    'id',
    'name',
    'size',
  ));
  _form_set_class($element, array(
    'form-select',
  ));
  $required = passports_generate_required_attribute($element['#attributes']);
  return '<select' . drupal_attributes($element['#attributes']) . $required . '>' . form_select_options($element) . '</select>';
}

/**
 * Returns HTML for a password form element.
 */
function passports_password($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'password';
  element_set_attributes($element, array(
    'id',
    'name',
    'size',
    'maxlength',
  ));
  _form_set_class($element, array(
    'form-text',
  ));
  $required = passports_generate_required_attribute($element['#attributes']);
  return '<input' . drupal_attributes($element['#attributes']) . $required . ' />';
}

/**
 * Returns HTML for a textarea form element.
 */
function passports_textarea($variables) {
  $element = $variables['element'];
  element_set_attributes($element, array(
    'id',
    'name',
    'cols',
    'rows',
  ));
  _form_set_class($element, array(
    'form-textarea',
  ));
  $wrapper_attributes = array(
    'class' => array(
      'form-textarea-wrapper',
    ),
  );

  // Add resizable behavior.
  if (!empty($element['#resizable'])) {
    drupal_add_library('system', 'drupal.textarea');
    $wrapper_attributes['class'][] = 'resizable';
  }
  $required = passports_generate_required_attribute($element['#attributes']);
  $output = '<div' . drupal_attributes($wrapper_attributes) . '>';
  $output .= '<textarea' . drupal_attributes($element['#attributes']) . $required . '>' . check_plain($element['#value']) . '</textarea>';
  $output .= '</div>';
  return $output;
}

/**
 * Returns HTML for a checkbox form element.
 */
function passports_checkbox($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'checkbox';
  element_set_attributes($element, array(
    'id',
    'name',
    '#return_value' => 'value',
  ));

  // Unchecked checkbox has #value of integer 0.
  if (!empty($element['#checked'])) {
    $element['#attributes']['checked'] = 'checked';
  }
  _form_set_class($element, array(
    'form-checkbox',
  ));
  $required = passports_generate_required_attribute($element['#attributes']);
  return '<input' . drupal_attributes($element['#attributes']) . $required . ' />';
}

/**
 * Returns HTML for a radio form element.
 */
function passports_radio($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'radio';
  element_set_attributes($element, array(
    'id',
    'name',
    '#return_value' => 'value',
  ));
  if (isset($element['#return_value']) && $element['#value'] !== FALSE && $element['#value'] == $element['#return_value']) {
    $element['#attributes']['checked'] = 'checked';
  }
  _form_set_class($element, array(
    'form-radio',
  ));

  $required = passports_generate_required_attribute($element['#attributes']);
  return '<input' . drupal_attributes($element['#attributes']) . $required . ' />';
}

/**
 * Returns HTML for a set of radio button form elements.
 */
function passports_radios($variables) {
  $element = $variables['element'];
  $attributes = array();

  $required_marker = !empty($element['#required']) ? ' ' . theme('form_required_marker', array(
    'element' => $element,
  )) : '';
  if (isset($element['#id'])) {
    $attributes['id'] = $element['#id'];
  }
  $attributes['class'] = 'form-radios';
  if (!empty($element['#attributes']['class'])) {
    $attributes['class'] .= ' ' . implode(' ', $element['#attributes']['class']);
  }
  if (isset($element['#attributes']['title'])) {
    $attributes['title'] = $element['#attributes']['title'];
  }
  $attributes['class'] .= ' radios-fieldset';
  $output = '<fieldset ' . drupal_attributes($attributes) . '>';
  if (!empty($element['#title'])) {
    if ($element['#title_display'] == 'element-invisible') {
      $output .= '<legend class="element-invisible">';
    } else {
      $output .= '<legend>'; 
    }
    $output .= $element['#title'] . $required_marker;
    $output .= '</legend>';
  }
  $output .= !empty($element['#children']) ? $element['#children'] : '';
  $output .= '</fieldset>';
  return $output;
}

/**
 * Returns HTML for a file upload form element.
 */
function passports_file($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'file';
  element_set_attributes($element, array('id', 'name', 'size'));
  _form_set_class($element, array('form-file'));

  $required = passports_generate_required_attribute($element['#attributes']);
  return '<input' . drupal_attributes($element['#attributes']) . $required . ' />';
}

/**
 * Returns HTML for a textfield form element.
 */
function passports_textfield($variables) {
  $element = $variables['element'];
  if (empty($element['#attributes']['type'])) {
    $element['#attributes']['type'] = 'text';
  }
  if (isset($element['#webform_component']) && $element['#webform_component']['form_key'] == 'phone_number') {
    $element['#attributes']['type'] = 'tel';
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
  $required = passports_generate_required_attribute($element['#attributes']);
  $output = '<input' . drupal_attributes($element['#attributes']) . $required . ' />';
  return $output . $extra;
}

/**
 * Returns HTML for a query pager.
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
      // Add an extra new line after the group name.
      $output .= "\n";
    }
    elseif (!in_array(drupal_substr($element['#title'], -1), array('?', ':', '!', '%', ';', '@'))) {
      $output .= $element['#title'] . ':';
    }
    else {
      $output .= $element['#title'];
    }
  }

  // Add the value to the output. Add a newline before the response if needed.
  $output .= (strpos($value, "\n") === FALSE ? ' ' : "\n") . $value;

  // Add an extra line break at the end of a fieldset.
  // Indenting of fields in fieldsets has been removed because that makes it
  // frustrating to copy & paste into something else.
  if ($is_group) {
    $output .= "\n";
  }

  if ($output) {
    $output .= "\n";
  }

  return $output;
}

/**
 * Returns HTML for a link to a file.
 */
function passports_file_link($variables) {
  $file = $variables['file'];
  $icon_directory = $variables['icon_directory'];
  $url = file_create_url($file->uri);

  // Human-readable names, for use as text-alternatives to icons.
  $mime_name = array(
    'application/msword' => t('Microsoft Office document icon'),
    'application/vnd.ms-excel' => t('Office spreadsheet icon'),
    'application/vnd.ms-powerpoint' => t('Office presentation icon'),
    'application/pdf' => t('PDF icon'),
    'video/quicktime' => t('Movie icon'),
    'audio/mpeg' => t('Audio icon'),
    'audio/wav' => t('Audio icon'),
    'image/jpeg' => t('Image icon'),
    'image/png' => t('Image icon'),
    'image/gif' => t('Image icon'),
    'application/zip' => t('Package icon'),
    'text/html' => t('HTML icon'),
    'text/plain' => t('Plain text icon'),
    'application/octet-stream' => t('Binary Data'),
  );
  $mimetype = file_get_mimetype($file->uri);
  $icon = theme('file_icon', array(
    'file' => $file,
    'icon_directory' => $icon_directory,
    'alt' => !empty($mime_name[$mimetype]) ? $mime_name[$mimetype] : t('File'),
  ));

  // Set options as per anchor format described at
  // http://microformats.org/wiki/file-format-examples
  $options = array(
    'attributes' => array(
      'type' => $file->filemime . '; length=' . $file->filesize,
    ),
  );

  // Use the description as the link text if available.
  if (empty($file->description)) {
    $link_text = $file->filename;
  }
  else {
    $link_text = $file->description;
    $options['attributes']['title'] = check_plain($file->filename);
  }

  // Add the file size to the link, like the file_entity module does in its
  // formatters. See theme_file_entity_file_link(). 
  $output = '<span class="file">' . $icon . ' ' . l($link_text, $url, $options);
  $output .= ' ' . '<span class="file-size">(' . format_size($file->filesize) . ')</span>';
  $output .= '</span>';
  return $output;
}
