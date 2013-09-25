<?php

namespace sylma\dom\basic\handler;
use sylma\dom, sylma\core;

/**
 * Extends main dom\element root's methods
 */
abstract class Documented extends Container {

  static protected $iNS = 0;
  protected $aPrefixes = array();

  public function set() {

    $mResult = null;

    if (!func_num_args()) {

      $this->getRoot()->remove();
    }
    else if (func_num_args() == 1) {

      $mValue = func_get_arg(0);

      if (is_object($mValue)) {

        $mResult = $this->setObject($mValue);
      }
      else if (is_array($mValue) && $mValue) {

        $mResult = $this->setArray($mValue);
      }
      else if (is_string($mValue) && $mValue) {

        $mResult = $this->startString($mValue);
      }
    }
    else if (func_num_args() > 1) {

      $this->set(func_get_args());
    }

    return $mResult;
  }

  protected function setObjectDOM(dom\node $val) {

    if ($val instanceof dom\element || $val instanceof dom\fragment) {

      $result = $this->setRoot($val);
    }
    else if ($val instanceof dom\document) {

      if ($val->isEmpty()) {

        $this->throwException('Empty document cannot be setted to another document');
      }

      $result = $this->setRoot($val->getRoot());
    }
    else {

      $this->setRoot($val);
    }

    return $result;
  }

  protected function setObject($val) {

    $result = null;

    if ($val instanceof dom\node) {

      $result = $this->setObjectDOM($val);
    }
    else if ($val instanceof dom\collection) {

      if ($val->length > 1) {

        $this->throwException('Cannot add collection with multiple elements as root');
      }
      else if ($val->length) {

        $this->set($val->current());
      }
    }
    else if ($val instanceof core\argumentable) {

      $result = $this->setArgument($val->asArgument());
    }
    else if ($val instanceof dom\domable) {

      $result = $this->setRoot($this->setObjectDOM($val->asDOM()));
    }
    else if ($val instanceof \DOMDocument) {

      $el = $val->documentElement;
      $result = $this->setObjectNode($el);
    }
    else if ($val instanceof \DOMNode) {

      $result = $this->setObjectNode($val);
    }
    else {

      $this->launchException('$val cannot be used in dom document', get_defined_vars());
    }

    return $result;
  }

  protected function setObjectNode(\DOMNode $node) {

    $container = $this->getContainer();

    $el = $container->importNode($node);
    $this->setRoot($el);
  }

  protected function setArgument(core\argument $arg) {

    $doc = $arg->asDOM();

    return $this->setObject($doc);
  }

  protected function setArray(array $aVal) {

    $mResult = array();

    if (count($aVal) > 1) {

      // > 1

      $aChildren = array();

      $this->set(array_shift($aVal));
      foreach ($aVal as $oChild) $aChildren = $this->add($oChild);

      $mResult = $aChildren;

    }
    else {

      // = 1

      $mResult = $this->set(array_pop($aVal));
    }

    return $mResult;
  }

  public function isEmpty() {

    return !$this->getContent() && !$this->getRoot(false, false);
  }

  public function addElement($sName, $mContent = '', array $aAttributes = array(), $sNamespace = null) {

    $result = null;
    $el = $this->createElement($sName, $mContent, $aAttributes, $sNamespace);

    if (!$this->getRoot(false)) {

      $result = $this->setRoot($el);
    }
    else {

      $result = $this->getRoot()->insertChild($el);
    }

    return $result;
  }

  protected function generateName($sName, $sNamespace) {

    if (!array_key_exists($sNamespace, $this->aPrefixes)) {

      $sPrefix = self::$iNS++;
      $this->aPrefixes[$sNamespace] = $sPrefix;
    }
    else {

      $sPrefix = $this->aPrefixes[$sNamespace];
    }

    return 'ns' . $sPrefix . ':' . $sName;
  }

  public function setRoot(dom\element $el) {

    $container = $this->getContainer();

    return $container->setRoot($el);
  }

  /**
   * Load text content when exists then return root element
   * @param bool $bDebug If set to FALSE, no exception will be thrown if document is empty
   * @return dom\element
   */
  public function getRoot($bDebug = true, $bLoad = true) {

    if ($bLoad) $this->loadContent();

    $result = $this->getContainer()->getRoot();

    if ($bDebug && !$result) {

      $this->throwException('Cannot get root, document is empty.');
    }

    return $result;
  }

  public function __toString() {

    $sResult = '';

    if (!$this->isEmpty()) {

      $sResult = $this->asString();
    }

    return $sResult;
  }

}