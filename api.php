<?php

    $status = 500;
    
    // check if post or get 
    if (isset($_POST) || isset($_GET)) {
        
        // check parameters
        if(isset($_POST['source']) && isset($_POST['target']) && isset($_POST['message'])) {
            $source = $_POST['source'];
            $target = $_POST['target'];
            $message = $_POST['message'];
            
            // check source and target length and alphanumeric
            if((strlen($source) <= 32 && (strlen($source) >= 4)) && (strlen($target) <= 32 && strlen($target) >= 4) && ctype_alnum($source) && ctype_alnum($target)) { 
                
                // access database
                $mysqli = new mysqli("localhost:3306", "jbm39_1", "6pKboNpA.chC", "jbm39_ci527_api");
                
                // protect against SQL injection attacks
                $source = mysqli_real_escape_string($mysqli, $source);
                $target = mysqli_real_escape_string($mysqli, $target);
                $message = mysqli_real_escape_string($mysqli, $message); 
                
                // build SQL statement
                $sql = "INSERT INTO messages (source, target, message) "
                . "VALUES ('$source', '$target', '$message')";
                
                // exceute SQL statement
                $result = $mysqli -> query($sql);
                
                if($result !== false) {
                    
                    // success 
                    $id = array('id'=> $mysqli -> insert_id);
                    $status = 201;
                } else {
                    
                    // failure
                    $status = 400;
                }
                
            } else {
                $status = 400;
            }
            http_response_code($status);
            if (isset($id)) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode($id, JSON_PRETTY_PRINT);
            }
            
        } 
        // check if source or target set and if alphanumeric
        else if (isset($_GET['source']) || isset($_GET['target'])) {
            
            
            
            // access database
            $mysqli = new mysqli("localhost:3306", "jbm39_1", "6pKboNpA.chC", "jbm39_ci527_api");
            
            // protect against SQL injection
            if (isset($_GET['source'])) {
                $source = mysqli_real_escape_string($mysqli, $_GET['source']);
            }
            if (isset($_GET['target'])) {
                $target = mysqli_real_escape_string($mysqli, $_GET['target']);
            }
            
            // handle different params and build SQL statement
            if (isset($_GET['source']) && isset($_GET['target'])) {

                // check source and target for length and alphanumeric
                if((strlen($source) <= 32 && (strlen($source) >= 4)) && (strlen($target) <= 32 && strlen($target) >= 4) && ctype_alnum($source) && ctype_alnum($target)) { 
                    $sql = "SELECT * FROM messages WHERE source='$source' AND target='$target'";
                }
            } else if (isset($_GET['source']) && !isset($_GET['target'])) {
                
                // check source only for length and alphanumric
                if((strlen($source) <= 32 && (strlen($source) >= 4)) && ctype_alnum($source)) { 
                    $sql = "SELECT * FROM messages WHERE source='$source'";
                }
            } else if (!isset($_GET['source']) && isset($_GET['target'])) {
                
                // check target only for length and alphanumeric
                if( (strlen($target) <= 32 && strlen($target) >= 4) && ctype_alnum($target)) { 
                    $sql = "SELECT * FROM messages WHERE target='$target'";
                }
            } 
            
            // check sql statement
            if (isset($sql)) {
                $result = $mysqli -> query($sql);
                
                // check result returned
                if ($result !== false) {
                    
                    // check if any messages
                    if (mysqli_num_rows($result) != 0) {
                        $status = 200;
                    
                        // populate array with any possible message data
                        $data = array();
                        while ($row = $result -> fetch_assoc()) {
                            $data[] = array('messageId' => (int)$row['messageId'], 
                                            'sent' => $row['sent'], 
                                            'source' => $row['source'], 
                                            'target' => $row['target'], 
                                            'message' => $row['message']);
                        }  
                    } else  {
                        $status = 204;
                        echo "204";
                    }
                } else {
                    $status = 400;
                }
            } else {
                $status = 400;
            }
            
            http_response_code($status);
            if (isset($data)) {
                header('Content-Type: application/json; charset=UTF-8');
                echo '{"messages":'.json_encode($data, JSON_PRETTY_PRINT).'}';
            }
        } else {
            $status = 400;
            http_response_code($status);
        }
        
        $mysqli -> close();
        
    } else {
        $status = 405;
        http_response_code($status);
    }
?>