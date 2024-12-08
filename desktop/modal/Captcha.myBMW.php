<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

include_file('core', 'authentification', 'php');

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

?>	

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form with hCaptcha</title>
</head>

<body>

    <p></p>
    <div id="captchaResponse">
        <div style="text-align: center;">
            <form id="captcha_form" action="#" method="post">
                <!-- hCaptcha widget -->
                <div class="h-captcha" data-sitekey="7244955f-8f30-4445-adff-4fefe059f815"></div><br>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
            <!-- hCaptcha script -->
            <script src="https://hcaptcha.com/1/api.js" async defer></script>
        </div>
    </div>
    <p></p>
    
    <script>
        
        document.getElementById('captcha_form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission
            const hCaptchaResponse = document.querySelector('[name="h-captcha-response"]').value;
            
            $('#div_captcha').empty();
            $('#div_captcha').append('<input id="captcha" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="hCaptcha" value="'+hCaptchaResponse+'"><span class="input-group-btn" title="{{Résoudre le captcha}}"><a class="btn btn-primary" id="bt_Captcha"><i class="fas fa-key"></i></a></span>');
            $('#mod_captcha').dialog( "close" );
        }); 

    </script>

</body>

</html>
