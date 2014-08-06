<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://www.sylma.org/modules/uploader/test/user"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"

  xmlns:file="http://www.sylma.org/modules/uploader/test/file"
>

  <table name="uploader01" connection="test">

    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>

    <reference name="files" table="file:uploader_file01" foreign="file:user" import="default_file.xql"/>

  </table>

</schema>

