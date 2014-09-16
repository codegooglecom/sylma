<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

class _While extends common\basic\Structured implements common\argumentable, common\scope, common\structure, common\addable {

  protected $looped;
  protected $var;
  protected $key;

  public function __construct(common\_window $controler, $test, $content = null) {

    $this->setControler($controler);

    $this->test = $test;

    if ($content) {

      $this->addContent($content);
    }
  }

  public function onAdd() {

    $this->getWindow()->loadContent($this->test);
    $this->getWindow()->loadContent($this->getContent());
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
       'while' => array(
         'test' => $this->test,
         'content' => $this->getContent(),
       ),
    ));
  }
}