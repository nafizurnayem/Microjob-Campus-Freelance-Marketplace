<?php
// Server side input rules. esc() escapes every value printed into HTML.
function esc($value)
{
    return htmlspecialchars($value);
}

function cleanInput($value)
{
    return trim($value);
}

function isEmptyValue($value)
{
    if (trim($value) == "") {
        return true;
    }
    return false;
}

function isTooShort($value, $min)
{
    if (strlen(trim($value)) < $min) {
        return true;
    }
    return false;
}

function isTooLong($value, $max)
{
    if (strlen(trim($value)) > $max) {
        return true;
    }
    return false;
}

function isValidEmail($email)
{
    $email = trim($email);

    if ($email == "") {
        return false;
    }
    if (str_contains($email, " ")) {
        return false;
    }

    $parts = explode("@", $email);
    if (count($parts) != 2) {
        return false;
    }

    $local = $parts[0];
    $domain = $parts[1];

    if (strlen($local) < 1 || strlen($domain) < 3) {
        return false;
    }
    if (!str_contains($domain, ".")) {
        return false;
    }

    $dotPosition = strpos($domain, ".");
    if ($dotPosition === 0) {
        return false;
    }
    if ($dotPosition === strlen($domain) - 1) {
        return false;
    }

    return true;
}

function isDigitsOnly($value)
{
    $value = trim($value);

    if ($value == "") {
        return false;
    }
    if (!is_numeric($value)) {
        return false;
    }
    if (str_contains($value, ".")) {
        return false;
    }
    if (str_contains($value, "-")) {
        return false;
    }
    if (str_contains($value, "+")) {
        return false;
    }
    if (str_contains($value, "e") || str_contains($value, "E")) {
        return false;
    }

    return true;
}

function isValidPhone($phone)
{
    $phone = trim($phone);

    if (!isDigitsOnly($phone)) {
        return false;
    }
    if (strlen($phone) != 11) {
        return false;
    }
    if (substr($phone, 0, 2) != "01") {
        return false;
    }

    return true;
}

function isValidPrice($price, $min, $max)
{
    $price = trim($price);

    if ($price == "") {
        return false;
    }
    if (!is_numeric($price)) {
        return false;
    }
    if (str_contains($price, "-")) {
        return false;
    }

    $amount = round($price, 2);
    if ($amount < $min || $amount > $max) {
        return false;
    }

    return true;
}

function isValidWholeNumber($value, $min, $max)
{
    if (!isDigitsOnly($value)) {
        return false;
    }

    $number = round(trim($value));
    if ($number < $min || $number > $max) {
        return false;
    }

    return true;
}

function isInList($value, $allowedList)
{
    $total = count($allowedList);
    for ($i = 0; $i < $total; $i++) {
        if ($allowedList[$i] === $value) {
            return true;
        }
    }
    return false;
}

function isValidAccountNumber($number, $minLength, $maxLength)
{
    $number = str_replace(" ", "", trim($number));

    if (!isDigitsOnly($number)) {
        return false;
    }
    if (strlen($number) < $minLength || strlen($number) > $maxLength) {
        return false;
    }

    return true;
}

function formatMoney($amount)
{
    return CURRENCY . " " . round($amount, 2);
}

function setFlash($key, $value)
{
    $_SESSION["flash_" . $key] = $value;
}

function getFlash($key)
{
    $value = $_SESSION["flash_" . $key] ?? "";
    unset($_SESSION["flash_" . $key]);
    return $value;
}

function keepOldInput($fields)
{
    $_SESSION["old_input"] = $fields;
}

function oldInput($field)
{
    $old = $_SESSION["old_input"] ?? array();
    return $old[$field] ?? "";
}

function clearOldInput()
{
    unset($_SESSION["old_input"]);
}
