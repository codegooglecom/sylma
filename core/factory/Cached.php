<?php

namespace sylma\core\factory;
use sylma\core;

class Cached extends core\module\Argumented implements core\factory {

  /**
   * Classes to use within @method create() loaded in @settings /classes
   */
  public $aClasses = array();
  protected static $aObjects = array();

  public static function loadStats() {

    $arg = new core\argument\advanced\Treed(self::$aObjects);
    $aTree = $arg->parseTree();
    $aResult = $arg->renderTree($aTree);

    return $aResult[1];
  }

  protected function addStat($sClass) {

    if (!array_key_exists($sClass, self::$aObjects)) self::$aObjects[$sClass] = 0;
    self::$aObjects[$sClass]++;
  }

  public function __construct(core\argument $classes = null) {

    if ($classes) $this->setArguments($classes);
  }

  /**
   * Build an object defined in @settings classes
   *
   * @param string $sName The short name of the class
   * @param array $aArguments The arguments sent to the object on contstruction
   *
   * @return mixed The object builded
   */
  public function create($sName, array $aArguments = array(), $sDirectory = '') {

    $class = $this->getClass($sName, $sDirectory);
    $result = $this->createObject($class, $aArguments);

    //$this->addStat($class->read('name'));

    return $result;
  }

  public function setArguments($mArguments = null, $bMerge = true) {

    $this->aClasses = array();
    return parent::setArguments($mArguments, $bMerge);
  }

  protected function getClass($sName, $sDirectory) {

    if (!array_key_exists($sName, $this->aClasses)) {

      $result = $this->findClass($sName, $sDirectory);
      $this->aClasses[$sName] = $result;
    }

    return $this->aClasses[$sName];
  }


  /**
   *
   * @param string $sName
   * @param string $sDirectory
   * @return \sylma\core\argument
   */
  public function findClass($sName, $sDirectory = '', $bDebug = true) {

    if (!$this->getArguments()) {

      $this->throwException(sprintf('Cannot build object @class %s. No argument defined', $sName));
    }

    if ($result = $this->loadClass($sName, $this->getArguments(), $bDebug)) {

      $this->loadClassBase($result);
      $this->loadFileBase($result, $sDirectory);
    }

    return $result;
  }

  protected function loadClassBase(core\argument $class) {

  }

  protected function loadFileBase(core\argument $class, $sDirectory = '') {

  }

  /**
   * Look through tree argument for classe's key
   *
   * @param type $sName
   * @param argument $args
   * @return core\argument
   */
  protected function loadClass($sName, core\argument $args, $bDebug) {

    $aPath = explode('/', $sName);
    array_unshift($aPath, null);

    $sPath = substr(implode('/classes/', $aPath), 1);
    $class = $this->lookupClass($sPath, $args, $bDebug);

    return $class;
  }

  protected function lookupClass($sPath, core\argument $args, $bDebug = true) {

    if (!$result = $args->get($sPath, false) and $bDebug) {

      $this->throwException(sprintf('Cannot build object alias %s. Path not found', $sPath), array(), 4);
    }

    return $result;
  }

  /**
   * Create an object from an argument using @method buildClass()
   *
   * @param argument $class The argument object containing the classes infos
   *  Ex : array(classes' => array('keyname' => array('name' => 'classname', 'file' => 'filename')))
   * @param* array $aArguments The list of arguments to send to __construct
   *
   * @return mixed The object created
   */
  public function createObject(core\argument $class, array $aArguments = array()) {

    $result = null;

    if (!$sClass = $class->read('name')) { // has name ?

      $this->throwException(sprintf('Cannot build object. No "name" defined in class'), array(), 3);
    }

    if (self::includeClass($sClass, $class->read('file', false))) {

      $result = $this->buildClass($sClass, $aArguments);
    }

    return $result;
  }

  /**
   * Include classe's file
   *
   * @param string $sClass
   * @param string $sPath
   * @return bool
   */
  public static function includeClass($sClass, $sPath = '') {

    $sMain = \Sylma::ROOT;

    if (!class_exists($sClass, false)) {

      if (!$sPath) {

        $sPath = str_replace('\\', '/', $sClass . '.php');
      }

      // include the file

      $sFile = $sMain . $sPath;

      if (file_exists($sFile)) {

        try {

          require_once($sFile);
        }
        catch (core\exception $e) {

          \Sylma::throwException(sprintf('Cannot load @class %s : %s', $sClass, $e->getMessage()), array('@file ' . $sPath));
        }
      }
      else {

        //$this->throwException(sprintf('Cannot load @class %s. @file %s not found !', $sClass, $sFile));
      }
    }

    return true;
  }

  /**
   * Build an object from the class name with Reflection
   *
   * @param string $sClass the class name
   * @param array $aArgument the arguments to use at the __construct call
   * @return null|object the created object
   */
  public function buildClass($sClass, array $aArguments = array()) {

    $result = null;

    // creation of object

    // caching classes improve performances

    try {

      if (array_key_exists($sClass, $this->aClasses)) $reflected = $this->aClasses[$sClass];
      else $reflected = $this->aClasses[$sClass] = new \ReflectionClass($sClass);
    }
    catch (\ReflectionException $e) {

      $this->throwException($e->getMessage());
    }

    if ($aArguments) $result = $reflected->newInstanceArgs($aArguments);
    else $result = $reflected->newInstance();

    // These 2 following functions doesn't work, keep here for futur brainstorming
    //
    // $result = new $sClass(list($aArguments));
    // $result = call_user_func_array(array($sClass, '__construct'), $aArguments);

    return $result;
  }

  public function getNamespace($sPrefix = null) {

    return parent::getNamespace($sPrefix);
  }
}