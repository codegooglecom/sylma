<?php

namespace sylma\storage\fs;
use sylma\core, sylma\storage\fs;

interface directory extends fs\resource {

  function __construct($sName, fs\directory $parent = null, array $aRights = array(), fs\controler $controler = null);
  function getDistantFile(array $aPath, $bDebug = false);

  /**
   *
   * @param string $sName
   * @param integer $iDebug
   * @return \sylma\storage\fs\directory
   */
  function getDirectory($sName, $iDebug = self::DEBUG_LOG);

  /**
   *
   * @param string $sName
   * @param integer $iDebug
   * @return \sylma\storage\fs\file
   */
  function getFile($sName, $iDebug = self::DEBUG_LOG);

  /**
   * @param $arg
   * @return \sylma\core\argument
   */
  function browse(core\argument $arg = null);
}