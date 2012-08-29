<?php

namespace sylma\parser\action\compiler;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom;

require_once('core/module/Domed.php');

class Main extends core\module\Domed {

  const PHP_TEMPLATE = '/#sylma/parser/languages/php/class.xsl';
  const DOM_TEMPLATE = '/#sylma/parser/languages/php/template.xsl';

  public function __construct(core\factory $controler) {

    $this->setControler($controler);

    $this->setDirectory(__FILE__);
    $this->loadDefaultArguments();
  }

  public function build(fs\file $file, fs\directory $base) {

    $sClass = $file->getName() . '.php';
    $sTemplate = $file->getName() . '.tpl.php';

    $fs = $this->getControler('fs/cache');
    $dir = $fs->getDirectory()->addDirectory((string) $file->getParent());

    $tpl = $dir->getFile($sTemplate, fs\resource::DEBUG_EXIST);

    $method = $this->reflectAction($file, $base);

    if ($this->getControler()->readArgument('debug/show')) {
      $tmp = $this->create('document', array($method));
//      dspm($this->getFile()->asToken());
//      dspm(new \HTML_Tag('pre', $tmp->asString(true)));
      echo '<pre>' . $this->getFile()->asToken() . '</pre>';
      echo '<pre>' . str_replace(array('<', '>'), array('&lt;', '&gt'), $tmp->asString(true)) . '</pre>';
    }

    // set new class and file

    $script = $dir->getFile($sClass, fs\resource::DEBUG_EXIST);

    $template = $this->getTemplate(self::PHP_TEMPLATE);

    $template->setParameters(array(
      'template' => $tpl->getRealPath(),
    ));

    $sResult = $template->parseDocument($method, false);
    $script->saveText($sResult);

    if ($method->getRoot()->testAttribute('use-template')) {

      $template = $this->getTemplate(self::DOM_TEMPLATE);

      if ($sResult = $template->parseDocument($method, false)) {

        $sResult = $this->parseAttributes($sResult);
        $tpl->saveText(substr($sResult, 22));
      }
    }

    return $script;
  }

  protected function reflectAction(fs\file $file, fs\directory $base) {

    $doc = $file->getDocument(array(), \Sylma::MODE_EXECUTE);

    try {

      $action = $this->getControler()->create('compiler/dom', array($this->getControler(), $doc, $base));
      $result = $action->asDOM();
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