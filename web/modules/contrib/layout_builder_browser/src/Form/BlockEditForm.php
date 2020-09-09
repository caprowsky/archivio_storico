<?php

namespace Drupal\layout_builder_browser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Component\Utility\Html;

/**
 * Builds a listing of block entities.
 */
class BlockEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_builder_browser_block_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $block_id = NULL) {


    $block_categories = \Drupal::entityTypeManager()
      ->getStorage('layout_builder_browser_blockcat')
      ->loadMultiple();

    $block = NULL;
    $category_options = [];
    $selected_category = NULL;
    foreach ($block_categories as $category) {
      $category_options[$category->id()] = $category->label();
      if (!$block) {
        $category_blocks = $category->getBlocks();
        foreach ($category_blocks as $category_block) {
          if ($category_block['block_id'] == $block_id) {
            $selected_category = $category->id();
            $block = $category_block;
            break;
          }
        }
      }
    }

    if (!$block) {
      return; // not found, lets handle this better...
    }


    $form['image_path'] = [
      '#type' => 'textfield',
      '#title' => t('Path to image'),
      '#default_value' => $block["image_path"],
    ];

    $form['category'] = [
      '#type' => 'select',
      '#label' => t('Category'),
      '#options' => $category_options,
      '#default_value' => $selected_category
    ];
    $form['block_id'] = [
      '#type' => 'hidden',
      '#value' => $block_id,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {


    $category = $form_state->getValue('category');
    $block_id = $form_state->getValue('block_id');
    $image_path = $form_state->getValue('image_path');


    $block_category = \Drupal::entityTypeManager()
      ->getStorage('layout_builder_browser_blockcat')
      ->load($category);


    $b = 1;
  }


}
