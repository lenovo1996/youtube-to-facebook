<meta charset="utf-8">
<?php 
$token = auto('http://tenmienit.net/dir/token.txt');
// Lấy inbox
$data = json_decode(auto('https://graph.fb.me/me/threads?fields=id,snippet,unread_count,senders&limit=5&access_token='.$token),true);
for($i=0;$i<=count($data['data']);$i++){

if($data['data'][$i]['unread_count'] > 0){

// nội dung tin nhắn
$message = $data['data'][$i]['snippet'];
$id = $data['data'][$i]['id'];

// kiểm tra và lưu lại token
if(preg_match('#/set#is', $message)){ // tìm lệnh
  $set = explode(' ', $message);
   $_CheckToken = json_decode(auto('https://graph.fb.me/me?access_token=' . $set[1]), true); // gửi thông tin lên facebook và lấy trả lời từ facebook
   if (!empty($_CheckToken['id'])) { // kiểm tra dữ liệu (mail ảo, token chết)

      // lưu lại token nếu token còn sống
      $password = rand_string(4);
      $file = fopen('save_token/' . $password, 'w');
      fwrite($file, $set[1]);
      fclose($file);
      // trả lời nếu token sống
      $msg = 'Đã cài đặt thành công.
--------
Mật khẩu của bạn là: '.$password.'
Vui lòng ghi nhớ mật khẩu để có thể sử dụng tool
--------
Nếu mất mật khẩu, bạn có thể dùng lệnh cài token để nhận lại mật khẩu mới

sử dụng lệnh /show để lấy lệnh sử dụng.';
auto('https://graph.facebook.com/'.$id.'/messages?message='.urlencode($msg).'&access_token='.$token.'&method=post');
auto('https://graph.facebook.com/tags?id='.$id.'&name=read&state=1&access_token='.$token.'&method=post');

   } else {
      // trả lời nếu token chết
      $msg = 'Token không hoạt động. vui lòng lấy token khác';
auto('https://graph.facebook.com/'.$id.'/messages?message='.urlencode($msg).'&access_token='.$token.'&method=post');
auto('https://graph.facebook.com/tags?id='.$id.'&name=read&state=1&access_token='.$token.'&method=post');

   }
} else if(preg_match('#/show#is', $message)){ // tìm lệnh
   // trả lời lệnh /show
$msg = 'Dưới đây là một số lệnh cơ bản của tool:
----------
/set (token)
=> cài token
----------
/show
=> hiển thị các lệnh sử dụng
----------
/upload (mật khẩu) (id youtube)
=> upload video từ Youtube lên Facebook, mật khẩu được cấp từ lúc cài token';
auto('https://graph.facebook.com/'.$id.'/messages?message='.urlencode($msg).'&access_token='.$token.'&method=post');
auto('https://graph.facebook.com/tags?id='.$id.'&name=read&state=1&access_token='.$token.'&method=post');


}else if(preg_match('#/upload#isu', $message)){ // tìm lệnh
$datas = explode(' ', $message);
   if(file_exists('save_token/' . $datas[1])){ // kiểm tra xem có tồn tại file token hay không

      $_Token = file_get_contents('save_token/' . $datas[1]); // lấy token
      $_CheckToken = json_decode(auto('https://graph.fb.me/me?access_token=' . $_Token), true); // gửi thông tin 
      if (!empty($_CheckToken['id'])) { // check token
         if(CheckVideo($datas[2])){ // check id video youtube
auto('https://graph.facebook.com/'.$id.'/messages?message='.urlencode('Đang upload video ...').'&access_token='.$token.'&method=post');
auto('https://graph.facebook.com/tags?id='.$id.'&name=read&state=1&access_token='.$token.'&method=post');
            $datass = auto('http://congdongmang.org/dir/Execute.php?token=' . $_Token . '&id=' . $datas[2]); // gửi request upload video
if($datass){ // nếu upload thành công

               $msg = 'Đã đăng video thành công!
Vui lòng chờ Facebook duyệt video (trong vòng 2 đến 5 phút)';
auto('https://graph.facebook.com/'.$id.'/messages?message='.urlencode($msg).'&access_token='.$token.'&method=post');
auto('https://graph.facebook.com/tags?id='.$id.'&name=read&state=1&access_token='.$token.'&method=post');

            }else{ // nếu upload thất bại

               $msg = 'Upload lỗi. vui lòng liên hệ với quản trị viên để được fix lỗi';
auto('https://graph.facebook.com/'.$id.'/messages?message='.urlencode($msg).'&access_token='.$token.'&method=post');
auto('https://graph.facebook.com/tags?id='.$id.'&name=read&state=1&access_token='.$token.'&method=post');

            }

         } else {
           
            // message video chết
            $msg = 'Video không tồn tại hoặc video đã chết. vui lòng lấy ID video khác';
            auto('https://graph.facebook.com/'.$id.'/messages?message='.urlencode($msg).'&access_token='.$token.'&method=post');
            auto('https://graph.facebook.com/tags?id='.$id.'&name=read&state=1&access_token='.$token.'&method=post');

         }


      }else{
          // trả lời nếu token chết
                     $msg = 'Token không hoạt động. vui lòng lấy token khác';
         auto('https://graph.facebook.com/'.$id.'/messages?message='.urlencode($msg).'&access_token='.$token.'&method=post');
         auto('https://graph.facebook.com/tags?id='.$id.'&name=read&state=1&access_token='.$token.'&method=post');
         

      }


   } else {
      //message mật khẩu sai
      $msg = 'Mật khẩu không đúng. vui lòng thử lại';
      auto('https://graph.facebook.com/'.$id.'/messages?message='.urlencode($msg).'&access_token='.$token.'&method=post');
                auto('https://graph.facebook.com/tags?id='.$id.'&name=read&state=1&access_token='.$token.'&method=post');


   }

}

}else{

echo 'chưa có sms<br />';


}

}

function auto($url){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $uaa = $_SERVER['HTTP_USER_AGENT'];

        curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: $uaa");

        return curl_exec($ch);

   }


function rand_string( $length ) {
   $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
   $size = strlen( $chars );
   for( $i = 0; $i < $length; $i++ ) {
   $str .= $chars[ rand( 0, $size - 1 ) ];
    }
   return $str;
}


function CheckVideo($id){

        $info = get_headers('https://i.ytimg.com/vi/'.$id.'/default.jpg');
        if(preg_match('#404#is', $info[0])){
            return false;            
        }else{
         return true;
        }
    }


?>

<meta http-equiv=refresh content="1; URL=file.php">