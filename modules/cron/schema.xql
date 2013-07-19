<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma,org/modules/cron"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
>

  <xs:simpleType name="recurrence">
    <xs:restriction base="sql:string">
      <xs:maxLength value="6"/>
    </xs:restriction>
  </xs:simpleType>

  <table name="cron">
    <field name="id" type="sql:id"/>
    <field name="title" type="sql:string"/>
    <field name="command" type="sql:string"/>
    <field name="hour" type="recurrence" default="'*'"/>
    <field name="minute" type="recurrence"/>
    <field name="day" title="day of month" type="recurrence" default="'*'"/>
    <field name="weekday" title="day of week" type="recurrence" default="'*'"/>
    <field name="month" type="recurrence" default="'*'"/>
    <field name="disabled" type="sql:boolean" default="null"/>
  </table>

</schema>

