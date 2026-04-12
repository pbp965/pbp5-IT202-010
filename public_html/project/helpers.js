function flash(message = "", color = "info") {
    let flash = document.getElementById("flash");
    //create a div (or whatever wrapper we want)
    let outerDiv = document.createElement("div");
    outerDiv.className = "row justify-content-center";
    let innerDiv = document.createElement("div");

    //apply the CSS (these are bootstrap classes which we'll learn later)
    innerDiv.className = `alert alert-${color}`;
    //set the content
    innerDiv.innerText = `JavaScript: ${message}`;

    outerDiv.appendChild(innerDiv);
    //add the element to the DOM (if we don't it merely exists in memory)
    flash.appendChild(outerDiv);
}
function isValidPassword(pass) {
    return pass?.length >= 8; 
}
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email?.trim());
}

// Returns true if value is a valid username: lowercase, alphanumeric, _ or -
function isValidUsername(username) {
    return /^[a-z0-9_-]+$/.test(username?.trim());
}

// Returns true if both values are non-empty and match
function isValidConfirm(value, confirm) {
    return !!value && value === confirm;
}