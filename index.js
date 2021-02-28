jQuery(function($) {

    function clearMain() {
        $('.main').html('');
    }

    function searchLog() {
        let main = $('.main');
        let searchCondition = $('<input>').attr({
            type: 'text',
            name: 'search_condition',
            placeholder: '検索条件'
        });
        let from = $('<input>').attr({
            type: 'datetime-local',
            name: 'from'
        });
        let to = $('<input>').attr({
            type: 'datetime-local',
            name: 'to'
        });
        let button = $('<button>').addClass('search').text('検索');
        let div = $('<div>').append(searchCondition).append(from).append('~').append(to).append(button);
        main.append(div);

        button.on('click', function() {
            let from = $('input[name=from]');
            let to = $('input[name=to]');
            let search_condition = $('input[name=search_condition]');
            let name = '';
            let idm = '';

            if ((from.val() != '' && to.val() != '') || search_condition.val() != '') {

                if (search_condition != '') {
                    if (search_condition.val().match('^[0-9a-fA-F]{16}$')) {
                        idm = search_condition.val();
                    } else {
                        name = search_condition.val();
                    }
                }

                if (from.val() != '' && to.val() != '') {
                    from = from.val();
                    to = to.val();
                } else {
                    from = '';
                    to = '';
                }

                console.log(idm, name, from, to);

                $.ajax({
                    url : 'api.php',
                    type : 'GET',
                    dataType : 'json',
                    data : {
                        command : 'search',
                        idm : idm,
                        name : name,
                        from : from,
                        to : to
                    }
                }).done(function(data) {

                    if (data.log) {
                        let main = $('.main'); 
                        let ul = $('<ul>');
                        data.log.forEach(function(element) {
                            let li = $('<li>');
                            let time = $('<div>').addClass('time').text(element.enter_time+'~'+element.exit_time);
                            let name = $('<div>').addClass('name').text(element.name);
        
                            li.append(time).append(name);
                            ul.append(li);
                        });
                        main.append(ul);

                    } else {
                        let li = $('<li></li>').text('検索に一致するログが見つかりませんでした');
                        ul.append(li);
                    }
                }).fail(function(data) {
                    alert('通信に失敗しました');
                });

            } else {
                alert('入力値が正しくありません');
            }
        });
    }

    function home(date) {
        $.ajax({
            url : 'api.php',
            type : 'GET',
            dataType : 'json',
            data : {
                command : 'search',
                from : encodeURI(date+' '+'00:00:00'),
                to : encodeURI(date+' '+'23:59:59')
            }
        }).done(function(retData) {
            let canvas = $('<canvas>');
            $('.main').append(canvas);

            data = [];
            labels = [];
            for (let i = 0; i < 24; i++) {
                data[i] = 0;
                labels[i] = i;
            }

            retData.log.forEach(function(element) {
                data[Number(element.enter_time.slice(11, 13))]++;
            });

            new Chart(canvas, {
                type : 'bar',
                data : {
                    labels : labels,
                    datasets : [
                        {
                            label : '入室者数',
                            data : data,
                            backgroundColor : 'rgba(130, 201, 169,)'
                        }
                    ]
                },
                options : {
                    title : {
                        display : true,
                        text : '今日の入室者数'
                    },
                    scales : {
                        yAxes : [{
                            ticks : {
                                suggestedMax : 50,
                                suggestedMin : 0,
                                stepSize : 5,
                                callback : function(value, index, values) {
                                    return value + '人'
                                }
                            }
                        }]
                    },
                }
            });
        });
    }

    function changePassword() {
        let passwordText = $('<input>').attr({
            type: 'password',
            name: 'password',
            placeholder: 'パスワード'
        });
        let newPasswordText = $('<input>').attr({
            type: 'password',
            name: 'newPassword',
            placeholder: '新しいパスワード'
        });
        let button = $('<button>').text('変更');
        let wrapper = $('<div>').addClass('wrapper');
        let passwordForm = $('<div>').addClass('passwordForm');
        
        wrapper.append(passwordText).append(newPasswordText).append(button);
        passwordForm.append(wrapper);
        $('.main').append(passwordForm);

        button.on('click', function() {
            let password = $('input[name=password]').val();
            let newPassword = $('input[name=newPassword]').val();

            console.log(newPassword);

            if (password !== '' && newPassword !== '') {
                $.ajax({
                    url: 'api.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        command: 'update_password',
                        password: password,
                        newPassword: newPassword,
                    }
                }).done(function(data) {
                    if (data.result === 'success') {
                        alert('変更しました');
                        $('input[name=password]').val('');
                        $('input[name=newPassword]').val('');
                    } else {
                        alert('失敗しました');
                    }
                })
            } else {
                alert('入力項目を確認してください');
            }
        });
    }

    function changeUserName() {
        let passwordText = $('<input>').attr({
            type: 'password',
            name: 'password',
            placeholder: 'パスワード'
        });
        let newUserNameText = $('<input>').attr({
            type: 'text',
            name: 'newName',
            placeholder: '新しいユーザ名'
        });
        let button = $('<button>').text('変更');
        let wrapper = $('<div>').addClass('wrapper');
        let changeUserNameForm = $('<div>').addClass('changeUserNameForm');
        
        wrapper.append(passwordText).append(newUserNameText).append(button);
        changeUserNameForm.append(wrapper);
        $('.main').append(changeUserNameForm);

        button.on('click', function() {
            let password = $('input[name=password]').val();
            let newUserName = $('input[name=newName]').val();

            if (password !== '' && newUserName !== '') {
                $.ajax({
                    url: 'api.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        command: 'update_user',
                        password: password,
                        newName: newUserName,
                    }
                }).done(function(data) {
                    if (data.result === 'success') {
                        alert('変更しました');
                        $('input[name=password]').val('');
                        $('input[name=newName]').val('');
                    } else {
                        alert('失敗しました');
                    }
                })
            } else {
                alert('入力項目を確認してください');
            }
        });
    }

    function manageUser() {

        let changePasswordForm = $('<div>').attr({name: 'changePassword'}).addClass('form').append($('<div>').addClass('wrapper').append($('<div>').text('パスワード')).append('<i>').addClass('fas fa-key fa-2x'));
        let changeUserNameForm = $('<div>').attr({name: 'changeUserName'}).addClass('form').append($('<div>').addClass('wrapper').append($('<div>').text('ユーザ名')).append('<i>').addClass('fas fa-user-cog fa-2x'));
        let showUserForm = $('<div>').attr({name: 'showUser'}).addClass('form').append($('<div>').addClass('wrapper').append($('<div>').text('ユーザ検索')).append('<i>').addClass('fas fa-users fa-2x'));
        $('.main').append(changePasswordForm).append(changeUserNameForm).append(showUserForm).css('display', 'flex');

        $('.form').on('click', function() {
            let command = $(this).attr('name');

            clearMain();
            switch (command) {
                case 'changePassword':
                    changePassword();
                    break;
                case 'changeUserName':
                    changeUserName();
                    break;
                case 'showUser':
                    showUser();
                    break;
            }
        });
        
    }

    
    function formatDate(dt) {
        let year = dt.getFullYear();
        let month = ('00'+(dt.getMonth()+1)).slice(-2);
        let date = ('00' + dt.getDate()).slice(-2);
        return year + '-' + month + '-' + date;
    }
    
    $(home(formatDate(new Date())));
    
    $('li').on('click', function() {
        let command = $(this).attr('name');
        console.log(command);
    
        switch (command) {
            case 'home':
                clearMain();
                home(formatDate(new Date()));
                break;
            case 'search_log':
                clearMain();
                searchLog();
                break;
            case 'manage_user':
                clearMain();
                manageUser();
                break;
            case 'statistics':
                break;
        }
    });


    // ログイン処理
    $('header div').on('click', function() {
        clearMain();
        let studentIdText = $('<input>').attr({
            type: 'text',
            name: 'studentId',
            placeholder: '学籍番号'
        });
        let passwordText = $('<input>').attr({
            type: 'password',
            name: 'password',
            placeholder: 'パスワード'
        });
        let button = $('<button>').text('ログイン');
        let wrapper = $('<div>').addClass('wrapper');
        let loginForm = $('<div>').addClass('loginForm');
        
        wrapper.append(studentIdText).append(passwordText).append(button);
        loginForm.append(wrapper);
        $('.main').append(loginForm);

        button.on('click', function() {
            let studentId = $('input[name=studentId]').val();
            let password = $('input[name=password]').val();

            if (studentId !== '' && password !== '') {
                $.ajax({
                    url: 'login_api.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        command: 'login',
                        studentId: studentId,
                        password: password
                    }
                }).done(function(data) {
                    console.log(data);
                    $('aside nav ul li[name=home]').trigger('click');
                    $('header div i[name=login]').remove();
                    $('header div').append($('<i>').addClass('fas fa-sign-out-alt fa-2x'));
                    $('header div span').text('ログアウト');
                }).fail(function() {
                    alert('ログインできませんでした');
                })
            } else {
                alert('学籍番号とパスワードを入力してください');
            }
        })
    })
});