<?php



//set map api url
$url = "https://aquamatrix.bg/aquamatrix_categories_feed.json";

//call api
$json = file_get_contents($url);
$json = json_decode($json);


//set url json for products
$urlProducts = "http://aquamatrix.bg/aquamatrix_products_feed.json";

//call api
$jsonProducts = file_get_contents($urlProducts);
$jsonProducts = json_decode($jsonProducts);




function printCategories($category,$parantId)
{
	$idCat = $category->id;
	$nameCat = $category->name;
	
			echo '<li> idCat             = ' . $idCat . '</li>';
			echo '<li> nameCat       = ' . $nameCat. '</li>';
	if (property_exists($category, 'subcategories')) {
		$countCat = count($category->subcategories);
		for ($i = 0; $i < $countCat; $i++) {
			insertCategory($category->subcategories[$i],$parantId);
			
	
		}
	}
}

function insertAllCategory($category,$parantId)
{
	$idCat = $category->id;
	$nameCat = $category->name;
	
	if (property_exists($category, 'subcategories')) {
		$countCat = count($category->subcategories);
		
		echo '<li> idCat             = ' . $idCat . '</li>  $countCat'  . $countCat .  '';
			echo '<li> nameCat       = ' . $nameCat. '</li>';
		for ($i = 0; $i < $countCat; $i++) {
			insertCategory($category->subcategories[$i],$parantId);

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
			
		
		
		foreach ($data['category_store'] as $result) {
			echo '<li> category_id             = ' . $result['category_id'] . '</li>';
			echo '<li> store_id       = ' . $result['store_id'] . '</li>';
			
		}
		
		
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
		if (property_exists($category, 'subcategories')) {
		$countCat = count($category->subcategories);
		for ($i = 0; $i < $countCat; $i++) {
			insertCategory($category->subcategories[$i],$idCat);
			
	
		}
	}
}


//printCategories($json,0);
//insert all categories working version
//insertAllCategory($json,0);

//printProducts($jsonProducts);
insertProducts($jsonProducts);


 function printProducts($products){
	
	$countCat = count($products);
		for ($i = 0; $i < $countCat; $i++) {
			$idProduct = $products[$i]->id;
			$nameProduct = $products[$i]->name;
			$descriptionProduct = $products[$i]->description;
			$images = $products[$i]->images;
			

			 $countImg = count($images);
			
			 for ($j = 0; $j < $countImg; $j++) {
			// $image = $images[$j];
			 //$imageData = base64_encode(file_get_contents($image));
             //echo '<img src="data:image/jpeg;base64,'.$imageData.'">';
			 }
			 			 //echo "<br>";
	
		}
  
}

function insertProduct($product) { 


		
		$data['model'] = $product->name;
		$data['subtract'] = 1;
		$data['price'] = 0;
		$data['status'] = 1;
		$data['sort_order'] = 0;
		$data['quantity'] = 1;
		$data['minimum'] = 1;
		$data['stock_status_id'] = 6;
		$data['manufacturer_id'] = 0;
		$data['shipping'] = 1;
		$data['price'] = 0.0000;
		$data['points'] = 0;
		$data['weight'] = 0.00000000;
		$data['weight_class_id'] = 1;
		$data['length'] = 0.00000000;
		$data['width'] = 0.00000000;
		$data['height'] = 0.00000000;
		$data['length_class_id'] = 1;
		$data['tax_class_id'] = 0;
		$data['tag'] = 0;
		$data['viewed'] = 0;
		$data['image'] = '';
		if (property_exists($product, 'images')) {
		$data['image'] = $product->images[0];
		}
		
		$product_id = $product->id;
		

		$data['product_description'][1] = array(
				'name'             => $product->name,
				'meta_title'       => $product->name,
				'meta_description' => '',
				'meta_keyword'     => '',
				'description'      => $product->description,
				'tag'              => '',
				'language_id'      => 1,
			);
	
			
			
			$data['product_store'][0] = array(
				'product_id'    => $product->id,
				'store_id'       => 0
			);
			
			$data['product_layout'][0] = array(
				'product_id'    => $product->id,
				'store_id'       => 0,
				'layout_id'       => 0,
			);
			
			
		$data['product_category'] = array();
		$countCat = 0;
		if (property_exists($product, 'categories')) {
		$countCat = count($product->categories);
		for ($i = 0; $i < $countCat; $i++) {
			$data['product_category'][$i] = new stdClass();
			
			$data['product_category'][$i]->product_id = $product->id;
			$data['product_category'][$i]->category_id = $product->categories[$i];
			
		}
		}
		$countImages = 0;
		if (property_exists($product, 'images')) {
		$countImages = count($product->images);
		$data['product_image'] = array();
			for ($i = 0; $i < $countImages; $i++) {
			$data['product_image'][$i] = new stdClass();
			
			$data['product_image'][$i]->product_id = $product->id;
			$data['product_image'][$i]->image = $product->images[$i];
			$data['product_image'][$i]->sort_order = $i;
			
		 }
		}
		
		if (property_exists($product, 'combinations')) { 
		$data['price'] = $product->combinations[0]->price;
		$countSumQuantity = 0;
		$countQuantity = count($product->combinations);
		
			
				for ($i = 0; $i < $countQuantity; $i++) { 
					$countSumQuantity = $countSumQuantity + $product->combinations[$i]->quantity;
					 echo 'id : ' .$product->combinations[$i]->quantity. ' ';
					 echo '</br>';
				}
			$data['quantity'] = $countSumQuantity;
			
			
		}
		
		require_once('C:\Users\nikoleta.todorova\Documents\big-e-migration\htdocs\shop\config.php');

		// Load the database class
		require_once(DIR_SYSTEM . 'library\db.php');

		// Load the OpenCart autoloader
		require_once(DIR_SYSTEM . 'startup.php');

		// Load the registry class
		require_once(DIR_SYSTEM . 'engine/registry.php');

		// Database initialization
		$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
		
		$db->query("INSERT INTO " . DB_PREFIX . "product SET  product_id = '" . (int)$product_id . "', model = '" . $db->escape($data['model']) . "',  quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = NOW(), manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");
		
		foreach ($data['product_description'] as $language_id => $value) {
					$db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $db->escape($value['name']) . "', description = '" . $db->escape($value['description']) . "', tag = '" . $db->escape($value['tag']) . "', meta_title = '" . $db->escape($value['meta_title']) . "', meta_description = '" . $db->escape($value['meta_description']) . "', meta_keyword = '" . $db->escape($value['meta_keyword']) . "'");
				}
		
		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '0'");
			}
		}
		
		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
		
		if (isset($data['product_category'])) {
			
			for ($i = 0; $i < $countCat; $i++) {
				$db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$data['product_category'][$i]->product_id . "', category_id = '" . (int)$data['product_category'][$i]->category_id . "'");
			}
		}
		
		if (isset($data['product_image'])) {
			for ($i = 1; $i < $countImages; $i++) {
				
				$db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$data['product_image'][$i]->product_id . "', image = '" . $db->escape($data['product_image'][$i]->image) . "', sort_order = '" . (int)$data['product_image'][$i]->sort_order . "'");
			}
		}
		
		if (isset($data['image'])) {
			if(! ($data['image'] == '')) {
				$db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $db->escape($data['image']) . "' WHERE product_id = '" . (int)$product->id . "'");
		}
		}
		//find price and discount/special
		
		if (property_exists($product, 'combinations')) {
		
			if (property_exists($product->combinations[0], 'sale_price')) { 
				$data['product_special'][0] = array(
					'product_id'         => $product->id,
					'customer_group_id'  => 1,
					'priority'           => 0,
					'price'              => $product->combinations[0]->sale_price,
					'date_start'         =>'0000-00-00',
					'date_end'           =>'0000-00-00',
				);
				
			}
		}
		
		
		
		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $db->escape($product_special['date_start']) . "', date_end = '" . $db->escape($product_special['date_end']) . "'");
			}
		}
		
}

 function insertProducts($products){
	
	$countCat = count($products);
		for ($i = 0; $i < $countCat; $i++) {
			$idProduct = $products[$i]->id;
			$nameProduct = $products[$i]->name;
			$descriptionProduct = $products[$i]->description;
			 insertProduct($products[$i]);
		}
}

?>