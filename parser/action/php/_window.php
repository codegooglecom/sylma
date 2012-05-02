<?php

namespace sylma\parser\action\php;
use sylma\core, sylma\parser\action, sylma\parser\domed, sylma\parser\action\php, sylma\dom;

require_once('scope.php');
require_once('core/argumentable.php');

interface _window extends scope, core\argumentable {

  const CONTEXT_DEFAULT = 'default';
  
  function __construct(action\compiler $controler, core\argument $args, $sClass);

  function add($mVal);

  function addControler($sName);

  /**
   * @return basic\ObjectVar
   */
  function getSelf();

  /**
   * @return ?
   */
  function getSylma();

  /**
   * @return string
   */
  function getVarName();

  /**
   * @return basic\CallMethod
   */
  function createCall(php\_object $obj, $sMethod, $mReturn, array $aArguments = array());

  function createString($mContent);

  /**
   * @return _instance
   */
  function loadInstance($sName, $sFile = '');

  /**
   * @return php\basic\Insert
   */
  function createInsert($mVal);

  /**
   * @return php\basic\Template
   */
  function createTemplate(dom\node $node);

  /**
   * @return php\_var
   */
  function addVar(php\linable $val);

  function setScope(php\scope $scope);

  /**
   * @return _instance
   */
  function argToInstance($mVar);
  function stringToInstance($sFormat);

  function getKey($sPrefix);
}
