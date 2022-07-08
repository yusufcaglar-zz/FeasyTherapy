const server_addr = "https://feasytherapy.site/";

//Dependencies
const path = require("path");
const express = require("express");
const socketio = require("socket.io");
const axios = require('axios');

const formatMessage = require('/root/messages');
const {
    createSession, 
    getCurrentSession,
    getAllSessions
} = require("/root/sessions");

//Https
const https = require("https");
const fs = require("fs");

const options = {
    key: fs.readFileSync("/etc/ssl/private/feasytherapy.pem"),
    cert: fs.readFileSync("/etc/pki/tls/certs/feasytherapy.pem")
};

//Port, Server and Socket
const PORT = process.env.PORT || 3000;
const app = express();
const server = https.createServer(options, app);
const io = socketio(server);

//Set static folder
app.use(express.static(path.join(__dirname, "../var/www/html")));
app.get('/', function (req, res) {
    res.sendFile(path.join(__dirname, '../var/www/html/server.html'));
});

//Listen Port
server.listen(PORT);

//Listen Connections
io.on("connection", socket => {
    socket.on('join-room', (roomId, userId) => {
        socket.join(roomId)
        socket.to(roomId).broadcast.emit('user-connected', userId)

        socket.on('disconnect', () => {
            socket.to(roomId).broadcast.emit('user-disconnected', userId)
        })
    })

    //Create Session
    socket.on("createSession", ({ patientId, patientSocket, patientPhpsessid,
        physiotherapistId, physiotherapistSocket, physiotherapistPhpsessid }) => {
        var data = JSON.stringify({
            operation: "createSession",
            physiotherapist_id: physiotherapistId,
            patient_id: patientId,
            password: "!Fsc%vA>vtD5qahh"
        });

        const headers = { 'Content-Type': 'application/json' };
        const config = { headers: headers };
        
        axios.post(server_addr + 'php/Exercise.php', data, config)
            .then(function (response) {
                if (response.data.result === "success") {
                    createSession(patientId, patientSocket, patientPhpsessid,
                        physiotherapistId, physiotherapistSocket, physiotherapistPhpsessid);
                } else
                    socket.emit("endSession", formatMessage("Server failed", { session: patientSocket }));
            })
            .catch(function (error) {
                console.error(error);

                socket.emit("endSession", formatMessage("Server failed", { session: patientSocket }));
            });
    });

    //Exercise Adjust
    socket.on("exercise_adjust", ({ id, hand_selection, exercise_mode, motion_mode, target_force, initial_position, target_position, repeat }) => {
        const session = getCurrentSession(id, "physiotherapist");

        if (session) {
            io.emit("exercise_adjust_order", ({ hand_selection, exercise_mode, motion_mode, target_force, initial_position, target_position, repeat }));

            var data = JSON.stringify({
                operation: "ensureChat"
            });

            const headers = { 'Content-Type': 'application/json' };
            const config = { headers: headers };
        }
    });

    /*
    //Block
    socket.on("block", (id) => {
        const room = getCurrentRoom_history(id);

        if (room) {
            data = JSON.stringify({
                operation: "block",
                receiverId: room.receiverId,
                anon_name: room.senderUsername,
                anon_ip: room.senderOnline ? room.senderId : room.senderIp,
                password: "!Fsc%vA>vtD5qahh"
            });

            post(data);

            removeRoom_history(id);
        }
    });

    //Swap Anonimity
    socket.on("swapAnonimity", ({ id, username }) => {
        const room = getCurrentRoom(id);

        if (room) {
            swapAnonimity(id, username);
            swapAnonimity_history(id, username);
            socket.broadcast.to(id).emit("swapAnonimity", formatMessage(username, "", { room: id}));
        }
    });

    //Seen
    socket.on("seen", (id) => {
        const room = getCurrentRoom(id);

        if (room)
            socket.broadcast.to(id).emit("seen", formatMessage("", "", { room: id }));
    });

    //Typing
    socket.on("typing", ({ id, status }) => {
        const room = getCurrentRoom(id);

        if (room)
            socket.broadcast.to(id).emit("typing", formatMessage("", "", { room: id, status: status }));
    });

    //Close Conversation From Both Sides
    socket.on("closeConversation", (id) => {
        const room = getCurrentRoom(id);

        if (room != null) {
            setReceiverNotAvailable(id);
            removeRoom_history(id);
            socket.broadcast.to(room.id).emit("endConversation", formatMessage(room.receiverUsername, "Has closed chat", { room: room.id }));
        }
    });

    //Disconnection, Remove Room
    socket.on("disconnect", () => {
        const rooms = getAllRooms(socket.id);

        if (rooms.length > 0) {
            while (rooms.length > 0) {
                var i = rooms.length - 1;

                if (rooms[i].receiverSocket === socket.id) {
                    socket.broadcast.to(rooms[i].id).emit("endConversation", formatMessage(rooms[i].receiverUsername, "Has disconnected", { room: rooms[i].id }));

                    var data = JSON.stringify({
                        operation: "nullToken",
                        token: rooms[i].receiverSocket,
                        password: "!Fsc%vA>vtD5qahh"
                    });

                    post(data);

                    removeRoom_history(rooms[i].id);
                } else if (rooms[i].receiverAvailable)
                    socket.broadcast.to(rooms[i].id).emit("endConversation", formatMessage(rooms[i].senderUsername, "Has disconnected", { room: rooms[i].id }));

                var data = JSON.stringify({
                    operation: "endConversation",
                    receiver: rooms[i].receiverId,
                    sender: rooms[i].senderId,
                    password: "!Fsc%vA>vtD5qahh"
                });

                post(data);

                removeRoom(rooms[i].id);
                rooms.splice(i, 1);
            }
        } else {
            var data = JSON.stringify({
                operation: "nullToken",
                token: socket.id,
                password: "!Fsc%vA>vtD5qahh"
            });

            post(data);
        }
    });
    */
});

//Server Requests
function post(data) {
    const headers = { 'Content-Type': 'application/json' };
    const config = { headers: headers };

    axios.post('https://feasytherapy.site/php/Server.php', data, config)
        .then(function (response) {
            console.log(response.status + " " + JSON.parse(data).operation);
        })
        .catch(function (error) {
            console.error(error);
        });
}

/*
    socket.emit(name, value) -> send message to only related user
    socket.broadcast.emit(name, value) -> send message to everyone except related user
    io.emit(name, value) -> send message to everyone
    */

    // sckt = io.sockets.connected[socketId] -> get socket from its id

    /*
    console.log(Object.keys(io.of('/chat').sockets).length) -> Namespace connectionts
    console.log(Object.keys(io.sockets.sockets).length) -> Non namespace connections
    console.log(io.sockets.adapter.rooms[rommId].length) -> Room connections
    */

    /* -> native https post
        var data = JSON.stringify({
            operation: "ensureChat",
            isOnline: isOnline,
            receiver: room.receiverId,
            sender: room.senderId,
            password: "!Fsc%vA>vtD5qahh"
        });

        const options = {
            hostname: 'swirlia.net',
            port: 443,
            path: '/php/Chat.php',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        };

        process.env["NODE_TLS_REJECT_UNAUTHORIZED"] = 0; //REMOVE THIS LINE WHEN HAVING REAL SERVER
        const req = https.request(options, res => {
            res.on('data', d => {
                process.stdout.write(d);
            });

            console.log(res.statusCode);
        });

        req.on('error', error => {
            console.error(error);
        });

        req.write(data);
        req.end();

process.env["NODE_TLS_REJECT_UNAUTHORIZED"] = 0; //REMOVE THIS LINE WHEN HAVING REAL SERVER
*/