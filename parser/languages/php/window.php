<?php

namespace sylma\parser\languages\php;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php;

interface window extends common\_window {

  const NS = 'http://www.sylma.org/parser/languages/php';

  function __construct($controler, core\argument $args, $sClass);

  function addControler($sName);

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
  function callFunction($sName, common\_instance $return = null, array $aArguments = array());

  

  /**
   * @return _instance
   */
  function loadInstance($sName, $sFile = '');

  /**
   *
   * @param string $sFormat
   * @return _instance
   */
  function tokenToInstance($sFormat);

  /**
   * @return php\basic\Insert
   */
  //function createInsert($mVal);

  /**
   * @return php\basic\Template
   */
  //function createTemplate(dom\node $node);

  function getKey($sPrefix);
}
