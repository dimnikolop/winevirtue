<?php
namespace app\models;
use app\src\DbModel;
/**
 * 
 */
class Comment extends DbModel
{
	public $id;
	public $parent_id;
	public $post_id;
	public $name;
	public $email;
	public $message;
	public $posted_at;

	function __construct()
	{
		# code...
	}

	public function tableName()
	{
		return 'comments';
	}

	public function primaryKey()
	{
		return 'id';
	}
	
	// Validate form rules
	public function rules()
	{
		return [
			'name' => [self::RULE_REQUIRED],
			'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
			'message' => [self::RULE_REQUIRED]
		];
	}

	public function attributes()
	{
		return ['parent_id', 'post_id', 'name', 'email', 'message', 'posted_at'];
	}

	public function setPostedAt()
	{
		$this->posted_at = self::getCurrentTime();
	}

	/* * * * * * * * * * * * * * *
	* Insert comment to the database
	* * * * * * * * * * * * * * */
	public function addComment()
	{
		$this->setPostedAt();
	 	return $this->save();
	}

	/* * * * * * * * * * * * * * *
	* Load comments from database
	* * * * * * * * * * * * * * */
	public function fetchComments($post_id)
	{
		$output = "";
		// Count all comments for the current post
		$sql = "SELECT * FROM comments WHERE post_id = ?";
		$totalComments = $this->getRecordCount($sql, [$post_id]);

		if($totalComments > 0)
		{
			//Get comments of the current post that has no parents (parent_id=0)
			$parent_id = 0;
			$sql = "SELECT id, name, message, posted_at FROM comments WHERE parent_id = ? AND post_id = ? ORDER BY id DESC";
			$comments = $this->findAll($sql, 'obj', [$parent_id, $post_id]);

			$output = '<h3 class="mb-5 font-weight-normal"><!---Comments-->Σχόλια (' . $totalComments . ')</h3>
	            <ul class="comment-list">';

	        // Loop through each parent comment
			foreach($comments as $comment)
			{
		  		$output .= '
		  		<li class="comment">
		    		<div class="vcard">
		      			<img src="/images/profile.png" alt="Image placeholder">
		    		</div>
		    		<div class="comment-body">
		      			<h3>' . $comment->name . '</h3>
		      			<div class="meta">' . date("F j, Y", strtotime($comment->posted_at)) . ' at ' . date("H:i:s", strtotime($comment->posted_at)) . '</div>
		        		<p>'. nl2br($comment->message) .'</p>
		        		<p><a id="'. $comment->id .'" class="reply rounded"><i class="fa fa-reply"></i> Reply</a></p>
		    		</div>';

		  		// Get all replies for this comment
		  		$output .= $this->get_reply_comment($comment->id);
			}
			$output .= '</ul>';
		}
		return $output;
	}

	/* * * * * * * * * * * * * * *
	* Get reply comments from database
	* * * * * * * * * * * * * * */
	public function get_reply_comment($parent_id = 0)
	{
		$output = "";
	 	// Get all replies for current comment
	 	$sql = "SELECT id, name, message, posted_at FROM comments WHERE parent_id = ?";
	 	$comments = $this->findAll($sql, 'obj', [$parent_id]);
	 	
	 	if (!empty($comments))
	 	{
	 		$output .= '<ul class="children">';

	 		if (!is_array($comments)) {
				$comments = array($comments);
			}
	 		
		  	// Loop through each reply comment
		  	foreach($comments as $comment)
		  	{
		   		$output .= '
		    	<li class="comment">
		      		<div class="vcard">
		        			<img src="/images/profile.png" alt="Image placeholder">
		      		</div>
		      		<div class="comment-body">
		        		<h3>' . $comment->name . '</h3>
		        		<div class="meta">' . date("F j, Y", strtotime($comment->posted_at)) . ' at ' . date("H:i:s", strtotime($comment->posted_at)) . '</div>
		          		<p>'. nl2br($comment->message) .'</p>
		          		<p><a id="'. $comment->id .'" class="reply rounded"><i class="fa fa-reply"></i> Reply</a></p>
		      		</div>';

		   		// Get all replies for this comment
		   		$output .= $this->get_reply_comment($comment->id);
		  	}
	 		$output .= '</ul>';
	 	}
	 	return $output;
	}
}