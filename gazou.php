<?php
/*************************************
  * GazouBBS             by ToR
  *   
  * http://php.s3.to/
  *
  *Half-assed translation   by spoot
  * 
  * http://saguaroimgboard.co.cc/
  *   
  * Please note that I do not know a lick of japanese
  * so the parts i couldn't figure out I just left alone, the script
  * still seems to be stable enough to use, but a couple error messages i left alone,
  * so if you encounter any japanese error messages email me the message & how it happened at: spoot@saguaroimgboard.co.cc   
  **************************************/
if(phpversion()>="4.1.0"){
  extract($_REQUEST);
  extract($_COOKIE);
  $upfile_name=$_FILES["upfile"]["name"];
  $upfile=$_FILES["upfile"]["tmp_name"];
}
//----Settings--------
define(LOGFILE, 'imglog.log');		//location of the logfile that holds post info
define(IMG_DIR, 'img/');		//location of image directory

define(TITLE, 'GazouBBS');		//name of this bulletin board
define(HOME,  'gazou.php');	//location of your homepage

define(MAX_KB, '2048');			//Maximum file size (in kilobytes)
define(MAX_W,  '250');			//Maximum picture width before thumbnailing
define(MAX_H,  '250');			//Maximum picture height before thumbnailing

define(PAGE_DEF, '7');			//Maximum number of posts per page
define(LOG_MAX,  '200');		//Maximum number of posts

define(ADMIN_PASS, '0123');		//password for the admin panel/posts
define(CHECK, 0);			//proxy check yes=1
define(SOON_ICON, 'soon.jpg');		//I dunno what this is, thought it was a logo image but...
define(RE_COL, '789922');               //color of responses (phrases following a greater than symbol)

define(NIKKI, 0);			//Close the board Yes=1 No=0

define(PHP_SELF, "gazou.php");		//Name of this file
//----End of settings-------

//path to image directory (usually fine "as is") $path="/home/public_html/***/img/";
$path = dirname($_SERVER[PATH_TRANSLATED]).IMG_DIR;

/* ����
$badstring = array("dummy_string","dummy_string2"); //���₷�镶����
$badfile = array("dummy","dummy2"); //���₷��t�@�C����md5
$badip = array("addr.dummy.com","addr2.dummy.com"); //���₷��z�X�g
*/
/* �w�b�_ */
function head(&$dat){
  $dat.='
<html><head>
<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=Shift_JIS">
<STYLE TYPE="text/css">
<!--
body,tr,td,th { font-size:10pt }
a:hover { color:#DD0000; }
span { font-size:20pt }
small { font-size:8pt }
-->
</STYLE>
<title>'.TITLE.'</title></head>
<body bgcolor="#FFFFEE" text="#800000" link="#0000EE" vlink="#0000EE">
<p align=right>
[<a href="'.HOME.'" target="_top">Home</a>]
[<a href="'.PHP_SELF.'?mode=admin">Manage</a>]
<p align=center>
<font color="#800000" face="�l�r �o�S�V�b�N" size=5>
<b><SPAN>'.TITLE.'</SPAN></b></font>
<hr width="90%" size=1>
';
}
/* ���e�t�H�[�� */
function form(&$dat,$resno,$admin=""){
  global $gazoubbs;

  if (get_magic_quotes_gpc()) $gazoubbs = stripslashes($gazoubbs);
  list($cname,$cemail,$cpass) = explode(",", $gazoubbs);

  $maxbyte = MAX_KB * 1024;
  if($resno){
    $find = false;
    $line = file(LOGFILE);
    for($i = 0; $i < count($line); $i++){
      list($no,$now,$name,$email,$sub,$com,) = explode(",", $line[$i]);
      if($no == $resno){
        $find = true;
        break;
      }
    }
    if(!$find) error("OH NOEZ THE LOG FILE-B-GONE!");

    if(ereg("Re\[([0-9])\]:", $sub, $reg)){
      $reg[1]++;
      $r_sub=ereg_replace("Re\[([0-9])\]:", "Re[$reg[1]]:", $sub);
    }elseif(ereg("^Re:", $sub)){ 
      $r_sub=ereg_replace("^Re:", "Re[2]:", $sub);
    }else{
      $r_sub = "Re:$sub";
    }
    $r_com = "&gt;$com";
    $r_com = ereg_replace("<br( /)?>","\r&gt;",$r_com);
    $msg = "<h5>Replying to: No. $no</h5>";
  }
  if($admin){
    $hidden = "<input type=hidden name=admin value=\"".ADMIN_PASS."\">";
    $msg = "<h4>Html tags are allowed</h4>";
  }
  $dat.='
<center>'.$msg.'
<form action="'.PHP_SELF.'" method="POST" enctype="multipart/form-data">
<input type=hidden name=mode value="regist">
'.$hidden.'
<input type=hidden name="MAX_FILE_SIZE" value="'.$maxbyte.'">
<table cellpadding=1 cellspacing=1>
<tr>
  <td bgcolor=#eeaa88><b>Name</b></td>
  <td><input type=text name=name size="28" value="'.$cname.'"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>Email</b></td>
  <td><input type=text name=email size="28" value="'.$cemail.'"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>Title</b></td>
  <td>
    <input type=text name=sub size="35" value="'.$r_sub.'">
    <input type=submit value="Submit"><input type=reset value="Reset">
  </td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>Comment</b></td>
  <td><textarea name=com cols="48" rows="4" wrap=soft>'.$r_com.'</textarea>
  </td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>Url</b></td>
  <td><input type=text name=url size="63" value="http://"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>File</b></td>
  <td><input type=file name=upfile size="35"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>Password</b></td>
  <td>
    <input type=password name=pwd size=8 maxlength=8 value="'.$cpass.'">
    <small>(Password used for post deletion.)</small>
  </td>
</tr>
<tr><td colspan=2>
<small>
<LI>Supported file types are: GIF, JPG, PNG.<br>
<LI>Sometimes files might not upload correctly depending on the browser used.
<LI>Maximum file size allowed is '.MAX_KB.' KB.<br>
<LI>Images greater than '.MAX_W.'x'.MAX_H.' pixels will be thumbnailed.
</small>
</td></tr></table></form></center>
<hr>
  ';
}
/* �L������ */
function main(&$dat, $page){
  global $path;

  $line = file(LOGFILE);
  $st = ($page) ? $page : 0;

  for($i = $st; $i < $st+PAGE_DEF; $i++){
    if($line[$i]=="") continue;
    list($no,$now,$name,$email,$sub,$com,$url,
         $host,$pwd,$ext,$w,$h,$time,$chk) = explode(",", $line[$i]);
    // URL�ƃ��[���Ƀ����N
    if($url)   $url = "<a href=\"http://$url\" target=_blank>Link</a>";
    if($email) $name = "<a href=\"mailto:$email\">$name</a>";
    $com = auto_link($com);
    $com = eregi_replace("(^|>)(&gt;[^<]*)", "\\1<font color=".RE_COL.">\\2</font>", $com);
    // �摜�t�@�C����
    $img = $path.$time.$ext;
    $src = ''.IMG_DIR.$time.$ext;
/* ���R�ɕύX���Ă�������["]=[\"]�� */
    // <img�^�O�쐬
    $imgsrc = "";
    if($ext && is_file($img)){
      $size = ceil(filesize($img) / 1024);//alt�ɃT�C�Y�\��
      if(CHECK && $chk != 1){//���`�F�b�N
        $imgsrc = "<img src=".SOON_ICON." hspace=20>";
      }elseif($w && $h){//�T�C�Y�����鎞
        $imgsrc = "<a href=\"".$src."\" target=_blank><img src=".$src."
			border=0 align=left width=$w height=$h hspace=20 alt=\"".$size." KB\"></a>";
      }else{//����ȊO
        $imgsrc = "<a href=\"".$src."\" target=_blank><img src=".$src."
			border=0 align=left hspace=20 alt=\"".$size." KB\"></a>";
      }
    }
    // ���C���쐬
    $dat.="No.$no <font color=#cc1105 size=+1><b>$sub</b></font>";
    $dat.="Name <font color=#117743><b>$name</b></font> Date: $now &nbsp; $url [<a href=".PHP_SELF."?res=$no>Reply</a>]";
    $dat.="<p><blockquote>$imgsrc $com</blockquote><br clear=left><hr>\n";

    $p++;
    clearstatcache();//�t�@�C����stat���N���A
  }
  $prev = $st - PAGE_DEF;
  $next = $st + PAGE_DEF;
  // ���y�[�W����
  $dat.="<table align=left><tr>\n";
  if($prev >= 0){
    $dat.="<td><form action=\"".PHP_SELF."\" method=POST>";
    $dat.="<input type=hidden name=page value=$prev>";
    $dat.="<input type=submit value=\"Back\" name=submit>\n";
    $dat.="</form></td>\n";
  }
  if($p >= PAGE_DEF && count($line) > $next){
    $dat.="<td><form action=\"".PHP_SELF."\" method=POST>";
    $dat.="<input type=hidden name=page value=$next>";
    $dat.=" <input type=submit value=\"Next\" name=submit>\n";
    $dat.="</form></td>\n";
  }
  $dat.="</td>\n</tr></table>\n";
}
/* �t�b�^ */
function foot(&$dat){
  $dat.='
<table align=right><tr>
<td nowrap align=center><form action="'.PHP_SELF.'" method=POST>
<input type=hidden name=mode value=usrdel>
[Delete]<br>
Post No<input type=text name=no size=3>
Password<input type=password name=pwd size=4 maxlength=8>
<input type=submit value="Submit">
</form></td>
</tr></table><br clear=all>
<center><P><small>- <a href="http://saguaroimgboard.co.cc/" target="_blank">Saguaro</a> + <a href="http://php.s3.to" target=_blank>GazouBBS</a> -
</small></center>
</body></html>
  ';
}
/* �L���������� */
function regist($name,$email,$sub,$com,$url,$pwd,$upfile,$upfile_name){
  global $REQUEST_METHOD,$path;

  // �t�H�[�����e���`�F�b�N
  if(!$name||ereg("^( |�@)*$",$name)) $name="Anonymous"; 
  if(!$com||ereg("^( |�@|\t)*$",$com)) error("Come on now, ya gotta type something."); 
  if(!$sub||ereg("^( |�@)*$",$sub))   $sub=""; 
  if(strlen($com) > 1000) error("TOO MUCH TEXT.");

  $line = file(LOGFILE);
  // ���Ԃƃz�X�g�擾
  $tim = time();
  $host = gethostbyaddr(getenv("REMOTE_ADDR"));
  // �A�����e�`�F�b�N
  list($lastno,,$lname,,,$lcom,,$lhost,,,,,$ltime,) = explode(",", $line[0]);
  if(RENZOKU && $host == $lhost && $tim - $ltime < RENZOKU)
    error("Bad proxy, dude.");
  // No.�ƃp�X�Ǝ��Ԃ�URL�t�H�[�}�b�g
  $no = $lastno + 1;
  $c_pass = $pwd;
  $pass = ($pwd) ? substr(md5($pwd),2,8) : "*";
  $now = gmdate("Y/m/d(D) H:i",$tim+9*60*60);
  $url = ereg_replace("^http://", "", $url);
  //�e�L�X�g���`
  $name = CleanStr($name);
  $email= CleanStr($email);
  $sub  = CleanStr($sub);
  $url  = CleanStr($url);
  $com  = CleanStr($com);
  // ���s�����̓���B 
  $com = str_replace( "\r\n",  "\n", $com); 
  $com = str_replace( "\r",  "\n", $com);
  // �A�������s����s
  $com = ereg_replace("\n((�@| )*\n){3,}","\n",$com);
  $com = nl2br($com);										//���s�����̑O��<br>��������
  $com = str_replace("\n",  "", $com);	//\n�𕶎��񂩂�����B
  // ��d���e�`�F�b�N
  if($name == $lname && $com == $lcom)
    error("��d���e�͋֎~�ł�<br><br><a href=$PHP_SELF>Return</a>");
  // ���O�s���I�[�o�[
  if(count($line) >= LOG_MAX){
    for($d = count($line)-1; $d >= LOG_MAX-1; $d--){
      list($dno,,,,,,,,,$ext,,,$dtime,) = explode(",", $line[$d]);
      if(is_file($path.$dtime.$ext)) unlink($path.$dtime.$ext);
      $line[$d] = "";
    }
  }
  // �A�b�v���[�h����
  if(file_exists($upfile)){
    $dest = $path.$upfile_name;
    move_uploaded_file($upfile, $dest);
    //���ŃG���[�Ȃ火�ɕύX
    //copy($upfile, $dest);
    if(!is_file($dest)) error("Image already exists.");
    $size = @getimagesize($dest);
    if($size[2]=="") error("File size is too big!");
    $W = $size[0];
    $H = $size[1];
    $ext = substr($upfile_name,-4);
    if ($ext == ".php" || $ext == "php3" || $ext == "php4" || $ext == "html") error("You dirty bastard!");
    rename($dest,$path.$tim.$ext);
    // �摜�\���k��
    if($W > MAX_W || $H > MAX_H){
      $W2 = MAX_W / $W;
      $H2 = MAX_H / $H;

      ($W2 < $H2) ? $key = $W2 : $key = $H2;

      $W = $W * $key;
      $H = $H * $key;
    }
    $mes = "$upfile_name, uploaded!<br><br>";
  }
  $chk = (CHECK) ? 0 : 1;//���`�F�b�N��0

    //�N�b�L�[�ۑ�
  $cookvalue = implode(",", array($name,$email,$c_pass));
  setcookie ("gazoubbs", $cookvalue,time()+14*24*3600);  /* 2�T�ԂŊ����؂� */

  $newline = "$no,$now,$name,$email,$sub,$com,$url,$host,$pass,$ext,$W,$H,$tim,$chk,\n";

  $fp = fopen(LOGFILE, "w");
  flock($fp, 2);
  fputs($fp, $newline);
  fputs($fp, implode('', $line));
  fclose($fp);

  echo "$mes";
  echo "<META HTTP-EQUIV=\"refresh\" content=\"1;URL=".PHP_SELF."?\">";
}
/* �e�L�X�g���` */
function CleanStr($str){
  global $admin;

  $str = trim($str);//�擪�Ɩ����̋󔒏���
  if (get_magic_quotes_gpc()) {//�����폜
    $str = stripslashes($str);
  }
  if($admin!=ADMIN_PASS){//�Ǘ��҂̓^�O�\
    $str = htmlspecialchars($str);//�^�O���֎~
    $str = str_replace("&amp;", "&", $str);//���ꕶ��
  }
  return str_replace(",", "&#44;", $str);//�J���}��ϊ�
}
/* ���[�U�[�폜 */
function usrdel($no,$pwd){
  global $path;

  if($no == "") error("�폜No�����͘R��ł�");

  $line = file(LOGFILE);
  $flag = FALSE;

  for($i = 0; $i<count($line); $i++){
    list($dno,,,,,,,,$pass,$dext,,,$dtim,) = explode(",", $line[$i]);
    if($no == $dno) {
      if(substr(md5($pwd),2,8) == $pass || ($pwd == '' && $pass == '*')){
        $flag = TRUE;
        $line[$i] = "";			//�p�X���[�h���}�b�`�����s�͋��
        $delfile = $path.$dtim.$dext;	//�폜�t�@�C��
        break;
      }
    }
  }
  if(!$flag) error("�Y���L����������Ȃ����p�X���[�h���Ԉ���Ă��܂�");
  // ���O�X�V
  $fp = fopen(LOGFILE, "w");
  flock($fp, 2);
  fputs($fp, implode('', $line));
  fclose($fp);

  if(is_file($delfile)) unlink($delfile);//�폜
}
/* �p�X�F�� */
function valid($pass){
  if($pass && $pass != ADMIN_PASS) error("Wrong Password, asshole.");

  head($dat);
  echo $dat;
  echo "[<a href=\"".PHP_SELF."\">Return</a>]\n";
  echo "<table width='100%'><tr><th bgcolor=#E08000>\n";
  echo "<font color=#FFFFFF>Admin Mode</font>\n";
  echo "</th></tr></table>\n";
  echo "<p><form action=\"".PHP_SELF."\" method=POST>\n";
  // ���O�C���t�H�[��
  if(!$pass){
    echo "<center><input type=radio name=admin value=del checked>Management Panel";
    echo "<input type=radio name=admin value=post>Admin Post<p>";
    echo "<input type=hidden name=mode value=admin>\n";
    echo "<input type=password name=pass size=8>";
    echo "<input type=submit value=\"Submit\"></form></center>\n";
    die("</body></html>");
  }
}
/* �Ǘ��ҍ폜 */
function admindel($delno,$chkno,$pass){
  global $path;

  if($chkno || $delno){
    $line = file(LOGFILE);
    $find = FALSE;
    for($i = 0; $i < count($line); $i++){
      list($no,$now,$name,$email,$sub,$com,$url,
           $host,$pw,$ext,$w,$h,$tim,$chk) = explode(",",$line[$i]);
      if($chkno == $no){//�摜�`�F�b�N$chk=1��
        $find = TRUE;
        $line[$i] = "$no,$now,$name,$email,$sub,$com,$url,$host,$pw,$ext,$w,$h,$tim,1,\n";
        break;
      }
      if($delno == $no){//�폜�̎��͋��
        $find = TRUE;
        $line[$i] = "";
        $delfile = $path.$tim.$ext;	//�폜�t�@�C��
        break;
      }
    }
    if($find){//���O�X�V
      $fp = fopen(LOGFILE, "w");
      flock($fp, 2);
      fputs($fp, implode('', $line));
      fclose($fp);

      if(is_file($delfile)) unlink($delfile);//�폜
    }
  }
  // �폜��ʂ�\��
  echo "<input type=hidden name=mode value=admin>\n";
  echo "<input type=hidden name=admin value=del>\n";
  echo "<input type=hidden name=pass value=\"$pass\">\n";
  echo "<center><P>Management Panel";
  echo "<P><table border=1 cellspacing=0>\n";
  echo "<tr bgcolor=6080f6><th>Delete?</th><th>Post No</th><th>Date</th><th>Title</th>";
  echo "<th>Name</th><th>Comment</th><th>Host/I.P Adress</th><th>Image size<br>(Bytes)</th>";
  if(CHECK) echo "<th>�摜<br>����</th>";
  echo "</tr>\n";

  $line = file(LOGFILE);

  for($j = 0; $j < count($line); $j++){
    $img_flag = FALSE;
    list($no,$now,$name,$email,$sub,$com,$url,
         $host,$pw,$ext,$w,$h,$time,$chk) = explode(",",$line[$j]);
    // �t�H�[�}�b�g
    list($now,$dmy) = split("\(", $now);
    if($email) $name="<a href=\"mailto:$email\">$name</a>";
    $com = str_replace("<br />"," ",$com);
    $com = htmlspecialchars($com);
    if(strlen($com) > 40) $com = substr($com,0,38) . " ...";
    // �摜������Ƃ��̓����N
    if($ext && is_file($path.$time.$ext)){
      $img_flag = TRUE;
      $clip = "<a href=\"".IMG_DIR.$time.$ext."\" target=_blank>".$time.$ext."</a>";
      $size = filesize($path.$time.$ext);
      $all += $size;			//���v�v�Z
    }else{
      $clip = "";
      $size = 0;
    }
    $bg = ($j % 2) ? "d6d6f6" : "f6f6f6";//�w�i�F

    echo "<tr bgcolor=$bg><th><input type=checkbox name=del value=\"$no\"></th>";
    echo "<th>$no</th><td><small>$now</small></td><td>$sub</td>";
    echo "<td><b>$name</b></td><td><small>$com</small></td>";
    echo "<td>$host</td><td align=center>$clip<br>($size)</td>\n";

    if(CHECK){//�摜�`�F�b�N
      if($img_flag && $chk == 1){
        echo "<th><font color=red>OK</font></th>";
      }elseif($img_flag && $chk != 1) {
        echo "<th><input type=checkbox name=chk value=$no></th>";
      }else{
        echo "<td><br></td>";
      }
    }
    echo "</tr>\n";
  }
  if(CHECK) $msg = "or������";

  echo "</table><p><input type=submit value=\"submit\">";
  echo "<input type=reset value=\"reset\"></form>";

  $all = (int)($all / 1024);
  echo "[ space used : <b>$all</b> KB ]";
  die("</center></body></html>");
}
/* �I�[�g�����N */
function auto_link($proto){
  $proto = ereg_replace("(https?|ftp|news)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",$proto);
  return $proto;
}
/* �G���[��� */
function error($mes){
  global $upfile_name,$path;

  if(is_file($path.$upfile_name)) unlink($path.$upfile_name);

  head($dat);
  echo $dat;
  echo "<br><br><hr size=1><br><br>
        <center><font color=red size=5><b>$mes</b></font></center>
        <br><br><hr size=1>";
  die("</body></html>");
}
/*-----------Main-------------*/
switch($mode){
  case 'regist':
    regist($name,$email,$sub,$com,$url,$pwd,$upfile,$upfile_name);
    break;
  case 'admin':
    valid($pass);
    if($admin=="del") admindel($del,$chk,$pass);
    if($admin=="post"){
      echo "</form>";
      form($post,$res,1);
      echo $post;
      die("</body></html>");
    }
    break;
  case 'usrdel':
    usrdel($no,$pwd);
  default:
    head($buf);
    if(!NIKKI) form($buf,$res);
    main($buf,$page);
    foot($buf);
    echo $buf;
}
?>