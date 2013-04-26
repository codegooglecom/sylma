<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:html="http://www.w3.org/1999/xhtml"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://www.sylma.org/parser/languages/php"
  xmlns:tpl="http://2013.sylma.org/template/parser"
  version="1.0"
>

  <xsl:import href="/#sylma/modules/html/copy.xsl"/>

  <xsl:template match="tpl:log">
    <div>
      <style type="text/css">
        .sylma-template-component {
          border-left: 2px dotted gray;
          padding-left: 5px;
        }
        .sylma-template-children {
          padding-left: 2em;
        }
        .sylma-template-exception {
          color: red;
        }
      </style>
      <xsl:apply-templates select="tpl:component"/>
    </div>
  </xsl:template>

  <xsl:template match="tpl:component">
    <div class="sylma-template-component sylma-debug-focused" tabindex="5">
      <span><xsl:value-of select="tpl:message"/></span>
      <div class="sylma-template-vars sylma-debug-mask">
        <xsl:apply-templates select="tpl:vars/*"/>
      </div>
      <div class="sylma-template-children">
        <xsl:apply-templates select="tpl:component"/>
      </div>
      <xsl:apply-templates select="tpl:exception"/>
    </div>
  </xsl:template>

  <xsl:template match="tpl:exception">
    <span class="sylma-template-exception">
      <xsl:value-of select="."/>
    </span>
  </xsl:template>

</xsl:stylesheet>
