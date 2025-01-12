<?php
require_once(SITE_ROOT . '/php/db.php');
require_once(SITE_ROOT . '/php/utilities.php');
require_once(SITE_ROOT . '/php/Models/Utente.php');
use DB\DBAccess;

class Corso {

    protected $filtrable_fields = array("titolo", "descrizione", "data_inizio", "data_fine", "copertina");

    protected $id;
    protected $titolo;
    protected $descrizione;
    protected $data_inizio;
    protected $data_fine;
    protected $copertina;

    public function index(array $filters)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT corso.id, titolo, descrizione, data_inizio, data_fine, copertina, trainer as trainer_id, alt_copertina, utente.nome as trainer_nome, utente.cognome as trainer_cognome FROM corso
                INNER JOIN utente ON utente.id = trainer";
            // append if there are some filters
            if(count($filters)) $query .= append_filters($filters, $this->filtrable_fields);

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

    public static function create(array $data)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "INSERT INTO corso (id, titolo, descrizione, data_inizio, data_fine, copertina, alt_copertina, trainer)
            VALUE (
                '" . $data['id'] . "',
                '" . $data['titolo'] . "',
                '" . $data['descrizione'] . "',
                '" . $data['data_inizio'] . "',
                '" . $data['data_fine'] . "',
                '" . $data['copertina'] . "',
                '" . $data['alt_copertina'] . "',
                '" . $data['trainer'] . "'
            )";
            
            $queryResults = $connection_manager->executeQuery($query); 
            $connection_manager->closeDBConnection();
            
            return $queryResults;
        }

        return false;
    }

    public function read(int $id)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){

            $query = "SELECT * FROM corso WHERE id = " . $id;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults[0])?$queryResults[0]:NULL;
        }

        return NULL;
    }

    public function update(int $id, array $data)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){

            $query = "UPDATE corso SET 
                titolo = '" . $data['titolo'] . "', 
                descrizione = '" . $data['descrizione'] . "',
                data_inizio = '" . $data['data_inizio'] . "',
                data_fine = '" . $data['data_fine'] . "',
                copertina = '" . $data['copertina'] . "',
                alt_copertina = '" . $data['alt_copertina'] . "',
                trainer = " . $data['trainer'] . "
                
                WHERE id = " . $id;
            
            $queryResults = $connection_manager->executeQuery($query); 
            $connection_manager->closeDBConnection();
            
            return $queryResults;
        }

        return false;
    }

    public function delete(int $id)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){

            $query = "DELETE FROM corso WHERE id = " . $id;
            
            $queryResults = $connection_manager->executeQuery($query); 
            $connection_manager->closeDBConnection();
            
            return $queryResults;
        }

        return false;
    }

    /******************************************
     * 
     *              UTILITIES
     * 
     *****************************************/

    public static function isMandatory($data, $key, $name) {
        if($data[$key] == "")
            return "<li>Il campo '".$name."' va inserito</li>";
        return "";
    }

    public static function checkRegExp($data, $key, $regEx, $name) {
        if($data[$key]!="" && !preg_match($regEx, $data[$key]))
            return "<li>Il campo '".$name."' contiene input non valido</li>";
        return "";
    }

    public static function trainerExists($trainerId) {
        if(!Utente::isTrainer($trainerId))
            return "<li>Il trainer inserito non esiste</li>";
        return "";
    }

    public static function validator(array $data = NULL)
    {
        $errors = "";
        $errors .=  Corso::isMandatory($data, "titolo", "titolo").
                    Corso::isMandatory($data, "descrizione", "descrizione").
                    Corso::isMandatory($data, "data_inizio", "data di inizio").
                    Corso::isMandatory($data, "data_fine", "data di fine").
                    Corso::isMandatory($data, "alt_copertina", "descrizione copertina").
                    Corso::isMandatory($data, "trainer", "trainer").
                    Corso::checkRegExp($data, "titolo", "/^[a-zA-ZÀ-ÿ\s-]+$/", "titolo").
                    Corso::checkRegExp($data, "descrizione", "/^[a-zA-ZÀ-ÿ\s\.\,\!\"\&\*\#\:-]+$/", "descrizione").
                    Corso::checkRegExp($data, "alt_copertina", "/^[a-zA-ZÀ-ÿ\s\.\,\!\"\&\*\#\:-]+$/", "alt_copertina").
                    (strtotime($data['data_inizio']) < strtotime($data['data_fine'])?"":"<li>La data di inizio deve precedere la data di fine</li>").
                    Corso::trainerExists($data['trainer']);
        if($errors != "")
            return "<ul>".$errors."</ul>";
        return true;
    }

    public function getAllCorsi(array $filters)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT id, titolo, descrizione, data_inizio, data_fine, copertina, trainer_id, trainer_nome FROM
                (
                SELECT corso.id, titolo, descrizione, data_inizio, data_fine, copertina, trainer as trainer_id, utente.nome as trainer_nome FROM corso
                LEFT JOIN utente ON utente.id = trainer
                ) as corsi
                LEFT JOIN iscrizione_corso ON corso = id
                WHERE 1=1";

            // append if there are some filters
            if(count($filters)) $query .= append_filters($filters, $this->filtrable_fields, false);

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

    public static function getNewId() {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT id FROM corso ORDER BY id DESC";
            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?($queryResults[0]['id']+1):1;
        }

        return NULL;
    }

    public function getCorsiByUserId(int $utenteId)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT id, titolo, descrizione, data_inizio, data_fine, copertina, trainer_id, trainer_nome, trainer_cognome FROM
                (
                SELECT corso.id, titolo, descrizione, data_inizio, data_fine, copertina, trainer as trainer_id, utente.nome as trainer_nome, utente.cognome as trainer_cognome FROM corso
                LEFT JOIN utente ON utente.id = trainer
                ) as corsi
                LEFT JOIN iscrizione_corso ON corso = id 
                WHERE cliente =" . $utenteId;

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

    public function getCorsiByTrainerId(array $filters, int $trainerId)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT id, titolo, descrizione, data_inizio, data_fine, copertina, trainer_id, trainer_nome FROM
                (
                SELECT corso.id, titolo, descrizione, data_inizio, data_fine, copertina, trainer as trainer_id, utente.nome as trainer_nome FROM corso
                LEFT JOIN utente ON utente.id = trainer
                ) as corsi
                LEFT JOIN iscrizione_corso ON corso = id 
                WHERE trainer_id =" . $trainerId;
            
            // append if there are some filters
            if(count($filters)) $query .= append_filters($filters, $this->filtrable_fields, false);

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

    public function getUnregisteredCorsiByUserId(array $filters, int $utenteId)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT id, titolo, descrizione, data_inizio, data_fine, copertina, trainer_id, trainer_nome, trainer_cognome FROM
                (
                SELECT corso.id, titolo, descrizione, data_inizio, data_fine, copertina, trainer as trainer_id, utente.nome as trainer_nome, utente.cognome as trainer_cognome FROM corso
                LEFT JOIN utente ON utente.id = trainer
                ) as corsi
                LEFT JOIN iscrizione_corso ON corso = id 
                WHERE (cliente <> " . $utenteId . " OR cliente IS NULL)";
            
            // append if there are some filters
            if(count($filters)) $query .= append_filters($filters, $this->filtrable_fields, false);

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

    public function getNumeroIscritti(int $corsoId)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT COUNT(cliente) as count FROM iscrizione_corso WHERE corso =".$corsoId;

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $queryResults = $queryResults[0]['count'];
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

    public function getIscritti(int $corsoId)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT * FROM utente WHERE id IN (SELECT cliente FROM iscrizione_corso WHERE corso ='.$corsoId.')";

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

    public function registerUser(int $corsoId, int $utenteId)
    {
        if($this->read($corsoId) != NULL) {
            $connection_manager = new DBAccess();
            $conn_ok = $connection_manager->openDBConnection();

            if($conn_ok){
                $query = "INSERT INTO iscrizione_corso (cliente, corso)
                VALUE (
                    " . $utenteId . ",
                    " . $corsoId . "
                )";

                //echo $query;
                
                $queryResults = $connection_manager->executeQuery($query); 
                $connection_manager->closeDBConnection();
                
                return $queryResults;
            }

            return false;
        }

        return false;
    }

    public function unregisterUser(int $corsoId, int $utenteId)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "DELETE FROM iscrizione_corso
            WHERE 
                cliente = '" . $utenteId . "' AND
                corso = '" . $corsoId . "'";

            //echo $query;
            
            $queryResults = $connection_manager->executeQuery($query); 
            $connection_manager->closeDBConnection();
            
            return $queryResults;
        }

        return false;
    }

    public function isAlreadyRegistered(int $corsoId, int $utenteId)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT * FROM iscrizione_corso
            WHERE 
                cliente = '" . $utenteId . "' AND
                corso = '" . $corsoId . "'";

            //echo $query;
            
            $queryResults = $connection_manager->executeQuery($query); 
            $connection_manager->closeDBConnection();
            
            return isset($queryResults[0]) ? TRUE : FALSE;
        }

        return false;
    }

}

?>