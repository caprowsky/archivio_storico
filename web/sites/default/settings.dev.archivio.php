<?php

$settings['trusted_host_patterns'] = [
  '^archiviostorico\.unica\.it$',
  '^nginx$',
  '^localhost$',
];

// Config DEV
#$config['config_split.config_split.config_dev']['status'] = true;
#$config['config_split.config_split.config_live']['status'] = false;

// Config LIVE
$config['config_split.config_split.config_dev']['status'] = false;
$config['config_split.config_split.config_live']['status'] = true;

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/default/services.dev.yml';

// Various conf override
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$GLOBALS['_kint_settings']['maxLevels'] = 4;
$config['_kint_settings']['maxLevels'] = 4;
$settings['_kint_settings']['maxLevels'] = 4;
$config['google_analytics.settings']['account'] = 'UA-';

