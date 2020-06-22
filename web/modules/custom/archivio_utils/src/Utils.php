<?php

namespace Drupal\archivio_utils;


class Utils {

  /**
   * @param  \Drupal\node\Entity\Node $entity
   * @param $field_name string
   */
  public function implodeValue($entity, $field_name) {
    $values = $entity->get($field_name)->getValue();
    foreach ($values as $value) {
      $valori[] = $value['value'];
    }
    return implode(", ", $valori);
  }

  public function checkFigliDalPadre($term_id) {
    $query = \Drupal::entityQuery('taxonomy_term')->condition('parent', $term_id);
    return $query->execute();
  }

  public function checkPadreDalFiglio($term) {
    $padre = $term->get('parent')->getValue();
    return $padre[0]['target_id'];
  }

  /**
   * @var \Drupal\node\Entity\Node $node *
   * @return mixed
   */
  public function checkImage($entity) {
    if ($entity->hasField('field_immagine') && !$entity->get('field_immagine')->isEmpty()) {
      $image = $entity->get('field_immagine')->referencedEntities();
      $fid = $image[0]->id();
      return $fid;
    } elseif ($entity->hasField('field_foto') && !$entity->get('field_foto')->isEmpty()) {
      $image = $entity->get('field_foto')->referencedEntities();
      $fid = $image[0]->id();
      return $fid;
    }


    else {
      return FALSE;
    }
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

  /**
   * @var \Drupal\node\Entity\Node $carriera
   * @var \Drupal\node\Entity\Node $persona
   */
  public function getIconaCarriera($carriera) {
    global $base_url;
    $persone = $carriera->get('field_persona')->referencedEntities();
    $persona = $persone[0];
    /**  @var \Drupal\node\Entity\Node $persona **/
    $sesso = $persona->get('field_sesso')->getValue();
    $tipologia_carriera = $carriera->get('field_tipologia_carriera')->getValue();

    if ($tipologia_carriera[0]['value'] == "s" && !$carriera->get('field_data_fine_carriera')->isEmpty()) {
      $tipologia_carriera[0]['value'] = "l";
    }

    $icona_url  = $base_url . '/themes/custom/archivio/images/icone/' . $sesso[0]['value'] . '_' . $tipologia_carriera[0]['value'] . '.svg';
    return $icona_url;
  }
}

