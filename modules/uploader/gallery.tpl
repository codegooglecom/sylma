<?xml version="1.0" encoding="utf-8"?>
<tpl:templates
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"

  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
>

  <tpl:template match="sql:table" mode="file/view">
    <img src="{$$uploader-dir}/{path}?format=small"/>
    <tpl:apply mode="file/view"/>
  </tpl:template>

  <tpl:template match="*" mode="file/extension">preview</tpl:template>

  <tpl:template match="*" mode="file/resources">

    <tpl:apply mode="file/resources"/>

    <le:context name="css">
      <le:file>/#sylma/modules/uploader/gallery.css</le:file>
    </le:context>

  </tpl:template>

</tpl:templates>
