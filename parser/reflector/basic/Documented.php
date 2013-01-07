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

  /**
   *
   * @param $doc
   * @return array
   */
  protected function parseDocument(dom\document $doc) {

    return $this->parseChildren($doc->getChildren());
  }

/*
  protected function buildContainer(php\window $window) {

    $switch = $window->createSwitch();
    $switch->setCase(self::MODE_DEFAULT);

    $window->add($switch);
    $window->setScope($switch);
  }
*/

  /**
   *
   * @return array
   */
  protected function build() {

    $doc = $this->getDocument();

    if ($doc->isEmpty()) {

      $this->throwException('Empty document');
    }

    $doc->registerNamespaces($this->getNS());

    $window = $this->getWindow();
    //$this->buildContainer($window);

    $mContent = $this->parseDocument($doc);
    $this->buildInstanciation($window, array($mContent));
  }

  protected function buildInstanciation(common\_window $window, array $aArguments) {

    $new = $window->createInstanciate($window->getSelf()->getInstance(), $aArguments);
    //$window->add($new);
    $window->setReturn($new);
  }
}
