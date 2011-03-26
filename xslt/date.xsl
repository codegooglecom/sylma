<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:lx="http://ns.sylma.org/xslt" xmlns:func="http://exslt.org/functions" xmlns:exsl="http://exslt.org/common" version="1.0" extension-element-prefixes="lx func exsl">
  <xsl:import href="date-fr.xsl"/>
  <func:function name="lx:format-date">
    <xsl:param name="date"/>
    <xsl:param name="from" select="'default'"/>
    <xsl:param name="to" select="'default'"/>
    <func:result>
      <xsl:choose>
        <xsl:when test="$from = 'drupal'">
          <xsl:variable select="substring($date, 9, 3)" name="abbr-month"/>
          <xsl:variable name="s-month" select="lx:get-month($abbr-month)"/>
          <xsl:call-template name="lx:_format-date">
            <xsl:with-param select="substring($date, 6, 2)" name="day"/>
            <xsl:with-param select="$s-month" name="s-month"/>
            <xsl:with-param name="i-month" select="position($s-month)"/>
            <xsl:with-param select="substring($date, 13, 4)" name="year"/>
            <xsl:with-param name="to" select="$to"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:variable name="i-month" select="substring($date, 6, 2)"/>
          <xsl:call-template name="lx:_format-date">
            <xsl:with-param select="substring($date, 9, 2)" name="day"/>
            <xsl:with-param select="lx:get-month($i-month)" name="s-month"/>
            <xsl:with-param name="i-month" select="$i-month"/>
            <xsl:with-param select="substring($date, 1, 4)" name="year"/>
            <xsl:with-param name="to" select="$to"/>
          </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
    </func:result>
  </func:function>
  <xsl:template name="lx:_format-date">
    <xsl:param name="day"/>
    <xsl:param name="i-month"/>
    <xsl:param name="s-month"/>
    <xsl:param name="year"/>
    <xsl:param name="to"/>
    <xsl:choose>
      <xsl:when test="$to = 'simple-full-year'">
        <xsl:value-of select="concat($day, '.', $i-month, '.', $year)"/>
      </xsl:when>
      <xsl:when test="$to = 'simple'">
        <xsl:value-of select="concat($day, '.', $i-month, '.', substring($year, 3, 2))"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="concat($day, ' ', $s-month, ' ', $year)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>
