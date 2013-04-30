<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

class _Line extends common\basic\Controled implements core\argumentable, common\addable {

  private $content;

  public function __construct(common\_window $controler, $content) {

    $this->setControler($controler);
    $controler->checkContent($content);
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