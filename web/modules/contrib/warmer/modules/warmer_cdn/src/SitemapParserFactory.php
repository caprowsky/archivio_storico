<?php

namespace Drupal\warmer_cdn;

use vipnytt\SitemapParser;

/**
 * The Sitemap Parser factory.
 */
class SitemapParserFactory {

  /**
   * {@inheritdoc}
   */
  public function get(string $user_agent = SitemapParser::DEFAULT_USER_AGENT, array $config = []) : SitemapParser {
    return new SitemapParser($user_agent, $config);
  }

}
