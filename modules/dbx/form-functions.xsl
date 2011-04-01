<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" xmlns:lc="http://www.sylma.org/schemas" xmlns:lx="http://ns.sylma.org/xslt" xmlns:la="http://www.sylma.org/processors/action-builder" xmlns:ld="http://www.sylma.org/directory" version="1.0" extension-element-prefixes="func lx">
  
  <xsl:import href="../../schemas/functions.xsl"/>
  <xsl:import href="/sylma/xslt/string.xsl"/>
  <xsl:import href="form-file.xsl"/>
  
  <xsl:param name="action"/>
  <xsl:param name="method" select="'POST'"/>
  <xsl:param name="sylma-attr-prefix">sylma-attr-</xsl:param>
  <xsl:param name="sylma-file-prefix">sylma-file-</xsl:param>
  
  <func:function name="lc:is-last-duplicate">
    <func:result select="count(../*[name() = name(current())]) = count(preceding-sibling::*[name() = name(current())]) + 1"/>
  </func:function>
  
  <func:function name="lc:is-visible">
    <xsl:param name="source" select="."/>
    <func:result select="lc:boolean($source/@lc:visible, 1)"/>
  </func:function>
  
  <func:function name="lc:is-editable">
    <xsl:param name="source" select="."/>
    <func:result select="lc:boolean($source/@lc:editable, 1)"/>
  </func:function>
  
  <func:function name="lc:build-name">
    <xsl:param name="element"/>
    <xsl:param name="parent" select="''"/>
    <xsl:param name="name" select="lc:get-name()"/>
    
    <xsl:variable name="suffix">
      <xsl:if test="($element and lc:element-is-multiple($element)) or count(../*[local-name() = $name]) &gt; 1">
        <xsl:value-of  select="concat('[sylma-', generate-id(), ']')"/>
      </xsl:if>
    </xsl:variable>
    
    <xsl:variable name="full-name">
      <xsl:choose>
        <xsl:when test="$parent">
          <xsl:value-of select="concat('[', $name, ']')"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$name"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    
    <func:result select="concat($parent, $full-name, $suffix)"/>
    
  </func:function>
  
  <!-- This is where it begins -->
  
  <xsl:template match="*" mode="field">
    
    <xsl:param name="parent"/>
    <xsl:param name="parent-element"/>
    
    <xsl:variable name="element" select="lc:element-get-element($parent-element)"/>
    
    <xsl:choose>
      <xsl:when test="$element">
        <xsl:variable name="name" select="lc:build-name($element, $parent)"/>
        <xsl:choose>
          
          <xsl:when test="lc:element-is-complex($element) and not(lc:element-is-mixed($element))">
            <!-- is complex -->
            <xsl:apply-templates select="." mode="field-complex">
              <xsl:with-param name="name" select="$name"/>
              <xsl:with-param name="element" select="$element"/>
            </xsl:apply-templates>
            
          </xsl:when>
          
          <xsl:when test="lc:element-is-file($element)">
            
            <!-- is file (is simple), call ld:file-->
            <xsl:variable name="file" select="lc:get-file()"/>
            
            <xsl:choose>
              <xsl:when test="$file">
                <xsl:apply-templates select="$file" mode="field" ld:ns="null">
                  <xsl:with-param name="name" select="lc:build-name($element, $parent, concat($sylma-file-prefix, lc:get-name()))"/>
                  <xsl:with-param name="title" select="@name"/>
                  <xsl:with-param name="model" select="lc:get-model()"/>
                  <xsl:with-param name="id" select="@lc:temp-file"/>
                </xsl:apply-templates>
              </xsl:when>
              <xsl:otherwise>
                <div class="center sylma-message-error">Fichier introuvable</div>
              </xsl:otherwise>
            </xsl:choose>
            
          </xsl:when>
          
          <xsl:otherwise>
            <!-- is simple -->
            <xsl:variable name="id" select="concat('field-', $name)"/>
            <xsl:variable name="class" select="'field-input field-input-element'"/>
            
            <xsl:call-template name="field-simple">
              <xsl:with-param name="id" select="$id"/>
              <xsl:with-param name="name" select="$name"/>
              
              <xsl:with-param name="content">
                <xsl:apply-templates select="." mode="input">
                  <xsl:with-param name="id" select="$id"/>
                  <xsl:with-param name="name" select="$name"/>
                  <xsl:with-param name="class" select="$class"/>
                  <xsl:with-param name="element" select="$element"/>
                </xsl:apply-templates>
                <xsl:apply-templates select="@*" mode="field">
                  <xsl:with-param name="parent" select="$name"/>
                  <xsl:with-param name="parent-element" select="$element"/>
                </xsl:apply-templates>
              </xsl:with-param>
              
              <xsl:with-param name="element" select="$element"/>
              <xsl:with-param name="model" select="lc:get-model()"/>
            </xsl:call-template>
            
          </xsl:otherwise>
          
        </xsl:choose>
        
        <xsl:if test="@lc:view-options">
          <input type="hidden" id="sylma-options-{$name}" value="{@view-options}"/>
        </xsl:if>
        
      </xsl:when>
      <xsl:otherwise>
        <div class="clear-block message-error">
          Invalid field <strong><xsl:value-of select="name()"/></strong> element. Please contact admin.
        </div>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="@*" mode="field">
    <xsl:param name="parent"/>
    <xsl:param name="parent-element"/>
    <xsl:if test="namespace-uri() != 'http://www.sylma.org/schemas'">
      
      <xsl:variable name="element" select="lc:element-get-attribute($parent-element)"/>
      <xsl:choose>
        <xsl:when test="$element">
          <xsl:choose>
            
            <xsl:when test="$element and lc:is-visible($element)">
              <xsl:variable name="name" select="lc:build-name($element, $parent, concat($sylma-attr-prefix, local-name()))" />
              <xsl:variable name="id" select="concat('field-', $name)"/>
              <xsl:variable name="class" select="'field-input field-input-attribute'"/>
              
              <xsl:call-template name="field-simple">
                <xsl:with-param name="id" select="$id"/>
                <xsl:with-param name="name" select="$name"/>
                
                <xsl:with-param name="content">
                  <xsl:apply-templates select="." mode="input">
                    <xsl:with-param name="id" select="$id"/>
                    <xsl:with-param name="name" select="$name"/>
                    <xsl:with-param name="class" select="$class"/>
                    <xsl:with-param name="element" select="$element"/>
                  </xsl:apply-templates>
                </xsl:with-param>
                
                <xsl:with-param name="element" select="$element"/>
              </xsl:call-template>
              
            </xsl:when>
            
            <xsl:otherwise>
              <input type="hidden" value="{.}" name="{$sylma-attr-prefix}{local-name()}"/>
            </xsl:otherwise>
            
          </xsl:choose>
        </xsl:when>
        
        <xsl:otherwise>
          <div class="clear-block message-error">
            Invalid field @<strong><xsl:value-of select="name()"/></strong> attribute. Please contact admin.
          </div>
        </xsl:otherwise>
        
      </xsl:choose>
    </xsl:if>
  </xsl:template>
  
  <xsl:template name="field-simple">
    <xsl:param name="id"/>
    <xsl:param name="name"/>
    <xsl:param name="content"/>
    <xsl:param name="element"/>
    <xsl:param name="model"/>
    <xsl:choose>
      <xsl:when test="lc:is-visible($element)">
        
        <xsl:variable name="statut">
          <xsl:choose>
            <xsl:when test="$model">
              <xsl:value-of select="concat('field-statut-', lc:model-get-statut($model))"/>
            </xsl:when>
            <xsl:otherwise>field-statut-attr</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:variable name="label">
          <xsl:apply-templates select="." mode="label">
            <xsl:with-param name="element" select="$element"/>
            <xsl:with-param name="id" select="$id"/>
          </xsl:apply-templates>
        </xsl:variable>
        
        <xsl:variable name="is-multiple" select="lc:element-is-multiple($element)"/>
        
        <xsl:variable name="full-content">
          <xsl:choose>
            <xsl:when test="lc:element-is-boolean($element)">
              <xsl:copy-of select="$content"/>
              <xsl:copy-of select="$label"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:copy-of select="$label"/>
              <xsl:copy-of select="$content"/>
            </xsl:otherwise>
          </xsl:choose>
          <xsl:if test="$model">
            <xsl:apply-templates select="$model/lc:annotations/lc:message"/>
          </xsl:if>
        </xsl:variable>
        
        <xsl:choose>
          
          <xsl:when test="$is-multiple">
            
            <la:layer>
              <div class="field clear-block {$statut}">
                <xsl:copy-of select="$full-content"/>
                <xsl:apply-templates select="." mode="multiple-remove">
                  <xsl:with-param name="element" select="$element"/>
                </xsl:apply-templates>
              </div>
            </la:layer>
            
          </xsl:when>
          
          <xsl:otherwise>
            <div class="field clear-block {$statut}" id="field-container-{$name}">
              <xsl:copy-of select="$full-content"/>              
            </div>
          </xsl:otherwise>
        
        </xsl:choose>
        
      </xsl:when>
      <xsl:otherwise>
        <xsl:copy-of select="$content"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="*" mode="field-complex">
    <xsl:param name="name"/>
    <xsl:param name="element"/>
    
    <xsl:variable name="model" select="lc:get-model()"/>
    
    <la:layer class="complex">
      <xsl:variable name="statut">
        <xsl:value-of select="concat('field-statut-', lc:model-get-statut($model))"/>
      </xsl:variable>
      
      <div>
        <xsl:choose>
          <xsl:when test="lc:element-is-multiple($element)">
            <div class="field-complex field-multiple clear-block {$statut}">
              <xsl:apply-templates select="." mode="multiple-remove">
                <xsl:with-param name="element" select="$element"/>
              </xsl:apply-templates>
              
              <xsl:apply-templates select="lc:get-model()/lc:annotations/lc:message"/>
              
              <xsl:call-template name="field-complex">
                <xsl:with-param name="name" select="$name"/>
                <xsl:with-param name="element" select="$element"/>
              </xsl:call-template>
            </div>
          </xsl:when>
          
          <xsl:otherwise>
            <div class="field-complex clear-block {$statut}">
              
              <xsl:apply-templates select="lc:get-model()/lc:annotations/lc:message"/>
              
              <xsl:if test="$element/@lc:title">
                <h3>
                  <xsl:value-of select="lx:first-case(lc:element-get-title($element, .))"/>
                </h3>
              </xsl:if>
              
              <xsl:call-template name="field-complex">
                <xsl:with-param name="name" select="$name"/>
                <xsl:with-param name="element" select="$element"/>
              </xsl:call-template>
              
            </div>
          </xsl:otherwise>
        </xsl:choose>
      </div>
      
    </la:layer>
  </xsl:template>
  
  <xsl:template name="field-complex">
    
    <xsl:param name="name"/>
    <xsl:param name="element"/>
    
    <xsl:apply-templates select="@*" mode="field">
      <xsl:with-param name="parent" select="$name"/>
      <xsl:with-param name="parent-element" select="$element"/>
    </xsl:apply-templates>
    <xsl:apply-templates select="*" mode="field">
      <xsl:with-param name="parent" select="$name"/>
      <xsl:with-param name="parent-element" select="$element"/>
    </xsl:apply-templates>
    
  </xsl:template>
  
  <!-- Links are multiple element's related node to indicate where this element could be added -->
  
  <xsl:template match="lc:link-add" mode="field">
    <xsl:param name="parent"/>
    <xsl:param name="parent-element"/>
    <xsl:variable name="element" select="lc:name-get-element($parent-element, @name)"/>
    
    <xsl:choose>
      <xsl:when test="lc:element-is-file($element)">
        <xsl:apply-templates select="." mode="file">
          <xsl:with-param name="parent" select="$parent"/>
          <xsl:with-param name="element" select="$element"/>
        </xsl:apply-templates>
      </xsl:when>
      
      <xsl:otherwise>
        <xsl:apply-templates select=".">
          <xsl:with-param name="element" select="$element"/>
        </xsl:apply-templates>
      </xsl:otherwise>
    </xsl:choose>
    
  </xsl:template>
  
  <xsl:template match="lc:link-add">
    <xsl:param name="parent"/>
    <xsl:param name="element"/>
    
    <la:layer class="template">
      <la:property name="path">
        <xsl:value-of select="."/>
      </la:property>
      
      <div class="center field-add">
        <button type="button">
          <la:event name="click">
           %ref-object%.add();
           return false;
          </la:event>
          
          <span><xsl:text>Ajouter un/e</xsl:text>
          <strong><xsl:value-of select="lc:element-get-title($element)"/></strong></span>
        </button>
      </div>
    </la:layer>
    
  </xsl:template>
  
  <xsl:template match="lc:link-add" mode="file">
    <xsl:param name="parent"/>
    <xsl:param name="element"/>
    
    <la:layer class="files">
      <la:property name="parentName">
        <xsl:value-of select="$parent"/>
      </la:property>
      <la:property name="name">
        <xsl:value-of select="concat($sylma-file-prefix, @name)"/>
      </la:property>
      <la:property name="path">
        <xsl:value-of select="."/>
      </la:property>
      
      <div class="field-add">
        
        <div>
          <input type="file" name="file-uploader">
            <la:event name="change"><![CDATA[return %ref-object%.sendFile(this);]]></la:event>
          </input>
          <span class="sylma-field-loading">... chargement</span>
        </div>
        
        <iframe id="sylma-uploader-iframe" name="sylma-uploader-iframe" style="display: none;width: 0; height: 0; border:0">
          <la:event name="load"><![CDATA[%ref-object%.updateFile(this);]]></la:event>
        </iframe>
      </div>
    </la:layer>
  </xsl:template>
  
  <xsl:template match="*" mode="multiple-remove">
    <xsl:param name="element"/>
    <input type="button" value="Retirer" class="right">
      <la:event name="click">
        %ref-object%.remove();
      </la:event>
    </input>
  </xsl:template>
  
  <xsl:template match="*" mode="notice">
    <xsl:param name="class"/>
    <div class="field-notice {$class}">
      Les champs marqués d'un <strong>*</strong> sont obligatoires
    </div>
  </xsl:template>
  
  <xsl:template match="*" mode="label">
    <xsl:param name="element"/>
    <xsl:param name="id"/>
    
    <xsl:call-template name="label">
      <xsl:with-param name="element" select="$element"/>
      <xsl:with-param name="id" select="$id"/>
    </xsl:call-template>
    
  </xsl:template>
  
  <xsl:template match="@*" mode="label">
    <xsl:param name="element"/>
    <xsl:param name="id"/>
    
    <xsl:call-template name="label">
      <xsl:with-param name="element" select="$element"/>
      <xsl:with-param name="id" select="$id"/>
    </xsl:call-template>
    
  </xsl:template>
  
  <xsl:template name="label">
    <xsl:param name="element"/>
    <xsl:param name="id"/>
    
    <label for="{$id}">
      <xsl:value-of select="lx:first-case(lc:element-get-title($element))"/>
      <xsl:if test="not(lc:element-is-boolean($element))"> : </xsl:if>
      <xsl:if test="lc:element-is-required($element)"> *</xsl:if>
    </label>
  </xsl:template>
  
  <xsl:template match="*" mode="input">
    <xsl:param name="id"/>
    <xsl:param name="name"/>
    <xsl:param name="class"/>
    <xsl:param name="element"/>
    <xsl:call-template name="input">
      <xsl:with-param name="id" select="$id"/>
      <xsl:with-param name="name" select="$name"/>
      <xsl:with-param name="class" select="$class"/>
      <xsl:with-param name="element" select="$element"/>
    </xsl:call-template>
  </xsl:template>
  
  <xsl:template match="@*" mode="input">
    <xsl:param name="id"/>
    <xsl:param name="name"/>
    <xsl:param name="class"/>
    <xsl:param name="element"/>
    <xsl:call-template name="input">
      <xsl:with-param name="id" select="$id"/>
      <xsl:with-param name="name" select="$name"/>
      <xsl:with-param name="class" select="$class"/>
      <xsl:with-param name="element" select="$element"/>
    </xsl:call-template>
  </xsl:template>
  
  <!-- Here you will find last input type template with form elements -->
  
  <xsl:template name="input">
    <xsl:param name="id"/>
    <xsl:param name="name"/>
    <xsl:param name="class"/>
    <xsl:param name="element"/>
    
    <xsl:variable name="is-visible" select="lc:is-visible($element)"/>
    <xsl:variable name="is-editable" select="lc:is-editable($element)"/>
    
    <xsl:choose>
      <xsl:when test="not($is-visible) and $is-editable">
        <input type="hidden" class="{$class}" name="{$name}" id="{$id}" value="{.}"/>
      </xsl:when>
      <xsl:when test="not($is-editable) and $is-visible">
        <span class="field-uneditable" id="{$id}">
          <xsl:value-of select="."/>
        </span>
      </xsl:when>
      <xsl:when test="not($is-editable) and not($is-visible)"/>
      <xsl:when test="lc:element-is-keyref($element)">
        <select name="{$name}" id="{$id}" class="{$class}">
          <option value="0">&lt; choisissez &gt;</option>
          <xsl:variable name="self" select="."/>
          <xsl:for-each select="lc:get-values()/*">
            <xsl:sort select="."/>
            <xsl:call-template name="enumeration">
              <xsl:with-param name="value" select="$self"/>
            </xsl:call-template>
          </xsl:for-each>
        </select>
      </xsl:when>
      <xsl:when test="lc:element-is-string($element)">
        <xsl:choose>
          <xsl:when test="@lc:file">
            <xsl:call-template name="input-file">
              <xsl:with-param name="id" select="$id"/>
              <xsl:with-param name="name" select="$name"/>
              <xsl:with-param name="class" select="$class"/>
              <xsl:with-param name="element" select="$element"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:when test="lc:element-is-enum($element)">
            <xsl:variable name="schema" select="lc:element-get-schema($element)"/>
            <select name="{$name}" id="{$id}" class="{$class}">
              <option value="0">&lt; choisissez &gt;</option>
              <xsl:apply-templates select="$schema/lc:restriction/lc:enumeration">
                <xsl:with-param name="value" select="node()"/>
              </xsl:apply-templates>
            </select>
          </xsl:when>
          <xsl:when test="lc:boolean($element/@lc:line-break) or lc:boolean($element/@lc:wiki)">
            <textarea id="{$id}" name="{$name}" class="{$class}">
              <xsl:value-of select="."/>
            </textarea>
          </xsl:when>
          <xsl:otherwise>
            <input type="text" value="{.}" name="{$name}" id="{$id}" class="{$class}"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="lc:element-is-date($element)">
        <input class="{$class} field-input-date" id="{$id}" value="{.}"/>
        <input type="hidden" name="{$name}" value="{.}"/>
      </xsl:when>
      <xsl:when test="lc:element-is-boolean($element)">
        <input type="checkbox" id="{$id}" class="{$class} field-input-boolean" name="{$name}" value="1">
          <xsl:if test=". = '1' or . = 'true'">
            <xsl:attribute name="checked">checked</xsl:attribute>
          </xsl:if>
        </input>
      </xsl:when>
      <xsl:when test="lc:element-is-integer($element)">
        <input type="text" class="{$class} field-input-integer" id="{$id}" name="{$name}" value="{.}"/>
      </xsl:when>
      <xsl:otherwise>
        <textarea id="{$id}" name="{$name}" class="{$class}">
          <xsl:value-of select="."/>
        </textarea>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="lc:enumeration">
    <xsl:param name="value"/>
    <option>
      <xsl:if test="$value = text()">
        <xsl:attribute name="selected">selected</xsl:attribute>
      </xsl:if>
      <xsl:value-of select="."/>
    </option>
  </xsl:template>
  
  <xsl:template name="enumeration">
    <xsl:param name="value"/>
    <option>
      <xsl:choose>
        <xsl:when test="@key != ''">
          <xsl:attribute name="value">
            <xsl:value-of select="@key"/>
          </xsl:attribute>
          <xsl:if test="$value = @key">
            <xsl:attribute name="selected">selected</xsl:attribute>
          </xsl:if>
        </xsl:when>
        <xsl:otherwise>
          <xsl:attribute name="value">
            <xsl:value-of select="position()"/>
          </xsl:attribute>
          <xsl:if test="$value = position()">
            <xsl:attribute name="selected">selected</xsl:attribute>
          </xsl:if>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:value-of select="."/>
    </option>
  </xsl:template>
  
  <xsl:template match="lc:message">
    <div class="field-message">
      <xsl:copy-of select="node()"/>
    </div>
  </xsl:template>
  
  <xsl:template match="*" mode="actions">
    <div class="field-actions">
      <input type="submit" value="Enregistrer"/>
      <input type="button" value="Annuler" onclick="history.go(-1);"/>
    </div>
  </xsl:template>
  
  <xsl:template match="*" mode="events"/>
  
</xsl:stylesheet>
