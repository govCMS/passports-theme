/**
 * @file
 * General JavaScript functionality for the passports theme.
 */

// Load flexbox polyfill for old versions of IE.
if (!Modernizr.flexbox || !Modernizr.flexwrap) {
  flexibility(document.documentElement);
}

(function ($, Drupal, window, document, undefined) {

/**
 * Mobile menu functionality.
 */
Drupal.behaviors.oaktonMobileMenu = {
  attach: function(context, settings) {
    $('.region-mobile-nav .menu-block-wrapper', context).once('mobile-menu', function() {
      // Link to toggle child menus expended and collapsed.
      var $expander = $('<button class="menu-expander expand" aria-label="' + Drupal.t('Expand menu') + '" title="' + Drupal.t('Expand menu') + '"></button>');
      // Link to toggle the menu open and closed.
      var $opener = $('<button class="menu-opener open" aria-label="' + Drupal.t('Open main menu') + '" title="' + Drupal.t('Open main menu') + '">' +
                        '<span class="lines">' +
                          '<span class="line line-1"></span>' +
                          '<span class="line line-2"></span>' +
                          '<span class="line line-3"></span>' +
                        '</span>' +
                      '</button>');

      // Initial setup of the menu controls.
      // The menu will initially render completely expanded, which will mean
      // no-js users will still be able to use it.
      $(this)
        .prepend($opener)
        .find('li.is-expanded')
          .toggleClass('is-expanded expanded is-collapsed collapsed')
          .children('a')
            .after($expander);

      // Click handler for opening/closing the menu.
      $('button.menu-opener', this).click(function(e) {
        var $this = $(this);

        $this
          .toggleClass('open close')
          .siblings('ul.menu')
            .slideToggle();

        if ($this.hasClass('open')) {
          $this.attr('aria-label', Drupal.t('Open main menu'));
          $this.attr('title', Drupal.t('Open main menu'));
        }
        else {
          $this.attr('aria-label', Drupal.t('Close main menu'));
          $this.attr('title', Drupal.t('Close main menu'));
        }

        e.preventDefault();
      });

      // Click handler for expanding/collapsing child menus.
      $('button.menu-expander', this).click(function(e) {
        var $this = $(this);

        $this
          .toggleClass('expand collapse')
          .closest('li')
            .toggleClass('is-expanded expanded is-collapsed collapsed')
            .children('ul')
              .slideToggle();

        if ($this.hasClass('expand')) {
          $this.attr('aria-label', Drupal.t('Expand menu'));
          $this.attr('title', Drupal.t('Expand menu'));
        }
        else {
          $this.attr('aria-label', Drupal.t('Collapse menu'));
          $this.attr('title', Drupal.t('Collapse menu'));
        }

        e.preventDefault();
      });
    });
  }
};

/**
 * Setting equal heights on sets of elements.
 */
Drupal.behaviors.oaktonEqualHeights = {
  attach: function(context, settings) {
    $('.gov-front-main-inner .entity-bean a').matchHeight({byRow: false});
    $('.gov-front-lower-inner .gov-front-left .entity-bean').not('.bean-image').matchHeight({byRow: false});

    if (Modernizr.matchmedia) {
      if (window.matchMedia("(min-width: 480px) and (max-width: 759px)").matches) {
        $('.gov-front-lower-inner .gov-front-right .entity-bean').not('.bean-image, .bean-video').matchHeight({byRow: false});
      }
    }
  }
};

/**
 * Tweaks to enhance acessibility.
 *
 * Generally we will try to do this at the template level, JavaScript should
 * only be used in cases wheree it is very difficult to achieve via templates.
 */
Drupal.behaviors.oaktonAccessibility = {
  attach: function(context, settings) {
    // This doesn't need to use jQuery once because we are changing the markup
    // we're searching for here, so we'll never double handle an element.
    $('article.node div.highlight').replaceWith(function() {
      return '<aside class="highlight">' + this.innerHTML + '</span>';
    });
  }
};

})(jQuery, Drupal, this, this.document);
