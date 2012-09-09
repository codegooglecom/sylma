<?php

namespace sylma\parser\action;
use sylma\parser, sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common;

\Sylma::load('/parser/languages/php/basic/Window.php');

class Window extends php\basic\Window {

  protected $sContext = self::CONTEXT_DEFAULT;

  public function __construct($controler, core\argument $args, $sClass) {

    parent::__construct($controler, $args, $sClass);
  }

  public function getSelf() {

    return $this->self;
  }

  public function setContext($sContext) {

    $this->sContext = $sContext;
  }

  public function getContext() {

    return $this->sContext;
  }

  public function createInsert($mVal, $sFormat = '', $iKey = null, $bTemplate = true, $bRoot = false) {

    if ($sFormat) {

      switch ($sFormat) {

        case 'dom' :$mVal = $this->convertToDOM($mVal, !$bRoot); break;
        case 'txt' : $mVal = $this->convertToString($mVal); break;
      }
    }

    $result = $this->create('insert', array($this, $mVal, $iKey, $bTemplate));

    return $result;
  }

  public function createTemplate(dom\node $node) {

    return $this->create('template', array($this, $node));
  }

  public function convertToString($val, $iMode = 0) {

    $result = null;

    if ($val instanceof common\_scalar) {

      if ($val instanceof common\_var) $instance = $val->getInstance();
      else $instance = $val;

      if ($instance instanceof common\_scalar) {

        $controler = $this->getControler();
        $bString = $controler->useTemplate() || $controler->useString();

        if ($bString && $instance instanceof php\basic\instance\_Array) {

          $aContent = array();
          foreach($instance as $sub) {

            $aContent[] = $this->convertToString($sub);
          }

          $val = $this->createString($aContent);
        }

        $result = $val;
      }
      else {

        $this->throwException(sprintf('Cannot convert scalar value %s to string', get_class($instance)));
      }
    }
    else if ($val instanceof php\basic\Called) {

      $val = $val->getVar();
      $result = $this->convertToString($val);
    }
    else if ($val instanceof common\_object) {

      $interface = $val->getInstance()->getInterface();

      if (!$interface->isInstance('\sylma\core\stringable')) {

        $this->throwException(sprintf('Cannot convert object %s to string', $interface->getName()));
      }

      $result = $this->createCall($this->getSelf(), 'loadStringable', 'php-string', array($val, $iMode));
    }
    else if ($val instanceof dom\node) {

      $result = $this->argToInstance($val);
    }
    else if ($val instanceof php\basic\Template) {

      $result = $val;
    }
    else {

      $formater = $this->getControler('formater');
      $this->throwException(sprintf('Cannot convert %s to string', $formater->asToken($val)));
    }

    return $result;
  }

  public function convertToDOM($val, $bTemplate = false) {

    $result = null;

    if (is_array($val)) {

      // concat

      foreach ($val as $mSub) {

        $aResult[] = $this->convertToDOM($mSub, $bTemplate);
      }

      $result = $this->createString($aResult);
    }
    else if (is_object($val)) {

      $result = $this->convertObjectToDOM($val, $bTemplate);
    }
    else {

      $this->throwException(sprintf('Cannot insert %s', $this->show($val, true)));
    }

    return $result;
  }

  protected function convertObjectToDOM($val, $bTemplate = false) {

    $result = null;

    if ($val instanceof php\basic\CallMethod) {

      $result = $this->convertToDOM($val->getVar(), $bTemplate);
    }
    else if ($val instanceof common\_object) {

      $result = $this->convert_ObjectToDOM($val, $bTemplate);
    }
    else if ($val instanceof common\_scalar) {

      $result = $this->convertToString($val);
    }
    else if ($val instanceof dom\node) {

      $result = $bTemplate ? $this->convertToString($val) : $val;
    }
    else {

      $this->throwException(sprintf('Cannot insert %s', $this->show($val, true)));
    }

    return $result;
  }

  protected function convert_ObjectToDOM($val, $bTemplate = false) {

    $result = null;

    if ($val instanceof common\_instance) {

      $this->throwException('Cannot insert object instance');
    }

    $interface = $val->getInstance()->getInterface();

    if ($interface->isInstance('\sylma\dom\node')) {

      $result = $bTemplate ? $this->convertToString($val) : $val;
    }
    else if ($interface->isInstance('\sylma\core\argumentable')) {

      $result = $this->createCall($this->getSelf(), 'loadArgumentable', '\sylma\dom\node', array($val));
    }
    else if ($interface->isInstance('\sylma\dom\domable')) {

      $result = $this->createCall($this->getSelf(), 'loadDomable', '\sylma\dom\node', array($val));
    }
    else {

      $this->throwException(sprintf('Cannot add @class %s', $interface->getName()));
    }

    return $result;
  }

  public function checkContent($mVal) {

    if ((!is_string($mVal) && !$mVal instanceof core\argumentable && !$mVal instanceof dom\node)) {

      $this->throwException(sprintf('Cannot add %s in content', $this->show($mVal, true)));
    }
  }

  public function asArgument() {

    $result = parent::asArgument();

    $interface = $this->getControler()->getInterface();
    $result->set('window/@extends', $interface->getName());

    return $result;
  }
}