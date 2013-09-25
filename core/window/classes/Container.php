<?php

namespace sylma\core\window\classes;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\core\functions;

class Container extends core\module\Domed {

  const SETTINGS_FACTORY_PRIORITY = true;

  const CONTENT_ARGUMENT = 'sylma-paths';
  const CONTENT_SUB = 'content';

  protected $aPaths = array();

  public function __construct(core\argument $args, core\argument &$contexts) {

    $this->setArguments($args);

    $this->setPaths($this->getArgument(self::CONTENT_ARGUMENT)->query());
    $this->setArgument(self::CONTENT_ARGUMENT, null);
  }

  protected function setPaths(array $aPaths) {

    $this->aPaths = $aPaths;
  }

  protected function getPaths() {

    return $this->aPaths;
  }

  protected function getContexts() {

    return $this->contexts;
  }

  protected function setContexts(core\argument $contexts) {

    $this->contexts = $contexts;
  }

  protected function loadContexts(dom\document $doc) {

    foreach ($this->getContexts()->query() as $sName => $context) {

      $this->loadContext($sName, $context, $doc);
    }
  }

  protected function loadContext($sName, $context, dom\document $doc) {

    // TODO : define behaviour
  }

  protected function loadInfos(dom\handler $doc) {

    \Sylma::load('/sylma/core/functions/Numeric.php');

    $parser = $this->getManager('parser');
    $path = $this->getManager('path');
    $user = $this->getManager('user');
    $init = $this->getManager('init');

    $aBuilded = $parser->aBuilded;
    $aLoaded = $parser::$aLoaded;

    $iLoaded = 0;
    array_walk($aLoaded, function (&$item, $key) use (&$iLoaded) {
      $iLoaded += $item;
      $item = "$key : ($item)";
    });

    $file = $path->asFile();

    $content = $this->createArgument(array(
      'ul' => array(
        '#li' => array(
          'user : ' . $user->getName(),
          'time : ' . functions\numeric\formatFloat($init->getElapsedTime()),
          array(
            'a' => array(
              '@href' => '#',
              '@onclick' => "sylma.ui.send('/sylma/modules/rebuild/standalone', {path : '$file'}, true); return false;",
              (string) $file,
            ),
          ),
          'builded : ' . count($aBuilded),
          array(
            'ul' => array(
              '#li' => array_map('strval', $aBuilded),
            ),
          ),
          'loaded : ' . $iLoaded,
          array(
            'ul' => array(
              '#li' => $aLoaded,
            ),
          ),
        ),
      ),
    ), $this->getNamespace('html'));

    return $content;
  }

  protected function parseDOM(dom\domable $val) {

    return $val->asDOM();
  }

  public function getContent() {

    return $this->buildWindowScript($this->getPaths());
  }

  protected function buildWindowScript(array $aPaths) {

    $args = $this->getArguments();

    try {

      $content = $this->prepareMain($this->getFile(current($aPaths)), $args, true);
    }
    catch (core\exception $e) {

      $e->save(false);

      if (!\Sylma::isAdmin()) {

        header('HTTP/1.0 404 Not Found');
      }

      $content = $this->getError();
    }

    while (next($aPaths)) {

      $sPath = current($aPaths);

      $args->set(self::CONTENT_SUB, $content);
      $content = $this->getScriptFile($this->getFile($sPath), array(
        'arguments' => $args,
        'contexts' => $this->getContexts(),
      ));
    }

    return $content;
  }

  public function getError() {

    $this->loadDefaultSettings();

    return $this->runScript($this->getFile($this->read('error/action')));
  }

  protected function prepareMain(fs\file $file, core\argument $args) {

    switch ($file->getExtension()) {

      case 'eml' : $result = $this->runAction($file, $args); break;
      case 'vml' : $result = $this->runScript($file, $args); break;
      default :

        $this->launchException('Unknown extension for window content');
    }

    if (!$result) {

      $result = '';
      //$this->launchException('No content for main window');
    }

    return $result;
  }

  protected function runScript(fs\file $file, core\argument $args = null) {

    $builder = $this->getManager(self::PARSER_MANAGER);

    $result = $builder->load($file, array(
      'arguments' => $args,
      'contexts' => $this->getContexts(),
      //'post' => $post,
    ), $this->read('debug/update', false), $this->read('debug/run'), true);

    return $result;
  }

  /**
   * @deprecated
   */
  protected function runAction(fs\file $file, core\argument $args) {

    $result = $this->createAction($file, $args->asArray());
    $result->setContexts($this->getContexts());
    //$result->setParentParser($window);

    return $result->asString();
  }

  protected function createAction(fs\file $file, array $aArguments = array()) {

    return $this->create('action', array($file, $aArguments));
  }

  public function prepare($sContent) {

    return $sContent;
  }
}
