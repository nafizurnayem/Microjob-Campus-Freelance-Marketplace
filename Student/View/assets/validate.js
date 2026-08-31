// Client side form checks. Every rule is re-checked in PHP on the server.

/* small helpers */

function showError(fieldId, message) {
    var box = document.getElementById(fieldId + "Error");
    if (box) {
        box.innerHTML = message;
        box.style.color = "#b3261e";
    }
}

function clearError(fieldId) {
    var box = document.getElementById(fieldId + "Error");
    if (box) {
        box.innerHTML = "";
    }
}

function valueOf(fieldId) {
    var field = document.getElementById(fieldId);
    if (!field) {
        return "";
    }
    return field.value.replace(/^\s+|\s+$/g, "");
}

function isEmailText(text) {
    var parts = text.split("@");
    if (parts.length !== 2) {
        return false;
    }
    if (parts[0].length < 1 || parts[1].length < 3) {
        return false;
    }
    if (parts[1].indexOf(".") < 1) {
        return false;
    }
    if (parts[1].charAt(parts[1].length - 1) === ".") {
        return false;
    }
    if (text.indexOf(" ") >= 0) {
        return false;
    }
    return true;
}

function isDigits(text) {
    if (text.length === 0) {
        return false;
    }
    for (var i = 0; i < text.length; i++) {
        var c = text.charAt(i);
        if (c < "0" || c > "9") {
            return false;
        }
    }
    return true;
}

function requireField(fieldId, message) {
    clearError(fieldId);
    if (valueOf(fieldId) === "") {
        showError(fieldId, message);
        return false;
    }
    return true;
}

/* registration */

function validateRegister() {
    var ok = true;

    ok = requireField("full_name", "Full name is required.") && ok;
    if (ok && valueOf("full_name").length < 3) {
        showError("full_name", "Full name must be at least 3 characters.");
        ok = false;
    }

    clearError("email");
    if (valueOf("email") === "") {
        showError("email", "Email is required.");
        ok = false;
    } else if (!isEmailText(valueOf("email"))) {
        showError("email", "Enter a valid email address.");
        ok = false;
    }

    clearError("password");
    if (valueOf("password") === "") {
        showError("password", "Password is required.");
        ok = false;
    } else if (valueOf("password").length < 6) {
        showError("password", "Password must be at least 6 characters.");
        ok = false;
    }

    clearError("confirm_password");
    if (valueOf("confirm_password") !== valueOf("password")) {
        showError("confirm_password", "Passwords do not match.");
        ok = false;
    }

    clearError("phone");
    if (valueOf("phone") === "") {
        showError("phone", "Phone number is required.");
        ok = false;
    } else if (!isDigits(valueOf("phone")) || valueOf("phone").length !== 11) {
        showError("phone", "Enter an 11 digit mobile number, for example 01712345678.");
        ok = false;
    }

    ok = requireField("university", "University name is required.") && ok;
    ok = requireField("department", "Department is required.") && ok;

    clearError("role");
    if (valueOf("role") !== "student" && valueOf("role") !== "client") {
        showError("role", "Choose whether you want to sell or hire.");
        ok = false;
    }

    return ok;
}

/* login */

function validateLogin() {
    var ok = true;

    clearError("email");
    if (valueOf("email") === "") {
        showError("email", "Email is required.");
        ok = false;
    } else if (!isEmailText(valueOf("email"))) {
        showError("email", "Enter a valid email address.");
        ok = false;
    }

    ok = requireField("password", "Password is required.") && ok;

    return ok;
}

/* change password */

function validatePasswordChange() {
    var ok = true;

    ok = requireField("current_password", "Current password is required.") && ok;

    clearError("new_password");
    if (valueOf("new_password").length < 6) {
        showError("new_password", "New password must be at least 6 characters.");
        ok = false;
    }

    clearError("confirm_password");
    if (valueOf("confirm_password") !== valueOf("new_password")) {
        showError("confirm_password", "Passwords do not match.");
        ok = false;
    }

    return ok;
}

/* profile */

function validateProfile() {
    var ok = true;

    ok = requireField("full_name", "Full name is required.") && ok;
    ok = requireField("university", "University name is required.") && ok;
    ok = requireField("department", "Department is required.") && ok;

    clearError("phone");
    if (!isDigits(valueOf("phone")) || valueOf("phone").length !== 11) {
        showError("phone", "Enter an 11 digit mobile number.");
        ok = false;
    }

    return ok;
}

/* gig form */

function validateGig() {
    var ok = true;

    clearError("title");
    if (valueOf("title") === "") {
        showError("title", "Gig title is required.");
        ok = false;
    } else if (valueOf("title").length < 10) {
        showError("title", "Title must be at least 10 characters.");
        ok = false;
    }

    clearError("description");
    if (valueOf("description").length < 30) {
        showError("description", "Describe your service in at least 30 characters.");
        ok = false;
    }

    clearError("category_id");
    if (valueOf("category_id") === "" || valueOf("category_id") === "0") {
        showError("category_id", "Choose a category.");
        ok = false;
    }

    clearError("price_bdt");
    var price = Number(valueOf("price_bdt"));
    if (valueOf("price_bdt") === "" || isNaN(price)) {
        showError("price_bdt", "Price is required.");
        ok = false;
    } else if (price < 50 || price > 100000) {
        showError("price_bdt", "Price must be between BDT 50 and BDT 100000.");
        ok = false;
    }

    clearError("delivery_days");
    var days = Number(valueOf("delivery_days"));
    if (!isDigits(valueOf("delivery_days")) || days < 1 || days > 30) {
        showError("delivery_days", "Delivery time must be between 1 and 30 days.");
        ok = false;
    }

    return ok;
}

/* place order */

function validateOrder() {
    var ok = true;

    clearError("requirement");
    if (valueOf("requirement").length < 15) {
        showError("requirement", "Describe what you need in at least 15 characters.");
        ok = false;
    }

    return ok;
}

/* payment */

function validatePayment() {
    var ok = true;
    var method = valueOf("method");

    clearError("method");
    if (method === "") {
        showError("method", "Choose a payment method.");
        return false;
    }

    clearError("account_no");
    var account = valueOf("account_no").replace(/\s+/g, "");

    if (!isDigits(account)) {
        showError("account_no", "Only digits are allowed.");
        return false;
    }

    if (method === "bkash" || method === "nagad") {
        if (account.length !== 11) {
            showError("account_no", "Enter the 11 digit mobile number of your account.");
            ok = false;
        }
    } else if (method === "bank") {
        if (account.length < 10 || account.length > 20) {
            showError("account_no", "Bank account number must be 10 to 20 digits.");
            ok = false;
        }
    } else if (method === "card") {
        if (account.length !== 16) {
            showError("account_no", "Card number must be 16 digits.");
            ok = false;
        }
    }

    return ok;
}

/* payment method hint */
function paymentMethodChanged() {
    var method = valueOf("method");
    var hint = document.getElementById("accountHint");
    var label = document.getElementById("accountLabel");

    if (!hint || !label) {
        return;
    }

    if (method === "bkash") {
        label.innerHTML = "bKash account number";
        hint.innerHTML = "11 digit mobile number, for example 01712345678.";
    } else if (method === "nagad") {
        label.innerHTML = "Nagad account number";
        hint.innerHTML = "11 digit mobile number, for example 01712345678.";
    } else if (method === "bank") {
        label.innerHTML = "Bank account number";
        hint.innerHTML = "10 to 20 digits.";
    } else if (method === "card") {
        label.innerHTML = "Card number";
        hint.innerHTML = "16 digits, Visa or Mastercard.";
    } else {
        label.innerHTML = "Account number";
        hint.innerHTML = "Choose a payment method first.";
    }

    clearError("account_no");
}

/* review */

function validateReview() {
    var ok = true;

    clearError("rating");
    var rating = Number(valueOf("rating"));
    if (valueOf("rating") === "" || rating < 1 || rating > 5) {
        showError("rating", "Choose a rating from 1 to 5.");
        ok = false;
    }

    clearError("comment");
    if (valueOf("comment").length > 500) {
        showError("comment", "Comment must be 500 characters or less.");
        ok = false;
    }

    return ok;
}

/* withdrawal */

function validateWithdraw() {
    var ok = true;

    clearError("amount_bdt");
    var amount = Number(valueOf("amount_bdt"));
    if (valueOf("amount_bdt") === "" || isNaN(amount) || amount <= 0) {
        showError("amount_bdt", "Enter the amount you want to withdraw.");
        ok = false;
    }

    clearError("account_no");
    var account = valueOf("account_no").replace(/\s+/g, "");
    if (!isDigits(account) || account.length < 10) {
        showError("account_no", "Enter a valid account number.");
        ok = false;
    }

    return ok;
}

/* message */

function validateMessage() {
    clearError("body");
    if (valueOf("body") === "") {
        showError("body", "Write a message first.");
        return false;
    }
    return true;
}

/* category (admin) */

function validateCategory() {
    clearError("name");
    if (valueOf("name").length < 3) {
        showError("name", "Category name must be at least 3 characters.");
        return false;
    }
    return true;
}
