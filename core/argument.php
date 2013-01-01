<?php

namespace sylma\core;
use sylma\core;

interface argument extends \Iterator {

  const NORMALIZE_EMPTY_ARRAY = 1;
  const NORMALIZE_ARGUMENT = 2;

  const NORMALIZE_DEFAULT = 3;

  /**
   * Load a pathed value and return it as argument object. It's opposite to @method read()
   *
   * @param string $sPath The path to look for value
   * @param boolean $bDebug If TRUE, a result is expected and an exception is thrown if NULL
   *
   * @return core\argument|null The value located in the path as an object or NULL if none
   */
  function get($sPath = '', $bDebug = true);
  function read($sPath = '', $bDebug = true);
  //function query($sPath = '', $bDebug = true);
  function set($sPath = '', $mValue = null);
  function add($mValue);
  //function shift($mValue);
  function setParent(core\argument $parent);
  function getParent();

  /**
   * Replace object contained in argument with arrays
   */
  function normalize($iMode = self::NORMALIZE_DEFAULT);

  /**
   * Main search method, it will go through the tree to localize value
   * Used as public function for exploring argument inside argument
   *
   * @param array $aPath The array of keys to look for
   * @param boolean $bDebug If set to FALSE, no exception will be thrown if path is incorrect
   * @param boolean $bReturn If set to TRUE, return the result even though path is incorrect
   *
   * @return null|mixed The value localized by path, or NULL
   */
  function &locateValue(array &$aPath = array(), $bDebug = true, $bReturn = false);

  /**
   * Recursively merge argument object or array, argument received will overwrite this one
   * @param array|core\argument $mArgument The argument that will merge onto this one
   */
  function merge($mArgument);

  /**
   * Normalize then return full array
   * @param type $bEmpty
   */
  function asArray($bEmpty = false);
}

