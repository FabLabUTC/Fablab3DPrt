<?php
include_once 'LoginService.php'; 
include_once PLUGIN_DIR . '/DataProvider/config.php';
?>

<table>
    <thead>
        <tr>
            <td colspan="6"><a href="?p=commande.php">Faire une nouvelle demande d'impression</a></td>
        </tr>
        <tr>
            <?php if (WPDataSource::GetUser($_SESSION["UserId"])->Rank >= RANK_PERMANENCIER) { ?> <td colspan="6"><a href="?p=admin.php">Accéder au panel de gestion</a></td> <?php } ?>
        </tr>
        <tr>
            <th>Fichier (lien)</th>
            <th>Description</th>
            <th>Date d'envoi</th>
            <th>Date limite</th>
            <th>Etat</th>
            <th>Position</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $i = 0;
            foreach(WPDataSource::GetCurrentQueue() as $item)
            {
                ++$i;
                if ($item->User->Id != $_SESSION['UserId']) continue;
                $Id = $item->Id;
                ?>
                <tr>
                    <td><a href='?p=download.php&file=<?php echo $item->Item->Id; ?>'>lien</a></td>
                    <td><?php echo $item->Item->Description; ?></td>
                    <td><?php echo $item->SubmissionDate; ?></td>
                    <td><?php echo $item->DeadLine; ?></td>
                    <td><?php echo $item->Step_str; ?></td>
                    <td><?php echo $i; ?></td>
                </tr>
                <?php
            }
        ?>
    </tbody>
</table>