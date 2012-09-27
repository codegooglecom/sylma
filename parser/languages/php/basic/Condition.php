<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

require_once('core/argumentable.php');
require_once('parser/languages/common/scope.php');
require_once('parser/languages/common/structure.php');
\Sylma::load('/parser/languages/common/basic/Controled.php');

class Condition extends common\basic\Controled implements core\argumentable, common\scope, common\structure {

  protected $aContent = array();
  protected $test;

  protected $bTemplate = false;

  public function __construct(common\_window $controler, $test, $content = null) {

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
    $window = $this->getControler();

    if ($this->useTemplate()) {
      
      $insert = $window->createInsert($window->argToInstance(true), '', null, false);
      $sArgument = $insert->getKey();
      $this->addContent($insert);
      $window->add($window->createInsert($window->argToInstance(false), '', $insert->getKey(), false));
    }

    return $this->getControler()->createArgument(array(
       'condition' => array(
          '@context' => $window->getContext(),
          '@argument' => $sArgument,
          'test' => $this->test,
          'content' => $this->aContent,
       )
    ));
  }
}