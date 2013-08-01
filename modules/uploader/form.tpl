<?xml version="1.0" encoding="utf-8"?>
<view:view
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"

  xmlns:upl="http://2013.sylma.org/modules/uploader"
>

  <tpl:template match="*" mode="file">
    <div js:name="template" js:class="sylma.crud.fieldset.FileDropper" class="clearfix sylma-uploader">
      <tpl:apply reflector="Tree"/>
      <div class="sylma-uploader-dropper">
        <input type="file" name="{alias('form')}">
          <js:event name="change">return %object%.sendFile(this);</js:event>
        </input>
        <span class="sylma-hidder" js:node="loading">Loading ...</span>
      </div>
    </div>
    <tpl:apply select="ref()" mode="file/update">
      <tpl:read select="alias('form')" tpl:name="alias"/>
    </tpl:apply>
  </tpl:template>

  <tpl:template match="*" mode="file/form">
    <le:context name="css">
      <le:file>medias/form.css</le:file>
    </le:context>
    <form js:name="uploader" js:class="sylma.crud.fieldset.FileForm" class="sylma-uploader" target="sylma-uploader-iframe" enctype="multipart/form-data" method="post">
      <input js:node="position" type="hidden" name="position"/>
      <tpl:apply mode="file/form/init"/>
      <iframe name="sylma-uploader-iframe">
        <js:event name="load">
          %object%.update(this.contentWindow.document.body);
        </js:event>
      </iframe>
    </form>
  </tpl:template>

  <tpl:template match="*" mode="file/form/init">
    <tpl:token name="action">
      <le:path>validate</le:path>
      <tpl:text>.json</tpl:text>
    </tpl:token>
  </tpl:template>

  <tpl:template match="upl:root">
    <p>You can drag and drop your files (one by one) to the button below or click it to browse your file system.</p>
    <ul>
      <li>Maximum file size is
        <strong>
          <tpl:read select="max-size()"/>
        </strong>.
      </li>
      <li>
        Following extensions are allowed :
        <em>
          <tpl:read select="extensions()"/>
        </em>
      </li>
    </ul>
  </tpl:template>

  <tpl:template match="*" mode="file/update">
    <tpl:argument name="alias"/>
    <tpl:argument name="position" default="position()"/>
    <tpl:argument name="prefix" default="'{$alias}[{$position}]'"/>

    <div js:class="sylma.crud.fieldset.File" class="field-file sylma-hidder sylma-visible form-reference field-file-extension-{extension}">
      <input type="hidden" name="{$prefix}[name]" value="{name}"/>
      <input type="hidden" name="{$prefix}[path]" value="{path}"/>
      <input type="hidden" name="{$prefix}[size]" value="{size}"/>
      <input type="hidden" name="{$prefix}[extension]" value="{extension}"/>
      <h4><tpl:read select="name"/></h4>
      <em><tpl:read select="size"/> Ko</em>
      <tpl:apply mode="file/view"/>
      <button type="button" class="right">
        <js:event name="click">
          %object%.remove();
        </js:event>
        <tpl:text>-</tpl:text>
      </button>
    </div>

  </tpl:template>

</view:view>
