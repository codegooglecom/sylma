<?php

namespace sylma\parser\action\compiler;
use sylma\parser, sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common;

class Window extends php\basic\Window {

  const ACTION_ALIAS = 'action';

  protected $sContext = '';
  protected $aObjects = array();

  public function __construct($controler, core\argument $args, $sClass) {

    parent::__construct($controler, $args, $sClass);
  }

  public function startContext($sName) {

    if ($this->getContext()) {

      $this->throwException(sprintf('Cannot start context [%s] when previous context [%s] has not been stopped', $sName, $this->getContext()));
    }

    $this->setContext($sName);

    $call = $this->createCall($this->getSelf(), 'getContext', $this->tokenToInstance('php-string'), array($sName, false));
    $if = $this->createCondition($call);

    $this->setScope($if);
    $this->addContent($if);
  }

  public function stopContext() {

    $this->stopScope();
    $this->setContext();
  }

  protected function setContext($sName = '') {

    $this->sContext = $sName;
  }

  public function getContext() {

    return $this->sContext;
  }

  protected function toInstance($mValue) {

    $mResult = null;

    if (is_array($mValue)) {

      if (count($mValue) == 1) {

        $mResult = $this->toInstance(current($mValue));
      }
      else {

        $mResult = array();

        foreach ($mValue as $sKey => $mSub) {

          $mResult[$sKey] = $this->toInstance($mSub);
        }
      }
    }
    else {

      $mResult = $this->argToInstance($mValue);
    }

    return $mResult;
  }

  public function insert($mValue) {

    return $this->add($this->createInsert($this->toInstance($mValue)));
  }

  public function addControler($sName) {

    return parent::addControler($sName, $this->getSelf());
  }

  public function createInsert($mVal, $sFormat = '', $iKey = null, $bTemplate = true, $bRoot = false) {

    if ($sFormat) {

      switch ($sFormat) {

        case 'dom' :$mVal = $this->convertToDOM($mVal, !$bRoot); break;
        case 'txt' : $mVal = $this->convertToString($mVal); break;
      }
    }

    if (!$sContext = $this->getContext()) $sContext = self::CONTEXT_DEFAULT;

    $result = $this->create('insert', array($this, $mVal, $sContext, $iKey, $bTemplate));

    return $result;
  }

  public function createTemplate(dom\node $node) {

    return $this->create('template', array($this, $node));
  }

  protected function argToString($mValue) {

    if ($mValue instanceof core\stringable) {

      $result = $this->create('string', array($this, $mValue));
    }
    else {

      $result = $this->create('concat', array($this, $mValue));
    }

    return $result;
  }

  public function convertToString($val, $iMode = 0) {

    $result = null;

    if ($val instanceof common\_scalar) {

      if ($val instanceof common\_var) {

        $instance = $val->getInstance();
      }
      else {

        $instance = $val;
      }

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

      $result = $this->createCall($this->addControler(static::ACTION_ALIAS), 'loadStringable', 'php-string', array($val, $iMode));
    }/*
    else if ($val instanceof core\stringable) {

      $result = $this->createString($val->asString());
    }*/
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

      $result = $this->createCall($this->addControler(static::ACTION_ALIAS), 'loadArgumentable', '\sylma\dom\node', array($val));
    }
    else if ($interface->isInstance('\sylma\dom\domable')) {

      $result = $this->createCall($this->addControler(static::ACTION_ALIAS), 'loadDomable', '\sylma\dom\node', array($val));
    }
    else {

      $this->throwException(sprintf('Cannot add @class %s', $interface->getName()));
    }

    return $result;
  }

  protected function addContentUnknown($mVal) {

    $this->checkContent($mVal);

    return parent::addContentUnknown($mVal);
  }

  protected function objectUnknownToInstance($obj) {

    $result = null;

    if ($obj instanceof core\stringable) {

      $result = $this->createString($obj);
    }
    else {

      parent::objectUnknownToInstance($obj);
    }

    return $result;
  }

  public function checkContent($mVal) {

    if ((!is_string($mVal) && !$mVal instanceof common\argumentable && !$mVal instanceof dom\node)) {

      $this->throwException(sprintf('Cannot add %s in content', $this->show($mVal, true)));
    }

    return $mVal;
  }

  public function getObject() {

    if (!$this->aObjects) {

      $this->throwException(t('Cannot get object, no object defined'));
    }

    return $this->aObjects[count($this->aObjects) - 1];
  }

  public function setObject(common\_object $obj) {

    $this->aObjects[] = $obj;
  }

  public function stopObject() {

    if (!$this->aObjects) {

      $this->throwException(t('Cannot stop object scope, no object defined'));
    }

    return array_pop($this->aObjects);
  }

  public function asArgument() {

    $result = parent::asArgument();

    $interface = $this->getControler()->getInterface();
    $result->set('window/@extends', $interface->getName());

    return $result;
  }
}