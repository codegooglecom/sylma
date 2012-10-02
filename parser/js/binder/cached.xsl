<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:la="http://www.sylma.org/parser/js/binder/cached" xmlns:php="test" version="1.0">

  <xsl:template match="/*">
    <la:window>
      <la:objects>
        <xsl:apply-templates/>
      </la:objects>
      <la:render>
        <xsl:apply-templates select="." mode="copy"/>
      </la:render>
    </la:window>
  </xsl:template>

  <xsl:template match="*">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="la:*">
    <xsl:element name="{name()}" namespace="{namespace-uri()}">
      <xsl:apply-templates select="node() | @*"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="@*">
    <xsl:copy/>
  </xsl:template>

  <xsl:template match="text()">
  </xsl:template>

  <xsl:template match="*" mode="copy">
    <xsl:element name="{local-name()}" namespace="{namespace-uri()}">
      <xsl:apply-templates select="node() | @*" mode="copy"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="la:*" mode="copy">
    <xsl:apply-templates mode="copy"/>
  </xsl:template>

  <xsl:template match="@*" mode="copy">
    <xsl:copy/>
  </xsl:template>

  <xsl:template match="text()" mode="copy">
    <xsl:copy/>
  </xsl:template>

</xsl:stylesheet>
