<?php

interface FileInterface {
  
  const DEBUG_LOG = 1;
  const DEBUG_EXIST = 2;
  
  public function getDocument();
}