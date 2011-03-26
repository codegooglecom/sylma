<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:lx="http://ns.sylma.org/xslt" xmlns:func="http://exslt.org/functions" xmlns:exsl="http://exslt.org/common" version="1.0" extension-element-prefixes="lx func exsl">
  <func:function name="lx:string-resume">
    <xsl:param name="string"/>
    <xsl:param name="max-length"/>
    <xsl:param name="search" select="' '"/>
    <xsl:param name="word-length" select="20"/>
    <xsl:choose>
      <xsl:when test="string-length($string) &gt; $max-length">
        <xsl:variable name="r-string" select="substring($string, 1, $max-length)"/>
        <xsl:variable name="first-string" select="substring($r-string, 1, $max-length - $word-length)"/>
        <xsl:variable name="last-string" select="substring($r-string, $max-length - $word-length + 1)"/>
        <xsl:variable name="last-formated">
          <xsl:value-of select="lx:substring-before-last($last-string, $search)"/>
        </xsl:variable>
        <func:result select="concat($first-string, $last-formated, ' ...')"/>
      </xsl:when>
      <xsl:otherwise>
        <func:result select="$string"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  <func:function name="lx:substring-before-last">
    <xsl:param name="string"/>
    <xsl:param name="search"/>
    <xsl:variable name="after-string">
      <xsl:value-of select="lx:substring-after-last($string, $search)"/>
    </xsl:variable>
    <func:result select="substring($string, 1, string-length($string) - string-length($after-string) - 1)"/>
  </func:function>
  <func:function name="lx:substring-after-last">
    <xsl:param name="string"/>
    <xsl:param name="search"/>
    <xsl:choose>
      <xsl:when test="contains($string, $search)">
        <func:result select="lx:substring-after-last(substring-after($string, $search), $search)"/>
      </xsl:when>
      <xsl:otherwise>
        <func:result select="$string"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  <func:function name="lx:split">
    <xsl:param name="string" select="''"/>
    <xsl:param name="pattern" select="' '"/>
    <xsl:choose>
      <xsl:when test="not($string)">
        <func:result select="/.."/>
      </xsl:when>
      <xsl:when test="not(function-available('exsl:node-set'))">
        <xsl:message terminate="yes">

        ERROR: EXSLT - Functions implementation of lx:split relies on exsl:node-set().
      
</xsl:message>
      </xsl:when>
      <xsl:otherwise>
        <xsl:variable name="tokens">
          <xsl:choose>
            <xsl:when test="not($pattern)">
              <xsl:call-template name="lx:_split-characters">
                <xsl:with-param name="string" select="$string"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
              <xsl:call-template name="lx:_split-pattern">
                <xsl:with-param name="string" select="$string"/>
                <xsl:with-param name="pattern" select="$pattern"/>
              </xsl:call-template>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <func:result select="exsl:node-set($tokens)/*"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  <xsl:template name="lx:_split-characters">
    <xsl:param name="string"/>
    <xsl:if test="$string">
      <token>
        <xsl:value-of select="substring($string, 1, 1)"/>
      </token>
      <xsl:call-template name="lx:_split-characters">
        <xsl:with-param name="string" select="substring($string, 2)"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>
  <xsl:template name="lx:_split-pattern">
    <xsl:param name="string"/>
    <xsl:param name="pattern"/>
    <xsl:choose>
      <xsl:when test="contains($string, $pattern)">
        <xsl:if test="not(starts-with($string, $pattern))">
          <token>
            <xsl:value-of select="substring-before($string, $pattern)"/>
          </token>
        </xsl:if>
        <xsl:call-template name="lx:_split-pattern">
          <xsl:with-param name="string" select="substring-after($string, $pattern)"/>
          <xsl:with-param name="pattern" select="$pattern"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <token>
          <xsl:value-of select="$string"/>
        </token>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <func:function name="lx:to-upper">
    <xsl:param name="str"/>
    <xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
    <xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
    <func:result select="translate($str,$lower,$upper)"/>
  </func:function>
  <func:function name="lx:first-case">
    <xsl:param name="str"/>
    <xsl:variable name="firstLetter" select="substring($str,1,1)"/>
    <xsl:variable name="restString" select="substring($str,2,string-length($str))"/>
    <xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
    <xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
    <xsl:variable name="translate" select="translate($firstLetter,$lower,$upper)"/>
    <func:result select="concat($translate,$restString)"/>
  </func:function>
</xsl:stylesheet>
