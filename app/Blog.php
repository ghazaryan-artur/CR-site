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
public function getByCount($limit) {

    $blogs = array();

    $query = "SELECT `title`, `slug`, `image`
                  FROM `blogs` WHERE `publish_status` ORDER BY `id` DESC LIMIT " . $limit;
    $result = $this->con->query($query);

    if(!$result) {
        return $blogs;
    }

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($blogs, $row);
        }
    }
    return $blogs;
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

    public function getAllBlogsCount($publish = false) {
    $where = "";
    if($publish) {
        $where = " WHERE `publish_status` = 1 AND `main_status` <> 1";
    }

    $query = "SELECT count(`id`) as `blogs` FROM `blogs` " . $where;
    $result = $this->con->query($query);

    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['blogs'];
    } else {
        return 0;
    }
}

    public function findBySlug($slug) {
        $blog = array();
        $slug = $this->con->real_escape_string($slug);

        $query = "SELECT * FROM `blogs` where `slug` = '".$slug."'";
        $result = $this->con->query($query);
        if($result->num_rows > 0) {
            $blog = $result->fetch_assoc();
        }
        return $blog;
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

    public function  getRecentBlogs($publised = false, $limit = 6, $offset = 0) {
    $offset = $this->con->real_escape_string($offset);
    $blogs = array();
    $where = "";

    if($publised) {
        $where = " WHERE `publish_status` = 1 AND `main_status` <> 1";
    }

    $query = "SELECT `id`, `title`, `slug`, `image`, `content`, `page_description`,
                  `publish_status`, `trending_status`, `main_status`, `created_at`
                  FROM `blogs` ".$where." ORDER BY `id` DESC LIMIT " . $limit . " OFFSET " . $offset;

    $result = $this->con->query($query);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($blogs, $row);
        }
    }
    return $blogs;
}

    public function getTrendingBlogs($publised = false, $except_id = false) {
    $blogs = array();
    $where = "";
    $second_where = "";

    if($publised) {
        $where = " WHERE `publish_status` = 1 AND `trending_status` = 1";
    }
    if($except_id != false) {
        $second_where = ($where == "") ? " WHERE `id` <> " . $except_id : " AND `id` <>" . $except_id;
    }

    $query = "SELECT `id`, `title`, `slug`, `content`, `image`, `page_description`, `created_at`
                  FROM `blogs` ".$where.$second_where." ORDER BY `id` DESC LIMIT 3";

    $result = $this->con->query($query);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($blogs, $row);
        }
    }
    return $blogs;
}

    public function createOrUpdate($blog, $action, $id = null, $related = false) {
    $date = date('Y-m-d H:i:s');

    $blog['pageTitle']        = trim($this->con->real_escape_string($blog['pageTitle']));
    $blog['pageDescription']  = trim($this->con->real_escape_string($blog['pageDescription']));
    $blog['pageKeywords']     = "";

    $blog['bannerTitle']      = trim($this->con->real_escape_string($blog['bannerTitle']));
    $blog['bannerText']       = trim($this->con->real_escape_string($blog['bannerText']));
    $blog['bannerSlug']       = trim($this->con->real_escape_string($blog['bannerSlug']));
    $blog['bannerSlugText']   = trim($this->con->real_escape_string($blog['bannerSlugText']));

    $blog['title']            = trim($this->con->real_escape_string($blog['title']));
    $blog['slug']             = trim($this->con->real_escape_string($blog['slug']));
    $blog['content']          = trim($this->con->real_escape_string($blog['content']));
    $blog['publish']          = trim($this->con->real_escape_string($blog['publish']));
    $blog['trend']            = trim($this->con->real_escape_string($blog['trend']));
    $blog['main']             = trim($this->con->real_escape_string($blog['main']));

    $blog['image_title']      = trim($this->con->real_escape_string($blog['image_title']));
    $blog['image_alt']        = trim($this->con->real_escape_string($blog['image_alt']));

    if($blog['main']) {
        $query = "UPDATE `blogs` SET `main_status` = 0 WHERE `main_status` = 1";
        $update_check = $this->con->query($query);
        if(!$update_check) {
            return false;
        }
    }

    if($blog['trend']) {
        $query = "SELECT `id` FROM `blogs` WHERE trending_status = 1 ORDER BY `id` ASC";
        $result = $this->con->query($query);

        if($result) {
            if($result->num_rows >= 3) {
                $row = $result->fetch_assoc();
                $untrending_id = $row['id'];

                $query = "UPDATE `blogs` SET `trending_status` = 0 WHERE id = ".$untrending_id;
                $update_check = $this->con->query($query);
                if(!$update_check) {
                    return false;
                }
            }
        }
    }

    if($action == "create") {
        $query = "INSERT INTO `blogs`
                   (`page_title`,`page_description`, `page_keywords`,
                    `banner_title`, `banner_text`, `banner_slug`, `banner_slug_text`,
                    `title`, `slug`, `content`, `image`, `image_title`, `image_alt`,
                    `publish_status`, `trending_status`, `main_status`, `created_at`) VALUES
                   ('".$blog['pageTitle']."', '".$blog['pageDescription']."', '".$blog['pageKeywords']."',
                    '".$blog['bannerTitle']."', '".$blog['bannerText']."', '".$blog['bannerSlug']."', '".$blog['bannerSlugText']."',
                    '".$blog['title']."', '".$blog['slug']."', '".$blog['content']."',
                    '".$blog['image']."', '".$blog['image_title']."', '".$blog['image_alt']."',
                    '".$blog['publish']."', '".$blog['trend']."',
                    '".$blog['main']."', '".$date."')";
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

    if($this->con->query($query)) {
        if($related) {
            $query = "SELECT `id` as `last_blog_id` from `blogs` ORDER BY `id` DESC";
            $result = $this->con->query($query);

            if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['last_blog_id'];
            }
        }
        return true;
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

    public function addRelatedBlogs ($blog_id, $related_blogs_id) {
    $query = "DELETE FROM `blogs_related_blogs` WHERE `blog_id` = ".$blog_id;
    $result = $this->con->query($query);

    if($related_blogs_id != '') {
        $related_blogs_id_array = explode(",", $related_blogs_id);
        $insert = '';
        $counter = 1;
        $last_loop = count($related_blogs_id_array);

        foreach ($related_blogs_id_array as $related_blog) {
            if($last_loop == 1) {
                $insert .= '(`blog_id`, `related_blog_id`) VALUES ('.$blog_id.', '.$related_blog.')';
            } else {
                if($counter >= $last_loop) {
                    $insert .= '('.$blog_id.', '.$related_blog.')';
                } else {
                    $insert .= '(`blog_id`, `related_blog_id`) VALUES ('.$blog_id.', '.$related_blog.'),';
                }
            }
            $counter++;
        }

        $query = "INSERT INTO `blogs_related_blogs` ". $insert;
        $result = $this->con->query($query);
    }

    if($result) {
        return true;
    } else {
        return false;
    }
}

    public function findRelatedBlogs($id) {
    $related_blogs = array();

    $query = "SELECT `related_blog_id`, `blogs`.`title`, `blogs`.`content`, `blogs`.`slug`,
                  `blogs`.`created_at`, `blogs`.`image`, `blogs`.`page_description`
                  FROM `blogs_related_blogs`
                  LEFT JOIN `blogs` ON blogs.id = blogs_related_blogs.related_blog_id
                  WHERE `blogs_related_blogs`.`blog_id` = " . $id;

    $result = $this->con->query($query);

    if($result) {
        if($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($related_blogs, $row);
            }
        }
    }
    return $related_blogs;
}
}
