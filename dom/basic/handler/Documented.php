<?php

namespace sylma\dom\basic\handler;
use \sylma\dom, \sylma\core;

require_once('Container.php');
require_once(dirname(dirname(__dir__)) . '/handler.php');

/**
 * Extends main dom\element root's methods
 */
abstract class Documented extends Container {

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
      else if (is_string($mValue)) {

        $mResult = $this->startString($mValue);
      }
    }
    else if (func_num_args() > 1) {

      $this->set(func_get_args());
    }

    return $mResult;
  }

  protected function setObject($val) {

    $result = null;

    if ($val instanceof dom\element || $val instanceof dom\fragment) {

      $result = $this->setRoot($val);
    }
    else if ($val instanceof dom\document) {

      if ($val->isEmpty()) {

        $this->throwException(t('Empty document cannot be setted to another document'));
      }

      $result = $this->setRoot($val->getRoot());
    }
    else if ($val instanceof dom\collection) {

      $val->rewind();
      $this->set($val->current());
      $val->next();

      while ($val->valid()) {

        $this->add($val->current());
        $val->next();
      }
    }
    else if ($val instanceof core\argumentable) {

      $result = $this->setArgument($val->asArgument());
    }
    else if ($val instanceof dom\domable) {

      $result = $this->setRoot($val->asDOM());
    }
    else if ($val instanceof \DOMDocument) {

      $el = $val->documentElement;
      $result = $this->setDOMNode($el);
    }
    else if ($val instanceof \DOMNode) {

      $result = $this->setDOMNode($val);
    }
    else {

      $formater = \Sylma::getControler('formater');
      $this->throwException(sprintf('Object %s cannot be used in dom document', $formater->asToken($val)));
    }

    return $result;
  }

  protected function setDOMNode(\DOMNode $node) {

    $container = $this->getContainer();

    $el = $container->importNode($node);
    $this->setRoot($el);
  }

  protected function setArgument(core\argument $arg) {

    $doc = $arg->getDocument();

    return $this->setObject($doc);
  }

  protected function setArray($aVal) {

    $mResult = array();

    if (count($aVal) > 1) {

      // > 1

      $aChildren = array();

      $this->set(array_shift($aVal));
      foreach ($aVal as $oChild) $aChildren = $this->add($oChild);

      $mResult = $aChildren;

    } else {

      // = 1

      $mResult = $this->set(array_pop($mValue));
    }

    return $mResult;
  }

  public function isEmpty() {

    $this->loadContent();
    return $this->getDocument()->isEmpty();
  }

  public function addElement($sName, $mContent = '', array $aAttributes = null, $sNamespace = null) {

    $result = null;
    $el = $this->createElement($sName, $mContent, $aAttributes, $sNamespace);

    if (!$this->getRoot()) {

      $result = $this->setRoot($el);
    }
    else {

      $result = $this->getRoot()->insertChild($el);
    }

    return $result;
  }

  public function createElement($sName, $mContent = '', array $aAttributes = array(), $sNamespace = null) {

    $doc = $this->getDocument();

    if (!$sName) $this->throwException(t('Empty value cannot be used as element\'s name'));

    if ($sNamespace) {

      $el = $doc->createElementNS($sNamespace, $sName);
    }
    else {

      $el = $doc->createElement($sName);
    }

    if ($mContent) {

      $el->set($mContent);
    }

    if ($aAttributes) {

      $el->setAttributes($aAttributes);
    }

    return $el;
  }

  public function setRoot(dom\element $el) {

    $container = $this->getContainer();

    return $container->setRoot($el);
  }

  public function getRoot() {

    $container = $this->getContainer();
    $this->loadContent();

    return $this->getContainer()->getRoot();
  }
}