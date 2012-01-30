<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:frm="http://www.sylma.org/modules/formater" version="1.0">

  <xsl:template match="/frm:window">
    <div>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="frm:array">
    <span>
      <xsl:text>Array(</xsl:text>
      <xsl:for-each select="frm:item">
        <xsl:apply-templates select="."/>
        <xsl:if test="position() != last()">, </xsl:if>
      </xsl:for-each>
      <xsl:text>)</xsl:text>
    </span>
  </xsl:template>

  <xsl:template match="frm:item">
    <xsl:apply-templates select="frm:key"/>
    <xsl:text> => </xsl:text>
    <xsl:apply-templates select="frm:value"/>
  </xsl:template>

  <xsl:template match="frm:string">
    <span><xsl:value-of select="concat('&quot;', ., '&quot;')"/></span>
  </xsl:template>

  <xsl:template match="frm:numeric">
    <span><xsl:value-of select="."/></span>
  </xsl:template>

</xsl:stylesheet>
