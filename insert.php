<?php
require_once("config.php");

// lien vers la base de données 
$pdo = null;

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


/*if(isset($_GET['temperature']) && isset($_GET['humidite']) && isset($_GET['particule_1_0']) && isset($_GET['particule_2_5']) && isset($_GET['particule_10_0']) && isset($_GET['id_capteur']) && isset($_GET['nom'])){
    echo $_GET['temperature'];
    echo $_GET['id_capteur'];
    $t = doubleval($_GET['temperature']);
    $h = doubleval($_GET['humidite']);
    $p1 = doubleval($_GET['particule_1_0']);
    $p2 = doubleval($_GET['particule_2_5']);
    $p10 = doubleval($_GET['particule_10_0']);
    $i = varcharval($_GET['id_capteur']);
    $n = varcharval($_GET['nom']);*/





if(isset($_POST['temperature']) && isset($_POST['humidite']) && isset($_POST['particule_1_0']) && isset($_POST['particule_2_5']) && isset($_POST['particule_10_0']) && isset($_POST['id_capteur']) && isset($_POST['nom'])){
    $t = doubleval($_POST['temperature']);
    $h = doubleval($_POST['humidite']);
    $p1 = doubleval($_POST['particule_1_0']);
    $p2 = doubleval($_POST['particule_2_5']);
    $p10 = doubleval($_POST['particule_10_0']);
    $i = varcharval($_POST['id_capteur']);
    $n = varcharval($_POST['nom']);


    echo $t;
    echo $h;
    echo $p1;
    echo $p2;
    echo $p10;
    echo $i;
    echo $n;

    // pour tester avec GET : http://10.5.25.5/insert.php?temperature=55&humidite=66&particule_1_0=6&particule_2_5=6&particule_10_0=6&id_capteur=5&nom=5


    if ($pdo==null) bddConnect();

    try {
		
		$sql = "INSERT INTO mesure_pm25 (temperature,humidite,particule_1_0,particule_2_5,particule_10_0,id_capteur,nom) VALUES (:temperature,:humidite,:particule_1_0,:particule_2_5,:particule_10_0,:id_capteur,:nom)";
		$stmt = $pdo->prepare($sql);
        $stmt->execute(array(    
                        ':temperature' => $t,
                        ':humidite' => $h,
                        ':particule_1_0' => $p1,
                        ':particule_2_5' => $p2,
                        ':particule_10_0' => $p10,
                        ':id_capteur' => $i,
                        ':nom' => $n )   );
		 
    
    
    } 
    catch (PDOException $e) { // En cas d'erreur, on annule la transaction $pdo->rollBack(); 
		  echo "Erreur : " . $e->getMessage(); 
      
    } 

}
    else {
        echo "erreur";
    }
?>
