var requested_id = null;
var patient_id = null;
var phpsessid = null;

var hand_selection = 0;

window.onload = function (e) {
    //Requested ID
    var temp = window.location.href.split("=");
    phpsessid = temp[1].split("&")[0];
    patient_id = temp[2];

    var params = "operation=getExercise&id=" + patient_id + "&phpsessid=" + phpsessid;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'https://feasytherapy.site/php/Exercise.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.withCredentials = true;
    xhr.send(params);
    xhr.onload = function () {
        var server_response = JSON.parse(this.response);

        if (server_response.result === "success") {
            const socket = io();

            //Connected
            socket.on("connect", () => {
                const name_label = document.getElementById("patient_name_label");

                //Physiotherapist
                if (server_response.type === "physiotherapist") {
                    var patient = server_response.patient;

                    params = "operation=createToken&token=" + socket.id + "&phpsessid=" + phpsessid + "&patient_id=" + patient_id;
                    xhr = new XMLHttpRequest();
                    xhr.open('POST', 'https://feasytherapy.site/php/Exercise.php', true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.withCredentials = true;
                    xhr.send(params);

                    xhr.onload = function () {
                        server_response = JSON.parse(this.response);

                        if (server_response.result === "failure") {
                            document.body.style = "background-color: #eee; display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100vh; padding:0px; margin:1em;";
                            document.body.innerHTML = "<img loading='lazy' alt='' src='/images/server_failure.png' style='object-fit:contain; width:100px; height:100px;' /><p style='color:indianred; font-size:x-large; font-weight:bold; text-align:center; font-family:Lucida Console;'>SERVER FAILURE. PLEASE MAKE SURE YOU ARE LOGGED IN AND REFRESH THE PAGE.</p>";
                            socket.disconnect();
                        } else {
                            //Keep Alive
                            keepAlive();

                            name_label.innerText = patient.name + " " + patient.surname;
                            if (patient.gender === "0")
                                document.getElementById("patient_information_age_gender").innerText = patient.age + " Male";
                            else
                                document.getElementById("patient_information_age_gender").innerText = patient.age + " Female";
                            document.getElementById("patient_information_complaint").innerText = patient.complaint;
                            new Promise((resolve, reject) => {
                                document.getElementById("patient_chat_img").src = "https://feasytherapy.site/php/uploads/" + patient.photo;
                            });                            

                            //Display Page
                            document.getElementsByClassName("loader")[0].style.display = "none";
                            document.getElementById("container").style.display = "flex";

                            //Exercise Management
                            const start_button = document.getElementById("start_button");

                            start_button.onclick = function () {
                                const exercise_select = document.getElementById("exercise_mode_select");
                                const exercise_mode = exercise_select.options[exercise_select.selectedIndex].value;

                                const motion_select = document.getElementById("motion_type_select");
                                const motion_mode = motion_select.options[motion_select.selectedIndex].value;

                                const target_force = document.getElementById("target_force_input").value;

                                const initial_position = document.getElementById("initial_position_input").value;

                                const target_position = document.getElementById("target_position_input").value;

                                const repeat = document.getElementById("repeat_input").value;

                                socket.emit("exercise_adjust", {
                                    id: socket.id, hand_selection: hand_selection, exercise_mode: exercise_mode, motion_mode: motion_mode,
                                    target_force: target_force, initial_position: initial_position, target_position: target_position, repeat: repeat
                                });
                            }
                        }
                    }

                    //Patient
                } else {
                    //Keep Alive
                    keepAlive();

                    //Edit GUI
                    const info_label = document.getElementById("exercise_management");
                    info_label.innerText = "Exercise Information";

                    const left_table = document.getElementsByClassName("exercise_table_left_div")[0];
                    left_table.style.display = "none";

                    const stop_button = document.getElementById("stop_button");
                    stop_button.innerText = "LOG OUT";

                    stop_button.onclick = function () {
                        logout();
                    }

                    var physiotherapist = server_response.physiotherapist;
                    name_label.innerText = physiotherapist.name + " " + physiotherapist.surname;
                    new Promise((resolve, reject) => {
                        document.getElementById("patient_chat_img").src = "https://feasytherapy.site/php/uploads/" + physiotherapist.photo;
                    });   

                    //Socket Connection
                    socket.emit("createSession", {
                        patientId: server_response.id, patientSocket: socket.id, patientPhpsessid: phpsessid, 
                        physiotherapistId: server_response.physiotherapist_id, physiotherapistSocket: server_response.token,
                        physiotherapistPhpsessid: server_response.physiotherapist_phpsessid
                    });

                    //Display Page
                    document.getElementsByClassName("loader")[0].style.display = "none";
                    document.getElementById("container").style.display = "flex";
                }
            });

            //Exercise Adjust
            socket.on("exercise_adjust_order", (data) => {
                current_exercise_label = document.getElementById("current_exercise_label");
                current_exercise_img = document.getElementById("current_exercise_img");

                current_motion_label = document.getElementById("current_motion_label");
                current_motion_img = document.getElementById("current_motion_img");

                target_force_label = document.getElementById("target_force_label");
                initial_position_label = document.getElementById("initial_position_label");
                target_position_label = document.getElementById("target_position_label");
                target_repeat_label = document.getElementById("target_repeat_label");

                if (data.exercise_mode === "isometric_option") {
                    current_exercise_label.innerHTML = "current exercise is <br /><b>isometric</b>"
                    current_exercise_img.src = "images/isometric.png";
                } else if (data.exercise_mode === "isotonic_option") {
                    current_exercise_label.innerHTML = "current exercise is <br /><b>isotonic</b>"
                    current_exercise_img.src = "images/isotonic.png";
                } else if (data.exercise_mode === "isokinetic_option") {
                    current_exercise_label.innerHTML = "current exercise is <br /><b>isokinetic</b>"
                    current_exercise_img.src = "images/isokinetic.png";
                } else if (data.exercise_mode === "passive_option") {
                    current_exercise_label.innerHTML = "current exercise is <br /><b>passive</b>"
                    current_exercise_img.src = "images/passive.png";
                } else if (data.exercise_mode === "active_option") {
                    current_exercise_label.innerHTML = "current exercise is <br /><b>active</b>"
                    current_exercise_img.src = "images/active.png";
                } else if (data.exercise_mode === "active_aided_option") {
                    current_exercise_label.innerHTML = "current exercise is <br /><b>active aided</b>"
                    current_exercise_img.src = "images/active_aided.png";
                } else if (data.exercise_mode === "stretching_option") {
                    current_exercise_label.innerHTML = "current exercise is <br /><b>stretching</b>"
                    current_exercise_img.src = "images/stretching.png";
                }

                if (data.motion_mode === "flexion_extension_option") {
                    current_motion_label.innerHTML = "current motion type is <br /><b>Flexion - Extension</b>"
                    current_motion_img.src = "images/flexion-extension.png";
                } else if (data.motion_mode === "ulnar_radial_deviation_option") {
                    current_motion_label.innerHTML = "current motion type is <br /><b>Ulnar - Radial Deviation</b>"
                    current_motion_img.src = "images/ulnar_radial_deviation.png";
                } else if (data.motion_mode === "pronation_supination_option") {
                    current_motion_label.innerHTML = "current motion type is <br /><b>Pronation - Supination</b>"
                    current_motion_img.src = "images/pronation_supination.png";
                }

                target_force_label.innerHTML = "target force is<br /><b>" + data.target_force + " N</b>";
                initial_position_label.innerHTML = "initial position is<br /><b>" + data.initial_position + " degrees</b>";
                target_position_label.innerHTML = "target position is<br /><b>" + data.target_position + " degrees</b>";
                target_repeat_label.innerHTML = "target repeat is<br /><b>" + data.repeat + " times</b>";

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'https://feasytherapy.com/test2.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.send();

                xhr.onload = function () {
                    var temp = this.response.split(",");

                    initial_position_label.innerHTML = "initial position is<br /><b>" + temp[0] + " degrees</b>";
                    target_position_label.innerHTML = "target position is<br /><b>" + temp[1] + " degrees</b>";
                    target_force_label.innerHTML = "target force is<br /><b>" + temp[2] + " grams</b>";

                }
            });
        } else if (server_response.message === "User is offline") {
            document.body.style = "background-color: #eee; display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100vh; padding:0px; margin:1em;";
            document.body.innerHTML = "<img loading='lazy' alt='' src='/images/offline_user.png' style='object-fit:contain; width:100px; height:100px;' /><p style='color:indianred; font-size:x-large; font-weight:bold; text-align:center; font-family:Lucida Console;'>WAITING FOR PHYSIOTHERAPIST TO START THE SESSION</p><button onclick='logout()' style='color: indianred; border-color: indianred;'>LOG OUT</button>";
        } else if (server_response.message === "A session have been created already") {
            document.body.style = "background-color: #eee; display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100vh; padding:0px; margin:1em;";
            document.body.innerHTML = "<img loading='lazy' alt='' src='/images/alarm.png' style='object-fit:contain; width:100px; height:100px;' /><p style='color:indianred; font-size:x-large; font-weight:bold; text-align:center; font-family:Lucida Console;'>ALREADY OPEN ON ANOTHER TAB.<br />IF NOT, PLEASE REFRESH THE PAGE</p><button onclick='logout()' style='color: indianred; border-color: indianred;'>LOG OUT</button>";
        } else {
            document.body.style = "background-color: #eee; display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100vh; padding:0px; margin:1em;";
            document.body.innerHTML = "<img loading='lazy' alt='' src='/images/search_failed.png' style='object-fit:contain; width:100px; height:100px;' /><p style='color:indianred; font-size:x-large; font-weight:bold; text-align:center; font-family:Lucida Console;'>USER NOT EXISTS</p><button onclick='logout()' style='color: indianred; border-color: indianred;'>LOG OUT</button>";
        }
    }
}

function keepAlive() {
    //First
    var params = "operation=keepAlive&phpsessid=" + phpsessid;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'https://feasytherapy.site/php/Exercise.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.withCredentials = true;
    xhr.send(params);
    xhr.onload = function () {
        var server_response = JSON.parse(this.response);

        if (server_response.result === "failure")
            location.href = "";
    }

    var x = setInterval(function () {
        var params = "operation=keepAlive&phpsessid=" + phpsessid;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'https://feasytherapy.site/php/Exercise.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.withCredentials = true;
        xhr.send(params);
        xhr.onload = function () {
            var server_response = JSON.parse(this.response);

            if (server_response.result === "failure")
                location.href = "";
        }
    }, 30000);
}

function logout() {
    var params = "operation=logout&phpsessid=" + phpsessid;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'https://feasytherapy.site/php/Exercise.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send(params);

    xhr.onload = function () {
        parent.window.location.href = "https://feasytherapy.com/";
    }
}