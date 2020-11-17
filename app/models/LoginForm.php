<?php
namespace app\models;
use app\src\{Model, Application};
/**
 * 
 */
class LoginForm extends Model
{
	public $email;
	public $password;
	
	function __construct()
	{
		# code...
	}

	public function rules()
	{
		return [
			'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
			'password' => [self::RULE_REQUIRED]
		];
	}

	public function login()
	{
		//$sql = "SELECT id, fullname, email, password, role FROM users WHERE email = ?";
		$user = User::findOne(["email" => $this->email], 'obj');
		if(!$user) {
			$this->addError('email', 'Δεν υπάρχει χρήστης με αυτό το email');
			return false;
		}
		// Check password - Compare password given with hash value of password stored in DB
		if (!password_verify($this->password, $user->password)) {
			$this->addError('password', 'Ο κωδικός πρόσβασης είναι λανθασμένος');
			return false;
		}
		// Login user
		return Application::$app->login($user);
	}
}