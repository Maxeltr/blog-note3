$( document ).ready(function(){
    //$( "button" ).click(function(){ // задаем функцию при нажатиии на элемент <button>
    document.getElementById('loginLink').onclick = function(event){
        event.preventDefault();
        
        var modalWindow = {
            blockBackground: null,
            dialogWindow: null,
            width: 400,
            
            initBlock: function() {
                this.blockBackground = document.getElementById('blockscreen'); //Получаем наш блокирующий фон по ID

                //Если он не определен, то создадим его
                if (!this.blockBackground) {
                    var parent = document.getElementsByTagName('body')[0];  //Получим первый элемент тега body
                    var obj = parent.firstChild;                            //Для того, чтобы вставить наш блокирующий фон в самое начало тега body
                    this.blockBackground = document.createElement('div');   //Создаем элемент div
                    this.blockBackground.id = 'blockscreen';                //Присваиваем ему наш ID
                    parent.insertBefore(this.blockBackground, obj);         //Вставляем в начало
                    this.blockBackground.onclick = function(e) { 
                        modalWindow.close(e);                                //Добавим обработчик события по нажатию на блокирующий экран - закрыть модальное окно.
                    }; 
                }
                this.blockBackground.style.display = 'inline'; //Установим CSS-свойство        
            },
            
            initWin: function(html) {
                dialogWindow = document.getElementById('modalwindow'); //Получаем наше диалоговое окно по ID
                //Если оно не определено, то также создадим его по аналогии
                if (!dialogWindow) {
                    //var parent = document.getElementsByTagName('body')[0];
                    var parent = document.getElementById('blockscreen');
                    var obj = parent.firstChild;
                    dialogWindow = document.createElement('div');
                    dialogWindow.id = 'modalwindow';
                    //dialogWindow.style.padding = '0 0 5px 0';
                    parent.insertBefore(dialogWindow, obj);
                }
                //dialogWindow.style.width = width + 'px'; //Установим ширину окна
                dialogWindow.style.display = 'inline'; //Зададим CSS-свойство

                dialogWindow.innerHTML = html; //Добавим нужный HTML-текст в наше диалоговое окно

                // закрыть по клику вне окна
//                $(document).mouseup(function (e) { 
//                    var popup = $('#modalwindow');
//                    if (e.target!=popup[0]&&popup.has(e.target).length === 0){
//                        $('#blockscreen').fadeOut();
//
//                    }
//                });
        
                //Установим позицию по центру экрана

                //dialogWindow.style.left = '50%'; //Позиция по горизонтали
                //dialogWindow.style.top = '50%'; //Позиция по вертикали

                //Выравнивание по центру путем задания отрицательных отступов
                //dialogWindow.style.marginTop = -(dialogWindow.offsetHeight / 2) + 'px'; 
                //dialogWindow.style.marginLeft = -(width / 2) + 'px';
            },
            
            close: function(e) {
                var modalwindow = $('#modalwindow');
                var blockscreen = $('#blockscreen');
                
                if (e.target != modalwindow[0] && modalwindow.has(e.target).length === 0){
                    modalwindow.fadeOut();
                    blockscreen.fadeOut();
                }
                //document.getElementById('blockscreen').style.display = 'none';
                //document.getElementById('modalwindow').style.display = 'none';        
            }
    
            

        };
        
        
            
        modalWindow.initBlock();
            
        $.ajax({
            url: '/login',
            type: 'GET',
            cache: false,
            contentType: false,
            processData: false,
            
            //data: ($("#foo").serialize()),
            success: function (data) {
                
                
//                var xmlString = data, parser = new DOMParser(), doc = parser.parseFromString(xmlString, "text/xml");
//                //doc.firstChild; 
//                qw = doc.firstChild;

                var wrapper= document.createElement('html');
                wrapper.innerHTML = data;
                var form = wrapper.getElementsByClassName('site-wrapper')[0];
                
                modalWindow.initWin(form.innerHTML);
                
                document.getElementById('redirect').value = window.location.href;
                
                document.getElementById('login').onclick = function(event){
                    event.preventDefault();
                    
                    $.ajax({
                        url: '/login',
                        type: 'POST',
                        cache: false,
                        contentType: false,
                        processData: false,

                        data: { // данные, которые будут отправлены на сервер
                            email: "Denis",
                            password: "Erebor"
                        },
                        success: function (data) {
                            var wrapper = document.createElement('html');
                            wrapper.innerHTML= data;
                            var message = wrapper.querySelector('#error-login');
                            
                            console.log(message);
                        }
                    });
                };
    
                
                
                
                //var form = data.getElementById('login_user')
                //modalWindow.initWin(500, form);
            }
//            method: "POST", // метод HTTP, используемый для запроса
//            url: "about.php", // строка, содержащая URL адрес, на который отправляется запрос
//            data: { // данные, которые будут отправлены на сервер
//                name: "Denis",
//                city: "Erebor"
//            },
//            success: [
//                function ( msg ) { // функции обратного вызова, которые вызываются если AJAX запрос выполнится успешно (если несколько функций, то необходимо помещать их в массив)
//                    $( "p" ).text( "User saved: " + msg ); // добавляем текстовую информацию и данные возвращенные с сервера
//                },
//                function () { // вызов второй функции из массива
//                    console.log( "next function" );
//                }
//            ],
//            statusCode: {
//                200: function () { // выполнить функцию если код ответа HTTP 200
//                    console.log( "Ok" );
//                }
//            }
        });
    };
    
    
});