<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" xmlns:lc="http://www.sylma.org/schemas" xmlns:lx="http://ns.sylma.org/xslt" xmlns:ld="http://www.sylma.org/directory" version="1.0" extension-element-prefixes="func lx">
  
  <xsl:import href="/sylma/schemas/functions.xsl"/>
  <xsl:import href="/sylma/xslt/string.xsl"/>
  <xsl:import href="/sylma/xslt/date.xsl"/>
  
  <xsl:template match="/*">
    
    <xsl:variable name="element" select="lc:get-root-element(current()/*[1])"/>
    
    <div>
      <xsl:apply-templates select="*[1]" mode="title"/>
      <xsl:apply-templates select="." mode="annotations"/>
      <xsl:apply-templates select="*[1]/@*" mode="field">
        <xsl:with-param name="parent-element" select="$element"/>
      </xsl:apply-templates>
      <xsl:apply-templates select="*[1]/*" mode="field">
        <xsl:with-param name="parent-element" select="$element"/>
      </xsl:apply-templates>
    </div>
  </xsl:template>
  
  <xsl:template match="*" mode="title"/>
  
  <xsl:template match="*" mode="annotations">
    <xsl:call-template name="annotations"/>
  </xsl:template>
  
  <xsl:template match="@*" mode="annotations">
    <xsl:call-template name="annotations"/>
  </xsl:template>
  
  <xsl:template name="annotations">
    <xsl:variable name="messages" select="lc:get-model()/lc:annotations/lc:message"/>
    <xsl:if test="$messages">
      <div class="view-message">
        <xsl:apply-templates select="$messages"/>
      </div>
    </xsl:if>
  </xsl:template>
  
  <xsl:template match="lc:link-add" mode="field"/>
  
  <xsl:template match="*" mode="field">
    <xsl:param name="parent-element"/>
    <xsl:variable name="element" select="lc:element-get-element($parent-element)"/>
    
    <xsl:choose>
      
      <xsl:when test="lc:element-is-complex($element)">
        
        <div class="field field-complex clearfix">
          <xsl:if test="not(lc:element-is-multiple($element))">
            <h3><xsl:value-of select="lc:element-get-title($element)"/></h3>
          </xsl:if>
          <xsl:apply-templates mode="field">
            <xsl:with-param name="parent-element" select="$element"/>
          </xsl:apply-templates>
        </div>
        
      </xsl:when>
      
      <xsl:when test="lc:element-is-file($element)">
        
        <xsl:variable name="file" select="lc:get-file()"/>
        
        <div class="field field-file clearfix">
          <xsl:apply-templates select="." mode="label">
            <xsl:with-param name="element" select="$element"/>
          </xsl:apply-templates>
          <xsl:choose>
            <xsl:when test="$file">
              <xsl:apply-templates select="$file" mode="field">
                <xsl:with-param name="title" select="@name"/>
              </xsl:apply-templates>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="."/>
            </xsl:otherwise>
          </xsl:choose>
        </div>
        
      </xsl:when>
      
      <xsl:otherwise>
        
        <xsl:call-template name="field">
          <xsl:with-param name="element" select="$element"/>
        </xsl:call-template>
        
      </xsl:otherwise>
      
    </xsl:choose>
    
  </xsl:template>
  
  <xsl:template match="@*" mode="field">
    <xsl:param name="parent-element"/>
    <xsl:variable name="element" select="lc:element-get-attribute($parent-element)"/>
    
    <xsl:if test="namespace-uri() != 'http://www.sylma.org/schemas'">
      <xsl:call-template name="field">
        <xsl:with-param name="element" select="$element"/>
      </xsl:call-template>
    </xsl:if>
    
  </xsl:template>
  
  <xsl:template name="field">
    <xsl:param name="element"/>
    
    <xsl:variable name="class">
      <xsl:choose>
        <xsl:when test="not($element)">unknown</xsl:when>
        <xsl:when test="lc:element-is-keyref($element)">keyref</xsl:when>
        <xsl:when test="lc:element-is-string($element)">string</xsl:when>
        <xsl:when test="lc:element-is-date($element)">date</xsl:when>
        <xsl:when test="lc:element-is-integer($element)">integer</xsl:when>
        <xsl:otherwise>default</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:if test="not(@lc:editable = 'false')">
      <div class="field clearfix type-{$class}">
        <xsl:apply-templates select="." mode="label">
          <xsl:with-param name="element" select="$element"/>
        </xsl:apply-templates>
        <xsl:apply-templates select="." mode="value">
          <xsl:with-param name="element" select="$element"/>
        </xsl:apply-templates>
        <xsl:apply-templates select="." mode="annotations"/>
      </div>
    </xsl:if>
    
  </xsl:template>
  
  <xsl:template match="*" mode="label">
    <xsl:param name="element"/>
    <xsl:call-template name="label">
      <xsl:with-param name="element" select="$element"/>
    </xsl:call-template>
  </xsl:template>
  
  <xsl:template match="@*" mode="label">
    <xsl:param name="element"/>
    <xsl:call-template name="label">
      <xsl:with-param name="element" select="$element"/>
    </xsl:call-template>
  </xsl:template>
  
  <xsl:template name="label">
    <xsl:param name="element"/>
    <label>
      <xsl:value-of select="lx:first-case(lc:element-get-title($element))"/>
      <xsl:text> : </xsl:text>
    </label>
  </xsl:template>
  
  <xsl:template match="*" mode="value">
    <xsl:param name="element"/>
    <xsl:call-template name="value">
      <xsl:with-param name="element" select="$element"/>
    </xsl:call-template>
  </xsl:template>
  
  <xsl:template match="@*" mode="value">
    <xsl:param name="element"/>
    <xsl:call-template name="value">
      <xsl:with-param name="element" select="$element"/>
    </xsl:call-template>
  </xsl:template>
  
  <xsl:template name="value">
    <xsl:param name="element"/>
    
    <xsl:choose>
      
      <xsl:when test=". = '' and not(lc:element-is-boolean($element))">
        <em class="left">-</em>
      </xsl:when>
      
      <xsl:when test="@lc:line-break">
        <pre class="field-content">
          <xsl:if test="string-length(.) &gt; 250">
            <xsl:attribute name="style">width: auto;</xsl:attribute>
          </xsl:if>
          <xsl:value-of select="."/>
        </pre>
      </xsl:when>
      
      <xsl:otherwise>
        <div class="field-content">
          <xsl:choose>
            
            <xsl:when test="lc:element-is-date($element)">
              <xsl:value-of select="lx:format-date(.)"/>
            </xsl:when>
            
            <xsl:when test="lc:element-is-boolean($element)">
              
              <xsl:variable name="icone">
                <xsl:choose>
                  <xsl:when test=". = '0' or . = '' or . = 'false'">delete</xsl:when>
                  <xsl:otherwise>ok</xsl:otherwise>
                </xsl:choose>
              </xsl:variable>
              
              <img src="{$sylma-directory}/images/{$icone}.png"/>
            </xsl:when>
            
            <xsl:otherwise>
              <xsl:value-of select="."/>
            </xsl:otherwise>
            
          </xsl:choose>
        </div>
      </xsl:otherwise>
      
    </xsl:choose>

  </xsl:template>
  
  <xsl:template match="ld:file" ld:ns="null" mode="field">
    <xsl:param name="title"/>
    
    <div class="field-file-extension-{@extension} field-file-small">
      <xsl:if test="contains('jpg,jpeg,gif,png', @extension)">
        <img src="{@full-path}?width=96&amp;height=76"/>
      </xsl:if>
      <strong>
        <xsl:value-of select="$title"/>
      </strong> - 
      <xsl:value-of select="@size"/> Ko
    </div>
  </xsl:template>
  
  <xsl:template match="lc:message">
    <div>
      <xsl:copy-of select="node()"/>
    </div>
  </xsl:template>
</xsl:stylesheet>
