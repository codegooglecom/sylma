<?php

namespace sylma\parser\action\php;
use \sylma\core, \sylma\parser\action\php;

require_once('scope.php');
require_once('core/argumentable.php');

interface _window extends scope, core\argumentable {
  
  function __construct(core\factory $controler);
  
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
  
  /**
   * @return _instance
   */
  function loadInstance($sName, $sFile = '');
  
  /**
   * @return php\Insert
   */
  function createInsert(core\argumentable $val);
  
  function setScope(php\scope $scope);
  
  /**
   * @return _instance
   */
  function argToInstance($mVar);
}
