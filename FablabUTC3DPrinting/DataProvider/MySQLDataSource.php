<?php

/**
 * MySQLDataSource short summary.
 *
 * MySQLDataSource description.
 *
 * @version 1.0
 * @author Alexandre
 */
class MySQLDataSource
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
        return $ret->fetch()['c'];
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
     * Retourne un utilisateur du service identifié par son id
     * @param int $Id 
     * @return User
     */
    public static function GetUser($Id)
    {
        $ret = self::$Link->query('SELECT * FROM `' . DB_Prefix . 'user` WHERE Id=' . self::$Link->quote($Id));
        $ret->setFetchMode(PDO::FETCH_INTO, new User());
        return $ret->fetch();
    }
    
    /**
     * Retourne les utilisateurs du service
     * @return User[]
     */
    public static function GetUsers()
    {
        $ret = self::$Link->query('SELECT * FROM `' . DB_Prefix . 'user`');
        $ret->setFetchMode(PDO::FETCH_INTO, new User());
        return $ret;
    }
    
    /**
     * Tente de résoudre l'utilisateur s'authentifiant avec les identifiants en paramètre, retourne null si cela échoue
     * @param string $Mail 
     * @param string $Pass 
     * @return User
     */
    public static function Authentify($Mail, $Pass)
    {
        $ret = self::$Link->query('SELECT * FROM `' . DB_Prefix . 'user` WHERE (Email=' . self::$Link->quote($Mail) 
                                                                . ' OR Login=' . self::$Link->quote($Mail) . ') AND Password=UNHEX(' 
                                                                . self::$Link->quote(hash('sha256', $Pass)) . ')');
        $ret->setFetchMode(PDO::FETCH_INTO, new User());
        if ($ret->rowCount() > 0) return $ret->fetch();
        else return null;
    }
    
    /**
     * Tente d'insérer un objet PHP dans la base de donnée, les objets non supporté lèvent une exception
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
            self::$Link->query('INSERT INTO `' . DB_Prefix . 'user`
                (FirstName, LastName, Login, Email, Password, Rank) 
            VALUES 
                (' . "$fn, $ln, $l, $e, $p, $r" . ')');
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
     * Tente de mettre à jour un objet PHP dans la base de donnée, les objets non supporté lèvent une exception
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
            $fn = self::$Link->quote($Object->FirstName);
            $ln = self::$Link->quote($Object->LastName);
            $l = self::$Link->quote($Object->Login);
            $e = self::$Link->quote($Object->Email);
            $p = self::$Link->quote($Object->Password);
            $r = self::$Link->quote($Object->Rank_str);
            self::$Link->query('UPDATE `' . DB_Prefix . 'user`' . "
                SET FirstName=$fn, LastName=$ln, Login=$l, Email=$e, Password=$p, Rank=$r" . '
                WHERE Id=' . $Object->Id);
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