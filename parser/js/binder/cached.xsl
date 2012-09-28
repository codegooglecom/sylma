<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:la="http://www.sylma.org/parser/js/binder/cached" version="1.0">

  <xsl:template match="/*">
    <la:window>
      <xsl:apply-templates/>
    </la:window>
  </xsl:template>

  <xsl:template match="text()">
  </xsl:template>

  <xsl:template match="@*">
    <xsl:copy/>
  </xsl:template>

  <xsl:template match="*">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="la:*">
    <xsl:element name="{name()}">
      <xsl:apply-templates select="* | @*"/>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
