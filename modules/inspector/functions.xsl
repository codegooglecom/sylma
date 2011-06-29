<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" extension-element-prefixes="func" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ins="http://www.sylma.org/modules/inspector" xmlns:func="http://exslt.org/functions" xmlns:set="http://exslt.org/sets">
  
	<func:function name="ins:implode">
    <xsl:param name="items" />
    <xsl:param name="separator" select="', '" />
    <xsl:if test="$items">
	    <func:result>
		    <xsl:for-each select="$items">
		      <xsl:if test="position() &gt; 1">
		        <xsl:value-of select="$separator" />
		      </xsl:if>
		      <xsl:apply-templates select="." />
		    </xsl:for-each>
	    </func:result>
	  </xsl:if>
	</func:function>
	
</xsl:stylesheet>

