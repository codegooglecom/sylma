<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

class _Line extends common\basic\Controled implements core\argumentable, common\addable, common\instruction {

  private $content;

  public function __construct(common\_window $controler, $content) {

//if ($content instanceof \sylma\view\parser\component\Container) \Sylma::throwException ('tst');
    $this->setControler($controler);
    $controler->checkContent($content);

    if (is_scalar($content)) {

      $this->getWindow()->throwException('Scalar not allowed');
    }

    $this->setContent($content);
  }

  protected function setContent($content) {

    $this->content = $content;
  }

  protected function getContent() {

    return $this->content;
  }

  public function onAdd() {

    $this->getControler()->loadContent($this->getContent());
  }

  public function asArgument() {

    $content = $this->content;

    if ($content) {

      $result = $this->getControler()->createArgument(array(
        'line' => $this->content,
      ));
    }
    else {

      $result = null;
    }

    return $result;
  }
}