<?php



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


printCategories($json);

require_once('C:\Users\nikoleta.todorova\Documents\big-e-migration\htdocs\shop\config.php');

// Load the database class
require_once(DIR_SYSTEM . 'library\db.php');

// Load the OpenCart autoloader
require_once(DIR_SYSTEM . 'startup.php');

// Load the registry class
require_once(DIR_SYSTEM . 'engine/registry.php');

// Database initialization
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);


// Perform database operations
$query = $db->query("SELECT * FROM " . DB_PREFIX . "product");

$queryCategory = $db->query("SELECT * FROM " . DB_PREFIX . "category");

// Start HTML output
echo '<ul>';
foreach ($query->rows as $product) {
    // Display each product
    echo '<li>' . $product['product_id'] . ' : '. $product['image'] .  ' : ' . $product['model'] . '</li>';
	
}
echo '</ul>';

echo '<ul>';
foreach ($queryCategory->rows as $category) {
    // Display each category
    echo '<li> category_id = ' . $category['category_id'] . '  parent_id = ' . $category['parent_id'] . '</li>';
	
}
echo '</ul>';

// Optionally close the database connection
//$db->close();
?>