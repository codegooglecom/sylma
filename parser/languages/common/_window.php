<?php

namespace sylma\parser\languages\common;
use sylma\core, sylma\parser, sylma\parser\domed, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

require_once('scope.php');
require_once('core/argumentable.php');

interface _window extends scope, core\argumentable {

  const CONTEXT_DEFAULT = 'default';

  function add($mVal);

  /**
   * @return basic\ObjectVar
   */
  //function getSelf();

  function callFunction($sName, common\_instance $return = null, array $aArguments = array());

  /**
   * @return common\_var
   */
  function addVar(common\linable $val);

  function setScope(common\scope $scope);

  function stopScope();

  /**
   *
   * @param string $sFormat
   * @return _instance
   */
  function stringToInstance($sFormat);

  /**
   * @return _instance
   */
  function argToInstance($mVar);
}
