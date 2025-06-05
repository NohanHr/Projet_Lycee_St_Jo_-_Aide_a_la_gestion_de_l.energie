<?php


require_once("config.php");

// lien vers la base de données 
$pdo = null;


/** 
 * Connexion à la base de données
 */
function bddConnect() {
    global $pdo;
    try {
      $pdo = new PDO("mysql:host=".SERVER.";dbname=".BDD.";charset=utf8",
                   USER, PWD,
                   array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION)); 
    } catch (Exception $e) {
        echo $e->getMessage();
        $pdo = null;
        die();
    }
}


 



// pour tester avec GET : http://10.5.25.5/add_son.php?level=1223.3&id_m5=101	

//if(isset($_GET['level']) && isset($_GET['id_m5']) ){
    //echo $_GET['level'];
    //echo $_GET['id_m5'];
    //$l = doubleval($_GET['level']);
    //$i = intval($_GET['id_m5']);

if(isset($_POST['level']) && isset($_POST['id_m5']) ){
    $l = doubleval($_POST['level']);
    $i = intval($_POST['id_m5']);

    echo $l;
    echo $i;
//-----------// pour tester avec GET : http://10.5.25.5/add_son.php?level=1223.3&id_m5=101
    if ($pdo==null) bddConnect();

    try {
		
		$sql = "INSERT INTO mesure_sound (sonore,nom) VALUES (:level,:id)";
		$stmt = $pdo->prepare($sql);
        $stmt->execute(array(    
                        ':level' => $l,
                        ':id' => $i )   );
		 
    
    
    } 
    catch (PDOException $e) { // En cas d'erreur, on annule la transaction $pdo->rollBack(); 
		  echo "Erreur : " . $e->getMessage(); 
      
    } 

}
    else {
        echo "erreur";
    }
	



?>




