

if (document.readyState !== 'loading') {
    onload();
} else {
    document.addEventListener('DOMContentLoaded', onload);
}

function onload() {
    let loginLink = document.getElementById('loginLink');
    if (loginLink && window.location.href !== 'http://blog-note3/login') {
        loginLink.onclick = loginProcess;
    }
}

let form = new LoginForm(new Ajax());
let popup = new PopupWindow();

function loginProcess(event) {
    event.preventDefault();

//    let popup = new PopupWindow();
    popup.showOverlay();

//    let form = new LoginForm(new Ajax());
    form.getLoginForm(showForm);
    
    
}

function showForm(loginForm) {
    if (loginForm) {
        popup.showWindow(loginForm);
        
        let loginButton = document.getElementById('login-button');
        if (loginButton) {
            loginButton.onclick = postLoginForm;
        } else {
            window.location.href = '/login';
        }
    
    }
    
    
}

function postLoginForm(event) {
    event.preventDefault();
    form.postLoginForm(handleLoginResponse);
}

function handleLoginResponse(data) {
    if (data.error === false) {
        let wrapper = document.createElement('html');
        wrapper.innerHTML = data.data;
        let signoutMenu = wrapper.querySelector('#sign-out-menu');
        if (signoutMenu) {
            let signinMenu = document.getElementById('sign-in-menu');
            signinMenu.parentNode.replaceChild(signoutMenu, signinMenu);
        }
        popup.close();
    } else {
        let wrapper = document.createElement('html');
        wrapper.innerHTML = data.data;
        let overlay = document.getElementById('overlay');
        if (overlay) {
            overlay.appendChild(getLoginErrorMessages(wrapper));
        }
        updateClassNameOfLoginInputs(wrapper);
    }
}

function getLoginErrorMessages(wrapper) {
    let describeError = wrapper.querySelector('#error-login');
    if (! describeError) {
        describeError = document.createElement('div');
        describeError.className = 'container';
        describeError.id = 'error-login';
        describeError.innerHTML = '<div class="alert alert-danger alert-dismissible fade in" role="alert">'
            + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">X</span></button>'
            + 'Unknown error.</div>';
    }
    describeError.style.marginTop = '35px';
    
    return describeError;
}

function updateClassNameOfLoginInputs(wrapper) {
    let modalWindow = document.getElementById('login_user');
    if (modalWindow) {
        let email = modalWindow.querySelector('#login-email').parentNode;
        let newEmail = wrapper.querySelector('#login-email').parentNode;
        if (email && newEmail && (email.className !== newEmail.className)) {
            email.className = newEmail.className;
        }
        let password = modalWindow.querySelector('#login-password').parentNode;
        let newPassword = wrapper.querySelector('#login-password').parentNode;
        if (password && newPassword && (password.className !== newPassword.className)) {
            password.className = newPassword.className;
        }
    }
}
    
function LoginForm(ajax) {
    if (typeof this.getLoginForm !== 'function') {
        LoginForm.prototype.getLoginForm = function (callback) {
            ajax.makeRequest(
                    'GET',
                    '/login',
                    {
                        'success': function (xhr) {
                            let wrapper = document.createElement('html');
                            wrapper.innerHTML = xhr.responseText;
                            let form = wrapper.getElementsByClassName('site-wrapper');
                            if (form.length !== 0) {
                                callback(form[0].innerHTML);
                            } else {
                                callback(null);
                            }
                        },
                        'error': function () {
                            callback(null);
                        }
                    },
                    {},
                    {
                        'async': true
                    }
            );
        };
    }

    if (typeof this.postLoginForm !== 'function') {
        LoginForm.prototype.postLoginForm = function (callback) {
            ajax.makeRequest(
                'POST',
                '/login',
                {
                    'success': function (xhr) {
                        var wrapper = document.createElement('html');
                        wrapper.innerHTML = xhr.responseText;
                        let result = wrapper.querySelector('#sign-out-menu ');
                        if (!result) {
//                            var error = wrapper.querySelector('#error-login');
//                            if (error) {	//if invalid email/password
//                                var invalidCredentials = wrapper.querySelector('#error-message');
//                                var invalidEmail = wrapper.querySelector('#error-email');
//                                var invalidPassword = wrapper.querySelector('#error-password');
//                                callback(
//                                        {
//                                            'error': true,
//                                            'messages': {
//                                                'status': xhr.statusText,
//                                                'invalidCredentials': invalidCredentials ? invalidCredentials.innerText : null,
//                                                'invalidEmail': invalidEmail ? invalidEmail.innerText : null,
//                                                'invalidPassword': invalidPassword ? invalidPassword.innerText : null
//                                            },
//                                            'data': xhr.responseText
//                                        }
//                                );

//                                return;
//                            }
                            callback(
                                {
                                    'error': true,
                                    'messages': {
                                        'status': xhr.statusText,
                                        //'invalidCredentials': invalidCredentials ? invalidCredentials.innerText : null,
                                        //'invalidEmail': invalidEmail ? invalidEmail.innerText : null,
                                        //'invalidPassword': invalidPassword ? invalidPassword.innerText : null
                                    },
                                    'data': xhr.responseText
                                }
                            );
                    
                            return;
                        }
                        
                        callback({'error': false, 'messages': {'status': xhr.statusText}, 'data': xhr.responseText});
                    },
                    'error': function (xhr) {
                        callback({'error': true, 'messages': {'status': xhr.statusText}, 'data': xhr.responseText});
                    }
                },
                {
                    'email': document.querySelector('#login-email').value, 
                    'password': document.querySelector('#login-password').value,
                    'redirect': window.location.href,
                    'login_csrf': document.querySelector('#login_csrf').value
                },
                {
                    'async': true
                }
            );
        };
    }
}

function Ajax() {
    if (typeof this.makeRequest !== 'function') {
        Ajax.prototype.makeRequest = function (method, url, callbacks, data, config) {
            callbacks = callbacks || {};
            config = config || {};
            config.async = !!config.async;
            config.headers = config.headers || {};		//add test it
            method = method || 'GET';

            var xhr = new XMLHttpRequest();

            if (method === 'GET') {
                url = this.urlEncode(url, data);
            }

            if (xhr) {
                xhr.open(method, url, config.async);

                config.headers["Accept"] = config.headers["Accept"] || "text/html";
                config.headers["X-Requested-With"] = config.headers["X-Requested-With"] || "XMLHttpRequest";
                for (var header in config.headers) {																	//add
                    xhr.setRequestHeader(header, config.headers[header]);
                }

                xhr.onreadystatechange = function () {
                    if (xhr.readyState !== 4) {
                        return;
                    }

                    if (xhr.status >= 200 && xhr.status < 300) {
                        callbacks.success(this);
                    } else {
                        callbacks.error(this);
                    }

                };

                if (method === 'GET') {
                    xhr.send();
                } else {
                    var formData = new FormData();
                    var keys = Object.keys(data);
                    for (var i = 0; i < keys.length; i++) {
                        formData.append(keys[i], data[keys[i]]);
                    }
                    xhr.send(formData);
                }
            }
        };
    }

    if (typeof this.urlEncodeParams !== 'function') {
        Ajax.prototype.urlEncodeParams = function (data, prefix) {
            prefix = prefix || "";
            if (data) {
                var encodedParams = [];
                for (var k in data) {
                    if (typeof data[k] !== 'undefined' && data[k] !== null) {
                        if (typeof data[k] === "object") {
                            var encodedObject = this.urlEncodeParams(data[k], prefix + encodeURIComponent(k) + ".");
                            encodedParams = encodedParams.concat(encodedObject.split("&"));
                        } else {
                            encodedParams.push(prefix + encodeURIComponent(k) + "=" + encodeURIComponent(data[k]));
                        }
                    }
                }
                return encodedParams.join("&");
            }
            return null;
        };
    }

    if (typeof this.urlEncode !== 'function') {
        Ajax.prototype.urlEncode = function (url, data) {
            var encodedParams = this.urlEncodeParams(data);
            if ((encodedParams === null || url.indexOf(encodedParams)) >= 0 || (/(\&$)/).test(url)) {
                return url;
            }
            url += ((/(\?)/).test(url) ? "&" : "?") + encodedParams;
            return url;
        };
    }
}

function PopupWindow() {
    if (typeof this.showOverlay !== 'function') {
        PopupWindow.prototype.showOverlay = function () {
            var overlay = document.getElementById('overlay');

            if (!overlay) {
                var parent = document.getElementsByTagName('body')[0];  //Получим первый элемент тега body
                var element = parent.firstChild;                        //Для того, чтобы вставить наш блокирующий фон в самое начало тега body
                overlay = document.createElement('div');                //Создаем элемент div
                overlay.id = 'overlay';                                 //Присваиваем ему наш ID
                parent.insertBefore(overlay, element);                  //Вставляем в начало
                overlay.onclick = function (event) {
                    this.closeByEvent(event);                                  //Добавим обработчик события по нажатию на блокирующий экран - закрыть модальное окно.
                }.bind(this);
            }

            overlay.style.display = 'inline';                           //Установим CSS-свойство
        };
    }

    if (typeof this.showWindow !== 'function') {
        PopupWindow.prototype.showWindow = function (html) {
            var dialogWindow = document.getElementById('modalwindow');

            if (!dialogWindow) {
                var parent = document.getElementById('overlay');
                var element = parent.firstChild;
                dialogWindow = document.createElement('div');
                dialogWindow.id = 'modalwindow';
                parent.insertBefore(dialogWindow, element);
            }

            dialogWindow.style.display = 'inline';
            dialogWindow.innerHTML = html;
        };
    }

    if (typeof this.closeByEvent !== 'function') {
        PopupWindow.prototype.closeByEvent = function (event) {
            var dialogWindow = document.getElementById('modalwindow');
            //var overlay = document.getElementById('overlay');

            if (event.target.firstChild === dialogWindow) {
                //overlay.style.display = 'none';
                //dialogWindow.style.display = 'none';
                this.close();
            }
        };
    }
    
    if (typeof this.close !== 'function') {
        PopupWindow.prototype.close = function () {
            let overlayWindow = document.getElementById("overlay");
            let errors = overlayWindow.querySelectorAll('#error-login');
            for (let i = 0; i < errors.length; i++) {
                errors[i].remove();
            }
            document.getElementById('modalwindow').style.display = 'none';
            overlayWindow.style.display = 'none';
        };
    }
}




//
//$( document ).ready(function(){
//    //$( "button" ).click(function(){ // задаем функцию при нажатиии на элемент <button>
//    document.getElementById('loginLink').onclick = function(event){
//        event.preventDefault();
//        
//        var modalWindow = {
//            blockBackground: null,
//            dialogWindow: null,
//            width: 400,
//            
//            initBlock: function() {
//                this.blockBackground = document.getElementById('blockscreen'); //Получаем наш блокирующий фон по ID
//
//                //Если он не определен, то создадим его
//                if (!this.blockBackground) {
//                    var parent = document.getElementsByTagName('body')[0];  //Получим первый элемент тега body
//                    var obj = parent.firstChild;                            //Для того, чтобы вставить наш блокирующий фон в самое начало тега body
//                    this.blockBackground = document.createElement('div');   //Создаем элемент div
//                    this.blockBackground.id = 'blockscreen';                //Присваиваем ему наш ID
//                    parent.insertBefore(this.blockBackground, obj);         //Вставляем в начало
//                    this.blockBackground.onclick = function(e) { 
//                        modalWindow.close(e);                                //Добавим обработчик события по нажатию на блокирующий экран - закрыть модальное окно.
//                    }; 
//                }
//                this.blockBackground.style.display = 'inline'; //Установим CSS-свойство        
//            },
//            
//            initWin: function(html) {
//                dialogWindow = document.getElementById('modalwindow'); //Получаем наше диалоговое окно по ID
//                //Если оно не определено, то также создадим его по аналогии
//                if (!dialogWindow) {
//                    //var parent = document.getElementsByTagName('body')[0];
//                    var parent = document.getElementById('blockscreen');
//                    var obj = parent.firstChild;
//                    dialogWindow = document.createElement('div');
//                    dialogWindow.id = 'modalwindow';
//                    //dialogWindow.style.padding = '0 0 5px 0';
//                    parent.insertBefore(dialogWindow, obj);
//                }
//                //dialogWindow.style.width = width + 'px'; //Установим ширину окна
//                dialogWindow.style.display = 'inline'; //Зададим CSS-свойство
//
//                dialogWindow.innerHTML = html; //Добавим нужный HTML-текст в наше диалоговое окно
//
//                // закрыть по клику вне окна
////                $(document).mouseup(function (e) { 
////                    var popup = $('#modalwindow');
////                    if (e.target!=popup[0]&&popup.has(e.target).length === 0){
////                        $('#blockscreen').fadeOut();
////
////                    }
////                });
//        
//                //Установим позицию по центру экрана
//
//                //dialogWindow.style.left = '50%'; //Позиция по горизонтали
//                //dialogWindow.style.top = '50%'; //Позиция по вертикали
//
//                //Выравнивание по центру путем задания отрицательных отступов
//                //dialogWindow.style.marginTop = -(dialogWindow.offsetHeight / 2) + 'px'; 
//                //dialogWindow.style.marginLeft = -(width / 2) + 'px';
//            },
//            
//            close: function(e) {
//                var modalwindow = $('#modalwindow');
//                var blockscreen = $('#blockscreen');
//                
//                if (e.target != modalwindow[0] && modalwindow.has(e.target).length === 0){
//                    modalwindow.fadeOut();
//                    blockscreen.fadeOut();
//                }
//                //document.getElementById('blockscreen').style.display = 'none';
//                //document.getElementById('modalwindow').style.display = 'none';        
//            }
//    
//            
//
//        };
//        
//        
//            
//        modalWindow.initBlock();
//            
//        $.ajax({
//            url: '/login',
//            type: 'GET',
//            cache: false,
//            contentType: false,
//            processData: false,
//            
//            //data: ($("#foo").serialize()),
//            success: function (data) {
//                
//                
////                var xmlString = data, parser = new DOMParser(), doc = parser.parseFromString(xmlString, "text/xml");
////                //doc.firstChild; 
////                qw = doc.firstChild;
//
//                var wrapper= document.createElement('html');
//                wrapper.innerHTML = data;
//                var form = wrapper.getElementsByClassName('site-wrapper')[0];
//                
//                modalWindow.initWin(form.innerHTML);
//                
//                document.getElementById('redirect').value = window.location.href;
//                
//                document.getElementById('login').onclick = function(event){
//                    event.preventDefault();
//                    
//                    $.ajax({
//                        url: '/login',
//                        type: 'POST',
//                        cache: false,
//                        contentType: false,
//                        processData: false,
//
//                        data: { // данные, которые будут отправлены на сервер
//                            email: "Denis",
//                            password: "Erebor"
//                        },
//                        success: function (data) {
//                            var wrapper = document.createElement('html');
//                            wrapper.innerHTML= data;
//                            var message = wrapper.querySelector('#error-login');
//                            
//                            console.log(message);
//                        }
//                    });
//                };
//    
//                
//                
//                
//                //var form = data.getElementById('login_user')
//                //modalWindow.initWin(500, form);
//            }
////            method: "POST", // метод HTTP, используемый для запроса
////            url: "about.php", // строка, содержащая URL адрес, на который отправляется запрос
////            data: { // данные, которые будут отправлены на сервер
////                name: "Denis",
////                city: "Erebor"
////            },
////            success: [
////                function ( msg ) { // функции обратного вызова, которые вызываются если AJAX запрос выполнится успешно (если несколько функций, то необходимо помещать их в массив)
////                    $( "p" ).text( "User saved: " + msg ); // добавляем текстовую информацию и данные возвращенные с сервера
////                },
////                function () { // вызов второй функции из массива
////                    console.log( "next function" );
////                }
////            ],
////            statusCode: {
////                200: function () { // выполнить функцию если код ответа HTTP 200
////                    console.log( "Ok" );
////                }
////            }
//        });
//    };
//    
//    
//});