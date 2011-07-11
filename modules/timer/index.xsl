<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" extension-element-prefixes="func" xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:time="http://www.sylma.org/modules/utils/timer" xmlns:func="http://exslt.org/functions" xmlns:set="http://exslt.org/sets">
  
  <xsl:param name="weight" select="500"/>
  
  <xsl:variable name="class-weight">sylma-timer-weight</xsl:variable>
  
	<xsl:template match="/time:classes">
    <xsl:apply-templates/>
  </xsl:template>
  
	<xsl:template match="time:class">
    <xsl:variable name="total" select="sum(.//time:time)"/>
    <h3><xsl:value-of select="@name"/><em><xsl:value-of select="ceiling($total * 1000)"/> ms</em></h3>
    <ul>
      <xsl:apply-templates>
        <xsl:with-param name="avg" select="1 div ($total div count(*)) * ($weight div count(*))"/>
      </xsl:apply-templates>
    </ul>
  </xsl:template>
  
  <xsl:template match="time:method">
    <xsl:param name="avg"/>
    <li>
      <xsl:value-of select="@name"/>
      <em>
        <xsl:value-of select="time:calls"/> calls - 
        <xsl:value-of select="concat(ceiling(time:time * 1000), ' ms')"/>
      </em>
      <xsl:call-template name="time:weight">
        <xsl:with-param name="value" select="time:time"/>
        <xsl:with-param name="avg" select="$avg"/>
      </xsl:call-template>
    </li>
  </xsl:template>
  
  <func:function name="time:abs">
    <xsl:param name="number"/>
    <func:result select="$number * ($number &gt;= 0) - $number * ($number &lt; 0)"/>
  </func:function>
  
  <xsl:template name="time:weight">
    <xsl:param name="value"/>
    <xsl:param name="avg"/>
    <div style="width: {ceiling(time:abs(time:time * $avg))}px;" class="{$class-weight}"/>
  </xsl:template>
  
</xsl:stylesheet>

