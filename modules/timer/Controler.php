<?php

namespace sylma\modules\timer;
use sylma\core, sylma\storage\fs;

require_once('core/module/Domed.php');

class Controler extends core\module\Domed {

  const NS = 'http://www.sylma.org/modules/utils/timer';

  const ARGUMENTS_CLASS = 'argument';
  const DIRECTORY_TMP = '/tmp';
  const DIRECTORY_PREFIX = 'timer-';

  const FILE_PHP = 'Classes.php';
  const FILE_YAML = 'settings.yml';
  const FILE_TEMPLATE = 'class-time.xsl';

  const INSPECTOR_CLASS = 'inspector';
  const INSPECTOR_PATH = 'modules/inspector';

  const CLASS_PREFIX = 'sylma\modules\timer\tmp';

  protected $inspector;

  public function __construct() {

    $this->setDirectory(__file__);

    $this->loadDefaultArguments();
    $this->setArguments('settings.yml');

    $this->setNamespace(self::NS);

    \Sylma::setControler('timer', $this->create('timer'));

    //$this->getArguments()->set('classes/inspector', \Sylma::get(self::INSPECTOR_PATH));
    $this->inspector = $this->create(self::INSPECTOR_CLASS);
  }

  public function loadSettings($sPath) {

    $directory = $this->createTempDirectory('test');

    if (!$this->inspector) $this->throwException(t('No inspector defined'));

    $arg = $this->createArgument($sPath);

    if (!$classes = $arg->get('classes')) $this->throwException(txt('No classes found in @file %s', $sPath));

    require_once('core/settings/Arguments.php');

    $aClasses = $this->loadClasses($classes, $directory);

    $classes = \Arguments::buildDocument(array('classes' => $aClasses), $this->inspector->getNamespace());

    $template = new \XSL_Document((string) $this->getFile(self::FILE_TEMPLATE));
    $template->setParameter('namespace', self::CLASS_PREFIX);

    $sContent = $template->parseDocument($classes, false);

    $file = $directory->getFile(self::FILE_PHP, fs\file::DEBUG_EXIST);

    if ($file->saveText($sContent)) dspm(xt('File has been saved in %s', new \HTML_A((string) $file, (string) $file)), 'success');
    else dspm(xt('Cannot save file at path %s', (string) $file), 'warning');
    // dspf(SYLMA_PATH);
    // dspf($arg->query('classes'));
    $sContent = $arg->dump();

    $file = $directory->getFile(self::FILE_YAML, fs\file::DEBUG_EXIST);

    if ($file->saveText($sContent)) dspm(xt('File has been saved in %s', new \HTML_A((string) $file, (string) $file)), 'success');
    else dspm(xt('Cannot save file at path %s', (string) $file), 'warning');
  }

  protected function loadClasses(core\argument $classes, fs\directory $dir) {

    $aResult = array();

    $factory = \Sylma::getControler('factory');

    $classes->registerToken(core\factory::CLASSBASE_TOKEN);
    $classes->registerToken(core\factory::DIRECTORY_TOKEN);

    foreach ($classes as $sKey => $class) {

      if ($subClasses = $class->get('classes', false)) $aResult = array_merge($aResult, $this->loadClasses($subClasses, $dir));

      if (!$class->read('name', false)) continue; // empty class

      if ($sClassBase = $classes->getToken(core\factory::CLASSBASE_TOKEN)) {

        $class->set('name', path_absolute($class->read('name'), $sClassBase, '\\'));
      }

      if ($sFile = $class->read('file', false)) {

        $class->set('file', path_absolute($sFile, $class->getLastDirectory()));
      }

      $sFullName = $class->read('name');

      core\factory\Reflector::includeClass($sFullName, $sFile);

      $aResult[] = $arg = $this->inspector->getSimpleClass($sFullName, $sFile);

      $sName = str_replace('\\', '_', $sFullName);

      $arg->setAttribute('valid-name', $sName);

      $class->set('name', '\\' . self::CLASS_PREFIX . '\\' . $sName);
      $class->set('file', $dir . '/' . self::FILE_PHP);
    }

    return $aResult;
  }

  // public function getClass($sClass) {


    // $doc = $inspector

    // if ($doc && !$doc->isEmpty()) {

      // $sResult = $doc;
      // $result = new HTML_Tag('pre', $sResult);
    // }

    // return $result;
  // }

  public function parse() {

    return \Sylma::getControler('timer')->parse();
  }
}