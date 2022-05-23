<?php

require_once(SITE_ROOT . '/php/db.php');
require_once(SITE_ROOT . '/php/utilities.php');
use DB\DBAccess;

class Scheda {

    protected $filtrable_fields = array("data", "cliente", "trainer");

    protected $id;
    protected $data;
    protected $cliente;
    protected $trainer;

    public function index(array $filters)
    {
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = "SELECT * FROM scheda";
            // append if there are some filters
            $query .= append_filters($filters, $this->filtrable_fields);

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

    public function create(array $data)
    {

    }

    public function read(int $id)
    {

    }

    public function update(int $id, array $data)
    {

    }

    public function delete(int $id)
    {

    }

    public static function getEserciziById($id){
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = 
                "SELECT esercizio.id AS id, nome, categoria, serie, ripetizioni, riposo 
                 FROM esercizio_scheda INNER JOIN esercizio ON esercizio = esercizio.id WHERE scheda = ".$id;

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

    public static function getSchedeByUtente(int $id){
        $connection_manager = new DBAccess();
        $conn_ok = $connection_manager->openDBConnection();

        if($conn_ok){
            $query = 
                "SELECT scheda.id as id, data, CONCAT(nome, ' ', cognome) AS trainer FROM scheda 
                INNER JOIN utente on trainer = utente.id 
                WHERE cliente = ".$id;

            //echo $query;
            $queryResults = $connection_manager->executeQuery($query);
            $connection_manager->closeDBConnection();

            return isset($queryResults)?$queryResults:NULL;
        }

        return NULL;
    }

}

?>