<?php

namespace App\Models\Partners;

use App\Config\Database;

class Review
{
    public $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function findByUser($sellerId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.username
                FROM seller_ratings r
                JOIN users u ON r.buyer_id = u.id
                WHERE r.seller_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$sellerId]);
            $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("Fetched " . count($reviews) . " seller reviews for seller ID: $sellerId");
            return $reviews;
        } catch (\PDOException $e) {
            error_log("Error fetching seller reviews: " . $e->getMessage());
            return [];
        }
    }

  public function createSellerReview($sellerId, $buyerId, $rating, $comment)
    {
        try {
            // Kiểm tra xem người mua đã đánh giá shop này chưa (Dựa trên UNIQUE KEY: seller_id, buyer_id)
            $stmt = $this->db->prepare("SELECT id FROM seller_ratings WHERE seller_id = ? AND buyer_id = ?");
            $stmt->execute([$sellerId, $buyerId]);
            if ($stmt->fetch()) {
                error_log("Seller Review failed: Buyer ID $buyerId already rated seller ID $sellerId.");
                return false; // Đã đánh giá rồi
            }

            // Giả định order_id = 0 cho đánh giá tổng quát. Cần cho phép NULL hoặc DEFAULT 0 trong DB
            $orderIdPlaceholder = 0; 
            
            $stmt = $this->db->prepare("
                INSERT INTO seller_ratings (order_id, seller_id, buyer_id, rating, comment, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $result = $stmt->execute([$orderIdPlaceholder, $sellerId, $buyerId, $rating, $comment]);
            
            if ($result) {
                 error_log("Created seller review for seller ID: $sellerId by buyer ID: $buyerId");
            } else {
                 error_log("Failed to create seller review (DB execution error).");
            }
            return $result;
        } catch (\PDOException $e) {
            error_log("Error creating seller review: " . $e->getMessage());
            return false;
        }
    }

    public function reply($reviewId, $sellerId, $reply)
    {
        try {
            // Kiểm tra xem review có tồn tại và thuộc shop không
            $stmt = $this->db->prepare("SELECT id, reply FROM seller_ratings WHERE id = ? AND seller_id = ?");
            $stmt->execute([$reviewId, $sellerId]);
            $review = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$review) {
                error_log("Reply failed: Review ID $reviewId not found for seller ID: $sellerId");
                return false;
            }
            if ($review['reply']) {
                error_log("Reply failed: Review ID $reviewId already has a reply for seller ID: $sellerId");
                return false;
            }

            $stmt = $this->db->prepare("
                UPDATE seller_ratings 
                SET reply = ?, updated_at = NOW() 
                WHERE id = ? AND seller_id = ?
            ");
            $result = $stmt->execute([$reply, $reviewId, $sellerId]);
            if ($result) {
                error_log("Replied to seller review ID: $reviewId for seller ID: $sellerId");
            } else {
                error_log("Failed to update seller review ID: $reviewId for seller ID: $sellerId - No rows affected");
            }
            return $result;
        } catch (\PDOException $e) {
            error_log("Error replying to seller review ID: $reviewId - " . $e->getMessage());
            return false;
        }
    }

    public function findUserReviews($ratedId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.username
                FROM userRating r
                JOIN users u ON r.rater_id = u.id
                WHERE r.rated_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$ratedId]);
            $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("Fetched " . count($reviews) . " user reviews for rated ID: $ratedId");
            return $reviews;
        } catch (\PDOException $e) {
            error_log("Error fetching user reviews: " . $e->getMessage());
            return [];
        }
    }

    public function createUserReview($raterId, $ratedId, $rating, $comment)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO userRating (rater_id, rated_id, rating, comment, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $result = $stmt->execute([$raterId, $ratedId, $rating, $comment]);
            error_log("Created user review for rated ID: $ratedId by rater ID: $raterId");
            return $result;
        } catch (\PDOException $e) {
            error_log("Error creating user review: " . $e->getMessage());
            return false;
        }
    }

    public function countReviews($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM seller_ratings WHERE seller_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            error_log("Counted " . ($result['total'] ?? 0) . " seller reviews for seller ID: $userId");
            return $result['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error counting seller reviews for user ID: $userId - " . $e->getMessage());
            return 0;
        }
    }
}
