////////////IS LOGGED IN?////////////
var params = "operation=isLoggedIn";
var xhr = new XMLHttpRequest();
xhr.open('POST', 'php/Index.php', true);
xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
xhr.send(params);

xhr.onload = function () {
    var server_response = JSON.parse(this.response);

    if (server_response.result === "success")
        document.body.style.display = "block";
    else
        location.href = "html";
}