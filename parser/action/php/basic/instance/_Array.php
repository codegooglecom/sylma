<?php

namespace sylma\parser\action\php\basic\instance;
use sylma\parser\action\php;

require_once('_Scalar.php');
require_once(dirname(dirname(__dir__)) . '/_instance.php');

class _Array extends _Scalar implements php\_instance {

  private $aValues;

  public function __construct(php\_window $controler, array $aValues = array()) {

    $this->setControler($controler);
    $this->aValues = $aValues;
  }

  public function asArgument() {

    $window = $this->getControler();
    $aContent = array();

    foreach ($this->aValues as $mKey => $mVal) {

      $aContent[] = array(
        'key' => $window->argToInstance($mKey),
        'value' => $mVal,
      );
    }

    return $this->getControler()->createArgument(array(
      'array' => array('#item' =>  $aContent),
    ));
  }
}
