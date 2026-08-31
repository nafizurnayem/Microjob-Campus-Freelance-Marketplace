<?php
// Payment records for the simulated checkout.
class PaymentModel
{
    public $conn;

    function setConnection($conn)
    {
        $this->conn = $conn;
    }

    function makeTransactionId($method, $orderId)
    {
        $prefix = "TXN";
        switch ($method) {
            case "bkash":
                $prefix = "BKS";
                break;
            case "nagad":
                $prefix = "NGD";
                break;
            case "bank":
                $prefix = "BNK";
                break;
            case "card":
                $prefix = "CRD";
                break;
        }
        return $prefix . "-" . time() . "-" . $orderId;
    }

    function lastFourDigits($accountNumber)
    {
        $digits = str_replace(" ", "", trim($accountNumber));
        return substr($digits, strlen($digits) - 4, 4);
    }

    function findByOrder($orderId)
    {
        $sql = "SELECT * FROM payments WHERE order_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    function isOrderPaid($orderId)
    {
        $row = $this->findByOrder($orderId);
        if ($row) {
            return true;
        }
        return false;
    }

    function listByClient($clientId)
    {
        $sql = "SELECT p.*, o.order_id, g.title AS gig_title, s.full_name AS student_name
                FROM payments p
                JOIN orders o ON p.order_id  = o.order_id
                JOIN gigs   g ON o.gig_id    = g.gig_id
                JOIN users  s ON o.student_id = s.user_id
                WHERE o.client_id = ?
                ORDER BY p.paid_at DESC";
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

    function listAll()
    {
        $sql = "SELECT p.*, o.order_id, g.title AS gig_title,
                       c.full_name AS client_name, s.full_name AS student_name
                FROM payments p
                JOIN orders o ON p.order_id   = o.order_id
                JOIN gigs   g ON o.gig_id     = g.gig_id
                JOIN users  c ON o.client_id  = c.user_id
                JOIN users  s ON o.student_id = s.user_id
                ORDER BY p.paid_at DESC";
        $result = $this->conn->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        return $rows;
    }

    function totalCollected()
    {
        $sql = "SELECT COALESCE(SUM(amount_bdt), 0) AS total FROM payments WHERE status = 'paid'";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row["total"];
    }

    function totalByMethod()
    {
        $sql = "SELECT method, COUNT(*) AS orders_count, COALESCE(SUM(amount_bdt), 0) AS total
                FROM payments WHERE status = 'paid'
                GROUP BY method ORDER BY total DESC";
        $result = $this->conn->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        return $rows;
    }

    function create($orderId, $method, $accountLast4, $txnId, $amount)
    {
        $sql = "INSERT INTO payments
                (order_id, method, account_last4, txn_id, amount_bdt, status, paid_at)
                VALUES (?, ?, ?, ?, ?, 'paid', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssd", $orderId, $method, $accountLast4, $txnId, $amount);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
