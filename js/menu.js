var phpsessid = null;
const server_addr = "https://feasytherapy.site/";

var inOperation = false;

// Session Check
var xhr = new XMLHttpRequest();
xhr.open('POST', 'page/session.php', true);
xhr.send();

xhr.onload = function () {
    phpsessid = this.response;

    afterSessionSet();
}

function afterSessionSet() {
    //BUTTONS
    const menu_patient_list = document.getElementById("menu_patient_list");
    const menu_logout = document.getElementById("menu_logout");

    //GET PHYSIOTHERAPIST
    var params = "operation=getPhysiotherapist&phpsessid=" + phpsessid;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', server_addr + 'php/Index.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send(params);

    xhr.onload = function () {
        var server_response = JSON.parse(this.response);

        var physiotherapist = server_response.physiotherapist;

        document.getElementById("menu_physiotherapist_label").innerText = physiotherapist.name + " " + physiotherapist.surname;
        new Promise((resolve, reject) => {
            document.getElementById("menu_physiotherapist_img").src = server_addr + "/php/uploads/" + physiotherapist.photo;
        }); 
    }

    //EVENTS
    menu_patient_list.onclick = function () {
        patient_list();
    }
    menu_logout.onclick = function () {
        logout();
    }
}

//FUNCTIONS
function patient_list() {
    if (!inOperation)
        parent.window.location.href = "page/patient_list.html";
}

function logout() {
    if (!inOperation) {
        inOperation = true;

        var params = "operation=logout&phpsessid=" + phpsessid;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', server_addr + 'php/Index.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.send(params);

        xhr.onload = function () {
            parent.window.location.href = "";
        }
    }
}