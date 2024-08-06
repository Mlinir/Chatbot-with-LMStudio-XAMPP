<?php
session_start();
$_SESSION['id'] = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background-color: black;
            color: white;
        }

        #chatLogs .log-entry {
            transition: background-color 0.3s, border-radius 0.3s;
        }

        #chatLogs .log-entry:hover {
            background-color: white;
            border-radius: 10px;
            color: black;
        }

        .sidebar {
            width: 300px;
            background-color: #343a40;
            overflow-y: auto;
            padding: 15px;
        }

        .sidebar h2 {
            color: white;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
        }

        #response {
            flex-grow: 1;
            overflow-y: auto;
            border: 1px solid #444;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #222;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .loading-spinner {
            display: none;
            border: 8px solid #f3f3f3;
            border-radius: 50%;
            border-top: 8px solid #3498db;
            width: 40px;
            height: 40px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Chat Logs</h2>
        <input class="btn btn-primary" type="button" value="New chat" id="newChatButton">
        <div id="chatLogs">
        </div>
    </div>

    <div class="main-content">
        <h1 class="text-center mb-4">Unesite zahtjev:</h1>
        <div id="response" class="mb-4 border p-3"></div>
        <form id="chatForm">
            <div class="input-group">
                <input type="text" name="prompt" id="input" class="form-control" placeholder="Type your request..." required />
                <div class="input-group-append">
                    <input type="submit" class="btn btn-primary" value="Generate" id="submitButton" />
                    <div id="spinner" class="loading-spinner"></div>
                </div>
            </div>
        </form>
        <p class="text-center mt-3"><a href="logout.php" class="btn btn-danger">Logout</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        let conversationHistory = [];

        async function fetchChatLogs() {
            try {
                const response = await fetch('view_logs.php');
                const logs = await response.text();
                document.getElementById('chatLogs').innerHTML = logs;
            } catch (error) {
                console.error('Error fetching chat logs:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', fetchChatLogs);

        document.getElementById('chatLogs').addEventListener('click', async function(event) {
            const responseContainer = document.getElementById('response');
            responseContainer.innerHTML = "";
            const logEntry = event.target.closest('.log-entry');
            if (event.target.tagName === 'INPUT') {
                try {
                    const response = await fetch('delete_log.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `log_id=${logEntry.id}`
                    });

                    const result = await response.text();
                    //console.log(result);
                    document.getElementById(logEntry.id).remove();
                } catch (error) {
                    console.error('Error deleting log entry:', error);
                }
            } else {
                try {
                    const response = await fetch('load_log.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `log_id=${logEntry.id}`
                    });

                    const data = await response.json();
                    conversationHistory = data;
                    data.forEach(function(e) {
                        let upper = e.role.charAt(0).toUpperCase() + e.role.slice(1);
                        responseContainer.innerHTML += `<div class="mt-2"><strong>${upper}:</strong> ${e.content}</div>`;
                    });

                    document.getElementById('input').value = "";
                    try {
                        const response = await fetch('update_session.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                id: logEntry.id
                            }),
                        });

                        const result = await response.text();
                        //console.log(result);
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    }

                } catch (error) {
                    console.error('Error loading log entry:', error);
                }
            }
        });

        document.getElementById('newChatButton').addEventListener('click', async function() {
            try {
                const response = await fetch('reset_session.php');
                const message = await response.text();
                console.log(message);
                const responseContainer = document.getElementById('response');
                responseContainer.innerHTML = "";
                conversationHistory = [];
            } catch (error) {
                console.error('Error resetting session:', error);
            }
        });

        document.getElementById('chatForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const submitButton = document.getElementById('submitButton');
            const spinner = document.getElementById('spinner');
            const responseContainer = document.getElementById('response');
            submitButton.style.display = 'none';
            spinner.style.display = 'block';

            const formData = new FormData(this);
            const prompt = formData.get('prompt');

            conversationHistory.push({
                role: 'user',
                content: prompt
            });

            try {
                const response = await fetch('backend.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation: conversationHistory
                    })
                });

                const data = await response.json();
                if (data.response) {
                    conversationHistory.push({
                        role: 'assistant',
                        content: data.response
                    });
                    responseContainer.innerHTML += `<div class="mt-2"><strong>User:</strong> ${prompt}</div>`;
                    responseContainer.innerHTML += `<div class="mt-2"><strong>Assistant:</strong> ${data.response}</div>`;
                    document.getElementById('input').value = "";
                } else if (data.error) {
                    responseContainer.innerHTML += `<div class="mt-2"><strong>Error:</strong> ${data.error}</div>`;
                }
            } catch (error) {
                responseContainer.innerHTML += `<div class="mt-2"><strong>Error:</strong> ${error.message}</div>`;
            } finally {
                submitButton.style.display = 'block';
                spinner.style.display = 'none';
            }
            fetchChatLogs();
        });
    </script>
</body>

</html>