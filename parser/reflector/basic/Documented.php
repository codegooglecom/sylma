<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

\Sylma::load('Master.php', __DIR__);

abstract class Documented extends Master {

  /**
   *
   * @var common\_window
   */
  private $window;

  /**
   *
   * @var dom\handler
   */
  private $document;

  public function setWindow(common\_window $window) {

    $this->window = $window;
  }

  /**
   *
   * @return common\_window
   */
  public function getWindow() {

    if (!$this->window) {

      $this->throwException('No window defined');
    }

    return $this->window;
  }

  protected function setDocument(dom\handler $doc) {

    $this->document = $doc;
  }

  /**
   *
   * @return dom\handler
   */
  protected function getDocument() {

    return $this->document;
  }
}
