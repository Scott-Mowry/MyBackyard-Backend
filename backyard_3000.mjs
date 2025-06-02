import express from "express";
import { createServer } from "http";
import { Server } from "socket.io";
import mysql from "mysql";
import https from "https";
import http from "http";
import fs from "fs";
const app = express();
const options = {
    key: fs.readFileSync(
        "/etc/letsencrypt/live/admin.mybackyardusa.com/privkey.pem"
    ),
    cert: fs.readFileSync(
        "/etc/letsencrypt/live/admin.mybackyardusa.com/fullchain.pem"
    ),
};
// const options = {};

const server = https.createServer(options, app);
// const server = createServer(app);
const io = new Server(server);

var db = mysql.createPool({
    host: "153.92.214.173",
    user: "user_mybackyard",
    password: "2n<6ZI<4p@dY",
    database: "mybackyard_db",
    debug: true,
    charset: "utf8mb4",
});
console.log("Connected to the database");
// Socket.io connection
io.on("connection", (socket) => {
    console.log("A user connected");

    // get businesses
    socket.on("get_buses", (object) => {
        db.getConnection(function (error, connection) {
            if (error) {
                socket.emit(`error`, {
                    object_type: "get_buses",
                    message: error,
                });
            } else {
                connection.query(
                    `SELECT *, 
                    (3959 * acos(cos(radians(${object.lat})) * cos(radians(latitude)) * cos(radians(longitude) - radians(${object.long})) + sin(radians(${object.lat})) * sin(radians(latitude)))) AS distance
                    FROM users
                    WHERE users.role = 'Business'
                    AND users.sub_id IS NOT NULL
                    AND users.latitude IS NOT NULL
                    AND users.longitude IS NOT NULL
                    HAVING distance <= ${object.radius}
                    ORDER BY distance`,
                    function (err, data) {
                        if (err) {
                            console.error("SQL error:", err);
                            socket.emit("error", {
                                error: "Database query failed",
                            });
                        } else {
                            // Create an array of promises for each user's schedule query
                            const schedulePromises = data.map((user) => {
                                return new Promise((resolve, reject) => {
                                    connection.query(
                                        `SELECT * FROM schedule
                                        WHERE schedule.owner_id = ${user["id"]}
                                        ORDER BY schedule.id`,
                                        function (err, days) {
                                            if (err) {
                                                console.error(
                                                    "SQL error:",
                                                    err
                                                );
                                                reject("Database query failed");
                                            } else {
                                                user["days"] = days;
                                                resolve(); // Resolve once the user's schedule is attached
                                            }
                                        }
                                    );
                                });
                            });

                            // Wait for all schedule queries to complete
                            Promise.all(schedulePromises)
                                .then(() => {
                                    connection.release();
                                    socket.emit("response", {
                                        object_type: "get_buses",
                                        data: data,
                                    });
                                })
                                .catch((error) => {
                                    console.error("Error:", error);
                                    socket.emit("error", {
                                        error: "Database query failed",
                                    });
                                    connection.release();
                                });
                        }
                    }
                );
            }
        });
    });

    // get trigger
    socket.on("get_trigger", (object) => {
        db.getConnection(function (error, connection) {
            if (error) {
                socket.emit(`error`, {
                    object_type: "get_trigger",
                    message: error,
                });
            } else {
                connection.query(
                    `SELECT * from content_webs WHERE id = '4'`,
                    function (err, data) {
                        connection.release();
                        if (err) {
                            console.error("SQL error:", err);
                            socket.emit("error", {
                                error: "Database query failed",
                            });
                        } else {
                            socket.emit(`response`, {
                                object_type: "get_trigger",
                                data: data[0],
                            });
                        }
                    }
                );
            }
        });
    });

    // trigger
    socket.on("trigger", (object) => {
        db.getConnection(function (error, connection) {
            if (error) {
                socket.emit(`error`, {
                    object_type: "trigger",
                    message: error,
                });
            } else {
                if (object.value != null) {
                    if (object.value == "0") {
                        connection.query(
                            `TRUNCATE TABLE personal_access_tokens`
                        );
                    }
                    connection.query(
                        `UPDATE content_webs SET url = ${object.value} WHERE id = '4'`,
                        function (err, data) {
                            connection.release();
                            if (err) {
                                console.error("SQL error:", err);
                                socket.emit("error", {
                                    error: "Database query failed",
                                });
                            } else {
                                socket.emit(`response`, {
                                    object_type: "trigger",
                                    data: {
                                        url: object.value,
                                    },
                                });
                            }
                        }
                    );
                } else {
                    socket.emit(`response`, {
                        object_type: "trigger",
                        message: "No value",
                    });
                }
            }
        });
    });

    // Event to fetch a user by ID
    socket.on("get_user", (object) => {
        console.log("USERID: " + object.id);

        db.getConnection(function (error, connection) {
            if (error) {
                socket.emit(`error`, {
                    object_type: "get_user",
                    message: error,
                });
            } else {
                connection.query(
                    `SELECT * FROM users WHERE id = ${object.id}`,
                    function (err, data) {
                        connection.release();
                        if (err) {
                            console.error("SQL error:", err);
                            socket.emit("error", {
                                error: "Database query failed",
                            });
                        } else {
                            socket.emit(`response_${object.id}`, {
                                object_type: "get_user",
                                data: data,
                            });
                        }
                    }
                );
            }
        });
    });

    socket.on("disconnect", () => {
        console.log("A user disconnected");
    });
});

// Start the server
const PORT = 3000;
server.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
