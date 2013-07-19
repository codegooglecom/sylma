<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\parser\languages\common, sylma\schema\parser;

class Foreign extends sql\schema\component\Foreign implements sql\template\pathable, parser\element {

  protected $query;
  protected $var;

  protected $bBuilded = false;

  const JUNCTION_MODE = 'view';

  /**
   *
   * @return sql\query\parser\Select
   */
  public function getQuery() {

    return $this->query;
  }

  public function getVar() {

    return $this->var;
  }

  protected function setVar(common\_var $var) {

    $this->var = $var;
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode, $bRead = false, array $aArguments = array()) {

    return $this->getParser()->reflectApplyDefault($this, $sPath, $aPath, $sMode, $bRead, $aArguments);
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
      case 'ref' : $result = $this->reflectFunctionRef($aPath, $sMode, $aArguments); break;
      case 'title' : $result = $this->getTitle(); break;

      default :

        $this->launchException("Invalid function name : '{$sName}'");
        //$result = $this->getParser()->parsePathFunction($this, $aMatch, $aPath, $sMode);
    }

    return $result;
  }

  protected function reflectFunctionRef(array $aPath, $sMode, array $aArguments = array()) {

    $this->launchException('Should not be called');
  }

  protected function reflectFunctionAll(array $aPath, $sMode, array $aArguments = array()) {

    if ($aPath) {

      $this->throwException('Not yet implemented');
    }

    $element = $this->getElementRef();

    $collection = $this->loadSimpleComponent('component/collection');

    $collection->setQuery($element->getQuery());
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
    else {

      $this->launchException('No template found', get_defined_vars());
      //$result = $this->reflectRead();
    }

    return $result;
  }

  protected function loadJunction() {

    $sName = $this->readx('@junction', true);

    $field = $this->getElementRef();
    $parent = $this->getParent();

    $sSource = 'id_' . $parent->getName();
    $sTarget = 'id_' . $field->getName();

    $doc = $this->createArgument(array(
      'schema' => array(
        '@targetNamespace' => $this->getNamespace(),
        'table' => array(
          '@name' => $sName,
          '#foreign' => array(
            array(
              '@name' => $sSource,
              '@occurs' => '0..1',
              '@table' => 't1:' . $parent->getName(),
              '@import' => (string) $this->getSourceFile(),
            ),
            array(
              '@name' => $sTarget,
              '@occurs' => '0..1',
              '@table' => 't2:' . $field->getName(),
              '@import' => (string) $this->getSourceFile($this->readx('@import')),
            ),
          ),
        ),
      ),
    ), $this->getNamespace('sql'))->asDOM();

    $doc->registerNamespaces(array(
      't1' => $this->getNamespace(),
      't2' => $field->getNamespace(),
    ));

    $sql = $this->getManager(self::DB_MANAGER);

    if (!$sql->get("show tables like '$sName'", false)) {

      $handler = new sql\alter\Handler;
      $handler->setDocument($doc);

      $handler->asString();
    }

    $this->getParser()->changeMode(static::JUNCTION_MODE);

    $sElement = $this->getParser()->addSchema($doc);

    $table = $this->getParser()->getElement($sElement, $this->getNamespace());
    $table->isSub(true);

    $source = $table->getElement($sSource);
    $target = $table->getElement($sTarget);

    $this->getParser()->resetMode();

    return array($table, $source, $target);
  }
}

