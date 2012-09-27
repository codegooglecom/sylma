<?php

namespace sylma\parser\languages\common\basic;
use \sylma\core, \sylma\parser\languages\common;

class Variable extends Controled implements common\_var {

  private $sName = '';
  protected $instance;

  protected $bDefined = false;
  protected $bInserted = false;

  protected $content;

  public function __construct(common\_window $controler, common\_instance $instance, $sName, common\linable $content = null) {

    $this->setName($sName);
    $this->setControler($controler);
    $this->setInstance($instance);

    if ($content) $this->setContent($content);
  }

  public function getInstance() {

    return $this->instance;
  }

  protected function setContent(common\linable $content) {

    $this->content = $content;
  }

  protected function getContent() {

    return $this->content;
  }

  public function insert(common\linable $content = null) {

    $window = $this->getControler();

    if (!$content && !$this->getContent()) {

      $window->throwException(sprintf('Variable "%s" cannot be inserted, no content defined', $this->getName()));
    }

    if (!$this->bInserted || $content) {

      if (!$content) $content = $this->getContent();

      $assign = $window->create('assign', array($window, $this, $content));
      $window->add($assign);

      $this->bInserted = true;
    }
  }

  protected function setInstance(common\_instance $instance) {

    $this->instance = $instance;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  public function asArgument() {

    if (!$this->bInserted && $this->getContent()) {

      $this->getControler()->throwException(sprintf('Variable "%s" has not been inserted', $this->getName()));
    }

    return $this->getControler()->createArgument(array(
      'var' => array(
        '@name' => $this->sName,
      ),
    ));
  }
}