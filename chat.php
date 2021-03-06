<?php
session_start();
ob_start();
header("Content-type: application/json");
date_default_timezone_set('UTC');
//connect to database
$db = mysqli_connect('mariadb', 'cs431s23', 'Va7Wobi9', 'cs431s23');
if (mysqli_connect_errno()) {
   echo '<p>Error: Could not connect to database.<br/>
   Please try again later.</p>';
   exit;
}
//helper funtion to replace get_results() if without mysqlnd 
function get_result( $Statement ) {
    $RESULT = array();
    $Statement->store_result();
    for ( $i = 0; $i < $Statement->num_rows; $i++ ) {
        $Metadata = $Statement->result_metadata();
        $PARAMS = array();
        while ( $Field = $Metadata->fetch_field() ) {
            $PARAMS[] = &$RESULT[ $i ][ $Field->name ];
        }
        call_user_func_array( array( $Statement, 'bind_result' ), $PARAMS );
        $Statement->fetch();
    }
    return $RESULT;
}

try { 
    $currentTime = time();
    $session_id = session_id();    
    $lastPoll = isset($_SESSION['last_poll']) ? $_SESSION['last_poll'] : $currentTime;    
    $action = isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST') ? 'send' : 'poll';
    switch($action) {
        case 'poll':
           $query = "SELECT * FROM chatlog WHERE date_created >= ".$lastPoll;
           $stmt = $db->prepare($query);
           $stmt->execute();
           $stmt->bind_result($id, $message, $session_id, $date_created);
           $result = get_result($stmt);
           $newChats = [];
           while($chat = array_shift($result)) {
               
               if($session_id == $chat['sent_by']) {
                  $chat['sent_by'] = 'self';
               } else {
                  $chat['sent_by'] = 'other';
               }
             
               $newChats[] = $chat;
            }
           $_SESSION['last_poll'] = $currentTime;

           print json_encode([
                'success' => true,
		        'messages' => $newChats
           ]);
           exit;
        case 'send':
            $userName = isset($_POST['userName']) ? $_POST['userName'] : ''; //Get the username from the userName form
            $userName = strip_tags($userName); //Strips and HTML and PHP tags from string

            $message = isset($_POST['message']) ? $_POST['message'] : ''; //Get the message from the message form           
            $message = strip_tags($message); //Strips and HTML and PHP tags from string

            $findUserQuery = "SELECT username, color FROM chatlog WHERE username = '".$userName."'"; //query to get username and color from the database
            $queryResult = $db->query($findUserQuery); //run the query

            if ($queryResult->num_rows > 0) { //if there is a username match, then use the color from the database
                $row = $queryResult->fetch_assoc();
                $color = $row["color"];
            }
            else { //else if there is no username match, then assign a random color from the 10 options below
                $randomNum = rand(1,10); // random number from 1 to 10
                switch($randomNum){
                    case 1:
                    $color="#d98880";
                    break;
                    case 2: 
                    $color="#c39bd3";
                    break;
                    case 3:
                    $color="#7fb3d5";
                    break;
                    case 4:
                    $color="#76d7c4";
                    break;
                    case 5:
                    $color="#7dcea0";
                    break;
                    case 6:
                    $color="#f7dc6f";
                    break;
                    case 7:
                    $color="#f0b27a";
                    break;
                    case 8:
                    $color="#d7dbdd";
                    break;
                    case 9:
                    $color="#d6eaf8";
                    break;
                    case 10:
                    $color="#f2d7d5";
                    break;
                    default:
                    $color="#a2d9ce";
                }  
            }
            $query = "INSERT INTO chatlog (message, sent_by, date_created, username, color) VALUES(?, ?, ?, ?, ?)"; //Prepared SQL query that inserts data into database
            $stmt = $db->prepare($query);
            $stmt->bind_param('ssiss', $message, $session_id, $currentTime, $userName, $color); 
            $stmt->execute(); 
            print json_encode(['success' => true]);
            exit;
    }
} catch(Exception $e) {
    print json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}


?>
