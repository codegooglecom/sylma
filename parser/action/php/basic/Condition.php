<?php

namespace sylma\parser\action\php\basic;
use sylma\core, sylma\parser\action\php, sylma\dom;

require_once('core/argumentable.php');
require_once(dirname(__dir__) . '/scope.php');
require_once(dirname(__dir__) . '/structure.php');
require_once('Controled.php');

class Condition extends Controled implements core\argumentable, php\scope, php\structure {

  protected $aContent = array();
  protected $test;

  protected $bTemplate = false;

  public function __construct(php\_window $controler, $test, $content = null) {

    $this->setControler($controler);
    $this->setTest($test);

    if ($content) $this->addContent($content);
  }

  public function addContent($mVal) {

    if (is_array($mVal)) {

      foreach ($mVal as $mSub) $this->addContent($mSub);
    }
    else {

      if (is_object($mVal)) {

        if ($mVal instanceof dom\node) {

          $this->bTemplate = true;
        }
      }

      $this->aContent[] = $this->getControler()->create('line', array($this->getControler(), $mVal));

    }
  }

  public function getTest() {

    return $this->test;
  }

  public function setTest($test) {

    $this->test = $test;
  }

  protected function useTemplate() {

    return $this->bTemplate;
  }

  public function asArgument() {

    $sArgument = null;

    if ($this->useTemplate()) {

      $window = $this->getControler();

      $insert = $window->createInsert($window->argToInstance(true), false, null, false);
      $sArgument = $insert->getKey();
      $this->addContent($insert);
      $window->add($window->createInsert($window->argToInstance(false), false, $insert->getKey(), false));
    }

    return $this->getControler()->createArgument(array(
       'condition' => array(
          '@argument' => $sArgument,
          'test' => $this->test,
          'content' => $this->aContent,
       )
    ));
  }
}