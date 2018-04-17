<?php
/**
 * @file
 * Provides themed representation of the front layout.
 *
 * @license GPL v2 http://www.fsf.org/licensing/licenses/gpl.html
 *
 * aGov
 * @copyright Copyright(c) 2014 PreviousNext
 * @author Nick Schuch nick at previousnext dot com dot au
 *
 * govCMS (Forked on 1 April 2015 - http://cgit.drupalcode.org/govcms/commit/?id=64b602dcc7ddde0992c5c7cf5f3c4a795e5be08a)
 * The original foundation for the govCMS distribution is aGov; the Drupal distribution created by PreviousNext to provide a core set of elements, functionality and features that can be used to develop government websites
 * @copyright Copyright(c) 2015 Commonwealth of Australia as represented by Department of Finance
 * @author Department of Finance
 *
 * Available variables
 * -------------------
 * $content array of panels.
 */

?>


<div class="gov-front-layout clearfix" <?php if (!empty($css_id)) : print "id=\"$css_id\""; endif; ?>>
  
  <?php if (!empty($content['main'])) : ?>
    <section class="grid-8 gov-front-main">
      <h2 class="element-invisible">Highlights</h2>
      <div class="gov-front-main-inner">
        <?php print $content['main'];?>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!empty($content['left']) || !empty($content['right'])): ?>
    <div class="gov-front-lower">
      <div class="gov-front-lower-inner">
        <?php if (!empty($content['left'])) : ?>
          <section class="alpha grid-4 gov-front-left gov-front-col">
            <h2 class="element-invisible">Content left</h2>
            <?php print $content['left'];?>
          </section>
        <?php endif; ?>

        <?php if (!empty($content['right'])) : ?>
          <section class="omega grid-4 gov-front-right gov-front-col">
            <h2 class="element-invisible">Content right</h2>
            <?php print $content['right'];?>
          </section>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

</div>
