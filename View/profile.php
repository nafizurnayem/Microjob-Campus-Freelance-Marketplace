<?php
require "../../Model/init.php";

requireLogin();

$user = $userModel->findById(currentUserId());

if (!$user) {
    logoutUser();
    redirectTo(BASE_URL . "/Student/View/login.php");
}

$rating = $userModel->ratingOf($user["user_id"]);

function profileValue($field, $user)
{
    $old = oldInput($field);
    if ($old != "") {
        return $old;
    }
    return $user[$field];
}

$pageTitle = "My profile";
include "header.php";
?>

<h1>My profile</h1>

<div class="col-half">
    <form action="<?php echo BASE_URL; ?>/Student/Controller/profileController.php" method="post"
          onsubmit="return validateProfile()">
        <input type="hidden" name="action" value="update" />

        <fieldset>
            <legend>Profile information</legend>

            <div class="field">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?php echo esc(profileValue("full_name", $user)); ?>" />
                <span class="error" id="full_nameError"></span>
            </div>

            <div class="field">
                <label>Email</label>
                <input type="text" value="<?php echo esc($user["email"]); ?>" disabled />
                <span class="hint">The email is your login name and cannot be changed here.</span>
            </div>

            <div class="field">
                <label for="university">University</label>
                <input type="text" id="university" name="university"
                       value="<?php echo esc(profileValue("university", $user)); ?>" />
                <span class="error" id="universityError"></span>
            </div>

            <div class="field">
                <label for="department">Department</label>
                <input type="text" id="department" name="department"
                       value="<?php echo esc(profileValue("department", $user)); ?>" />
                <span class="error" id="departmentError"></span>
            </div>

            <div class="field">
                <label for="phone">Mobile number</label>
                <input type="text" id="phone" name="phone"
                       value="<?php echo esc(profileValue("phone", $user)); ?>" />
                <span class="error" id="phoneError"></span>
            </div>

            <?php if ($user["role"] == "student") { ?>
                <div class="field">
                    <label for="skills">Skills <span class="muted">(comma separated)</span></label>
                    <input type="text" id="skills" name="skills"
                           value="<?php echo esc(profileValue("skills", $user)); ?>" />
                </div>
            <?php } ?>

            <div class="field">
                <label for="bio">Short bio</label>
                <textarea id="bio" name="bio"><?php echo esc(profileValue("bio", $user)); ?></textarea>
            </div>

            <input type="submit" value="Save changes" />
        </fieldset>
    </form>
</div>

<div class="col-half">
    <div class="card">
        <h3>Account</h3>
        <p class="muted">Role: <span class="badge"><?php echo esc($user["role"]); ?></span></p>
        <p class="muted">Status:
            <span class="badge badge-<?php echo esc($user["status"]); ?>">
                <?php echo esc($user["status"]); ?>
            </span>
        </p>
        <p class="muted">
            Member since <?php echo esc(date("d M Y", strtotime($user["created_at"]))); ?>
        </p>

        <?php if ($user["role"] == "student") { ?>
            <p class="rating">
                <?php
                if ($rating["total"] > 0) {
                    echo esc($rating["average"]) . " / 5";
                } else {
                    echo "<span class='muted'>Not rated yet</span>";
                }
                ?>
                <span class="muted">(<?php echo esc($rating["total"]); ?> review(s))</span>
            </p>
        <?php } ?>

        <p class="spaced">
            <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Student/View/changePassword.php">
                Change password
            </a>
        </p>
    </div>

    <?php if ($user["role"] != "admin") { ?>
        <div class="card">
            <h3>Delete account</h3>
            <p class="muted">
                This removes your profile, your gigs and your order history for good. It cannot be
                undone.
            </p>
            <form action="<?php echo BASE_URL; ?>/Student/Controller/profileController.php" method="post"
                  onsubmit="return confirm('Delete your account permanently? This cannot be undone.');">
                <input type="hidden" name="action" value="delete" />
                <div class="field">
                    <label for="password">Type your password to confirm</label>
                    <input type="password" id="password" name="password" />
                </div>
                <input class="btn-danger" type="submit" value="Delete my account" />
            </form>
        </div>
    <?php } ?>
</div>
<div class="clear"></div>

<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>

<?php
clearOldInput();
include "footer.php";
?>
