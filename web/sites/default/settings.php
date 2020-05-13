<?php

$settings['hash_salt'] = 'UiAKh_kZk4z3_Q6UB7GF5TLWZwZ0gFz9pTF7teo1TOnxCUUFvk7oiv4kM6ZiEztrKUJ7BLvNrw';

$settings['update_free_access'] = FALSE;

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$settings['entity_update_batch_size'] = 50;

$settings['entity_update_backup'] = TRUE;

$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'drupal',
  'password' => 'drupal',
  'prefix' => '',
  'host' => 'mariadb',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
$settings['config_sync_directory'] = 'sites/default/config';



if (isset($_SERVER['ARCHIVIO_ENV'])) {
  switch ($_SERVER['ARCHIVIO_ENV']) {
    case 'dev':
      include __DIR__ . '/settings.dev.archivio.php';
    break;
    
    case 'live':
      include __DIR__ . '/settings.live.archivio.php';
      break;
  }
} else {
  $config['config_split.config_split.config_dev']['status'] = FALSE;
  $config['config_split.config_split.config_live']['status'] = FALSE;
}
$settings['install_profile'] = 'standard';

ini_set('memory_limit', '1024M');


// Redis
$settings['cache']['bins']['form'] = 'cache.backend.database';
$settings['redis.connection']['interface'] = 'PhpRedis';
$settings['redis.connection']['host'] = 'redis';
$settings['cache']['default'] = 'cache.backend.redis';
$settings['container_yaml'][] = 'modules/contrib/redis/example.services.yml';
