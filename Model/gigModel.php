<?php
// Gig and category queries. Every statement is prepared.
class GigModel
{
    public $conn;

    function setConnection($conn)
    {
        $this->conn = $conn;
    }

    function allCategories()
    {
        $sql = "SELECT category_id, name FROM categories ORDER BY name ASC";
        $result = $this->conn->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        return $rows;
    }

    function categoryExists($categoryId)
    {
        $sql = "SELECT category_id FROM categories WHERE category_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            return true;
        }
        return false;
    }

    function addCategory($name)
    {
        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function deleteCategory($categoryId)
    {
        $sql = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function findById($gigId)
    {
        $sql = "SELECT g.*, c.name AS category_name, u.full_name AS student_name,
                       u.university, u.department, u.bio, u.skills, u.status AS student_status
                FROM gigs g
                JOIN categories c ON g.category_id = c.category_id
                JOIN users u      ON g.student_id  = u.user_id
                WHERE g.gig_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $gigId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    function listByStudent($studentId)
    {
        $sql = "SELECT g.*, c.name AS category_name
                FROM gigs g
                JOIN categories c ON g.category_id = c.category_id
                WHERE g.student_id = ?
                ORDER BY g.created_at DESC";
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

    function listByStatus($status)
    {
        $sql = "SELECT g.*, c.name AS category_name, u.full_name AS student_name
                FROM gigs g
                JOIN categories c ON g.category_id = c.category_id
                JOIN users u      ON g.student_id  = u.user_id
                WHERE g.status = ?
                ORDER BY g.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        $stmt->close();
        return $rows;
    }

    function search($keyword, $categoryId, $maxPrice, $sortBy)
    {
        $sql = "SELECT g.gig_id, g.title, g.description, g.price_bdt, g.delivery_days,
                       g.student_id, c.name AS category_name, u.full_name AS student_name
                FROM gigs g
                JOIN categories c ON g.category_id = c.category_id
                JOIN users u      ON g.student_id  = u.user_id
                WHERE g.status = 'approved' AND u.status = 'active'
                  AND (g.title LIKE ? OR g.description LIKE ?)
                  AND (? = 0 OR g.category_id = ?)
                  AND (? = 0 OR g.price_bdt <= ?)";

        switch ($sortBy) {
            case "price_low":
                $sql = $sql . " ORDER BY g.price_bdt ASC";
                break;
            case "price_high":
                $sql = $sql . " ORDER BY g.price_bdt DESC";
                break;
            case "fastest":
                $sql = $sql . " ORDER BY g.delivery_days ASC";
                break;
            default:
                $sql = $sql . " ORDER BY g.created_at DESC";
        }

        $like = "%" . trim($keyword) . "%";

        $category = 0;
        if (isDigitsOnly($categoryId)) {
            $category = round($categoryId);
        }

        $price = 0;
        if (is_numeric($maxPrice) && $maxPrice > 0) {
            $price = round($maxPrice, 2);
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiidd", $like, $like, $category, $category, $price, $price);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        $stmt->close();
        return $rows;
    }

    function countByStatus($status)
    {
        $sql = "SELECT COUNT(*) AS total FROM gigs WHERE status = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row["total"];
    }

    function create($studentId, $categoryId, $title, $description, $price, $deliveryDays)
    {
        $sql = "INSERT INTO gigs
                (student_id, category_id, title, description, price_bdt, delivery_days, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissdi", $studentId, $categoryId, $title, $description, $price, $deliveryDays);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function update($gigId, $studentId, $categoryId, $title, $description, $price, $deliveryDays)
    {
        $sql = "UPDATE gigs
                SET category_id = ?, title = ?, description = ?, price_bdt = ?, delivery_days = ?,
                    status = 'pending'
                WHERE gig_id = ? AND student_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issdiii", $categoryId, $title, $description, $price, $deliveryDays, $gigId, $studentId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function deleteOwn($gigId, $studentId)
    {
        $sql = "DELETE FROM gigs WHERE gig_id = ? AND student_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $gigId, $studentId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function setStatus($gigId, $status)
    {
        $sql = "UPDATE gigs SET status = ? WHERE gig_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $gigId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function deleteAny($gigId)
    {
        $sql = "DELETE FROM gigs WHERE gig_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $gigId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
