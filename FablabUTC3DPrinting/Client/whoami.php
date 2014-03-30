<?php

echo 'you are: <br/>';
$u = WPDataSource::GetUser($_SESSION["UserId"]);
var_dump($u);
echo'<br/>your 3Dprt role is: ' . $u->Rank_str;