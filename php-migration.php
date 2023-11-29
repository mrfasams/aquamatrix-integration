<?php


// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'ocdb');
define('DB_PORT', '3307');
define('DB_PREFIX', 'oc_');
//set map api url
$url = "https://aquamatrix.bg/aquamatrix_categories_feed.json";

//call api
$json = file_get_contents($url);
$json = json_decode($json);




function printCategories($category)
{
	$idCat = $category->id;
	$nameCat = $category->name;
	
    echo "Id : " .$idCat. " Name  : " .  $nameCat;
	echo "<br>";
	if (property_exists($category, 'subcategories')) {
		$countCat = count($category->subcategories);
		for ($i = 0; $i < $countCat; $i++) {
			
			printCategories($category->subcategories[$i]);
	
		}
	}
}


//printCategories($json);

$servername = "localhost";
$username = "root";
$password = "";
$password = "";

try {
  $conn = new PDO("mysql:host=$servername;dbname=ocdb", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

// output
// Latitude: 40.6781784, Longitude: -73.9441579
?>