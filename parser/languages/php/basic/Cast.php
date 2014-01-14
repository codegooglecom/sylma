<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common;

class Cast extends common\basic\Controled implements common\argumentable, common\addable {

  protected $content;
  protected $sType;

  public function __construct(common\_window $window, $content, $sType) {

    $this->setWindow($window);
    $this->setContent($content);
    $this->setType($sType);
  }

  protected function getType() {

    return $this->sType;
  }

  protected function setType($sType) {

    $this->sType = $sType;
  }

  protected function setContent($content) {

    $this->content = $content;
  }

  protected function getContent() {

    return $this->content;
  }

  public function onAdd() {

    $this->getWindow()->loadContent($this->getContent());
  }

  public function asArgument() {

    return $this->getWindow()->createArgument(array(
      'cast' => array(
        '@type' => $this->getType(),
        $this->getContent(),
      ),
    ));
  }
}

