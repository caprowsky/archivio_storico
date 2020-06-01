<?php

namespace Drupal\archivio_cer;


class Utils
{

  /**
   * @param \Drupal\node\Entity\Node $carriera
   */
  public function updateCarriera($carriera) {
    /** Se il link è vuoto */
    if ($carriera->get('field_persona')->isEmpty()) {
      if (isset($carriera->original) && !empty($carriera->original)) {
        /**  @param \Drupal\node\Entity\Node $carriera_originale **/
        $carriera_originale = $carriera->original;
        if (!$carriera_originale->get('field_persona')->isEmpty()) {
          $persona_originale = $this->getPersona($carriera_originale);
          $this->removeCarrieraFromPersona($persona_originale, $carriera->id());
        }
      }
    } else {
      /** Se invece il link non è vuoto */
      /** @var \Drupal\node\Entity\Node $persona
       */

      /** se la carriera è nuova */
      if (!isset($carriera->original) || empty($carriera->original)) {
        $persona = $this->getPersona($carriera);
        $persona->get('field_link_carriera')->appendItem(['target_id' => $carriera->id()]);
        $persona->save();
      }

      /** se la carriera non è nuova */
      if (isset($carriera->original) && !empty($carriera->original)) {
        $carriera_originale = $carriera->original;
        $persona_originale = $this->getPersona($carriera_originale);
        $persona = $this->getPersona($carriera);

        if ($persona->id() != $persona_originale->id()) {
          $this->removeCarrieraFromPersona($persona_originale, $carriera->id());
          $persona->get('field_link_carriera')->appendItem(['target_id' => $carriera->id()]);
          $persona->save();
        }
      }
    }
  }

  /**
   * @param \Drupal\node\Entity\Node $carriera
   */
  public function deleteCarriera($carriera) {
    if (!$carriera->get('field_persona')->isEmpty()) {
      $persona = $this->getPersona($carriera);
      $this->removeCarrieraFromPersona($persona, $carriera->id());
    }
  }

  /**
   * @param \Drupal\node\Entity\Node $persona
   * @param $carriera_id
   */
  private function removeCarrieraFromPersona($persona, $carriera_id) {
    $carriere = $persona->get('field_link_carriera')->getValue();
    foreach ($carriere as $key => $carriera) {
      if ($carriera['target_id'] == $carriera_id) {
        unset($persona->field_link_carriera[$key]);
        $persona->save();
      }
    }
  }

  /**
   * @param \Drupal\node\Entity\Node $carriera
   * return $persona
   */
  private function getPersona($carriera) {
    $personas = $carriera->get('field_persona')->referencedEntities();
    return $personas[0];
  }
}
