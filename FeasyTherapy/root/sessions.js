const sessions = [];

// Create Session
function createSession(patientId, patientSocket, patientPhpsessid,
    physiotherapistId, physiotherapistSocket, physiotherapistPhpsessid) {
    const session = {
        patientId: patientId, patientSocket: patientSocket, patientPhpsessid: patientPhpsessid,
        physiotherapistId: physiotherapistId, physiotherapistSocket: physiotherapistSocket, physiotherapistPhpsessid: physiotherapistPhpsessid
    };

    sessions.push(session);
}

// Get current room
function getCurrentSession(id, type) {
    if (type === "physiotherapist")
        return sessions.find(session => session.physiotherapistSocket === id);
    else
        return sessions.find(session => session.patientSocket === id);
}

// Get All Sessions
function getAllSessions() {
    return sessions;
}

module.exports = {
    createSession, 
    getCurrentSession, 
    getAllSessions
};