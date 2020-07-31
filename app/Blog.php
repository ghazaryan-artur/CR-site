<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Blog extends Model
{
    public function blogs_related_blogs()
    {
        return $this->hasMany('App\Blogs_related_blogs');
    }
    // im gratci avart
    public static function getMainBlog() {
        return self::select('id', 'title', 'slug', 'image', 'image_title', 'image_alt', 'content','page_title',
                            'page_description', 'publish_status','trending_status', 'main_status', 'created_at')
                    ->where('main_status', 1)
                    ->get();
    }
    public static function getByCount($limit) {
        return self::select('title', 'slug', 'image')
            ->where('publish_status', 1)
            ->take($limit)
            ->get();
    }

    public function findByTerm($select, $term, $ids = false) {
    $related_blogs = array();
    $where = '';
    if(empty($select)) {
        return $related_blogs;
    }

    $select = implode(',',$select);

    if($ids !== false && $ids !== '') {
        $where = " AND `id` NOT IN (".$ids.")";
    }

    $term = $this->con->real_escape_string($term);

    $query = "SELECT ".$select." FROM `blogs` WHERE `title` LIKE '%".$term."%'".$where;
    $result = $this->con->query($query);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($related_blogs, $row);
        }
    }
    return $related_blogs;
}

    public static function getAllBlogsCount() {
        return self::select('id')
            ->where([
                ['publish_status', '=', 1],
                ['main_status', '<>', 1]
            ])
            ->count();
    }

    public static function findBySlug($slug) {
        return self::select('id', 'title','page_description')
                    ->where('slug', $slug)
                    ->firstOrFail();
    }

    public function getAllBlogs() {
        $blogs = array();

        $query = "SELECT * FROM `blogs` ORDER BY `id` DESC";
        $result = $this->con->query($query);

        if(!$result->num_rows) {
            return $blogs;
        }
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                array_push($blogs, $row);
            }
        }
        return $blogs;
    }

    public static function  getRecentBlogs($published = false, $skip = 0, $take = 6) {
        return self::select('id', 'title', 'slug', 'image', 'content', 'page_description','publish_status',
                        'trending_status','main_status','created_at')
                        ->when($published, function($query){
                                return $query->where([
                                    ['publish_status', '=', 1],
                                    ['main_status', '<>', 1]
                                ]);
                        })
                        ->skip($skip)
                        ->take($take)
                        ->get();
    }

    public static function getTrendingBlogs($publised = false, $except_id = false) {
        return self::select('id','title','slug','content','image','page_description','created_at')
                    ->when($publised, function ($query) {
                        return $query->where([
                            ['publish_status', '=', 1],
                            ['trending_status', '=', 1]
                        ]);
                    })
                    ->when($except_id, function($query, $except_id){
                        $query->where('id', '<>',  $except_id);
                    })
                    ->get();
    }

    public function createOrUpdate($new_blog, $action, $id = null, $related = false) {
        $date = date('Y-m-d H:i:s');

        if($new_blog['main']) {
            $mainBlog = self::firstWhere('main_status', 1);
            $mainBlog->main_status = 0;
            $updated = $mainBlog->save();
            if(!$updated) {
                return false;
            }
        }

        if($new_blog['trend']) {
            $trendings = Blog::select('id', 'trending_status')->where('trending_status', 1)
                ->get();
            if(count($trendings) >= 3 ){
                $trendings[0]->trending_status = 0;
                $updated = $trendings[0]->save();
                if(!$updated){
                    return false;
                }
            }
        }


        if($action == "create") {
            $blog = new Blog;

            $blog->page_title       = $new_blog['pageTitle'];
            $blog->page_description = $new_blog['pageDescription'];
            $blog->banner_title     = $new_blog['bannerTitle'];
            $blog->banner_text      = $new_blog['bannerText'];
            $blog->banner_slug      = $new_blog['bannerSlug'];
            $blog->banner_slug_text = $new_blog['bannerSlugText'];
            $blog->title            = $new_blog['title'];
            $blog->slug             = $new_blog['slug'];
            $blog->content          = $new_blog['content'];
            $blog->image            = $new_blog['image'];
            $blog->image_title      = $new_blog['image_title'];
            $blog->image_alt        = $new_blog['image_alt'];
            $blog->publish_status   = $new_blog['publish'];
            $blog->trending_status  = $new_blog['trend'];
            $blog->main_status      = $new_blog['main'];

            $result = $blog->save();
        } else {

            $image_update = '';
            if(isset($blog['image'])) {
                $image_update = "`image` = '".$blog['image']."'";
            }

            $query = "UPDATE `blogs` SET `page_title` = '".$blog['pageTitle']."',
                          `page_description` = '".$blog['pageDescription']."', `page_keywords` = '".$blog['pageKeywords']."',
                          `banner_title` = '".$blog['bannerTitle']."', `banner_text` = '".$blog['bannerText']."',
                          `banner_slug` = '".$blog['bannerSlug']."', `banner_slug_text` = '".$blog['bannerSlugText']."',
                          `title` = '". $blog['title']."', `content` = '".$blog['content']."', `slug` = '".$blog['slug']."'";

            if(!empty($image_update)) {
                $query .= ", " . $image_update;
            }
            $query .=  ", `image_title` = '".$blog['image_title']."', `image_alt` = '".$blog['image_alt']."',
                          `publish_status` = '".$blog['publish']."', `trending_status` = '".$blog['trend']."',
                          `main_status` = '".$blog['main']."' WHERE `id` = " . $id;
        }

        if($result) {
            $lastInsert = self::select('id')
                                ->where('slug', '=', $new_blog['slug'])
                                ->get();
            return $lastInsert[0]->id;
        } else {
            return false;
        }
    }

    public function publishOrUnpublish($id, $publish_status) {
    $id = $this->con->real_escape_string($id);

    $query = "UPDATE `blogs` SET `publish_status` = ".$publish_status." WHERE `id` = " . $id;

    if ($this->con->query($query)) {
        return true;
    } else {
        return false;
    }
}

    public function trendOrUntrend ($id, $trend_status) {
    $id = $this->con->real_escape_string($id);
    $after_update = false;

    if($trend_status == 1) {
        $query = "SELECT `id` FROM `blogs` WHERE trending_status = 1 ORDER BY `id` ASC";
        $result = $this->con->query($query);

        if($result) {
            if($result->num_rows >= 3) {
                $row = $result->fetch_assoc();
                $untrending_id = $row['id'];
                $after_update = true;
            }
        }
    }

    $query = "UPDATE `blogs` SET `trending_status` = ".$trend_status." WHERE `id` = " . $id;

    if ($this->con->query($query)) {
        if($after_update) {
            $query = "UPDATE `blogs` SET `trending_status` = 0 WHERE id = ".$untrending_id;
            if($this->con->query($query)) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    } else {
        return false;
    }
}

    public function mainOrUnmain($id, $main_status) {
    $id = $this->con->real_escape_string($id);
    $after_update = false;

    if($main_status) {
        $after_update = true;
    }

    $query = "UPDATE `blogs` SET `main_status` = ".$main_status." WHERE `id` = " . $id;

    if ($this->con->query($query)) {

        if($after_update) {
            $query = "UPDATE `blogs` SET `main_status` = 0 WHERE `id` <> ".$id." AND `main_status` = 1";
            if($this->con->query($query)) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    } else {
        return false;
    }
}



    public static function findRelatedBlogs($id) {
        return self::select('blogs_related_blogs.related_blog_id','blogs.id','blogs.title','blogs.content','blogs.slug','blogs.created_at',
                    'blogs.image','blogs.page_description')
                    ->rightJoin('blogs_related_blogs','blogs.id', '=', 'blogs_related_blogs.related_blog_id')
                    ->where('blogs_related_blogs.blog_id',  $id)
                    ->toSql();
    }
}
