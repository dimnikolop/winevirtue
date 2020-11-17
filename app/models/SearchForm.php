<?php
namespace app\models;
use app\src\Model;
/**
 * 
 */
class SearchForm extends Model
{
	public $search;
	
	function __construct()
	{
		# code...
	}

	public function rules()
	{
		return [
			'search' => [self::RULE_REQUIRED, [self::RULE_MAX_LENGTH, 'max' => 50]]
		];
	}

	public function getSearchResults()
	{
		$searchWords = explode(" ", $this->search);
		$wordsCount = count($searchWords);
		$paramValue = [];

		$queryCondition = " WHERE published = 1 AND ";
		for($i=0; $i<$wordsCount; $i++) {
			$queryCondition .= "title RLIKE CONCAT('[[:<:]]',?,'[[:>:]]') OR content RLIKE CONCAT('[[:<:]]',?,'[[:>:]]')";
			$paramValue[] = $searchWords[$i];
			$paramValue[] = $searchWords[$i];
			if($i!=$wordsCount-1) {
				$queryCondition .= " OR ";
			}
		}

		$sql = "SELECT title, slug, image, content, created_at FROM posts" . $queryCondition . " ORDER BY created_at DESC";
		$article = new Article();
		$posts = $article->findAll($sql, 'assoc', $paramValue);

		if (!empty($posts)) {
			foreach ($posts as $key => $post) {
				$content = strip_tags($post['content']);
				$partContent =  $this->getPartitionText($content, $searchWords);

				if (empty($partContent)) {
					$partContent = preg_replace('/\s+?(\S+)?$/', '', mb_substr($content, 0, 200)) . "&#8230;";
				}
				$partContent = $this->highlightWords($partContent, $searchWords);
				$posts[$key]['content'] = $partContent;
			}
		}
		
		$paramValue = [];

		$queryCondition = " WHERE published = 1 AND ";
		for($i=0;$i<$wordsCount;$i++) {
			$queryCondition .= "CONCAT(title, description, rating, color, sweetness, producer, country, region, varieties, vintage, alcohol, consumption, food_pairing) RLIKE CONCAT('[[:<:]]',?,'[[:>:]]')";
			$paramValue[] = $searchWords[$i];
			if($i!=$wordsCount-1) {
				$queryCondition .= " OR ";
			}
		}

		$sql = "SELECT title, slug, imageh, description, food_pairing, created_at FROM wines" . $queryCondition . " ORDER BY created_at DESC";

		$wine = new Wine();
		$wines = $wine->findAll($sql, 'assoc', $paramValue);

		if (!empty($wines)) {
			foreach ($wines as $key1 => $wine) {
				$description = $this->getPartitionText($wine['description'], $searchWords);
				if (empty($description)) {
					$description = preg_replace('/\s+?(\S+)?$/', '', mb_substr($wine['description'], 0, 200)) . "&#8230;";

					foreach ($searchWords as $key2 => $searchWord) {
						$pos = mb_stripos($wine['food_pairing'], $searchWord);
									
						if ($pos !== false) {
							$description = $wine['food_pairing'];
						}
					}
				}

				$description = $this->highlightWords($description, $searchWords);
				$wines[$key1]['description'] = $description;
			}
		}

		return ["posts" => $posts, "wines" => $wines, "search" => $this->search];
		
	}

	function getPartitionText($text, $words) {
		$final_text = "";
		foreach ($words as $key => $word) {
			$pos = mb_stripos($text, $word);

			if($pos !== false) {
		    	$length = 100;
		    	
			   if($pos <= $length) {
			      $start = mb_substr($text, 0, $pos);
			   }
			   else {
			    	$start = mb_substr($text, 0, $pos);
			    	$last_dot = mb_strripos($start, '.');
			    	$start = mb_substr($start, $last_dot+1, $pos-$last_dot);
			   }
			   if(strlen(mb_substr($text, $pos)) <= $length) {
			    	$end = mb_substr($text, $pos);
			   }
			   else {
			    	$end = mb_substr($text, $pos, $length);
			    	$end = preg_replace('/\s+?(\S+)?$/', '', $end) . "&#8230;";
			   }
		  		
		    	$subStr = $start . $end;
		    	if ($key > 0 && mb_substr($subStr, -1) !== '...') {
		    		$final_text .= "&#8230;";
		    	}
		    	$final_text .= $subStr;
	    	}
		}

		return $final_text;
	}

	// Highlight words in text
	function highlightWords($text, $words) {
		foreach ($words as $key => $word) {
			$text = preg_replace('/'. preg_quote($word) .'/i', '<strong>$0</strong>', $text);
			$word = mb_convert_case($word, MB_CASE_LOWER, "UTF-8");
			$text = preg_replace('/'. preg_quote($word) .'/i', '<strong>$0</strong>', $text);
			$word = mb_convert_case($word, MB_CASE_TITLE, "UTF-8");
			$text = preg_replace('/'. preg_quote($word) .'/i', '<strong>$0</strong>', $text);
		}	    
	   return $text;
	}
}