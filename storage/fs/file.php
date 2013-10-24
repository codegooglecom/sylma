<?php

namespace sylma\storage\fs;
use \sylma\dom, \sylma\storage\fs, \sylma\core;

require_once('resource.php');

interface file extends fs\resource {

  function __construct($sName, fs\directory $parent, array $aRights, $iDebug);

  /**
   * @return dom\document|null An XML document loaded with the content of this file
   *
   */
  function asDocument();

  /**
   * @deprecated use asDocument() instead
   */
  function getDocument();

  /**
   * @return string The content of the text file
   */
  function read();

  /**
   * @return array The content of the file as an array of lines, usefull for YAML loads
   */
  function readArray();

  /**
   * Get file name without extension
   * ex : /sylma/index.eml will output index
   */
  function getSimpleName();

  function getExtension();

  function asPath();
}
