<?php

/**
 * This class act as an interface to arrays of arrays/objects/strings with dom-like functions get/set/add
 * It can also be used with YAML files with the extended version @class XArguments
 *
 * @author rodolphe.gerber (at) gmail.com
 */

class Arguments extends Namespaced implements SettingsInterface, Iterator {

  const MESSAGES_STATUT = Sylma::LOG_STATUT_DEFAULT;

  /**
   * The default main array
   */
  protected $aArray = array();
  private $parent;

  public function __construct(array $aArray = array(), $sNamespace = '', SettingsInterface $parent = null) {

    if (is_array($aArray)) $this->aArray = $aArray;
    $this->setNamespace($sNamespace);
    if ($parent) $this->setParent($parent);
  }

  public function setParent(SettingsInterface $parent) {

    $this->parent = $parent;
  }

  public function getParent() {

    return $this->parent;
  }

  public function set($sPath = '', $mValue = null) {

  	$aPath = $this->parsePath($sPath);

  	$mTarget =& $this->locateValue($aPath, false, true);

  	if ($mTarget === null) $mTarget =& $this->aArray;

  	foreach ($aPath as $sKey) {

  		$mTarget[$sKey] = array();
  		$mTarget =& $mTarget[$sKey];
  	}

    if ($mValue !== null) {

      $mTarget = $mValue;
    }
    else {

      $mTarget = null;
    }

    if ($mTarget !== null) return $this->get($sPath);
    else return null;
  }

  public function query($sPath = '', $bDebug = true) {

    return (array) $this->getValue($sPath, $bDebug);
  }

  public function get($sPath = '', $bDebug = true) {

    $mResult =& $this->getValue($sPath, $bDebug);

    if (is_array($mResult)) {

      $mResult = new self($mResult, $this->getNamespace(), $this);
    }

    return $mResult;
  }

  /**
   * Calls getter's related method, it's an interface between @method get() and @method locateValue()
   *
   * @param? string $sPath The path to look for
   * @param? boolean $bDebug If set to FALSE, no exception will be thrown if path is incorrect
   *
   * @return null|mixed The value localized by path, or NULL
   */
  protected function &getValue($sPath = '', $bDebug = true) {

    $mResult = null;

    if (!$sPath) {

      $mResult =& $this->aArray;
    }
    else {

      try {

        $aPath = self::parsePath($sPath);
        $mResult =& $this->locateValue($aPath, $bDebug);
      }
      catch (SylmaExceptionInterface $e) {

        $mResult = null;
        return $mResult;
      }
    }

    return $mResult;
  }

  /**
   * Split a path in an array of keys. Allow use of '..' to get upper levels
   *
   * @param string $sPath A relative or absolute path to split
   * @param? string $sParent The parent path if @param $sPath is relative
   *
   * @return array An array of keys
   */
  protected static function parsePath($sPath, $sParent = '') {

    if ($sPath[0] == '/') $sPath = substr($sPath, 1);
    else if ($sParent) $sPath = $sParent . '/' . $sPath;

    if (strpos($sPath, '/') !== false) $aPath = explode('/', $sPath);
    else $aPath = array($sPath);

    $aResult = array();

    foreach ($aPath as $sSubPath) {

      if ($sSubPath != '..') $aResult[] = $sSubPath;
      else {

        if (!$aResult) $this->throwException(self::NS, txt('Cannot use .. when current level is root in @path /%s', $sSubPath));
        else array_pop($aResult);
      }
    }

    if ($sPath && !$aPath) $this->throwException(txt('Cannot parse path %s', $sPath));

    return $aResult;
  }

  /**
   * Main search method, it will go through the tree to localize value
   *
   * @param array $aPath The array of keys to look for
   * @param boolean $bDebug If set to FALSE, no exception will be thrown if path is incorrect
   * @param boolean $bReturn If set to TRUE, return the result even though path is incorrect
   *
   * @return null|mixed The value localized by path, or NULL
   */

  protected function &locateValue(array &$aPath = array(), $bDebug = true, $bReturn = false) {

    $mCurrent =& $this->aArray;
    $mResult = null;
    $aParentPath = array();
    $sKey = '[none]';

    while ($aPath) {

      if (!is_array($mCurrent)) {

        if ($mCurrent instanceof SettingsInterface) {

          $mCurrent->setParent($this);
          $mResult =& $mCurrent->locateValue($aPath, $bDebug, $bReturn);
          break;
        }
        else {

          if ($bReturn) {

            $mResult =& $mCurrent;
            break;
          }
          else if ($aPath && $bDebug) {

            $this->throwException(
              txt('No array in @path %s. Cannot browse with @path %s',
              implode('/', $aParentPath), implode('/', $aParentPath + $aPath)),
              count($aPath) + 3);
          }
        }
      }
      else if ($sKey = $this->extractValue($mCurrent, $aPath, $aParentPath, $bDebug)) {

        $mCurrent =& $mCurrent[$sKey];

        // run hypotheticals parse on strings
        if ($mCurrent && is_string($mCurrent)) $mCurrent = $this->parseValue($mCurrent, $aParentPath);

        // if last, save result
        if (!$aPath) $mResult =& $mCurrent;
      }
      else {

        if ($bReturn) $mResult =& $mCurrent;
        break;
      }
    }

    return $mResult;
  }

  /**
   * Get next key in paths when traversing the tree
   *
   * @param array $aArray The current array of values
   * @param array $aPath The current key's path
   * @param? array $aParentPath The parent key's
   * @param? boolean $bDebug if set to TRUE, no exception will be thrown if path is incorrect
   *
   * @return string The next valid key or empty if not found
   */
  protected function extractValue(array $aArray, array &$aPath, array &$aParentPath = array(), $bDebug = true) {

    $mResult = null;
    $sKey = array_shift($aPath);
    array_push($aParentPath, $sKey);

    if (!array_key_exists($sKey, $aArray) || $aArray[$sKey] === null) {

      array_unshift($aPath, $sKey);
      if ($bDebug) $this->throwException(txt('Unknown key %s in %s', $sKey, implode('/', $aParentPath + $aPath)), count($aPath) + 4);

      $sKey = '';
    }

    return $sKey;
  }

  /**
   * This methods does nothing as is.
   * It allows extended class to update value when loading, usefull with @class XArguments and YAML files
   *
   * @param string $sValue The value to edit
   * @param? array $aParentPath The path to the value
   *
   * @return string The same value as @param $sValue
   */
  protected function parseValue($sValue, array $aParentPath = array()) {

    return $sValue;
  }

  public function read($sPath = '', $bDebug = true) {

    $sResult = $this->get($sPath, $bDebug);

    if (is_array($sResult)) {

      $this->log("Cannot read array in $sPath");
      $sResult = '';
    }

    return $sResult;
  }

  public function mergeArray(array $aArray) {

    $this->aArray = $this->mergeArrays($this->aArray, $aArray);
  }

  public function merge(SettingsInterface $with) {

    $this->mergeArray($with->query());
  }

  private function mergeArrays(array $array1, array $array2, array $aPath = array()) {

    foreach($array2 as $key => $val) {

      if (is_integer($key)) $array1[] = $val;
      else {

        if(array_key_exists($key, $array1)) {

          if (is_string($array1[$key]) && is_array($val)) {

            $array1[$key] = $this->parseValue($array1[$key], $aPath);
          }

          if (is_array($array1[$key]) && is_array($val)) {

            $array1[$key] = $this->mergeArrays($array1[$key], $val, $aPath + array($key));
          }
          else {

            $array1[$key] = $val;
          }
        }
        else {

          $array1[$key] = $val;
        }
      }
    }

    return $array1;
  }

  public function getDocument($sNamespace = '') {

    return new XML_Document($this->getFragment());
  }

  public function getFragment($sNamespace = '') {

    if (count($this->aArray) > 1) $this->throwException(txt('Cannot build document with more than one root value with @namespace %s', $sNamespace));
    if (!$sNamespace) $sNamespace = $this->getNamespace();

    return self::buildFragment($this->aArray, $sNamespace);
  }

  /**
   * Build an @class Options's object with this argument's array
   *
   * @param DOMNode $oRoot The root node to insert the results to
   * @param? DOMDocument|null $oSchema The schema that will be used by the Options object
   * @param? string $sPath An optional sub-path to extract the arguments from
   *
   * @return ElementInterface The new builded node, containing the xml version of this array
   */
  public function getOptions(DOMNode $root, DOMDocument $schema = null, $sPath = '') {

    self::getElement($root, $sPath);

    return new XML_Options(new XML_Document($root), $schema);
  }

  public static function buildDocument(array $aArray, $sNamespace) {

    return new XML_Document(self::buildFragment($aArray, $sNamespace));
  }

  public static function buildFragment(array $aArray, $sNamespace) {

    $doc = XML_Document::createFragment($sNamespace);

    self::buildNode($doc, $aArray);

    return $doc;
  }

  public function getElement(ElementInterface $root, $sPath = '') {

    if ($sPath) $aArray = $this->get($sPath);
    else $aArray = $this->aArray;

    self::buildNode($root, $aArray);
  }

  private static function buildNode(DOMNode $parent, array $aArray) {

    foreach ($aArray as $sKey => $mValue) {

      if ($mValue !== null) {

        if (is_integer($sKey)) {

          $node = $parent;
        }
        else {

          if ($sKey[0] == '@') {

            $parent->setAttribute(substr($sKey, 1), $mValue);
            continue;
          }
          else if ($sKey[0] == '#') {

            foreach ($mValue as $mSubValue) {

              $node = $parent->addNode(substr($sKey, 1));

              if (is_array($mSubValue)) self::buildNode($node, $mSubValue);
              else if ($mSubValue instanceof SettingsInterface) self::buildNode($node, $mSubValue->query());
              else $node->add($mSubValue);
            }

            continue;
          }
          else {

            $node = $parent->addNode($sKey);
          }
        }

        if (is_array($mValue)) self::buildNode($node, $mValue);
        else if ($mValue instanceof SettingsInterface) self::buildNode($node, $mValue->query());
        else $node->add($mValue);
      }
    }
  }

  /**
   * Replace @class SettingsInterface and remove null values from array
   * @param array $aArray The array to use
   * @return array A new array with replaced values
   */
  public static function normalizeArray(array $aArray) {

    $aResult = array();

    foreach ($aArray as $sKey => $mVal) {

      if ($mVal instanceof SettingsInterface) {

        $mVal = self::normalizeArray($mVal->query());
      }

      if ($mVal !== null) $aResult[$sKey] = $mVal;
    }

    return $aResult;
  }

  public function normalize() {

    $this->aArray = self::normalizeArray($this->aArray);
  }

  public function rewind() {

    reset($this->aArray);
  }

  public function current() {

    $sKey = key($this->aArray);

    return $this->get($sKey);
  }

  public function key() {

    return key($this->aArray);
  }

  public function next() {

    next($this->aArray);
  }

  public function valid() {

    return current($this->aArray) !== false;
  }

  protected function throwException($sMessage, $iOffset = 1) {

    Sylma::throwException($sMessage, array('@namespace ' . $this->getNamespace()), $iOffset);
  }

  protected function log($sMessage, $sStatut = self::MESSAGES_STATUT) {

    Sylma::log($this->getNamespace(), $sMessage, $sStatut);
  }

  public function __toString() {

    $sResult = '';

    if (count($this->aArray) == 1) {

      list(,$val) = each($this->aArray);
      $sResult = (string) $val;
    }
    else {

      $this->log(txt('Cannot render an array as a string'));
    }

    return $sResult;
  }
}
