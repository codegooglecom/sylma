<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:js="http://www.sylma.org/parser/languages/js" version="1.0">

  <xsl:template match="js:window">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="js:*">
    throw 'Cannot handle element : <xsl:value-of select="concat(namespace-uri(), ':', local-name())"/>';
  </xsl:template>

  <xsl:template match="js:assign">
    <xsl:call-template name="js:assign">
      <xsl:with-param name="variable" select="js:variable/*"/>
      <xsl:with-param name="value" select="js:value/*"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="js:assign">
    <xsl:param name="variable"/>
    <xsl:param name="value"/>
    <xsl:apply-templates select="$variable"/> = <xsl:apply-templates select="$value"/>
  </xsl:template>

  <xsl:template match="js:instruction">
    <xsl:apply-templates/>;
  </xsl:template>

  <xsl:template match="js:instanciate">
    <xsl:text>new </xsl:text>
    <xsl:value-of select="@class"/>
    <xsl:text>(</xsl:text>
    <xsl:apply-templates select="js:argument/*"/>
    <xsl:text>)</xsl:text>
  </xsl:template>

  <xsl:template match="js:object">
    <xsl:text>{</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>}</xsl:text>
  </xsl:template>

  <xsl:template match="js:items">
    <xsl:for-each select="js:item">
      <xsl:apply-templates select="."/>
      <xsl:if test="position() != last()">, </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="js:concat">
    <xsl:for-each select="*">
      <xsl:apply-templates select="."/>
      <xsl:if test="position() != last()"> + </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="js:item">
    <xsl:value-of select="@key"/>
    <xsl:text> : </xsl:text>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="js:condition">
    <xsl:text>if (</xsl:text>
    <xsl:apply-templates select="js:test/*"/>
    <xsl:text>) {</xsl:text>
    <xsl:value-of select="$break"/>
    <xsl:apply-templates select="js:content/*"/>
    <xsl:value-of select="$break"/>
    <xsl:text>}</xsl:text>
    <xsl:value-of select="$break"/>
  </xsl:template>

  <xsl:template match="js:function">
    <xsl:text>function(</xsl:text>
    <xsl:apply-templates select="js:arguments"/>
    <xsl:text>) {</xsl:text>
    <xsl:value-of select="$break"/>
    <xsl:apply-templates select="js:content/*"/>
    <xsl:text>}</xsl:text>
  </xsl:template>

  <xsl:template match="js:return">
    <xsl:text>return </xsl:text>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="js:property">
    <xsl:apply-templates/>
    <xsl:text>.</xsl:text>
    <xsl:apply-templates select="@name"/>
  </xsl:template>

  <xsl:template match="js:call">
    <xsl:apply-templates select="js:called"/>
    <xsl:text>(</xsl:text>
    <xsl:call-template name="js:arguments"/>
    <xsl:text>)</xsl:text>
  </xsl:template>

  <xsl:template match="js:called">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="js:variable">
    <xsl:value-of select="@name"/>
  </xsl:template>

  <xsl:template match="js:argument">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template name="js:arguments" match="js:arguments">
    <xsl:for-each select="js:argument">
      <xsl:apply-templates select="."/>
      <xsl:if test="following-sibling::js:argument">, </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="js:code">
    <xsl:value-of select="."/>
  </xsl:template>

  <xsl:template match="js:test">
    <xsl:apply-templates select="js:val1/*"/>
    <xsl:value-of select="concat(' ', @operator, ' ')"/>
    <xsl:apply-templates select="js:val2/*"/>
  </xsl:template>

  <xsl:template match="js:string">
    <xsl:text>'</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>'</xsl:text>
  </xsl:template>

  <xsl:template match="js:not">
    <xsl:text>!(</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>)</xsl:text>
  </xsl:template>

  <xsl:template match="js:numeric">
    <xsl:value-of select="."/>
  </xsl:template>

  <xsl:template match="js:boolean">
    <xsl:value-of select="@value"/>
  </xsl:template>

  <xsl:template match="js:concat">
    <xsl:for-each select="*">
      <xsl:apply-templates select="."/>
      <xsl:if test="position() != last()"> + </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="js:null">null</xsl:template>

  <xsl:template match="js:array">
    <xsl:text>[</xsl:text>
    <xsl:for-each select="js:item">
      <xsl:apply-templates select="."/>
      <xsl:if test="position() != last()">, </xsl:if>
    </xsl:for-each>
    <xsl:text>]</xsl:text>
  </xsl:template>

  <xsl:template match="@key">
    <xsl:choose>
      <xsl:when test="number(@key)"><xsl:value-of select="."/></xsl:when>
      <xsl:otherwise>'<xsl:value-of select="."/>'</xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
