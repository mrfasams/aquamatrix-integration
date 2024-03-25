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

$categoryList = "";
$productAttributesArray = array();
$seoUrlIdsArray = array();
$seoUrlProductsIdsArray = array();


		require_once('C:\Users\nikoleta.todorova\Documents\big-e-migration\htdocs\shop\config.php');

		// Load the database class
		require_once(DIR_SYSTEM . 'library\db.php');

		// Load the OpenCart autoloader
		require_once(DIR_SYSTEM . 'startup.php');

		// Load the registry class
		require_once(DIR_SYSTEM . 'engine/registry.php');

		// Database initialization
		$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);


initAquamatrixInformation($db);
saveProductsAndCategoryIds($jsonProducts,$json,0,$db);
deleteAllCategory($db);
deleteAllProducts($db);
deleteAllAttribute($db);
deleteAllSeoUrl($db);


/**
Insert all categories working version
**/
insertAllCategory($json,0,$db);

insertProducts($jsonProducts,$db);

/**
Disable empty category
**/

disableEmptyCategory($db);


function insertAllCategory($category,$parantId,$db)
{
	$idCat = $category->id;
	$nameCat = $category->name;
	
	if (property_exists($category, 'subcategories')) {
		$countCat = count($category->subcategories);
		
		for ($i = 0; $i < $countCat; $i++) {
			insertCategory($category->subcategories[$i],$parantId,$db);
		}
	}
}

function insertCategory($category,$parantId,$db) {
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
			
			$data['category_description'][2] = array(
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
		
		$data['top'] = 1;
		if($parantId == 2) {
			$data['top'] = 0;
			$data['parent_id'] = 0;
		}
		
		
		
		
		
		if($idCat != 2){
		 
	
		$db->query("INSERT INTO " . DB_PREFIX . "category SET category_id = '" .$idCat . "', parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

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
		
		$keywordSeoNoDot = str_replace('.', '', $nameCat);
		$keywordSeoCleaned = $idCat . "-" . preg_replace('/[^\p{L}\p{N}]+/u', '-', $keywordSeoNoDot);
		$data['category_seo_url'] = array(
			0 => array( // Store ID 0
				1 => $keywordSeoCleaned, // Language ID 1 (English)
				2 => $keywordSeoCleaned, // Language ID 2 (Bulgarian)
				)
		);
		
		if (isset($data['category_seo_url'])) {

						$db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '0', language_id = '1', query = 'category_id=" . (int)$idCat  . "', keyword = '" .$keywordSeoCleaned."-en'");
						$db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '0', language_id = '2', query = 'category_id=" . (int)$idCat  . "', keyword = '" .$keywordSeoCleaned."-bg'");
	
			
		}
		
		// Set which layout to use with this category
		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '0'");
			}
		}
		}
		if (property_exists($category, 'subcategories')) {
		$countCat = count($category->subcategories);
		for ($i = 0; $i < $countCat; $i++) {
			insertCategory($category->subcategories[$i],$idCat,$db);
			
	
		}
	}
}


function insertProduct($product,$db) { 


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
		$data['weight'] = 1.00000000;
		$data['weight_class_id'] = 1;
		$data['length'] = 25.00000000;
		$data['width'] = 25.00000000;
		$data['height'] = 25.00000000;
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
	
	$data['product_description'][2] = array(
				'name'             => $product->name,
				'meta_title'       => $product->name,
				'meta_description' => '',
				'meta_keyword'     => '',
				'description'      => $product->description,
				'tag'              => '',
				'language_id'      => 2,
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
					
			}
			$data['quantity'] = $countSumQuantity;	
		}
		
		
		
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
				$db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '0'");
				//$db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
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
		
		$data['product_option'] = array();
		if (property_exists($product, 'combinations')) { 
		
		$countQuantity = count($product->combinations);
		
		$data['product_option'][0]['type'] = 'select';
		$data['product_option'][0]['product_id'] =  $product->id;

		$data['product_option'][0]['required'] = 1;
				
			for ($i = 0; $i < $countQuantity; $i++) { 

				if (property_exists($product->combinations[$i], 'attributes')) { 
				$countAttributes = count($product->combinations[$i]->attributes);
				$attribute['type'] = 'select';
					for ($j = 0; $j < $countAttributes; $j++) { 
						
						$attribute['option_id'] = $product->combinations[$i]->attributes[$j]->id_attribute;
						$attribute['sort_order'] =0; 
						$attribute['option_description'][1] = array(
				             'name' => $product->combinations[$i]->attributes[$j]->attribute_name
							 
						);
						$attribute['option_description'][2] = array(
				             'name' => $product->combinations[$i]->attributes[$j]->attribute_name
						);
						$attribute['option_value'][0] = array(
						    'option_value_id' => $product->combinations[$i]->attributes[$j]->id_attribute_value
						);
						
						$attribute['option_value'][0]['option_value_description'][1] = array(
				             'name' => $product->combinations[$i]->attributes[$j]->attribute_value
							 
						);
						
						$attribute['option_value'][0]['option_value_description'][2] = array(
				             'name' => $product->combinations[$i]->attributes[$j]->attribute_value
						);
	
						$option_id = addOption($attribute,$db);
						
						$data['product_option'][0]['option_id'] = $option_id ;
						$data['product_option'][0]['product_option_value'][$i] = array(
						'option_value_id'=>$product->combinations[$i]->attributes[0]->id_attribute_value,
						    'quantity' => (int)$product->combinations[$i]->quantity,
							'subtract' => (int)1,
							'price' => (float)0.0000,
							'price_prefix' => '-',
							'weight' => (float)1.00000000,
							'points_prefix' => '+',
							'points' => (int)0,
							'weight_prefix' => '+',
							
						);					
					}
		
				}
			}
		}
		
		
		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . 
							(int)$product_option_id . "', product_id = '" . 
							(int)$product_id . "', option_id = '" .
							(int)$product_option['option_id'] . "', option_value_id = '" . 
							(int)$product_option_value['option_value_id'] . "', quantity = '" . 
							(int)$product_option_value['quantity'] . "', subtract = '" . 
							(int)$product_option_value['subtract'] . "', price = '" . 
							(float)$product_option_value['price'] . "', price_prefix = '" . 
							$db->escape($product_option_value['price_prefix']) . "', points = '" . 
							(int)$product_option_value['points'] . "', points_prefix = '" . 
							$db->escape($product_option_value['points_prefix']) . "', weight = '" . 
							(float)$product_option_value['weight'] . "', weight_prefix = '" .
							$db->escape($product_option_value['weight_prefix']) . "'");
						}
					}
				} else {
					$db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}
		
	  if (property_exists($product, 'features')) { 
		
		$countFeatures = count($product->features);
		$data['product_attribute'] = array();
		for ($i = 0; $i < $countFeatures; $i++) { 
		
		$attribute['attribute_id'] = $product->features[$i]->id_feature;
		$attribute['attribute_group_id'] = 11;
		
			
		$attribute['name'] =$product->features[$i]->feature_name;
		if($attribute['attribute_id'] != 0)
		 addAttribute($attribute,$db);
		
		$data['product_attribute'][$i]['attribute_id'] = $product->features[$i]->id_feature;
		$data['product_attribute'][$i]['product_attribute_description'][1]['text'] = $product->features[$i]->feature_value;
		$data['product_attribute'][$i]['product_attribute_description'][2]['text'] = $product->features[$i]->feature_value;
		}
	  }
		
		
		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}
		
		
		$keywordSeoNoDot = str_replace('.', '', $product->name);
		$keywordSeoCleaned = $product->id . "-" . preg_replace('/[^\p{L}\p{N}]+/u', '-', $keywordSeoNoDot);
		$data['product_seo_url'] = array(
			0 => array( // Store ID 0
				1 => $keywordSeoCleaned, // Language ID 1 (English)
				2 => $keywordSeoCleaned, // Language ID 2 (Bulgarian)
				)
		);
		
		if (isset($data['product_seo_url'])) {
	
			$db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '0', language_id = '1', query = 'product_id=" . (int)$product->id  . "', keyword = '" .$keywordSeoCleaned."-en'");
			$db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '0', language_id = '2', query = 'product_id=" . (int)$product->id  . "', keyword = '" .$keywordSeoCleaned."-bg'");

		}
		
		
}

 function insertProducts($products,$db){
	 
		addAttributeGroup($db);	
		$countCat = count($products);

		for ($i = 0; $i < $countCat; $i++) {
			$idProduct = $products[$i]->id;
			$nameProduct = $products[$i]->name;
			$descriptionProduct = $products[$i]->description;
			 insertProduct($products[$i],$db);
		}
	
}

	 function addOption($data,$db) {
		 
		 $data['type'] = 'select';
		  $qu = $db->query("SELECT * FROM " . DB_PREFIX . "option where option_id = '".$data['option_id'].  "' ");
		   
		  if(!$qu->row){
			$db->query("INSERT INTO " . DB_PREFIX . "option SET option_id ='" . (int)$data['option_id'] . "' ,type = '" . $db->escape($data['type']) . "', sort_order = '" . (int)$data['sort_order'] . "'");
			} 
			$option_id = $data['option_id'];
			$queryDesc = $db->query("SELECT * FROM " . DB_PREFIX . "option_description where option_id = '".$data['option_id'].  "' ");
			if(!$queryDesc->row) {
				foreach ($data['option_description'] as $language_id => $value) {
					$db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$language_id . "', name = '" . $db->escape($value['name']) . "'");
				}
			}
			if (isset($data['option_value'])) {
				foreach ($data['option_value'] as $option_value) {
						$queryOpionValue = $db->query("SELECT * FROM " . DB_PREFIX . "option_value where option_value_id = '".$option_value['option_value_id'].  "' ");
						if(!$queryOpionValue->row) {
						$db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_value_id ='" .(int)$option_value['option_value_id'] . "',option_id = '" . (int)$option_id . "', sort_order = '0'");
						}
					$option_value_id = $option_value['option_value_id'];
					foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
						$queryValueDescription = $db->query("SELECT * FROM " . DB_PREFIX . "option_value_description where option_value_id ='" . (int)$option_value_id . "' and language_id ='" . (int)$language_id . "' and option_id = '" . (int)$option_id . "'");
						if(!$queryValueDescription->row) {
							$db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language_id . "', option_id = '" . (int)$option_id . "', name = '" . $db->escape($option_value_description['name']) . "'");
						}
					}
				}
			}

			return $option_id;
		}
	
	 function addAttribute($data,$db) {
		  
		   //echo "<br>";
		$queryAttribute = $db->query("SELECT * FROM " . DB_PREFIX . "attribute where attribute_id = '" . (int)$data['attribute_id'] . "'  and attribute_group_id = '" . (int)$data['attribute_group_id'] . "' ");
		 if(!$queryAttribute->row) {
		  $db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_id = '" . (int)$data['attribute_id'] . "' ,attribute_group_id = '" . (int)$data['attribute_group_id'] . "', sort_order = '0'");
		 }
		
		$queryAttributeDescription = $db->query("SELECT * FROM " . DB_PREFIX . "attribute_description where attribute_id = '" . (int)$data['attribute_id'] . "' ");
		 if(!$queryAttributeDescription->row) {
			 
			 $db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$data['attribute_id']. "', language_id = '1', name = '" . $data['name'] . "'");
			 $db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$data['attribute_id']. "', language_id = '2', name = '" . $data['name'] . "'");
			
		 }

	}
	//static insert
	 function addAttributeGroup($db) {
		 $queryAttributeGroup = $db->query("SELECT * FROM " . DB_PREFIX . "attribute_group where attribute_group_id = '11' ");
		 if(!$queryAttributeGroup->row) {
			$db->query("INSERT INTO " . DB_PREFIX . "attribute_group (`attribute_group_id`, `sort_order`) VALUES (11, 1)");
		 }
		
		$queryAttributeGroupDescription = $db->query("SELECT * FROM " . DB_PREFIX . "attribute_group_description where attribute_group_id = '11' ");
		 if(!$queryAttributeGroupDescription->row) {
			$db->query("INSERT INTO `" . DB_PREFIX . "attribute_group_description` (`attribute_group_id`, `language_id`, `name`) VALUES (11, 1, 'Information'),(11, 2, 'Информация');");
		 }
	}
	

/**
Delete all previous products id
**/

 function deleteAllProducts($db){
	 
	$products_ids = $db->query("SELECT products_ids,MAX(date_added) as max_date FROM `aquamatrix_information` GROUP BY products_ids;");
	
	foreach ($products_ids->rows as $result) {
		$productList= $result['products_ids'];
			
	}
	$productListIds = rtrim($productList, ',');
	$db->query("DELETE FROM " . DB_PREFIX . "product_to_store where product_id in (" .$productListIds .");");
	$db->query("DELETE FROM " . DB_PREFIX . "product_description where product_id in (" .$productListIds .");");
	$db->query("DELETE FROM " . DB_PREFIX . "product where product_id in (" .$productListIds .");");
	$db->query("DELETE FROM " . DB_PREFIX . "product_to_layout where product_id in (" .$productListIds .");");
	$db->query("DELETE FROM " . DB_PREFIX . "product_to_category where product_id in (" .$productListIds .");");
	$db->query("DELETE FROM " . DB_PREFIX . "product_image where product_id in (" .$productListIds .");");
	$db->query("DELETE FROM " . DB_PREFIX . "product_special where product_id in (" .$productListIds .");");
	$db->query("DELETE FROM " . DB_PREFIX . "product_option where product_id in (" .$productListIds .");");
	$db->query("DELETE FROM " . DB_PREFIX . "product_option_value where product_id in (" .$productListIds .");");
	$db->query("DELETE FROM " . DB_PREFIX . "product_attribute where product_id in (" .$productListIds .")");
	
}
/**
Save products id before insert them
**/
 function saveProductsAndCategoryIds($products,$category,$parantId,$db){
		$countProd = count($products);
		$productAttributesIds = '';
		$productList = "";
		
		for ($i = 0; $i < $countProd; $i++) {
			$idProduct = $products[$i]->id;
			
			findAllProductSeo($products[$i],$db);
			
			if((int)($countProd)  - 1 == (int)$i) {
				$productList = $productList .  $products[$i]->id ;
			} else {
			$productList = $productList .  $products[$i]->id . ",";
			}
			
			findAllAttributeArray($products[$i]);
			
			
		}
		
		if (property_exists($category, 'subcategories')) {
			$countCat = count($category->subcategories);
			global  $categoryList;
			$categoryList = $parantId;
			$idCat = $category->id;
			$list =  $idCat;
			
			
			for ($i = 0; $i < $countCat; $i++) {
				findCategory($category->subcategories[$i],$parantId,$list,$db);
				
			}
	}
		$productListIds = rtrim($productList, ',');
		$temp = rtrim($productAttributesIds, ','); 
		
		global $productAttributesArray;

		$concatenatedString = concatAttributesToString($productAttributesArray);
		echo $concatenatedString;
		
		global $seoUrlIdsArray;
		global $seoUrlProductsIdsArray;
		$seoUrlIdsArrayStrings;
		$seoUrlProductsIdsArrayStrings;
		// Check if the array is populated
		if (!empty($seoUrlIdsArray)) {
			// Concatenating the values into a string
			$seoUrlIdsArrayStrings = concatAttributesToString($seoUrlIdsArray);

			// Printing the concatenated string
			echo $seoUrlIdsArrayStrings;
		} else {
			echo "<br>";
			echo "Array category is empty";
		}
		
		// Check if the array is populated
		if (!empty($seoUrlProductsIdsArray)) {
			// Concatenating the values into a string
			$seoUrlProductsIdsArrayStrings = concatAttributesToString($seoUrlProductsIdsArray);

			// Printing the concatenated string
			echo $seoUrlProductsIdsArrayStrings;
		} else {
			echo "<br>";
			echo "Array products is empty";
		}
		
	    $db->query("INSERT INTO `aquamatrix_information` (`category_ids`, `products_ids`, `date_added`, `attribute_ids`,`seoCatergory_ids`,`seoProduct_ids`) VALUES ('".$categoryList."', '".$productListIds."', NOW(), '".$concatenatedString."' , '".$seoUrlIdsArrayStrings."' , '".$seoUrlProductsIdsArrayStrings."');");
		
 }
 
 function concatAttributesToString($word) {
	 
	 $filteredArray = array_filter(array_keys($word));

	// Concatenating the values into a string
	$concatenatedString = implode(',', $filteredArray);
    // Extract keys from the associative array and join them into a comma-separated string
    return $concatenatedString;
}
 
 function findCategory($category,$parantId,$list,$db) {
	 $idCat = $category->id;
	global $categoryList;
	$categoryList = $categoryList . ",". $idCat;
	 if (property_exists($category, 'subcategories')) {
		$countCat = count($category->subcategories);
		for ($i = 0; $i < $countCat; $i++) {
			findCategory($category->subcategories[$i],$idCat,$list,$db);
			
			findAllCategorySeo($category,$db);
			
		}
	}
 }


/**
Delete all previous Category id
**/

function deleteAllCategory($db) {
	$category_ids = $db->query("SELECT category_ids,MAX(date_added) as max_date FROM `aquamatrix_information` GROUP BY category_ids;");
	
	foreach ($category_ids->rows as $result) {
		$categoryListIds = $result['category_ids'];
			
	}
	$db->query("DELETE FROM " . DB_PREFIX . "category_to_store where category_id in (" .$categoryListIds .")");
	$db->query("DELETE FROM " . DB_PREFIX . "category_description where category_id in (" .$categoryListIds .")");
	$db->query("DELETE FROM " . DB_PREFIX . "category where category_id in (" .$categoryListIds .")");
	$db->query("DELETE FROM " . DB_PREFIX . "category_to_layout where category_id in (" .$categoryListIds .")");
	$db->query("DELETE FROM " . DB_PREFIX . "category_path where category_id in (" .$categoryListIds .")");
	
}
 
function findAllAttributeArray($product){
    global $productAttributesArray;
    if (property_exists($product, 'features')) { 
        $countFeatures = count($product->features);
        for ($i = 0; $i < $countFeatures; $i++) { 
            $id_feature = $product->features[$i]->id_feature;
            if (!isset($productAttributesArray[$id_feature])) {
                $productAttributesArray[$id_feature] = true; // Or any value, as we're only using keys
            }
        }
    }
}


function findAllCategorySeo($category,$db){
	
	$idCat = $category->id;
	$resultSeo = $db->query("SELECT * FROM " . DB_PREFIX . "seo_url where query = 'category_id=" . (int)$idCat  . "'");
											
	foreach ($resultSeo->rows as $result) { 
		$seo_url_id = $result['seo_url_id'];
		
		addSeoCategorysArray($seo_url_id);
	}

}

function findAllProductSeo($product,$db){
	
	$idProduct = $product->id;
	$resultSeo = $db->query("SELECT * FROM " . DB_PREFIX . "seo_url where query = 'product_id=" . (int)$idProduct  . "'");
											
	foreach ($resultSeo->rows as $result) { 
		$seo_url_id = $result['seo_url_id'];
		
		addSeoProductsArray($seo_url_id);
	}

}

function addSeoCategorysArray($seoCat) {
    global $seoUrlIdsArray;

    if (!isset($seoUrlIdsArray[$seoCat])) {
        $seoUrlIdsArray[$seoCat] = true; 
    }
}

function addSeoProductsArray($seoProd) {
    global $seoUrlProductsIdsArray;

    if (!isset($seoUrlProductsIdsArray[$seoProd])) {
        $seoUrlProductsIdsArray[$seoProd] = true; 
    }
}

 
 /**
Delete all previous Category id
**/

function deleteAllAttribute($db) {
	$attribute_ids = $db->query("SELECT attribute_ids,MAX(date_added) as max_date FROM `aquamatrix_information` GROUP BY attribute_ids;");
	
	foreach ($attribute_ids->rows as $result) {
		$attributeListIds = $result['attribute_ids'];
			
	}
	
	$db->query("DELETE FROM " . DB_PREFIX . "attribute where attribute_id in (" .$attributeListIds .")");
	$db->query("DELETE FROM " . DB_PREFIX . "attribute_description where attribute_id in (" .$attributeListIds .")");
	
}

 /**
Delete all previous Category Seo id
**/

function deleteAllSeoUrl($db) {
	$seoCatergory_ids = $db->query("SELECT seoCatergory_ids,MAX(date_added) as max_date FROM `aquamatrix_information` GROUP BY seoCatergory_ids;");
	$seoCatergoryIds;
	foreach ($seoCatergory_ids->rows as $result) {
		$seoCatergoryIds = $result['seoCatergory_ids'];
			
	}
	
	$db->query("DELETE FROM " . DB_PREFIX . "seo_url where seo_url_id in (" .$seoCatergoryIds .")");
	
	
}
 
function initAquamatrixInformation($db) {

	$db->query("
	CREATE TABLE IF NOT EXISTS `aquamatrix_information` (
	`category_ids` varchar(50000) NOT NULL,
	`products_ids` varchar(50000) NOT NULL,
	`attribute_ids` varchar(50000) NOT NULL,
	`seoCatergory_ids` varchar(50000) NOT NULL,
	`seoProduct_ids` varchar(50000) NOT NULL,
	`date_added` datetime NOT NULL
 	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
	

}

function disableEmptyCategory($db) {
	$db->query("update oc_category c set status = 0  where category_id not in (SELECT category_id FROM `oc_product_to_category`) and category_id not in (select ca.parent_id from oc_category ca where c.category_id = ca.parent_id);");
	//not sure if is correct we will see
	$db->query("update oc_category c set status = 0  where category_id not in (SELECT category_id FROM `oc_product_to_category`) and category_id not in (select ca.parent_id from oc_category ca where c.category_id = ca.parent_id and status = 1) and c.parent_id = 0; ");
}
?>
