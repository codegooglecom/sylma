<?xml version="1.0" encoding="utf-8"?>
<tpl:templates
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"

  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"

  xmlns:upl="http://2013.sylma.org/modules/uploader"
>

  <tpl:template match="*" mode="file/resources">

    <js:include>/#sylma/crud/Form.js</js:include>

    <le:context name="js">
      <le:file>Main.js</le:file>
      <le:file>Dropper.js</le:file>
    </le:context>

    <le:context name="css">
      <le:file>form.less</le:file>
    </le:context>

  </tpl:template>

  <tpl:template match="*" mode="file">

    <tpl:apply mode="file/resources"/>
    <tpl:apply mode="file/dropper"/>

  </tpl:template>

  <tpl:template match="*" mode="file/dropper">

    <tpl:argument name="alias" default="alias('form')"/>

    <div js:name="template" js:class="sylma.uploader.Dropper" class="clearfix sylma-uploader dropper">
      <input type="file" name="{$alias}">
        <js:event name="change">return %object%.sendFile(this);</js:event>
      </input>
      <tpl:apply mode="file/tree"/>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="file/form">

    <tpl:variable name="name">
      <tpl:read select="gen('uploader')"/>
    </tpl:variable>

    <form class="sylma-uploader" target="{$name}" enctype="multipart/form-data" method="post" js:class="sylma.ui.Container" js:name="uploader">
      <js:option name="ajax">1</js:option>
      <tpl:apply mode="file/form/init"/>
      <input type="hidden" name="position" js:node="position"/>
      <iframe name="{$name}" js:node="iframe"/>
    </form>
  </tpl:template>

  <tpl:template match="*" mode="file/form/init">
    <tpl:token name="action">
      <le:path>validate</le:path>
      <tpl:text>.json</tpl:text>
    </tpl:token>
  </tpl:template>

  <tpl:template match="*" mode="file/tree">
    <tpl:apply reflector="Tree" mode="file/tree/content"/>
  </tpl:template>

  <tpl:template match="*" mode="file/settings">
    <tpl:apply select="init('doc', 'docx', 'pdf', 'png','jpg','jpeg')"/>
  </tpl:template>

  <tpl:template match="*" mode="file/tree/content">
    <tpl:apply mode="file/settings"/>
    <div class="infos">
      <div class="loader">... loading ...</div>
      <div class="todo">
        <span class="events">
          <em>click</em> or <em>drag &amp; drop</em>
        </span>
        <span class="max">
          - max <em><tpl:read select="max-size()"/></em>
        </span>
      </div>
      <div class="extensions">
        <tpl:apply select="extensions()"/>
      </div>
    </div>
  </tpl:template>

  <tpl:template match="upl:extension">
    <span class="file-{read()}">
      <tpl:read/>
    </span>
  </tpl:template>

</tpl:templates>
