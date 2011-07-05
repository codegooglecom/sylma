<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" extension-element-prefixes="func" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ins="http://www.sylma.org/modules/inspector" xmlns:func="http://exslt.org/functions" xmlns:set="http://exslt.org/sets">
  
  <xsl:import href="functions.xsl"/>
  
  <xsl:param name="inspect" select="concat($sylma-directory, '/class/')"/>
  
  <xsl:param name="class-prefix">sylma-ins</xsl:param>
  
  <xsl:param name="class-class">sylma-ins-class</xsl:param>
  <xsl:param name="class-extends">sylma-ins-extends</xsl:param>
  <xsl:param name="class-group">sylma-ins-group</xsl:param>
  <xsl:param name="class-comment">sylma-ins-comment</xsl:param>
  <xsl:param name="class-item">sylma-ins-item</xsl:param>
  <xsl:param name="class-method">sylma-ins-method</xsl:param>
  <xsl:param name="class-property">sylma-ins-property</xsl:param>
  <xsl:param name="class-optional">sylma-ins-optional</xsl:param>
  <xsl:param name="class-required">sylma-ins-required</xsl:param>
  <xsl:param name="class-parameter">sylma-ins-parameter</xsl:param>
  <xsl:param name="class-return">sylma-ins-return</xsl:param>
  <xsl:param name="class-name">sylma-ins-name</xsl:param>
  <xsl:param name="class-dollar">sylma-ins-dollar</xsl:param>
  <xsl:param name="class-basetype">sylma-ins-basetype</xsl:param>
  <xsl:param name="class-cast">sylma-ins-cast</xsl:param>
  <xsl:param name="class-static">sylma-ins-static</xsl:param>
  <xsl:param name="class-parameter">sylma-ins-parameter</xsl:param>
  <xsl:param name="class-parameters">sylma-ins-parameters</xsl:param>
  
	<xsl:template match="/*">
		<div class="{$class-class}">
		  <h2><xsl:value-of select="@name"/></h2>
		  <p>
		    <xsl:apply-templates select="ins:comment"/>
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
          <xsl:apply-templates select="." mode="normal">
            <xsl:with-param name="element">constant</xsl:with-param>
          </xsl:apply-templates>
        </ul>
      </xsl:if>
      <xsl:if test=".//ins:property">
        <ul>
          <h3>Properties</h3>
          <xsl:apply-templates select="." mode="normal">
            <xsl:with-param name="element">property</xsl:with-param>
          </xsl:apply-templates>
        </ul>
      </xsl:if>
      <xsl:if test="//ins:method">
        <ul>
          <h3>Methods</h3>
          <xsl:apply-templates select="." mode="normal">
            <xsl:with-param name="element">method</xsl:with-param>
          </xsl:apply-templates>
        </ul>
      </xsl:if>
		</div>
	</xsl:template>
	
  <xsl:template match="ins:class" mode="normal">
    <xsl:param name="element"/>
    <xsl:for-each select="ins:*[local-name() = $element]">
       <xsl:sort select="@name"/>
       <xsl:apply-templates select="."/>
     </xsl:for-each>
     <xsl:apply-templates select="ins:extension/*" mode="extends">
       <xsl:with-param name="element" select="$element"/>
     </xsl:apply-templates>
  </xsl:template>
  
	<xsl:template match="ins:class" mode="extends">
	  <xsl:param name="element"/>
	  <xsl:variable name="set" select="ins:*[local-name() = $element and not(@name = current()/ancestor::ins:class/ins:*[local-name() = $element]/@name)]"/>
    <xsl:if test="$set">
      <li class="{$class-group} clearfix">
        <ul>
          <h4><em>herited from </em><xsl:copy-of select="ins:get-class(@name)"/></h4>
          <xsl:for-each select="$set">
             <xsl:sort select="@name"/>
             <xsl:apply-templates select=".">
               <xsl:with-param name="class" select="$class-extends"/>
             </xsl:apply-templates>
           </xsl:for-each>
           <xsl:apply-templates select="ins:extension/*" mode="extends">
             <xsl:with-param name="element" select="$element"/>
           </xsl:apply-templates>
        </ul>
      </li>
    </xsl:if>
	</xsl:template>
	
	<xsl:template match="ins:interface">
	  <xsl:copy-of select="ins:get-class()"/>
	</xsl:template>
	
	<xsl:template match="ins:modifiers">
	  	 
	</xsl:template>
	
	<xsl:template match="ins:constant">
	  <xsl:param name="class"/>
	  <li>
      <xsl:attribute name="class">
        <xsl:value-of select="concat($class-property, ' ', $class, ' ', $class-item)"/>
      </xsl:attribute>
      <strong><xsl:value-of select="@name"/></strong> = 
      <span><xsl:value-of select="ins:default"/></span>
    </li>
	</xsl:template>
	
	<xsl:template match="ins:property">
	  <xsl:param name="class"/>
	  <li>
      <xsl:attribute name="class">
        <xsl:if test="@static = 'true'"><xsl:value-of select="concat($class-static, ' ')"/></xsl:if>
        <xsl:value-of select="concat($class-property, ' ', $class, ' ', $class-item, ' ', $class-prefix, '-', @access)"/>
      </xsl:attribute>
	    <xsl:apply-templates select="ins:modifiers"/>
      <strong>$<xsl:value-of select="@name"/></strong>
      <xsl:if test="ins:default">
         = <span><xsl:value-of select="ins:default"/></span>
      </xsl:if>
      <xsl:apply-templates select="ins:comment"/>
    </li>
	</xsl:template>
	
	<xsl:template match="ins:method">
	  <xsl:param name="class" select="''"/>
	  <li>
      <xsl:attribute name="class">
        <xsl:if test="@static = 'true'"><xsl:value-of select="concat($class-static, ' ')"/></xsl:if>
        <xsl:value-of select="concat($class-item, ' ', $class-method, ' ', $class, ' ', $class-prefix, '-', @access)"/>
      </xsl:attribute>
      <strong><xsl:value-of select="@name"/></strong>
      <div class="{$class-parameters}">(<xsl:copy-of select="ins:implode(ins:parameter)"/> )</div>
      <xsl:variable name="methods" select="../ins:extension//ins:method[@name = current()/@name]"/>
      <xsl:if test="$methods"> also found in </xsl:if>
      <xsl:for-each select="$methods">
        <xsl:copy-of select="ins:get-class(@class)"/>
      </xsl:for-each>
      <xsl:apply-templates select="ins:comment"/>
    </li>
	</xsl:template>
	
	<xsl:template match="ins:parameter">
    <div>
      <xsl:attribute name="class">
        <xsl:value-of select="concat($class-parameter, ' ')"/>
        <xsl:choose>
          <xsl:when test="ins:default">
            <xsl:value-of select="$class-optional"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$class-required"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:apply-templates select="ins:cast"/>
      <span class="{$class-dollar}">$</span>
      <span class="{$class-name}"><xsl:value-of select="@name"/></span>
    </div>
	</xsl:template>
	
	<xsl:template match="ins:comment">
    <xsl:if test="ins:description | ins:return">
      <div class="{$class-comment}">
        <xsl:choose>
          <xsl:when test="ins:description">
            <p><xsl:copy-of select="ins:description/node()"/></p>
          </xsl:when>
          <xsl:otherwise test="ins:description">
            <p><xsl:copy-of select="ins:description/node()"/></p>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:variable name="properties" select="*[local-name() != 'description' and local-name() != 'source']"/>
        <xsl:if test="$properties">
          <ul>
            <xsl:for-each select="$properties">
              <li>
                <xsl:apply-templates select="." mode="comment"/>
              </li>
            </xsl:for-each>
          </ul>
        </xsl:if>
      </div>
    </xsl:if>
  </xsl:template>
  
  <xsl:template match="*" mode="comment">
    <strong>@<xsl:value-of select="local-name()"/> : </strong>
    <xsl:value-of select="."/>
  </xsl:template>
  
  <xsl:template match="ins:parameter" mode="comment">
    <xsl:attribute name="class">
      <xsl:if test="@required != 'true'">
        <xsl:value-of select="concat($class-optional, ' ')"/>
      </xsl:if>
      <xsl:value-of select="$class-parameter"/>
    </xsl:attribute>
    <strong>@param</strong>
    <xsl:if test="@required != 'true'">?</xsl:if>
    <xsl:apply-templates select="ins:cast"/>
    <span class="{$class-dollar}">$</span>
    <span class="{$class-name}"><xsl:value-of select="@name"/></span> : 
    <span><xsl:value-of select="ins:description"/></span>
  </xsl:template>
  
  <xsl:template match="ins:return" mode="comment">
    <xsl:attribute name="class">
      <xsl:value-of select="$class-return"/>
    </xsl:attribute>
    <strong>@return </strong>
    <xsl:apply-templates select="ins:cast"/> : 
    <xsl:value-of select="ins:description"/>
  </xsl:template>
  
	<xsl:template match="ins:cast">
    <span class="{$class-cast}">
      <xsl:choose>
        <xsl:when test=". = 'array' or . = 'null' or . = 'boolean' or . = 'string' or . = 'mixed' or . = 'integer'">
          <span class="{$class-basetype}"><xsl:value-of select="."/></span>
        </xsl:when>
        <xsl:otherwise>
          <xsl:copy-of select="ins:get-class(.)"/>
        </xsl:otherwise>
      </xsl:choose>
    </span>
    <xsl:if test="position() != last()"> |</xsl:if>
	</xsl:template>
	
	<func:function name="ins:get-class">
	  <xsl:param name="name" select="."/>
	  <func:result>
      <xsl:variable name="require">
        <xsl:if test="/*/@file">
          <xsl:value-of select="concat('&amp;file=', /*/@file)"/>
        </xsl:if>
      </xsl:variable>
	    <a href="{$inspect}{$name}{$require}"><xsl:value-of select="$name"/></a>
	  </func:result>
	</func:function>
	
</xsl:stylesheet>

