<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" xmlns:lc="http://www.sylma.org/schemas" xmlns:lx="http://ns.sylma.org/xslt" xmlns:dbx="http://www.sylma.org/modules/dbx" xmlns:la="http://www.sylma.org/processors/action-builder" version="1.0" extension-element-prefixes="func lx dbx">
  
  <xsl:import href="/sylma/schemas/functions.xsl"/>
  <xsl:import href="/sylma/xslt/string.xsl"/>
  
  <xsl:param name="model"/>
  <xsl:param name="module"/>
  <xsl:variable name="doc-model" select="document($model)"/>
  
  <func:function name="dbx:get-element">
    <xsl:param name="parent"/>
    <xsl:choose>
      <xsl:when test="$parent">
        <func:result select="$parent/*[local-name() = current()/@name]"/>
      </xsl:when>
      <xsl:otherwise>
        <func:result select="/*/*[1]/*[local-name() = current()/@name]"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  <xsl:template match="/*">
    <thead>
      <tr>
        <th class="tools"> </th>
        <xsl:variable name="n-order" select="*[3]/dbx:order"/>
        <xsl:variable name="order" select="$n-order/."/>
        <xsl:variable name="order-dir">
          <xsl:choose>
            <xsl:when test="*[3]/dbx:order/@dir != 'a'">a</xsl:when>
            <xsl:otherwise>d</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:apply-templates select="*[3]/dbx:element">
          <xsl:with-param name="order" select="$order"/>
          <xsl:with-param name="order-dir" select="$order-dir"/>
        </xsl:apply-templates>
      </tr>
    </thead>
  </xsl:template>
  <xsl:template match="dbx:element">
    <xsl:param name="order"/>
    <xsl:param name="order-dir"/>
    <xsl:param name="parent"/>
    <xsl:variable name="element" select="dbx:get-element($parent)"/>
    <xsl:choose>
      <xsl:when test="dbx:element">
        <xsl:apply-templates select="dbx:element">
          <xsl:with-param name="order" select="$order"/>
          <xsl:with-param name="order-dir" select="$order-dir"/>
          <xsl:with-param name="parent" select="$element"/>
        </xsl:apply-templates>
      </xsl:when>
      <xsl:otherwise>
        <th>
          <la:layer key="{@name}">
            <la:property name="element">
              <xsl:value-of select="@name"/>
            </la:property>
            <a href="#">
              <xsl:if test="$order = @name">
                <xsl:attribute name="class">current</xsl:attribute>
              </xsl:if>
              <la:event name="click"><![CDATA[return %ref-object%.rootObject.updateOrder(%ref-object%.element);]]></la:event>
              <xsl:if test="$element">
                <xsl:value-of select="lx:first-case(lc:get-title($element))"/>
              </xsl:if>
            </a>
          </la:layer>
        </th>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>
