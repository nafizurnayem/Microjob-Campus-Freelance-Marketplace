<?php
require "../../Model/init.php";

if (isLoggedIn()) {
    redirectTo(dashboardFor(currentRole()));
}

$pageTitle = "Create an account";
include "header.php";
?>

<h1>Create your MicroJob account</h1>
<p class="muted">
    Register as a <strong>student</strong> to sell your skills, or as a <strong>client</strong>
    to hire other students.
</p>

<form action="<?php echo BASE_URL; ?>/Student/Controller/registerController.php" method="post"
      onsubmit="return validateRegister()">

    <div class="col-half">
        <fieldset>
            <legend>Account details</legend>

            <div class="field">
                <label for="role">I want to</label>
                <select id="role" name="role">
                    <option value="">-- choose --</option>
                    <option value="student" <?php if (oldInput("role") == "student") { echo "selected"; } ?>>
                        Sell my services (Student)
                    </option>
                    <option value="client" <?php if (oldInput("role") == "client") { echo "selected"; } ?>>
                        Hire students (Client)
                    </option>
                </select>
                <span class="error" id="roleError"></span>
            </div>

            <div class="field">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?php echo esc(oldInput("full_name")); ?>" />
                <span class="error" id="full_nameError"></span>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input type="text" id="email" name="email"
                       value="<?php echo esc(oldInput("email")); ?>" />
                <span class="error" id="emailError"></span>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" />
                <span class="hint">At least <?php echo MIN_PASSWORD_LENGTH; ?> characters.</span>
                <span class="error" id="passwordError"></span>
            </div>

            <div class="field">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" />
                <span class="error" id="confirm_passwordError"></span>
            </div>
        </fieldset>
    </div>

    <div class="col-half">
        <fieldset>
            <legend>Campus details</legend>

            <div class="field">
                <label for="university">University</label>
                <input type="text" id="university" name="university"
                       value="<?php echo esc(oldInput("university")); ?>" />
                <span class="error" id="universityError"></span>
            </div>

            <div class="field">
                <label for="department">Department</label>
                <input type="text" id="department" name="department"
                       value="<?php echo esc(oldInput("department")); ?>" />
                <span class="error" id="departmentError"></span>
            </div>

            <div class="field">
                <label for="phone">Mobile number</label>
                <input type="text" id="phone" name="phone"
                       value="<?php echo esc(oldInput("phone")); ?>" />
                <span class="hint">11 digits, for example 01712345678.</span>
                <span class="error" id="phoneError"></span>
            </div>

            <div class="field">
                <label for="skills">Skills <span class="muted">(students only, comma separated)</span></label>
                <input type="text" id="skills" name="skills"
                       value="<?php echo esc(oldInput("skills")); ?>" />
            </div>

            <div class="field">
                <label for="bio">Short bio</label>
                <textarea id="bio" name="bio"><?php echo esc(oldInput("bio")); ?></textarea>
            </div>

            <input type="submit" value="Create account" />
            <p class="hint spaced">Already registered?
                <a href="<?php echo BASE_URL; ?>/Student/View/login.php">Log in here</a>.
            </p>
        </fieldset>
    </div>

    <div class="clear"></div>
</form>

<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>

<?php
clearOldInput();
include "footer.php";
?>
