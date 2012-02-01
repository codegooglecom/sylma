<?php

namespace sylma\parser\action\php\basic;
use sylma\core, sylma\parser\action\php;

require_once('Controled.php');
require_once('core/argumentable.php');

class Template extends Controled implements core\argumentable {

  private $content = '';
  protected static $iKey = 0;

  public function __construct(php\_window $controler, $mContent) {

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