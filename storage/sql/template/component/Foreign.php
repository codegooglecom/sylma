<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Foreign extends sql\schema\component\Foreign implements sql\template\pathable, parser\element {

  protected $query;
  protected $var;

  protected $bBuilded = false;

  /**
   * @return sql\template\handler\Formed
   */
  protected function getHandler() {

    return parent::getHandler();
  }

  /**
   * @return sql\query\parser\Select
   */
  public function getQuery($bDebug = true) {

    if ($bDebug && !$this->query) {

      $this->launchException('No query defined');
    }

    return $this->query;
  }

  public function getVar() {

    return $this->var;
  }

  protected function setVar(common\_var $var) {

    $this->var = $var;
  }

  protected function importElementRef() {

    if ($result = parent::importElementRef()) {

      if ($collection = $this->getParent()->getCollection(false)) {

        $result->setCollection($collection);
      }
    }

    return $result;
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode, $bRead = false, array $aArguments = array()) {

    array_unshift($aPath, $sPath);

    return $this->reflectFunctionRef($aPath, $sMode, $aArguments, $bRead);
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'alias' : $result = $this->reflectFunctionAlias($sMode, $bRead, $sArguments); break;
      case 'name' : $result = $this->getName(); break;
      case 'is-optional' : $result = $this->isOptional(); break;
      case 'is-multiple' : $result = $this->getMaxOccurs(true); break;
      //case 'this' : $result = $aPath ? $this->getParser()->parsePathToken($this, $aPath, $sMode, $aArguments) : $this->reflectApply($sMode, $aArguments); break;
      case 'value' : $result = $this->reflectRead(); break;
      case 'all' : $result = $this->reflectFunctionAll($aPath, $sMode, $aArguments); break;
      case 'ref' : $result = $this->reflectFunctionRef($aPath, $sMode, $aArguments, $bRead); break;
      case 'subref' : $result = $this->reflectFunctionSubRef($aPath, $sMode, $aArguments, $bRead); break;
      case 'collection' : $result = $this->reflectFunctionCollection($aPath, $sMode, $aArguments); break;
      case 'title' : $result = $this->getTitle(); break;

      case 'parent' :

        $result = $this->getHandler()->parsePathToken($this->getParent(), $aPath, $sMode, $bRead, $aArguments);
        break;

      default :

        $result = $this->getHandler()->getCurrentTemplate()->reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
        //$result = $this->getParser()->parsePathFunction($this, $aMatch, $aPath, $sMode);
    }

    return $result;
  }

  protected function reflectFunctionSubRef(array $aPath, $sMode, array $aArguments = array(), $bRead = false) {

    $table = $this->getElementRef();
    $table->isSub(true);

    $table->isMultiple((bool) $this->getMaxOccurs(true));

    return $this->reflectFunctionRef($aPath, $sMode, $aArguments, $bRead);
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array(), $bRead = false) {

    $this->launchException('Should not be called');
  }

  protected function reflectFunctionCollection(array $aPath, $sMode, array $aArguments = array()) {

    $this->launchException('Should not be called');
  }

  protected function reflectFunctionAll(array $aPath, $sMode, array $aArguments = array()) {

    if ($aPath) {

      $this->throwException('Not yet implemented');
    }

    $element = $this->getElementRef();

    $collection = $this->loadSimpleComponent('component/collection');

    $collection->setQuery($element->getQuery(true));
    $collection->setTable($element);

    return $collection->reflectApplyAll($sMode, $aArguments);
  }

  public function reflectApply($sMode = '', array $aArguments = array()) {

    return $this->reflectApplySelf($sMode, $aArguments);
  }

  public function reflectRead() {

    return null;
  }

  protected function lookupTemplate($sMode) {

    return $this->getParser()->lookupTemplate($this, 'element', $sMode);
  }

  protected function reflectApplySelf($sMode, array $aArguments = array()) {

    if ($result = $this->lookupTemplate($sMode)) {

      $result->setTree($this);
      $result->sendArguments($aArguments);
    }
    else if (!$sMode) {

      $this->launchException('No template found', get_defined_vars());
      //$result = $this->reflectRead();
    }

    return $result;
  }

  public function reflectRegister() {

    return null;
  }
}

