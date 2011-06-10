<?php

require_once('Reflector.php');

class InspectorReflectorCommented extends InspectorReflector {
  
  /**
   * An object reading the comment of the class
   * @var InspectorCommentInterface
   */
  protected $comment;
  
  protected function loadComment($sClass) {
    
    $this->comment = $this->getControler()->create($sClass,
      array($this->getReflector()->getDocComment(), $this));
  }
}