<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/view/test/sample1"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema"
  
  xmlns:group="http://2013.sylma.org/view/test/sample2"
>

  <!-- must use same sql table than user1.xql for references tests -->
  <table name="user2" connection="test">
    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>
    <field name="email" type="sql:string-short"/>
    <foreign occurs="0..1" name="group_id" table="group:group" import="group1.xql"/>
  </table>

</schema>

