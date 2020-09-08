<?php

namespace Drupal\layout_builder_browser\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\layout_builder\Context\LayoutBuilderContextTrait;
use Drupal\layout_builder\Controller\ChooseBlockController;
use Drupal\layout_builder\LayoutBuilderHighlightTrait;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BrowserController.
 */
class BrowserController extends ControllerBase {

  use AjaxHelperTrait;
  use LayoutBuilderContextTrait;
  use LayoutBuilderHighlightTrait;
  use StringTranslationTrait;


  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * BrowserController constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(BlockManagerInterface $block_manager, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user = NULL) {
    $this->blockManager = $block_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Overrides the core ChooseBlockController::build.
   */
  public function browse(SectionStorageInterface $section_storage, $delta, $region) {

    $config = $this->config('layout_builder_browser.settings');
    $enabled_section_storages = $config->get('enabled_section_storages');
    if (!in_array($section_storage->getPluginId(), $enabled_section_storages)) {
      $default_choose_block_controller = new ChooseBlockController($this->blockManager, $this->entityTypeManager, $this->currentUser);
      return $default_choose_block_controller->build($section_storage, $delta, $region);
    }
    $build['filter'] = [
      '#type' => 'search',
      '#title' => $this->t('Filter by block name'),
      '#title_display' => 'invisible',
      '#size' => 30,
      '#placeholder' => $this->t('Filter by block name'),
      '#attributes' => [
        'class' => ['js-layout-builder-filter'],
        'title' => $this->t('Enter a part of the block name to filter by.'),
      ],
    ];

    $block_categories['#type'] = 'container';
    $block_categories['#attributes']['class'][] = 'block-categories';
    $block_categories['#attributes']['class'][] = 'js-layout-builder-categories';
    $block_categories['#attributes']['data-layout-builder-target-highlight-id'] = $this->blockAddHighlightId($delta, $region);

    // @todo Explicitly cast delta to an integer, remove this in
    //   https://www.drupal.org/project/drupal/issues/2984509.
    $delta = (int) $delta;

    $definitions = $this->blockManager->getFilteredDefinitions('layout_builder', $this->getAvailableContexts($section_storage), [
      'section_storage' => $section_storage,
      'delta' => $delta,
      'region' => $region,
      'list' => 'inline_blocks',
    ]);

    $blockcats = $this->entityTypeManager
      ->getStorage('layout_builder_browser_blockcat')
      ->loadMultiple();
    uasort($blockcats, ['Drupal\Core\Config\Entity\ConfigEntityBase', 'sort']);

    foreach ($blockcats as $blockcat) {

      $blocks = [];
      $items = $blockcat->getBlocks();
      uasort($items, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
      foreach ($items as $item) {
        $key = $item['block_id'];
        if (isset($definitions[$key])) {
          $blocks[$key] = $definitions[$key];
          $blocks[$key]['layout_builder_browser_data'] = $item;
        }
      }
      $block_categories[$blockcat->id()]['links'] = $this->getBlocks($section_storage, $delta, $region, $blocks);
      if ($block_categories[$blockcat->id()]['links']) {
        // Only add the information if the category has links.
        $block_categories[$blockcat->id()]['#type'] = 'details';
        $block_categories[$blockcat->id()]['#attributes']['class'][] = 'js-layout-builder-category';
        $block_categories[$blockcat->id()]['#open'] = TRUE;
        $block_categories[$blockcat->id()]['#title'] = Html::escape($blockcat->label());
      }
      else {
        // Since the category doesn't have links, remove it to avoid confusion.
        unset($block_categories[$blockcat->id()]);
      }

    }

    $build['block_categories'] = $block_categories;
    $build['#attached']['library'][] = 'layout_builder_browser/browser';

    return $build;
  }

  /**
   * Gets a render array of block links.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   * @param string $region
   *   The region the block is going in.
   * @param array $blocks
   *   The information for each block.
   *
   * @return array
   *   The block links render array.
   */
  protected function getBlocks(SectionStorageInterface $section_storage, $delta, $region, array $blocks) {
    $links = [];

    foreach ($blocks as $block_id => $block) {
      $attributes = $this->getAjaxAttributes();
      $attributes['class'][] = 'js-layout-builder-block-link';
      $attributes['class'][] = 'layout-builder-browser-block-item';

      $block_render_array = [];
      if (isset($block["layout_builder_browser_data"]["image_path"]) && trim($block["layout_builder_browser_data"]["image_path"]) != '') {
        $block_render_array['image'] = [
          '#theme' => 'image',
          '#uri' => $block["layout_builder_browser_data"]["image_path"],
          '#alt' => $block['layout_builder_browser_data']['image_alt'] ?: $block['admin_label'],
        ];
      }
      $block_render_array['label'] = ['#markup' => $block['admin_label']];
      $link = [
        '#type' => 'link',
        '#title' => $block_render_array,
        '#url' => Url::fromRoute('layout_builder.add_block',
          [
            'section_storage_type' => $section_storage->getStorageType(),
            'section_storage' => $section_storage->getStorageId(),
            'delta' => $delta,
            'region' => $region,
            'plugin_id' => $block_id,
          ]
        ),
        '#attributes' => $attributes,
      ];

      $links[] = $link;
    }
    return $links;
  }

  /**
   * Get dialog attributes if an ajax request.
   *
   * @return array
   *   The attributes array.
   */
  protected function getAjaxAttributes() {
    if ($this->isAjax()) {
      return [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'dialog',
        'data-dialog-renderer' => 'off_canvas',
        'data-dialog-options' => Json::encode(['width' => '500px']),
      ];
    }
    return [];
  }

}
