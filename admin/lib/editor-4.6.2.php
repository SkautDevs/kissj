<?php
/** Adminer Editor - Compact database editor
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2009 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 4.6.2
*/error_reporting(6135);$mc=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($mc||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$_g=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($_g)$$X=$_g;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection(){global$i;return$i;}function
adminer(){global$b;return$b;}function
version(){global$ca;return$ca;}function
idf_unescape($v){$ud=substr($v,-1);return
str_replace($ud.$ud,$ud,substr($v,1,-1));}function
escape_string($X){return
substr(q($X),1,-1);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes($Je,$mc=false){if(get_magic_quotes_gpc()){while(list($z,$X)=each($Je)){foreach($X
as$kd=>$W){unset($Je[$z][$kd]);if(is_array($W)){$Je[$z][stripslashes($kd)]=$W;$Je[]=&$Je[$z][stripslashes($kd)];}else$Je[$z][stripslashes($kd)]=($mc?$W:stripslashes($W));}}}}function
bracket_escape($v,$Ha=false){static$lg=array(':'=>':1',']'=>':2','['=>':3','"'=>':4');return
strtr($v,($Ha?array_flip($lg):$lg));}function
min_version($Lg,$Fd="",$j=null){global$i;if(!$j)$j=$i;$uf=$j->server_info;if($Fd&&preg_match('~([\d.]+)-MariaDB~',$uf,$B)){$uf=$B[1];$Lg=$Fd;}return(version_compare($uf,$Lg)>=0);}function
charset($i){return(min_version("5.5.3",0,$i)?"utf8mb4":"utf8");}function
script($Bf,$kg="\n"){return"<script".nonce().">$Bf</script>$kg";}function
script_src($Eg){return"<script src='".h($Eg)."'".nonce()."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace("\0","&#0;",htmlspecialchars($Q,ENT_QUOTES,'utf-8'));}function
nbsp($Q){return(trim($Q)!=""?h($Q):"&nbsp;");}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($C,$Y,$Wa,$qd="",$ge="",$d="",$rd=""){$J="<input type='checkbox' name='$C' value='".h($Y)."'".($Wa?" checked":"").($rd?" aria-labelledby='$rd'":"").">".($ge?script("qsl('input').onclick = function () { $ge };",""):"");return($qd!=""||$d?"<label".($d?" class='$d'":"").">$J".h($qd)."</label>":$J);}function
optionlist($D,$of=null,$Hg=false){$J="";foreach($D
as$kd=>$W){$le=array($kd=>$W);if(is_array($W)){$J.='<optgroup label="'.h($kd).'">';$le=$W;}foreach($le
as$z=>$X)$J.='<option'.($Hg||is_string($z)?' value="'.h($z).'"':'').(($Hg||is_string($z)?(string)$z:$X)===$of?' selected':'').'>'.h($X);if(is_array($W))$J.='</optgroup>';}return$J;}function
html_select($C,$D,$Y="",$fe=true,$rd=""){if($fe)return"<select name='".h($C)."'".($rd?" aria-labelledby='$rd'":"").">".optionlist($D,$Y)."</select>".(is_string($fe)?script("qsl('select').onchange = function () { $fe };",""):"");$J="";foreach($D
as$z=>$X)$J.="<label><input type='radio' name='".h($C)."' value='".h($z)."'".($z==$Y?" checked":"").">".h($X)."</label>";return$J;}function
select_input($Da,$D,$Y="",$fe="",$Ae=""){$Uf=($D?"select":"input");return"<$Uf$Da".($D?"><option value=''>$Ae".optionlist($D,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$Ae'>").($fe?script("qsl('$Uf').onchange = $fe;",""):"");}function
confirm($Nd="",$pf="qsl('input')"){return
script("$pf.onclick = function () { return confirm('".($Nd?js_escape($Nd):lang(0))."'); };","");}function
print_fieldset($u,$wd,$Og=false){echo"<fieldset><legend>","<a href='#fieldset-$u'>$wd</a>",script("qsl('a').onclick = partial(toggle, 'fieldset-$u');",""),"</legend>","<div id='fieldset-$u'".($Og?"":" class='hidden'").">\n";}function
bold($Pa,$d=""){return($Pa?" class='active $d'":($d?" class='$d'":""));}function
odd($J=' class="odd"'){static$t=0;if(!$J)$t=-1;return($t++%2?$J:'');}function
js_escape($Q){return
addcslashes($Q,"\r\n'\\/");}function
json_row($z,$X=null){static$nc=true;if($nc)echo"{";if($z!=""){echo($nc?"":",")."\n\t\"".addcslashes($z,"\r\n\t\"\\/").'": '.($X!==null?'"'.addcslashes($X,"\r\n\"\\/").'"':'null');$nc=false;}else{echo"\n}\n";$nc=true;}}function
ini_bool($bd){$X=ini_get($bd);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
sid(){static$J;if($J===null)$J=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
set_password($Kg,$O,$V,$G){$_SESSION["pwds"][$Kg][$O][$V]=($_COOKIE["adminer_key"]&&is_string($G)?array(encrypt_string($G,$_COOKIE["adminer_key"])):$G);}function
get_password(){$J=get_session("pwds");if(is_array($J))$J=($_COOKIE["adminer_key"]?decrypt_string($J[0],$_COOKIE["adminer_key"]):false);return$J;}function
q($Q){global$i;return$i->quote($Q);}function
get_vals($H,$f=0){global$i;$J=array();$I=$i->query($H);if(is_object($I)){while($K=$I->fetch_row())$J[]=$K[$f];}return$J;}function
get_key_vals($H,$j=null,$bg=0,$xf=true){global$i;if(!is_object($j))$j=$i;$J=array();$j->timeout=$bg;$I=$j->query($H);$j->timeout=0;if(is_object($I)){while($K=$I->fetch_row()){if($xf)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($H,$j=null,$p="<p class='error'>"){global$i;$jb=(is_object($j)?$j:$i);$J=array();$I=$jb->query($H);if(is_object($I)){while($K=$I->fetch_assoc())$J[]=$K;}elseif(!$I&&!is_object($j)&&$p&&defined("PAGE_HEADER"))echo$p.error()."\n";return$J;}function
unique_array($K,$x){foreach($x
as$w){if(preg_match("~PRIMARY|UNIQUE~",$w["type"])){$J=array();foreach($w["columns"]as$z){if(!isset($K[$z]))continue
2;$J[$z]=$K[$z];}return$J;}}}function
escape_key($z){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$z,$B))return$B[1].idf_escape(idf_unescape($B[2])).$B[3];return
idf_escape($z);}function
where($Z,$r=array()){global$i,$y;$J=array();foreach((array)$Z["where"]as$z=>$X){$z=bracket_escape($z,1);$f=escape_key($z);$J[]=$f.($y=="sql"&&preg_match('~^[0-9]*\\.[0-9]*$~',$X)?" LIKE ".q(addcslashes($X,"%_\\")):($y=="mssql"?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($r[$z],q($X))));if($y=="sql"&&preg_match('~char|text~',$r[$z]["type"])&&preg_match("~[^ -@]~",$X))$J[]="$f = ".q($X)." COLLATE ".charset($i)."_bin";}foreach((array)$Z["null"]as$z)$J[]=escape_key($z)." IS NULL";return
implode(" AND ",$J);}function
where_check($X,$r=array()){parse_str($X,$Ua);remove_slashes(array(&$Ua));return
where($Ua,$r);}function
where_link($t,$f,$Y,$ie="="){return"&where%5B$t%5D%5Bcol%5D=".urlencode($f)."&where%5B$t%5D%5Bop%5D=".urlencode(($Y!==null?$ie:"IS NULL"))."&where%5B$t%5D%5Bval%5D=".urlencode($Y);}function
convert_fields($g,$r,$M=array()){$J="";foreach($g
as$z=>$X){if($M&&!in_array(idf_escape($z),$M))continue;$_a=convert_field($r[$z]);if($_a)$J.=", $_a AS ".idf_escape($z);}return$J;}function
cookie($C,$Y,$zd=2592000){global$aa;return
header("Set-Cookie: $C=".urlencode($Y).($zd?"; expires=".gmdate("D, d M Y H:i:s",time()+$zd)." GMT":"")."; path=".preg_replace('~\\?.*~','',$_SERVER["REQUEST_URI"]).($aa?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
restart_session(){if(!ini_bool("session.use_cookies"))session_start();}function
stop_session(){if(!ini_bool("session.use_cookies"))session_write_close();}function&get_session($z){return$_SESSION[$z][DRIVER][SERVER][$_GET["username"]];}function
set_session($z,$X){$_SESSION[$z][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($Kg,$O,$V,$n=null){global$Fb;preg_match('~([^?]*)\\??(.*)~',remove_from_uri(implode("|",array_keys($Fb))."|username|".($n!==null?"db|":"").session_name()),$B);return"$B[1]?".(sid()?SID."&":"").($Kg!="server"||$O!=""?urlencode($Kg)."=".urlencode($O)."&":"")."username=".urlencode($V).($n!=""?"&db=".urlencode($n):"").($B[2]?"&$B[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Ad,$Nd=null){if($Nd!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Ad!==null?$Ad:$_SERVER["REQUEST_URI"]))][]=$Nd;}if($Ad!==null){if($Ad=="")$Ad=".";header("Location: $Ad");exit;}}function
query_redirect($H,$Ad,$Nd,$Ue=true,$Yb=true,$fc=false,$ag=""){global$i,$p,$b;if($Yb){$Hf=microtime(true);$fc=!$i->query($H);$ag=format_time($Hf);}$Ef="";if($H)$Ef=$b->messageQuery($H,$ag,$fc);if($fc){$p=error().$Ef.script("messagesPrint();");return
false;}if($Ue)redirect($Ad,$Nd.$Ef);return
true;}function
queries($H){global$i;static$Ne=array();static$Hf;if(!$Hf)$Hf=microtime(true);if($H===null)return
array(implode("\n",$Ne),format_time($Hf));$Ne[]=(preg_match('~;$~',$H)?"DELIMITER ;;\n$H;\nDELIMITER ":$H).";";return$i->query($H);}function
apply_queries($H,$T,$Vb='table'){foreach($T
as$R){if(!queries("$H ".$Vb($R)))return
false;}return
true;}function
queries_redirect($Ad,$Nd,$Ue){list($Ne,$ag)=queries(null);return
query_redirect($Ne,$Ad,$Nd,$Ue,false,!$Ue,$ag);}function
format_time($Hf){return
lang(1,max(0,microtime(true)-$Hf));}function
remove_from_uri($te=""){return
substr(preg_replace("~(?<=[?&])($te".(SID?"":"|".session_name()).")=[^&]*&~",'',"$_SERVER[REQUEST_URI]&"),0,-1);}function
pagination($E,$sb){return" ".($E==$sb?$E+1:'<a href="'.h(remove_from_uri("page").($E?"&page=$E".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($E+1)."</a>");}function
get_file($z,$wb=false){$kc=$_FILES[$z];if(!$kc)return
null;foreach($kc
as$z=>$X)$kc[$z]=(array)$X;$J='';foreach($kc["error"]as$z=>$p){if($p)return$p;$C=$kc["name"][$z];$hg=$kc["tmp_name"][$z];$lb=file_get_contents($wb&&preg_match('~\\.gz$~',$C)?"compress.zlib://$hg":$hg);if($wb){$Hf=substr($lb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Hf,$Ve))$lb=iconv("utf-16","utf-8",$lb);elseif($Hf=="\xEF\xBB\xBF")$lb=substr($lb,3);$J.=$lb."\n\n";}else$J.=$lb;}return$J;}function
upload_error($p){$Kd=($p==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($p?lang(2).($Kd?" ".lang(3,$Kd):""):lang(4));}function
repeat_pattern($ze,$xd){return
str_repeat("$ze{0,65535}",$xd/65535)."$ze{0,".($xd%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\\0-\\x8\\xB\\xC\\xE-\\x1F]~',$X));}function
shorten_utf8($Q,$xd=80,$Of=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$xd).")($)?)u",$Q,$B))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$xd).")($)?)",$Q,$B);return
h($B[1]).$Of.(isset($B[2])?"":"<i>...</i>");}function
format_number($X){return
strtr(number_format($X,0,".",lang(5)),preg_split('~~u',lang(6),-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~[^a-z0-9_]~i','-',$X);}function
hidden_fields($Je,$Sc=array()){$J=false;while(list($z,$X)=each($Je)){if(!in_array($z,$Sc)){if(is_array($X)){foreach($X
as$kd=>$W)$Je[$z."[$kd]"]=$W;}else{$J=true;echo'<input type="hidden" name="'.h($z).'" value="'.h($X).'">';}}}return$J;}function
hidden_fields_get(){echo(sid()?'<input type="hidden" name="'.session_name().'" value="'.h(session_id()).'">':''),(SERVER!==null?'<input type="hidden" name="'.DRIVER.'" value="'.h(SERVER).'">':""),'<input type="hidden" name="username" value="'.h($_GET["username"]).'">';}function
table_status1($R,$gc=false){$J=table_status($R,$gc);return($J?$J:array("Name"=>$R));}function
column_foreign_keys($R){global$b;$J=array();foreach($b->foreignKeys($R)as$vc){foreach($vc["source"]as$X)$J[$X][]=$vc;}return$J;}function
enum_input($U,$Da,$q,$Y,$Qb=null){global$b;preg_match_all("~'((?:[^']|'')*)'~",$q["length"],$Hd);$J=($Qb!==null?"<label><input type='$U'$Da value='$Qb'".((is_array($Y)?in_array($Qb,$Y):$Y===0)?" checked":"")."><i>".lang(7)."</i></label>":"");foreach($Hd[1]as$t=>$X){$X=stripcslashes(str_replace("''","'",$X));$Wa=(is_int($Y)?$Y==$t+1:(is_array($Y)?in_array($t+1,$Y):$Y===$X));$J.=" <label><input type='$U'$Da value='".($t+1)."'".($Wa?' checked':'').'>'.h($b->editVal($X,$q)).'</label>';}return$J;}function
input($q,$Y,$Ac){global$vg,$b,$y;$C=h(bracket_escape($q["field"]));echo"<td class='function'>";if(is_array($Y)&&!$Ac){$ya=array($Y);if(version_compare(PHP_VERSION,5.4)>=0)$ya[]=JSON_PRETTY_PRINT;$Y=call_user_func_array('json_encode',$ya);$Ac="json";}$af=($y=="mssql"&&$q["auto_increment"]);if($af&&!$_POST["save"])$Ac=null;$Bc=(isset($_GET["select"])||$af?array("orig"=>lang(8)):array())+$b->editFunctions($q);$Da=" name='fields[$C]'";if($q["type"]=="enum")echo
nbsp($Bc[""])."<td>".$b->editInput($_GET["edit"],$q,$Da,$Y);else{$Ic=(in_array($Ac,$Bc)||isset($Bc[$Ac]));echo(count($Bc)>1?"<select name='function[$C]'>".optionlist($Bc,$Ac===null||$Ic?$Ac:"")."</select>".on_help("getTarget(event).value.replace(/^SQL\$/, '')",1).script("qsl('select').onchange = functionChange;",""):nbsp(reset($Bc))).'<td>';$dd=$b->editInput($_GET["edit"],$q,$Da,$Y);if($dd!="")echo$dd;elseif(preg_match('~bool~',$q["type"]))echo"<input type='hidden'$Da value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$Da value='1'>";elseif($q["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$q["length"],$Hd);foreach($Hd[1]as$t=>$X){$X=stripcslashes(str_replace("''","'",$X));$Wa=(is_int($Y)?($Y>>$t)&1:in_array($X,explode(",",$Y),true));echo" <label><input type='checkbox' name='fields[$C][$t]' value='".(1<<$t)."'".($Wa?' checked':'').">".h($b->editVal($X,$q)).'</label>';}}elseif(preg_match('~blob|bytea|raw|file~',$q["type"])&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$C'>";elseif(($Xf=preg_match('~text|lob~',$q["type"]))||preg_match("~\n~",$Y)){if($Xf&&$y!="sqlite")$Da.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$Da.=" cols='30' rows='$L'".($L==1?" style='height: 1.2em;'":"");}echo"<textarea$Da>".h($Y).'</textarea>';}elseif($Ac=="json"||preg_match('~^jsonb?$~',$q["type"]))echo"<textarea$Da cols='50' rows='12' class='jush-js'>".h($Y).'</textarea>';else{$Md=(!preg_match('~int~',$q["type"])&&preg_match('~^(\\d+)(,(\\d+))?$~',$q["length"],$B)?((preg_match("~binary~",$q["type"])?2:1)*$B[1]+($B[3]?1:0)+($B[2]&&!$q["unsigned"]?1:0)):($vg[$q["type"]]?$vg[$q["type"]]+($q["unsigned"]?0:1):0));if($y=='sql'&&min_version(5.6)&&preg_match('~time~',$q["type"]))$Md+=7;echo"<input".((!$Ic||$Ac==="")&&preg_match('~(?<!o)int(?!er)~',$q["type"])&&!preg_match('~\[\]~',$q["full_type"])?" type='number'":"")." value='".h($Y)."'".($Md?" data-maxlength='$Md'":"").(preg_match('~char|binary~',$q["type"])&&$Md>20?" size='40'":"")."$Da>";}echo$b->editHint($_GET["edit"],$q,$Y);$nc=0;foreach($Bc
as$z=>$X){if($z===""||!$X)break;$nc++;}if($nc)echo
script("mixin(qsl('td'), {onchange: partial(skipOriginal, $nc), oninput: function () { this.onchange(); }});");}}function
process_input($q){global$b,$o;$v=bracket_escape($q["field"]);$Ac=$_POST["function"][$v];$Y=$_POST["fields"][$v];if($q["type"]=="enum"){if($Y==-1)return
false;if($Y=="")return"NULL";return+$Y;}if($q["auto_increment"]&&$Y=="")return
null;if($Ac=="orig")return($q["on_update"]=="CURRENT_TIMESTAMP"?idf_escape($q["field"]):false);if($Ac=="NULL")return"NULL";if($q["type"]=="set")return
array_sum((array)$Y);if($Ac=="json"){$Ac="";$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}if(preg_match('~blob|bytea|raw|file~',$q["type"])&&ini_bool("file_uploads")){$kc=get_file("fields-$v");if(!is_string($kc))return
false;return$o->quoteBinary($kc);}return$b->processInput($q,$Y,$Ac);}function
fields_from_edit(){global$o;$J=array();foreach((array)$_POST["field_keys"]as$z=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$z];$_POST["fields"][$X]=$_POST["field_vals"][$z];}}foreach((array)$_POST["fields"]as$z=>$X){$C=bracket_escape($z,1);$J[$C]=array("field"=>$C,"privileges"=>array("insert"=>1,"update"=>1),"null"=>1,"auto_increment"=>($z==$o->primary),);}return$J;}function
search_tables(){global$b,$i;$_GET["where"][0]["val"]=$_POST["query"];$rf="<ul>\n";foreach(table_status('',true)as$R=>$S){$C=$b->tableName($S);if(isset($S["Engine"])&&$C!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$I=$i->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",$b->selectSearchProcess(fields($R),array())),1));if(!$I||$I->fetch_row()){$He="<a href='".h(ME."select=".urlencode($R)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$C</a>";echo"$rf<li>".($I?$He:"<p class='error'>$He: ".error())."\n";$rf="";}}}echo($rf?"<p class='message'>".lang(9):"</ul>")."\n";}function
dump_headers($Qc,$Sd=false){global$b;$J=$b->dumpHeaders($Qc,$Sd);$qe=$_POST["output"];if($qe!="text")header("Content-Disposition: attachment; filename=".$b->dumpFilename($Qc).".$J".($qe!="file"&&!preg_match('~[^0-9a-z]~',$qe)?".$qe":""));session_write_close();ob_flush();flush();return$J;}function
dump_csv($K){foreach($K
as$z=>$X){if(preg_match("~[\"\n,;\t]~",$X)||$X==="")$K[$z]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($_POST["format"]=="tsv"?"\t":";")),$K)."\r\n";}function
apply_sql_function($Ac,$f){return($Ac?($Ac=="unixepoch"?"DATETIME($f, '$Ac')":($Ac=="count distinct"?"COUNT(DISTINCT ":strtoupper("$Ac("))."$f)"):$f);}function
get_temp_dir(){$J=ini_get("upload_tmp_dir");if(!$J){if(function_exists('sys_get_temp_dir'))$J=sys_get_temp_dir();else{$s=@tempnam("","");if(!$s)return
false;$J=dirname($s);unlink($s);}}return$J;}function
file_open_lock($s){$zc=@fopen($s,"r+");if(!$zc){$zc=@fopen($s,"w");if(!$zc)return;chmod($s,0660);}flock($zc,LOCK_EX);return$zc;}function
file_write_unlock($zc,$tb){rewind($zc);fwrite($zc,$tb);ftruncate($zc,strlen($tb));flock($zc,LOCK_UN);fclose($zc);}function
password_file($ob){$s=get_temp_dir()."/adminer.key";$J=@file_get_contents($s);if($J||!$ob)return$J;$zc=@fopen($s,"w");if($zc){chmod($s,0660);$J=rand_string();fwrite($zc,$J);fclose($zc);}return$J;}function
rand_string(){return
md5(uniqid(mt_rand(),true));}function
select_value($X,$A,$q,$Yf){global$b;if(is_array($X)){$J="";foreach($X
as$kd=>$W)$J.="<tr>".($X!=array_values($X)?"<th>".h($kd):"")."<td>".select_value($W,$A,$q,$Yf);return"<table cellspacing='0'>$J</table>";}if(!$A)$A=$b->selectLink($X,$q);if($A===null){if(is_mail($X))$A="mailto:$X";if(is_url($X))$A=$X;}$J=$b->editVal($X,$q);if($J!==null){if($J==="")$J="&nbsp;";elseif(!is_utf8($J))$J="\0";elseif($Yf!=""&&is_shortable($q))$J=shorten_utf8($J,max(0,+$Yf));else$J=h($J);}return$b->selectVal($J,$A,$q,$X);}function
is_mail($Nb){$Aa='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$Eb='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$ze="$Aa+(\\.$Aa+)*@($Eb?\\.)+$Eb";return
is_string($Nb)&&preg_match("(^$ze(,\\s*$ze)*\$)i",$Nb);}function
is_url($Q){$Eb='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^(https?)://($Eb?\\.)+$Eb(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q);}function
is_shortable($q){return
preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~',$q["type"]);}function
count_rows($R,$Z,$id,$Cc){global$y;$H=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($id&&($y=="sql"||count($Cc)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$Cc).")$H":"SELECT COUNT(*)".($id?" FROM (SELECT 1$H GROUP BY ".implode(", ",$Cc).") x":$H));}function
slow_query($H){global$b,$jg;$n=$b->database();$bg=$b->queryTimeout();if(support("kill")&&is_object($j=connect())&&($n==""||$j->select_db($n))){$pd=$j->result(connection_id());echo'<script',nonce(),'>
var timeout = setTimeout(function () {
	ajax(\'',js_escape(ME),'script=kill\', function () {
	}, \'kill=',$pd,'&token=',$jg,'\');
}, ',1000*$bg,');
</script>
';}else$j=null;ob_flush();flush();$J=@get_key_vals($H,$j,$bg,false);if($j){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$Qe=rand(1,1e6);return($Qe^$_SESSION["token"]).":$Qe";}function
verify_token(){list($jg,$Qe)=explode(":",$_POST["token"]);return($Qe^$_SESSION["token"])==$jg;}function
lzw_decompress($Ma){$Cb=256;$Na=8;$bb=array();$cf=0;$df=0;for($t=0;$t<strlen($Ma);$t++){$cf=($cf<<8)+ord($Ma[$t]);$df+=8;if($df>=$Na){$df-=$Na;$bb[]=$cf>>$df;$cf&=(1<<$df)-1;$Cb++;if($Cb>>$Na)$Na++;}}$Bb=range("\0","\xFF");$J="";foreach($bb
as$t=>$ab){$Mb=$Bb[$ab];if(!isset($Mb))$Mb=$Xg.$Xg[0];$J.=$Mb;if($t)$Bb[]=$Xg.$Mb[0];$Xg=$Mb;}return$J;}function
on_help($gb,$yf=0){return
script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $gb, $yf) }, onmouseout: helpMouseout});","");}function
edit_form($a,$r,$K,$Cg){global$b,$y,$jg,$p;$Sf=$b->tableName(table_status1($a,true));page_header(($Cg?lang(10):lang(11)),$p,array("select"=>array($a,$Sf)),$Sf);if($K===false)echo"<p class='error'>".lang(12)."\n";echo'<form action="" method="post" enctype="multipart/form-data" id="form">
';if(!$r)echo"<p class='error'>".lang(13)."\n";else{echo"<table cellspacing='0'>".script("qsl('table').onkeydown = editingKeydown;");foreach($r
as$C=>$q){echo"<tr><th>".$b->fieldName($q);$xb=$_GET["set"][bracket_escape($C)];if($xb===null){$xb=$q["default"];if($q["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$xb,$Ve))$xb=$Ve[1];}$Y=($K!==null?($K[$C]!=""&&$y=="sql"&&preg_match("~enum|set~",$q["type"])?(is_array($K[$C])?array_sum($K[$C]):+$K[$C]):$K[$C]):(!$Cg&&$q["auto_increment"]?"":(isset($_GET["select"])?false:$xb)));if(!$_POST["save"]&&is_string($Y))$Y=$b->editVal($Y,$q);$Ac=($_POST["save"]?(string)$_POST["function"][$C]:($Cg&&$q["on_update"]=="CURRENT_TIMESTAMP"?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(preg_match("~time~",$q["type"])&&$Y=="CURRENT_TIMESTAMP"){$Y="";$Ac="now";}input($q,$Y,$Ac);echo"\n";}if(!support("table"))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select("field_funs[]",$b->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($r){echo"<input type='submit' value='".lang(14)."'>\n";if(!isset($_GET["select"])){echo"<input type='submit' name='insert' value='".($Cg?lang(15):lang(16))."' title='Ctrl+Shift+Enter'>\n",($Cg?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".lang(17)."...', this); };"):"");}}echo($Cg?"<input type='submit' name='delete' value='".lang(18)."'>".confirm()."\n":($_POST||!$r?"":script("focus(qsa('td', qs('#form'))[1].firstChild);")));if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo'<input type="hidden" name="referer" value="',h(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),'">
<input type="hidden" name="save" value="1">
<input type="hidden" name="token" value="',$jg,'">
</form>
';}if(isset($_GET["file"])){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");if($_GET["file"]=="favicon.ico"){header("Content-Type: image/x-icon");echo
lzw_decompress("\0\0\0` \0„\0\n @\0´C„è\"\0`EãQ¸àÿ‡?ÀtvM'”JdÁd\\Œb0\0Ä\"™ÀfÓˆ¤îs5›ÏçÑAXPaJ“0„¥‘8„#RŠT©‘z`ˆ#.©ÇcíXÃşÈ€?À-\0¡Im? .«M¶€\0È¯(Ì‰ıÀ/(%Œ\0");}elseif($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("\n1Ì‡“ÙŒŞl7œ‡B1„4vb0˜Ífs‘¼ên2BÌÑ±Ù˜Şn:‡#(¼b.\rDc)ÈÈa7E„‘¤Âl¦Ã±”èi1Ìs˜´ç-4™‡fÓ	ÈÎi7†³é†„ŒFÃ©”vt2‚Ó!–r0Ïãã£t~½U'3M€ÉW„B¦'cÍPÂ:6T\rc£A¾zr_îWK¶\r-¼VNFS%~Ãc²Ùí&›\\^ÊrÀ›­æu‚ÅÃôÙ‹4'7k¶è¯ÂãQÔæhš'g\rFB\ryT7SS¥PĞ1=Ç¤cIèÊ:d”ºm>£S8L†Jœt.M¢Š	Ï‹`'C¡¼ÛĞ889¤È QØıŒî2#8Ğ­£’˜6mú²†ğjˆ¢h«<…Œ°«Œ9/ë˜ç:Jê)Ê‚¤\0d>!\0Z‡ˆvì»në¾ğ¼o(Úó¥ÉkÔ7½sàù>Œî†!ĞR\"*nSı\0@P\"Áè’(‹#[¶¥£@g¹oü­’znş9k¤8†nš™ª1´I*ˆô=Ín²¤ª¸è0«c(ö;¾Ã Ğè!°üë*cì÷>Î¬E7DñLJ© 1Èä·ã`Â8(áÕ3M¨ó\"Ç39é?Ee=Ò¬ü~ù¾²ôÅîÓ¸7;ÉCÄÁ›ÍE\rd!)Âa*¯5ajo\0ª#`Ê38¶\0Êí]“eŒêˆÆ2¤	mk×øe]…Á­AZsÕStZ•Z!)BR¨G+Î#Jv2(ã öîc…4<¸#sB¯0éú‚6YL\r²=£…¿[×73Æğ<Ô:£Šbx”ßJ=	m_ ¾ÏÅfªlÙ×t‹åIªƒHÚ3x*€›á6`t6¾Ã%UÔLòeÙ‚˜<´\0ÉAQ<P<:š#u/¤:T\\> Ë-…xJˆÍQH\nj¡L+jİzğó°7£•«`İğ³\nkƒƒ'“NÓvX>îC-TË©¶œ¸†4*L”%Cj>7ß¨ŠŞ¨­õ™`ù®œ;yØûÆqÁrÊ3#¨Ù} :#ní\rã½^Å=CåAÜ¸İÆs&8£K&»ô*0ÑÒtİSÉÔÅ=¾[×ó:\\]ÃEİŒ/Oà>^]ØÃ¸Â<èØ÷gZÔV†éqº³ŠŒù ñËx\\­è•ö¹ßŞº´„\"J \\Ã®ˆû##Á¡½D†Îx6êœÚ5xÊÜ€¸¶†¨\rHøl ‹ñø°bú r¼7áÔ6†àöj|Á‰ô¢Û–*ôFAquvyO’½WeM‹Ö÷‰D.Fáö:RĞ\$-¡Ş¶µT!ìDS`°8D˜~ŸàA`(Çemƒ¦òı¢T@O1@º†X¦â“\nLpğ–‘PäşÁÓÂm«yf¸£)	‰«ÂˆÚGSEI‰¥xC(s(a?\$`tE¨n„ñ±­,÷Õ \$a‹U>,èĞ’\$ZñkDm,G\0å \\iú£%Ê¹¢ n¬¥¥±·ìİÜgÉ„b	y`’òÔ†ËWì· ä——¡_CÀÄT\niÏH%ÕdaÀÖiÍ7íAt°,Á®J†X4nˆ‘”ˆ0oÍ¹»9g\nzm‹M%`É'Iü€Ğ-èò©Ğ7:pğ3pÇQ—rEDš¤×ì àb2]…PF ı¥É>eÉú†3j\n€ß°t!Á?4ftK;£Ê\rÎĞ¸­!àoŠu?ÓúPhÒ0uIC}'~ÅÈ2‡vşQ¨ÒÎ8)ìÀ†7ìDIù=§éy&•¢eaàs*hÉ•jlAÄ(ê›\"Ä\\Óêm^i‘®M)‚°^ƒ	|~Õl¨¶#!YÍf81RS Áµ!‡†è62PÆC‘ôl&íûäxd!Œ| è9°`Ö_OYí=ğÑGà[EÉ-eLñCvT¬ )Ä@j-5¨¶œpSg».’G=”ĞZEÒö\$\0¢Ñ†KjíU§µ\$ ‚ÀG'IäP©Â~ûÚğ ;ÚhNÛG%*áRjñ‰X[œXPf^Á±|æèT!µ*NğğĞ†¸\rU¢Œ^q1V!ÃùUz,ÃI|7°7†r,¾¡¬7”èŞÄ¾BÖùÈ;é+÷¨©ß•ˆAÚpÍÎ½Ç^€¡~Ø¼W!3PŠI8]“½vÓJ’Áfñq£|,êè9Wøf`\0áq”ZÎp}[Jdhy­•NêµY|ï™Cy,ª<s A{eÍQÔŸòhd„ìÇ‡ ÌB4;ks&ƒ¬ñÄİÇaŞøÅûé”Ø;Ë¹}çSŒËJ…ïÍ)÷=dìÔ|ÎÌ®NdÒ·Iç*8µ¢dlÃÑ“E6~Ï¨F¦•Æ±X`˜M\rÊ/Ô%B/VÀIåN&;êùã0ÅUC cT&.E+ç•óƒÀ°Š›éÜ@²0`;ÅàËGè5ä±ÁŞ¦j'™›˜öàÆ»Yâ+¶‰QZ-iôœyvƒ–I™5Úó,O|­PÖ]FÛáòÓùñ\0üË2™49Í¢™¢n/Ï‡]Ø³&¦ªI^®=Ól©qfIÆÊ= Ö]x1GRü&¦e·7©º)Šó'ªÆ:B²B±>a¦z‡-¥‰Ñ2.¯ö¬¸bzø´Ü#„¥¼ñ“ÄUá“ÆL7-¼w¿tç3Éµñ’ôe§ŠöDä§\$²#÷±¤jÕ@ÕG—8Î “7púÜğR YCÁĞ~ÁÈ:À@ÆÖEU‰JÜÙ;67v]–J'ØÜäq1Ï³éElôQĞ†i¾ÍÃÎñ„/íÿ{k<àÖ¡MÜpoì}ĞèrÁ¢qŒØìcÕÃ¤™_mÒwï¾^ºu–´ÅùÚüù½«¶Çlnş”™	ı_‘~·Gønèæ‹Ö{kÜßwãŞù\rj~—K“\0Ïİü¦¾-îúÏ¢B€;œà›öb`}ÁCC,”¹-¶‹LĞ8\r,‡¿klıÇŒòn}-5Š3u›gm¸òÅ¸À*ß/äôÊùÏî×ô`Ë`½#xä+B?#öÛN;OR\r¨èø¯\$÷ÎúöÉkòÿÏ™\01\0kó\0Ğ8ôÍaèé/t úû#(&Ìl&­ù¥p¸Ïì‚…şâÎiM{¯zp*Ö-g¨Âèv‰Å6œkë	åˆğœd¬Ø‹¬Ü×ÄA`");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("f:›ŒgCI¼Ü\n8œÅ3)°Ë7œ…†81ĞÊx:\nOg#)Ğêr7\n\"†è´`ø|2ÌgSi–H)N¦S‘ä§\r‡\"0¹Ä@ä)Ÿ`(\$s6O!ÓèœV/=Œ' T4æ=„˜iS˜6IO“ÊerÙxî9*Åº°ºn3\rÑ‰vƒCÁ`õšİ2G%¨YãæáşŸ1™Ífô¹ÑÈ‚l¤Ã1‘\ny£*pC\r\$ÌnTª•3=\\‚r9O\"ã	Ààl<Š\rÇ\\€³I,—s\nA¤Æeh+Mâ‹!q0™ıf»`(¹N{c–—+wËñÁY£–pÙ§3Š3ú˜+I¦Ôj¹ºıÏk·²n¸qÜƒzi#^rØÀº´‹3èâÏ[èºo;®Ë(‹Ğ6#ÀÒ\":cz>ß£C2vÑCXÊ<P˜Ãc*5\nº¨è·/üP97ñ|F»°c0ƒ³¨°ä!ƒæ…!¨œƒ!‰Ã\nZ%ÃÄ‡#CHÌ!¨Òr8ç\$¥¡ì¯,ÈRÜ”2…Èã^0·á@¤2Œâ(ğ88P/‚à¸İ„á\\Á\$La\\å;càH„áHX„•\nÊƒtœ‡á8A<ÏsZô*ƒ;IĞÎ3¡Á@Ò2<Š¢¬!A8G<Ôj¿-Kƒ({*\r’Åa1‡¡èN4Tc\"\\Ò!=1^•ğİM9O³:†;jŒŠ\rãXÒàL#HÎ7ƒ#Tİª/-´‹£pÊ;B Â‹\n¿2!ƒ¥Ít]apÎİî\0RÛCËv¬MÂI,\rö§\0Hv°İ?kTŞ4£Š¼óuÙ±Ø;&’ò+&ƒ›ğ•µ\rÈXbu4İ¡i88Â2Bä/âƒ–4ƒ¡€N8AÜA)52íúøËåÎ2ˆ¨sã8ç“5¤¥¡pçWC@è:˜t…ã¾´Öešh\"#8_˜æcp^ãˆâI]OHşÔ:zdÈ3g£(„ˆ×Ã–k¸î“\\6´˜2ÚÚ–÷¹iÃä7²˜Ï]\rÃxO¾nºpè<¡ÁpïQ®UĞn‹ò|@çËó#G3ğÁ8bA¨Ê6ô2Ÿ67%#¸\\8\rıš2Èc\ræİŸk®‚.(’	’-—J;î›Ñó ÈéLãÏ ƒ¼Wâøã§“Ñ¥É¤â–÷·nû Ò§»æıMÎÀ9ZĞs]êz®¯¬ëy^[¯ì4-ºU\0ta ¶62^•˜.`¤‚â.Cßjÿ[á„ % Q\0`dëM8¿¦¼ËÛ\$O0`4²êÎ\n\0a\rA„<†@Ÿƒ›Š\r!À:ØBAŸ9Ù?h>¤Çº š~ÌŒ—6ÈˆhÜ=Ë-œA7XäÀÖ‡\\¼\r‘Q<èš§q’'!XÎ“2úT °!ŒD\r§Ò,K´\"ç%˜HÖqR\r„Ì ¢îC =í‚ æäÈ<c”\n#<€5Mø êEƒœyŒ¡”“‡°úo\"°cJKL2ù&£ØeRœÀWĞAÎTwÊÑ‘;åJˆâá\\`)5¦ÔŞœBòqhT3§àR	¸'\r+\":‚8¤ÀtV“Aß+]ŒÉS72Èğ¤YˆFƒ¼Z85àc,æô¶JÁ±/+S¸nBpoWÅdÖ\"§Qû¦a­ZKpèŞ§y\$›’ĞÏõ4I¢@L'@‰xCÑdfé~}Q*”ÒºAµàQ’\"BÛ*2\0œ.ÑÕkF©\"\r”‘° Øoƒ\\ëÔ¢™ÚVijY¦¥MÊôO‚\$Šˆ2ÒThH´¤ª0XHª5~kL©‰…T*:~P©”2¦tÒÂàB\0ıY…ÀÈÁœŸj†vDĞs.Ğ9“s¸¹Ì¤ÆP¥*xª•b¤o“õÿ¢PÜ\$¹W/“*ÃÉz';¦Ñ\$*ùÛÙédâmíÃƒÄ'b\rÑn%ÅÄ47Wì-Ÿ’àöÕ ¶K´µ³@<ÅgæÃ¨bBÑÿ[7§\\’|€VdR£¿6leQÌ`(Ô¢,Ñd˜å¹8\r¥]S:?š1¹`îÍYÀ`ÜAåÒ“%¾ÒZkQ”sMš*Ñ×È{`¯J*w¶×ÓŠ>îÕ¾ôDÏû›>ïeÓ¾·\"åt+poüüŸÊ=Ş*‚µApc7gæä ]ÓÊlî!×Ñ—+ÌûzsN¦îıàÀPÔšòia§y}U²ašÓù™`äãA¥­Á½Áw\n¡óÊ›Øj“ÿ<­:+Ÿ7;\"°ÕN3tqd4Åºg”ƒ¦T‹x€ªPH¨—FvWõV\nÕh;¢”BáD°Ø³/öbJ³İ\\Ê+ %¥ñ–÷îá]úúÑŠ½£wa×İ«¹Š¦»á¦ğèE‘­(iÉ!îô7ë×x±†z¤×Ò÷çÅHÉ³¸d´êmdéìèQ±r@§a•î¤ja?¤\r”\ryë4-4µfPáÒ‰WÃÊ`,¼x@§ƒİx¼ˆèA’¦K.€OÁi€¯o²;ê©ö–)±Ğ¨ºä’É†SÙdÙÓeOı™%ÙNĞåL78í¦Fãª›§SîáÒùöIÁÂ\rîÛZ˜²r^‰>ıĞì*‚d\ri°YüëYd‹uÃës‡*œ	ÌèE ¡Ê½éD§9æë!Â>ùkCá€›A‡Ád®åâ°!WWì1ğğÿQAæœÛk½°d%¦Ü# ïy†°{›–`}Té_YY‹R®ğ-¹MôºO–2ÖâÊ,Ë,Å É`ú-2ÓÀ÷¨+]L•È7E¤Ôç{`¢ƒË•­ñ~wì-…×ı ©M6¥¤]Fóûƒ¦@™§Ìe`°/˜8¹@‡e¦ÍØ\\ap.‚H¥ûĞC´Àæ*EAoz2¹Æçg0úˆ?]Í~Ÿs°ñÏ`ŒhJ`†êç®¤`û}‡áÍ^`èÑÃ>§ÈOñ5\rğW^Iœõõ\n³ù¬ı;ñ¸´ğ:ŸäÏ_h›n±µŒ´ßYP4®ğˆ)û *ı¸îÉõ¯æÑ6vÖä[Ë¤­C;ûö³ïã»¶näW/jº<\$J*qÄ¢ûä°ú-LôŒ\0µ¯ãï÷\0Oš\$ëZW zş	\0}Ú.4F„\rnu\0âàØÀä‹’éLŞ ÷IA\nz›©*–©ªŠjJ˜Ì…PŠ¢ë‚Ğp…Â6€Ø¦NšDÈBf\\	\0¨	 ˜W@L\rÀÄ`àg'Bd¯	Bi	œ°‚‰*|r%|\nr\r#°„@w®»î(T.¬vâ8ñÊâ\nm˜¥ğ<pØÔ`úY0ØÔâğÀÊö\0Ğ#€Ì‘}.I œx¢T\\âôÑ\n ÍQ‘æ@bR MFÙÇ|¢è%0SDr§ÂÈ f/b–àÂá¢:áík/şã	f%äĞ¨®e\nx\0Âl\0ÌÅÚ	‘0€W`ß¥Ú\nç8\r\0}p²‘›Â;\0È.Bè¤Vù§,z&Àf Ì\röWOcKƒ\nì» ’åÒkªz2\rñÉÀîW@Â’ç%\n~1€‚X ¤ßqâD¢!°^ù¦t<§\$²{0<E¦ÊÑª³2&ÜNÒ\r\næ^iÀ\"è³#nı ì­#2D§ˆüË®Dâæo!¬zK6Âë:ïìÃÏğ#RlÓ%q'kŞ¾*¸«Ã€à¶ Z@ºòJÌ`^PàHÀbSR|§	%|öôì.ÿ¯Âµ²^ßrc&oæÑk<ÿ­şí&ş²xK²Õ'æüLÄ‚«ò‹(ò’òmE)¥*–ÿ¬`R¥bWGbTRø½î`VNf¢®jæğ´woVèè˜(\"­’Ú§ô&s\0§².²¦Ş³8=h®ë Q&üân*hø\0òv¢BèGØè@\\F\n‚WÅr f\$óe6‘6àaã¤¥¢5H•ñâ°bYĞfÓRF€Ñ9¨(Òº³.EQå*Êî¸ë(Ú1‰*Â/+,º\"ˆö\r Ü	ªâ8ı\0ˆü3@İ%lå­ã¥,+¼¼å&í#-\$¦óÈ%†ÌÅgF!sİ1³Ö%¯Ôsó/¥nKªq”\0O\"EA©8…2ÀŠ}5\0Ë8‹ŸA\n¯ÅRrH…Ú³‡9Å4UìdW3!b¨z`í>ãF>Òi,”a?L>°´`´r¾±r ta;L¦ëÅ%ÀRxîŒ‰R†ëtŠÊ¥HW/m7Dr¶EsG2Î.B5Iî°ëÉQ3â_€ÒÔˆë´¤§24.ì‰ÅRkâ€z@¶@ºNì[4Î&<%b>n¦YPWÎŸâ“6n\$bK5“t‡âZB³YI Lê~G³YÎÖñcQc	6DXÖµ\"}ÆfŠĞ¢IÎj€ó5“\\ö XÙ¢td®„\nbtNaEÀTb;lâp‚Õ|\0Ô¯x\n‚ådVÖíŒÖà]Xõ“Yf„÷%D`‡QbØsvDsk0ÓqT¥ÿ7“l c7ç€ä ÖôÎSZ”6äï¾ãµŠÄ#êx‚Õh Õšâ¬£`·_`Ü¾ÎÚ§±•ê¥œ·+w`Ö%U§…’ï©è™¯¶ïÌ»U òöD‹Xl#µ†Ju¯[ åQ'×\\Hğ÷„¤÷äGRÕë0«oaĞõÓCÃX¥+ÔaícàNä®`ÖreÚ\n€Ò%¤4šS_­k_àÚš!3({7ó’bI\rV\r÷5ç×\0µ\\“€aeSg[Óz f-PöO,ju;XUvĞîıÖÃmËl…\"\\B1ÄİÅ0æ µ‘pğå4á•ë;2*‘î.b£\0ØØuÔãJ\"NV‰ÛrrOÕfî2äW3[‰Ø¢”¤³	€ËÆ5\r7²Ë0,ytÉÛwS	W	]kGÓX·iA*=P\rbs\"®\\÷o{eÀòœ¶5k€ïkÆ<±‚;®;xÕ¶-ö0§É_\$4İ ²¶´™8*i\0f›.Ñ(`¼•òñD`æP·&Œô˜ŒÄA+eB\"ZÀ¨¶³¢WÌ¢\\M>¶wö÷ú¶Ëg0¦ãGààš…‘Òø´\rÆÜ©*İf\\òŒp\0ğ¼‚åKf#€ÛÀËƒ\rÎÙÍ¡ƒØ@\r÷‚Öd ¢Ÿ\nó&D°%‚Ø3­wı‚©.}÷ùÏÿÅ­‚ ñ‚kHÆk1x~]¸PÙ­Óƒ€[…Œ;…ÀY€ØˆØ‘KÅ6 ËZäÖàtµ©>gL\r€àHsMìºe¤\0Ÿä&3²\$ë‰n3íü wÊ“7Õ—®·\"ôÒë+İ;¢s;é” *1™ y*îË®;TG|ç|B©! {!åÅ\"/Ê–oÎãj÷Wë+µæ“LşDJş’Í…´w2´ÆVTZ¹Gg/šıÖŠƒ]4n½4²À¿±Á‹Ï÷i©=ÈT…ˆ]dâ&¦ÀÄM\0Ö[88‡È®Eæ–â8&LXVmôvÀ±	Ê”j„×›‡FåÄ\\™Â	™ÆÊ&t\0Q›à\\\"òb€°	àÄ\rBs	wÂ	ŸõŸ‚N š7ÇC/|Ù×	€¨\n\nNúıK›yà*A™`ñWÏYvUZ4tz;~0}šñJ?hW£d*#É3€åĞàyF\nKTë¤Åæ@|„gy›\0ÊOÀxôa§`w£Z9¥ŒbO„»¨ÚWY’RÄÉ}J¾ˆXÊÚPñU2`÷©šG©åbeuª…zWö+œÈğ\rè¬\$4ƒ…\"\n\0\n`¨X@Nà‹®%d|‚hé¬ÈÚ™ŞÅ‡egÄê‚+âH¸t™(ªŞÑ( À^\0Zk@îªP¦@%Â(WÍ{¬º/¯ºşt{o\$â\0[³èŞ±¡„%¡§ë´É™¯‚hU]¤B,€rDèğe:D§¢ÌX«†V&ÚWll@ÀdòìY4 Ë¯›iYy¡š[‘¬Ã+«Z¹©]¦g·‡FrÚFû´wŞµ”#1¦tÏ¦¤ÃN¢hq`å§Dóğğ§v|º¦Z…Lúv…:S¨ú@åeº»ÿB’ƒ.2‡¬EŠ%Ú¯Bè’@[”ŠúÖB£*Y;¿™[ú#ª”©™›µ@:5Ã`Y8Û¾–è&¹è	@¦	àœüQÅS8!›£³»Â Â¼¢2MY„äO;¾«©Æ›È)êõFÂ¨FZõA\\1 PF¨B¤lF+šó”<ÚRÊ><J?šÚ{µf’õkÄ˜8®ëW‚¬èë®ºM\r•Í¼Û–RsC÷NÍô€î”%©ÊJë~Á˜?·Úâ¯,\r4×k0µ,Jóª•b—öo\0Ê!1 ø5'¦\ràø·u\r\0øÊ\$¡Ğ=š}\r7NÌÔ=DW6Kø8võ\r³ Ê\n ¤	*‚\r»Ä7)¦ÏDüm›1	aÖ@ßÖ‡°¨w.äT”Èİ~©Ç¼pV½ÀœJ‚u¢\rä&N MqcÊdĞĞdĞ8îğØ€_ĞK×aU&®H#]°d}`P¬\0~ÀU/ª…ñƒ…ùÌynY<>dC·<GÉ@éÃ\"’eZS¹wã•›“ÆGy¼\\j)ğ}•¤\r5â1,pª^u\0èéˆÕÆnÌÚC©ºHPÖ¬G<Ÿšp‹ô2¨\nèFDÜ\rÖ\$°­yuycöçõv6İe)ÖpÛYHÏÄ’õŞ#VP¾€üÕØeW®Ş=mÙæc:&‰¥æ-ÛÄPv.£Ë€øæºğš	‹úØ£\0\$êÁ@+×ì¹Pÿl&_çCb-U&0\"åF…®Vy¸p\rÄa5Ûq9U>5è\\LBg†èU­[¶7m düóyV[5Ÿ*}Õ4ø5/ç¶àÒ¾HöD60 ¿­Åì¿íÃ:Suy\r„¼‡ãSMÀŸÂ;W“ªØÎµL4ÖG¢NØã°§–Ÿõ ÜeÜmğšt„Èsq¶€˜\".Fÿ™§CsQ¸ h€e7äünØ>°²*àc!iSİj¾†Ì­Ù‘ü°ø‚°ü {üµ­÷%t€ê\0`&lrÅ“,Ü!0ahy	RµB=ÍegWãùo\0¦H‡h/v(’N4‘\rı„ÀTz„&q÷?X\$€X!ôJ^,Ÿ­öbó“ı`2@:†¼7ÃCX’H€e¡Š@qïÛ\ny¶ 0¦è‹´£´€ñPÀO02@èv‰/IPa°2ÀÜ0\n]-(^Æüt.½•3&Ç\"«0¤˜\"Ğ\0]°1šÍñaÂ˜´°E³SúÄP|\\€ÉÑAõpú9›\$K˜ˆByuØ¯zë7Z•\rìb¤uÉ_ïò8õÆmãq³ğû˜E<-ÈÉ@\0®!)³Ä )÷)Õ~Qå	rÙ‘Ü/MèPÿ\nº	¦É`à!\n(ˆ‚\n\n>X€Ğ!` WºËáø¼àp4AÚ	Å¶Á©d‘Ç\0XÒÙ§V\n€+Cd/EØFåâ¯m+`\0Ş2´ôp/-ØÌ2·™´eæËC@C„\0pX,4½ìª¼ƒÌ9àòÔXt!.Pß˜\\ı•q„£b{…vˆbfMÃÍ)D]ûw„˜°ŸË… XàB4'»—fÀtXĞ¦¢(O Õ¾©	ğ‘qü#³3¸«p]¢i\".ªè7¬iw[T\0y\rÄ4Cå;,\$a2i(™\$µmÈ†DÒ&Ô”4¥Z â;E#6UAÄR€­üìeFFUŒ1•h2\n¨÷UpÖ‡ÃéTÊ¹€âÏØÕ[î+‘^ôXÕ¤Ù78 A\rnK‚‚d1´>€pƒ+¦`Î:‡‹Iƒo<ÚL„@äa	¾€´\0:ˆ†İG—½ hQ„\$ùjR¸Ç'ÉÈŒ¯K!ı`¨£¸1ÅÒÀHƒCÆâZ0\$ÀeÉyXG£5hÎEâ\r1ŸG‚\nº`·g'\0¼İ6qVã(\r‡„VPHöÇŒëbÖŠ\r¯-k–\0B‘bÆıØGß:½áŒZ×Ñ|¹>*ÄXXÙ!¡’£´\"&öÀ:EÕa«÷,vB P‰h!pf;\0£¾[Á‘/r:qTƒèÙ8\"x3Gl‘İ\"Xm#Ã`è5ÑæÜx\n¨óG¶;ÑşEQ—X¹Ç‚<HhAúå¢ê·+1Nsº´ã¡µk•jsH{€Øõãï&1•GãaIÊ?76š22Îp4™ş—È™V!°Á‡¢º2ÍŸ:€¤z	IàÄ‰ZÔ1ER7Ãİ%£¶ÂôÅ6!Á?@(•ä–‘ï,&…2’¸ò”>™I8 ÒP+œ”‚hâ&7N'2V˜š\0Ñ¢i\0œ‡ËÜ™i%8ù¹V8e„Z:Ò@Ê´°ñ6ä¦R{¨JzÔs2…	j(C`Z*ôˆJ-bçë#¸DEu\$¹WŒ*Œ¥*#9ˆ”D3y¥?\"Ø9ı,Q”/§ßw8ˆ‚UÀ=•qÿ™]\0ƒÊ¹¸mtøŒ-*ç(˜ğdÒ‰•!åƒ+FX\$IŒÌ„âîˆ¼ºU\$õ`‚‚Ìeò'c¦¿Vr¨n«Æ1l€Šõ5¬?XTÅ&*@ òIBÖtyt–fêõN¨ğ%ÂÅS™H˜xô\$Ü\0}/sH]]˜â»ôãÃP')HC&…ìIá1\ri.äU&\$…dIı<)ôÅÓÓ(	EPâˆT^\n¢7›(ˆ™T'&TÇÔ:.,µdªBjõ¸:D…ğ}u{¬a\0ì¦&mÑ1CCH\n˜5!ª²hÀlš@¸ÒÁàL©€â¹™i&Ä:™µ¿G†fqNš\\Ó\\|ğÇ „`»“X\nzâÌ–üz©™‡Ê¦³6`¸=\rJEƒ\n0¦äegÌÔÊÎ‰ÜË×\n‡Y™¾äWÎ®ƒû¯áM\$aÁæ'îíwZ°ÑDa¸L÷É\\¨1)‘‘Z=&«ZúA’.Ç´91úpŸ;™‰Øó•‘<ìãïA8ÑË,F¬	lË,=ò¤\0ø›'Íåè&yğKÔ5X”e÷“xw´ß§E3)ÒLÒpn¼™ôá¦€M9Å5I(sw“E&İ6Y™ÉˆÔõ€N9qM{I2èëÀÂeT„:aÈñ.ªI”ÜøX¢òtÓ •De&f§fniØ]’hbHE†`˜");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress(compile_file('','minify_js'));}else{header("Content-Type: image/gif");switch($_GET["file"]){case"plus.gif":echo"GIF89a\0\0\0001îîî\0\0€™™™\0\0\0!ù\0\0\0,\0\0\0\0\0\0!„©ËíMñÌ*)¾oú¯) q•¡eˆµî#ÄòLË\0;";break;case"cross.gif":echo"GIF89a\0\0\0001îîî\0\0€™™™\0\0\0!ù\0\0\0,\0\0\0\0\0\0#„©Ëí#\naÖFo~yÃ._wa”á1ç±JîGÂL×6]\0\0;";break;case"up.gif":echo"GIF89a\0\0\0001îîî\0\0€™™™\0\0\0!ù\0\0\0,\0\0\0\0\0\0 „©ËíMQN\nï}ôa8ŠyšaÅ¶®\0Çò\0;";break;case"down.gif":echo"GIF89a\0\0\0001îîî\0\0€™™™\0\0\0!ù\0\0\0,\0\0\0\0\0\0 „©ËíMñÌ*)¾[Wş\\¢ÇL&ÙœÆ¶•\0Çò\0;";break;case"arrow.gif":echo"GIF89a\0\n\0€\0\0€€€ÿÿÿ!ù\0\0\0,\0\0\0\0\0\n\0\0‚i–±‹”ªÓ²Ş»\0\0;";break;}}exit;}if($_GET["script"]=="version"){$zc=file_open_lock(get_temp_dir()."/adminer.version");if($zc)file_write_unlock($zc,serialize(array("signature"=>$_POST["signature"],"version"=>$_POST["version"])));exit;}global$b,$i,$Fb,$Kb,$Sb,$p,$Bc,$Fc,$aa,$cd,$y,$ba,$td,$ee,$_e,$Lf,$Jc,$jg,$ng,$vg,$Bg,$ca;if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if($_SERVER["HTTP_X_FORWARDED_PREFIX"])$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];$aa=$_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off");@ini_set("session.use_trans_sid",false);if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");$F=array(0,preg_replace('~\\?.*~','',$_SERVER["REQUEST_URI"]),"",$aa);if(version_compare(PHP_VERSION,'5.2.0')>=0)$F[]=true;call_user_func_array('session_set_cookie_params',$F);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$mc);if(get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("zend.ze1_compatibility_mode",false);@ini_set("precision",15);$td=array('en'=>'English','ar'=>'Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©','bg'=>'Ğ‘ÑŠĞ»Ğ³Ğ°Ñ€ÑĞºĞ¸','bn'=>'à¦¬à¦¾à¦‚à¦²à¦¾','bs'=>'Bosanski','ca'=>'CatalÃ ','cs'=>'ÄŒeÅ¡tina','da'=>'Dansk','de'=>'Deutsch','el'=>'Î•Î»Î»Î·Î½Î¹ÎºÎ¬','es'=>'EspaÃ±ol','et'=>'Eesti','fa'=>'ÙØ§Ø±Ø³ÛŒ','fi'=>'Suomi','fr'=>'FranÃ§ais','gl'=>'Galego','he'=>'×¢×‘×¨×™×ª','hu'=>'Magyar','id'=>'Bahasa Indonesia','it'=>'Italiano','ja'=>'æ—¥æœ¬èª','ko'=>'í•œêµ­ì–´','lt'=>'LietuviÅ³','ms'=>'Bahasa Melayu','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'PortuguÃªs','pt-br'=>'PortuguÃªs (Brazil)','ro'=>'Limba RomÃ¢nÄƒ','ru'=>'Ğ ÑƒÑÑĞºĞ¸Ğ¹','sk'=>'SlovenÄina','sl'=>'Slovenski','sr'=>'Ğ¡Ñ€Ğ¿ÑĞºĞ¸','ta'=>'à®¤â€Œà®®à®¿à®´à¯','th'=>'à¸ à¸²à¸©à¸²à¹„à¸—à¸¢','tr'=>'TÃ¼rkÃ§e','uk'=>'Ğ£ĞºÑ€Ğ°Ñ—Ğ½ÑÑŒĞºĞ°','vi'=>'Tiáº¿ng Viá»‡t','zh'=>'ç®€ä½“ä¸­æ–‡','zh-tw'=>'ç¹é«”ä¸­æ–‡',);function
get_lang(){global$ba;return$ba;}function
lang($v,$ae=null){if(is_string($v)){$Ce=array_search($v,get_translations("en"));if($Ce!==false)$v=$Ce;}global$ba,$ng;$mg=($ng[$v]?$ng[$v]:$v);if(is_array($mg)){$Ce=($ae==1?0:($ba=='cs'||$ba=='sk'?($ae&&$ae<5?1:2):($ba=='fr'?(!$ae?0:1):($ba=='pl'?($ae%10>1&&$ae%10<5&&$ae/10%10!=1?1:2):($ba=='sl'?($ae%100==1?0:($ae%100==2?1:($ae%100==3||$ae%100==4?2:3))):($ba=='lt'?($ae%10==1&&$ae%100!=11?0:($ae%10>1&&$ae/10%10!=1?1:2)):($ba=='bs'||$ba=='ru'||$ba=='sr'||$ba=='uk'?($ae%10==1&&$ae%100!=11?0:($ae%10>1&&$ae%10<5&&$ae/10%10!=1?1:2)):1)))))));$mg=$mg[$Ce];}$ya=func_get_args();array_shift($ya);$xc=str_replace("%d","%s",$mg);if($xc!=$mg)$ya[0]=format_number($ae);return
vsprintf($xc,$ya);}function
switch_lang(){global$ba,$td;echo"<form action='' method='post'>\n<div id='lang'>",lang(19).": ".html_select("lang",$td,$ba,"this.form.submit();")," <input type='submit' value='".lang(20)."' class='hidden'>\n","<input type='hidden' name='token' value='".get_token()."'>\n";echo"</div>\n</form>\n";}if(isset($_POST["lang"])&&verify_token()){cookie("adminer_lang",$_POST["lang"]);$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=array();redirect(remove_from_uri());}$ba="en";if(isset($td[$_COOKIE["adminer_lang"]])){cookie("adminer_lang",$_COOKIE["adminer_lang"]);$ba=$_COOKIE["adminer_lang"];}elseif(isset($td[$_SESSION["lang"]]))$ba=$_SESSION["lang"];else{$qa=array();preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$Hd,PREG_SET_ORDER);foreach($Hd
as$B)$qa[$B[1]]=(isset($B[3])?$B[3]:1);arsort($qa);foreach($qa
as$z=>$Me){if(isset($td[$z])){$ba=$z;break;}$z=preg_replace('~-.*~','',$z);if(!isset($qa[$z])&&isset($td[$z])){$ba=$z;break;}}}$ng=$_SESSION["translations"];if($_SESSION["translations_version"]!=2859241323){$ng=array();$_SESSION["translations_version"]=2859241323;}function
get_translations($sd){switch($sd){case"en":$h="A9D“yÔ@s:ÀGà¡(¸ffƒ‚Š¦ã	ˆÙ:ÄS°Şa2\"1¦..L'ƒI´êm‘#Çs,†KƒšOP#IÌ@%9¥i4Èo2ÏÆó €Ë,9%ÀPÀb2£a¸àr\n2›NCÈ(Şr4™Í1C`(:Ebç9AÈi:‰&ã™”åy·ˆFó½ĞY‚ˆ\r´\n– 8ZÔS=\$Aœ†¤`Ñ=ËÜŒ²‚0Ê\nÒãdFé	ŒŞn:ZÎ°)­ãQ¦ÕÈmwÛø€İO¼êmfpQËÎ‚‰†qœêaÊÄ¯ ¢„\\Ã}ö5ğ#|@èhÚ3·ÃN¾}@¡ÑiÕ¦¦t´sN}+ö\\òp¤Û¥æ+÷ÌˆÎ NbBØ­8„µŒ#’Ê'£ î³`P2ğ+à²‰‰ëÚ.ËÂÎúH¤\nê:ãœ9PŞµ\rÎ¢ü6¹ƒ+`#ÑbÜ2áğ°¡‰Ä<³ HKHÈòü©`PH…¡ g&†­@ËŒc@æ#»À€µ:ó­°‚1¶-|¤‹Ìà[\$ÀPŒÁ­@ê1\rC,Ò ‹Œ«¶˜§E25Œ)3ë„ädƒÆJ”„Œ´\"LŸ1mU7Q‚P¥àP\$Bhš\nb˜-5Hò.ÍchZ2E3üĞ¦³kˆ6>;»ƒ0Ì6-m¤91£”T3 «È¨7£Q€Ü<£kàê1ŒjHæ3£di·ac/\$C=L-iúC0Ã«:6]›,Z(E©k[ÔknÛöƒ¡qÇTÅt]JJP½Ã\r€ˆb˜¤#2ãx×ÁşÖ2ïH³¦ÚÖ”	‘]’ƒÚv¨çkÛ)@¨ÅÃZ|3,îË†#%Ğ¿[ÍsÈ2>¡âF4 #0z(|£¢‹šã|ÒZÏ-0§J2[Èì¦ì^ôÆ47%	R¢ú,OH§ˆçLV{ŸÈà9‡Ax^;îrA™ArÎ3…ñ–úñFCœè˜|\$¬H¼ø:iR~ŸBÃ@ßİø|t¦¿•Fé™„¼%ÊÚ\"ˆ²k“bÃÉÏé5´ş2öYÍ&©ÌÎYç‘Ş¶ÜXv.ş*c\nz4àÌ3àp:rÜí„ïĞtKÿr(	ƒÖñğO0@(JD€¤X*LÑ%^ßõînÈ2L§sî½±Ò”S	‰3ueè70Vóq°z°Ã¢XK‰‚é\r§ñŞ½¦\r\0‰°i8áŸõªM]X :­\\ç›	{\"ƒrBRÊi}b‡|Ğâlİ0fNÁ™,zwÔXc#ÇbæÂeY2ÌQ%¦ğ¦µ,D)U¦gŠ1œ\$l´µÂs¯{Ì5ä”ç#8çË¹uæ4”‚Îa™#Ä€‘D°ÎÄ‚ˆL\$‡¨É’0ŒGE‰Ñ—‚IÍâÅ`ft˜²|¥â‰ÇA°\$ÈÅ#Ã*‹#ŠÖJHÂ%İ:‹7eøÎ†BP ‘ÜQ@0«äí)Wd¨‘ŠPÜJÉ\\™ C>°Ä ÒÃ[Ùk@‚7î\\˜iË@¼’péJk´\\¡T*`Z\nt&™'6S’dt¡\\’ïJe`Œ\$´RÒfq¿ÈDÍ÷/ªÍí—TJfLËLà9­SXpÀTU\r‘œóî^B©Ø6¥5`2™>V„E˜ŒÙ±Â>¹Ë<YVÌtP\0 t\r=* (+¹\"p@HB¸G³ ;Îy¾Ÿ¤”¸E!ô8ÙRñzœ‡)‡\$«bÒı4.¨ù…CE@gM+†åùhôìÀIO	rÄÔ²Ğeªpcª\0‚©à";break;case"ar":$h="ÙC¶P‚Â²†l*„\r”,&\nÙA¶í„ø(J.™„0Se\\¶\r…ŒbÙ@¶0´,\nQ,l)ÅÀ¦Âµ°¬†Aòéj_1CĞM…«e€¢S™\ng@ŸOgë¨ô’XÙDMë)˜°0Œ†cA¨Øn8Çe*y#au4¡ ´Ir*;rSÁUµdJ	}‰ÎÑ*zªU@¦ŠX;ai1l(nóÕòıÃ[Óy™dŞu'c(€ÜoF“±¤Øe3™Nb¦ êp2NšS¡ Ó³:LZúz¶PØ\\bæ¼uÄ.•[¶Q`u	!Š­Jyµˆ&2¶(gTÍÔSÑšMÆxì5g5¸K®K¦Â¦àØ÷á—0Ê‡Æ¢¶§\nS ü›r\$ ®êjÄ(î¢v†°Ì¶!Jb¸¡‰q««0\n¸šj\nÙˆé­¥jƒù@Åzšl<\$W¿ÈrØ“£åsœñ§Ì†U&…[Í*¯³lƒê (B&÷¾ÆÉè4_!©±b’>ñ,?t[¢	ãë?‰:²X¦Œ3Şœ:îšã•ÊÃ±7+SŠ‰Î¬§	JÄ*hı¡ª¬›ºÈ’,2 …B€ÈËd4àPH…á gL†)¥›kR<ñ‘Jº\"ÓjÚ½£åBh›F¡KDKoïUŒ„ØêQÑÂıY%È‹\r5 èóÚƒ¿ĞÊ²[E:|µ¡)‚N@ÊOªŸYËozã6§&Å±0®*]¶ÛJ	S¨%\$	Ğš&‡B˜¦\rCP^6¡x¶0ßƒ»A­¥´y\$*µC! C Ø3Í\0Â94£xÌ3\rPËt.P2ÙPHtŞÄÎJ\r5&àP¨7µhÂ7!\0ê7c¨Æ1¶C˜Ì:\0Ø7ŒïPæ6ƒ–Z0ŒãÔhá-6½C«vaJ“¡¥@!ŠbŒ‚*ˆúJ¢:…h]4!²Ü[*†¡/äo3Í3:ç‘¨Ì-»<NºC´k¾­›LÑ\rÃ[f3\rã–\\9½C8@ Œš`Ü2Z\0Æ7æHÉÊàÂĞâ0z)|2s\r×ã}k³¢ê'” lŠ¹…¥6b|A ‘ŠBW¯Só®SäìB&bhÂ9·|Páçc¿F£Àà4â&ÇÏt/D\r è8aĞ^ÿh\\0ò8°äq#8^2ß¸ğİó<À^é˜>	!´8ğÚş£¬uÅ,ÙÀszÔaµ¨5šPÒ\rK”eNL7B~Éñ×x\r´”Ÿ´ôcEÂ`nÕp’fA¥!Œ:—0¹®g!ˆÒ‡(â”Q„3=6Ì™ sfÌá3ÆMé´†\$l0Àp@Şì\r!„65Ğ„š¿k¥Ye¢²¬Ld`FlÔ†€àÙ9û0d4TµÂªIc*\"kœ70äá£6† ÕÃ\\l*Œ\$:3~iƒ|‹Œì;È’’HûBŒ\rå'èì×ˆ\"†NdÚ>â†ñs€+bÉÙ§7†¯Ó™(e£Ÿ#–”Óócåi§5¦JÔ&a\$“>#š’`9¿ˆ\"o›!ÔÙA€ÌƒxmqïÅÉ¸£zş\"»;™±JG½3bLÏä NÄ6M…\0Â¡\"2`¶ÇU`eÒSKê8½¥8BQ!6'	}ÙÕãa9óéFdÁP<uJF&:@Çx¨Ğ-V²µ½\0ìÇl­—@PŞüŸA¥Ç0¢\0fà€Ö¹ğŒ…)\rÊ04ÀIÒ&´Ø›S49Gæ·ù6şWÕ,[²_?GY½EØ*…HADNŠÆUê|@ò¢¦ƒ¢¡è*2\\Å î4¢fÃ r\r€¬1±PÆÁ7sáÚ.MIà€iÌ¶“0LÃhuz\0‚4€Ü¨TÀ´ğåæIëª\$é0«š6ñQ=âÜ¶Šr:¤9#kå±\\Q¨Vbğ\n	¶Á­ÊÍC1*DKU\n*§†Èé C/\"Ò¤°-Á\0\":¨¹8‚\"\\ªÌoNÊ¥ˆtĞ”Î¼t&a2“´·iMÉÁWqø8ÒhQIL¤F¨Å:µä–ìHù}¢Já¤»B\\í\\\na”×ÃI‰“®UJæ%ÄÛosSÈ,UÑuJí›Ë&†	Dc~UİÑd	 A ";break;case"bg":$h="ĞP´\r›EÑ@4°!Awh Z(&‚Ô~\n‹†faÌĞNÅ`Ñ‚şDˆ…4ĞÕü\"Ğ]4\r;Ae2”­a°µ€¢„œ.aÂèúrpº’@×“ˆ|.W.X4òå«FPµ”Ìâ“Ø\$ªhRàsÉÜÊ}@¨Ğ—pÙĞ”æB¢4”sE²Î¢7fŠ&EŠ, Ói•X\nFC1 Ôl7còØMEo)_G×ÒèÎ_<‡GÓ­}†Íœ,kë†ŠqPX”}F³+9¤¬7i†£Zè´šiíQ¡³_a·–—ZŠË*¨n^¹ÉÕS¦Ü9¾ÿ£YŸVÚ¨~³]ĞX\\Ró‰6±õÔ}±jâ}	¬lê4v±ø=ˆè†3	´\0ù@D|ÜÂ¤‰³[€’ª’^]#ğs.Õ3dŠ¯m XúÂÉ3’‡²îé \\µ	Òá¦.L\\ÍOºp©¥\r²À…¿ÍBz·.+šÒ¯«‰ºªš¯H’î¿*¬¶A·Îb^Ë¹23r—¹¢J•BÃÇ\"ŠÃÊ”ğLˆ’‰”|ú§Éªf÷šJnäµ‰¬x¢¸Å²d’k’¥ª¤8Ò#èç%5¨>Ø¿-¢)£äü¹AKÍSY0´&…\$™¡1<öhF‰\n¯<¶¨ĞƒKBi¼Y-Šú±@Eä02!­RÒ‰!-q/×jÚ>ı#ğ˜H…¡ gd†¨ùD¾	\"±V´\$Ò©SŠĞ²å¢ŠGNó‚hQ–Ü«nN+Y'&èÇI9)‹-	[‚(Z6Œ#HØ“Èœ°9ôŒ#y&‘İÄª‚úœBuòY3)i\\¾ªªñ#.ÊMTÂœWµıâW-%D³Ji.5àU¤®–! S‡F‚ŒNf(	Bjë=‹¡kL^gÍKM+×”-V;ë%¶ïNã`è9J÷r}'Fiü‰¡K‰Ch;k¼=„*u“¡“î6Œ‡ÚiR¥»Lö\$à\$pRMŠ%/²¼ùÜ®HÁlN<G»Ì\n[‡¿zdã„sL±¤jŞ9MlÊf\r‰mŠöÜ¼î2Şé!®Nñ:ÈnùRoü÷`»é7\n’mG±Eè=Á)+SËmÚiôrŸ¥×:fD¡©ŒBG&rxdÖÒš‘L3fKŒ:£«C­Ü¡‘ê‡:Ô;Sšã\r5pµÄÔbKÒ¹\$ìÂˆ*\rÜ5a\0Ì7A\0ê9#pÎ# ÚşC(rDaŒ7†çğ  „€äC0=E¸àÉCt!œğÂšÑ@Od­–Æ,‹Mé\0íµ¢Dèœ9SyªM½äøÕÉ¹<MjŞ¤H`‘Îú.8-Û›2xñ \\\rğD€è€s@¼‡x¬ƒş€È?@ÎÃ(nŒà:FñA|`ù°12¨‹`ìq/ÄÓ=&ÆßİK'Â<²Ÿ#¦»\\[3i8ÍšRÔŠ¤8ä Ş'¦		OÒ)\$AJ3TwHiú„ÇL©Ÿ‚¬ÁH\n}”Ç ( ëè6\0Å)„~aÈ6†UpC4e~¡Ì:†0ÆC˜sÁÖV†ÀŞßÌª\r!Ğ4	™)˜a– €1Àø Ğa\rÌæ3Œ´ŸGM¥w\$.Wšª5KlZ5Äôw”¹Ñ\n (NîÒò<4€€àR^a1Y5l€ ‡Cpe”ğngğ@iËè2†ymCß t~!¥ø†àŞ%(l˜ÁŞ[;Hˆ–«F‡KB„B‚‰‹v?D¥Œ~ä™Ï+óê>8z.iR²‡h¹Œ;TUÚÉ\r(È˜4@ÒCƒH|HÖŞôÚ£dÆTÚš³ÂİjUfô®TR¤ÿ\"Õ	€Mèè'8ŞóQò>ÅíJ0ÄyŞû	«Œ¢¹ ªÊäJ‹­)Ğµ¶¶¦—M\ry\r\$*¡É=\$fÄÙà°òÀ¢âyIòP©ç•‚ØèçfjÙ8&ï`ß0³Ñ&P^)(¡ó’¬ôz´\$\r‚Yâ‹›qu9À€)…˜YË¡k&‡[‚\0Œ'ª“Eu‰©#';èÑ©µÃ×Œ*pêĞ^Ã³Tİ,²®ª\\¸¡W[w.óé±çeW£»ËvÑÙµ-Ì…ìª4ŠnÖÑ1‡NÍ[«Ï|gUß“è¸ë2³=~ØãŠLL\0002AbÈ¯µlÀÅdÚ”@†ÓÃ`+/§L˜´„\\¬+¡^(E:º#èğÉ\$Ôû´)ıƒ[tƒpM<•4²_2D’¨TÀ´<—:ò(Œ8ãEsz‘!KD×BÔ°Ì®2.Â’çdµ¿“gƒ´s-ÎOØ9ÖÑ‘MvˆXÄÔĞ3Î˜7rO£ÄßgkÙ/Ædh{‰{Ä\"á•%êf!D¨£¸'š²8?¦VÃ¥š…áÃiM*™ÙL\\ÓÍ“i,‘«;jc>:gJ®†·]g‚‰e·=:Óf´ØÊGhØ¬7®—öDÏÌwZ4½†Î˜½#âh¯œ™ômÕj)¡¯æ’–öÎYíy•œû¨,CvÄˆòW{*¿Êİ<Ê}r¥”¬ä4†’d)vœp";break;case"bn":$h="àS)\nt]\0_ˆ 	XD)L¨„@Ğ4l5€ÁBQpÌÌ 9‚ \n¸ú\0‡€,¡ÈhªSEÀ0èb™a%‡. ÑH¶\0¬‡.bÓÅ2n‡‡DÒe*’D¦M¨ŠÉ,OJÃ°„v§˜©”Ñ…\$:IK“Êg5U4¡Lœ	Nd!u>Ï&¶ËÔöå„Òa\\­@'Jx¬ÉS¤Ñí4ĞP²D§±©êêzê¦.SÉõE<ùOS«éékbÊOÌafêhb\0§Bïğør¦ª)—öªå²QŒÁWğ²ëE‹{K§ÔPP~Í9\\§ël*‹_W	ãŞ7ôâÉ¼ê 4NÆQ¸Ş 8'cI°Êg2œÄO9Ôàd0<‡CA§ä:#Üº¸%3–©5Š!n€nJµmk”Åü©,qŸÁî«@á­‹œ(n+Lİ9ˆx£¡ÎkŠIÁĞ2ÁL\0I¡Î#VÜ¦ì#`¬æ¬‡B›Ä4Ã:Ğ ª,X‘¶í2À§§Î,(_)ìã7*¬è¶n¢\rÁ%3l¥ÃM”|¨ \r²öã¢m¢ä‡KÑKp€LKÂúÙC	‹€S.ëIL•FsÔW9ÊSÁ°³“TŒJzÜDÈËdz¾6­ò[Àí\$ßK‘û¬ŒÓl÷CÔT»ODu;t§««tÖIÑTÒˆJ©î}F¶ ñC\rYÔËÄNİÍ5,àSCélB…ÒÈ0·×TdÿX¯sP¶*5ÁO5ÏÊ!B§Ää½TAÓyJªÛòrÜõ¤~¯6å…İµ]!y(ØÉ<Vøß÷³µ8À­\\CËâ<ÀPHÁ iŠ†-g(“Š|ˆ¤MÎš×u-•¬b¼HëÂºEİ\$väMÔ…Ey…cdG¬+r¼\"òAÄ@yúÂC…ªúÜ·J}×F5¤=_{ dıAŞ•L_Ûn)@¶F\"·4:Ô 5éc…1ÊÙ­¸¹Zƒ\\ÑŠ5R`x*óf6ú@A²Û4£´mZÖJ˜må6hÛîNÚŸ44’@\$Bhš\nb˜:Ãh\\-\\èÔ.©èM¥ËìM…œàÑ,Í4Ç¹`Ø:@SºïŒ#“È7ŒÃ0Ù&°%—pÍòÌırl¨XSZz¨7¼ãhÂ7!\0ê7c¨Æ1¾#˜Ì:\0Ø7Œò`æ>c—¢0Œã˜}zğÛ&¯ĞP9…=ò·=”äB¬b˜¤”êEA*0ˆÒÌ¢Sx®%n¤›8ğÛÂ¸PkÀS<w’«SyÄ+¦Õ”%ÚÜLQŒ\n‡„7³äƒxrzAÍ&p@C#ï\rÁ”9>@ÆŞ iĞÀÂx°f ˆ¹ |!Áù… ğ†|ïŒnN¬¨Û­vÂ®N(¦!ÄMFw\náá{|-x([Y5G©F”àšC™ú…AÂ80ï\nWøeÀ4»`È¡ì?\r!†€è€s@¼‡yƒ1wÈBÎÃ(n“àıC˜pâ sÁ\$6‡ÜdÈt‰‘8¸Ÿ)Pzÿ>€ç³ÈC¡è†:†ç0ºb’6YkÔ:9„TZ²YÑš±}Õ\\\\KæÊƒÈCt\r0àsÚ÷CäÒ)P¿ÃfDë=€æöãŞ|®oÃç5‰\0a•\0€1ÇéRClnøéL\$Ñ2JŠrI’ºc´™ Ô\n (*Ô0aWZÂ,©˜ Z\0(.M¥g\"ö¢‰2ìô8Å”à‡!œÙ…§Ìó“Ö{Oxe_áÂ‡Cä(o—4ï‡ztïŒóÈo.aEàPoÜ®¥@XQÈ£jŒ%j ª–¹Q˜¬ì«„„@ÓM„ûº%‘2M\\\nP\nl‡6bÜâj•[ª„\r(¶fd˜ŒÂ*d.V°\\@‰…*æêf×ÃzcI'xHuPeÈs“2ÈşŸ'lC©ñ—!˜9ğÛä”3…GòLÏÇ¿f§½@‡Áß*\$¢PµQÂğ\0Â¡·Eâ§@bd[g€hLŒ“acPÍl eØU\0R‹:È\\ÊF'WVR™ÁD21JÃóŒ›ìYØu„ÁÙY@ÆóŞ”¦\ròDCğÄapS\n!0jpd?ÁRŠ¼õşe-=}–ÒÚ{4”ië!|&³r¾Ì1!É@€8˜£<ÏO”0¸¥í…):ú(„\rFALq*ŠŒ¥uÎ”Aê>£q2«Åçâ»0È¹I°¤xÒ¢kX¦ÆÎ\"”æ¼Â,f' U{aŒX [Ro5o)ßë¹GA\rØÀVİØc\r`‚nCğí@m\r4–’Ä4†g£.¨F¿á´:ÇA8_`nT*`Z™†öY&—\\šˆj\\61¾Ÿ)#wX&`°Ì\\gzòNKÄµåº©Ü¢ïòA	Ò¥}…é–À³ ±º‡fæSñ¹¹Y\n!›ÔøÏr:ÅŒ€S?y’…ĞMÎ™Úˆ3%ƒ™‘xÅfâı|k6¡D8…á¥”š6\"ÁeNë¯iŠè•ª3!Ù¢\nø0ˆ\"ÉÙ]ä`L«»\rZ“\nšº\n§Ëæ¿Ãl)<‡á\0\0­ı\$ajÛ°â™|X Œk‹»fä®…’¢‡-…¤èò£¤Åş¥ÒZ¦0´’OtÙ§[4½7GV^œÚíICi³g§µÁ·×Eëb*\"ÂÇ7ß9QœíC²}RŒI®¼ÒÛig­ö";break;case"bs":$h="D0ˆ\r†‘Ìèe‚šLçS‘¸Ò?	EÃ34S6MÆ¨AÂt7ÁÍpˆtp@u9œ¦Ãx¸N0šÆV\"d7Æódpİ™ÀØˆÓLüAH¡a)Ì….€RL¦¸	ºp7Áæ£L¸X\nFC1 Ôl7AG‘„ôn7‚ç(UÂlŒ§¡ĞÂb•˜eÄ“Ñ´Ó>4‚Š¦Ó)Òy½ˆFYÁÛ\n,›Î¢A†f ¸-†“±¤Øe3™NwÓ|œáH„\r]øÅ§—Ì43®XÕİ£w³ÏA!“D‰–6eào7ÜY>9‚àqÃ\$ÑĞİiMÆpVÅtb¨q\$«Ù¤Ö\n%Üö‡LI6xi6ˆ\r(1¦;ˆĞ@7Œ\0Âä2Ê @¦ªúB©¨óD¬¤\nâ\\**h3àş!ÊÖ‚>ŠÃJ¼JØ¨¯Ê;.ˆã¼®Èjâ&²f)|0B8Ê7±ƒ¤[	› *²f!\"Ò80Úè9Ã¤köâÅâbr¢ª€P¡²ÀP¨¶J3F53ÑÀœ7²È*ü;J¡,\n1&# 5`Ô¿Mó\\Û2Gğ«A j\0¥Ò»¶ŒñÀÄÓ5£8ÈJË›˜˜Ç+j1ÇÖ1°ñÃ¿Œ« \"t&ô¯ÀP2%ğ:X;JË¢X=1\"€Şô®êâ#«ë%u2±T)jº²Œ¯Átë	×ÉA`Ôåş9³jÃ´Œ¶Tá^×è\r¡aÚV,'k¤	pæ0Õ‚@	¢ht)Š`PÈ2ÃhÚcÍô<‹¬õP.š‘Cd|Ì3A\0ÂÉÀc0ÌªÍÍB™UU0ËC{Z6£CÊN7ZƒÆÕc0ê6 CxÎ¢abú9cÃ\r¢¸£t°Ãpêƒ…˜R—	Ã+-;È †)ŠB3õTÒ½Ã²õŒ¸'a\0Ì¹£ª\\*¥#+Ò£@“-³£pÖÕêã’N9¨£8@ Œ™ÍC—c~@4»»Px€˜Ì„JhD'	ûèñ‡xÂ%Â°Ã\\ ­U;\0!ÖŒåÒû“iês\"Z	°:µ8æ;®p(Ê<K'zÛ9¿p@è:˜t…ã¿|]ä.c8^çxãÂ»îÁ9‡ÂHÚ84ì`Ü:q<\\ÕÇ£DÖåœÿ4U8«zésì¶¿ñæT:¦¯ò¬ƒ“Ò5³wÿ#¶#›\"CônÀ€;šfP`(rm&1†ÌèÈ(udL‘“2‚XÊÙ´6Eô4…Îc\0cuç8úpæK‹Ñ|' 0¥PœÔó6{ïÑû#r\0P	@£ B?J«P‡¤:ºPÜK‚v*Ğ¶¾FiM9©W…Ì:³hh	\n\r„°;™F€Ğœx¾;Ätœ“³p¬C+='9Ê>ZÛàl!ÉÅ†¨zN‰áˆ‡`˜ @}ˆù7§y6„yH¨ya!‘¼E€èäCr6Q\\É‡êj¤xfD§å¸<(9,fÄÙš·JjT)“('¸‚\0Â£‡á­S>µ°QÈs|åpÿ¿IfqH&dÖB‚Yia¸3¢\0Kv#®¸©ŸC?!ÌÉ›SÚ\"’ÅĞÂ“‰\nL(„Æ¬iˆA¥ !*CÂ4OÂ½‘ï’M#Ç\"˜¡¹Lè(“ôyÍ¡2áN}úaZÏ'óèÏ ¢›IK'8Ë§?ú6!º-¤Eàñ›Œ4mK-•—EÈ!&¤eÑK9ğ†ƒ`+rç¹LdZe&LSøëæ´‚4è|\$¢GÀ†hB F ág†æë#.N¾6È6‰Ö[¯—&n«F*VL¢xª¤Î«¹ö‚ø_|¸8«üÀ3òm‰Xu<¦Ÿ\0ŞéHğj~}‹L“O(I‰>),úLböşH\r€'Æm2Nšn)É]1Eÿ&ò•¸¡–Eù*…[(™Ãm\"èú¶wÚ8hìÉXvGÕİ­•€[Dñ\\Ëı-DóvóNÃÑŠ¸¢Ò#gçƒ¢Î3uÀŒ±Ä´¨È\n\nU…,„5K\"!";break;case"ca":$h="E9j˜€æe3NCğP”\\33AD“iÀŞs9šLFÃ(€Âd5MÇC	È@e6Æ“¡àÊr‰†´Òdš`gƒI¶hp—›L§9¡’Q*–K¤Ì5LŒ œÈS,¦W-—ˆ\rÆù<òe4&\"ÀPÀb2£a¸àr\n1e€£yÈÒg4›Œ&ÀQ:¸h4ˆ\rC„à ’M†¡’Xa‰› ç+âûÀàÄ\\>RñÊLK&ó®ÂvÖÄ±ØÓ3ĞñÃ©Âpt0Y\$lË1\"Pò ƒ„ådøé\$ŒÄš`o9>UÃ^yÅ==äÎ\n)ínÔ+OoŸŠ§M|°õ)àN°S†,ê,}†ÏtÒD¢£¨â\n2\rÃ\$4ì’ 9ªŠ²’¬I¤4«ë\nb!£îÒ†\nƒHàù„\nxØ¾cªJ4²ãhÄÊnxÂ’8ÌêÈKÌN	(ğÈã+Ğ2‹³ &?ŠüZ\"¹‹¬SÄƒœL»B Œ(8Ü<²HÜ4ŒcJhÅ Ê2a–oÄ4ÌZ‚0ÎĞèË´©@Ê¡9Á(ÈCËpÎÓÄõ\r0Ú¶¨^t8c(¥ì(š10ØƒÃzR6\rƒxÆ	ã’Œ½&FZ›MâÇ.Ì“29SÁ¤92“W ˜e”·M¸ P‚Œ¨«q]\$	»Èãs\\øìÓµcŠ„î1®µŠOYU­n\"“6\$ö4½fÏ¶…`2ÄÇZVÒäGL€\$Bhš\nb˜2xÚ6…âØÃŒ\"íT2ÕJ‹Å£d‚4m*Jã0ÌşM©ˆ¦—µÃ\$”âZtÛs² Ş®'Ò»òÉµ<Ä31A2¼2OÄ‚<£Ã8Â¼¸Ãuœš¯/ĞÊaJc‘\rnĞ@!ŠbœÈø2ÁD9/Hõ»c¤Ê8Ìº¬TNà(xìÚM7)hlÜ5 ºÚLù»Á\0‚2&Óhäüc¨ÊŒ“‰ ĞÊÁèD¨„Aö4½=ƒpÎã|˜¦s\n14m2º8\r7hé™.º«£[ËÃ[6äì`&Ê­ÂL8J£˜îºÎğ'0ÊjúÁpĞ:ƒ€æáxïá…Èşê—…Ë¨Î»^d\ní#0H_Áaò(úÎswÇAi¬€4\ró¸é’:+f¨–K7BëØAH£ªØ„°?JtT ±@§ üˆÚHèa\\ê´s^JC}-¸¡'pÂQe!•²Ö^Î 1º1 ”B„fİ¢nKÊMö˜t“e/ÆÌ˜CˆP	²„ïAVP \räYÍ0€@\n\nˆ)D(P‹%³|…ĞKq!œÙš³ZkÈ@eNğğ:T(VƒzÍ/jd;Å\$/Cr]/qYc4ânºÖÑšuªX–-8°ŠŒ¡5'dö°ÂEP` KAŒÊ'D`TIˆI\"!äÒD4îVÖjV|FìGà@IPr=\rÉº’²nš0cS\$\rÅwXl\\²'ğ¦2dgDøŒ@Î»L“Q\nQH:œ¤ˆañ\\/1ñì’4¾Q\\Ë=Ù\0´JÈJíUV@0É\naYÑµS\$}«\"î˜Q	… 0T‡)±;“Ø¨ùäŒ“2f ˜·b¤eÀ9Gæ-;àtRGõf¢9êû'qš:d¼’OÙèG#ôÍ\$åğ†’VaW±¡±l­¹WChz/gsü‘®°Œl‰GH!°É¤¸Õ©ÄS.¬¬†Ú0—	(-S\$Ä#Ej|Öl	g €*…@ŒAÂİZ9ÅOzEK­gfy\n\$E[QÆ©Ê¥'Ú˜h\n©Lß&V°ËÑ&L\$Åºz0W»a;J}7Ë3XlR1ç¨ıŸÙæv”Àab&W)–š‰5.1Ôd“³iQur¾jRèâmÒŒ¨‡|(µ@NíC‘e©9PÌUEµX„TÓ(Ÿ½W©Èµ‹«8¤”TÊ ¡â*%š¦I`r0²âR-Ö\\l«\"\n…¯\0ªj€€RH1àµğ0è";break;case"cs":$h="O8Œ'c!Ô~\n‹†faÌN2œ\ræC2i6á¦Q¸Âh90Ô'Hi¼êb7œ…À¢i„ği6È†æ´A;Í†Y¢„@v2›\r&³yÎHs“JGQª8%9¥e:L¦:e2ËèÇZt¬@\nFC1 Ôl7APèÉ4TÚØªùÍ¾j\nb¯dWeH€èa1M†³Ì¬«šN€¢´eŠ¾Å^/Jà‚-{ÂJâpßlPÌDÜÒle2bçcèu:F¯ø×\rÈbÊ»ŒP€Ã77šàLDn¯[?j1F¤»7ã÷»ó¶òI61T7r©¬Ù{‘FÁE3i„õ­¼Ç“^0òbbÊ*,ÔÛÀ:ôGHå:Ş¦Aˆ7mXÊ5„\n‚¦ªNJ´×««Á02 ô1Œ®{¤Ö?ƒ`æ5˜kèè<Èb‰¨æ6 PˆÖ¯»~â(p„4§£“Lñ¦¦)Jã(Ş6ÂƒÓŠc(ô\r±0ŠÔ¶#”4ÈCŠ\$±XŞ\n°Ò4;åæŞpÈ¨95úú8KÓDí'‹¸œÕ¼(“p5Å¡(ÈCöÏSäıĞÜúı®`PÂ7Kct2ÁpHRA‹İ05@Ö2@éĞÒ;<c*,0Ê\0P˜˜2\"×=Ã€æô¡kÊŒB}89¦±d;.\"Í@ÚÒ>L¹ZŒ5m,×@P„<'d\$Ct=ŒµÂŒÀM–66uo0ÖíjÖvå³m£V|Wh£W¬Œ.i[øbò¸	¢ht)Š`PÈ\r¡p¶9`ƒ»VÕî»[ƒ¤¤%(ÌğÌ3(Tr¦2ÍÅj2MÓ#.“5ÈÒím4#HÖ:kœ›Í³àÓ\0õ•â+ò5%µp@±Ã`Û9E’dÃ~PàgvS˜ÍåâVæ¹¾XşgYµ;gÖK¡DvÎaäiö“¥åYf˜\"øîfšê¹Æ°ØëYîmŸÏzÜØ3Htš¹ĞÖ¦)ÁpAc»IŞ°3%#k7“;®¸•ÖÛv¤Ó&ï ÄÒLĞìà2ì¦V•ˆ‰«	»¿èš„É½c®ªÖ1úÀxß¤(Ì„J€DM™	Z9\r\"€5‡xÂbãœÆ7ÚC´H9NfkâËñcµ¬L¼ëêV¾ùç/.oê*I0 m6–4Y§¯ìŒ<Wvw½øD4ƒ¥\\x/ğ*GhJ‰HgáŒ9‚ò²sÓ\rÀ½à‡0|Chp'i7!øó^{%B„L¦j`ì5¤`bd^Œ|JüÒ—JË€bbô’‚tœEI+UHa>U8øŠœ#Ç\01tò\r!Ú.İî9¦¦FÈiü\rN ¶ÇºEE	ÆgØ4ÄÑtEPi%eä½ÃcèÒ=-L‡ÅpÈúƒ¨p†Æ>6¢lp!>†±À0Ğ€H\n\0‚7ÇZ¯\\à(*\0¤•²\0ÒDƒRt.q>F(g8Ñpà è\\Ïºsø¨Æû\nƒ¤6Š¯z0šÈÆM¢th†)f6Ä}#„<¤¼Î7a+‹\"S“¢jâ‚ÓÖ{äp€¾3î‚’áÁˆÅ5œÁèDŒz¤ h«‘\$VJvP0³Rş‚\r{t(Ä†M²¶î•éü’«¡2B€`ÏP	áL*(_æ„N\rbƒ›õJ´Ò3¯à‰¨Ã^€ f\r!œ:œ1ç\rHr@áÑO® æJÂ±AD)…˜II9V\r¥ÌÇ¡B8G‰\"™¡´7Ëp@‚¤‹7äÂA9E±L¬d¥ÉRttEòxO‹0ß‡3Ê–‹õ›)Üß†*¨oƒY¯¤=I¢=SªáPªh°Ÿ›‚yV–ÚÎ:ª|ÑšV½V\nK,m,ÅºjÊ¢4‹†»–Ô˜aké®Vº¨Ë^RbñA\r‡ÀVwŠuk-;SˆQ­acúC!†°¡ n1H.˜ ×\$®	´:‡‡!8î–‹°\n¡P#ĞpJëD%5hØúõ_Úİ¸Rš³.\$÷^.=ˆ]\0‚åÜI6»n}†¯vHˆ\"(—¬SázÊ¸ã›3ÊÀPZµ&E2•¸ôi`z/ëG’ŒˆK#–¢ÁšBüˆ[£(-á½ºÓ ~ÌğdOûèfˆüôaGdí“jCğ‰\"·ù26´SÁk(¤ì»\nÛ&PaÙ?‡h¬Ô rİ*œ\rjË2Lq¦1\rj\$'×©‚\\\0PK#-mXŠŠ ÆG\nÁåLPzÂÎEæ †C\0 ¨`/:-¢`Ì( ïá¥7¨Œ8\0";break;case"da":$h="E9‡QÌÒk5™NCğP”\\33AAD³©¸ÜeAá\"©ÀØo0™#cI°\\\n&˜MpciÔÚ :IM’¤Js:0×#‘”ØsŒB„S™\nNF’™MÂ,¬Ó8…P£FY8€0Œ†cA¨Øn8‚†óh(Şr4™Í&ã	°I7éS	Š|l…IÊFS%¦o7l51Ór¥œ°‹È(‰6˜n7ˆôé13š/”)‰°@a:0˜ì\n•º]—ƒtœe²ëåæó8€Íg:`ğ¢	íöåh¸‚¶B\r¤gºĞ›°•ÀÛ)Ş0Å3Ëh\n!¦~Çkjv¥-3Še,Ã’k\$SøV¢‰G¤Òä˜)ÎNS:On&^ïn:#‚ş'%ÎxäÇ4{ˆÚ¦##°µ°8œ2ƒ´\"5¹«\$(´BbšÀëÛ|…-nã²ÿK`ì7\"czD³ÁÂcŠµÁÂ‚È¢ãsB­Q`œ<´-‚.†Œ\0Å  HK\"Èé¨æ\rC‹@PHá hˆ)§NàĞ;,ãšÈÍ'î›p¿ÃÃhÒÃ	Èñ8\"³6;‚(ZÈ¤ PŒ9JB¤ŞŸB²ø40ƒ3¦¥AÒZ™>3Ê:ÒTŠØ)ºFR’˜ê	@t&‰¡Ğ¦)C ^—‹fKWV(»BÎí¨ÈĞJCcò#z0´£xÌ3NŠ¤¾…<<dÚ°p¨7ÁQÀò\rÃ˜ê1Œo¨æ3¬ôxµab`9ZcÏL©kZ‡\"ÍÃpêš…˜R“Š£8ò67Í†)ŠB0Z±¡*XZ5ãtiIH­¸äçµ°ëŠó„V<±”è¨42I[l³£ˆ2â#%Ú…\\Cßj)!\0ĞŒÁèD¢AğÉ“´(xŒ!óïm\r9:æ¡Y£bt»48—ˆ¼/C#,BN”ªZ5ÅÃ,a\n)ˆ‹£ÂpA–eÙ†d42ã€æáxï·…ÉF9Ë8Î©[Àğšå8_™ağ’6£Í‚•gyëÊ9ëE *' Ï´#:tÒ¤M…²\\>~9 Ù:\n\rÂ\n5­¸Œ\"Œz1K®3©>.Œ#cÑé”ŒŒsxSŸ\"¼ñGl[Vå¼Ú]]Èè4&Ò\n0¹á\0ÆŞğãJÚû0ëš;e)òì¼ÈŠåIÂtË³a\0 \$\no·î½ˆP¢:ê`’òÅ3öñ.I8†ÉÈ»¶D¤ÁPÒ÷ÁrH®”9’2Á\0n\ræ„¶‘ğî_¢ö_ø#ÃŒ‘Cy!Íƒ¶èJ	Q,%Æ}:ÌOƒ¢*5½d‡0ÖõI¢}¦†sÈ{Ëy*ø2˜#ÔÙy¥<ŞPâO©¡ÇX—²æóK˜c#å%Á\0à¹–1)cA@'…0¨@•Ãö†„*C†Äˆ–¹B\r¦Œ3’'Ã£-.å	0RrNÉéì]%9µvNI8uPİ’sAK#¤|”–NàS\n!0–\"õ\0F\nA‘—šÜÁsŠl´ %’R‰8E\r¤] Z…	ª–RÒ5nˆ\n—a•É æh ù'QÉ	Ş*ç¸ˆJ”RÊ<‹á3ƒLĞ˜!ü†ÀVÏk\\R¸´,)ƒ‘}rs Ñ†IRÃJA¬tî¦sšPÒ¨TÀá†PÎšÁ}.3)\"˜¡Lc°i”ğ4¨Í%AŒa*T.`)Ğ–dˆÍœéàŒG“Tk\0PÃÆşbÛ‰\n/dÎFóz_:'6´¼ß‚Ôé8˜S”4OêwˆìEˆÄõ/¤œ¸'3–ã\nŒxJU£\niZ—Şè\n Æšª|F	êÖWF\n\$CxbÈLÓ¢%…bÚÌ0K#è°\"’¸aœo:ö_²ÙïG£Ú\n…ÕB)*é\$&2€";break;case"de":$h="S4›Œ‚”@s4˜ÍSü%ÌĞpQ ß\n6L†Sp€ìo‘'C)¤@f2š\r†s)Î0a–…À¢i„ği6˜M‚ddêb’\$RCIœäÃ[0ÓğcIÌè œÈS:–y7§a”ót\$Ğt™ˆCˆÈf4†ãÈ(Øe†‰ç*,t\n%ÉMĞb¡„Äe6[æ@¢”Âr¿šd†àQfa¯&7‹Ôªn9°Ô‡CÑ–g/ÑÁ¯* )aRA`€êm+G;æ=DYĞë:¦ÖQÌùÂK\n†c\n|j÷']ä²C‚ÿ‡ÄâÁ\\¾<,å:ô\rÙ¨U;IzÈd£¾g#‡7%ÿ_,äaäa#‡\\ç„ÎÂ1J*£nªªÅ.2:¨ºÏÛ8âP:®¦—\r	f-;¨ãL:;L(Üş3£’63 0²ù½bĞÂ•=j^ç pã\0<e ä	Ã+8éCX#Œ£xÛ.ƒ(&BXŠÙƒ|wD‹ˆ 0c˜ï)’XŞ3  T¯,Ëc”.ö»Ìdz:¡ŠFiô‡ bò’!,ë;¥ PÔ0KÜÂÁpHPÁ‹¶:¹bş6+CŠ¹DËÛÂ¨Èãr7?âz4¤ŒŞ³ª+H±‚(Zš#`+T,(èáŒÃ0 ÃC5NIBÅ!-3=UX¯Ée€1·¡tòØÃ\rZ9·c”…X+ıRè\$Bhš\nb˜°\r¡p¶5]ƒP»Q1mØë-3Qƒ6VnÚ´ƒ (ßZÒÛ>†Ë}#ˆÛ-7C²´·§ƒÌ7¥c`ŞÚd¾€…¢²9ÑˆX\\P\n8C}\$†4Z *‚& /â7\"ãBx€§#e†#Øz†bx®.ƒcS\0İãù=’dÁQ•8Ym÷—æ6&iŞ9ÂÙêf!ŠbŒ\nƒ}„çŒìğ¢äy,@ƒ6H0)\r£ª3TxŠöşZ¶]„™àì\0Èÿb:-2¯&b£D7\rv¦&êŞhÎ# Ú€¡hÆ!(@-\rh„JˆD‰GËxÂ3ìU63c Ã6:¶Kİ¥s[In{ğËÀ;\r\$V¢jóAlÇ-K™‚j¥‰÷ƒ‘ô0Ê3u\nøè8aĞ^ÿH\\skØäJC8^…ã(ğÅ¡	\0_Ôağ’CoHçÅª†çbìÈ\"ûn¥Aç#ä2FÍj3eç9•,SÍÈ '*4™…SâÇÃ¡‘väœÈP €-\ndµü™ˆ4¼Ö¡¼B¬8­rôãHY»hTZNËûRhà20Â¡Qfô³J‡ÁÑEBœR8 g\r…!RtÉ™ys‹9#Cñ¤ %8)¶e#¢<'P§8¼a<)±”éÆ…xGAAQ/X§‘ğäãØSc\$î¹`@”Z‘taÅ¼–0à—¡Mà§ÆĞŞtƒ\$S¹\n’Ç0ò˜He¡\$792s±T_dä›³Ö\"OŠWé“·DYÕB€€È—ğä†£s&MAâ Â®…±0a\\Ó@İXÂ¸ÇŞSÓVG¡ë™}©µeÂ6SÍxDŒ¢”Õ›¡³ Ğ®3“0 Â˜T¦]~2QƒyJdf¥Ã‡¨g‰å¤”°@İE¤Nó¿B\r‰¤š‰š3›BY Eœ:Ÿy`íH0k)¹K6\r@'y\n!2}¦òM\0F\n@Ğ³’2Â‘¡±y\$t›V¨“0€	r•EFœLáÑ¤¤l=’†Ñ—ªê©\"j^UÚ«5=9 ÀïTÚ–{æD3­¥ˆS0i4Ñ\$/÷¶‚³\r€¬%©˜\rÉ°0§b>Ñõ©FÉ\$`B+í	7”²¨TÀ´Z …¡±&k­'*»U	Ä—®%mgYÊf}ŸU.Ñ¦g¼Ã¼½£'T¾¨ö J	R×%DÀõŒ1@H\n/Ç™S jHÌ°=g\\ÄXSô×ìÖF‘&9\0ŒìÍŒèyXr¶KI˜M%(¾ÓóÖÒJ„·-ËØ‘\"ÁÔ%ˆ®‡4è›ŸÂrY÷á8Zƒ:­×™,ëÕ|\$ªúHpx´HU–Çÿ‚à*‹9y´c­©€-ëÂş™»ş±x ";break;case"el":$h="ÎJ³•ìô=ÎZˆ &rÍœ¿g¡Yè{=;	EÃ30€æ\ng%!åè‚F¯’3–,åÌ™i”¬`Ìôd’L½•I¥s…«9e'…A×ó¨›='‡‹¤\nH|™xÎVÃeH56Ï@TĞ‘:ºhÎ§Ïg;B¥=\\EPTD\r‘d‡.g2©MF2AÙV2iì¢q+–‰Nd*S:™d™[h÷Ú²ÒG%ˆÖÊÊ..YJ¥#!˜Ğj62Ö>h\n¬QQ34dÎ%Y_Èìı\\RkÉ_®šU¬[\n•ÉOWÕx¤:ñXÈ +˜\\­g´©+¶[JæŞyó\"Šİô‚Eb“w1uXK;rÒÊàh›ÔŞs3ŠD6%ü±œ®…ï`şY”J¶F((zlÜ¦&sÒÂ’/¡œ´•Ğ2®‰/%ºA¶[ï7°œ[¤ÏJXë¦	ÃÄ‘®KÚº‘¸mëŠ•!iBdABpT20Œ:º%±#š†ºq\\¾5)ªÂ”¢*@I¡‰âªÀ\$Ğ¤·‘¬6ï>Îr¸™Ï¼gfyª/.JŒ®?Š@PEˆ¢WK¤rC«…º¹)ï”¹/ª£ö§Jª\"½\0*®b×§¥ÒªÊ;\nšÖÁ0¬:Ø·1Š\"¬²ŒTHÂ“JD†±©fy%³)2ª°‘¢‹’Ó: I.²ÅP[¥1to&KÒ»¼˜%o<Ó¤(e­¨|¶Ş½‹àä\$Ú=*ñ‚\0à™ÑJ¸ãZÅ¤š¬oiœÙÔv…LM:õÖÚE<”±ÍìÌgŠúq:Ci5ÔFİŠ½ŞN¬Ñ2z‡9óQŸ,ºA(ÈCÈè286…¨ü\n\\µ\rjŠ¨^xØc	À¥³ïR¡p“\$ª¨¥®ä^«õJ5µMŒ€.¶¼H•9L’ä]ÀQğ\"…£hÂ4‘Œş2ä™×UŠœVŠz¨¯úR*‚¨Räµ‡º.6O×Õ¯hÈ]†àû¤l·vÏ h”Â’)Iœ¿5\$IœT‡FuœMğ)(V…¸^²—x¶‘§¼ˆ»«9úÌ_<‰rFÇ>ººb\rƒ äNª(ëˆBù_¿è´A|'nJîØ1,Îª}Mµ­\"/`ó_R¦¬§pŞ½ª¬ºÅ¢%-ï*×R{p¯>*¥);>Z©ßöPŠ,sO«KzQÍªº'c¯Õ0I6ÒÔ¿û_‡ôxÛ¯³IT‹ç+L‹,uKÕ ¯\\´½“znòù]Èmï¾ùå8oõ@ûZóÎPæ,¨b\$|QºEH\$¤–„0¦‚0.^Ç†%Ôö©HP†&…q§’h&JEb>…Q©¼Ğ…ÏKæ,)ç¬BÜ‹ŠPT\r„7°æ0o@€:‡0Òƒ8 !6ÅpÊŸ\0c\ráº+H¾àa 9PÌA‚@ø2F0Â\"¸g€¼0ƒã\n%Ñ='¤¼Û2¢ı‘Kàdé]´Û `SØYéíóÁC\n\" ÉöE\$˜².E>Íˆ‚ƒ\\‡±sC1\n£Lk±¼€è€s@¼‡ylƒ\\‹ÁÈE ÎÃ(n˜á…Æ@ÓÁ|q`ùœ\$¤V©rT‡î=ÇÔ‡J>®rÄò¤‰Oq{e\$Ù\nÇ›—‘öN¦PÃ¸VNÌáufé&óˆT¦öy*l!Å&ÖÊ©øfÇYyŠáÌÍàD ÊcŒvÙ~(‡ ÚX8aÌ.)‡0êÃa˜:¶@ØÃ<W¢¡¤:€AMhxs4|8Ûƒtx!°9˜Sfm^‘&8ÁÖ3ò¢‰™%e=[\"Äş€T\n5ô\0ƒ§&l-™Œö€ñ‘¬:œ¢jÈP	@ƒ\n\\Ñà)ı¼lZNáy^‚Ü¥8Æƒ-4Ü7‚\0àƒHvi”3Ò\rƒ¤O\r1<7ğéDe/ô€ß•D³#¦É\$Ä‰³è\"dH=J¦R™#¸E»%O…D¸­‹Ya[\"i!OknšO²«\"¢4…TÃzÛÁ&M\$İ—I‹)Ãšo²¢²K‘û…¨²JÓX€LD“xĞÖ«KÁ¥Õ€ŒÉCJÚV{©?– ûÍwÖOZ(1ÂÆv &èdë‹Àw(½…\0Â¤*^œê_\$8§…	*éÂŞ–\"æîƒçºd%R 2\nxÈ9è¾¥|\\#˜”a ^'ÎİŞ#ËhŸ¡='ê «ÉLŸù“kÆ Ê–ğ@ÂˆL¶ePD‚\0Œ+d®¶‰”º¼OˆqúuW•@'4í+¸Äô?º¶\0•Øõ®ÅÑ€Ôä²­\ræ]\$y}0GVÕE¡e§4ŒÕs*X>ğ`ì³ğQó¥yå·¾Zº† »Íhó6ŸwåRÊ„\$zã!âJÃ´^yÌ'rS¦sí^\\ÈCtA°†0ØCk¾éòp(Ë†‰sÉšÈ[¢*|Ğ¬Ò?'‘F•côIB’z…Aj#œ`ˆçŠ¿”‡Û, ¢Pİ—¨U\nƒ‚”	=O;Xœş…«ãl¾úGb(<û·‹vtGbôÂùÈc+¢9%ğô™Z’M6ã–>¨úâ¤›šÔ!\\š:.ñL…Ô\"L\$©L@ÈëA\$ğáOu°^QqÓ\"›AzOuƒ²kÛD-:ÖN*¬x¹È\$ÚÅk>õÏÆ®«ëYÕ£‰\0¦†e´­š´mÉ4æn‡Bë®Ühz'9\"¼ñ/<ÒYÃ	ùx	Ší>óü]8ÈÎ&­Iej¬EH<‡x-<Œí\"EfâÛS»‰?7-\\óYÊg®\$N	´1†ç<ö4&q“Ò·}KJ²:»l¡÷Gî=ì‘R\$ô÷òkZ:zzHŞÚ(ÑÑÛí¼êë„³í‘3SçÌB€";break;case"es":$h="Â_‘NgF„@s2™Î§#xü%ÌĞpQ8Ş 2œÄyÌÒb6D“lpät0œ£Á¤Æh4âàQY(6˜Xk¹¶\nx’EÌ’)tÂe	Nd)¤\nˆr—Ìbæè¹–2Í\0¡€Äd3\rFÃqÀän4›¡U@Q¼äi3ÚL&È­V®t2›„‰„ç4&›Ì†“1¤Ç)Lç(N\"-»ŞDËŒMçQ Âv‘U#vó±¦BgŒŞâçSÃx½Ì#WÉĞu”ë@­¾æR <ˆfóqÒÓ¸•prƒqß¼än£3t\"O¿B7›À(§Ÿ´™æ¦É%ËvIÁ›ç ¢©ÏP·Ùûp°@u„}ÍÆ@6/Ì‚ğê.#R¥)¯ÊŠ©8â¬4«	 †0¨oØ*\r(â4¡°«Cœ\$É[î9¹**a—ChÊËB0Ê—¿Ğ· P„óDÂ“”Ş¯PÊ:F[‰‚P9¦®ZÜöD‰LL!¸’ü2§r Ş¸Îƒ|•8n(å)Ê¨¨ê2¨ñÓ+ 9á(ÈCÊğÍSdÜŒCÌè¶¨^sØbJk4˜eœ²ç9©‰ã”*ÄH£hÒîˆ#ÇBPÀSË1*rÓB ÊÄÄ+ ŒƒPëSI(ÜÒ±ˆËO¥‹Í`çÓ„×YŒ-25[\r0¢:š1œ¾	@t&‰¡Ğ¦)C È£h^-Œ6ÈÂ.B´ Ü<ƒÓ.ÀcKœ\r’2Í¥¬`@7ŒÃ3È7+.ôVÒ¹ÍØ‡ËéRP²È¨7§£ÜàÕq%,Œc0ê–\r’ºÒ9¾Ò‚0Œõêô´„\$7«ÀP9…)¦5²B¦)Î ì‚¼•À@+Lc`ìşAÕ}‚•b0Û^¿£*X3-Ãn'&w¶C>K•Ã\n?’è@a•*\0é¤ŠŒğÜ5£Z\"Tü<!QÅo´º(iPxş\r`Ì„JPDQ\r{Âã|š\nhÍ0½0ã ëÀŒ/´Bù¦NHæ1­ãù8PA „É¤‰«òğ•ğæ;­ÕØğ8\r,`È[;·n@è:˜t…ã¿lB› ä-Ã8^¼÷ãÄ8~â9‡ÂJJÅN.ñ½c¨ÔT:Fs^®'9BF:#qZsz8©¥D12HÊP•3ÃMIêÔ\0_ğ-èÌk* Æã8\0îÓ%Ÿ'ºšò*Ma„39²àÃaÌA‰§öõ\rŠŠ(¨á:c¸°Ca˜1æxà•g†ßÈ©áUgğÿ>ô^^#ó@\$É1(5Äh RR!0aÌ‚@½T\nô~í…ë‡HiˆIÊ#e¸:¤ åC{Ü.lD;œ¢h{Q) ¼‹†7\$HğaaaÌ‹–“†Œc÷%4<@“kûQ`Ç×&²|(J9r†’´\0à÷”šæXp¬ø¸ƒâC«Í6 ’&WdoŠp„ÓcÌ`qLdùÀÚØƒ#º‚.’'©Üé¨&@'…0¨M|\$W„yaÁŠ¨GN=tDN	Ñ<]ä¶DÂD€ë¥Eç›Ç³ˆaHR_Rìè“C4g9z5ŒE\nÔØ\\\0S\n!1¦’@¬\0F\nº\\8úIŞÜ=D{¢²h™’1%g”ı«€>±=0FÃ,)îBgÈo)GPëC&’¬aÜ\"ğeĞetJÕ#TR5!ô\$`+‹Æ†%ÜĞ§9s>.u/‘ƒ–JX‰4Ñ!ì‡¸ÿ˜àU\nƒ‚0ƒ-DÑ8™J;¡¨´\"€ÄéßC ÑUDtÎbHT³ò7ó Ú#S`Ã0yZ\$f¨)?Úb~IĞ6Ğ3\naÍØU8Ç’¢\"ò€+©ş9JjRÃøc˜i\\Ir	¾¥†‚`o›µµwâQb,UB<A@‚¥„jŠIA­‘tËS†òÑúiMu€Ñ{HšC}«à2(\"~ÍØEtq0Ä<ª’nÂø®Á««òõWR@T%ö´&tXiy*jè|";break;case"et":$h="K0œÄóa”È 5šMÆC)°~\n‹†faÌF0šM†‘\ry9›&!¤Û\n2ˆIIÙ†µ“cf±p(ša5œæ3#t¤ÍœÎ§S‘Ö%9¦±ˆÔpË‚šN‡S\$ÔX\nFC1 Ôl7AGHñ Ò\n7œ&xTŒØ\n*LPÚ| ¨Ôê³jÂ\n)šNfS™Òÿ9àÍf\\U}:¤“RÉ¼ê 4NÒ“q¾Uj;FŒ¦| €é:œ/ÇIIÒÍÃ ³RœË7…Ãí°˜a¨Ã½a©˜±¶†t“áp­Æ÷Aßš¸'#<{ËĞ›Œà¢]§†îa½È	×ÀP™MĞ.òÊt¼FL°¾öìAH¥Ğ7§SüÊœ°M`ÊµI¨¨ÿ°£HÈò(L3|²ˆğÅBpê6ŒKR‚ƒ;ŠààŒ£³œ„!©ÂÑBÚ0@P¬—CX@'£ î´aH#Œ£xÚñ‹Rş&@0 ¤ƒ'\r<Z€A£|7\$Ë;Ğ9\rè &\rëb*Á0`P”à·²¡¨d®Á7èHä5¨‚şª@HK7£#¢Î<°€S:°\\“øb	ã¢t2C@Êø%h+\0´ÀKP(\r#Hä¿Àí ì£C¬\"…©[”%Ä¨héIŒ©”Ô³¦ãr6©#r’Qj¢K\rC¨tû¯cr<Ab@	¢ht)Š`Rª6…ÂØóg\"ì\\‘·Œ`ÉÂƒd‚2¬¼X”ã0Ì§­¤ÍÅ²Ä´—ƒc|¸J®+<„R„1Œl æ3£bñ,¡C˜XÓW Â3£/Z^8¨SÌ2…˜RÚ\rğ˜Ú0ªa\0†)ŠB68=/]E;0Í @;-#mê:·cJkİ­ğÙ€:ÉJ^7'¬*3#rrËMê;A\0ƒk¥–1ãrt†A\0xßÊ3¡Ã&›Œ!C8xŒ!õÌÄ2«×¦„8Îõ*˜şC­™2‡–ËlHÊš¦ì,• #œn9.ÃÀáK Áv¥ªjÚÀD4ƒ à9‡Ax^;órIšArÒ3…ïŸB<5švÒ7úĞæ	#hà‚Çãpé±ì¯ºµÃzÓŞCZR©%P.ö[:uµA^4’ }ƒşc\n¦x”Œ#í¶èÕü¹ø#–‚9Gè8Â35šï|±7åı™­_²×4ÔR*0Çá\0Ç«!Ò™i¨ŠT\nyz>!¤ù†C¼`:0D¨–Ñxä\r4ê-èp „€§ÔÂ£2ÄÀPCi¤õë6Lgƒ 4Fƒ’°ä¡3§À™30îâTùı\re¥«µc–³áo„à1rxKN\nîV´9\"\"<AL1zqG 6¢*PÚ±`ä¤ü\0 ’EÉ–”ƒÃ2+_ŒjÁÅ–AĞÌ•SGsÄù¿ff`ãIdš…\0Â -ˆ*,”±w @ÏYkj+”H¦Ü×bŠ‹LšA¼’\\L\$lY/Ì˜¤ ĞiƒVĞÊF Æc«¯\rï0ß¡0Î˜Q	\0Ñ2wö0Tƒ+Zºå!O„oGÑ& 2jwYÑ†f¤tZ³ƒvÎÀPUe‘je³vrTßéÜW–N¶ÂL\\Œ:H›õsU²jMˆ)]§6^pNœØ§ä †4C`+\rdÌ0ØACaK–§½ÆB F à6˜INÑ5u°¸’§fZ‹ò.D4\"72Ã)ıšÓ<‹×tC0yF-¢d©Âš=0h,ãBşÌ'Š¯8³ĞÁOc“iÙæ9+„“T°óii%&­!Êº vÂ`eQA®'³BLN¸T80™«b¦ìzN`‚ŒrèõàKfm5O“š(Yè™i\$ªCÈvÛÛiGçô€ áFËxRAAÔ¾SbKÃI	";break;case"fa":$h="ÙB¶ğÂ™²†6Pí…›aTÛF6í„ø(J.™„0SeØSÄ›aQ\n’ª\$6ÔMa+XÄ!(A²„„¡¢Ètí^.§2•[\"S¶•-…\\J§ƒÒ)Cfh§›!(iª2o	D6›\n¾sRXÄ¨\0Sm`Û˜¬›k6ÚÑ¶µm­›kvÚá¶¹6Ò	¼C!ZáQ˜dJÉŠ°X¬‘+<NCiWÇQ»Mb\"´ÀÄí*Ì5o#™dìv\\¬Â%ZAôüö#—°g+­…¥>m±c‘ùƒ[—ŸPõvræsö\r¦ZUÍÄs³½/ÒêH´r–Âæ%†)˜NÆ“qŸGXU°+)6\r‡*«’>n?a ¥&IYd„—ÈcC1È[fâÁê„U6©	Pœ¶H*|¡jÚ®¬¡\$+TÉ¬ÉZU9KIh‡*°sƒ²i	r)MrTX¿3,×¡É‚vW<*¢	41\"Èˆ0ÍâL¥?Ä:¢‰–oñÄèR@Í7Lóx–¤hì¨±¢–Ë¾©‹&»¦ò¤Ìœq7DŒÒG\$±ÚB°%vıL.	^Ÿ\"Ã#É-@HKA>´#“Í\$;æ»@PH…¡ gJ†¬còÉêXÆ¬iN +L)Æ¬ÂR\n;ú²Ïl	rëÊmºÂpªLÁ1:›Æ°¡cÀeJ:š-2¬şÑÎºÂ[2l²_&Ë\r}+Tmr¨”|¯±´Â'RCQòqL–Û‰§ì<˜CA`P\$Bhš\nb˜-8ò.…£hÚŒƒ%q°p´ê•Ù€Pä:\rƒd’”J³ÿu!YA·KK9¬1Ãka¬-\$à²Û\rL¿gcDRî6=Z‘cô<ê&ÄPó‘’âI‰[{HrB#såpjº³Ìyflşj£g\nşYî?è-Jıh©zi\$fD!ŠbŒƒxÖ2Ú‚óTêtL‡ÙÚÂ•2ùú\\¦WEDÄ:–xˆ•I.â•‰-á‘\nƒ@Â7\rc˜@3\rã@:o`Î# Úö£XcxİĞŒH@!\0Ğ9£0z(a|2uÃéÑxÂ+pdWkÁ2Ô%6êãô…ãïú«ïÔLæéÆ.,JØ»+Sa 9³È{HËxgéO¾ää8’³½ŸkÛ÷=ØĞ:ƒ€æx/ğLê.s¡œ·H£tt.¸»Àæ–™;?	ŠñÓŞgèQ±“€ıJ’w@«l©²%¦’ˆyp’T^YPiPÛ4Ø·¡¢\$KÅÊ†P@CÃuÀ€;†Ø\0bˆÁÁÔ¹ÀäC*‚!š¹àæCc¡Ì9†`ë`oç²&è€hˆÁÌ0Å‡Xîˆnxa„6!Õx‚ËnÄ¼&U<€ÈB5:Éö+òhU\$\n-iá@\$	Ü3'L}s€PPÁK‰e)Ñ¤ W\0oy@n¸7X’è›€oÀ9“Ö(g‹2ÍÎ‡G4\\Ğn\ráÒ#ÄğŞâÉÊFHöS´4¢ƒc.0’f3”«‰5)­\r\n#¦ LÊËN3È„àÈÖŒö‘šæo¯Y\0”7èqŒ\$7fõ² ’FƒÈo €2•0¦ snŠ\n7Ì\0@îˆuŒs3 Ş]\$•®z7·GYhq ÁÀ0Ë©®d“ê;Y†Ñd®ILqE‘-6½¬:BÄ	B5*.z,Ä6YÆ:ä}’IÜ„y\$ƒ-¥–’N²á©\\>‰jš§lR!@§è°¡*n‡Êñ8\naD&RÒlŸaDæ4é™ª`©%Ò \"u,Ô=QÕÙ\$+~5\nª½ä0Ú\\:zq„¬äŸØ¯H±>£s&Í©-V“‹À SûŸ*	b°ÅX\$÷_\$\$)êÆ.†1eÓ\r™%snÉœ¤nÓÒ9Úg‘¡²ƒRˆ©á0uĞ9ÀVÃ`ia­1CF–‡“™q§íq86B F ák~·‰åc2)‘ï–«=tJsİ3”‘óøŞ‰:kBL0•¡tücÉÅ¾?*Ô¡¸—ŠèI&tú7¸‹¯iÛ3çâ«RRÍª©–²Ô>ôüŠéq6ÄÜ7³ôUĞm½“vf¥ÊØ™£ê%g˜¸ÒLwë&lLŞ Vœ,ŒXØ1\$ĞVœûÉÌ1\nME³IeM1#ºøå“^4(ãï3ìQÄ:mÃğ‰Ù9+eUÖíª“}ÎH5h‘5öôcUy7SYenİ…‡~05«F„\\€";break;case"fi":$h="O6N†³x€ìa9L#ğP”\\33`¢¡¤Êd7œÎ†ó€ÊiƒÍ&Hé°Ã\$:GNaØÊl4›eğp(¦u:œ&è”²`t:DH´b4o‚Aùà”æBšÅbñ˜Üv?Kš…€¡€Äd3\rFÃqÀät<š\rL5 *Xk:œ§+dìÊnd“©°êj0ÍI§ZA¬Âa\r';e²ó K­jI©Nw}“G¤ø\r,Òk2h«©ØÓ@Æ©(vÃ¥²†a¾p1IõÜİˆ*mMÛqzaÇM¸C^ÂmÅÊv†Èî;¾˜cšã„å‡ƒòù¦èğP‘F±¸´ÀK¶u¶ßB“Õ®5å3±8[&0š¶ÇSYÏ’ÙªJ26¥§ŒàÊ…c›f&®n(ÒøÏ“Îôµ#&-ÈàÓBpê™P Ò½#›~,û!'mJtî/´‚B8Ê7¦C¢tÄ	ƒª:%ğŠ24ãzu	½Ğ¬.˜)²X0ÁM»Î4L\0ê2 P–6Iàà<cË\\5Ê’²‚î.ì@ª:¿Ê, Œ¨hÈ×Ã¨ÎiLêÄ\rcË˜Ç#àPH…Á gB†2ĞÃ8°ÌüÈ–9BcòÓÈ)?-ÂD™\rĞØÉ8ŠÉcNê¦ÁhàªI¢bØ4Gëc&#MXÄ4 Ê15ZÇ¨•V5˜eµN\r-0Ëb5Ãeu (È];×Å Ñ`XUEd5VU¤Göp7=RĞ	¢ht)Š`Pà8Î(\\-×ˆì.U\n~ê„	C6G@R\0)c0Ì˜;jˆ“A-˜ –%Ø\$§*¶À@¾£5 Ò0§ƒj˜”£Ã‚Ø¦\rÎHê74Xãüé-ƒtÉˆ4Pû<§KB4¶’³©Å>ñc¬æädè¸/Êeg±X¾3ªØñIdY#F‰1mNU2¶\n‘0æ3×šæãNs+ØéN{<p¢'š&K£Ş²¥X™®Éà†)ŠB0Zƒ¼QU¶õ„dp0´ÈæVãrj*ğÃW3eÑ„	£C¨Ò¨ÃúÒ6&ÿ\rÃ8@ ŒšçšŒjcıOA\0xÊ3¡œÈÃo¶áà^0‡É¨“ÌĞ7UNFÑlÄ®¡gIŸÁJ\0äÁXF0«3lêîÔ¤)Œ¡p˜AÚöıÈĞ ˜t…ã¿ì\$ıPäHÃ8^]ßøxH¸ÿÀ^îƒ˜>\n‡ÈĞ’—„ñ¨s¨ä7¤B\$]Êe4\rn\r&Ô¾˜C±l?®l<tÎFÊ\nd@…a=C¦)ù”\"Š±2ˆS\0wlA° Êrt\$\$‹¢€Ìöˆuac‡0Ì_\\(oçÒ@ĞPá¢\n&@€1»e>òX¸ldÔ½8S~Úl9A4Óµb†Š	ÿ@\$ïÏÜ#<@€àRfŠÄ?X	%VŠCL‚Ka5l€2Ãbvép2-ˆ2†xŠ©ÃtDÆ,!èŠ`oä\\š…fä­Îa‹`M¬²åˆTÃ¡5	eÜ bx÷IùA#Ì„6†Òêåô/.†©?uºàZP ìp„b9I¨J3„O­¥X…WŸ=s òòV§u%Ø&jËéú5b’näêMZK=ä\0Â -_S¶L7\0%±¶5ËèŸ6m2™³<§3b˜ƒHg'\$Š0B¦QØL¥\$ST“Ğ˜’N`ÀS\n!1ƒR©0T\n\0é¶µ–8oŠ#§V[«d¼Ã	y1%ä¤¨¦ô}\nÌ(„î*~Ó–,Æ–µŠSàËCN‰¬¨n8ó†ğĞ‘É mY8,w®B]V«ÅŸ&êŒ­Î©w‘Hè6³nüZÍ\nS8á6¶ÌV<\$@4!I¼VÈU\nèA3QŸBrN7Ÿ%jã“ºs’fº˜Õ2¢lóà|²èƒ“ó`Íˆ%…Ü2·ÂˆMƒ«ïVáĞ3\naÒAï5V¸â˜sîNĞq®8l”Ã×„ÚzÜ%6jI³8V«×Í	G”u³ƒ,@Q¦§MÖª”¢‚älT“²6.¤˜åìiÍçpÅ2ØJih“B¡„Œ\n*ĞC^lOù´6*^î–Û@ÇÊë\r%°";break;case"fr":$h="ÃE§1iØŞu9ˆfS‘ĞÂi7\n¢‘\0ü%ÌÂ˜(’m8Îg3IˆØeæ™¾IÄcIŒĞi†DÃ‚i6L¦Ä°Ã22@æsY¼2:JeS™\ntL”M&Óƒ‚  ˆPs±†LeCˆÈf4†ãÈ(ìi¤‚¥Æ“<B\n LgSt¢gMæCLÒ7Øj“–?ƒ7Y3™ÔÙ:NŠĞxI¸Na;OB†'„™,f“¤&Bu®›L§K¡†  õØ^ó\rf“Îˆ¦ì­ôç½9¹g!uz¢c7›‘¬Ã'Œíöz\\Î®îÁ‘Éåk§ÚnñóM<ü®ëµÒ3Œ0¾ŒğÜ3» Pªí›+£ª€“µc¬	+£`NÂ%\nJ< LˆÒì¡*¢®¬©Šâ¼¢¹ë@!	†W0¨è¨<\nT >c\nÜBpŞ6ŒLª:\"FÉCÌ4A,¨!/ÃL|\nLàÊ0 PÉÇlšÄœ'oš˜dKÁ\"ÀPxç ÇR¢¤µpæ2°tˆí1©¬m\r¾é7»jÔæìµˆb†Â»Sdt:µ#`@ÉŒ›ì:\"ã @7Œhè„´u!I#¨8Ò\rÏH…á gP†2`Ê›!•C¢Ó%‰\n_G˜eÈ*Jä²6¶“`ƒHÎ€P§DTïÜÂº\"‡ZŒ£`\"M‚˜ê57`P 4˜eB„¸K\nñ ÃËJ„\rÒF0ŒéB{+2jğİn²au)G\\SÊ„»*ĞTİWaR9§J´7/¸\$Bhš\nb˜5\rAxÚ6…âØÃ‰Œ\"ë\0ü©‰ƒ`”»#s'm³î‹F„PåT¶\r	s^¾0©Ğ¦†51ÀˆÉ»Ê¢¼U Ş8*HV;›ÎˆNi”¸øæ+×¼Õ·á;BIãªS€Ã·ƒ¸]ÙÖy4f¬.pÈĞåJlƒèÚB1ét;§µ2†§°\\Àİ iĞ¨7c+2!ŠbŒó¬°*’êÁR‚Õ3[Õ»+)pÚÏ!ìªtÀñ(4”5œR7\rhHÌ—B 3„É¹!šFj’)*èx¨Ì„J DÌæÿ‡xÂeÛ<éV?¤f0_ƒd’„ï½O1›¶j0ıÂ7ÌéCZ‘ÜŞ'ŒTº½~QÙvƒCx8aĞ^ÿh\\0ôè‚r—á~õûCÒHì…ı¨æˆøpQ\"¥Eš§zïÉ‰=\r©Y”¦‚¨	43) ™PÜlÒ~`/@Ñ —”jÏÙÛ5(D¥&²FĞ[²D‚\nñ>tN|9¥H]3¡k‡0Ìg‰ˆo/¦f'r}\n“º!FT¤§¿“ùCIğ°–5rÑ‚B…I¡—öØ€H\np:‡\\0 PŠb¾c™!\rÄ”ä¢v~¯ù>\ræ±Z—rN¹Ttg!'Í§=ˆ PCxw¡‘É¦ç œC{3-\r²\n”LV\$*<2«„¢ÀêYéî\$<ç’“&öÌ“àÁ\0êà)]CÊø4‚˜iM9©5om|†ğòºN)q!Å¢²d*C‚\n!.™Ô&QÙQ3OÎ\"`½¥È‰‚#!@'…0¨O‹<áÉ€¡cª“EHd™N 6Ò~áâãXq¤ƒÊé`ô'a=p)ÀğÈÕ K>\rÄÑ\0NE„ÏcP4FÖGC`o~ÀÂ	ÆHŠY[&L(„ÄÒWB0TŒg™ı ©„„•i.‡%JdÈBdšÃ“1:„ì¨…ô ƒaàÎÆekß4¦Ğœ.qSNŞ¹Œ&‹¶¡\n˜¢à}F©BT™º‚¸*‹§+Ş¤§Ú»Û\rOzĞíS¥ÒL]^_dè!– Ø\nÃ˜ Î•Ø·>ÍÅ\r¯3¶®˜êfVò!)Ìp4ššÊ0Ù4›oG`2ÅÔ¨TÀ´¥ÀC9…«utÒ’Ç×u—e…)zÖeñg©Ûu#Ä¶–v{*I<,fÈFwE€bªRrX@*Æ¶#f¸Ï¸RY2HÖ‹rcĞ4Mf¬™ VôÆN\$0W£sZkñ\n7s\n¼S4PC’å=a±’&‡s<ä±»÷„2¨µ¥5íákU#Nƒ:gÑ²ê;k}GYƒX/òÛWõ9`§F=%@s(ˆ¬H£Ì|¥F>á*¥TÛÍyJ0æÁYÛ`™hÈèV(óğÖ•Ûz~p";break;case"gl":$h="E9jÌÊg:œãğP”\\33AADãy¸@ÃTˆó™¤Äl2ˆ\r&ØÙÈèa9\râ1¤Æh2šaBàQ<A'6˜XkY¶x‘ÊÌ’l¾c\nNFÓIĞÒd•Æ1\0”æBšM¨³	”¬İh,Ğ@\nFC1 Ôl7AF#‚º\n7œ4uÖ&e7B\rÆƒŞb7˜f„S%6P\n\$› ×£•ÿÃ]EFS™ÔÙ'¨M\"‘c¦r5z;däjQ…0˜Î‡[©¤õ(°Àp°% Â\n#Ê˜ş	Ë‡)ƒA`çY•‡'7T8N6âBiÉR¹°hGcKÀáz&ğQ\nòrÇ“;ùTç(^e†·ÈëÉ:àğ¼3„ğÒ²CI†Y²J¨æ¬¥‰r¸¤*Ä4¬‰ †0¨mø¨4£oê†–Ê{Z‰[îê\r/ œÌ\rªR8ƒ\nN°„BòßˆNÂQBÊ¡BÀÊ7Å# äa•­ûÔİ`S¦¯ Sİ<‚+!(êú6RÜ2¶O‚”œËc”h¬¸ĞDÃ{ê°¤HÜ:<(ÈCËJÎS¤ìÎHè¯ÀPH…á gB†/+î1±èª¨ƒA©«ŒÀ=2\"˜ïˆ#Ç@€P¦2¤ÔJ¾²¢*rÕEƒ( ³³{È ŒƒÃó8I\"(ÜÔ±óÈËS‹\rn4Âƒx]<NuØÂÕ?¬p¨@àPÂ3Æ`P\$Bhš\nb˜2xÚ6…âØÃqŒ\"í=PK”Ú*Ü8u Ï²­nÓL²ÚĞ4øäÔª¨¡.0C9NP\"%ŒØ/ë¼ÀVŠšCzÁ÷ÀX‘Mø`Â¡\$*üÖÀ*³š˜7³€ƒ'8E™Š@j©ˆ^ğ&'…Íø¾cI^9<ãùV*\rã^8!ŠbŠÈÙÁm¢•©\n©š¥PÔ\\Öª	bò6³Ñ½„_éxŞû¤,*ß\"ÀŒ(‚h*0ƒpÖşŒÏ´¬3„†>¥bwª:¡¥!ã\n43c0z*Á|¨/ªsã}	Bƒ¬Ê:„‰#OÌú>MŒÄáæy¬zÊ’²êÓ»ØáÓÑƒ'%¯é;ôcÀğc@è:˜t…ã¿|B›¸ä/#8^Àxïû\0¢¡9‡ÂJI6âÜg•	ñS8jª³\\ô>sB¤©o²ÕoîÇ‚CC(Å3‰:RÂMSşmi‡*ÙTa\0îj•ëï|Ä¥£·*)ÈrIˆ9‡0Ìg!çaÿĞĞMßÑïE)Í”4Ş°bÍ^H\0ÿ È§Bqë?&©¥XJòß` \n (PŞIM.X›“xTĞ'dx!\"*˜\\StN Ô’\0Ï¡Ã\n?¥|:9PØˆCº;BPtÀ)µAB#kE„µöÌ›ÏC¦\rôì4QBÑÖa‹|§˜R¼ŠSÙ'å™¨ÂH‡ÒùMCÈ9§8JCuMÆ•E>ÍÊXd›¢’S”ÛeƒÅP•·c°˜ØceI`áËê,jgô(ğ¦s%X\rT3ƒ¢gJ:uÆİ¨ÂÖ¤L‹l1\0Í¿Àì~Øy%¤ ôI”	#Zào(o¨:¿RR~PIF+ €)…˜Ìå©â&á„œ“²VÓ(F\nÔëÇòL]XÄ·{M~\$ĞŠÑØû_9,Ùê*âhfi(³èÏÅvÃÙ‘2i°=…Ñ\"ÃyV&Šê~Ä°Â“:ÁXtRƒªãûEèÌqK)6°ÆÑÃ6FmìLtBJ][àGf9 ‚¶QÉ\\\$!Ô¿p™LiEX1œP¨h8!©Ì–†Ã\nHáÂ1KÔæ;¡Ì\"*\$eÄBBA•\nêTTÃÓ¨A%Æ>i¦)nÅQÊ 6'a\$Ó7ä“I…háœ0‡©5OLlÎ\"ÔÄÂ†ÚP‰Kñ\$4¸¼¹\0å_	I>(B_\"´'cÏÒ\n ß™ƒ>Pk‰3õ\r2½0Êœ*]U5R¦¥UÏXX\"YD*‘%Š¢e^•·QAÍF•ÚkXá¼7É\$*zÎU®Ba°1µä";break;case"he":$h="×J5Ò\rtè‚×U@ Éºa®•k¥Çà¡(¸ffÁPº‰®œƒª Ğ<=¯RÁ”\rtÛ]S€FÒRdœ~kÉT-tË^q ¦`Òz\0§2nI&”A¨-yZV\r%ÏS ¡`(`1ÆƒQ°Üp9ª'“˜ÜâKµ&cu4ü£ÄQ¸õª š§K*u\rÎ×u—I¯ĞŒ4÷ MHã–©|õ’œBjsŒ¼Â=5–â.ó¤-ËóuF¦}ŠƒD 3‰~G=¬“`1:µFÆ9´kí¨˜)\\÷‰ˆN5ºô½³¤˜Ç%ğ¤n’Ëô½(F½SƒóRsxä&!;èV©Q©ÍA¯)öÖ`–Øâ!§½Fçq	¼î¸\nÓèô7º®.|—£Ä£¬µ¥pBx´±+Ù®ş îJº,¢ÖÕÂÈòààµÈ+şê%ÏÒÖ§sÊú¡Œ\\,€¡.lb¡šå3^¼Ìa®èA\$ÂñE!(Èğ!03B¨\0PH…¡ g(†¨Ûšè0zZN<‹{ç°ì\nf¼#n„D˜?);êå¦¨ #ëhtª¬I ¤¨dtõ5ñÔFôËéz@K¢’:8ˆ&³:DkÍ¨á.0têJ£ij<½‰@t&‰¡Ğ¦)BØóQ\"èZ6¡hÈ2AîkzÁÇ®+æ9ƒc¥n.¤È>×-Q›º•¡%:6¯McÈ¦¯òVÙ:–ÖIÛ¶•¥´‘**L •0ˆR:š)T2k/BÖÊm5 hbæÑv\"MY¶ãA®É3 P Éz H°1¤`û!”t'®l]¦®Ûµ\n»ÈXZ0ŒƒhÒ7£•»ß¯j!.ƒ@4C(Ì„JXD&KN‡xÂÈÚ4éÃ€ 33I{ xä_YNBO8Í}Ù éÚWº	›Käy.N\r è8aĞ^û]ŠbØÀä\rãÎŒ£vØ<›hæ4ûvR9å˜JÄG©ÙG˜æh2ŒòX‘ÆzÜÛ´[Ní¤E™	ÒsWgÉí!Ç0NÚ¥õø¨4¡\0Â1› ÜãHØ6B8c#6Ò6Œ²8Â3n8Ğæ:ŒcÊ9c0ëÖ\rƒxÏ‹õHè4_B9Œ= @1ä£&ÛÒŒ#`çš ìr\r¾šå\\ˆDßF\n@ \nXRÓLø‡ºã&.3ù£x@8CHìêƒ(gv¯í´‡@æL\rÁ¼::'VÃ»µ{‰\rŸæÑ“&d@ù²8LÔº8'W^£ZY¯-D0L‚ãÀvIÛ \$Ìš©HŠY†DÅÄ®%\$¼Ù\\Bp„*ÅØÉ’W(µ’Ö>	Üh§4+çà‹‰ÃDe!Ta<\\ı‰\0Â¤.#„”‡ˆnâLÁkQ&I7(ºãc„Œ0d€Ør`Ñà×1ë*°SÚ‹Z& ´#GÖIˆ–„MÛcº‰‘i‰X&\nÔ„`ÉÁÍXÎQIôÎh\$œ”§®:\rsNba`çÕ\$b\r'`©•¤½Ÿ)B`sIK¤N°£ƒšOQÒ‹#Äµ›sm‹ìLqeñu”õş{Ü1k3°ÁÇ¨€@C f áB±ÔFgV‰2Ê´µ¬\$hÊ\"à]EjLôTMa*?7&! ˜=4Kù:lE5†>f‰=%®hÄÂÅÖH	zuQq¼MtCˆñˆ^DAz.Ï>1\"fj±&a•q&Wæ	H0\" ¤±{–Ô“!bVC)J%ä´è	ÅH?IZ}ôÁÓTğC(ú¥\"åüÁ´ÇhÂD";break;case"hu":$h="B4†ó˜€Äe7Œ£ğP”\\33\r¬5	ÌŞd8NF0Q8Êm¦C|€Ìe6kiL Ò 0ˆÑCT¤\\\n ÄŒ'ƒLMBl4Áfj¬MRr2X)\no9¡ÍD©±†©:OF“\\Ü@\nFC1 Ôl7AL5å æ\nL”“LtÒn1ÁeJ°Ã7)£F³)Î\n!aOL5ÑÊíx‚›L¦sT¢ÃV\r–*DAq2QÇ™¹dŞu'c-LŞ 8'cI³'…ëÎ§!†³!4Pd&é–nM„J•6şA»•«ÁpØ<W>do6N›è¡ÌÂ\næõº\"a«}Åc1Å=]ÜÎ\n*JÎUn\\tó(;‰1º(6?Oàôÿ'ï2`AJ–‚cJ²92¬3:)é’h6¢²­« S•µxŒ”5Oëşa–izTVªß”#h\"\"‰@ñ##:Ä.è£d·‰9f=7ÀP2¤ªKdï‰Š·0C“		GqÙÀr%%4PÄ%\n¸àèB(Úú0ƒĞôH¤da“CRB««0\0J2 É èÎóÊ‰=Ï£ô>ëxÜ7A l¥BZâ9Ì”B9\rÄ<7Cb¤\rË›útâ¬PŠXµµR%¶oü½­â(Z6Œ-¨+#\$¾øÒã8ònò6'Í\"¶\r•óë>Œµ½s%]˜25tù<Z5ÃjÏ–±yfÛ6Û?i[ö­¯lCj	TÅ@t8ĞÛcÍô<‹¡pÚ€Â9;cbJ%,sLÔ`mhŞ3ÊÀJ-cX>“ê*\rí}pÑ„¨Ü9£ÆÂc0ë`Ã:î9…‰ä<¦=r7º—<\r«¸ê¬…˜R›˜dHä;*íê„¦)É€ô7Ğô£3Á\0—kÏ(ä˜Œ3]VùŒ#€ß\nCÊå&A¸Ë¢›ŠPÜ5¨TæB9½Á\0‚Ğ®èÖb1ê›Âæ9âb4)0z)|’\rÍóÜã|ãäCXÂÉ!JSl5GÃ¦bÊ¶ÎtÓ5 ï!t5kî;ù¶àæ;Ó–àğ8\r*@É¬ğÍOÅ è8aĞ^şH]^ç¨PäSƒ8^„zcÂ³‘: _Æağ’6\r·8:rœ´‰)4Ìğ§L&r¥##–>äÂ£o3Í¡\nHÊ5¡CX@ĞYCÙ\rÈ(„räUÎÉZ5°”2ÂƒIÆ>l˜0é\0\0w6¨aÔ¿Ì§’x!™Ø2FLÊS,\rì¹œA£4p\riŞ2@€1»£’CldÜ'ròÛqB\$	2CÓ gÖà*ê`Îâö»™±x@€(€¡|C:À ¦  æGÉ¸Cj„*7—ØlM™µ6éá³0B\np”9J‡,;—&ˆÑš@©iFYœ+Ó|Ğé9WğŸ\"VŒê^F-%r²îÁ\0ND¡3>`Œ tH,›„’\"M8 ˆä×¨¾¨NÂ)Ä:˜R”ƒ‘%oMñçCpØ4_•\$8Üb\"Ïø˜…\0Â¡07¡Ğ´Õ(-ƒY„9ÁÎ&¸üRŠ1H9Nˆi|Íğ¶U°‰ósşUš¹m.pnØ2½&4†p@ÂˆL&QªnÃ€@‚¤WÉàÆÒ”ƒ‰”±\$}£‘¤+6ÉBsÉ´–.ÂsDÄ°(-X:.Â˜œÊÅ éxÆÍânŸhÁä(åÜ¬¼%µJ¥‰t°¤¡EÍJi™Î¦±„:0W.HcÍRyÍàÙ+MÉ\"á¤33DMÂ4ü~ÁÌ¥:–tB F àÏ—Š–ƒ='OYÂPU8^Ãe&\\Õ‘œ¾\nÎëR}­•™‚WşGHù!\$rÉ´‚~¬çq/&%ì¾£SÁRêh\n/\$®h„v“¡a¥/4¤uP\n&'ó’Ë%8ˆrÍI³7Ê”ÅÎ©.Ó\"YÊÄLY’J8wH¢™DÜ&ùä»ŠD¢7É2ÛJ:ÀÁ½@üì)AfZÄ°s:dĞ#´D™ùÜ\"§~¹)Ê×u®©ë2ĞX¹)¬,Ô8zL®Ş³€§¿A‘9R*LñØ2)aEkSÁ¸ø^òü‚y†`ÁÂä\\£t’9á”";break;case"id":$h="A7\"É„Öi7ÁBQpÌÌ 9‚Š†˜¬A8N‚i”Üg:ÇÌæ@€Äe9Ì'1p(„e9˜NRiD¨ç0Çâæ“Iê*70#d@%9¥²ùL¬@tŠA¨P)l´`1ÆƒQ°Üp9Íç3||+6bUµt0ÉÍ’Òœ†¡f)šNf“…×©ÀÌS+Ô´²o:ˆ\r±”@n7ˆ#IØÒl2™æü‰Ôá:c†‹Õ>ã˜ºM±“p*ó«œÅö4Sq¨ë›7hAŸ]ªÖl¨7»İ÷c'Êöû£»½'¬D…\$•óHò4ç£2éˆ\$îïÃE’ÌN˜“)¬ç¡7^èòÉtÖœs:À¤¶ë¡Ó(³	HóJ8#Ã;Ææ :T‰'03Îáºõ¥ÈC	L\">ïã(Ş¿ËPˆ0ŒË€ä£ Ò:\rñ8¡Àîrµ	©Xê5Q«ğ‹@ƒÚœ£Ñ@İ¤Éê…Œ‰4Væ)€ÈA b„œ¨B/#‰Êê5¢¨äÛ¯Îºàˆ¢hÊ\n¸Ò45Ã¨ä2€TFÊCÈë:‰ƒV4Nğ—@5RB!9N‰ äÅ¯cbvƒ²ƒjZÈîˆ	¢ht)Š`P¶<ÔÈº£hZ2“dÜ’´hØÿL;™1ÃxÌ3-#pÊº%lN%Îƒªd‰\rìŒ~İ	ğ1Œi€æ3Oa\0Ø7Œè@æ)ãò¡\$h@Ao²HÚ„6ã(P9…)h¨7hğ@!ŠbŒ§­ˆ‚H#d¶ƒ8@3E£l÷@)iEË„hÙaWS7=×ü[d¯iĞ‚2\\uĞålk*ö2%\0x˜Ì„J@D‹*4Ş‡xÂW“1/VÅòË±İ¤0Ú©ltĞ¦hÈæ9ñl’2…\0002Œ—ÆFÅdÙ@Ğ:ƒ€æáxï¯…Ê2”ÑhÎ£Û@ğø/k(_”ağ’Õ³\rÈé˜æoš82´c|“K·m¾Ç¶:<:%¢\$n¤n`4±R2o	#6Ç# ²Â,½“²IFj\nKÙvnhQÖª ñZJÑ±ÉŠ:sA!/;è\"½Ûg ¨0tQà£Ï \$\n	ÂuœZt)Méâª’ÊË]sMë8É2Œ³0ÍI#„Z:&í+ ¼ö˜ï¨%¢ÜÍøŠ’b4.ÊŒÌg`êO@ Le9ãTÿŞrşEDôŸ“•ğt”p1á„4†²€|ÉhI!áäÄ´’úÈoæ›§0âIÁÉ‚.ØÉ›´(!i‡3E	“@WiØ0 Â˜T)ä ¢°²hLXjùNH5È*Ò„\\J=\$ñ §0ÖTyb\rÁ˜4’|ŠA†ƒ¡ŒØ™å¦PÊJ_ÁL(„ÅşeÌq– a*<Òr’NçpkşØB•ñ-©Ê(¦£‘Q‹:şB9‘!ÉÜRHÁ¦AHÒ‘!‰[EG¾Z¸A‡­!´ÆşÉş\r€®ÆEœáÇ=ğÚßœªÛEd´#G8uHÛ [ÁT*`Z%nc¨¨Éô’`Ì¡7‘òfDÃV‹IhGh<®2ÒìËŠtG2à3nS‘!AI:I~áf;g’™4¼QT<ñq«%!£vöGÃ©ƒ\$¡¸ËÁE¿A	hL\rñ¢[§3d\\\0U\n(g@éUš!4ˆœ)¬NäTj†šè×'@Êæš€\n›I¨¿<\0æù‘;s¦	dÂPòQñ¦.H2y¢‚­©0";break;case"it":$h="S4˜Î§#xü%ÌÂ˜(†a9@L&Ó)¸èo¦Á˜Òl2ˆ\rÆóp‚\"u9˜Í1qp(˜aŒšb†ã™¦I!6˜NsYÌf7ÈXj\0”æB–’c‘éŠH 2ÍNgC,¶Z0Œ†cA¨Øn8‚ÇS|\\oˆ™Í&ã€NŒ&(Ü‚ZM7™\r1ã„Išb2“M¾¢s:Û\$Æ“9†ZY7Dƒ	ÚC#\"'j	¢ ‹ˆ§!†© 4NzØS¶¯ÛfÊ  1É–³®Ïc0ÚÎx-T«E%¶ šü­¬Î\n\"›&V»ñ3½NwîÔÃ0)µ¤Òln4ÑNtš]¡RÓÚ˜j	iO•Î4AECIÃÒ#ÏCvŒ­£`N:¼ª¢Ş:¢ˆˆ\"4Î\0@´/Â©\nC,#Œ£z(ûº­T\"¯H¸äìÁ/Ğ cºĞ2BŠ·kèôó¿B`Şµ\$£ƒœÑ£ô',ƒ²0Œ©šÌŒ\0Ä<ª€L™'J\nˆ<ÊÉ¡xHËÁŠÁ/«:Æ7#«ô'KÒØ—:pPƒ¦í|‘:Mâ(Zœ£ğzü'Œìğè³/ÊÆ·køÜÎ/Ò˜Ë=Œ(ø@µÑ‹])I´|øRƒKLªC\n4àP\$Bhš\nb˜2xÚ6…âØó[\"è+5Më¤=OLÂPÈ2H‚B7ŒÃ3¥ ¾¬-ˆ¬#ö7£\0òã±ÆÃŒÃª86GnXĞVØÂÆ­tšK)­â¨aJZ*\rãZ*b˜¤#)É+déƒx\\C4ê`6&ˆÆÔ¼Œs²Â.J…›\rî*Ü¤¶í¿R^ì Ü5µc2ĞÑ&£pÎ#%ã W>8š£C@&@ä2ŒÁèD£Aö\n¶7yPxŒ!õ¢Çbú2A\rÎ0Æh²\$9&©ŠE\"àÀPš*™°áEÓj <?ÙØÉƒçw„CE8aĞ^ûÈ]Qeì\\´áz+Á*j’ùøæ	#hàIZ>’õøĞ¼´ÈÃ}Rc£3Œ2\n,–‰‰‡¦\$øË#3;®húWOä=€Æİ©ãº>.œøå“H¤š0ŒÛ\n:Û×Ä\\‘àAİƒCQÓ\"iÇµF”ğØõº\nš+Ã½3¸z³»éõ÷ğÏ;Ò(	‡8BäÓ…\n0R˜«ĞŠ	-d‘ »sœæ_i3„€Ï\$×êƒ¡«5E9Ï PŞÃ+k!Ï`7#rÚzIƒäi{GèzKë\r,(\":eKC°AˆÑe¢’BÃÉ‘&)4‘¹çÊ“^Œgi¤òÒYc.-p¹è¯°Æ¹¨‚mŒÏBPß	Ğ\"Q€€(ğ¦8R\r-@93µ'OIj‰*°ÂbÎÔ\n< ’àÎ@Ã\nx\$%ÇÃ¢úIMäT@€˜@VÂˆL(6-„`¨ü\nM&P=Ï¸Œ‡ˆbÒoÇAÈ#Dt™y¦k§E\$Éé I°rh’N‘`ŞQ—qÃ\"\$°¿‘Ã¸Ÿ¹fhˆà®õ3+I`	)F,Ä,YDn	!ğ—¥_ÊV¬KB2‘_‡(6¸@C[Ç8àÃ|åKÉ{jë’hàÚJó½]¡T*`Z\n€n7)¸3’Ô¦ZI„“~ÈEKÏs~T›ŸeQh’ôú£qr.‡ˆ˜BJ	QrkÓ„3•^qH©\"Çé°ÄğaCm\"LN|®2×6i	z/„Á3jS8‹ìfˆ…LŒMÖvé|ƒ¤1˜ÒÑ\$t¨p\nEhğ¿K‚d_Œa#Şc\$´›>Á‹çå;oHräO/ö}—úğCL”4º’YOÙs.¥ú1U3ô^)Q4 ‹€";break;case"ja":$h="åW'İ\nc—ƒ/ É˜2-Ş¼O‚„¢á™˜@çS¤N4UÆ‚PÇÔ‘Å\\}%QGqÈB\r[^G0e<	ƒ&ãé0S™8€r©&±Øü…#AÉPKY}t œÈQº\$‚›Iƒ+ÜªÔÃ•8¨ƒB0¤é<†Ìh5\rÇSRº9P¨:¢aKI ĞT\n\n>ŠœYgn4\nê·T:Shiê1zR‚ xL&ˆ±Îg`¢É¼ê 4NÆQ¸Ş 8'cI°Êg2œÄMyÔàd05‡CA§tt0˜¶ÂàS‘~­¦9¼ş†¦s­“=”×O¡\\‡£İõë• õF“qò‰E:S*LÒ¡\0èU'¹«Õû(T#d	ƒHûE ÅqÌE”')xZœÅJA—©1Èş Å®ƒè1@ƒ#Ğ 9ªˆò¬£°D	séIUº*òÀƒ±\$ÊzKêÙ.r‘º¨S/äl˜ ÑÎ_')<E§¤©a'¤¹Js,r8H*ìAU*‰¹•dB8WÈ*Ô–EÂ>U#‰ÂRT™8#åÊ8DMC£ğÑ_Çò	lr’j¨HÎ³şA‘*¢^A\n¹f–Ã¸s“P^QôŒPAÒgI@BœäÙ]ÂäáÌDÈJê¼ğ<· S\\ˆ\\uØj”„áÎZNiv]œÄ!4B¤c0¯\$Ama‹ÉJÕ QÒ@—1ıM´YV¼–åqÊC—G!t¼(%	CÅ¹vrdÂ9(ÊEÆtœPÕÕ7YêQ%~_ÅúC48b\"s‘åôeÅ’œªÊ¡ÔxCHÂ4-9ò.…ÃhÚƒ\"©>YÈı\0006ƒ“HÓäÖ\rã0Ì6=ƒ+˜©™iVÓˆı<”R *\ríxÚ0ÃÈ@:Ã˜ê1ŒmÈæ3£`@6\rã;Ø9…Øå©#8Âö`KW¯`êá˜Ræ…Ás°ÑUb˜¤#Nó*8Y±„ÊC¸Eşë20ùÚº- ”#úTğ*5#pÖİÃxå©o`Î#&â7£–Ê1ú¨Ò2v!\0x0µ¸Ì„JˆD«ƒÕxÂgäv•#åº8¡ñNï‘!O„\nÆóTò‘ê’õ)iÈr\\§-ÌBhÂ9¸]8á÷c¿MWŒ£Àà4æã \\îàhwÏ\0004@è˜:à¼;ÀĞ\\]s=@¹Óp^Ct	Û;P^ğC˜>	!´8`Û£ÉyhŒBsˆÕy¼…¡„5šÀÒ\rƒ±iîÀ7GšóÒûÑzn“áy“²›a¥\"µ]¬!Œ:—j¹µkÁˆÖbéC”'Uá„3?@ÕšÃZky°6 İ!¸h7a Ö0Ã	Á\0c° 4†ØÎbA¨=¡4*Šˆ14³À ƒAˆ9!!Ğ…xˆ*\$<™È‡œôx‚%1A–\$La’š‘†Œ!»WaQ»5æÄÙ›SnUxptÁĞİcZáÄ~lŞ\\7†ôßòl%ÍI‘\\‘’@åâ¾KÈqTOŠB^éõq!Ì“‰0èÆ10ÒÄ'ÜIQVIQDBš,Q1ÊÄäâ@PI\"ÁåšGo/áÀsƒÄâ›¦nC©¹‡˜9ğÚë ‹°tçG–Á@£œ¾~fàæ\0Â¡e„Å?˜2‚MD	7\$0@ˆäjâiAí\0¦ˆ!tI‡(°rˆ…ÊÁ‚‹…ƒ T\nÔ‹N&Õ)C¢¥ÏW/Ù™§mA©ÂPß¨ A¥Õ…0¢\0f–À€Ú;ÀŒƒ5\rÊ¼4ÂIvÛh]\r¡ô9¸&¶•¥Tó°rŸäHØadœõáR*hŠ&Å æÊFÀ¹\"TU…¯bHsc0ä,±ê¾ÃØ›f,p\nlÄ6°ÆÏk]ŞhıBe”-†¤35(ns5c\r¡ÕøU¨mÂ¨TÀ´0í'ñí9¼²L¤ˆàQs”±Y‘DHá~hF¬NÅ¶·Vù4&Æ&F¾ÛgmVĞ™q¢€Ê‘,(qæ@ÉDè¨úd—ÌÈ™2GÒ`LªíÁÓÃ€r\0V‚¨ª VZšIé?âÔÇ¡Q„Ä€æÂq€™³ÒM´T—	¡2L¶„¤Èù`Wé;;¤Ğ“ÅĞÅ×M•Ó(Š°V.§ÉÌAİ¥Ÿw››âÁ\0ÒÈ’v/¡“";break;case"ko":$h="ìE©©dHÚ•L@¥’ØŠZºÑh‡Rå?	EÃ30Ø´D¨Äc±:¼“!#Ét+­Bœu¤Ódª‚<ˆLJĞĞøŒN\$¤H¤’iBvrìZÌˆ2Xê\\,S™\n…%“É–‘å\nÑØVAá*zc±*ŠD‘ú°0Œ†cA¨Øn8È¡´R`ìM¤iëóµXZ:×	JÔêÓ>€Ğ]¨åÃ±N‘¿ —µô,Š	v%çqU°Y7Dƒ	ØÊ 7Ä‘¤ìi6LæS˜€é²:œ†¦¼èh4ïN†æ‚ìP +ê[ÿG§bu,æİ”#±êô“Ê^ÇhA?“IRéòÙ(êX E=i¤ÜgÌ«z	Ëú[*KŒÉXvEJôLd£ ÄÉ*é„\n`¾©J<A@p*Ä€?DY8v\"¦9ªê#@N±%ypÄCµ² QÖV2¤ñ ĞÀ'd1*ûØèAğaÚL«ùUÇËü<ø‹üPËI§YL©6Fªr\r\"P’Å-È§YTT¥Äêşõd¡(v…„GÊÑÖSJ%Éu¾'YdDHeÄd—E»*NÏÑu°@@„áx—&t…AÏÈ9[1/9NF&%\$\$ŒŒ9`ÆElª-àØA b„`Ë¥“¤A‘1‘TT&%ªJeXêÃä©{% H\"şBi3eMH^FE›AEqÑXÁ‘–%0¿–Uál¦\$jÅ¨uÚĞIiO„µ\r£iÂ×CAÅÍ#  \$DÃº°#ÒYa&¡AÑÖ[Ò>‹cÎ<‹¡hÚ6…£ É¥‚µ6MÃ`è94íH@0MxŞ3Ãcì2¹ä\"`YJ4³;¤Ù\nƒ{d6Œ#pò£pæ:Œcx9ŒÃ¨Ø\rƒxÎûac|9gcÎ0¾Á«v„kì:¸¡@æ¹ì\$Ä]d‚f!ŠbŒƒÖiXÏ:…<÷I¯iAÃñ\nƒIeä®c™Š`Ü5·£0Ş9gƒ›ì3„É­\rÃ(å§c~|4Œœ°@-^B3¡¨Ã'4âqáà^0‡ÙK*B§']6u”dfâôCÏ>]JC{Ú_hÂ9¸¼`áãc¿PŒ£Àà4ä#&ãĞtC/H\r è8aĞ^ÿ(\\0òy8äqc8^2ßxğâó|Ğ_Óağ’6\rÈÚü£®v¬†SÕ	¿k!„5šğÒ\r›–gT7Gd;¢[Hñà·ÇˆP\\)¯!Œ:—4¹¸háˆ×‡,âƒ“ÿT!„3<@ÏÚCh­¤´°İ\n xh7Á ×‡0ÂÿÁ\0cz°\0004†ØÌ’BI\nD‹Í0P	A óÆAT'À²¥1Ø.P@!(\"âÈG‹<XF!\rÍ9XHãÍñ²6†ØÜ Ê¨Cƒ‹†ôä›\0ß\"sIñı° TÙ3¾!l½B+òLNc\" äÌ¤¥Èà‰Œœ'¬R‹Â<HÈ¼ kÔ£€ˆå\$¦/ˆ S 7„‡ ø\n	\$t<±àÈç\$4oÂœƒzÈCˆu7@3 Ş\\‹ér®0ã¿’ÒfC¯,İ” Â˜T<ìU‹¦ØÈyÄ!k…m,‹ÓJdgT©´¦”ò£,Hğ¤;¨ u&‚  ìŒÂ ’©fœå´oÌtÕ6rÏèo}| Òä˜Q	€€3GĞ@mİF\n‘qœªÒÿ\$V™“:hL0äkŸYzRé\r#ô(ä«KáK)„,|‰\0Y¢ZVNõğU	¥3G¢t•#£6'„-:^Ò¸ ®ÙñP*R•¦˜!±°Ø\nÃ&a¬BwB¢tÊ-fÌÎàyAÔx6‡W‘E`t?\n¡P#Ğpƒ™—çŞªª\"iùñ:dL˜Ê¦iŒ1ˆ1J’¦¬Åœ'H96„àÇXôbkmoLçèDÁÔ-Ü\$FPË‡l RÈ¬ Ö°Ë€ ™D•mqf¼áœ»mn}«Aex²#!O„¶W)‰^	3¶,…„©Te0Êna\$Z¢Ôı£&î¼—RŸŒ–,\">:ç\$ó*D1[«›\"œŒY¹jí^Û3*eÀ";break;case"lt":$h="T4šÎFHü%ÌÂ˜(œe8NÇ“Y¼@ÄWšÌ¦Ã¡¤@f‚\râàQ4Âk9šM¦aÔçÅŒ‡“!¦^-	Nd)!Ba—›Œ¦S9êlt:›ÍF €0Œ†cA¨Øn8‚©Ui0‚ç#IœÒn–P!ÌD¼@l2›‘³Kg\$)L†=&:\nb+ uÃÍül·F0j´²o:ˆ\r#(€İ8YÆ›œË/:E§İÌ@t4M´æÂHI®Ì'S9¾ÿ°Pì¶›hñ¤å§b&NqÑÊõ|‰J˜ˆPVãuµâo¢êü^<k49`¢Ÿ\$Üg,—#H(—,1XIÛ3&òì7ö4Ù»,AuPˆËdtÜº–iÈæ§ézˆ£8jJ–’\nƒ*P:-B°Â94-Ô»4ãJ\"òŠcZ¯,(ˆ0Â»~6 ò\"Ã(Ô2Â:lğ¬ã\\P†ˆã(Ş6Æ\"–î9lZ/+ØÖĞ¬‰p	B²”µ\nq@á¥ğŞš¡¢‚È”C«¾¿Š\nB;%ÏÔ¶4Ë®úTµ=cª–—±C\n¸µ£ @ô»*\0:Îè Î‚Os´ğ:.ÀPòÏL¸!hHÑÁ«š2«ˆªşÍc¨å\" #Jüò‰‰Tò†ŠÃ*9¥hh‚:<r;Ê\"sõ90‚') P‚¹1nÑ.KCK@ÊçXµ²s;EÄT6BícYkz	J –…¤Y¸c\r«fÒ¬h½½4Ø\$Bhš\nb˜-7ˆò.…£hÚŒƒ%uVF® È¤ÇúSl{#	²£xÌ3\r‹8Ê’\nc(åËwÜ¹/ëø¨7¢ÉXÜ<„õ21Œløæ3£bŞ7¬Ãpæ4ã–@ä,ï¸İj«8ê¹…˜R’!ëšl³Ïéx†)ŠB3Nø®*d!#rfù2©M¦âËÚ7¥÷üv¸µKÛBZH%¥Z_bîûúÊÊJ£ŒºÜğ²crP¬y\nRï„Éœ¨C–`1ùpÒ2bA\0yo\r0Ì„IĞD=«Cü3‡xÂbüÑ®ş¥Mi|ğ3­‰Me`íº£î®2Í˜Ô‘¤³ˆc˜î±ÎÃ(ğ87ËÕeÇ²\\—(4ƒ à9‡Ax^;úpÃÁáÃ\\±ŒázãïŸÄ…ü¨æ	#k÷º®#§;ÏÀAxÔJú_ä“²®Ëõ‰cßoB—ÁY¡¬<ª“N¦ˆğbm'Œ&–2Ó›Á•!Œ¸@Ìã*FT81&²‘âv!˜¹›öFÉ]ë(eD‰–Ás²\r8hj¡…\0Æqœaä%ÜÃ–Õ¼~Ïèi\r!¼µÃã,©ô2îÌüÀ\$RĞÀP	@ıÁ.\0 éŸ€ †âJhØÌ†“6gKÑú,aĞ—š£.,\$Aİãä`©›ùfLÍ¢dÀˆynáXßE”JÉBk%¤¼¸8´ªXjA!²°Ô0vq6uï]Å£S²eIÑ¯!aäÈj£y.)ØÔ£ŒC©Ÿ˜9#·ö,1.0Ø‘3++Iy3Ä(8Òöm\0P	áL*Ji\0Œh \naÔ•Èöõ'æs_šL²1C‘éVrOb*ƒ1em\$1º2‚K€t€§ÚP€¦\r\rš‹!?a½ëÇîYfQ	„|ÎS6·‚0TŠE; áÏ¼³–¡¶U16\$I\nB¥q~(!FU¡@Vdh£ÑóHC™:!Ô5©¥m1Å%%éñ8§4S\r\$Z”ĞêRÒ´)ÌxYTğÅ® †«`+l41†°A–ğv%’Åû5²j™Ù'´ùOîşY¸U\nƒ€@\\\\A5;ä’¢Rƒ¦FˆáLP'M!â1\\êĞ­¥qWr6„\0e#y­äH«N!p.DmÖ’T®jÀ\nš¢ø¶¨üiêrCÉ<•ÄB¡Á|Sä8±–ÂöÎN)Ç/¦òÔ¸Nµ§µÙZşfÚÙ–)2Ô26`'âvkgS\"BÃp\$áßX¹)#7yKbÂ¤&Ôˆã¦S<S¢v¦¢…=W»¸kÛP¬D¸ABôy‰q=Á”Pô(ZŸMîVÊMJÚÆ¼ÀPÅŠ.qZ_[÷Ûcı Ng”%Ô\"<GCË";break;case"ms":$h="A7\"„æt4ÁBQpÌÌ 9‚‰§S	Ğ@n0šMb4dØ 3˜d&Áp(§=G#Âi„Ös4›N¦ÑäÂn3ˆ†“–0r5ÍÄ°Âh	Nd))WFÎçSQÔÉ%†Ìh5\rÇQ¬Şs7ÎPca¤T4Ñ fª\$RH\n*˜¨ñ(1Ô×A7[î0!èäi9É`J„ºXe6œ¦é±¤@k2â!Ó)ÜÃBÉ/ØùÆBk4›²×C%ØA©4ÉJs.g‘¡@Ñ	´Å“œoF‰6ÓsB–œïØ”èe9NyCJ|yã`J#h(…GƒuHù>®Î òo(ÔƒœTë¼ßp(Tªl®§U«É˜{Q*|Ä ‰ğÎ3¼€Pœ7·Ãxä·Œ,8Ö¤7IcÂï50jÜ)&ã:‚¸°\"8Ë9Ì:LŸA¨È\"§c¤7¨á@ˆÀ%¢¨Œ6\"Œ˜7§.JüGqêú³\nØ	.zhÃ¨XÈÂ.xÉã¢2¢)°Æ`PH…¡ g0† P‡IÜ\$Œ©ld)ºL˜\$(€P‚—­Â€Ò¶¸‚+9#ˆ“Dà1\rC,º&7£€Ò9&óšn:A²p@È¨ûõ§CHÑGÁ©(çCD«P\$Bhš\nb˜-5hò.Í£hZ2€Tõ>'Q¨6?s²ÓíÒr7ŒÃ2Ú7©(¡JŒ)´u2ôª)Š„n÷¯N¢7C„Ï·ce¡#vrr¤ab!= vòô(Ã;,¢·°“Ú¤Éãk`:\$¢ ŞÌ7b¦)Á¸Ş\rp@ó tØİHL,¹²3-x¶2 ÌGk\nè.‰µúè&(‚˜)ÅŒ4Èæ#%õdWU¸ÍÛ0ÓA‡´aæ9£0z)A|Î .‚Zã|ÙKµ.£Ş«\$:]IƒÏqHNTˆÈ/KjKR€Ã·c­ –àÖb‘jøV|\r„\r è8aĞ^ü\\¢fn€]áz}ÅxÜ˜-!~ˆ9‡Ï0Àišp@1ÄªHæ4-3Ôc\0ê6WúÒ·túa\0QÅ2—!3~*†1¡H@;Ïhú{ØĞS9'Œ#3Ÿˆ¹CÇ,c0êëO~4ŠG³İ=¬ç7 ‰ò³±,äø7éÅ“¾?Sv(	‚kŠ\nPSL­2qƒ\r*~U\n²Ê:æõôºÅ¢AQ\\Æ¼ƒ“:@Ø9;'¤ıÛ§b^–ãş=¬%\ngÜcĞ‹S)	ü2†%4e”ÚOpInÂ˜cü':Ãè‚Q dÜÆ?ÓèØsvÍH#`®L8 f.æ.ÆÚš³tlè´İ›J [(P	áL*6×bÙÖÂXS €%†PÆÎal¨Ô÷ˆfENaÏRÕÏ¥~Äƒ`e„dP‹‚4J“Û\n!1ù¿²z{XŠŒX#Gä„M¤	1ë\\Ë\"òŒ³‰*I2\rµL²\n@ƒl“DÒ)“0rMAª“)(¥IÆ¨N×\$J†½%Jç´È)t|¡ı†ÀVËhc[oœÕc,èHÙœfdúÈ9UÉF&ˆ¼Ò–d\"B F à’Ë3€%);’!É~+c ÔÑB5†¹ƒ“ĞäÈNªzWà)¤S¢`BAÂ>FoœG,‹ƒj4\rĞ3É“9ã¼Å#&¾U1¢ˆ¼!*šW*B‡µEÓqkAê-†V®tŠ˜Sèàè°å%7ÈŠÕJ)>#5Ä`B)/%\$h™âLj kĞšN¶Ï­?;æˆ—–¨¡MPs";break;case"nl":$h="W2™N‚¨€ÑŒ¦³)È~\n‹†faÌO7Mæs)°Òj5ˆFS™ĞÂn2†X!ÀØo0™¦áp(ša<M§Sl¨Şe2³tŠI&”Ìç#y¼é+Nb)Ì…5!Qäò“q¦;å9¬Ô`1ÆƒQ°Üp9 &pQ¼äi3šMĞ`(¢É¤fË”ĞY;ÃM`¢¤şÃ@™ß°¹ªÈ\n,›à¦ƒ	ÚXn7ˆs±¦å©4'S’‡,:*R£	Šå5'œt)<_u¼¢ÌÄã”ÈåFÄœ¡†íöìÃ'5Æ‘¸Ã>2ããœÂvõt+CNñş6D©Ï¾ßÌG#©§êö{„Ÿ†o6væB)âˆ9«Ã˜tªjÂ´”(É+HÉ±ˆZJÉ=oj9)C*d3/CI†U¡¯Øè<	#\$“0Œˆˆ¡§ãò0ëĞÂ4Á¡8°&h°œ9/xÊ7¨î2Bb>’²\"(È4µC”(øÂã›¬0 P®0Œc@è;©®(\$ÉxÎí°èğÔŠ9ãr9 ƒ '+Ã¨î¨¥r¨J”ŒCÊVÎéõ13@¨Zt8bIû–º¸ƒ\nú)®É\\æ+1+”#é\0B]@\"…£lf¾ŠÍê0ã9#„b5(S#Â9!K™>Œµ-Ná¡£ÒÄ™&“âS\\ÔÍ2¦âWÕ²j7 P\$Bhš\nb˜2•(Ú‹cU¼5¬”p7*cš:2ÓÈk\"6’s2‚º‰`Ş3ÈòöšŠhkˆ\n²dÅ21­\rL7(%È:ŒcH9ŒÃ¨ØLKĞæèİ¸(Â3Æj*ôª%#jô:¥a@æ·R;%Œá\0†)ŠB2|å…Áf’£«ƒ c2ì£Èh@7\nèË9&·èé¸MCx3Œ‰¨¨Î\rÃZ§8ã¬5É/c“¢1¬PŒ¸ÑŒÁèDª„AğÈ±(nøxŒ!ö‹9ÜÃœ\0–6n\"_¦ÿ³Ùşƒ8ÎxÚ¡¤0É¼ãõ.8¯àğ8FÖc² ÛFÕ-Ğt…ã¿L&:â.Ã8^™õïôr4¬A~Ö9‡ÂHÚ—²Ü:n{¬H-ˆŞ”µ^ Â†N¦c¥ñ…À>\"~¢FÃ,±O³Ñ\n°ÚzYíöŠ+ÆÂ(ŞpåªH)HÂ3RÈ®…oXnˆüÒ`Ğ• è¬¨K\rX\n}I!şpéÿ+ÆI¤²rğK€H\nZD–Opb9çVH‚b/jxï“ãDi\r1%!ÁJ•3fh’%æ%¦‘vL›`{#g|ÈåÈMI¹9'dõ4¢…x›N#A§¹é3\"\nk)*¤Ô\$‘@òfi+…8Š“7lŠš\r!Ô£‚\0ÌS	ëZuGØœ ÆK×3ü6nDP Â˜T¦¸“RK\nYM‰ªÌ5—&6~âÙ/„-\"†u×š¼Q@\"XUA`\rÈp3³fâIªï“'ØÖòb¸b/\0€)…™¡IÃ\r'\\#H&VÊ‹»R¯63¤º\$TN²Hñ†£\rBLÃM\$qÈ’Y„ïdÁª‡¤ı¦Tú“ì¨\$ó1É=%„éS™a–f½(@»C`+§É Ã„àÙû—ÄÈ…¢XHÑé—ÒÄ’WÒÆÂ¨TÀ´\"æÀ’ÓTÖ%&T’RG¯—AÈ•¢]7Ì¬ïD06ˆÑ:*qY)(\$|˜\";=¢ér]*\\›<pÒƒÊœ^­/\0 ¤GdùŸ)'¡i,ÄÑ!Tìå&~Og¡,QT¢> y†	„Á—bY\nEª‡|.Œ‹ŠI\0(+‚äÊ¡\$aÄ˜¡‘\nGèÁ2+æ•´Ü%(iaD4W.¥WQÌwP²ŠX£‡‘ğisÃjGiµr9•:ˆrÀ";break;case"no":$h="E9‡QÌÒk5™NCğP”\\33AAD³©¸ÜeAá\"a„ætŒÎ˜Òl‰¦\\Úu6ˆ’xéÒA%“ÇØkƒ‘ÈÊl9Æ!B)Ì…)#IÌ¦á–ZiÂ¨q£,¤@\nFC1 Ôl7AGCy´o9Læ“q„Ø\n\$›Œô¹‘„Å?6B¥%#)’Õ\nÌ³hÌZárºŒ&KĞ(‰6˜nW˜úmj4`éqƒ–e>¹ä¶\rKM7'Ğ*\\^ëw6^MÒ’a„Ï>mvò>Œät á4Â	õúç¸İjÍûŞ	ÓL‹Ôw;iñËy›`N-1¬B9{Åmi²Õ¼&½@€Âvœl±”İçH¥S\$Ñc/ß:4;¾õ¡C ò80r`6° Â²Zd4ŒúØa”ÍÀœÁƒ²ïã*ÊÁ­-Ê :ËãŒ…-ƒ<ñ!Khì7B‚<ÎP¨˜å·«dh(!LŠ.79Ãc–¶Bpòâ1hhÈô)\0Éã¢şCPÂ\"ãHÁxH bÀ§nğĞ;-èÚÌ¨£´EÖÅ\rÈH\$2C#Ì¹O Ù¡hà7£àPŒÅB Ò›'ô\nú¼ŒñsÔÉÊmô(-HèJrxËMÅ*–2SòãM=‰Ğš&‡B˜¦zb-´ÕÉJÓ´•AòÜ<7#éğZ™hĞ-À²7 ƒ”3ÓúªÀ¡Pó­Ò¡\0ë9\nƒxŞHRZ*9£Æşc0ê6#’=ˆ3ò[l0­Œ*‰'«e¾2…˜R”Š£8ò6/Ë@!ŠbcèJ˜„¼Ÿ £XÔ2²,23ãm˜*’5+„ób<¡R*\r’Zß[È6 Œ—ê.9^ƒßbBŠx0„B|3¡ŠÃ&Å¡à^0‡ÉI†L\rnu¾6'cƒŠXcsÌ3½4z‹ƒ†7J5JV¨T4n´TH·®Óá‡¢èúHĞ›˜t…ã¿1yÒô´áz—Ç	Ÿ…úPæ	#hà¼Á’\"©jl²1™¢‹š8yvÕ±t9HJ]S«ë!jt\\R#†;C\rXç;£\$Ÿµ¹{2÷i~é£ÒŒuã2ÑIãÍRİUÙw^5óæƒBeãcn/d4­Ïòâ¹©–øí ¾Œ?à÷>/oHt¢€kò~ŠtŒ‚Š\nJiY€¥—6ÆµÂ?\"ï%”!¶_š”/­¥:\" po9Å¹#rúÁ3eù³\"Ölô\rLü”’²KÉ‰’’“ğèŒŠ`o[Nıõ5öRRÛèM'Ø”„’\"VB‚*cRøàù>!ÔşœàÌŒó8qP(Ì”ÀÆ‘ŠX4AÀÛ-pŒiù¤‹Î†wJXËc­åR‚@cœ;¦07/BğPâ[·'¤ıN¶“†QAhkgä˜3‡SášÄ/Äù¬Á†\\cˆ k2DprDI¤q !L(„ÀZI—ƒóh*\"\\Òt*F°ı¥L–ÑK/f,º0æv	©\nS¦9O P,Ãc óœ‰JTIÓ?0äBTú¡›e\râMéÀJB\0\r€¬5b#9t[EùÔORë&Š?.èÓ»Ä2F#ÀU\n,pÊÓÈoa!l¤óİHÌ¹7óFqPò†Tè’(U!.Q•H ÈÄ;6ÈYî@S:bF0Â;&ù€8¥üëÄqí4¦Ê;ˆ`ig±ª#şDtrgÉµ=2@,¦ÜADé‚~F	òè\rîd©šEf!Ö\nÅ¸3§ –‘‘ E%ªß—\n2`œK-<R)u‚¡v/¶°ÒZNL8e";break;case"pl":$h="C=D£)Ìèeb¦Ä)ÜÒe7ÁBQpÌÌ 9‚Šæs‘„İ…›\r&³¨€Äyb âù”Úob¯\$Gs(¸M0šÎg“i„Øn0ˆ!ÆSa®`›b!ä29)ÒV%9¦Å	®Y 4Á¥°I°€0Œ†cA¨Øn8‚X1”b2„£i¦<\n!GjÇC\rÀÙ6\"™'C©¨D7™8kÌä@r2ÑFFÌï6ÆÕ§éŞZÅB’³.Æj4ˆ æ­UöˆiŒ'\nÍÊév7v;=¨ƒSF7&ã®A¥<éØ‰ŞĞçrÔèñZÊ–pÜók'“¼z\n*œÎº\0Q+—5Æ&(yÈô\n(üşXƒÆ¼<Ò`zSq”Î•®OôçŒ¯rBA ©ª¨îß+Hz¸\nŒŠ7¦ ò8 O»£3ÉÂ	Ã¨Û¹#ÓúÃŒ+ã|cĞÂŒˆCJ€9Ebš¤B8Ê7Äã ä»Bb²áL7OcÃûÒ\$FiHŞİƒxÎãcK–æŒ+«–¾5ƒš\n5Kât02È‰Œ‰3:!,â1NcÌë;Îl¨î/JZ°\\”8b\ncĞê5±`PÎ2HzŒ6(oH§&0Œ4Œº€R\0áÊ<x8\rô‚c\$…©èÒ6Dƒ;Œq*Ô-’Rb±Œ¤Ø\néA6‘0Öæ”£Ê|â2Õ£\r^65‘RÁV]›W¦¶ˆócVMªãÚélƒ£¯x\$Bhš\nb˜2Ãh\\-W¨ä.¾HÂ˜´HòÜ6HO*\nƒ¡!\0Ş3Éú›\np*i&Ø2Ì¶Ã6Mî•¥£«ÜŒcB92£A‰CğÂ#æBãU,B2®jì.® èåÚ¨É‹ªã.498öTYäY#ºäùNA¢eƒ~\\–æ6şj:¸YÊƒ¦8ÂŸãš—¢ËY.‘”zÅ¦éée©FÚ¢l'!ìàÂ‡’°î)ÃZ b˜¤#Á\0§³8Û½¸0¿C4˜6£`Â÷!zt¢‡¦ØÃ£¥u@Ûa'ü£J¹i\"	Íê0ö#&ké0»~3˜ôú‡ŠĞÈÁèD¨AóF¾9DÈxŒ!ö3¨;¸Ï*Û¹De‰K’ñ3Èí)ª5}—•*ı\rb‚3¥3óúÿ¨CÏ—Ãğ=¨AÛŒ½ÈD4ƒ à9‡Ax^;ÿueÕAt˜Áxp\r€¼2‡‚’ëNîì9ƒãdCl%ÄAN¼7ŠP–@s\rÊñšRœ©Peõfæ—ŠQ%…—¬°IëË(ìƒ0`Ä·«Ë#	œÔ‡p@@P&ÁP43\0ÂÃ iiÅ	W†ÂLÌ`rqAÉ§Â—!¡Œ1Pæœk#KHÚ&F=™€sL,À1™\0É°lf`½ğŞ€ÃÒ- Qv¬ésÑâ= xO±/&\$Ì»&…zĞ\\Š»W±ì%Óê\\“Ã³«õ®‚\0PTK=†ĞÑ6n&NÃÖ¢^# H\r‘AÑ?c8@Hpl¡©±I	\rIi£AÜ7¥–‰‰\r-Ô•I8Ì3&*h5Ÿ\$bä™¡9'dôŸÁÂDŒCõT¿‚\0´C	ÏAQÍ_(›¼ÄY¹‚š²0šq.N¥ş4”dCY5…9ƒPß4ãË‡Zè“ÊF°/	[h.-Ò·:ƒxbMd§=²•D˜ë#¬Ša½\"tm‹©ƒrlà`Ò–Èèc!ª•Î·+I!K\rf¤)…˜Í	€¼ ¥È;‘ÇR¡šfL=ÈËrâ0T\n~<§eD³lD=y\0 ¤dReV”ËìAcÄGÓy&Õ}7:ÆJYˆk0„ø4‡©{Zë“*ºÖS¤Ğ	µ!ä~l34ÛXYÍ)°‹RÃW“blJ±‰ÆÃ™4üM‚B\r€¬5•ZPœ})’rAN;¥\$›³VâU\$®1.’còR‹a|!Í9˜C0ª0-ÚÆÖÚí_YXS•İ=˜û>«x‚ŸK:Ü¶¶n-a–·¾%k“ujÓG¢s!˜3û½%š¨DtıR<\\‹á~6	€©¹.åjW«î8Ó–ëRaRqø5Œ(’sLj\$.v*£êwjÕr¨ÄÁ˜¸˜râé¬½Äk\n'N[‚œø(\0*±‡B>zdz(G=m‡Jv[®É“¹¶TÁc;ŸÚ*ˆõÙ„C7lŒ0E'UeP„›À¨V2R²g|XA©İ!¾%#†„(kœrE\r3€ ãLq}\rªC`";break;case"pt":$h="T2›DŒÊr:OFø(J.™„0Q9†£7ˆj‘ÀŞs9°Õ§c)°@e7&‚2f4˜ÍSIÈŞ.&Ó	¸Ñ6°Ô'ƒI¶2d—ÌfsXÌl@%9§jTÒl 7Eã&Z!Î8†Ìh5\rÇQØÂz4›ÁFó‘¤Îi7M‘ZÔ»	&))„ç8&›Ì†™X\n\$›py­ò1~4× \"‘–ï^Î&ó¨€Ğa’V#'¬¨Ù2œÄHÉÔàd0ÂvfŒÎÏ¯œÎ²ÍÁÈÂâK\$ğSy¸éxáË`†\\[\rOZãôx¼»ÆNë-Ò&À¢¢ğgM”[Æ<“‹7ÏES<¡tµƒ®L@:§pÙ+ˆK\$a–­ŠÃJ¢d«##R„Ì3IÀ†0Œ‰ Âœ(óe¦pÒ¤6C‚JÚ¹ïZ¤8È±t6 èø\"7.›LºCbğ¡.«¤ê®8ÊøŒ¯V	ŒËŠ¬[iÈÊÿÅ#LVº<‰CNô¹Ã“& +¤¬å Œš}\r‰ÃxìŒÇìûh‡\0Ä<¡ HK8ÎhJ(<¶ PÜ¹¨^tb\n	°Æ:ËÎ0âá”z\rƒ{½‰ã”2¼¸ÒHÚ4¡ P‚ë;šJ2Œk8ô…©àÒ½ˆ’‚®rä Êäd\"¥)[;¤õrL”%PÂo;N(ÃWÀ•í#<p	@t&‰¡Ğ¦)C È£h^-Œ7(Â.Ë-}Ræ¹MÄH\rÑ´©d¦7ŒÃ3×\\§0ä•E‰ÊOÓˆ›b£	ÈŞ '£ËşÉ£ÃªMJ®ìXÙXu£eÁğ§Î8 ê„…˜Rœ\nƒxÖ”¦)ÙR¡=j@\\W–+Àü®ƒn,–.-Rİ9Ã-ûƒ¯¨Hå‚C Æø¢x›§-8Ü5¶C3ø9¼á\0‚2`ˆ>7©²Q& ƒC:3¡ ÊBòçÃ8xŒ!ö–/ªààšê8Ú<–¿SZZ”<Rœ3£kk\r§#¬0æ;®–HñÁ³£&w¶4Û~â4ƒ à9‡Ax^;õt1²Arè3…éGj<@ÛšîC˜|\$£ƒ_¤›Şû„€èŸÎ-§’0åùèàiïœVx7Ãã*H6,¤ç­Ğ`¨™ cæ„½^“0 å¯iî¢3rƒ#‰x®.ØËíT İ¥3êŒ+q„¬6 Â,eÃB@¤¡„ œ–@eùÜ „@Pj®ÁA°PTI'+ÈLÅ‡2<ˆ‰ lBÇ5\\¾¦Âó`i5ÆÁ8ÂCdoSûÓ/JT;Bp{àY*Ë—€ìÎa'Dáœè¢ãÊ	C(°i)˜ç‚Gß’Ÿ9è¿c‰Ù÷qìé ”Ê„G\"AäÒ \$Fjšfy¦ğÙÕ\$WÉ6 MØ@VBÔ©şyfô8-”¢xS\nˆ	¥\"xØ0 éx7!–vÃ u!d¸ßÂ|Fc¨aÍ\$Çc:ù\"áÌ\rÄÀ3¨È–uÌ¼q^qÔÇ2l¥PÄ­EØ0¢Ê¯Jk`©‰êq(°‘éH¨w‰#±Iµ%³ÖÔÔÛ3Óuä˜£LV18›†€¨3c¼ˆÚÂÀTl0†d‹:VCÒ‚Ş{Æ€Ïlr 4© @IÒ\na¼1ÙH[”©-‘©x“¤àÂT©S™O\$ş½7âÈÂ¨TÀ´pÜÔÌ[y'\0Î¦â34Ë¤%Xô²qšÈBÏ'<¦‘Ä<Ø!‹]J©2tab1&,3•ºªès Í&qT•H÷\0QF©“ ãT«Be\"û\rs–­˜ê\$K!ziÄ°œÀß0J57	\$VúRzBc(la‹¥âQQñ±bÔÈæ<^›é¥.Ÿ4¶n‘ELºˆHdŸêUU„W7LOu8|J Ø¨ó2»VyX0ÕÁ˜R)QÌ,â¥É09©\$¢ƒ:";break;case"pt-br":$h="V7˜Øj¡ĞÊmÌ§(1èÂ?	EÃ30€æ\n'0Ôfñ\rR 8Îg6´ìe6¦ã±¤ÂrG%ç©¤ìoŠ†i„ÜhXjÁ¤Û2LSI´pá6šN†šLv>%9§\$\\Ön 7F£†Z)Î\r9†Ìh5\rÇQØÂz4›ÁFó‘¤Îi7M‘‹ªË„&)A„ç9\"™*RğQ\$Üs…šNXHŞÓfƒˆF[ı˜å\"œ–MçQ Ã'°S¯²ÓfÊs‚Ç§!†\r4gà¸½¬ä§‚»føæÎLªo7TÍÇY|«%Š7RA\\¾i”A€Ì_f³¦Ÿ·¯ÀÁDIA—›\$äóĞQTç”(_mèêÌªz7­ÂÈƒ2æjÛ„\nÂ¶®©¡\0Ô¡³Ír!Œ#\"V0§CJBÜCC3\0ª\$IPİcª†¾¯HÉt6¡iÖß.r€9C‚¯ P¤2Ã@P2¾orû	Œû‹²*’øúÅ1âô)¥mp\"º1Ã6&\rëøİIèÜ• ÈÀŞ¯Çí+Ê½£ @1(06Íó‹n3¸Ü½¨^søb\n\r8Æ:Ë@RhÁ&ˆX@6 ,'PÓ-¨ÊÊ&N±;ohÆ¼Òò¸Z,ÙN\$c«¢½3ñK	k): ã\rRıãJT–%ÊH]9ÍÕÍRÜËây_ÖÊK#=”	@t&‰¡Ğ¦)C È£h^-Œ7(Â.Ô-­Fè:­íœ\r(Õ5‰{¼7ŒÃ0ØÀ®2–©.UtÍŠƒz‚Ÿ0.:ŒhRF3©M!/ab\n9a6u/°lÜ¨ºh0P9…)Î\n5¥a\0†)ŠB3¾–(wİ„V©sÇ^L8Š^¼¶+´`èß¯:JŞÍíR…2MîL×\rÃ]ÿoH@ Œ™‹n†£\r&äj44c0z+|¤°.Îã}\$l*È83PÈÃ‹\$)ƒø›¦˜ê2…°÷ŸLššÇ É€àÇcºùb£F2Á¼Öì;Ğ:ƒ€æáxïÑ…ĞÎ¬9ËàÎ¥}`ñj.€_²ağ’6\r®~:m›tÜF¢7˜>Pï“Å28ÍÚøßÆ¬ÆãÄ+`Û½íkT²vt4;ÃÆ©º\0ïT¥,dÁdÌ2\0@0ŒÜJ-†aÃ!‰K¸ïÏá8óîŒK“L„¸6,ò2gCB@ä­Ø†â0•P5@¦Ø ¤@P~mäÅA0PVI*,ÈPÉ2B‰±(BçA1¾6¤ñI³U&Ù7AÃpp“ÛÉ0J@;¤r|`\"a…OS\0™‰T\$ä(C£Ğ (E£A#½İÂ%)é„©\"DD^r§å½Ò`P…wğüŠ‡“V\n¡±y&`ìœ„hÔif?fœ„5GNÿRøcRá'ÚP	áL* DhËQC\$p3£ƒ²¿A?Ô‡†˜Š1^±¥ >øàhÊ™v’<7\" Î¢\"\"Ÿ3¨9yÆø†—ÍÚC2˜1à@ÂˆL?pÌ+`@‚¤'É¸£AÇ‘Ï™_ä,œ„ù2iQ§WŠù\rX:Nœ×MSid«ã|uÙ{ÓY4ÍŞV&Û/<(œ§E4B'+ïÇºI¬9ììÂË~ñg!sÊ\\‚\r*4‹ğÄhäñvR(—£T€dÁj'!bÓşò_S\n¡P#Ğp¯ÚÉ’\rÁz¦ãG8Èäù™¯>~¬CE6\r2%}‹.™É'Œ)\$‰¨’rRfR¤:¨Ô˜ÀØ‡ho2A˜<­ĞÊÍs!MØŠõ\0U=çÔ©Î•î£Ì\0k:ÆNŠ#ˆ¥TƒLB:¯`ïQ\"øNRä»(ô@Ş˜ ^iQê\n\r/!Á†.—‰F/Hı¯&sİÄŞM”ºœ&ºmKçqJ‹¨ƒ@Ô‚¥®Fš³OŒØB?ñ˜.äJ`MÅM1¤^ÙTú»L`sQ¨#Ä•„";break;case"ro":$h="S:›†VBlÒ 9šLçS¡ˆƒÁBQpÌÍ¢	´@p:\$\"¸Üc‡œŒf˜ÒÈLšL§#©²>e„LÎÓ1p(/˜Ìæ¢i„ğiL†ÓIÌ@-	NdùéÆe9%´	‘È@n™hõ˜|ôX\nFC1 Ôl7AFsy°o9B&ã\rÙ†7FÔ°É82`uøÙÎZ:LFSa–zE2`xHx(’n9ÌÌ¹Äg’If;ÌÌÓ=,›ãfƒî¾oŞNÆœ©° :n§N,èh¦ğ2YYéNû;Ò¹ÆÎê ˜AÌføìë×2ær'-KŸ£ë û!†{Ğù:<íÙ¸Î\nd& g-ğ(˜¤0`P‚ŞŒ©òê7¡(*€°ËØ@†\r¨{‚0¼Œ¨@± m\0ÒƒªIê~ì¨I²Ä¦»5)ëò4¦‹È@Ã„	Xä0ŒoÜ\n*\r)]\$-àÒÂ¸+ËMc\"1Ic²à)	í÷\nB’M¼¢8Ê7£(èÖ¿Ñ\$\nÌ3KÄ†Ä'èÙ±S0¡\n.£<Lè˜7Œğ¢p8&j(Ü2 LêşĞ5Šêp76LèJ2|k/4]GÁ@Pò¬T@¡pHÓÁˆ!¡éô»¯(é/\nÿ©ƒ¤1ÁH\n)²ƒ-èŠz`ÈÂì¬j9;ƒJPÒˆ‰ÀÙ9\n¾:§ÊãD2)ƒ¯5Œ\n ¥°C!l¹4’ŠŞÀH‚ŒÖóp4*qj×Q ê6B@	¢ht)Š`PÈ2ÃhÚcÎ<‹®•s]½ PÙ/MŠ7=Q£0Í5Ïîjt™³È\"bòB ŞÜOÃÌĞ£®9c2†6O€YuåCóA×¼…˜Rˆb˜¤#8x×@l4ŒW A+KêC4œ]#’ŠªÁZnDÍF->\\¬'¢£h7\rjv³¡/°@ ©kÚg›»pÓ¯‰`ĞòÁèD©AğÈî1KØÎã}²ÉªB„ 0Ú9„XÅfö Î„:ã³¯º×Vª\rÁÔ@ê‰£\n²™£ıPæ;¯bÅË<˜ A¼„ØË¾„C@è:˜t…ã¿Œ)Jdş9ËÀÎĞ€ñ¡.à_¿ağ’6¬ºqR¢§K®8ßF8/n—¦«Sæ´0Ïè=È\rÍâq/Œ£ê™sR\\ËÃõ m”4„hIÜÜŞc&MÃ“YKª00†cöBk/f,Íš˜qÎ!tœÃÂ³ƒI}dô!wVå¡24Ğ¸Å›XRÔCa×~¡Œƒ›„0 I81ÁÁV˜£®\n\n“CTgq?£WÒMÈi7hFD@ä\nqÉQh¾³@îxab÷W(½4À„ÖÄ##aÁ8,¸hN9ä;(”AÆˆM	±E(äyUkQÙMˆÖ@eºVV¡ğÀÔ†¢ğZê3Ä\\<›'H£\"Ù9\rÏ äãÈC©X+A™bæàòaÇiš.Órca¾u`0†¢†xS\n‘¼Üš3Úy\$XT\\\r®®2>PREë^5BRKİlFo–<#¸KˆZ3AA”ƒ™“É‰Ú…lVK³pÙ¡JrmEÍ…0¢\nÚ7d°#@ G\$ÛM{ŠµöÙJNC‘;y€)A@Î‰™ärk¦D+óFƒ™e †ƒP†0K(aZÊ ÍQ*\nh9Y¡&Œ©PµÔ|	œF¨\nC• ÚùÃI.%qÑw˜ş¡—*ç¥¥˜S)˜¤£ñ«Ğ!¥ğØ\nåbÆ\rdİF‚\0ì_C{O‰Ñ‹ÔÚ^fiò~Å:Èˆƒ‚¨TÀ´!rOL±=RAÊ?(ÆÌRå­uµyÏ¨¬d`3#³‘£B°–Å18È™2ìièy`Eb´\$™’²ËAA¤4©ÂµPªUDÅ’êyV5*l\$r[\$†ş	`\rì!WUéŠÆ!ã]“Ôï:©}\n8©ÊÛ™“ì“}/€(šÀ‚ŠÉCyZ<êâ˜¢jDÔeAP¦‰HİUtÒ¸°…Q³DştB+•U¦z}Ås:Ã	!:ñøåXdndŒ¥¦æ¸&C6zZHt";break;case"ru":$h="ĞI4QbŠ\r ²h-Z(KA{‚„¢á™˜@s4°˜\$hĞX4móEÑFyAg‚ÊÚ†Š\nQBKW2)RöA@Âapz\0]NKWRi›Ay-]Ê!Ğ&‚æ	­èp¤CE#©¢êµyl²Ÿ\n@N'R)û‰\0”	Nd*;AEJ’K¤–©îF°Ç\$ĞVŠ&…'AAæ0¤@\nFC1 Ôl7c+ü&\"IšIĞ·˜ü>Ä¹Œ¤¥K,q¡Ï´Í.ÄÈu’9¢ê †ì¼LÒ¾¢,&²NsDšM‘‘˜ŞŞe!_Ìé‹Z­ÕG*„r;i¬«9Xƒàpdû‘‘÷'ËŒ6ky«}÷VÍì\nêP¤¢†Ø»N’3\0\$¤,°:)ºfó(nB>ä\$e´\n›«mz”û¸ËËÃ!0<=›–”ÁìS<¡lP…*ôEÁióä¦–°;î´(P1 W¥j¡tæ¬EŒ£\$Â˜ìÂŠ’´ƒ1ÚU	,òTúè#ìâ¶‹#Äh‘Ò¾Š²äº”‹Yvš±j 0Œ2ÏLZjÿ¹n;†™£+»èÎ f„˜‘IĞòA­ãPhîÒ‚¿£\$¥ÜÊï2^\$}\"¢9	¡°¬på1a I¡®BÏ<»TÑ¡\0;-ö\\#.¸á8	\$©ÔËÌ¼ª\$bd÷Òhj£Wô<õ`µ/Ä8Œ“ÎrZğÄìğ(u³º”3•kºô´é„£#V& MËs¯¯ÕÕs\reÚÄ½ôÙan±€PH…Á g~†(Í“N©’ÓB×Šò^%U[=ÕõÔ9¹2A M©ª{£MÑV”[ \n¤Œ¬bÅjDWé)ıZ	¬ú†©÷¹ O¨o¢—1ÜJŠ¾Ù*“y¢œ¯´!@4}'S*¢G+@,KÏiÑ2ªãxV,„§—Áphi+—)¾ú|Y¨½Ú~ĞªYµ®iKŞ›AAo‚²İwdîŠ;¶ªš4ûÄŒÑ¥8th)\$ç\0(Ê>ZÊrœkÑgÉ²%ã#ÀÃ`è9Hµ,±PiæşRÍ\$ÿ¨ª\"ÑËJ.,DŞLİU†Ê«Ö™&-†ú°Ë·‘@>îAuÒ´‰Q™¯:{‘#J’˜÷9(%?;’Îë*d–Aípa0—X_ièr}º„PôûfÚ÷Ûà¸~\$¨Xxøá=y›ãŸ(>ƒÂôKÕlÎ8ú÷´M„[İ?Åğ»7ùs¸})õ'z Şô~&)Ó!§èJŸ³Êrï5ı­—üOİÛÔ%p\rì@dî¾ÓNˆc#LXÂS\nA¿Â|:ÑiÍÆ3ârIHb˜d©ä¼JË© fuqÆ¤û_&&ñl¶‡ö¯^à\n\n 0†àÖÁ\0f\ráÈPæCpg2ØàC,Œ7†èŞ#À „€äC0=EÈàÉCtœğÂ“ÂzxpÅ¾C2hµÎÙƒ('Ğ\\%sœWz{)µ©<õ¼xqÅ[2/3\n]JJ_d,®#”²Ğt^•é”H9!äH\"\rĞ:\0æx/óLëÃ.aœ†Pİ7CÀt›Ñ¾>‚ùÁğĞVÅ@¦¤÷ôBd¬—v†0Òç›ëğHÑL ‘ó¸_Ñ<LvÌ¥iQX!»yT’ˆ¦ÊT4…%a¦5pXéKÂTDt),%1†P@C’ €;†Ø\0b£ÁÂ<F äC*æ!šqFÀæCc¡Ì9†`ëJ`oñÂ’èG£ÁÌ0Ó÷!ä\0n’A„62êuO*G\$é…¦B«œ§ Çe² Ä¦† ‚V«NÆ® Å};YJŸÅ\rD>Ş ±µÈ”p‡Cpe¤2N¤†ğ@iÔ˜2†zcaãXt¦4àŞ(ı'\ráŞ˜ÕrØ‚„XOA7±šŞèT*Ä9Q}c¥wI/˜¢Á.ªH’™ãèäárôv„„¥Ê7HVµ´¤°ŸAFæ	T\roMúX—ë®'Äî’¡Fê®Y(JIœŒ‰R™RFÔ“µéÙ)FŠ[i¤¨–„\"I­rY‘ÚÀÇ›Ø†ÓÓ­>¯m8dJv„4·uçiğ>&“Dc±9Xö\rÌ\0P	áL*KÓ„Ïx…~¨0»Jã•Ú«…ˆ©PÃ õ¯6º.?ÎÓ+Á8|ìËòZªïõÉ/\$<œÛsÉ1M9Èü¢?[UÍv SÚ(ä·rrR\\/†ãZ´k0F\n,aL(„ÈzĞ\n©<wÀ€#@ ¥NºÛµÅ=&¸–¥!¯” -”y‹Ğuäµª’rRîgb7~„g=…ÇYÏj†º—=gÅÆ\\¨æ=iÕ”MñˆË]-‡­,bÁ  ínÍYµé«Á§K>Ÿ,:‡G+!q©[kLÕ¥fë¨³é\r95Ğ,\nİ	B›\r€¬h\n˜j§Ş\r‚ÅwˆæS}î-DÂ:|2¸5>ètºy\$Ğš²6¾MÄ;HĞr²;!üP©Áu;6ˆ'œ	mÅlÒM<f·¼ócÍëy©.½ø^bËkàû7&.İ7xVyŸA2ÙmPu¨{J}n	\rí*X7´İà¯5ÇEÆl½“örkpe¦\$÷;È¦@RWİo1mçÊy½½/h>TkšÅå§öµƒv¶t¢m±d!W¶õNİ%]èÒBK÷±%œt¡##Ç±Qbb—ÄÙ¡UˆŞ’YmZ‰¥S<à€t0Î'rÁ?L»ƒ†)•}ığäÏÄñ#†å™ùT@Mqzİ¾2±ÓšŞğÍì •ç‰¹ä0Bƒ&Å—#?nÃ œ¡\nËı^\0îÊ½V<\$ˆîÕ–%\0";break;case"sk":$h="N0›ÏFPü%ÌÂ˜(¦Ã]ç(a„@n2œ\ræC	ÈÒl7ÅÌ&ƒ‘…Š¥‰¦Á¤ÚÃP›\rÑhÑØŞl2›¦±•ˆ¾5›ÎrxdB\$r:ˆ\rFQ\0”æB”Ãâ18¹”Ë-9´¹H€0Œ†cA¨Øn8‚)èÉDÍ&sLêb\nb¯M&}0èa1gæ³Ì¤«k02pQZ@Å_bÔ·‹Õò0 _0’’É¾’hÄÓ\rÒY§83™Nb¤„êp/ÆƒN®şbœa±ùaWw’M\ræ¹+o;I”³ÁCv˜Í\0­ñ¿!À‹·ôF\"<Âlb¨XjØv&êg¦0•ì<šñ§“—P9P¼fÙçĞÊ96JPÊ·©#Ğ@ Ã4Œ£Zš9ª*2¨«¶ªÒ¸\nC*Nöc+¨È<nKdŸcY†TµƒÈà<F!ñc`Â‰‚´ş\"Î0Â†ˆKª`9.œÆã(Ş6Œ££2ô I˜Û£ ÒÖ@P ÏDlDŸÀPÕ\$²<4\r‰€æàq˜¨993,PÒÌ“2sBs£MØ×„£ @1 ƒ >ÏôóAÏÔ\0ÔÖÀPòÕMÁpHRÁ‹æ4'ëã”\rc\$7§éëä-\ròT)1‚b])BÖ1¯o˜áSâ(Zõ£àP2(PdeË¯Ä\$“Æ\re~¡FL`„›„İ 0§Eê›?µØÂ…ÀM¦6\r6­Û=¶[µàØÂÜIºCk]Ë¥uİ¶ú@;%-’J=KÂ@t&‰¡Ğ¦)C \\6…ÂØå‡Bím\\(ƒ\$m’PÎ©(ê˜7ŒÃ5ÈŒ*/ú{*×ó‰C3±‚ Ş½cpò£pæ:Œpèæ9ŒÃ¨Ø\$¦l5ƒ–f0ŒöøÜ.·XÚº­P9…)HœŒØvËXá§!\0†)ŠB0\\Yï\rÉ\0ŒÈ˜ÛŸGá\0ôÑ¹)NS2åsš˜µŒ£Ë2\$#°@3# Ö:¥\"£@7\rm^ÒŠ³8Ü3„É§£–‰fÃMDŠˆ²H2ŒÁèD§Aõ€™8xŒ!öK#~–;EC“ÂïŒ:\$áº+*dÓ¿¿#›÷·n\nR•÷¨ ácº'n€Ò9@{:sıD4ƒ à9‡Ax^;ürùÊ?ár&3…éÏÚ<6\\Ïdôc˜|\$£‚q\$\rÃ§VëPx Y µ¸CYLJmüÿ³rzgvO\0002¿ÂtŠZg\"Áˆ<”BjMÉÊ'p¡ ¦Æê`˜w#ìı½@ÀäâÒB~!™ÆpÎ™ã>h½¡TA¬„„U\$\0Æõı/a±Bö_LÁ<'êäèbœ­.¹–)ˆG\0rH½^çä=E1[O 9ª€ †ìˆÄ'<ğÒšpÊjSó€†¬Ú‘uRpCa ïU:µ€Â\"û»Gq¡ZÂ Gºã‚‡RØB¶Y-%äÅ™7ù:’e16'Ú §“ÆØÂÑĞ§…\0–³ô+OáN%!\$…‡“=ÜÔ†„0œ§è!^ q¦ªa†cò“è\"‘®†2@Íœ…yf¤”Ÿ…–bÅ	<Kˆ™\0Â¤[bòÁş¦S°¡/ò!¼’õ,¥¡L€…½T””‹˜u8²x5L¨Ã¢!fréËğÆ¶£ú\råğà§òæ˜Q	€‚€‚x^Á\0F\n@à†äüK¤L™³>b&ÇÒ•—‘Oiü›òzÈv›´Ä Aºjph@u;á¤=FŠuLĞa)ÔÑÃwE	)P‰äëS˜vAä;‰XJBJ\r€¬5®@×	3”É”Vœb‚Ñ(A]GI ôÿ‘)Ä|¦A™‡ZhU\nƒ…Ø‚HÙç%2j”5WXz-©9p®5ËcPF]JÉ\nŞR}•^M1üØë5¢\rœ²v€ä8S\0`ŠIwäb2‘Ò>ßQS2a6Ä†dYQJÉ¯.Z6ŠRF‹\ry%1Gİê6çøRM’‹\$3ìÑ‡FfFƒ©×@\$m= k’y‰9)	”M?Ò'<\$z3¼eğóä”LB3#HÅ­˜ìS*Ú–WÑ`%àNˆ3<Â`'PThS¿– 0ÉÅ1èØàş\$›S¤qŠ±s˜mí‚&'HK^`Ñ˜RÀˆ°ÃÜ£€\nI( ";break;case"sl":$h="S:D‘–ib#L&ãHü%ÌÂ˜(6›à¦Ñ¸Âl7±WÆ“¡¤@d0\rğY”]0šÆXI¨Â ™›\r&³yÌé'”ÊÌ²Ñª%9¥äJ²nnÌSé‰†^ #!˜Ğj6 ¨!„ôn7‚£F“9¦<l‹I†”Ù/*ÁL†QZ¨v¾¤Çc”øÒc—–MçQ Ã3›àg#N\0Øe3™Nb	P€êp”@s†ƒNnæbËËÊfƒ”.ù«ÖÃèé†Pl5MBÖz67Q ­†»fnœ_îT9÷n3‚‰'£QŠ¡¾Œ§©Ø(ªp]/…”ôÒmg¼Ó’e¨ææó\$Ÿé)„Š]6†ùªkšl—°Nã¼õ®ˆc®5®CHà¾¥Ë R˜:¨ãh„Œ(¨„·#’	¨*Eˆã(Ş6Œ£ ä„Äb›¶\r«âò2¨Ã²>†\rp;Â1AE\nxÙÅ€TR9¤\0SÈ4¦Ì(2B£Z5#Ìœ˜ÇÂ¢pŞı ƒ(Éˆ{ê1&# Ìs(Ã3Í#RK6\$İ-CË4-\0PHÁ i>†.“®„¯ƒZŒ9'‰Óˆ\$²˜&<é\$*\"GZŒé:ƒ+(¡hÚ0+€2BÓl2ÇRS(ğ\rÉl””-ÃÆˆZŠ£¾£-=P.àH¡Z!iº¨ı×5İB«WÖd9²S@A]SõÒY2C\\4Vö%Ÿc\$²^=%P\$	Ğš&‡B˜¦pÚc•ä9´ÂêÊ o¸éW\r‘pÅ1‘\n¢3ÉÒ ÕEÃ,NîÔrjL9Ê ŞÉSãpòÏ\rÃ˜ê1ŒlĞæ3£`@-¸ËÓbãäÚ6–tj7¨P9…0%P»I¯ƒ87¥®@†)ŠB0\\Wî½dÑÈĞÛÄ!®÷Õ­hè—Érjn:c†P£Éx¨ÇU¬Ş’93Ã› 2úİM=#ßŒ#%4‰»X2ŒÁèD¤AğÉ·¥.€xŒ!óUJÛ/YZèo#ÌŒ½5d¶´Iò¶€¥âl:€ìƒ„:9èÕr<AëÖ‡º±¨.ò\r è8aĞ^ı¨\\ÛeôĞ\\á{áßá·…ûØæ	#hàËÅƒpéÁğ¯Û70\r¦vSê\"HÓX«á©Õã\$ŞXQÂ0ğÎ)\"Mã8Ş„¾\n´¢4 CÆ’íá\0ïPäK¯¸›,>¡„39Â(ÆØèscì…‘¾ÓhşÃ¢Ø‚\$9†Xz{á¤‡2^ˆ)½oæ„ü‘b¤Î±à|AÈ’­ˆF~Ûäh (\0PRI\$ÉT;ópÉxCmåAû¶s:AÌ±˜KËæ³JdIù\$İ/3br“J“9ìufMH×Bj„É7øzn€i[‚48’\nÄ	ğˆ\rò¶g ¾bù\nMÅ\"&LY'n1A'†ãëby!ÔÍğÌyHãiw-’	œ€ÆH£‰îxÌµB\nNIÙ’…PÀı¢nxS\n-1PÔuÓ,a1Œ/˜IHù&<æX›S”„á‘ü_ò1«c>H\r° &ç´3‚\0¦B` Ê„n‚¤5!gÔó²~öäz+IáÈ™‡)<—“Ù\r'PÊAPİ9Òú-q\$ôú(Ñ;ÕTèE¥!l‡bÿ=ÍR]y¥äe´—g‹S!N\n°‹/IdPÂ7C£IY§Ö„&§BèiÁ+bƒ,ê:‹RÜAEÁ°†²tÍ©8/E ŸC êoÔ±.Aj=†°Oßñ´\n¡P#Ğp³Ãsn/a¸3—ˆ{R­%>¦ÃLU¨jx¾£+\rgUBJèıX\$5j,¬ÅµWŒ9.5Š¨F¥³WL-_fÏ`T5;	œ.…Ù(ÓèC0y,†Ø˜¾ñ”‘v~É09 ÀMÒò6A´ÑYA¥ÙÛ1ä\nSJ[¬¹4F/SBJKÂ`o™\$d‚”æ£íUK:\$\0ØªSlpP¨C.À€Ö‡¦B\\Ó\rpª©®áUğÒœ+C¹ÄÃœJøıÒğ\n	d€†(ã§P5b@Á%AØyl~à¯EÕZ†„£^Õ©}¹ŒâR\0";break;case"sr":$h="ĞJ4‚í ¸4P-Ak	@ÁÚ6Š\r¢€h/`ãğP”\\33`¦‚†h¦¡ĞE¤¢¾†Cš©\\fÑLJâ°¦‚şe_¤‰ÙDåeh¦àRÆ‚ù ·hQæ	™”jQŸÍĞñ*µ1a1˜CV³9Ôæ%9¨P	u6ccšUãPùíº/œAèBÀPÀb2£a¸às\$_ÅàTù²úI0Œ.\"uÌZîH‘™-á0ÕƒAcYXZç5åV\$Q´4«YŒiq—ÌÂc9m:¡MçQ Âv2ˆ\rÆñÀäi;M†S9”æ :q§!„éÁ:\r<ó¡„ÅËµÉ«èx­b¾˜’xš>Dšq„M«÷|];Ù´RT‰R×Ò”=q0ø!/kVÖ è‚NÚ)\nSü)·ãHÜ3¤<Å‰ÓšÚÆ¨2EÒH•2	»è×šâš“²EâšD°ÌN·¡+1 –³¥ê§ˆ\"¬…&,ën² kBÖ€«ëÂÅ\" Š;XM ‰ò`ú&	Épµ”I‘u QÜÈ§sÖ²>èk%)+A\"ÅJ©\$†<±t¨±KVØ2Qú01ÑLêhÈHI¦JtACÉ`’)Q’ŞÔN¯Ò\$Û½rôc0«K!|ø5HuÄ	²â’ôJs!PFDÅ<ï”£S>µJˆÛ)råcQ„£\"Ï¼è`\\×j,ƒ_×Lõ„—¼É\$p’.`PH…Á gh†+]JüÑÃ:YÕ,û\$  –âZÿü¾¤?o=V&ĞÉ\rT’	™£wÃ5<*Æ3¬ûXÈ¤„ª\nJ+q ¤„ôn¡¥Š€¨ÔÂN§D&”*}€º²*â‚,eŞŒCQ¢òºJIâ\r\$ Au‰€ã/jhºc¸şK¬H‚+–dËik•bù))işedK6q…-ª3¥ \$	Ğš&‡B˜¦ƒ \\6¡p¶<ìÈºÑ_5÷9ÍÂc—C`è97-Ø@0NŞ3Ãd\$2¼*B1'\"ø,oEY\" Şâ\r£Ü<„¨Ü9£Æçc0ê6`Ş3ÂC˜Xè\\`Â3Œ0AÓXl\$:ºá@æ­j\"Ç!Ğ2|!ŠbÅe¦?k®Ğ\\n›æ¸Ã¬Sf‰	s—ˆªÖQ§‰óY\r«½ïÛ¨B£|7\rnxÌ7\\hç	á\0‚2uCpÊ9sãßÇ#'Öƒ{ºŒÁèD´AğÈûÎ³ä€¼0ƒâÖºPÒµ\"&°ˆ¯UjÑ‘1o,ŒÏ“Õ`ªË5'Œ‘§ÇP‚hag]ğ‡DÃ»àWA”<\0ÒİZÓô~Á¡ü? Ğ p`è‚ğïÁpa}\rì9çÀÁxe\rÑ\$<wàûÁ{û`ø\$†ĞàrÃlJ‚Ğ\"ÉÙ\rêèèÆ\0ÂÎi‡õ¸—Ôƒ¡kEH•”EŠÈ¡Q¤Äø¶’/RØƒ …@Á²y™Ø\n{G0†0è_x ç)Ì#‚[ßQe]Í		‘raÍÊ¹w2æİ4“;'@4æbÈ pÂ-Â™k3|¦#ÂÚgUĞ(’!á£”vÙcƒ#\n“q\n€H\nöb¤˜»Y½K‡”Ÿ#’„ß{ê‘ÏèCŒrQÌªè8>\0èsÎÙÂ\rñ®[¹ ï:İ‰2vˆ4š›STZÖOæ”¯¡â2I­^P5N™Òk•Úl=ï\rˆ<¢Ub™ì€ª	Ñaª@¥aÛ+Öj‘€PI\$!å¹Gã<£Xs‰QíöêC©Îa˜9ğÚù¢ê|'f%K'5L¥dñ„Ç6:IHI¡\n<)…I’ÚŒá3cñÍV#IŠ‚¨!)x„Õ´Ëòf§4}En\0¥Rbj,“ù-O‰qŞázòˆ”y««ÒÏÒoS’Úf¤‘z\\·xÜSŠá¾!70@ƒKå\naD&\0Í:AÉ~Á*M7®ƒLVÎÓÚLƒ‘Àˆ§¦kv76H‘P*\$ˆÌÓpImjL¶HÒÃMmÅ\r¹.1~ØOê°ÊHêºVÊ¢×ÁĞ·	û£,¥•Ü²°ËÙ´‚x×AI]6wuO¥ÌA6Âç–ë¤cfûo\r€®	‘!tš\rp©ÕÈ»ÉĞmâ£/8k0C¬#±ñ¤7\0ª0-\0‚%>ê\\„ÊÀ®4 ‡Tœ \n«<¹WÎ3|,L©òÃFÈ™Oão…)Ä)ò|—CJS£É^S’³P36b×Ñ\" (&Æ0ÒƒÉ.­5aG¥<45ôJhrüšµÚ¥­¤|I¦e›¯Œg~ˆÎUV…ÒùbS­’º+peX™Ô\\^I€ˆªõš`½Çns5¬[&,«‡‚A¥bQc@S(\\H‡Ï(»H\$2äçôoupà°ÑyZ(°¦N\\iºn\"Êºz‚ ÜÔ<‚ÃVÂìP5ª_2‹±‡ªÎ9dô4’%•òh	  Ñ‹\$h=8™H¿";break;case"ta":$h="àW* øiÀ¯FÁ\\Hd_†«•Ğô+ÁBQpÌÌ 9‚¢Ğt\\U„«¤êô@‚W¡à(<É\\±”@1	| @(:œ\r†ó	S.WA•èhtå]†R&Êùœñ\\µÌéÓI`ºD®JÉ\$Ôé:º®TÏ X’³`«*ªÉúrj1k€,êÕ…z@%9«Ò5|–Udƒß jä¦¸ˆ¯CˆÈf4†ãÍ~ùL›âg²Éù”Úp:E5ûe&­Ö@.•î¬£ƒËqu­¢»ƒW[•è¬\"¿+@ñm´î\0µ«,-ô­Ò»[Ü×‹&ó¨€Ğa;Dãx€àr4&Ã)œÊs<´!„éâ:\r?¡„Äö8\nRl‰¬Êü¬Î[zR.ì<›ªË\nú¤8N\"ÀÑ0íêä†AN¬*ÚÃ…q`½Ã	&°BÎá%0dB•‘ªBÊ³­(BÖ¶nK‚æ*Îªä9QÜÄB›À4Ã:¾ä”ÂNr\$ƒÂÅ¢¯‘)2¬ª0©\n¶Ëq\$&‚ í¹±*A\$€:S®·ºPz±Æ©k\0Ò¸Ü9#xÜ£ ÊU-¬P¼	Ju8“\r,suY©ËÔBæÀ.Š­'â˜èôI-\\µªŠÒW\"¥u,ˆÍ±‹Ÿ·(²­J!\nù€7\rê/Ö‘<›-Ë2W*ÉÃ{cQkRÄTÚPãÖ+C£+ c@Ù¥+ä-VÉìòæ·ºæ³Ô­äbã(Ş6Œ´ûTãÛíêéÜ­õŸBÒ\"”¬µM)^ôH«T÷õ§¨æ2Uá“ôTÈŞ©P³ajÃ²Y%Î\n´™ÓQ	T×db‘&GXÑ‡bRkHÇV\rÉĞSluH`Hæ_£c\$ehÛ\0HKŸR9Y]l6PÆJ¼ÙãËŞ<€PH…á gª†*û„›G5²\0L©Ë\rP¦Î‘.U–tl±ÁóyWÄ1DÄ/±B—–D8¼„›[“|Ü¥LØ	)ÅJ¬O.­~ÖÆ¹¾éTõm áÍÅz¯¦ÕÎœHz#Ë¯D1ÎHš†·Æ©O\rñ§µ•ÜgF².7ŠSˆÒìH]¡h’ÊŞõhZ”®ÛÎÃYÙ]’û}Ôh9ÛD2 à&İ'(WcYŒˆ\$	Ğš&‡B˜¦ƒ ^6¡x¶<ıƒÈ»­B-·ŒÒWü«z¾1JLÊ¦Ö£e>Ùİ!ÈñğÌƒbH§p‘hU›º ju†gŸÚª-ép›,€yå\r¡„7@SØuaŒ÷‡0ÌC` (a\$0X|C”&!œ0¤€A0mIÔû‚€æ\nJğC\naH#ƒfa”B²D.¤¤8ÌV›B ¹-ôäNa¹té5\\ärhÕs± 5æ3“ÙˆDƒ+”“…ÈH‹#‘¤Ï´Åè³#²ä\"³ÇBtî ùß\rÁ¬ø`Ş¡:|I € †H„˜d7iù>Fàa;Ğ3ĞD`>‰øû\$€ÎxaÈ‹v²›©&‘fœ¸ÿ[¿)¤¯(À_bá£IÂ¹\\»xáYA\0eMàˆ¿ˆĞW‚hagŞH	´Ã¼gÁ”<\0Ó\$\\”2Œ2ÊPD tÌğ^çÀ.(²]†éÁxe\rÔ<Ğ\$øŸ|§`ø\$šsØ»pt–È¿ŸØ~C{>>@‚†³ÄC¡æaLQhİÅt¶Šk(Ğ6„Ø¢Š”ÔIñIç;F ƒKã™ŒÓ<¯@Ğxƒc‰ô7\0îzáˆb<AÁ†Hàä»ğaÓpB¨Xát0†A¾TZ~O?\$…v\0Ç:(C!°9Ç&Ú¸H(œ‰è9›±\\šİPZ+¯cH‚€H\n\"–ÇˆŞ›¦#\0NM)ä0ƒ^Î¹½7à(!§é1P¥qñ<§œô³ÚYğp‘áĞø³Æé\rk(aŞĞ)–ãäÒ*1½¡¶Òˆ`Š‰W‹*È˜ã²lkÊ‹Œ\nã¢úlÎ£2\r5¬Êš<ÙŠñj®u¥Á§U¼Ì)ÆââA'èË¦Y;¸Èiè4yĞ¢a\\I¥‰¸†„Ó¹/X¥9dFŠ*RáÕ¹e¾ê¶êfç¬:É (\$‘ğòw\0d\r,øòR¨Ñú>8‡SßHC0r]rRK@© ~hf(l.°Úi¼{ƒ¦ˆŞ_{«­“&\n<)…KÒˆn¬½¯Â6dœâ¸yÕ_F„–_üs3ñf;*®æøár\"ûsÈ¨e~F Q&Ãpf\r!œ:‡)ZŸ°Lç¶`ğ×¼a!…ëyl»@á\$'(E¨‚\0Å–Á\0S\n!0e£ØT¢ÁRÁÂF|M=£‡øc\rÒAgêX~Î9\r4•„Æ#\"WEÆãÜ÷¢‚¢Ş’`­ç±&Ì5Ó¿—šŞMM1²f¡P:NéÅé„\nI÷\rµíê4¡«5®¯v÷œ¯:mg¨ô¢üÖÎ,æ‹ÅW‹FcòùL;ÂÍ]®ÊºÆÆœÏ}ŞÑÓ‚)½;(§Ã`+p\$1†°A˜%v­xZÍQÊ2C4&¤x#Yê8fŞv£õ*…@ŒAÀ  Ro\$ŠïqÕşW~«½pÊÂ×\$9MyS=2Ê—9£Übîö1WZh<ê\\Ø4ì8®8l(–áëzh¸ï¥„§éÍµ§¹.18ã“MSn´oÊYõüE^@S•,cGLï‘X“o@\n	»ß|¹‚u9k&ëÛƒ¨¶~åÊôŸ… Ê2ú{aÈM%\"m´ñõ³\"\\×U;ó«·Ö9†ëá ½góN²›¤ïÛ_’%È¡ªôŞjÀY´&gMí0Iö +Ê”Y\\¼9—‘,ê›6ŒA’û.e1Á£H²ğz––,ïáÌ>VYÎ¦ÖÉîW–-ªåÂŸoÌgÜ2\$¼~DeŞ·eL0á!Ö?4ã-¾¾f’#{­òúdÿ›Ú>çñıİ~¿]ÜtdBæ¤\"æéDÆb¥.‰¬?1sKz×ûÜ§ïÜ\\kZ(±¿Ï&ıeò1 ";break;case"th":$h="à\\! ˆMÀ¹@À0tD\0†Â \nX:&\0§€*à\n8Ş\0­	EÃ30‚/\0ZB (^\0µAàK…2\0ª•À&«‰bâ8¸KGàn‚ŒÄà	I”?J\\£)«Šbå.˜®)ˆ\\ò—S§®\"•¼s\0CÙWJ¤¶_6\\+eV¸6r¸JÃ©5kÒá´]ë³8õÄ@%9«9ªæ4·®fv2° #!˜Ğj65˜Æ:ïi\\ (µzÊ³y¾W eÂj‡\0MLrS«‚{q\0¼×§Ú|\\Iq	¾në[­Rã|¸”é¦›©7;ZÁá4	=j„¸´Ş.óùê°Y7Dƒ	ØÊ 7Ä‘¤ìi6LæS˜€èù£€È0xè4\r/èè0ŒOËÚ¶í‘p—²\0@«-±p¢BP¤,ã»JQpXD1’™«jCb¹2ÂÎ±;èó¤…—\$3€¸\$\rü6¹ÃĞ¼J±¶+šçº.º6»”Qó„Ÿ¨1ÚÚå`P¦ö#pÎ¬¢ª²P.åJVİ!ëó\0ğ0JË¶Ÿ­ˆ2¼\\Ì+ûbœ:HÃdÔ­IúSÅ’K¤ò¥QZ\0QŠL\\N|å9©Ã†è7…Ã[%BŠ#bğ£Qi(Ãp{°°*\n”\$ìÏÅÄ“&Î4€‹Áî99Eã·/'ÊÊEÄ“¡q.Bh8³0b76\nzLµğŒ…M\$#;rÍjæÎRË\\ƒ¹²Ê¶H0KTXC¹ˆfŸÆL}¶€ET}EnÑjÚz™ÍS¹*¬¼“ü””jÍwR9.Û‹9VM~ÇÕxäÍ³„…^\nLEWsŞj\\“{E,î’BÒ ¯ÑÈgyÈõ´TÚbx‹ÇPÓƒ,ÅŠªg(ÈîFíŞ‹í*ë\$#\"LåCIr¢/àøA j„«(b®Ò¶rºDÎÚ4é[˜…ìÔL`\\cëlœÜã{•ŠÊ™³VÊìî®æˆİÑ,°±d0ïÔjvÊ«76î\\Rµ^#ºŠu;«»%¸k1ijÃç[Æµ½ äe&Åîİ¯E›´1”õK¾®«µ	@t&‰¡Ğ¦)ÙRr»\nT@_­m{ª‹mYpZ‹»\0¸ÉY¾Åå(6ƒô=Cä÷ã0Ì6J£+Šæ‘jmVd\$b³ÎÉ[öcŒCMÈ¨7¾ChÂ7!\0ê7c¨Æ1¿ƒ˜Ì:\0ØÃ:U`°ÿ'âC8aJ ‚‚VtR¨u@  9‚—¶·™Šá;êQF“Õ\0Â˜RÌìê—0’´[j\rÍ¦C¼¨^APL,İ€»ô”·Íº“Aï‘1Ó„™Èè7êØŸµÅF†ˆâ¡OK5Ğ€¨{pk?¡˜7‡'äÒ¨g2A\0ÜC”a½úÉ\0<'­é†`zŒ@\"Á’2 H²xaÅ`)¿Ó4lH²7>lE:ƒˆ\rQ5ÆDÄ‹Sdñ”³cxt‘c\\;páÃ Cs@±X8Jæâ«:¡à8—¦tia¢6ÆğĞ p`è‚ğï/Ápa‹¯d9èªÁxe\rÓ\$< XË|p`ø\$†Ğà~ClÊ‘â=)€A6P8ogGşo†Ö{ÃHt>q÷EğÜ¡55Y§rªÁT™‚ô9ÅˆÉƒAï!Œ:GğÜ¹ø¡ˆ÷‡Á”ÙgA„3J0@ı_»ùoõÿÀBP9ş 4\\0Í@å|Ú\r!„6)—\nd‘ÏCPµCµ¥‰Õ/:Ê5®SDú¢YŠw'en…\0\0(1\0¥A»Bà¢‰é;f§q_&)òp‘¡3*¥\\8Éè\$Y?ÇÈúcğ~ƒ+:T:Ô||ê¥ü;Ö”ñ\"-y&2+È{\n`Ú1í„®ÓW©!rŠ-®¢5âÂÒÉUSÈ°®–Âìp„3W§4í¶ùÌSI9VÉ¸¹2…ÔÁX	\$”<@#5pAÎeN4^˜q§òu`äÃl[˜q~+ y•Ißı´¤U¾RŸºî˜Ñd1kõ8éBÙæàÙ%<b(HÏv]2¶³¤)A“;Caê‰ã Y¤2QiAj\"±…Îî•:¶[¡±›Ğa³Xu\$cÏqQj©ƒA„öyípc}ïÊk†ù„#Xb\r1h)…˜5gŞ5„`©R{:\r3Z¶@Ë{oî\r´Gºb°–dÈ\\'‡©dÛ*hš¬™ŠCJÍ45åâ×aë5ÆÒ!Mó ñû3<n^¿‹Ü£‘‰™¬‡!’F0I™51öÛ{Ğ–‰9M  ó¶¥ÂÍ\r€¬1½€Æïi\09ğiâeöTÊÛN5¥`#a Úe ¡2ƒ…P¨h8*1Û¬V s;;i59c#¶WrñÍ¯;2*ŠuSË½Rf%[7C¾vÎ\rSO‡*­ÉùÄC0yÊ¥Üä2ôû\n“ûM;·¦Ã¶‡\nè˜r¦é}+ÔP˜ù±K)Üğ´ç,‰sºıÎ'9€I‡|OñÓZVieÚ¯\$%–Íù«3@øª{Ğ[’aE’²¥Q£\$KÁ’µFÒÌS‘;^ï+H¢](”ƒ)ù •¤¬¡FÂ÷Mˆ³+7Xï·b³¦Ö›×M=…HäÂWuK1<«%š}‰zÈ¿DËñ_…\$°XQ;€";break;case"tr":$h="E6šMÂ	Îi=ÁBQpÌÌ 9‚ˆ†ó™äÂ 3°ÖÆã!”äi6`'“yÈ\\\nb,P!Ú= 2ÀÌ‘H°€Äo<N‡XƒbnŸ§Â)Ì…'‰ÅbæÓ)ØÇ:GX‰ùœ@\nFC1 Ôl7ASv*|%4š F`(¨a1\râ	!®Ã^¦2Q×|%˜O3ã¥Ğßv§‡K…Ês¼ŒfSd†˜kXjyaäÊt5ÁÏXlFó:´Ú‰i–£x½²Æ\\õFša6ˆ3ú¬²]7›F	¸Óº¿™AE=é”É 4É\\¹KªK:åL&èYÌ@u=vÎ“ãa†…?2væÆ˜ÿ@kìùhúDÒ/á:L`”ÚyÒ„îS°í>c†:/’B¹lÈó-Ï0Ò45¡Â6»iA`ĞƒH ª`P2ê`é	ƒHæüÁh“`½¶ƒHÇ@o’} P ó\rï,Dö\r++˜¸ã@Âñ–0PÂ2#ß-HJ2&ÌèÊr¨òÔ©­ÜŒÁÉ PHÁ i2†0û†\rÑN1=‹¢<¹j\nÙ%Å(R”¬¬³–2³kèZ8\"ŒdD#ˆòbÕ8#ªúäºb=ºrÈËAĞ¨º2Ò‰K ÂÖRô\$WMRixÙOK(Ò6¹îX¦‚ t9¢\"Øó]\"èZ0Œ!hÎ3B²^ˆP86DàQ†C©¨ı`Ÿ,©ğæŸ>cÒN)£pê§ÙÄ:|5Ú‹5Ã„0Â=<¡\0ŞC«r0³pÃl7\$C*º#ƒÈ2'Õ²\"8ƒHÖ‹n†?#[†9^ñ‹mDVJ<ƒ4a@†)ŠB0R\rLı2J6±c.ó0ÎÒN'Ş”ª²¼Éğóvá-“·€)8‚2 7Æ ø@™â§l'ÍÜ–?+.T5Á-Õ:Ñ‡ˆ¸Ğ9£0z(¡|1:9b²ßƒ„105åâÊÈ²!à^0‡Ù~bÜpMôã¦uXêbğ:ÏÖ‡£È‹_Ï¤>7\"õpÍ¬8Èê^kŞò2¹#6Fê{Zå‹2Í¥š¨A«ë:ØĞ:ƒ€æáxïÙ…Õ‚É(Î¦=àğüÈ3‚ëƒ˜|¢¿iŸ1omÛƒŞø»vÆä:Ş-Ê;Œª›Ï¸·pÙs…x“CSêE¾0O›ßWÃ¬Äà6é‚?›µÛhóæè½ßêfW¡N¿×ŒrL)Mã6Ê‰	e‰\0™0×ß U<!¹\$B\$¾˜i!_(€ r ÔAd0PQXúm3iÀŸ²œT\n‘T\rŒø<pÆ[áJ¥LùAPÜaÌaå<î2‰óäÙÈ¹%„¸½ó¢II:#€áÉŒ>°ÚûO[ğ~A¤“’’V@âa0 géu.Â.Ü’Pt €0­QbªØ 	ıÀ7‘Hô#=Ç™\$D2NbŒËØ{@Ÿ»pä	á)S„x¦ğòÒ!£Dğ9˜%ÔÚ8pHïIp®€Ä^Éx3@'…0¨kHùo‘„<œuÖ[A&§¤¢ãË%	#ÈƒàêC:İ{Ç˜â6TG\nœÁR’@Ù/×lŸ’„^>äâÂˆL*'Ú5ÍC’gŸzq7áÍn­ğêzHn5ÇÆ9Ã\r'4Æˆ9\râ`0¤d^#)•dóJ/\"X–X<ú7F‚1)õC\re	r†Ï€„œcÄ-†)OÁ.èÌáEç´×4NZH/é@7R”¨TÀ´ğŞ+ [ÑÏÃ‡ÄPI=JuBÙØÍc{<`JŠˆÔ:@±ê9çõ—ç\$!³V!›#”O	òCHt²¡Â<ƒ%]/ì‰õFàQì£í,™V¥óaêÖi‰¢#gÁ2é]k¶0—ID_Í‡H‡I‡•Ô™Én/\rÑF¨…>¨z£3é>}'ú²¬BXoYEËR\$\$B¦)œpƒj™oªÑ\nZXY‰UK”ù0‘\nÉ9BğmÕq@";break;case"uk":$h="ĞI4‚É ¿h-`­ì&ÑKÁBQpÌÌ 9‚š	Ørñ ¾h-š¸-}[´¹Zõ¢‚•H`Rø¢„˜®dbèÒrbºh d±éZí¢Œ†Gà‹Hü¢ƒ Í\rõMs6@Se+ÈƒE6œJçTd€Jsh\$g\$æG†­fÉj> ”CˆÈf4†ãÌj¾¯SdRêBû\rh¡åSEÕ6\rVG!TI´ÂV±‘ÌĞÔ{Z‚L•¬éòÊ”i%QÏB×ØÜvUXh£ÚÊZ<,›Î¢A„ìeâÈÒv4›¦s)Ì@tåNC	Ót4zÇC	‹¥kK´4\\L+U0\\F½>¿kCß5ˆAø™2@ƒ\$M›à¬4é‹TA¥ŠJ\\G¾ORú¾èò‚¶	‹.©%\nKş§B›Œ4Ã;\\’µ\r'¬²TÏSX6„‹VZ(è\"I(L©` Œ¹ Ê±\nËf@¦Ü\\¦‹’š¦.)Dæ‰™«(S³kZÚ±-êê„—.í*bŞED’¡~ÈHMƒVƒF: ‚£E:f¡FèÑ(É³ËšlÉGÔ(ß'R½’ªdX#Dš#Ïa¯+°a P ó¼Öøš\r2¨	„‚Sdî—Íì™ìš²(Äb4QĞf„øU‰‰x·)¤a®¯dˆºÌÌT«C)\\Ò ¢c\"Ğ,IxÙĞu¢ÏZv­¡[U\rñdWÑú4—ÀPH…Á gt†5ËD5eÒ4XÆj8Zİ¡(Õ1 ÕDàÚE­\"™JÈ},JR±§z+5üP\"…£hÂèµÓ™iµ*jHÿ©·‘¡=¿³å7c%q¢é<dTäeck„Ç/4)j•j?L¶,¦£É&…ÀJÒÕåªNK˜FğÒÍj°õ«üª49Ûsçùb•¡7z\"b†¤ù¥3\r·Ú^r‡1‹ZSa¦oFš˜th»f×L(Ò˜ZÚaxt¡½¸T]†ÃÂZˆC`è9N†0N@Ş3Ãd@2­tÅ…Ñ;õo\nƒ{—‰\rÃÈ@:Ã˜ê1Œn¨æ3£`@6\rã<@9Çœ?D0Œø˜ÜD8@6Ä«¼aJÖÍj´\"ê†Ù¦)Ìî;°È\"9¡®’šŒºRdbÁS+‹)¢D¡0\"V[­hÔƒDº}\${I®Ì›(R“¦	à¨âÃ[¬ƒxrtaÍp@C#À\rÁ”9#ÀÆİ(i0ÀÂqhf ˆ³‚ | İ€ ğ†|åPzÃ7+%§2\\dŠš7*\"1\"äó1.¨é&ôæN™2¨‡\"Éú*r`fÊzkPA“ÀšC™Ş€AÂ(0ï\0VpeÀ4¸ĞÈõ`´°h€è€s@¼‡xÜƒ	raÈ@ÎÃ(nàïA ààsÁ\$6‡¤cÈt„™,;ğÊxzÎ;ü0†³C¡Ì‚DÂ…„WD»5c…L†®ESÜK%\$X6 şƒAÈ!Œ:˜ ¹ÑuÁˆä\0”ˆYÁ„3E\"0éİHsunµ×»{.O×–\$`0È€@âô‰\r!„6%•ŒfÍ¹ülÉI*Jåd4\ró!I¯Ì(€ gQJfÍE%pPYÁL=|„œ§5ÕPŠ¤¨Ñ¨ˆøk:ÏònA ¸“À‡ \\´€§\\åœÓtN˜eYÁÂ\0‡C¬xHo“3qØz4ñÈqûl¨ùXñ,RÚºCDB¤²ZÙ>ÅÙ_B‰_«L(¥¡CzHê!½MĞæ„¤E'BÊI³²Š¤-çªÔZ¬9†Hä•CRR½\rò±JEuî¥‚xH¸y8@€2A*C&Cœy’G„ë8ĞâN¬™ÁÈ7†Ø \\<æk»\nã4©T:‡¡«²õDHq\$*fUdÑb¯Ø¼@'…0¨½Ñ+Ì¦•\\ô°2\\¼â5²Ö`ªC>Ôêús¬V¢!3VÈ®z“+ëâzåBIL¼Md€—‚¦ÑxÑ4eĞKœ\nÖÃ»;NÂ8‚.ƒL\naD&\0ÍFĞ‚á*¬–pi´uŞWªù_«ˆr8ñÑŒ)”‡)Ú”üGÏµ°û²“½ø43ğ­Âëú/¯ıö(%Ÿ>ä*JÈ3:CE­•à42W\rñqhÍi±à¡Ô0«AÀ¸g»æÌLV[õÄ\rØPHÁZÊÕÃ¸¥9‹*áÃ`+Mom5®KkQq9\rN7öS>;-…ğ2'MDè;ü‚5à\r¡Ö(‚	xïØU\nƒ€A`}mD\$ñš)CtÒÅÖ\r)IÌu(¨KXg¹•½æu7š‰>lky½3,¶MÆv¨ñalõ››ŞpNNo´Â@H¬‘&².ä¬bh,•Ê+&ÉÒƒÉÏ%„2[K–(Ğ=\"É^¢SeŠD¨Ö+‚\0IR‰~Ú(Õ¹´•›ªE”°¯#c’1&J&ÂòäU‘x\n	¾ê†ØráäÚ[S1\"%.‘T¦\$GDhÄTíNnQŸ{ÇÕ(²Â¯‰zÍĞ+à­™—Å”Şù×|ãT:N”´£J“—mj™D­@Ïz\$lÌÚ›ïS‚¡x²š`àéyzV¿Lš1MÒgã†‹t)Ós¿Vò\rÃE\"";break;case"vi":$h="Bp®”&á†³‚š *ó(J.™„0Q,ĞÃZŒâ¤)vƒ@Tf™\nípj£pº*ÃV˜ÍÃC`á]¦ÌrY<•#\$b\$L2–€@%9¥ÅIÄô×ŒÆÎ“„œ§4Ë…€¡€Äd3\rFÃqÀät9N1 QŠE3Ú¡±hÄj[—J;±ºŠo—ç\nÓ(©Ubµ´da¬®ÆIÂ¾Ri¦Då\0\0A)÷XŞ8@q:g!ÏC½_#yÃÌ¸™6:‚¶ëÑÚ‹Ì.—òŠšíK;×.ğ›­Àƒ}FÊÍ¼S06ÂÁ½†¡Œ÷\\İÅv¯ëàÄN5°‡SÁ«Ü“ ¹»g	“¤pä7±®úvù¾#ô]“áÒ]“+°æ0¡Ò9©jjP ˜eî„Adš²c@êœãJ*Ì#ìÓŠX„\n\npEÉš44…K\nÁd‹Âñ”È@3Åè&Ã!\0Úï2Œì0ß%Å¤‹öƒb‰ÀCC>4¥j²V4ºò¦ÉÁ:ûoÄš&Ëïê€•-ÉˆH„úúL\0P2Êë;ËéDş&“2I JÌ3Å£\"ò<ƒ(P9… S4jˆ!hHÓÁ¬ÄV:c[‚_±KÑJşŒiÁS(˜erÁEzP<:³öœi#èBB‹+ÅĞc¸2±Sœ^¡¦IÁYÒ6Gs)w3ÇìM§<¢ÕSša+fPÅöÒG#©µa\r<Ål>4E<¼¬X\"@t&‰¡Ğ¦)BØó\"èZ6¡hÈ2BÅõ²¬»2\r‹@,ìĞÂ9¡\0Ş3Ãc¶2¥Ò!CY¥ÙOE;ÅèÏÉk†Z¢v’8¢¤Š|½ C¾ÍŞ[46@¡`@‹”qò:õhŠÆjÍQ¡æa—OèÜÊŒ9,‹S'¦´(b¦)Ù¨Ó£iôc@¥2ZH1E4ıEXê—\\PÒ;–¿£Ä\0003©cD¬5a\0Ì7A\0ê9»hØ‚2\r¹å£p2¼Hx¡àÌ„J@D\0Â:q¡à^0‡Ép¡zLiQ@é»JjwCO¡~:mÌ}?NÍœğ”i¥?6ElÜúN7~ê¿µúZsz?=Ğ\r è8aĞ^ÿ\\0ò]ÄáxÊ7}CÅ\"7q\0_Ğaóõ Å\nd¢v}WX·Èú?O„µ@`	qïGÅ©BF^Si¡Íø’ÕÇCcu\0u¦ËeÃ‡ ÚTpaÊEÄ‡0êÃa˜:Â\0ØÒƒÄtš1Ô	È+s¹ÔØ…Y¤Cp5¤bp‚PZ\rI¦€¾q@Pºçô(€ M9š#Â\\ÿ„4Lƒz(\0007X4ãHäiÈ4 ØC<(jN :8PÒáCpo?±‡xPRÙÀ gE½³8NZj`Bp•dşT”X.\0¡¼œå¬(Ïê@WŠù˜ÀRĞµ„év8¤ı(¬ÀÑˆ¸©8‡üüŸ³úÜës -Ø’à’DƒË=a¥GHúßZì^ˆ&C1ÿ:n=ÈÆ×G_Y†ó\"08PàcÉ.ŒE‘’B}J!,á@'…0¨iÃ«?h„åé¬&“\0‰¥&ív“rrNÎ)=•ä[Ğ	/%,bb±+‚†‚I—»ÆBrõ˜–Òà	Wôâ&3î]‚0T‹ç= 6ÃŸCÆ\$N'±ÎÄ‰3w\\\"A\"·ã¾Ş)¤gm­ä‡Jp‘N‰‰)¹¨ I*óˆèawéH]‰T> ¥ ´B\nHª¡€Lü´\"ø\0HI„B¤˜”Th‘İË-‚èX˜ÔDœ U\nƒŠ~†©ä’§¬È™9È½i‘t**\0‡†{´å¦'¤åYD^Î™”’ ((‡T¬óÉ0\n¬&\"&’úÕ&9¯«äÒ¥@\nVé]]b,Í”SjÃX}A²5ı‰§4¬lÒZal°1¢4r‰‚¡Ş· Éô'\"!ÎÈ\$š·Ø\rÇ\\0Ò‰b˜BÂ#J¬ğ¼9ó\rÈT¡j•SÛ:_b„:*–†•V³eEoz85òçXÃ	\"›‰š±Âô€";break;case"zh":$h="ä^¨ês•\\šr¤îõâ|%ÌÂ:\$\nr.®„ö2Šr/d²È»[8Ğ S™8€r©!T¡\\¸s¦’I4¢b§r¬ñ•Ğ€Js!Kd²u´eåV¦©ÅDªX,#!˜Ğj6 §:¥t\nr£“îU:.Z²PË‘.…\rVWd^%äŒµ’r¡T²Ô¼*°s#UÕ`QdŞu'c(€ÜoF“±¤Øe3™Nb¦`êp2N™S¡ Ó£:LYñta~¨&6ÛŠ‹•r¶s®Ôükó{¾¹6ûòÙÍÀ©c(¸Ê2ªòf“qĞˆP:S*@S¡^­t*…êıÎ”TyUëx»àè_¦\\‹¤Û™Tœ¥‰*Œ¸©Óªë¡„ÒÆ'ŠaÊ[–Nb¨Æ*¹ÎVÈÉd²>1[œå‰vr“ËqÌÃÂ¬!J—ç1.[\$¹hŒDcğMœ¤Al²¤‹‚N-9@€§)6_¥éDï’ë£âã/KáÊLÉğì>„‘«A^Cå²1zJ·g1@œó“2\$…Ê]—Q;Å!ã A\nRX<´@S.ˆ\\tj–’áÎZIi9vsŠzFœåé\\–‘ÌtG¼¤a#Fg)T Ò@'1TÃ…ª H\$\\¦%¤éJtîÂ2]%tñ•¡ÊG’5<¾ÎÊ	XÖiô!aØ¤ÆätÜªB@	¢ht)Š`P¶<ÜƒÈº\r£h\\2€UOTÕs“Kƒ äÆ±á\0Â92£xÌ3\ràÊÚ¥ÊyPÊo‰ÌY•I(õŠƒ{06Œ#pò£pæ:ŒcD9ŒÃ¨Ø\rƒxÎîacH9bãÎ0»cdk¸:µa@æàŒ,ND¦)ÁNRäI«\$96Ú4œr‘¤¬†aÄ<HTDû*2CpÖÑŒÃxåŒnàÎ#&l7£–T1øĞÒ2n!\0x0²7àÌ„J8D›«UµxÂ6¢>4î³¼ üñb>znKLÅ«.âhÂ9µ{8áÑc¾Í;£Àà4ßƒ ]½oƒFıÀ\r è8aĞ^ş\\0íØälÃ8^2ŞXğÕîÛ¨_Àağ’6øÛæœO£4~ÛZ7ÎÍ.j0l¨Ò:3;)¸\rÃ§Çò')~B%	vtEZ+Ïyv%€Ê†Æ€nÜÏ20ÄeCƒql¡Éí§`Â( clu²FÉY<\n†´Òƒ*ÃÛØ½ÀÒC`s`…À¹ä\0€‹yqŠ¤J\nôÌš	Ğ \n (l€R(€À†‚CIŒmÕ¸@vÔiÁš3†xĞTì0t4f¼Ë÷Ù(w‹ÌôÃÊ!Åjó1ƒ [W(K‰20\$Ô›“‘…G0®©É¦ˆò¬y…¸åByQ\nÀcI+ä27xÊû›Ì|¦¸Ñ¯ÀâMìÁÈ7†ÖØñ[ƒg5¯2²Y3	c#§4&Ô˜Š¢¢O\naR#xäJãx(\"Ey¤<^„\0…‘R0EHâH…RŠXr‰<.Å!Œ1Æ@1±V0öC{Ä_@€1–ÖÂˆLš.:ß0Tˆ¬U;—±Y”¢”’šL‡#(ñËBÏ<ˆR?‘r9ÄÙ‹.è<Ya6-m¡(–PÃ`¡(,»³C¯Fˆ(èå7Ñb9D˜ŠKØ6°ÆÀCkE¾ha(\"Ã5|¤31wÖmB4í\r¡ÕÒNGÕ‚¨TÀ´˜İ\$©İ6¬ĞºÊ9Ex¼:&0§Áz»ÕP©<ÊÜº€ ›Néêî%=M–\n,Ç@‡(¸ˆ^^‰É=°(Ò\$ÅğæÈ­[¡\\9„ ¹¬c”@Ã'\nv\r­˜Êš“b¬ƒÄmK¸P‹QÒ'R\\x!\"8–ˆ–µÎ“Ây UAB#Èe3ğ/HSÑÒZ«B³ÕŠ´`‹ATIÈs(•(¦*—IÇ–×§*ğr\rb ";break;case"zh-tw":$h="ä^¨ê%Ó•\\šr¥ÑÎõâ|%ÌÎu:HçB(\\Ë4«‘pŠr –neRQÌ¡D8Ğ S•\nt*.tÒI&”G‘N”ÊAÊ¤S¹V÷:	t%9Sy:\"<r«STâ ,#!˜Ğj61uL\0¼–£“îU:.–²I9“ˆ—BÍæK&]\nDªXç[ªÅ}-,°r¨“ÖûÎöŒ¿‹&ó¨€Ğa;Dãx€àr4&Ã)œÊs3§SÂtÍ\rAĞÂbÒ¥¨E•E1»ŞÔ£Êg:åxç]#0,'}Ã¼b1Qä\\y\0çV¡E<Á¤Üg–¢SÅ )ĞªOLP\0¨ıÎ”«:}Uï»áÔr¢òå´yZë¤se¢\\BœÅABs–¤ @¤2*bPr–î\n¦ª²*‰.Ocê÷°D\nt”\$ñÊO-Ç1*\\CJY.R®DùÌLGI,I½IÒ@H‹–Å‘Ğ[°§)r_ «ÂK¯j³–Á•ç)2«¥Áft(qÊWÈĞs“%Ú\\R©epr\$,Á1³#¢Ä“ÇIA5er2òØ˜R-8ÎA b©d8¡-Ç!v]œÄ!åììsÄ‘Ê]àRxŸ éi—¤åD@—!QsZH\$kIÏa|C9Tö¡.«'„%p–•ä!ÊC—Il+-ÙVd<(D\rk[—e³\"s‘åò¨«	@t&‰¡Ğ¦)BØóu\"è\\6¡pÈ2Uui&C®«ºò\rƒ äÉ2\0Â93CxÌ3\rğÊİ…át¹*š/0Ùvh¨7³£hÂ7!\0ê7c¨Æ1´ã˜Ì:\0Ø7Œîğæ5#–:0Œã¼fá,ğ6»Ã¬ù?7HÂ4J‘	¤!ŠbŒÔãXÊ7/Ï‘täk£0‘Ú`¾¤±]‘	ñOÍx«\"*2ãpÖÔÃxånğÎ#&x7£–`1ù\0Ò2o!\0x0²Ø Ì„J@D›ë_¹xÂ7BJ4ï¡(Y\$	¡„f¥ªjÌ>Ë6²\"hÂ9¶xáÕc¾İ<£Àà4àƒ ]ÁpƒG\rÄ\r è8aĞ^şH\\0îØPämÃ8_§úcÃa¿o¡9‡ÂHÚ84ƒn:r<œÔ|Mß<5A5§g£=¼ã[ÀÜ:r¼¿2r—ñ@\"@¢c”J¶CàÙÃAš!Œ:9€Ü¹£e!ˆÍòÛC“âO„3:Ğ@ÈY%dì¥•²ØM‘©0|0¾ @İËã\r!„66‚FP\"¨! Ác‰RBí\$a(+Ç0(€¡P:	Ah4†‚CI‘mõ¼@ÆäÓñ 4FÓ'€àÛƒ¡¨6†l7¿(hÊÃ¸ewD^4FŒ9„0‘¶,’³Ì-‘%ÄÀ™4d49…pµEBáªBj\\„?(%¨Ÿ4à9DP Bø\$†`@Xdoñ±ù6û\r™¨`Ä:šwäƒo\r­Ñæ·†Şlš|/eršF·^iĞP	áL*ò^É?RH¿ap&Êz.Ó s,:—“/œ4bb+H1’‘øÉ™PÆÆØóà\rï1'ÒÜÂ˜Q	€€334hœ F\nAäğ^üh~3ÒXË9LŒËÏ<Â¥Î'1Î\\Ü€gˆò,ád9„Ø´:)É:5øüæÓyŠ£”<WI²XÎS¡Ô@GŸ±bè±\"|_Å¥üXca!Œ5‚2á´4•±€6¾°Ò˜èi\0(#OgÜ]by3LÜ*…@ŒAÀ iíòQótæÇ(ŒJ)µõ·ã^Ëâ†Lrò“@PM¨Õ!Q	’ !…P(s¢zg	´ş:D¸œÂ¤óš{Q†èìQ Á 9Øäâº#“\"'[;mÆh×`fcrTB€@”±9\$EğŒ:ÉÊÒa \"\rÂyUVQB#Øe429aH¾½%µ‰ŠÜ:Æ™ın™\"èUe¤4Ó­åğó[yEÌ\nP%Â¡ZŠ€";break;}$ng=array();foreach(explode("\n",lzw_decompress($h))as$X)$ng[]=(strpos($X,"\t")?explode("\t",$X):$X);return$ng;}if(!$ng){$ng=get_translations($ba);$_SESSION["translations"]=$ng;}if(extension_loaded('pdo')){class
Min_PDO
extends
PDO{var$_result,$server_info,$affected_rows,$errno,$error;function
__construct(){global$b;$Ce=array_search("SQL",$b->operators);if($Ce!==false)unset($b->operators[$Ce]);}function
dsn($Ib,$V,$G,$D=array()){try{parent::__construct($Ib,$V,$G,$D);}catch(Exception$Wb){auth_error(h($Wb->getMessage()));}$this->setAttribute(13,array('Min_PDOStatement'));$this->server_info=@$this->getAttribute(4);}function
query($H,$wg=false){$I=parent::query($H);$this->error="";if(!$I){list(,$this->errno,$this->error)=$this->errorInfo();return
false;}$this->store_result($I);return$I;}function
multi_query($H){return$this->_result=$this->query($H);}function
store_result($I=null){if(!$I){$I=$this->_result;if(!$I)return
false;}if($I->columnCount()){$I->num_rows=$I->rowCount();return$I;}$this->affected_rows=$I->rowCount();return
true;}function
next_result(){if(!$this->_result)return
false;$this->_result->_offset=0;return@$this->_result->nextRowset();}function
result($H,$q=0){$I=$this->query($H);if(!$I)return
false;$K=$I->fetch();return$K[$q];}}class
Min_PDOStatement
extends
PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch(2);}function
fetch_row(){return$this->fetch(3);}function
fetch_field(){$K=(object)$this->getColumnMeta($this->_offset++);$K->orgtable=$K->table;$K->orgname=$K->name;$K->charsetnr=(in_array("blob",(array)$K->flags)?63:0);return$K;}}}$Fb=array();class
Min_SQL{var$_conn;function
__construct($i){$this->_conn=$i;}function
select($R,$M,$Z,$Cc,$me=array(),$_=1,$E=0,$He=false){global$b,$y;$id=(count($Cc)<count($M));$H=$b->selectQueryBuild($M,$Z,$Cc,$me,$_,$E);if(!$H)$H="SELECT".limit(($_GET["page"]!="last"&&$_!=""&&$Cc&&$id&&$y=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($Cc&&$id?"\nGROUP BY ".implode(", ",$Cc):"").($me?"\nORDER BY ".implode(", ",$me):""),($_!=""?+$_:null),($E?$_*$E:0),"\n");$Hf=microtime(true);$J=$this->_conn->query($H);if($He)echo$b->selectQuery($H,$Hf,!$J);return$J;}function
delete($R,$Oe,$_=0){$H="FROM ".table($R);return
queries("DELETE".($_?limit1($R,$H,$Oe):" $H$Oe"));}function
update($R,$P,$Oe,$_=0,$N="\n"){$Jg=array();foreach($P
as$z=>$X)$Jg[]="$z = $X";$H=table($R)." SET$N".implode(",$N",$Jg);return
queries("UPDATE".($_?limit1($R,$H,$Oe,$N):" $H$Oe"));}function
insert($R,$P){return
queries("INSERT INTO ".table($R).($P?" (".implode(", ",array_keys($P)).")\nVALUES (".implode(", ",$P).")":" DEFAULT VALUES"));}function
insertUpdate($R,$L,$Fe){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
convertSearch($v,$X,$q){return$v;}function
value($X,$q){return$X;}function
quoteBinary($if){return
q($if);}function
warnings(){return'';}function
tableHelp($C){}}$Fb["sqlite"]="SQLite 3";$Fb["sqlite2"]="SQLite 2";if(isset($_GET["sqlite"])||isset($_GET["sqlite2"])){$De=array((isset($_GET["sqlite"])?"SQLite3":"SQLite"),"PDO_SQLite");define("DRIVER",(isset($_GET["sqlite"])?"sqlite":"sqlite2"));if(class_exists(isset($_GET["sqlite"])?"SQLite3":"SQLiteDatabase")){if(isset($_GET["sqlite"])){class
Min_SQLite{var$extension="SQLite3",$server_info,$affected_rows,$errno,$error,$_link;function
__construct($s){$this->_link=new
SQLite3($s);$Lg=$this->_link->version();$this->server_info=$Lg["versionString"];}function
query($H){$I=@$this->_link->query($H);$this->error="";if(!$I){$this->errno=$this->_link->lastErrorCode();$this->error=$this->_link->lastErrorMsg();return
false;}elseif($I->numColumns())return
new
Min_Result($I);$this->affected_rows=$this->_link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->_link->escapeString($Q)."'":"x'".reset(unpack('H*',$Q))."'");}function
store_result(){return$this->_result;}function
result($H,$q=0){$I=$this->query($H);if(!is_object($I))return
false;$K=$I->_result->fetchArray();return$K[$q];}}class
Min_Result{var$_result,$_offset=0,$num_rows;function
__construct($I){$this->_result=$I;}function
fetch_assoc(){return$this->_result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->_result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$f=$this->_offset++;$U=$this->_result->columnType($f);return(object)array("name"=>$this->_result->columnName($f),"type"=>$U,"charsetnr"=>($U==SQLITE3_BLOB?63:0),);}function
__desctruct(){return$this->_result->finalize();}}}else{class
Min_SQLite{var$extension="SQLite",$server_info,$affected_rows,$error,$_link;function
__construct($s){$this->server_info=sqlite_libversion();$this->_link=new
SQLiteDatabase($s);}function
query($H,$wg=false){$Qd=($wg?"unbufferedQuery":"query");$I=@$this->_link->$Qd($H,SQLITE_BOTH,$p);$this->error="";if(!$I){$this->error=$p;return
false;}elseif($I===true){$this->affected_rows=$this->changes();return
true;}return
new
Min_Result($I);}function
quote($Q){return"'".sqlite_escape_string($Q)."'";}function
store_result(){return$this->_result;}function
result($H,$q=0){$I=$this->query($H);if(!is_object($I))return
false;$K=$I->_result->fetch();return$K[$q];}}class
Min_Result{var$_result,$_offset=0,$num_rows;function
__construct($I){$this->_result=$I;if(method_exists($I,'numRows'))$this->num_rows=$I->numRows();}function
fetch_assoc(){$K=$this->_result->fetch(SQLITE_ASSOC);if(!$K)return
false;$J=array();foreach($K
as$z=>$X)$J[($z[0]=='"'?idf_unescape($z):$z)]=$X;return$J;}function
fetch_row(){return$this->_result->fetch(SQLITE_NUM);}function
fetch_field(){$C=$this->_result->fieldName($this->_offset++);$ze='(\\[.*]|"(?:[^"]|"")*"|(.+))';if(preg_match("~^($ze\\.)?$ze\$~",$C,$B)){$R=($B[3]!=""?$B[3]:idf_unescape($B[2]));$C=($B[5]!=""?$B[5]:idf_unescape($B[4]));}return(object)array("name"=>$C,"orgname"=>$C,"orgtable"=>$R,);}}}}elseif(extension_loaded("pdo_sqlite")){class
Min_SQLite
extends
Min_PDO{var$extension="PDO_SQLite";function
__construct($s){$this->dsn(DRIVER.":$s","","");}}}if(class_exists("Min_SQLite")){class
Min_DB
extends
Min_SQLite{function
__construct(){parent::__construct(":memory:");$this->query("PRAGMA foreign_keys = 1");}function
select_db($s){if(is_readable($s)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$s)?$s:dirname($_SERVER["SCRIPT_FILENAME"])."/$s")." AS a")){parent::__construct($s);$this->query("PRAGMA foreign_keys = 1");return
true;}return
false;}function
multi_query($H){return$this->_result=$this->query($H);}function
next_result(){return
false;}}}class
Min_Driver
extends
Min_SQL{function
insertUpdate($R,$L,$Fe){$Jg=array();foreach($L
as$P)$Jg[]="(".implode(", ",$P).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($L))).") VALUES\n".implode(",\n",$Jg));}function
tableHelp($C){if($C=="sqlite_sequence")return"fileformat2.html#seqtab";if($C=="sqlite_master")return"fileformat2.html#$C";}}function
idf_escape($v){return'"'.str_replace('"','""',$v).'"';}function
table($v){return
idf_escape($v);}function
connect(){return
new
Min_DB;}function
get_databases(){return
array();}function
limit($H,$Z,$_,$ce=0,$N=" "){return" $H$Z".($_!==null?$N."LIMIT $_".($ce?" OFFSET $ce":""):"");}function
limit1($R,$H,$Z,$N="\n"){global$i;return(preg_match('~^INTO~',$H)||$i->result("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($H,$Z,1,0,$N):" $H WHERE rowid = (SELECT rowid FROM ".table($R).$Z.$N."LIMIT 1)");}function
db_collation($n,$db){global$i;return$i->result("PRAGMA encoding");}function
engines(){return
array();}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name = 'sqlite_sequence'), name",1);}function
count_tables($m){return
array();}function
table_status($C=""){global$i;$J=array();foreach(get_rows("SELECT name AS Name, type AS Engine, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($C!=""?"AND name = ".q($C):"ORDER BY name"))as$K){$K["Rows"]=$i->result("SELECT COUNT(*) FROM ".idf_escape($K["Name"]));$J[$K["Name"]]=$K;}foreach(get_rows("SELECT * FROM sqlite_sequence",null,"")as$K)$J[$K["name"]]["Auto_increment"]=$K["seq"];return($C!=""?$J[$C]:$J);}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){global$i;return!$i->result("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($R){global$i;$J=array();$Fe="";foreach(get_rows("PRAGMA table_info(".table($R).")")as$K){$C=$K["name"];$U=strtolower($K["type"]);$xb=$K["dflt_value"];$J[$C]=array("field"=>$C,"type"=>(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":"numeric")))),"full_type"=>$U,"default"=>(preg_match("~'(.*)'~",$xb,$B)?str_replace("''","'",$B[1]):($xb=="NULL"?null:$xb)),"null"=>!$K["notnull"],"privileges"=>array("select"=>1,"insert"=>1,"update"=>1),"primary"=>$K["pk"],);if($K["pk"]){if($Fe!="")$J[$Fe]["auto_increment"]=false;elseif(preg_match('~^integer$~i',$U))$J[$C]["auto_increment"]=true;$Fe=$C;}}$Ef=$i->result("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));preg_match_all('~(("[^"]*+")+|[a-z0-9_]+)\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$Ef,$Hd,PREG_SET_ORDER);foreach($Hd
as$B){$C=str_replace('""','"',preg_replace('~^"|"$~','',$B[1]));if($J[$C])$J[$C]["collation"]=trim($B[3],"'");}return$J;}function
indexes($R,$j=null){global$i;if(!is_object($j))$j=$i;$J=array();$Ef=$j->result("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$Ef,$B)){$J[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$B[1],$Hd,PREG_SET_ORDER);foreach($Hd
as$B){$J[""]["columns"][]=idf_unescape($B[2]).$B[4];$J[""]["descs"][]=(preg_match('~DESC~i',$B[5])?'1':null);}}if(!$J){foreach(fields($R)as$C=>$q){if($q["primary"])$J[""]=array("type"=>"PRIMARY","columns"=>array($C),"lengths"=>array(),"descs"=>array(null));}}$Ff=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$j);foreach(get_rows("PRAGMA index_list(".table($R).")",$j)as$K){$C=$K["name"];$w=array("type"=>($K["unique"]?"UNIQUE":"INDEX"));$w["lengths"]=array();$w["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($C).")",$j)as$hf){$w["columns"][]=$hf["name"];$w["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($C).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$Ff[$C],$Ve)){preg_match_all('/("[^"]*+")+( DESC)?/',$Ve[2],$Hd);foreach($Hd[2]as$z=>$X){if($X)$w["descs"][$z]='1';}}if(!$J[""]||$w["type"]!="UNIQUE"||$w["columns"]!=$J[""]["columns"]||$w["descs"]!=$J[""]["descs"]||!preg_match("~^sqlite_~",$C))$J[$C]=$w;}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$K){$vc=&$J[$K["id"]];if(!$vc)$vc=$K;$vc["source"][]=$K["from"];$vc["target"][]=$K["to"];}return$J;}function
view($C){global$i;return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\\s+~iU','',$i->result("SELECT sql FROM sqlite_master WHERE name = ".q($C))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($n){return
false;}function
error(){global$i;return
h($i->error);}function
check_sqlite_name($C){global$i;$dc="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($dc)\$~",$C)){$i->error=lang(21,str_replace("|",", ",$dc));return
false;}return
true;}function
create_database($n,$e){global$i;if(file_exists($n)){$i->error=lang(22);return
false;}if(!check_sqlite_name($n))return
false;try{$A=new
Min_SQLite($n);}catch(Exception$Wb){$i->error=$Wb->getMessage();return
false;}$A->query('PRAGMA encoding = "UTF-8"');$A->query('CREATE TABLE adminer (i)');$A->query('DROP TABLE adminer');return
true;}function
drop_databases($m){global$i;$i->__construct(":memory:");foreach($m
as$n){if(!@unlink($n)){$i->error=lang(22);return
false;}}return
true;}function
rename_database($C,$e){global$i;if(!check_sqlite_name($C))return
false;$i->__construct(":memory:");$i->error=lang(22);return@rename(DB,$C);}function
auto_increment(){return" PRIMARY KEY".(DRIVER=="sqlite"?" AUTOINCREMENT":"");}function
alter_table($R,$C,$r,$sc,$hb,$Rb,$e,$Fa,$we){$Gg=($R==""||$sc);foreach($r
as$q){if($q[0]!=""||!$q[1]||$q[2]){$Gg=true;break;}}$c=array();$pe=array();foreach($r
as$q){if($q[1]){$c[]=($Gg?$q[1]:"ADD ".implode($q[1]));if($q[0]!="")$pe[$q[0]]=$q[1][0];}}if(!$Gg){foreach($c
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$C&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($C)))return
false;}elseif(!recreate_table($R,$C,$c,$pe,$sc))return
false;if($Fa)queries("UPDATE sqlite_sequence SET seq = $Fa WHERE name = ".q($C));return
true;}function
recreate_table($R,$C,$r,$pe,$sc,$x=array()){if($R!=""){if(!$r){foreach(fields($R)as$z=>$q){if($x)$q["auto_increment"]=0;$r[]=process_field($q,$q);$pe[$z]=idf_escape($z);}}$Ge=false;foreach($r
as$q){if($q[6])$Ge=true;}$Hb=array();foreach($x
as$z=>$X){if($X[2]=="DROP"){$Hb[$X[1]]=true;unset($x[$z]);}}foreach(indexes($R)as$nd=>$w){$g=array();foreach($w["columns"]as$z=>$f){if(!$pe[$f])continue
2;$g[]=$pe[$f].($w["descs"][$z]?" DESC":"");}if(!$Hb[$nd]){if($w["type"]!="PRIMARY"||!$Ge)$x[]=array($w["type"],$nd,$g);}}foreach($x
as$z=>$X){if($X[0]=="PRIMARY"){unset($x[$z]);$sc[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$nd=>$vc){foreach($vc["source"]as$z=>$f){if(!$pe[$f])continue
2;$vc["source"][$z]=idf_unescape($pe[$f]);}if(!isset($sc[" $nd"]))$sc[]=" ".format_foreign_key($vc);}queries("BEGIN");}foreach($r
as$z=>$q)$r[$z]="  ".implode($q);$r=array_merge($r,array_filter($sc));if(!queries("CREATE TABLE ".table($R!=""?"adminer_$C":$C)." (\n".implode(",\n",$r)."\n)"))return
false;if($R!=""){if($pe&&!queries("INSERT INTO ".table("adminer_$C")." (".implode(", ",$pe).") SELECT ".implode(", ",array_map('idf_escape',array_keys($pe)))." FROM ".table($R)))return
false;$tg=array();foreach(triggers($R)as$rg=>$cg){$qg=trigger($rg);$tg[]="CREATE TRIGGER ".idf_escape($rg)." ".implode(" ",$cg)." ON ".table($C)."\n$qg[Statement]";}if(!queries("DROP TABLE ".table($R)))return
false;queries("ALTER TABLE ".table("adminer_$C")." RENAME TO ".table($C));if(!alter_indexes($C,$x))return
false;foreach($tg
as$qg){if(!queries($qg))return
false;}queries("COMMIT");}return
true;}function
index_sql($R,$U,$C,$g){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($C!=""?$C:uniqid($R."_"))." ON ".table($R)." $g";}function
alter_indexes($R,$c){foreach($c
as$Fe){if($Fe[0]=="PRIMARY")return
recreate_table($R,$R,array(),array(),array(),$c);}foreach(array_reverse($c)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables($T){return
apply_queries("DELETE FROM",$T);}function
drop_views($Ng){return
apply_queries("DROP VIEW",$Ng);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
move_tables($T,$Ng,$Vf){return
false;}function
trigger($C){global$i;if($C=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$v='(?:[^`"\\s]+|`[^`]*`|"[^"]*")+';$sg=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$v\\s*(".implode("|",$sg["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($v))?\\s+ON\\s*$v\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",$i->result("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($C)),$B);$be=$B[3];return
array("Timing"=>strtoupper($B[1]),"Event"=>strtoupper($B[2]).($be?" OF":""),"Of"=>($be[0]=='`'||$be[0]=='"'?idf_unescape($be):$be),"Trigger"=>$C,"Statement"=>$B[4],);}function
triggers($R){$J=array();$sg=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$K){preg_match('~^CREATE\\s+TRIGGER\\s*(?:[^`"\\s]+|`[^`]*`|"[^"]*")+\\s*('.implode("|",$sg["Timing"]).')\\s*(.*)\\s+ON\\b~iU',$K["sql"],$B);$J[$K["name"]]=array($B[1],$B[2]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
begin(){return
queries("BEGIN");}function
last_id(){global$i;return$i->result("SELECT LAST_INSERT_ROWID()");}function
explain($i,$H){return$i->query("EXPLAIN QUERY PLAN $H");}function
found_rows($S,$Z){}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($kf){return
true;}function
create_sql($R,$Fa,$Mf){global$i;$J=$i->result("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$C=>$w){if($C=='')continue;$J.=";\n\n".index_sql($R,$w['type'],$C,"(".implode(", ",array_map('idf_escape',$w['columns'])).")");}return$J;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($l){}function
trigger_sql($R){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){global$i;$J=array();foreach(array("auto_vacuum","cache_size","count_changes","default_cache_size","empty_result_callbacks","encoding","foreign_keys","full_column_names","fullfsync","journal_mode","journal_size_limit","legacy_file_format","locking_mode","page_size","max_page_count","read_uncommitted","recursive_triggers","reverse_unordered_selects","secure_delete","short_column_names","synchronous","temp_store","temp_store_directory","schema_version","integrity_check","quick_check")as$z)$J[$z]=$i->result("PRAGMA $z");return$J;}function
show_status(){$J=array();foreach(get_vals("PRAGMA compile_options")as$ke){list($z,$X)=explode("=",$ke,2);$J[$z]=$X;}return$J;}function
convert_field($q){}function
unconvert_field($q,$J){return$J;}function
support($hc){return
preg_match('~^(columns|database|drop_col|dump|indexes|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$hc);}$y="sqlite";$vg=array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0);$Lf=array_keys($vg);$Bg=array();$je=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");$Bc=array("hex","length","lower","round","unixepoch","upper");$Fc=array("avg","count","count distinct","group_concat","max","min","sum");$Kb=array(array(),array("integer|real|numeric"=>"+/-","text"=>"||",));}$Fb["pgsql"]="PostgreSQL";if(isset($_GET["pgsql"])){$De=array("PgSQL","PDO_PgSQL");define("DRIVER","pgsql");if(extension_loaded("pgsql")){class
Min_DB{var$extension="PgSQL",$_link,$_result,$_string,$_database=true,$server_info,$affected_rows,$error;function
_error($Ub,$p){if(ini_bool("html_errors"))$p=html_entity_decode(strip_tags($p));$p=preg_replace('~^[^:]*: ~','',$p);$this->error=$p;}function
connect($O,$V,$G){global$b;$n=$b->database();set_error_handler(array($this,'_error'));$this->_string="host='".str_replace(":","' port='",addcslashes($O,"'\\"))."' user='".addcslashes($V,"'\\")."' password='".addcslashes($G,"'\\")."'";$this->_link=@pg_connect("$this->_string dbname='".($n!=""?addcslashes($n,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->_link&&$n!=""){$this->_database=false;$this->_link=@pg_connect("$this->_string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->_link){$Lg=pg_version($this->_link);$this->server_info=$Lg["server"];pg_set_client_encoding($this->_link,"UTF8");}return(bool)$this->_link;}function
quote($Q){return"'".pg_escape_string($this->_link,$Q)."'";}function
value($X,$q){return($q["type"]=="bytea"?pg_unescape_bytea($X):$X);}function
quoteBinary($Q){return"'".pg_escape_bytea($this->_link,$Q)."'";}function
select_db($l){global$b;if($l==$b->database())return$this->_database;$J=@pg_connect("$this->_string dbname='".addcslashes($l,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($J)$this->_link=$J;return$J;}function
close(){$this->_link=@pg_connect("$this->_string dbname='postgres'");}function
query($H,$wg=false){$I=@pg_query($this->_link,$H);$this->error="";if(!$I){$this->error=pg_last_error($this->_link);return
false;}elseif(!pg_num_fields($I)){$this->affected_rows=pg_affected_rows($I);return
true;}return
new
Min_Result($I);}function
multi_query($H){return$this->_result=$this->query($H);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($H,$q=0){$I=$this->query($H);if(!$I||!$I->num_rows)return
false;return
pg_fetch_result($I->_result,0,$q);}function
warnings(){return
h(pg_last_notice($this->_link));}}class
Min_Result{var$_result,$_offset=0,$num_rows;function
__construct($I){$this->_result=$I;$this->num_rows=pg_num_rows($I);}function
fetch_assoc(){return
pg_fetch_assoc($this->_result);}function
fetch_row(){return
pg_fetch_row($this->_result);}function
fetch_field(){$f=$this->_offset++;$J=new
stdClass;if(function_exists('pg_field_table'))$J->orgtable=pg_field_table($this->_result,$f);$J->name=pg_field_name($this->_result,$f);$J->orgname=$J->name;$J->type=pg_field_type($this->_result,$f);$J->charsetnr=($J->type=="bytea"?63:0);return$J;}function
__destruct(){pg_free_result($this->_result);}}}elseif(extension_loaded("pdo_pgsql")){class
Min_DB
extends
Min_PDO{var$extension="PDO_PgSQL";function
connect($O,$V,$G){global$b;$n=$b->database();$Q="pgsql:host='".str_replace(":","' port='",addcslashes($O,"'\\"))."' options='-c client_encoding=utf8'";$this->dsn("$Q dbname='".($n!=""?addcslashes($n,"'\\"):"postgres")."'",$V,$G);return
true;}function
select_db($l){global$b;return($b->database()==$l);}function
value($X,$q){return$X;}function
quoteBinary($if){return
q($if);}function
warnings(){return'';}function
close(){}}}class
Min_Driver
extends
Min_SQL{function
insertUpdate($R,$L,$Fe){global$i;foreach($L
as$P){$Cg=array();$Z=array();foreach($P
as$z=>$X){$Cg[]="$z = $X";if(isset($Fe[idf_unescape($z)]))$Z[]="$z = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$Cg)." WHERE ".implode(" AND ",$Z))&&$i->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($P)).") VALUES (".implode(", ",$P).")")))return
false;}return
true;}function
convertSearch($v,$X,$q){return(preg_match('~char|text'.(is_numeric($X["val"])&&!preg_match('~LIKE~',$X["op"])?'|'.number_type():'').'~',$q["type"])?$v:"CAST($v AS text)");}function
value($X,$q){return$this->_conn->value($X,$q);}function
quoteBinary($if){return$this->_conn->quoteBinary($if);}function
warnings(){return$this->_conn->warnings();}function
tableHelp($C){$_d=array("information_schema"=>"infoschema","pg_catalog"=>"catalog",);$A=$_d[$_GET["ns"]];if($A)return"$A-".str_replace("_","-",$C).".html";}}function
idf_escape($v){return'"'.str_replace('"','""',$v).'"';}function
table($v){return
idf_escape($v);}function
connect(){global$b,$vg,$Lf;$i=new
Min_DB;$k=$b->credentials();if($i->connect($k[0],$k[1],$k[2])){if(min_version(9,0,$i)){$i->query("SET application_name = 'Adminer'");if(min_version(9.2,0,$i)){$Lf[lang(23)][]="json";$vg["json"]=4294967295;if(min_version(9.4,0,$i)){$Lf[lang(23)][]="jsonb";$vg["jsonb"]=4294967295;}}}return$i;}return$i->error;}function
get_databases(){return
get_vals("SELECT datname FROM pg_database WHERE has_database_privilege(datname, 'CONNECT') ORDER BY datname");}function
limit($H,$Z,$_,$ce=0,$N=" "){return" $H$Z".($_!==null?$N."LIMIT $_".($ce?" OFFSET $ce":""):"");}function
limit1($R,$H,$Z,$N="\n"){return(preg_match('~^INTO~',$H)?limit($H,$Z,1,0,$N):" $H WHERE ctid = (SELECT ctid FROM ".table($R).$Z.$N."LIMIT 1)");}function
db_collation($n,$db){global$i;return$i->result("SHOW LC_COLLATE");}function
engines(){return
array();}function
logged_user(){global$i;return$i->result("SELECT user");}function
tables_list(){$H="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support('materializedview'))$H.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$H.="
ORDER BY 1";return
get_key_vals($H);}function
count_tables($m){return
array();}function
table_status($C=""){$J=array();foreach(get_rows("SELECT c.relname AS \"Name\", CASE c.relkind WHEN 'r' THEN 'table' WHEN 'm' THEN 'materialized view' ELSE 'view' END AS \"Engine\", pg_relation_size(c.oid) AS \"Data_length\", pg_total_relation_size(c.oid) - pg_relation_size(c.oid) AS \"Index_length\", obj_description(c.oid, 'pg_class') AS \"Comment\", CASE WHEN c.relhasoids THEN 'oid' ELSE '' END AS \"Oid\", c.reltuples as \"Rows\", n.nspname
FROM pg_class c
JOIN pg_namespace n ON(n.nspname = current_schema() AND n.oid = c.relnamespace)
WHERE relkind IN ('r', 'm', 'v', 'f')
".($C!=""?"AND relname = ".q($C):"ORDER BY relname"))as$K)$J[$K["Name"]]=$K;return($C!=""?$J[$C]:$J);}function
is_view($S){return
in_array($S["Engine"],array("view","materialized view"));}function
fk_support($S){return
true;}function
fields($R){$J=array();$xa=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',);foreach(get_rows("SELECT a.attname AS field, format_type(a.atttypid, a.atttypmod) AS full_type, d.adsrc AS default, a.attnotnull::int, col_description(c.oid, a.attnum) AS comment
FROM pg_class c
JOIN pg_namespace n ON c.relnamespace = n.oid
JOIN pg_attribute a ON c.oid = a.attrelid
LEFT JOIN pg_attrdef d ON c.oid = d.adrelid AND a.attnum = d.adnum
WHERE c.relname = ".q($R)."
AND n.nspname = current_schema()
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$K){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$K["full_type"],$B);list(,$U,$xd,$K["length"],$sa,$za)=$B;$K["length"].=$za;$Va=$U.$sa;if(isset($xa[$Va])){$K["type"]=$xa[$Va];$K["full_type"]=$K["type"].$xd.$za;}else{$K["type"]=$U;$K["full_type"]=$K["type"].$xd.$sa.$za;}$K["null"]=!$K["attnotnull"];$K["auto_increment"]=preg_match('~^nextval\\(~i',$K["default"]);$K["privileges"]=array("insert"=>1,"select"=>1,"update"=>1);if(preg_match('~(.+)::[^)]+(.*)~',$K["default"],$B))$K["default"]=($B[1]=="NULL"?null:(($B[1][0]=="'"?idf_unescape($B[1]):$B[1]).$B[2]));$J[$K["field"]]=$K;}return$J;}function
indexes($R,$j=null){global$i;if(!is_object($j))$j=$i;$J=array();$Tf=$j->result("SELECT oid FROM pg_class WHERE relnamespace = (SELECT oid FROM pg_namespace WHERE nspname = current_schema()) AND relname = ".q($R));$g=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Tf AND attnum > 0",$j);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption , (indpred IS NOT NULL)::int as indispartial FROM pg_index i, pg_class ci WHERE i.indrelid = $Tf AND ci.oid = i.indexrelid",$j)as$K){$We=$K["relname"];$J[$We]["type"]=($K["indispartial"]?"INDEX":($K["indisprimary"]?"PRIMARY":($K["indisunique"]?"UNIQUE":"INDEX")));$J[$We]["columns"]=array();foreach(explode(" ",$K["indkey"])as$Yc)$J[$We]["columns"][]=$g[$Yc];$J[$We]["descs"]=array();foreach(explode(" ",$K["indoption"])as$Zc)$J[$We]["descs"][]=($Zc&1?'1':null);$J[$We]["lengths"]=array();}return$J;}function
foreign_keys($R){global$ee;$J=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = (SELECT pc.oid FROM pg_class AS pc INNER JOIN pg_namespace AS pn ON (pn.oid = pc.relnamespace) WHERE pc.relname = ".q($R)." AND pn.nspname = current_schema())
AND contype = 'f'::char
ORDER BY conkey, conname")as$K){if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$K['definition'],$B)){$K['source']=array_map('trim',explode(',',$B[1]));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$B[2],$Gd)){$K['ns']=str_replace('""','"',preg_replace('~^"(.+)"$~','\1',$Gd[2]));$K['table']=str_replace('""','"',preg_replace('~^"(.+)"$~','\1',$Gd[4]));}$K['target']=array_map('trim',explode(',',$B[3]));$K['on_delete']=(preg_match("~ON DELETE ($ee)~",$B[4],$Gd)?$Gd[1]:'NO ACTION');$K['on_update']=(preg_match("~ON UPDATE ($ee)~",$B[4],$Gd)?$Gd[1]:'NO ACTION');$J[$K['conname']]=$K;}}return$J;}function
view($C){global$i;return
array("select"=>trim($i->result("SELECT view_definition
FROM information_schema.views
WHERE table_schema = current_schema() AND table_name = ".q($C))));}function
collations(){return
array();}function
information_schema($n){return($n=="information_schema");}function
error(){global$i;$J=h($i->error);if(preg_match('~^(.*\\n)?([^\\n]*)\\n( *)\\^(\\n.*)?$~s',$J,$B))$J=$B[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($B[3]).'})(.*)~','\\1<b>\\2</b>',$B[2]).$B[4];return
nl_br($J);}function
create_database($n,$e){return
queries("CREATE DATABASE ".idf_escape($n).($e?" ENCODING ".idf_escape($e):""));}function
drop_databases($m){global$i;$i->close();return
apply_queries("DROP DATABASE",$m,'idf_escape');}function
rename_database($C,$e){return
queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($C));}function
auto_increment(){return"";}function
alter_table($R,$C,$r,$sc,$hb,$Rb,$e,$Fa,$we){$c=array();$Ne=array();foreach($r
as$q){$f=idf_escape($q[0]);$X=$q[1];if(!$X)$c[]="DROP $f";else{$Ig=$X[5];unset($X[5]);if(isset($X[6])&&$q[0]=="")$X[1]=($X[1]=="bigint"?" big":" ")."serial";if($q[0]=="")$c[]=($R!=""?"ADD ":"  ").implode($X);else{if($f!=$X[0])$Ne[]="ALTER TABLE ".table($R)." RENAME $f TO $X[0]";$c[]="ALTER $f TYPE$X[1]";if(!$X[6]){$c[]="ALTER $f ".($X[3]?"SET$X[3]":"DROP DEFAULT");$c[]="ALTER $f ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}}if($q[0]!=""||$Ig!="")$Ne[]="COMMENT ON COLUMN ".table($R).".$X[0] IS ".($Ig!=""?substr($Ig,9):"''");}}$c=array_merge($c,$sc);if($R=="")array_unshift($Ne,"CREATE TABLE ".table($C)." (\n".implode(",\n",$c)."\n)");elseif($c)array_unshift($Ne,"ALTER TABLE ".table($R)."\n".implode(",\n",$c));if($R!=""&&$R!=$C)$Ne[]="ALTER TABLE ".table($R)." RENAME TO ".table($C);if($R!=""||$hb!="")$Ne[]="COMMENT ON TABLE ".table($C)." IS ".q($hb);if($Fa!=""){}foreach($Ne
as$H){if(!queries($H))return
false;}return
true;}function
alter_indexes($R,$c){$ob=array();$Gb=array();$Ne=array();foreach($c
as$X){if($X[0]!="INDEX")$ob[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$Gb[]=idf_escape($X[1]);else$Ne[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($ob)array_unshift($Ne,"ALTER TABLE ".table($R).implode(",",$ob));if($Gb)array_unshift($Ne,"DROP INDEX ".implode(", ",$Gb));foreach($Ne
as$H){if(!queries($H))return
false;}return
true;}function
truncate_tables($T){return
queries("TRUNCATE ".implode(", ",array_map('table',$T)));return
true;}function
drop_views($Ng){return
drop_tables($Ng);}function
drop_tables($T){foreach($T
as$R){$Jf=table_status($R);if(!queries("DROP ".strtoupper($Jf["Engine"])." ".table($R)))return
false;}return
true;}function
move_tables($T,$Ng,$Vf){foreach(array_merge($T,$Ng)as$R){$Jf=table_status($R);if(!queries("ALTER ".strtoupper($Jf["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($Vf)))return
false;}return
true;}function
trigger($C,$R=null){if($C=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");if($R===null)$R=$_GET['trigger'];$L=get_rows('SELECT t.trigger_name AS "Trigger", t.action_timing AS "Timing", (SELECT STRING_AGG(event_manipulation, \' OR \') FROM information_schema.triggers WHERE event_object_table = t.event_object_table AND trigger_name = t.trigger_name ) AS "Events", t.event_manipulation AS "Event", \'FOR EACH \' || t.action_orientation AS "Type", t.action_statement AS "Statement" FROM information_schema.triggers t WHERE t.event_object_table = '.q($R).' AND t.trigger_name = '.q($C));return
reset($L);}function
triggers($R){$J=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE event_object_table = ".q($R))as$K)$J[$K["trigger_name"]]=array($K["action_timing"],$K["event_manipulation"]);return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($C,$U){$L=get_rows('SELECT routine_definition AS definition, LOWER(external_language) AS language, *
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($C));$J=$L[0];$J["returns"]=array("type"=>$J["type_udt_name"]);$J["fields"]=get_rows('SELECT parameter_name AS field, data_type AS type, character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = '.q($C).'
ORDER BY ordinal_position');return$J;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($C,$K){$J=array();foreach($K["fields"]as$q)$J[]=$q["type"];return
idf_escape($C)."(".implode(", ",$J).")";}function
last_id(){return
0;}function
explain($i,$H){return$i->query("EXPLAIN $H");}function
found_rows($S,$Z){global$i;if(preg_match("~ rows=([0-9]+)~",$i->result("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$Ve))return$Ve[1];return
false;}function
types(){return
get_vals("SELECT typname
FROM pg_type
WHERE typnamespace = (SELECT oid FROM pg_namespace WHERE nspname = current_schema())
AND typtype IN ('b','d','e')
AND typelem = 0");}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){global$i;return$i->result("SELECT current_schema()");}function
set_schema($jf){global$i,$vg,$Lf;$J=$i->query("SET search_path TO ".idf_escape($jf));foreach(types()as$U){if(!isset($vg[$U])){$vg[$U]=0;$Lf[lang(24)][]=$U;}}return$J;}function
create_sql($R,$Fa,$Mf){global$i;$J='';$ff=array();$tf=array();$Jf=table_status($R);$r=fields($R);$x=indexes($R);ksort($x);$qc=foreign_keys($R);ksort($qc);if(!$Jf||empty($r))return
false;$J="CREATE TABLE ".idf_escape($Jf['nspname']).".".idf_escape($Jf['Name'])." (\n    ";foreach($r
as$ic=>$q){$ve=idf_escape($q['field']).' '.$q['full_type'].default_value($q).($q['attnotnull']?" NOT NULL":"");$ff[]=$ve;if(preg_match('~nextval\(\'([^\']+)\'\)~',$q['default'],$Hd)){$sf=$Hd[1];$Df=reset(get_rows(min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q($sf):"SELECT * FROM $sf"));$tf[]=($Mf=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $sf;\n":"")."CREATE SEQUENCE $sf INCREMENT $Df[increment_by] MINVALUE $Df[min_value] MAXVALUE $Df[max_value] START ".($Fa?$Df['last_value']:1)." CACHE $Df[cache_value];";}}if(!empty($tf))$J=implode("\n\n",$tf)."\n\n$J";foreach($x
as$Tc=>$w){switch($w['type']){case'UNIQUE':$ff[]="CONSTRAINT ".idf_escape($Tc)." UNIQUE (".implode(', ',array_map('idf_escape',$w['columns'])).")";break;case'PRIMARY':$ff[]="CONSTRAINT ".idf_escape($Tc)." PRIMARY KEY (".implode(', ',array_map('idf_escape',$w['columns'])).")";break;}}foreach($qc
as$pc=>$oc)$ff[]="CONSTRAINT ".idf_escape($pc)." $oc[definition] ".($oc['deferrable']?'DEFERRABLE':'NOT DEFERRABLE');$J.=implode(",\n    ",$ff)."\n) WITH (oids = ".($Jf['Oid']?'true':'false').");";foreach($x
as$Tc=>$w){if($w['type']=='INDEX')$J.="\n\nCREATE INDEX ".idf_escape($Tc)." ON ".idf_escape($Jf['nspname']).".".idf_escape($Jf['Name'])." USING btree (".implode(', ',array_map('idf_escape',$w['columns'])).");";}if($Jf['Comment'])$J.="\n\nCOMMENT ON TABLE ".idf_escape($Jf['nspname']).".".idf_escape($Jf['Name'])." IS ".q($Jf['Comment']).";";foreach($r
as$ic=>$q){if($q['comment'])$J.="\n\nCOMMENT ON COLUMN ".idf_escape($Jf['nspname']).".".idf_escape($Jf['Name']).".".idf_escape($ic)." IS ".q($q['comment']).";";}return
rtrim($J,';');}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
trigger_sql($R){$Jf=table_status($R);$J="";foreach(triggers($R)as$pg=>$og){$qg=trigger($pg,$Jf['Name']);$J.="\nCREATE TRIGGER ".idf_escape($qg['Trigger'])." $qg[Timing] $qg[Events] ON ".idf_escape($Jf["nspname"]).".".idf_escape($Jf['Name'])." $qg[Type] $qg[Statement];;\n";}return$J;}function
use_sql($l){return"\connect ".idf_escape($l);}function
show_variables(){return
get_key_vals("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
show_status(){}function
convert_field($q){}function
unconvert_field($q,$J){return$J;}function
support($hc){return
preg_match('~^(database|table|columns|sql|indexes|comment|view|'.(min_version(9.3)?'materializedview|':'').'scheme|routine|processlist|sequence|trigger|type|variables|drop_col|kill|dump)$~',$hc);}function
kill_process($X){return
queries("SELECT pg_terminate_backend(".number($X).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){global$i;return$i->result("SHOW max_connections");}$y="pgsql";$vg=array();$Lf=array();foreach(array(lang(25)=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),lang(26)=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),lang(23)=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),lang(27)=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),lang(28)=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"txid_snapshot"=>0),lang(29)=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),)as$z=>$X){$vg+=$X;$Lf[$z]=array_keys($X);}$Bg=array();$je=array("=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");$Bc=array("char_length","lower","round","to_hex","to_timestamp","upper");$Fc=array("avg","count","count distinct","max","min","sum");$Kb=array(array("char"=>"md5","date|time"=>"now",),array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",));}$Fb["oracle"]="Oracle (beta)";if(isset($_GET["oracle"])){$De=array("OCI8","PDO_OCI");define("DRIVER","oracle");if(extension_loaded("oci8")){class
Min_DB{var$extension="oci8",$_link,$_result,$server_info,$affected_rows,$errno,$error;function
_error($Ub,$p){if(ini_bool("html_errors"))$p=html_entity_decode(strip_tags($p));$p=preg_replace('~^[^:]*: ~','',$p);$this->error=$p;}function
connect($O,$V,$G){$this->_link=@oci_new_connect($V,$G,$O,"AL32UTF8");if($this->_link){$this->server_info=oci_server_version($this->_link);return
true;}$p=oci_error();$this->error=$p["message"];return
false;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($l){return
true;}function
query($H,$wg=false){$I=oci_parse($this->_link,$H);$this->error="";if(!$I){$p=oci_error($this->_link);$this->errno=$p["code"];$this->error=$p["message"];return
false;}set_error_handler(array($this,'_error'));$J=@oci_execute($I);restore_error_handler();if($J){if(oci_num_fields($I))return
new
Min_Result($I);$this->affected_rows=oci_num_rows($I);}return$J;}function
multi_query($H){return$this->_result=$this->query($H);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($H,$q=1){$I=$this->query($H);if(!is_object($I)||!oci_fetch($I->_result))return
false;return
oci_result($I->_result,$q);}}class
Min_Result{var$_result,$_offset=1,$num_rows;function
__construct($I){$this->_result=$I;}function
_convert($K){foreach((array)$K
as$z=>$X){if(is_a($X,'OCI-Lob'))$K[$z]=$X->load();}return$K;}function
fetch_assoc(){return$this->_convert(oci_fetch_assoc($this->_result));}function
fetch_row(){return$this->_convert(oci_fetch_row($this->_result));}function
fetch_field(){$f=$this->_offset++;$J=new
stdClass;$J->name=oci_field_name($this->_result,$f);$J->orgname=$J->name;$J->type=oci_field_type($this->_result,$f);$J->charsetnr=(preg_match("~raw|blob|bfile~",$J->type)?63:0);return$J;}function
__destruct(){oci_free_statement($this->_result);}}}elseif(extension_loaded("pdo_oci")){class
Min_DB
extends
Min_PDO{var$extension="PDO_OCI";function
connect($O,$V,$G){$this->dsn("oci:dbname=//$O;charset=AL32UTF8",$V,$G);return
true;}function
select_db($l){return
true;}}}class
Min_Driver
extends
Min_SQL{function
begin(){return
true;}}function
idf_escape($v){return'"'.str_replace('"','""',$v).'"';}function
table($v){return
idf_escape($v);}function
connect(){global$b;$i=new
Min_DB;$k=$b->credentials();if($i->connect($k[0],$k[1],$k[2]))return$i;return$i->error;}function
get_databases(){return
get_vals("SELECT tablespace_name FROM user_tablespaces");}function
limit($H,$Z,$_,$ce=0,$N=" "){return($ce?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $H$Z) t WHERE rownum <= ".($_+$ce).") WHERE rnum > $ce":($_!==null?" * FROM (SELECT $H$Z) WHERE rownum <= ".($_+$ce):" $H$Z"));}function
limit1($R,$H,$Z,$N="\n"){return" $H$Z";}function
db_collation($n,$db){global$i;return$i->result("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
engines(){return
array();}function
logged_user(){global$i;return$i->result("SELECT USER FROM DUAL");}function
tables_list(){return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."
UNION SELECT view_name, 'view' FROM user_views
ORDER BY 1");}function
count_tables($m){return
array();}function
table_status($C=""){$J=array();$lf=q($C);foreach(get_rows('SELECT table_name "Name", \'table\' "Engine", avg_row_len * num_rows "Data_length", num_rows "Rows" FROM all_tables WHERE tablespace_name = '.q(DB).($C!=""?" AND table_name = $lf":"")."
UNION SELECT view_name, 'view', 0, 0 FROM user_views".($C!=""?" WHERE view_name = $lf":"")."
ORDER BY 1")as$K){if($C!="")return$K;$J[$K["Name"]]=$K;}return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return
true;}function
fields($R){$J=array();foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)." ORDER BY column_id")as$K){$U=$K["DATA_TYPE"];$xd="$K[DATA_PRECISION],$K[DATA_SCALE]";if($xd==",")$xd=$K["DATA_LENGTH"];$J[$K["COLUMN_NAME"]]=array("field"=>$K["COLUMN_NAME"],"full_type"=>$U.($xd?"($xd)":""),"type"=>strtolower($U),"length"=>$xd,"default"=>$K["DATA_DEFAULT"],"null"=>($K["NULLABLE"]=="Y"),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),);}return$J;}function
indexes($R,$j=null){$J=array();foreach(get_rows("SELECT uic.*, uc.constraint_type
FROM user_ind_columns uic
LEFT JOIN user_constraints uc ON uic.index_name = uc.constraint_name AND uic.table_name = uc.table_name
WHERE uic.table_name = ".q($R)."
ORDER BY uc.constraint_type, uic.column_position",$j)as$K){$Tc=$K["INDEX_NAME"];$J[$Tc]["type"]=($K["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($K["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$J[$Tc]["columns"][]=$K["COLUMN_NAME"];$J[$Tc]["lengths"][]=($K["CHAR_LENGTH"]&&$K["CHAR_LENGTH"]!=$K["COLUMN_LENGTH"]?$K["CHAR_LENGTH"]:null);$J[$Tc]["descs"][]=($K["DESCEND"]?'1':null);}return$J;}function
view($C){$L=get_rows('SELECT text "select" FROM user_views WHERE view_name = '.q($C));return
reset($L);}function
collations(){return
array();}function
information_schema($n){return
false;}function
error(){global$i;return
h($i->error);}function
explain($i,$H){$i->query("EXPLAIN PLAN FOR $H");return$i->query("SELECT * FROM plan_table");}function
found_rows($S,$Z){}function
alter_table($R,$C,$r,$sc,$hb,$Rb,$e,$Fa,$we){$c=$Gb=array();foreach($r
as$q){$X=$q[1];if($X&&$q[0]!=""&&idf_escape($q[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($q[0])." TO $X[0]");if($X)$c[]=($R!=""?($q[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");else$Gb[]=idf_escape($q[0]);}if($R=="")return
queries("CREATE TABLE ".table($C)." (\n".implode(",\n",$c)."\n)");return(!$c||queries("ALTER TABLE ".table($R)."\n".implode("\n",$c)))&&(!$Gb||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$Gb).")"))&&($R==$C||queries("ALTER TABLE ".table($R)." RENAME TO ".table($C)));}function
foreign_keys($R){$J=array();$H="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($R);foreach(get_rows($H)as$K)$J[$K['NAME']]=array("db"=>$K['DEST_DB'],"table"=>$K['DEST_TABLE'],"source"=>array($K['SRC_COLUMN']),"target"=>array($K['DEST_COLUMN']),"on_delete"=>$K['ON_DELETE'],"on_update"=>null,);return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Ng){return
apply_queries("DROP VIEW",$Ng);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
last_id(){return
0;}function
schemas(){return
get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX'))");}function
get_schema(){global$i;return$i->result("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($kf){global$i;return$i->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($kf));}function
show_variables(){return
get_key_vals('SELECT name, display_value FROM v$parameter');}function
process_list(){return
get_rows('SELECT sess.process AS "process", sess.username AS "user", sess.schemaname AS "schema", sess.status AS "status", sess.wait_class AS "wait_class", sess.seconds_in_wait AS "seconds_in_wait", sql.sql_text AS "sql_text", sess.machine AS "machine", sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
show_status(){$L=get_rows('SELECT * FROM v$instance');return
reset($L);}function
convert_field($q){}function
unconvert_field($q,$J){return$J;}function
support($hc){return
preg_match('~^(columns|database|drop_col|indexes|processlist|scheme|sql|status|table|variables|view|view_trigger)$~',$hc);}$y="oracle";$vg=array();$Lf=array();foreach(array(lang(25)=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),lang(26)=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),lang(23)=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),lang(27)=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),)as$z=>$X){$vg+=$X;$Lf[$z]=array_keys($X);}$Bg=array();$je=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");$Bc=array("length","lower","round","upper");$Fc=array("avg","count","count distinct","max","min","sum");$Kb=array(array("date"=>"current_date","timestamp"=>"current_timestamp",),array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",));}$Fb["mssql"]="MS SQL (beta)";if(isset($_GET["mssql"])){$De=array("SQLSRV","MSSQL","PDO_DBLIB");define("DRIVER","mssql");if(extension_loaded("sqlsrv")){class
Min_DB{var$extension="sqlsrv",$_link,$_result,$server_info,$affected_rows,$errno,$error;function
_get_error(){$this->error="";foreach(sqlsrv_errors()as$p){$this->errno=$p["code"];$this->error.="$p[message]\n";}$this->error=rtrim($this->error);}function
connect($O,$V,$G){$this->_link=@sqlsrv_connect($O,array("UID"=>$V,"PWD"=>$G,"CharacterSet"=>"UTF-8"));if($this->_link){$ad=sqlsrv_server_info($this->_link);$this->server_info=$ad['SQLServerVersion'];}else$this->_get_error();return(bool)$this->_link;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($l){return$this->query("USE ".idf_escape($l));}function
query($H,$wg=false){$I=sqlsrv_query($this->_link,$H);$this->error="";if(!$I){$this->_get_error();return
false;}return$this->store_result($I);}function
multi_query($H){$this->_result=sqlsrv_query($this->_link,$H);$this->error="";if(!$this->_result){$this->_get_error();return
false;}return
true;}function
store_result($I=null){if(!$I)$I=$this->_result;if(!$I)return
false;if(sqlsrv_field_metadata($I))return
new
Min_Result($I);$this->affected_rows=sqlsrv_rows_affected($I);return
true;}function
next_result(){return$this->_result?sqlsrv_next_result($this->_result):null;}function
result($H,$q=0){$I=$this->query($H);if(!is_object($I))return
false;$K=$I->fetch_row();return$K[$q];}}class
Min_Result{var$_result,$_offset=0,$_fields,$num_rows;function
__construct($I){$this->_result=$I;}function
_convert($K){foreach((array)$K
as$z=>$X){if(is_a($X,'DateTime'))$K[$z]=$X->format("Y-m-d H:i:s");}return$K;}function
fetch_assoc(){return$this->_convert(sqlsrv_fetch_array($this->_result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->_convert(sqlsrv_fetch_array($this->_result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->_fields)$this->_fields=sqlsrv_field_metadata($this->_result);$q=$this->_fields[$this->_offset++];$J=new
stdClass;$J->name=$q["Name"];$J->orgname=$q["Name"];$J->type=($q["Type"]==1?254:0);return$J;}function
seek($ce){for($t=0;$t<$ce;$t++)sqlsrv_fetch($this->_result);}function
__destruct(){sqlsrv_free_stmt($this->_result);}}}elseif(extension_loaded("mssql")){class
Min_DB{var$extension="MSSQL",$_link,$_result,$server_info,$affected_rows,$error;function
connect($O,$V,$G){$this->_link=@mssql_connect($O,$V,$G);if($this->_link){$I=$this->query("SELECT SERVERPROPERTY('ProductLevel'), SERVERPROPERTY('Edition')");$K=$I->fetch_row();$this->server_info=$this->result("sp_server_info 2",2)." [$K[0]] $K[1]";}else$this->error=mssql_get_last_message();return(bool)$this->_link;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($l){return
mssql_select_db($l);}function
query($H,$wg=false){$I=@mssql_query($H,$this->_link);$this->error="";if(!$I){$this->error=mssql_get_last_message();return
false;}if($I===true){$this->affected_rows=mssql_rows_affected($this->_link);return
true;}return
new
Min_Result($I);}function
multi_query($H){return$this->_result=$this->query($H);}function
store_result(){return$this->_result;}function
next_result(){return
mssql_next_result($this->_result->_result);}function
result($H,$q=0){$I=$this->query($H);if(!is_object($I))return
false;return
mssql_result($I->_result,0,$q);}}class
Min_Result{var$_result,$_offset=0,$_fields,$num_rows;function
__construct($I){$this->_result=$I;$this->num_rows=mssql_num_rows($I);}function
fetch_assoc(){return
mssql_fetch_assoc($this->_result);}function
fetch_row(){return
mssql_fetch_row($this->_result);}function
num_rows(){return
mssql_num_rows($this->_result);}function
fetch_field(){$J=mssql_fetch_field($this->_result);$J->orgtable=$J->table;$J->orgname=$J->name;return$J;}function
seek($ce){mssql_data_seek($this->_result,$ce);}function
__destruct(){mssql_free_result($this->_result);}}}elseif(extension_loaded("pdo_dblib")){class
Min_DB
extends
Min_PDO{var$extension="PDO_DBLIB";function
connect($O,$V,$G){$this->dsn("dblib:charset=utf8;host=".str_replace(":",";unix_socket=",preg_replace('~:(\\d)~',';port=\\1',$O)),$V,$G);return
true;}function
select_db($l){return$this->query("USE ".idf_escape($l));}}}class
Min_Driver
extends
Min_SQL{function
insertUpdate($R,$L,$Fe){foreach($L
as$P){$Cg=array();$Z=array();foreach($P
as$z=>$X){$Cg[]="$z = $X";if(isset($Fe[idf_unescape($z)]))$Z[]="$z = $X";}if(!queries("MERGE ".table($R)." USING (VALUES(".implode(", ",$P).")) AS source (c".implode(", c",range(1,count($P))).") ON ".implode(" AND ",$Z)." WHEN MATCHED THEN UPDATE SET ".implode(", ",$Cg)." WHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($P)).") VALUES (".implode(", ",$P).");"))return
false;}return
true;}function
begin(){return
queries("BEGIN TRANSACTION");}}function
idf_escape($v){return"[".str_replace("]","]]",$v)."]";}function
table($v){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($v);}function
connect(){global$b;$i=new
Min_DB;$k=$b->credentials();if($i->connect($k[0],$k[1],$k[2]))return$i;return$i->error;}function
get_databases(){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($H,$Z,$_,$ce=0,$N=" "){return($_!==null?" TOP (".($_+$ce).")":"")." $H$Z";}function
limit1($R,$H,$Z,$N="\n"){return
limit($H,$Z,1,0,$N);}function
db_collation($n,$db){global$i;return$i->result("SELECT collation_name FROM sys.databases WHERE name = ".q($n));}function
engines(){return
array();}function
logged_user(){global$i;return$i->result("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables($m){global$i;$J=array();foreach($m
as$n){$i->select_db($n);$J[$n]=$i->result("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$J;}function
table_status($C=""){$J=array();foreach(get_rows("SELECT name AS Name, type_desc AS Engine FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($C!=""?"AND name = ".q($C):"ORDER BY name"))as$K){if($C!="")return$K;$J[$K["Name"]]=$K;}return$J;}function
is_view($S){return$S["Engine"]=="VIEW";}function
fk_support($S){return
true;}function
fields($R){$J=array();foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name, t.name type, CAST(d.definition as text) [default]
FROM sys.all_columns c
JOIN sys.all_objects o ON c.object_id = o.object_id
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.parent_column_id
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.type IN ('S', 'U', 'V') AND o.name = ".q($R))as$K){$U=$K["type"];$xd=(preg_match("~char|binary~",$U)?$K["max_length"]:($U=="decimal"?"$K[precision],$K[scale]":""));$J[$K["name"]]=array("field"=>$K["name"],"full_type"=>$U.($xd?"($xd)":""),"type"=>$U,"length"=>$xd,"default"=>$K["default"],"null"=>$K["is_nullable"],"auto_increment"=>$K["is_identity"],"collation"=>$K["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),"primary"=>$K["is_identity"],);}return$J;}function
indexes($R,$j=null){$J=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$j)as$K){$C=$K["name"];$J[$C]["type"]=($K["is_primary_key"]?"PRIMARY":($K["is_unique"]?"UNIQUE":"INDEX"));$J[$C]["lengths"]=array();$J[$C]["columns"][$K["key_ordinal"]]=$K["column_name"];$J[$C]["descs"][$K["key_ordinal"]]=($K["is_descending_key"]?'1':null);}return$J;}function
view($C){global$i;return
array("select"=>preg_replace('~^(?:[^[]|\\[[^]]*])*\\s+AS\\s+~isU','',$i->result("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($C))));}function
collations(){$J=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$e)$J[preg_replace('~_.*~','',$e)][]=$e;return$J;}function
information_schema($n){return
false;}function
error(){global$i;return
nl_br(h(preg_replace('~^(\\[[^]]*])+~m','',$i->error)));}function
create_database($n,$e){return
queries("CREATE DATABASE ".idf_escape($n).(preg_match('~^[a-z0-9_]+$~i',$e)?" COLLATE $e":""));}function
drop_databases($m){return
queries("DROP DATABASE ".implode(", ",array_map('idf_escape',$m)));}function
rename_database($C,$e){if(preg_match('~^[a-z0-9_]+$~i',$e))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $e");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($C));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$C,$r,$sc,$hb,$Rb,$e,$Fa,$we){$c=array();foreach($r
as$q){$f=idf_escape($q[0]);$X=$q[1];if(!$X)$c["DROP"][]=" COLUMN $f";else{$X[1]=preg_replace("~( COLLATE )'(\\w+)'~","\\1\\2",$X[1]);if($q[0]=="")$c["ADD"][]="\n  ".implode("",$X).($R==""?substr($sc[$X[0]],16+strlen($X[0])):"");else{unset($X[6]);if($f!=$X[0])queries("EXEC sp_rename ".q(table($R).".$f").", ".q(idf_unescape($X[0])).", 'COLUMN'");$c["ALTER COLUMN ".implode("",$X)][]="";}}}if($R=="")return
queries("CREATE TABLE ".table($C)." (".implode(",",(array)$c["ADD"])."\n)");if($R!=$C)queries("EXEC sp_rename ".q(table($R)).", ".q($C));if($sc)$c[""]=$sc;foreach($c
as$z=>$X){if(!queries("ALTER TABLE ".idf_escape($C)." $z".implode(",",$X)))return
false;}return
true;}function
alter_indexes($R,$c){$w=array();$Gb=array();foreach($c
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY")$Gb[]=idf_escape($X[1]);else$w[]=idf_escape($X[1])." ON ".table($R);}elseif(!queries(($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")"))return
false;}return(!$w||queries("DROP INDEX ".implode(", ",$w)))&&(!$Gb||queries("ALTER TABLE ".table($R)." DROP ".implode(", ",$Gb)));}function
last_id(){global$i;return$i->result("SELECT SCOPE_IDENTITY()");}function
explain($i,$H){$i->query("SET SHOWPLAN_ALL ON");$J=$i->query($H);$i->query("SET SHOWPLAN_ALL OFF");return$J;}function
found_rows($S,$Z){}function
foreign_keys($R){$J=array();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R))as$K){$vc=&$J[$K["FK_NAME"]];$vc["table"]=$K["PKTABLE_NAME"];$vc["source"][]=$K["FKCOLUMN_NAME"];$vc["target"][]=$K["PKCOLUMN_NAME"];}return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Ng){return
queries("DROP VIEW ".implode(", ",array_map('table',$Ng)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('table',$T)));}function
move_tables($T,$Ng,$Vf){return
apply_queries("ALTER SCHEMA ".idf_escape($Vf)." TRANSFER",array_merge($T,$Ng));}function
trigger($C){if($C=="")return
array();$L=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($C));$J=reset($L);if($J)$J["Statement"]=preg_replace('~^.+\\s+AS\\s+~isU','',$J["text"]);return$J;}function
triggers($R){$J=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($R))as$K)$J[$K["name"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){global$i;if($_GET["ns"]!="")return$_GET["ns"];return$i->result("SELECT SCHEMA_NAME()");}function
set_schema($jf){return
true;}function
use_sql($l){return"USE ".idf_escape($l);}function
show_variables(){return
array();}function
show_status(){return
array();}function
convert_field($q){}function
unconvert_field($q,$J){return$J;}function
support($hc){return
preg_match('~^(columns|database|drop_col|indexes|scheme|sql|table|trigger|view|view_trigger)$~',$hc);}$y="mssql";$vg=array();$Lf=array();foreach(array(lang(25)=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20),lang(26)=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10),lang(23)=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823),lang(27)=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),)as$z=>$X){$vg+=$X;$Lf[$z]=array_keys($X);}$Bg=array();$je=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");$Bc=array("len","lower","round","upper");$Fc=array("avg","count","count distinct","max","min","sum");$Kb=array(array("date|time"=>"getdate",),array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",));}$Fb['firebird']='Firebird (alpha)';if(isset($_GET["firebird"])){$De=array("interbase");define("DRIVER","firebird");if(extension_loaded("interbase")){class
Min_DB{var$extension="Firebird",$server_info,$affected_rows,$errno,$error,$_link,$_result;function
connect($O,$V,$G){$this->_link=ibase_connect($O,$V,$G);if($this->_link){$Fg=explode(':',$O);$this->service_link=ibase_service_attach($Fg[0],$V,$G);$this->server_info=ibase_server_info($this->service_link,IBASE_SVC_SERVER_VERSION);}else{$this->errno=ibase_errcode();$this->error=ibase_errmsg();}return(bool)$this->_link;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($l){return($l=="domain");}function
query($H,$wg=false){$I=ibase_query($H,$this->_link);if(!$I){$this->errno=ibase_errcode();$this->error=ibase_errmsg();return
false;}$this->error="";if($I===true){$this->affected_rows=ibase_affected_rows($this->_link);return
true;}return
new
Min_Result($I);}function
multi_query($H){return$this->_result=$this->query($H);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($H,$q=0){$I=$this->query($H);if(!$I||!$I->num_rows)return
false;$K=$I->fetch_row();return$K[$q];}}class
Min_Result{var$num_rows,$_result,$_offset=0;function
__construct($I){$this->_result=$I;}function
fetch_assoc(){return
ibase_fetch_assoc($this->_result);}function
fetch_row(){return
ibase_fetch_row($this->_result);}function
fetch_field(){$q=ibase_field_info($this->_result,$this->_offset++);return(object)array('name'=>$q['name'],'orgname'=>$q['name'],'type'=>$q['type'],'charsetnr'=>$q['length'],);}function
__destruct(){ibase_free_result($this->_result);}}}class
Min_Driver
extends
Min_SQL{}function
idf_escape($v){return'"'.str_replace('"','""',$v).'"';}function
table($v){return
idf_escape($v);}function
connect(){global$b;$i=new
Min_DB;$k=$b->credentials();if($i->connect($k[0],$k[1],$k[2]))return$i;return$i->error;}function
get_databases($rc){return
array("domain");}function
limit($H,$Z,$_,$ce=0,$N=" "){$J='';$J.=($_!==null?$N."FIRST $_".($ce?" SKIP $ce":""):"");$J.=" $H$Z";return$J;}function
limit1($R,$H,$Z,$N="\n"){return
limit($H,$Z,1,0,$N);}function
db_collation($n,$db){}function
engines(){return
array();}function
logged_user(){global$b;$k=$b->credentials();return$k[1];}function
tables_list(){global$i;$H='SELECT RDB$RELATION_NAME FROM rdb$relations WHERE rdb$system_flag = 0';$I=ibase_query($i->_link,$H);$J=array();while($K=ibase_fetch_assoc($I))$J[$K['RDB$RELATION_NAME']]='table';ksort($J);return$J;}function
count_tables($m){return
array();}function
table_status($C="",$gc=false){global$i;$J=array();$tb=tables_list();foreach($tb
as$w=>$X){$w=trim($w);$J[$w]=array('Name'=>$w,'Engine'=>'standard',);if($C==$w)return$J[$w];}return$J;}function
is_view($S){return
false;}function
fk_support($S){return
preg_match('~InnoDB|IBMDB2I~i',$S["Engine"]);}function
fields($R){global$i;$J=array();$H='SELECT r.RDB$FIELD_NAME AS field_name,
r.RDB$DESCRIPTION AS field_description,
r.RDB$DEFAULT_VALUE AS field_default_value,
r.RDB$NULL_FLAG AS field_not_null_constraint,
f.RDB$FIELD_LENGTH AS field_length,
f.RDB$FIELD_PRECISION AS field_precision,
f.RDB$FIELD_SCALE AS field_scale,
CASE f.RDB$FIELD_TYPE
WHEN 261 THEN \'BLOB\'
WHEN 14 THEN \'CHAR\'
WHEN 40 THEN \'CSTRING\'
WHEN 11 THEN \'D_FLOAT\'
WHEN 27 THEN \'DOUBLE\'
WHEN 10 THEN \'FLOAT\'
WHEN 16 THEN \'INT64\'
WHEN 8 THEN \'INTEGER\'
WHEN 9 THEN \'QUAD\'
WHEN 7 THEN \'SMALLINT\'
WHEN 12 THEN \'DATE\'
WHEN 13 THEN \'TIME\'
WHEN 35 THEN \'TIMESTAMP\'
WHEN 37 THEN \'VARCHAR\'
ELSE \'UNKNOWN\'
END AS field_type,
f.RDB$FIELD_SUB_TYPE AS field_subtype,
coll.RDB$COLLATION_NAME AS field_collation,
cset.RDB$CHARACTER_SET_NAME AS field_charset
FROM RDB$RELATION_FIELDS r
LEFT JOIN RDB$FIELDS f ON r.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME
LEFT JOIN RDB$COLLATIONS coll ON f.RDB$COLLATION_ID = coll.RDB$COLLATION_ID
LEFT JOIN RDB$CHARACTER_SETS cset ON f.RDB$CHARACTER_SET_ID = cset.RDB$CHARACTER_SET_ID
WHERE r.RDB$RELATION_NAME = '.q($R).'
ORDER BY r.RDB$FIELD_POSITION';$I=ibase_query($i->_link,$H);while($K=ibase_fetch_assoc($I))$J[trim($K['FIELD_NAME'])]=array("field"=>trim($K["FIELD_NAME"]),"full_type"=>trim($K["FIELD_TYPE"]),"type"=>trim($K["FIELD_SUB_TYPE"]),"default"=>trim($K['FIELD_DEFAULT_VALUE']),"null"=>(trim($K["FIELD_NOT_NULL_CONSTRAINT"])=="YES"),"auto_increment"=>'0',"collation"=>trim($K["FIELD_COLLATION"]),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),"comment"=>trim($K["FIELD_DESCRIPTION"]),);return$J;}function
indexes($R,$j=null){$J=array();return$J;}function
foreign_keys($R){return
array();}function
collations(){return
array();}function
information_schema($n){return
false;}function
error(){global$i;return
h($i->error);}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($jf){return
true;}function
support($hc){return
preg_match("~^(columns|sql|status|table)$~",$hc);}$y="firebird";$je=array("=");$Bc=array();$Fc=array();$Kb=array();}$Fb["simpledb"]="SimpleDB";if(isset($_GET["simpledb"])){$De=array("SimpleXML + allow_url_fopen");define("DRIVER","simpledb");if(class_exists('SimpleXMLElement')&&ini_bool('allow_url_fopen')){class
Min_DB{var$extension="SimpleXML",$server_info='2009-04-15',$error,$timeout,$next,$affected_rows,$_result;function
select_db($l){return($l=="domain");}function
query($H,$wg=false){$F=array('SelectExpression'=>$H,'ConsistentRead'=>'true');if($this->next)$F['NextToken']=$this->next;$I=sdb_request_all('Select','Item',$F,$this->timeout);if($I===false)return$I;if(preg_match('~^\s*SELECT\s+COUNT\(~i',$H)){$Pf=0;foreach($I
as$jd)$Pf+=$jd->Attribute->Value;$I=array((object)array('Attribute'=>array((object)array('Name'=>'Count','Value'=>$Pf,))));}return
new
Min_Result($I);}function
multi_query($H){return$this->_result=$this->query($H);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}}class
Min_Result{var$num_rows,$_rows=array(),$_offset=0;function
__construct($I){foreach($I
as$jd){$K=array();if($jd->Name!='')$K['itemName()']=(string)$jd->Name;foreach($jd->Attribute
as$Ca){$C=$this->_processValue($Ca->Name);$Y=$this->_processValue($Ca->Value);if(isset($K[$C])){$K[$C]=(array)$K[$C];$K[$C][]=$Y;}else$K[$C]=$Y;}$this->_rows[]=$K;foreach($K
as$z=>$X){if(!isset($this->_rows[0][$z]))$this->_rows[0][$z]=null;}}$this->num_rows=count($this->_rows);}function
_processValue($Mb){return(is_object($Mb)&&$Mb['encoding']=='base64'?base64_decode($Mb):(string)$Mb);}function
fetch_assoc(){$K=current($this->_rows);if(!$K)return$K;$J=array();foreach($this->_rows[0]as$z=>$X)$J[$z]=$K[$z];next($this->_rows);return$J;}function
fetch_row(){$J=$this->fetch_assoc();if(!$J)return$J;return
array_values($J);}function
fetch_field(){$od=array_keys($this->_rows[0]);return(object)array('name'=>$od[$this->_offset++]);}}}class
Min_Driver
extends
Min_SQL{public$Fe="itemName()";function
_chunkRequest($Rc,$ra,$F,$Zb=array()){global$i;foreach(array_chunk($Rc,25)as$Ya){$ue=$F;foreach($Ya
as$t=>$u){$ue["Item.$t.ItemName"]=$u;foreach($Zb
as$z=>$X)$ue["Item.$t.$z"]=$X;}if(!sdb_request($ra,$ue))return
false;}$i->affected_rows=count($Rc);return
true;}function
_extractIds($R,$Oe,$_){$J=array();if(preg_match_all("~itemName\(\) = (('[^']*+')+)~",$Oe,$Hd))$J=array_map('idf_unescape',$Hd[1]);else{foreach(sdb_request_all('Select','Item',array('SelectExpression'=>'SELECT itemName() FROM '.table($R).$Oe.($_?" LIMIT 1":"")))as$jd)$J[]=$jd->Name;}return$J;}function
select($R,$M,$Z,$Cc,$me=array(),$_=1,$E=0,$He=false){global$i;$i->next=$_GET["next"];$J=parent::select($R,$M,$Z,$Cc,$me,$_,$E,$He);$i->next=0;return$J;}function
delete($R,$Oe,$_=0){return$this->_chunkRequest($this->_extractIds($R,$Oe,$_),'BatchDeleteAttributes',array('DomainName'=>$R));}function
update($R,$P,$Oe,$_=0,$N="\n"){$yb=array();$ed=array();$t=0;$Rc=$this->_extractIds($R,$Oe,$_);$u=idf_unescape($P["`itemName()`"]);unset($P["`itemName()`"]);foreach($P
as$z=>$X){$z=idf_unescape($z);if($X=="NULL"||($u!=""&&array($u)!=$Rc))$yb["Attribute.".count($yb).".Name"]=$z;if($X!="NULL"){foreach((array)$X
as$kd=>$W){$ed["Attribute.$t.Name"]=$z;$ed["Attribute.$t.Value"]=(is_array($X)?$W:idf_unescape($W));if(!$kd)$ed["Attribute.$t.Replace"]="true";$t++;}}}$F=array('DomainName'=>$R);return(!$ed||$this->_chunkRequest(($u!=""?array($u):$Rc),'BatchPutAttributes',$F,$ed))&&(!$yb||$this->_chunkRequest($Rc,'BatchDeleteAttributes',$F,$yb));}function
insert($R,$P){$F=array("DomainName"=>$R);$t=0;foreach($P
as$C=>$Y){if($Y!="NULL"){$C=idf_unescape($C);if($C=="itemName()")$F["ItemName"]=idf_unescape($Y);else{foreach((array)$Y
as$X){$F["Attribute.$t.Name"]=$C;$F["Attribute.$t.Value"]=(is_array($Y)?$X:idf_unescape($Y));$t++;}}}}return
sdb_request('PutAttributes',$F);}function
insertUpdate($R,$L,$Fe){foreach($L
as$P){if(!$this->update($R,$P,"WHERE `itemName()` = ".q($P["`itemName()`"])))return
false;}return
true;}function
begin(){return
false;}function
commit(){return
false;}function
rollback(){return
false;}}function
connect(){return
new
Min_DB;}function
support($hc){return
preg_match('~sql~',$hc);}function
logged_user(){global$b;$k=$b->credentials();return$k[1];}function
get_databases(){return
array("domain");}function
collations(){return
array();}function
db_collation($n,$db){}function
tables_list(){global$i;$J=array();foreach(sdb_request_all('ListDomains','DomainName')as$R)$J[(string)$R]='table';if($i->error&&defined("PAGE_HEADER"))echo"<p class='error'>".error()."\n";return$J;}function
table_status($C="",$gc=false){$J=array();foreach(($C!=""?array($C=>true):tables_list())as$R=>$U){$K=array("Name"=>$R,"Auto_increment"=>"");if(!$gc){$Pd=sdb_request('DomainMetadata',array('DomainName'=>$R));if($Pd){foreach(array("Rows"=>"ItemCount","Data_length"=>"ItemNamesSizeBytes","Index_length"=>"AttributeValuesSizeBytes","Data_free"=>"AttributeNamesSizeBytes",)as$z=>$X)$K[$z]=(string)$Pd->$X;}}if($C!="")return$K;$J[$R]=$K;}return$J;}function
explain($i,$H){}function
error(){global$i;return
h($i->error);}function
information_schema(){}function
is_view($S){}function
indexes($R,$j=null){return
array(array("type"=>"PRIMARY","columns"=>array("itemName()")),);}function
fields($R){return
fields_from_edit();}function
foreign_keys($R){return
array();}function
table($v){return
idf_escape($v);}function
idf_escape($v){return"`".str_replace("`","``",$v)."`";}function
limit($H,$Z,$_,$ce=0,$N=" "){return" $H$Z".($_!==null?$N."LIMIT $_":"");}function
unconvert_field($q,$J){return$J;}function
fk_support($S){}function
engines(){return
array();}function
alter_table($R,$C,$r,$sc,$hb,$Rb,$e,$Fa,$we){return($R==""&&sdb_request('CreateDomain',array('DomainName'=>$C)));}function
drop_tables($T){foreach($T
as$R){if(!sdb_request('DeleteDomain',array('DomainName'=>$R)))return
false;}return
true;}function
count_tables($m){foreach($m
as$n)return
array($n=>count(tables_list()));}function
found_rows($S,$Z){return($Z?null:$S["Rows"]);}function
last_id(){}function
hmac($wa,$tb,$z,$Se=false){$Oa=64;if(strlen($z)>$Oa)$z=pack("H*",$wa($z));$z=str_pad($z,$Oa,"\0");$ld=$z^str_repeat("\x36",$Oa);$md=$z^str_repeat("\x5C",$Oa);$J=$wa($md.pack("H*",$wa($ld.$tb)));if($Se)$J=pack("H*",$J);return$J;}function
sdb_request($ra,$F=array()){global$b,$i;list($Oc,$F['AWSAccessKeyId'],$mf)=$b->credentials();$F['Action']=$ra;$F['Timestamp']=gmdate('Y-m-d\TH:i:s+00:00');$F['Version']='2009-04-15';$F['SignatureVersion']=2;$F['SignatureMethod']='HmacSHA1';ksort($F);$H='';foreach($F
as$z=>$X)$H.='&'.rawurlencode($z).'='.rawurlencode($X);$H=str_replace('%7E','~',substr($H,1));$H.="&Signature=".urlencode(base64_encode(hmac('sha1',"POST\n".preg_replace('~^https?://~','',$Oc)."\n/\n$H",$mf,true)));@ini_set('track_errors',1);$kc=@file_get_contents((preg_match('~^https?://~',$Oc)?$Oc:"http://$Oc"),false,stream_context_create(array('http'=>array('method'=>'POST','content'=>$H,'ignore_errors'=>1,))));if(!$kc){$i->error=$php_errormsg;return
false;}libxml_use_internal_errors(true);$Yg=simplexml_load_string($kc);if(!$Yg){$p=libxml_get_last_error();$i->error=$p->message;return
false;}if($Yg->Errors){$p=$Yg->Errors->Error;$i->error="$p->Message ($p->Code)";return
false;}$i->error='';$Uf=$ra."Result";return($Yg->$Uf?$Yg->$Uf:true);}function
sdb_request_all($ra,$Uf,$F=array(),$bg=0){$J=array();$Hf=($bg?microtime(true):0);$_=(preg_match('~LIMIT\s+(\d+)\s*$~i',$F['SelectExpression'],$B)?$B[1]:0);do{$Yg=sdb_request($ra,$F);if(!$Yg)break;foreach($Yg->$Uf
as$Mb)$J[]=$Mb;if($_&&count($J)>=$_){$_GET["next"]=$Yg->NextToken;break;}if($bg&&microtime(true)-$Hf>$bg)return
false;$F['NextToken']=$Yg->NextToken;if($_)$F['SelectExpression']=preg_replace('~\d+\s*$~',$_-count($J),$F['SelectExpression']);}while($Yg->NextToken);return$J;}$y="simpledb";$je=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","IS NOT NULL");$Bc=array();$Fc=array("count");$Kb=array(array("json"));}$Fb["mongo"]="MongoDB";if(isset($_GET["mongo"])){$De=array("mongo","mongodb");define("DRIVER","mongo");if(class_exists('MongoDB')){class
Min_DB{var$extension="Mongo",$error,$last_id,$_link,$_db;function
connect($O,$V,$G){global$b;$n=$b->database();$D=array();if($V!=""){$D["username"]=$V;$D["password"]=$G;}if($n!="")$D["db"]=$n;try{$this->_link=@new
MongoClient("mongodb://$O",$D);return
true;}catch(Exception$Wb){$this->error=$Wb->getMessage();return
false;}}function
query($H){return
false;}function
select_db($l){try{$this->_db=$this->_link->selectDB($l);return
true;}catch(Exception$Wb){$this->error=$Wb->getMessage();return
false;}}function
quote($Q){return$Q;}}class
Min_Result{var$num_rows,$_rows=array(),$_offset=0,$_charset=array();function
__construct($I){foreach($I
as$jd){$K=array();foreach($jd
as$z=>$X){if(is_a($X,'MongoBinData'))$this->_charset[$z]=63;$K[$z]=(is_a($X,'MongoId')?'ObjectId("'.strval($X).'")':(is_a($X,'MongoDate')?gmdate("Y-m-d H:i:s",$X->sec)." GMT":(is_a($X,'MongoBinData')?$X->bin:(is_a($X,'MongoRegex')?strval($X):(is_object($X)?get_class($X):$X)))));}$this->_rows[]=$K;foreach($K
as$z=>$X){if(!isset($this->_rows[0][$z]))$this->_rows[0][$z]=null;}}$this->num_rows=count($this->_rows);}function
fetch_assoc(){$K=current($this->_rows);if(!$K)return$K;$J=array();foreach($this->_rows[0]as$z=>$X)$J[$z]=$K[$z];next($this->_rows);return$J;}function
fetch_row(){$J=$this->fetch_assoc();if(!$J)return$J;return
array_values($J);}function
fetch_field(){$od=array_keys($this->_rows[0]);$C=$od[$this->_offset++];return(object)array('name'=>$C,'charsetnr'=>$this->_charset[$C],);}}class
Min_Driver
extends
Min_SQL{public$Fe="_id";function
select($R,$M,$Z,$Cc,$me=array(),$_=1,$E=0,$He=false){$M=($M==array("*")?array():array_fill_keys($M,true));$Af=array();foreach($me
as$X){$X=preg_replace('~ DESC$~','',$X,1,$nb);$Af[$X]=($nb?-1:1);}return
new
Min_Result($this->_conn->_db->selectCollection($R)->find(array(),$M)->sort($Af)->limit($_!=""?+$_:0)->skip($E*$_));}function
insert($R,$P){try{$J=$this->_conn->_db->selectCollection($R)->insert($P);$this->_conn->errno=$J['code'];$this->_conn->error=$J['err'];$this->_conn->last_id=$P['_id'];return!$J['err'];}catch(Exception$Wb){$this->_conn->error=$Wb->getMessage();return
false;}}}function
get_databases($rc){global$i;$J=array();$vb=$i->_link->listDBs();foreach($vb['databases']as$n)$J[]=$n['name'];return$J;}function
count_tables($m){global$i;$J=array();foreach($m
as$n)$J[$n]=count($i->_link->selectDB($n)->getCollectionNames(true));return$J;}function
tables_list(){global$i;return
array_fill_keys($i->_db->getCollectionNames(true),'table');}function
drop_databases($m){global$i;foreach($m
as$n){$bf=$i->_link->selectDB($n)->drop();if(!$bf['ok'])return
false;}return
true;}function
indexes($R,$j=null){global$i;$J=array();foreach($i->_db->selectCollection($R)->getIndexInfo()as$w){$Ab=array();foreach($w["key"]as$f=>$U)$Ab[]=($U==-1?'1':null);$J[$w["name"]]=array("type"=>($w["name"]=="_id_"?"PRIMARY":($w["unique"]?"UNIQUE":"INDEX")),"columns"=>array_keys($w["key"]),"lengths"=>array(),"descs"=>$Ab,);}return$J;}function
fields($R){return
fields_from_edit();}function
found_rows($S,$Z){global$i;return$i->_db->selectCollection($_GET["select"])->count($Z);}$je=array("=");}elseif(class_exists('MongoDB\Driver\Manager')){class
Min_DB{var$extension="MongoDB",$error,$last_id;var$_link;var$_db,$_db_name;function
connect($O,$V,$G){global$b;$n=$b->database();$D=array();if($V!=""){$D["username"]=$V;$D["password"]=$G;}if($n!="")$D["db"]=$n;try{$d='MongoDB\Driver\Manager';$this->_link=new$d("mongodb://$O",$D);return
true;}catch(Exception$Wb){$this->error=$Wb->getMessage();return
false;}}function
query($H){return
false;}function
select_db($l){try{$this->_db_name=$l;return
true;}catch(Exception$Wb){$this->error=$Wb->getMessage();return
false;}}function
quote($Q){return$Q;}}class
Min_Result{var$num_rows,$_rows=array(),$_offset=0,$_charset=array();function
__construct($I){foreach($I
as$jd){$K=array();foreach($jd
as$z=>$X){if(is_a($X,'MongoDB\BSON\Binary'))$this->_charset[$z]=63;$K[$z]=(is_a($X,'MongoDB\BSON\ObjectID')?'MongoDB\BSON\ObjectID("'.strval($X).'")':(is_a($X,'MongoDB\BSON\UTCDatetime')?$X->toDateTime()->format('Y-m-d H:i:s'):(is_a($X,'MongoDB\BSON\Binary')?$X->bin:(is_a($X,'MongoDB\BSON\Regex')?strval($X):(is_object($X)?json_encode($X,256):$X)))));}$this->_rows[]=$K;foreach($K
as$z=>$X){if(!isset($this->_rows[0][$z]))$this->_rows[0][$z]=null;}}$this->num_rows=$I->count;}function
fetch_assoc(){$K=current($this->_rows);if(!$K)return$K;$J=array();foreach($this->_rows[0]as$z=>$X)$J[$z]=$K[$z];next($this->_rows);return$J;}function
fetch_row(){$J=$this->fetch_assoc();if(!$J)return$J;return
array_values($J);}function
fetch_field(){$od=array_keys($this->_rows[0]);$C=$od[$this->_offset++];return(object)array('name'=>$C,'charsetnr'=>$this->_charset[$C],);}}class
Min_Driver
extends
Min_SQL{public$Fe="_id";function
select($R,$M,$Z,$Cc,$me=array(),$_=1,$E=0,$He=false){global$i;$M=($M==array("*")?array():array_fill_keys($M,1));if(count($M)&&!isset($M['_id']))$M['_id']=0;$Z=where_to_query($Z);$Af=array();foreach($me
as$X){$X=preg_replace('~ DESC$~','',$X,1,$nb);$Af[$X]=($nb?-1:1);}if(isset($_GET['limit'])&&is_numeric($_GET['limit'])&&$_GET['limit']>0)$_=$_GET['limit'];$_=min(200,max(1,(int)$_));$zf=$E*$_;$d='MongoDB\Driver\Query';$H=new$d($Z,array('projection'=>$M,'limit'=>$_,'skip'=>$zf,'sort'=>$Af));$ef=$i->_link->executeQuery("$i->_db_name.$R",$H);return
new
Min_Result($ef);}function
update($R,$P,$Oe,$_=0,$N="\n"){global$i;$n=$i->_db_name;$Z=sql_query_where_parser($Oe);$d='MongoDB\Driver\BulkWrite';$Sa=new$d(array());if(isset($P['_id']))unset($P['_id']);$Xe=array();foreach($P
as$z=>$Y){if($Y=='NULL'){$Xe[$z]=1;unset($P[$z]);}}$Cg=array('$set'=>$P);if(count($Xe))$Cg['$unset']=$Xe;$Sa->update($Z,$Cg,array('upsert'=>false));$ef=$i->_link->executeBulkWrite("$n.$R",$Sa);$i->affected_rows=$ef->getModifiedCount();return
true;}function
delete($R,$Oe,$_=0){global$i;$n=$i->_db_name;$Z=sql_query_where_parser($Oe);$d='MongoDB\Driver\BulkWrite';$Sa=new$d(array());$Sa->delete($Z,array('limit'=>$_));$ef=$i->_link->executeBulkWrite("$n.$R",$Sa);$i->affected_rows=$ef->getDeletedCount();return
true;}function
insert($R,$P){global$i;$n=$i->_db_name;$d='MongoDB\Driver\BulkWrite';$Sa=new$d(array());if(isset($P['_id'])&&empty($P['_id']))unset($P['_id']);$Sa->insert($P);$ef=$i->_link->executeBulkWrite("$n.$R",$Sa);$i->affected_rows=$ef->getInsertedCount();return
true;}}function
get_databases($rc){global$i;$J=array();$d='MongoDB\Driver\Command';$gb=new$d(array('listDatabases'=>1));$ef=$i->_link->executeCommand('admin',$gb);foreach($ef
as$vb){foreach($vb->databases
as$n)$J[]=$n->name;}return$J;}function
count_tables($m){$J=array();return$J;}function
tables_list(){global$i;$d='MongoDB\Driver\Command';$gb=new$d(array('listCollections'=>1));$ef=$i->_link->executeCommand($i->_db_name,$gb);$eb=array();foreach($ef
as$I)$eb[$I->name]='table';return$eb;}function
drop_databases($m){return
false;}function
indexes($R,$j=null){global$i;$J=array();$d='MongoDB\Driver\Command';$gb=new$d(array('listIndexes'=>$R));$ef=$i->_link->executeCommand($i->_db_name,$gb);foreach($ef
as$w){$Ab=array();$g=array();foreach(get_object_vars($w->key)as$f=>$U){$Ab[]=($U==-1?'1':null);$g[]=$f;}$J[$w->name]=array("type"=>($w->name=="_id_"?"PRIMARY":(isset($w->unique)?"UNIQUE":"INDEX")),"columns"=>$g,"lengths"=>array(),"descs"=>$Ab,);}return$J;}function
fields($R){$r=fields_from_edit();if(!count($r)){global$o;$I=$o->select($R,array("*"),null,null,array(),10);while($K=$I->fetch_assoc()){foreach($K
as$z=>$X){$K[$z]=null;$r[$z]=array("field"=>$z,"type"=>"string","null"=>($z!=$o->primary),"auto_increment"=>($z==$o->primary),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,),);}}}return$r;}function
found_rows($S,$Z){global$i;$Z=where_to_query($Z);$d='MongoDB\Driver\Command';$gb=new$d(array('count'=>$S['Name'],'query'=>$Z));$ef=$i->_link->executeCommand($i->_db_name,$gb);$ig=$ef->toArray();return$ig[0]->n;}function
sql_query_where_parser($Oe){$Oe=trim(preg_replace('/WHERE[\s]?[(]?\(?/','',$Oe));$Oe=preg_replace('/\)\)\)$/',')',$Oe);$Vg=explode(' AND ',$Oe);$Wg=explode(') OR (',$Oe);$Z=array();foreach($Vg
as$Tg)$Z[]=trim($Tg);if(count($Wg)==1)$Wg=array();elseif(count($Wg)>1)$Z=array();return
where_to_query($Z,$Wg);}function
where_to_query($Rg=array(),$Sg=array()){global$je;$tb=array();foreach(array('and'=>$Rg,'or'=>$Sg)as$U=>$Z){if(is_array($Z)){foreach($Z
as$ac){list($cb,$he,$X)=explode(" ",$ac,3);if($cb=="_id"){$X=str_replace('MongoDB\BSON\ObjectID("',"",$X);$X=str_replace('")',"",$X);$d='MongoDB\BSON\ObjectID';$X=new$d($X);}if(!in_array($he,$je))continue;if(preg_match('~^\(f\)(.+)~',$he,$B)){$X=(float)$X;$he=$B[1];}elseif(preg_match('~^\(date\)(.+)~',$he,$B)){$ub=new
DateTime($X);$d='MongoDB\BSON\UTCDatetime';$X=new$d($ub->getTimestamp()*1000);$he=$B[1];}switch($he){case'=':$he='$eq';break;case'!=':$he='$ne';break;case'>':$he='$gt';break;case'<':$he='$lt';break;case'>=':$he='$gte';break;case'<=':$he='$lte';break;case'regex':$he='$regex';break;default:continue;}if($U=='and')$tb['$and'][]=array($cb=>array($he=>$X));elseif($U=='or')$tb['$or'][]=array($cb=>array($he=>$X));}}}return$tb;}$je=array("=","!=",">","<",">=","<=","regex","(f)=","(f)!=","(f)>","(f)<","(f)>=","(f)<=","(date)=","(date)!=","(date)>","(date)<","(date)>=","(date)<=",);}function
table($v){return$v;}function
idf_escape($v){return$v;}function
table_status($C="",$gc=false){$J=array();foreach(tables_list()as$R=>$U){$J[$R]=array("Name"=>$R);if($C==$R)return$J[$R];}return$J;}function
last_id(){global$i;return$i->last_id;}function
error(){global$i;return
h($i->error);}function
collations(){return
array();}function
logged_user(){global$b;$k=$b->credentials();return$k[1];}function
connect(){global$b;$i=new
Min_DB;$k=$b->credentials();if($i->connect($k[0],$k[1],$k[2]))return$i;return$i->error;}function
alter_indexes($R,$c){global$i;foreach($c
as$X){list($U,$C,$P)=$X;if($P=="DROP")$J=$i->_db->command(array("deleteIndexes"=>$R,"index"=>$C));else{$g=array();foreach($P
as$f){$f=preg_replace('~ DESC$~','',$f,1,$nb);$g[$f]=($nb?-1:1);}$J=$i->_db->selectCollection($R)->ensureIndex($g,array("unique"=>($U=="UNIQUE"),"name"=>$C,));}if($J['errmsg']){$i->error=$J['errmsg'];return
false;}}return
true;}function
support($hc){return
preg_match("~database|indexes~",$hc);}function
db_collation($n,$db){}function
information_schema(){}function
is_view($S){}function
convert_field($q){}function
unconvert_field($q,$J){return$J;}function
foreign_keys($R){return
array();}function
fk_support($S){}function
engines(){return
array();}function
alter_table($R,$C,$r,$sc,$hb,$Rb,$e,$Fa,$we){global$i;if($R==""){$i->_db->createCollection($C);return
true;}}function
drop_tables($T){global$i;foreach($T
as$R){$bf=$i->_db->selectCollection($R)->drop();if(!$bf['ok'])return
false;}return
true;}function
truncate_tables($T){global$i;foreach($T
as$R){$bf=$i->_db->selectCollection($R)->remove();if(!$bf['ok'])return
false;}return
true;}$y="mongo";$Bc=array();$Fc=array();$Kb=array(array("json"));}$Fb["elastic"]="Elasticsearch (beta)";if(isset($_GET["elastic"])){$De=array("json");define("DRIVER","elastic");if(function_exists('json_decode')){class
Min_DB{var$extension="JSON",$server_info,$errno,$error,$_url;function
rootQuery($ye,$lb=array(),$Qd='GET'){@ini_set('track_errors',1);$kc=@file_get_contents("$this->_url/".ltrim($ye,'/'),false,stream_context_create(array('http'=>array('method'=>$Qd,'content'=>$lb===null?$lb:json_encode($lb),'header'=>'Content-Type: application/json','ignore_errors'=>1,))));if(!$kc){$this->error=$php_errormsg;return$kc;}if(!preg_match('~^HTTP/[0-9.]+ 2~i',$http_response_header[0])){$this->error=$kc;return
false;}$J=json_decode($kc,true);if($J===null){$this->errno=json_last_error();if(function_exists('json_last_error_msg'))$this->error=json_last_error_msg();else{$kb=get_defined_constants(true);foreach($kb['json']as$C=>$Y){if($Y==$this->errno&&preg_match('~^JSON_ERROR_~',$C)){$this->error=$C;break;}}}}return$J;}function
query($ye,$lb=array(),$Qd='GET'){return$this->rootQuery(($this->_db!=""?"$this->_db/":"/").ltrim($ye,'/'),$lb,$Qd);}function
connect($O,$V,$G){preg_match('~^(https?://)?(.*)~',$O,$B);$this->_url=($B[1]?$B[1]:"http://")."$V:$G@$B[2]";$J=$this->query('');if($J)$this->server_info=$J['version']['number'];return(bool)$J;}function
select_db($l){$this->_db=$l;return
true;}function
quote($Q){return$Q;}}class
Min_Result{var$num_rows,$_rows;function
__construct($L){$this->num_rows=count($this->_rows);$this->_rows=$L;reset($this->_rows);}function
fetch_assoc(){$J=current($this->_rows);next($this->_rows);return$J;}function
fetch_row(){return
array_values($this->fetch_assoc());}}}class
Min_Driver
extends
Min_SQL{function
select($R,$M,$Z,$Cc,$me=array(),$_=1,$E=0,$He=false){global$b;$tb=array();$H="$R/_search";if($M!=array("*"))$tb["fields"]=$M;if($me){$Af=array();foreach($me
as$cb){$cb=preg_replace('~ DESC$~','',$cb,1,$nb);$Af[]=($nb?array($cb=>"desc"):$cb);}$tb["sort"]=$Af;}if($_){$tb["size"]=+$_;if($E)$tb["from"]=($E*$_);}foreach($Z
as$X){list($cb,$he,$X)=explode(" ",$X,3);if($cb=="_id")$tb["query"]["ids"]["values"][]=$X;elseif($cb.$X!=""){$Wf=array("term"=>array(($cb!=""?$cb:"_all")=>$X));if($he=="=")$tb["query"]["filtered"]["filter"]["and"][]=$Wf;else$tb["query"]["filtered"]["query"]["bool"]["must"][]=$Wf;}}if($tb["query"]&&!$tb["query"]["filtered"]["query"]&&!$tb["query"]["ids"])$tb["query"]["filtered"]["query"]=array("match_all"=>array());$Hf=microtime(true);$lf=$this->_conn->query($H,$tb);if($He)echo$b->selectQuery("$H: ".print_r($tb,true),$Hf,!$lf);if(!$lf)return
false;$J=array();foreach($lf['hits']['hits']as$Nc){$K=array();if($M==array("*"))$K["_id"]=$Nc["_id"];$r=$Nc['_source'];if($M!=array("*")){$r=array();foreach($M
as$z)$r[$z]=$Nc['fields'][$z];}foreach($r
as$z=>$X){if($tb["fields"])$X=$X[0];$K[$z]=(is_array($X)?json_encode($X):$X);}$J[]=$K;}return
new
Min_Result($J);}function
update($U,$Te,$Oe){$xe=preg_split('~ *= *~',$Oe);if(count($xe)==2){$u=trim($xe[1]);$H="$U/$u";return$this->_conn->query($H,$Te,'POST');}return
false;}function
insert($U,$Te){$u="";$H="$U/$u";$bf=$this->_conn->query($H,$Te,'POST');$this->_conn->last_id=$bf['_id'];return$bf['created'];}function
delete($U,$Oe){$Rc=array();if(is_array($_GET["where"])&&$_GET["where"]["_id"])$Rc[]=$_GET["where"]["_id"];if(is_array($_POST['check'])){foreach($_POST['check']as$Ua){$xe=preg_split('~ *= *~',$Ua);if(count($xe)==2)$Rc[]=trim($xe[1]);}}$this->_conn->affected_rows=0;foreach($Rc
as$u){$H="{$U}/{$u}";$bf=$this->_conn->query($H,'{}','DELETE');if(is_array($bf)&&$bf['found']==true)$this->_conn->affected_rows++;}return$this->_conn->affected_rows;}}function
connect(){global$b;$i=new
Min_DB;$k=$b->credentials();if($i->connect($k[0],$k[1],$k[2]))return$i;return$i->error;}function
support($hc){return
preg_match("~database|table|columns~",$hc);}function
logged_user(){global$b;$k=$b->credentials();return$k[1];}function
get_databases(){global$i;$J=$i->rootQuery('_aliases');if($J){$J=array_keys($J);sort($J,SORT_STRING);}return$J;}function
collations(){return
array();}function
db_collation($n,$db){}function
engines(){return
array();}function
count_tables($m){global$i;$J=array();$I=$i->query('_stats');if($I&&$I['indices']){$Xc=$I['indices'];foreach($Xc
as$Wc=>$If){$Vc=$If['total']['indexing'];$J[$Wc]=$Vc['index_total'];}}return$J;}function
tables_list(){global$i;$J=$i->query('_mapping');if($J)$J=array_fill_keys(array_keys($J[$i->_db]["mappings"]),'table');return$J;}function
table_status($C="",$gc=false){global$i;$lf=$i->query("_search",array("size"=>0,"aggregations"=>array("count_by_type"=>array("terms"=>array("field"=>"_type")))),"POST");$J=array();if($lf){$T=$lf["aggregations"]["count_by_type"]["buckets"];foreach($T
as$R){$J[$R["key"]]=array("Name"=>$R["key"],"Engine"=>"table","Rows"=>$R["doc_count"],);if($C!=""&&$C==$R["key"])return$J[$C];}}return$J;}function
error(){global$i;return
h($i->error);}function
information_schema(){}function
is_view($S){}function
indexes($R,$j=null){return
array(array("type"=>"PRIMARY","columns"=>array("_id")),);}function
fields($R){global$i;$I=$i->query("$R/_mapping");$J=array();if($I){$Dd=$I[$R]['properties'];if(!$Dd)$Dd=$I[$i->_db]['mappings'][$R]['properties'];if($Dd){foreach($Dd
as$C=>$q){$J[$C]=array("field"=>$C,"full_type"=>$q["type"],"type"=>$q["type"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),);if($q["properties"]){unset($J[$C]["privileges"]["insert"]);unset($J[$C]["privileges"]["update"]);}}}}return$J;}function
foreign_keys($R){return
array();}function
table($v){return$v;}function
idf_escape($v){return$v;}function
convert_field($q){}function
unconvert_field($q,$J){return$J;}function
fk_support($S){}function
found_rows($S,$Z){return
null;}function
create_database($n){global$i;return$i->rootQuery(urlencode($n),null,'PUT');}function
drop_databases($m){global$i;return$i->rootQuery(urlencode(implode(',',$m)),array(),'DELETE');}function
alter_table($R,$C,$r,$sc,$hb,$Rb,$e,$Fa,$we){global$i;$Ke=array();foreach($r
as$ec){$ic=trim($ec[1][0]);$jc=trim($ec[1][1]?$ec[1][1]:"text");$Ke[$ic]=array('type'=>$jc);}if(!empty($Ke))$Ke=array('properties'=>$Ke);return$i->query("_mapping/{$C}",$Ke,'PUT');}function
drop_tables($T){global$i;$J=true;foreach($T
as$R)$J=$J&&$i->query(urlencode($R),array(),'DELETE');return$J;}function
last_id(){global$i;return$i->last_id;}$y="elastic";$je=array("=","query");$Bc=array();$Fc=array();$Kb=array(array("json"));$vg=array();$Lf=array();foreach(array(lang(25)=>array("long"=>3,"integer"=>5,"short"=>8,"byte"=>10,"double"=>20,"float"=>66,"half_float"=>12,"scaled_float"=>21),lang(26)=>array("date"=>10),lang(23)=>array("string"=>65535,"text"=>65535),lang(27)=>array("binary"=>255),)as$z=>$X){$vg+=$X;$Lf[$z]=array_keys($X);}}$Fb=array("server"=>"MySQL")+$Fb;if(!defined("DRIVER")){$De=array("MySQLi","MySQL","PDO_MySQL");define("DRIVER","server");if(extension_loaded("mysqli")){class
Min_DB
extends
MySQLi{var$extension="MySQLi";function
__construct(){parent::init();}function
connect($O="",$V="",$G="",$l=null,$Be=null,$_f=null){global$b;mysqli_report(MYSQLI_REPORT_OFF);list($Oc,$Be)=explode(":",$O,2);$Gf=$b->connectSsl();if($Gf)$this->ssl_set($Gf['key'],$Gf['cert'],$Gf['ca'],'','');$J=@$this->real_connect(($O!=""?$Oc:ini_get("mysqli.default_host")),($O.$V!=""?$V:ini_get("mysqli.default_user")),($O.$V.$G!=""?$G:ini_get("mysqli.default_pw")),$l,(is_numeric($Be)?$Be:ini_get("mysqli.default_port")),(!is_numeric($Be)?$Be:$_f),($Gf?64:0));return$J;}function
set_charset($Ta){if(parent::set_charset($Ta))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $Ta");}function
result($H,$q=0){$I=$this->query($H);if(!$I)return
false;$K=$I->fetch_array();return$K[$q];}function
quote($Q){return"'".$this->escape_string($Q)."'";}}}elseif(extension_loaded("mysql")&&!(ini_get("sql.safe_mode")&&extension_loaded("pdo_mysql"))){class
Min_DB{var$extension="MySQL",$server_info,$affected_rows,$errno,$error,$_link,$_result;function
connect($O,$V,$G){$this->_link=@mysql_connect(($O!=""?$O:ini_get("mysql.default_host")),("$O$V"!=""?$V:ini_get("mysql.default_user")),("$O$V$G"!=""?$G:ini_get("mysql.default_password")),true,131072);if($this->_link)$this->server_info=mysql_get_server_info($this->_link);else$this->error=mysql_error();return(bool)$this->_link;}function
set_charset($Ta){if(function_exists('mysql_set_charset')){if(mysql_set_charset($Ta,$this->_link))return
true;mysql_set_charset('utf8',$this->_link);}return$this->query("SET NAMES $Ta");}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->_link)."'";}function
select_db($l){return
mysql_select_db($l,$this->_link);}function
query($H,$wg=false){$I=@($wg?mysql_unbuffered_query($H,$this->_link):mysql_query($H,$this->_link));$this->error="";if(!$I){$this->errno=mysql_errno($this->_link);$this->error=mysql_error($this->_link);return
false;}if($I===true){$this->affected_rows=mysql_affected_rows($this->_link);$this->info=mysql_info($this->_link);return
true;}return
new
Min_Result($I);}function
multi_query($H){return$this->_result=$this->query($H);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($H,$q=0){$I=$this->query($H);if(!$I||!$I->num_rows)return
false;return
mysql_result($I->_result,0,$q);}}class
Min_Result{var$num_rows,$_result,$_offset=0;function
__construct($I){$this->_result=$I;$this->num_rows=mysql_num_rows($I);}function
fetch_assoc(){return
mysql_fetch_assoc($this->_result);}function
fetch_row(){return
mysql_fetch_row($this->_result);}function
fetch_field(){$J=mysql_fetch_field($this->_result,$this->_offset++);$J->orgtable=$J->table;$J->orgname=$J->name;$J->charsetnr=($J->blob?63:0);return$J;}function
__destruct(){mysql_free_result($this->_result);}}}elseif(extension_loaded("pdo_mysql")){class
Min_DB
extends
Min_PDO{var$extension="PDO_MySQL";function
connect($O,$V,$G){global$b;$D=array();$Gf=$b->connectSsl();if($Gf)$D=array(PDO::MYSQL_ATTR_SSL_KEY=>$Gf['key'],PDO::MYSQL_ATTR_SSL_CERT=>$Gf['cert'],PDO::MYSQL_ATTR_SSL_CA=>$Gf['ca'],);$this->dsn("mysql:charset=utf8;host=".str_replace(":",";unix_socket=",preg_replace('~:(\\d)~',';port=\\1',$O)),$V,$G,$D);return
true;}function
set_charset($Ta){$this->query("SET NAMES $Ta");}function
select_db($l){return$this->query("USE ".idf_escape($l));}function
query($H,$wg=false){$this->setAttribute(1000,!$wg);return
parent::query($H,$wg);}}}class
Min_Driver
extends
Min_SQL{function
insert($R,$P){return($P?parent::insert($R,$P):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,$L,$Fe){$g=array_keys(reset($L));$Ee="INSERT INTO ".table($R)." (".implode(", ",$g).") VALUES\n";$Jg=array();foreach($g
as$z)$Jg[$z]="$z = VALUES($z)";$Of="\nON DUPLICATE KEY UPDATE ".implode(", ",$Jg);$Jg=array();$xd=0;foreach($L
as$P){$Y="(".implode(", ",$P).")";if($Jg&&(strlen($Ee)+$xd+strlen($Y)+strlen($Of)>1e6)){if(!queries($Ee.implode(",\n",$Jg).$Of))return
false;$Jg=array();$xd=0;}$Jg[]=$Y;$xd+=strlen($Y)+2;}return
queries($Ee.implode(",\n",$Jg).$Of);}function
convertSearch($v,$X,$q){return(preg_match('~char|text|enum|set~',$q["type"])&&!preg_match("~^utf8~",$q["collation"])?"CONVERT($v USING ".charset($this->_conn).")":$v);}function
warnings(){$I=$this->_conn->query("SHOW WARNINGS");if($I&&$I->num_rows){ob_start();select($I);return
ob_get_clean();}}function
tableHelp($C){$Ed=preg_match('~MariaDB~',$this->_conn->server_info);if(information_schema(DB))return
strtolower(($Ed?"information-schema-$C-table/":str_replace("_","-",$C)."-table.html"));if(DB=="mysql")return($Ed?"mysql$C-table/":"system-database.html");}}function
idf_escape($v){return"`".str_replace("`","``",$v)."`";}function
table($v){return
idf_escape($v);}function
connect(){global$b,$vg,$Lf;$i=new
Min_DB;$k=$b->credentials();if($i->connect($k[0],$k[1],$k[2])){$i->set_charset(charset($i));$i->query("SET sql_quote_show_create = 1, autocommit = 1");if(min_version('5.7.8',10.2,$i)){$Lf[lang(23)][]="json";$vg["json"]=4294967295;}return$i;}$J=$i->error;if(function_exists('iconv')&&!is_utf8($J)&&strlen($if=iconv("windows-1250","utf-8",$J))>strlen($J))$J=$if;return$J;}function
get_databases($rc){$J=get_session("dbs");if($J===null){$H=(min_version(5)?"SELECT SCHEMA_NAME FROM information_schema.SCHEMATA":"SHOW DATABASES");$J=($rc?slow_query($H):get_vals($H));restart_session();set_session("dbs",$J);stop_session();}return$J;}function
limit($H,$Z,$_,$ce=0,$N=" "){return" $H$Z".($_!==null?$N."LIMIT $_".($ce?" OFFSET $ce":""):"");}function
limit1($R,$H,$Z,$N="\n"){return
limit($H,$Z,1,0,$N);}function
db_collation($n,$db){global$i;$J=null;$ob=$i->result("SHOW CREATE DATABASE ".idf_escape($n),1);if(preg_match('~ COLLATE ([^ ]+)~',$ob,$B))$J=$B[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$ob,$B))$J=$db[$B[1]][-1];return$J;}function
engines(){$J=array();foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$J[]=$K["Engine"];}return$J;}function
logged_user(){global$i;return$i->result("SELECT USER()");}function
tables_list(){return
get_key_vals(min_version(5)?"SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME":"SHOW TABLES");}function
count_tables($m){$J=array();foreach($m
as$n)$J[$n]=count(get_vals("SHOW TABLES IN ".idf_escape($n)));return$J;}function
table_status($C="",$gc=false){$J=array();foreach(get_rows($gc&&min_version(5)?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($C!=""?"AND TABLE_NAME = ".q($C):"ORDER BY Name"):"SHOW TABLE STATUS".($C!=""?" LIKE ".q(addcslashes($C,"%_\\")):""))as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($C!="")return$K;$J[$K["Name"]]=$K;}return$J;}function
is_view($S){return$S["Engine"]===null;}function
fk_support($S){return
preg_match('~InnoDB|IBMDB2I~i',$S["Engine"])||(preg_match('~NDB~i',$S["Engine"])&&min_version(5.6));}function
fields($R){$J=array();foreach(get_rows("SHOW FULL COLUMNS FROM ".table($R))as$K){preg_match('~^([^( ]+)(?:\\((.+)\\))?( unsigned)?( zerofill)?$~',$K["Type"],$B);$J[$K["Field"]]=array("field"=>$K["Field"],"full_type"=>$K["Type"],"type"=>$B[1],"length"=>$B[2],"unsigned"=>ltrim($B[3].$B[4]),"default"=>($K["Default"]!=""||preg_match("~char|set~",$B[1])?$K["Default"]:null),"null"=>($K["Null"]=="YES"),"auto_increment"=>($K["Extra"]=="auto_increment"),"on_update"=>(preg_match('~^on update (.+)~i',$K["Extra"],$B)?$B[1]:""),"collation"=>$K["Collation"],"privileges"=>array_flip(preg_split('~, *~',$K["Privileges"])),"comment"=>$K["Comment"],"primary"=>($K["Key"]=="PRI"),);}return$J;}function
indexes($R,$j=null){$J=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$j)as$K){$C=$K["Key_name"];$J[$C]["type"]=($C=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?($K["Index_type"]=="SPATIAL"?"SPATIAL":"INDEX"):"UNIQUE")));$J[$C]["columns"][]=$K["Column_name"];$J[$C]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$C]["descs"][]=null;}return$J;}function
foreign_keys($R){global$i,$ee;static$ze='`(?:[^`]|``)+`';$J=array();$pb=$i->result("SHOW CREATE TABLE ".table($R),1);if($pb){preg_match_all("~CONSTRAINT ($ze) FOREIGN KEY ?\\(((?:$ze,? ?)+)\\) REFERENCES ($ze)(?:\\.($ze))? \\(((?:$ze,? ?)+)\\)(?: ON DELETE ($ee))?(?: ON UPDATE ($ee))?~",$pb,$Hd,PREG_SET_ORDER);foreach($Hd
as$B){preg_match_all("~$ze~",$B[2],$Bf);preg_match_all("~$ze~",$B[5],$Vf);$J[idf_unescape($B[1])]=array("db"=>idf_unescape($B[4]!=""?$B[3]:$B[4]),"table"=>idf_unescape($B[4]!=""?$B[4]:$B[3]),"source"=>array_map('idf_unescape',$Bf[0]),"target"=>array_map('idf_unescape',$Vf[0]),"on_delete"=>($B[6]?$B[6]:"RESTRICT"),"on_update"=>($B[7]?$B[7]:"RESTRICT"),);}}return$J;}function
view($C){global$i;return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\\s+AS\\s+~isU','',$i->result("SHOW CREATE VIEW ".table($C),1)));}function
collations(){$J=array();foreach(get_rows("SHOW COLLATION")as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$z=>$X)asort($J[$z]);return$J;}function
information_schema($n){return(min_version(5)&&$n=="information_schema")||(min_version(5.5)&&$n=="performance_schema");}function
error(){global$i;return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",$i->error));}function
create_database($n,$e){return
queries("CREATE DATABASE ".idf_escape($n).($e?" COLLATE ".q($e):""));}function
drop_databases($m){$J=apply_queries("DROP DATABASE",$m,'idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($C,$e){$J=false;if(create_database($C,$e)){$Ye=array();foreach(tables_list()as$R=>$U)$Ye[]=table($R)." TO ".idf_escape($C).".".table($R);$J=(!$Ye||queries("RENAME TABLE ".implode(", ",$Ye)));if($J)queries("DROP DATABASE ".idf_escape(DB));restart_session();set_session("dbs",null);}return$J;}function
auto_increment(){$Ga=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$w){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$w["columns"],true)){$Ga="";break;}if($w["type"]=="PRIMARY")$Ga=" UNIQUE";}}return" AUTO_INCREMENT$Ga";}function
alter_table($R,$C,$r,$sc,$hb,$Rb,$e,$Fa,$we){$c=array();foreach($r
as$q)$c[]=($q[1]?($R!=""?($q[0]!=""?"CHANGE ".idf_escape($q[0]):"ADD"):" ")." ".implode($q[1]).($R!=""?$q[2]:""):"DROP ".idf_escape($q[0]));$c=array_merge($c,$sc);$Jf=($hb!==null?" COMMENT=".q($hb):"").($Rb?" ENGINE=".q($Rb):"").($e?" COLLATE ".q($e):"").($Fa!=""?" AUTO_INCREMENT=$Fa":"");if($R=="")return
queries("CREATE TABLE ".table($C)." (\n".implode(",\n",$c)."\n)$Jf$we");if($R!=$C)$c[]="RENAME TO ".table($C);if($Jf)$c[]=ltrim($Jf);return($c||$we?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$c).$we):true);}function
alter_indexes($R,$c){foreach($c
as$z=>$X)$c[$z]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$c));}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Ng){return
queries("DROP VIEW ".implode(", ",array_map('table',$Ng)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('table',$T)));}function
move_tables($T,$Ng,$Vf){$Ye=array();foreach(array_merge($T,$Ng)as$R)$Ye[]=table($R)." TO ".idf_escape($Vf).".".table($R);return
queries("RENAME TABLE ".implode(", ",$Ye));}function
copy_tables($T,$Ng,$Vf){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$C=($Vf==DB?table("copy_$R"):idf_escape($Vf).".".table($R));if(!queries("\nDROP TABLE IF EXISTS $C")||!queries("CREATE TABLE $C LIKE ".table($R))||!queries("INSERT INTO $C SELECT * FROM ".table($R)))return
false;}foreach($Ng
as$R){$C=($Vf==DB?table("copy_$R"):idf_escape($Vf).".".table($R));$Mg=view($R);if(!queries("DROP VIEW IF EXISTS $C")||!queries("CREATE VIEW $C AS $Mg[select]"))return
false;}return
true;}function
trigger($C){if($C=="")return
array();$L=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($C));return
reset($L);}function
triggers($R){$J=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K)$J[$K["Trigger"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($C,$U){global$i,$Sb,$cd,$vg;$xa=array("bool","boolean","integer","double precision","real","dec","numeric","fixed","national char","national varchar");$Cf="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";$ug="((".implode("|",array_merge(array_keys($vg),$xa)).")\\b(?:\\s*\\(((?:[^'\")]|$Sb)++)\\))?\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?";$ze="$Cf*(".($U=="FUNCTION"?"":$cd).")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$ug";$ob=$i->result("SHOW CREATE $U ".idf_escape($C),2);preg_match("~\\(((?:$ze\\s*,?)*)\\)\\s*".($U=="FUNCTION"?"RETURNS\\s+$ug\\s+":"")."(.*)~is",$ob,$B);$r=array();preg_match_all("~$ze\\s*,?~is",$B[1],$Hd,PREG_SET_ORDER);foreach($Hd
as$te){$C=str_replace("``","`",$te[2]).$te[3];$r[]=array("field"=>$C,"type"=>strtolower($te[5]),"length"=>preg_replace_callback("~$Sb~s",'normalize_enum',$te[6]),"unsigned"=>strtolower(preg_replace('~\\s+~',' ',trim("$te[8] $te[7]"))),"null"=>1,"full_type"=>$te[4],"inout"=>strtoupper($te[1]),"collation"=>strtolower($te[9]),);}if($U!="FUNCTION")return
array("fields"=>$r,"definition"=>$B[11]);return
array("fields"=>$r,"returns"=>array("type"=>$B[12],"length"=>$B[13],"unsigned"=>$B[15],"collation"=>$B[16]),"definition"=>$B[17],"language"=>"SQL",);}function
routines(){return
get_rows("SELECT ROUTINE_NAME AS SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ".q(DB));}function
routine_languages(){return
array();}function
routine_id($C,$K){return
idf_escape($C);}function
last_id(){global$i;return$i->result("SELECT LAST_INSERT_ID()");}function
explain($i,$H){return$i->query("EXPLAIN ".(min_version(5.1)?"PARTITIONS ":"").$H);}function
found_rows($S,$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($jf){return
true;}function
create_sql($R,$Fa,$Mf){global$i;$J=$i->result("SHOW CREATE TABLE ".table($R),1);if(!$Fa)$J=preg_replace('~ AUTO_INCREMENT=\\d+~','',$J);return$J;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($l){return"USE ".idf_escape($l);}function
trigger_sql($R){$J="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$K)$J.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$J;}function
show_variables(){return
get_key_vals("SHOW VARIABLES");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
show_status(){return
get_key_vals("SHOW STATUS");}function
convert_field($q){if(preg_match("~binary~",$q["type"]))return"HEX(".idf_escape($q["field"]).")";if($q["type"]=="bit")return"BIN(".idf_escape($q["field"])." + 0)";if(preg_match("~geometry|point|linestring|polygon~",$q["type"]))return(min_version(8)?"ST_":"")."AsWKT(".idf_escape($q["field"]).")";}function
unconvert_field($q,$J){if(preg_match("~binary~",$q["type"]))$J="UNHEX($J)";if($q["type"]=="bit")$J="CONV($J, 2, 10) + 0";if(preg_match("~geometry|point|linestring|polygon~",$q["type"]))$J=(min_version(8)?"ST_":"")."GeomFromText($J)";return$J;}function
support($hc){return!preg_match("~scheme|sequence|type|view_trigger|materializedview".(min_version(5.1)?"":"|event|partitioning".(min_version(5)?"":"|routine|trigger|view"))."~",$hc);}function
kill_process($X){return
queries("KILL ".number($X));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){global$i;return$i->result("SELECT @@max_connections");}$y="sql";$vg=array();$Lf=array();foreach(array(lang(25)=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),lang(26)=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),lang(23)=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),lang(30)=>array("enum"=>65535,"set"=>64),lang(27)=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),lang(29)=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),)as$z=>$X){$vg+=$X;$Lf[$z]=array_keys($X);}$Bg=array("unsigned","zerofill","unsigned zerofill");$je=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");$Bc=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");$Fc=array("avg","count","count distinct","group_concat","max","min","sum");$Kb=array(array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",),array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",));}define("SERVER",$_GET[DRIVER]);define("DB",$_GET["db"]);define("ME",preg_replace('~^[^?]*/([^?]*).*~','\\1',$_SERVER["REQUEST_URI"]).'?'.(sid()?SID.'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));$ca="4.6.2";class
Adminer{var$operators=array("<=",">=");var$_values=array();function
name(){return"<a href='https://www.adminer.org/editor/'".target_blank()." id='h1'>".lang(31)."</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($ob=false){return
password_file($ob);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($O){}function
database(){global$i;if($i){$m=$this->databases(false);return(!$m?$i->result("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1)"):$m[(information_schema($m[0])?1:0)]);}}function
schemas(){return
schemas();}function
databases($rc=true){return
get_databases($rc);}function
queryTimeout(){return
5;}function
headers(){}function
csp(){return
csp();}function
head(){return
true;}function
css(){$J=array();$s="adminer.css";if(file_exists($s))$J[]=$s;return$J;}function
loginForm(){echo'<table cellspacing="0">
<tr><th>',lang(32),'<td><input type="hidden" name="auth[driver]" value="server"><input name="auth[username]" id="username" value="',h($_GET["username"]),'" autocapitalize="off">
<tr><th>',lang(33),'<td><input type="password" name="auth[password]">
</table>
',script("focus(qs('#username'));"),"<p><input type='submit' value='".lang(34)."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],lang(35))."\n";}function
login($Bd,$G){return
true;}function
tableName($Rf){return
h($Rf["Comment"]!=""?$Rf["Comment"]:$Rf["Name"]);}function
fieldName($q,$me=0){return
h(preg_replace('~\s+\[.*\]$~','',($q["comment"]!=""?$q["comment"]:$q["field"])));}function
selectLinks($Rf,$P=""){$a=$Rf["Name"];if($P!==null)echo'<p class="tabs"><a href="'.h(ME.'edit='.urlencode($a).$P).'">'.lang(36)."</a>\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$Qf){$J=array();foreach(get_rows("SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = ".q($this->database())."
AND REFERENCED_TABLE_SCHEMA = ".q($this->database())."
AND REFERENCED_TABLE_NAME = ".q($R)."
ORDER BY ORDINAL_POSITION",null,"")as$K)$J[$K["TABLE_NAME"]]["keys"][$K["CONSTRAINT_NAME"]][$K["COLUMN_NAME"]]=$K["REFERENCED_COLUMN_NAME"];foreach($J
as$z=>$X){$C=$this->tableName(table_status($z,true));if($C!=""){$lf=preg_quote($Qf);$N="(:|\\s*-)?\\s+";$J[$z]["name"]=(preg_match("(^$lf$N(.+)|^(.+?)$N$lf\$)iu",$C,$B)?$B[2].$B[3]:$C);}else
unset($J[$z]);}return$J;}function
backwardKeysPrint($Ja,$K){foreach($Ja
as$R=>$Ia){foreach($Ia["keys"]as$fb){$A=ME.'select='.urlencode($R);$t=0;foreach($fb
as$f=>$X)$A.=where_link($t++,$f,$K[$X]);echo"<a href='".h($A)."'>".h($Ia["name"])."</a>";$A=ME.'edit='.urlencode($R);foreach($fb
as$f=>$X)$A.="&set".urlencode("[".bracket_escape($f)."]")."=".urlencode($K[$X]);echo"<a href='".h($A)."' title='".lang(36)."'>+</a> ";}}}function
selectQuery($H,$Hf,$fc=false){return"<!--\n".str_replace("--","--><!-- ",$H)."\n(".format_time($Hf).")\n-->\n";}function
rowDescription($R){foreach(fields($R)as$q){if(preg_match("~varchar|character varying~",$q["type"]))return
idf_escape($q["field"]);}return"";}function
rowDescriptions($L,$uc){$J=$L;foreach($L[0]as$z=>$X){if(list($R,$u,$C)=$this->_foreignColumn($uc,$z)){$Rc=array();foreach($L
as$K)$Rc[$K[$z]]=q($K[$z]);$_b=$this->_values[$R];if(!$_b)$_b=get_key_vals("SELECT $u, $C FROM ".table($R)." WHERE $u IN (".implode(", ",$Rc).")");foreach($L
as$Ud=>$K){if(isset($K[$z]))$J[$Ud][$z]=(string)$_b[$K[$z]];}}}return$J;}function
selectLink($X,$q){}function
selectVal($X,$A,$q,$oe){$J=($X===null?"&nbsp;":$X);$A=h($A);if(preg_match('~blob|bytea~',$q["type"])&&!is_utf8($X)){$J=lang(37,strlen($oe));if(preg_match("~^(GIF|\xFF\xD8\xFF|\x89PNG\x0D\x0A\x1A\x0A)~",$oe))$J="<img src='$A' alt='$J'>";}if(like_bool($q)&&$J!="&nbsp;")$J=(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?lang(38):lang(39));if($A)$J="<a href='$A'".(is_url($A)?target_blank():"").">$J</a>";if(!$A&&!like_bool($q)&&preg_match(number_type(),$q["type"]))$J="<div class='number'>$J</div>";elseif(preg_match('~date~',$q["type"]))$J="<div class='datetime'>$J</div>";return$J;}function
editVal($X,$q){if(preg_match('~date|timestamp~',$q["type"])&&$X!==null)return
preg_replace('~^(\\d{2}(\\d+))-(0?(\\d+))-(0?(\\d+))~',lang(40),$X);return$X;}function
selectColumnsPrint($M,$g){}function
selectSearchPrint($Z,$g,$x){$Z=(array)$_GET["where"];echo'<fieldset id="fieldset-search"><legend>'.lang(41)."</legend><div>\n";$od=array();foreach($Z
as$z=>$X)$od[$X["col"]]=$z;$t=0;$r=fields($_GET["select"]);foreach($g
as$C=>$zb){$q=$r[$C];if(preg_match("~enum~",$q["type"])||like_bool($q)){$z=$od[$C];$t--;echo"<div>".h($zb)."<input type='hidden' name='where[$t][col]' value='".h($C)."'>:",(like_bool($q)?" <select name='where[$t][val]'>".optionlist(array(""=>"",lang(39),lang(38)),$Z[$z]["val"],true)."</select>":enum_input("checkbox"," name='where[$t][val][]'",$q,(array)$Z[$z]["val"],($q["null"]?0:null))),"</div>\n";unset($g[$C]);}elseif(is_array($D=$this->_foreignKeyOptions($_GET["select"],$C))){if($r[$C]["null"])$D[0]='('.lang(7).')';$z=$od[$C];$t--;echo"<div>".h($zb)."<input type='hidden' name='where[$t][col]' value='".h($C)."'><input type='hidden' name='where[$t][op]' value='='>: <select name='where[$t][val]'>".optionlist($D,$Z[$z]["val"],true)."</select></div>\n";unset($g[$C]);}}$t=0;foreach($Z
as$X){if(($X["col"]==""||$g[$X["col"]])&&"$X[col]$X[val]"!=""){echo"<div><select name='where[$t][col]'><option value=''>(".lang(42).")".optionlist($g,$X["col"],true)."</select>",html_select("where[$t][op]",array(-1=>"")+$this->operators,$X["op"]),"<input type='search' name='where[$t][val]' value='".h($X["val"])."'>".script("mixin(qsl('input'), {onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});","")."</div>\n";$t++;}}echo"<div><select name='where[$t][col]'><option value=''>(".lang(42).")".optionlist($g,null,true)."</select>",script("qsl('select').onchange = selectAddRow;",""),html_select("where[$t][op]",array(-1=>"")+$this->operators),"<input type='search' name='where[$t][val]'></div>",script("mixin(qsl('input'), {onchange: function () { this.parentNode.firstChild.onchange(); }, onsearch: selectSearchSearch});"),"</div></fieldset>\n";}function
selectOrderPrint($me,$g,$x){$ne=array();foreach($x
as$z=>$w){$me=array();foreach($w["columns"]as$X)$me[]=$g[$X];if(count(array_filter($me,'strlen'))>1&&$z!="PRIMARY")$ne[$z]=implode(", ",$me);}if($ne){echo'<fieldset><legend>'.lang(43)."</legend><div>","<select name='index_order'>".optionlist(array(""=>"")+$ne,($_GET["order"][0]!=""?"":$_GET["index_order"]),true)."</select>","</div></fieldset>\n";}if($_GET["order"])echo"<div style='display: none;'>".hidden_fields(array("order"=>array(1=>reset($_GET["order"])),"desc"=>($_GET["desc"]?array(1=>1):array()),))."</div>\n";}function
selectLimitPrint($_){echo"<fieldset><legend>".lang(44)."</legend><div>";echo
html_select("limit",array("","50","100"),$_),"</div></fieldset>\n";}function
selectLengthPrint($Yf){}function
selectActionPrint($x){echo"<fieldset><legend>".lang(45)."</legend><div>","<input type='submit' value='".lang(46)."'>","</div></fieldset>\n";}function
selectCommandPrint(){return
true;}function
selectImportPrint(){return
true;}function
selectEmailPrint($Ob,$g){if($Ob){print_fieldset("email",lang(47),$_POST["email_append"]);echo"<div>",script("qsl('div').onkeydown = partialArg(bodyKeydown, 'email');"),"<p>".lang(48).": <input name='email_from' value='".h($_POST?$_POST["email_from"]:$_COOKIE["adminer_email"])."'>\n",lang(49).": <input name='email_subject' value='".h($_POST["email_subject"])."'>\n","<p><textarea name='email_message' rows='15' cols='75'>".h($_POST["email_message"].($_POST["email_append"]?'{$'."$_POST[email_addition]}":""))."</textarea>\n","<p>".script("qsl('p').onkeydown = partialArg(bodyKeydown, 'email_append');","").html_select("email_addition",$g,$_POST["email_addition"])."<input type='submit' name='email_append' value='".lang(11)."'>\n";echo"<p>".lang(50).": <input type='file' name='email_files[]'>".script("qsl('input').onchange = emailFileChange;"),"<p>".(count($Ob)==1?'<input type="hidden" name="email_field" value="'.h(key($Ob)).'">':html_select("email_field",$Ob)),"<input type='submit' name='email' value='".lang(51)."'>".confirm(),"</div>\n","</div></fieldset>\n";}}function
selectColumnsProcess($g,$x){return
array(array(),array());}function
selectSearchProcess($r,$x){$J=array();foreach((array)$_GET["where"]as$z=>$Z){$cb=$Z["col"];$he=$Z["op"];$X=$Z["val"];if(($z<0?"":$cb).$X!=""){$ib=array();foreach(($cb!=""?array($cb=>$r[$cb]):$r)as$C=>$q){if($cb!=""||is_numeric($X)||!preg_match(number_type(),$q["type"])){$C=idf_escape($C);if($cb!=""&&$q["type"]=="enum")$ib[]=(in_array(0,$X)?"$C IS NULL OR ":"")."$C IN (".implode(", ",array_map('intval',$X)).")";else{$Zf=preg_match('~char|text|enum|set~',$q["type"]);$Y=$this->processInput($q,(!$he&&$Zf&&preg_match('~^[^%]+$~',$X)?"%$X%":$X));$ib[]=$C.($Y=="NULL"?" IS".($he==">="?" NOT":"")." $Y":(in_array($he,$this->operators)||$he=="="?" $he $Y":($Zf?" LIKE $Y":" IN (".str_replace(",","', '",$Y).")")));if($z<0&&$X=="0")$ib[]="$C IS NULL";}}}$J[]=($ib?"(".implode(" OR ",$ib).")":"1 = 0");}}return$J;}function
selectOrderProcess($r,$x){$Uc=$_GET["index_order"];if($Uc!="")unset($_GET["order"][1]);if($_GET["order"])return
array(idf_escape(reset($_GET["order"])).($_GET["desc"]?" DESC":""));foreach(($Uc!=""?array($x[$Uc]):$x)as$w){if($Uc!=""||$w["type"]=="INDEX"){$Hc=array_filter($w["descs"]);$zb=false;foreach($w["columns"]as$X){if(preg_match('~date|timestamp~',$r[$X]["type"])){$zb=true;break;}}$J=array();foreach($w["columns"]as$z=>$X)$J[]=idf_escape($X).(($Hc?$w["descs"][$z]:$zb)?" DESC":"");return$J;}}return
array();}function
selectLimitProcess(){return(isset($_GET["limit"])?$_GET["limit"]:"50");}function
selectLengthProcess(){return"100";}function
selectEmailProcess($Z,$uc){if($_POST["email_append"])return
true;if($_POST["email"]){$qf=0;if($_POST["all"]||$_POST["check"]){$q=idf_escape($_POST["email_field"]);$Nf=$_POST["email_subject"];$Nd=$_POST["email_message"];preg_match_all('~\\{\\$([a-z0-9_]+)\\}~i',"$Nf.$Nd",$Hd);$L=get_rows("SELECT DISTINCT $q".($Hd[1]?", ".implode(", ",array_map('idf_escape',array_unique($Hd[1]))):"")." FROM ".table($_GET["select"])." WHERE $q IS NOT NULL AND $q != ''".($Z?" AND ".implode(" AND ",$Z):"").($_POST["all"]?"":" AND ((".implode(") OR (",array_map('where_check',(array)$_POST["check"]))."))"));$r=fields($_GET["select"]);foreach($this->rowDescriptions($L,$uc)as$K){$Ze=array('{\\'=>'{');foreach($Hd[1]as$X)$Ze['{$'."$X}"]=$this->editVal($K[$X],$r[$X]);$Nb=$K[$_POST["email_field"]];if(is_mail($Nb)&&send_mail($Nb,strtr($Nf,$Ze),strtr($Nd,$Ze),$_POST["email_from"],$_FILES["email_files"]))$qf++;}}cookie("adminer_email",$_POST["email_from"]);redirect(remove_from_uri(),lang(52,$qf));}return
false;}function
selectQueryBuild($M,$Z,$Cc,$me,$_,$E){return"";}function
messageQuery($H,$ag,$fc=false){return" <span class='time'>".@date("H:i:s")."</span><!--\n".str_replace("--","--><!-- ",$H)."\n".($ag?"($ag)\n":"")."-->";}function
editFunctions($q){$J=array();if($q["null"]&&preg_match('~blob~',$q["type"]))$J["NULL"]=lang(7);$J[""]=($q["null"]||$q["auto_increment"]||like_bool($q)?"":"*");if(preg_match('~date|time~',$q["type"]))$J["now"]=lang(53);if(preg_match('~_(md5|sha1)$~i',$q["field"],$B))$J[]=strtolower($B[1]);return$J;}function
editInput($R,$q,$Da,$Y){if($q["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$Da value='-1' checked><i>".lang(8)."</i></label> ":"").enum_input("radio",$Da,$q,($Y||isset($_GET["select"])?$Y:0),($q["null"]?"":null));$D=$this->_foreignKeyOptions($R,$q["field"],$Y);if($D!==null)return(is_array($D)?"<select$Da>".optionlist($D,$Y,true)."</select>":"<input value='".h($Y)."'$Da class='hidden'>"."<input value='".h($D)."' class='jsonly'>"."<div></div>".script("qsl('input').oninput = partial(whisper, '".ME."script=complete&source=".urlencode($R)."&field=".urlencode($q["field"])."&value=');
qsl('div').onclick = whisperClick;",""));if(like_bool($q))return'<input type="checkbox" value="'.h($Y?$Y:1).'"'.($Y?' checked':'')."$Da>";$Mc="";if(preg_match('~time~',$q["type"]))$Mc=lang(54);if(preg_match('~date|timestamp~',$q["type"]))$Mc=lang(55).($Mc?" [$Mc]":"");if($Mc)return"<input value='".h($Y)."'$Da> ($Mc)";if(preg_match('~_(md5|sha1)$~i',$q["field"]))return"<input type='password' value='".h($Y)."'$Da>";return'';}function
editHint($R,$q,$Y){return(preg_match('~\s+(\[.*\])$~',($q["comment"]!=""?$q["comment"]:$q["field"]),$B)?h(" $B[1]"):'');}function
processInput($q,$Y,$Ac=""){if($Ac=="now")return"$Ac()";$J=$Y;if(preg_match('~date|timestamp~',$q["type"])&&preg_match('(^'.str_replace('\\$1','(?P<p1>\\d*)',preg_replace('~(\\\\\\$([2-6]))~','(?P<p\\2>\\d{1,2})',preg_quote(lang(40)))).'(.*))',$Y,$B))$J=($B["p1"]!=""?$B["p1"]:($B["p2"]!=""?($B["p2"]<70?20:19).$B["p2"]:gmdate("Y")))."-$B[p3]$B[p4]-$B[p5]$B[p6]".end($B);$J=($q["type"]=="bit"&&preg_match('~^[0-9]+$~',$Y)?$J:q($J));if($Y==""&&like_bool($q))$J="0";elseif($Y==""&&($q["null"]||!preg_match('~char|text~',$q["type"])))$J="NULL";elseif(preg_match('~^(md5|sha1)$~',$Ac))$J="$Ac($J)";return
unconvert_field($q,$J);}function
dumpOutput(){return
array();}function
dumpFormat(){return
array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($n){}function
dumpTable(){echo"\xef\xbb\xbf";}function
dumpData($R,$Mf,$H){global$i;$I=$i->query($H,1);if($I){while($K=$I->fetch_assoc()){if($Mf=="table"){dump_csv(array_keys($K));$Mf="INSERT";}dump_csv($K);}}}function
dumpFilename($Qc){return
friendly_url($Qc);}function
dumpHeaders($Qc,$Sd=false){$bc="csv";header("Content-Type: text/csv; charset=utf-8");return$bc;}function
importServerPath(){}function
homepage(){return
true;}function
navigation($Rd){global$ca;echo'<h1>
',$this->name(),' <span class="version">',$ca,'</span>
<a href="https://www.adminer.org/editor/#download"',target_blank(),' id="version">',(version_compare($ca,$_COOKIE["adminer_version"])<0?h($_COOKIE["adminer_version"]):""),'</a>
</h1>
';if($Rd=="auth"){$nc=true;foreach((array)$_SESSION["pwds"]as$Kg=>$vf){foreach($vf[""]as$V=>$G){if($G!==null){if($nc){echo"<p id='logins'>",script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");$nc=false;}echo"<a href='".h(auth_url($Kg,"",$V))."'>".($V!=""?h($V):"<i>".lang(7)."</i>")."</a><br>\n";}}}}else{$this->databasesPrint($Rd);if($Rd!="db"&&$Rd!="ns"){$S=table_status('',true);if(!$S)echo"<p class='message'>".lang(9)."\n";else$this->tablesPrint($S);}}}function
databasesPrint($Rd){}function
tablesPrint($T){echo"<ul id='tables'>",script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");foreach($T
as$K){echo'<li>';$C=$this->tableName($K);if(isset($K["Engine"])&&$C!="")echo"<a href='".h(ME).'select='.urlencode($K["Name"])."'".bold($_GET["select"]==$K["Name"]||$_GET["edit"]==$K["Name"],"select")." title='".lang(56)."'>$C</a>\n";}echo"</ul>\n";}function
_foreignColumn($uc,$f){foreach((array)$uc[$f]as$tc){if(count($tc["source"])==1){$C=$this->rowDescription($tc["table"]);if($C!=""){$u=idf_escape($tc["target"][0]);return
array($tc["table"],$u,$C);}}}}function
_foreignKeyOptions($R,$f,$Y=null){global$i;if(list($Vf,$u,$C)=$this->_foreignColumn(column_foreign_keys($R),$f)){$J=&$this->_values[$Vf];if($J===null){$S=table_status($Vf);$J=($S["Rows"]>1000?"":array(""=>"")+get_key_vals("SELECT $u, $C FROM ".table($Vf)." ORDER BY 2"));}if(!$J&&$Y!==null)return$i->result("SELECT $C FROM ".table($Vf)." WHERE $u = ".q($Y));return$J;}}}$b=(function_exists('adminer_object')?adminer_object():new
Adminer);function
page_header($dg,$p="",$Ra=array(),$eg=""){global$ba,$ca,$b,$Fb,$y;page_headers();if(is_ajax()&&$p){page_messages($p);exit;}$fg=$dg.($eg!=""?": $eg":"");$gg=strip_tags($fg.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".$b->name());echo'<!DOCTYPE html>
<html lang="',$ba,'" dir="',lang(57),'">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<title>',$gg,'</title>
<link rel="stylesheet" type="text/css" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=4.6.2"),'">
',script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=4.6.2");if($b->head()){echo'<link rel="shortcut icon" type="image/x-icon" href="',h(preg_replace("~\\?.*~","",ME)."?file=favicon.ico&version=4.6.2"),'">
<link rel="apple-touch-icon" href="',h(preg_replace("~\\?.*~","",ME)."?file=favicon.ico&version=4.6.2"),'">
';foreach($b->css()as$rb){echo'<link rel="stylesheet" type="text/css" href="',h($rb),'">
';}}echo'
<body class="',lang(57),' nojs">
';$s=get_temp_dir()."/adminer.version";if(!$_COOKIE["adminer_version"]&&function_exists('openssl_verify')&&file_exists($s)&&filemtime($s)+86400>time()){$Lg=unserialize(file_get_contents($s));$Le="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";if(openssl_verify($Lg["version"],base64_decode($Lg["signature"]),$Le)==1)$_COOKIE["adminer_version"]=$Lg["version"];}echo'<script',nonce(),'>
mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick',(isset($_COOKIE["adminer_version"])?"":", onload: partial(verifyVersion, '$ca', '".js_escape(ME)."', '".get_token()."')");?>});
document.body.className = document.body.className.replace(/ nojs/, ' js');
var offlineMessage = '<?php echo
js_escape(lang(58)),'\';
var thousandsSeparator = \'',js_escape(lang(5)),'\';
</script>

<div id="help" class="jush-',$y,' jsonly hidden"></div>
',script("mixin(qs('#help'), {onmouseover: function () { helpOpen = 1; }, onmouseout: helpMouseout});"),'
<div id="content">
';if($Ra!==null){$A=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($A?$A:".").'">'.$Fb[DRIVER].'</a> &raquo; ';$A=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$O=$b->serverName(SERVER);$O=($O!=""?$O:lang(59));if($Ra===false)echo"$O\n";else{echo"<a href='".($A?h($A):".")."' accesskey='1' title='Alt+Shift+1'>$O</a> &raquo; ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ra)))echo'<a href="'.h($A."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> &raquo; ';if(is_array($Ra)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> &raquo; ';foreach($Ra
as$z=>$X){$zb=(is_array($X)?$X[1]:h($X));if($zb!="")echo"<a href='".h(ME."$z=").urlencode(is_array($X)?$X[0]:$X)."'>$zb</a> &raquo; ";}}echo"$dg\n";}}echo"<h2>$fg</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($p);$m=&get_session("dbs");if(DB!=""&&$m&&!in_array(DB,$m,true))$m=null;stop_session();define("PAGE_HEADER",1);}function
page_headers(){global$b;header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach($b->csp()as$qb){$Kc=array();foreach($qb
as$z=>$X)$Kc[]="$z $X";header("Content-Security-Policy: ".implode("; ",$Kc));}$b->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self'","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
get_nonce(){static$Yd;if(!$Yd)$Yd=base64_encode(rand_string());return$Yd;}function
page_messages($p){$Dg=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Od=$_SESSION["messages"][$Dg];if($Od){echo"<div class='message'>".implode("</div>\n<div class='message'>",$Od)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$Dg]);}if($p)echo"<div class='error'>$p</div>\n";}function
page_footer($Rd=""){global$b,$jg;echo'</div>

';switch_lang();if($Rd!="auth"){echo'<form action="" method="post">
<p class="logout">
<input type="submit" name="logout" value="',lang(60),'" id="logout">
<input type="hidden" name="token" value="',$jg,'">
</p>
</form>
';}echo'<div id="menu">
';$b->navigation($Rd);echo'</div>
',script("setupSubmitHighlight(document);");}function
int32($Ud){while($Ud>=2147483648)$Ud-=4294967296;while($Ud<=-2147483649)$Ud+=4294967296;return(int)$Ud;}function
long2str($W,$Pg){$if='';foreach($W
as$X)$if.=pack('V',$X);if($Pg)return
substr($if,0,end($W));return$if;}function
str2long($if,$Pg){$W=array_values(unpack('V*',str_pad($if,4*ceil(strlen($if)/4),"\0")));if($Pg)$W[]=strlen($if);return$W;}function
xxtea_mx($ah,$Zg,$Pf,$kd){return
int32((($ah>>5&0x7FFFFFF)^$Zg<<2)+(($Zg>>3&0x1FFFFFFF)^$ah<<4))^int32(($Pf^$Zg)+($kd^$ah));}function
encrypt_string($Kf,$z){if($Kf=="")return"";$z=array_values(unpack("V*",pack("H*",md5($z))));$W=str2long($Kf,true);$Ud=count($W)-1;$ah=$W[$Ud];$Zg=$W[0];$Me=floor(6+52/($Ud+1));$Pf=0;while($Me-->0){$Pf=int32($Pf+0x9E3779B9);$Jb=$Pf>>2&3;for($re=0;$re<$Ud;$re++){$Zg=$W[$re+1];$Td=xxtea_mx($ah,$Zg,$Pf,$z[$re&3^$Jb]);$ah=int32($W[$re]+$Td);$W[$re]=$ah;}$Zg=$W[0];$Td=xxtea_mx($ah,$Zg,$Pf,$z[$re&3^$Jb]);$ah=int32($W[$Ud]+$Td);$W[$Ud]=$ah;}return
long2str($W,false);}function
decrypt_string($Kf,$z){if($Kf=="")return"";if(!$z)return
false;$z=array_values(unpack("V*",pack("H*",md5($z))));$W=str2long($Kf,false);$Ud=count($W)-1;$ah=$W[$Ud];$Zg=$W[0];$Me=floor(6+52/($Ud+1));$Pf=int32($Me*0x9E3779B9);while($Pf){$Jb=$Pf>>2&3;for($re=$Ud;$re>0;$re--){$ah=$W[$re-1];$Td=xxtea_mx($ah,$Zg,$Pf,$z[$re&3^$Jb]);$Zg=int32($W[$re]-$Td);$W[$re]=$Zg;}$ah=$W[$Ud];$Td=xxtea_mx($ah,$Zg,$Pf,$z[$re&3^$Jb]);$Zg=int32($W[0]-$Td);$W[0]=$Zg;$Pf=int32($Pf-0x9E3779B9);}return
long2str($W,true);}$i='';$Jc=$_SESSION["token"];if(!$Jc)$_SESSION["token"]=rand(1,1e6);$jg=get_token();$_e=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($z)=explode(":",$X);$_e[$z]=$X;}}function
add_invalid_login(){global$b;$zc=file_open_lock(get_temp_dir()."/adminer.invalid");if(!$zc)return;$gd=unserialize(stream_get_contents($zc));$ag=time();if($gd){foreach($gd
as$hd=>$X){if($X[0]<$ag)unset($gd[$hd]);}}$fd=&$gd[$b->bruteForceKey()];if(!$fd)$fd=array($ag+30*60,0);$fd[1]++;file_write_unlock($zc,serialize($gd));}function
check_invalid_login(){global$b;$gd=unserialize(@file_get_contents(get_temp_dir()."/adminer.invalid"));$fd=$gd[$b->bruteForceKey()];$Xd=($fd[1]>29?$fd[0]-time():0);if($Xd>0)auth_error(lang(61,ceil($Xd/60)));}$Ea=$_POST["auth"];if($Ea){session_regenerate_id();$Kg=$Ea["driver"];$O=$Ea["server"];$V=$Ea["username"];$G=(string)$Ea["password"];$n=$Ea["db"];set_password($Kg,$O,$V,$G);$_SESSION["db"][$Kg][$O][$V][$n]=true;if($Ea["permanent"]){$z=base64_encode($Kg)."-".base64_encode($O)."-".base64_encode($V)."-".base64_encode($n);$Ie=$b->permanentLogin(true);$_e[$z]="$z:".base64_encode($Ie?encrypt_string($G,$Ie):"");cookie("adminer_permanent",implode(" ",$_e));}if(count($_POST)==1||DRIVER!=$Kg||SERVER!=$O||$_GET["username"]!==$V||DB!=$n)redirect(auth_url($Kg,$O,$V,$n));}elseif($_POST["logout"]){if($Jc&&!verify_token()){page_header(lang(60),lang(62));page_footer("db");exit;}else{foreach(array("pwds","db","dbs","queries")as$z)set_session($z,null);unset_permanent();redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),lang(63).' '.lang(64,'https://sourceforge.net/donate/index.php?group_id=264133'));}}elseif($_e&&!$_SESSION["pwds"]){session_regenerate_id();$Ie=$b->permanentLogin();foreach($_e
as$z=>$X){list(,$Za)=explode(":",$X);list($Kg,$O,$V,$n)=array_map('base64_decode',explode("-",$z));set_password($Kg,$O,$V,decrypt_string(base64_decode($Za),$Ie));$_SESSION["db"][$Kg][$O][$V][$n]=true;}}function
unset_permanent(){global$_e;foreach($_e
as$z=>$X){list($Kg,$O,$V,$n)=array_map('base64_decode',explode("-",$z));if($Kg==DRIVER&&$O==SERVER&&$V==$_GET["username"]&&$n==DB)unset($_e[$z]);}cookie("adminer_permanent",implode(" ",$_e));}function
auth_error($p){global$b,$Jc;$wf=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$wf]||$_GET[$wf])&&!$Jc)$p=lang(65);else{add_invalid_login();$G=get_password();if($G!==null){if($G===false)$p.='<br>'.lang(66,target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent();}}if(!$_COOKIE[$wf]&&$_GET[$wf]&&ini_bool("session.use_only_cookies"))$p=lang(67);$F=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?$_COOKIE["adminer_key"]:rand_string()),$F["lifetime"]);page_header(lang(34),$p,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".lang(68)."\n";echo"</div>\n";$b->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])){if(!class_exists("Min_DB")){unset($_SESSION["pwds"][DRIVER]);unset_permanent();page_header(lang(69),lang(70,implode(", ",$De)),false);page_footer("auth");exit;}list($Oc,$Be)=explode(":",SERVER,2);if(is_numeric($Be)&&$Be<1024)auth_error(lang(71));check_invalid_login();$i=connect();$o=new
Min_Driver($i);}$Bd=null;if(!is_object($i)||($Bd=$b->login($_GET["username"],get_password()))!==true)auth_error((is_string($i)?h($i):(is_string($Bd)?$Bd:lang(72))));if($Ea&&$_POST["token"])$_POST["token"]=$jg;$p='';if($_POST){if(!verify_token()){$bd="max_input_vars";$Ld=ini_get($bd);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars")as$z){$X=ini_get($z);if($X&&(!$Ld||$X<$Ld)){$bd=$z;$Ld=$X;}}}$p=(!$_POST["token"]&&$Ld?lang(73,"'$bd'"):lang(62).' '.lang(74));}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$p=lang(75,"'post_max_size'");if(isset($_GET["sql"]))$p.=' '.lang(76);}if(!ini_bool("session.use_cookies")||@ini_set("session.use_cookies",false)!==false)session_write_close();function
email_header($Kc){return"=?UTF-8?B?".base64_encode($Kc)."?=";}function
send_mail($Nb,$Nf,$Nd,$_c="",$lc=array()){$Tb=(DIRECTORY_SEPARATOR=="/"?"\n":"\r\n");$Nd=str_replace("\n",$Tb,wordwrap(str_replace("\r","","$Nd\n")));$Qa=uniqid("boundary");$Ba="";foreach((array)$lc["error"]as$z=>$X){if(!$X)$Ba.="--$Qa$Tb"."Content-Type: ".str_replace("\n","",$lc["type"][$z]).$Tb."Content-Disposition: attachment; filename=\"".preg_replace('~["\\n]~','',$lc["name"][$z])."\"$Tb"."Content-Transfer-Encoding: base64$Tb$Tb".chunk_split(base64_encode(file_get_contents($lc["tmp_name"][$z])),76,$Tb).$Tb;}$La="";$Lc="Content-Type: text/plain; charset=utf-8$Tb"."Content-Transfer-Encoding: 8bit";if($Ba){$Ba.="--$Qa--$Tb";$La="--$Qa$Tb$Lc$Tb$Tb";$Lc="Content-Type: multipart/mixed; boundary=\"$Qa\"";}$Lc.=$Tb."MIME-Version: 1.0$Tb"."X-Mailer: Adminer Editor".($_c?$Tb."From: ".str_replace("\n","",$_c):"");return
mail($Nb,email_header($Nf),$La.$Nd.$Ba,$Lc);}function
like_bool($q){return
preg_match("~bool|(tinyint|bit)\\(1\\)~",$q["full_type"]);}$i->select_db($b->database());$ee="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";$Fb[DRIVER]=lang(34);if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["download"])){$a=$_GET["download"];$r=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$I=$o->select($a,$M,array(where($_GET,$r)),$M);$K=($I?$I->fetch_row():array());echo$o->value($K[0],$r[$_GET["field"]]);exit;}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$r=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$r):""):where($_GET,$r));$Cg=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($r
as$C=>$q){if(!isset($q["privileges"][$Cg?"update":"insert"])||$b->fieldName($q)=="")unset($r[$C]);}if($_POST&&!$p&&!isset($_GET["select"])){$Ad=$_POST["referer"];if($_POST["insert"])$Ad=($Cg?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Ad))$Ad=ME."select=".urlencode($a);$x=indexes($a);$yg=unique_array($_GET["where"],$x);$Pe="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Ad,lang(77),$o->delete($a,$Pe,!$yg));else{$P=array();foreach($r
as$C=>$q){$X=process_input($q);if($X!==false&&$X!==null)$P[idf_escape($C)]=$X;}if($Cg){if(!$P)redirect($Ad);queries_redirect($Ad,lang(78),$o->update($a,$P,$Pe,!$yg));if(is_ajax()){page_headers();page_messages($p);exit;}}else{$I=$o->insert($a,$P);$vd=($I?last_id():0);queries_redirect($Ad,lang(79,($vd?" $vd":"")),$I);}}}$K=null;if($_POST["save"])$K=(array)$_POST["fields"];elseif($Z){$M=array();foreach($r
as$C=>$q){if(isset($q["privileges"]["select"])){$_a=convert_field($q);if($_POST["clone"]&&$q["auto_increment"])$_a="''";if($y=="sql"&&preg_match("~enum|set~",$q["type"]))$_a="1*".idf_escape($C);$M[]=($_a?"$_a AS ":"").idf_escape($C);}}$K=array();if(!support("table"))$M=array("*");if($M){$I=$o->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));if(!$I)$p=error();else{$K=$I->fetch_assoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetch_assoc()))$K=null;}}if(!support("table")&&!$r){if(!$Z){$I=$o->select($a,array("*"),$Z,array("*"));$K=($I?$I->fetch_assoc():false);if(!$K)$K=array($o->primary=>"");}if($K){foreach($K
as$z=>$X){if(!$Z)$K[$z]=null;$r[$z]=array("field"=>$z,"null"=>($z!=$o->primary),"auto_increment"=>($z==$o->primary));}}}edit_form($a,$r,$K,$Cg);}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$x=indexes($a);$r=fields($a);$wc=column_foreign_keys($a);$de=$S["Oid"];parse_str($_COOKIE["adminer_import"],$ta);$gf=array();$g=array();$Yf=null;foreach($r
as$z=>$q){$C=$b->fieldName($q);if(isset($q["privileges"]["select"])&&$C!=""){$g[$z]=html_entity_decode(strip_tags($C),ENT_QUOTES);if(is_shortable($q))$Yf=$b->selectLengthProcess();}$gf+=$q["privileges"];}list($M,$Cc)=$b->selectColumnsProcess($g,$x);$id=count($Cc)<count($M);$Z=$b->selectSearchProcess($r,$x);$me=$b->selectOrderProcess($r,$x);$_=$b->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$zg=>$K){$_a=convert_field($r[key($K)]);$M=array($_a?$_a:idf_escape(key($K)));$Z[]=where_check($zg,$r);$J=$o->select($a,$M,$Z,$M);if($J)echo
reset($J->fetch_row());}exit;}$Fe=$Ag=null;foreach($x
as$w){if($w["type"]=="PRIMARY"){$Fe=array_flip($w["columns"]);$Ag=($M?$Fe:array());foreach($Ag
as$z=>$X){if(in_array(idf_escape($z),$M))unset($Ag[$z]);}break;}}if($de&&!$Fe){$Fe=$Ag=array($de=>0);$x[]=array("type"=>"PRIMARY","columns"=>array($de));}if($_POST&&!$p){$Ug=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Xa=array();foreach($_POST["check"]as$Ua)$Xa[]=where_check($Ua,$r);$Ug[]="((".implode(") OR (",$Xa)."))";}$Ug=($Ug?"\nWHERE ".implode(" AND ",$Ug):"");if($_POST["export"]){cookie("adminer_import","output=".urlencode($_POST["output"])."&format=".urlencode($_POST["format"]));dump_headers($a);$b->dumpTable($a,"");$_c=($M?implode(", ",$M):"*").convert_fields($g,$r,$M)."\nFROM ".table($a);$Ec=($Cc&&$id?"\nGROUP BY ".implode(", ",$Cc):"").($me?"\nORDER BY ".implode(", ",$me):"");if(!is_array($_POST["check"])||$Fe)$H="SELECT $_c$Ug$Ec";else{$xg=array();foreach($_POST["check"]as$X)$xg[]="(SELECT".limit($_c,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$r).$Ec,1).")";$H=implode(" UNION ALL ",$xg);}$b->dumpData($a,"table",$H);exit;}if(!$b->selectEmailProcess($Z,$wc)){if($_POST["save"]||$_POST["delete"]){$I=true;$ua=0;$P=array();if(!$_POST["delete"]){foreach($g
as$C=>$X){$X=process_input($r[$C]);if($X!==null&&($_POST["clone"]||$X!==false))$P[idf_escape($C)]=($X!==false?$X:idf_escape($C));}}if($_POST["delete"]||$P){if($_POST["clone"])$H="INTO ".table($a)." (".implode(", ",array_keys($P)).")\nSELECT ".implode(", ",$P)."\nFROM ".table($a);if($_POST["all"]||($Fe&&is_array($_POST["check"]))||$id){$I=($_POST["delete"]?$o->delete($a,$Ug):($_POST["clone"]?queries("INSERT $H$Ug"):$o->update($a,$P,$Ug)));$ua=$i->affected_rows;}else{foreach((array)$_POST["check"]as$X){$Qg="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$r);$I=($_POST["delete"]?$o->delete($a,$Qg,1):($_POST["clone"]?queries("INSERT".limit1($a,$H,$Qg)):$o->update($a,$P,$Qg,1)));if(!$I)break;$ua+=$i->affected_rows;}}}$Nd=lang(80,$ua);if($_POST["clone"]&&$I&&$ua==1){$vd=last_id();if($vd)$Nd=lang(79," $vd");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$Nd,$I);if(!$_POST["delete"]){edit_form($a,$r,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$p=lang(81);else{$I=true;$ua=0;foreach($_POST["val"]as$zg=>$K){$P=array();foreach($K
as$z=>$X){$z=bracket_escape($z,1);$P[idf_escape($z)]=(preg_match('~char|text~',$r[$z]["type"])||$X!=""?$b->processInput($r[$z],$X):"NULL");}$I=$o->update($a,$P," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($zg,$r),!$id&&!$Fe," ");if(!$I)break;$ua+=$i->affected_rows;}queries_redirect(remove_from_uri(),lang(80,$ua),$I);}}elseif(!is_string($kc=get_file("csv_file",true)))$p=upload_error($kc);elseif(!preg_match('~~u',$kc))$p=lang(82);else{cookie("adminer_import","output=".urlencode($ta["output"])."&format=".urlencode($_POST["separator"]));$I=true;$fb=array_keys($r);preg_match_all('~(?>"[^"]*"|[^"\\r\\n]+)+~',$kc,$Hd);$ua=count($Hd[0]);$o->begin();$N=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$L=array();foreach($Hd[0]as$z=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$N]*)$N~",$X.$N,$Id);if(!$z&&!array_diff($Id[1],$fb)){$fb=$Id[1];$ua--;}else{$P=array();foreach($Id[1]as$t=>$cb)$P[idf_escape($fb[$t])]=($cb==""&&$r[$fb[$t]]["null"]?"NULL":q(str_replace('""','"',preg_replace('~^"|"$~','',$cb))));$L[]=$P;}}$I=(!$L||$o->insertUpdate($a,$L,$Fe));if($I)$I=$o->commit();queries_redirect(remove_from_uri("page"),lang(83,$ua),$I);$o->rollback();}}}$Sf=$b->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(46).": $Sf",$p);$P=null;if(isset($gf["insert"])||!support("table")){$P="";foreach((array)$_GET["where"]as$X){if($wc[$X["col"]]&&count($wc[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&!preg_match('~[_%]~',$X["val"]))))$P.="&set".urlencode("[".bracket_escape($X["col"])."]")."=".urlencode($X["val"]);}}$b->selectLinks($S,$P);if(!$g&&support("table"))echo"<p class='error'>".lang(84).($r?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?'<input type="hidden" name="db" value="'.h(DB).'">'.(isset($_GET["ns"])?'<input type="hidden" name="ns" value="'.h($_GET["ns"]).'">':""):"");echo'<input type="hidden" name="select" value="'.h($a).'">',"</div>\n";$b->selectColumnsPrint($M,$g);$b->selectSearchPrint($Z,$g,$x);$b->selectOrderPrint($me,$g,$x);$b->selectLimitPrint($_);$b->selectLengthPrint($Yf);$b->selectActionPrint($x);echo"</form>\n";$E=$_GET["page"];if($E=="last"){$yc=$i->result(count_rows($a,$Z,$id,$Cc));$E=floor(max(0,$yc-1)/$_);}$nf=$M;$Dc=$Cc;if(!$nf){$nf[]="*";$mb=convert_fields($g,$r,$M);if($mb)$nf[]=substr($mb,2);}foreach($M
as$z=>$X){$q=$r[idf_unescape($X)];if($q&&($_a=convert_field($q)))$nf[$z]="$_a AS $X";}if(!$id&&$Ag){foreach($Ag
as$z=>$X){$nf[]=idf_escape($z);if($Dc)$Dc[]=idf_escape($z);}}$I=$o->select($a,$nf,$Z,$Dc,$me,$_,$E,true);if(!$I)echo"<p class='error'>".error()."\n";else{if($y=="mssql"&&$E)$I->seek($_*$E);$Pb=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$L=array();while($K=$I->fetch_assoc()){if($E&&$y=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$_!=""&&$Cc&&$id&&$y=="sql")$yc=$i->result(" SELECT FOUND_ROWS()");if(!$L)echo"<p class='message'>".lang(12)."\n";else{$Ka=$b->backwardKeys($a,$Sf);echo"<table id='table' cellspacing='0' class='nowrap checkable'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$Cc&&$M?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".lang(85)."</a>");$Vd=array();$Bc=array();reset($M);$Re=1;foreach($L[0]as$z=>$X){if(!isset($Ag[$z])){$X=$_GET["columns"][key($M)];$q=$r[$M?($X?$X["col"]:current($M)):$z];$C=($q?$b->fieldName($q,$Re):($X["fun"]?"*":$z));if($C!=""){$Re++;$Vd[$z]=$C;$f=idf_escape($z);$Pc=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($z);$zb="&desc%5B0%5D=1";echo"<th>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});",""),'<a href="'.h($Pc.($me[0]==$f||$me[0]==$z||(!$me&&$id&&$Cc[0]==$f)?$zb:'')).'">';echo
apply_sql_function($X["fun"],$C)."</a>";echo"<span class='column hidden'>","<a href='".h($Pc.$zb)."' title='".lang(86)."' class='text'> â†“</a>";if(!$X["fun"]){echo'<a href="#fieldset-search" title="'.lang(41).'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($z)."');");}echo"</span>";}$Bc[$z]=$X["fun"];next($M);}}$yd=array();if($_GET["modify"]){foreach($L
as$K){foreach($K
as$z=>$X)$yd[$z]=max($yd[$z],min(40,strlen(utf8_decode($X))));}}echo($Ka?"<th>".lang(87):"")."</thead>\n";if(is_ajax()){if($_%2==1&&$E%2==1)odd();ob_end_clean();}foreach($b->rowDescriptions($L,$wc)as$Ud=>$K){$yg=unique_array($L[$Ud],$x);if(!$yg){$yg=array();foreach($L[$Ud]as$z=>$X){if(!preg_match('~^(COUNT\\((\\*|(DISTINCT )?`(?:[^`]|``)+`)\\)|(AVG|GROUP_CONCAT|MAX|MIN|SUM)\\(`(?:[^`]|``)+`\\))$~',$z))$yg[$z]=$X;}}$zg="";foreach($yg
as$z=>$X){if(($y=="sql"||$y=="pgsql")&&preg_match('~char|text|enum|set~',$r[$z]["type"])&&strlen($X)>64){$z=(strpos($z,'(')?$z:idf_escape($z));$z="MD5(".($y!='sql'||preg_match("~^utf8~",$r[$z]["collation"])?$z:"CONVERT($z USING ".charset($i).")").")";$X=md5($X);}$zg.="&".($X!==null?urlencode("where[".bracket_escape($z)."]")."=".urlencode($X):"null%5B%5D=".urlencode($z));}echo"<tr".odd().">".(!$Cc&&$M?"":"<td>".checkbox("check[]",substr($zg,1),in_array(substr($zg,1),(array)$_POST["check"])).($id||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$zg)."' class='edit'>".lang(88)."</a>"));foreach($K
as$z=>$X){if(isset($Vd[$z])){$q=$r[$z];$X=$o->value($X,$q);if($X!=""&&(!isset($Pb[$z])||$Pb[$z]!=""))$Pb[$z]=(is_mail($X)?$Vd[$z]:"");$A="";if(preg_match('~blob|bytea|raw|file~',$q["type"])&&$X!="")$A=ME.'download='.urlencode($a).'&field='.urlencode($z).$zg;if(!$A&&$X!==null){foreach((array)$wc[$z]as$vc){if(count($wc[$z])==1||end($vc["source"])==$z){$A="";foreach($vc["source"]as$t=>$Bf)$A.=where_link($t,$vc["target"][$t],$L[$Ud][$Bf]);$A=($vc["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\\1'.urlencode($vc["db"]),ME):ME).'select='.urlencode($vc["table"]).$A;if($vc["ns"])$A=preg_replace('~([?&]ns=)[^&]+~','\\1'.urlencode($vc["ns"]),$A);if(count($vc["source"])==1)break;}}}if($z=="COUNT(*)"){$A=ME."select=".urlencode($a);$t=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$yg))$A.=where_link($t++,$W["col"],$W["val"],$W["op"]);}foreach($yg
as$kd=>$W)$A.=where_link($t++,$kd,$W);}$X=select_value($X,$A,$q,$Yf);$u=h("val[$zg][".bracket_escape($z)."]");$Y=$_POST["val"][$zg][bracket_escape($z)];$Lb=!is_array($K[$z])&&is_utf8($X)&&$L[$Ud][$z]==$K[$z]&&!$Bc[$z];$Xf=preg_match('~text|lob~',$q["type"]);if(($_GET["modify"]&&$Lb)||$Y!==null){$Gc=h($Y!==null?$Y:$K[$z]);echo"<td>".($Xf?"<textarea name='$u' cols='30' rows='".(substr_count($K[$z],"\n")+1)."'>$Gc</textarea>":"<input name='$u' value='$Gc' size='$yd[$z]'>");}else{$Cd=strpos($X,"<i>...</i>");echo"<td id='$u' data-text='".($Cd?2:($Xf?1:0))."'".($Lb?"":" data-warning='".h(lang(89))."'").">$X</td>";}}}if($Ka)echo"<td>";$b->backwardKeysPrint($Ka,$L[$Ud]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n";}if(!is_ajax()){if($L||$E){$Xb=true;if($_GET["page"]!="last"){if($_==""||(count($L)<$_&&($L||!$E)))$yc=($E?$E*$_:0)+count($L);elseif($y!="sql"||!$id){$yc=($id?false:found_rows($S,$Z));if($yc<max(1e4,2*($E+1)*$_))$yc=reset(slow_query(count_rows($a,$Z,$id,$Cc)));else$Xb=false;}}$se=($_!=""&&($yc===false||$yc>$_||$E));if($se){echo(($yc===false?count($L)+1:$yc-$E*$_)>$_?'<p><a href="'.h(remove_from_uri("page")."&page=".($E+1)).'" class="loadmore">'.lang(90).'</a>'.script("qsl('a').onclick = partial(selectLoadMore, ".(+$_).", '".lang(91)."...');",""):''),"\n";}}echo"<div class='footer'><div>\n";if($L||$E){if($se){$Jd=($yc===false?$E+(count($L)>=$_?2:1):floor(($yc-1)/$_));echo"<fieldset>";if($y!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".lang(92)."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".lang(92)."', '".($E+1)."')); return false; };"),pagination(0,$E).($E>5?" ...":"");for($t=max(1,$E-4);$t<min($Jd,$E+5);$t++)echo
pagination($t,$E);if($Jd>0){echo($E+5<$Jd?" ...":""),($Xb&&$yc!==false?pagination($Jd,$E):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Jd'>".lang(93)."</a>");}}else{echo"<legend>".lang(92)."</legend>",pagination(0,$E).($E>1?" ...":""),($E?pagination($E,$E):""),($Jd>$E?pagination($E+1,$E).($Jd>$E+1?" ...":""):"");}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(94)."</legend>";$Db=($Xb?"":"~ ").$yc;echo
checkbox("all",1,0,($yc!==false?($Xb?"":"~ ").lang(95,$yc):""),"var checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$Db' : checked); selectCount('selected2', this.checked || !checked ? '$Db' : checked);")."\n","</fieldset>\n";if($b->selectCommandPrint()){echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>',lang(85),'</legend><div>
<input type="submit" value="',lang(14),'"',($_GET["modify"]?'':' title="'.lang(81).'"'),'>
</div></fieldset>
<fieldset><legend>',lang(96),' <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="',lang(10),'">
<input type="submit" name="clone" value="',lang(97),'">
<input type="submit" name="delete" value="',lang(18),'">',confirm(),'</div></fieldset>
';}$xc=$b->dumpFormat();foreach((array)$_GET["columns"]as$f){if($f["fun"]){unset($xc['sql']);break;}}if($xc){print_fieldset("export",lang(98)." <span id='selected2'></span>");$qe=$b->dumpOutput();echo($qe?html_select("output",$qe,$ta["output"])." ":""),html_select("format",$xc,$ta["format"])," <input type='submit' name='export' value='".lang(98)."'>\n","</div></fieldset>\n";}$b->selectEmailPrint(array_filter($Pb,'strlen'),$g);}echo"</div></div>\n";if($b->selectImportPrint()){echo"<div>","<a href='#import'>".lang(99)."</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"<span id='import' class='hidden'>: ","<input type='file' name='csv_file'> ",html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$ta["format"],1);echo" <input type='submit' name='import' value='".lang(99)."'>","</span>","</div>";}echo"<input type='hidden' name='token' value='$jg'>\n","</form>\n",(!$Cc&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["script"])){if($_GET["script"]=="kill")$i->query("KILL ".number($_POST["kill"]));elseif(list($R,$u,$C)=$b->_foreignColumn(column_foreign_keys($_GET["source"]),$_GET["field"])){$_=11;$I=$i->query("SELECT $u, $C FROM ".table($R)." WHERE ".(preg_match('~^[0-9]+$~',$_GET["value"])?"$u = $_GET[value] OR ":"")."$C LIKE ".q("$_GET[value]%")." ORDER BY 2 LIMIT $_");for($t=1;($K=$I->fetch_row())&&$t<$_;$t++)echo"<a href='".h(ME."edit=".urlencode($R)."&where".urlencode("[".bracket_escape(idf_unescape($u))."]")."=".urlencode($K[0]))."'>".h($K[1])."</a><br>\n";if($K)echo"...\n";}exit;}else{page_header(lang(59),"",false);if($b->homepage()){echo"<form action='' method='post'>\n","<p>".lang(100).": <input type='search' name='query' value='".h($_POST["query"])."'> <input type='submit' value='".lang(41)."'>\n";if($_POST["query"]!="")search_tables();echo"<table cellspacing='0' class='nowrap checkable'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^tables\[/);",""),'<th>'.lang(101),'<td>'.lang(102),"</thead>\n";foreach(table_status()as$R=>$K){$C=$b->tableName($K);if(isset($K["Engine"])&&$C!=""){echo'<tr'.odd().'><td>'.checkbox("tables[]",$R,in_array($R,(array)$_POST["tables"],true)),"<th><a href='".h(ME).'select='.urlencode($R)."'>$C</a>";$X=format_number($K["Rows"]);echo"<td align='right'><a href='".h(ME."edit=").urlencode($R)."'>".($K["Engine"]=="InnoDB"&&$X?"~ $X":$X)."</a>";}}echo"</table>\n","</form>\n",script("tableCheck();");}}page_footer();