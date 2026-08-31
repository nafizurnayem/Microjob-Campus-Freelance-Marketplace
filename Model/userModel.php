<?php
// User accounts. Passwords are stored as plain text: no hashing function appears in the course slides.
class User
{
    public $fullName = "";
    public $email = "";
    public $password = "";
    public $role = "client";
    public $university = "";
    public $department = "";
    public $phone = "";
    public $bio = "";
    public $skills = "";

    function setBasic($fullName, $email, $password, $role)
    {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    function setCampus($university, $department, $phone)
    {
        $this->university = $university;
        $this->department = $department;
        $this->phone = $phone;
    }

    function setProfile($bio, $skills)
    {
        $this->bio = $bio;
        $this->skills = $skills;
    }

    function skillList()
    {
        if (trim($this->skills) == "") {
            return array();
        }
        return explode(",", $this->skills);
    }
}

class UserModel
{
    public $conn;

    function setConnection($conn)
    {
        $this->conn = $conn;
    }

    function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    function findById($userId)
    {
        $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    function emailExists($email)
    {
        $row = $this->findByEmail($email);
        if ($row) {
            return true;
        }
        return false;
    }

    function attemptLogin($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return array("ok" => false, "reason" => "invalid", "user" => null);
        }
        // Plain text comparison, matching how the password was stored.
        if ($user["password"] != $password) {
            return array("ok" => false, "reason" => "invalid", "user" => null);
        }
        if ($user["status"] == "suspended") {
            return array("ok" => false, "reason" => "suspended", "user" => null);
        }

        return array("ok" => true, "reason" => "", "user" => $user);
    }

    function listByRole($role)
    {
        $sql = "SELECT user_id, full_name, email, role, university, department, phone, status, created_at
                FROM users WHERE role = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        $stmt->close();
        return $rows;
    }

    function listAllExceptAdmin()
    {
        $sql = "SELECT user_id, full_name, email, role, university, department, phone, status, created_at
                FROM users WHERE role <> 'admin' ORDER BY created_at DESC";
        $result = $this->conn->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            array_push($rows, $row);
        }
        return $rows;
    }

    function countByRole($role)
    {
        $sql = "SELECT COUNT(*) AS total FROM users WHERE role = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row["total"];
    }

    function create($user)
    {
        $sql = "INSERT INTO users
                (full_name, email, password, role, university, department, phone, bio, skills, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssssssss",
            $user->fullName,
            $user->email,
            $user->password,
            $user->role,
            $user->university,
            $user->department,
            $user->phone,
            $user->bio,
            $user->skills
        );
        $ok = $stmt->execute();
        $newId = $this->conn->insert_id;
        $stmt->close();

        if ($ok) {
            return $newId;
        }
        return 0;
    }

    function updateProfile($userId, $fullName, $university, $department, $phone, $bio, $skills)
    {
        $sql = "UPDATE users
                SET full_name = ?, university = ?, department = ?, phone = ?, bio = ?, skills = ?
                WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssi", $fullName, $university, $department, $phone, $bio, $skills, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function changePassword($userId, $newPassword)
    {
        $sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $newPassword, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function setStatus($userId, $status)
    {
        $sql = "UPDATE users SET status = ? WHERE user_id = ? AND role <> 'admin'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function deleteAccount($userId)
    {
        $sql = "DELETE FROM users WHERE user_id = ? AND role <> 'admin'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    function ratingOf($studentId)
    {
        $sql = "SELECT COUNT(*) AS total, AVG(rating) AS average
                FROM reviews WHERE student_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $total = $row["total"];
        $average = 0;
        if ($total > 0) {
            $average = round($row["average"], 1);
        }

        return array("total" => $total, "average" => $average);
    }
}
