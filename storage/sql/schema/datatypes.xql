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

  <xs:simpleType name="string-short">
    <xs:restriction base="xs:string">
      <xs:maxLength value="64"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="id" type="xs:integer"/>

  <xs:simpleType name="string">
    <xs:restriction base="xs:string">
      <xs:maxLength value="255"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="string-long">
    <xs:restriction base="xs:string">
      <xs:maxLength value="65536"/>
    </xs:restriction>
  </xs:simpleType>

  <xs:simpleType name="datetime">
    <xs:restriction base="xs:string">
      <xs:maxLength value="15"/>
    </xs:restriction>
  </xs:simpleType>

</sql:schema>
