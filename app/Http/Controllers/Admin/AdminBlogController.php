<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Blog;
use App\BlogsRelatedBlogs;
use Illuminate\Support\Str;


class AdminBlogController extends Controller
{
    public $upload_dir;
    public $params = [];
    public $data = [];

//    function __construct() {
//
//        if(!isset($_SESSION['user'])) {
//            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//                header('HTTP/1.0 403 Forbidden');
//                die;
//            } else {
//                header('Location: /admin');
//                die;
//            }
//        }
//
//        $this->upload_dir = getcwd().'/public/uploads/';
//        $this->params = array(
//            'admin' => true,
//            'admin_login' => false
//        );
//        $this->data = array();
//    }

    public function index() {
        $response['recent_blogs']   = Blog::getRecentBlogs();
        $response['trending_blogs'] = Blog::getTrendingBlogs();
        return view('admin.blog', $response);
    }

    public function show() {
        $response['blogs'] = Blog::all();
        return view('admin.blogs', $response);
    }

    public function create(Request $request) {

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


//        $new_blog['pageTitle']        = 'The Importance of EdTech During COVID-19';
//        $new_blog['pageDescription']  = 'Online learning during COVID-19 steps up. Find out the best EdTech solutions used in various countries, updates by the private sector, post-COVID-19 EdTech.';
//        $new_blog['bannerTitle']      = 'Get free consultation about technical solutions that will drive your business to success';
//        $new_blog['bannerText']       = 'CodeRiders may become the ultimate solution for your software issues during Coronavirus pandemic.';
//        $new_blog['bannerSlug']       = 'https://www.coderiders.am/services/custom-software-development';
//        $new_blog['bannerSlugText']   = 'Grab your free consultation';
//        $new_blog['title']            = 'The Importance of EdTech During COVID-19';
//        $new_blog['slug']             = '2the-importance-of-edtech-during-covid-19';
//        $new_blog['content']          = 'some content';
//        $new_blog['image']            = 'hashhashhash';
//        $new_blog['image_alt']        = 'Online learning with a book and a computer';
//        $new_blog['image_title']      = 'EdTech is evolving IT industry especially during Covid-19';
//        $new_blog['publish']          = 1;
//        $new_blog['trend']            = 1;
//        $new_blog['main']             = 0;

//        $blog = new Blog;
//        $blog->page_title = $new_blog['pageTitle'];
//        $blog->page_description = $new_blog['pageDescription'];
//        $blog->banner_title = $new_blog['bannerTitle'];
//        $blog->banner_text = $new_blog['bannerText'];
//        $blog->banner_slug = $new_blog['bannerSlug'];
//        $blog->banner_slug_text = $new_blog['bannerSlugText'];
//        $blog->title = $new_blog['title'];
//        $blog->slug = $new_blog['slug'];
//        $blog->content = $new_blog['content'];
//        $blog->image = $new_blog['image'];
//        $blog->image_title = $new_blog['image_title'];
//        $blog->image_alt = $new_blog['image_alt'];
//        $blog->publish_status = $new_blog['publish'];
//        $blog->trending_status = $new_blog['trend'];
//        $blog->main_status = $new_blog['main'];
//        $blog->save();



        $create_blog = Blog::createOrUpdate($new_blog, "create", null, $request['relatedBlogs']);

        $add_related = false;
        if($request['relatedBlogs']) {
            $related_blog = $request['relatedBlogs'];
            BlogsRelatedBlogs::where('blog_id', $create_blog)->delete();
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
            $_SESSION['message'] = "Blog has been successfully created!";
            $_SESSION['message_type'] = "success";
            $_SESSION['message_prefix'] = "Success!";
            return redirect('/admin/blog');
        } else {
            $_SESSION['message'] = "Blog URL must be unique string, it must contain only letters, numbers and dashes.";
            $_SESSION['message_type'] = "danger";
            $_SESSION['message_prefix'] = "Error";
            return redirect('/admin/blog/create');
        }



    }

    public function update($id) {
        $numeric_check = $this->checkNumeric($id);
        if(!empty($numeric_check)) {
            $_SESSION['message'] = "Invalid url!";
            $_SESSION['message_type'] = "danger";
            $_SESSION['message_prefix'] = "Error";
            header('Location: /admin/blog');
            die;
        }

        $blog = new Blog;
        $blog_checking = $blog->find('blogs', $id);
        $related_blogs = $blog->findRelatedBlogs($id);

        if(empty($blog_checking) || !isset($blog_checking[0])) {
            $_SESSION['message'] = "No such blog!";
            $_SESSION['message_type'] = "danger";
            $_SESSION['message_prefix'] = "Error";
            header('Location: /admin/blog');
            die;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $allowed_extentions = array('jpeg','jpg','png');
            $new_blog = array();

            $new_blog['pageTitle']        = $_POST['pageTitle'];
            $new_blog['pageDescription']  = $_POST['pageDescription'];
//            $new_blog['pageKeywords']     = $_POST['pageKeywords'];

            $new_blog['bannerTitle']      = $_POST['bannerTitle'];
            $new_blog['bannerText']       = $_POST['bannerText'];
            $new_blog['bannerSlug']       = $_POST['bannerSlug'];
            $new_blog['bannerSlugText']   = $_POST['bannerSlugText'];

            $new_blog['title']            = $_POST['title'];
            $new_blog['content']          = $_POST['content'];
            $new_blog['slug']             = $_POST['slug'];

            if(!empty($_FILES['blogImage']['name'])) {
                $new_blog['image']        = $_FILES['blogImage'];
            }
            $new_blog['image_title']      = $_POST['imageTitle'];
            $new_blog['image_alt']        = $_POST['imageAlt'];

            (isset($_POST['publishStatus']))  ? $new_blog['publish'] = 1 : $new_blog['publish'] = 0;
            (isset($_POST['trendingStatus'])) ? $new_blog['trend'] = 1   : $new_blog['trend'] = 0;
            (isset($_POST['mainStatus']))     ? $new_blog['main'] = 1    : $new_blog['main'] = 0;

            if(!empty($_FILES['blogImage']['name'])) {
                $extention = explode('.', $new_blog['image']['name']);
                $extention = end($extention);
                if(!in_array(strtolower($extention), $allowed_extentions)) {
                    $_SESSION['message'] = "This file extension is not allowed. Please upload a JPEG or PNG";
                    $_SESSION['message_type'] = "danger";
                    $_SESSION['message_prefix'] = "Error";
                    header('Location: /admin/blog/create');
                    die;
                }
                if ($new_blog['image']['size'] > 2000000) {
                    $_SESSION['message'] = "This file is more than 2MB. Sorry, it has to be less than or equal to 2MB";
                    $_SESSION['message_type'] = "danger";
                    $_SESSION['message_prefix'] = "Error";
                    header('Location: /admin/blog/create');
                    die;
                }

                $hash_image_name =   md5($new_blog['image']['name'].date('Y-m-d H:i:s:u')).".".$extention;
                $full_hash_image_name = $this->upload_dir.$hash_image_name;

                if(!file_exists($full_hash_image_name)) {

                    $didUpload = move_uploaded_file($new_blog['image']['tmp_name'], $full_hash_image_name);
                    if(!$didUpload) {
                        $_SESSION['message'] = "This file is more than 2MB. Sorry, it has to be less than or equal to 2MB";
                        $_SESSION['message_type'] = "danger";
                        $_SESSION['message_prefix'] = "Error";
                        header('Location: /admin/blog/create');
                        die;
                    }
                }
                $new_blog['image'] = $hash_image_name;

                if(file_exists($this->upload_dir.$blog_checking[0]['image'])) {
                    unlink($this->upload_dir.$blog_checking[0]['image']);
                }
            }
            $blog = new Blog;
            $related_blog = true;

            $update_blog = $blog->createOrUpdate($new_blog, "update", $id, $related_blog);

            if($related_blog) {
                $related_blog = $_POST['relatedBlogs'];

                $add_realted = $blog->addRelatedBlogs($id, $related_blog);
                if($add_realted) {
                    $update_blog = true;
                }
            }

            if($update_blog) {
                $_SESSION['message'] = "Blog has been successfully updated!";
                $_SESSION['message_type'] = "success";
                $_SESSION['message_prefix'] = "Success";
                header('Location: /admin/blog');
                die;
            }
        }

        $this->data['page_title']    = "Blog Edit";
        $this->data['blog']          = $blog_checking[0];
        $this->data['related_blogs'] = $related_blogs;

        $this->view->render('blogCreate', $this->data, $this->params);
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
