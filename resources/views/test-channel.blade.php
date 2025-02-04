<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pusher Test</title>
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
    }
    #messages {
      border: 1px solid #ccc;
      padding: 10px;
      max-width: 600px;
      margin: 20px auto;
      height: 300px;
      overflow-y: auto;
    }
    .message {
      padding: 10px;
      margin-bottom: 10px;
      background: #f4f4f4;
      border-radius: 5px;
    }
  </style>
</head>
<body>
  <h1 style="text-align: center;">Pusher Test</h1>
  <div id="messages">
  </div>

  <script>
    Pusher.logToConsole = true;

    var pusher = new Pusher('7622003dc2b0738bcd61', {
      cluster: 'eu'
    });

    var channel = pusher.subscribe('service-requested.1');
    channel.bind('periodic-examination', function(data) {
      var message = data.order;

      var messageDiv = document.createElement('div');
      messageDiv.className = 'message';
      messageDiv.textContent = message;

      var messagesContainer = document.getElementById('messages');
      messagesContainer.appendChild(messageDiv);

      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    });
  </script>
</body>
</html>
