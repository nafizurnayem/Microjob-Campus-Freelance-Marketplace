// The page must define API_BASE before loading this file.

// Escapes database text before it goes into innerHTML.
function escapeHtml(text) {
    if (text === null || text === undefined) {
        return "";
    }
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function searchGigs() {
    var keyword = document.getElementById("keyword").value;
    var category = document.getElementById("category_id").value;
    var maxPrice = document.getElementById("max_price").value;
    var sortBy = document.getElementById("sort_by").value;

    var url = API_BASE + "/searchGigs.php" +
        "?keyword=" + encodeURIComponent(keyword) +
        "&category_id=" + encodeURIComponent(category) +
        "&max_price=" + encodeURIComponent(maxPrice) +
        "&sort_by=" + encodeURIComponent(sortBy);

    var results = document.getElementById("results");
    results.innerHTML = "<p class='muted'>Searching...</p>";

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            var data = JSON.parse(this.responseText);
            drawGigs(data);
        } else if (this.readyState === 4) {
            results.innerHTML = "<p class='error'>Could not load gigs. Please refresh the page.</p>";
        }
    };
    xhttp.open("GET", url, true);
    xhttp.send();

    return false;
}

function drawGigs(data) {
    var results = document.getElementById("results");
    var countBox = document.getElementById("resultCount");

    if (data.success !== true) {
        results.innerHTML = "<p class='error'>" + escapeHtml(data.message) + "</p>";
        return;
    }

    var gigs = data.data;

    if (countBox) {
        countBox.innerHTML = gigs.length + " gig(s) found";
    }

    if (gigs.length === 0) {
        results.innerHTML = "<div class='card'><p class='muted'>No gig matches your filter. Try a different keyword or a higher price.</p></div>";
        return;
    }

    var out = "";
    for (var i = 0; i < gigs.length; i++) {
        var gig = gigs[i];
        out += "<div class='gig'>";
        out += "<span class='price'>" + escapeHtml(gig.currency) + " " + escapeHtml(gig.price_bdt) + "</span>";
        out += "<h3><a href='" + escapeHtml(gig.link) + "'>" + escapeHtml(gig.title) + "</a></h3>";
        out += "<p class='meta'>" + escapeHtml(gig.category_name) + " &middot; by " + escapeHtml(gig.student_name) +
            " &middot; delivery in " + escapeHtml(gig.delivery_days) + " day(s)</p>";
        out += "<p>" + escapeHtml(gig.short_description) + "</p>";
        out += "<a class='btn btn-small' href='" + escapeHtml(gig.link) + "'>View details</a>";
        out += "</div>";
    }

    results.innerHTML = out;
}

// Cookie keeps the chosen category selected for 30 days.
function rememberCategory() {
    var category = document.getElementById("category_id").value;
    var expires = new Date();
    expires.setTime(expires.getTime() + (30 * 24 * 60 * 60 * 1000));
    document.cookie = "last_category=" + encodeURIComponent(category) +
        ";expires=" + expires.toUTCString() + ";path=/";
    searchGigs();
}

function refreshOrderStatus(orderId) {
    var box = document.getElementById("liveStatus");
    if (!box) {
        return;
    }

    fetch(API_BASE + "/orderStatus.php?order_id=" + encodeURIComponent(orderId))
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success === true) {
                box.innerHTML = "<span class='badge badge-" + escapeHtml(data.data.status) + "'>" +
                    escapeHtml(data.data.status_label) + "</span> " +
                    "<span class='muted'>checked at " + escapeHtml(data.data.checked_at) + "</span>";
            } else {
                box.innerHTML = "<span class='error'>" + escapeHtml(data.message) + "</span>";
            }
        })
        .catch(function () {
            box.innerHTML = "<span class='error'>Status check failed.</span>";
        });
}
