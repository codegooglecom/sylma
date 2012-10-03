<?php

namespace sylma\parser\languages\js\basic\instance;
use sylma\parser\languages\js, sylma\parser\languages\common;

class _Object extends js\basic\Base implements common\_instance, common\_object {

  protected $aProperties = array();

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
  
  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'object' => array(
        '@class' => $this->getInterface(),
        '#item' => $this->loadProperties()
      ),
    ));
  }
}
