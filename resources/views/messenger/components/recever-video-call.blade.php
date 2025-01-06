{{--
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incoming Call Notification</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .notification {
            background-color: var(--colorWhite);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }

        .caller-info h2 {
            margin: 0;
            font-size: 24px;
            color: var(--colorBlack);
        }

        .caller-info p {
            margin: 10px 0 20px;
            font-size: 16px;
            color: var(--paraColor);
        }

        .buttons {
            display: flex;
            justify-content: space-between;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button.decline {
            background-color: #ff4d4d;
            color: var(--colorWhite);
        }

        button.decline:hover {
            background-color: #e60000;
        }

        button.answer {
            background-color: var(--colorPrimary);
            color: var(--colorWhite);
        }

        button.answer:hover {
            background-color: #1a6fd1;
        }

    </style>
</head>
<body>
    <div class="overlay"  id="overlay">
        <div class="notification">
            <div class="caller-info">
                <h2>Incoming Call</h2>
                <p>{{ $callerName ?? 'Unknown Caller' }}</p> <!-- Dynamic caller name -->
            </div>
            <div class="buttons">
                <button class="decline">Decline</button>
                <button class="answer">Answer</button>
            </div>
        </div>
    </div>
</body>
</html>
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incoming Call Notification</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .notification {
            background-color: var(--colorWhite);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%; /* Adjusted for smaller screens */
            max-width: 300px; /* Maximum width for larger screens */
        }

        .caller-info h2 {
            margin: 0;
            font-size: 24px;
            color: var(--colorBlack);
        }

        .caller-info p {
            margin: 10px 0 20px;
            font-size: 16px;
            color: var(--paraColor);
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            flex-direction: row; /* Default layout for larger screens */
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 48%; /* Adjusted for smaller screens */
        }

        button.decline {
            background-color: #ff4d4d;
            color: var(--colorWhite);
        }

        button.decline:hover {
            background-color: #e60000;
        }

        button.answer {
            background-color: var(--colorPrimary);
            color: var(--colorWhite);
        }

        button.answer:hover {
            background-color: #1a6fd1;
        }

        /* Media query for smaller screens */
        @media (max-width: 768px) {
            .notification {
                width: 95%; /* Further adjusted for very small screens */
                padding: 15px;
            }

            .buttons {
                flex-direction: column; /* Stack buttons vertically */
            }

            button {
                width: 100%; /* Full width for stacked buttons */
                margin-bottom: 10px; /* Space between stacked buttons */
            }

            button:last-child {
                margin-bottom: 0; /* Remove margin for the last button */
            }
        }
    </style>
</head>
<body>
    <div class="overlay" id="overlay">
        <div class="notification">
            <div class="caller-info">
                <h2>Incoming Call</h2>
                <p>{{ $callerName ?? 'Unknown Caller' }}</p> <!-- Dynamic caller name -->
            </div>
            <div class="buttons">
                <button class="decline">Decline</button>
                <button class="answer">Answer</button>
            </div>
        </div>
    </div>
</body>
</html>
