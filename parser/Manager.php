<?php

namespace sylma\parser;
use sylma\core, sylma\storage\fs, sylma\parser\compiler, sylma\parser\reflector;

class Manager extends compiler\Manager {

  const MANAGER_PATH = 'manager';
  const REFLECTOR_PATH = 'reflector';
  const CACHED_PATH = 'cached';

  protected static $sArgumentClass = 'sylma\core\argument\Filed';
  protected static $sFactoryClass = '\sylma\core\factory\Reflector';

  protected $aNamespaces = array();
  protected $aParserManagers = array();
  protected $aContexts = array();

  /**
   * For stats
   */
  public $aBuilded = array();

  public function __construct() {

    $this->setDirectory(__FILE__);
    $this->setArguments('manager.yml');

    $this->loadNamespaces($this->getArgument('namespaces'));

  }

  protected function loadNamespaces(core\argument $namespaces) {

    $this->aNamespaces = $namespaces->asArray();
  }

  public function load(fs\file $file, array $aArguments = array(), $bUpdate = null, $bRun = true) {

    $result = parent::load($file, $aArguments, $bUpdate, $bRun);
/*
    if (!$result) {

      $this->launchException('No result on parsing', get_defined_vars());
    }
*/
    return $result;
  }

  public function loadBuilder(fs\file $file, fs\directory $dir = null, core\argument $args = null) {

    if (!$dir) $dir = $file->getParent();
    $doc = $file->getDocument();
    $sNamespace = $doc->getRoot()->getNamespace();

    if (!$result = $this->getParserManager($sNamespace)) {

      if (array_key_exists($sNamespace, $this->aNamespaces[self::MANAGER_PATH])) {

        $sClass = $this->aNamespaces[self::MANAGER_PATH][$sNamespace];
        $result = $this->createBuilder($sClass, $file, $dir, $args);
      }
      else {

        $this->throwException(sprintf('No builder associated to namespace %s', $sNamespace));
      }
    }

    return $result;
  }

  public function build(fs\file $file, fs\directory $dir) {

    if (!\Sylma::read('debug/enable')) {

      $this->throwException('This function is low performance and must not be used in production environnement');
    }

    $builder = $this->loadBuilder($file, $dir);
    $this->aBuilded[] = $file;

    return $builder->build($dir);
  }

  /**
   *
   * @param type $sNamespace
   * @param boolean $bDebug
   * @return \sylma\parser\compiler\Manager
   */
  public function getParserManager($sNamespace, $bDebug = true) {

    return array_key_exists($sNamespace, $this->aParserManagers) ? $this->aParserManagers[$sNamespace] : null;
  }

  public function getCachedParser($sNamespace, $parent, $bDebug = true) {

    if (array_key_exists($sNamespace, $this->aNamespaces[self::CACHED_PATH])) {

      $sClass = $this->aNamespaces[self::CACHED_PATH][$sNamespace];
      $result = $this->create($sClass, array($parent));
    }
    else if ($bDebug) {

      $this->throwException(sprintf('No cached parser associated to namespace %s', $sNamespace));
    }

    return $result;
  }

  /**
   *
   * @param string $sNamespace
   * @param unknown $parent TODO : set type (between cached parser and reflector\elemented)
   * @param boolean $bDebug
   * @return \sylma\parser\reflector\container
   */
  public function getParser($sNamespace, reflector\documented $documented, reflector\domed $parent = null, $bDebug = true) {

    $result = null;

    if (array_key_exists($sNamespace, $this->aNamespaces[self::REFLECTOR_PATH])) {

      $sClass = $this->aNamespaces[self::REFLECTOR_PATH][$sNamespace];
      $result = $this->create($sClass, array($documented, $parent, $this->findClass($sClass)));
    }
    else if ($bDebug) {

      $this->throwException(sprintf('No elemented parser associated to namespace %s', $sNamespace));
    }

    return $result;
  }

  protected function findClass($sPath) {

    return $this->getFactory()->findClass($sPath);
  }

  public function setContext($sName, $context) {

    $this->aContexts[$sName] = $context;
  }

  /**
   *
   * @param type $sName
   * @param type $bLoad
   * @return \sylma\parser\context|\sylma\parser\handler
   */
  public function getContext($sName, $bLoad = true) {

    $result = null;

    if (!array_key_exists($sName, $this->aContexts)) {

      if ($bLoad) {
//$this->launchException('Seems not ready');
        $result = $this->create($sName);
        $this->setContext($sName, $result);
      }
    }
    else {

      $result = $this->aContexts[$sName];
    }

    return $result;
  }
}
