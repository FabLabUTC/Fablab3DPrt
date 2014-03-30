<?php 
include_once 'LoginService.php'; 
include_once PLUGIN_DIR . '/DataProvider/config.php';
if (isset($_FILES["Commande_File"]) && isset($_POST["Commande_Description"]) && isset($_POST["Commande_DeadLine"]))
{
    move_uploaded_file($_FILES["Commande_File"]["tmp_name"], $_FILES["Commande_File"]["name"]);
    $cmd = new PrintQuery($_FILES["Commande_File"]["name"], 
                            $_SESSION["UserId"], 
                            $_POST["Commande_DeadLine"], 
                            $_POST["Commande_Description"]);
    if (!in_array($cmd->Item->BaseFileType, $FILETYPE_LIST)) 
    {
        unlink($cmd->Item->BaseLink);
        echo "le type de fichier " . $cmd->Item->BaseFileType . " n'est pas supporté, utilisez un des formats autorisé";
        echo '<script>setTimeout(function() { document.location.replace("?p=commande.php");}, 5000)</script>';
    }
    else
    {
        if ($cmd->Item->Id == null) WPDataSource::Insert($cmd->Item);
        WPDataSource::Insert($cmd);
        echo '<script>setTimeout(function() { document.location.replace("?p=my.php");}, 10)</script>';
    }
}
else
{
?>
    <form method="post" action="?p=commande.php" enctype="multipart/form-data">
        <table>
            <tbody>
                <tr>
                    <td>Fichier:</td>
                    <td><input type="file" name="Commande.File" /></td>
                </tr>
                <tr>
                    <td>Description:</td>
                    <td><input type="text" name="Commande.Description" /></td>
                </tr>
                <tr>
                    <td>DeadLine:</td>
                    <td><input type="datetime" name="Commande.DeadLine" /></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Envoyer"/></td>
                </tr>
                <tr>
                    <td colspan="2">notes: nous acceptons uniquement les fichiers aux formats <?php echo implode(', ', $FILETYPE_LIST) ; ?>
                    <br/> les dates pour la deadline sont au format AAAA-MM-JJ HH:MM:SS (bien que les secondes vous sont certainement égal...)</td>
                </tr>
            </tbody>
        </table>   
    </form>
<?php } ?>