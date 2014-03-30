<?php

/**
 * Une demande d'impression 3D
 *
 * PrintQuery description.
 *
 * @version 1.0
 * @author Alexandre
 */
const QUERY_SUBMITED = 0; //quand c'est en attente de modération (format de fichier, est ce que c'est imprimable?, ...)
const QUERY_VALIDED = 1; //quand c'est en file d'attente
const QUERY_DONE = 2; //quand c'est imprimé
const QUERY_CLOSED = 3; //quand c'est payé

class PrintQuery
{
    public $Id;
    
    /**
     * une personne voulant imprimer cet objet
     * @var User
     */
    protected $User;
    /**
     * Objet à imprimer pour cette commande
     * @var Item
     */
    protected $Item;
    /**
     * Date de la demande
     * @var mixed
     */
    public $SubmissionDate;
    /**
     * Parfois il y a une deadline, quand c'est possible on peut en tenir compte
     * @var mixed
     */
    public $DeadLine;
    
    /**
     * L'étape d'impression
     * @var mixed
     */
    protected $Step;
    
    public function __set($name, $value)
    {
        switch ($name)
        {
            case 'Item': 
                if (is_a($value, 'Item')) $this->Item = $value;
                else $this->Item = WPDataSource::GetItem($value);
                break;
            case 'User':
                if (is_a($value, 'User')) $this->User = $value;
                else $this->User = WPDataSource::GetUser($value);
                break;
            case 'Step': 
                if (is_int($value)) $this->Step = $value;
                else switch($value)
                {
                    case 'Submited': $this->Step = QUERY_SUBMITED; break;
                    case 'Valided': $this->Step = QUERY_VALIDED; break;
                    case 'Done': $this->Step = QUERY_DONE; break;
                    case 'Closed': $this->Step = QUERY_CLOSED; break;
                }
                break;
                
        }
    }
    public function __get($name)
    {
        switch ($name)
        {
            case 'Item': 
                return $this->Item;
                break;
            case 'User':
                return $this->User;
                break;
            case 'Step':
                return $this->Step;
                break;
            case 'Step_str': 
                switch($this->Step)
                {
                    case QUERY_SUBMITED: return 'Submited'; break;
                    case QUERY_VALIDED: return 'Valided'; break;
                    case QUERY_DONE: return 'Done'; break;
                    case QUERY_CLOSED: return 'Closed'; break;
                }
                break;
        }
    }
    
    public function __construct($File = null, $User = null, $DeadLine = "5000-01-01 00:00:00", $Description = "Aucune description")
    {
        if ($File == null || $User == null) return;
        $this->Item = new Item($File);
        $this->__set("User", $User);
        $this->DeadLine = $DeadLine;
        $this->Item->Description = $Description;
    }
}
