<?php


namespace App\Core;

class QueryBuilder
{

    private $request;
    private $params;
    private $select;
    private $where = [];
    private $order;
    private $limit;
    private $group;


    protected $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new \PDO(DBDRIVER . ":host=" . DBHOST . ";dbname=" . DBNAME . ";port=" . DBPORT, DBUSER, DBPWD);
        } catch (Exception $e) {
            die("Erreur SQL : " . $e->getMessage());
        }
    }

    public function select(...$columns){

        $this->select = $columns;
        return $this;
    }

    public function where(...$condition){

        $this->where = array_merge($condition);
        return $this;
    }

    public function setParams(array $params){

        $this->params = $params;
        return $this;
    }

    public function get(){

        $this->request = "SELECT ";
        $this->select ? $this->request.= implode(',',$this->select) : $this->request.= "*";
        $this->request.= " FROM " . $this->table;

        if (!empty($this->where)){
            $this->request.= " WHERE ( ";
            $this->request.= implode(' ) AND ( ',$this->where);
            $this->request.= " )";
        }

        $this->execute();

    }


    public function execute(){

        if ($this->params) {
            $query = $this->pdo->prepare($this->request);
            $query->execute($this->params);
        }else {
            $query = $this->pdo->query($this->request);
        }
        return $query->fetchAll(\PDO::FETCH_ASSOC);

    }






}