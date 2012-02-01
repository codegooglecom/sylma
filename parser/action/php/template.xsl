<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ls="http://www.sylma.org/security" xmlns:php="http://www.sylma.org/parser/action/compiler" version="1.0">

  <xsl:template match="php:window">
    <xsl:variable name="tpl" select="//php:template"/>
    <xsl:choose>
      <xsl:when test="$tpl">

        <!-- Sub templates with if/else -->
        <xsl:for-each select="$tpl">
          <xsl:processing-instruction name="php">
            <xsl:if test="position() != 1">else</xsl:if>
            <xsl:value-of select="concat('if ($iTemplate == ', @key, ')')"/>
            <xsl:text> : </xsl:text>
          </xsl:processing-instruction>
          <xsl:apply-templates/>
        </xsl:for-each>

        <!-- Main template with else -->
        <xsl:processing-instruction name="php">else : </xsl:processing-instruction>
        <xsl:apply-templates/>
        <xsl:processing-instruction name="php">
          <xsl:text>endif; </xsl:text>
        </xsl:processing-instruction>

      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="php:*">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="php:insert-call">
    <xsl:processing-instruction name="php">
      <xsl:text>echo $aArguments[</xsl:text>
      <xsl:value-of select="@key"/>
      <xsl:text>]; </xsl:text>
    </xsl:processing-instruction>
  </xsl:template>

  <xsl:template match="php:template">

  </xsl:template>

  <xsl:template match="php:argument">
  </xsl:template>

  <xsl:template match="*">
    <xsl:element name="{local-name()}" namespace="{namespace-uri()}">
      <xsl:apply-templates select="node() | @*"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="text()">
    <xsl:copy-of select="."/>
  </xsl:template>

  <xsl:template match="@*">
    <xsl:copy-of select="."/>
  </xsl:template>

</xsl:stylesheet>
