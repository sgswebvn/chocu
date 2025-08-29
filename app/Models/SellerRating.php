        <?php

        namespace App\Models;

        use App\Config\Database;

        class SellerRating
        {
            private $db;
            public function __construct()
            {
                $this->db = (new Database())->getConnection();
            }
            public function create($sellerId, $buyerId, $rating, $comment)
            {
                $stmt = $this->db->prepare("INSERT INTO seller_ratings (seller_id, buyer_id, rating, comment) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([$sellerId, $buyerId, $rating, $comment]);
                if ($result) {
                    $this->updateSellerAverageRating($sellerId);
                }
                return $result;
            }
            public function getBySeller($sellerId)
            {
                $stmt = $this->db->prepare("SELECT * FROM seller_ratings WHERE seller_id = ? ORDER BY created_at DESC");
                $stmt->execute([$sellerId]);
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            private function updateSellerAverageRating($sellerId)
            {
                $stmt = $this->db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count FROM seller_ratings WHERE seller_id = ?");
                $stmt->execute([$sellerId]);
                $data = $stmt->fetch(\PDO::FETCH_ASSOC);
                $stmt = $this->db->prepare("UPDATE users SET average_rating = ?, rating_count = ? WHERE id = ?");
                return $stmt->execute([$data['avg_rating'], $data['rating_count'], $sellerId]);
            }
        }
