<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

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

  /**
   * @param common\_window $window
   */
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

  protected function parseDocument(dom\document $doc) {

    return $this->parseChildren($doc->getChildren());
  }

  protected function build() {

    $doc = $this->getDocument();

    if ($doc->isEmpty()) {

      $this->throwException('Empty document');
    }

    $doc->registerNamespaces($this->getNS());

    $window = $this->getWindow();
    $mContent = $this->parseDocument($doc);

    $window->add($window->argToInstance($mContent));

    return $window;
  }

}
