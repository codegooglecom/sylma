<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ins="http://www.sylma.org/modules/inspector">
  <xsl:param name="inspect" select="concat($sylma-directory, '/class/')"/>
	<xsl:template match="/*">
		<div>
		  <h2><xsl:value-of select="@name"/></h2>
		  <xsl:if test="ins:extension">
		    <p>
		      <strong>Extends </strong>
		      <a href="{$inspect}{ins:extension}">
		        <xsl:value-of select="ins:extension"/>
	        </a>
		    </p>
	    </xsl:if>
	    <xsl:if test="ins:interface">
	      <p>
	        <strong>Implements</strong>
	        <xsl:apply-templates select="ins:interface"/>
	      </p>
      </xsl:if>
      <xsl:if test="ins:property">
        <p>
          <h3>Properties</h3>
          <xsl:apply-templates select="ins:property"/>
        </p>
      </xsl:if>
      <xsl:if test="ins:constant">
        <p>
          <h3>Constants</h3>
          <xsl:apply-templates select="ins:constant"/>
        </p>
      </xsl:if>
      <xsl:if test="ins:method">
        <p>
          <h3>Methods</h3>
          <xsl:for-each select="ins:method">
            <xsl:sort select="@class"/>
            <xsl:apply-templates select="."/>
          </xsl:for-each>
        </p>
      </xsl:if>
		</div>
	</xsl:template>
	
	<xsl:template match="ins:interface">
    <a href="{$inspect}{.}"><xsl:value-of select="."/></a>
	</xsl:template>
	
	<xsl:template match="ins:modifiers">
	  	 
	</xsl:template>
	
	<xsl:template match="ins:constant">
	  <p>
      <strong><xsl:value-of select="@name"/></strong> = 
      <span><xsl:value-of select="ins:default"/></span>
    </p>
	</xsl:template>
	
	<xsl:template match="ins:property">
	  <p>
	    <xsl:apply-templates select="ins:modifiers"/>
      <strong><xsl:value-of select="@name"/></strong>
      <xsl:if test="ins:default">
         = <span><xsl:value-of select="ins:default"/></span>
      </xsl:if>
    </p>
	</xsl:template>
	
	<xsl:template match="ins:method">
    <p>
      <strong><xsl:value-of select="@name"/></strong>
      <span>(<xsl:apply-templates select="ins:parameter"/>)</span>
      <xsl:if test="@class">
        <span>from <xsl:value-of select="@class"/></span>
      </xsl:if>
    </p>
	</xsl:template>
	
	<xsl:template match="ins:parameter">
	  <xsl:apply-templates select="ins:cast"/>
	  <span>$<xsl:value-of select="@name"/></span>
	  <xsl:if test="ins:default">
	    = <span><xsl:value-of select="ins:default"/></span>
	  </xsl:if>
	</xsl:template>
	
	<xsl:template match="ins:cast">
    <xsl:choose>
      <xsl:when test=". = 'array'">
        <span>array</span>
      </xsl:when>
      <xsl:otherwise>
        <a href="{$inspect}{.}"><xsl:value-of select="."/></a>
      </xsl:otherwise>
    </xsl:choose>
	</xsl:template>
	
</xsl:stylesheet>