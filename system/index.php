<?php
/**
 * /system/index.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (C) 2010-2015 Greg Chetcuti <greg@chetcuti.com>
 *
 * Project: http://domainmod.org   Author: http://chetcuti.com
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
?>
<?php
include("../_includes/start-session.inc.php");
include("../_includes/init.inc.php");
include(DIR_INC . "config.inc.php");
include(DIR_INC . "database.inc.php");
include(DIR_INC . "software.inc.php");
include(DIR_INC . "auth/auth-check.inc.php");

$page_title = "Control Panel";
$software_section = "system";
?>
<?php include(DIR_INC . "doctype.inc.php"); ?>
<html>
<head>
<title><?php echo $software_title . " :: " . $page_title; ?></title>
<?php include(DIR_INC . "layout/head-tags.inc.php"); ?>
</head>
<body>
<?php include(DIR_INC . "layout/header.inc.php"); ?>
<font class="subheadline">User Menu</font><BR><BR>
&raquo; <a href="display-settings.php">Display Settings</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="email-settings.php">Email Settings</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="defaults.php">User Defaults</a><BR><BR>
&raquo; <a href="update-profile.php">Update Profile</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="change-password.php">Change Password</a><BR>
<BR><BR><font class="subheadline">Maintenance Menu</font><BR><BR>
&raquo; <a href="../_includes/system/update-conversion-rates.direct.php">Update Conversion Rates</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="../_includes/system/update-domain-fees.direct.php">Update Domain Fees</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="../_includes/system/update-ssl-fees.direct.php">Update SSL Fees</a><BR>
<?php
if ($_SESSION['is_admin'] == 1) { ?>
    <BR><BR><font class="subheadline">Admin Menu</font><BR><BR>
    &raquo; <a href="admin/system-settings.php">System Settings</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="admin/defaults.php">System Defaults</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="admin/users.php">Users</a><BR><BR>
    &raquo; <a href="admin/domain-fields.php">Custom Domain Fields</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="admin/ssl-fields.php">Custom SSL Fields</a><BR><BR>
    &raquo; <a href="admin/perform-maintenance.php">Perform System Maintenance</a><BR><BR>
    &raquo; <a href="admin/dw/">Data Warehouse</a><BR><BR>
    &raquo; <a href="admin/system-info.php">System Information</a><BR><?php
} ?>
<?php include(DIR_INC . "layout/footer.inc.php"); ?>
</body>
</html>
