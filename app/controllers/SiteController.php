<?php
namespace app\controllers;
use app\src\Controller;
use app\src\Application;
use app\models\Article;
use app\models\Wine;
use app\models\Comment;
use app\models\ContactForm;
use app\models\SearchForm;
use app\src\exceptions\NotFoundException;
/**
 * 
 */
class SiteController extends Controller
{
	function __construct()
	{
		# code...
	}

	public function home()
	{
		$article = new Article();
		$articles = $article->getPublishedPosts();
		return $this->render('home', ['articles'=> $articles]);
	}

	public function about()
	{
		return $this->render('about');
	}

	public function articles($request)
	{
		$article = new Article();

		if ($request->getMethod() === 'post') {
			$clean_data = $request->getBody();
			// AJAX call to load more articles
			return $article->loadMorePosts($clean_data['aDate']);
		}

		$articles = $article->getPublishedPosts();
		return $this->render('articles', ['model' => $articles]);
	}

	public function wineReviews($request)
	{
		$wine = new Wine();
		
		if ($request->getMethod() === 'post') {
			$clean_data = $request->getBody();
			if ($clean_data['action'] === 'fetch_wines') {
				$data = $wine->getPublishedWines($clean_data['page']);
				return json_encode($data);
			}
			elseif ($clean_data['action'] === 'filter_wines') {
				$data = $wine->getFilteredWines($clean_data, $clean_data['page']);
				return json_encode($data);
			}	
		}

		$data = $wine->getPublishedWines();
		return $this->render('wine_reviews', ['model' => $data['wines'], 'pageLinks' => $data['pagination']]);
	}

	public function contact($request, $response)
	{
		$contact = new ContactForm();

		if ($request->getMethod() === 'post') {
			$clean_data = $request->getBody();
			$contact->loadData($clean_data);
			if (!empty($clean_data['bot_mail'])) exit('Bot Bot');
			if ($contact->validate() && $contact->send()) {
				return json_encode(['success' => '<div class="alert alert-success" role="alert"><i class="fa fa-check-circle"></i> <strong>Success!</strong> Thanks for contacting us.</div>']);
			}
			else {
				return json_encode(['errors' => $contact->errors]);
			}
		}
		return $this->render('contact', ['model' => $contact]);
	}

	public function singleArticle($request, $response)
	{
		if ($request->getMethod() === 'get') {
			$params = $request->getUrlParams();

			if ($params) {
				$article = new Article();
				$post = $article->getPost($params[0]);
				$rposts = $article->getRecentPosts();
				if ($post) {
					return $this->render('single_article', ['post' => $post, 'rposts' => $rposts]);
				}
				else {
					throw new NotFoundException();
				}
			}
			else {
				throw new NotFoundException();
			}
		}
		else {
			$comment = new Comment();
			$clean_data = $request->getBody();

			if ($clean_data['action'] === 'addComment') {
				$comment->loadData($clean_data);
				if (!empty($clean_data['spam_mail'])) exit('Spamming');
				if ($comment->validate() && $comment->addComment()) {
					return json_encode(['success' => '<div class="alert alert-success" role="alert"><i class="fa fa-check-circle"></i> <strong>Success!</strong> Ευχαριστούμε για το σχολιό σας.</div>']);
				}
				return json_encode(['errors' => $comment->errors]);
			}
			if ($clean_data['action'] === 'fetchComments') {
				return $comment->fetchComments($clean_data['id']);
			}
		}
	}

	public function singleWine($request)
	{
		$params = $request->getUrlParams();
		
		if ($params) {
			$wine = new Wine();
			$wine = $wine->getWine($params[0]);
			if ($wine) {
				return $this->render('single_wine', ['model' => $wine]);
			}
			else {
				throw new NotFoundException();
			}
		}
		else {
			throw new NotFoundException();
		}
	}

	public function search($request)
	{
		$searchResults = [
			'posts' => '',
			'wines' => ''
		];
		$search = new SearchForm();

		if ($request->getMethod() === 'post') {
			$search->loadData($request->getBody());
			if ($search->validate()) {
				$searchResults = $search->getSearchResults();
			}
		}
		return $this->render('search', ['model' => $search, 'posts' => $searchResults['posts'], 'wines' => $searchResults['wines']]);
	}
}