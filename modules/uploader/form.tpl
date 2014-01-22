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

    <le:context name="js">
      <le:file>Form.js</le:file>
      <le:file>Dropper.js</le:file>
      <le:file>File.js</le:file>
      <le:file>Scroller.js</le:file>
    </le:context>

    <tpl:apply mode="file/content"/>

  </tpl:template>

  <tpl:template match="*" mode="file/content">

    <tpl:apply mode="file/dropper"/>
    <tpl:apply mode="file/ref"/>
    <tpl:apply mode="file/scroller"/>

  </tpl:template>

  <tpl:template match="*" mode="file/ref">
    <div js:class="sylma.crud.Group" js:name="content">
      <tpl:apply select="ref()" mode="file/update">
        <tpl:read select="alias('form')" tpl:name="alias"/>
      </tpl:apply>
    </div>
  </tpl:template>

  <tpl:template match="*" mode="file/dropper">

    <div js:name="template" js:class="sylma.uploader.Dropper" class="clearfix sylma-uploader">
      <tpl:apply reflector="Tree"/>
      <div class="sylma-uploader-dropper">
        <input type="file" name="{alias('form')}">
          <js:event name="change">return %object%.sendFile(this);</js:event>
        </input>
        <span class="sylma-hidder" js:node="loading">Loading ...</span>
      </div>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="file/scroller">

    <div js:class="sylma.uploader.Scroller" js:name="scroller" class="scroller sylma-hidder">

      <js:event name="mouseout">
        %object%.stopScroll();
      </js:event>
      <div js:node="top" class="top">
        <js:event name="mouseenter">
          %object%.scroll(-1,e);
        </js:event>
      </div>
      <div js:node="bottom" class="bottom">
        <js:event name="mouseenter">
          %object%.scroll(1,e);
        </js:event>
      </div>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="file/form">
    <le:context name="css">
      <le:file>form.less</le:file>
    </le:context>
    <form js:name="uploader" js:class="sylma.uploader.Form" class="sylma-uploader" target="sylma-uploader-iframe" enctype="multipart/form-data" method="post">
      <input js:node="position" type="hidden" name="position"/>
      <tpl:apply mode="file/form/init"/>
      <iframe name="sylma-uploader-iframe">
        <js:event name="load">
          %object%.update(this.contentWindow.document.body);
        </js:event>
      </iframe>
    </form>
  </tpl:template>

  <tpl:template match="*" mode="file/fieldset">
    <fieldset js:class="sylma.crud.fieldset.Container" js:parent-name="fieldset">
      <tpl:apply mode="legend"/>
      <tpl:apply mode="file"/>
    </fieldset>
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
    <tpl:argument name="position" default="position()"/>
    <tpl:argument name="alias"/>
    <tpl:argument name="prefix" default="'{$alias}[{$position}]'"/>

    <div js:class="sylma.uploader.File" class="field-file sylma-hidder sylma-visible form-reference field-file-extension-{extension}">
      <tpl:apply mode="file/inputs">
        <tpl:read select="$prefix" tpl:name="prefix"/>
        <tpl:read select="$position" tpl:name="position"/>
      </tpl:apply>
      <tpl:apply mode="file/view"/>
      <div class="actions">
        <button type="button">
          <js:event name="click">
            %object%.remove(e);
          </js:event>
          <tpl:text>-</tpl:text>
        </button>
        <button type="button" js:node="move">
          <js:event name="mousedown">
            %object%.drag(e);
          </js:event>
          <tpl:text>↕</tpl:text>
        </button>
      </div>
    </div>

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

</view:view>
