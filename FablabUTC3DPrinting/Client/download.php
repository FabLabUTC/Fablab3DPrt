<?php
include_once 'LoginService.php'; 
include_once '/sites/fablab/wp-content/plugins/3Dprt' . '/DataProvider/config.php';

$file = WPDataSource::GetItem($_GET["file"]);

if (isset($_GET["format"]) && in_array($_GET["format"], $FILETYPE_LIST))
{
    $RAW_OUTPUT = true;
    try
    {
        $ln = $file->GetFileLink($_GET["format"]);
    }
    catch (Exception $e) { $ln = $file->GetFileLink(); $_GET["format"] = $file->BaseFileType;  }
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=f.' . $_GET["format"]);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($ln));
    
    ob_clean();
    flush();
    readfile($ln);
    die();
}
else
{ ?>
<table>
    <tbody>
        <?php foreach ($FILETYPE_LIST as $ft) { ?>
        <tr>
            <td><a href="<?php echo HTML_PLUGIN_DIR;?>/Client/download.php?file=<?php echo urlencode($_GET["file"]) . '&format=' . urlencode($ft); ?>">Télécharger en <?php echo $ft; ?></a></td>
        </tr>
        <?php } ?>
        <tr>
            <td>note: si la conversion vers le type choisi n'est pas possible, vous receverez un autre format accepté par notre imprimante</td>
        </tr>
    </tbody>
</table>
<?php } ?>