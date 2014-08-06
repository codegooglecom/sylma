<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://www.sylma.org/modules/uploader/test/file"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"

  xmlns:user="http://www.sylma.org/modules/uploader/test/user"
>

  <table name="uploader_file03" connection="test">

    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>
    <field name="path" type="sql:string-short"/>
    <field name="size" type="sql:string-short"/>
    <field name="extension" type="sql:string-short"/>

    <foreign name="parent" occurs="0..1" table="user:uploader03" import="fieldset_user.xql"/>

  </table>

</schema>

