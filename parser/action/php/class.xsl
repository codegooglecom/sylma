<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ls="http://www.sylma.org/security" xmlns:php="http://www.sylma.org/parser/action/compiler" version="1.0">

  <xsl:output method="text"/>

  <xsl:param name="namespace"/>
  <xsl:param name="class"/>

  <xsl:template match="php:window">&lt;?php

namespace <xsl:value-of select="$namespace"/>;
use sylma\parser;

require_once('parser\action\cached\Document.php');

class <xsl:value-of select="$class"/> extends <xsl:value-of select="@extends"/> {

  protected $bTemplate = <xsl:value-of select="@use-template"/>;

  protected function runAction() {

    $aArguments = array();

    <xsl:apply-templates select="*"/>

    return $aArguments;
  }
}

  </xsl:template>

  <xsl:template match="php:*">
    $this->throwException(t('Invalid template @element <xsl:value-of select="local-name()"/>'))
  </xsl:template>

  <xsl:template match="php:assign">
    <xsl:call-template name="php:assign">
      <xsl:with-param name="variable" select="php:variable/*"/>
      <xsl:with-param name="value" select="php:value/*"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="php:line">
    <xsl:apply-templates/>;
  </xsl:template>

  <xsl:template name="php:assign">
    <xsl:param name="variable"/>
    <xsl:param name="value"/>
    <xsl:apply-templates select="$variable"/> = <xsl:apply-templates select="$value"/>
  </xsl:template>

  <xsl:template match="php:insert">
    <xsl:text>$aArguments[</xsl:text>
    <xsl:value-of select="@key"/>
    <xsl:text>] = </xsl:text>
    <xsl:apply-templates/>;
  </xsl:template>

  <xsl:template match="php:call">
    <xsl:apply-templates select="php:called"/>
    <xsl:text>-&gt;</xsl:text>
    <xsl:apply-templates select="@name"/>
    <xsl:text>(</xsl:text>
    <xsl:call-template name="arguments"/>
    <xsl:text>)</xsl:text>
  </xsl:template>

  <xsl:template match="php:called">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="php:var">
    <xsl:text>$</xsl:text>
    <xsl:value-of select="@name"/>
  </xsl:template>

  <xsl:template match="php:argument">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template name="arguments">
    <xsl:for-each select="php:argument">
      <xsl:apply-templates select="."/>
      <xsl:if test="following-sibling::php:argument">, </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="php:string">
    <xsl:text>'</xsl:text>
    <xsl:value-of select="."/>
    <xsl:text>'</xsl:text>
  </xsl:template>

  <xsl:template match="php:numeric">
    <xsl:value-of select="."/>
  </xsl:template>

  <xsl:template match="php:boolean">
    <xsl:value-of select="."/>
  </xsl:template>

  <xsl:template match="php:cast">
    <xsl:value-of select="concat('(', @type, ')')"/>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="php:concat">
    <xsl:for-each select="*">
      <xsl:apply-templates select="."/>
      <xsl:if test="position() != last()"> . </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="php:null">NULL</xsl:template>

</xsl:stylesheet>
