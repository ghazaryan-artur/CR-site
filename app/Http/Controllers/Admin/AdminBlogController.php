<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Blog;
use App\BlogsRelatedBlogs as Related;
use App\BlogsRelatedBlogs;
use Illuminate\Support\Str;


class AdminBlogController extends Controller
{
    public $upload_dir;
    public $params = [];
    public $data = [];



    public function index() {

        $response['recent_blogs']   = Blog::getRecentBlogs();
        $response['trending_blogs'] = Blog::getTrendingBlogs();
        return view('admin.blog', $response);
    }

    public function show() {
        $response['blogs'] = Blog::all();
        return view('admin.blogs', $response);
    }

    public function store(Request $request) {

            $request->validate([
            'pageTitle'       => 'required',
            'pageDescription' => 'required',
            'bannerTitle'     => 'required',
            'bannerText'      => 'required',
            'bannerSlug'      => 'required',
            'bannerSlugText'  => 'required',
            'title'           => 'required',
            'slug'            => 'required',
            'content'         => 'required',
            'imageTitle'      => 'required',
            'imageAlt'        => 'required',
            'image'           => "required|file|mimes:jpg,jpeg,png|max:2048",
        ]);

        $path = $request->file('blogImage')->store('images'); //directory name may be changes

        $new_blog = array();

        $new_blog['pageTitle']        = $request['pageTitle'];
        $new_blog['pageDescription']  = $request['pageDescription'];
        $new_blog['bannerTitle']      = $request['bannerTitle'];
        $new_blog['bannerText']       = $request['bannerText'];
        $new_blog['bannerSlug']       = $request['bannerSlug'];
        $new_blog['bannerSlugText']   = $request['bannerSlugText'];
        $new_blog['title']            = $request['title'];
        $new_blog['slug']             = $request['slug'];
        $new_blog['content']          = $request['content'];
        $new_blog['image']            = Str::afterLast($path, '/');
        $new_blog['image_title']      = $request['imageTitle'];
        $new_blog['image_alt']        = $request['imageAlt'];
        $new_blog['publish']          = $request['publishStatus'] ? 1 : 0;
        $new_blog['trend']            = $request['trend'] ? 1 : 0;
        $new_blog['main']             = $request['main'] ? 1 : 0;


        $create_blog = Blog::createOrUpdate($new_blog, "create", null, $request['relatedBlogs']);


        if($request['relatedBlogs']) {
            $related_blog = $request['relatedBlogs'];
            BlogsRelatedBlogs::where('blog_id', $create_blog)->delete(); // veradarnal
            $related_blogs_array = explode(",", $related_blog);
            foreach ($related_blogs_array as $id){
                $relBlog = new BlogsRelatedBlogs;
                $relBlog->blog_id = $create_blog;
                $relBlog->related_blog_id = $id;
                $add_related = $relBlog->save();
                if(!$add_related){
                    break;
                }
            }
        }

        if($add_related && $create_blog) {
            return redirect('/admin/blog')->with(['message_prefix' => 'Success! ',
                                                  'message' => 'Blog has been successfully created!',
                                                  'color' => 'success']);
        }
        return redirect('/admin/blog/create')->with(['message_prefix' => 'Error. ',
                                                         'message' => 'Blog URL must be unique string, it must contain only letters, numbers and dashes.',
                                                         'color' => 'danger']);
    }

    public function editPage($id) {
        $blog = Blog::findOrFail($id);
        $related_blogs = Related::select('related_blog_id')
            ->where('blog_id',  $id)
            ->get();
        $response['blog'] = $blog;
        $response['related_blogs'] = $related_blogs;
        $response['page_title']    = "Blog Edit";
        return view('admin.createOrUpdate', $response);
    }

    public function edit(Request $request, $id) {

        $request->validate([
            'pageTitle'       => 'required',
            'pageDescription' => 'required',
            'bannerTitle'     => 'required',
            'bannerText'      => 'required',
            'bannerSlug'      => 'required',
            'bannerSlugText'  => 'required',
            'title'           => 'required',
            'slug'            => 'required',
            'content'         => 'required',
            'imageTitle'      => 'required',
            'imageAlt'        => 'required',
            'image'           => "file|mimes:jpg,jpeg,png|max:2048",
        ]);

        $new_blog = array();

        $new_blog['pageTitle']        = $request->pageTitle;
        $new_blog['pageDescription']  = $request->pageDescription;
        $new_blog['bannerTitle']      = $request->bannerTitle;
        $new_blog['bannerText']       = $request->bannerText;
        $new_blog['bannerSlug']       = $request->bannerSlug;
        $new_blog['bannerSlugText']   = $request->bannerSlugText;
        $new_blog['title']            = $request->title;
        $new_blog['content']          = $request->blogContent;
        $new_blog['slug']             = $request->slug;
        $new_blog['publish']          = $request->publishStatus ? 1 : 0;
        $new_blog['trend']            = $request->trend ? 1 : 0;
        $new_blog['main']             = $request->main ? 1 : 0;
        $new_blog['image_title']      = $request->imageTitle;
        $new_blog['image_alt']        = $request->imageAlt;

        if($request->file('blogImage')){
            $path = $request->file('blogImage')->store('images'); //directory name may be changes
            $new_blog['image']        = Str::afterLast($path, '/');

        }

        $blog = new Blog;
        $add_related = true;

        $update_blog = $blog->createOrUpdate($new_blog, "update", $id, $related_blog);

        if($request->relatedBlogs) {
            $related_blog = $request->relatedBlogs;
            BlogsRelatedBlogs::where('blog_id', $update_blog)->delete(); // veradarnal
            $related_blogs_array = explode(",", $related_blog);
            foreach ($related_blogs_array as $id){
                $relBlog = new BlogsRelatedBlogs;
                $relBlog->blog_id = $update_blog;
                $relBlog->related_blog_id = $id;
                $add_related = $relBlog->save();
                if(!$add_related){
                    break;
                }
            }
        }

        if($add_related && $update_blog) {
            return redirect('/admin/blog')->with(['message_prefix' => 'Success! ',
                                                'message' => 'Blog has been successfully updated!',
                                                'color' => 'success']);
        }
        return redirect('/admin/blog/create')->with(['message_prefix' => 'Error. ',
                                                'message' => 'Blog URL must be unique string, it must contain only letters, numbers and dashes.',
                                                'color' => 'danger']);
    }

    public function delete() {
        if(!isset($_POST['blogId'])) {
            $_SESSION['message'] = "Oops, something went wrong, please try again!";
            $_SESSION['message_type'] = "danger";
            header('Location: /admin/blog');
            die;
        }

        $id = $_POST['blogId'];

        $blog = new Blog;
        $blog_checking = $blog->find('blogs', $id);

        if(!empty($blog_checking) && isset($blog_checking[0])) {
            $blog_image = $blog_checking[0]['image'];

            if(!file_exists($this->upload_dir.$blog_image)) {
                $_SESSION['message'] = "Oops, something went wrong, please try again, can't find blog image!";
                $_SESSION['message_type'] = "danger";
                $_SESSION['message_prefix'] = "Error";
                header('Location: /admin/blog');
                die;
            }
            unlink($this->upload_dir.$blog_image);

            $delete_blog = $blog->remove('blogs', $id);
            if($delete_blog) {
                $_SESSION['message'] = "Blog has been removed successfully!";
                $_SESSION['message_type'] = "success";
                $_SESSION['message_prefix'] = "Success";
                header('Location: /admin/blog');
                die;
            }

        } else {
            $_SESSION['message'] = "Oops, something went wrong, please try again, no such blog!";
            $_SESSION['message_type'] = "danger";
            $_SESSION['message_prefix'] = "Error";
            header('Location: /admin/blog');
            die;
        }
    }

    public function publish($id) {
        $response = array();

        $numeric_check = $this->checkNumeric($id);
        if(!empty($numeric_check)) {
            echo $numeric_check;
            die;
        }

        $blog = new Blog;
        $blog_checking = $blog->find('blogs', $id);

        if(empty($blog_checking) || !isset($blog_checking[0])) {
            $response = array (
                'code'    => 404,
                'status'  => 'error',
                'message' => 'Error! No such blog with id: '.$id.'!'
            );
            echo json_encode($response);
            die;
        }

        $publish = $blog->publishOrUnpublish($id, 1);

        if($publish) {
            $response = array (
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Blog has been successfully published!'
            );
            echo json_encode($response);
            die;
        } else {
            $response = array (
                'code'    => 500,
                'status'  => 'error',
                'message' => 'Error! Oops something went wrong, please try again!'
            );
            echo json_encode($response);
            die;
        }
    }
    public function unpublish($id) {
        $response = array();

        $numeric_check = $this->checkNumeric($id);
        if(!empty($numeric_check)) {
            echo $numeric_check;
            die;
        }

        $blog = new Blog;
        $blog_checking = $blog->find('blogs', $id);

        if(empty($blog_checking) || !isset($blog_checking[0])) {
            $response = array (
                'code'    => 404,
                'status'  => 'error',
                'message' => 'Error! No such blog with id: '.$id.'!'
            );
            echo json_encode($response);
            exit;
        }

        $publish = $blog->publishOrUnpublish($id, 0);

        if($publish) {
            $response = array (
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Blog has been successfully unpublished!'
            );
            echo json_encode($response);
            exit;
        } else {
            $response = array (
                'code'    => 500,
                'status'  => 'error',
                'message' => 'Error! Oops something went wrong, please try again!'
            );
            echo json_encode($response);
            exit;
        }
    }
    public function trend($id) {
        $response = array();

        $numeric_check = $this->checkNumeric($id);
        if(!empty($numeric_check)) {
            echo $numeric_check;
            die;
        }

        $blog = new Blog;
        $blog_checking = $blog->find('blogs', $id);

        if(empty($blog_checking) || !isset($blog_checking[0])) {
            $response = array (
                'code'    => 404,
                'status'  => 'error',
                'message' => 'Error! No such blog with id: '.$id.'!'
            );
            echo json_encode($response);
            exit;
        }

        $untrend = $blog->trendOrUntrend($id, 1);

        if($untrend) {
            $response = array (
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Blog has been successfully marked as trending blog!'
            );
            echo json_encode($response);
            exit;
        } else {
            $response = array (
                'code'    => 500,
                'status'  => 'error',
                'message' => 'Error! Oops something went wrong, please try again!'
            );
            echo json_encode($response);
            exit;
        }
    }
    public function untrend($id) {
        $response = array();

        $numeric_check = $this->checkNumeric($id);
        if(!empty($numeric_check)) {
            echo $numeric_check;
            die;
        }

        $blog = new Blog;
        $blog_checking = $blog->find('blogs', $id);

        if(empty($blog_checking) || !isset($blog_checking[0])) {
            $response = array (
                'code'    => 404,
                'status'  => 'error',
                'message' => 'Error! No such blog with id: '.$id.'!'
            );
            echo json_encode($response);
            exit;
        }

        $untrend = $blog->trendOrUntrend($id, 0);

        if($untrend) {
            $response = array (
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Blog has been successfully marked as non trended blog!'
            );
            echo json_encode($response);
            exit;
        } else {
            $response = array (
                'code'    => 500,
                'status'  => 'error',
                'message' => 'Error! Oops something went wrong, please try again!'
            );
            echo json_encode($response);
            exit;
        }
    }
    public function main($id) {
        $response = array();

        $numeric_check = $this->checkNumeric($id);
        if(!empty($numeric_check)) {
            echo $numeric_check;
            die;
        }

        $blog = new Blog;
        $blog_checking = $blog->find('blogs', $id);

        if(empty($blog_checking) || !isset($blog_checking[0])) {
            $response = array (
                'code'    => 404,
                'status'  => 'error',
                'message' => 'Error! No such blog with id: '.$id.'!'
            );
            echo json_encode($response);
            exit;
        }

        $untrend = $blog->mainOrUnmain($id, 1);

        if($untrend) {
            $response = array (
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Blog has been successfully marked as non trended blog!'
            );
            echo json_encode($response);
            exit;
        } else {
            $response = array (
                'code'    => 500,
                'status'  => 'error',
                'message' => 'Error! Oops something went wrong, please try again!'
            );
            echo json_encode($response);
            exit;
        }

    }
    public function unmain($id) {
        $response = array();

        $numeric_check = $this->checkNumeric($id);
        if(!empty($numeric_check)) {
            echo $numeric_check;
            die;
        }

        $blog = new Blog;
        $blog_checking = $blog->find('blogs', $id);

        if(empty($blog_checking) || !isset($blog_checking[0])) {
            $response = array (
                'code'    => 404,
                'status'  => 'error',
                'message' => 'Error! No such blog with id: '.$id.'!'
            );
            echo json_encode($response);
            exit;
        }

        $untrend = $blog->mainOrUnmain($id, 0);

        if($untrend) {
            $response = array (
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Blog has been successfully marked as non trended blog!'
            );
            echo json_encode($response);
            exit;
        } else {
            $response = array (
                'code'    => 500,
                'status'  => 'error',
                'message' => 'Error! Oops something went wrong, please try again!'
            );
            echo json_encode($response);
            exit;
        }
    }

    public function find() {
        $response = array();

        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            require('Controllers/ErrorPage.php');
            $controller = new \ErrorPage();
            exit;
        }

        $exepted_ids = $_POST['ids'];

        $blog = new Blog;
        $related_blogs = $blog->findByTerm(array("id", "image", "title"), $_POST['term'], $exepted_ids);

        if(!empty($related_blogs)) {
            $response['related_blogs'] =  $related_blogs;
            $response['code'] = 200;
        } else {
            $response['code'] = 404;
        }

        echo json_encode($response);
        die;
    }
}
