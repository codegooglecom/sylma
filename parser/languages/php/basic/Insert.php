<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\dom, \sylma\parser\languages\common, \sylma\parser\languages\php;

require_once('core/argumentable.php');
\Sylma::load('/parser/languages/common/basic/Controled.php');

/**
 * Add content result to template, choose for wich as*() method to use for render
 */
class Insert extends common\basic\Controled implements core\argumentable {

  protected $content;

  protected $iKey;

  protected $sContext = common\_window::CONTEXT_DEFAULT;

  public function __construct(common\_window $controler, common\linable $mContent, $iKey = null, $bTemplate = true) {

    $this->setControler($controler);

    $this->bTemplate = $bTemplate;
    $sContext = $controler->getContext();

        
    if (!is_null($iKey)) $this->iKey = $iKey;
    else $this->iKey = $this->getControler()->getKey('insert-' . $sContext);

    if ($bTemplate) $controler->add(new self($controler, $mContent, $this->getKey(), false));
    else $this->addContent($mContent);

    $this->setContext($sContext);
    //if ($this->getKey() == 2) $this->getControler()->throwException('t');
  }

  protected function getContext() {

    return $this->sContext;
  }

  protected function setContext($sContext) {

    $this->sContext = $sContext;
  }

  protected function addContent(common\linable $mContent) {

    if ($this->content) {

      $this->throwException(t('Cannot set more than once the content in insert'));
    }

    $this->content = $mContent;
  }

  public function getKey() {

    return $this->iKey;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      ($this->bTemplate ? 'insert-call' : 'insert') => array(
        '@key' => $this->getKey(),
        '@context' => $this->getContext(),
        $this->content,
      ),
    ));
  }
  
  public function asString() {
    
    return '[sylma:insert:' . $this->getKey() . ']';
  }
}