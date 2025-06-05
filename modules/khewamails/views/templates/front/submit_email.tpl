<style>
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f6f7f9;
    }

    .khewa-container {
        background: #fff;
        padding: 60px 50px;
        border-radius: 12px;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.08);
        text-align: center;
        width: 100%;
        max-width: 600px;
    }

    .khewa-container img.logo {
        max-width: 130px;
        margin-bottom: 25px;
    }

    .khewa-container h1 {
        margin-bottom: 15px;
        font-size: 30px;
        color: #444;
    }

    .khewa-container .description {
        color: #555;
        font-size: 18px;
        line-height: 1.8;
        margin-bottom: 30px;
        text-align: left;
    }

    #success-message {
        display: none;
        background-color: #dff0d8;
        color: #3c763d;
        padding: 14px;
        border-radius: 6px;
        margin-bottom: 22px;
        font-size: 16px;
    }

    .khewa-input {
        width: 100%;
        padding: 14px 16px;
        font-size: 17px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        margin-bottom: 18px;
        transition: border-color 0.3s;
    }

    .khewa-input:focus {
        border-color: #007bff;
    }

    .khewa-button {
        width: 100%;
        padding: 14px;
        background-color: #007bff;
        color: #fff;
        font-size: 17px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .khewa-button:hover {
        background-color: #0056b3;
    }

    .message-block {
        text-align: center;
        font-size: 17px;
        color: #444;
        margin: 0 auto 30px auto;
        line-height: 1.8;
        max-width: 680px;
    }


    .message-block p {
        margin: 12px 0;
        font-size: 17px;
    }

    .message-block .leaf {
        font-size: 22px;
        margin: 18px 0;
    }
</style>

<div class="khewa-container">
    <img src="{$logo_url}" alt="Khewa Logo" class="logo" />

{*    <h1>{$welcome_message|escape:'htmlall':'UTF-8'}</h1>*}


        <div class="message-block">
            <p><strong>Bienvenu à Khewa!</strong></p>
            <p>Participez à notre tirage mensuel  pour gagner une <strong>carte-cadeau de 25 $</strong></p>
            <p>Votre certificat peut être utilisé en magasin ou en ligne sur <a href="https://www.khewa.com" target="_blank">www.khewa.com</a></p>

            <p class="leaf">🌿</p>

            <p><strong>Welcome to Khewa!</strong></p>
            <p>Enter our monthly draw to win a <strong>$25 Gift Card</strong></p>
            <p>Your certificate can be redeemed in store or online at <a href="https://www.khewa.com" target="_blank">www.khewa.com</a></p>
        </div>
 

    <div id="success-message"></div>
    <input type="text" id="user-name" class="khewa-input" placeholder="Enter your first name" required>

    <input type="email" id="user-email" class="khewa-input" placeholder="Enter your email" required>
    <button class="khewa-button" onclick="submitEmail()">Submit</button>
</div>

<script>
    function submitEmail() {
        var name = document.getElementById('user-name').value.trim();
        var email = document.getElementById('user-email').value.trim();

        if (!name || !email) return;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '{$link->getModuleLink('khewamails', 'submitemail')}', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    var message = document.getElementById('success-message');
                    message.innerHTML = response.message;
                    message.style.display = 'block';
                    message.style.backgroundColor = response.success ? '#d4edda' : '#f8d7da';
                    message.style.color = response.success ? '#155724' : '#721c24';

                    setTimeout(function () {
                        message.style.display = 'none';
                        if (response.success) {
                            document.getElementById('user-email').value = '';
                            document.getElementById('user-name').value = '';
                        }
                    }, 3000);
                } catch (e) {
                    console.error('Invalid response', e);
                }
            }
        };

        xhr.send('ajax=1&action=submitEmail&name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email));

    }
</script>
