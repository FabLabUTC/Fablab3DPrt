<?php
include_once 'LoginService.php'; 
include_once PLUGIN_DIR . '/DataProvider/config.php';
if (WPDataSource::GetUser($_SESSION["UserId"])->Rank < RANK_PERMANENCIER) 
{ 
    header("HTTP/1.1 403 Forbidden"); 
    die(); 
}

if (isset($_POST['Id'])) 
{
    $q = WPDataSource::GetQuery($_POST['Id']);
    if ($q->Step < QUERY_CLOSED) $q->Step = $q->Step + 1;
    WPDataSource::Update($q);
}

function nextstep($s)
{
    if ($s == QUERY_DONE) return "Fermer la commande (payé)";
    if ($s == QUERY_SUBMITED) return "Valider la commande";
    if ($s == QUERY_VALIDED) return "Marquer la commande comme imprimé";
}
?>
<script type="text/javascript" src="<?php echo HTML_PLUGIN_DIR ;?>/Client/References/sorttable.js"></script>
<table class="sortable">
    <thead>
        <tr>
            <th>Fichier (lien)</th>
            <th>Client</th>
            <th>Description</th>
            <th>Date d'envoi</th>
            <th>Date limite</th>
            <th>Etat</th>
            <th>Position</th>
            <th>Validation d'étape</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $i = 0;
            foreach(WPDataSource::GetCurrentQueue() as $item)
            {
                ++$i;
                $Id = $item->Id;
                ?>
                <tr>
                    <td><a href='?p=download.php&file=<?php echo $item->Item->Id; ?>'>lien</a></td>
                    <td><a href='mailto:<?php echo $item->User->Email; ?>'><?php echo $item->User->Login; ?></a></td>
                    <td><?php echo $item->Item->Description; ?></td>
                    <td><?php echo $item->SubmissionDate; ?></td>
                    <td><?php echo $item->DeadLine; ?></td>
                    <td><?php echo $item->Step_str; ?></td>
                    <td><?php echo $i; ?></td>
                    <td>
                        <form action="?p=admin.php" method="post">
                            <input type="hidden" name="Id" value="<?php echo $Id; ?>"/>
                            <input type="submit" value="<?php echo nextstep($item->Step); ?>"/>
                        </form>
                    </td>
                </tr>
                <?php
            }
        ?>
    </tbody>
</table>