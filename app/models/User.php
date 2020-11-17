<?php
namespace app\models;
use app\src\{DbModel, Application};
/**
 * 
 */
class User extends DbModel
{	
	public $id;
	public $fullname;
	public $email;
	public $password;
	public $confirmPassword;
	public $role;
	public $created_at;
	public static $isEditingUser = false;
	
	function __construct()
	{
		# code...
	}

	public function tableName()
	{
		return 'users';
	}

	public function primaryKey()
	{
		return 'id';
	}

	// Validate form rules
	public function rules()
	{
		return [
			'fullname' => [self::RULE_REQUIRED],
			'email' => [self::RULE_REQUIRED, self::RULE_EMAIL, [self::RULE_UNIQUE, 'class' => self::class]],
			'password' => [self::RULE_REQUIRED, [self::RULE_MIN_LENGTH, 'min' => 8], [self::RULE_MAX_LENGTH, 'max' => 24]],
			'confirmPassword' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'match' => 'password']],
			'role' => [self::RULE_REQUIRED]
		];
	}

	public function attributes()
	{
		return ['fullname', 'email', 'password', 'role', 'created_at'];
	}

	private function setcreatedAt()
	{
		$this->created_at = self::getCurrentTime();
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	* - Returns all registered users and their corresponding roles
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function getAdminUsers()
	{
		// Admin can view and edit all users
		// Authors can only view and edit their account
		if (Application::$app->user->role === "Admin") {
			$sql = "SELECT id, fullname, email, role FROM users";
		} elseif (Application::$app->user->role === "Author") {
			$id = Application::$app->user->id;
			$sql = "SELECT id, fullname, email, role FROM users WHERE id = '" . $id . "'";
		}
		return $this->findAll($sql, 'obj');
	}

	// Create new user
	public function create()
	{
		$this->created_at = $this->getCurrentTime();
		//encrypt the password before saving in the database
		$this->password = password_hash($this->password, PASSWORD_DEFAULT);
		return $this->save();
	}

	/* * * * * * * * * * * * * * * * * * * * *
	* - Takes user id as parameter
	* - Fetches the user from database
	* - Sets user fields on form for editing
	* * * * * * * * * * * * * * * * * * * * * */
	public function edit($userId)
	{
		self::$isEditingUser = true;
		return $this->findOne(['id' => $userId], 'obj', ['id', 'fullname', 'email', 'role']);
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
	* - Receives user request from form and updates in database
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function update()
	{
		$this->setcreatedAt();
		//encrypt the password before saving in the database
		$this->password = password_hash($this->password, PASSWORD_DEFAULT);
		return $this->save();	
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * *
	* - Takes user id as parameter 
	* -	Deletes user user
	* * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function delete($userId)
	{
		return parent::delete($userId);
	}
}