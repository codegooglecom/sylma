<?php

namespace sylma\core\factory;
use sylma\core;

class Cached extends core\module\Argumented implements core\factory {

  /**
   * Classes to use within @method create() loaded in @settings /classes
   */
  public $aClasses = array();

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

    return $this->createObject($this->getClass($sName, $sDirectory), $aArguments);;
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
  public function findClass($sName, $sDirectory = '') {

    $result = null;

    if (!$this->getArguments()) {

      $this->throwException(sprintf('Cannot build object @class %s. No argument defined', $sName));
    }

    // set class name
    if (!$result = $this->loadClass($sName, $this->getArguments())) {

      $this->throwException(sprintf('Class %s cannot be load', $sName));
    }

    $this->loadClassBase($result);
    $this->loadFileBase($result, $sDirectory);

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
  protected function loadClass($sName, core\argument $args) {

    $aPath = explode('/', $sName);
    array_unshift($aPath, null);

    $sPath = implode('/classes/', $aPath);
    $class = $this->lookupClass($sPath, $args);

    return $class;
  }

  protected function lookupClass($sPath, core\argument $args) {

    if (!$result = $args->get($sPath, false)) {

      $this->throwException(sprintf('Cannot build object alias %s. Path not found', $sPath));
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
   * @param string $sFile
   * @return bool
   */
  public static function includeClass($sClass, $sFile = '') {

    $sMain = \Sylma::ROOT;

    if (!class_exists($sClass, false)) {

      if (!$sFile) {

        $sFile = str_replace('\\', '/', $sClass . '.php');
      }

      // include the file

      $sFile = $sMain . $sFile;

      if (file_exists($sFile)) {

        require_once($sFile);
      }
      else {

        //$this->throwException(sprintf('Cannot load @class %s. @file %s not found !', $sClass, $sFile));
      }
    }

    if (!interface_exists($sClass) && !class_exists($sClass, false)) {

      $this->throwException(sprintf('@class %s has not been loaded !', $sClass));
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
    if (array_key_exists($sClass, $this->aClasses)) $reflected = $this->aClasses[$sClass];
    else $reflected = $this->aClasses[$sClass] = new \ReflectionClass($sClass);

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