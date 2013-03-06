<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ls="http://www.sylma.org/security" xmlns:php="http://www.sylma.org/parser/languages/php" version="1.0">

  <xsl:output method="text"/>

<xsl:variable name="break">
<xsl:text>
</xsl:text>
</xsl:variable>

  <xsl:template match="php:window">&lt;?php
    <xsl:apply-templates select="*"/>
  </xsl:template>

  <xsl:template match="php:*">
    $this->throwException(sprintf('Invalid template\'s %s', '@element <xsl:value-of select="concat(namespace-uri(), ':', local-name())"/>'))
  </xsl:template>

  <xsl:template match="php:assign">
    <xsl:call-template name="php:assign">
      <xsl:with-param name="variable" select="php:variable/*"/>
      <xsl:with-param name="value" select="php:value/*"/>
      <xsl:with-param name="prefix" select="php:prefix"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template match="php:line">
    <xsl:apply-templates/>;
  </xsl:template>

  <xsl:template match="php:instanciate">
    <xsl:text>new </xsl:text>
    <xsl:value-of select="@class"/>
    <xsl:text>(</xsl:text>
    <xsl:call-template name="php:arguments"/>
    <xsl:text>)</xsl:text>
  </xsl:template>

  <xsl:template match="php:condition">
    <xsl:text>if (</xsl:text>
    <xsl:apply-templates select="php:test/*"/>
    <xsl:text>) {</xsl:text>
    <xsl:value-of select="$break"/>
    <xsl:apply-templates select="php:content/*"/>
    <xsl:value-of select="$break"/>
    <xsl:text>}</xsl:text>
  </xsl:template>

  <xsl:template match="php:foreach">
    <xsl:text>foreach (</xsl:text>
    <xsl:apply-templates select="php:looped/*"/> as <xsl:apply-templates select="php:var/*"/>
    <xsl:text>) {</xsl:text>
    <xsl:value-of select="$break"/>
    <xsl:apply-templates select="php:content/*"/>
    <xsl:text>}</xsl:text>
  </xsl:template>

  <xsl:template name="php:call">
    <xsl:text>(</xsl:text>
    <xsl:call-template name="php:arguments"/>
    <xsl:text>)</xsl:text>
  </xsl:template>

  <xsl:template match="php:call">
    <xsl:apply-templates select="php:called"/>
    <xsl:call-template name="php:call"/>
  </xsl:template>

  <xsl:template match="php:call-function">
    <xsl:value-of select="@name"/>
    <xsl:call-template name="php:call"/>
  </xsl:template>

  <xsl:template match="php:closure">
    <xsl:text>function(</xsl:text>
    <xsl:call-template name="php:arguments"/>
    <xsl:text>) {</xsl:text>
    <xsl:value-of select="$break"/>
    <xsl:apply-templates select="php:content/*"/>
    <xsl:text>}</xsl:text>
  </xsl:template>

  <xsl:template match="php:return">
    <xsl:text>return </xsl:text>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="php:class">
    <xsl:value-of select="."/>
  </xsl:template>

  <xsl:template name="php:assign">
    <xsl:param name="variable"/>
    <xsl:param name="value"/>
    <xsl:param name="prefix"/>
    <xsl:apply-templates select="$variable"/> <xsl:value-of select="$prefix"/>= <xsl:apply-templates select="$value"/>
  </xsl:template>

  <xsl:template match="php:call-method">
    <xsl:apply-templates select="php:called"/>
    <xsl:choose>
      <xsl:when test="@static">::</xsl:when>
      <xsl:otherwise>-&gt;</xsl:otherwise>
    </xsl:choose>
    <xsl:apply-templates select="@name"/>
    <xsl:call-template name="php:call"/>
  </xsl:template>

  <xsl:template match="php:called">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="php:var">
    <xsl:text>$</xsl:text>
    <xsl:value-of select="@name"/>
  </xsl:template>

  <xsl:template match="php:argument">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template name="php:arguments">
    <xsl:for-each select="php:argument">
      <xsl:apply-templates select="."/>
      <xsl:if test="following-sibling::php:argument">, </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="php:code">
    <xsl:value-of select="."/>
  </xsl:template>

  <xsl:template match="php:test">
    <xsl:apply-templates select="php:val1/*"/>
    <xsl:value-of select="concat(' ', @operator, ' ')"/>
    <xsl:apply-templates select="php:val2/*"/>
  </xsl:template>

  <xsl:template match="php:string">
    <xsl:text>'</xsl:text>
    <xsl:value-of select="."/>
    <xsl:text>'</xsl:text>
  </xsl:template>

  <xsl:template match="php:not">
    <xsl:text>!(</xsl:text>
    <xsl:apply-templates/>
    <xsl:text>)</xsl:text>
  </xsl:template>

  <xsl:template match="php:numeric">
    <xsl:value-of select="."/>
  </xsl:template>

  <xsl:template match="php:boolean">
    <xsl:choose>
      <xsl:when test="@value">
        <xsl:value-of select="@value"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>(bool) </xsl:text><xsl:apply-templates/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="php:cast">
    <xsl:value-of select="concat('(', @type, ')')"/>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="php:concat">
    <xsl:for-each select="*">
      <xsl:apply-templates select="."/>
      <xsl:if test="position() != last()"> . </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="php:null">NULL</xsl:template>

  <xsl:template match="php:array">
    <xsl:text>array(</xsl:text>
    <xsl:for-each select="php:item">
      <xsl:apply-templates select=".">
        <xsl:with-param name="assoc" select="../@associative"/>
      </xsl:apply-templates>
      <xsl:if test="position() != last()">, </xsl:if>
    </xsl:for-each>
    <xsl:text>)</xsl:text>
  </xsl:template>

  <xsl:template match="php:item">
    <xsl:param name="assoc"/>
    <xsl:if test="$assoc">
      <xsl:apply-templates select="@key"/>
      <xsl:text> => </xsl:text>
    </xsl:if>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="@key">
    <xsl:choose>
      <xsl:when test="number(@key)"><xsl:value-of select="."/></xsl:when>
      <xsl:otherwise>'<xsl:value-of select="."/>'</xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
