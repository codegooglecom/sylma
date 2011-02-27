<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" xmlns:lc="http://www.sylma.org/schemas" xmlns:lx="http://ns.sylma.org/xslt" xmlns:la="http://www.sylma.org/processors/action-builder" version="1.0" extension-element-prefixes="func lx">
  
  <xsl:import href="../../schemas/functions.xsl"/>
  <xsl:import href="/sylma/xslt/string.xsl"/>
  
  <xsl:param name="action"/>
  <xsl:param name="method" select="'POST'"/>
  
  <func:function name="lc:is-visible">
    <xsl:param name="source" select="."/>
    <func:result select="lc:boolean($source/@lc:visible, 1)"/>
  </func:function>
  
  <func:function name="lc:is-editable">
    <xsl:param name="source" select="."/>
    <func:result select="lc:boolean($source/@lc:editable, 1)"/>
  </func:function>
  
  <func:function name="lc:build-name">
    <xsl:param name="parent"/>
    <xsl:param name="name" select="lc:get-name()"/>
    <xsl:choose>
      <xsl:when test="$parent">
        <func:result select="concat($parent, '[', $name, ']')"/>
      </xsl:when>
      <xsl:otherwise>
        <func:result select="$name"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  
  <xsl:template match="/*">
    <form method="{$method}" action="{$action}" enctype="multipart/form-data">
      
      <xsl:apply-templates select="lc:get-model(*[1])/lc:annotations/lc:message"/>
      
      <xsl:apply-templates select="*[1]" mode="field"/>
      
      <div class="field-actions">
        <input type="submit" value="Enregistrer"/>
        <input type="button" value="Annuler" onclick="history.go(-1);"/>
      </div>
      
    </form>
  </xsl:template>
  
  <xsl:template match="*" mode="field">
    
    <xsl:param name="parent"/>
    <xsl:param name="parent-schema"/>
    <xsl:variable name="schema" select="lc:schema-get-schema($parent-schema)"/>
    
    <xsl:choose>
      <xsl:when test="$schema">
        <xsl:variable name="name" select="lc:build-name($parent)"/>
        <xsl:choose>
          <xsl:when test="lc:schema-is-complex($schema)">
            <xsl:call-template name="field-complex">
              <xsl:with-param name="name" select="$name"/>
              <xsl:with-param name="schema" select="$schema"/>
            </xsl:call-template>
          </xsl:when>
          
          <xsl:otherwise>
            <xsl:variable name="id" select="concat('field-', lc:get-name())"/>
            <xsl:variable name="class" select="'field-input-element'"/>
            
            <xsl:call-template name="field-simple">
              <xsl:with-param name="id" select="$id"/>
              <xsl:with-param name="name" select="$name"/>
              
              <xsl:with-param name="content">
                <xsl:apply-templates select="." mode="input">
                  <xsl:with-param name="id" select="$id"/>
                  <xsl:with-param name="name" select="$name"/>
                  <xsl:with-param name="class" select="$class"/>
                  <xsl:with-param name="schema" select="$schema"/>
                </xsl:apply-templates>
                <xsl:apply-templates select="@*" mode="field">
                  <xsl:with-param name="parent" select="$name"/>
                  <xsl:with-param name="parent-schema" select="$schema"/>
                </xsl:apply-templates>
              </xsl:with-param>
              
              <xsl:with-param name="schema" select="$schema"/>
              <xsl:with-param name="model" select="lc:get-model()"/>
            </xsl:call-template>
            
          </xsl:otherwise>
          
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <div class="clear-block message-error">Invalid field element. Please contact admin.</div>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="@*" mode="field">
    <xsl:param name="parent"/>
    <xsl:param name="parent-schema"/>
    <xsl:if test="namespace-uri() != 'http://www.sylma.org/schemas'">
      <xsl:variable name="schema" select="lc:schema-get-schema-attribute($parent-schema)"/>
      <xsl:choose>
        <xsl:when test="$schema">
          <xsl:choose>
            
            <xsl:when test="$schema and lc:is-visible($schema)">
              <xsl:variable name="id" select="concat('field-', local-name())"/>
              <xsl:variable name="name" select="lc:build-name($parent, local-name())" />
              <xsl:variable name="class" select="'field-input-attribute'"/>
              
              <xsl:call-template name="field-simple">
                <xsl:with-param name="id" select="$id"/>
                <xsl:with-param name="name" select="$name"/>
                
                <xsl:with-param name="content">
                  <xsl:apply-templates select="." mode="input">
                    <xsl:with-param name="id" select="$id"/>
                    <xsl:with-param name="name" select="$name"/>
                    <xsl:with-param name="class" select="$class"/>
                    <xsl:with-param name="schema" select="$schema"/>
                  </xsl:apply-templates>
                </xsl:with-param>
                
                <xsl:with-param name="schema" select="$schema"/>
                <xsl:with-param name="model" select="lc:get-model(..)"/>
              </xsl:call-template>
              
              <input type="text" value="ATTR1 {name()}" name="attr-{local-name()}"/>
            </xsl:when>
            
            <xsl:otherwise>
              <input type="text" value="ATTR INVISIBLE {name()}" name="attr-{local-name()}"/>
            </xsl:otherwise>
            
          </xsl:choose>
        </xsl:when>
        
        <xsl:otherwise>
          <div class="clear-block message-error">Invalid field element. Please contact admin.</div>
        </xsl:otherwise>
        
      </xsl:choose>
    </xsl:if>
  </xsl:template>
  
  <xsl:template name="field-complex">
    <xsl:param name="name"/>
    <xsl:param name="schema"/>
    <la:layer class="complex">
      <xsl:if test="$schema/@lc:title">
        <h3>
          <xsl:value-of select="lx:first-case(lc:schema-get-title($schema, .))"/>
        </h3>
      </xsl:if>
      <div class="field-complex clear-block">
        <xsl:apply-templates select="lc:get-model()/lc:annotations/lc:message"/>
        <xsl:apply-templates select="@*" mode="field">
          <xsl:with-param name="parent" select="$name"/>
          <xsl:with-param name="parent-schema" select="$schema"/>
        </xsl:apply-templates>
        <xsl:apply-templates select="*" mode="field">
          <xsl:with-param name="parent" select="$name"/>
          <xsl:with-param name="parent-schema" select="$schema"/>
        </xsl:apply-templates>
      </div>
    </la:layer>
  </xsl:template>
  
  <xsl:template name="field-simple">
    <xsl:param name="id"/>
    <xsl:param name="name"/>
    <xsl:param name="content"/>
    <xsl:param name="schema"/>
    <xsl:param name="model"/>
    <xsl:choose>
      <xsl:when test="lc:is-visible($schema)">
        <xsl:variable name="statut">
          <xsl:choose>
            <xsl:when test="@lc:model">
              <xsl:value-of select="concat('field-statut-', lc:get-statut())"/>
            </xsl:when>
            <xsl:otherwise>field-statut-attr</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:variable name="label">
          <xsl:apply-templates select="." mode="label">
            <xsl:with-param name="id" select="$id"/>
          </xsl:apply-templates>
        </xsl:variable>
        <div class="field clear-block {$statut}" id="field-container-{$name}">
          <xsl:choose>
            <xsl:when test="lc:schema-is-boolean($schema)">
              <xsl:copy-of select="$content"/>
              <xsl:copy-of select="$label"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:copy-of select="$label"/>
              <xsl:copy-of select="$content"/>
            </xsl:otherwise>
          </xsl:choose>
          <xsl:if test="@lc:model">
            <xsl:apply-templates select="$model/lc:annotations/lc:message"/>
          </xsl:if>
        </div>
      </xsl:when>
      <xsl:otherwise>
        <xsl:copy-of select="$content"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="*" mode="notice">
    <div class="field-notice">
      Les champs marqués d'un <strong>*</strong> sont obligatoires
    </div>
  </xsl:template>
  
  <xsl:template match="*" mode="label">
    <xsl:param name="id"/>
    <label for="{$id}">
      <xsl:value-of select="lx:first-case(lc:get-title())"/>
      <xsl:if test="not(lc:is-boolean())">
        <xsl:text> : </xsl:text>
      </xsl:if>
      <xsl:if test="not(lc:get-statut() = 'optional')"> *</xsl:if>
    </label>
  </xsl:template>
  
  <xsl:template name="input">
    <xsl:param name="id"/>
    <xsl:param name="name"/>
    <xsl:param name="class"/>
    <xsl:param name="schema"/>
    <xsl:variable name="is-visible" select="lc:is-visible($schema)"/>
    <xsl:variable name="is-editable" select="lc:is-editable($schema)"/>
    <xsl:choose>
      <xsl:when test="not($is-visible) and $is-editable">
        <input type="hidden" class="{$class}" name="{$name}" id="{$id}" value="{.}"/>
      </xsl:when>
      <xsl:when test="not($is-editable) and $is-visible">
        <span class="{$class}" id="{$id}">
          <xsl:value-of select="."/>
        </span>
      </xsl:when>
      <xsl:when test="not($is-editable) and not($is-visible)"/>
      <xsl:when test="lc:schema-is-keyref($schema)">
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
      <xsl:when test="lc:schema-is-string($schema)">
        <xsl:choose>
          <xsl:when test="lc:schema-is-enum($schema)">
            <select name="{$name}" id="{$id}" class="{$class}">
              <option value="0">&lt; choisissez &gt;</option>
              <xsl:apply-templates select="$schema/lc:restriction/lc:enumeration">
                <xsl:with-param name="value" select="node()"/>
              </xsl:apply-templates>
            </select>
          </xsl:when>
          <xsl:when test="lc:boolean($schema/@lc:line-break) or lc:boolean($schema/@lc:wiki)">
            <textarea id="{$id}" name="{$name}" class="{$class}">
              <xsl:value-of select="."/>
            </textarea>
          </xsl:when>
          <xsl:otherwise>
            <input type="text" value="{.}" name="{$name}" id="{$id}" class="{$class}"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="lc:schema-is-date($schema)">
        <input class="{$class} field-input-date" id="{$id}" value="{.}"/>
        <input type="hidden" name="{$name}" value="{.}"/>
      </xsl:when>
      <xsl:when test="lc:schema-is-boolean($schema)">
        <input type="checkbox" id="{$id}" class="{$class} field-input-boolean" name="{$name}" value="1">
          <xsl:if test=". = '1' or . = 'true'">
            <xsl:attribute name="checked">checked</xsl:attribute>
          </xsl:if>
        </input>
      </xsl:when>
      <xsl:when test="lc:schema-is-integer($schema)">
        <input type="text" class="{$class} field-input-integer" id="{$id}" name="{$name}" value="{.}"/>
      </xsl:when>
      <xsl:otherwise>
        <textarea id="{$id}" name="{$name}" class="{$class}">
          <xsl:value-of select="."/>
        </textarea>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="*" mode="input">
    <xsl:param name="id"/>
    <xsl:param name="name"/>
    <xsl:param name="class"/>
    <xsl:param name="schema"/>
    <xsl:call-template name="input">
      <xsl:with-param name="id" select="$id"/>
      <xsl:with-param name="name" select="$name"/>
      <xsl:with-param name="class" select="$class"/>
      <xsl:with-param name="schema" select="$schema"/>
    </xsl:call-template>
  </xsl:template>
  
  <xsl:template match="@*" mode="input">
    <xsl:param name="id"/>
    <xsl:param name="name"/>
    <xsl:param name="class"/>
    <xsl:param name="schema"/>
    <xsl:call-template name="input">
      <xsl:with-param name="id" select="$id"/>
      <xsl:with-param name="name" select="$name"/>
      <xsl:with-param name="class" select="$class"/>
      <xsl:with-param name="schema" select="$schema"/>
    </xsl:call-template>
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
        <xsl:when test="@key">
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
  
</xsl:stylesheet>
