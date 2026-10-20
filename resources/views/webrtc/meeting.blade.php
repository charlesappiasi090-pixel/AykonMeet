<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover">
    <title>AykonMeet - Online Session</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 30px;
        }

        h1 {
            color: #1e3557;
        }

        .video-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .video-box {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        video {
            width: 100%;
            max-width: 100%;
            background: black;
            border-radius: 8px;
        }

        button {
            padding: 12px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: #1e3557;
            color: white;
            min-height: 44px;
        }
        @media (max-width: 640px) {
            body { padding: 16px; }
            h1 { font-size: 26px; }
            .video-box { width: 100%; }
            button { width: 100%; }
        }
    </style>
</head>

<body>

    <h1>AykonMeet Online Session</h1>

    <div class="video-container">

        <div class="video-box">
            <h3>My Camera</h3>
            <video id="localVideo" autoplay muted playsinline></video>
        </div>

        <div class="video-box">
            <h3>Participant</h3>
            <video id="remoteVideo" autoplay playsinline></video>
        </div>

    </div>

    <button id="startCamera">Start Camera & Microphone</button>

    <script>
        const localVideo = document.getElementById('localVideo');
        const startCamera = document.getElementById('startCamera');

        startCamera.addEventListener('click', async () => {

            try {

                const stream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });

                localVideo.srcObject = stream;

            } catch (error) {

                console.error(error);

                alert('Unable to access camera or microphone.');

            }

        });
    </script>

</body>
</html>