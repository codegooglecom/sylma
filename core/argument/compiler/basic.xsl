<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://www.sylma.org/parser/action/compiler" version="1.0">

  <xsl:include href="/#sylma/parser/languages/php/source.xsl"/>

  <xsl:template match="php:window">&lt;?php
    return <xsl:apply-templates select="php:array"/>
  </xsl:template>

  <xsl:template match="*">
    <php:item>
      <xsl:choose>
        <xsl:when test="* | @*">
          <php:key><xsl:value-of select="local-name()"/></php:key>
          <php:array>
            <xsl:apply-templates/>
          </php:array>
        </xsl:when>
        <xsl:otherwise>
          <php:key><xsl:value-of select="local-name()"/></php:key>
          <php:value><xsl:value-of select="."/></php:value>
        </xsl:otherwise>
      </xsl:choose>
    </php:item>
  </xsl:template>

</xsl:stylesheet>
