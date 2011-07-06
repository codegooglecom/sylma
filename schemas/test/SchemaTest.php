<?php

require_once('schemas/XML_Schema.php');
require_once('modules/test/Test.php');

class schemaTest extends Test {
  
  const NS_SCHEMA = 'http://www.sylma.org/schemas/test';
  const NS_TARGET = 'http://www.sylma.org';
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    
    $this->setNamespaces(array(
      'self' => self::NS_SCHEMA,
      'target' => self::NS_TARGET,
      'schema' => XSD_Parser::NS,
    ));
    
    parent::__construct();
  }
  
  private function loadSchema(DocumentInterface $doc) {
    
    if (!$eSchema = $doc->get('self:schema', $this->getNS())) {
      
      $this->throwException('No schema defined', '@file ' . $doc->getFile());
    }
    
    $schema = new XML_Document;
    $schema->addNode('schema', $eSchema->getChildren(), array('targetNamespace' => $this->getNamespace('target')), $this->getNamespace());
    
    return $schema;
  }
  
  public function generate(DocumentInterface $doc) {
    
    $aResult = array();
    $schema = $this->loadSchema($doc);
    
    if ((!$expected = $doc->get('self:expected', $this->getNS())) || !$expected->isComplex()) {
      
      $this->throwException('No schema defined', '@file ' . $doc->getFile());
    }
    
    $sName = $expected->getAttribute('name');
    $sExpected = $expected->getAttribute('expected');
    
    $expected = $this->createNode('sylma-schema', $expected->getChildren(), array(), 'schema');
    
    if ((!$test = $doc->get('self:document', $this->getNS())) || !$test->isComplex()) {
      
      $this->throwException('No document defined', '@file ' . $doc->getFile());
    }
    
    $test = new XML_Document($test->getFirst());
    $model = $test->getModel($schema);
    
    if (!$model || $model->isEmpty() || !($root = $model->getRoot())) $this->throwException('No valid model returned', '@file ' . $doc->getFile());
    
    $iResult = $root->compare($expected);
    
    $aResult = array(
      'result' => booltostr($iResult === XML_Element::COMPARE_SUCCESS),
      '@expected' => strtoupper($sExpected),
      '@name' => $sName,
    );
    
    if ($iResult !== XML_Element::COMPARE_SUCCESS) $aResult['message'] = '@node ' . $root->compareBadNode->getPath();
    
    return $aResult;
  }
  
  public function validate(DocumentInterface $doc) {
    
    $aResult = array();
    $schema = $this->loadSchema($doc);
    
    foreach ($doc->query('self:document', $this->getNS()) as $test) {
      
      $tmpDoc = new XML_Document($test->getChildren());
      $bResult = $tmpDoc->validate($schema);
      
      $aResult[] = array(
        'result' => booltostr($test->testAttribute('expected') === $bResult),
        '@expected' => strtoupper($test->getAttribute('expected')),
        '@name' => $test->getAttribute('name'));
    }
    
    return array(
      'description' => $doc->read('self:description', $this->getNS()),
      '#test' => $aResult,
    );
  }
  
  public function parse() {
    
    $aValidation = $aGeneration = array();
    
    foreach ($this->getDirectory()->getFiles(array('xml'), null, null) as $file) {
      
      $doc = $file->getDocument(Sylma::MODE_READ, true);
      
      if (!$root = $doc->getRoot()) continue;
      
      try {
        
        switch ($root->getName()) {
          
          case 'validation' : $aValidation[] = $this->validate($doc); break;
          case 'generation' : $aGeneration[] = $this->generate($doc); break;
        }
      }
      catch (SylmaException $e) {
        
        continue;
      }
    }
    
    $result = Arguments::buildDocument(array(
      'group' => array(
        'description' => t('W3C Schemas validation and generation'),
        '#group' => array(
          array(
            'description' => t('Validation of document'),
            '#group' => $aValidation,
          ),
          array(
            'description' => t('Generation of model'),
            '#test' => $aGeneration,
          ),
        ),
      ),
    ), $this->getNamespace());
    
    return $result;
  }
}

