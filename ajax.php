<?php
    header('Content-Type: application/json; charset=utf-8');
    include 'mysqldiff.php';



    function searchArr($searchkey,$searchvalue, $array) {
        foreach ($array as $key => $val) {
            if ($val[$searchkey] === $searchvalue) {
                return $key;
            }
        }
        return null;
    }

    if(isset($_POST['host']) && isset($_POST['port']) && isset($_POST['username']) && isset($_POST['dbname']) && $_POST['test'] == 1)
    {

        $connection = new \mysqlDiff\mysqlDiff();
        $connectionResult = $connection->connectionTest($_POST['host'],$_POST['port'],$_POST['username'],$_POST['password'],$_POST['dbname']);

        echo json_encode(['connection_test_result'=>$connectionResult]);

    }


    if(isset($_POST['master_host']) && isset($_POST['master_port']) && isset($_POST['master_username']) && isset($_POST['master_dbname']) && isset($_POST['slave_host']) && isset($_POST['slave_port']) && isset($_POST['slave_username']) && isset($_POST['slave_dbname']))
    {


        $errors = [];

        $tablesMasterTypes = new \mysqlDiff\mysqlDiff();
        $tablesMasterTypesResult = $tablesMasterTypes->connectionGetTablesTypes($_POST['master_host'],$_POST['master_port'],$_POST['master_username'],$_POST['master_password'],$_POST['master_dbname']);

        $tablesSlaveTypes = new \mysqlDiff\mysqlDiff();
        $tablesSlaveTypesResult = $tablesSlaveTypes->connectionGetTablesTypes($_POST['slave_host'],$_POST['slave_port'],$_POST['slave_username'],$_POST['slave_password'],$_POST['slave_dbname']);



        foreach ($tablesMasterTypesResult as $masterData){

            // check table

            $searchTable = searchArr('name',$masterData['name'], $tablesSlaveTypesResult);

            if(is_null($searchTable)){
                $error = [
                    'type' => 'table',
                    'name' => $masterData['name'],
                    'error' => 'Not Exits'
                ];
                array_push($errors,$error);
            } else {

                // check column

                foreach($masterData['content'] as $masterTableColumns){
                    $searchColumn = searchArr('Field',$masterTableColumns['Field'], $tablesSlaveTypesResult[$searchTable]['content']);

                    if(is_null($searchColumn)){
                        $error = [
                            'type' => 'column',
                            'table' => $masterData['name'],
                            'name' => $masterTableColumns['Field'],
                            'error' => 'Not Exits'
                        ];
                        array_push($errors,$error);
                    } else {

                        $columnDiffList = array_diff($masterTableColumns, $tablesSlaveTypesResult[$searchTable]['content'][$searchColumn]);

                        if($columnDiffList){

                            // check types

                            foreach ($columnDiffList as $columnDiffKey=>$columnDiffVal){

                                $error = [
                                    'type' => 'column_content',
                                    'table' => $masterData['name'],
                                    'name' => $masterTableColumns['Field'],
                                    'error_type' => $columnDiffKey,
                                    'master_value' => $masterTableColumns[$columnDiffKey],
                                    'slave_value' => $tablesSlaveTypesResult[$searchTable]['content'][$searchColumn][$columnDiffKey],

                                ];
                                array_push($errors,$error);
                            }
                        }



                    }

                }

            }


        }


        $html = '';
        if($errors){
            $result = 1;
            foreach ($errors as $error){
                if($error['type']=='table'){
                    $html.="<tr>
                            <td>".$error['name']."</td>
                            <td>Table Not Exits</td>
                            <td>-</td>
                            <td>-</td>
                          </tr>";
                } else if($error['type']=='column'){
                    $html.="<tr>
                            <td>".$error['table'].".".$error['name']."</td>
                            <td>Column Not Exits</td>
                            <td>-</td>
                            <td>-</td>
                          </tr>";
                } else if($error['type']=='column_content'){
                    $html.="<tr>
                            <td>".$error['table'].".".$error['name']."</td>
                            <td>".$error['error_type']." Not Match</td>
                            <td>".$error['master_value']."</td>
                            <td>".$error['slave_value']."</td>
                          </tr>";
                }
            }
        } else {
            $result = 0;
        }
        echo json_encode(['result'=>$result,'errors'=>$errors,'html'=>$html]);
    }






?>