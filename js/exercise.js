var phpsessid = null;
const server_addr = "https://feasytherapy.site/";

//Patient ID
var temp = window.location.href.split("=");
patient_id = temp[1].split("&")[0];

// Session Check
var xhr = new XMLHttpRequest();
xhr.open('POST', 'page/session.php', true);
xhr.send();

xhr.onload = function () {
    phpsessid = this.response;

    afterSessionSet();
}

function afterSessionSet() {
    ////////////IS LOGGED IN?////////////
    var params = "operation=isLoggedIn&phpsessid=" + phpsessid;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', server_addr + 'php/Index.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send(params);

    xhr.onload = function () {
        var server_response = JSON.parse(this.response);

        if (server_response.result === "success") {
            if (server_response.type === "physiotherapist") {
                document.body.style.display = "block";

                //Exercise
                const iframe_server = document.getElementById("iframe_server");
                iframe_server.src = "https://feasytherapy.site:3000/server.html?phpsessid=" + phpsessid + "&patient_id=" + patient_id;
            } else
                location.href = "";
        }  else
            location.href = "";
    }
}