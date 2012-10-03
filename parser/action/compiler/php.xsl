<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ls="http://www.sylma.org/security" xmlns:js="http://www.sylma.org/parser/languages/js" xmlns:php="http://www.sylma.org/parser/languages/php" version="1.0">

  <xsl:include href="/#sylma/parser/languages/php/source.xsl"/>

  <xsl:param name="namespace"/>
  <xsl:param name="class"/>
  <xsl:param name="template"/>

  <xsl:template match="php:window">&lt;?php

    <xsl:if test="@use-template = 'true'">$this->setTemplate('<xsl:value-of select="$template"/>');
</xsl:if>

    <xsl:apply-templates select="*"/>

  </xsl:template>

  <xsl:template match="*">
    <xsl:apply-templates select="*"/>
  </xsl:template>

  <xsl:template match="php:template">
    <xsl:value-of select="concat('$this->loadTemplate(', @key, ', $aArguments)')"/>
  </xsl:template>

  <xsl:template match="php:insert-call">

  </xsl:template>

  <xsl:template match="php:insert">
    <xsl:choose>
      <xsl:when test="@context = 'default'">
        <xsl:text>  $aArguments[</xsl:text>
        <xsl:value-of select="@key"/>
        <xsl:text>] = </xsl:text>
        <xsl:apply-templates/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>  $this->getContext('</xsl:text>
        <xsl:value-of select="@context"/>
        <xsl:text>')->add(</xsl:text>
        <xsl:apply-templates/>
        <xsl:text>)</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
