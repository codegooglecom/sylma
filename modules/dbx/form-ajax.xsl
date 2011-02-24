<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" xmlns:lc="http://www.sylma.org/schemas" xmlns:lx="http://ns.sylma.org/xslt" xmlns:la="http://www.sylma.org/processors/action-builder" version="1.0" extension-element-prefixes="func lx">
  <xsl:import href="../../schemas/functions.xsl"/>
  <xsl:import href="/sylma/xslt/string.xsl"/>
  <xsl:param name="action"/>
  <xsl:param name="method" select="'POST'"/>
  <func:function name="lc:is-visible">
    <xsl:param name="source" select="."/>
    <func:result select="not(./@lc:visible = 'false')"/>
  </func:function>
  <xsl:template match="/*">
    <form method="{$method}" action="{$action}" enctype="multipart/form-data">
      <xsl:apply-templates select="lc:get-model(*[1])/lc:annotations/lc:message"/>
      <xsl:apply-templates select="*[1]/@*"/>
      <xsl:apply-templates select="*[1]/*" mode="field"/>
      <xsl:apply-templates select="*[1]" mode="notice"/>
      <div class="field-actions">
        <input type="submit" value="Enregistrer"/>
        <input type="button" value="Annuler" onclick="history.go(-1);"/>
      </div>
    </form>
  </xsl:template>
  <xsl:template match="*" mode="notice">
    <div class="field-notice">
      Les champs marqués d'un <strong>*</strong> sont obligatoires
    </div>
  </xsl:template>
  <xsl:template match="*" mode="field">
    <xsl:param name="parent"/>
    <xsl:variable name="name">
      <xsl:choose>
        <xsl:when test="$parent">
          <xsl:value-of select="concat($parent, '[', lc:get-name(), ']')"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="lc:get-name()"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="id" select="concat('field-', lc:get-name())"/>
    <xsl:variable name="statut" select="concat('field-statut-', lc:get-statut())"/>
    <xsl:variable select="'field-input-element'" name="class"/>
    <xsl:variable name="content">
      <xsl:apply-templates select="." mode="input">
        <xsl:with-param name="id" select="$id"/>
        <xsl:with-param name="name" select="$name"/>
        <xsl:with-param name="class" select="$class"/>
      </xsl:apply-templates>
      <xsl:apply-templates select="@*"/>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="lc:is-complex()">
        <xsl:choose>
          <xsl:when test="lc:is-required()">
            <la:layer class="complex">
              <h3>
                <xsl:value-of select="lc:get-title()"/>
              </h3>
              <div class="field-complex clear-block">
                <xsl:apply-templates select="@*"/>
                <xsl:apply-templates select="*" mode="field">
                  <xsl:with-param name="parent" select="$name"/>
                </xsl:apply-templates>
              </div>
            </la:layer>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="lc:is-visible()">
        <xsl:variable name="label">
          <xsl:apply-templates select="." mode="label">
            <xsl:with-param name="id" select="$id"/>
          </xsl:apply-templates>
        </xsl:variable>
        <div class="field clear-block {$statut}" id="field-container-{$name}">
          <xsl:choose>
            <xsl:when test="lc:is-boolean()">
              <xsl:copy-of select="$content"/>
              <xsl:copy-of select="$label"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:copy-of select="$label"/>
              <xsl:copy-of select="$content"/>
            </xsl:otherwise>
          </xsl:choose>
          <xsl:apply-templates select="lc:get-model()/lc:annotations/lc:message"/>
        </div>
      </xsl:when>
      <xsl:otherwise>
        <xsl:copy-of select="$content"/>
      </xsl:otherwise>
    </xsl:choose>
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
  <xsl:template match="*" mode="input">
    <xsl:param name="id"/>
    <xsl:param name="name"/>
    <xsl:param name="class"/>
    <xsl:choose>
      <xsl:when test="not(lc:get-model())">
        <textarea name="{$name}" id="{$id}" style="background-color: #eee" class="{$class}">
          <xsl:value-of select="."/>
        </textarea>
      </xsl:when>
      <xsl:when test="@lc:visible = 'false' and not(@lc:editable = 'false')">
        <input type="hidden" class="{$class}" name="{$name}" id="{$id}" value="{.}"/>
      </xsl:when>
      <xsl:when test="@lc:editable = 'false' and not(@lc:visible = 'false')">
        <span class="{$class}" id="{$id}">
          <xsl:value-of select="."/>
        </span>
      </xsl:when>
      <xsl:when test="@lc:editable = 'false' and @lc:visible = 'false'"/>
      <xsl:when test="lc:is-keyref()">
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
      <xsl:when test="lc:is-string()">
        <xsl:choose>
          <xsl:when test="lc:is-enum()">
            <select name="{$name}" id="{$id}" class="{$class}">
              <option value="0">&lt; choisissez &gt;</option>
              <xsl:apply-templates select="lc:get-schema()/lc:restriction/lc:enumeration">
                <xsl:with-param name="value" select="node()"/>
              </xsl:apply-templates>
            </select>
          </xsl:when>
          <xsl:when test="@lc:line-break or @lc:wiki">
            <textarea id="{$id}" name="{$name}" class="{$class}">
              <xsl:value-of select="."/>
            </textarea>
          </xsl:when>
          <xsl:otherwise>
            <input type="text" value="{.}" name="{$name}" id="{$id}" class="{$class}"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="lc:is-date()">
        <input class="{$class} field-input-date" id="{$id}" value="{.}"/>
        <input type="hidden" name="{$name}" value="{.}"/>
      </xsl:when>
      <xsl:when test="lc:is-boolean()">
        <input type="checkbox" id="{$id}" class="{$class} field-input-boolean" name="{$name}" value="1">
          <xsl:if test=". = '1' or . = 'true'">
            <xsl:attribute name="checked">checked</xsl:attribute>
          </xsl:if>
        </input>
      </xsl:when>
      <xsl:when test="lc:is-integer()">
        <input type="text" class="{$class} field-input-integer" id="{$id}" name="{$name}" value="{.}"/>
      </xsl:when>
      <xsl:otherwise>
        <textarea id="{$id}" name="{$name}" class="{$class}">
          <xsl:value-of select="."/>
        </textarea>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template match="@*">
    <xsl:if test="namespace-uri() != 'http://www.sylma.org/schemas'">
      <input type="hidden" value="{.}" name="attr-{local-name()}"/>
    </xsl:if>
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
