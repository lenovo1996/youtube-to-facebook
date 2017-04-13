<!DOCTYPE html>
<html>
<head>
        <meta charset="utf-8">
	<title>Tool Transfer Video Youtube To Facebook</title>
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.1.0.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css">
</head>
<body style="background: url('assets/background.png')">
	<div class="container" style="margin-top: 50px">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading text-center"><h4>Tool Transfer Video Youtube To Facebook</h4></div>
				<div class="panel-body">

					<div class="form-group">
						<input class="form-control" id="access_token" placeholder="Nhập Access Token">
					</div>

					<div id="list_user" class="form-group">

					</div>

					<div class="form-group">
						<textarea style="width: 888px; height: 170px;" class="form-control" name="list_id" placeholder="Nhập ID Video Youtube | một id một dòng"></textarea>
					</div>

					<div class="form-group text-center">
						<button class="btn btn-primary btn-sm" id="submit" >Bắt Đầu</button> 
					</div>
					<a class="list-group-item" id="loading" style="display:none">Loading...</a>
					<div class="form-group" id="result">
						
					</div>

				</div>

			</div>
		</div>
	</div>
<script type="text/javascript">

var list_id;
var i = 0;
var token;

    $("#access_token").on('paste', function(event) {
        var _this = this;
        setTimeout( function() {
            var text = $(_this).val();
            token = text;
            CheckToken(text);
        }, 100);
    });


$(document).on('click', '#submit', function(){
	$('#loading').show();
	token = $('#list_user option:selected').val();
	list_id = document.getElementsByName('list_id')[0].value;
	list_id = list_id.split('\n');
	Upload_Request();

});

function CheckToken(_token){

    $.ajax({

        url: 'CheckToken.php',
        type: 'post',
        data: {access_token: _token},
        success: function(data){


            if(data){

                $('#list_user').html(data);
                $('#list_id').removeAttr('disabled');
                $('#submit').removeAttr('disabled');
                $('#access_token').attr('disabled', 'disabled');
            }else{


                alert('Access Token Không Hợp Lệ Hoặc Đã Hết Hạn! Xin Vui Lòng Lấy Lại Token Để Sử Dụng Dịch Vụ Của Chúng Tôi!');
                $('#list_id').attr('disabled', 'disabled');
                $('#submit').attr('disabled', 'disabled');

            }


        }



    });



}


function Upload_Request(){


	if(i >= list_id.length){

		alert('Hoàn Thành!');
		var curent_html = $('#result').html();
        
        $('#loading').html('Xong!');

	}else{

		$.ajax({

	        url: 'Execute.php',
	        data: { token: token, id: list_id[i] },
	        success: function(data){

	        	i++;
	            if(data){

	            	var curent_html = $('#result').html();
	                $('#result').html('<a class="list-group-item">' +data + '</a>' + curent_html);
	                

	            }else{


	                var curent_html = $('#result').html();
	                $('#result').html('<a class="list-group-item">Upload Lỗi</a>' + curent_html);

	            }
	            Upload_Request();

	        }



	    });

	}

}


</script>
</body>
</html>