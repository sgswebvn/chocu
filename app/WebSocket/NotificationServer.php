<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Models\Notification;
use App\Models\ChatModel;

class NotificationServer implements MessageComponentInterface
{
    private $clients;
    private $notificationModel;
    private $chatModel;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->notificationModel = new Notification();
        $this->chatModel = new ChatModel();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $params);
        $userId = $params['user_id'] ?? 'guest';
        $conn->userId = $userId;
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId}) - User ID: $userId\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if (!$data) {
            echo "Invalid message data: " . $msg . "\n";
            return;
        }

        $timestamp = date('Y-m-d H:i:s');

        if (isset($data['type']) && in_array($data['type'], ['order', 'review', 'report', 'cart', 'favorite', 'product', 'auth'])) {
            // Kiểm tra thông báo trùng lặp
            if ($data['type'] === 'order' && isset($data['order_id'])) {
                $existingNotification = $this->notificationModel->findByOrderId($data['order_id'], $data['target_user_id']);
                if ($existingNotification) {
                    echo "Notification for order {$data['order_id']} already exists for user {$data['target_user_id']}\n";
                    return;
                }
            }

            $this->notificationModel->create(
                $data['target_user_id'],
                $data['type'],
                $data['title'] ?? 'Thông báo mới',
                $data['message'] ?? '',
                $data['link'] ?? null,
                $data['order_id'] ?? null
            );

            foreach ($this->clients as $client) {
                if ($client->userId == $data['target_user_id']) {
                    $client->send(json_encode([
                        'type' => $data['type'],
                        'order_id' => $data['order_id'] ?? null,
                        'title' => $data['title'] ?? 'Thông báo mới',
                        'message' => $data['message'] ?? '',
                        'status' => $data['status'] ?? null,
                        'link' => $data['link'] ?? '#',
                        'timestamp' => $timestamp
                    ]));
                    echo "Sent {$data['type']} notification to user {$client->userId}\n";
                }
            }
        } elseif (isset($data['sender_id'], $data['receiver_id'], $data['product_id'], $data['message'], $data['type']) && $data['type'] === 'chat') {
            $sellerId = $this->chatModel->getSellerIdByProductId($data['product_id']);
            if (!$sellerId) {
                echo "Invalid seller_id for product {$data['product_id']}\n";
                return;
            }

            $result = $this->chatModel->saveChat($data['sender_id'], $data['receiver_id'], $data['product_id'], $data['message']);
            if (!$result['success']) {
                echo "Failed to save chat: {$result['message']}\n";
                return;
            }

            $messageData = [
                'type' => 'chat',
                'sender_id' => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'product_id' => $data['product_id'],
                'message' => $data['message'],
                'timestamp' => $result['timestamp'],
                'sender_role' => ($data['sender_id'] == $sellerId) ? 'seller' : 'buyer',
                'sender_name' => $data['sender_name'] ?? 'Người dùng'
            ];

            foreach ($this->clients as $client) {
                if ($client->userId == $data['receiver_id']) {
                    $client->send(json_encode($messageData));
                    echo "Sent chat message to user {$client->userId}\n";
                }
            }
        } else {
            echo "Invalid message format: " . $msg . "\n";
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} disconnected.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    public static function sendNotification($targetUserId, $type, $data)
    {
        try {
            $client = new \WebSocket\Client('ws://localhost:9000', ['timeout' => 5]);
            $message = json_encode(array_merge([
                'type' => $type,
                'target_user_id' => $targetUserId,
                'timestamp' => date('Y-m-d H:i:s')
            ], $data));
            $client->send($message);
            $client->close();
            return true;
        } catch (\Exception $e) {
            error_log("WebSocket send error: " . $e->getMessage());
            return false;
        }
    }

    public static function sendChatMessage($senderId, $receiverId, $productId, $message, $data)
    {
        try {
            $client = new \WebSocket\Client('ws://localhost:9000', ['timeout' => 5]);
            $messageData = array_merge([
                'type' => 'chat',
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'product_id' => $productId,
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s')
            ], $data);
            $client->send(json_encode($messageData));
            $client->close();
            return true;
        } catch (\Exception $e) {
            error_log("WebSocket send error: " . $e->getMessage());
            return false;
        }
    }
}
