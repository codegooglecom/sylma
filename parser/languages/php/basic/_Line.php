<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

require_once('core/argumentable.php');
\Sylma::load('/parser/languages/common/basic/Controled.php');

class _Line extends common\basic\Controled implements core\argumentable {

  private $content;

  public function __construct(common\_window $controler, $content) {

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