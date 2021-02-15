jQuery(function($) {

    // 検索ボタンを押したときの処理
    $('button').on('click.button', function() {
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

                let ul = $('ul');
                
                search_condition.val('');

                ul.html('');

                if (data.log) {
                    data.log.forEach(function(element) {
                        const li = $('<li></li>');
                        const time = $('<div></div>').addClass('time').text(element.enter_time+'~'+element.exit_time);
                        const name = $('<div></div>').addClass('name').text(element.name);
    
                        li.append(time).append(name);
                        ul.append(li);
                    });

                } else {
                    let li = $('<li></li>').text('検索に一致するログが見つかりませんでした');
                    ul.apend(li);
                }

            }).fail(function(data) {
                alert('通信に失敗しました');
            })

        } else {
            alert('入力値が正しくありません');
        }
    });

    // 一定時間ごとに現在入室している人のデータを取ってくる
    $(function (){
        setInterval(function (){
            $.ajax({
                url : 'api.php',
                // GETメソッドを利用します。
                type : 'GET',
                // 応答データの種類はJSON
                dataType : 'json',
                // サーバ側では、command変数内の文字列により
                // 処理の分岐を行っているため、現在入室している
                // ユーザのデータを取得するために"use_now"を入れます。
                data : {
                    command : 'use_now'
                }
            }).done(function(data) {
                // サーバからの返却値がdataに入っているため、
                // それらのデータをDOMに追加することにより、
                // リアルタイム更新を実現しています
                const users = data.map(function(user) {
                    const time = $('<div></div>').addClass('time').text(user.enter_time);
                    const name = $('<div></div>').addClass('name').text(user.name);

                    return $('<div></div>').append(time, name);
                });
                $('.use_now').html(users);
            }).fail(function() {
                alert('通信に失敗しました')
            })
        }, 10000);
    });
})