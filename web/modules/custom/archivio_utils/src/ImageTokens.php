<?php

namespace Drupal\archivio_utils;

/**
 * Class DefaultService.
 *
 * @package Drupal\archivio_utils
 */
class ImageTokens {
  private function getImage($entity = NULL, $mode = 'normal') {
    if (method_exists($entity, 'getEntityTypeId')) {
      $entity_type = $entity->getEntityTypeId();
    }
    else {
      $entity_type = NULL;
    }

    switch ($entity_type) {
      case 'node':
        return $this->getImageNode($entity, 'file');
        break;

      case 'taxonomy_term':
        return $this->getImageTerm($entity, 'file');
        break;
    }
  }

  public function getImageUrl($entity = NULL, $mode = 'normal') {
    $image = $this->getImage($entity, $mode);
    if (isset($image['url'])) {
      return $image['url'];
    }
  }

  public function getImageMime($entity = NULL) {
    $image = $this->getImage($entity);
    if (isset($image['mime'])) {
      return $image['mime'];
    }
  }

  public function getImageNode($entity, $mode) {
    $image = [];
    $node = $entity;
    $service = \Drupal::service('archivio_utils.utils');
    switch ($entity->bundle()) {
      case 'page':
      case 'persona':
        $fid = $service->checkImage($node);
        if (!empty($fid)) {
          $image = $service->getSfondo($fid, $mode);
        }
        break;
    }

    return $image;
  }

  public function getImageTerm($entity, $mode) {
    $image = [];
    $service = \Drupal::service('archivio_utils.utils');
    if (!$entity->get('field_immagine')->isEmpty()) {
      $fid = $service->checkImage($entity);
      $image = $service->getSfondo($fid, $mode);
    }
    return $image;
  }

  /**
   * Restituisce lo sfondo in base al fid del media
   *
   * @param $fid
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getSfondo($fid, $mode) {
    $entity = \Drupal::entityTypeManager()->getStorage('media')->load($fid);
    switch ($mode) {
      case 'file':
        if (!empty($entity)) {
          $images = $entity->get('field_media_image');
          $image = $images->getValue();
          $file = \Drupal\file\Entity\File::load($image[0]['target_id']);
          $result = [];
        }
        else {
          return FALSE;
        }

        if (!empty($file)) {
          $result['mime'] = $file->getMimeType();
          $result['width'] = '1200';
          $result['height'] = '630';
          $style = \Drupal::entityTypeManager()
            ->getStorage('image_style')
            ->load('metatag');
          $destination = $style->buildUri($file->getFileUri());
          $result['url'] = $style->buildUrl($file->getFileUri());
          $style->createDerivative($file->getFileUri(), $destination);
        }
        return $result;
        break;

      case 'sfondo':
        if (!empty($entity)) {
          return $entity->field_media_image->view('sfondo');
        }
        else {
          return TRUE;
        }
        break;
    }

    return TRUE;
  }


}
