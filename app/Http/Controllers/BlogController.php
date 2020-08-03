<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Blog;


class BlogController extends Controller
{

    public function index() {
        $main_blog      = Blog::getMainBlog();

        $main_blog_id   = $main_blog ? $main_blog->id : false;

        $recent_blogs   = Blog::getRecentBlogs(true);
        $trending_blogs = Blog::getTrendingBlogs(true, $main_blog_id);

        $response = array();
        $response['title']          = 'Custom Software Development Company Blog | CodeRiders';;
        $response['description']    = 'The latest research-driven software development articles and news on web development and design, custom software development, software outsource, etc.';
        $response['main_blog']      = $main_blog;
        $response['recent_blogs']   = $recent_blogs;
        $response['trending_blogs'] = $trending_blogs;

        $response['next_page'] = (count($recent_blogs) >= 6) ? 1 : 0;

        return view('Blog', $response);
    }

    public function show($slug) {
        $response       = array();

        $target_blog    = Blog::findBySlug($slug);

        $trending_blogs = Blog::getTrendingBlogs(true, $target_blog['id']);
        $related_blogs  = Blog::findRelatedBlogs($target_blog['id']);

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

        $all_blogs_count = Blog::getAllBlogsCount();
        $recent_blogs    = Blog::getRecentBlogs(true, $offset, 2);

        $has_next_page = $next_offset < $all_blogs_count;
        if($recent_blogs->count() > 0) {

            foreach ($recent_blogs as $key => $value) {

                $date = date('d-m-Y', strtotime($value['created_at']));
                $content = strip_tags($value['content']);
                $content = substr($content, 0, 200)."...";

                $recent_blogs[$key]['created_at'] = $date;
                $recent_blogs[$key]['content'] = $content;
                $recent_blogs[$key]['twitter_link']  = 'https://twitter.com/intent/tweet?url=' . url("/") . '/blog/' . $value['slug'];
                $recent_blogs[$key]['facebook_link'] = url("/") . '/blog/' . $value['slug'];
                $recent_blogs[$key]['linkedin_link'] = 'https://www.linkedin.com/shareArticlemini=true&url=' . url("/")
                                                    . '/blog/' . $value['slug'] . '&title=' . urlencode($value['title']) . '&summary='
                                                    . urlencode($value['page_description']) . '&source=' . url("/") . '/blog/' . $value['slug'];
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


}
