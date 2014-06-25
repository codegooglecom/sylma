<?xml version="1.0" encoding="utf-8"?>
<tpl:templates
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:xl="http://2013.sylma.org/storage/xml"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"
>

  <tpl:template match="*" mode="tab/js">

    <js:include>/#sylma/crud/Form.js</js:include>
    <js:include>/#sylma/ui/Clonable.js</js:include>
    <js:include>/#sylma/crud/Group.js</js:include>

    <js:include>Main.js</js:include>
    <js:include>Head.js</js:include>
    <js:include>Container.js</js:include>
    <js:include>Caller.js</js:include>
    <js:include>Tab.js</js:include>
  </tpl:template>

</tpl:templates>
