<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LINE MINI App Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap');

        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f2f5;
        }

        .message-bubble {
            max-width: 80%;
            border-radius: 1.25rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .message-incoming {
            background-color: #ffffff;
            border-bottom-left-radius: 0;
        }

        .message-outgoing {
            background-color: #dcf8c6;
            border-bottom-right-radius: 0;
        }

        .message-gemini {
            background-color: #e0f7fa; /* สีฟ้าอ่อนสำหรับข้อความจาก Gemini */
            border-bottom-left-radius: 0;
        }

        .header {
            background-color: #00b900;
        }

        .input-container {
            background-color: #f0f2f5;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
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

        .dialog-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .dialog-box {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col h-screen">

<!-- Loading Dialog -->
<div id="loading-dialog" class="dialog-container hidden">
    <div class="dialog-box flex flex-col items-center">
        <div class="loader"></div>
        <p class="mt-4 text-gray-700">กำลังเชื่อมต่อ...</p>
    </div>
</div>

<!-- Custom Dialog for alerts -->
<div id="alert-dialog" class="dialog-container hidden">
    <div class="dialog-box flex flex-col items-center">
        <h3 id="alert-title" class="text-xl font-bold mb-2"></h3>
        <p id="alert-message" class="text-gray-700 mb-4"></p>
        <button id="alert-button"
                class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-300">
            ตกลง
        </button>
    </div>
</div>

<!-- Main Chat UI -->
<div id="main-content" class="flex flex-col h-full hidden">
    <header class="header text-white p-4 shadow-lg flex-shrink-0 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-center flex-grow">แชท LINE MINI App</h1>
        <div class="flex items-center">
            <button id="summarize-button"
                    class="bg-green-600 text-white p-2 rounded-full w-12 h-12 flex items-center justify-center transition duration-300 hover:bg-green-700 focus:outline-none ml-2">
                <i class="fas fa-list-alt"></i> ✨
            </button>
            <button id="tts-button"
                    class="bg-blue-500 text-white p-2 rounded-full w-12 h-12 flex items-center justify-center transition duration-300 hover:bg-blue-600 focus:outline-none ml-2">
                <i class="fas fa-volume-up"></i> ✨
            </button>
        </div>
    </header>

    <p class="text-center text-sm opacity-80 mt-2" id="user-id-display"></p>

    <main id="chat-messages" class="flex-grow overflow-y-auto p-4 space-y-2 flex flex-col-reverse">
        <!-- Messages will be injected here -->
    </main>

    <footer class="input-container p-4 border-t border-gray-300 flex items-center flex-shrink-0">
        <input type="text" id="message-input" placeholder="พิมพ์ข้อความ... หรือใช้ /gemini และ /image"
               class="flex-grow p-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400 mr-2">
        <button id="send-button"
                class="bg-green-500 text-white p-3 rounded-full w-12 h-12 flex items-center justify-center transition duration-300 hover:bg-green-600 focus:outline-none">
            <i class="fas fa-paper-plane"></i>
        </button>
    </footer>
</div>

<script type="module">
    import {initializeApp} from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
    import {
        getAuth,
        signInAnonymously,
        signInWithCustomToken,
        onAuthStateChanged
    } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
    import {
        getFirestore,
        collection,
        addDoc,
        query,
        onSnapshot,
        orderBy,
        limit
    } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";
    import {setLogLevel} from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

    // Use setLogLevel('debug') for debugging Firestore.
    setLogLevel('debug');

    // Firestore helper function to show custom alert dialog
    function showCustomAlert(title, message) {
        const alertDialog = document.getElementById('alert-dialog');
        document.getElementById('alert-title').textContent = title;
        document.getElementById('alert-message').textContent = message;
        alertDialog.classList.remove('hidden');
    }

    // Add event listener to the custom alert's button
    document.getElementById('alert-button').addEventListener('click', () => {
        document.getElementById('alert-dialog').classList.add('hidden');
    });

    // Firebase configuration and initialization
    const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');
    const app = initializeApp(firebaseConfig);
    const db = getFirestore(app);
    const auth = getAuth(app);

    const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
    const messagesCollectionRef = collection(db, `artifacts/${appId}/public/data/messages`);

    const loadingDialog = document.getElementById('loading-dialog');
    const mainContent = document.getElementById('main-content');
    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const summarizeButton = document.getElementById('summarize-button');
    const ttsButton = document.getElementById('tts-button');
    const userIdDisplay = document.getElementById('user-id-display');

    let currentUserId = null;
    let recentMessages = [];

    // Sign in anonymously or with custom token
    const authenticate = async () => {
        loadingDialog.classList.remove('hidden');
        try {
            if (typeof __initial_auth_token !== 'undefined' && __initial_auth_token) {
                await signInWithCustomToken(auth, __initial_auth_token);
            } else {
                await signInAnonymously(auth);
            }
        } catch (error) {
            console.error("Error signing in:", error);
            showCustomAlert("ข้อผิดพลาด", "ไม่สามารถเข้าสู่ระบบได้ โปรดลองใหม่อีกครั้ง");
        }
    };

    // Handle authentication state changes
    onAuthStateChanged(auth, (user) => {
        if (user) {
            currentUserId = user.uid;
            loadingDialog.classList.add('hidden');
            mainContent.classList.remove('hidden');
            userIdDisplay.textContent = `UID ของคุณ: ${currentUserId}`;

            // Set up real-time listener for messages
            const q = query(messagesCollectionRef, orderBy("timestamp", "desc"), limit(20)); // Limit to 20 recent messages
            onSnapshot(q, (querySnapshot) => {
                chatMessages.innerHTML = '';
                recentMessages = [];
                querySnapshot.docs.slice().reverse().forEach((doc) => {
                    const messageData = doc.data();
                    recentMessages.push(messageData); // เก็บข้อความล่าสุดไว้ในตัวแปร
                    const isOutgoing = messageData.userId === currentUserId;
                    const isGemini = messageData.userId === 'gemini_bot';

                    const messageElement = document.createElement('div');

                    messageElement.className = `flex ${isOutgoing ? 'justify-end' : 'justify-start'}`;

                    let contentHTML = '';
                    if (messageData.imageUrl) {
                        contentHTML = `<img src="${messageData.imageUrl}" class="rounded-lg shadow-md max-w-full h-auto">`;
                    } else {
                        // Sanitize message content for display
                        const sanitizedText = document.createElement('div');
                        sanitizedText.textContent = messageData.text;
                        contentHTML = `<p class="text-gray-800">${sanitizedText.textContent}</p>`;
                    }

                    messageElement.innerHTML = `
                            <div class="message-bubble ${isOutgoing ? 'message-outgoing' : isGemini ? 'message-gemini' : 'message-incoming'} shadow-md">
                                <p class="text-sm text-gray-500 mb-1">${isOutgoing ? 'คุณ' : messageData.userId === 'gemini_bot' ? 'Gemini ✨' : messageData.userId}</p>
                                ${contentHTML}
                            </div>
                        `;
                    chatMessages.prepend(messageElement);
                });
                // Scroll to the bottom of the chat, but since we prepend, we scroll to the top.
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, (error) => {
                console.error("Error getting messages: ", error);
                showCustomAlert("ข้อผิดพลาด", "ไม่สามารถโหลดข้อความได้");
            });
        } else {
            currentUserId = null;
            // User is signed out, handle as needed
        }
    });

    // Function to call Gemini API for text generation
    const callGeminiApi = async (prompt) => {
        const systemPrompt = "คุณคือผู้ช่วยที่ตอบคำถามอย่างเป็นมิตรและสร้างสรรค์ในแชท ตอบกลับสั้นๆและกระชับ";
        const userQuery = prompt;
        const apiKey = "";
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=${apiKey}`;

        const payload = {
            contents: [{parts: [{text: userQuery}]}],
            systemInstruction: {
                parts: [{text: systemPrompt}]
            },
        };

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            const text = result?.candidates?.[0]?.content?.parts?.[0]?.text;
            return text || "ขออภัย, เกิดข้อผิดพลาดในการตอบกลับ";
        } catch (e) {
            console.error("Error calling Gemini API:", e);
            return "ขออภัย, ไม่สามารถเชื่อมต่อกับบอทได้ในขณะนี้";
        }
    };

    // Function to handle sending message, including Gemini bot logic
    const sendMessage = async () => {
        const messageText = messageInput.value.trim();
        if (!currentUserId || messageText === '') return;

        // Add the user's prompt to the chat
        const userMessage = {
            userId: currentUserId,
            text: messageText,
            timestamp: new Date()
        };
        await addDoc(messagesCollectionRef, userMessage);

        // Check if the message is a command for the Gemini bot
        if (messageText.startsWith('/gemini')) {
            const prompt = messageText.substring('/gemini'.length).trim();

            // Show a temporary message to indicate the bot is thinking
            const thinkingMessage = {
                userId: 'gemini_bot',
                text: 'กำลังคิด... โปรดรอสักครู่',
                timestamp: new Date()
            };
            await addDoc(messagesCollectionRef, thinkingMessage);

            // Call the Gemini API and add the response to the chat
            const botResponse = await callGeminiApi(prompt);

            const responseMessage = {
                userId: 'gemini_bot',
                text: botResponse,
                timestamp: new Date()
            };
            await addDoc(messagesCollectionRef, responseMessage);

        } else if (messageText.startsWith('/image')) {
            const prompt = messageText.substring('/image'.length).trim();

            // Show a temporary message to indicate image generation is in progress
            const thinkingMessage = {
                userId: 'gemini_bot',
                text: 'กำลังสร้างรูปภาพ... โปรดรอสักครู่',
                timestamp: new Date()
            };
            await addDoc(messagesCollectionRef, thinkingMessage);

            // Call the image generation API
            const imageUrl = await generateImage(prompt);

            const imageMessage = {
                userId: 'gemini_bot',
                text: `รูปภาพจาก Gemini สำหรับ: ${prompt}`,
                imageUrl: imageUrl,
                timestamp: new Date()
            };
            await addDoc(messagesCollectionRef, imageMessage);

        }
        messageInput.value = ''; // Clear input after handling command
    };

    // Function to summarize chat using Gemini API
    const summarizeChat = async () => {
        if (recentMessages.length === 0) {
            showCustomAlert("ไม่พบข้อมูล", "ไม่มีข้อความในแชทที่จะสรุป");
            return;
        }

        const chatHistory = recentMessages.map(msg => `${msg.userId}: ${msg.text}`).join('\n');
        const prompt = `สรุปบทสนทนาต่อไปนี้ให้เป็นข้อความสั้นๆ: \n\n${chatHistory}`;

        const thinkingMessage = {
            userId: 'gemini_bot',
            text: 'กำลังสรุปแชท... โปรดรอสักครู่',
            timestamp: new Date()
        };
        await addDoc(messagesCollectionRef, thinkingMessage);

        const summary = await callGeminiApi(prompt);

        const summaryMessage = {
            userId: 'gemini_bot',
            text: `สรุปแชท:\n${summary}`,
            timestamp: new Date()
        };
        await addDoc(messagesCollectionRef, summaryMessage);
    };

    // Function to read the last message aloud using TTS API
    const textToSpeech = async () => {
        if (recentMessages.length === 0 || recentMessages[recentMessages.length - 1].text.trim() === '') {
            showCustomAlert("ไม่พบข้อความ", "ไม่มีข้อความในแชทที่จะอ่าน");
            return;
        }

        const textToRead = recentMessages[recentMessages.length - 1].text;
        const audioUrl = await callGeminiTtsApi(textToRead);

        if (audioUrl) {
            const audio = new Audio(audioUrl);
            audio.play();
        } else {
            showCustomAlert("ข้อผิดพลาด", "ไม่สามารถสร้างเสียงได้ โปรดลองอีกครั้ง");
        }
    };

    // Helper function to convert base64 to ArrayBuffer
    function base64ToArrayBuffer(base64) {
        const binaryString = window.atob(base64);
        const len = binaryString.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }
        return bytes.buffer;
    }

    // Helper function to convert PCM audio to WAV blob
    function pcmToWav(pcm16, sampleRate) {
        const buffer = new ArrayBuffer(44 + pcm16.length * 2);
        const view = new DataView(buffer);

        let offset = 0;
        const writeString = (str) => {
            for (let i = 0; i < str.length; i++) {
                view.setUint8(offset++, str.charCodeAt(i));
            }
        };

        // RIFF header
        writeString('RIFF');
        view.setUint32(offset, 36 + pcm16.length * 2, true);
        offset += 4;
        writeString('WAVE');

        // fmt chunk
        writeString('fmt ');
        view.setUint32(offset, 16, true);
        offset += 4;
        view.setUint16(offset, 1, true);
        offset += 2; // Audio format (1 = PCM)
        view.setUint16(offset, 1, true);
        offset += 2; // Number of channels
        view.setUint32(offset, sampleRate, true);
        offset += 4; // Sample rate
        view.setUint32(offset, sampleRate * 2, true);
        offset += 4; // Byte rate
        view.setUint16(offset, 2, true);
        offset += 2; // Block align
        view.setUint16(offset, 16, true);
        offset += 2; // Bits per sample

        // data chunk
        writeString('data');
        view.setUint32(offset, pcm16.length * 2, true);
        offset += 4;

        for (let i = 0; i < pcm16.length; i++) {
            view.setInt16(offset, pcm16[i], true);
            offset += 2;
        }

        return new Blob([view], {type: 'audio/wav'});
    }

    // Call Gemini TTS API
    const callGeminiTtsApi = async (text) => {
        const apiKey = "";
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-tts:generateContent?key=${apiKey}`;

        const payload = {
            contents: [{
                parts: [{text: text}]
            }],
            generationConfig: {
                responseModalities: ["AUDIO"],
                speechConfig: {
                    voiceConfig: {
                        prebuiltVoiceConfig: {voiceName: "Puck"}
                    }
                }
            },
            model: "gemini-2.5-flash-preview-tts"
        };

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            const part = result?.candidates?.[0]?.content?.parts?.[0];
            const audioData = part?.inlineData?.data;
            const mimeType = part?.inlineData?.mimeType;

            if (audioData && mimeType && mimeType.startsWith("audio/")) {
                const sampleRate = parseInt(mimeType.match(/rate=(\d+)/)[1], 10);
                const pcmData = base64ToArrayBuffer(audioData);
                const pcm16 = new Int16Array(pcmData);
                const wavBlob = pcmToWav(pcm16, sampleRate);
                return URL.createObjectURL(wavBlob);
            } else {
                console.error("No audio data returned from TTS API.");
                return null;
            }
        } catch (e) {
            console.error("Error calling TTS API:", e);
            return null;
        }
    };

    // Call Gemini Image Generation API
    const generateImage = async (prompt) => {
        const payload = {instances: {prompt: prompt}, parameters: {"sampleCount": 1}};
        const apiKey = "";
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-002:predict?key=${apiKey}`;

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.predictions && result.predictions.length > 0 && result.predictions[0].bytesBase64Encoded) {
                return `data:image/png;base64,${result.predictions[0].bytesBase64Encoded}`;
            } else {
                console.error("No image data returned from image generation API.");
                return null;
            }
        } catch (e) {
            console.error("Error calling image generation API:", e);
            return null;
        }
    };

    // Event listeners
    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    summarizeButton.addEventListener('click', summarizeChat);
    ttsButton.addEventListener('click', textToSpeech);

    // Start the authentication process on page load
    window.onload = authenticate;
</script>

</body>
</html>

