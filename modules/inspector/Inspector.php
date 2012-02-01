<?php

namespace sylma\modules\inspector;
use sylma\core;

require_once('core/module/Domed.php');

class Module extends core\module\Domed {

  const ELEMENT_CLASS = 'element';
  const CLASS_CLASS = 'class'; // :(

  const MESSAGES_STATUT = 'warning';
  const NS = 'http://www.sylma.org/modules/inspector';

  public function __construct() {

    $this->setArguments(\Sylma::get('modules/inspector'));

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS);
  }

  public function getDeclared() {

    $system = $this->createArgument('system-classes.yml');
    $system->merge($this->createArgument('sylma-classes.yml'));
    $aAll = get_declared_classes();

    $root = $this->create(self::ELEMENT_CLASS, array('classes', null, null, self::NS));
    foreach (array_diff($aAll, $system->query()) as $sClass) $root->addNode('class', $sClass);

    return $root->getDocument();
  }

  public function getSimpleClass($sClass, $sFile = '') {

    $result = null;

    try {

      if ($sFile) Controler::loadClass($sClass, $sFile);

      $class = $this->create(self::CLASS_CLASS, array($sClass, $this, array('parent' => false)));
      $result = $class->parse();

    }
    catch (SylmaExceptionInterface $e) {

    }

    return $result;
  }

  public function stringClass($sClass) {

    $result = null;

    try {

      $class = $this->create(self::CLASS_CLASS, array($sClass, $this, array('parent' => false)));
      $doc = $class->parse();

      if ($doc && !$doc->isEmpty()) {

        $sResult = $doc->parseXSL($this->getTemplate('class-string.xsl'), false);
        $result = new HTML_Tag('pre', $sResult);
      }
    }
    catch (SylmaExceptionInterface $e) {

    }

    return $result;
  }

  /**
   * Read the module @settings /classes
   */
  public function getModule($sSettings) {

    $args = $this->createArgument($sSettings);

    return $this->createArgument(array(
      'classes' => array(
        '#class' => $this->extractClasses($args),
      ),
    ));
  }

  private function extractClasses(\sylma\core\argument $class) {

    $aResult = array();

    if ($classes = $class->get('classes', false)) {

      foreach ($classes as $sKey => $subClass) {

        if ($sKey{0} != '@') {

          $aResult[] = $this->createClass($sKey, $subClass);
        }
      }
    }

    return $aResult;
  }

  private function createClass($sName, \sylma\core\argument $class) {

    return array(
      '@key' => $sName,
      '@name' => $class->read('name'),
      'file' => $class->read('file', false),
      '#class' => $this->extractClasses($class),
    );
  }

  protected function buildClass($sKey, $sPath) {

    $args = new XArguments($sPath, $this->getNamespace());
    $class = $this->loadClass($sKey, $args);

    if ($sFile = $class->read('file', false)) $class->set('file', path_absolute($sFile, $args->getLastDirectory()));
    if ($sClass = $class->getToken(self::CLASSBASE_TOKEN)) $class->set('name', path_absolute($class->get('name'), $sClass, '\\'));

    if (!$class->read('name')) {

      $this->throwException(txt('No name defined for class %s', $sKey));
    }

    return $class;
  }

  /**
   * Load full class and sub-classes
   */
  public function getClassSettings($sKey, $sPath) {

    $class = $this->buildClass($sKey, $sPath);
    return $this->getClass($class->read('name'), $class->read('file', false));
  }

  /**
   * Load full class and sub-classes
   */
  public function getClass($sClass, $sFile = '') {

    $result = null;

    try {

      Controler::loadClass($sClass, $sFile);

      $class = $this->create(self::CLASS_CLASS, array($sClass, $this));

      $result = $class->parse();
      if ($sFile) $result->setAttribute('file', $sFile);
    }
    catch (SylmaExceptionInterface $e) {


    }
    catch (Exception $e) {

      Sylma::loadException($e);
    }

    return $result;
  }
}


