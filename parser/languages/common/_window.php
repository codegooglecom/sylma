<?php

namespace sylma\parser\languages\common;
use sylma\core, sylma\parser\languages\common;

interface _window extends scope {

  const CONTEXT_DEFAULT = 'default';

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
  function createString($mContent);
  function createAssign($to, $value);
  function createInstruction(common\argumentable $content);
  function createInstanciate(common\_instance $instance, array $aArguments = array());

  /**
   * @return _instance
   */
  function argToInstance($mVar);
}
