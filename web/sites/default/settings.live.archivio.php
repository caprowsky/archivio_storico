<?php

$settings['trusted_host_patterns'] = [
  '^www\.archiviostorico\.org$',
  '^nginx$',
  '^localhost$',
];


// Config LIVE
$config['config_split.config_split.config_dev']['status'] = false;
$config['config_split.config_split.config_live']['status'] = true;

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/default/services.live.yml';

// Various conf override
$config['reroute_email.settings']['enable'] = FALSE;
$config['system.logging']['error_level'] = 'hide';
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['css']['gzip'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;
$config['system.performance']['js']['gzip'] = TRUE;
$config['system.performance']['response']['gzip'] = TRUE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = FALSE;
$config['views.settings']['ui']['show']['performance_statistics'] = FALSE;

