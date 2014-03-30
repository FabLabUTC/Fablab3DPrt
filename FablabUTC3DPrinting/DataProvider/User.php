<?php

/**
 * Une personne qui commande
 *
 * Student description.
 *
 * @version 1.0
 * @author Alexandre
 */

const RANK_ETUDIANT = 0;
const RANK_PROFESSIONEL = 1;
const RANK_PERMANENCIER = 2;
const RANK_MEMBRE = 3;
const RANK_FABLAB = 4;
class User
{
    public $Id;
    public $FirstName;
    public $LastName;
    public $Login;
    public $Email;
    public $Password;
    protected $Rank;
    
    public function __get($name)
    {
        switch($name)
        {
            case "Rank": return $this->Rank; break;
            case "Rank_str": 
                switch ($this->Rank)
                {
                    case $this->Rank = RANK_ETUDIANT: return "Etudiant"; break;
                    case $this->Rank = RANK_PROFESSIONEL: return "Professionel"; break;
                    case $this->Rank = RANK_PERMANENCIER: return "Permanencier"; break;
                    case $this->Rank = RANK_MEMBRE: return "Membre"; break;
                    case $this->Rank = RANK_FABLAB: return "Fablab"; break;
                }
                break;
        }
    }
    
    public function __set($name, $value)
    {
        switch($name)
        {
            case "Rank": 
                if (is_int($value)) $this->Rank = $value;
                else switch ($value)
                {
                    case "Etudiant": $this->Rank = RANK_ETUDIANT; break;
                    case "Professionel": $this->Rank = RANK_PROFESSIONEL; break;
                    case "Permanencier": $this->Rank = RANK_PERMANENCIER; break;
                    case "Membre": $this->Rank = RANK_MEMBRE; break;
                    case "Fablab": $this->Rank = RANK_FABLAB; break;
                }
                break;
        }
    }
}
