<?php
namespace App\Core;


class Database extends QueryBuilder
{
    protected $table;

//    Global variables

    const NEW_OBJECT = 1;
    const UPDATE_OBJECT = 2;
    const DELETE_OBJECT = 3;

    const USER_TABLE = 'cc_user';


    public function __construct()
    {

        parent::__construct();

        //  jclm_   App\Models\User -> jclm_User
        $classExploded = explode("\\", get_called_class());
        $this->table = strtolower(DBPREFIXE . end($classExploded)); //jclm_User

    }

    public function save()
    {
        //INSERT OU UPDATE
        //Array ( [firstname] => Yves [lastname] => SKRZYPCZYK [email] => y.skrzypczyk@gmail.com [pwd] => Test1234 [country] => fr [role] => 0 [status] => 1 [isDeleted] => 0)
        $column = array_diff_key(get_object_vars($this), get_class_vars(get_class()));

        if (is_null($this->getId())) {

            $query = $this->pdo->prepare("INSERT INTO " . $this->table . " (" . implode(',', array_keys($column)) . ") VALUES (:" . implode(',:', array_keys($column)) . ") "); //1

        } else {

            foreach($column as $key => $value){
                $sqlColumn[] = $key . "=:". $key;
            }
            $column["id"] = $this->getId();

            $query = $this->pdo->prepare("UPDATE " . $this->table . " SET " . implode(',', $sqlColumn) . " WHERE id = :id");
        }
        $value = $query->execute($column);
        return $value;
    }

    public function deleteUser($id)
    {
        $column["id"] = $this->getId();
        $column["isDeleted"] = $this->getIsDeleted();

        $query = $this->pdo->prepare("UPDATE " . self::USER_TABLE  . " SET isDeleted = :isDeleted" . " WHERE id = :id");

        $value = $query->execute($column);
       return $value;
    }

    public function populate($data){

        foreach ($data as $key => $value){

            $method = 'set'.ucfirst($key);

            if (method_exists($this,$method)){
                $this->$method($value);
            }
        }
        return $this;
    }
}