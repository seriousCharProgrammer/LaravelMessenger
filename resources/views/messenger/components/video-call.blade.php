
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Call Overlay</title>
    <style>
        :root {
            --colorPrimary: #2180f3;
            --paraColor: #787882;
            --colorBlack: #121212;
            --colorWhite: #ffffff;
            --colorLightBg: #ecf5ff;
            --bodyFont: "Lato", sans-serif;
        }

        body {
            font-family: var(--bodyFont);
            margin: 0;
            padding: 0;
            background-color: var(--colorLightBg);
            color: var(--colorBlack);
        }

        .video-call-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            cursor: pointer;
        }

        .video-call-icon img {
            width: 50px;
            height: 50px;
        }

        .overlay {
            display: none; /* Initially hidden */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .overlay-content {
            background-color: var(--colorWhite);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 90%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .call-status {
            font-size: 1.5rem;
            color: var(--colorPrimary);
            margin-bottom: 20px;
        }

        .video-container {

            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
        }

        .caller-video,
        .receiver-video {
            border-radius: 10px;
            overflow: hidden;
            background-color: var(--colorBlack);
        }

        .caller-video video {
            width: 800px;
            height: 300px;
        }

        .receiver-video video {
            width: 800px;
            height: 600px;
        }

        .end-call-btn {
            background-color: var(--colorPrimary);
            color: var(--colorWhite);
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .end-call-btn:hover {
            background-color: #1a6fd1;
        }

         @media (max-width: 768px) {
            .video-container {
                flex-direction: column;
            }

            .caller-video video,
            .receiver-video video {
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>


    <!-- Overlay -->
    <div class="overlay" id="overlay">
        <div class="overlay-content">
            <div class="call-status">Calling...</div>
            <div class="video-container">
                <div class="caller-video">
                    <video autoplay muted></video>
                </div>
                <div class="receiver-video">
                    <video autoplay></video>
                </div>
            </div>
            <button class="end-call-btn">End Call</button>
        </div>
    </div>
</body>
</html>

