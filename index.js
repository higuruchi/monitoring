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
                        let li = $('<li></li>');
                        let time = $('<div></div>').addClass('time').text(element.enter_time+'~'+element.exit_time);
                        let name = $('<div></div>').addClass('name').text(element.name);
    
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
                type : 'GET',
                dataType : 'json',
                data : {
                    command : 'use_now'
                }
            }).done(function(data) {
                let use_now = $('.use_now');
                use_now.html('');
                data.forEach(function(element) {
                    let div = $('<div></div>');
                    let time = $('<div></div>').addClass('time').text(element.enter_time);
                    let name = $('<div></div>').addClass('name').text(element.name);

                    div.append(time).append(name);
                    use_now.append(div);
                });
            }).fail(function() {
                alert('通信に失敗しました')
            })
        }, 10000);
    });
})