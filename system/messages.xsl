<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:lm="http://www.sylma.org/messages" version="1.0">
  <xsl:template match="messages">
    <div class="messages">
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  <xsl:template match="/*//*">
    <xsl:choose>
      <xsl:when test="lm:message">
        <ul>
          <xsl:attribute name="class">
            <xsl:call-template name="implode">
              <xsl:with-param name="items" select="ancestor::*"/>
            </xsl:call-template>
            <xsl:value-of select="concat(' message-', name())"/>
          </xsl:attribute>
          <xsl:apply-templates/>
        </ul>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template match="//lm:message">
    <li>
      <xsl:copy-of select="content/node()"/>
    </li>
  </xsl:template>
  <xsl:template name="implode">
    <xsl:param name="items"/>
    <xsl:for-each select="$items">
      <xsl:if test="position() &gt; 1">
        <xsl:value-of select="'-'"/>
      </xsl:if>
      <xsl:value-of select="name()"/>
    </xsl:for-each>
  </xsl:template>
</xsl:stylesheet>
