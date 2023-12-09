<?php



//set map api url
$url = "https://aquamatrix.bg/aquamatrix_categories_feed.json";

//call api
$json = file_get_contents($url);
$json = json_decode($json);




function printCategories($category,$parantId)
{
	$idCat = $category->id;
	$nameCat = $category->name;
	
    echo "Id : " .$idCat. " Name  : " .  $nameCat. " Parent id =   : " .$parantId;
	echo "<br>";
	if (property_exists($category, 'subcategories')) {
		$countCat = count($category->subcategories);
		for ($i = 0; $i < $countCat; $i++) {
			//insertCategory($category->subcategories[$i],$parantId);
			printCategories($category->subcategories[$i],$idCat);
	
		}
	}
}

function insertCategory($category,$parantId) {
		$idCat = $category->id;
		$nameCat = $category->name;
	    $data['parent_id'] = $parantId;
		$category_id  = $idCat;
		$data['category_description'][0] = $nameCat;
		$data['category_description'][1] = $nameCat;
		$data['sort_order'] = 0;
		$data['column'] = 0;
		$data['status'] = 1;
		
		$data['category_description'] = array();
		

		$data['category_description'][1] = array(
				'name'             => $nameCat,
				'meta_title'       => $nameCat,
				'meta_description' => $nameCat,
				'meta_keyword'     => $nameCat,
				'description'      => $nameCat
			);
			
			$data['category_store'][0] = array(
				'category_id'    => $idCat,
				'store_id'       => 0
			);
			
			$data['category_layout'][0] = array(
				'category_id'    => $idCat,
				'store_id'       => 0,
				'layout_id'      => 0
			);
			
		
		/*
		foreach ($data['category_store'] as $result) {
			echo '<li> category_id             = ' . $result['category_id'] . '</li>';
			echo '<li> store_id       = ' . $result['store_id'] . '</li>';
			
		}
		*/
		
		require_once('C:\Users\nikoleta.todorova\Documents\big-e-migration\htdocs\shop\config.php');

		// Load the database class
		require_once(DIR_SYSTEM . 'library\db.php');

		// Load the OpenCart autoloader
		require_once(DIR_SYSTEM . 'startup.php');

		// Load the registry class
		require_once(DIR_SYSTEM . 'engine/registry.php');

		// Database initialization
		$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
		
		$data['top'] = 1;
		if($parantId == 2) {
			$data['top'] = 0;
			$data['parent_id'] = 0;
		}
		if($idCat != 2){
		 
		//$this->db = $db;
	
		$db->query("INSERT INTO " . DB_PREFIX . "category SET category_id = '" .$idCat . "', parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

		//$category_id = $this->db->getLastId();
		
		if (isset($data['image'])) {
			$db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			$db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $db->escape($value['name']) . "', description = '" . $db->escape($value['description']) . "', meta_title = '" . $db->escape($value['meta_title']) . "', meta_description = '" . $db->escape($value['meta_description']) . "', meta_keyword = '" . $db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				//$db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
				$db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '0'");
			}
		}
		
		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $db->escape($keyword) . "'");
					}
				}
			}
		}
		
		// Set which layout to use with this category
		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
		}
		
		//$this->cache->delete('category');
}


printCategories($json,0);

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
  //  echo '<li>' . $product['product_id'] . ' : '. $product['image'] .  ' : ' . $product['model'] . '</li>';
	
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