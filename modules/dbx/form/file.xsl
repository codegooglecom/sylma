<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" xmlns:lc="http://www.sylma.org/schemas" xmlns:lx="http://ns.sylma.org/xslt" xmlns:la="http://www.sylma.org/processors/action-builder" xmlns:ld="http://www.sylma.org/directory" version="1.0" extension-element-prefixes="func lx">
  
  <xsl:template match="ld:file" ld:ns="null" mode="field">
    <xsl:param name="name"/>
    <xsl:param name="title"/>
    <xsl:param name="model"/>
    <xsl:param name="id" select="@lc:temp-file"/>
    
    <la:layer class="file">
      <div class="field-file-container clearfix">
        <input type="hidden" name="{$name}[path]" value="{@full-path}"/>
        <input type="hidden" name="{$name}[name]" value="{$title}"/>
        <input type="hidden" name="{$name}[id]" value="{$id}"/>
        <div class="left field-file-icone field-file-extension-{@extension}">
          
          <xsl:if test="contains('jpg,jpeg,gif,png', @extension)">
            <img src="{@full-path}?width=96&amp;height=76"/>
          </xsl:if>
          
        </div>
        
        <div class="left center field-file-label">
          
          <xsl:if test="$model">
            <xsl:apply-templates select="$model/lc:annotations/lc:message"/>
          </xsl:if>

          <div>
            <strong>
              <xsl:value-of select="$title"/>
            </strong> - 
            <xsl:value-of select="@size"/> Ko
          </div>
          
          <div class="field-file-actions">
            <a href="{@full-path}" target="_blank">voir</a> | 
            <a href="#">
              <la:event name="click">
                <![CDATA[%ref-object%.askRemove(); return false;]]>
              </la:event>
              <span>supprimer</span>
            </a>
          </div>
        </div>
      </div>
    </la:layer>
  </xsl:template>
  
</xsl:stylesheet>
