<?php
const PLUGIN_DIR = "/sites/fablab/wp-content/plugins/3Dprt";
const HTML_PLUGIN_DIR = "/fablab/wp-content/plugins/3Dprt";
include_once PLUGIN_DIR . '/DataProvider/config.php';
if ($_SESSION["User"] == null)
{
    $_SESSION["User"] = WPDataSource::Authentify(null, null);
    if ($_SESSION["User"] != null) $_SESSION["UserId"] = $_SESSION["User"]->Id;
}

if (isset($_GET['p']) && strpos($_GET['p'], '/') === false && $_SESSION["User"] != null) 
{
    include $_GET['p'];
}
?>