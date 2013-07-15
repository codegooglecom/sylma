<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

class _Foreach extends common\basic\Structured implements common\argumentable, common\scope, common\structure, common\addable {

  protected $looped;
  protected $var;
  protected $key;

  public function __construct(common\_window $controler, $looped, common\_var $var, $content = null, $key = null) {

    $this->setControler($controler);

    $this->looped = $looped;
    $this->var = $var;
    $this->key = $key;

    $var->insert(null, false);

    if ($content) $this->addContent($content);
  }

  public function onAdd() {

    $this->getWindow()->loadContent($this->looped);
    $this->getWindow()->loadContent($this->getContent());
  }

  protected function getKey() {

    return $this->key;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
       'foreach' => array(
         'looped' => $this->looped,
         'key' => $this->getKey(),
         'var' => $this->var,
         'content' => $this->getContent(),
       ),
    ));
  }
}