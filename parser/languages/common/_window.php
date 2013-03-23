<?php

namespace sylma\parser\languages\common;
use sylma\core, sylma\parser\languages\common;

interface _window extends scope {

  const CONTEXT_DEFAULT = 'default';

  function flattenArray(array $aContent);

  /**
   * Add content to current scope
   * @param mixed $mVal
   */
  function add($mVal);

  /**
   * @return basic\ObjectVar
   */
  //function getSelf();

  function checkContent($mVar);

  function getScope();
  function setScope(common\scope $scope);
  function stopScope();

  function createVariable($sName, $mReturn);
  function setVariable(common\_var $var);
  function addVar(common\argumentable $val, $sName = '');
  function createString($mContent);
  function createAssign($to, $value, $sPrefix = '');
  function createInstruction(common\argumentable $content);
  function createInstanciate(common\_instance $instance, array $aArguments = array());

  /**
   * @return _instance
   */
  function argToInstance($mVar);
}
