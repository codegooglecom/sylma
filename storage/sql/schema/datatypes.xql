<?xml version="1.0"?>
<sql:schema version="1.0"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"

  targetNamespace="http://2013.sylma.org/storage/sql"
  elementFormDefault="qualified"
>

  <sql:group name="table-dated">
    <sql:field name="date-publish" type="xs:dateTime"/>
    <sql:field name="date-update" type="xs:dateTime"/>
  </sql:group>

  <xs:simpleType name="int" reflector="\sylma\schema\cached\form\_Integer">
    <xs:restriction base="xs:integer">
      <xs:totalDigits value="6"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="id" reflector="\sylma\schema\cached\form\_Integer">
    <xs:restriction base="sql:int"/>
  </xs:simpleType>

  <xs:simpleType name="float" reflector="\sylma\schema\cached\form\_Float" reflector-static="\sylma\schema\cached\view\Float">
    <xs:restriction base="xs:float">
      <xs:fractionDigits value="6"/>
      <xs:totalDigits value="6"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="boolean" type="xs:boolean"/>

  <xs:simpleType name="string" reflector-static="\sylma\schema\cached\view\_String">
    <xs:restriction base="xs:string">
      <xs:maxLength value="255"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="string-short">
    <xs:restriction base="sql:string">
      <xs:maxLength value="64"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="string-long">
    <xs:restriction base="sql:string">
      <xs:maxLength value="65535"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="text">
    <xs:restriction base="sql:string"/>
  </xs:simpleType>

  <xs:simpleType name="datetime" reflector-static="\sylma\schema\cached\view\Datetime">
    <xs:restriction base="xs:string">
      <xs:maxLength value="15"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="html">
    <xs:restriction base="sql:text"/>
  </xs:simpleType>

</sql:schema>
