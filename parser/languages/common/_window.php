<?php

namespace sylma\parser\languages\common;
use sylma\core, sylma\parser\languages\common;

require_once('scope.php');
require_once('core/argumentable.php');

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

  /**
   * @return common\_var
   */
  function addVar(common\argumentable $val);

  function checkContent($mVar);

  function getScope();
  function setScope(common\scope $scope);
  function stopScope();

  function createVariable($sName);
  function createString($mContent);
  function createAssign($to, $value);
  function createInstruction(common\argumentable $content);

  /**
   * @return _instance
   */
  function argToInstance($mVar);
}
