<?php
// Orders through their whole life: created, accepted, delivered, completed or cancelled, plus the message thread.
class OrderModel
{
    public $conn;

    function setConnection($conn)
    {
        $this->conn = $conn;
    }

    function findById($orderId)
    {
        $sql = "SELECT o.*, g.title AS gig_title, g.delivery_days,
                       c.full_name AS client_name, c.email AS client_email,
                       s.full_name AS student_name, s.email AS student_email,
                       p.payment_id, p.method AS payment_method, p.txn_id, p.paid_at
                FROM orders o
                JOIN gigs  g ON o.gig_id     = g.gig_id
                JOIN users c ON o.client_id  = c.user_id
                JOIN users s ON o.student_id = s.user_id
                LEFT JOIN payments p ON p.order_id = o.order_id
                WHERE o.order_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    function listByClient($clientId)
    {
        $sql = "SELECT o.*, g.title AS gig_title, s.full_name AS student_name,
                       p.payment_id, p.txn_id,
                       r.review_id
                FROM orders o
                JOIN gigs  g ON o.gig_id     = g.gig_id
                JOIN users s ON o.student_id = s.user_id
                LEFT JOIN payments p ON p.order_id = o.order_id
                LEFT JOIN reviews  r ON r.order_id = o.order_id
                WHERE o.client_id = ?
                ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        $stmt->close();
        return $rows;
    }

    function listByStudent($studentId)
    {
        $sql = "SELECT o.*, g.title AS gig_title, c.full_name AS client_name,
                       p.payment_id, p.txn_id
                FROM orders o
                JOIN gigs  g ON o.gig_id    = g.gig_id
                JOIN users c ON o.client_id = c.user_id
                LEFT JOIN payments p ON p.order_id = o.order_id
                WHERE o.student_id = ?
                ORDER BY o.created_at DESC";
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

    function listAll()
    {
        $sql = "SELECT o.*, g.title AS gig_title,
                       c.full_name AS client_name, s.full_name AS student_name,
                       p.txn_id, p.method AS payment_method
                FROM orders o
                JOIN gigs  g ON o.gig_id     = g.gig_id
                JOIN users c ON o.client_id  = c.user_id
                JOIN users s ON o.student_id = s.user_id
                LEFT JOIN payments p ON p.order_id = o.order_id
                ORDER BY o.created_at DESC";
        $result = $this->conn->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        return $rows;
    }

    function countByStatus($status)
    {
        $sql = "SELECT COUNT(*) AS total FROM orders WHERE status = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row["total"];
    }

    function countForStudentByStatus($studentId, $status)
    {
        $sql = "SELECT COUNT(*) AS total FROM orders WHERE student_id = ? AND status = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $studentId, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row["total"];
    }

    function countForClientByStatus($clientId, $status)
    {
        $sql = "SELECT COUNT(*) AS total FROM orders WHERE client_id = ? AND status = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $clientId, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row["total"];
    }

    function earningsOf($studentId)
    {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN amount_bdt ELSE 0 END), 0) AS earned,
                    COALESCE(SUM(CASE WHEN status IN ('placed','accepted','delivered') THEN amount_bdt ELSE 0 END), 0) AS pending
                FROM orders WHERE student_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    function create($gigId, $clientId, $studentId, $requirement, $amount, $deadline)
    {
        $sql = "INSERT INTO orders
                (gig_id, client_id, student_id, requirement, amount_bdt, deadline, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'placed', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiisds", $gigId, $clientId, $studentId, $requirement, $amount, $deadline);
        $ok = $stmt->execute();
        $newId = $this->conn->insert_id;
        $stmt->close();

        if ($ok) {
            return $newId;
        }
        return 0;
    }

    function acceptByStudent($orderId, $studentId)
    {
        $sql = "UPDATE orders o
                JOIN payments p ON p.order_id = o.order_id
                SET o.status = 'accepted'
                WHERE o.order_id = ? AND o.student_id = ? AND o.status = 'placed'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $orderId, $studentId);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
        return $changed > 0;
    }

    function deliverByStudent($orderId, $studentId)
    {
        $sql = "UPDATE orders SET status = 'delivered'
                WHERE order_id = ? AND student_id = ? AND status = 'accepted'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $orderId, $studentId);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
        return $changed > 0;
    }

    function completeByClient($orderId, $clientId)
    {
        $sql = "UPDATE orders SET status = 'completed', completed_at = NOW()
                WHERE order_id = ? AND client_id = ? AND status = 'delivered'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $orderId, $clientId);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
        return $changed > 0;
    }

    function cancelByClient($orderId, $clientId)
    {
        $sql = "UPDATE orders o
                LEFT JOIN payments p ON p.order_id = o.order_id
                SET o.status = 'cancelled'
                WHERE o.order_id = ? AND o.client_id = ? AND o.status = 'placed'
                  AND p.payment_id IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $orderId, $clientId);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
        return $changed > 0;
    }

    function addMessage($orderId, $senderId, $body)
    {
        $sql = "INSERT INTO messages (order_id, sender_id, body, sent_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $orderId, $senderId, $body);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function listMessages($orderId)
    {
        $sql = "SELECT m.*, u.full_name AS sender_name, u.role AS sender_role
                FROM messages m
                JOIN users u ON m.sender_id = u.user_id
                WHERE m.order_id = ?
                ORDER BY m.sent_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        $stmt->close();
        return $rows;
    }
}
