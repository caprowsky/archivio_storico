<?php

namespace Drupal\layout_builder_browser\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the LayoutBuilderBrowserBlockCategory entity.
 *
 * @ConfigEntityType(
 *   id = "layout_builder_browser_blockcat",
 *   label = @Translation("Layout builder browser block category"),
 *   handlers = {
 *     "list_builder" =
 *   "Drupal\layout_builder_browser\Form\BlockCategoryListingForm",
 *     "form" = {
 *       "add" = "Drupal\layout_builder_browser\Form\BlockCategoryForm",
 *       "edit" = "Drupal\layout_builder_browser\Form\BlockCategoryForm",
 *       "delete" =
 *   "Drupal\layout_builder_browser\Form\BlockCategoryDeleteConfirmForm",
 *     }
 *   },
 *   config_prefix = "layout_builder_browser_blockcat",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *     "blocks"
 *   },
 *   links = {
 *     "edit-form" =
 *   "/admin/config/content/layout-builder-browser/categories/{layout_builder_browser_blockcat}",
 *     "delete-form" =
 *   "/admin/config/content/layout-builder-browser/categories/{layout_builder_browser_blockcat}/delete",
 *   }
 * )
 */
class LayoutBuilderBrowserBlockCategory extends ConfigEntityBase {

  /**
   * The layout_builder_browser_blockcat ID.
   *
   * @var string
   */
  public $id;

  /**
   * The layout_builder_browser_blockcat label.
   *
   * @var string
   */
  public $label;

  /**
   * The weight.
   *
   * @var int
   *   The weight.
   */
  protected $weight;

  /**
   * List of blocks in current group with their settings.
   *
   * @var array
   */
  public $blocks;

  /**
   * Retrieve blocks.
   *
   * @return array
   *   List of blocks.
   */
  public function getBlocks() {
    return $this->blocks;
  }

  /**
   * Set blocks.
   *
   * @param array $blocks
   *   Array of blocks.
   */
  public function setBlocks(array $blocks) {
    $this->blocks = $blocks;
  }

  /**
   * Clear all blocks.
   */
  public function clearBlocks() {
    $this->blocks = [];
  }

  /**
   * Add a block.
   *
   * @param array $block
   *   A block.
   */
  public function addBlock(array $block) {
    $this->blocks[] = $block;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
  }

}
