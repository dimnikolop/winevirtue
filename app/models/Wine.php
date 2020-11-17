<?php
namespace app\models;
use app\src\{DbModel, Application};
/**
 * 
 */
class Wine extends DbModel
{
	public $id;
	public $user_id;
	public $title;
	public $slug;
	public $imageh;
	public $imagev;
	public $description;
	public $rating;
	public $color;
	public $sweetness;
	public $producer;
	public $country;
	public $region;
	public $varieties;
	public $vintage;
	public $alcohol;
	public $consumption;
	public $food_pairing;
	public $published = 0;
	public $created_at;
	public $updated_at;
	public $author;
	public static $isEditingWine = false;
	
	function __construct()
	{
		# code...
	}

	public function tableName()
	{
		return 'wines';
	}

	public function primaryKey()
	{
		return 'id';
	}

	// Validate form - rules
	public function rules()
	{
		return [
			'title' => [self::RULE_REQUIRED, [self::RULE_UNIQUE, 'class' => self::class]],
			'description' => [self::RULE_REQUIRED],
			'rating' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 0], [self::RULE_MAX, 'max' => 99]],
			'color' => [self::RULE_REQUIRED],
			'sweetness' => [self::RULE_REQUIRED],
			'producer' => [self::RULE_REQUIRED],
			'country' => [self::RULE_REQUIRED],
			'region' => [self::RULE_REQUIRED],
			'varieties' => [self::RULE_REQUIRED],
			'vintage' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 1960], [self::RULE_MAX, 'max' => 2030]],
			'alcohol' => [self::RULE_REQUIRED],
			'consumption' => [self::RULE_REQUIRED],
			'food_pairing' => [self::RULE_REQUIRED]
		]; 
	}

	public function attributes()
	{	
		// If id is not set -> insert record
		if (!isset($this->id)) {
			return [
				'user_id', 'title', 'slug', 'imageh', 'imagev', 'description', 'rating', 'color', 'sweetness', 'producer', 'country', 'region', 'varieties', 'vintage', 'alcohol', 'consumption', 'food_pairing', 'published', 'created_at', 'updated_at'
			];
		}
		// Else id is set -> update record
		else {
			// If both horizontal and vertical featured image have been provided
			if (isset($this->imageh) && isset($this->imagev)) {
				return [
					'user_id', 'title', 'slug', 'imageh', 'imagev', 'description', 'rating', 'color', 'sweetness', 'producer', 'country', 'region', 'varieties', 'vintage', 'alcohol', 'consumption', 'food_pairing', 'published', 'updated_at'
				];
			}
			// If only horizontal featured image has been provided
			elseif (isset($this->imageh) && !isset($this->imagev)) {
				return [
					'user_id', 'title', 'slug', 'imageh', 'description', 'rating', 'color', 'sweetness', 'producer', 'country', 'region', 'varieties', 'vintage', 'alcohol', 'consumption', 'food_pairing', 'published', 'updated_at'
				];
			}
			// If only vertical featured image has been provided
			elseif (!isset($this->imageh) && isset($this->imagev)) {
				return [
					'user_id', 'title', 'slug', 'imagev', 'description', 'rating', 'color', 'sweetness', 'producer', 'country', 'region', 'varieties', 'vintage', 'alcohol', 'consumption', 'food_pairing', 'published', 'updated_at'
				];
			}
			// If neither horizontal/vertical featured image is provided
			else {
				return [
					'user_id', 'title', 'slug', 'description', 'rating', 'color', 'sweetness', 'producer', 'country', 'region', 'varieties', 'vintage', 'alcohol', 'consumption', 'food_pairing', 'published', 'updated_at'
				];
			}
		}
	}

	// Receives a string like 'Some Sample String'
	// and returns 'some-sample-string'
	public function setSlug($string)
	{
		$this->slug = preg_replace('/[^\p{Greek}A-Za-z0-9-]+/u', '-', mb_strtolower($string));
	}

	public function setCreatedAt()
	{
		$this->created_at = self::getCurrentTime();
	}

	public function setUpdatedAt()
	{
		$this->updated_at = self::getCurrentTime();
	}

	/* * * * * * * * * * * * * * * * *
	* Takes wine slug as parameter
	* Fetches the wine form DB 
	* * * * * * * * * * * * * * * * */
	public function getWine($slug)
	{
		$wine = $this->findOne(['slug' => $slug], 'obj');

		if($wine) {
			// get the author to which this wine review belongs
			$wine->author = $this->getAuthorById($wine->user_id);

			switch ($wine->country) {
				case 'ar':
					$wine->country = '<span class="flag-icon flag-icon-ar mr-2"></span>Αργεντινή';
					break;
				case 'au':
					$wine->country = '<span class="flag-icon flag-icon-au mr-2"></span>Aυστραλία';
					break;
				case 'at':
					$wine->country = '<span class="flag-icon flag-icon-at mr-2"></span>Αυστρία';
					break;
				case 'fr':
					$wine->country = '<span class="flag-icon flag-icon-fr mr-2"></span>Γαλλία';
					break;
				case 'de':
					$wine->country = '<span class="flag-icon flag-icon-de mr-2"></span>Γερμανία';
					break;
				case 'ch':
					$wine->country = '<span class="flag-icon flag-icon-ch mr-2"></span>Ελβετία';
					break;
				case 'gr':
					$wine->country = '<span class="flag-icon flag-icon-gr mr-2"></span>Ελλάδα';
					break;
				case 'gb':
					$wine->country = '<span class="flag-icon flag-icon-gb mr-2"></span>Ηνωμένο Βασίλειο';
					break;
				case 'us':
					$wine->country = '<span class="flag-icon flag-icon-us mr-2"></span>Η.Π.Α.';
					break;
				case 'es':
					$wine->country = '<span class="flag-icon flag-icon-es mr-2"></span>Ισπανία';
					break;
				case 'il':
					$wine->country = '<span class="flag-icon flag-icon-il mr-2"></span>Ισραήλ';
					break;
				case 'it':
					$wine->country = '<span class="flag-icon flag-icon-it mr-2"></span>Ιταλία';
					break;
				case 'ca':
					$wine->country = '<span class="flag-icon flag-icon-ca mr-2"></span>Καναδάς';
					break;
				case 'cy':
					$wine->country = '<span class="flag-icon flag-icon-cy mr-2"></span>Κύπρος';
					break;
				case 'lb':
					$wine->country = '<span class="flag-icon flag-icon-lb mr-2"></span>Λίβανος';
					break;
				case 'nz':
					$wine->country = '<span class="flag-icon flag-icon-nz mr-2"></span>Νεα Ζηλανδία';
					break;
				case 'za':
					$wine->country = '<span class="flag-icon flag-icon-za mr-2"></span>Νότιος Αφρική';
					break;
				case 'hu':
					$wine->country = '<span class="flag-icon flag-icon-hu mr-2"></span>Ουγγαρία';
					break;
				case 'uy':
					$wine->country = '<span class="flag-icon flag-icon-uy mr-2"></span>Ουρουγουάη';
					break;
				case 'pt':
					$wine->country = '<span class="flag-icon flag-icon-pt mr-2"></span>Πορτογαλία';
					break;
				case 'ro':
					$wine->country = '<span class="flag-icon flag-icon-ro mr-2"></span>Ρουμανία';
					break;
				case 'tr':
					$wine->country = '<span class="flag-icon flag-icon-tr mr-2"></span>Τουρκία';
					break;
				case 'cl':
					$wine->country = '<span class="flag-icon flag-icon-cl mr-2"></span>Χιλή';
					break;
				default:
					# code...
					break;
			}
			return $wine;
		}
		else {
			return null;
		}

	}

	// Fetch all wine-reviews from DB
	public function getAllWines()
	{
		$final_wines = [];

		// Admin can view all wine-reviews
		// Author can only view their wine-reviews
		if (Application::$app->user->role == "Admin") {
			$sql = "SELECT id, user_id, title, slug, rating, published, created_at FROM wines ORDER BY created_at";
		} elseif (Application::$app->user->role == "Author") {
			$user_id = Application::$app->user->id;
			$sql = "SELECT id, user_id, title, slug, rating, published, created_at FROM wines WHERE user_id = $user_id ORDER BY created_at";
		}
		
		$wines = $this->findAll($sql, 'obj');

		foreach ($wines as $wine) {
			$wine->author = $this->getAuthorById($wine->user_id);
			array_push($final_wines, $wine);
		}
		return $final_wines;
	}

	/* * * * * * * * * * * * * * *
	* Fetches all published wines
	* * * * * * * * * * * * * * */
	public function getPublishedWines($pn = 1)
	{
		$limit = 16;
		$startFrom = ($pn - 1) * $limit;

		$sql = "SELECT id FROM wines WHERE published = 1";
	    $total_records = $this->getRecordCount($sql);
		$totalPages = ceil($total_records / $limit);

		$sql = "SELECT title, slug, imageh, rating FROM wines WHERE published = 1 ORDER BY created_at DESC LIMIT $startFrom , $limit";
		$wines = $this->findAll($sql, 'obj');

		$pagination = Pagination::createLinks($pn, $totalPages);

		return ['wines' => $wines, 'pagination' => $pagination];
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * *
	* Returns all published wines according to filter values
	* * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function getFilteredWines($filterValues, $pn)
	{
		$limit = 16;
		$queryCondition = "";
		$startFrom = ($pn - 1) * $limit;
		$paramValue = [];

		if (isset($filterValues['search']) && !empty($filterValues['search'])) {
			$queryCondition .= " AND title LIKE CONCAT('%',?,'%')";
			$paramValue[] = $filterValues['search'];
		}

		if (isset($filterValues['variety']) && !empty($filterValues['variety'])) {
			// remove single quotes for execute of sql query
			$variety = '%'.str_replace("'", "", $filterValues['variety']).'%';
			$queryCondition .= " AND varieties LIKE ?";
			$paramValue[] = $variety;
		}

		if (isset($filterValues['color']) && !empty($filterValues['color'])) {
			$queryCondition .= " AND color LIKE CONCAT('%',?,'%')";
			$paramValue[] = $filterValues['color'];
		}

		if (isset($filterValues['sweetness']) && !empty($filterValues['sweetness'])) {
			$queryCondition .= " AND sweetness = ?";
			$paramValue[] = $filterValues['sweetness'];
		}

		if (isset($filterValues['rating']) && !empty($filterValues['rating'])) {
			$single_value = explode('-', $filterValues['rating']);
			$queryCondition .= " AND rating BETWEEN ? AND ?";
			$paramValue[] = $single_value[0];
			$paramValue[] = $single_value[1];
		}

		$sql = "SELECT id FROM wines WHERE published = 1" . $queryCondition;
		$total_records = $this->getRecordCount($sql, $paramValue);
	    $totalPages = ceil($total_records / $limit);

		$sql = "SELECT title, slug, imageh, rating FROM wines WHERE published = 1" . $queryCondition . " ORDER BY created_at DESC LIMIT $startFrom, $limit";
		
		// Fetch all wines as an object array called $wines
		$wines = $this->findAll($sql, 'obj', $paramValue);
		
		// Delete action and page elements from array, so that array(filterValues) has only filter values to use them in page links
		unset($filterValues['action'], $filterValues['page']);
		// Get page links given the current page, totalPages and filter data to attach to page link attribute
		$pagination = Pagination::createLinks($pn, $totalPages, $filterValues);
		
		return ['wines' => $wines, 'pagination' => $pagination, 'total_records' => $total_records];
	}

	// Create new wine -- Upload featured images
	public function create()
	{
		$uploadDir = 'uploads/images/wines/';  // Wines images file directory
		
		$this->user_id = Application::$app->user->id;

		if(isset($this->title)) {
			$this->title = $this->title . ', ' . $this->producer . ', ' . $this->vintage;
		}
		else {
			$this->title = $this->producer . ', ' . $this->vintage;
		}

		// create slug: if title is "The Storm Is Over", return "the-storm-is-over" as slug
		$this->setSlug($this->title);

		$this->setCreatedAt();
		$this->setUpdatedAt();

		// Check if image has been provided - return error if has not
		if(isset($_FILES['featured_imageH']) && $_FILES['featured_imageH']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['featured_imageH']['size'] > 1) {
			
			// Upload image on server
			$imageAttr = 'imageh';
			$imageFile = $_FILES['featured_imageH'];
			if (!$this->uploadImage($imageAttr, $imageFile, $uploadDir)) {
				return false;
			}
		}
		else {
			$this->addError('imageh', 'This field is required');
			return false;
		}

		// Check if image has been provided - return error if has not
		if (isset($_FILES['featured_imageV']) && $_FILES['featured_imageV']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['featured_imageV']['size'] > 1) {

			// Upload image on server
			$imageAttr = 'imagev';
			$imageFile = $_FILES['featured_imageV'];
			if (!$this->uploadImage($imageAttr, $imageFile, $uploadDir)) {
				return false;
			}
		}
		else {
			$this->addError('imagev', 'This field is required');
			return false;
		}
	
		// create wine if there are no errors in the form
		return $this->save();
	}

	/* * * * * * * * * * * * * * * * * * * * *
	* - Takes wine id as parameter
	* - Fetches the wine from database
	* - sets wine fields on form for editing
	* * * * * * * * * * * * * * * * * * * * * */
	public function edit($wineId)
	{
		self::$isEditingWine = true;
		
		$wine = $this->findOne(['id' => $wineId], 'obj', ['id', 'title', 'description', 'rating', 'color', 'sweetness', 'producer', 'country', 'region', 'varieties', 'vintage', 'alcohol', 'consumption', 'food_pairing', 'published']);
			
		// set form values on the form to be updated
		$first_arg = explode(',', $wine->title);
		$wine->title = $first_arg[0];
		return $wine;
	}

	// Takes hidden field wine id and updates wine to database 
	public function update()
	{
		$uploadDir = 'uploads/images/wines/';  // Wines images file directory

		$this->user_id = Application::$app->user->id;

		if(isset($this->title)) {
			$this->title = $this->title . ', ' . $this->producer . ', ' . $this->vintage;
		}
		else {
			$this->title = $this->producer . ', ' . $this->vintage;
		}

		// create slug: if title is "The Storm Is Over", return "the-storm-is-over" as slug
		$this->setSlug($this->title);

		$this->setUpdatedAt();
		
		// If new horizontal featured image has been provided
		if(isset($_FILES['featured_imageH']) && $_FILES['featured_imageH']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['featured_imageH']['size'] > 1) {

			// Delete from server previous horizontal featured image of wine
			$result = $this->findOne(['id' => $this->id], 'assoc', ['imageh']);
			$img_pathH = "uploads/images/wines/" . $result['imageh'];
			if (!file_exists($img_pathH) || !unlink($img_pathH)) {
				$this->addError('imageh', 'Couldn\'t find/delete image from server');
				return false;
	    	}
			
			// Upload image on server
			$imageAttr = 'imageh';
			$imageFile = $_FILES['featured_imageH'];
			if (!$this->uploadImage($imageAttr, $imageFile, $uploadDir)) {
				return false;
			}

		}

		// If new vertical featured image has been provided
		if(isset($_FILES['featured_imageV']) && $_FILES['featured_imageV']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['featured_imageV']['size'] > 1) {

			// Delete from server previous vertical featured image of wine
			$result = $this->findOne(['id' => $this->id], 'assoc', ['imagev']);
			$img_pathV = "uploads/images/wines/" . $result['imagev'];
			if (!file_exists($img_pathV) || !unlink($img_pathV)) {
				$this->addError('image', 'Couldn\'t find/delete image from server');
				return false;
			}

			// Upload image on server
			$imageAttr = 'imagev';
			$imageFile = $_FILES['featured_imageV'];
			if (!$this->uploadImage($imageAttr, $imageFile, $uploadDir)) {
				return false;
			}
		}
		// Save updated wine to db if there are no errors
		return $this->save();
	}

	// Delete blog wine review
	public function delete($wineId)
	{
		// Get images path from DB and delete featured images from server
		$result = $this->findOne(['id' => $wineId], 'assoc', ['imageh', 'imagev']);
		$img_pathH = "uploads/images/wines/" . $result['imageh'];
		$img_pathV = "uploads/images/wines/" . $result['imagev'];

		if(file_exists($img_pathH) && unlink($img_pathH) && file_exists($img_pathV) && unlink($img_pathV)) {
			return parent::delete($wineId);
		}
		else {
			return false;
		}
	}

	// Switch the status of wine review (published/unpublished)
	public function togglePublishWine($wineId)
	{
		$sql = "UPDATE wines SET published = !published WHERE id = ?";
		return $this->execute($sql, [$wineId]);
	}
}