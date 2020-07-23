<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Blog;


class BlogController extends Controller
{
    private $pageTitle = 'Custom Software Development Company Blog | CodeRiders';
    private $pageMetaDescription = 'The latest research-driven software development articles and news on web development and design, custom software development, software outsource, etc.';
    private $basePath;


    function __construct(){
        $this->basePath = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'];
    }

    public function index() {

        // $blog           = new Blog;
        $main_blog      = Blog::select('id', 'title', 'slug', 'image', 'image_title', 'image_alt', 'content','page_title',
                                        'page_description', 'publish_status','trending_status', 'main_status', 'created_at')
                                        ->where('main_status', 1)
                                        ->get();
        $main_blog = $main_blog[0]->toArray(); // возможно не исполнять эту строку если видоизменить следующуу

        $main_blog_id   = (!empty($main_blog)) ? $main_blog['id'] : false;

//       $recent_blogs   = $blog->getRecentBlogs(true, 6, 0);
        $recent_blogs   = Blog::select('id', 'title', 'slug', 'image', 'content', 'page_description','publish_status',
                                       'trending_status','main_status','created_at')
                                        ->where([
                                            ['publish_status', '=', 1],
                                            ['trending_status', '=', 1]
                                        ])
                                        ->limit(6)
                                        ->get();

//        $trending_blogs = $blog->getTrendingBlogs(true, $main_blog_id);
        $trending_blogs = Blog::select('id','title','slug','content','image','page_description','created_at')
                                ->where([
                                    ['publish_status', '=', 1],
                                    ['trending_status', '=', 1]
                                ])
                                ->when($main_blog_id, function($query, $main_blog_id){
                                    $query->where('id', '<>',  $main_blog_id);
                                })
                                ->get();

        $response = array();

        $response['title']          = $this->pageTitle;
        $response['description']    = $this->pageMetaDescription;
        $response['main_blog']      = $main_blog;
        $response['recent_blogs']   = $recent_blogs;
        $response['trending_blogs'] = $trending_blogs;

        $response['next_page'] = (count($recent_blogs) >= 6) ? 1 : 0;

        return view('Blog', $response);
    }

    public function show($slug) {
        $response       = array();

        // $target_blog    = $blog->findBySlug($slug);
        $target_blog    = Blog::select('id', 'title','page_description')
                                ->where('slug', $slug)
                                ->firstOrFail();


//        $trending_blogs = $blog->getTrendingBlogs(true, $target_blog['id']);
        $trending_blogs = Blog::select('id','title','slug','content','image','page_description','created_at')
                                ->where([
                                    ['publish_status', '=', 1],
                                    ['trending_status', '=', 1],
                                    ['id', '<>', $target_blog['id']]
                                ])
                                ->limit(3)
                                ->get();

//        $related_blogs  = $blog->findRelatedBlogs($target_blog['id']);
        $related_blogs  = Blog::select('blogs_related_blogs.related_blog_id','blogs.id','blogs.title','blogs.content','blogs.slug','blogs.created_at',
                                'blogs.image','blogs.page_description')
                                ->rightJoin('blogs_related_blogs','blogs.id','=', 'blogs_related_blogs.related_blog_id')
                                ->where('blogs_related_blogs.blog_id',  $target_blog['id'])
                                ->get();
//        with relationship
//        $related_blogs  = Blog::select('blogs.id','blogs.title','blogs.content','blogs.slug','blogs.created_at',
//                                        'blogs.image','blogs.page_description')
//                                        ->with('blogs_related_blogs')
//                                        ->get();


        $response['target_blog']    = $target_blog;
        $response['trending_blogs'] = $trending_blogs;
        $response['related_blogs']  = $related_blogs;

        return view('BlogInner', $response);
    }

    public function load(Request $request) {
        $response = array();
        $request['offset'] = 1; // temporary row

        if(!isset($request['offset'])) {
            abort(404);
        }

        $offset = $request['offset'];
        $next_offset = $offset + 2;


//        $all_blogs_count = $blog->getAllBlogsCount(true);
        $all_blogs_count =  Blog::select('id')
                                ->where([
                                    ['publish_status', '=', 1],
                                    ['main_status', '<>', 1]
                                ])
                                ->count();

//        $recent_blogs    = $blog->getRecentBlogs(true, 2, $offset);
        $recent_blogs    = Blog::select('id', 'title', 'slug', 'image', 'content', 'page_description','publish_status',
                                        'trending_status','main_status','created_at')
                                        ->where('publish_status', 1)
                                        ->where('main_status','<>', 1)
                                        ->offset($offset)
                                        ->limit(2)
                                        ->get();

        $has_next_page = $next_offset < $all_blogs_count;
        if(($recent_blogs)->count() > 0) {

            foreach ($recent_blogs as $key => $value) {

                $date = date('d-m-Y', strtotime($value['created_at']));
                $content = strip_tags($value['content']);
                $content = substr($content, 0, 200)."...";

                $recent_blogs[$key]['created_at'] = $date;
                $recent_blogs[$key]['content'] = $content;
                $recent_blogs[$key]['twitter_link']  = $this->generateTwitterLink($value['slug']);
                $recent_blogs[$key]['facebook_link'] = $this->generateFacebookLink($value['slug']);
                $recent_blogs[$key]['linkedin_link'] = $this->generateLinkedInLink($value['slug'], $value['title'], $value['page_description']);
                $recent_blogs[$key]['google_link']   = $this->generateGooglePlusLink($value['slug']);
            }

            $response['blogs']         = $recent_blogs;
            $response['code']          = 200;
            $response['has_next_page'] = $has_next_page;
            $response['next_offset']   = $next_offset;
        } else {
            $response['code']         = 404;
        }


        return response($response);
    }

    private function generateTwitterLink($slug) {
        return 'https://twitter.com/intent/tweet?url=' . $this->basePath . '/blog/' . $slug;
    }

    private function generateFacebookLink($slug) {
        return $this->basePath . '/blog/' . $slug;
    }

    private function generateLinkedInLink($slug, $title, $description) {
        return
            'https://www.linkedin.com/shareArticlemini=true&url=' . $this->basePath . '/blog/' . $slug . '&title=' . urlencode($title) . '&summary=' . urlencode($description) . '&source=' . $this->basePath . '/blog/' . $slug;
    }

    private function generateGooglePlusLink($slug) {
        return 'https://plus.google.com/share?url=' . $this->basePath . '/blog/' . $slug;
    }
}
