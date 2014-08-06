<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common, sylma\template as template_ns;

class _If extends Unknowned implements common\arrayable, template_ns\parser\component {

  protected $reflector;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, true);
    //$this->allowForeign(true);
    //$this->allowUnknown(true);
    $this->allowText(true);
  }

  protected function setReflector(php\basic\Condition $if) {

    $this->reflector = $if;
  }

  protected function getReflector() {

    return $this->reflector;
  }

  protected function parseComponent(dom\element $el) {

    $result = parent::parseComponent($el);

    if ($result instanceof _Else) {

      $this->getReflector()->setElse($result->parseContent());
      $result = null;
    }

    return $result;
  }

  public function asArray() {

    $this->setReflector($this->getWindow()->createCondition());

    $test = $this->getTemplate()->getPather()->parseExpression($this->readx('@test'), true);
    $aChildren = $this->parseChildren($this->getNode()->getChildren());

    if ($this->getWindow()->isStatic($test)) {

      if ($sTest = implode('', $this->prepareEval($test))) {

        try {

          eval("\$bResult = $sTest;");

        } catch (core\exception $e) {

          $bResult = false;
        }
      }
      else {

        $bResult = false;
      }

      $result = $bResult ? $aChildren : $this->getReflector()->getElse();
    }
    else {

      $result = $this->getReflector();

      $result->setTest($test);
      $result->setContent($this->getWindow()->parseArrayables($aChildren));
    }

    return array($result);
  }

  protected function prepareEval(array $aContent) {

    foreach ($aContent as &$mVal) {

      if (is_string($mVal)) {

        $mVal = "'" . addslashes($mVal) . "'";
      }
      else if (is_bool($mVal)) {

        $mVal = $mVal ? 'true' : 'false';
      }
    }

    return $aContent;
  }
}

