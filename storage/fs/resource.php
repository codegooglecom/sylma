<?php

namespace sylma\storage\fs;
use \sylma\core;

interface resource extends core\argumentable, core\tokenable, core\stringable {

  const DEBUG_NOT = 0;
  /**
   * Throw exception if a file doesn't exist
   */
  const DEBUG_LOG = 1;

  /**
   * Return file either if it doesn't exist
   */
  const DEBUG_EXIST = 2;

  /**
   * @return core\factory
   */
  function getControler();

  /**
   * @param boolean $bRecursive Try to get manager from parents
   * @return fs\security\manager|null The security manager
   */
  function getSettings($bRecursive = false);

  /**
   * Indicate if the resource exist or not, allow the manipulation of un-existent resource
   * @return boolean
   */
  function doExist();

  /**
   * Check if the file is accessible against the given access mode
   *
   * @param integer $iMode One of the three access modes : @constant \Sylma::EXECUTE, \Sylma::READ, \Sylma::WRITE ;
   * @return boolean TRUE if access is granted
   */
  function checkRights($iMode);

  /**
   * @return fs\directory|null The parent directory or NULL if root directory
   */
  function getParent();

  /**
   * @return string The name of the resource without parent's path (ex: index.eml)
   */
  function getName();

  /**
   *
   * @return string The full path to resource, including root directory path (ex: protected/index.eml)
   */
  function getRealPath();

  /**
   * @return string The full path to the resource (ex : /storage/fs/resource.php)
   */
  function __toString();
}