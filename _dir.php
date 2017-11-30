<?
error_reporting(E_ALL ^ E_NOTICE);

##################################################
//계정 quota 계산
//$_quota = 2147483648; // 할당받은용량(2G)
//$_quota = 390000000000; //
$_quota_mb = 390000 ;
##################################################
Class DirLib{

	//라이브러리
	public static function _calculate_du($_quota = 0){
		//계정 quota 계산
		//$_size_limit = 2147483648; // 할당받은용량(2G)
		if($_quota > 0){
			$_used = self::_foldersize("./");
			$_remaining = $_quota - $_used;
			$_info = self::_format_size($_used)." / ".self::_format_size($_quota).", 여유(".self::_format_size($_remaining).")";
		}else{
			$_info = "[할당받은 Quota 용량을 셋팅하세요]";
		}
		return $_info;
	}
	public static function _foldersize($path) {
		$total_size = 0;
		$files = @scandir($path);
		$cleanPath = rtrim($path, '/'). '/';
		foreach($files as $t) {
			if ($t<>"." && $t<>"..") {
				$currentFile = $cleanPath . $t;
				if (is_dir($currentFile)) {
					$size = self::_foldersize($currentFile);
					$total_size += $size;
				}else{
					$size = filesize($currentFile);
					$total_size += $size;
				}
			}
		}
		return $total_size;
	}
	public static function _format_size($size) {
		$units = explode(' ', 'B KB MB GB TB PB');
		$mod = 1024;
		$_tmp = "";
		for ($i = 0; $size >= $mod; $i++) {
			$_tmp .= "<div>[$i : $size]</div>";
			$size /= $mod;
		}
		$endIndex = strpos($size, ".")+3;
		//return $_tmp."($size / $endIndex , $i)".substr( $size, 0, $endIndex).''.$units[$i];
		return substr( $size, 0, $endIndex).''.$units[$i];


	}
	public static function _get_dirs($dir){
		if (is_dir($dir)) {
			//echo PHP_EOL.PHP_EOL."<hr style='clear:both;' /> DIR : $dir <hr />".PHP_EOL;
			if( $dir != './'){
				//$_up_dir = str_replace(strrchr($dir, "/"),'',$dir);
				$_up_dir = dirname($dir);
				if( $_up_dir =='.' ) $_up_dir = "./";
				$_uplink = "<a href='".$_SERVER['PHP_SELF']."?d=".urlencode($_up_dir)."'>[UpDir]</a><a href='#'>[Top]</a>";
			}
			echo PHP_EOL.PHP_EOL."<div style='clear:both;'></div><div class='class0'>[<a href='https://gist.github.com/landzz/db4037aadd44f89a0cef' target='_blank'>Gist</a>] Current DIR  : ".$dir." ".$_uplink."</div>".PHP_EOL;
			echo "<div id='DIR_AREA'></div>";
			$_seq=0;
			$_seq_dir=0;
			$_size = 0;
			$_files_array = scandir($dir);
			foreach($_files_array AS $file){
				// 디렉터리 이름앞에 _ 가 있는경우에도 스킵
				$_chks = true;
				if($file == '.' || $file == '..' || substr($file,0,1) == '.'  /*|| substr($file,0,1) == '_' */){
					$_chks = false;
				}
				if($_chks == true){
					//$_seq++;
					$_src = $dir ."/". $file;
					$_src = str_replace("//","/",$_src);
					if(is_dir($_src)){
						$_seq_dir++;
						//디렉터리일경우 디렉터리명만 출력하고 skip
						//_get_dirs($_src);
						$_dir_size = self::_foldersize($_src);
						echo PHP_EOL."<div class='class1 DIR'>";
						echo "<div class='class21'>";
						echo "[DIR]&nbsp;&nbsp;<a href='".$_SERVER['PHP_SELF']."?d=".urlencode($_src)."' >".$_src." <span class='s11 color1'>(".self::_format_size($_dir_size).")</span></a>";
						echo "</div>";
						echo "</div>";
						flush();

					}else{
						$_seq++;
						$_info = self::_get_file_info($_src);
						//self::_print_r_text($_info);
						$_size = $_size + $_info['size'];
						//$_text = "$_seq :  ".$_info['filename']."(".$_info['type'].",".$_info['size_txt'].", ".$_info['width']."x".$_info['height'].") ";

						echo PHP_EOL."<div class='class1'>";
						echo "<div class='class2'>";
						if($_info['type'] == 'image'){
							//echo "<img src='".$_src."' border='0' class='img'/>";
							$_memo_pop = "<div class='mm0'>".$_text."</div><div class='mm'><img src='".$_src."' border='0' /></div>";
							//echo "[<a href='".$_src."' target='_blank' class='images' title=\"".$_memo_pop."\" >view</a>]";
							//echo "<img src='".$_src."' border='0' class='img' title=\"".$_memo_pop."\" />";
							// lazyload
							echo "<img class='lazy img' data-original='".$_src."' border='0' title=\"".$_memo_pop."\" />";
							$_text = "$_seq :  ".$_src."(".$_info['type'].",".$_info['size_txt'].", ".$_info['width']."x".$_info['height'].") ";
						}else{
							echo "&nbsp;[".$_info['ext']."]";
							//$_src_text = iconv('euc-kr','utf-8',$_src)."[".$_encoding."]";
							$_src_text = mb_detect_encoding($_src) != 'ASCII' ? iconv('euc-kr','utf-8',$_src) :  $_src;
							$_text = "$_seq :  <a href='".$_src."' target='_blank'>".$_src_text."</a>(".$_info['type'].",".$_info['size_txt'].") ";
						}
						$_text .= $_info['md5'] ? "[md5 : ".$_info['md5']."]" : '' ;
						$_text .= ', '.$_info['time']['mtime_date']." / ".$_info['time']['atime_date']." / ".$_info['time']['ctime_date'];
						echo "</div>";

						echo  "<div class='class3'>".$_text."</div>";
						echo "</div>";
						flush();
						//echo "filename: $file : filetype: " . filetype($dir . $file) . "<br />";
					}
				}
			}
			//$_size2 = number_format( $_size/(1000*1024),1 )." MB";
			$_size2 = self::_format_size($_size);
			echo PHP_EOL."<div class='class4'> Dir: ".number_format($_seq_dir).", File : ".number_format($_seq)." , Size :  ".$_size2." (".number_format($_size).")</div>".PHP_EOL;
			flush();
		}
	}
	public static function _print_r_text($_arr){
		echo "<textarea style='min-width:350px;min-height:70px;resize:auto;display:block;'>";
		print_r($_arr);
		echo "</textarea>";
	}
	public static function _get_file_info($file){
		$_info=array();
		//if($file !=""){
			//$_file_src = $data_path."/".$file;
			$_file_src = $file;
			if( file_exists($_file_src) ){
				//$_md5 = @md5_file($_file_src);
				//확장자 없을경우 mime type 가져와서 이미지일경우 확장자 인식시킴
				$exts = explode(".",$file);
				$exts1 = strtolower($exts[count($exts)-1]);
				if(function_exists(finfo_file)){
					$_fname = explode('/', $file);
					$_fname = end($_fname);
					//$_fname = end(explode('/', $file));
					if(strpos($_fname,'.') === false){
						$_mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
						if(strpos($_mime,'image') !== false){
							$exts = explode('/', $_mime);
							$exts = end($exts);
							//$exts = end(explode('/', $_mime));
							$exts1  = strtolower($exts);
						}else{
							$exts = $_mime;
							$exts1 = '';
						}
					}
				}
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/assets/icon/".$exts1.".gif")){
					$exts = '<img src="/assets/icon/".$exts1.".gif" border="0" align="absmiddle">';
				}else{
					$exts = '<img src="/assets/icon/unknown.gif" border="0" align="absmiddle">';
				}
				/*
				$exts = explode(".",$file);
				$exts1 = strtolower($exts[count($exts)-1]);
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/assets/icon/".$exts1.".gif")){
					$exts = "<img src='/assets/icon/".$exts1.".gif' border=0 align='absmiddle' />";
				}else{
					$exts = "<img src='/assets/icon/unknown.gif' border=0 align='absmiddle'  />";
				}
				*/
				$_size = filesize($_file_src);
				if($_size > 1000000){
					$_size = number_format( $_size/(1000*1024),1 )." MB";
				}else{
					$_size = number_format($_size/1024)." KB";
				}
				if( preg_match("/\.(gif|jpg|jpeg|png|bmp|jpeg|tif)$/i", $file)){
					$_img = @getimagesize($file);
					$_img['type'] = "image";
					//파일타입이 이미지일경우에만 md5 체크
					$_md5 = @md5_file($_file_src);
				}else{
					$_img['type'] = "file";
				}
				$_file_time = array(
					'atime' => fileatime($_file_src)
					,'ctime' => filectime($_file_src)
					,'mtime' => filemtime($_file_src)
					,'atime_date' => date('Y-m-d H:i:s',fileatime($_file_src))
					,'ctime_date' => date('Y-m-d H:i:s',filectime($_file_src))
					,'mtime_date' => date('Y-m-d H:i:s',filemtime($_file_src))
				);
				$_info = array(
					'filename'	=> basename($file)
					,'ext'	=> $exts1
					,'ext_icon'	=> $exts
					,'size'=> filesize($_file_src)
					,'size_txt'=> $_size
					,'type' => $_img['type']
					,'width' =>$_img[0]
					,'height' =>$_img[1]
					,'wh'=>$_img[3]
					,'md5' => $_md5
					,'time' => $_file_time
				);
			}
		//}
		return $_info;
	}
}
//라이브러리
############################################################
?>
<!doctype html>
<html lang="kr">
<head>
<meta charset="UTF-8">
<title></title>
<link rel="stylesheet" href="//landzz.github.io/assets/style.css" type="text/css">
<script type="text/javascript" src="//landzz.github.io/assets/jquery-1.8.3.js"></script>
<script type="text/javascript" src="//landzz.github.io/assets/easyTooltip.js"></script>
<script type="text/javascript" src="//landzz.github.io/assets/jquery.lazyload.js"></script>

<style type="text/css">
#DIR_AREA{margin-top:50px;}
.class0{clear:both;position:fixed;top:0;width:98%; margin-top:15px;padding:10px;display:block;color:yellow;background-color:#222;}
.class0 a{color:yellow;font-weight:bold;}
.class1{clear:both;margin:5px 0 5px 0;padding:5px;display:block;}
.class2{min-width:210px !important;float:left;background-color:#fefefe; border:1px solid #dfdfdf;padding:7px;margin-right:10px;}
.class21{min-width:210px !important;float:left;background-color:#666; color:yellow; border:1px solid #dfdfdf;padding:7px;margin-right:10px;}
.class21 a{color:yellow;}
.class3{float:left;padding-top:5px;color:#666;}
.class4{clear:both; margin-top:35px;padding:10px;background-color:#eee;display:block; font-weight:bold;font-size:11px;color:#333;border:1px solid #ccc;width:98%;}
.img{max-height:200px;max-width:700px;min-width:10px; cursor:pointer; border:1px solid #000;}
#easyTooltip{ /*width:250px;*/padding:5px;border:1px solid #333399;background:#333399;font-size:11px; }
.mm0{ margin:6px;color:#ffffff; font-weight:;font-size:11px; }
.mm{ background:#FFFFE1; padding:7px;margin:0px; }
</style>
</head>
<body>
<?
$dir = $_GET['d'] !='' ? $_GET['d'] : "./" ;
if(substr($dir,0,1) == '/') die('illegal Access!!!');
if(strpos($dir,'..') !== false) die('illegal Access!!!');
DirLib::_get_dirs($dir);
?>
<div style='clear:both;'>&nbsp;<div>
<div class='s11 bold'>[계정사용량] <?=DirLib::_calculate_du($_quota);?><br />&nbsp;</div>
<script type="text/javascript">
$(document).ready(function(){
	$(".img").easyTooltip({xOffset: 0,yOffset: -30});
	$(".img").click(function(){var srcs = $(this).attr('src');window.open(srcs);});
	$("img.lazy").lazyload({
		 /*threshold : 20,*/
		/*event : "click",*/
		effect : "fadeIn",
		placeholder : true
	});
});
$(window).load(function(){
	var tmps = $('.DIR');
	$('.DIR').remove();
	$('#DIR_AREA').html(tmps);
});
</script>
</body>
</html>