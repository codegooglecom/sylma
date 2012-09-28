<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:frm="http://www.sylma.org/modules/formater" version="1.0">

  <xsl:template match="/frm:window">
    <div>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="frm:array">
    <span class="formater-array">
      <xsl:text>Array[</xsl:text>
      <xsl:value-of select="count(frm:item)"/>
      <xsl:text>](</xsl:text>
      <br/>
      <xsl:for-each select="frm:item">
        <xsl:apply-templates select="."/>
        <br/>
        <xsl:if test="position() != last()">, </xsl:if>
      </xsl:for-each>
      <xsl:text>)</xsl:text>
    </span>
  </xsl:template>

  <xsl:template match="frm:object">
    <span class="formater-object">
      <xsl:text>Object[</xsl:text>
      <xsl:value-of select="@class"/>
      <xsl:text>](</xsl:text>
      <xsl:apply-templates/>
      <xsl:text>)</xsl:text>
    </span>
  </xsl:template>

  <xsl:template match="frm:item">
    <xsl:apply-templates select="frm:key"/>
    <xsl:text> => </xsl:text>
    <xsl:apply-templates select="frm:value"/>
  </xsl:template>

  <xsl:template match="frm:string">
    <span class="formater-string"><xsl:value-of select="."/></span>
  </xsl:template>

  <xsl:template match="frm:numeric">
    <span class="formater-numeric"><xsl:value-of select="."/></span>
  </xsl:template>

  <xsl:template match="frm:null">
    <span class="formater-null">[NULL]</span>
  </xsl:template>

  <xsl:template match="frm:boolean">
    <span class="formater-boolean"><xsl:value-of select="."/></span>
  </xsl:template>

</xsl:stylesheet>
