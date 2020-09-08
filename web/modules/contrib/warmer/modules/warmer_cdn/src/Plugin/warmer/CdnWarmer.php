<?php

namespace Drupal\warmer_cdn\Plugin\warmer;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\warmer\Plugin\WarmerPluginBase;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The cache warmer for the built-in entity cache.
 *
 * @Warmer(
 *   id = "cdn",
 *   label = @Translation("CDN"),
 *   description = @Translation("Executes HTTP requests to warm the edge caches. It is useful without a CDN as well, as it will also warm Varnish and Page Cache.")
 * )
 */
final class CdnWarmer extends WarmerPluginBase {

  use UserInputParserTrait;
  use LoggerChannelTrait;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    assert($instance instanceof CdnWarmer);
    $instance->setHttpClient($container->get('http_client'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = []) {
    // Ensure items are fully loaded URLs.
    $urls = array_map([$this, 'resolveUri'], $ids);
    return array_filter($urls, [UrlHelper::class, 'isValid']);
  }

  /**
   * {@inheritdoc}
   */
  public function warmMultiple(array $items = []) {
    $headers = $this->parseHeaders();
    $verify = (bool) $this->getConfiguration()['verify'];
    $max_concurrent_requests = (int) $this->getConfiguration()['maxConcurrentRequests'];

    // Default to one request at a time.
    if ($max_concurrent_requests <= 0) {
      $max_concurrent_requests = 1;
    }
    $promises = [];
    $success = 0;

    foreach ($items as $key => $url) {
      // Fire async request.
      $promises[] = $this->httpClient
        ->requestAsync('GET', $url, ['headers' => $headers, 'verify' => $verify])
        ->then(function (ResponseInterface $response) use (&$success) {
          if ($response->getStatusCode() < 399) {
            $success++;
          }
        }, function (\Exception $e) {
          $this->getLogger('warmer')->warning($e->getMessage());
        });
      // Wait for all fired requests if max number is reached.
      $item_keys = array_keys($items);
      if ($key % $max_concurrent_requests == 0 || $key == end($item_keys)) {
        \GuzzleHttp\Promise\all($promises)->wait();
        $promises = [];
      }
    }

    return $success;
  }

  /**
   * Parses the configuration to extract the headers to inject in every request.
   *
   * @return array
   *   The array of headers as expected by Guzzle.
   */
  private function parseHeaders() {
    $configuration = $this->getConfiguration();
    $header_lines = $configuration['headers'];
    // Parse headers.
    return array_reduce($header_lines, function ($carry, $header_line) {
      list($name, $value_line) = array_map('trim', explode(':', $header_line));
      $values = array_map('trim', explode(';', $value_line));
      $values = array_filter($values);
      $values = count($values) === 1 ? reset($values) : $values;
      $carry[$name] = $values;
      return $carry;
    }, []);
  }

  /**
   * {@inheritdoc}
   */
  public function buildIdsBatch($cursor) {
    // Parse the sitemaps and extract the URLs.
    $config = $this->getConfiguration();
    $urls = empty($config['urls']) ? [] : $config['urls'];
    $cursor_position = is_null($cursor) ? -1 : array_search($cursor, $urls);
    if ($cursor_position === FALSE) {
      return [];
    }
    return array_slice($urls, $cursor_position + 1, (int) $this->getBatchSize());
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    $this->validateHeaders($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function addMoreConfigurationFormElements(array $form, SubformStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $form['urls'] = [
      '#type' => 'textarea',
      '#title' => $this->t('URLs'),
      '#description' => $this->t('Enter the list of URLs. One on each line. Examples: https://example.org/foo/bar, /foo/bar.'),
      '#default_value' => empty($configuration['urls']) ? '' : implode("\n", $configuration['urls']),
    ];
    $form['headers'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Headers'),
      '#description' => $this->t('Specific headers to use when making HTTP requests. Format: <code>Header-Name: value1; value2</code>'),
      '#default_value' => empty($configuration['headers']) ? '' : implode("\n", $configuration['headers']),
    ];
    $form['verify'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable SSL verification'),
      '#description' => $this->t('Enable SSL verification. Recommended to keep it checked for security reasons.'),
      '#default_value' => isset($configuration['verify']) ? $configuration['verify'] : TRUE,
    ];
    $form['maxConcurrentRequests'] = [
      '#type' => 'number',
      '#min' => 1,
      '#step' => 1,
      '#title' => $this->t('Maximum number of concurrent Requests.'),
      '#description' => $this->t('The maximum number of concurrent requests.'),
      '#default_value' => empty($configuration['maxConcurrentRequests']) ? 10 : $configuration['maxConcurrentRequests'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $configuration = $form_state->getValues() + $this->configuration;
    $configuration['urls'] = $this->extractTextarea($configuration, 'urls');
    $configuration['headers'] = $this->extractTextarea($configuration, 'headers');
    $this->setConfiguration($configuration);
  }

  /**
   * Set the HTTP client.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The client.
   */
  public function setHttpClient(ClientInterface $client) {
    $this->httpClient = $client;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'maxConcurrentRequests' => 10,
    ] + parent::defaultConfiguration();
  }

}
