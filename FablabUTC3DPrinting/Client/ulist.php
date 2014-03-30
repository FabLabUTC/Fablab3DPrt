<?php
include_once 'LoginService.php'; 
include_once PLUGIN_DIR . '/DataProvider/config.php';
$rck = WPDataSource::GetUser($_SESSION["UserId"])->Rank;
if ($rck < RANK_PERMANENCIER) 
{ 
    header("HTTP/1.1 403 Forbidden"); 
    die(); 
}

if (isset($_POST['Id']))
{
    if (is_numeric($_POST["Rank"]) && ($_POST["Rank"] < RANK_PERMANENCIER || ($_POST["Rank"] < RANK_FABLAB && $rck == RANK_FABLAB))) 
    {
        $q = WPDataSource::GetUser($_POST['Id']);
        $q->Rank = intval($_POST["Rank"]);
        WPDataSource::Update($q);
        echo '<p>' . $q->Login . ' est maintenant ' . $q->Rank_str . '</p>';
    }
    else 
    {
        echo "Action interdite";
    }
}


?>
<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Login</th>
            <th>Droits</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $i = 0;
            foreach(WPDataSource::GetUsers() as $item)
            {
                if ($item->Rank == RANK_FABLAB) continue;
                ?>
                <tr>
                    <td><?php echo $item->FirstName; ?></td>
                    <td><?php echo $item->LastName; ?></td>
                    <td><?php echo $item->Email; ?></td>
                    <td><?php echo $item->Login; ?></td>
                    <td>
                        <?php ?>
                        <form action="?p=ulist.php" method="post" id="f<?php echo $item->Id; ?>">
                            <input type="hidden" name="Id" value="<?php echo $item->Id; ?>"/>
                            <select name="Rank" onchange="setTimeout(function() {document.forms['f<?php echo $item->Id; ?>'].submit();}, 1000);">
                                <option value="0" <?php if ($item->Rank == 0) { ?> selected="selected" <?php } ?> >Etudiant</option>
                                <option value="1" <?php if ($item->Rank == 1) { ?> selected="selected" <?php } ?>>Professionel</option>
                                <?php if ($rck == RANK_FABLAB) { ?>
                                <option value="2" <?php if ($item->Rank == 2) { ?> selected="selected" <?php } ?>>Permanencier</option>
                                <option value="3" <?php if ($item->Rank == 3) { ?> selected="selected" <?php } ?>>Membre</option>
                                <?php } ?>
                            </select>
                            <script type="text/javascript">

                            </script>
                        </form>
                    </td>
                </tr>
                <?php
            }
        ?>
    </tbody>
</table>