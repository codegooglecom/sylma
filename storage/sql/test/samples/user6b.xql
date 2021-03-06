<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/view/test/sample1"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema"

  xmlns:city="http://2013.sylma.org/storage/sql/test/city"
>

  <table name="user6b" connection="test">
    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>
    <foreign occurs="0..1" name="city" table="city:city01" import="city01.xql"/>
  </table>

</schema>

