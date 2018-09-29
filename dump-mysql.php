<?php

    function dumpMySQL($dbhost, $dbuser, $dbpass, $dbname, $option = 0){

        /*$option = 1 
            Irá fazer via linha de comando, se não houver restrições.*/

        /*$option = 0
            Irá fazer executando query's e guardando em uma váriavel, depois salvar em um arquivo.*/

        //Nome do arquivo que será salvo
        $file = $dbname.date("Y-m-d").".sql";

        if($option === 1){
            system("mysqldump -h $dbhost -u $dbuser -p$dbpass $dbname > /www/dump_mysql/$file");
        }else{

            //Variável do corpo do arquivo
            $body = "";

            //Conexão se for necessário
            try{
                $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            }catch(PDOException $e){
                throw new Exception('Error: '.$e->getMessage());
            }

            //Mostrar os nomes das tabelas do banco
            $stmt = $conn->prepare('SHOW TABLES');
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            foreach($result as $key){
                // Drop Table se ja existir a tabela
                $body .= "\n\nDROP TABLE IF EXISTS $key->Tables_in_atacadodasraco ;"."\n\n";

                //Create Table de cada tabela
                $query2 = "SHOW CREATE TABLE $key->Tables_in_atacadodasraco";
                $stmt = $conn->prepare($query2);
                $stmt->execute();
                $resultTwo = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach($resultTwo as $keyTwo){
                    $body .= $keyTwo["Create Table"].";\n\n";
                }

                //----------------------------------------------------------

                //Insert de cada tabela
                $query3 = "SELECT * FROM $key->Tables_in_atacadodasraco";
                $stmt = $conn->prepare($query3);
                $stmt->execute();
                $resultThree = $stmt->fetchAll(PDO::FETCH_ASSOC);

                for($i = 0; $i < count($resultThree); $i++){
                    $body .= "INSERT INTO ".$key->Tables_in_atacadodasraco." VALUES(";
                    $keys = array_keys($resultThree[$i]);
                    for($j = 0; $j < count($keys); $j++){
                        $body .= $resultThree[$i][$keys[$j]];
                        if($j != count($keys) - 1){
                            $body .= ", ";
                        }
                    }
                    $body .= ");\n\n";
                }


            }

            //----------------------------------------------------------

            //Cria o arquivo e escreve o body
            $create = fopen("dump_mysql/".$file, "x");
            fwrite($create, $body);
            fclose($create);
        
        }

    }

    

?>
