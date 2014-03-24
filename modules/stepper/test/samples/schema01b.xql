<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/modules/stepper/test/sample1"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
>

  <table name="sylma_stepper_sample01" connection="test">
    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>
    <field name="email" type="sql:string"/>
    <field name="update" type="sql:datetime"/>
  </table>

</schema>

