<?php
require "../../Model/init.php";

if (isLoggedIn()) {
    redirectTo(dashboardFor(currentRole()));
}

$rememberedEmail = $_COOKIE["remember_email"] ?? "";
$emailValue = oldInput("email");
if ($emailValue == "") {
    $emailValue = $rememberedEmail;
}

$pageTitle = "Login";
include "header.php";
?>

<h1>Log in</h1>

<div class="col-half">
    <form action="<?php echo BASE_URL; ?>/Student/Controller/loginController.php" method="post"
        onsubmit="return validateLogin()">
        <fieldset>
            <legend>Account</legend>

            <div class="field">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" value="<?php echo esc($emailValue); ?>" />
                <span class="error" id="emailError"></span>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" />
                <span class="error" id="passwordError"></span>
            </div>

            <div class="field">
                <label>
                    <input type="checkbox" name="remember" value="yes" <?php if ($rememberedEmail != "") {
                        echo "checked";
                    } ?> />
                    Remember my email on this computer
                </label>
            </div>

            <input type="submit" value="Log in" />
            <p class="hint spaced">
                New here? <a href="<?php echo BASE_URL; ?>/Student/View/register.php">Create an account</a>.
            </p>
        </fieldset>
    </form>
</div>

<div class="col-half">
    <div class="card">
        <h3>Demo accounts</h3>
        <p class="muted"></p>
        <table>
            <tr>
                <th>Role</th>
                <th>Email</th>
                <th>Password</th>
            </tr>
            <tr>
                <td>Student</td>
                <td>nafiz@student.test</td>
                <td>nafiz123</td>
            </tr>
            <tr>
                <td>Client</td>
                <td>karim@faculty.test</td>
                <td>karim123</td>
            </tr>
            <tr>
                <td>Admin</td>
                <td>admin@microjob.test</td>
                <td>admin123</td>
            </tr>
        </table>
    </div>
</div>
<div class="clear"></div>

<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>

<?php
clearOldInput();
include "footer.php";
?>
