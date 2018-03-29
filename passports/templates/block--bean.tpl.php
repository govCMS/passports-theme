<?php
/**
 * @file
 * Returns the HTML for a block.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728246
 */
?>

<div id="<?php print $block_html_id; ?>" class="<?php print $classes; ?>"<?php print $attributes; ?>>

  <?php print render($title_prefix); ?>
  <?php // Don't print the title as we print it in the bean.tpl.php template. ?>
  <?php print render($title_suffix); ?>

  <div class="block__content">
    <?php print $content; ?>
  </div>

</div>
