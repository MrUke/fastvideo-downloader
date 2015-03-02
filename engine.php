<?php
function engine($INPUT_URL){
	$id=preg_match("#www.fastvideo.me/(.{12})#",$INPUT_URL);
	$cc=new cURL();
	$html=file_get_contents($INPUT_URL);
	if(preg_match("#File Not Found#",$html)){return -1;}
	$cc->headers[]="Referer: $INPUT_URL";
	preg_match('|<input type="hidden" name="op" value="(.*?)"|',$html,$op);
	preg_match('|<input type="hidden" name="id" value="(.*?)"|',$html,$id);
	preg_match('|<input type="hidden" name="fname" value="(.*?)"|',$html,$fname);
	preg_match('|<input type="hidden" name="hash" value="(.*?)"|',$html,$hash);
	preg_match('|name="imhuman" value="(.*?)"|', $html, $imhuman);
	$html=$cc->post($INPUT_URL, "op=$op[1]&usr_login=&id=$id[1]&fname=$fname[1]&referer=&hash=$hash[1]&imhuman=$imhuman[1]");
	preg_match("@eval\(function\(p,a,c,k,e,d\){.*?}\('(.*?)', *(\d+), *(\d+), *'(.*?)'\.split\('\|'\)(.*?\)\)|\)\))@",$html,$str);
	$evaled=unpackjs($str[1],$str[2],$str[3],$str[4]);
	if(empty($evaled)){
		preg_match('/file:"(.*?)"/s',$html,$matches);
	}else{
		preg_match('/file:"(.*?)"/s',$evaled,$matches);
	}
	$link=$matches[1];
	preg_match("|mp4:(.*?).mp4|",$link,$matches);
	$OUTPUT_URL=str_replace("/mp4:","|mp4:",$link);
	return $OUTPUT_URL;
}

class cURL
{
	var $headers;
	var $user_agent;
	var $compression;
	function post($url,$data){
		$process=curl_init($url);
		curl_setopt($process,CURLOPT_HTTPHEADER,$this->headers);
		curl_setopt($process,CURLOPT_HEADER,1);
		curl_setopt($process,CURLOPT_USERAGENT,$this->user_agent);
		curl_setopt($process,CURLOPT_ENCODING,$this->compression);
		curl_setopt($process,CURLOPT_TIMEOUT,30);
		curl_setopt($process,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($process,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($process,CURLOPT_POSTFIELDS,$data);
		curl_setopt($process,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($process,CURLOPT_FOLLOWLOCATION,0);
		curl_setopt($process,CURLOPT_POST,1);
		$return = curl_exec($process);
		curl_close($process);
		return $return;
	}
}

function decode($x, $base){
	$digs = array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	if($x<0){
		$sign=-1;
	}elseif ($x==0){
		return "0";
	}else{
		$sign=1;
	}
	$x*=$sign;
	$digits=array();
	while($x){
		$digits[]=$digs[$x%$base];
	$x=intval($x/$base);
	}
	if($sign<0){
		$digits[]="-";
	}
	$digits=implode("",array_reverse($digits));
	return $digits;
}

function unpackjs($p,$a,$c,$k,$e=null,$d=null){
	$k=explode("|",$k);
	for($i=$c-1;$i>-1;$i--){
		if($k[$i]){
			$m=decode($i,$a);
			$p=preg_replace("/\b$m\b/",$k[$i],$p);
		}
	}
	return $p;
}
?>
