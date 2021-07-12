<?php


namespace App\Core;


use App\Models\Role;

class Security
{
    public static function changeFile($file, $type){
        $ptr = fopen("$file", "r");
        $contenu = fread($ptr, filesize($file));

        fclose($ptr);
        $contenu = explode(PHP_EOL, $contenu);

        $line = false;
        $searchValue = '';

        switch ($type){
            case 'changeRoute':
            case 'finalChangeRoute':
                $searchValue = '/:';
                break;
            case 'changeConstantManager':
                $searchValue = 'config-sample.env';
                break;
            case 'deleteStartInstallation':
                $searchValue = '/start-install:';
                break;
            case 'removeRedirection':
                $searchValue = '$slug[0]';
                break;
        }

        foreach ($contenu as $index => $value) {
            if (strpos($value, $searchValue) !== false) {
                $line = $index;
                break;
            }
        }

        if ($line !== false){


            switch ($type){
                case 'changeRoute':
                    $contenu[$line+1] = "  controller: Security";
                    $contenu[$line+2] = "  action: registerInstall";
                    break;
                case 'finalChangeRoute':
                    $contenu[$line+1] = "  controller: Pages";
                    $contenu[$line+2] = "  action: displayFront";
                    break;
                case 'changeConstantManager':
                    $contenu[$line] = '    private $envFile = "config.env";';
                    break;
                case 'deleteStartInstallation':
                    for ($i = -1; $i < 4; $i++){
                        unset($contenu[$line+$i]);
                    }
                    break;
                case 'removeRedirection':

                    unset($contenu[$line]);
                    unset($contenu[$line+1]);
                    unset($contenu[$line+2]);
                    unset($contenu[$line+3]);
                    break;
            }

            $contenu = array_values($contenu);

            $contenu = implode(PHP_EOL, $contenu);

            $ptr = fopen($file, "w");
            fwrite($ptr, $contenu);
            fclose($ptr);
        }
    }


    public static function isAllowed($page){

        $idRole = $_SESSION['user']['id_role'];

        $role = new Role();
        $isAllowed = $role->select("$page")->where("id = :id")->setParams(["id" => $idRole])->get();

        if (empty($isAllowed)){
            throw new MyException("Erreur SQL");
        }
        if (!$isAllowed[0][$page]){
            throw new MyException("Vous n'avez pas les droits !");
        }

    }

    public static function isConnected(){
        if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
            $connected = true;
        }else {
            $connected = false;
        }
        return $connected;
    }

    public static function auth($page){

        if(self::isConnected()){
            try {
                self::isAllowed($page);
            } catch (MyException $e) {
                $e->error();
            }
        }else{
           header("Location: /connexion-admin");
           exit();
        }
    }
}