<?php

const ATTACHMENTS_DIR = "/sites/fablab/wp-content/plugins/3Dprt/Attachments";

const DB_Prefix = '3Dprt_';
const DB_Type = 'mysql';
const DB_Name = 'fablab';
const DB_Host = 'sql.mde.utc';
const DB_UserName = 'fablab';
const DB_Password = 'XEfYQsI9';

session_start();
if (!isset($_SESSION["User"])) $_SESSION["User"] = null;

include PLUGIN_DIR . '/DataProvider/Item.php';
include PLUGIN_DIR . '/DataProvider/WPDataSource.php';
include PLUGIN_DIR . '/DataProvider/PrintQuery.php';
include PLUGIN_DIR . '/DataProvider/User.php';