<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php;

require_once('Controled.php');
require_once('core/argumentable.php');

class Template extends Controled implements core\argumentable {

  private $content = '';
  protected static $iKey = 0;

  public function __construct(common\_window $controler, $mContent) {

    $this->setControler($controler);
    $this->content = $mContent;
  }

  public function asArgument() {

    self::$iKey++;

    return $this->getControler()->createArgument(array(
      'template' => array(
        '@key' => self::$iKey,
        $this->content,
    )));
  }
}