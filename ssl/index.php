<?php
/**
 * /ssl/index.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2019 Greg Chetcuti <greg@chetcuti.com>
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
<?php //@formatter:off
require_once __DIR__ . '/../_includes/start-session.inc.php';
require_once __DIR__ . '/../_includes/init.inc.php';
require_once DIR_INC . '/config.inc.php';
require_once DIR_INC . '/software.inc.php';
require_once DIR_ROOT . '/vendor/autoload.php';

$deeb = DomainMOD\Database::getInstance();
$system = new DomainMOD\System();
$layout = new DomainMOD\Layout();
$date = new DomainMOD\Date();
$time = new DomainMOD\Time();
$form = new DomainMOD\Form();
$assets = new DomainMOD\Assets();
$currency = new DomainMOD\Currency();
$customField = new DomainMOD\CustomField();

require_once DIR_INC . '/head.inc.php';
require_once DIR_INC . '/debug.inc.php';
require_once DIR_INC . '/settings/ssl-main.inc.php';

$system->authCheck();
$pdo = $deeb->cnxx;

$export_data = (int) $_GET['export_data'];
$oid = (int) $_REQUEST['oid'];
$did = (int) $_REQUEST['did'];
$sslpid = (int) $_REQUEST['sslpid'];
$sslpaid = (int) $_REQUEST['sslpaid'];
$ssltid = (int) $_REQUEST['ssltid'];
$sslipid = (int) $_REQUEST['sslipid'];
$sslpcid = (int) $_REQUEST['sslpcid'];
$is_active = $_REQUEST['is_active'];
$search_for = urlencode($_REQUEST['search_for']);
$from_dropdown = (int) $_REQUEST['from_dropdown'];
$expand = (int) $_REQUEST['expand'];
$daterange = $_REQUEST['daterange'];

list($new_start_date, $new_end_date) = $date->splitAndCheckRange($daterange);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $from_dropdown = 0;

    $date = new DomainMOD\Date();

    if ($new_start_date > $new_end_date) {

        $_SESSION['s_message_danger'] .= 'The date range is invalid<BR>';

    }

}

if ($export_data !== 1) {

    if ($from_dropdown !== 1) {

        if ($search_for != "") {

            $_SESSION['s_search_for_ssl'] = $search_for;

        } else {

            $_SESSION['s_search_for_ssl'] = "";

        }

        if ($new_start_date != '' && $new_start_date != 'YYYY-MM-DD') {

            $_SESSION['s_start_date'] = $new_start_date;
            $_SESSION['s_end_date'] = $new_end_date;

        } else {

            $_SESSION['s_start_date'] = 'YYYY-MM-DD';
            $_SESSION['s_end_date'] = 'YYYY-MM-DD';

        }

    }

}

if ($_SESSION['s_start_date'] == '') $_SESSION['s_start_date'] = 'YYYY-MM-DD';
if ($_SESSION['s_end_date'] == '') $_SESSION['s_end_date'] = 'YYYY-MM-DD';

if ($is_active == "") $is_active = "LIVE";

if ($is_active == "0") { $is_active_string = " AND sslc.active = '0' ";
} elseif ($is_active == "1") { $is_active_string = " AND sslc.active = '1' ";
} elseif ($is_active == "2") { $is_active_string = " AND sslc.active = '2' ";
} elseif ($is_active == "3") { $is_active_string = " AND sslc.active = '3' ";
} elseif ($is_active == "4") { $is_active_string = " AND sslc.active = '4' ";
} elseif ($is_active == "5") { $is_active_string = " AND sslc.active = '5' ";
} elseif ($is_active == "6") { $is_active_string = " AND sslc.active = '6' ";
} elseif ($is_active == "7") { $is_active_string = " AND sslc.active = '7' ";
} elseif ($is_active == "8") { $is_active_string = " AND sslc.active = '8' ";
} elseif ($is_active == "9") { $is_active_string = " AND sslc.active = '9' ";
} elseif ($is_active == "10") { $is_active_string = " AND sslc.active = '10' ";
} elseif ($is_active == "LIVE") { $is_active_string = " AND sslc.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9')";
} elseif ($is_active == "ALL") { $is_active_string = " AND sslc.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')";
}

if ($oid != "") {
    $oid_string = " AND o.id = '$oid' ";
} else {
    $oid_string = "";
}
if ($did != "") {
    $did_string = " AND d.id = '$did' ";
} else {
    $did_string = "";
}
if ($sslpid != "") {
    $sslpid_string = " AND sslp.id = '$sslpid' ";
} else {
    $sslpid_string = "";
}
if ($sslpaid != "") {
    $sslpaid_string = " AND sslc.account_id = '$sslpaid' ";
} else {
    $sslpaid_string = "";
}
if ($ssltid != "") {
    $ssltid_string = " AND sslc.type_id = '$ssltid' ";
} else {
    $ssltid_string = "";
}
if ($sslipid != "") {
    $sslipid_string = " AND sslc.ip_id = '$sslipid' ";
} else {
    $sslipid_string = "";
}
if ($sslpcid != "") {
    $sslpcid_string = " AND sslc.cat_id = '$sslpcid' ";
} else {
    $sslpcid_string = "";
}
if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
    $range_string = " AND (sslc.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND sslc.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
} else {
    $range_string = "";
}
if ($_SESSION['s_search_for_ssl'] != "") {
    $search_string = " AND (sslc.name LIKE '%" . $_SESSION['s_search_for_ssl'] . "%' OR d.domain LIKE '%" . $_SESSION['s_search_for_ssl'] . "%')";
} else {
    $search_string = "";
}

$sslfd_columns = $customField->getCustomFieldsSql('ssl_cert_fields', 'sslfd');

$sql = "SELECT sslc.id, sslc.domain_id, sslc.name, sslc.expiry_date, sslc.total_cost, sslc.notes, sslc.active, sslc.creation_type_id, sslc.created_by, sslc.insert_time, sslc.update_time, sslpa.id AS sslpa_id, sslpa.username, sslp.id AS sslp_id, sslp.name AS ssl_provider_name, o.id AS o_id, o.name AS owner_name, f.id AS f_id, f.initial_fee, f.renewal_fee, f.misc_fee, cc.conversion, d.domain, sslcf.id as type_id, sslcf.type, ip.id AS ip_id, ip.name as ip_name, ip.ip, ip.rdns, cat.id AS cat_id, cat.name AS cat_name" . $sslfd_columns . "
        FROM ssl_certs AS sslc, ssl_accounts AS sslpa, ssl_providers AS sslp, owners AS o, ssl_fees AS f, currencies AS c, currency_conversions AS cc, domains AS d, ssl_cert_types AS sslcf, ip_addresses AS ip, categories AS cat, ssl_cert_field_data AS sslfd
        WHERE sslc.account_id = sslpa.id
          AND sslpa.ssl_provider_id = sslp.id
          AND sslpa.owner_id = o.id
          AND sslc.fee_id = f.id
          AND f.currency_id = c.id
          AND c.id = cc.currency_id
          AND sslc.domain_id = d.id
          AND sslc.type_id = sslcf.id
          AND sslc.ip_id = ip.id
          AND sslc.cat_id = cat.id
          AND sslc.id = sslfd.ssl_id
          AND cc.user_id = '" . $_SESSION['s_user_id'] . "'
          $is_active_string
          $oid_string
          $did_string
          $sslpid_string
          $sslpaid_string
          $ssltid_string
          $sslipid_string
          $sslpcid_string
          $range_string
          $search_string";
$_SESSION['s_raw_list_type'] = 'ssl-certs';
$_SESSION['s_raw_list_query'] = $sql;

// This query is identical to the main query, except that it only does a count
$total_rows = $pdo->query("
    SELECT count(*)
    FROM ssl_certs AS sslc, ssl_accounts AS sslpa, ssl_providers AS sslp, owners AS o, ssl_fees AS f, currencies AS c, currency_conversions AS cc, domains AS d, ssl_cert_types AS sslcf, ip_addresses AS ip, categories AS cat, ssl_cert_field_data AS sslfd
    WHERE sslc.account_id = sslpa.id
      AND sslpa.ssl_provider_id = sslp.id
      AND sslpa.owner_id = o.id
      AND sslc.fee_id = f.id
      AND f.currency_id = c.id
      AND c.id = cc.currency_id
      AND sslc.domain_id = d.id
      AND sslc.type_id = sslcf.id
      AND sslc.ip_id = ip.id
      AND sslc.cat_id = cat.id
      AND sslc.id = sslfd.ssl_id
      AND cc.user_id = '" . $_SESSION['s_user_id'] . "'
      $is_active_string
      $oid_string
      $did_string
      $sslpid_string
      $sslpaid_string
      $ssltid_string
      $sslipid_string
      $sslpcid_string
      $range_string
      $search_string")->fetchColumn();

$grand_total = $pdo->query("
    SELECT SUM(sslc.total_cost * cc.conversion) AS grand_total
    FROM ssl_certs AS sslc, ssl_accounts AS sslpa, ssl_providers AS sslp, owners AS o, ssl_fees AS f, currencies AS c, currency_conversions AS cc, domains AS d, ssl_cert_types AS sslcf, ip_addresses AS ip, categories AS cat
    WHERE sslc.account_id = sslpa.id
      AND sslpa.ssl_provider_id = sslp.id
      AND sslpa.owner_id = o.id
      AND sslc.fee_id = f.id
      AND f.currency_id = c.id
      AND c.id = cc.currency_id
      AND sslc.domain_id = d.id
      AND sslc.type_id = sslcf.id
      AND sslc.ip_id = ip.id
      AND sslc.cat_id = cat.id
      AND cc.user_id = '" . $_SESSION['s_user_id'] . "'
      $is_active_string
      $oid_string
      $did_string
      $sslpid_string
      $sslpaid_string
      $ssltid_string
      $sslipid_string
      $sslpcid_string
      $range_string
      $search_string")->fetchColumn();

$grand_total = $currency->format($grand_total, $_SESSION['s_default_currency_symbol'],
    $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

if ($export_data === 1) {

    $result = $pdo->query($sql)->fetchAll();

    $export = new DomainMOD\Export();
    $export_file = $export->openFile('ssl_results', strtotime($time->stamp()));

    $row_contents = array('SSL Certificate Search Results Export');
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    $row_contents = array(
        'Total Cost:',
        $grand_total,
        $_SESSION['s_default_currency']
    );
    $export->writeRow($export_file, $row_contents);

    $row_contents = array(
        'Number of SSL Certs:',
        $total_rows
    );
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    $row_contents = array(
        '[Search Filters]'
    );
    $export->writeRow($export_file, $row_contents);

    if ($_SESSION['s_search_for_ssl'] != "") {

        $row_contents = array(
            'Keyword Search:',
            $_SESSION['s_search_for_ssl']
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($did > 0) {

        $domain = new DomainMOD\Domain();
        $temp_domain = $domain->getDomain($did);

        $row_contents = array(
            'Associated Domain:',
            $temp_domain
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($sslpid > 0) {

        $temp_name = $assets->getSslProvider($sslpid);

        $row_contents = array(
            'SSL Provider:',
            $temp_name
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($sslpaid > 0) {

        $stmt = $pdo->prepare("
            SELECT sslp.name AS ssl_provider_name, o.name AS owner_name, sslpa.username
            FROM ssl_accounts AS sslpa, ssl_providers AS sslp, owners AS o
            WHERE sslpa.ssl_provider_id = sslp.id
              AND sslpa.owner_id = o.id
              AND sslpa.id = :sslpaid");
        $stmt->bindValue('sslpaid', $sslpaid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if ($result) {

            $row_contents = array(
                'SSL Provider Account:',
                $result->ssl_provider_name . " - " . $result->owner_name . " - " . $result->username
            );
            $export->writeRow($export_file, $row_contents);


        }

    }

    if ($ssltid > 0) {

        $temp_type = $assets->getSslType($ssltid);

        $row_contents = array(
            'SSL Type:',
            $temp_type
        );
        $export->writeRow($export_file, $row_contents);


    }

    if ($sslipid > 0) {

        list($temp_ip, $temp_ip_name) = $assets->getIpAndName($sslipid);

        $row_contents = array(
            'SSL IP Address:',
            $temp_ip_name . ' (' . $temp_ip . ')'
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($sslpcid > 0) {

        $temp_category = $assets->getCat($sslpcid);

        $row_contents = array(
            'SSL Category:',
            $temp_category
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($oid > 0) {

        $temp_owner = $assets->getOwner($oid);

        $row_contents = array(
            'Owner:',
            $temp_owner
        );
        $export->writeRow($export_file, $row_contents);

    }

    if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {

        $row_contents = array(
            'Expiry Date Range:',
            $_SESSION['s_start_date']  . " to " . $_SESSION['s_end_date']
        );
        $export->writeRow($export_file, $row_contents);

    }

    unset($row_contents);
    $count = 0;

    $row_contents[$count++] = "SSL Status:";

    if ($is_active == "ALL") {

        $row_contents[$count++] = 'ALL';

    } elseif ($is_active == "LIVE" || $is_active == "") {

        $row_contents[$count++] = 'LIVE (Active / Pending)';

    } elseif ($is_active == "0") {

        $row_contents[$count++] = 'Expired';

    } elseif ($is_active == "1") {

        $row_contents[$count++] = 'Active';

    } elseif ($is_active == "3") {

        $row_contents[$count++] = 'Pending (Renewal)';

    } elseif ($is_active == "4") {

        $row_contents[$count++] = 'Pending (Other)';

    } elseif ($is_active == "5") {

        $row_contents[$count++] = 'Pending (Registration)';

    }
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    unset($row_contents);
    $count = 0;

    $row_contents[$count++] = 'SSL Cert Status';
    $row_contents[$count++] = 'Expiry Date';
    $row_contents[$count++] = 'Initial Fee';
    $row_contents[$count++] = 'Renewal Fee';
    $row_contents[$count++] = 'Misc Fee';
    $row_contents[$count++] = 'Total Yearly Cost';
    $row_contents[$count++] = 'Host / Label';
    $row_contents[$count++] = 'Domain';
    $row_contents[$count++] = 'SSL Provider';
    $row_contents[$count++] = 'SSL Provider Account';
    $row_contents[$count++] = 'Username';
    $row_contents[$count++] = 'SSL Type';
    $row_contents[$count++] = 'IP Address Name';
    $row_contents[$count++] = 'IP Address';
    $row_contents[$count++] = 'IP Address rDNS';
    $row_contents[$count++] = 'Category';
    $row_contents[$count++] = 'Owner';
    $row_contents[$count++] = 'Notes';
    $row_contents[$count++] = 'Creation Type';
    $row_contents[$count++] = 'Created By';
    $row_contents[$count++] = "Inserted";
    $row_contents[$count++] = "Updated";
    $row_contents[$count++] = "CUSTOM FIELDS";

    $result_field = $pdo->query("
        SELECT `name`
        FROM ssl_cert_fields
        ORDER BY `name` ASC")->fetchAll();

    if ($result_field) {

        foreach ($result_field as $row_field) {

            $row_contents[$count++] = $row_field->name;

        }

    }

    $export->writeRow($export_file, $row_contents);

    foreach ($result as $row) {

        $temp_initial_fee = $row->initial_fee * $row->conversion;
        $temp_renewal_fee = $row->renewal_fee * $row->conversion;
        $temp_misc_fee = $row->misc_fee * $row->conversion;
        $temp_total_cost = $row->total_cost * $row->conversion;

        if ($row->active == "0") {
            $ssl_status = "EXPIRED";
        } elseif ($row->active == "1") {
            $ssl_status = "ACTIVE";
        } elseif ($row->active == "3") {
            $ssl_status = "PENDING (RENEWAL)";
        } elseif ($row->active == "4") {
            $ssl_status = "PENDING (OTHER)";
        } elseif ($row->active == "5") {
            $ssl_status = "PENDING (REGISTRATION)";
        } else {
            $ssl_status = "ERROR -- PROBLEM WITH CODE IN SSL/INDEX.PHP";
        }

        $export_initial_fee = $currency->format($temp_initial_fee,
            $_SESSION['s_default_currency_symbol'], $_SESSION['s_default_currency_symbol_order'],
            $_SESSION['s_default_currency_symbol_space']);

        $export_renewal_fee = $currency->format($temp_renewal_fee, $_SESSION['s_default_currency_symbol'],
            $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

        $export_misc_fee = $currency->format($temp_misc_fee,
            $_SESSION['s_default_currency_symbol'], $_SESSION['s_default_currency_symbol_order'],
            $_SESSION['s_default_currency_symbol_space']);

        $export_total_cost = $currency->format($temp_total_cost, $_SESSION['s_default_currency_symbol'],
            $_SESSION['s_default_currency_symbol_order'], $_SESSION['s_default_currency_symbol_space']);

        unset($row_contents);
        $count = 0;

        $creation_type = $system->getCreationType($row->creation_type_id);

        if ($row->created_by == '0') {
            $created_by = 'Unknown';
        } else {
            $user = new DomainMOD\User();
            $created_by = $user->getFullName($row->created_by);
        }

        $row_contents[$count++] = $ssl_status;
        $row_contents[$count++] = $row->expiry_date;
        $row_contents[$count++] = $export_initial_fee;
        $row_contents[$count++] = $export_renewal_fee;
        $row_contents[$count++] = $export_misc_fee;
        $row_contents[$count++] = $export_total_cost;
        $row_contents[$count++] = $row->name;
        $row_contents[$count++] = $row->domain;
        $row_contents[$count++] = $row->ssl_provider_name;
        $row_contents[$count++] = $row->ssl_provider_name . ', ' . $row->owner_name . ' (' . $row->username . ')';
        $row_contents[$count++] = $row->username;
        $row_contents[$count++] = $row->type;
        $row_contents[$count++] = $row->ip_name;
        $row_contents[$count++] = $row->ip;
        $row_contents[$count++] = $row->rdns;
        $row_contents[$count++] = $row->cat_name;
        $row_contents[$count++] = $row->owner_name;
        $row_contents[$count++] = $row->notes;
        $row_contents[$count++] = $creation_type;
        $row_contents[$count++] = $created_by;
        $row_contents[$count++] = $time->toUserTimezone($row->insert_time);
        $row_contents[$count++] = $time->toUserTimezone($row->update_time);
        $row_contents[$count++] = '';

        $sslfd_columns_array = $customField->getCustomFields('ssl_cert_fields');

        if ($sslfd_columns_array != "") {

            foreach ($sslfd_columns_array as $column) {

                $row_contents[$count++] = $row->{$column};

            }

        }

        $export->writeRow($export_file, $row_contents);

    }

    $export->closeFile($export_file);

}
?>
<?php require_once DIR_INC . '/doctype.inc.php'; ?>
<html>
<head>
    <title><?php echo $layout->pageTitle($page_title); ?></title>
    <?php require_once DIR_INC . '/layout/head-tags.inc.php'; ?>
    <?php require_once DIR_INC . '/layout/date-range-picker-head.inc.php'; ?>
    <?php echo $layout->jumpMenu(); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
<?php require_once DIR_INC . '/layout/header.inc.php'; ?>
<?php
// Double check to make sure there are still no SSL certs in the system
if ($_SESSION['s_has_ssl_cert'] == '0') {

    $queryB = new DomainMOD\QueryBuild();
    $sql_asset_check = $queryB->singleAsset('ssl_certs');
    $_SESSION['s_has_ssl_cert'] = $system->checkForRows($sql_asset_check);

}

$subtext1 = 'Before you can start adding SSL certificates to DomainMOD you need to add at least one SSL provider, an SSL provider account, and a domain.';

if ($_SESSION['s_has_ssl_provider'] != '1') {
    echo "<strong>0</strong> SSL Providers found<BR><BR>";
    echo $subtext1 . '<BR><BR>';
    echo "<a href=\"../assets/add/ssl-provider.php\">Click here to add an SSL Provider &raquo;</a><BR>";
}

if ($_SESSION['s_has_ssl_account'] != '1' && $_SESSION['s_has_ssl_provider'] == '1') {
    echo "<strong>0</strong> SSL Provider Accounts found<BR><BR>";
    echo $subtext1 . '<BR><BR>';
    echo "<a href=\"../assets/add/ssl-provider-account.php\">Click here to add an SSL Provider Account &raquo;</a><BR>";
}

if ($_SESSION['s_has_domain'] != '1' && $_SESSION['s_has_ssl_provider'] == '1' && $_SESSION['s_has_ssl_account'] == '1') {
    echo "<strong>0</strong> Domains found<BR><BR>";
    echo $subtext1 . '<BR><BR>';
    echo "<a href=\"../domains/\">Click here to add a Domain &raquo;</a><BR>";
}

if ($_SESSION['s_has_ssl_cert'] != '1' && $_SESSION['s_has_ssl_provider'] == '1' && $_SESSION['s_has_ssl_account'] == '1' && $_SESSION['s_has_domain'] == '1') {
    echo "<strong>0</strong> SSL Certificates found<BR><BR>";
    echo "<a href=\"add.php\">Click here to add an SSL Certificate &raquo;</a><BR>";
}

$result = $pdo->query($sql)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $expand === 1) {
    $box_type = 'expanded';
    $box_icon = 'minus';
} else {
    $box_type = 'collapsed';
    $box_icon = 'plus';
}

if ($_SESSION['s_has_ssl_provider'] == '1' && $_SESSION['s_has_ssl_account'] == '1' && $_SESSION['s_has_ssl_cert'] == '1' && $_SESSION['s_has_domain'] == '1') { ?>

    <div class="box box-default <?php echo $box_type; ?>-box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title" style="padding-top: 3px;">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-<?php echo $box_icon; ?>"></i></button>&nbsp;Advanced Filtering [<a href="<?php echo $web_root; ?>/ssl/">reset filters</a>]
            </h3>
        </div>
        <div class="box-body">
            <?php
            echo $form->showFormTop('');

            // DOMAIN
            if ($is_active == "0") {
                $is_active_string = " AND sslc.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND sslc.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND sslc.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND sslc.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND sslc.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND sslc.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND sslc.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND sslc.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND sslc.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND sslc.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND sslc.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND sslc.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9')";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND sslc.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')";
            }

            if ($oid != "") {
                $oid_string = " AND sslc.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($sslpid != "") {
                $sslpid_string = " AND sslc.ssl_provider_id = '$sslpid' ";
            } else {
                $sslpid_string = "";
            }
            if ($sslpaid != "") {
                $sslpaid_string = " AND sslc.account_id = '$sslpaid' ";
            } else {
                $sslpaid_string = "";
            }
            if ($ssltid != "") {
                $ssltid_string = " AND sslc.type_id = '$ssltid' ";
            } else {
                $ssltid_string = "";
            }
            if ($sslipid != "") {
                $sslipid_string = " AND sslc.ip_id = '$sslipid' ";
            } else {
                $sslipid_string = "";
            }
            if ($sslpcid != "") {
                $sslpcid_string = " AND sslc.cat_id = '$sslpcid' ";
            } else {
                $sslpcid_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (sslc.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND sslc.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for_ssl'] != "") {
                $search_string = " AND (sslc.name LIKE '%" . $_SESSION['s_search_for_ssl'] . "%' OR d.domain LIKE '%" . $_SESSION['s_search_for_ssl'] . "%')";
            } else {
                $search_string = "";
            }

            $result_domain = $pdo->query("
                SELECT d.id, d.domain
                FROM domains AS d, ssl_certs AS sslc
                WHERE d.id = sslc.domain_id
                  AND d.active not in ('0', '10')
                  $is_active_string
                  $oid_string
                  $sslpid_string
                  $sslpaid_string
                  $ssltid_string
                  $sslipid_string
                  $sslpcid_string
                  $range_string
                  $search_string
                GROUP BY d.domain
                ORDER BY d.domain asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1', '', 'Domain - ALL', 'null');
            foreach ($result_domain as $row_domain) {

                echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $row_domain->id . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1&null=', $row_domain->id, $row_domain->domain, $did);

            }
            echo $form->showDropdownBottom('');


            // SSL PROVIDER
            if ($is_active == "0") {
                $is_active_string = " AND sslc.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND sslc.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND sslc.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND sslc.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND sslc.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND sslc.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND sslc.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND sslc.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND sslc.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND sslc.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND sslc.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND sslc.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9')";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND sslc.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')";
            }

            if ($oid != "") {
                $oid_string = " AND sslc.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($did != "") {
                $did_string = " AND sslc.domain_id = '$did' ";
            } else {
                $did_string = "";
            }
            if ($sslpaid != "") {
                $sslpaid_string = " AND sslc.account_id = '$sslpaid' ";
            } else {
                $sslpaid_string = "";
            }
            if ($ssltid != "") {
                $ssltid_string = " AND sslc.type_id = '$ssltid' ";
            } else {
                $ssltid_string = "";
            }
            if ($sslipid != "") {
                $sslipid_string = " AND sslc.ip_id = '$sslipid' ";
            } else {
                $sslipid_string = "";
            }
            if ($sslpcid != "") {
                $sslpcid_string = " AND sslc.cat_id = '$sslpcid' ";
            } else {
                $sslpcid_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (sslc.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND sslc.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for_ssl'] != "") {
                $search_string = " AND (sslc.name LIKE '%" . $_SESSION['s_search_for_ssl'] . "%' OR d.domain LIKE '%" . $_SESSION['s_search_for_ssl'] . "%')";
            } else {
                $search_string = "";
            }

            $result_ssl_provider = $pdo->query("
                SELECT sslp.id, sslp.name
                FROM ssl_providers AS sslp, ssl_certs AS sslc, domains AS d
                WHERE sslp.id = sslc.ssl_provider_id
                  AND sslc.domain_id = d.id
                  $is_active_string
                  $oid_string
                  $did_string
                  $sslpaid_string
                  $ssltid_string
                  $sslipid_string
                  $sslpcid_string
                  $range_string
                  $search_string
                GROUP BY sslp.name
                ORDER BY sslp.name asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1', '', 'SSL Provider - ALL', 'null');
            foreach ($result_ssl_provider as $row_ssl_provider) {

                echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $row_ssl_provider->id . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1&null=', $row_ssl_provider->id, $row_ssl_provider->name, $sslpid);

            }
            echo $form->showDropdownBottom('');


            // SSL PROVIDER ACCOUNT
            if ($is_active == "0") {
                $is_active_string = " AND sslc.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND sslc.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND sslc.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND sslc.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND sslc.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND sslc.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND sslc.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND sslc.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND sslc.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND sslc.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND sslc.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND sslc.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9')";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND sslc.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')";
            }

            if ($oid != "") {
                $oid_string = " AND sslc.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($did != "") {
                $did_string = " AND sslc.domain_id = '$did' ";
            } else {
                $did_string = "";
            }
            if ($sslpid != "") {
                $sslpid_string = " AND sslc.ssl_provider_id = '$sslpid' ";
            } else {
                $sslpid_string = "";
            }
            if ($ssltid != "") {
                $ssltid_string = " AND sslc.type_id = '$ssltid' ";
            } else {
                $ssltid_string = "";
            }
            if ($sslipid != "") {
                $sslipid_string = " AND sslc.ip_id = '$sslipid' ";
            } else {
                $sslipid_string = "";
            }
            if ($sslpcid != "") {
                $sslpcid_string = " AND sslc.cat_id = '$sslpcid' ";
            } else {
                $sslpcid_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (sslc.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND sslc.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for_ssl'] != "") {
                $search_string = " AND (sslc.name LIKE '%" . $_SESSION['s_search_for_ssl'] . "%' OR d.domain LIKE '%" . $_SESSION['s_search_for_ssl'] . "%')";
            } else {
                $search_string = "";
            }

            $result_account = $pdo->query("
                SELECT sslpa.id AS sslpa_id, sslpa.username, sslp.name AS sslp_name, o.name AS owner_name
                FROM ssl_accounts AS sslpa, ssl_providers AS sslp, owners AS o, ssl_certs AS sslc, domains AS d
                WHERE sslpa.ssl_provider_id = sslp.id
                  AND sslpa.owner_id = o.id
                  AND sslpa.id = sslc.account_id
                  AND sslc.domain_id = d.id
                  $is_active_string
                  $oid_string
                  $did_string
                  $sslpid_string
                  $ssltid_string
                  $sslipid_string
                  $sslpcid_string
                  $range_string
                  $search_string
                GROUP BY sslp.name, o.name, sslpa.username
                ORDER BY sslp.name asc, o.name asc, sslpa.username asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1', '', 'SSL Provider Account - ALL', 'null');
            foreach ($result_account as $row_account) {

                echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $row_account->sslpa_id . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1&null=', $row_account->sslpa_id, $row_account->sslp_name . ', ' . $row_account->owner_name . ' (' . $row_account->username . ')', $sslpaid);

            }
            echo $form->showDropdownBottom('');


            // TYPE
            if ($is_active == "0") {
                $is_active_string = " AND sslc.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND sslc.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND sslc.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND sslc.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND sslc.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND sslc.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND sslc.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND sslc.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND sslc.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND sslc.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND sslc.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND sslc.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9')";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND sslc.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')";
            }

            if ($oid != "") {
                $oid_string = " AND sslc.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($did != "") {
                $did_string = " AND sslc.domain_id = '$did' ";
            } else {
                $did_string = "";
            }
            if ($sslpid != "") {
                $sslpid_string = " AND sslc.ssl_provider_id = '$sslpid' ";
            } else {
                $sslpid_string = "";
            }
            if ($sslpaid != "") {
                $sslpaid_string = " AND sslc.account_id = '$sslpaid' ";
            } else {
                $sslpaid_string = "";
            }
            if ($sslipid != "") {
                $sslipid_string = " AND sslc.ip_id = '$sslipid' ";
            } else {
                $sslipid_string = "";
            }
            if ($sslpcid != "") {
                $sslpcid_string = " AND sslc.cat_id = '$sslpcid' ";
            } else {
                $sslpcid_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (sslc.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND sslc.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for_ssl'] != "") {
                $search_string = " AND (sslc.name LIKE '%" . $_SESSION['s_search_for_ssl'] . "%' OR d.domain LIKE '%" . $_SESSION['s_search_for_ssl'] . "%')";
            } else {
                $search_string = "";
            }

            $result_type = $pdo->query("
                SELECT sslc.type_id, sslcf.type
                FROM ssl_certs AS sslc, domains AS d, ssl_cert_types AS sslcf
                WHERE sslc.domain_id = d.id
                  AND sslc.type_id = sslcf.id
                  $is_active_string
                  $oid_string
                  $did_string
                  $sslpid_string
                  $sslpaid_string
                  $sslipid_string
                  $sslpcid_string
                  $range_string
                  $search_string
                GROUP BY sslcf.type
                ORDER BY sslcf.type asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1', '', 'SSL Type - ALL', 'null');
            foreach ($result_type as $row_type) {

                echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $row_type->type_id . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1&null=', $row_type->type_id, $row_type->type, $ssltid);

            }
            echo $form->showDropdownBottom('');


            // IP ADDRESS
            if ($is_active == "0") {
                $is_active_string = " AND sslc.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND sslc.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND sslc.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND sslc.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND sslc.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND sslc.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND sslc.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND sslc.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND sslc.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND sslc.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND sslc.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND sslc.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9')";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND sslc.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')";
            }

            if ($oid != "") {
                $oid_string = " AND sslc.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($did != "") {
                $did_string = " AND sslc.domain_id = '$did' ";
            } else {
                $did_string = "";
            }
            if ($sslpid != "") {
                $sslpid_string = " AND sslc.ssl_provider_id = '$sslpid' ";
            } else {
                $sslpid_string = "";
            }
            if ($sslpaid != "") {
                $sslpaid_string = " AND sslc.account_id = '$sslpaid' ";
            } else {
                $sslpaid_string = "";
            }
            if ($ssltid != "") {
                $ssltid_string = " AND sslc.type_id = '$ssltid' ";
            } else {
                $ssltid_string = "";
            }
            if ($sslpcid != "") {
                $sslpcid_string = " AND sslc.cat_id = '$sslpcid' ";
            } else {
                $sslpcid_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (sslc.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND sslc.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for_ssl'] != "") {
                $search_string = " AND (sslc.name LIKE '%" . $_SESSION['s_search_for_ssl'] . "%' OR d.domain LIKE '%" . $_SESSION['s_search_for_ssl'] . "%')";
            } else {
                $search_string = "";
            }

            $result_ip = $pdo->query("
                SELECT ip.id AS ip_id, ip.name AS ip_name, ip.ip
                FROM ssl_certs AS sslc, domains AS d, ip_addresses AS ip
                WHERE sslc.domain_id = d.id
                  AND sslc.ip_id = ip.id
                  $is_active_string
                  $oid_string
                  $did_string
                  $sslpid_string
                  $sslpaid_string
                  $ssltid_string
                  $sslpcid_string
                  $range_string
                  $search_string
                GROUP BY ip.name, ip.ip
                ORDER BY ip.name, ip.ip")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1', '', 'IP Address - ALL', 'null');
            foreach ($result_ip as $row_ip) {

                echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $row_ip->ip_id . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1&null=', $row_ip->ip_id, $row_ip->ip_name . ' (' . $row_ip->ip . ')', $sslipid);

            }
            echo $form->showDropdownBottom('');


            // CATEGORY
            if ($is_active == "0") {
                $is_active_string = " AND sslc.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND sslc.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND sslc.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND sslc.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND sslc.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND sslc.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND sslc.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND sslc.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND sslc.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND sslc.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND sslc.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND sslc.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9')";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND sslc.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')";
            }

            if ($oid != "") {
                $oid_string = " AND sslc.owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($did != "") {
                $did_string = " AND sslc.domain_id = '$did' ";
            } else {
                $did_string = "";
            }
            if ($sslpid != "") {
                $sslpid_string = " AND sslc.ssl_provider_id = '$sslpid' ";
            } else {
                $sslpid_string = "";
            }
            if ($sslpaid != "") {
                $sslpaid_string = " AND sslc.account_id = '$sslpaid' ";
            } else {
                $sslpaid_string = "";
            }
            if ($ssltid != "") {
                $ssltid_string = " AND sslc.type_id = '$ssltid' ";
            } else {
                $ssltid_string = "";
            }
            if ($sslipid != "") {
                $sslipid_string = " AND sslc.ip_id = '$sslipid' ";
            } else {
                $sslipid_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (sslc.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND sslc.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for_ssl'] != "") {
                $search_string = " AND (sslc.name LIKE '%" . $_SESSION['s_search_for_ssl'] . "%' OR d.domain LIKE '%" . $_SESSION['s_search_for_ssl'] . "%')";
            } else {
                $search_string = "";
            }

            $result_cat = $pdo->query("
                SELECT c.id AS cat_id, c.name AS cat_name
                FROM ssl_certs AS sslc, domains AS d, categories AS c
                WHERE sslc.domain_id = d.id
                  AND sslc.cat_id = c.id
                  $is_active_string
                  $oid_string
                  $did_string
                  $sslpid_string
                  $sslpaid_string
                  $ssltid_string
                  $sslipid_string
                  $range_string
                  $search_string
               GROUP BY c.name
               ORDER BY c.name")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1', '', 'Category - ALL', 'null');
            foreach ($result_cat as $row_cat) {

                echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $row_cat->cat_id . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1&null=', $row_cat->cat_id, $row_cat->cat_name, $sslpcid);

            }
            echo $form->showDropdownBottom('');


            // OWNER
            if ($is_active == "0") {
                $is_active_string = " AND sslc.active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND sslc.active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND sslc.active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND sslc.active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND sslc.active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND sslc.active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND sslc.active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND sslc.active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND sslc.active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND sslc.active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND sslc.active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND sslc.active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9')";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND sslc.active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')";
            }

            if ($did != "") {
                $did_string = " AND sslc.domain_id = '$did' ";
            } else {
                $did_string = "";
            }
            if ($sslpid != "") {
                $sslpid_string = " AND sslc.ssl_provider_id = '$sslpid' ";
            } else {
                $sslpid_string = "";
            }
            if ($sslpaid != "") {
                $sslpaid_string = " AND sslc.account_id = '$sslpaid' ";
            } else {
                $sslpaid_string = "";
            }
            if ($ssltid != "") {
                $ssltid_string = " AND sslc.type_id = '$ssltid' ";
            } else {
                $ssltid_string = "";
            }
            if ($sslipid != "") {
                $sslipid_string = " AND sslc.ip_id = '$sslipid' ";
            } else {
                $sslipid_string = "";
            }
            if ($sslpcid != "") {
                $sslpcid_string = " AND sslc.cat_id = '$sslpcid' ";
            } else {
                $sslpcid_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (sslc.expiry_date >= '" . $_SESSION['s_start_date'] . "' AND sslc.expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for_ssl'] != "") {
                $search_string = " AND (sslc.name LIKE '%" . $_SESSION['s_search_for_ssl'] . "%' OR d.domain LIKE '%" . $_SESSION['s_search_for_ssl'] . "%')";
            } else {
                $search_string = "";
            }

            $result_owner = $pdo->query("
                SELECT o.id, o.name
                FROM owners AS o, ssl_certs AS sslc, domains AS d
                WHERE o.id = sslc.owner_id
                  AND o.id = d.owner_id
                  $is_active_string
                  $did_string
                  $sslpid_string
                  $sslpaid_string
                  $ssltid_string
                  $sslipid_string
                  $sslpcid_string
                  $range_string
                  $search_string
                GROUP BY o.name
                ORDER BY o.name asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?oid=&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1', '', 'Owner - ALL', 'null');
            foreach ($result_owner as $row_owner) {

                echo $form->showDropdownOptionJump('index.php?oid=' . $row_owner->id . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $is_active . '&from_dropdown=1&expand=1&null=', $row_owner->id, $row_owner->name, $oid);

            }
            echo $form->showDropdownBottom('');


            // STATUS
            if ($is_active == "0") {
                $is_active_string = " AND active = '0' ";
            } elseif ($is_active == "1") {
                $is_active_string = " AND active = '1' ";
            } elseif ($is_active == "2") {
                $is_active_string = " AND active = '2' ";
            } elseif ($is_active == "3") {
                $is_active_string = " AND active = '3' ";
            } elseif ($is_active == "4") {
                $is_active_string = " AND active = '4' ";
            } elseif ($is_active == "5") {
                $is_active_string = " AND active = '5' ";
            } elseif ($is_active == "6") {
                $is_active_string = " AND active = '6' ";
            } elseif ($is_active == "7") {
                $is_active_string = " AND active = '7' ";
            } elseif ($is_active == "8") {
                $is_active_string = " AND active = '8' ";
            } elseif ($is_active == "9") {
                $is_active_string = " AND active = '9' ";
            } elseif ($is_active == "10") {
                $is_active_string = " AND active = '10' ";
            } elseif ($is_active == "LIVE") {
                $is_active_string = " AND active IN ('1', '2', '3', '4', '5', '6', '7', '8', '9')";
            } elseif ($is_active == "ALL") {
                $is_active_string = " AND active IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')";
            }

            if ($oid != "") {
                $oid_string = " AND owner_id = '$oid' ";
            } else {
                $oid_string = "";
            }
            if ($did != "") {
                $did_string = " AND domain_id = '$did' ";
            } else {
                $did_string = "";
            }
            if ($sslpid != "") {
                $sslpid_string = " AND ssl_provider_id = '$sslpid' ";
            } else {
                $sslpid_string = "";
            }
            if ($sslpaid != "") {
                $sslpaid_string = " AND account_id = '$sslpaid' ";
            } else {
                $sslpaid_string = "";
            }
            if ($ssltid != "") {
                $ssltid_string = " AND type_id = '$ssltid' ";
            } else {
                $ssltid_string = "";
            }
            if ($sslipid != "") {
                $sslipid_string = " AND ip_id = '$sslipid' ";
            } else {
                $sslipid_string = "";
            }
            if ($sslpcid != "") {
                $sslpcid_string = " AND cat_id = '$sslpcid' ";
            } else {
                $sslpcid_string = "";
            }
            if ($_SESSION['s_start_date'] != '' && $_SESSION['s_start_date'] != 'YYYY-MM-DD') {
                $range_string = " AND (expiry_date >= '" . $_SESSION['s_start_date'] . "' AND expiry_date <= '" . $_SESSION['s_end_date'] . "')";
            } else {
                $range_string = "";
            }
            if ($_SESSION['s_search_for_ssl'] != "") {
                $search_string = " AND (sslc.name LIKE '%" . $_SESSION['s_search_for_ssl'] . "%' OR d.domain LIKE '%" . $_SESSION['s_search_for_ssl'] . "%')";
            } else {
                $search_string = "";
            }

            $result_active = $pdo->query("
                SELECT active, count(*) AS total_count
                FROM ssl_certs
                WHERE id != '0'
                  $oid_string
                  $did_string
                  $sslpid_string
                  $sslpaid_string
                  $ssltid_string
                  $sslipid_string
                  $sslpcid_string
                  $range_string
                  $search_string
                GROUP BY active
                ORDER BY active asc")->fetchAll();

            echo $form->showDropdownTopJump('', '', '', '');
            echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=LIVE&from_dropdown=1&expand=1&null=', $is_active, '"Live" SSL Certificates (Active / Pending)', 'LIVE');
            foreach ($result_active as $row_active) {

                if ($row_active->active == "0") {
                    $display_text = "Expired";
                } elseif ($row_active->active == "1") {
                    $display_text = "Active";
                } elseif ($row_active->active == "3") {
                    $display_text = "Pending (Renewal)";
                } elseif ($row_active->active == "4") {
                    $display_text = "Pending (Other)";
                } elseif ($row_active->active == "5") {
                    $display_text = "Pending (Registration)";
                }

                /* TODO: This needs to be fixed, but it's going to be a very big refactoring job, and this is the best temporary band-aid solution. */
                /*
                 * The problem is that the showDropdownOptionJump method uses the == comparison operator instead of ===,
                 * so 0 technically equals "LIVE", and Expired gets selected in the dropdown menu by default, since it
                 * overrides the actual "LIVE" option.
                 *
                 * It would be easy to just change the == operator to ===, however this may break other instances of
                 * the method, so to fix this properly it's going to take some time.
                 *
                 * This issue also exists on the main Domains page.
                 *
                 * START
                 */
                if ($row_active->active === 0) $row_active->active = '0';
                /*
                 * END
                 */

                echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=' . $row_active->active . '&from_dropdown=1&expand=1&null=', $is_active, $display_text, $row_active->active);

            }
            echo $form->showDropdownOptionJump('index.php?oid=' . $oid . '&did=' . $did . '&sslpid=' . $sslpid . '&sslpaid=' . $sslpaid . '&ssltid=' . $ssltid . '&sslipid=' . $sslipid . '&sslpcid=' . $sslpcid . '&start_date=' . $new_start_date . '&end_date=' . $new_end_date . '&is_active=ALL&from_dropdown=1&expand=1&null=', $is_active, 'ALL', 'ALL');
            echo $form->showDropdownBottom('');
            ?>

            <?php echo $form->showInputText('search_for', 'SSL Keyword Search', '', $_SESSION['s_search_for_ssl'], '100', '', '', '', ''); ?>

            <?php
            echo $form->showInputText('daterange', 'Expiring Between', '', $daterange, '23', '', '', '', '');

            echo $form->showInputHidden('oid', $oid);
            echo $form->showInputHidden('did', $did);
            echo $form->showInputHidden('sslpid', $sslpid);
            echo $form->showInputHidden('sslpaid', $sslpaid);
            echo $form->showInputHidden('ssltid', $ssltid);
            echo $form->showInputHidden('sslipid', $sslipid);
            echo $form->showInputHidden('sslpcid', $sslpcid);
            echo $form->showInputHidden('is_active', $is_active);
            echo $form->showSubmitButton('Apply Filters', '', '');
            ?>
            <a href="<?php echo $web_root; ?>/ssl/"><?php echo $layout->showButton('button', 'Reset Filters'); ?></a><?php

            echo $form->showFormBottom(''); ?>

        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    <BR><?php

}

if ($result) { ?>

    <a href="add.php"><?php echo $layout->showButton('button', 'Add SSL Cert'); ?></a>
    <a target="_blank" href="<?php echo $web_root; ?>/raw.php"><?php echo $layout->showButton('button', 'Raw List'); ?></a>
    <a href="index.php?<?php echo urlencode($_SERVER['QUERY_STRING']); ?>&export_data=1"><?php echo $layout->showButton('button', 'Export'); ?></a>

    <BR><BR><strong>Total Cost:</strong> <?php echo htmlentities($grand_total, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlentities($_SESSION['s_default_currency'], ENT_QUOTES, 'UTF-8'); ?><BR>
    <strong>Number of SSL Certs:</strong> <?php echo number_format(count($result)); ?><BR><BR><?php

    if ($total_rows) { ?>

        <table id="<?php echo $slug; ?>" class="<?php echo $datatable_class; ?>">
            <thead>
            <tr>
                <th width="20px"></th>
                <th class="all">
                    Name
                </th>
                <?php if ($_SESSION['s_display_ssl_expiry_date'] == "1") { ?>
                <th>
                    Expiry
                </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_fee'] == "1") { ?>
                <th>
                    Fee
                </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_domain'] == "1") { ?>
                <th>
                    Domain
                </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_provider'] == "1") { ?>
                <th>
                    Provider
                </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_account'] == "1") { ?>
                <th>
                    Account
                </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_type'] == "1") { ?>
                <th>
                    Type
                </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_ip'] == "1") { ?>
                <th>
                    IP Address
                </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_category'] == "1") { ?>
                <th>
                    Category
                </th>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_owner'] == "1") { ?>
                <th>
                    Owner
                </th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($result as $row) { ?>
            <tr>
                <td></td>
                <td>
                    <?php if ($row->active == "0") {
                        echo "<a title=\"Expired\"><strong>x</strong></a>&nbsp;";
                    } elseif ($row->active == "3") {
                        echo "<a title=\"Pending (Renewal)\"><strong>PRn</strong></a>&nbsp;";
                    } elseif ($row->active == "4") {
                        echo "<a title=\"Pending (Other)\"><strong>PO</strong></a>&nbsp;";
                    } elseif ($row->active == "5") {
                        echo "<a title=\"Pending (Registration)\"><strong>PRg</strong></a>&nbsp;";
                    }
                    ?><a href="edit.php?sslcid=<?php echo $row->id; ?>"><?php echo $row->name; ?></a>
                </td>
                <?php if ($_SESSION['s_display_ssl_expiry_date'] == "1") { ?>
                <td>
                    <a href="edit.php?sslcid=<?php echo $row->id; ?>"><?php echo $row->expiry_date; ?></a>
                </td>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_fee'] == "1") { ?>
                <td>
                    <a href="../assets/edit/ssl-provider-fee.php?sslpid=<?php echo $row->sslp_id; ?>&fee_id=<?php echo $row->f_id; ?>">
                            <?php
                            $converted_total_cost = $row->total_cost * $row->conversion;
                            $temp_output_amount = $currency->format($converted_total_cost,
                                $_SESSION['s_default_currency_symbol'], $_SESSION['s_default_currency_symbol_order'],
                                $_SESSION['s_default_currency_symbol_space']);
                            echo htmlentities($temp_output_amount, ENT_QUOTES, 'UTF-8');
                            ?>
                        </a>
                </td>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_domain'] == "1") { ?>
                <td>
                    <a href="../domains/edit.php?did=<?php echo $row->domain_id; ?>"><?php echo $row->domain; ?></a>
                </td>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_provider'] == "1") { ?>
                <td>
                    <a href="../assets/edit/ssl-provider.php?sslpid=<?php echo $row->sslp_id; ?>"><?php echo $row->ssl_provider_name; ?></a>
                </td>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_account'] == "1") { ?>
                <td>
                    <a href="../assets/edit/ssl-provider.php?sslpid=<?php echo $row->sslp_id; ?>"><?php echo $row->ssl_provider_name; ?></a>, <a href="../assets/edit/account-owner.php?oid=<?php echo $row->o_id; ?>"><?php echo $row->owner_name; ?></a> (<a href="../assets/edit/ssl-provider-account.php?sslpaid=<?php echo $row->sslpa_id; ?>"><?php echo substr($row->username, 0, 15); ?><?php if (strlen($row->username) >= 16) echo "..."; ?></a>)
                </td>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_type'] == "1") { ?>
                <td>
                    <a href="../assets/edit/ssl-type.php?ssltid=<?php echo $row->type_id; ?>"><?php echo $row->type; ?></a>
                </td>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_ip'] == "1") { ?>
                <td>
                    <a href="../assets/edit/ip-address.php?ipid=<?php echo $row->ip_id; ?>"><?php
                        echo $row->ip_name; ?> (<?php echo $row->ip; ?>)</a>
                </td>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_category'] == "1") { ?>
                <td>
                    <a href="../assets/edit/category.php?pcid=<?php echo $row->cat_id; ?>"><?php echo $row->cat_name; ?></a>
                </td>
                <?php } ?>
                <?php if ($_SESSION['s_display_ssl_owner'] == "1") { ?>
                <td>
                    <a href="../assets/edit/account-owner.php?oid=<?php echo $row->o_id; ?>"><?php echo $row->owner_name; ?></a>
                </td>
                <?php } ?>

            </tr>
            <?php } ?>
            </tbody>
        </table><BR><?php

    }

} else {

    if ($_SESSION['s_has_ssl_provider'] == '1' && $_SESSION['s_has_ssl_account'] == '1' && $_SESSION['s_has_ssl_cert'] == '1' && $_SESSION['s_has_domain'] == '1') { ?>

        Your search returned zero results.<?php

    }

} ?>
<?php require_once DIR_INC . '/layout/footer.inc.php'; //@formatter:on ?>
<?php require_once DIR_INC . '/layout/date-range-picker-footer.inc.php'; ?>
</body>
</html>
