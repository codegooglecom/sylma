<?php

namespace sylma\storage\fs;
use \sylma\dom, \sylma\storage\fs, \sylma\core;

require_once('resource.php');
require_once('core/tokenable.php');
require_once('core/argumentable.php');

interface file extends resource, core\argumentable, core\tokenable {
  
  function __construct($sName, fs\directory $parent, array $aRights, $iDebug);
  
  /**
   * @return dom\document|null An XML document loaded with the content of this file
   * 
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
}
