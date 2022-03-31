var inOperation = false;

//BUTTONS
const menu_patient_list = document.getElementById("menu_patient_list");
const menu_logout = document.getElementById("menu_logout");

//EVENTS
menu_patient_list.onclick = function () {
    patient_list();
}
menu_logout.onclick = function () {
    logout();
}

//FUNCTIONS
function patient_list() {
    if (!inOperation)
        parent.window.location.href = "html/patient_list.html";
}
function logout() {
    if (!inOperation) {
        inOperation = true;

        var params = "operation=logout";
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/Index.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.send(params);

        xhr.onload = function () {
            parent.window.location.href = "html";
        }
    }
}