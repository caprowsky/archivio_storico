<?php

namespace Drupal\layout_builder_browser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds a listing of block entities.
 */
class BlockListingForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_builder_browser_block_listing';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'title' => [
        'data' => $this->t('Title'),
      ],
      'provider' => $this->t('Provider'),
      'image_path' => $this->t('Image path'),
      'category' => $this->t('Category'),
    ];
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $groups = $this->loadGroups();

    $form['categories'] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#empty' => $groups['hidden_blocks'] ? '' : $this->t('No blocks or block categories defined.'),
    ];


    foreach ($groups['categories'] as $category_group) {
      $category_group_id = $category_group["category"]->id;
      $form['categories'][$category_group_id] = $this->buildBlockCategoryRow($category_group["category"]);
      foreach ($category_group['blocks'] as $block) {
        $block['category'] = $category_group_id;
        $form['categories'][$block['block_id']] = $this->buildBlockRow($block);
      }
    }

    // Output the list of blocks without a block category separately.
    if (!empty($groups['hidden_blocks'])) {
      $form['categories']['hidden_blocks'] = [
        'type' => [
          '#theme_wrappers' => [
            'container' => [
              '#attributes' => ['class' => 'block-category'],
            ],
          ],
          '#type' => 'markup',
          '#markup' => '<h3>' . $this->t('Hidden blocks, not assigned to a category') . '</h3>',
        ],
      ];

      foreach ($groups['hidden_blocks'] as $hidden_block) {
        $hidden_block['block_id'] = $hidden_block['id'];
        $form['categories'][$hidden_block['id']] = $this->buildBlockRow($hidden_block);
      }
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
      '#button_type' => 'primary',
    ];

    $form['#attached']['library'][] = 'layout_builder_browser/admin';
    return $form;
  }


  private function buildBlockRow($block) {
    $row = [];
    $row['title'] = [
      '#type' => 'markup',
      '#markup' => '<div class="block-title">' . $block['label'] . '</div>',
    ];

    $row['definition_category'] = [
      '#type' => 'markup',
      '#markup' => $block["definition_category"]
    ];

    if ($block['category'] == 'hidden') {
      $row['definition_category']['#wrapper_attributes'] = [
        'colspan' => 2,
      ];
    }
    else {
      $row['image_path'] = [
        '#type' => 'textfield',
        '#default_value' => $block['image_path'],
      ];
    }

    $block_categories = \Drupal::entityTypeManager()
      ->getStorage('layout_builder_browser_blockcat')
      ->loadMultiple();
    uasort($block_categories, [
      'Drupal\Core\Config\Entity\ConfigEntityBase',
      'sort',
    ]);

    $categories_options = ['hidden' => t('Hidden')];
    foreach ($block_categories as $block_category) {
      $categories_options[$block_category->id()] = $block_category->label();
    }
    $row['category'] = [
      '#type' => 'select',
      '#options' => $categories_options,
      '#default_value' => $block['category'],
    ];
    $row['#attributes'] = [
      'title' => $this->t('ID: @name', ['@name' => $block['block_id']]),
      'class' => [
        'block-wrapper',
      ],
    ];
    return $row;

  }

  /**
   * Builds an array of block categorie for display in the overview.
   */
  private function buildBlockCategoryRow($block_category) {
    return [
      'title' => [
        '#theme_wrappers' => [
          'container' => [
            '#attributes' => ['class' => 'block-category-title'],
          ],
        ],
        '#type' => 'markup',
        '#markup' => $block_category->label(),
        '#wrapper_attributes' => [
          'colspan' => 3,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $blocks = $form_state->getValue('categories');

    $block_categories = \Drupal::entityTypeManager()
      ->getStorage('layout_builder_browser_blockcat')
      ->loadMultiple();

    foreach ($block_categories as $block_category) {
      $block_category->clearBlocks();
    }

    foreach ($blocks as $id => $block) {
      if ($block['category'] != 'hidden') {
        $data = [
          'block_id' => $id,
          'weight' => 0,
          'image_path' => $block["image_path"],
        ];
        $block_categories[$block['category']]->addBlock($data);
      }
    }

    foreach ($block_categories as $block_category) {
      $block_category->save();
    }

    \Drupal::messenger()->addMessage(t('The blocks have been updated.'));
  }

  /**
   * Loads block categories and blocks, grouped by block categories.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface[][]
   *   An associative array with two keys:
   *   - categories: All available block categories, each followed by all blocks
   *     attached to it.
   *   - hidden_blocks: All blocks that aren't attached to any block categories.
   */
  public function loadGroups() {


    $block_plugin_manager = \Drupal::service('plugin.manager.block');
    $definitions = $block_plugin_manager->getFilteredDefinitions('layout_builder', NULL, ['list' => 'inline_blocks']);
    $hidden_blocks = [];
    foreach ($definitions as $id => $definition) {
      $hidden_blocks[$id] = [
        'label' => $definition['admin_label'],
        'id' => $id,
        'weight' => 0,
        'image_path' => '',
        'category' => 'hidden',
        'definition_category' => $definition['category']
      ];
    }

    $block_categories = \Drupal::entityTypeManager()
      ->getStorage('layout_builder_browser_blockcat')
      ->loadMultiple();
    uasort($block_categories, [
      'Drupal\Core\Config\Entity\ConfigEntityBase',
      'sort',
    ]);

    $block_categories_group = [];

    foreach ($block_categories as $key => $block_category) {

      $block_categories_group[$key]['category'] = $block_category;
      $block_categories_group[$key]['blocks'] = [];

      foreach ($block_category->getBlocks() as $block) {
        $block['label'] = $hidden_blocks[$block['block_id']]['label'];
        $block['definition_category'] = $hidden_blocks[$block['block_id']]['definition_category'];
        $block['block_id'] = $hidden_blocks[$block['block_id']]['id'];
        unset($hidden_blocks[$block['block_id']]);
        $block_categories_group[$key]['blocks'][] = $block;
      }
    }

    return [
      'categories' => $block_categories_group,
      'hidden_blocks' => $hidden_blocks,
    ];
  }

}
