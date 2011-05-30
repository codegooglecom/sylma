<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" extension-element-prefixes="func" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ins="http://www.sylma.org/modules/inspector" xmlns:func="http://exslt.org/functions" xmlns:set="http://exslt.org/sets">
  <xsl:param name="inspect" select="concat($sylma-directory, '/class/')"/>
	<xsl:template match="/*">
		<div>
		  <h2><xsl:value-of select="@name"/></h2>
		  <p>
		    <xsl:value-of select="ins:comment"/>
		  </p>
		  <xsl:if test="ins:extension">
		    <p>
		      <strong>Extends </strong>
		      <xsl:for-each select="ins:extension//ins:class">
		        <xsl:copy-of select="ins:get-class(@name)"/>
	        </xsl:for-each>
		    </p>
	    </xsl:if>
	    <xsl:if test=".//ins:interface">
	      <p>
	        <strong>Implements </strong>
	        <xsl:apply-templates select="set:distinct(.//ins:interface)"/>
	      </p>
      </xsl:if>
      <xsl:if test=".//ins:constant">
        <ul>
          <h3>Constants</h3>
          <xsl:apply-templates select=".//ins:constant"/>
        </ul>
      </xsl:if>
      <xsl:if test=".//ins:property">
        <ul>
          <h3>Properties</h3>
          <xsl:apply-templates select=".//ins:property"/>
        </ul>
      </xsl:if>
      <xsl:if test="//ins:method">
<!--        <xsl:variable name="methods-normal" select="ins:extension//ins:method[@name != current()/ins:method/@name]"/>-->
<!--        <xsl:variable name="methods-extends" select="ins:extension/ins:method"/>-->
        <ul>
          <h3>Methods</h3>
          <xsl:apply-templates select="." mode="normal"/>
        </ul>
      </xsl:if>
		</div>
	</xsl:template>
	
	<xsl:template match="ins:class" mode="extends">
	  <h4><em>herited from </em><xsl:copy-of select="ins:get-class(@name)"/></h4>
    <xsl:for-each select="ins:method[not(@name = current()/ancestor::ins:class/ins:method/@name)]">
       <xsl:sort select="@name"/>
       <xsl:apply-templates select=".">
         <xsl:with-param name="class">inspector-method-extends</xsl:with-param>
       </xsl:apply-templates>
     </xsl:for-each>
     <xsl:apply-templates select="ins:extension/*" mode="methods"/>
	</xsl:template>
	
	<xsl:template match="ins:class" mode="normal">
	  <xsl:for-each select="ins:method">
	     <xsl:sort select="@name"/>
	     <xsl:apply-templates select="."/>
	   </xsl:for-each>
	   <xsl:apply-templates select="ins:extension/*" mode="extends"/>
	</xsl:template>
	
	<xsl:template match="ins:interface">
	  <xsl:copy-of select="ins:get-class()"/>
	</xsl:template>
	
	<xsl:template match="ins:modifiers">
	  	 
	</xsl:template>
	
	<xsl:template match="ins:constant">
	  <li>
      <strong><xsl:value-of select="@name"/></strong> = 
      <span><xsl:value-of select="ins:default"/></span>
    </li>
	</xsl:template>
	
	<xsl:template match="ins:property">
	  <li>
	    <xsl:apply-templates select="ins:modifiers"/>
      <strong><xsl:value-of select="@name"/></strong>
      <xsl:if test="ins:default">
         = <span><xsl:value-of select="ins:default"/></span>
      </xsl:if>
    </li>
	</xsl:template>
	
	<xsl:template match="ins:method">
	  <xsl:param name="class" select="''"/>
	  <li class="inspector-method {$class}">
      <strong><xsl:value-of select="@name"/></strong>
      <span>(<xsl:apply-templates select="ins:parameter"/>)</span>
      <xsl:variable name="methods" select="../ins:extension//ins:method[@name = current()/@name]"/>
      <xsl:if test="$methods"> also found in </xsl:if>
      <xsl:for-each select="$methods">
        <xsl:copy-of select="ins:get-class(@class)"/>
      </xsl:for-each>
    </li>
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
        <xsl:copy-of select="ins:get-class(ins:cast)"/>
      </xsl:otherwise>
    </xsl:choose>
	</xsl:template>
	
	<func:function name="ins:get-class">
	  <xsl:param name="name" select="."/>
	  <func:result>
	    <a href="{$inspect}{$name}"><xsl:value-of select="$name"/></a>
	  </func:result>
	</func:function>
	
</xsl:stylesheet>