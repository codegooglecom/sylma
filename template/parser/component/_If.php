<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common, sylma\template as template_ns;

class _If extends Unknowned implements common\arrayable, template_ns\parser\component {

  protected $reflector;
  protected $else;

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

      $this->else = $result;
      $result = null;
    }

    return $result;
  }

  /**
   * @return _Else
   */
  protected function getElse() {

    return $this->else;
  }

  public function asArray() {

    $this->setReflector($this->getWindow()->createCondition());

    $test = $this->getTemplate()->getPather()->parseExpression($this->readx('@test', true), true);
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

      if ($bResult) {

        $result = $aChildren;
      }
      else if ($else = $this->getElse()) {

        $result = $this->getElse()->parseContent();
      }
      else {

        $result = null;
      }
    }
    else {

      $window = $this->getWindow();
      $result = $this->getReflector();

      $result->setTest($test);
      $result->setContent($window->parse($aChildren));

      if ($else = $this->getElse()) {

        $result->setElse($window->parse($else->parseContent()));
      }
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

