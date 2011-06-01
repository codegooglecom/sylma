<?php

class InspectorComment {
  
  protected $sComment = '';
  protected $aComment = '';
  
  public function __construct($sComment) {
    
    $this->sComment = $sComment;
    $this->parseValue();
  }
  
  public function parseValue() {
    
    preg_match('`/\*\*[*\s]*(\s+\*\s+(.+)$)+`', $this->sComment, $aMatch);
  }
}

class InspectorCommentClass extends InspectorComment {
  
  public function getClass() {
    
    return $this->sComment;
  }
  
  public function parse() {
    
    dspf($this->aComment);
  }
}