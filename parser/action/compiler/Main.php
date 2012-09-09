<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom, sylma\parser\languages\common;

\Sylma::load('/parser/compiler/Basic.php');
\Sylma::load('/parser/compiler/documented.php');

class Main extends parser\compiler\Basic implements parser\compiler\documented {

  const PHP_TEMPLATE = 'php.xsl';
  const DOM_TEMPLATE = 'template.xsl';

  const WINDOW_ARGS = 'classes/php';

  public function __construct(core\factory $controler) {

    $this->setControler($controler);
  }

  public function build(fs\file $file, fs\directory $base) {

    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();

    $window = $this->runReflector($file, $base);

    if ($this->getControler()->readArgument('debug/show')) {

      $tmp = $this->create('document', array($window));
//      dspm($this->getFile()->asToken());
//      dspm(new \HTML_Tag('pre', $tmp->asString(true)));
      echo '<pre>' . $this->getFile()->asToken() . '</pre>';
      echo '<pre>' . str_replace(array('<', '>'), array('&lt;', '&gt'), $tmp->asString(true)) . '</pre>';
    }

    $tpl = $this->getCachedFile($file, '.tpl.php');

    $template = $this->getTemplate(self::PHP_TEMPLATE);

    $template->setParameters(array(
      'template' => $tpl->getRealPath(),
    ));

    $script = $this->getCachedFile($file);

    $sContent = $template->parseDocument($window, false);
    $script->saveText($sContent);

    if ($window->getRoot()->testAttribute('use-template')) {

      $template = $this->getTemplate(self::DOM_TEMPLATE);

      if ($sContent = $template->parseDocument($window, false)) {

        $sContent = $this->parseAttributes($sContent);
        $tpl->saveText(substr($sContent, 22));
      }
    }

    return $script;
  }

  public function buildInto(fs\file $file, fs\directory $base, common\_window $window) {

    $reflector = $this->createReflector($file, $base);
    $reflector->setWindow($window);

    try {

      $reflector->build($window);
    }
    catch (core\exception $e) {

      $e->addPath($file->asToken());
      throw $e;
    }
  }

  /**
   *
   * @param \sylma\storage\fs\file $file
   * @param \sylma\storage\fs\directory $base
   * @return dom\handler
   * @throws \sylma\parser\action\compiler\exception
   */
  protected function runReflector(fs\file $file, fs\directory $base) {

    $factory = $this->getControler();
    $reflector = $this->createReflector($file, $base);

    $window = $factory->create('window', array($reflector, $factory->getArgument(self::WINDOW_ARGS), $reflector->getInterface()->getName()));
    $reflector->setWindow($window);

    try {

      $result = $reflector->asDOM();
    }
    catch (core\exception $e) {

      $e->addPath($file->asToken());
      throw $e;
    }

    return $result;
  }

  protected function parseAttributes($sContent) {

    $sContent = preg_replace('/\[sylma:insert:(\d+)\]/', '<?php echo $aArguments[$1]; ?>', $sContent);

    return $sContent;
  }

}