<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:lc="http://www.sylma.org/schemas" xmlns:dbx="http://www.sylma.org/modules/dbx" xmlns:func="http://exslt.org/functions"  extension-element-prefixes="func" version="1.0">
  <xsl:param name="build-empty" select="boolean(false)"/>
  <xsl:output method="text"/>
  <xsl:import href="../../schemas/functions.xsl"/>
  
  <func:function name="dbx:get-ref">
    <xsl:param name="source" select="current()"/>
    <func:result select="/*/*[2]/lc:key-ref[@name = $source/@name]"/>
  </func:function>
  
  <func:function name="dbx:get-view">
    <xsl:param name="source" select="current()"/>
    <xsl:variable name="ref" select="dbx:get-ref($source)"/>
    <xsl:choose>
      <xsl:when test="$source/@view"><func:result select="concat('/', $source/@view)"/></xsl:when>
      <xsl:when test="$ref/@lc:ref-view"><func:result select="concat('/', $ref/@lc:ref-view)"/></xsl:when>
    </xsl:choose>
  </func:function>
  
  <xsl:template match="/*">
    <xsl:call-template name="functions"/>
element <xsl:value-of select="$parent-name"/> {
    <xsl:call-template name="loop"/>
}
  </xsl:template>
  
  <xsl:template name="functions">
    declare function local:buildEmpty($el as element()?, $name as xs:string) as element() {
      
      if ($el)
        then $el
        else element {$name} {}
    };
  </xsl:template>
  
  <xsl:template name="loop">
    <xsl:variable name="headers" select="*[3]"/>
  for $self in <xsl:value-of select="$parent-path"/>
    <xsl:apply-templates select="$headers/dbx:element" mode="prepare"/>
    <xsl:variable name="where">
      <xsl:apply-templates select="$headers/dbx:filter"/>
    </xsl:variable>
    <xsl:if test="$where != ''">
    where <xsl:value-of select="$where"/></xsl:if>
    <xsl:variable name="order">
      <xsl:apply-templates select="$headers/dbx:order"/>
    </xsl:variable>
    <xsl:if test="$order != ''">
    order by <xsl:value-of select="$order"/></xsl:if>
    return element {name($self)} {
      $self/@*, 
      <xsl:apply-templates select="$headers/dbx:element" mode="result"/>
    }
    
  </xsl:template>
  
  <xsl:template match="dbx:element" mode="prepare">
    <xsl:param name="parent" select="''"/>
    <xsl:variable name="name" select="concat($prefix, @name)"/>
    <xsl:choose>
      <xsl:when test="dbx:element">
        <xsl:apply-templates select="dbx:element" mode="prepare">
          <xsl:with-param name="parent" select="concat($parent, '/', $name)"/>
        </xsl:apply-templates>
      </xsl:when>
      <xsl:otherwise>
        <xsl:variable name="ref" select="dbx:get-ref()"/>
        <xsl:if test="$ref">
          let $sylma-<xsl:value-of select="concat(@name, ' := ', $ref/.)"/>
          <xsl:choose>
            <xsl:when test="$ref/@key-constrain"><xsl:value-of select="$ref/@key-constrain"/></xsl:when>
            <xsl:otherwise>[@key = $self/<xsl:value-of select="concat($prefix, @name)"/>/text()][1]</xsl:otherwise>
          </xsl:choose>
        </xsl:if>
        <xsl:if test="@path">
          let $sylma-<xsl:value-of select="concat(@name, ' := $self/', @path)"/>
        </xsl:if>
        <xsl:if test="@transform">
          let $sylma-<xsl:value-of select="concat(@name, ' := ', @transform)"/>
        </xsl:if>
        <xsl:if test="$parent != ''">
          let $sylma-<xsl:value-of select="concat(@name, ' := $self', $parent, '/', $name, '/text()')"/>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <func:function name="dbx:get-op">
    <xsl:param name="source" select="."/>
    <xsl:param name="default" select="'and'"/>
    <xsl:choose>
      <xsl:when test="$source"><func:result select="$source/."/></xsl:when>
      <xsl:otherwise><func:result select="$default"/></xsl:otherwise>
    </xsl:choose>
  </func:function>
  
  <xsl:template match="dbx:filter">
    <xsl:param name="op" select="''"/>
    ( 
    <xsl:apply-templates select="dbx:where">
      <xsl:with-param name="op" select="dbx:get-op(@op)"/>
    </xsl:apply-templates>
    <xsl:apply-templates select="dbx:filter">
      <xsl:with-param name="op" select="dbx:get-op(@op)"/>
    </xsl:apply-templates>
    ) <xsl:if test="$op and not(position() = last())"><xsl:value-of select="concat($op, ' ')"/></xsl:if>
  </xsl:template>
  
  <xsl:template match="dbx:where">
    <xsl:param name="op"/>
    <xsl:value-of select="concat('(', @test, ')')"/>
    <xsl:if test="not(position() = last())"><xsl:value-of select="concat(' ', $op, ' ')"/></xsl:if>
  </xsl:template>
  
  <xsl:template match="dbx:order">
    <xsl:variable name="order-dir">
      <xsl:choose>
        <xsl:when test="@dir = 'a'">ascending</xsl:when>
        <xsl:otherwise>descending</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="element" select="/*/*[1]/*[local-name() = current()]"/>
    <xsl:variable name="header" select="/*/*[3]/*[@name = current()/.]"/>
    <xsl:variable name="order-name">
      <xsl:choose>
        <xsl:when test="$element/@lc:key-ref">
          <xsl:value-of select="concat('$sylma-', ., dbx:get-view($header))"/>
        </xsl:when>
        <xsl:otherwise><xsl:value-of select="concat('$self/', $prefix, .)"/></xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="order-call">
      <xsl:variable name="type" select="lc:get-type($element)"/>
      <xsl:choose>
        <xsl:when test="not($element/@lc:key-ref) and $type">
          <xsl:variable name="order-typed" select="concat($type, '(', $order-name, ')')"/>
          <xsl:choose>
            <xsl:when test="$type = 'xs:string'"><xsl:value-of select="concat('upper-case(', $order-typed, ')')"/></xsl:when>
            <xsl:otherwise><xsl:value-of select="$order-typed"/></xsl:otherwise>
          </xsl:choose>
        </xsl:when>
        <xsl:otherwise><xsl:value-of select="concat('upper-case(', $order-name, ')')"/></xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:value-of select="concat($order-call, ' ', $order-dir)"/>
  </xsl:template>
  
  <xsl:template match="dbx:element" mode="result">
    <xsl:param name="parent" select="''"/>
    <xsl:variable name="name" select="concat($prefix, @name)"/>
    <xsl:variable name="ref" select="dbx:get-ref()"/>
    <xsl:choose>
      <xsl:when test="@copy-node = 'true'">
        element <xsl:value-of select="$name"/> {
          for $sub in $self/<xsl:value-of select="$name"/>/node()
            return typeswitch($sub)
              case element() return if ($sub/*)
                then concat('&lt;', local-name($sub), '&gt;', $sub/*, '&lt;/', local-name($sub), '&gt;')
                else concat('&lt;', local-name($sub), '/&gt;')
              default return $sub
        }
      </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <xsl:when test="$ref">
            element <xsl:value-of select="$name"/> {
              attribute lc:value {$self/<xsl:value-of select="$name"/>/text()},
              xs:string($sylma-<xsl:value-of select="concat(@name, dbx:get-view())"/>)}
          </xsl:when>
          <xsl:when test="@path or @transform or $parent">
            element <xsl:value-of select="$name"/> { $sylma-<xsl:value-of select="@name"/> }
          </xsl:when>
          <xsl:when test="dbx:element">
            element <xsl:value-of select="$name"/> { 
              <xsl:apply-templates select="dbx:element" mode="result">
                <xsl:with-param name="parent" select="concat($parent, '/', $name)"/>
              </xsl:apply-templates>
            }
          </xsl:when>
          <xsl:otherwise>
            <xsl:choose>
              <xsl:when test="$build-empty">local:buildEmpty(<xsl:value-of select="concat('$self/', $name, ', &quot;', $name, '&quot;')"/>)</xsl:when>
              <xsl:otherwise><xsl:value-of select="concat('$self/', $name)"/></xsl:otherwise>
            </xsl:choose>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:if test="not(position() = last())">,</xsl:if>
  </xsl:template>
</xsl:stylesheet>
