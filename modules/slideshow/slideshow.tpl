<?xml version="1.0" encoding="utf-8"?>
<tpl:templates
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
>

  <tpl:template>

    <le:context name="css">
      <le:file>common.less</le:file>
    </le:context>

    <js:include>/#sylma/ui/Loader.js</js:include>
    <js:include>Container.js</js:include>
    <js:include>Slide.js</js:include>

    <div js:class="sylma.slideshow.Container" js:parent-name="handler" js:name="handler" class="slideshow">

      <js:option name="directory" cast="x">
        <tpl:apply mode="slideshow/files" required="x"/>
      </js:option>

      <tpl:apply mode="top"/>

      <div class="loading" js:node="loading"/>
      <div class="container" js:node="container">
        <tpl:apply mode="slideshow/container"/>
      </div>

      <tpl:apply mode="slideshow/pager"/>

    </div>

  </tpl:template>

  <tpl:template mode="slideshow/container">
    <tpl:apply mode="query"/>
    <tpl:apply select="*" mode="slideshow/tree"/>
  </tpl:template>

  <tpl:template match="*" mode="slideshow/pager">

    <div class="pager" js:node="pager">
      <a href="javascript:void(0)" class="previous">
        <js:event name="click">
          %object%.goPrevious('normal');
          %object%.resetLoop();
        </js:event>
        &lt;&lt;
      </a>
      <div class="pages" js:node="pages"/>
      <a href="javascript:void(0)" class="next">
        <js:event name="click">
          %object%.goNext('normal');
          %object%.resetLoop();
        </js:event>
        &gt;&gt;
      </a>
    </div>

  </tpl:template>

  <tpl:template match="*" mode="slideshow/item">
    <div js:class="sylma.slideshow.Slide" class="slide">
      <js:option name="path">
        <tpl:read select="path"/>
      </js:option>
      <js:option name="id">
        <tpl:read select="parent()/parent()/id"/>
      </js:option>
    </div>

  </tpl:template>

</tpl:templates>
