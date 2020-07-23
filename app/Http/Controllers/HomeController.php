<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Blog;
use App\Review;

class HomeController extends Controller
{
    private $pageTitle = 'Custom Software Development Company | CodeRiders';
    private $pageMetaDescription = 'CodeRiders is custom software development company delivering web development and design services, mobile and desktop applications, BI, e-Commerce, CRM solutions, etc.';

    public function index() {
        $response = array();

//        $carousel_reviews = $review->findCarouselReviews();
        $carousel_reviews = Review::all()->where('carousel_status',  1);

        // $recent_blogs     = $blog->getByCount(3);
        $recent_blogs = Blog::select('title', 'slug', 'image')
                                ->where('publish_status', 1 )
                                ->limit(3)
                                ->get();

        $response['title']          = $this->pageTitle;
        $response['description']    = $this->pageMetaDescription;
        $response['reviews']        = $carousel_reviews;
        $response['carousel_admin'] = false;
        $response['blogs']          = $recent_blogs;
        $response['target_blog']['image']['full_url'] = '/public/images/fbshare/homepage.jpg';

        return view('home', $response);
    }
}
