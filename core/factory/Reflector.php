<?php

namespace sylma\core\factory;
use \sylma\core;

require_once('core/factory.php');
require_once('core/module/Namespaced.php');
require_once('core/functions/Path.php');

class Reflector extends core\module\Namespaced implements core\factory {

  /**
   * Classes to use within @method create() loaded in @settings /classes
   */
  private $aClasses = array();
  private $settings;

  public function setSettings(core\argument $settings) {

    $this->settings = $settings;
  }

  protected function getSettings() {

    return $this->settings;
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

    $class = $this->findClass($sName, $sDirectory);

    return $this->createObject($class, $aArguments);
  }

  public function findClass($sName, $sDirectory = '') {

    $result = null;

    if (!$this->getSettings()) {

      $this->throwException(txt('Cannot build object @class %s. No argument defined', $sName));
    }

    // set class name
    if (!$class = $this->loadClass($sName, $this->getSettings())) {

      $this->throwException(txt('Class %s cannot be load', $sName));
    }

    //if ($sName == 'directory') dspf()
    //dspf(class_exists('\sylma\storage\fs\basic\editable\Directory'));
    if ($sClassBase = $this->getSettings()->getToken(self::CLASSBASE_TOKEN)) {

      $class->set('name', core\functions\path\toAbsolute($class->read('name'), $sClassBase, '\\'));
    }

    // set file name
    if ($sInlineDirectory = $this->getSettings()->getLastDirectory()) $sDirectory = $sInlineDirectory;

    if ($sFile = $class->read('file', false)) {

      $class->set('file', path_absolute($sFile, $sDirectory));
    }
    //echo($class->read('name')) . '<br/>';
    return $class;
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

    $args->registerToken(self::DIRECTORY_TOKEN);
    $args->registerToken(self::CLASSBASE_TOKEN);

    if (!$class = $args->get($sPath)) {

      $this->throwException(txt('Cannot build object @class %s. No argument defined for these class', $sName));
    }

    $args->unRegisterToken(self::DIRECTORY_TOKEN);
    $args->unRegisterToken(self::CLASSBASE_TOKEN);

    return $class;
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

      \Sylma::throwException(txt('Cannot build object. No "name" defined in class'), array(), 3);
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

    if (!class_exists($sClass)) {

      if (!$sFile) {

        $sFile = str_replace('\\', '/', $sClass . '.php');
      }

      // include the file

      $sFile = $sMain . $sFile;

      if (file_exists($sFile)) require_once($sFile);
      else {

        \Sylma::throwException(txt('Cannot load @class %s. @file %s not found !', $sClass, $sFile));
      }
    }

    if (!interface_exists($sClass) && !class_exists($sClass)) {

      \Sylma::throwException(txt('@class %s has not been loaded !', $sClass));
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