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

  function getFiles(array $aExtensions = array(), $sPreg = null, $iDepth = 1);

  function getDirectories();

  /**
   *
   * @param core\argument $arg argument containing parameters :
   *        mode => 'file' // path, argument or file
   *        depth => null // null : no restriction, 0 : only current directory, 1..n : nb. of levels to retrieve
   *        extensions => array() // if empty, all extensions. If extensions, get only that ones
   *        excluded => array() // if empty, no exclusion. If excluded dir, compare with name or path if it begins with /
   * @param type $bRoot if TRUE, root element 'browse' will be added
   * @return core\argument
   */
  function browse(core\argument $arg = null);
}