<?php

namespace MailPoet\AdminPages\Pages;

use MailPoet\AdminPages\PageRenderer;
use MailPoet\Config\Env;
use MailPoet\WP\Functions as WPFunctions;
use MailPoetVendor\IsoGutenberg\IsoGutenberg;

class NewsletterEditorV2 {
  /** @var PageRenderer */
  private $pageRenderer;

  /** @var WPFunctions */
  private $wp;

  public function __construct(
    PageRenderer $pageRenderer,
    WPFunctions $wp
  ) {
    $this->pageRenderer = $pageRenderer;
    $this->wp = $wp;
  }

  public function render() {
    // Load Gutenberg deps from WP
    // @see https://github.com/Automattic/isolated-block-editor/tree/trunk/examples/wordpress-php
    $isolatedEditor = new IsoGutenberg();
    $isolatedEditor->load();

    $this->wp->wpEnqueueScript(
      'mailpoet_email_editor',
      Env::$assetsUrl . '/dist/js/newsletter_editor_v2.js',
      [],
      Env::$version,
      true
    );
    $this->pageRenderer->displayPage('newsletter/editorv2.html', []);
  }
}
