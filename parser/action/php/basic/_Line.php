<?php

namespace sylma\parser\action\php\basic;
use \sylma\core, \sylma\parser\action\php;

require_once('core/argumentable.php');
require_once('Controled.php');

class _Line extends Controled implements core\argumentable {

  private $content;

  public function __construct(php\_window $controler, $content) {

    $this->setControler($controler);
    //$controler->checkContent($content);
    $this->content = $content;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'line' => $this->content,
    ));
  }
}