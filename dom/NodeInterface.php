<?php

interface NodeInterface {
  
  public function isElement();
  public function isText();
  public function remove();
  public function getDocument();
  public function getParent();
  public function getPrevious();
  public function getNext();
  public function getPath();
}