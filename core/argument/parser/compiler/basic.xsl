<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://www.sylma.org/parser/languages/php" version="1.0">

  <xsl:include href="/#sylma/parser/languages/php/source.xsl"/>

  <xsl:template match="php:window">&lt;?php
    <xsl:apply-templates select="*"/>
  </xsl:template>

  <xsl:template match="php:array">
    <xsl:param name="indent" select="string('')"/>
    <xsl:text>array(</xsl:text>
    <xsl:value-of select="$break"/>
    <xsl:for-each select="php:item">
      <xsl:value-of select="$indent"/>
      <xsl:apply-templates select=".">
        <xsl:with-param name="assoc" select="../@associative"/>
        <xsl:with-param name="indent" select="concat($indent, '  ')"/>
      </xsl:apply-templates>
      <xsl:if test="position() != last()">,<xsl:value-of select="$break"/></xsl:if>
    </xsl:for-each>
    <xsl:text>)</xsl:text>
  </xsl:template>

  <xsl:template match="php:item">
    <xsl:param name="assoc"/>
    <xsl:param name="indent"/>
    <xsl:if test="$assoc">
      <xsl:apply-templates select="@key"/>
      <xsl:text> => </xsl:text>
    </xsl:if>
    <xsl:apply-templates select="*">
      <xsl:with-param name="indent" select="$indent"/>
    </xsl:apply-templates>
  </xsl:template>

</xsl:stylesheet>
