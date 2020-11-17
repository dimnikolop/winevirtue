<?php
namespace app\models;
use app\src\DbModel;
use app\src\Application;

/**
 * 
 */
class Article extends DbModel 
{
	public $id;
	public $user_id;
	public $title;
	public $slug;
	public $image;
	public $content;
	public $published = 0;
	public $created_at;
	public $updated_at;
	public $author;
	public static $isEditingPost = false;

	public function __construct()
	{
		# code...
	}

	public function tableName()
	{
		return 'posts';
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
			'content' => [self::RULE_REQUIRED]
		]; 
	}

	public function attributes()
	{
		// If id is not set -> insert record
		if (!isset($this->id)) {
			return ['user_id', 'title', 'slug', 'image', 'content', 'published', 'created_at', 'updated_at'];
		}
		// Else id is set -> update record
		else {
			// If featured image has been provided
			if (isset($this->image)) {
				return ['user_id', 'title', 'slug', 'image', 'content', 'published', 'updated_at'];
			}
			else {
				return ['user_id', 'title', 'slug', 'content', 'published', 'updated_at'];
			}
		}
	}

	// Receives a string like 'Some Sample String'
	// and returns 'some-sample-string'
	public function setSlug($string) {
		$this->slug = preg_replace('/[^\p{Greek}A-Za-z0-9-]+/u', '-', mb_strtolower($string));
	}

	public function setCreatedAt() {
		$this->created_at = self::getCurrentTime();
	}

	public function setUpdatedAt() {
		$this->updated_at = self::getCurrentTime();
	}

	/* * * * * * * * * * * * * * *
	* Takes post slug as parameter
	* Fetches the post form DB
	* * * * * * * * * * * * * * */
	public function getPost($slug)
	{
		$post = $this->findOne(["slug" => $slug], 'obj');

		if ($post) {
			// get the author to whom this post belongs
			$post->author = $this->getAuthorById($post->user_id);
			return $post;
		}
		else {
			return false;
		}
	}

	// Fetch all posts from database
	public function getAllPosts()
	{
		$final_posts = [];

		// Admin can view all posts
		// Author can only view their posts
		if (Application::$app->user->role == "Admin") {
			$sql = "SELECT id, user_id, title, slug, published FROM posts ORDER BY created_at";
		} elseif (Application::$app->user->role == "Author") {
			$user_id = Application::$app->user->id;
			$sql = "SELECT id, user_id, title, slug, published FROM posts WHERE user_id = $user_id ORDER BY created_at";
		}
		
		$posts = $this->findAll($sql, 'obj');
	
		foreach ($posts as $post) {
			$post->author = $this->getAuthorById($post->user_id);
			array_push($final_posts, $post);
		}
		return $final_posts;
	}

	// Fetch all published posts from database
	public function getPublishedPosts()
	{
		$sql = "SELECT * FROM posts WHERE published = 1 ORDER BY created_at DESC LIMIT 6";
		return $this->findAll($sql, 'obj');
	}

	// Fetch recent published posts from database
	public function getRecentPosts()
	{
		$sql = "SELECT * FROM posts WHERE published = 1 ORDER BY created_at DESC LIMIT 4";
		return $this->findAll($sql, 'obj');
	}

	// Create new article -- Upload featured image
	public function create()
	{
		$this->user_id = Application::$app->user->id;

		// create slug: if title is "The Storm Is Over", return "the-storm-is-over" as slug
		$this->setSlug($this->title);

		$this->setCreatedAt();
		$this->setUpdatedAt();

		// Check if image has been provided - return error if has not
		if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['featured_image']['size'] > 1) {

			// Upload image on server
			$imageAttr = 'image';
			$imageFile = $_FILES['featured_image'];
			$uploadDir = 'uploads/images/articles/';  // Articles images file directory
			if (!$this->uploadImage($imageAttr, $imageFile, $uploadDir)) {
				return false;
			}
		}
		else {
			$this->addError('image', 'This field is required');
			return false;
		}

		// create post if there are no errors in the form
		return $this->save();
	}

	/* * * * * * * * * * * * * * * * * * * * *
	* - Takes post id as parameter
	* - Fetches the post from database
	* - sets post fields on form for editing
	* * * * * * * * * * * * * * * * * * * * * */
	public function edit($postId)
	{
		self::$isEditingPost = true;
		return $this->findOne(["id" => $postId], 'obj', ['id', 'title', 'content', 'published']);
	}

	// Takes hidden field post id and updates post to database 
	public function update()
	{
		$this->user_id = Application::$app->user->id;

		// create slug: if title is "The Storm Is Over", return "the-storm-is-over" as slug
		$this->setSlug($this->title);

		$this->setUpdatedAt();

		// If new featured image has been provided
		if(isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['featured_image']['size'] > 1) {

			// Delete from server previous featured image of post
			$result = $this->findOne(['id' => $this->id], 'assoc', ['image']);
			$img_path = "uploads/images/articles/" . $result['image'];
			if (!file_exists($img_path) || !unlink($img_path)) {
				$this->addError('image', 'Couldn\'t find/delete image from server');
				return false;
			}

			// Upload image on server
			$imageAttr = 'image';
			$imageFile = $_FILES['featured_image'];
			$uploadDir = 'uploads/images/articles/';  // Articles images file directory
			if (!$this->uploadImage($imageAttr, $imageFile, $uploadDir)) {
				return false;
			}

		}
		// Save updated article to db if there are no errors
		return $this->save();
	}

	// Delete blog post
	public function delete($postId)
	{
		// Get image path from DB and delete featured image from server
		//$sql = "SELECT image FROM posts WHERE id = ?";
		$result = $this->findOne(['id' => $postId], 'assoc', ['image']);
		$img_path = "uploads/images/articles/" . $result['image'];

		if(file_exists($img_path) && unlink($img_path)) {
			return parent::delete($postId);
		}
		else {
			return false;
		}
	}

	// Switch the status of post (published/unpublished)
	public function togglePublishPost($postId)
	{
		$now = $this->getCurrentTime();

		$sql = "UPDATE posts SET published = !published, created_at = ?, updated_at = ? WHERE id = ?";
		return $this->execute($sql, [$now, $now, $postId]);
	}

	public function loadMorePosts($aDate)
	{
		$published = 1;
		$output = '';
		// Count all records except already displayed
		$sql = "SELECT id FROM posts WHERE published = ? AND created_at < ? ORDER BY created_at DESC";
		$totalRowCount = $this->getRecordCount($sql, [$published, $aDate]);
		
		// Limit of rows
		$showlimit = 2;

		// Get records from the database
		$sql = "SELECT title, slug, image, content, created_at FROM posts WHERE published = ? AND created_at < ? ORDER BY created_at DESC LIMIT $showlimit";
		$posts = $this->findAll($sql, 'obj', [$published, $aDate]);

		if(!empty($posts)) {
			$lastPostDate = end($posts)->created_at;
			foreach ($posts as $key => $post) {
				$output .= '<div class="half d-md-flex d-block">';
				if($key % 2 == 0) {
					$output .= '<div class="image" style="background-image: url('. BASE_URL . 'uploads/images/articles/'. $post->image . ')"></div>';
				}
				else {
					$output .= '<div class="image order-2" style="background-image: url(' . BASE_URL . 'uploads/images/articles/'. $post->image . ')"></div>';
				}

				$output .= '<div class="text text-center element-animate fadeInUp element-animated">
								<span class="post-meta">' . date("j F Y", strtotime($post->created_at)) . '</span>
	          					<h3 class="mb-4">' . $post->title . '</h3>
	          					<p class="article_summary mb-5">' . preg_replace('/\s+?(\S+)?$/', '', mb_substr(strip_tags($post->content), 0, 200)) . '...</p>
	          					<p><a href="article/' . $post->slug . '" class="btn btn-primary btn-sm px-3 py-2"><!--Continue Reading-->Διαβάστε Περισσότερα</a></p>
	        				</div>
	      				</div>';
	      	}
		}


		if($totalRowCount > $showlimit) {
			$output .= '<div id="show_more_main" class="col-md-12 py-5 text-center bg-light">
	      					<a href="#" id="show_more" class="btn btn-primary" title="Load More Posts..." data-adate="' . $lastPostDate . '"><!--Load More Posts...-->Δείτε Περισσότερα...</a>
	      					<img src="images/ovals_in_circle.svg" id="loading" style="display: none;" width="32" height="32">
	    				</div>';
	    }

		return $output;
	}

	public function ckUpload()
	{
		// Define file upload path 
		$upload_dir = array( 
		    'img'=> 'uploads/images/articles/content/', 
		); 
		 
		// Allowed image properties  
		$imgset = array( 
		    'maxsize' => 2000, 
		    'maxwidth' => 2560, 
		    'maxheight' => 1800, 
		    'minwidth' => 10, 
		    'minheight' => 10, 
		    'type' => array('bmp', 'gif', 'jpg', 'jpeg', 'png'), 
		); 
		 
		// If 0, will OVERWRITE the existing file 
		define('RENAME_F', 1); 
		 
		/** 
		 * Set filename 
		 * If the file exists, and RENAME_F is 1, set "img_name_1" 
		 * 
		 * $p = dir-path, $fn=filename to check, $ex=extension $i=index to rename 
		 */ 
		function setFName($p, $fn, $ex, $i){ 
		    if(RENAME_F ==1 && file_exists($p .$fn .$ex)){ 
		        return setFName($p, F_NAME .'_'. ($i +1), $ex, ($i +1)); 
		    }else{ 
		        return $fn .$ex; 
		    } 
		} 
		 
		$re = ''; 
		if(isset($_FILES['upload']) && strlen($_FILES['upload']['name']) > 1) {
		 
		    define('F_NAME', preg_replace('/\.(.+?)$/i', '', basename($_FILES['upload']['name'])));   
		 
		    // Get filename without extension 
		    $sepext = explode('.', strtolower($_FILES['upload']['name'])); 
		    $type = end($sepext);    /** gets extension **/ 
		     
		    // Upload directory 
		    $upload_dir = in_array($type, $imgset['type']) ? $upload_dir['img'] : $upload_dir['audio'];
		 
		    // Validate file type 
		    if(in_array($type, $imgset['type'])) { 
		        // Image width and height 
		        list($width, $height) = getimagesize($_FILES['upload']['tmp_name']); 
		 
		        if(isset($width) && isset($height)) { 
		            if($width > $imgset['maxwidth'] || $height > $imgset['maxheight']){ 
		                $re .= '\\n Width x Height = '. $width .' x '. $height .' \\n The maximum Width x Height must be: '. $imgset['maxwidth']. ' x '. $imgset['maxheight']; 
		            } 
		 
		            if($width < $imgset['minwidth'] || $height < $imgset['minheight']){ 
		                $re .= '\\n Width x Height = '. $width .' x '. $height .'\\n The minimum Width x Height must be: '. $imgset['minwidth']. ' x '. $imgset['minheight']; 
		            } 
		 
		            if($_FILES['upload']['size'] > $imgset['maxsize']*1000){ 
		                $re .= '\\n Maximum file size must be: '. $imgset['maxsize']. ' KB.'; 
		            } 
		        } 
		    }else { 
		        $re .= 'The file: '. $_FILES['upload']['name']. ' has not the allowed extension type.'; 
		    } 
		     
		    // File upload path 
		    $f_name = setFName($_SERVER['DOCUMENT_ROOT'] .'/'. $upload_dir, F_NAME, ".$type", 0); 
		    $uploadpath = $upload_dir . $f_name;
		 
		    // If no errors, upload the image, else, output the errors 
		    if($re == '') {
		        if(move_uploaded_file($_FILES['upload']['tmp_name'], $uploadpath)) { 
		            $CKEditorFuncNum = $_GET['CKEditorFuncNum']; 
		            $url = BASE_URL . $upload_dir . $f_name; 
		            $msg = F_NAME .'.'. $type .' successfully uploaded: \\n- Size: '. number_format($_FILES['upload']['size']/1024, 2, '.', '') .' KB'; 
		            $re = in_array($type, $imgset['type']) ? "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>":'<script>var cke_ob = window.parent.CKEDITOR; for(var ckid in cke_ob.instances) { if(cke_ob.instances[ckid].focusManager.hasFocus) break;} cke_ob.instances[ckid].insertHtml(\' \', \'unfiltered_html\'); alert("'. $msg .'"); var dialog = cke_ob.dialog.getCurrent();dialog.hide();</script>'; 
		        }else { 
		            $re = '<script>alert("Unable to upload the file")</script>'; 
		        } 
		    }else { 
		        $re = '<script>alert("'. $re .'")</script>'; 
		    } 
		} 
		 
		// Render HTML output 
		@header('Content-type: text/html; charset=utf-8'); 
		echo $re;
	}
}