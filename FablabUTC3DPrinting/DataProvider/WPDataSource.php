<?php

/**
 * WPDataSource short summary.
 *
 * WPDataSource description.
 *
 * @version 1.0
 * @author Alexandre
 */
class WPDataSource
{
    /**
     * Lien vers la BDD
     * @var PDO
     */
    private static $Link;
    
    public static function Connect($DataBaseType = DB_Type, $DBName = DB_Name, $Host = DB_Host, $Username = DB_UserName, $Password = DB_Password)
    {
        self::$Link = new PDO("$DataBaseType:dbname=$DBName;host=$Host", $Username, $Password);
        self::$Link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    /**
     * Obtient la liste des impressions à faire
     * @param string $Where 
     * @param string $Order 
     * @param string $Limit 
     * @return PrintQuery[]
     */
    public static function GetCurrentQueue($Where = null, $Order = 'SubmissionDate ASC', $Limit = null)
    {
        $q = 'SELECT * FROM `' . DB_Prefix . 'printquery`';
        if ($Where != null) $q .= " WHERE (" . $Where . ") AND (Step != 'Closed')";
        else $q .= " WHERE (Step != 'Closed')";
        $q .= ' ORDER BY ' . $Order;
        if ($Limit != null) $q .= ' LIMIT ' . $Limit;
        $ret = self::$Link->query($q);
        $ret->setFetchMode(PDO::FETCH_INTO, new PrintQuery());
        return $ret;
    }
    
    /**
     * Retourne le nombre d'impression posté avant la date x
     * @param mixed $date 
     * @return int
     */
    public static function CountQueryBefore($date)
    {
        $ret = self::$Link->query('SELECT COUNT(*) as c FROM `' . DB_Prefix . 'printquery` WHERE SubmissionDate <=' . self::$Link->quote($date) . ' AND Step != \'Closed\'');
        $x = $ret->fetch();
        return $x['c'];
    }
    
    /**
     * Retourne une demande d'impression identifié par son id
     * @param int $Id 
     * @return PrintQuery
     */
    public static function GetQuery($Id)
    {
        $ret = self::$Link->query('SELECT * FROM `' . DB_Prefix . 'printquery` WHERE Id=' . self::$Link->quote($Id));
        $ret->setFetchMode(PDO::FETCH_INTO, new PrintQuery());
        return $ret->fetch();
    }
    
    /**
     * Retourne les métadonnées d'un fichier identifier par son id
     * @param int $Id 
     * @return Item
     */
    public static function GetItem($Id)
    {
        $ret = self::$Link->query('SELECT * FROM `' . DB_Prefix . 'item` WHERE Id=' . self::$Link->quote($Id));
        $ret->setFetchMode(PDO::FETCH_INTO, new Item());
        return $ret->fetch();
    }
    /**
     * Retourne les métadonnées d'un fichier identifier par sa somme de contrôle SHA1
     * @param int $Id 
     * @return Item
     */
    public static function GetItemByCheckSum($cs)
    {
        $ret = self::$Link->query('SELECT * FROM `' . DB_Prefix . 'item` WHERE CheckSum= 0x' . $cs);
        $ret->setFetchMode(PDO::FETCH_INTO, new Item());
        return $ret->fetch();
    }
    
    /**
     * Retourne un utilisateur du service identifié par son id MODIF: va demander à WP à la place
     * @param int $Id 
     * @return User
     */
    public static function GetUser($Id)
    {
        $udata =  get_userdata($Id);
        return self::MapWPUser($udata);
    }
    public static function MapWPUser($wp_user)
    {
        $r = new User();
        $r->Id = $wp_user->ID;
        $r->FirstName = $wp_user->first_name;
        $r->LastName = $wp_user->last_name;
        $r->Email = $wp_user->user_email;
        $r->Login = $wp_user->user_login;
        $d = self::$Link->query('SELECT * FROM `' . DB_Prefix . 'Usermeta` WHERE UserId=' . $r->Id);
        
        $d = $d->fetch();
        if (isset($d['Rank'])) $r->Rank = $d['Rank'];
        else {
            switch (true)
            {
                case $wp_user->wp_capabilities['administrator'] == 1:
                    $r->Rank = RANK_FABLAB;
                    break;
                default:
                    $r->Rank = RANK_ETUDIANT;
                    break;
            }
            self::Insert($r);
        }
        return $r;
    }
    
    /**
     * Retourne les utilisateurs du service MODIF: va demander à WP à la place
     * @return User[]
     */
    public static function GetUsers()
    {
        return array_map(function($a) {return WPDataSource::MapWPUser($a);}, get_users());
    }
    
    /**
     * MODIF: récupère le current user de wp
     * @param string $Mail 
     * @param string $Pass 
     * @return User
     */
    public static function Authentify($Mail, $Pass)
    {
        $ret = wp_get_current_user();
        if ($ret instanceof WP_User && $ret->ID != 0) return self::MapWPUser($ret);
        else return null;
    }
    
    /**
     * Tente d'insérer un objet PHP dans la base de donnée, les objets non supporté lèvent une exception /!\ NE FONCTIONNE PAS POUR USER
     * @param mixed $Object 
     * @throws Exception 
     */
    public static function Insert($Object)
    {
        if (is_a($Object, "Item"))
        {
            $bft = self::$Link->quote($Object->BaseFileType);
            $bl = self::$Link->quote($Object->BaseLink);
            $cs = self::$Link->quote($Object->CheckSum);
            $d = self::$Link->quote($Object->Description);
            self::$Link->query('INSERT INTO `' . DB_Prefix . 'item`
                (BaseFileType, BaseLink, CheckSum, Description) 
            VALUES 
                (' . "$bft, $bl, $cs, $d" . ')');
        }
        else if (is_a($Object, "User"))
        {
            $fn = self::$Link->quote($Object->FirstName);
            $ln = self::$Link->quote($Object->LastName);
            $l = self::$Link->quote($Object->Login);
            $e = self::$Link->quote($Object->Email);
            $p = self::$Link->quote($Object->Password);
            $r = self::$Link->quote($Object->Rank_str);
            self::$Link->query('INSERT INTO `' . DB_Prefix . 'Usermeta` (UserId, Rank) VALUES (' . self::$Link->quote($Object->Id) . ', ' . self::$Link->quote($Object->Rank_str) . ')');  
        }
        else if (is_a($Object, "PrintQuery"))
        {
            $u = self::$Link->quote($Object->User->Id);
            $i = self::$Link->quote($Object->Item->Id);
            $dl = self::$Link->quote($Object->DeadLine);
            self::$Link->query('INSERT INTO `' . DB_Prefix . 'printquery`
                (User, Item, DeadLine) 
            VALUES 
                (' . "$u, $i, $dl" . ')');
        }
        else { throw new Exception( get_class($Object) . " is not a valid table"); }
        $Object->Id = self::$Link->lastInsertId();
    }
    
    /**
     * Tente de mettre à jour un objet PHP dans la base de donnée, les objets non supporté lèvent une exception /!\ NE FONCTIONNE PAS POUR USER
     * @param mixed $Object 
     * @throws Exception 
     */
    public static function Update($Object)
    {
        if ($Object->Id == null) throw new Exception("Item not in DB");
        if (is_a($Object, "Item"))
        {
            $bft = self::$Link->quote($Object->BaseFileType);
            $bl = self::$Link->quote($Object->BaseLink);
            $cs = self::$Link->quote($Object->CheckSum);
            $d = self::$Link->quote($Object->Description);
            self::$Link->query('UPDATE `' . DB_Prefix . 'item`' . "
                SET BaseFileType=$bft, BaseLink=$bl, CheckSum=$cs, Description=$d" . '
                WHERE Id=' . $Object->Id);
        }
        else if (is_a($Object, "User"))
        {
            $user = new WP_User( $Object->Id );
            wp_update_user(array(
                    'ID' => $Object->Id,
                    //'user_login' => $Object->Login,
                    'user_email' => $Object->Email,
                    'first_name' => $Object->FirstName,
                    'last_name' => $Object->LastName,
                ));
            self::$Link->query('UPDATE `' . DB_Prefix . 'Usermeta` SET Rank=' . self::$Link->quote($Object->Rank_str) . ' WHERE UserId =' . self::$Link->quote($Object->Id));  
        }
        else if (is_a($Object, "PrintQuery"))
        {
            $u = self::$Link->quote($Object->User->Id);
            $i = self::$Link->quote($Object->Item->Id);
            $dl = self::$Link->quote($Object->DeadLine);
            $s = self::$Link->quote($Object->Step_str);
            self::$Link->query('UPDATE `' . DB_Prefix . 'printquery`' . "
                SET User=$u, Item=$i, DeadLine=$dl, Step=$s" . '
                WHERE Id=' . $Object->Id);
        }
    }
}
WPDataSource::Connect();