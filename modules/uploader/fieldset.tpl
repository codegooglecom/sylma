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

  <tpl:import>form.tpl</tpl:import>

  <tpl:template match="*" mode="file/resources">

    <js:include>/#sylma/crud/Form.js</js:include>

    <tpl:apply mode="reference/js"/>
    <tpl:apply mode="file/resources"/>

    <js:include>MainPosition.js</js:include>
    <js:include>MainFieldset.js</js:include>
    <js:include>/#sylma/crud/fieldset/RowMovable.js</js:include>

  </tpl:template>

  <tpl:template match="*" mode="file">

    <tpl:apply mode="file/resources"/>
    <tpl:apply mode="file/content"/>

  </tpl:template>

  <tpl:template match="*" mode="file/content">

    <tpl:apply mode="file/dropper"/>
    <tpl:apply mode="file/ref"/>

  </tpl:template>

  <tpl:template match="*" mode="file/ref">
    <div js:class="sylma.crud.Group" js:name="content">
      <tpl:apply select="ref()" mode="file/update">
        <tpl:read select="alias('form')" tpl:name="alias"/>
      </tpl:apply>
    </div>
  </tpl:template>

  <tpl:template mode="file/container">
    <div js:class="sylma.uploader.MainFieldset" js:parent-name="uploader-container">
      <tpl:apply mode="form/build"/>
      <tpl:apply mode="file/form"/>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="file/fieldset">
    <fieldset js:class="sylma.crud.fieldset.Container" js:parent-name="fieldset">
      <tpl:apply mode="legend"/>
      <tpl:apply mode="file"/>
    </fieldset>
  </tpl:template>

  <tpl:template match="*" mode="file/update">
    <tpl:argument name="position" default="position()"/>
    <tpl:argument name="alias"/>
    <tpl:argument name="prefix" default="'{$alias}[{$position}]'"/>

    <!-- @usedby gallery.tpl -->
    <tpl:variable name="extension">
      <tpl:apply mode="file/extension"/>
    </tpl:variable>

    <div js:class="sylma.crud.fieldset.RowMovable" class="file hidder visible form-reference file-{$extension}">
      <tpl:apply mode="file/inputs">
        <tpl:read select="$prefix" tpl:name="prefix"/>
        <tpl:read select="$position" tpl:name="position"/>
      </tpl:apply>
      <tpl:apply mode="file/view"/>
      <tpl:apply mode="file/actions"/>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="file/extension">
    <tpl:read select="extension"/>
  </tpl:template>

  <tpl:template match="*" mode="file/actions">
    <div class="actions">
      <tpl:apply mode="file/actions/content"/>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="file/actions/content">
    <tpl:apply mode="row/remove"/>
  </tpl:template>

  <tpl:template match="*" mode="file/inputs">

    <tpl:argument name="prefix"/>

    <input type="hidden" name="{$prefix}[name]" value="{name}"/>
    <input type="hidden" name="{$prefix}[path]" value="{path}"/>
    <input type="hidden" name="{$prefix}[size]" value="{size}"/>
    <input type="hidden" name="{$prefix}[extension]" value="{extension}"/>

  </tpl:template>

  <tpl:template match="*" mode="file/view">
    <div class="infos">
      <h4><tpl:read select="name"/></h4>
      <em><tpl:read select="size"/> Ko</em>
    </div>
  </tpl:template>

</tpl:templates>
