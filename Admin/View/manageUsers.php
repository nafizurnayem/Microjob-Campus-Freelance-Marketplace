<?php
require "../../Model/init.php";

requireRole("admin");

$users = $userModel->listAllExceptAdmin();

$pageTitle = "Manage users";
include "../../Student/View/header.php";
?>

<h1>Manage users</h1>
<p class="muted">
    A suspended account cannot log in, and its gigs disappear from the public catalogue.
</p>

<?php
$total = count($users);
if ($total == 0) {
    ?>
    <div class="card">
        <p class="muted">No student or client has registered yet.</p>
    </div>
    <?php
} else {
    ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>University</th>
            <th>Phone</th>
            <th>Joined</th>
            <th>Status</th>
            <th></th>
        </tr>
        <?php
        for ($i = 0; $i < $total; $i++) {
            $user = $users[$i];
            ?>
            <tr>
                <td><?php echo esc($user["full_name"]); ?></td>
                <td><?php echo esc($user["email"]); ?></td>
                <td><span class="badge"><?php echo esc($user["role"]); ?></span></td>
                <td>
                    <?php echo esc($user["department"]); ?><br />
                    <span class="muted"><?php echo esc($user["university"]); ?></span>
                </td>
                <td><?php echo esc($user["phone"]); ?></td>
                <td><?php echo esc(date("d M Y", strtotime($user["created_at"]))); ?></td>
                <td>
                    <span class="badge badge-<?php echo esc($user["status"]); ?>">
                        <?php echo esc($user["status"]); ?>
                    </span>
                </td>
                <td>
                    <form action="<?php echo BASE_URL; ?>/Admin/Controller/adminController.php" method="post">
                        <input type="hidden" name="action" value="user_status" />
                        <input type="hidden" name="user_id" value="<?php echo esc($user["user_id"]); ?>" />
                        <?php if ($user["status"] == "active") { ?>
                            <input type="hidden" name="status" value="suspended" />
                            <input class="btn-small btn-danger" type="submit" value="Suspend" />
                        <?php } else { ?>
                            <input type="hidden" name="status" value="active" />
                            <input class="btn-small" type="submit" value="Reactivate" />
                        <?php } ?>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<?php include "../../Student/View/footer.php"; ?>
