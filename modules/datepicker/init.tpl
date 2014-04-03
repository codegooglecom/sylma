<?xml version="1.0" encoding="utf-8"?>
<tpl:templates
  xmlns:crud="http://2013.sylma.org/view/crud"
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"
>

  <tpl:template match="*" mode="date/prepare" once="x">
    <le:context name="js">
      <le:file>/#sylma/ui/Extras.js</le:file>
      <le:file>/#sylma/ui/Locale.js</le:file>
      <le:file>/#sylma/ui/Date.js</le:file>
      <le:file>arian/Locale.fr-FR.DatePicker.js</le:file>
      <le:file>arian/Picker.js</le:file>
      <le:file>arian/Picker.Attach.js</le:file>
      <le:file>arian/Picker.Date.js</le:file>
      <le:file>date.js</le:file>
    </le:context>
    <le:context name="css">
      <le:file>arian/datepicker_jqui/datepicker_jqui.css</le:file>
    </le:context>
  </tpl:template>

</tpl:templates>
