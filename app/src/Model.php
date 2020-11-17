<?php
namespace app\src;
/**
 * 
 */
abstract class Model
{
	const RULE_REQUIRED = 'required';
	const RULE_EMAIL = 'email';
	const RULE_MIN = 'min';
	const RULE_MAX = 'max';
	const RULE_MIN_LENGTH = 'min_length';
	const RULE_MAX_LENGTH = 'max_length';
	const RULE_MATCH = 'match';
	const RULE_UNIQUE = 'unique';
	public $errors = [];

	
	function __construct()
	{
		# code...
	}

	abstract public function rules();

	public function loadData($data)
	{
		foreach ($data as $key => $value) {
			if(property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}
	
	public function validate($update = false)
	{
		foreach ($this->rules() as $attribute => $rules) {
			$value = $this->{$attribute};
			foreach ($rules as $rule) {
				$ruleName = $rule;
				if(!is_string($ruleName)) {
					$ruleName = $rule[0];
				}
				if($ruleName === self::RULE_REQUIRED && !$value) {
					$this->addErrorForRule($attribute, self::RULE_REQUIRED);
				}
				if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
					$this->addErrorForRule($attribute, self::RULE_EMAIL);
				}
				if ($ruleName === self::RULE_MIN_LENGTH && strlen($value) < $rule['min']) {
					$this->addErrorForRule($attribute, self::RULE_MIN_LENGTH, $rule);
				}
				if ($ruleName === self::RULE_MAX_LENGTH && strlen($value) > $rule['max']) {
					$this->addErrorForRule($attribute, self::RULE_MAX_LENGTH, $rule);
				}
				if ($ruleName === self::RULE_MIN && $value < $rule['min']) {
					$this->addErrorForRule($attribute, self::RULE_MIN, $rule);
				}
				if ($ruleName === self::RULE_MAX && $value > $rule['max']) {
					$this->addErrorForRule($attribute, self::RULE_MAX, $rule);
				}
				if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}) {
					$this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
				}
				if (!$update) {
					if ($ruleName === self::RULE_UNIQUE && !empty($value)) {
						$className = $rule['class'];
						$tableName = $className::tableName();
						$sql = "SELECT * FROM $tableName WHERE " . $attribute . " = :". $attribute;
						$stmt = Application::$app->dbc->prepare($sql);
						$stmt->execute(["$attribute" => $value]);
						$record = $stmt->fetch();
						if($record) {
							$this->addErrorForRule($attribute, self::RULE_UNIQUE, ['field' => $attribute]);
						}
					}
				}
				
			}
		}
		return empty($this->errors);
	}

	private function addErrorForRule($attribute, $rule, $params = [])
	{
		$message = isset($this->errorMessages()[$rule]) ? $this->errorMessages()[$rule] : '';
		foreach ($params as $key => $value) {
			$message = str_replace("{{$key}}", $value, $message);
		}
		$this->errors[$attribute][] = $message;
	}

	protected function addError($attribute, $message)
	{
		$this->errors[$attribute][] = $message;
	}

	protected function errorMessages()
	{
		return [
			self::RULE_REQUIRED => 'Αυτό το πεδίο είναι υποχρεωτικό',
			self::RULE_EMAIL => 'Η διεύθυνση email δεν είναι έγκυρη',
			self::RULE_MIN => 'Min value of this field must be {min}',
			self::RULE_MAX => 'Max value of this field must be {max}',
			self::RULE_MIN_LENGTH => 'Min length of this field must be {min}',
			self::RULE_MAX_LENGTH => 'Max length of this field must be {max}',
			self::RULE_MATCH => 'This field must be the same as {match}',
			self::RULE_UNIQUE => 'Record with this {field} already exists'	
		];
	}

	public function hasError($attribute)
	{
		return isset($this->errors[$attribute]) ? $this->errors[$attribute] : false;
	}

	public function getFirstError($attribute)
	{
		return isset($this->errors[$attribute][0]) ? $this->errors[$attribute][0] : false;
	}

	protected function getCurrentTime()
	{
		// Set timezone
		date_default_timezone_set('Europe/Athens');
		return date("Y-m-d H:i:s");
	}

	// Upload image file in upload directory and store image path to DB
	protected function uploadImage($imageAttr, $imageFile, $uploadDir)
	{
		
		// Get image name
		$fileName = $imageFile['name'];
		
		// Image width, height and type 
	    $sourceProperties = getimagesize($imageFile['tmp_name']);
		$resizeFileName = time();
		
		//Get file extension
		$fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
		
	    $uploadImageType = $sourceProperties[2];
		$sourceImageWidth = $sourceProperties[0];
		$sourceImageHeight = $sourceProperties[1];

		switch ($imageAttr) {
			case 'image':
				$resizeImageWidth = 1200;
				$resizeImageHeight = 800;
				// Generate unique filename to save in server
				$this->{$imageAttr} = "thump_".$resizeFileName.'.'. $fileExt;
				break;
			case 'imageh':
				$resizeImageWidth = 1600;
		  		$resizeImageHeight = 1200;
		  		// Generate unique filename to save in server
				$this->{$imageAttr} = "thump_H".$resizeFileName.'.'. $fileExt;
				break;
			case 'imagev':
				$resizeImageWidth = 800;
		  		$resizeImageHeight = 1200;
		  		// Generate unique filename to save in server
				$this->{$imageAttr} = "thump_V".$resizeFileName.'.'. $fileExt;
				break;
			default:
				# code...
				break;
		}
			
		switch ($uploadImageType) {
		  	case IMAGETYPE_JPEG:
		  		$src_image = imagecreatefromjpeg($imageFile['tmp_name']);
		  		$dst_image = $this->resizeImage($src_image, $sourceImageWidth, $sourceImageHeight, $resizeImageWidth, $resizeImageHeight);
		  		if(!imagejpeg($dst_image, $uploadDir . $this->{$imageAttr})) {
		  			$this->addError($imageAttr, 'Failed to upload image. Please check file settings for your server');
		  		}
		  		break;
		  	case IMAGETYPE_GIF:
		  		$src_image = imagecreatefromgif($imageFile['tmp_name']);
		  		$dst_image = $this->resizeImage($src_image, $sourceImageWidth, $sourceImageHeight, $resizeImageWidth, $resizeImageHeight);
		  		if(!imagegif($dst_image, $uploadDir . $this->{$imageAttr})) {
		  			$this->addError($imageAttr, 'Failed to upload image. Please check file settings for your server');
		  		}
		  		break;
		  	case IMAGETYPE_PNG:
		  		$src_image = imagecreatefrompng($imageFile['tmp_name']);
		  		$dst_image = $this->resizeImage($src_image, $sourceImageWidth, $sourceImageHeight, $resizeImageWidth, $resizeImageHeight);
		  		if(!imagepng($dst_image, $uploadDir . $this->{$imageAttr})) {
		  			$this->addError($imageAttr, 'Failed to upload image. Please check file settings for your server');
		  		}
		  		break;
		  	default:
		  		$this->addError($imageAttr, 'This image: '. $fileName . ' has not the allowed extension type.');
		  		break;
		}

		return empty($this->errors);	
	}

	// Resize Image
	protected function resizeImage($src_image, $src_width, $src_height, $resizeWidth, $resizeHeight)
	{
		$dst_image = imagecreatetruecolor($resizeWidth, $resizeHeight);
		imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $src_width, $src_height);
		return $dst_image;
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * *
	* Returns the author of a post or a wine review
	* * * * * * * * * * * * * * * * * * * * * * * * * */
	protected function getAuthorById($user_id)
	{
		// Get single user-author
		$sql = "SELECT fullname FROM users WHERE id = ?";
		$author = \app\models\User::findOne(['id' => $user_id], 'assoc', ['fullname']);
		return $author['fullname'];
	}
}