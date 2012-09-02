<?php

namespace sylma\parser;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

require_once('core/module/Filed.php');

abstract class Reflector extends core\module\Filed {

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
   * Sub parsers
   * @var array
   */
  private $aParsers = array();

  public function setWindow(common\_window $window) {

    $this->window = $window;
  }

  /**
   *
   * @return common\_window
   */
  public function getWindow() {

    if (!$this->window) {

      $this->throwException(t('No window defined'));
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

  /**
   *
   * @param string $sUri
   * @return parser\domed
   */
  protected function getParser($sUri) {

    $parser = null;

    if (array_key_exists($sUri, $this->aParsers)) {

      $parser = $this->aParsers[$sUri];
      $parser->setParent($this);
    }

    return $parser;
  }

  protected function setParser(parser\compiler\domed $parser, array $aNS) {

    $aResult = array();

    foreach ($aNS as $sNamespace) {

      $aResult[$sNamespace] = $parser;
    }

    $this->aParsers = array_merge($this->aParsers, $aResult);
  }



}
