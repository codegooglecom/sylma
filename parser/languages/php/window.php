<?php

namespace sylma\parser\languages\php;
use sylma\core, sylma\parser, sylma\parser\domed, sylma\parser\languages\common, sylma\parser\languages\php, sylma\dom;

\Sylma::load('/parser/languages/common/_window.php');

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
  function createCall($obj, $sMethod, $mReturn, array $aArguments = array());

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

  function getKey($sPrefix);
}
