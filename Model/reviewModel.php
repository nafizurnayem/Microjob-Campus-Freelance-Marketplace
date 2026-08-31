<?php
// One review per completed order.
class ReviewModel
{
    public $conn;

    function setConnection($conn)
    {
        $this->conn = $conn;
    }

    function findByOrder($orderId)
    {
        $sql = "SELECT * FROM reviews WHERE order_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    function listByStudent($studentId)
    {
        $sql = "SELECT r.*, c.full_name AS client_name, g.title AS gig_title
                FROM reviews r
                JOIN users  c ON r.client_id = c.user_id
                JOIN orders o ON r.order_id  = o.order_id
                JOIN gigs   g ON o.gig_id    = g.gig_id
                WHERE r.student_id = ?
                ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        $stmt->close();
        return $rows;
    }

    function countForStudent($studentId)
    {
        $sql = "SELECT COUNT(*) AS total FROM reviews WHERE student_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row["total"];
    }

    function create($orderId, $clientId, $studentId, $rating, $comment)
    {
        $sql = "INSERT INTO reviews (order_id, client_id, student_id, rating, comment, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiis", $orderId, $clientId, $studentId, $rating, $comment);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
