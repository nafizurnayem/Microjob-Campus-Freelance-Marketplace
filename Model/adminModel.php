<?php
// Withdrawal requests and platform wide totals for the admin pages.
class AdminModel
{
    public $conn;

    function setConnection($conn)
    {
        $this->conn = $conn;
    }

    function createWithdrawal($studentId, $amount, $method, $accountNo)
    {
        $sql = "INSERT INTO withdrawals (student_id, amount_bdt, method, account_no, status, requested_at)
                VALUES (?, ?, ?, ?, 'requested', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("idss", $studentId, $amount, $method, $accountNo);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function withdrawalsOf($studentId)
    {
        $sql = "SELECT * FROM withdrawals WHERE student_id = ? ORDER BY requested_at DESC";
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

    function withdrawnTotal($studentId)
    {
        $sql = "SELECT COALESCE(SUM(amount_bdt), 0) AS total FROM withdrawals WHERE student_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row["total"];
    }

    function allWithdrawals()
    {
        $sql = "SELECT w.*, u.full_name AS student_name, u.email AS student_email
                FROM withdrawals w
                JOIN users u ON w.student_id = u.user_id
                ORDER BY w.requested_at DESC";
        $result = $this->conn->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        return $rows;
    }

    function markWithdrawalPaid($withdrawalId)
    {
        $sql = "UPDATE withdrawals SET status = 'paid' WHERE withdrawal_id = ? AND status = 'requested'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $withdrawalId);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
        return $changed > 0;
    }

    function platformTotals()
    {
        $totals = array();

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'student'");
        $row = $result->fetch_assoc();
        $totals["students"] = $row["total"];

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'client'");
        $row = $result->fetch_assoc();
        $totals["clients"] = $row["total"];

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM gigs");
        $row = $result->fetch_assoc();
        $totals["gigs"] = $row["total"];

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM gigs WHERE status = 'pending'");
        $row = $result->fetch_assoc();
        $totals["pending_gigs"] = $row["total"];

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM orders");
        $row = $result->fetch_assoc();
        $totals["orders"] = $row["total"];

        $result = $this->conn->query("SELECT COALESCE(SUM(amount_bdt), 0) AS total FROM payments WHERE status = 'paid'");
        $row = $result->fetch_assoc();
        $totals["revenue"] = $row["total"];

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM withdrawals WHERE status = 'requested'");
        $row = $result->fetch_assoc();
        $totals["pending_withdrawals"] = $row["total"];

        return $totals;
    }
}
