<?php
//
// Update eduPersonNickname LDAP entry for user.
//
// Bill Edmunds, IT Services, University of Exeter
//
// Last modified: 16th April 2010
//

// Turn off all error reporting

error_reporting(0);

// Variables

$site = 'as.exeter.ac.uk';
include('/home/webs/' . $site . '/utils/ldap/template.php');
$uri = '/utils/helpdesk/index.php';
$heading = 'University LDAP Directory Schema';

printHeader($site,$uri,$heading);

?>

<h2><a name="#s0">Sections</a></h2>
<ul>
<li><a href="#s1">Introduction</a></li>
<li><a href="#s2">What is an Enterprise Directory?</a></li>
<li><a href="#s3">What is LDAP?</a></li>
<li><a href="#s4">LDAP Servers</a></li>
<li><a href="#s5">The Directory Information Tree</a></li>
<li><a href="#s6">Schema Declarations</a></li>
<li><a href="#s7">ObjectClasses</a></li>
<li><a href="#s8">Attributes Relating To People</a></li>
<li><a href="#s9">Attributes Relating To The Organisation</a></li>
<li><a href="#s10">A Sample Staff Entry</a></li>
<li><a href="#s11">A Sample Student Entry</a></li>
<li><a href="#s12">A Sample Associate Entry</a></li>
<li><a href="#s13">Further Information For Sys Admins And Exeter Users</a></li>
</ul>
<h2><a name="s1">Introduction</a></h2>
<p>This document provides details of the Enterprise Directory and LDAP schema in use at the University
of Exeter.</p>
<p><a href="#s0">Top</a></p>
<h2><a name="s2">What is an Enterprise Directory?</a></h2>
<p>An Enterprise Directory is a database specifically designed for the searching and browsing of information
relevant to an organisation. The directory provides an online view of users and resources
within the organisation under an accepted protocol for ease of managing and sharing directory profile
information. These directories represent users, applications and network resources as objects in a hierarchical
tree. The directory may provide a central view of all available resources on the network, as well as facilitate
the administration of user rights, profiles and permissions. The directory can provide authentication for services
within the organisation.</p>
<p><a href="#s0">Top</a></p>
<h2><a name="s3">What is LDAP?</a></h2>
<p>LDAP stands for the Lightweight Directory Access Protocol. It is a lightweight version of an earlier directory
standard called X.500 (DAP) that works over TCP/IP.</p>
<p>Typically, an LDAP directory has a hierarchical structure and is organized to show different levels within the
organisation, such as:
<ol>
  <li>The root directory</li>
  <li>Countries</li>
  <li>Organisations</li>
  <li>Schools, divisions, departments, etc.</li>
  <li>Individuals</li>
  <li>Individual resources, such as workstations and printers.</li>
</ol>
<p><a href="#s0">Top</a></p>
<h2><a name="s4">LDAP Servers</a></h2>
<p>Currently, the university runs a single LDAP master server and two (read
  only) slave servers. The slave servers provide resilience and are 
  generally accessible as:</p>
<pre>
ldap.ex.ac.uk

</pre>
<p>The default port is port <strong>389</strong>. The secure (SSL) port is port 
  <strong>636</strong>.</p>
<p><a href="#s0">Top</a></p>
<h2><a name="s5">The Directory Information Tree</a></h2>
<p>The root of the DIT for the university is: <strong>dc=exeter, dc=ac, dc=uk</strong>.
The following containers appear below the root...</p>
<table width="95%" border="1" cellspacing="5" cellpadding="5">
  <tr>
    <td><strong>Name</strong></td>
    <td><strong>Description</strong></td>
  </tr>
  <tr>
    <td><strong>ou = University of Exeter</strong></td>
    <td>The Organisational Structure in terms of Schools, departments, centres, etc.<br />
	Example DN: <em>ou=law, ou=School of Law, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = admins</strong></td>
    <td>Directory administrators and special users.<br />
	Example DN: <em>cn=admin1, ou=admins, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = associated</strong></td>
    <td>Intended for people entries removed from <em>ou=people</em>. Not currently in use.<br />
	Example DN: <em>uid=old201, ou=associated, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = computers</strong></td>
    <td>Computer registrations to support use of Samba for PC clusters.<br />
      Example DN: <em>uid=cronus, ou=computers, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = groups</strong></td>
    <td>Arbitrary Groups of "people", "computers", etc. Also used to support NIS 
      and MeetingMaker groups.<br />
      Example DN: <em>cn=ecu, ou=nis, ou=groups, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = hosts</strong></td>
    <td>Identity of hosts within the organisation. Not currently in use.<br />
      Example DN: <em>cn=undefined, ou=associated, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = mmresources</strong></td>
    <td>Used to support resources for MeetingMaker calendaring.<br />
      Example DN: <em>ou=IT Services, ou=mmresources, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = people</strong></td>
    <td>People within the organisation. Used to seed NIS and provide authentication.<br />
	Example DN: <em>uid=new201, ou=people, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = prototypes</strong></td>
    <td>Prototypes of services. Not currently in use.<br />
	Example DN: <em>cn=undefined, ou=prototypes, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = services</strong></td>
    <td>Service location information, e.g. printers. Not currently in use.<br />
	Example DN: <em>cn=undefined, ou=printers=old201, ou=services, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
  <tr>
    <td><strong>ou = software</strong></td>
    <td>Software Configuration data. Not currently in use.<br />
      Example DN: <em>cn=undefined, ou=software, dc=exeter, dc=ac, dc=uk</em></td>
  </tr>
</table>
<p><a href="#s0">Top</a></p>
<h2><a name="s6">Schema Declarations</a></h2>
<p>The folowing schema declarations are in use at the University of Exeter
in addition to the default schema definitions.</p>
<table align="center" cellpadding="5" cellspacing="5">
<tr>
<td><strong>eduorg.schema</strong></td>
<td>Intended to hold the University Oraganizational Chart</td>
</tr>
<tr>
<td><strong>eduperson.schema</strong></td>
<td>To extend local schema for Shibboleth and other Applications</td>
</tr>
<tr>
<td><strong>exeterperson.schema</strong></td>
<td>Local University of Exeter schema</td>
</tr>
<tr>
<td><strong>nis.schema</strong></td>
<td>Used to support NIS on Unix Systems</td>
</tr>
<tr>
<td><strong>samba.schema</strong></td>
<td>Used to support (legacy) SAMBA at University of Exeter</td>
</tr>
<tr>
<td><strong>solaris.schema</strong></td>
<td>For compatibility with Solaris</td>
</tr>
</table>
</p>
<p><a href="#s0">Top</a></p>
<h2><a name="s7">ObjectClasses</a></h2>
<p>The following objectClasses are currently in use:</p>
<table width="95%" border="1" cellspacing="5" cellpadding="5">
  <tr>
    <td><strong>Name</strong></td>
    <td><strong>Description</strong></td>
  </tr>
  <tr>
    <td><strong>eduOrg</strong></td>
    <td>An object class for representing institutions of higher education.</td>
  </tr>
  <tr>
    <td><strong>eduPerson</strong></td>
    <td>An auxiliary object class for campus directories designed to facilitate communication among higher
        education institutions. Contains for support for identity management systems, such as Shibboleth.</td>
  </tr>
  <tr>
    <td><strong>exeterPerson</strong></td>
    <td>A local auxiliary object class describing people attributes specific to the University of Exeter.</td>
  </tr>
  <tr>
    <td><strong>inetOrgPerson<br />
                organizationalPerson<br />
                person<br />
                </strong></td>
    <td>General purpose object classes that hold attributes about people.</td>
  </tr>
  <tr>
    <td><strong>organization<br />
                organizationalUnit</strong></td>
    <td>General purpose object classes that hold attributes about organisations.</td>
  </tr>
  <tr>
    <td><strong>posixAccount<br />
                posixGroup<br />
                shadowAccount<br />
                device</strong></td>
    <td>Object classes that hold attributes pertaining to (Unix) account details.</td>
  </tr>
  <tr>
    <td><strong>sambaAccount</strong></td>
    <td>An object class used to support the Samba application. Contains attributes relevant to people
        and computers.</td>
  </tr>
</table>
<p><a href="#s0">Top</a></p>
<h2><a name="s8">Attributes Relating To People</a></h2>
<p>The following attributes are currently in use:</p>
<table width="95%" border="1" cellspacing="5" cellpadding="5">
  <tr>
    <td><strong>Attribute</strong></td>
	<td><strong>Access</strong></td>
    <td><strong>Example</strong></td>
    <td><strong>Details</strong></td>
  </tr>
  <tr>
    <td><strong>uid</strong></td>
	<td><em>a</em></td>
    <td>ano201</td>
    <td>(Unix) Account name. This is also used as part of the distinguished name (dn),
	    e.g. uid=ano201, ou=people, dc=exeter, dc=ac, dc=uk</td>
  </tr>
  <tr>
    <td><strong>givenName</strong><br />
	    <strong>sn</strong><br />
	    <strong>cn</strong><br />
	    <strong>displayName</strong><br />
	    <strong>eduPersonNickname</strong></td>
	<td><em>a</em><br />
      <em>a</em><br />
      <em>Sa</em><br />
      <em>a</em><br />
      <em>Sa</em><br />
      </td>
    <td>Andrew Neil<br />
	    Other<br />
      Andy Other<br />
		A.N.Other<br />
		Andy<br />
		</td>
    <td>Forenames.<br />
	    Surname.<br />
      Common name. Originally set to <em>&quot;eduPersonNickname sn</em>&quot;.<br />
	    Display name.<br />
      Preferred name can be amended by the user. Orginally set to <em>&quot;gn&quot;</em>.</td>
  </tr>
  <tr>
    <td><strong>userPassword</strong></strong><br /><br />
	    <strong>lmPassword</strong><br />
	    <strong>ntPassword</strong></td>
	<td><em>S</em></td>
    <td>XXXXXXXXXXXXX<br />
        <br />
        XXXXXXXXXXXXX<br />
        XXXXXXXXXXXXX</td>
    <td>The primary password entry used for controlling access to the directory for each account. This is also
	    used as the (Unix) account password.<br />
	    The synchronised LanMan password entry.<br />
	    The synchronised NT password entry.</td>
  </tr>
  <tr>
    <td><strong>mail</strong></td>
	<td><em>a</em></td>
    <td>A.N.Other@exeter.ac.uk</td>
    <td>Email address.</td>
  </tr>
  <tr>
    <td><strong>telephone</strong></td>
	<td><em>a</em></td>
    <td>3263</td>
    <td>Telephone extension or telephone number.</td>
  </tr>
  <tr>
    <td><strong>exeterStatus</strong></td>
	<td><em>a</em></td>
    <td>Postgraduate</td>
    <td>This is a multi-value attribute describing the user's status within the University. Currently,
	    user's will be one of Staff, Student, or Associate. Student entries should also have either
		Postgraduate or Undergraduate status.</td>
  </tr>
  <tr>
    <td><strong>exeterStaffNumber</strong><br />
	    <strong>exeterStudentNumber</strong><br />
	    <strong>exeterAssociateNumber</strong>
	</td>
	<td><em>s</em></td>
    <td>4000<br />
	    212211<br />
		111111
	</td>
    <td>Staff HR number.<br />
	    Student Number (SITS).<br />
		Associate Number (assigned by the Card Office).
	</td>
  </tr>
  <tr>
    <td><strong>exeterAthensAccount</strong></td>
	<td><em>s</em></td>
	<td>111111</td>
	<td>Only applicable to Associate users who are permitted to use AthensDA for online access to
	    electronic resources.
	</td>
  </tr>
  <tr>
    <td><strong>exeterDOB</strong></td>
	<td><em>s</em></td>
	<td>19860701</td>
	<td>Date of birth. Only available for student entries.</td>
  </tr>
  <tr>
    <td><strong>exeterPrimaryOrg</strong><br />
	    <strong>exeterPrimaryOrgUnit</strong><br />
	    <strong>exeterOrg</strong><br />
	    <strong>exeterOrgUnit</strong><br />
	    &nbsp;
	</td>
	<td><em>a</em></td>
	<td>School of Physics<br />
	    Physics<br />
      School of Biosciences<br />
      Biology<br />
	    &nbsp;
    </td>
	<td>Primary School/Division.<br />
	    Primary Department.<br />
		Other Schools/Divisions (multi-value).<br />
		Other Departments (multi-value).<br />
      <strong>(See Note 1 below)</strong></td>
  </tr>
  <tr>
    <td><strong>eduPersonPrimaryOrgDN</strong><br />
	    <strong>eduPersonPrimaryOrgUnitDN</strong><br />
	    <strong>eduPersonOrgDN</strong><br />
	    <strong>eduPersonOrgUnitDN</strong>
	</td>
	<td><em>a</em></td>
	<td>Not in use.</td>
	<td>Not in use <strong>(See Note 2 below)</strong>.</td>
  </tr>
  <tr>
    <td><strong>exeterStartDate</strong><br />
	    <strong>exeterExpiryDate</strong>
	</td>
	<td><em>a</em></td>
	<td>Not in use.</td>
	<td>As yet, undefined.</td>
  </tr>
  <tr>
    <td><strong>uidNumber</strong><br />
	    <strong>gidNumber</strong><br />
	    <strong>homeDirectory</strong><br />
	    <strong>gecos</strong><br />
	    <strong>loginShell</strong><br />
	    <strong>logonTime</strong><br />
	    <strong>logoffTime</strong><br />
	    <strong>pwdCanChange</strong><br />
	    <strong>pwdLastSet</strong><br />
	    <strong>pwdMustChange</strong><br />
	    <strong>kickoffTime</strong><br />
	    <strong>smbHome</strong><br />
		<strong>acctFlags</strong><br />
		<strong>primaryGroupID</strong><br />
		<strong>rid</strong><br />
		<strong>homeDrive</strong>
    </td>
	<td><em>a</em><br />
      <em>a</em><br />
      <em>a</em><br />
      <em>s</em><br />
      <em>a</em><br />
      <em>a</em><br />
      <em>a</em><br />
      <em>a</em><br />
      <em>a</em><br />
      <em>a</em><br />
      <em>a</em><br />
      <em>a</em><br />
      <em>s</em><br />
      <em>s</em><br />
      <em>s</em><br />
      <em>s</em><br />
	</td>
    <td>30501<br />
	    30000<br />
	    /home/ecu/ano201<br />
	    A.N.Other, STF-HR-202211<br />
	    /bin/tcsh<br />
	    0<br />
	    0<br />
	    0<br />
	    1124206094<br />
	    2147483647<br />
	    0<br />
		\\cifshomes\ano201<br />
		[U<br />
		51001<br />
		123223<br />
		U:
	    </td>
    <td>These attributes support Unix and Samba account details.</td>
  </tr>
</table>
<p><strong>Access :-</strong> <em>a</em> - anonymous &amp; self Read, <em>s</em> 
  - self read, <em>S</em> - self write</p>
<p><strong>Note 1 :-</strong> The following attributes are temporary and subject 
  to change: <em>exeterPrimaryOrg</em>, <em>exeterPrimaryOrgUnit</em>, 
  <em>exeterOrg</em> and <em>exeterOrgUnit</em>. The need for a temporary 
  solution has arisen while we await consistency between the various source 
  databases. Please do not rely on these attributes.</p>
<p><strong>Note 2 :-</strong> The following attributes will be used in place of 
  those referenced in Note 1, once consistency between the various source databases 
  can be guaranteed: <em>eduPersonPrimaryOrgDN</em>, <em>eduPersonPrimaryOrgUnitDN</em>, 
  <em>eduPersonOrgDN</em> &amp; <em>eduPersonOrgUnitDN</em>. These attributes 
  hold distinguished names referencing entries in the university structure (<em>o=University 
  of Exeter, dc=exeter, dc=ac, dc=uk</em>).
<p><a href="#s0">Top</a></p>
<h2><a name="s8">Attributes Relating To The Organisation</a></h2>
<p>This is currently under development as <em>o=University of Exeter, dc=exeter, 
  dc=ac, dc=uk</em> based around the <em>eduOrg</em> schema.</p>
<p><a href="#s0">Top</a></p>
<h2><a name="s10">A Sample Staff Entry</a></h2>
<p>
<pre>
dn: uid=aw201,ou=people,dc=exeter,dc=ac,dc=uk
  objectClass      :  top
  objectClass      :  person
  objectClass      :  organizationalPerson
  objectClass      :  inetOrgPerson
  objectClass      :  posixAccount
  objectClass      :  shadowAccount
  objectClass      :  sambaAccount
  objectClass      :  eduPerson
  objectClass      :  exeterPerson
  acctFlags        :  [U          ]
  cn               :  Andy Worker
  displayName      :  A.Worker
  sn               :  Worker
  givenName        :  Andrew
  eduPersonNickname :  Andy
  exeterPrimaryOrg :  School of Education & Lifelong Learning
  exeterPrimaryOrgUnit :  School of Education & Lifelong Learning
  exeterStaffNumber :  222222
  exeterStatus     :  Staff
  telephoneNumber  :  3263
  gecos            :  A.Worker, EFL_STF-HR-222222
  gidNumber        :  61001
  homeDirectory    :  /home/efl/aw201
  homeDrive        :  U:
  kickoffTime      :  0
  loginShell       :  /bin/tcsh
  logoffTime       :  0
  logonTime        :  0
  mail             :  A.Worker@exeter.ac.uk
  lmPassword       :  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  ntPassword       :  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  userPassword     :  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  primaryGroupID   :  51001
  pwdCanChange     :  0
  pwdLastSet       :  1124206094
  pwdMustChange    :  2147483647
  rid              :  123223
  smbHome          :  \\cifshomes\aw201
  uid              :  aw201
  uidNumber        :  61111
</pre>
</p>
<p><a href="#s0">Top</a></p>
<h2><a name="s11">A Sample Student Entry</a></h2>
<p>
<pre>
dn: uid=ay202,ou=people,dc=exeter,dc=ac,dc=uk
  objectClass      :  top
  objectClass      :  person
  objectClass      :  organizationalPerson
  objectClass      :  inetOrgPerson
  objectClass      :  posixAccount
  objectClass      :  shadowAccount
  objectClass      :  eduPerson
  objectClass      :  exeterPerson
  objectClass      :  sambaAccount
  acctFlags        :  [U          ]
  cn               :  Alex Younger
  displayName      :  A.Younger
  sn               :  Younger
  givenName        :  Alex
  eduPersonNickname :  Alex
  exeterDOB        :  19860701
  exeterPrimaryOrg :  School of Physics
  exeterPrimaryOrgUnit :  Physics
  exeterStatus     :  Student
  exeterStudentNumber :  531111111
  gecos            :  ay202, PY05_UG-531111111
  gidNumber        :  51002
  homeDirectory    :  /home/epy_ug/py05/ay202
  homeDrive        :  U:
  kickoffTime      :  0
  loginShell       :  /usr/etc/nologin.pl
  logoffTime       :  0
  logonTime        :  0
  mail             :  ay202@exeter.ac.uk
  lmPassword       :  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  ntPassword       :  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  userPassword     :  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  primaryGroupID   :  51002
  pwdCanChange     :  0
  pwdLastSet       :  1063308595
  pwdMustChange    :  2147483647
  rid              :  123225
  smbHome          :  \\cifshomes\ay202
  uid              :  ay202
  uidNumber        :  61112
</pre>
</p>
<p><a href="#s0">Top</a></p>
<h2><a name="s12">A Sample Associate Entry</a></h2>
<p>
<pre>
dn: uid=ao203,ou=people,dc=exeter,dc=ac,dc=uk
  objectClass      :  top
  objectClass      :  person
  objectClass      :  organizationalPerson
  objectClass      :  inetOrgPerson
  objectClass      :  posixAccount
  objectClass      :  shadowAccount
  objectClass      :  sambaAccount
  objectClass      :  eduPerson
  objectClass      :  exeterPerson
  acctFlags        :  [U          ]
  cn               :  Ann Other
  displayName      :  A.Other
  sn               :  Other
  givenName        :  Ann
  eduPersonNickname :  Ann
  exeterPrimaryOrg :  School Of Education & Lifelong Learning - English Language Centre
  exeterPrimaryOrgUnit :  Visiting Lecturer
  exeterAssociateNumber :  111111
  exeterAthensAccount :  111111
  exeterStatus     :  Associate
  gecos            :  A.Other, EFL_STF-NP-111111
  gidNumber        :  61003
  homeDirectory    :  /home/efl/ao203
  homeDrive        :  U:
  kickoffTime      :  0
  loginShell       :  /bin/tcsh
  logoffTime       :  0
  logonTime        :  0
  mail             :  A.Other@exeter.ac.uk
  lmPassword       :  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  ntPassword       :  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  userPassword     :  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  primaryGroupID   :  51003
  pwdCanChange     :  0
  pwdLastSet       :  1124206238
  pwdMustChange    :  2147483647
  rid              :  123227
  smbHome          :  \\cifshomes\ao203
  uid              :  ao203
  uidNumber        :  61113
</pre>
</p>
<p><a href="#s0">Top</a></p>
<h2><a name="s13">Further Information For Sys Admins And Exeter Users</a></h2>
<p>Currently under construction...</p>
<ol>
<li>Configuring your email client to use the LDAP address book.</li>
<li>Setting up LDAP authentication on Linux.</li>
<li>Using PHP to connect to the  LDAP servers.</li>
</ol>
<p><a href="#s0">Top</a></p>

<?php

printFooter($site,$uri);

?>
