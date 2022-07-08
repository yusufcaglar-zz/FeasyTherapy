var phpsessid = null;
const server_addr = "https://feasytherapy.site/";

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
            if (server_response.type === "physiotherapist")
                document.body.style.display = "block";
            else
                location.href = "";
        } else
            location.href = "";
    }

    ////////////GET PATIENT LIST////////////
    serverProcessing = true;

    const patient_list = document.getElementById("patient_list");
    const ul = document.createElement("ul");

    ul.setAttribute("id", "patient_list_ul");
    patient_list.appendChild(ul);

    const p = document.getElementById("patient_list_p");
    p.style.display = "none";

    const header = document.getElementById("patient_list_header");
    header.style.display = "flex";

    params = "operation=getPatients&phpsessid=" + phpsessid;
    xhr = new XMLHttpRequest();
    xhr.open('POST', server_addr + 'php/Index.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send(params);
    xhr.onload = function () {
        var server_response = JSON.parse(this.response);

        if (server_response.result === "success") {
            var searchbox = document.getElementById("searchbox");
            searchbox_text.oninput = function () {
                if (!serverProcessing)
                    search(searchbox_text.value, ul);
            }

            for (let i = 0; i < server_response.patients.length; i++) {
                var label = document.createElement("label");
                label.innerText = server_response.patients[i].name + " " + server_response.patients[i].surname;
                label.classList.add("patient_list_name");

                var img = document.createElement("img");

                new Promise((resolve, reject) => {
                    img.src = server_addr + "php/uploads/" + server_response.patients[i].photo;
                });

                img.classList.add("patient_list_profile");

                var remove = document.createElement("img");

                new Promise((resolve, reject) => {
                    remove.src = "images/remove.png";
                });

                remove.classList.add("patient_list_remove");
                remove.onclick = function () {
                    if (!serverProcessing) {
                        serverProcessing = true;

                        var params = "operation=removePatient&id=" + server_response.patients[i].id + "&phpsessid=" + phpsessid;
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', server_addr + 'php/Index.php', true);
                        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                        xhr.send(params);
                        xhr.onload = function () {
                            for (let j = 0; j < ul.childNodes.length; j++) {
                                if (ul.childNodes[j].childNodes[0].childNodes[1].innerText === server_response.patients[i].name + " " + server_response.patients[i].surname)
                                    ul.removeChild(ul.childNodes[j]);
                            }

                            serverProcessing = false;

                            search(searchbox_text.value, ul);
                        }
                    }
                }

                var a = document.createElement("a");
                a.href = "page/exercise.html?id=" + server_response.patients[i].id;
                a.target = "_self";

                a.appendChild(img);
                a.appendChild(label);

                var li = document.createElement("li");
                li.appendChild(a);
                li.appendChild(remove);

                ul.appendChild(li);
            }
        } else {
            if (server_response.message === "User not exists")
                location.href = "";
            else if (server_response.message === "User not logged in")
                location.href = "";
            else {
                ul.style.display = "none";

                const header = document.getElementById("patient_list_header");
                header.style.display = "none";

                const p = document.getElementById("patient_list_p");
                p.style.display = "initial";
            }
        }

        serverProcessing = false;
    }
}

function search(filter, ul) {
    let counter = 0;

    for (let i = 0; i < ul.childNodes.length; i++) {
        var label = ul.childNodes[i].childNodes[0].childNodes[1];
        var li = ul.childNodes[i];
        
        if (!(label.innerText.toUpperCase()).includes(filter.toUpperCase()) && filter !== "")
            li.style.display = "none";
        else {
            li.style.display = "flex";
            counter++;
        }
    }

    if (counter === 0) {
        const p = document.getElementById("patient_list_p");
        p.innerText = "No result.";
        p.style.display = "initial";
    } else {
        const p = document.getElementById("patient_list_p");
        p.style.display = "none";
        p.innerText = "You don't have any patient at the moment.";
    }
}