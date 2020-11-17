<?php
namespace app\models;
use app\src\Model;
/**
 * 
 */
class ContactForm extends Model
{	
	public $name = '';
	public $surname = '';
	public $company = '';
	public $email = '';
	public $subject = 'Wine Virtue - User Contact';
	public $message = '';
	const to = 'nikos_fra@winevirtue.gr';
	
	function __construct()
	{
		# code...
	}

	public function rules()
	{
		return [
			'name' => [self::RULE_REQUIRED],
			'surname' => [self::RULE_REQUIRED],
			'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
			'message' => [self::RULE_REQUIRED]
		];
	}

	/* * * * * * * * * * * * * * *
	* Sends a contact mail to admin
	* * * * * * * * * * * * * * */
	public function send()
	{
		if(!empty($this->company)) { 
			$this->subject .= " - Επωνυμία: " . $this->company;
		}
		
		$mailHeaders = "From: " . $this->surname . " " . $this->name . "<" . $this->email . ">\r\n";
		$mailHeaders .= "Content-Type: text/plain; charset=utf-8" . "\r\n";

		return mail(self::to, $this->subject, $this->message, $mailHeaders);
	}
}