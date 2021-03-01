jQuery(function($) {
    $('button').on('click', function() {
        let studentId = $('input[name=studentId]').val();
        let password = $('input[name=password]').val();

        if (studentId !== '' && password !== '') {
            $.ajax({
                url : 'login_api.php',
                type : 'POST',
                dataType : 'json',
                data : {
                    command : 'login',
                    studentId: studentId,
                    password: password
                }
            }).done(function(data) {
                if (data.result === 'success') {
                    console.log('ok');
                    window.location.href = 'home.php';
                } else {
                    alert('パスワードもしくは学籍番号が間違っています');
                }
            })
        }
    })
});