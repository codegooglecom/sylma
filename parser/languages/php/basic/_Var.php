<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

abstract class _Var extends common\basic\Controled implements common\_var, common\addable, core\tokenable {

  const CHECK_INSERT = true;

  private $sName = '';
  protected $instance;

  protected $bInserted = false;
  protected $bStatic = false;

  protected $content;

  public function __construct(php\window $controler, common\_instance $instance, $sName, common\argumentable $content = null) {

    $this->setName($sName);
    $this->setControler($controler);
    $this->setInstance($instance);
    if ($content) $this->setContent($content);
  }

  public function getInstance() {

    return $this->instance;
  }

  protected function setContent(common\argumentable $content) {

    $this->content = $content;
  }

  protected function getContent() {

    return $this->content;
  }

  public function getInsert($content = null, $bInstruct = true) {

    if (!$content) $content = $this->getContent();
    $window = $this->getWindow();
    $assign = $window->createAssign($this, $content);

    return $bInstruct ? $window->createInstruction($assign) : $assign;
  }

  public function insert(common\argumentable $content = null, $bDebug = true) {

    $window = $this->getControler();

    if (!$this->bInserted && !$content && !$this->getContent()) {

      if ($bDebug) $window->throwException(sprintf('Variable "%s" cannot be inserted, no content defined', $this->getName()));

      $this->bInserted = true;
    }
    else {

      if (!$this->bInserted || $content) {

        $this->bInserted = true;
        $window->add($this->getInsert($content, false));
      }
    }

    return $this;
  }

  public function onAdd() {

    if ($this->getContent()) {

      $this->getControler()->loadContent($this->getContent());
    }
  }

  public function isStatic($bValue = null) {

    if (!is_null($bValue)) $this->bStatic = $bValue;
    return $this->bStatic;
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

  protected function checkInserted() {

    if (!$this->isStatic() && !$this->bInserted && $this->getContent() && self::CHECK_INSERT) {

      $this->getControler()->throwException(sprintf('Variable "%s" has not been inserted', $this->getName()));
    }
  }

  public function asArgument() {

    //$this->checkInserted();

    return $this->getControler()->createArgument(array(
      'var' => array(
        '@name' => $this->sName,
      ),
    ));
  }

  public function asToken() {

    return "php:var [name={$this->getName()}]";
  }
}