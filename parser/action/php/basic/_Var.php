<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once(dirname(__dir__) . '/_var.php');

require_once('Controled.php');

abstract class _Var extends Controled implements php\_var {

  private $sName = '';
  protected $instance;

  protected $bInserted = false;
  protected $content;

  public function __construct(php\_window $controler, php\_instance $instance, $sName, php\linable $content) {

    $this->setName($sName);
    $this->setControler($controler);
    $this->setInstance($instance);
    $this->setContent($content);
  }

  public function getInstance() {

    return $this->instance;
  }

  protected function setContent(php\linable $content) {

    $this->content = $content;
  }

  protected function getContent() {

    return $this->content;
  }

  public function insert(php\linable $content = null) {

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

  protected function setInstance(php\_instance $instance) {

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