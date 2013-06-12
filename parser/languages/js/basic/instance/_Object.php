<?php

namespace sylma\parser\languages\js\basic\instance;
use sylma\core, sylma\parser\languages\js, sylma\parser\languages\common;

class _Object extends js\basic\Base implements common\_instance, common\_object, common\argumentable {

  protected $aProperties = array();

  protected $windowPHP;
  protected $context;

  public function __construct(common\_window $window, array $aProperties = array()) {

    parent::__construct($window, 'Object');

    $this->setProperties($aProperties);
  }

  protected function loadProperties() {

    $window = $this->getControler();
    $aResult = array();

    foreach ($this->getProperties() as $mKey => $mVal) {

      $aResult[] = array(
        '@key' => $mKey,
        $window->argToInstance($mVal),
      );
    }

    return $aResult;
  }

  public function setProperty($sPath, $value) {

    $aPath = explode('.', $sPath);

    if (count($aPath) > 1) {

      $sName = array_shift($aPath);

      if ($property = $this->getProperty($sName, false)) {

        if (!$property instanceof self) {

          $this->throwException(sprintf('Cannot add property to %s', $this->getControler()->show($property)));
        }
      }
      else {

        $property = $this->setProperty($sName, new self($this->getControler()));
      }

      $result = $property->setProperty(implode('.', $aPath), $value);
    }
    else {

      $result = parent::setProperty($sPath, $value);
    }

    return $result;
  }

  public function setContext(common\_var $context) {

    $this->context = $context;
  }

  protected function getContext() {

    return $this->context;
  }

  public function setPHPWindow(common\_window $window) {

    $this->windowPHP = $window;

  }

  protected function addToWindow(core\argument $arg) {

    $window = $this->windowPHP;

    $contents = $this->getWindow()->argumentAsDOM($arg);
/*
    if ($this->readArgument('debug/show')) {

      //dsp($this->getFile()->asToken());
      dsp($contents);
    }
*/
    $aResult = array();

    foreach($contents->getChildren() as $child) {

      if ($child->getType() == $child::TEXT) $aResult[] = $child->getValue();
      else $aResult[] = $child;
    }

    if ($aResult) {

      $result = $this->getContext()->call('add', array($window->createString($aResult)), '\sylma\parser\context', false);
    }
    else {

      $result = null;
    }

    return $result;
  }

  public function asArgument() {

    $arg = $this->getControler()->createArgument(array(
      'object' => array(
        '@class' => $this->getInterface(),
        'items' => array(
          '#item' => $this->loadProperties(),
        )
      ),
    ));

    if ($this->getContext()) {

      $result = $this->addToWindow($arg);
    }
    else {

      $result = $arg;
    }

    return $result;
  }
}
