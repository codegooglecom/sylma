<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:frm="http://www.sylma.org/dom/handler" version="1.0">
  
  <xsl:template match="/">
    <xsl:apply-templates select="frm:*"/>
  </xsl:template>
  
  <xsl:template match="dom:handler">
    <div class="element">
      <span><xsl:value-of select="@class"/></span>
      <xsl:choose>
        <xsl:when test="frm:content">
          <pre class="hidden"><value-of select="frm:content"/></pre>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text> [empty]</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </div>
  </xsl:template>

</xsl:stylesheet>
