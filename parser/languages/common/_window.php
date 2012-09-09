<?php

namespace sylma\parser\languages\common;
use sylma\core, sylma\parser, sylma\parser\domed, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

require_once('scope.php');
require_once('core/argumentable.php');

interface _window extends scope, core\argumentable {

  const CONTEXT_DEFAULT = 'default';
  const NS = 'http://www.sylma.org/parser/languages/php';

  function __construct($controler, core\argument $args, $sClass);

  function add($mVal);

  function addControler($sName);

  /**
   * @return basic\ObjectVar
   */
  //function getSelf();

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
  function createCall(common\_object $obj, $sMethod, $mReturn, array $aArguments = array());

  function createString($mContent);

  /**
   * @return _instance
   */
  function loadInstance($sName, $sFile = '');

  /**
   * @return php\basic\Insert
   */
  //function createInsert($mVal);

  /**
   * @return php\basic\Template
   */
  //function createTemplate(dom\node $node);

  function createFunction($sName, common\_instance $return = null, array $aArguments = array());

  /**
   * @return common\_var
   */
  function addVar(common\linable $val);

  function setScope(common\scope $scope);

  function stopScope();

  /**
   * @return _instance
   */
  function argToInstance($mVar);
  function stringToInstance($sFormat);

  function getKey($sPrefix);
}
