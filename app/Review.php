<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public static function findCarouselReviews() {
        return self::all()->where('carousel_status',  1)->sortBy('id');
    }

    public function createOrUpdate($data, $action) {
        $date = date('Y-m-d H:i:s');

        $name      = trim($this->con->real_escape_string($data['name']));
        $position  = trim($this->con->real_escape_string($data['position']));
        $company   = trim($this->con->real_escape_string($data['company']));
        $review    = trim($this->con->real_escape_string($data['review']));

        if($action == "create") {
            $query = "INSERT INTO `reviews` (`client_name`, `client_review`, `client_position`, `client_company_name`,
                      `client_image`, `created_at`) VALUES ('".$name."', '".$review."', '".$position."', '".$company."',
                      '".$data['image']."', '".$date."')";
        } else {
            $query = "UPDATE `reviews` SET `client_name` = '".$name."', `client_review` = '".$review."',
                      `client_position` = '".$position."', `client_company_name` = '".$company."',
                      `client_image` = '".$data['image']."' WHERE `id` = " . $data['id'];
        }

        if($this->con->query($query)) {
            return true;
        } else {
            return false;
        }
    }

    public function addOrRemoveCarousel ($id, $status) {
        $id = $this->con->real_escape_string($id);
        $after_update = false;

        if($status == 1) {
            $query = "SELECT `id` FROM `reviews` WHERE `carousel_status` = 1 ORDER BY `id` ASC";
            $result = $this->con->query($query);

            if($result) {
                if($result->num_rows >= 5) {
                    $row = $result->fetch_assoc();
                    $removingFromCarouselElement = $row['id'];
                    $after_update = true;
                }
            }
        }

        $query = "UPDATE `reviews` SET `carousel_status` = ".$status." WHERE `id` = " . $id;

        if ($this->con->query($query)) {
            if($after_update) {
                $query = "UPDATE `reviews` SET `carousel_status` = 0 WHERE `id` = ".$removingFromCarouselElement;
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
}
