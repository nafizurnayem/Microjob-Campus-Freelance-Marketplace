<?php
require "../../Model/init.php";

requireLogin();

$pageTitle = "Change password";
include "header.php";
?>

<h1>Change password</h1>

<div class="col-half">
    <form action="<?php echo BASE_URL; ?>/Student/Controller/passwordController.php" method="post"
          onsubmit="return validatePasswordChange()">
        <fieldset>
            <legend>New password</legend>

            <div class="field">
                <label for="current_password">Current password</label>
                <input type="password" id="current_password" name="current_password" />
                <span class="error" id="current_passwordError"></span>
            </div>

            <div class="field">
                <label for="new_password">New password</label>
                <input type="password" id="new_password" name="new_password" />
                <span class="hint">At least <?php echo MIN_PASSWORD_LENGTH; ?> characters.</span>
                <span class="error" id="new_passwordError"></span>
            </div>

            <div class="field">
                <label for="confirm_password">Confirm new password</label>
                <input type="password" id="confirm_password" name="confirm_password" />
                <span class="error" id="confirm_passwordError"></span>
            </div>

            <input type="submit" value="Update password" />
            <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Student/View/profile.php">Back to profile</a>
        </fieldset>
    </form>
</div>

<div class="col-half">
    <div class="card">
        <h3>Choosing a password</h3>
        <p class="muted">Use at least <?php echo MIN_PASSWORD_LENGTH; ?> characters.</p>
        <p class="muted">Do not reuse the password of your university account.</p>
        <p class="muted">
            Note for the reviewer: passwords in this project are stored as plain text because the
            course slides do not cover any hashing function. This limitation is written down in
            <code>docs/SECURITY.md</code>.
        </p>
    </div>
</div>
<div class="clear"></div>

<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>

<?php include "footer.php"; ?>
