<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
use App\Blog;
use App\Review;

class HomeController extends Controller
{
      public function index() {
      $response = array();
      $pageTitle = 'Custom Software Development Company | CodeRiders';
      $pageMetaDescription = 'CodeRiders is custom software development company delivering web development and design services, mobile and desktop applications, BI, e-Commerce, CRM solutions, etc.';

      $carousel_reviews = Review::findCarouselReviews();
      $recent_blogs     = Blog::getByCount(3);

      $response['title']          = $pageTitle;
      $response['description']    = $pageMetaDescription;
      $response['reviews']        = $carousel_reviews;
      $response['carousel_admin'] = false;
      $response['blogs']          = $recent_blogs;
      $response['target_blog']['image']['full_url'] = '/public/images/fbshare/homepage.jpg';

      return view('home', $response);
    }
}
