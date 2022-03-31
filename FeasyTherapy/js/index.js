////////////IS LOGGED IN?////////////
var params = "operation=isLoggedIn";
var xhr = new XMLHttpRequest();
xhr.open('POST', 'php/Index.php', true);
xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
xhr.send(params);

xhr.onload = function () {
    var server_response = JSON.parse(this.response);

    if (server_response.result === "success")
        location.href = "html/patient_list.html";
    else {
        //BACKGROUND
        const random = Math.random();
        const background_image = document.getElementById("background_image");

        if (random <= 0.1)
            background_image.style.backgroundImage = "url(images/background/1.jpg)";
        else if (random <= 0.2)
            background_image.style.backgroundImage = "url(images/background/2.jpg)";
        else if (random <= 0.3)
            background_image.style.backgroundImage = "url(images/background/3.jpg)";
        else if (random <= 0.4)
            background_image.style.backgroundImage = "url(images/background/4.jpg)";
        else if (random <= 0.5)
            background_image.style.backgroundImage = "url(images/background/5.jpg)";
        else if (random <= 0.6)
            background_image.style.backgroundImage = "url(images/background/6.jpg)";
        else if (random <= 0.7)
            background_image.style.backgroundImage = "url(images/background/7.jpg)";
        else if (random <= 0.8)
            background_image.style.backgroundImage = "url(images/background/8.jpg)";
        else if (random <= 0.9)
            background_image.style.backgroundImage = "url(images/background/9.jpg)";
        else
            background_image.style.backgroundImage = "url(images/background/10.jpg)";

        document.body.style.display = "flex";
    }
}

////////////LOGIN////////////
const login_id_input = document.getElementById("login_id");
const login_password_input = document.getElementById("login_password");
const login_button = document.getElementById("login_button");
const error_label = document.getElementById("error_label");

var loginOperation = false;
var formFocused = false;

//FOCUS
login_id_input.addEventListener("focus", function () {
    formFocused = true;
});
login_id_input.addEventListener("blur", function () {
    formFocused = false;
});

login_password_input.addEventListener("focus", function () {
    formFocused = true;
});
login_password_input.addEventListener("blur", function () {
    formFocused = false;
});

//LOGIN
login_button.onclick = function () {
    login();
}

document.addEventListener('keydown', (e) => {
    var evtobj = window.event ? event : e;

    if ((e.code === "Enter" || e.code === "NumpadEnter" || evtobj.keyCode == 13) && formFocused) {
        e.preventDefault();

        login();
    }
});

function login() {
    if (!loginOperation) {
        error_label.innerText = "";
        error_label.style.display = "none";
        login_id_input.style.borderColor = "none;";
        login_password_input.style.borderColor = "none;";

        const login_id = login_id_input.value;
        const login_password = login_password_input.value;

        //Id Check
        let id_check = true;

        if (login_id.length == 11) {
            var allowed_characters = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
            var id_characters = login_id.split("");

            for (let i = 0; i < id_characters.length; i++) {
                let check = false;

                for (let j = 0; j < allowed_characters.length; j++) {
                    if (id_characters[i] === allowed_characters[j])
                        check = true;
                }

                if (!check) {
                    id_check = false;
                    break;
                }
            }
        } else
            id_check = false;

        //Password Check
        var upper_characters = ["A", "B", "C", "Ç", "D", "E", "F", "G", "Ğ", "H", "I", "İ", "J", "K", "L", "M",
            "N", "O", "Ö", "P", "Q", "R", "S", "Ş", "T", "U", "Ü", "V", "W", "X", "Y", "Z"];
        var number_characters = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
        var password_characters = login_password.split("");

        let upperCheck = false;
        for (let i = 0; i < password_characters.length; i++) {
            for (let j = 0; j < upper_characters.length; j++) {
                if (password_characters[i] === upper_characters[j])
                    upperCheck = true;
            }
        }

        let numberCheck = false;
        for (let i = 0; i < password_characters.length; i++) {
            for (let j = 0; j < number_characters.length; j++) {
                if (password_characters[i] === number_characters[j])
                    numberCheck = true;
            }
        }

        if (!id_check || login_password.length < 8 || login_password.length > 16 || !upperCheck || !numberCheck) {
            error_label.innerText = "*Giriş bilgisi hatalı";
            error_label.style.display = "flex";
            login_id_input.style.borderColor = "#C60000";
            login_password_input.style.borderColor = "#C60000";
        } else {
            loginOperation = true;

            login_button.style.pointerEvents = 'none';
            login_id_input.disabled = true;
            login_id_input.readOnly = true;
            login_password_input.disabled = true;
            login_password_input.readOnly = true;

            var params = "operation=login&id_number=" + encodeURIComponent(login_id) + "&password=" + encodeURIComponent(login_password);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'php/Index.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.send(params);

            xhr.onload = function () {
                loginOperation = false;

                login_button.style.pointerEvents = 'all';
                login_id_input.disabled = false;
                login_id_input.readOnly = false;
                login_password_input.disabled = false;
                login_password_input.readOnly = false;

                var server_response = JSON.parse(this.response);

                if (server_response.result === "success")
                    parent.window.location.href = "html/patient_list.html";
                else if (server_response.message === "User already logged in")
                    parent.window.location.href = "html/patient_list.html";
                else {
                    error_label.innerText = "*Giriş bilgisi hatalı";
                    error_label.style.display = "flex";
                    login_id_input.style.borderColor = "#C60000";
                    login_password_input.style.borderColor = "#C60000";
                }
            }
        }
    }
}