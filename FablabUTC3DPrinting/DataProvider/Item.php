<?php
/**
 * Un objet à imprimer
 *
 * Item description.
 *
 * @version 1.0
 * @author Alexandre
 */
const FILETYPE_UNDEFINED = "Undefined";
const FILETYPE_STL = "STL";
const FILETYPE_THING = "THING";

$FILETYPE_LIST = array(FILETYPE_THING, FILETYPE_STL);

class Item
{
    public $Id;
    
    /**
     * Type du fichier envoyé par le client
     * @var int
     */
    public $BaseFileType;
    /**
     * Emplacement du fichier du client sur nos serveurs (TODO: faire un dossier interdit au public pour stocker tout ça)
     * @var mixed
     */
    public $BaseLink;
    
    /**
     * Si on reçoi deux fois le même fichier on va quand même pas le stocker deux fois sur notre serveur, si?
     * @var mixed
     */
    public $CheckSum;
    
    public $Description;
    
    public function __construct($File = null)
    {
        if ($File == null) return;
        if (self::FileExists($File))
        {
            $i = WPDataSource::GetItemByCheckSum(sha1_file($File));
            foreach (get_object_vars($i) as $k => $v)
                $this->$k = $v;
        }
        else
        {
            $this->CheckSum = sha1_file($File);
            $this->BaseLink = ATTACHMENTS_DIR . '/' . $this->CheckSum;
            $this->BaseFileType = strtoupper(pathinfo($File, PATHINFO_EXTENSION));
            rename($File, $this->BaseLink);
            $this->CheckSum = pack("H*" , $this->CheckSum);
        }
    }
    
    /**
     * Obtient un lien de téléchargement de fichier après éventuelle conversion
     * @param int $Format 
     * @return string
     */
    public function GetFileLink($Format = FILETYPE_UNDEFINED)
    {
        switch ($Format)
        {
            case FILETYPE_UNDEFINED:
            case $this->BaseFileType:
                return $this->BaseLink; 
                break;
            default:
                throw new Exception("Unhandled conversion");
               
        }
    }
    
    public static function FileExists($File)
    {
        return file_exists(ATTACHMENTS_DIR . '/' . sha1_file($File));
    }
    
    private static function MakeAbsolute($Uri)
    {
        if (substr($Uri, 0, 1) == '/') $Uri = substr($Uri, 1);
        return 'http://' . $_SERVER['HTTP_HOST'] . '/' . $Uri;
    }
}
