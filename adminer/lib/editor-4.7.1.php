<?php
/** Adminer Editor - Compact database editor
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2009 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 4.7.1
*/error_reporting(6135);$oc=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($oc||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$Fg=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($Fg)$$X=$Fg;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection(){global$h;return$h;}function
adminer(){global$b;return$b;}function
version(){global$ca;return$ca;}function
idf_unescape($u){$zd=substr($u,-1);return
str_replace($zd.$zd,$zd,substr($u,1,-1));}function
escape_string($X){return
substr(q($X),1,-1);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes($Pe,$oc=false){if(get_magic_quotes_gpc()){while(list($y,$X)=each($Pe)){foreach($X
as$pd=>$W){unset($Pe[$y][$pd]);if(is_array($W)){$Pe[$y][stripslashes($pd)]=$W;$Pe[]=&$Pe[$y][stripslashes($pd)];}else$Pe[$y][stripslashes($pd)]=($oc?$W:stripslashes($W));}}}}function
bracket_escape($u,$Ha=false){static$rg=array(':'=>':1',']'=>':2','['=>':3','"'=>':4');return
strtr($u,($Ha?array_flip($rg):$rg));}function
min_version($Rg,$Kd="",$i=null){global$h;if(!$i)$i=$h;$zf=$i->server_info;if($Kd&&preg_match('~([\d.]+)-MariaDB~',$zf,$A)){$zf=$A[1];$Rg=$Kd;}return(version_compare($zf,$Rg)>=0);}function
charset($h){return(min_version("5.5.3",0,$h)?"utf8mb4":"utf8");}function
script($Hf,$qg="\n"){return"<script".nonce().">$Hf</script>$qg";}function
script_src($Kg){return"<script src='".h($Kg)."'".nonce()."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace("\0","&#0;",htmlspecialchars($Q,ENT_QUOTES,'utf-8'));}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($B,$Y,$Wa,$vd="",$me="",$ab="",$wd=""){$J="<input type='checkbox' name='$B' value='".h($Y)."'".($Wa?" checked":"").($wd?" aria-labelledby='$wd'":"").">".($me?script("qsl('input').onclick = function () { $me };",""):"");return($vd!=""||$ab?"<label".($ab?" class='$ab'":"").">$J".h($vd)."</label>":$J);}function
optionlist($C,$tf=null,$Ng=false){$J="";foreach($C
as$pd=>$W){$re=array($pd=>$W);if(is_array($W)){$J.='<optgroup label="'.h($pd).'">';$re=$W;}foreach($re
as$y=>$X)$J.='<option'.($Ng||is_string($y)?' value="'.h($y).'"':'').(($Ng||is_string($y)?(string)$y:$X)===$tf?' selected':'').'>'.h($X);if(is_array($W))$J.='</optgroup>';}return$J;}function
html_select($B,$C,$Y="",$le=true,$wd=""){if($le)return"<select name='".h($B)."'".($wd?" aria-labelledby='$wd'":"").">".optionlist($C,$Y)."</select>".(is_string($le)?script("qsl('select').onchange = function () { $le };",""):"");$J="";foreach($C
as$y=>$X)$J.="<label><input type='radio' name='".h($B)."' value='".h($y)."'".($y==$Y?" checked":"").">".h($X)."</label>";return$J;}function
select_input($Da,$C,$Y="",$le="",$Ge=""){$ag=($C?"select":"input");return"<$ag$Da".($C?"><option value=''>$Ge".optionlist($C,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$Ge'>").($le?script("qsl('$ag').onchange = $le;",""):"");}function
confirm($Sd="",$uf="qsl('input')"){return
script("$uf.onclick = function () { return confirm('".($Sd?js_escape($Sd):lang(0))."'); };","");}function
print_fieldset($t,$Ad,$Ug=false){echo"<fieldset><legend>","<a href='#fieldset-$t'>$Ad</a>",script("qsl('a').onclick = partial(toggle, 'fieldset-$t');",""),"</legend>","<div id='fieldset-$t'".($Ug?"":" class='hidden'").">\n";}function
bold($Pa,$ab=""){return($Pa?" class='active $ab'":($ab?" class='$ab'":""));}function
odd($J=' class="odd"'){static$s=0;if(!$J)$s=-1;return($s++%2?$J:'');}function
js_escape($Q){return
addcslashes($Q,"\r\n'\\/");}function
json_row($y,$X=null){static$pc=true;if($pc)echo"{";if($y!=""){echo($pc?"":",")."\n\t\"".addcslashes($y,"\r\n\t\"\\/").'": '.($X!==null?'"'.addcslashes($X,"\r\n\"\\/").'"':'null');$pc=false;}else{echo"\n}\n";$pc=true;}}function
ini_bool($gd){$X=ini_get($gd);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
sid(){static$J;if($J===null)$J=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
set_password($Qg,$O,$V,$F){$_SESSION["pwds"][$Qg][$O][$V]=($_COOKIE["adminer_key"]&&is_string($F)?array(encrypt_string($F,$_COOKIE["adminer_key"])):$F);}function
get_password(){$J=get_session("pwds");if(is_array($J))$J=($_COOKIE["adminer_key"]?decrypt_string($J[0],$_COOKIE["adminer_key"]):false);return$J;}function
q($Q){global$h;return$h->quote($Q);}function
get_vals($G,$e=0){global$h;$J=array();$I=$h->query($G);if(is_object($I)){while($K=$I->fetch_row())$J[]=$K[$e];}return$J;}function
get_key_vals($G,$i=null,$Bf=true){global$h;if(!is_object($i))$i=$h;$J=array();$I=$i->query($G);if(is_object($I)){while($K=$I->fetch_row()){if($Bf)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($G,$i=null,$o="<p class='error'>"){global$h;$kb=(is_object($i)?$i:$h);$J=array();$I=$kb->query($G);if(is_object($I)){while($K=$I->fetch_assoc())$J[]=$K;}elseif(!$I&&!is_object($i)&&$o&&defined("PAGE_HEADER"))echo$o.error()."\n";return$J;}function
unique_array($K,$w){foreach($w
as$v){if(preg_match("~PRIMARY|UNIQUE~",$v["type"])){$J=array();foreach($v["columns"]as$y){if(!isset($K[$y]))continue
2;$J[$y]=$K[$y];}return$J;}}}function
escape_key($y){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$y,$A))return$A[1].idf_escape(idf_unescape($A[2])).$A[3];return
idf_escape($y);}function
where($Z,$q=array()){global$h,$x;$J=array();foreach((array)$Z["where"]as$y=>$X){$y=bracket_escape($y,1);$e=escape_key($y);$J[]=$e.($x=="sql"&&preg_match('~^[0-9]*\.[0-9]*$~',$X)?" LIKE ".q(addcslashes($X,"%_\\")):($x=="mssql"?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($q[$y],q($X))));if($x=="sql"&&preg_match('~char|text~',$q[$y]["type"])&&preg_match("~[^ -@]~",$X))$J[]="$e = ".q($X)." COLLATE ".charset($h)."_bin";}foreach((array)$Z["null"]as$y)$J[]=escape_key($y)." IS NULL";return
implode(" AND ",$J);}function
where_check($X,$q=array()){parse_str($X,$Ua);remove_slashes(array(&$Ua));return
where($Ua,$q);}function
where_link($s,$e,$Y,$oe="="){return"&where%5B$s%5D%5Bcol%5D=".urlencode($e)."&where%5B$s%5D%5Bop%5D=".urlencode(($Y!==null?$oe:"IS NULL"))."&where%5B$s%5D%5Bval%5D=".urlencode($Y);}function
convert_fields($f,$q,$M=array()){$J="";foreach($f
as$y=>$X){if($M&&!in_array(idf_escape($y),$M))continue;$_a=convert_field($q[$y]);if($_a)$J.=", $_a AS ".idf_escape($y);}return$J;}function
cookie($B,$Y,$Dd=2592000){global$aa;return
header("Set-Cookie: $B=".urlencode($Y).($Dd?"; expires=".gmdate("D, d M Y H:i:s",time()+$Dd)." GMT":"")."; path=".preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]).($aa?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
restart_session(){if(!ini_bool("session.use_cookies"))session_start();}function
stop_session($uc=false){if(!ini_bool("session.use_cookies")||($uc&&@ini_set("session.use_cookies",false)!==false))session_write_close();}function&get_session($y){return$_SESSION[$y][DRIVER][SERVER][$_GET["username"]];}function
set_session($y,$X){$_SESSION[$y][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($Qg,$O,$V,$m=null){global$Hb;preg_match('~([^?]*)\??(.*)~',remove_from_uri(implode("|",array_keys($Hb))."|username|".($m!==null?"db|":"").session_name()),$A);return"$A[1]?".(sid()?SID."&":"").($Qg!="server"||$O!=""?urlencode($Qg)."=".urlencode($O)."&":"")."username=".urlencode($V).($m!=""?"&db=".urlencode($m):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Fd,$Sd=null){if($Sd!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Fd!==null?$Fd:$_SERVER["REQUEST_URI"]))][]=$Sd;}if($Fd!==null){if($Fd=="")$Fd=".";header("Location: $Fd");exit;}}function
query_redirect($G,$Fd,$Sd,$Ze=true,$ac=true,$hc=false,$gg=""){global$h,$o,$b;if($ac){$Nf=microtime(true);$hc=!$h->query($G);$gg=format_time($Nf);}$Kf="";if($G)$Kf=$b->messageQuery($G,$gg,$hc);if($hc){$o=error().$Kf.script("messagesPrint();");return
false;}if($Ze)redirect($Fd,$Sd.$Kf);return
true;}function
queries($G){global$h;static$Te=array();static$Nf;if(!$Nf)$Nf=microtime(true);if($G===null)return
array(implode("\n",$Te),format_time($Nf));$Te[]=(preg_match('~;$~',$G)?"DELIMITER ;;\n$G;\nDELIMITER ":$G).";";return$h->query($G);}function
apply_queries($G,$T,$Xb='table'){foreach($T
as$R){if(!queries("$G ".$Xb($R)))return
false;}return
true;}function
queries_redirect($Fd,$Sd,$Ze){list($Te,$gg)=queries(null);return
query_redirect($Te,$Fd,$Sd,$Ze,false,!$Ze,$gg);}function
format_time($Nf){return
lang(1,max(0,microtime(true)-$Nf));}function
remove_from_uri($ze=""){return
substr(preg_replace("~(?<=[?&])($ze".(SID?"":"|".session_name()).")=[^&]*&~",'',"$_SERVER[REQUEST_URI]&"),0,-1);}function
pagination($D,$ub){return" ".($D==$ub?$D+1:'<a href="'.h(remove_from_uri("page").($D?"&page=$D".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($D+1)."</a>");}function
get_file($y,$yb=false){$mc=$_FILES[$y];if(!$mc)return
null;foreach($mc
as$y=>$X)$mc[$y]=(array)$X;$J='';foreach($mc["error"]as$y=>$o){if($o)return$o;$B=$mc["name"][$y];$ng=$mc["tmp_name"][$y];$nb=file_get_contents($yb&&preg_match('~\.gz$~',$B)?"compress.zlib://$ng":$ng);if($yb){$Nf=substr($nb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Nf,$af))$nb=iconv("utf-16","utf-8",$nb);elseif($Nf=="\xEF\xBB\xBF")$nb=substr($nb,3);$J.=$nb."\n\n";}else$J.=$nb;}return$J;}function
upload_error($o){$Pd=($o==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($o?lang(2).($Pd?" ".lang(3,$Pd):""):lang(4));}function
repeat_pattern($Ee,$Bd){return
str_repeat("$Ee{0,65535}",$Bd/65535)."$Ee{0,".($Bd%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
shorten_utf8($Q,$Bd=80,$Uf=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$Bd).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$Bd).")($)?)",$Q,$A);return
h($A[1]).$Uf.(isset($A[2])?"":"<i>â€¦</i>");}function
format_number($X){return
strtr(number_format($X,0,".",lang(5)),preg_split('~~u',lang(6),-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~[^a-z0-9_]~i','-',$X);}function
hidden_fields($Pe,$Xc=array()){$J=false;while(list($y,$X)=each($Pe)){if(!in_array($y,$Xc)){if(is_array($X)){foreach($X
as$pd=>$W)$Pe[$y."[$pd]"]=$W;}else{$J=true;echo'<input type="hidden" name="'.h($y).'" value="'.h($X).'">';}}}return$J;}function
hidden_fields_get(){echo(sid()?'<input type="hidden" name="'.session_name().'" value="'.h(session_id()).'">':''),(SERVER!==null?'<input type="hidden" name="'.DRIVER.'" value="'.h(SERVER).'">':""),'<input type="hidden" name="username" value="'.h($_GET["username"]).'">';}function
table_status1($R,$ic=false){$J=table_status($R,$ic);return($J?$J:array("Name"=>$R));}function
column_foreign_keys($R){global$b;$J=array();foreach($b->foreignKeys($R)as$yc){foreach($yc["source"]as$X)$J[$X][]=$yc;}return$J;}function
enum_input($U,$Da,$p,$Y,$Sb=null){global$b;preg_match_all("~'((?:[^']|'')*)'~",$p["length"],$Md);$J=($Sb!==null?"<label><input type='$U'$Da value='$Sb'".((is_array($Y)?in_array($Sb,$Y):$Y===0)?" checked":"")."><i>".lang(7)."</i></label>":"");foreach($Md[1]as$s=>$X){$X=stripcslashes(str_replace("''","'",$X));$Wa=(is_int($Y)?$Y==$s+1:(is_array($Y)?in_array($s+1,$Y):$Y===$X));$J.=" <label><input type='$U'$Da value='".($s+1)."'".($Wa?' checked':'').'>'.h($b->editVal($X,$p)).'</label>';}return$J;}function
input($p,$Y,$Dc){global$Ag,$b,$x;$B=h(bracket_escape($p["field"]));echo"<td class='function'>";if(is_array($Y)&&!$Dc){$ya=array($Y);if(version_compare(PHP_VERSION,5.4)>=0)$ya[]=JSON_PRETTY_PRINT;$Y=call_user_func_array('json_encode',$ya);$Dc="json";}$ff=($x=="mssql"&&$p["auto_increment"]);if($ff&&!$_POST["save"])$Dc=null;$Ec=(isset($_GET["select"])||$ff?array("orig"=>lang(8)):array())+$b->editFunctions($p);$Da=" name='fields[$B]'";if($p["type"]=="enum")echo
h($Ec[""])."<td>".$b->editInput($_GET["edit"],$p,$Da,$Y);else{$Lc=(in_array($Dc,$Ec)||isset($Ec[$Dc]));echo(count($Ec)>1?"<select name='function[$B]'>".optionlist($Ec,$Dc===null||$Lc?$Dc:"")."</select>".on_help("getTarget(event).value.replace(/^SQL\$/, '')",1).script("qsl('select').onchange = functionChange;",""):h(reset($Ec))).'<td>';$id=$b->editInput($_GET["edit"],$p,$Da,$Y);if($id!="")echo$id;elseif(preg_match('~bool~',$p["type"]))echo"<input type='hidden'$Da value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$Da value='1'>";elseif($p["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$p["length"],$Md);foreach($Md[1]as$s=>$X){$X=stripcslashes(str_replace("''","'",$X));$Wa=(is_int($Y)?($Y>>$s)&1:in_array($X,explode(",",$Y),true));echo" <label><input type='checkbox' name='fields[$B][$s]' value='".(1<<$s)."'".($Wa?' checked':'').">".h($b->editVal($X,$p)).'</label>';}}elseif(preg_match('~blob|bytea|raw|file~',$p["type"])&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif(($dg=preg_match('~text|lob~',$p["type"]))||preg_match("~\n~",$Y)){if($dg&&$x!="sqlite")$Da.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$Da.=" cols='30' rows='$L'".($L==1?" style='height: 1.2em;'":"");}echo"<textarea$Da>".h($Y).'</textarea>';}elseif($Dc=="json"||preg_match('~^jsonb?$~',$p["type"]))echo"<textarea$Da cols='50' rows='12' class='jush-js'>".h($Y).'</textarea>';else{$Rd=(!preg_match('~int~',$p["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$p["length"],$A)?((preg_match("~binary~",$p["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$p["unsigned"]?1:0)):($Ag[$p["type"]]?$Ag[$p["type"]]+($p["unsigned"]?0:1):0));if($x=='sql'&&min_version(5.6)&&preg_match('~time~',$p["type"]))$Rd+=7;echo"<input".((!$Lc||$Dc==="")&&preg_match('~(?<!o)int(?!er)~',$p["type"])&&!preg_match('~\[\]~',$p["full_type"])?" type='number'":"")." value='".h($Y)."'".($Rd?" data-maxlength='$Rd'":"").(preg_match('~char|binary~',$p["type"])&&$Rd>20?" size='40'":"")."$Da>";}echo$b->editHint($_GET["edit"],$p,$Y);$pc=0;foreach($Ec
as$y=>$X){if($y===""||!$X)break;$pc++;}if($pc)echo
script("mixin(qsl('td'), {onchange: partial(skipOriginal, $pc), oninput: function () { this.onchange(); }});");}}function
process_input($p){global$b,$n;$u=bracket_escape($p["field"]);$Dc=$_POST["function"][$u];$Y=$_POST["fields"][$u];if($p["type"]=="enum"){if($Y==-1)return
false;if($Y=="")return"NULL";return+$Y;}if($p["auto_increment"]&&$Y=="")return
null;if($Dc=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$p["on_update"])?idf_escape($p["field"]):false);if($Dc=="NULL")return"NULL";if($p["type"]=="set")return
array_sum((array)$Y);if($Dc=="json"){$Dc="";$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}if(preg_match('~blob|bytea|raw|file~',$p["type"])&&ini_bool("file_uploads")){$mc=get_file("fields-$u");if(!is_string($mc))return
false;return$n->quoteBinary($mc);}return$b->processInput($p,$Y,$Dc);}function
fields_from_edit(){global$n;$J=array();foreach((array)$_POST["field_keys"]as$y=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$y];$_POST["fields"][$X]=$_POST["field_vals"][$y];}}foreach((array)$_POST["fields"]as$y=>$X){$B=bracket_escape($y,1);$J[$B]=array("field"=>$B,"privileges"=>array("insert"=>1,"update"=>1),"null"=>1,"auto_increment"=>($y==$n->primary),);}return$J;}function
search_tables(){global$b,$h;$_GET["where"][0]["val"]=$_POST["query"];$wf="<ul>\n";foreach(table_status('',true)as$R=>$S){$B=$b->tableName($S);if(isset($S["Engine"])&&$B!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$I=$h->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",$b->selectSearchProcess(fields($R),array())),1));if(!$I||$I->fetch_row()){$Ne="<a href='".h(ME."select=".urlencode($R)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$B</a>";echo"$wf<li>".($I?$Ne:"<p class='error'>$Ne: ".error())."\n";$wf="";}}}echo($wf?"<p class='message'>".lang(9):"</ul>")."\n";}function
dump_headers($Uc,$Xd=false){global$b;$J=$b->dumpHeaders($Uc,$Xd);$we=$_POST["output"];if($we!="text")header("Content-Disposition: attachment; filename=".$b->dumpFilename($Uc).".$J".($we!="file"&&!preg_match('~[^0-9a-z]~',$we)?".$we":""));session_write_close();ob_flush();flush();return$J;}function
dump_csv($K){foreach($K
as$y=>$X){if(preg_match("~[\"\n,;\t]~",$X)||$X==="")$K[$y]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($_POST["format"]=="tsv"?"\t":";")),$K)."\r\n";}function
apply_sql_function($Dc,$e){return($Dc?($Dc=="unixepoch"?"DATETIME($e, '$Dc')":($Dc=="count distinct"?"COUNT(DISTINCT ":strtoupper("$Dc("))."$e)"):$e);}function
get_temp_dir(){$J=ini_get("upload_tmp_dir");if(!$J){if(function_exists('sys_get_temp_dir'))$J=sys_get_temp_dir();else{$r=@tempnam("","");if(!$r)return
false;$J=dirname($r);unlink($r);}}return$J;}function
file_open_lock($r){$Bc=@fopen($r,"r+");if(!$Bc){$Bc=@fopen($r,"w");if(!$Bc)return;chmod($r,0660);}flock($Bc,LOCK_EX);return$Bc;}function
file_write_unlock($Bc,$vb){rewind($Bc);fwrite($Bc,$vb);ftruncate($Bc,strlen($vb));flock($Bc,LOCK_UN);fclose($Bc);}function
password_file($qb){$r=get_temp_dir()."/adminer.key";$J=@file_get_contents($r);if($J||!$qb)return$J;$Bc=@fopen($r,"w");if($Bc){chmod($r,0660);$J=rand_string();fwrite($Bc,$J);fclose($Bc);}return$J;}function
rand_string(){return
md5(uniqid(mt_rand(),true));}function
select_value($X,$_,$p,$eg){global$b;if(is_array($X)){$J="";foreach($X
as$pd=>$W)$J.="<tr>".($X!=array_values($X)?"<th>".h($pd):"")."<td>".select_value($W,$_,$p,$eg);return"<table cellspacing='0'>$J</table>";}if(!$_)$_=$b->selectLink($X,$p);if($_===null){if(is_mail($X))$_="mailto:$X";if(is_url($X))$_=$X;}$J=$b->editVal($X,$p);if($J!==null){if(!is_utf8($J))$J="\0";elseif($eg!=""&&is_shortable($p))$J=shorten_utf8($J,max(0,+$eg));else$J=h($J);}return$b->selectVal($J,$_,$p,$X);}function
is_mail($Pb){$Aa='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$Gb='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$Ee="$Aa+(\\.$Aa+)*@($Gb?\\.)+$Gb";return
is_string($Pb)&&preg_match("(^$Ee(,\\s*$Ee)*\$)i",$Pb);}function
is_url($Q){$Gb='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^(https?)://($Gb?\\.)+$Gb(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q);}function
is_shortable($p){return
preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~',$p["type"]);}function
count_rows($R,$Z,$nd,$Fc){global$x;$G=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($nd&&($x=="sql"||count($Fc)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$Fc).")$G":"SELECT COUNT(*)".($nd?" FROM (SELECT 1$G GROUP BY ".implode(", ",$Fc).") x":$G));}function
slow_query($G){global$b,$pg,$n;$m=$b->database();$hg=$b->queryTimeout();$Ef=$n->slowQuery($G,$hg);if(!$Ef&&support("kill")&&is_object($i=connect())&&($m==""||$i->select_db($m))){$ud=$i->result(connection_id());echo'<script',nonce(),'>
var timeout = setTimeout(function () {
	ajax(\'',js_escape(ME),'script=kill\', function () {
	}, \'kill=',$ud,'&token=',$pg,'\');
}, ',1000*$hg,');
</script>
';}else$i=null;ob_flush();flush();$J=@get_key_vals(($Ef?$Ef:$G),$i,false);if($i){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$Ve=rand(1,1e6);return($Ve^$_SESSION["token"]).":$Ve";}function
verify_token(){list($pg,$Ve)=explode(":",$_POST["token"]);return($Ve^$_SESSION["token"])==$pg;}function
lzw_decompress($Ma){$Eb=256;$Na=8;$cb=array();$hf=0;$if=0;for($s=0;$s<strlen($Ma);$s++){$hf=($hf<<8)+ord($Ma[$s]);$if+=8;if($if>=$Na){$if-=$Na;$cb[]=$hf>>$if;$hf&=(1<<$if)-1;$Eb++;if($Eb>>$Na)$Na++;}}$Db=range("\0","\xFF");$J="";foreach($cb
as$s=>$bb){$Ob=$Db[$bb];if(!isset($Ob))$Ob=$dh.$dh[0];$J.=$Ob;if($s)$Db[]=$dh.$Ob[0];$dh=$Ob;}return$J;}function
on_help($hb,$Cf=0){return
script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $hb, $Cf) }, onmouseout: helpMouseout});","");}function
edit_form($a,$q,$K,$Ig){global$b,$x,$pg,$o;$Yf=$b->tableName(table_status1($a,true));page_header(($Ig?lang(10):lang(11)),$o,array("select"=>array($a,$Yf)),$Yf);if($K===false)echo"<p class='error'>".lang(12)."\n";echo'<form action="" method="post" enctype="multipart/form-data" id="form">
';if(!$q)echo"<p class='error'>".lang(13)."\n";else{echo"<table cellspacing='0' class='layout'>".script("qsl('table').onkeydown = editingKeydown;");foreach($q
as$B=>$p){echo"<tr><th>".$b->fieldName($p);$zb=$_GET["set"][bracket_escape($B)];if($zb===null){$zb=$p["default"];if($p["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$zb,$af))$zb=$af[1];}$Y=($K!==null?($K[$B]!=""&&$x=="sql"&&preg_match("~enum|set~",$p["type"])?(is_array($K[$B])?array_sum($K[$B]):+$K[$B]):$K[$B]):(!$Ig&&$p["auto_increment"]?"":(isset($_GET["select"])?false:$zb)));if(!$_POST["save"]&&is_string($Y))$Y=$b->editVal($Y,$p);$Dc=($_POST["save"]?(string)$_POST["function"][$B]:($Ig&&preg_match('~^CURRENT_TIMESTAMP~i',$p["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(preg_match("~time~",$p["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$Dc="now";}input($p,$Y,$Dc);echo"\n";}if(!support("table"))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select("field_funs[]",$b->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($q){echo"<input type='submit' value='".lang(14)."'>\n";if(!isset($_GET["select"])){echo"<input type='submit' name='insert' value='".($Ig?lang(15):lang(16))."' title='Ctrl+Shift+Enter'>\n",($Ig?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".lang(17)."â€¦', this); };"):"");}}echo($Ig?"<input type='submit' name='delete' value='".lang(18)."'>".confirm()."\n":($_POST||!$q?"":script("focus(qsa('td', qs('#form'))[1].firstChild);")));if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo'<input type="hidden" name="referer" value="',h(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),'">
<input type="hidden" name="save" value="1">
<input type="hidden" name="token" value="',$pg,'">
</form>
';}if(isset($_GET["file"])){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");if($_GET["file"]=="favicon.ico"){header("Content-Type: image/x-icon");echo
lzw_decompress("\0\0\0` \0„\0\n @\0´C„è\"\0`EãQ¸àÿ‡?ÀtvM'”JdÁd\\Œb0\0Ä\"™ÀfÓˆ¤îs5›ÏçÑAXPaJ“0„¥‘8„#RŠT©‘z`ˆ#.©ÇcíXÃşÈ€?À-\0¡Im? .«M¶€\0È¯(Ì‰ıÀ/(%Œ\0");}elseif($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("\n1Ì‡“ÙŒŞl7œ‡B1„4vb0˜Ífs‘¼ên2BÌÑ±Ù˜Şn:‡#(¼b.\rDc)ÈÈa7E„‘¤Âl¦Ã±”èi1Ìs˜´ç-4™‡fÓ	ÈÎi7†³é†„ŒFÃ©”vt2‚Ó!–r0Ïãã£t~½U'3M€ÉW„B¦'cÍPÂ:6T\rc£A¾zr_îWK¶\r-¼VNFS%~Ãc²Ùí&›\\^ÊrÀ›­æu‚ÅÃôÙ‹4'7k¶è¯ÂãQÔæhš'g\rFB\ryT7SS¥PĞ1=Ç¤cIèÊ:d”ºm>£S8L†Jœt.M¢Š	Ï‹`'C¡¼ÛĞ889¤È QØıŒî2#8Ğ­£’˜6mú²†ğjˆ¢h«<…Œ°«Œ9/ë˜ç:Jê)Ê‚¤\0d>!\0Z‡ˆvì»në¾ğ¼o(Úó¥ÉkÔ7½sàù>Œî†!ĞR\"*nSı\0@P\"Áè’(‹#[¶¥£@g¹oü­’znş9k¤8†nš™ª1´I*ˆô=Ín²¤ª¸è0«c(ö;¾Ã Ğè!°üë*cì÷>Î¬E7DñLJ© 1Èä·ã`Â8(áÕ3M¨ó\"Ç39é?Ee=Ò¬ü~ù¾²ôÅîÓ¸7;ÉCÄÁ›ÍE\rd!)Âa*¯5ajo\0ª#`Ê38¶\0Êí]“eŒêˆÆ2¤	mk×øe]…Á­AZsÕStZ•Z!)BR¨G+Î#Jv2(ã öîc…4<¸#sB¯0éú‚6YL\r²=£…¿[×73Æğ<Ô:£Šbx”ßJ=	m_ ¾ÏÅfªlÙ×t‹åIªƒHÚ3x*€›á6`t6¾Ã%UÔLòeÙ‚˜<´\0ÉAQ<P<:š#u/¤:T\\> Ë-…xJˆÍQH\nj¡L+jİzğó°7£•«`İğ³\nkƒƒ'“NÓvX>îC-TË©¶œ¸†4*L”%Cj>7ß¨ŠŞ¨­õ™`ù®œ;yØûÆqÁrÊ3#¨Ù} :#ní\rã½^Å=CåAÜ¸İÆs&8£K&»ô*0ÑÒtİSÉÔÅ=¾[×ó:\\]ÃEİŒ/Oà>^]ØÃ¸Â<èØ÷gZÔV†éqº³ŠŒù ñËx\\­è•ö¹ßŞº´„\"J \\Ã®ˆû##Á¡½D†Îx6êœÚ5xÊÜ€¸¶†¨\rHøl ‹ñø°bú r¼7áÔ6†àöj|Á‰ô¢Û–*ôFAquvyO’½WeM‹Ö÷‰D.Fáö:RĞ\$-¡Ş¶µT!ìDS`°8D˜~ŸàA`(Çemƒ¦òı¢T@O1@º†X¦â“\nLpğ–‘PäşÁÓÂm«yf¸£)	‰«ÂˆÚGSEI‰¥xC(s(a?\$`tE¨n„ñ±­,÷Õ \$a‹U>,èĞ’\$ZñkDm,G\0å \\iú£%Ê¹¢ n¬¥¥±·ìİÜgÉ„b	y`’òÔ†ËWì· ä——¡_CÀÄT\niÏH%ÕdaÀÖiÍ7íAt°,Á®J†X4nˆ‘”ˆ0oÍ¹»9g\nzm‹M%`É'Iü€Ğ-èò©Ğ7:pğ3pÇQ—rEDš¤×ì àb2]…PF ı¥É>eÉú†3j\n€ß°t!Á?4ftK;£Ê\rÎĞ¸­!àoŠu?ÓúPhÒ0uIC}'~ÅÈ2‡vşQ¨ÒÎ8)ìÀ†7ìDIù=§éy&•¢eaàs*hÉ•jlAÄ(ê›\"Ä\\Óêm^i‘®M)‚°^ƒ	|~Õl¨¶#!YÍf81RS Áµ!‡†è62PÆC‘ôl&íûäxd!Œ| è9°`Ö_OYí=ğÑGà[EÉ-eLñCvT¬ )Ä@j-5¨¶œpSg».’G=”ĞZEÒö\$\0¢Ñ†KjíU§µ\$ ‚ÀG'IäP©Â~ûÚğ ;ÚhNÛG%*áRjñ‰X[œXPf^Á±|æèT!µ*NğğĞ†¸\rU¢Œ^q1V!ÃùUz,ÃI|7°7†r,¾¡¬7”èŞÄ¾BÖùÈ;é+÷¨©ß•ˆAÚpÍÎ½Ç^€¡~Ø¼W!3PŠI8]“½vÓJ’Áfñq£|,êè9Wøf`\0áqˆAÖwE¬àçÕ´¦F‡‘ŠÙTî«QÕ‘GÎù\$0Ç“Ê #Ç%By7r¨i{eÍQÔŸòˆd„ìÇ‡ ÌB4;ks(å0İÁ=1r)_<¿”Ø;Ì¹çSŒÛr  &YÇ,h,®ŸiiÙƒÕÁbÉÌ¢A–é ¼åG±´L˜z2p(¦ÏÙõ”‰ƒ0À°Š›ÂL	¡¹SÅú¨¨EêÀ˜	<©ÄÇ}_#\\fª¨daÊ„çKå3¼Y|V+êl@²0`;ÅàËLhÅä±ÁŞ¯j'™›˜öàÆ™»Yâ+¶‰QZ-iôœyvƒ–I™5Ú“0O|½PÖ]FÜáòÓùñ\0üË2™D9Í¢™¤Án/Ï‡QØ³&¦ªI^®=Ól©qfIÆÊ= Ö]xqGRüF¦e¹7éº)Šó9*Æ:B²b±>a¦z‡-µ‰Ñ2.¯ö¬¸b{°ğ4#„¥¼òÄUá“ÆL7-¼Áv/;Ê5ñ’ôu©ÊöHå§&²#÷³¤jÖ`ÕG—8Î “7pùØğÒ YCÁĞ~ÁÈ:À@ÆŞEU‰JÜÛ;v7v]¶J'ØŞäq1ï·éElô™Ğ†i¾ÍÃÏ„/íÿ{k<àÖ¡MÜpoí}ğéÁ¤±•Ù,ìdÃ¦Ù_uÓ—ïÂpºuŞ½Åùúüú=»‘·tnş´™	ıŸ™~×Lxîøæ‹Ö{kàß‡åŞù\rj~·P+ÿç0ĞuòowÚyu\$Üèß·î\nd¥Ém´ZdÀ8i`¤=ûÛgğ<§˜ùÛ“ìáÍˆ*+3jŒ¦ÌüÜ<[Œ\0²®ÿ/PÍ­BÿÎr±„ö`Ë`½#xå+B?#öÜ^;Ob\r¨èù¯4øÏ\n÷Ìæ¿0\núô¿0\\×0>Pø@ú¯À2‚lÆÂjÒOªëŒÿ¨(_î<çW\$Ùgºø G­t×@ûl.‡hœSiÆ¾°¬PH\n¦Jëâ‹ëèLDà");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("f:›ŒgCI¼Ü\n8œÅ3)°Ë7œ…†81ĞÊx:\nOg#)Ğêr7\n\"†è´`ø|2ÌgSi–H)N¦S‘ä§\r‡\"0¹Ä@ä)Ÿ`(\$s6O!ÓèœV/=Œ' T4æ=„˜iS˜6IO“ÊerÙxî9*Åº°ºn3\rÑ‰vƒCÁ`õšİ2G%¨YãæáşŸ1™Ífô¹ÑÈ‚l¤Ã1‘\ny£*pC\r\$ÌnTª•3=\\‚r9O\"ã	Ààl<Š\rÇ\\€³I,—s\nA¤Æeh+Mâ‹!q0™ıf»`(¹N{c–—+wËñÁY£–pÙ§3Š3ú˜+I¦Ôj¹ºıÏk·²n¸qÜƒzi#^rØÀº´‹3èâÏ[èºo;®Ë(‹Ğ6#ÀÒ\":cz>ß£C2vÑCXÊ<P˜Ãc*5\nº¨è·/üP97ñ|F»°c0ƒ³¨°ä!ƒæ…!¨œƒ!‰Ã\nZ%ÃÄ‡#CHÌ!¨Òr8ç\$¥¡ì¯,ÈRÜ”2…Èã^0·á@¤2Œâ(ğ88P/‚à¸İ„á\\Á\$La\\å;càH„áHX„•\nÊƒtœ‡á8A<ÏsZô*ƒ;IĞÎ3¡Á@Ò2<Š¢¬!A8G<Ôj¿-Kƒ({*\r’Åa1‡¡èN4Tc\"\\Ò!=1^•ğİM9O³:†;jŒŠ\rãXÒàL#HÎ7ƒ#Tİª/-´‹£pÊ;B Â‹\n¿2!ƒ¥Ít]apÎİî\0RÛCËv¬MÂI,\rö§\0Hv°İ?kTŞ4£Š¼óuÙ±Ø;&’ò+&ƒ›ğ•µ\rÈXbu4İ¡i88Â2Bä/âƒ–4ƒ¡€N8AÜA)52íúøËåÎ2ˆ¨sã8ç“5¤¥¡pçWC@è:˜t…ã¾´Öešh\"#8_˜æcp^ãˆâI]OHşÔ:zdÈ3g£(„ˆ×Ã–k¸î“\\6´˜2ÚÚ–÷¹iÃä7²˜Ï]\rÃxO¾nºpè<¡ÁpïQ®UĞn‹ò|@çËó#G3ğÁ8bA¨Ê6ô2Ÿ67%#¸\\8\rıš2Èc\ræİŸk®‚.(’	’-—J;î›Ñó ÈéLãÏ ƒ¼Wâøã§“Ñ¥É¤â–÷·nû Ò§»æıMÎÀ9ZĞs]êz®¯¬ëy^[¯ì4-ºU\0ta ¶62^•˜.`¤‚â.Cßjÿ[á„ % Q\0`dëM8¿¦¼ËÛ\$O0`4²êÎ\n\0a\rA„<†@Ÿƒ›Š\r!À:ØBAŸ9Ù?h>¤Çº š~ÌŒ—6ÈˆhÜ=Ë-œA7XäÀÖ‡\\¼\r‘Q<èš§q’'!XÎ“2úT °!ŒD\r§Ò,K´\"ç%˜HÖqR\r„Ì ¢îC =í‚ æäÈ<c”\n#<€5Mø êEƒœyŒ¡”“‡°úo\"°cJKL2ù&£ØeRœÀWĞAÎTwÊÑ‘;åJˆâá\\`)5¦ÔŞœBòqhT3§àR	¸'\r+\":– Øğà.“ÑZM'|¬et:3%LÜË#Â‘f!ñhà×€eŒ³œÙ+Ä¼­Ná¹	Á½_’CXŠGî˜1†µi-Ã£z\$’oK@O@TÒ=&‰0\$	àDA‘›¥ùùDàªSJèx9×FÈˆml¨Èp»GÕ­¤T6RfÀ@ƒa¾\rs´RªFgih]¥éf™.•7+Ñ<nhh’* ÈSH	P]¡ :Ò’¨Áa\"¨Õù¬2¦&R©)ùB¦PÊ™ÓH/õf {r|¨0^ÙhCAÌ0»@æMÎâç2“B”@©âzªUŠ‘¾O÷ş‰Cpp’å\\¾L«%è¬ğ›„’y«çodÃ¥•‰´p3·Š7E¸—ĞÜA\\°ö†KƒÛXn‚Øi.ĞZ×Í óŸ˜s¡‰Gım^tIòY‘J’üÙ±•G1€£R¨³D’c–äà6•tMihÆä9ƒ»9gƒq—RL–ûMj-TQÍ6i«G_!í.½hªvŞûcN¨Œı¸—^üÑ0w@n|ı½×VûÜ«˜AĞ­ÃÀ3ú[Úû]	s7õG†P@ :Ì1Ñ‚ØbØ µìİŸ›’wÏ(i³ø:Òåz\\ûº;Óù´AéPU T^£]9İ`UX+U î‹Q+‰ÃbÌÀñ*Ï”s¨¼€–—Î[ßÛ‰xkûF*ô‚İ§_w.òÅ6~òbÛÎmKì¾sIŞMKÉ}ï•Ò¥ÚøåeHÉ²ˆdµ*mdçlœQ°eHô2½ÔL¨ aÒ‚¯=…³sëPøaM\"apÃÀ:<á…äGB”\r2Ytx&L}}‘ßAÏÔ±N…GĞ¬za”öD4øtÔ4QÉvS©Ã¹S\rÎ;U¸ê¦éäı¸´Æ~’pBğƒ{¶ÑÆ,œ—¢O´ãt;ÇJ¡™ZC,&Yº:Y\"İ#‰ÜãÄt:\n‘h8r¯¡îÚnéÔÈh>„>Zğø`&àaŞpY+¹x¬UÕıA¼<?ã”PxWÕ¡¯W™	i¬Ë.É\r`÷\$,Àú©Ò¾‹³V¥]ŒZr›ä§H³ˆ5Æf\\º-KÆ©¦v¼•Zçä®A¸Õ(§{3­o›ó¿¡l.¿ì¹JéÅ.ç\\t2æ;¯ì2\0´Í>c+|ÁĞ*;-0înÂà[t@ÛÚ•ò¢¤=cQ\n.z‰•ÉwC&‡Ô@‘ù¦FæÕˆ‡'cBS7_*rsÑ¨Ô?jğ3@–ˆôĞ!ğ.@7sŠ]ÓªòL÷ÎGŸğ@ÿÕ_­qÕ&uûØótª\nÕ´LßEĞT¤ğ­}gG–ş¸îwëoö(*˜ªğ†›Aí¯-¥Åù¢Õ3¿mk¾…÷°¶×¤«Ÿt·¢Sø¥Á(ûd±Aî~ïx\n×õô§kÕÏ£:DŸø+Ÿ‘ gãäh14 Öâ\n.øÏdê«–ãì’ öşéAlYÂ©jš©êjJœÇÅPN+b D°j¼¬€îÔ€DªŞPäì€LQ`Of–£@Ø}(ÅÂ6^nB³4Û`ÜeÀ\n€š	…trp!lV¤'}b‰*€r%|\nr\r#°Ä@w®¼-ÔT.Vvâ8ìªæ\nmF¦/Èp¬Ï`úY0¬Ïâë­è€P\r8ÀY\r‡Øİ¤’	ÀQ‡%EÎ/@]\0ÊÀ{@ÌQØá\0bR M\r†Ù'|¢è%0SDr¨È f/–àÂÜb:Ü­¯¶ŞÃÂ%ß€æ3H¦x\0Âl\0ÌÅÚ	‘€Wàß%Ú\nç8\r\0}îD„É1d#±x‚ä.€jEoHrÇ¢lbÀØÚ%tì¦4¸p„Àä%Ñ4’åÒk®z2\rñ£`îW@Â’ç%\rJ‚1€‚X ¤Ú1¾D6!°ô†*‡ä²{4<E¦‹k.më4Äò×€\r\nê^iÀ è³!n«²!2\$§ÈüÌ÷(îfñöÄìÄùk>ï¢ÅËNú‚5\$Œàé2T¾,ÖLÄ‚¬ ¶ Z@ºí*Ğ`^PğP%5%ªt‘HâWÀğonüö«E#föÒ<Ú2@K:Ìošùò’ÌÏ¦Í-èû2\\Wi+f›&Ñòg&²níLõ'eÒ|‚²´¿nK¥2ûrÚ¶Ëpá*.ánü²’Î¦‰‚‚*Ğ+ªtBg* òQ…1+)1hªŠî^‹`Q#ñØân*hòàòv¢Bãñ\0\\F\n†WÅr f\$ó=4\$G4ed b˜:J^!“0€‰_àû¦%2ÀË6³.F€ÑèÒºóEQÁ±‚²Îdts\"×„‘’B(`Ú\rÀš®c€R©°°ñV®²”óºXêâ:RŸ*2E*sÃ\$¬Ï+Á:bXlÌØtb‹á-ÄÂ›S>’ù-åd¢=äò\$Sø\$å2ÀÊ7“jº\"[Ì\"€È] [6“€SE_>åq.\$@z`í;ô4²3Ê¼ÅCSÕ*ïª[ÀÒÀ{DO´ŞªCJjå³šPò:'€èÈ• QEÓ–æ`%rñ¯û7¯şG+hW4E*ÀĞ#TuFj•\n¾eùDô^æsš§r.ì‰ÅRkæ€z@¶@»…³Dâ`CÂV!Cæå•\0ñØÛŠ)3<Q4@Ù3SP‡âZB³5F€Lä¨~G³5ÈÒ:ñÂÓ5\$XÑÔö}ÆfŠËâI€ó3S8ñ\0XÔ‚td³<\nbtNç Q¢;\rÜÑH‚ÕP\0Ô¯&\n‚à\$VÒ\r:Ò\0]V5gV¦„òD`‡N1:ÓSS4Q…4³N•5u“5Ó`x	Ò<5_FHÜßõ}7­û)€SVíÌÄ#ê|‚Õ< Õ¼ÑË°£ ·\\ İ-Êz2³\0ü#¡WJU6kv·µÎ#µÒ\rµì·¤§ÀûUõöiÕï_îõ^‚UVJ|Y.¨É›\0u,€òğôæ°õ_UQD#µZJuƒXtñµ_ï&JO,Du`N\r5³Á`«}ZQM^mÌPìG[±Áa»bàNä® ÖreÚ\n€Ò%¤4š“o_(ñ^¶q@Y6t;I\nGSM£3§×^SAYH hB±5 fN?NjWU•JĞÂøÖ¯YÖ³ke\"\\B1Ø…0º µenĞÄí*<¥O`S’L—\n‘Ú.gÍ5Zj¡\0R\$åh÷n÷[¶\\İíñrŒÊ,æ4ğœ° cP§pq@Rµrw>‹wCK‘…t¶ }5_uvh¤Ó`/Àúà\$ò–J)ÏRõ2Du73Öd\rÂ;­çw´İöHùI_\"4±rµ«®¦Ï¿+ê¿&0>É_-eqeDöÍVÔnŒÄf‹hüÂ\"ZÀ¨¶óZ¢WÌ6\\Lî¶·ê÷î·ke&ã~‡ààš…‘i\$Ï°´Mr×i*×ÄâÔç\0Ì.Q,¶¢8\r±È¸\$×­K‚ÈYƒ ĞioÍe%tÕ2ÿ\0äJıø~×ñ/I/.…e€€n«~x!€8´À|f¸hÛ„-H×åÏ&˜/„Æo‡­‡ø‚.K” Ë^jÜÀtµé>('L\r€àHsK1´e¤\0Ÿ\$&3²\0æin3í¨ oä“6ôĞ¶ø®÷ô§9j°¸àÈÚ1‰(b.”vC İ8ŒÙ:wi¬Ÿ\"®^wµQ©¥Åïz–o~Ş/„úÒ’÷–÷`Y2”D¬VúÆ³/kã8³¹7ZHø°Šƒ]2k2rœ¿ñ›ŠÏ¯h©=ˆT…ˆ]O&§\0ÄM\0Ö[8–‡È®…æ–â8&LÚVm vÀ±ê˜j„×šÇFåÄ\\™¶	™º¾&så€Q› \\\"òb€°	àÄ\rBsœIw	YéÂN š7ÇC/*ÙË ¨\n\nÃH™[«š¹Ô*A˜ ñTEÏVP.UZ(tz/}\n2‚çyšS¢š,#É3âi°~W@yCC\nKT¿š1\"@|„zC\$ü€_CZjzHBºLVÔ,Kº£º„O—ÁÀPà@X…´…°‰¨ºƒ;DúWZšW¥aÙÀ\0ŞŠÂCG8–R  	à¦\n…„àºĞPÆA£è&šº é,ÚpfV|@N¨b¾\$€[‡I’Š­™âàğ¦´àZ¥@Zd\\\"…|¢ƒ+¢Û®šìtzğo\$â\0[²èŞ±yƒE çë³É™®bhU1£‚,€r\$ãŒo8D§²‡F«ÆV&Ú5 h}ÂNÜÍ³&ºçµ•ef€Ç™Y™¸:»^z©VPu	W¹Z\"rÚ:ûhw˜µh#1¥´O¥äÃKâhq`å¦„óÄ§v| Ë§:wDúj…(W¢ºº­¨›ï¤»õ?;|Z—«%Š%Ú¡Är@[†ŠúÄB»&™»³˜›ú#ª˜©Ù£”:)ÂàY6û²–è&¹Ü	@¦	àœüIÄÒ!›©²»¶ Â»â2M„äO;²«ÑWÆ¼)êùCãÊFZâp!ÂÄa™Ä*FÄb¹I³ÃÍ¾àŒ¤#Ä¤9¡¦åçS©/SüA‰`zé•L*Î8»+¨ÌNù‹Ä-¸M•Ä-kd°®àLiÎJë‚Â·şJnÂÃbí Ó>,ÜV¶SP¯8´è>¶wïì\"E.îƒRz`Ş‹u_ÀèœôE\\ùÏÉ«Ğ3Pç¬óÓ¥s]”•‰goVSƒ±ñ„\n ¤	*†\r»¸7)ªÊ„ümPWİUÕ€ßÕÇ°¨·Şf”×Ü“iÿÆ…kĞŒ\rÄ('W`ŞBdã/h*†AÌlºMä€_\nÀèüú½µëOªäT‚5Ú&AÀ2Ã©`¸à\\RÑE\"_–_œ½.7¥Mœ6d;¶<?ÈÜ)(;¾û‰}K¸[«Åû»ÆZ?ÕyI ÷á1pªbu\0èéˆ²²Œ£{ó£Å\ri„sÉQQ¦Y§2ª…\r×”0\0XØ\"@qÍuMböÓuJ6ÉNGÖş–^ÓÔwF/t’õ°#P¾p÷Í!7Øı­…å›œ!Ã»é^Vü„M–!(â©€8ÖÍ=¥\0å¥@˜¿í80N¬Sà½¾°QĞ_TÏàÄ¥şqSz\"Õ&hã\0R.\0hZÓfx‡ ÜF9¶Q(Ób³=ÄD&xs=X›bu@oÎwƒd“5ñÇİP1P>k¸ŠHöD6/Ú¿íqë¼¾Î3¥7TĞ¬KÈ~54°	ñt#µM–\rctx‹gçT˜æX\r‚2\$í<0øy}*ßÿCbiÆ^ó†±ÄL‡7	bäoùŒÓÊx71 b€XS`OÀàá­0)ù¨Ú\"®/†•=È¬ ¸lÊá˜QöpÍ-˜!ıà{ıõ€±©–Öâa„ÃÈ•9bAg¶2,1zf£kàÈj„h/o(’.4‰\rıƒàTz&nw¶”Ä7 X!ğûŸª@,»<—	“ı`\"@:†¼7ÃCX\\	 \$1H\n=Ä›¡O5Œ°&ºv*(	àtHÑ#É\nê_X/8•k~+t€—O&<v‰Í_Yh‚€.ØMe€HxpáI¨a‡ù0ÕM\nhø`r'B…¥ÃhÓn8qÑ‡!	åÖ eu»«]^TW­Š‘Öd9{û¾H,ã—‚8ÅüL­a«,!\0;ÆîB#É#ÁÒ`ò)³¯Ÿ™–	Å„aèEeòÚ‘Ü/MèPÓ	“l„ğÉa`	¥sâ²…<(D\nöá¡À9{06œÆˆ;A8¶¸5!	 ÍÀZ[Tâ© hV… »Ü»Åé¯U@än`ÆVp¥h(Rb4ÆVôÆ‰¼¸ÒÈRp€¢Ò”\$ª™ĞŞD3O¡¾õÔ\$€öÃÓaQ²¯0xbŒH` ®ĞâLÃ”8i¾èoC‹½àúğ#6”xÊ)XHĞ!`÷íÀô‹ÆÔBÖ%wÑÂÇo\nxÌ€h®ÁH‹»ˆr¦ Ê¼cóœÀmJHáLUğÜäÆe1l`ü(Õ\$\"¾h†JÒrvØíÓTPÁĞØ·ó1uï¢‡HA\0èèH2@(Ê¡Uà\"©Q@qg]l\"¨%©ú*«\0WŠj[ †·eÃ4êõÆPúÂN”‚àê5\$H\r¼îIP„'@:\0è\"#t^†D­0Åè“å«>ƒ(œ’h· 'œ¼F,sZJôèµAn¯#‰h ªX³—.q‹YobÚˆ·Ò2¨Ş?j¼€B÷I–ôß£€›¥ÖÛôù0†aû(ñ`ZñCÁà¯rššHSQîÆ\\‚‡W	¼€XZ÷Í|¹E@âÂTÔÅ–qğ DD:_yÕ¯Ä°±©B~ßxP±--e‚‡_äu‹|2(³G,Æåˆ-rR KxîÕ d¡ÃhHìA|ôŒw„|PÁ!Ç‰Ò‘ä¬}ÜTùÇÖ<Ñù,1ÑÕvêg*Ù¤ïz¯^€«÷¤œñ_pi {€ØGÕíİÿ	LaJJC–T%N1‡ÒI:V@ZÔÁ%É‚*Ô|@NNxLL€zd \$8b#Û!2=cÛ±QDŠí@½\0±Jàdzpû¯\$Aî|ya4)¤”s%!ğ¥BI’Q]d˜G´6&E\$˜…H\$Rj\0œ‡·Ü—Gi\$Ø¥â9Å†YúĞ@Ê´0ñ6Ä¦‘ºXÒÜ1&L•ç&2Ì	E^äa8öj¦#¸DEu€\$uTÌ*R¥#&ˆ‚P2•e¥äKƒ«'šE%â”¡’YWáJ•ôŒ	”©ö™O`ƒÊ•·€^l+¦„`¨	R¹1uƒ&F˜¸¥Z[)]J¬ZÃE•Ñ`±¶FN.\r•=ÀØ  ³\0´O~‰ÒÅM,«…FATÌb™hèz0‰`-bl‹\nñÇ…Z '—*I†n°\$â[’,8D‡Ÿn«¨`°˜ÒóI0uÑ0åŞEJé¸†Xceì2P‡‚ bûÀ]èõÌ5:ê²“º'xT	‰'bOYº‘V>&·–AÏ.PpûÅ­\${)9\"iˆcª–úÇ™•L¡ P”K½Tğ¸9ÁÕ×0wZ\"b	”)à©ÃR û&„É¢ÁºÍ&ÉX+™’ºs%[¤~&aF•Íi.:„Ka5@§­øqÈÎÉpGª˜hlÍn³0yÛH,W>ÑJ®!™‘®&¥2Y–±ŞlAp™ˆ¯-3§]ˆ¨±2CŒMZ–î’ŠøH¯oÚd1Dl±uS\"´ºMµTz\$h\\c²Øòæw<ÅcO3?zËÍàp%@\0…4\nìZèÓ—„§¥f*\r÷“°|ºÙ„;3âMÈRm¯º ™w¦X·¹Ï.YL°›óª]Wg]¹ş\rèƒœ1@U8•¤e3U›ÛŸ–Dê	zÀ'ˆ¸‡&½#huàa1CÂ0‘{phÍ”\n?Ğë¤YKÔB™ˆìYÜÁA9©,´F ¨wĞ");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress('');}else{header("Content-Type: image/gif");switch($_GET["file"]){case"plus.gif":echo'';break;case"cross.gif":echo'';break;case"up.gif":echo'';break;case"down.gif":echo'';break;case"arrow.gif":echo'';break;}}exit;}if($_GET["script"]=="version"){$Bc=file_open_lock(get_temp_dir()."/adminer.version");if($Bc)file_write_unlock($Bc,serialize(array("signature"=>$_POST["signature"],"version"=>$_POST["version"])));exit;}global$b,$h,$n,$Hb,$Mb,$Ub,$o,$Ec,$Ic,$aa,$hd,$x,$ba,$yd,$ke,$Fe,$Rf,$Mc,$pg,$tg,$Ag,$Hg,$ca;if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if($_SERVER["HTTP_X_FORWARDED_PREFIX"])$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];$aa=($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure");@ini_set("session.use_trans_sid",false);if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");$E=array(0,preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),"",$aa);if(version_compare(PHP_VERSION,'5.2.0')>=0)$E[]=true;call_user_func_array('session_set_cookie_params',$E);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$oc);if(get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("zend.ze1_compatibility_mode",false);@ini_set("precision",15);$yd=array('en'=>'English','ar'=>'Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©','bg'=>'Ğ‘ÑŠĞ»Ğ³Ğ°Ñ€ÑĞºĞ¸','bn'=>'à¦¬à¦¾à¦‚à¦²à¦¾','bs'=>'Bosanski','ca'=>'CatalÃ ','cs'=>'ÄŒeÅ¡tina','da'=>'Dansk','de'=>'Deutsch','el'=>'Î•Î»Î»Î·Î½Î¹ÎºÎ¬','es'=>'EspaÃ±ol','et'=>'Eesti','fa'=>'ÙØ§Ø±Ø³ÛŒ','fi'=>'Suomi','fr'=>'FranÃ§ais','gl'=>'Galego','he'=>'×¢×‘×¨×™×ª','hu'=>'Magyar','id'=>'Bahasa Indonesia','it'=>'Italiano','ja'=>'æ—¥æœ¬èª','ka'=>'áƒ¥áƒáƒ áƒ—áƒ£áƒšáƒ˜','ko'=>'í•œêµ­ì–´','lt'=>'LietuviÅ³','ms'=>'Bahasa Melayu','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'PortuguÃªs','pt-br'=>'PortuguÃªs (Brazil)','ro'=>'Limba RomÃ¢nÄƒ','ru'=>'Ğ ÑƒÑÑĞºĞ¸Ğ¹','sk'=>'SlovenÄina','sl'=>'Slovenski','sr'=>'Ğ¡Ñ€Ğ¿ÑĞºĞ¸','ta'=>'à®¤â€Œà®®à®¿à®´à¯','th'=>'à¸ à¸²à¸©à¸²à¹„à¸—à¸¢','tr'=>'TÃ¼rkÃ§e','uk'=>'Ğ£ĞºÑ€Ğ°Ñ—Ğ½ÑÑŒĞºĞ°','vi'=>'Tiáº¿ng Viá»‡t','zh'=>'ç®€ä½“ä¸­æ–‡','zh-tw'=>'ç¹é«”ä¸­æ–‡',);function
get_lang(){global$ba;return$ba;}function
lang($u,$ge=null){if(is_string($u)){$Ie=array_search($u,get_translations("en"));if($Ie!==false)$u=$Ie;}global$ba,$tg;$sg=($tg[$u]?$tg[$u]:$u);if(is_array($sg)){$Ie=($ge==1?0:($ba=='cs'||$ba=='sk'?($ge&&$ge<5?1:2):($ba=='fr'?(!$ge?0:1):($ba=='pl'?($ge%10>1&&$ge%10<5&&$ge/10%10!=1?1:2):($ba=='sl'?($ge%100==1?0:($ge%100==2?1:($ge%100==3||$ge%100==4?2:3))):($ba=='lt'?($ge%10==1&&$ge%100!=11?0:($ge%10>1&&$ge/10%10!=1?1:2)):($ba=='bs'||$ba=='ru'||$ba=='sr'||$ba=='uk'?($ge%10==1&&$ge%100!=11?0:($ge%10>1&&$ge%10<5&&$ge/10%10!=1?1:2)):1)))))));$sg=$sg[$Ie];}$ya=func_get_args();array_shift($ya);$_c=str_replace("%d","%s",$sg);if($_c!=$sg)$ya[0]=format_number($ge);return
vsprintf($_c,$ya);}function
switch_lang(){global$ba,$yd;echo"<form action='' method='post'>\n<div id='lang'>",lang(19).": ".html_select("lang",$yd,$ba,"this.form.submit();")," <input type='submit' value='".lang(20)."' class='hidden'>\n","<input type='hidden' name='token' value='".get_token()."'>\n";echo"</div>\n</form>\n";}if(isset($_POST["lang"])&&verify_token()){cookie("adminer_lang",$_POST["lang"]);$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=array();redirect(remove_from_uri());}$ba="en";if(isset($yd[$_COOKIE["adminer_lang"]])){cookie("adminer_lang",$_COOKIE["adminer_lang"]);$ba=$_COOKIE["adminer_lang"];}elseif(isset($yd[$_SESSION["lang"]]))$ba=$_SESSION["lang"];else{$qa=array();preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$Md,PREG_SET_ORDER);foreach($Md
as$A)$qa[$A[1]]=(isset($A[3])?$A[3]:1);arsort($qa);foreach($qa
as$y=>$Se){if(isset($yd[$y])){$ba=$y;break;}$y=preg_replace('~-.*~','',$y);if(!isset($qa[$y])&&isset($yd[$y])){$ba=$y;break;}}}$tg=$_SESSION["translations"];if($_SESSION["translations_version"]!=633854138){$tg=array();$_SESSION["translations_version"]=633854138;}function
get_translations($xd){switch($xd){case"en":$g="A9D“yÔ@s:ÀGà¡(¸ffƒ‚Š¦ã	ˆÙ:ÄS°Şa2\"1¦..L'ƒI´êm‘#Çs,†KƒšOP#IÌ@%9¥i4Èo2ÏÆó €Ë,9%ÀPÀb2£a¸àr\n2›NCÈ(Şr4™Í1C`(:Ebç9AÈi:‰&ã™”åy·ˆFó½ĞY‚ˆ\r´\n– 8ZÔS=\$Aœ†¤`Ñ=ËÜŒ²‚0Ê\nÒãdFé	ŒŞn:ZÎ°)­ãQ¦ÕÈmwÛø€İO¼êmfpQËÎ‚‰†qœêaÊÄ¯±#q®–w7SX3” ‰œŠ˜o¢\n>Z—M„ziÃÄs;ÙÌ’‚„_Å:øõğ#|@è46ƒÃ:¾\r-z| (j*œ¨Œ0¦:-hæé/Ì¸ò8)+r^1/Ğ›¾Î·,ºZÓˆKXÂ9,¢pÊ:>#Öã(Ş6ÅqCŠ´Iú|³©È¢,(y ¸,	%b{K²ğ³Âƒ’)BƒßŒPŞµ\rÒªü6¹’2šK‹pÊ;„ÂÂ†\$#òÎ!,Û7Ã#Ì2¥A b„œøµ,N1\0S˜<ğÔ=RZÛ#b×Ğ(½%&…³LÌÚÔ£Ô2Òâè¸Ğ‘£a	Šr4³9)ÔÈÂ“1OAHÈ<ÄN)ËY\$ÉóÕWÊúØ%¸\$	Ğš&‡B˜¦cÍ¬<‹´ÈÚŒ’Í[K)¬Úâ\rƒÄÄïàÌ3\r‹[G@Lhå-è*ò*\rèÔÀ7(Úø:Œc’9ŒÃ¨ØLØXËÅ	ÏY»+Z~­“;^_Õ!‚àøJù…Âë¡ˆM.ÍaŠÃ«:Ã/c°Ãv¤\"¦)Ì¸Ş5ÈÁpAVµŒ¼\0’,é†NµÉ2İìƒàç‚`É@¨Åº©ğÍ?.@ Œ™bı…µĞ É\n‡‰Ğ€ŒÁèD4Tã€æáxï¹…É¼î¬ã8_#ê:)IÁxDoÌã†ã|Ò`p+²§J2ahíñäXv ”%JŒ*÷iòÄÈòyöÊÅmVØ:mÛ†åºn×vğ9o[ä‹#ğ!€	+/UœG£ş7¤,ÄÁM/l¿0ÙŸÇiSÙâ¿*l9´O˜© C\r%êé6íÃ–®9F‰Â33£–™iù-â_+ÿ¡ C˜Â\0criˆ4³3`]¸sqÅ¸ı¤#üĞÏIë/äÔ\0ZH‚€\nI\$LÈ“\"PÍy¿|g5\$e ¤A©¤¥ÂbLÉ©(f,Ì4šØl (l0Ï‚FÏse/ñ”‡\\d¹ò\n\$4¨GŠZ[b·3Ä1†ò»ÉQ,%ÄÁ–†Ô8üì70P’œpÈOŠ{&°æ\nŸcÆZà—HÀˆB]É	çWMÿšM™Q\$Šy­µ×dÿÈc#ÇB“ãÜeZ’ùVå¦\n„ğ¦!àgÁHš°·(KŠB~Qà€Ÿx˜[	%9ÁÉÃóÈä•!±1¤ ÄhºvHá\$ŠM†v~Ba\$AFL‘„`©aº,O\\¼H´f®—‰~‡Ft˜²|±O!ÇEpä½MÅk7Ã*¹#‹Šr™¸fôZW&ì¿×¼¬TİVÓ÷†isUëÍ,+ÜOÅ×?ŞğCB°É(ªÒÙ\$lÈ68Z^@iËô¼ ’pK¤SÛbAT*`Zé®.ç4”+š'Œà%°Ê€Rã	A\$éb3N	½â&LÁ}\\0¸£òSFhØ\nlaÍÃ†¥ l–¥ğ°ròOµLe®ˆ<ª¿d¨ ¢†Í¤Ì:1—aKD‚§¥c£TŠˆ\n\ná 7“‚B*l0F€ÒõY8”‘5Aôš!‘z§‡A(­Zb]E.o|ÑU\no^¤A~_¥=R2è(ÂZ¼Vd´k8ìñ!´\0¸";break;case"ar":$g="ÙC¶P‚Â²†l*„\r”,&\nÙA¶í„ø(J.™„0Se\\¶\r…ŒbÙ@¶0´,\nQ,l)ÅÀ¦Âµ°¬†Aòéj_1CĞM…«e€¢S™\ng@ŸOgë¨ô’XÙDMë)˜°0Œ†cA¨Øn8Çe*y#au4¡ ´Ir*;rSÁUµdJ	}‰ÎÑ*zªU@¦ŠX;ai1l(nóÕòıÃ[Óy™dŞu'c(€ÜoF“±¤Øe3™Nb¦ êp2NšS¡ Ó³:LZúz¶PØ\\bæ¼uÄ.•[¶Q`u	!Š­Jyµˆ&2¶(gTÍÔSÑšMÆxì5g5¸K®K¦Â¦àØ÷á—0Ê€(ª7\rm8î7(ä9\rã’f\"7NÂ9´£ ŞÙ4Ãxè¶ã„xæ;Á#\"¸¿…Š´¥2É°W\"J\nî¦¬Bê'hkÀÅ«b¦Diâ\\@ªêÊp¬•êyf ­’9ÊÚV¨?‘TXW¡‰¡FÃÇ{â¹3)\"ªW9Ï|Á¨eRhU±¬Òªû1ÆÁPˆ>¨ê„\"o{ì\"7î^¥¶pL\n7OM*˜OÔÊ<7cpæ4ôRflN°SJ²‚›DÅ‹ï#Åô³ğ‡Jrª >³J­ÓHsŞœ:îšã•ÊÃ°ÃUlK¦‰Ö,nÈR„*hı¡ª¬›ºÈ’,2 …B€ÌÃËd4àPH…á gj†)¥”›kR<ñ‘Jº\"É£\r/hùPš&ÒÓ¨RØ‘3ÂûÅ—K!TÕöRN•ó°Æ'ÈÏYI«²ƒËx:²[IÏl~È!U9H>ª}á=ëÌœßën2)vF<ê1êäQa@	¢ht)Š`PÔ5ãhÚ‹ct0‹µúÚ[_Óz?rb\0Pä:\r€S<Ğ#“J7ŒÃ0Øõ¹4VÈJ•õT­U·ôX“È@P¨7µhÂ7!\0ëE£ÆÙc0ê6`Ş3½C˜XÚ[HÂ3Œ/PAÁ¶@Úõ­ØP9…*zN†–A\0†)ŠB2ª#é*ˆê¡uL†ÄÒaŒ*ô’„¿‹dLT¦Z	+ëê3Všæ@êv2’Æ¯«g;±4Pf3OÃ­õ„ÉÃĞ6ö1Ñ´XÉàÂĞéÃ0z\r è8aĞ^ÿ(\\0ùz¤ŒáxÊ7ğ•\0:QŸ€D~MÓÔ3‡xÂ˜±˜ %‚ä†ÀŒ*â…’–À]zX/J}V;u^®„&a5›°äjPò K!C‡\0ÒÓ‘Õzá¡ì½·º÷ßã|¡İó¾—šúÃ“í}ï¹D>õ£Ax\")€ø\$†ĞàkÃkïõÿ§ÀAMèoYÖ'†ÖiCJ@Í™æ†àèSÏÙ>:âÊ’“ö×Lh¸EĞ ±’LÂ h4¡„1¿E¹®nˆÒ‡ñ”IY„3Aä0Û›ƒrnÙ¼ HòoM¤r#a†\$‚\0Ç\n\"Pi!°9£ÔlKñn*¤•…\"uŒ¿Ïâ7k@Ô†€àçÑiû0d5ÖJ'2e1d (!¨×›_Ù´5¨Öã`VBhæÌß¡T/&Û°w™=¡—ôWN^QE„«É´»(,7®ĞëophŞÅ€ÜL h}Çƒ|ÃDO\r!î‚ù\$M(c/ª—v(¿Hs½+bÊBBDèB¯ Å¢”särÔŠºsÒŒ­#5^Iã4O„Ì\$‘ ògÁ\0d\r+ Ó¡t ¢™¾6m88‡Sd…Ã2\r¯&=IÚvBFf¡Ób£ÈlaVD6l\0Â¡\"3U×,CFÔŠYEíHÆ\"ˆA	±8S¨’¤’ég+yÏ«+\$l\"ª[%ÑR'^ZDâP·Ñ]o-³aÒ…\0í( HÛ·gĞ¸bŸ`€)…˜4Æµë„`¨,E0pq\r¸:qNˆÚ4ˆ)©Òl,	ú¯XlX»5:~´(6­aZÛJŒšò&ëúÚHÀ‹h³M6²XPò‚ëÜp¤Ì!‡@ä]A\r!Œ5¨DõÃ´›¦Ó\n'Å Ò›LY&aÉ×Œ…çû‚\n¡P#ĞpŸŞ…*=v¸)õín—ë… ‡¼[×²:³9#sE±z×¬PLA6ï^75ëiæ%I!Š#¥Ì–²›\$Wt²…¸ GU**ĞDBÒæ‘M\\Ïä»	¾Ã†Ô„Ph\nÆo¡ı“BŠJg\n[.jÅÌ§GËíÈ\\xq8ß¢ç	!ç¦¾:Ì„˜§ÅvXX—(¢—Q‘k‚ÚF\"®×…^`\nº&˜'5D¯”M°Ä¶É€ ";break;case"bg":$g="ĞP´\r›EÑ@4°!Awh Z(&‚Ô~\n‹†faÌĞNÅ`Ñ‚şDˆ…4ĞÕü\"Ğ]4\r;Ae2”­a°µ€¢„œ.aÂèúrpº’@×“ˆ|.W.X4òå«FPµ”Ìâ“Ø\$ªhRàsÉÜÊ}@¨Ğ—pÙĞ”æB¢4”sE²Î¢7fŠ&EŠ, Ói•X\nFC1 Ôl7còØMEo)_G×ÒèÎ_<‡GÓ­}†Íœ,kë†ŠqPX”}F³+9¤¬7i†£Zè´šiíQ¡³_a·–—ZŠË*¨n^¹ÉÕS¦Ü9¾ÿ£YŸVÚ¨~³]ĞX\\Ró‰6±õÔ}±jâ}	¬lê4v±ø=ˆè†3	´\0ù@D|ÜÂ¤‰³[€’ª’^]#ğs.Õ3d\0*ÃXÜ7ãp@2CŞ9(‚ Â:#Â9Œ¡\0È7Œ£˜Aˆèê8\\z8Fc˜ïŒ‹ŠŒä—m XúÂÉ4™;¦rÔ'HS†˜¹2Ë6A>éÂ¦”6Ëÿ5	êÜ¸®kJ¾®&êªj½\"Kºüª°Ùß9‰{.äÎ-Ê^ä:‰*U?Š+*>SÁ3z>J&SKêŸ&©›ŞhR‰»’Ö&³:ŠãÉ’>I¬J–ªLãHƒHç‘ªÜEq8İZVÑÕs[Œ£Àè2Ã˜Ò7Ø«ŠùÎ­j¶/ËhŠC¨ù?CÕ´KTÖQ	¡k¦hL¦X7&‰\n¯=¨ÕpƒK*Âi¼Y-Šú±UËD02!­RÒ‰!-ùE_êÚ>ı#ğ˜H…¡ g††¨ùD¾	\"±x´\$Ò©SŠ£è:Úºw£Ğ8°JóÊn¸6ú¼˜²Ğ–@\"…£hÂ4ˆù<‰âKŸkB9¢i3Yğl¢¨/©Ä'%”Š–•ÑJª¯(2ì¥+n©ÁvÙ%úÒ\\Ë4ü’éšã^bÊíÈhR8th(Äææ€” P¶®³Ûèº´Åç\0Ùİ9““šJšs¾²cîõD6ƒ•˜'ÓÌ¼ŸÍò²ebÚïiJÎäğ¤ûÄ!øºT†©nÓ=ª8	“jÉKì¯>h§në!¬FãÉıÅ Ë‹÷ÊŞÎ8A¨4ËF­ëÖÿN¦i§Z¯uëÏeCv³:ä÷0'xí÷å§ƒğxx+¾“xé'Såy‹´ƒ÷Sê±*¼¸ş.ŸL‰ú\\ÊI˜‰!¤Å&ˆâhˆj×|ğ¦’%¥Û;Z:\n°è¹„:nÓÚMåAïšƒ´§‹Š5hXïAF¨^ˆ;³\$æ`¢@ĞQ\n:Êä:¬`ÜÁ\0A´4†äP“ÃÈXÁ‘\0xA\0hA”3ĞD tÌğ^âÀ.0î¢€\\ŠÃ8/X€½«°è±Ãp/@ú2¢øzÁà/ ùeôÊÙlk	`Şñ`İa^ƒO›¾ò§ÖÂ„\nÌ“rx¥—[ÿMîô‘'8ôNP[ù6dòDx“âlOŠ1N*Åx³áä>E‘‚1F•‚°Ö,hE¸:ªUÄqo-hLğ«f?DøG–SätÙ{ÅPÆm<™³JBäâÖ]„ÅJÑ£ÉàbÅ\$ˆ-“†jé\r˜pTÛ•3ğUšAéP¹†ÇÖ@ ìğ6\0ÄC‚(†aÈ6†UøC2ÃHø1†4rÃ0u¡°7†x{<ƒHt\r\0‚‰#Ug?A\0c‰q7FpÂ™Ì8g=0™iĞ™]S04H\\¯9tÁ2Øø´sª”ï-Ã¢@P¤¨=ş¨”Îi\0(-À¤¼És@yZ;]A\rdCéßè oÀ9ìÏ(gŸÕM!#¥dÃz=£ô0;Ïçí&T¶j)|ã-¸ø“PQ1vçè”¦xZB5«ñ 9¤J(}°¸8T†‘R8r_Ü4ÇDáàgŠÙØ Æa¨eIFÑ§©Oæ)ã’Bæµ¯i –ZÓ÷@E]ÍÔ–]DzO0iÄÕ´x([i	õØAW,ÖA•6¦¬ğ¦'Ä¥Wy½+–¤©C˜¹*	Ö5\nz\\@ôÒ…±{[.(óT2Òîj»(*å½2¢ûÊtuÎ­Æ×S^CI\nìz–İïµVÄx/lĞxidR|í¥HNo[˜8\$a Ñ¾i§¢q %RÒ|(¥yRßGtˆIhÄÖ›%—_l \naD&rèZÉ¡Æà€#Jt'Ù%å-)MWtÚNú_r®tÁ9û¼4«ï_öH§k}Û[…Ç ´\$Í‘aZ^QP}3dÇ“Íª·´Pms§vÇ‰Œ~«ízä,ŸKò4èK7Iƒ=˜šóÌQ¹9‹äVÉ—f\nš,½­¶°†äC`+/§L˜¯¾½.Ñ^(E:í&›£0=¡kJ©¤aÔÙ‰Í<Ş4³ƒHÄè¨TÀ´@9¦™YK4(@ãEû”R†4PìW›q{œWæ­lÂÇ;¶k=u¦»Ùí»IÑziƒˆ2ú,–K®yçM-q¹©²|­¿‘ |L!îÆj<‹†Ø¢k¡ü÷}¬È'ú‰Ğ\"×GígàEpÁ^‡\rÆ8Êé~´²T—åÅ¶d¤r­¶\rqÍ\$øğ=şÊ…\nˆ»‹9·ãTûÍ›hŒzô&°TàÊih³t5ş<ãwe¢ÖÄ|Mó“Oõ³*B¥\"25ôÒµ—6bLQ¶œ	Â¯s·Ñ‹h]Ü:ĞJEâ‹@Ù/6Té¶®ûÚåqÆ2ó";break;case"bn":$g="àS)\nt]\0_ˆ 	XD)L¨„@Ğ4l5€ÁBQpÌÌ 9‚ \n¸ú\0‡€,¡ÈhªSEÀ0èb™a%‡. ÑH¶\0¬‡.bÓÅ2n‡‡DÒe*’D¦M¨ŠÉ,OJÃ°„v§˜©”Ñ…\$:IK“Êg5U4¡Lœ	Nd!u>Ï&¶ËÔöå„Òa\\­@'Jx¬ÉS¤Ñí4ĞP²D§±©êêzê¦.SÉõE<ùOS«éékbÊOÌafêhb\0§Bïğør¦ª)—öªå²QŒÁWğ²ëE‹{K§ÔPP~Í9\\§ël*‹_W	ãŞ7ôâÉ¼ê 4NÆQ¸Ş 8'cI°Êg2œÄO9Ôàd0<‡CA§ä:#Üº¸%3–©5Š!n€nJµmk”Åü©,qŸÁî«@á­‹œ(n+Lİ9ˆx£¡ÎkŠIÁĞ2ÁL\0I¡Î#VÜ¦ì#`¬æ¬‡B›Ä4Ã:Ğ ª,X‘¶í2À§§Î,(_)ìã7*¬\n£pÖóãp@2CŞ9.¢#ó\0Œ#›È2\rï‹Ê7‰ì8Móèá:c¼Ş2@LŠÚ ÜS6Ê\\4ÙGÊ‚\0Û/n:&Ú.Ht½·Ä¼/­”0˜¸2î´”ÉTgPEtÌ¥LÕ,L5HÁ§­ÄLŒ¶G«ãjß%±ŒÒR±t¹ºÈÁ-IÔ04=XK¶\$Gf·Jzº·R\$a`(„ªçÙ+b0ÖÈˆÿ@/râùMóXİv¼”íãNŒ£Ãô7cHß~Q(L¬\$±Œ>–Ä(]x€WË}ÁYT¶ºğW5b£oôH¢*|NKÕ…DJ”ª®3 !ØşCmGêõh·e4“Ú5¶Z@£c%=kàHKC-¹´9r/ˆóA l¦ ´mœ¢N)ò\"‘J:k^H¶[qŠñ#¯\né‘	Û‘JW7D]ív¾c°­Êğ‹É\0EïK	«ërÜY)ù-dÖö­Ñ™“ö4S—BVaŠ¸¥ÙgèrÜĞpKPP€dtN_…1ÊÙË8»2ƒoÖJ5hRgÚòSs•bUÏ”İ£Ñô¸G+°&YM·¶ıs¶§ÑÍ\$\$	Ğš&‡B˜¦pÚcW´5ª~ÃKìMÑºh;¼mGÇ»8Ø:@SºïŒ#“È7ŒÃ0Ù&´J£Ò²ÍHÇ\0%‚™†¸Ğ¨Ï8m!¸<‚\0ê¿¨cgÄ9†`ê\0l\rá&0X|Ã”\r!œ0¤ÀA	œÈmIÔı€æ\nTI[T\"œD`@Â˜RË\rE¸ƒzSK2¯R¢¸•¾Tféú/\n¥•\nhVš•8…tÂED@ÔÁnxÑ,§CÂ™f^!Õ~¤Ğ@C\$*\rÉ²†5ş¿C\"l\0ğ0äè\"\rĞ:\0æx/òÒş‚oà¼2†à^˜t_Ò8é ~R`g€¼0ƒçômÔÛe6ìEÚ¼4TˆHqVBİá<GZQàüˆÄÙ”ÀgŸAJ\$@ÆÔê~ƒ‘èPŠ93¥òKòQÎ:†ˆïcÜ}òA‡y!ã\\‰R.FÈÅõ#Wêÿàˆ¹à’Cî\r²4:I¹:»Áë?½IâCYä\r*	6@¨Ö«#-Ên\\Îù3%eåEÈ †bÛĞ¹«ÑhÊ¤¾lŒd^<„1É%ş¹íƒˆòÙƒ”ëgA„3Lı ¤ƒj&ªB˜h<Ì0Î°@æTì\r!„65tŸ:¡¥F,©¦ÀøÑÑ6gP(€ ´ĞÁ…¡LÄ²¨ÖØjj× €€'z)!xci²a?TYNkş5ÑÙ2|Ï9é=g´÷†Vt t>Gù=§Ú‰ƒ½~Q&yİÄ‚ğÅâÃ0¢†.\0ª4ü¡9ò¡ÍAÁ8Nšäú\rÁÂL æ¡T= ?¡Œ4OÒãØ ¤”äò0ÃÉO†Eu(šÂˆªäA]	ZÉ­\"ThCv*Åa!‘EÔ‘\0†L	˜ĞÓW2Á!Î†-¨të.t-µ1„ TÈ\\ğC†¸€\nUÍÕ—Pü§’>Nğ ¥dúƒtõ?§Éùê|SèfMÁ¶3ÍˆåhSPcƒIÚœØE|JÈJ% ÙAL/\0P	áL*ì<^Ô\\ÄÅ²¶ TCP£15&Âù!›ÁB_-êQå(³³@³U•c8íÌ¢ÎïaÆR·ÀìK”®ûéüÓ¤7È`AC¯L(„À@«à =‘Ô#J¯ÙĞi\nàœHaãN)˜¸Æ‚ÍŒ1!É@€<h‚¬‘=ŠìWºüè@3³5(„=K*ĞL½o»(S-	wt3AÎ¶GE)]”h1h×6S(´.	­uz_>İâœæT¹\rÓZ#Nœñ§İ2•5oûYWX^0\no°6¼\$Ck~:‡j‰ëÄñ¤3@Ùö]B6a\r±‘>Û8LB F áyFëö“K®°RÉK-‚î5§hÕÍ°…B4”¹º:VÈèS¶ìVñ@¬nøï¿¼ÜÓj3‡|_-÷¥¶®)õ„Ü³\\Ü¢u-ãT*üòÖÅ42Æİ§µj–ºmZY¶ØöQÒáåñI„±'IóİOwÇ] Ğ†¶â:¿Dk&(¢ŠˆŠV‚U¤`L®‘t\nÀÆª §ÌªÎƒjoNéÌui\r&X®IffÔ,½ÛrH‘·üÍo.2ƒ.JŠµœ^/øãÙ7x´d’Ou¯Ü¤Â¥c§ÍÔQ…*</·©í7·(7X«.*^¹È,-_©ñ3oåû§z¡ÈÄšñ}ãÑ‹ÇHÒš¤˜\0";break;case"bs":$g="D0ˆ\r†‘Ìèe‚šLçS‘¸Ò?	EÃ34S6MÆ¨AÂt7ÁÍpˆtp@u9œ¦Ãx¸N0šÆV\"d7Æódpİ™ÀØˆÓLüAH¡a)Ì….€RL¦¸	ºp7Áæ£L¸X\nFC1 Ôl7AG‘„ôn7‚ç(UÂlŒ§¡ĞÂb•˜eÄ“Ñ´Ó>4‚Š¦Ó)Òy½ˆFYÁÛ\n,›Î¢A†f ¸-†“±¤Øe3™NwÓ|œáH„\r]øÅ§—Ì43®XÕİ£w³ÏA!“D‰–6eào7ÜY>9‚àqÃ\$ÑĞİiMÆpVÅtb¨q\$«Ù¤Ö\n%Üö‡LITÜk¸ÍÂ)Èä¹ªúş0hèŞÕ4	\n\n:\nÀä:4P æ;®c\"\\&§ƒHÚ\ro’4 á¸xÈĞ@‹ó,ª\nl©E‰šjÑ+)¸—\nŠšøCÈr†5 ¢°Ò¯/û~¨°Ú;.ˆã¼®Èjâ&²f)|0B8Ê7±ƒ¤›,¢şÓÅ­Zæş'íº¦Ä£”Êş8Ü9#|æ—	› *²f!\"Ò81âè9ÇÄl:âÉâbr¢ª€P¡/²ÀP¨¶J3F53ÒÀœ7²È,UF„±8Ä˜€MBTcRıSTU%9,#ÀR¬¨\\u¸b—QìjÚ3ËLÖŒã\"9G.nbc,­¨pÇ,#XÆÃË\"şş±\"(ØFJü	ã\"_%ƒµº%ƒÓ(\rïJî®\"1<:Å‰]¸¬[ÊZ®¬£+ğ]VFƒ•è„^ÖéClÚ°í#ã-ÿSŞw­·ƒD)6~¥ Pæ0ÜB@	¢ht)Š`PÉ\r£h\\-9hò.°cÕ®ºFŠBF\r’ó0Í'ŒÃ2«7/êf9\\53I\ríhÚ)<æ:ŒcT9ŒÃ¨Ø\rã:Š9…‹èå¨Œ6Šâ¿u;7¨8P9…)pœ2²Ò³¼‚b˜¤#C‘5¹GßŒ;)_k‚vË˜Ú:¥ÂªR2½*4ML2ÑÃ:ûµ|LÜ”(£8@ Œ›[û°Œs´èîÍÁâ42c0z\r è8aĞ^ıÈ]tC\\¹Œá{œ§	ûé;á}â£üèxŒ!ò\\+7r\nÕZ‘=\rhÎÃK¹8GNc\"lRşÂ#œ'\nÎ€ÒÉÂÁTÎu½cÙö½¿r;÷}\nŠ]ørx/2§œ@\") ø\$†ĞàiÌ`n=è¦ôº1¬\r<‘œT Jƒ“O*ĞI—6‰Ck]¤ÕB°‘é\rfm™=tèG±!ãŸ°îišÙ€4GõÍÄNC1MÌ%ªµv²ÖÉc^‡È€4ĞĞBÙŒøãè]Ã™./Eğœ€Â£Bs‚#ÇîÂøb•ÈtU\n (tL€‘a%@Ä¢\\Ó±V‡v\r3JiÍJò.aĞÕ›D„¹,æQ¹7Gª/ò'Dñ‡)(®Ïäq%0ííŸ³e)Cƒm}/¬9\"pîlÃU0ÁØªƒcâØa%u^´óÒsÔŒéÑ§†Âª4v“dôç˜ªV	é‚`¡³zw•\"kŒ'”Š‡–xJ'.A†äNl¤q“!ÔÕ!\0ÌĞÛ w±Z`2Xb´\r–^2‚{ˆ P	áL*3Xğ·ÁmD\$x‚Â\"¸ˆa|x8¤\r#\"lªˆBLEÀƒ7ĞÜˆQ‡iÔ«wŞTÏ¡ŸEÌìÍ¬óöƒ	cgª …\0¦Bcˆ4Ôd4Œ#©Dèi!@â'ƒ×MÜ©òIƒHz<éˆŸÔÓ<‹*…pG>ª‘zœ›É4£—õuSœ€íV!ÒV!¼&Y ë	gµ¨º,Ö[Ôù\$Ò^µ¬Ö2Òğlot÷\"€AFåBÖ¨cœã\0PF§pl”!„ÙÂ¨TÀ´&‡K8.TïÂ`Ívì˜z'~ˆÍÚi1UXğUö”™Úv2ÜàÜ*(ä¨³0Ê†Í±+§”Â“ã çÙ\rP¬ï´š8blm[1'ÅAŸK^á¥¼«DøÍ©Ê2“L¥Š+¦(¡¢2†ÃRí4¿(Ğ«yQPa\rµyf¥†/FÔÀ\nilÉ]†rG×ı–ÄÊeV«2<äh¸„Ja[\rù„'aèÅa5Î¯¤¼.áÒ™»‚FZz’Zäd#(«ÀPA®˜@¾†à";break;case"ca":$g="E9j˜€æe3NCğP”\\33AD“iÀŞs9šLFÃ(€Âd5MÇC	È@e6Æ“¡àÊr‰†´Òdš`gƒI¶hp—›L§9¡’Q*–K¤Ì5LŒ œÈS,¦W-—ˆ\rÆù<òe4&\"ÀPÀb2£a¸àr\n1e€£yÈÒg4›Œ&ÀQ:¸h4ˆ\rC„à ’M†¡’Xa‰› ç+âûÀàÄ\\>RñÊLK&ó®ÂvÖÄ±ØÓ3ĞñÃ©Âpt0Y\$lË1\"Pò ƒ„ådøé\$ŒÄš`o9>UÃ^yÅ==äÎ\n)ínÔ+OoŸŠ§M|°õ*›u³¹ºNr9]xé†ƒ{d­ˆ3j‹P(àÿcºê2&\"›: £ƒ:…\0êö\rrh‘(¨ê8‚Œ£ÃpÉ\r#{\$¨j¢¬¤«#Ri*úÂ˜ˆhû´¡B Ò8BDÂƒªJ4²ãhÄÊn{øè°K« !/28,\$£Ã #Œ¯@Ê:.Îj0·Ñ`@º¤ëÔÊ¨Ìé4ÙÄèÌU¦Pê¿&ˆ®bë\$À ç#.ÀP‡Ló´<²HÜ4ŒcJhÅ Ê2a–o\$4ÒZ‚0ÎĞèË´©@Ê¡9Á(ÈCËpÔÕEU1â¶¨^u¸cA%ì(š20ØƒÃzR6\rƒxÆ	ã’Œ½&FZ›SâÇFÒ”²9kÅ6…üµ\r·0e•e¸ P‚Œ¨«qu\$	9B(Ü×2˜NÍ;WÄVŒk«)q£ÉsQp}0oµûG_õ>pH59\\·<è’²@	¢ht)Š`PÈ2ãhÚ‹c\\0‹¶Öy¯Pu&“\0Ñ´©*:7ŒÃ4;NÂ){]\0Nz‡€ÔîÈ\nƒz¸Ÿ\rÃÌ4É¶½\$31A’¼2PÌÁ«#8Â¼¸ÏµZ›\rĞØÊaJc¨nĞ@!Šb´È;ˆåšÆw½“(ã2ê6±R;¥ÅTêyLâl¦Ÿ¹á£ZÅ\$Ğ£¼#&Ø—Ã:b2£\$Áâh42ƒ0z\r è8aĞ^ıÈ\\ôi‚ê3…îĞ_\0¯OdT„A÷ˆş;Áà^0‡ÉŠgH£ÁfÓ+£€Ò9?›ëÂm4gÓº»	¯óp“AhÄg{>ØËÀuL[×ö=ŸkÛ÷#¿vè‹É/Îıà†ç‚‰Òr€Àˆ¨ƒâ(…•yÏA3#tÕ8tja…ºœd¦Ôé#Gì,:PÏa	0%\09¡PACb~HOR'@¨èa`Ëts^JC…p¡*pÂŸZkDa®,pŞØ!é,\r\"`ÂPŒÙ”Yç°½§DraÓ„	zÆ\\Á“a¡š#+©ÅnP \rè!'”b\n\n\nˆ)@Ô‹(³|‹=¸3›3VkMy~f¬º‡B\nJĞo@¥íd‡wæ\"ÑÚQ±u¾7âo\r¡Á”8Äš4Èëh7R¤7\$ƒz§æ\0001Å2lİŠ¨71TÂ¡C~xÙ4A‹–rRVÒT¢'B€ØfaHª-\n(1™EHIa1	\$D<šHö©Ğ\"‘\rĞdİ‚)	ˆ	*?39Ğ»Ø¨İƒÉ?æêH ³bô‘8À€(ğ¦2”>ÄøŒ@Î÷.Iù‡S•§Ñ²+…æjÁF£Ê+ò‡aÙ™´TJÈKÜ[\\¨š)¼aO±µY\$|*‚î˜Q	… ğ0T*qS“Ôf(fjD9‚a0Ò5‹Q¯uE5ĞŒÁ#Ìa!äjzª&hé’òIUêqšô–¤˜0IY…b3‹FÃá5¬õ¤şOVItÆÈ‘7tÀ\\ñQ”¶¾ÑÅ’û\nÈm®j0’‚Õ’LB4‰&¨QÄÒ¨TÀ´&–c¤%[:\$VF×UZg‘ª`7ì.`Ÿj}ik]¨4mZGª™H:Ûf„•¯CbßEs,™¥¨¶¨a‹f‰5ù˜óÔ‡õM;K 0Œ1NŸnm#SDšÅêèIƒè@¶ı ßK,T¢6æ@&^{@wÂ‹Záp=Ä{\$‘\"ÃœÅÅUô›iÈE©2Š±SÛ+T“B›7‘A,• CÅ?Q6‘™\0†¯J+7M-xÙ[ôxVE¯S˜Ahh+h:\0";break;case"cs":$g="O8Œ'c!Ô~\n‹†faÌN2œ\ræC2i6á¦Q¸Âh90Ô'Hi¼êb7œ…À¢i„ği6È†æ´A;Í†Y¢„@v2›\r&³yÎHs“JGQª8%9¥e:L¦:e2ËèÇZt¬@\nFC1 Ôl7APèÉ4TÚØªùÍ¾j\nb¯dWeH€èa1M†³Ì¬«šN€¢´eŠ¾Å^/Jà‚-{ÂJâpßlPÌDÜÒle2bçcèu:F¯ø×\rÈbÊ»ŒP€Ã77šàLDn¯[?j1F¤»7ã÷»ó¶òI61T7r©¬Ù{‘FÁE3i„õ­¼Ç“^0òbbâ©îp@c4{Ì2²&·\0¶£ƒr\"‰¢JZœ\r(æŒ¥b€ä¢¦£k€:ºCPè)Ëz˜=\n Ü1µc(Ö*\nšª99*Ó^®¯ÀÊ:4ƒĞÆ2¹î“Yƒ˜Öa¯£ ò8 QˆF&°XÂ?­|\$ß¸ƒ\n!\r)èäÓ<i©ŠRB8Ê7±xä4Æ‚ĞÂ5¢¥ô/jºPà'#dÎ¬Âãpô§Í0×¼c+è0²ŠÔ¶#”jÈFê\$AHÈ(\"ÃHĞï”#›z9Æ‚ äÖ;ëèáFÑé´.âsVğ¢MÄÈ„\0Ä0ÂÀHKTÕp°óWV`è¹CÜ7PÁpHXA‹İGµ@Ö2DIĞÒ;O(°Ã@Bb`Èˆ#\\f÷›Ğ\"…¯*0	ö`æšm\rF-@ÚÒ1weĞ7¿7%Ít±bò6‘\rÑ%R2Ü#\n07ĞØ<ß·¾†UîN\nŒ0¸Mö_Ğ^\0b8%Äì•é\\.bô8	¢ht)Š`PÈ\r¡p¶9fƒ»n[Î»üMÔ”£3Ã0Ì¡@J˜ËKè÷µ;H²7‘Z¡;A\0Û]Ò\$5£šç~¦ôå!O´Á`@=kü>\\6ßâ#l¢Ø6ÀN¨'Úé«8:Î·«kÔñCP»É¬ÌíÇ]Û^©mñö¹:íª¸.÷®¢ú^Àšğ[/´qVÛÆ³Hğš»8Ö¦)ÁpArŒ®Òw3ŒÉHÚÁSÄÏµ%w/5¿´É¼14”z4;8»)¬?‰«	±·è”(Ü¦¡\0‚2mĞ—±ú@2ŒÁèD4ƒ¥ºáxï÷…ÖŸ»£…ÉHÎŒc˜^2\rèğè_è/@ùM‘pæ\r\" ğ†|GN Ã¤ä54`æ¤Ÿé?GáÉO®–ÅTré;IJ<d +P“iq(5Ó”RH£È¦rˆ%#A¢OØ |( 9>GÌúPs}¹ø?\$¢ı³øIÀA€İ\0Š€>	!´8´º‘Z9©\"ò&S\nƒb02*šÒ0FŠ12°Z'BeŞiKƒRk/d1A\"HN	Ò \$­l£5TVd&(0\0@0ÅÔ4‡h¸!(@ğÜ!¨\\5:†Üæ!	'1¡Ó!ÅÑ\rA¤•—’÷Pxz;IYv¥B ñ_á[ÄÇÊtxØÎ\0d@§Ô€@@P¦UŠÒ-\$AAP(5F‘ Ô¨ÅÅDdg‘€Êåä‘Ä¥[Šô\$€,x'jíÒÆPéd|\"“f²N“i(£Z‹xêe	ÒæcÈäu¡Ñ±O‚šGaç\"’5(’8—S™%l)Ì\"\$qaå%æp™½™Ä¨I¢ÜWdƒ°y„dp€Ât\$ƒyU!©³'DŒzÓ hˆ«‘\$ŒP¥ÃÛ~d	±ôFkÜ1F\$3E¬=@í1Ğ¼Ì+¡È“â€`ÏP	áL*(_èüWSé«›ôìÚ)\r#AM]šô:ÿC0iáÔáˆÙ`aèkô…4°¬PQÛ©\naD&RNU§°¯Ÿ	œEGho €#I„oÉ‚6(Ş±¶28¥AKA¤èè‹äÈª—Ù¿g”=0ö¢ìá³áŠĞ›êäC	BS@YµLJ… ]\$üÜËOC\rYB4F‘€±J(SÛ1bÌ-~UhÍ+U7ä±VÆ.uÃ4¬ré–Ò=`IXCÈ6³¼Pî…„z´tVœBˆØ«“G–çá¦põÃqŠDÕı;µÎM§Ñ\$yˆìY¿ÀìÀÈU\nƒŠ¾Á‘›–ÑÅİK¾ÂSÂAÊÉ°ü+w“åÖbøF*a<4nãcÄ€‡ 2(£HÌ’¤+tã›3Ö\0PZ¿EJK+ cÑ^¡ÔÄc\"Èå÷¸†¿#ÇÕKxoGÈM\n¦r&™æD Y:¢?PÙÑÙ;dÚ®ü¾H±9â\nw€*zlNË°­A Á\"Y¨DJ ËT³(ˆ•¦}3\nÍˆ¼àÈÕÀOOt@¸\0 –F\\BßHE=(ƒÊÈªõXÿŒmT	øAPÀct1DÁ˜W§%Szƒ€";break;case"da":$g="E9‡QÌÒk5™NCğP”\\33AAD³©¸ÜeAá\"©ÀØo0™#cI°\\\n&˜MpciÔÚ :IM’¤Js:0×#‘”ØsŒB„S™\nNF’™MÂ,¬Ó8…P£FY8€0Œ†cA¨Øn8‚†óh(Şr4™Í&ã	°I7éS	Š|l…IÊFS%¦o7l51Ór¥œ°‹È(‰6˜n7ˆôé13š/”)‰°@a:0˜ì\n•º]—ƒtœe²ëåæó8€Íg:`ğ¢	íöåh¸‚¶B\r¤gºĞ›°•ÀÛ)Ş0Å3Ëh\n!¦pQTÜk7Îô¸WXå'\"h.¦Şe9ˆ<:œtá¸=‡3½œÈ“».Ø@;)CbÒœ)ŠXÂˆ¤bDŸ¡MBˆ£©*ZHÀ¾	8¦:'«ˆÊì;Møè<øœ—9ã“Ğ\rî#j˜ŒÂÖÂEBpÊ:Ñ Öæ¬‘ºĞÇ)ëªğ¡¾+<!#\n#ˆ€ÉC(ğšÈ0ß(¤âbšÅKÛ|…-näß­ƒ°Ü‰éü	*×ŠS\"‹Í\n>µLbpòĞ¶º2î2!,ù?&£˜5 R.5A l„ @ P¦;ƒ@ì³k#4ŸºmÂÿ+\r£K\$2C\$àŠÌ Øî¡k\"’B0åDŠ•2|\nËàĞÂÎš•ĞJefÏ(èP3ã`0¦è-‡eÑC¨\$	Ğš&‡B˜¦z^-˜e-Ës”¢íyW6£#Ô\rà,è ÂÒã0ÍUª”²ESKj:Æ\" ßÍãÊ9(£Æøc0êÏNkXæ&–0Œö‚–µ¨såJ7©¨P9…)8ª3#c|Ğˆb˜¤#«¥…¡^7MvLøÛN{[48°\\»,e¨*\r’VÛHÃª¸„É‘¡XÀÇ) É!\0ĞŒÁèD42ã€æáxï·…É°ó¬ã8^¥ã\$¦Ğ\r2˜^ÛÒØ˜®!à^0‡ĞÖ!¾Î\nb‚\r‰ÒìĞç87@Ã<5£,BN”ªZ5Ì£+ıñÈåUÉFápA°l[&Í´›VÙ·n’Ö¼î»¸İ»ÉêT¥ß„J |\$¨ó`¥K7„zÆ‘jcã 3íÎ4©Cè,—™Å~\nû?˜Ö¶Àq¢09ò%/PÎ¤úb00ŒiŒ¦é”úF@9éñ¥‡ÎÃ˜ƒb†Ñ?Ğè	š ¡„ç‚\0Æo^`i-¡Ì“—êƒ°iAå\0Ã—6Œõ«^'èË™°@@P¬Â2„\n\n )u&uíŠd<æWèCJd]ü¥Ò`‹N¨i„%à¹'ÃîÉ>'ÌÏš³Ä_S,eÅø'3ŒŸy!Í!ãÎŸ±¥)æ|‚Ÿ¦q ‰-aÁ’Ÿ“öC’|á 4†8 ©LÉ¡.ÑÆ¤¿IIN%¤¼ÉGØ‰ğtL& 7°Õ¡¢Õ9„hP™o\"!åz(D|ƒyôXpF5“ĞâO¡ÇX—µfçJXc#ç¶Æ³ô\\œù*>!@'…0¨@S©’¤*K™µ–Ó\r(A´Ñ†rD‚ˆŠî‘Å0Õ”%0AIÉ;'¨A¤GF”É gªİı'ĞAYK#¤|”–N0S\n!0 Ô\0F\nA—’¨és–m„ &\0R‰8E\r¤]<àÉªÔ¢TRe	´ThÕ©6‹¾IÖ*y€–‡\"²ÖjÆ\"ä8RÀÓKh\nhp6°ÖƒYù\\¯AŠhò^«Ù4Pø2P–†R\ra}fz¢Æ @B FÔ2†s¾™Rí(O†(SÇRh<P¤1bÖC@J•iKä%™\"3Yª*¯#8ÕÀ0Ä1¤Cá˜¶ÍrD‹Ù3¥üŞ—äT—Íá|±ÀµUÔ±IM#\\‹Šª_`ér\"˜ø¢›6»Ëƒ«´‡B!öºsA>#ôú¯@ÓÙ¼1d&˜ÖâŠ‚±mæ%‘ôÆI\\O0Ï\"å	¿6›\rH¯…Ü¿…Bê®Â”õEVÁH@";break;case"de":$g="S4›Œ‚”@s4˜ÍSü%ÌĞpQ ß\n6L†Sp€ìo‘'C)¤@f2š\r†s)Î0a–…À¢i„ği6˜M‚ddêb’\$RCIœäÃ[0ÓğcIÌè œÈS:–y7§a”ót\$Ğt™ˆCˆÈf4†ãÈ(Øe†‰ç*,t\n%ÉMĞb¡„Äe6[æ@¢”Âr¿šd†àQfa¯&7‹Ôªn9°Ô‡CÑ–g/ÑÁ¯* )aRA`€êm+G;æ=DYĞë:¦ÖQÌùÂK\n†c\n|j÷']ä²C‚ÿ‡ÄâÁ\\¾<,å:ô\rÙ¨U;IzÈd£¾g#‡7%ÿ_,äaäa#‡\\ç„Î\n£pÖ7\rãº:†Cxäª\$kğÂÚ6#zZ@Šxæ:„§xæ;ÁC\"f!1J*£nªªÅ.2:¨ºÏÛ8âQZ®¦,…\$	˜´î£0èí0£søÎHØÌ€ÇKäZõ‹C\nTõ¨m{ÇìS€³C'¬ã¤9\r`P2ãlÂº±ªš¿-ê€æAIàİ8 Ñ„ë£Ã„\$šf&GXŠÙSõ#Frğ¡Dè	ƒxÎ€TxçÃh;Úï1“\0ê†(I89¤cÒˆCÊH„µe\\–CPÂ/tÀHÁ i^†.Øêä1‹øØ­J*å\$¯lc\n£#ÈÜÿˆ-èÒFµ2:Î¨­\">Æ¡jj4€P­l0££†3ÀP‚7\rÕ§6ˆ#\\4!-3X„\rÆ¯Éeï|¬‚7\$ç€¥¨V™SõI‰@t&‰¡Ğ¦)K\0ÚcVD5¶Ëİ°û5)±ƒeÔí«H:÷e½³è`ì¸Ş³PØ±‚Ót;+SŠ3\réXØ7Â­.7¢²¹pAHh0áÙ(cHĞBh\n ‰¨øƒ@ˆRx€§#`\\èHöŠƒhúHÏ¥é´²¨êv«k\n¶7ë®;°N{ù²Ñ&Ò4mcvÚ™ˆb˜¤# ß}9ã;#—(¼Jƒ6H0\r£ª3\$ÛIãèş7w¶1ßIzÀ÷]¤%n¼™Š9é®R€Œá\0‚2\r¨\nÖŒtÓ„àÂĞİaèD¯ƒ€æáxïñ…È×š½ÁpPÎ£xÈ\$v¸Ü„A÷ß;Ç6˜xŒ!ó>b–ga0ª3ªÃ	ñ(\rÆµdµ—\\^İ€arNá\$æMO0rªiN) @‰,'ê½pĞö^ÚC{Ïñ>GÌóŸHr}dmö'åNüŸ¡QÁ\$6‡Şš‰Â#íşFbéS 	©„4ò6kR²8ÁÈœ¬ÒnA9X„Í\0Bœd`	'2'T(€ B™-Pb,²–µš![ åéà³vÑÉ :+—ğAŞ*j a„3B°ÃjÎ¨4¶ŸÃ£’\$T& @Î@G\$éAGœ¿C‘Ò1D0ÿBS‰ÒÇ+rx½›Hxbğt2&q“ÆXÎe	Ó”‹Ì‚‚¢\n`ùN#È)á4(HÜxy\0DÇòèÑy, hiI8”æ…MÙ@¡• ¦çÑÉ,yPMŸ†PZCt~\$<½0MÚ?0É8>ƒƒ°CªmH0î¾SÍå}ÌÈìà)´*ŒÄœ“vÚë‰ñ@ Ë¼=%\"võ4:°€È—ğä½Ë£Y&M]ÑÄ’¼E±0a\\8xlèœé>óˆ®š¹†G^[çTŒ²h‹ü ä`­¦¿KÉi2Œ™…\0Â -2ìÈèE‚ŒÊSV5.ä=D*\\éˆG(å\$¥§G¨Îá•c¨Ç~#:CIK ò0lOP%’–Ã©÷&Ğ²–k‘ Õ^£@¢*’¦\$ÑL`¨\r	%DhÒ,Ã6¤9Vˆ?Pİ^A‰ıTòFÁ\")Ü°2eOiC½§°a•y\"›ZeÕAœFÅ\$·GOHgµ«ğ © Òi£êkf°)Ê. Ø\nÂZÇv€€Ü›\nv%Ò(BuÀ’IĞˆÌQ³©S`€*…@ŒAÁ¥‰Èê9f¿-Å°¶V~j#’¶Ûï‚Ú3&lÎÚkzGIÅõGNÙ”‡z-+©}XÉÄ”¦KÌ˜Ã„ä€¢üy–éF¤Œ×³ÖuÌEÙQi£Öâ:¡ÔJ×_Ê4QLÜ%¤Î%ÂuJÉÈË@R–PíË¢ÜÄ‰\"f%¡„6BXŠèsUxŞ×™Æ\r“ÁšFk¹”’ÀîÊÙrmº3±pŒ×Œd<Ëîa‰îæM±‚X(Àû]”oâm_€¼\0";break;case"el":$g="ÎJ³•ìô=ÎZˆ &rÍœ¿g¡Yè{=;	EÃ30€æ\ng%!åè‚F¯’3–,åÌ™i”¬`Ìôd’L½•I¥s…«9e'…A×ó¨›='‡‹¤\nH|™xÎVÃeH56Ï@TĞ‘:ºhÎ§Ïg;B¥=\\EPTD\r‘d‡.g2©MF2AÙV2iì¢q+–‰Nd*S:™d™[h÷Ú²ÒG%ˆÖÊÊ..YJ¥#!˜Ğj62Ö>h\n¬QQ34dÎ%Y_Èìı\\RkÉ_®šU¬[\n•ÉOWÕx¤:ñXÈ +˜\\­g´©+¶[JæŞyó\"Šİô‚Eb“w1uXK;rÒÊàh›ÔŞs3ŠD6%ü±œ®…ï`şY”J¶F((zlÜ¦&sÒÂ’/¡œ´•Ğ2®‰/%ºA¶[ï7°œ[¤ÏJXë¦	ÃÄ‘®KÚº‘¸mëŠ•!iBdABpT20Œ:º%±#š†ºq\\¾5)ªÂ”¢*@I¡‰âªÀ\$Ğ¤·‘¬6ï>Îr¸™Ï¼gfyª/.JŒ®?ˆ*ÃXÜ7ãp@2CŞ9)B Â:#Â9Œ¡\0È7Œ£˜A5ˆğê8\n8Oc˜ï9ŒŒ)A\"‰\\=.‘ÈQ®èZä§¾Pä¾ªÚ*¨Šô\0ª¹‹\\N—J«(ì*k[Â°ëbÜÆ(lŠ²Ê1Q#\nM)Æ¥™äl–Ìh¤ÊªÂFtŠ.KM@\$ºË@Jyn”ÅÑ¼™/Jîò`•¼ğ3N¡•Š¶B¡òÛzö,/ƒƒHç<“ëNsxİ~_ÔŒ£Àè2Ã˜Ò7á¬)6Tª¼`€8&tR®8Ø«ñ‹¦«Úg6vv+h“N…ÓXµ¸¹Gd¥,s{3Äâ¾œSğÚM—‘¹Š¯š«4L¡Î}*gË.J2ó…:^›§Ğ)ş–5\rj\\A j„ÀÂp)lûÚ\\\$É'jª F©k£†¹ªı½µ\$\rm©x ®9%NS\$¹p|¡hÚ0#dcU\$ÂÌ§¹&v_x'ú§ª+ÿŠ ª¹-jC/Æ\r•NYt|+²j:gMÅñ½VgÖp¼-;0¤ŠRg/Ò©Rg!Ñ‰“~2DJ\$ùn¬¥à^-¤iï¬.ğJÏ³Ï\"\\‘±Ï¯8˜C`è9\$“ª…Ê=\n¾]Oú-g©Æeµ;·dK|JŸÜÇÜù ¯Ôó3ô¦Æ\nÉ;CnÍW:Å‰Ñ)7¯h×+¶(n\nññ*Šı˜ U #÷B\$X=óêiYÊ³{ÌhºXußzÿtpLÖ;`[ºz%™%*èÊÑ‘2ÇXÔÁ¢L7¦â³æf†á\$&AĞ¥ŠS‡¡yùÉ×’+*YV\$Hø£tŠII-aL)`\\ÎÏ!Kª™h¡M\nã”\$Ñ\\”ÆUÄ-\\ôÂ²Â ¢ï-È¸¥@ĞSJ‚Ì:°àÜÁ\0A´4†äá	CaÁ‘8\0xA\0hA”3ĞD tÌğ^åÀ.2nN§\0\\œÃ8/a€½?°0èÃÃp/@úb§y:Áà/ øÂ‰tA Ié/6ÅÌ¨Ã¤S	[bWAÄ­LMxX¢¦…‘`Âˆˆf}‘I&,Œ¥e7² «™IìeqàAFùO*e\\­•òÆYËYo.eÜœ“ÉÒ`L)’ÂX[\r™(Áæú’‘Z¡>¥ÉgŸ¹£4Ğ\nÈXú¾¶ Š’%=Åí·d+ ”†fçÙP)\"¸w\nÉÙ¥oÀİ\$Ãq\n•>Ğ6Ò\$8¯U?\rìáÔr61\\9‚9Ş€©ŸCc˜ì@wêC}	ÂI‡ ÚZ`aÌ,9(`ÆÔsÁÕÔ†ÀŞäí]\r!Ğ4\nøŸWİgUÊ@İ1ÃlfÙ›X.I9~&Lü«R@±	YO\\(±U ZB}I êu½‹fíOâ\\ˆ\$kºÊ+K>@P\0 Á—¾x\nCWG+¤•–“¸^YÈ·)A\rˆIêµ3ëğoÀ9ìãC(g­-E(%ô Ãz…±5Ø;Öƒ~UÌä¤eü“\$Jàâ©‘ õ-*ÈĞĞœí×•ÍFW\0A^kı÷\rÁÀ:¨µ£Ã“Lá 4†:û'<°i©öü†0Ã%C)…¦HîBîÚ•9Q.,U\"Ú£˜VÈ¤4HTÿ©Cìµˆ¨!VHÇ·zI”™‰7eÒr¢Åàpèì1±m”’È\$@~ã’,GôÖ :`\\z³„Ó#I™yBçòF•×1GÜ/Q÷¤0À¸RPc…Š’@NäÉÛx\nÿ`y{\n<)…HŞÍI9ÔÊè–Sš¤UÔŞ#,EÍãhYHJĞ@dñsÑ–Š+=Å1+r@Í!É–òC‚zOÕ™WY¥S´C&èÌA•-à€)…™†J ‰*[)Æ‡ÉMGEæ˜ãôûòZ«(O}ıšWû£*TÕ~\r+´Œµ_ÛF²‰eo›Í„Hö#Gi/ÁÌ­²‹UNÍ!?Ol¤°}á‘Ù!gà£í›¿n’…VE¶™´æÒ>ğÚÈ•=ÒH÷\\CÄ•©o\r½±äı\$\nL¹[÷¼ß8laŒ6`P×—=*U…—>—çy4ÄTù¡Z8~O\"¸*Æ&…{JƒG:UÓ¥Õ>¶¾AD¡İ³ ª0-(4Ë*v³.³m•\r»¥Ïé»óè[‘sRvaNı.1–è—\"…±é@{gÕb´“ŒÜ¨¤+”k™Åh)±ºŒé„9r©¡[E®\"Ä‚ *€ÇKÊ.:dSš³š…Çó›n~‘8…ªUİ–çx9›‹\"n1Æû'î:i÷ñ›=Å(¦´­š´mÉ50w'C³,Ïxı! íCÎ%è\$Ö±#ËĞ1dº‹§zÄÕÂ«İwg©›°Ê}Üˆ ¬v9s{r–/½^Ş|nxg,Oz‘;/5Z\$>óØáÛîŒ.ŸxèãÂèÙÏÅ£ÃÓı?IÈ©ƒ}K1nı'sö;ˆíÿ®ÄƒN:@NhÃfYCæ ";break;case"es":$g="Â_‘NgF„@s2™Î§#xü%ÌĞpQ8Ş 2œÄyÌÒb6D“lpät0œ£Á¤Æh4âàQY(6˜Xk¹¶\nx’EÌ’)tÂe	Nd)¤\nˆr—Ìbæè¹–2Í\0¡€Äd3\rFÃqÀän4›¡U@Q¼äi3ÚL&È­V®t2›„‰„ç4&›Ì†“1¤Ç)Lç(N\"-»ŞDËŒMçQ Âv‘U#vó±¦BgŒŞâçSÃx½Ì#WÉĞu”ë@­¾æR <ˆfóqÒÓ¸•prƒqß¼än£3t\"O¿B7›À(§Ÿ´™æ¦É%ËvIÁ›ç ¢©ÏU7ê‡{Ñ”å9Mšó	Šü‘9ÍJ¨: íbMğæ;­Ã\"h(-Á\0ÌÏ­Á`@:¡¸Ü0„\n@6/Ì‚ğê.#R¥)°ÊŠ©8â¬4«	 †0¨pØ*\r(â4¡°«Cœ\$É\\.9¹**a—CkìB0Ê—ÃĞ· P„óHÂ“”Ş¯PÊ:F[*ˆ‘ƒú„\nPA¯3:E5B3R­£Î#0&F	@æš¹ksÙ\"%20†âLúw*‰ƒzâ7:\ròTá¸£•XÊ¢pê2¨òÓ+09á(ÈCÊğÓÕDŒCÍP¶¨^uxbPnk4˜eç9©*‰ã”jÄOhÒîˆ#Ç\\W@SË1*rÓB ÊÄÈ+ ŒƒPëmOb(ÜÒ±(Ëi¥‹ÍÈçÕ%?sŒ-25u\r1¢:š2\$	@t&‰¡Ğ¦)C È£h^-Œ8hÂ.B´`Ü<ƒÓHDcKœ\r“2Í¥¬dÖ3Ï Ü¬»Ï³Jç7bíI%HB=\\Ñ‚ ŞŒ#sƒoÈ–R29ŒÃªX6QKHçLÂ3Ş+ÒÓ4Óö0Ü:¯@æ¦™°ÖÉb˜¤#:ƒ²\nò]\0­Kƒ´9\\wªU¢Gmz;Œ©`Ì·\rº™å9u	.X¬iR†T¨¦ø*3ÏŠ5»¥PÃÂ[š“íRò†•àĞÆÁèD4ƒ à9‡Ax^;õtiË?+pÎ¯!}x×Ä£p^İ»ŒµxÂ&‚š3f/L8È:ùC- Âi“’9Œk{ı¸QAº1¾	«òğ•A#œ²€ÒÆAÁ:ÎôIÓuWX;õÃ'`v]§tœËÊuw@ˆ¥à’II\nJ8¯á¦’4’ƒ¢SSî(–¶HĞAö',œâŸ ÊŒ‘%¨Ï#L±ÜDi™;’2ÖCADgâ\0îi‰d #gÙÈ\$¥>C3ã@¬ùó4†ÑKÔ7‚&Å_”pŸaÜ^¡°Ìó<p`\$f„Tğ­ô8‡¡Z\"€0¸\08o@ğ¤‚”\nJA Æ9‘ÂĞHBµdğÍÉÁ@ài\r1	9Dl·B4Œèo@…Í¢s”MjE\"áä‘pÆö2dd‹–“†}Òy(†Ø‘H„9C›:‘0ê¨FĞRXPØ˜ø*\\\" \$PD‘t0Ê(x“f×!÷ÎN1®j\$ü’Eù{‘Ô;Kd¯p@„	SÑB!„:À£`Š\0PI\"aåLIAÍ62 Æ.@Ğ‘\r®Qı“íKp—fÊX„Â˜TC§ñº’<½‹g%´òÃÑ‡HDàÄÖKféÜ\$H¾¹BLfqÂ8†…)%–Í q43FpÃ—£XÑ¡-TÀ0¢h\\€€#HÑBÉ%°bx¢Ê±4SIä†³Ê†×`\n©fX˜#a—µJ!507¦¦Œ}‰EQ\\Ğ\$îx(jÊî%k²‘ªÏ×±˜i€6¹.a£HbdMÔ6¦ˆù	J.9d¥¢@ `ª@é§…P¨h8#¸á˜2ÔM)”«•5®ÊÕfª¤‡¨G]“CjQÑM ¬§roèá´J¦Á†`òÁHÌ›VÔj©7YS\0PmªÆÃ›°ªq\$ª¯…åJ¤<r•½(A*HÉ†RæX²‰Ğ™{€¥Ln\"5Ä¢ïôhx@PP ª-*¤†:PmêDc´ áÀ„¾§Tı S–~­ÚSkKÀdV\r›¹ƒÏ=£„iY\\Zâ—™z¶Iì*&t«TÔâá°d©Å#ğ";break;case"et":$g="K0œÄóa”È 5šMÆC)°~\n‹†faÌF0šM†‘\ry9›&!¤Û\n2ˆIIÙ†µ“cf±p(ša5œæ3#t¤ÍœÎ§S‘Ö%9¦±ˆÔpË‚šN‡S\$ÔX\nFC1 Ôl7AGHñ Ò\n7œ&xTŒØ\n*LPÚ| ¨Ôê³jÂ\n)šNfS™Òÿ9àÍf\\U}:¤“RÉ¼ê 4NÒ“q¾Uj;FŒ¦| €é:œ/ÇIIÒÍÃ ³RœË7…Ãí°˜a¨Ã½a©˜±¶†t“áp­Æ÷Aßš¸'#<{ËĞ›Œà¢]§†îa½È	×ÀU7ó§sp€Êr9Zf¤CÆ)2ô¹Ó¤WR•Oèà€cºÒ½Š	êö±jx²¿©Ò2¡nóv)\nZ€Ş£~2§,X÷#j*D(Ò2<pÂß,…â<1E`Pœ:£Ô  Îâ†88#(ìç!jD0´`P„¶Œ#+%ãÖ	èéJAH#Œ£xÚñ‹Rş\"0K’ KKÜ7LÉJSCÜ<5ƒrt7ÎÉ¨™F¢’œ4òr7ÃrL³Á/Š	ƒzØŠ°L%8-ã¬ƒËèjFL¨@Ò9\rC* ƒÃÊÔˆÈè³, ÎA l¥hBxèLĞ2ÀIc\0´ÄkP(\r4úÿ4ƒ²2@PŠ¥nP—#!£¥2¦HMŒ›Ê4zÚ¤ÊI`*”õ@:‡PÂö7#ÈôX\$	Ğš&‡B˜¦*£h\\-8ò.ÉéxÆ’üj6L S*ËÉ©HŞ3ÈÚzÚ=ìÜFÑéqH67Ë€ŞÏ\r¬`òAjÆ1°ƒ˜Ì:‹Å…acL9dãÎŒ½¨UÜâ¡O0ÊaKh7Æ™*¦b˜¤#fĞÙ¥C|TÃ4\0ì´¹@êİ)¬¡·Ãffë%)xÜ°ª4NÌ½(Í5(ÈPÎ8JP9fÃğ!“ˆxßÊ3¡Ğ:ƒ€æáxïÉ…É&Ğ9ËHÎÂ{ó6UsÀ^Üûë»xÂcHÓ‘½³ÀA\$ğZ©¨ÁOnÌ¶k*ÁH1#*j›°¯zUK°ğ8Sè0\\plÇ\rÄq\\gÈr\\§-½ó.§87s“¤);ü	#hà‚ËãpéÔõs”¾×\rè;Nâ§	J¤•=ù*zûjwO­ş¢H@ßaÏC§Ôª6òRC¡=¡Ü3æÿ“sI|ƒ†Ìk‹*e†%—³vÏ ¡®4ÊüŠ†¾†!¸4“ æMB)P)åè¼”(ñ€c(´Î„\r4ëp „=BhUâ˜T¨R0\niàÁìiŒğp4ˆÒt\0®Á‚ñÙ†ÆÌŞrÒCÁ¬´¸`Ö®Ìsƒ9ÃF’oÃše‘¤öšçôƒæxæ%ä‚\0îYƒhhÅ‚/!¡€a\\Oœ¦JO	iÁdÙ‡\$†Gˆ)†/O=µ3ÚPŠ\$?gIº€ ’EÉ–”ƒÆÃBá\\jpÁÅ¯Å`Ì|Zëyrğ­\nCÌ`æE–dÔ(ğ¦lzX¥’ÉWdFá«?ÅV;–´pUù–nÌ…°¢K‰„ãpÓ©¬¦ğà€-0kœ:ItÌc(}a¾›ôhİÂ˜Q	€€3&µ\rA\0F\n‘E„F—Ô±#Iı˜éxŠÄ70›i†mtZ¶³vÛ€PUkóª‘¶¦ØTå¨KSê…7»\"L\\â:¬›ğÔy¢jêT(­vª†ÄpN0›‡ä¢–˜˜C`+\rdÌ8ÜCJ]8ÑZ³àª0-	­¿S´M_J\0\$ª­Ÿ–¢ü‰Ë‘\r#†HÒ0Ê‡©u'\"Á5ú†Ì@Q‹n	™- ¦—L,8ÅP¿¶:’¸-L0U9Ô\"yK!)ê‚)‚Ò~©Q¤‘»(ÀÊ¯Ã\\¨;f„˜p¨pP™3]%Mö.\$Xªn0åÒ£Ğ–Ù“ÂÒ'5¨³Ö”YÕÑ:!\n9ˆNÚîHÉÑÊ.%Ì·…\$VKåŒ!¼4";break;case"fa":$g="ÙB¶ğÂ™²†6Pí…›aTÛF6í„ø(J.™„0SeØSÄ›aQ\n’ª\$6ÔMa+XÄ!(A²„„¡¢Ètí^.§2•[\"S¶•-…\\J§ƒÒ)Cfh§›!(iª2o	D6›\n¾sRXÄ¨\0Sm`Û˜¬›k6ÚÑ¶µm­›kvÚá¶¹6Ò	¼C!ZáQ˜dJÉŠ°X¬‘+<NCiWÇQ»Mb\"´ÀÄí*Ì5o#™dìv\\¬Â%ZAôüö#—°g+­…¥>m±c‘ùƒ[—ŸPõvræsö\r¦ZUÍÄs³½/ÒêH´r–Âæ%†)˜NÆ“qŸGXU°+)6\r‡*«’<ª7\rcpŞ;Á\0Ê9Cxä ƒè0ŒCæ2„ Ş2a:#c¨à8APàá	c¼2+d\"ı„‚”™%e’_!ŒyÇ!m›‹*¹TÚ¤%BrÙ ©ò„9«jº²„­S&³%hiTå-%¢ªÇ,:É¤%È@¥5ÉQbü<Ì³^‡&	Ù\\ğªˆzĞÉë\" Ã7‰2”ç¡JŠ&Y¹â Ò9Âd(¡„T7P43CPƒ(ğ:£pæ4ô”RÊHR@Í7Lóx–¤hìn¨²ú–Ë¾©‹;»¦ò¤ÌœÇYIìÒG'¤³2B°%vıT®	^Ÿ\"Ã#ÉO@HKc>¶C“Õ¤;æ»@PH…¡ gl†¬còÉêXÌiN +L!LÂt\n;ú²×ì	rë‰ÚBUKQô€“#±¤¤§¦ó~XÆÑqR¦‹M3¿¶“®°–Ì›\0l—É²ÃÓW;\\†ª%Šß+Ä,—°‰ÄêŸÙVc<€dõF@âJÉû;Ñ°\$	Ğš&‡B˜¦cÎŒ<‹¡hÚ6…£ É~Íƒ\\¥xˆ9ƒc`Ù\$¥¬›«¨<™%I\nìˆæÉæ°Ìm®Ö±VÛ~\"®¬õ#@£ÉK¸ÚFWŠDF(VcúA&ÄPó‘•I+Å[4‡7N{@\\Ö‹.:ÔÔx¾AoLşø£oü\rrp¼=‡Ä´I+õÆ·œzºäB¦)Á\0è7tê‚<×Z(¡ÁwF°ìµ½^†—)–qØY²fïÒ\"%RK©8¥bKšö Ğ0ÀÈÍC´›Ú# Úö@á`@1Ò´˜É„àÂ\rÊ3¡Ğ:ƒ€æx/ğÌúZ\nà½H‚ô,¢¢”\rÀ¼èƒ`g€¼0ƒâ¶Ò³FŠy¢ÓnWÑp\r€•w†³^1‘U	@Ø’Ô`XAo¦ ÈCÊ™«‡\$)Iá+rÂoT\";~/Íú¿wòşßëÿ€0;ÀX8.P2	¨å ¤ ”(`ùŒ/¤pIYS\"nªcŠÄ]QÆ\rxàÖ¨HUÚ0%ÉÉ0êCÈ‹e èÀ£\"£HrÇSØ™«B—Í\"fÏi\nÇT¨ á¤6À@ p@ï|9ĞÊ±ƒfRÉ†0Æ†˜f²x6ğÎ{\$Ği¡Û„( %3ë~Ï¼7AÂD~O‡\rˆ;²ïUJâEÄ!0eƒ!X!4*“1,7@ ‚3äîA§\0ÊÉ@((`¥çVeÛ&œïØ±€Ô¬X’ğeÛğ@i=j3ÊyşˆPÊCA½Lyjå9ÊK©¡â¸´øÅc˜0“>Š2%° ¡OÌ9¢9_H,»B‡°8TD‰0rXÁÜ4Ç/8gr}\nR§Ö_e9KÈ&šâÑúej¤Ì¬£†èA1S6‰Yâ¥æVğ¡b,‰1ØÂM£voQÃ°	\$h<†ğê…CJÆCdmH¬iwBŸ°q¨ad_\$W~©†9j„åí\nDt¡™%‚™£ƒ(d-â‹\"Zm\"# ,vª’„ÀT]`b)²BcŒuÈúf\$•n9:˜ÊâJYi\$ìBBÃè§¬Ñ\ny†Å769Î~‹\n=³‰(¯€¦Be’&Ë®M‚j\nóœÁRpÃÈDí}È<7µÇ¨ŒS’äouÆyæ+ç¢@ñ/^Ç5xI“o¬8û#jP\"MŞJöPñÕvGy®ú¥ ˆüÔ{ÈË[]¼÷è€ßÂ˜ïïzÏGf¦·<ªI-Õ…i6Ğ“Öluú›µM ãé\\‡2\$ı²vÊ‘‚¨TÀ´,sxì0	<4ä’ì™‹şäñ‰¦»æí_b<Õ]ù'Uèõ¨’´„°Œy8Äåb'#µWÕSÉ)ĞÎŸRtØêœÉöÀüHeòW„1J79m½ªšŸƒjû\$lV€´Y®Uã;Ë·9P]Tm!ù+<ËíÎÇõBxÎ—A4˜±°bI ­9ñÍ·dò“rÂşSÆ“ÜqbËcF–jıêdx‚´ˆuF3‡á)6âVi1©”]¦8ûœJjÑ…ˆtÎüÆ¯2n·µ³\"ÆOMäìç’Ùa ";break;case"fi":$g="O6N†³x€ìa9L#ğP”\\33`¢¡¤Êd7œÎ†ó€ÊiƒÍ&Hé°Ã\$:GNaØÊl4›eğp(¦u:œ&è”²`t:DH´b4o‚Aùà”æBšÅbñ˜Üv?Kš…€¡€Äd3\rFÃqÀät<š\rL5 *Xk:œ§+dìÊnd“©°êj0ÍI§ZA¬Âa\r';e²ó K­jI©Nw}“G¤ø\r,Òk2h«©ØÓ@Æ©(vÃ¥²†a¾p1IõÜİˆ*mMÛqzaÇM¸C^ÂmÅÊv†Èî;¾˜cšã„å‡ƒòù¦èğP‘F±¸´ÀK¶u¶Ò©¸Ön7ç—3‘¼å5\"p&#T@Œ£˜@øˆ’â8>Ğ*V9c»ì2&¯AHõ5ÃPŞ”§aœ¤ÃÔÛ£Xæä¶j’Œ©iã82¡Pcf&®n(Ó@;ÒÔŒšx´#ƒN	Ãªdú€P Ò½0|0³ì@„µ)Ó¸¼\nÑŠã(Ş™‰Ó\"1oÛ:§)c’<ÛŒSûCPÊ<‹¼F¦i¨˜: SˆÙ¯##Nû\r1´'GI)¥èÂÛ¼ãHäÀ£ ê		cdÈæ<µÃ]H(.âîÄ\n£¬F¡¢ÊÊ†Œxì:!-Z”Õì@Ö<¹Œr>¨\\uøcJ5[ÏÓÉc”&CÍ<õUŠPóp‘&Ct|2Ub²XÓº©°[#T˜¶\rÊØÉBÓr±#Mœ2 LMÈ1Á*%r\rfmp(4¢5Ãeç8¨È]XŞå Ñ|ßj Ó\\8<àÀPÜõR‚@t&‰¡Ğ¦)C€à8¡p¶;e°º[Iûü”1dº –3Éƒ¸¨‰4\\šŠ	b]Q´{aIê3v4X@6©‰J<8-Š`Üä£sE©Dn’Ø÷Sí‚‚äSiÒĞ-`@¤£ìêql<Zue§²í2¸/Êeƒ±Zjy¨\rÚËªš¶±­4h“Ôì3E!1cÎÍM={VØ4íÔşïCUk‘­nÉæñ­ïyer¦k²x!ŠbŒ ï‡ÁÇ…^Tö…4O4æ^rj*Ïc=Çl}TS«£C¨Ò¨\\DÓeLŒá\0‚2sëÔ1Î‰æˆèĞ9£0z\r\n\0à9‡Ax^;şrOî¾ï°Î®áz0mÊ!Làˆ?ó€öà/ øš„—šàH)\rË•Àµ“Då›\n‘(M¼™¼Sˆr¡ÍDÂCxUÑZg5\$„˜ŸØHí_( |ï¥õ¾Ğèûß‹ó~¯Ü»?äşßêoN!¹9†èSğTBæ„”À˜›Aj\\\réø‰r†Md‹\nL©°ì[ÏP9P‘²‚§‘IXV†éŠEre¢å„1ÀxÜÀlˆ2‚ä©	\"ñÌ3BÃa˜¾‚öĞÌx ¡Ç(HL\0c}aÀ´ĞØL|0è|İªc”M;Œ(ià ¢@@@PC’FÄAN&h¬GÕò ×0¤4È0¶P†Õƒ,u{‚‡\"æ(gh 9DbĞg0ø‘rj:ğ9†-›<u@ä	S@¹ÇÙ°GC™+‘ É9Üµ~…È89*ĞîHc’‡ 3¾Ø÷g´™6U—r€E‰ä\$œmé3“Ú]C¥ĞÕ+ƒNÅ¨K?¤©€ìGi5	Fp€ VîÃ@§®Gàò\\\nú{Or¢FÔËê5b’š¢5^—sN¨ä\0Â -fh¶QÇj(¶5ÌÀ¡†×HÏÔsTœ¦Bƒ0iää‘IÒˆTÊ;?e„Š—zÃRßA<\naD&3ÂŠU\n` ÁP(§DØš‘¾(1NxEdFP\"±+%¤¼˜’òRTUJxÇ…‚BwfšpŒ>Ğû>E,ÈeL‡DÖYç„yÃxhOÏ47:Şª!.ØÛ9ZçUE¡^T»ÌdºY·}íˆÒjPpÚÙ‡DH1\"k\0Ñ½7+d*…G^\"‰„(Ğ½V	Ÿ*ĞºîxJÅVÌó]bíqQ6ø«\nNiÊ'æ Áš#K¸ev%›WÚ¼)Ã0¦A!CU‚)‡C„íë hÃ\rÓTç¬õÖ*ÈÑ%qq´ÿâ]‰J‹&Ì=Išk*ê­J(/òÌûÙy­!e·á\"„´öS0dà¡Ê*I:±#í\\5æÅCb‚–9-·õ©#ÆDK`";break;case"fr":$g="ÃE§1iØŞu9ˆfS‘ĞÂi7\n¢‘\0ü%ÌÂ˜(’m8Îg3IˆØeæ™¾IÄcIŒĞi†DÃ‚i6L¦Ä°Ã22@æsY¼2:JeS™\ntL”M&Óƒ‚  ˆPs±†LeCˆÈf4†ãÈ(ìi¤‚¥Æ“<B\n LgSt¢gMæCLÒ7Øj“–?ƒ7Y3™ÔÙ:NŠĞxI¸Na;OB†'„™,f“¤&Bu®›L§K¡†  õØ^ó\rf“Îˆ¦ì­ôç½9¹g!uz¢c7›‘¬Ã'Œíöz\\Î®îÁ‘Éåk§ÚnñóM<ü®ëµÒ3Œ0¾ŒğÜ3» Pªí›*ÃXÜ7ìÊ±º€Pˆ0°írP2\rêT¨³£‚B†µpæ;¥Ã#D2ªNÕ°\$®;	©C(ğ2#K²„ªŠº²¦+ŠòŠç­\0P†4&\\Â£¢ ò8)Qj€ùÂ‘C¢'\rãhÄÊ£°šëD¬2Bü4Ë€P¤Î£êìœ²É¬IÄ%*,á¨%ÊğÜä*hLû=ÆÑÂIªïš˜dKÁ+@Qpç*·\0S¨©1\nG20#¤Äí1©¬)>í>í«U²Ö!Š\níL’ÀêÔ&62o°è‹Œ“àÆ€HK^õûv ãH¾ j„ ÍC*l†Zî‹L–CòøŞa— P¨9+‰ÚXÚS•ıH\nu½¬ğÌ+¢!¸w Ê6BS ¦:MØ(\r&P…¡.Â¼h0òÇØat‘Œ#:PÎŒœı…2au…^áô%A;U±R:bÃ(İŒ#št¡àóûî\$	Ğš&‡B˜¦\rCP^6¡x¶0èºÀ?*b`Ø%.ÈÜÉáû¢Ñ¡±UEÌ)s^¾0©Ğ¦†54¨ˆÉ»ŒmuÜcxà©!ZVÇäI²¦ab½ˆam[~AuœÚ:¥##=câl»=3°°ª.Ù°\ryRîH'ºÖÎòÔÍÛîÿ†¼\nƒx×“¦)Ï:Ë©.¨­EMS5“aZ:²—\r¬òÊ§LfƒM\0CqIJ3O¨B 3„ÉÎŞ[–Â’)*èx¨Ì„CCx8aĞ^ÿ(\\0ùè‚r—á~LÃıà„A÷ãQ¿áà^0‡Ú×\$©pM\r1¹5×bjÈ3QMÊy“*„‚İÜ»{GÌ87©‚PkTØ '¨Ú±Ru^ºa{OqïGÀøŸ#æ}Aõ‡'ÚûÔ+&Pá¹ú@|GÃ‚¸*èÕ?§øLIèmN5L§°ÂMÊ™&FT7(.«[ô\rDè¤ »#övÍJ0)AÍÿ¨‡ˆÄ×R«è¨(@r7Jö#\"ºgCa˜ÏŞ_LÌcSÄù1ÀÂnˆQ•)*€£²£¤á”\0,%Y¨\0`‘™R)ò!¶À ¨x—2GBAAT\"†˜¯˜ÀæHCq%9)µ.£şOƒy¬\\…Ü“±%z·HIóoV\rQ4ŠT¯ÚãC«Lt	ˆÊ–Oe9û%B>M0ÜjšDh”rnM˜q|{Ñ”ÃGĞÆ›¢äAijf”2ŠîCkh=Ä€§œòRdàÁ’ƒà×ÅDWƒ¨g‡…u®ÀÒ I¥4æ¤ÕÁ†.ÃË8©|Ê‡’ÔÑ¤Ø—O:&äQÚ±3U®Ê‰Áv\"‘B#Jn\0€(ğ¦	ñcláœ97â’Êk!‘ÅÃV	é?Š²I¤¯VAè®Ê P‚•›B{ø×“ÖÂM5˜ÊfK…Di\radvRl6÷ĞL  ¦„ˆ¥•²`ÂˆLD%t#I2y¡’)KèÅnIÒ`RhÑ¯%p95Ô\":·W06\r2)=&òäˆP|Ø‘S•Õb¢¦\nÂhÆl­Š‡p6#Xæ,D'Ô'L6ÑY–(ÈØ°m1j…Œ±¶Ef k *6nÎÛFRN‚b\r€¬1 Z,ìŒé]’.®¨tam™°å(­ĞälCxi5,„aµ9šÉÀd3‰øP¨h8µjõ†sf­…œ¯¤6e[kÚ“åÜ¾%€–ßI>N¡Á\"\$‰åLÏ¢O[Ï'Ò8ÅYåD¼\0Uâ!ŒÙ±î—ºª2 *Ù`ä¤ªa(@®aÕBCa7öé£v—îa¸¸	\"rÔTËñir}#cF\$®—ı'Â\$2T°#*šŒá4	øí°ËØÖ-öÊuvÙuŞ©1ØeÌ¤jÊ¼¤:İ1ø—0¶VËš°D\$Ã›Å#‚fSAZ§â³ZWp‘úE ";break;case"gl":$g="E9jÌÊg:œãğP”\\33AADãy¸@ÃTˆó™¤Äl2ˆ\r&ØÙÈèa9\râ1¤Æh2šaBàQ<A'6˜XkY¶x‘ÊÌ’l¾c\nNFÓIĞÒd•Æ1\0”æBšM¨³	”¬İh,Ğ@\nFC1 Ôl7AF#‚º\n7œ4uÖ&e7B\rÆƒŞb7˜f„S%6P\n\$› ×£•ÿÃ]EFS™ÔÙ'¨M\"‘c¦r5z;däjQ…0˜Î‡[©¤õ(°Àp°% Â\n#Ê˜ş	Ë‡)ƒA`çY•‡'7T8N6âBiÉR¹°hGcKÀáz&ğQ\nòrÇ“;ùTç*›uó¼Z•\n9M†=Ó’¨4Êøè‚£‚Kæ9ëÈÈš\nÊX0Ğêä¬\nákğÒ²CI†Y²J¨æ¬¥‰r¸¤*Ä4¬‰ †0¨mø¨4£pê†–Ê{Z‰\\.ê\r/ œÌ\rªR8?i:Â\rË~!;	DŠ\nC*†(ß\$ƒ‘†V·â\$`0£é\n¬•%,ĞDÓdâ±Dê+OSt9Lbš¼Otˆòh¬ÃJ£`BÃ+dÇŠ\nRsFŒjP@1¢´sA#\rğªÂI#pèò£ @1-(RÔõK8# R¾7A j„p¼°¸ÆÇ¢ª¢\r¦®4ÜÊ‰“˜ïˆ#ÇD€P¦2¤tŠ¾²¢*rÕIƒ( ³µÈ ŒƒÄ3QÏ‚(ÜÔ±õ`Ëm‹\rÖ4Æƒx]UÔ×xÂÕC¬Ø¨OÏ)B@	¢ht)Š`PÈ2ãhÚ‹c,0‹¶•©GYè«páİ\0S>Ê´i»MLQªGZZc“R¨2´Š^Ü ÈîWn§(íë»ÌĞ©¤9D_•…‰EŸ*B¯ÓÌ«S)ƒpëQ\"%õ`4AŸªšUh¹íE¤è˜•éÕf£©Éb Ş5éÂ¦)Ú0ì‘ğ\\][¹Zª›:U?j –/#k=+^ÀVe(ÂéšÚ¶P‹•*Fš\nŒ#åŒĞ²:À„&¢¥h¹B:¡¥!ã\n43c0z\r è8aĞ^ıè]tïÊò3…ì\0^¨/ªr*„A÷ş=aà^0‡Ñ”h:Ó¨AÉ³œ®Š’ÂpªRØÒ®Ë³ÅEÑ®­;±gÁ7~2{xë½õÒ‡cÙö½¿rîİèwwá‘à‚ç†ñCsÅCæ;@DUğI\$Š¤='¨J>*Ã¦´zš•H%\$¥&|YVúrŒ­PÄÓŒá'%&1£‡á0{fÉk‚\0îj—Œ/„„¥¥§¶)È{\nU†c<g;ø¦†‚ozIRæl¡ª%èÃ%Dy:¹R&zĞÉ„*h*2@Ç*‡B€H\n7 tiAV&äŞ4`FÉÙ\$ÈŠ©Bœéî5\$€3ÄdÏê{a± ‡t´Œ¢á€Yëí‚„†ã/~Ê]C‡Ø#ÙØnâ…¼°rRºrh%J´h®uÃppjf¹ ÓƒB*ƒ!¤3»UNHC¤:WîDÅ‡‡ÊSÌ)^I	*“ò‚ÙUù\$GêH¦£äN‰)ÈQ	ê¨M*&&‹M³ü½\n2ı7E\$§,öVĞ‰!%n˜ì)f}=Ã‚PÄÂ‡	¬_Rc…<)…F€ÎW£‡ç¢|Íô²ı\r»ƒlşQÎiĞä£ñ›‡Ù\r´*KHAèÈ’u8àŞPáPu†¤¥¢’ŒWA\0S\n!1²Ñ3ÄMÃ	9'd­¿PŒ#™×›D˜º´©ó›/P¼µìr[D\\DĞ&ÒğÒQj¼úSë½Ÿ8Ó\"dÓ‘ƒntò¬MuZ‘!„3)¥ê½ëd\\RÊº¨ù\\¢Óla¼†8èŒÜ¤‰”Áô\\sêj(+e•Æ0âI\r%*`Æ”Uë)°U\nƒ‚©‰hl0¤;:ñj\rTlµ…äĞ £DTHÊ9D„ƒ-V:¶Ù†7¦\$UÛ:KŒ}0R³å£¥…¨lNÂ|KF8Êê@ŞC8aSÜª]:VE¬t«Öy6–@AbËËÖWd”“áBŒ(ÚKFw¬ñ ¡AÍù˜3åçÓ?gÔÄªÓ¢›T¾02\ncVú34‚¶èxÆV	a5zÕù)dµ§Ã|Ÿ¡‡¸Ø§Äf‹@";break;case"he":$g="×J5Ò\rtè‚×U@ Éºa®•k¥Çà¡(¸ffÁPº‰®œƒª Ğ<=¯RÁ”\rtÛ]S€FÒRdœ~kÉT-tË^q ¦`Òz\0§2nI&”A¨-yZV\r%ÏS ¡`(`1ÆƒQ°Üp9ª'“˜ÜâKµ&cu4ü£ÄQ¸õª š§K*u\rÎ×u—I¯ĞŒ4÷ MHã–©|õ’œBjsŒ¼Â=5–â.ó¤-ËóuF¦}ŠƒD 3‰~G=¬“`1:µFÆ9´kí¨˜)\\÷‰ˆN5ºô½³¤˜Ç%ğ (ªn5›çsp€Êr9ÎBàQÂt0˜Œ'3(€Èo2œÄ£¤dêp8x¾§YÌîñ\"O¤©{Jé!\ryR… îi&›£ˆJ º\nÒ”'*®”Ã*Ê¶¢-Â Ó¯HÚvˆ&j¸\nÔA\n7t®.|—£Ä¢6†'©\\h-,JökÅ(;’†Æ)ˆˆ4oHØö©aÄï\rÒt ùJrˆÊ<ƒ(Ü9#|¿2‹[W!£Ëƒ‚× ±[¨—DËZvœGPŒB†1r„¹³Â†k”Íz{	1†»¡“48£\$„ÆM\n6 A b„0£nk TÇl9-ğıÃ°)šğºJaÀnk–š¢€D­¡Ò6ª±\$‚6’¡”,×Ğ3T+S%é.ŠQÈâ šÕÉ¯Z U½FÁÙ1	*¥¨òö\$	Ğš&‡B˜¦cÍÔ<‹¡hÚ6…£ ÉPÖITˆ8°øä:\rŒ{&…H“\"û\\µOPJV„”èÚz½5‚zšÅIZw‡°lê[|§p:V–Û\$¨X©0x ÕtF É­K!ä	´¡ˆs›iai5òNälM”»\$ÎBƒ%è\"ÀÏs¼D„2T\n@¹³­šÀğ4…!ahÂ2\r£HÜïä‘ö‰¢x0„@ä2ŒÁèD4ƒ à9‡Ax^;ïv·®ëïÄ3…òğ^÷Ê£¤Â7á|˜!,Y:ã}!3kNœ1\nV´™ê±NÓî\"ä\$ó‹Œ×ÚMš¡Íí\r\"![>Óµí»~ã¹î»¾ó½ëÎø]¿ğ<D·.Ëü?¥ñzJJÄP‰ÙGÈrYNdœasÿ6Üä–“O¤~' [P¸s‰í¯ï0P:¥ø0¨4=£ÇÃLA\0î4ƒ`@1=£ƒ¾mª80†dº°cgÄ9†`êşƒ`oíyûè=©6\0ÆÚÃ\"^pÁ„64]3ÍâŒ×Û\n… \n (\0PRÁI¦Tì!¦&¾üÚğgƒ!¼\0äC³ø¡ÄSô|’aó\rçÖAï\0Ğ‡I©¤º\$òØÉ™CÅm÷óÛ[@s?,AH5Cpp§ìşŸğä£ƒ¸h\r!5ĞÎÜÙíáŒ0‡SØ€ˆ	3[Éüœ#\\@IzÔu&¼µ#À\\ƒb!Dp™“U®@ÉK0É´ =™6¶Yº“Op„¸ï`A_æØ’ÖÊŸÉÜatg40XtIÃßDN’e¯0V™<^Ò¡¬…\0Â¤—#„”‡‰@öLÁkZIZ™)Š÷fÄœ™1|€ÙD`Ô×1äuW´Ä2°KZm ´#HjIˆ–‘Ûd›S¡‰a-Õ`ÉÁÍa¯‘\$ĞvT`˜5Q^n\rsNbaiÈ…2Ç^¢'%îqm“šJ_˜r@­?œÒz V‘%©øÛ˜³l_XÛ/ŒÈ§´d6õ‹Y“3hšæh8Y5Ö0C²ô-l É2ˆ½™‰D§tÙ8“YëËù“HÔ­ÎÊ¸‚z@…­¢º2Š’Ikê1(ñ™^¯”×#”àİâ<bÊø¤,dÂ:ôdCˆ…§f¬I˜edI¨‰-Zí\$ˆ(ü^èõmO*íX¤šÊKÉiĞŒ9@+ITë¯­S\"m :¬‘”-¯N¶h“\$:\\éˆ";break;case"hu":$g="B4†ó˜€Äe7Œ£ğP”\\33\r¬5	ÌŞd8NF0Q8Êm¦C|€Ìe6kiL Ò 0ˆÑCT¤\\\n ÄŒ'ƒLMBl4Áfj¬MRr2X)\no9¡ÍD©±†©:OF“\\Ü@\nFC1 Ôl7AL5å æ\nL”“LtÒn1ÁeJ°Ã7)£F³)Î\n!aOL5ÑÊíx‚›L¦sT¢ÃV\r–*DAq2QÇ™¹dŞu'c-LŞ 8'cI³'…ëÎ§!†³!4Pd&é–nM„J•6şA»•«ÁpØ<W>do6N›è¡ÌÂ\næõº\"a«}Åc1Å=]ÜÎ\n*JÎUn\\tó(;‰1º(6B¨Ü5Ãxî73ãä7I¸ˆß8ãZ’7*”9·c„¥àæ;Áƒ\"nı¿¯ûÌ˜ĞR¥ £XÒ¬L«çŠzdš\rè¬«jèÀ¥mcŞ#%\rTJŸ˜eš^•£€ê·ÈÚˆ¢D<cHÈÎ±º(Ù-âCÿ\$Mğ#Œ©*’Ù;â\"‚â6Ñ`A3ãtàÖ©“˜å9£Â²7cHß@&âb‚íÇìäÂFräˆ6HÃÓı\$`P””0ÒK”*ãƒ¢£kèÂCĞ@9\"’™†M\rI\n®¬À(Èƒ&ƒ YVŠ%m\\U¨û­ğ(ÁpHXÁˆ%®#?^”#ĞìGğ`Ä˜©ræÅ¾£\\«#£Àb–-cmq	m›şş Ní@£jQãÉM>6ˆÎ<B¼‰óGe­ƒeîú×-áyG)@×‚Œ`][ÖxUâÚ³ãf^`Ø–(ÏáxÆˆb@PÚ‚\\RL’€t6ÊbØó™\"è\\6…Ã#à0NØØ’IKÓ5ãZ7ŒÃ2€…0SXÇ]/¥<úŠƒ{_x´a\0ë@£ÆÂc0ëçã:î9…‰ä<¦=å.ê]f6®ãª²aJna‘#ì«´u‚b˜¤#&Õ‚3	Qf^!Y¼£’b0Ô×#æ0ÃQ¬~®Y¡]©:)¸¨Õ@jé'½Á\0‚Ğ®èÖÒ1Ğt\nç=‡‰ˆĞ¤ÁèD4ƒ à9‡Ax^;ùuíº!Pl3…è@^’NÃ¥7á}ê7ÏpxŒ!ó­c\$…)M°Õ/›K*Û9Õ%Lƒ¼…ĞÕË8ïä`ØCÑ\0äÅCÀp\r%!rj]ã¾x	â<gŞS°y ¹ç½°ŸSúPod¦à’C¶|áÑï¾ädŒÑ7\$èÂ^lÓX9(Ø6¾GÌB\nHe@è7†ÀõÑYåÈ«’´k(l„ 7“Œ|Ù0oYA‚\0îmQËô#!È3 Ã\$¬Ãfha¯6ÄÙ{fA\\ÍZwŒ pä†Â™7	ÅÜ¼º\$^F‘’í>±ìÈõöÆJºÒ3†x½²vÜ@Ş \n (Gäa N°()ˆ¬9‘ònÔ\nŠn´›dm\r´CVhiS„…‰€lgáÜ¹7¶úßÅK2È!{àŞ9q…Ä˜‡4:×ár3F´»¤ä8ÿQV8!Œ4 ÒŞèÑÈ0¤øNW‰<'Å\0È•£:¦Ro)Jp£•˜\n’	(nfLÏ˜#Ò,&á\$ˆ‡“N\"Z5È\\„+34pŠ@q¦¥dH{Ì#Q¹6'NÂC±\rÓÄrEƒY0’A<)…B`oC !p‹8[³sƒœ`TÒô¥brŸh Á†CŸál¹£r çü«“ºçš\$ÜAíŸ¯jD¦¸ \naD&#jkM¡1ÁRJ†µ¡…Ô6‡‚	:ßˆÒ6§¡WePK)9­hæ‡œfY*y®g¡L˜ÚvMÕÅ{‘%Ü¬¼'`U}l°g8¤£V?`Š=„±²|:0WEƒHc¤1T˜Ó°ÙBá[' !™¶\"ònª¹Iå)ú7ª0-	ÕÚPÜì³\"ÉìÊÂö+û·u‚ßû‚®.½Cw¿‘Ò>HI%ÍüŸ®Ã·i{/©XğpÓi\0Qy%t¸„%À¤§c\r*hé¨³ª~—b“`ÔŠö¨ÉAN\r;‹xÅ‘YÖ`D‰9T„‰‹2I]ËÚÎ+L=(PßS™9H @ÌmyØ7¨»…œ¥F–gL‚šDv­QÛÁ'~ä Ë…‹±ië2ÑL¹,²¬,Ğ(zT\r€¨?qRAYk4ñ“´¸Z×\rÇÀŠÓ¾Ì3?¯bJÌ¢¯±Ï ";break;case"id":$g="A7\"É„Öi7ÁBQpÌÌ 9‚Š†˜¬A8N‚i”Üg:ÇÌæ@€Äe9Ì'1p(„e9˜NRiD¨ç0Çâæ“Iê*70#d@%9¥²ùL¬@tŠA¨P)l´`1ÆƒQ°Üp9Íç3||+6bUµt0ÉÍ’Òœ†¡f)šNf“…×©ÀÌS+Ô´²o:ˆ\r±”@n7ˆ#IØÒl2™æü‰Ôá:c†‹Õ>ã˜ºM±“p*ó«œÅö4Sq¨ë›7hAŸ]ªÖl¨7»İ÷c'Êöû£»½'¬D…\$•óHò4äU7òz äo9KH‘«Œ¯d7æò³xáèÆNg3¿ È–ºC“¦\$sºáŒ**J˜ŒHÊ5mÜ½¨éb\\š©Ïª’­Ë èÊ,ÂR<ÒğÏ¹¨\0Î•\"IÌO¸A\0îƒA©rÂBS»Â8Ê7£°úÔ\"/M;¤@@HĞ¬’™É(ñ	/k,,õŒË€ä£ Ò:=\0P¡Erµ	©Xê5SKê‹D£Úœ£Òàİ!\$Éê…Œ‰4¾æ)€ÈA b„Bq/#‰Êê5¢¨äÛ¯Îºàˆ¢h12ãHĞ×£Ê6O[)£ ëT	ƒV4ÀMh—Z5Sâ!RÔé äÅ¯cbvƒ²ƒjZñº\"@t&‰¡Ğ¦)BØói\"èZ6¡hÈ2TJJĞ9£d>0ìJdÇ\rã0Ì´Ã*è”1²Ø—S©’\$7²3›t\$/¨Æ1¦˜ÍW„`Ş3¡˜X§CÊ„‘¡\"Ï£jÛŒ¡@æ¥¢ Ş5£Á\0†)ŠB2¶\"	 \\Vö-øÚá\0Ìô\rµ}h¥¥.deêô¢L[Â›æi›ªŞ„É‹]£–1ÊÈ¢PˆÑSÁèD4ƒ à9‡Ax^;ìr‡¦%sĞ3…èğ^ø@Ê‚Ê„Aöà7¡à^0‡×|(ÅŒKÖ`ÀôŒß‚Œ8B[74)›ôş?ÒXğ8Vƒ+ÿªjÚÆµ®kÛÅ²û6–„m;^Ú7m²š=*õ	-[0Ü›Öù'H#@ß>ÌA‰?)EôTÖ¤n`4±SÒo\r#6:41ÃÇ¹(#»/a\$ì’Q¢£³èÃ,êwö\0ıàv\r„{2úJÑ±ÉŠ:u0Éâ\r0L,\"Â§˜TnKõÕ¼À 8'IyW¢t\n\n@)dğª(ÒÊ»^»Hw¦LÊ™s2åŒ‘ø&æ”ù” ØÁƒ»–%¡¸\0T‰ˆhzd ÇRÈË\0bEøC³¨~I[Iô;š@ÆIX°gk„Ğ§½GèNªîTˆm‚†W·Ã¨mA@K”äæ„\rTYLÅ/Ò~NYZ@h4†²€…IhI!áäÄ´û	C™w†›ª`âIÇœ6´—FÓbk!l<ÄÔHâb‘ë.\0€(ğ¦\ny((¬øšÒË*.y‹€¡œI<'j˜5•Xƒpf\r\$!Ba£¨c6&yƒ2„Ÿ™ˆS\n!1™Aà@eˆF\n\$œ§Ó€~2„Ax6”ìC:XIxÅGöÌ¢¾›DîT' ÒKB,à\r)\"l’¶v—!|(µŠ\$¹Õ&C’%GŠ^(Ô>\\‹\r!Œ‹Y~L€3„qİ½—ÉhF˜ÌÕÄÆ\"B F â{5¼Gç¢}0fP›ÎÕ‚Næ{@î%Ö,·1rM´43nU)!AISÎà}S¹Ğ;h7™5\$QUİAG…»&²8‰ƒ©ƒ\$¡¸Ë†˜B'8\n	¾_ÓĞ{ÏeZ«”|ß\"Š¾€S Æ‘-…5é?jÁWTŒ½åN^»–T­Oçù±>émGNHQ•1Tè¶™zHwê \rHŒ9€";break;case"it":$g="S4˜Î§#xü%ÌÂ˜(†a9@L&Ó)¸èo¦Á˜Òl2ˆ\rÆóp‚\"u9˜Í1qp(˜aŒšb†ã™¦I!6˜NsYÌf7ÈXj\0”æB–’c‘éŠH 2ÍNgC,¶Z0Œ†cA¨Øn8‚ÇS|\\oˆ™Í&ã€NŒ&(Ü‚ZM7™\r1ã„Išb2“M¾¢s:Û\$Æ“9†ZY7Dƒ	ÚC#\"'j	¢ ‹ˆ§!†© 4NzØS¶¯ÛfÊ  1É–³®Ïc0ÚÎx-T«E%¶ šü­¬Î\n\"›&V»ñ3½Nwâ©¸×#;ÉpPC”´‰¦¹Î¤&C~~Ft†hÎÂts;Ú’ŞÔÃ˜#Cbš¨ª‰¢l7\r*(æ¤©j\n ©4ëQ†P%¢›”ç\r(*\r#„#ĞCvŒ­£`N:Àª¢Ş:¢ˆˆó®MºĞ¿N¤\\)±P2è¤.¿SZ¨ÁĞ¨-ƒ›\"Èò(Ê<@©ªI¥ÍTT\"¯H¸äìÅ0Ğ û¿#È1B*İ¯£Ô\r	ƒzÔ’r7LğĞœ²ÈÂ62¦k0J2òª3ıAª PóD¤`PH…á gH†(s¾¬ëÜ8°ĞŸ1:’¨Ú•ÃBÔ›µóİN¶:jrÅëğ3³Ã¢Ì ÀC+İ¯ãs8¿PÃ-\\0£á×_®Au@XUz9c-2ª(Òv7B@	¢ht)Š`PÈ2ãhÚ‹cÍÔ<‹ P¬Õ7®ô=@\r3\n69@S É\"	Ş3Î”é\n°L´¶\"°ØŞŒNcËÄc3¨àÙ78Ac@9aÃµØÉ-\rQÓ0P9…)h¨7h¨@!ŠbŒ§\$­“¤öÁqh&b`ˆŒmLÇ;,\$b2àÈèŞÆ-ÊKˆbV¾TÊ;ÍXÍ#¥p@ Œ™#iÉª49â`4Z@z\r°à9‡Ax^;îu«¯<«@Î¢¡{Ø¶7i ^Ûıì7áà^0‡Ø\"w5¡‹èÉ8ĞÃ3\"Èäš¦)ğ7®éÒ©²>ÉÜÃföÒıì»>Ò3m{hé·î;ë»­l]½oƒvù(\"²—|(Áğ’6	ûÅqPA\rËA†9mŒúÎCè‹%¢bBáñé\$>2Ï,ÃÁ\$+š>•ÚZŸÒ1ğ)(î£‹£2Ájã’)@#7F|X‹b ‹¦ğ@üÃ h5|‰’ÆêÓ:Ñ\r‰Q„%‚Ipm'\rÌ˜bvÉò¹AğYñ\0 ñÌ9#N\n\n0)&!Ìù €ÄPIhC\$‰Ñø5£BfƒIœ\$y@BÀèjÍQN>”7‡pÊ~Èt\rÉ¨¶Á‚ÍƒI}Í¥c²a\rd0-‡PfSú€æ¥ ¼óÛT	!09Ëœ6TA!Z	/¯ 4³ `ˆé•-¥&uú‚ŠI&D˜¨ŞCª€1%´©ÄGH mkmuÜ¶HæIC8f¢\$Ÿc=Ã|yDˆ_Â`Â£9\nQdÅÄ>½’0 réöC³âbÚU´A@è’àÎ@Ã\n«H‰Q}›‡´hHoZ²é@˜À@ÂˆL(6V„`©\n^Ò€&QëÉ„{\$“%§E>&tôt™!¦tAåNét­	°rpÎv'ĞŞ‘NÄ%…ü6+ˆÃaJÇ‘,ÉêE\0	)F,Ä/‰änŒk‡›²Z–#/9A°ÊÏ‰ÇÚA¿vÈKÉ{sL^ àÚJƒödT*`ZA¹/N –¨bÒL'ô=*£Fó‚ò*QT`„‚eB|\\‹¢&dÅ’‚T\\€Qx/A˜<®#‹\$S¨\nGùU˜ZaXÈ“E©XµÒŠßL‹í.yôÄ¾Hy,TÈÅ,m/vi(\na\r-`M«Tç\0¤¾›Ëõ&EøÀ:21A¨²~Pe˜°Î†UI ~<‡1uXÂ\"5”ª´¿5/VË¬r.vÅ\rÛ:µZªua¯„HĞ\"‚.";break;case"ja":$g="åW'İ\nc—ƒ/ É˜2-Ş¼O‚„¢á™˜@çS¤N4UÆ‚PÇÔ‘Å\\}%QGqÈB\r[^G0e<	ƒ&ãé0S™8€r©&±Øü…#AÉPKY}t œÈQº\$‚›Iƒ+ÜªÔÃ•8¨ƒB0¤é<†Ìh5\rÇSRº9P¨:¢aKI ĞT\n\n>ŠœYgn4\nê·T:Shiê1zR‚ xL&ˆ±Îg`¢É¼ê 4NÆQ¸Ş 8'cI°Êg2œÄMyÔàd05‡CA§tt0˜¶ÂàS‘~­¦9¼ş†¦s­“=”×O¡\\‡£İõë• ït\\‹…måŠt¦T™¥BĞªOsW«÷:QP\n£pÖ×ãp@2CŞ99‚#‚äŒ#›X2\ríËZ7\0æß\\28B#˜ïŒbB ÄÒ>Âh1\\se	Ê^§1ReêLr?h1Fë ÄzP ÈñB*š¨*Ê;@‘‡1.”%[¢¯,;L§¤±­’ç)Kª…2şAÉ‚\0MåñRr“ÄZzJ–zK”§12Ç#„‚®ÄeR¨›iYD#…|Î­N(Ù\\#åR8ĞèáU8NB#Œä¶ÒHAÀãu8Ö*4øåO£Ã„7cHßVDÔ\n>\\£„E°d:?EüË3–Ç) Fª„gD¯äª%ä`«–ié`\\;‡95J¨å›gÉÄ¢t”)ÎM•ÑtxNÄA ‰ú«ÖÊÌNÈñ:\r[ˆ\\wØj”„áÎZNiv]œÄ!GGDcC¯\$Am‹ÉJÜàQÒ@—1üÒvIV¼–åqÊC—G!t¼(%…bÅ¹vrdÂ9&(ÊFFtœPİ—qJaêQ%gÅúC-4:b\"s‘åô±JSÌöÏaÔÄCHÂ4-;ò.…ÃhÚƒ\"©]aÈı|6ƒ“HÓäÖ\rã0Ì6\r#pË)vM×m#öâRALØ€¨7µãhÂ7!\0ëV£ÆÜc0ê6`Ş3ï£˜Xİ\\ˆÂ3Œ;èAÕ·hÛ¾®P9….cÖFœÅl~@•B¦)Õ­larÄn‘ŸºÄÌ@ÈDˆ@ÈØ¯	g%ÿ[\nLİÕ\0ëVÃ8@ Œ~ü9tc_VŒ@@-Fî3¡Ğ:ƒ€æx/ğÒßP@.Aaœ†PÜĞ¢¢Šº‚ }N}àğ†|àÙ*#âÜ7Š QxœçÄH&%t ˆ)zëp¤Bq.¸Å£ÍyïEé½PP‰ÂTè€9¢\$HÃÀp\r-İ¿7ê»ùoõÿÀà,}p(9@ÈUTUª¼‚\"¢‚HmØ6Ààé ò§7®Óxpk5¥ ‡ßƒpt„ˆABA	‰1>'™Z-–™ÒE^otÖÇÕx æÕÎ#XCáQµv†ÍÓ•rñÍ9Ç<è4›8†ì4Àæch q27Â™ÌFÉ#dp’3Æ€²bx@PjˆÅ£Tn:ÉD‡“9’ ¡\n…xB”Î!X’a„yëbfš0†«Ûô—ƒFì×›fmM¸e]¨t9Ctqº—îx;Ï§l.Ãº ÊH—8U ¤£wuféú‡4@åİZ8‘ì7bl\n#K´;œPÆ#ÀiïìIéhka|mü¼ŠäØ›‡(óbd\n¢|P\n7Wlˆ™dèI‡@¶4\n!¦–!>ñ•)&¢ˆ„%°X¤ƒÒ’KÔÆ€ ’EƒËt¥všä2„ƒtv8¦é»‡ênPÈfAAµóÅ§åFĞ0csÈJZP@n`P	áL*ÆØLUéƒ(\$Ô@“qCKoÃ&q~GÊbNBê©¢‘‰H´@ˆµ<:@­MuJ ØÔƒ­}Zz‹ÌÒšzbó€ÍÖ–Ò @ÂˆLš|hıB0T\n\rÖ´º¸Ô‡]q®hi´Æ—\nã\\¢ISŞ|IL,•:ì.%É\"DØ´ÂYgŞ'šDJŠà¼×lIc,fmã ×ÁvŞ‹Õ{/ÍïA\r¸†ÀW^ƒHc\rj}?Pí/ë|ôÔ4†g#`F¸¡µñ¡š\\ê‚¨TÀ´*İXß)Ìu €Í’e GêÉ©…Šı\"Gómf¬]Æ6ÇXù4&Æ&g4^î˜Ğ™yB€Ê‘,(qæ@ÉE,ñúŠ»™LÈ™2GÓ‘ÜÈwpû’ñ/Ç@¾&9œÍ\n¬Íš3Qç¬ö‘ÄTÛTy='ãœZ˜âô*3àÂxN46Æ˜ãÃ˜\\/¢¨n:Z\$|°hõ ñ:ÑĞ×c&×e¤K`iRÑ',p²qã#3uX'qDšÇ(€€";break;case"ka":$g="áA§ 	n\0“€%`	ˆj‚„¢á™˜@s@ô1ˆ#Š		€(¡0¸‚\0—ÉT0¤¶Vƒš åÈ4´Ğ]AÆäÒÈıC%ƒPĞjXÎPƒ¤Éä\n9´†=A§`³h€Js!Oã”éÌÂ­AG¤	‰,I#¦Í 	itA¨gâ\0PÀb2£a¸às@U\\)ó›]'V@ôh]ñ'¬IÕ¹.%®ªÚ³˜©:BÄƒÍÎ èUM@TØëzøÆ•¥duS­*w¥ÓÉÓyØƒyOµÓd©(æâOÆNoê<©h×t¦2>\\r˜ƒÖ¥ôú™Ï;‹7HP<6Ñ%„I¸m£s£wi\\Î:®äì¿\r£Pÿ½®3ZH>Úòó¾Š{ªA¶É:œ¨½P\"9 jtÍ>°Ë±M²s¨»<Ü.ÎšJõlóâ»*-;.«£JØÒAJKŒ· èáZÿ§mÎO1K²ÖÓ¿ê¢2mÛp²¤©ÊvK…²^ŞÉ(Ó³.ÎÓä¯´êO!Fä®L¦ä¢Úª¬R¦´íkÿºj“AŠŠ«/9+Êe¿ó|Ï#Êw/\nâ“°Kå+·Ê!LÊÉn=,ÔJ\0ïÍ­u4A¿‰Ìğİ¥N:<ô ÉL a.¯sZ’Â*ªÍ(+õ‘9X?I<Å[R²óLÇ(•Cœ¾);¿R®ÒíJÇMœxİ¯š: H”Š³ÓñbœÖ¤2œê%/üõ¬öJ«=‘•Û•£š7R“*Œ‰,f§Ô´üÑk´€PH…Á g‚†*ıj]°Ÿ\0ÜŠ‚‰)VO‹ù!BTR9pÕ3¥Ü¬Rpm§OÎôÛgdcĞ§vdJ\$ªì§T¶2NÖÙt Vö•§Üïå\0ºë^b´Ã´BU?ŒÊnçizEA)Mkœ¯_(êĞÛpØ•Xu­%ûİxÑI…ÔƒÄ-ì›>âVªVÿÄƒ`è9nÉm{©÷Š—ÖYÅ+ ê‰=´ôêw94:Š•oÃ¶6©puœª¥|¿õ\r[£•{gQ¸×>†»¿ú4{GvÍ§#!y‚ã£q¬îS5!4î¾J¥äı}!Šb*yÉÃèïlìY­›¨’ßè÷t„Ê6Üİ“[ş¦#š·ÉIVß¿Èmj'MÁ×+v¤ûNkOs¾)	?HóŒ|TÀ !6†ÜC“Ì€.İiº'Xk”»·,Æ´êÇÄºû(7o¼¼–Šràa 9PÌAhĞ80tÁxw†@¸0ÀH\rpoAœ†PÜÃ o\rÁ„:˜„Á>toÍJ›¢<Nàûê2àğ†|ãOúã?.“1#vöà{×uê1Y<2<n¹¿‚Î1˜¤ ƒ±Ït…›¿'ªSÚs¸)ë€0¸ûÉ%vEUÚ'ãĞëĞDl:Äİú’faa%„ğ¦ÂØ_aœ5€°9C˜waøeÒ8Á_‰Oô—b<“^‰eŠ‘X¤ô¸ëËa_g“–iƒ¥«¾}±xAGÄ¼&v<FÁ-Èµ<IN¹¸Y\$–W©T§bÔgåØï”˜4°»)ÑAÆ/5Å&“Ô-\$ö€SëïCeUsªT½S,uy®¢G—0\"Y9)1€I\$‚8¯Î<·aÁišVúqÅSİ¡&õh­¨0ÍÛˆ™ò(ì-:8ä\\)ì-/5pPWÁI§[4j\n4™Ó;à{ESò}ª³šIMÑ­EëIGs®j_bÕ[?uÓ+Ã¢®©”­iò{)šÚ¢b•\$T:••Aê©á¼ê‚@åÚ,#Ì,ûÀù~NP|İa±‰ØÈIà¹‹÷RMÄœÏ„*)Ÿ•öVS¦r§sÄ~ys•3BIMú•Œ¡\0‰	vª£É;Œ\n½02ë¦R.Ï™Øc¾ûÚueñ1wlämUp\nIÈ\"d»ƒ4Ëj\$ƒèA¸sËhÓğ Â˜T->¢v¹çü¿rõÛ¨é©Şš,}3ıÕ¾ò°¨åù'´2±Ä•XÀQíıÁ¸fá™OB9?ëe×§„AËÇ‹M]]˜wiXx.9JL“A†Í28©iiÇyÊ¼#@ óÍ=œ4	×“‘\$ø“<¶R¯¾Â^Êh¿SıM-rùÃN­n!¹JÅy”óRæ&Ñµ^x‹\ruTg-DB¬Èb10®qQJ·k\$ñŸò<k#š_ÈÛ¯Lmgç&õ•6·ÅŒ’ƒ`€!·°Ø\nÔ'?˜²0@ÊajNºaM\$5_R_åÚû¬{Ë`U\n,_f¤RVÌwT³O!áÆ.iÌñÆ£Iö]²=y)¥ÈXµñÏÒ,SJ7y\0×,eÿ_Ê•¾B]½â<½©´R]Î®,²‰|WqÂ˜&àWœBµ Œ†]£% Ü™ hæPrÁ†Æø—ˆÂah³J.„.ë’¤7ºâÇ}Sëf ÀH\"LrIö™OJg¡É>Ë\$¥ |…­†rQ½gƒgIw”i‹ĞòvË'Á@ÒêWVS/e¬NÒpaZÛqŸ’ŸĞ8sL.„ËIP5h3Aî\rÙ¸Ù™";break;case"ko":$g="ìE©©dHÚ•L@¥’ØŠZºÑh‡Rå?	EÃ30Ø´D¨Äc±:¼“!#Ét+­Bœu¤Ódª‚<ˆLJĞĞøŒN\$¤H¤’iBvrìZÌˆ2Xê\\,S™\n…%“É–‘å\nÑØVAá*zc±*ŠD‘ú°0Œ†cA¨Øn8È¡´R`ìM¤iëóµXZ:×	JÔêÓ>€Ğ]¨åÃ±N‘¿ —µô,Š	v%çqU°Y7Dƒ	ØÊ 7Ä‘¤ìi6LæS˜€é²:œ†¦¼èh4ïN†æ‚ìP +ê[ÿG§bu,æİ”#±êô“Ê^ÇhA?“IRéòÙ(êX E=i¤ÜgÌ«z	Ëú[*KŒÉXvEH*Ã[b;Á\0Ê9Cxä ˆ#˜0mxÈ7·€Ş:›‚8BQ\0ác¼\$22KÙ„¨È12Jºa X/…*RP\n± ÑN„ÁH©jºˆĞ¬I^\\#ÄñÇ­lˆu•Œ©<H40	ÙÀ…J¾ö:¤bv“ªşDsÿ!¾\"ÿ&²Ó‘ÖB DS*M‘‡jœƒM Tn±PPˆä¹ÌBPpİDµê¥9Qc(ğâÃ˜Ò7Ó*	ÖU)q:¿½gY(J¤!aL3´u”Ó±rBo‰ÖYAq+¥çQnÊ“µÜŠ@E¬P'a8^%É›_XÚVÓåKÎS‘‰‰I£##ÎX1’iÛ=CËx6 PH…¡ gv†´dédL®U	‰@ê’§Y@V:²!*^Íè¿…ÚAÔgYSp—’¹fÄR„¾V0dfj¯å•ò[)‰±ˆx™ÖA–àKoaØ„w’±\$¦Ò2\nDL;«=8’e±#é¶<éÈº£hZ2’¹X+UMV6ƒ“NÔ„ä×ã0Ì6>Ã+B&”í^×ë3ºM`P¨7¶ChÂ7!\0ëL£ÆŞc0ê6`Ş3¾Ã˜Xß[ÈÂ3Œ/°AÉea\0Úû®(P9….{	O—gY ™ˆb˜¤# Ä6@–sÎ€¡O>M”…PEÈR\$OmŒ©+·î\"£Y·£5:ÓO¸@ Œœ¸İñc9Mx@-^¾3¡Ğ:ƒ€æáxïó…Ã›²ÂpÎŒ£p_\rÑã¥7ùA÷èâ>Ã8xÃ>læTB“‘Ö¶X£¸ô\$3ÎïI0¸Hà…\$VlPÁÅRˆœ9¢”VƒƒÀp\r-}=w²Ûİ{ï…ñ¾WÎßKëyï´9>÷âü»ñSJp‚\"¨‚Hmä6¿èÿà\n‹ˆ§7­ã~åƒk5á¥ öìóÃpt€c²£¶.˜`›¹‚ÅáğÂß²œÜÜ8@ÄkÃ‚xÁÊ\"­àÂ Ò!o­ş¸'áœB\r‡ßƒ^ÃED`ÒC`s2HÍ£trh\rT4Á@\$d PŸÊ`¹ˆ„ ¢”Ë!,òi+58óãCı7ÆÈÚcpnƒ*ŞDÈ:Ó’‡‘pÁŞ`¹éV*£ä-¶¬å!šû“7¯d9¢vşäĞiÇŠa¸89“fŠPr[ÁÜä0ĞåƒHg{à‚7ÈS^ÃÈlËğ“™LH93))»òc@‰Àë¢ğ²/3'©T \$ŠCä«È\n\$8Æ‚I-p2•¼lQ\rÑ8äÖ¾C©¼D™†×—´âA¡Ã!™1Ñ9»(!@'…0¨yÚ›UUR˜óˆBÖ+\nÚ~¦”ÈÔ†¨¸•QM)åF‹‘áHwRhê(á¨±Ù*f%TeXQÈÄîÚÙªŸ4à¸gÔ×g¤ñL(„À@¥ø 6ïd#I<İÖğiˆH•ÉÓ:jˆPy®Beéj*¼ Ğ ”FZ3e¦µQÙğ\$…	j%U²’³öXN’¤¾fÄğ…³ŒÒŠWWm¤¶tLÓ6²]<\r!Œ5¨ÄöC´¦2íËDĞÒ›ÌT(!À†×ˆ'«’\n¡P#Ğp£•%>öÙo1hO‰ĞbÂdÆ[CLaŒAŠ\\6¹„°±:AÉ°ì':÷%`›s®‚¤?B ş¡h á\"2†XÌ@qŸ…aÁf\\Àß]ÃjCH[\ná{Ä~š0+Å‘+Šy`  :öSëäI±d,(zİ Ì)†Ss&§í+²†`ÉÙIQPW¤P1dŒ·]I«H†/Eí|y‹3Ø©|/¬\$eL¸";break;case"lt":$g="T4šÎFHü%ÌÂ˜(œe8NÇ“Y¼@ÄWšÌ¦Ã¡¤@f‚\râàQ4Âk9šM¦aÔçÅŒ‡“!¦^-	Nd)!Ba—›Œ¦S9êlt:›ÍF €0Œ†cA¨Øn8‚©Ui0‚ç#IœÒn–P!ÌD¼@l2›‘³Kg\$)L†=&:\nb+ uÃÍül·F0j´²o:ˆ\r#(€İ8YÆ›œË/:E§İÌ@t4M´æÂHI®Ì'S9¾ÿ°Pì¶›hñ¤å§b&NqÑÊõ|‰J˜ˆPVãuµâo¢êü^<k49`¢Ÿ\$Üg,—#H(—,1XIÛ3&ğU7òçsp€Êr9Xä„C	ÓX 2¯k>Ë6ÈcF8,c @ˆc˜î±Œ‰#Ö:½®ÃLÍ®.X@º”0XØ¶#£rêY§#šzŸ¥ê\"Œá©*ZH*©Cü†ŠÃäĞ´#RìÓ(‹Ê)h\"¼°<¯ãı\r·ãb	 ¡¢ ì2C+ü³¦Ï\nÎ5ÉHh2ãl¤²)`Pˆ›5‹„J,o²ĞÖ²©ÔĞßÍÃ(ğ¹ÉHß:¤‚›–Å €Rò½m\nÈ—Q¬nÛ)KP§%ñŠ_\réª(,‰HÔ:»ëø  4#²]Ò£M.ï¥KT&¥¥ìPÂ®-A(È=.Ê€ÕÕ‚3 •¥_X‹°<³àS.ˆZv8jæŒªâ*¿³c˜ê9OÈÒ¿<¢bUYFƒ*9¥hh‚:<tÊ\"tU”1š¤B\näÅ»D¸J\r.<¸o+~FiÍ_%C’`\\ßëµû-è%œ‚`øIfáŒ8f	g1 RöôÚ‚@	¢ht)Š`P¶<åÈº£hZ2—É+¸ƒ\"“/DHj9j1ìŒlÊã0Ì6,ã,òûÉô®eKS:ş*\rè²V7!1ic>9ŒÃ¨Ø·ë4ê4ã–ªä,ğÛëZ«8ê¹…˜S	JVòRï¨\0†)ŠB3N7£KDLCÜ™ÂÌªS…8ƒ2Æ6é~m.®-RößÈ1»–’	F)V—¿ºc¿2£r’†(ã/!<,›â—ñÍú\\³Œá\0‚2mªå²sºR2>á\0yŠ0Ì„C@è:˜t…ã¿¤=ÆŠ9ËÎ®!{Ú´#³¸^ŞûûÚxÂhô·\"W\"•R¥õˆÎ¶%7UñÑq0Ú¸e˜j\$d•N<ƒöÀß¥ÔğÌ“Æy)æ<ç ôƒ»ÔzÎéì‡'¶÷S’tNÁ¹ñ“ |Chp.à:>—Ö™Òñ¨R-ü²4†ĞPp>íL¸‡GÚ¨‘Ù`¬<®N´ÈğbsÇŒ*6œPxh2¡„1¾êÌã_FV'b—•xaÅÌßµ†´ƒZë_\$MˆEƒPiâ‘)x3Œğ!,u†¶±XTˆHi\rå®>™e¼Á—Èv!\$´ºÂ€H\nz@\"\$v\n	ÓsA\r;”(ªí!¡™\r&lÎ—¤‚Iyª2è(– ïˆsu&ÎĞJ'DYœ€ rlà<ºÀ©2t0ˆµ¦Õ©g\r½u Ô•xw5!Œ4CRÊò••5T1›0æÑ”)¾0Œ’VKIyp\r!‘G–\0ÚÑ`l‡M„bMŸÓÕi\\ì¦×XHXy2êu D7Ü<p5G8‡S>‚ƒ1ù\r®Ú\r¼¶}C\"›ô(—‘<IƒÁ/fĞ\0Â¤ºfåÄÆ‚\0¦I\\í>3ö•9@İ=WÈk#\0•ÖMI­34ÄT7bÊçˆcTO½Ó1@éPÑ:1ÔnŸSFH­<V–—˜GÌá•3lT#I&á•y*AŞú\$CA÷\$†>J’¸ÌË”P0	v7’\"FŠ=z®•ô9¦å¤Öš3¤»0jª•b&*nÀ0»u+èi`öRÆ·›.bØÈC\\°ÑÒÃZh>¬T;Ê\r5ÍTì“ÚÃ\rIr\n‹s*…@ŒAÂjwµ3’K?a™#…1]4øGˆÅÊ#¬ã•ÄwtHÙşn„a©¥Ö*W)Ä.È¿‚J¤CM³TÄ_ÀÔJˆ°tµ¡É §àòW \nåñlâÆ[Ûm8§¾›Ì@Jàm80\n8\ni~ÕÁ›—.˜±©6‚`o«.B`šRÃ†gÑß^ä¹F%Tb\nU\nÉ—œ`ô×òîº¢…Yã5sŒÕğS#a–*—£ÌH°¡¤¢µ®èQ‘KPIY¢…g‘éwK%ã.h|KÍü1o*ö°j°ò„»Gˆèp9`";break;case"ms":$g="A7\"„æt4ÁBQpÌÌ 9‚‰§S	Ğ@n0šMb4dØ 3˜d&Áp(§=G#Âi„Ös4›N¦ÑäÂn3ˆ†“–0r5ÍÄ°Âh	Nd))WFÎçSQÔÉ%†Ìh5\rÇQ¬Şs7ÎPca¤T4Ñ fª\$RH\n*˜¨ñ(1Ô×A7[î0!èäi9É`J„ºXe6œ¦é±¤@k2â!Ó)ÜÃBÉ/ØùÆBk4›²×C%ØA©4ÉJs.g‘¡@Ñ	´Å“œoF‰6ÓsB–œïØ”èe9NyCJ|yã`J#h(…GƒuHù>©TÜk7Îû¾ÈŞr’‘\"¦ÑÌË:7™Nqs|[”8z,‚c˜î÷ªî*Œ<âŒ¤h¨êŞ7Î„¥)©Z¦ªÁ\"˜èÃ­BR|Ä ‰ğÎ3¼€Pœ7·ÏzŞ0°ãZİ%¼ÔÆp¤›Œê\nâÀˆã,Xç0àPˆÄ>ƒcî¥x@ŸI2[÷'Iƒ(ğçÉ‚ÒÄ¤Ò€äŒB*v:EÂsz4PŒB[æ(Ãb(À‰ƒzrä¯ÀTë;¯¨Û0 €P’ç¦Œ0ê…ŒŒ(òç!-1QoĞ›LhÖˆZtØjqÈÆ¨ÀZ–Í‚›¤ÉBBˆ)zÜ(\r+kˆ\"³”å\"ÕCÔ2Òâcz8\r2ûW\rÃ¤aDIõÈ@çÁéĞÒ4&öSà>Ê\rŒ3Õ¢@t&‰¡Ğ¦)BØós\"íN6…£ ÈV•²tùCd?X (ìİ'#xÌ3-£pÊ’Š*Œ›N“³/ƒ\"ƒ’ŞèN0šôê#sHä1¸Lûv6aSÂ7„')\nF\"ªŒ/S‚DË(­ìk©4HÚØ‰(¨7³\rØ†)ŠB54ª-àĞ\rœjY1ƒ\n÷Çm\0Ë(Ã;c=aLÄå¥'£’è›fÎ‚b¨)‚ÌXÈ8œMir„ Œ™9d˜³7‰Ç­’Ñ‡ˆĞ9£0z\r è8aĞ^üH\\¢mÎ€\\÷Œáz|Œ‹J*4­!xDlã# –‡xÂ6OÓ-h(ó€êÉ™\"aã“ã•?2ÒÚ’ÍÖVU«·c­”–Ùø7S9uš&òo{îÿÀğ|/Äü^ÚÔñÜ‡\$7rRº}-{”<hÃĞôa\0Ç rcBÑìã=€0Œ¨ÙgJBİ¾É„J9NV’\\B1€fÁ û†ÆBH êÔ“×öyœQ!„3ğäD¨cgè9†`êGİ‰©A¤:ˆJ}Ğ‰œ|ÍñÏ,’âáp.HPß“‚nIì5~à€(€ MZh()@¥i‚ÌNûACeY‚xr…Ù˜ ˆ\n Dƒ4LLéRËH“ø¬	yú‰&9´2ßá¯?kè‚Á³^ná<ou„ƒ dƒ@ie!™†waO¼q|çI£r\rÜ(N¢>0`Ê–™–ZŠ%EÄ½\0š+&gäà ‡C˜†\0PJ#D›˜Æ€cŸkHZÏí·,¢„l™‡\r±ÆÁw_ó«yÁ3›³`|SxP	áL*<øü‘úF\r%†PÆ­NawGÍ	‰8ENaÏYFÔ¤¥Ã@ÃoIQğŠb0F‰Rµh¡D&ERrOPŒW3 Ë`©Ñ¡´Š“Í9dÒÁÉ¹%PfAà­vAHm‹ôšHÅ¤fI¨5T	B\$ÊNØô_QÆ½BPWS•ùtK\r†ÀVËhcb°ÌÕc,úÈÙœmÄ	Î£8AÙd¼&‰¤Ò–dhB F à’Ñ³‰(¸iŸAÉš¯ ­V™B5†º-µ–¶uU¢ùMœ:p¢€\nc'ğ¦€ÂïÃÀÇÓ2KHÌojiÔ¢'	´×šÊj…Ô­Å\\Q•AkFK6†WXtŠ˜>ÉÈè´…™QÈˆT:[&Ö8`B)/@51A¨Íy l´šU7á(K­§;æˆ——R¯ÍPs";break;case"nl":$g="W2™N‚¨€ÑŒ¦³)È~\n‹†faÌO7Mæs)°Òj5ˆFS™ĞÂn2†X!ÀØo0™¦áp(ša<M§Sl¨Şe2³tŠI&”Ìç#y¼é+Nb)Ì…5!Qäò“q¦;å9¬Ô`1ÆƒQ°Üp9 &pQ¼äi3šMĞ`(¢É¤fË”ĞY;ÃM`¢¤şÃ@™ß°¹ªÈ\n,›à¦ƒ	ÚXn7ˆs±¦å©4'S’‡,:*R£	Šå5'œt)<_u¼¢ÌÄã”ÈåFÄœ¡†íöìÃ'5Æ‘¸Ã>2ããœÂvõt+CNñş6D©Ï¾ßÌG#©§U7ô~	Ê˜rš‘({S	ÎX2'ê›@m`à» cƒú9ë°Èš½OcÜ.Náãc¶™(ğ¢jğæ*ƒš°­%\n2Jç c’2DÌb’²O[Ú†JPÊ™ËĞÒa•hl8:#‚HÉ\$Ì#\"ı‰ä:À¼Œ:ô01p@,	š,' NK¿ãj»Œ Pˆ©6«”J.Ò|Ò–*³c8ÃÑ\0Ò±F\"b>’²\"(È4µC”k	G›¬0 P®0Œc@éÁÀP’7%ã;¶Ã£ÃR(çÈäÄ6€Pœ¯£º¢•Ñ!*R1)XU\$Ul<Èí\0¡hH×Aˆ-'îZêâ+è§!¬Š³#9@P‚1‘%ÚB(Z6Ê‹è¬Ş£3’8JCR…K¼#’±¹•€ËkÛ.=,I’iW¥7]°Ó*n%át&£pê	@t&‰¡Ğ¦)C Ék¡h¶5bPºÉK#r¦ÿ.V…’æ\rƒ¥Ì ®¢X7ŒÃ2<½¦¢šâB­JÒìkCl\rÃÊ	‹£Æ’c0ê6ªô9º8öl0Œò¢Š½*‰HÚ½©XP9…-Å:Ïã8@!ŠbŒŸ9apAr¤£¨êã èÌ»'hò6\nèËR¦¹pé˜8MCx3Áìc8øª{[:Ä4è@ Œšzö9:#ğ4¸Á\0xëpÌ„TpèİAx^;ôrcÅ!¡rì3…é˜_\0:ÖŠÄ„A÷X¡»áà^0‡Û­Lÿq\nXÙ¸‰|ŒšşÜÏn•J¨°)fğÃ&óãw°k‹„«ì\\š\rËs×9Ït¿EÄ¯]/OÔİL?-NÿhDª‡ÂHÚ—³Ü:vİÄÖ˜ˆo%&¨-‚©Í\rf„Ì:2–xˆ )?(‰\\2¨Å¢g’	#æ*pAŸˆ <f£@äà“)!˜•œt\nÎ™ã>h\r¢3bJ ğsQ°1¢åä´V*1/ç	:¿ËÊ)Ak\$°(€ ö±\$EJ4÷#qÁAUj (!–\"ö´ù>4FÓRÃt*fÍÁóÌK}#(¼<â6wÌbÄÖ¬’¢TÎ¸sALíæC‡~ƒƒQ4H-ëîl”l/\n:›x\\ e&¤Üœ“²z§JîT‘¢ãİ[\r\r5­RjH y3GIÈÚ‘I±6h¸8‡R1L'®!Òy,QC/?ĞàÙ ¢8MB€O\naPšâMÉQ,)e6T.PÖ\\šb•gd¾yk5»\$]ÁÊU‘³¤œ\n›\rÈô3¶wi&ŒÄ³h\\ÖòcÉxL(„ÉNi:á*Dò¶T_º0‘VVéT’¨gŒ5fúhÒœ#RÑ—òœMTy'íğš«\0ş`ù'¤gö¯ENşd2´š„6<\\ßBa¼8!Ó„âº¢¤È…M“bŒ¸F¡ì’ÂV˜B F á'¸äş§ii)2°Š‘Á†+èŒi 'Sc+QÒyµ´’´„ÕˆÑA#ä„ÁÚœÆ+EÇ0&À ÒƒÊÎdÕ™ª¤Gg¹\$iyA£yŞĞKÑ!VLÅ\$ØOja,©Q!Ú\">£©†	„Á§cş~ÀU«&'|”ËŠ,O€(+!’äÖL¡\$gä˜¡‘\nÄGëq2,©Œ’µB'èigd4W²²,’®¬m_¬…8OÜV?Æ0Ú‘Ğ®QÌ)(©ÙÃ”LÀ";break;case"no":$g="E9‡QÌÒk5™NCğP”\\33AAD³©¸ÜeAá\"a„ætŒÎ˜Òl‰¦\\Úu6ˆ’xéÒA%“ÇØkƒ‘ÈÊl9Æ!B)Ì…)#IÌ¦á–ZiÂ¨q£,¤@\nFC1 Ôl7AGCy´o9Læ“q„Ø\n\$›Œô¹‘„Å?6B¥%#)’Õ\nÌ³hÌZárºŒ&KĞ(‰6˜nW˜úmj4`éqƒ–e>¹ä¶\rKM7'Ğ*\\^ëw6^MÒ’a„Ï>mvò>Œät á4Â	õúç¸İjÍûŞ	ÓL‹Ôw;iñËy›`N-1¬B9{ÅSq¬Üo;Ó!G+D¤ˆa:]£Ñƒ!¼Ë¢óógY£œ8#Ã˜î´‰H¬Ö‹R>OÖÔìœ6Lb€Í¨ƒš¥)‰2,û¥\"˜èĞ8îü…ƒÈàÀ	É€ÚÀ=ë @å¦CHÈï­†LÜ	Ìè;!Nğ2¬¬ÒÇ*²óÆh\n—%#\n,›&£Â@7 Ã|°Ú*	¬¾8ÈRØ3ÄÏ¶Ãp(@0#rå·«dÔ(!LŠ.79Ãc–¶Bpòâ1hhÉ)\0Ğc\nûCPÂ\"ãHÁxH bÀ§nğĞ;-èÚÌ¨£ÿ0˜ÖÅ<£(\$2C\$¹P8Ù2¡hà7£àPŒÅB Ò›'õªú¼Œó#ÔĞJmw¨-HèPôËgËÈ*–2ZtƒMW‰Ğš&‡B˜¦zb-´×iJÓ¶5n>|,Dc(Z™hĞ-À²7 ƒ”3ÕšªÀ¡R¬&N\0ëS\nƒxŞNÓú*ıŒcî9ŒÃ¨ØOrÀXÏÏí°Â¶0ª%6­˜˜ÊaJR*ŒãÈØ¿.A\0†)ŠB5ö7¡*`ZYtä‚cPÊÈ°hÈÏ§6`Pª:OVLÆH\rˆò„0iH¨42Ik}‰ Ùè‚2f¸å“ŒrÒÆ !àÂ\r	ğÌ„CBl8aĞ^ü(\\Åí«ÒĞ3…êX^ú­©œ´„A÷ øLaà^0‡ÉI†L\rnv&6'cƒ£Şãt3ÁzÌâ‹ƒ†7P¶êV¨UÔÚ9ZÈGÒÚaí¼o[æü:p	Ãqg9qœt«+Ë#w(¢‡ÂHÚ8/1|ğ…s\\ä!¬ŒfÏ)Í#‡©§m[ÿÎ¬’—nóıZ&0áÑÓVÕXuŠ\rÙ\nVÄfPˆcrE0;“DFXr}!É¨0Â–ËcÁÍ2&HZàbÃ\rÊ0Â‹Á\0c8¯Ì4–ä4aË™LbaÙòƒ¢5„:µˆù‚€H\n\0¶ÃT.kAE%4€¯Æ¦RË›«aa\r-xÖÑ‘Û\r0Ñd×bPå›Ğ_Y»9gmm9:äÆÚ`™‘a©-@r¤FMwh2Ğé`pbnÅ\0 7x¸h\r!³\0Îß |\$#Œ0‡R‚JIYO%äÄÉbIIøtM07°è\nİ;\\)n­©Ô2JBI,#hÆÏÒÛ„‘ˆŸê}Îpf=„Åµ¸˜šfJ`cOE]š\$\0\\ÊA¤#æ2/W¼ß0ii9¨<Á%ŠÕ>3\\ÆæN^\n®öH\n†AhkKD˜3‡SÛ‘~'Î†-¶AY’#„x›’\"H]&™\naD&ÒLÈá£wÁP(#4âöÕCî.“\n=0Ò¤ÕB‘‹.…9‚@·iå‰ˆÌ‘Î\$P^ÌZR¤‡Àç'\"R¢4ĞĞ9•¦µiÉC€´òŸ†ˆÃ`+\rh\$„;†]q~lõ<ÔÑ¦ªRM“û4ïõ‘‰´B Fì2†sÊÙàg¦êÅ;Ò3F\rı/¨´¡•:à—–èK ERU+r0|M²AªªæÚÑL`\n/„v|6àqKùÖ<ˆâÛ\"M”Ø#LÒšu·Uüªw¤¥›JÆ'\nõ:nàÀÃPAH'ä`Ÿ°Ø|i:Í­µÎ‘`¬[ƒ:¦	ié5RZ®Íùp®ê¦WN&ï6¬è:ÁP»‹ol…&H¨2€";break;case"pl":$g="C=D£)Ìèeb¦Ä)ÜÒe7ÁBQpÌÌ 9‚Šæs‘„İ…›\r&³¨€Äyb âù”Úob¯\$Gs(¸M0šÎg“i„Øn0ˆ!ÆSa®`›b!ä29)ÒV%9¦Å	®Y 4Á¥°I°€0Œ†cA¨Øn8‚X1”b2„£i¦<\n!GjÇC\rÀÙ6\"™'C©¨D7™8kÌä@r2ÑFFÌï6ÆÕ§éŞZÅB’³.Æj4ˆ æ­UöˆiŒ'\nÍÊév7v;=¨ƒSF7&ã®A¥<éØ‰ŞĞçrÔèñZÊ–pÜók'“¼z\n*œÎº\0Q+—5Æ&(yÈõà7ÍÆü÷är7œ¦ÄC\rğÄ0c+D7 ©`Ş:#ØàüÁ„\09ïÈÈ©¿{–<eàò¤ m(Ü2ŒéZäüNxÊ÷! t*\nšªÃ-ò´‡«€P¨È Ï¢Ü*#‚°j3<‘Œ Pœ:±;’=Cì;ú µ#õ\0/J€9I¢š¤B8Ê7É# ä»0êÊú6@J€@ü¸ê\0Å4EƒœÖ9N.8ğƒÃ˜Ò7Ï)°˜¬¸@SÁ¿/c ¾ˆûÒ\$@	HŞİƒxÎãON[š0®®ZøÖ@#˜ÕK	Ï¢È2C\"&2\$ÌXè„µCş58Ue]U2£¸¾=)hÁpHWÁˆ)C¨ÖÅC8È=!ê0Ø¡½\"œÂSúê:H†ù¡2äc¦4Z#dŒ0±C¸Ç\"Æéí°Ù%&!)QM€®”i\r{iJ<§Õ-Æ0Ü¡p~_ÏœY€àw*kƒ7éán>‘&È::÷‰@t&‰¡Ğ¦)P˜Úo»î.B€ßp<·\r“Ê‚ èLÖ3É>›\nq:h9=T&Ã6M2•¥£«ÜŒcB92£A£>ğÂ#æªãAoœ‡Jxªêâ^\r¤ŒšZ®2éÈó“©kÅ­¬;¨ş·®ê›¶Á>Q)ËV„8êámjÚ˜éˆ~ã¨n›îïIk;Ö¸9î£ÿ±%«šƒÂpÃ¢l'!ìàÂ‡”pî)ÃZ b˜¤#Á\0§Ì¸İ^\$0Ãƒ3ò6£`Â÷!|Æ›^ûÆ)·Â~‡¿vc–’ o=PûÊ@ Œ›<O½Äêc©ğ±2ŠĞÈÁèD4ƒ à9‡Ax^;ÿuÑï?OÈÎÃ€læŒ Döx\"ÆŒ¾¡2xaÍ\0‚†¢‚êÃ:bmq)´e(P»ap\$Ôb·\nAJ\rh †rR­Pú!(Aæ»·júAë¯µ÷¿æı_»ùiY‚çı\0 eNäA='ÈTñ²\r!¶\0’â aT‚E	0Ü¼œ(e)Á‘•¸_Qùtğ€¥BXPcQ)„‹ÜÁ’€‡H2kLH:Á’1MHw@\$lB!'Â„¹Ca&@D09;àä’U@aÌi†0ÆAC˜fx-]I%i”¨@AÍL  Æd\$W.Á°9™‚ôcÃz%H]· 1EÚèCåÎ\\K©xŠd…C¼˜“2ì§×›sx«ÅyËÒ@¥_9r?áê04ù¤\n\n€)mñá3¬&NÄ\$¢y%\" H\rÅAÑZ¡ƒ8@Hrh\rNViHòKMzB*EŸ¤gJgCK©%DÑ™L7”Ş¤<‰2ÈG'Íğn5R&Œ–´ƒĞ‰“(A 4µ\nC;ğ’TnWÙ	‚(\rgá)8\nNIÙ='ñ€‘% ÅL©h‡=3œI°J!ğ×Î7V„WôrgF¬Œ&\$ø@‹“Üí)†ÓäMaNMgÈ7¡IvîØPz\$ó•·Mæ¢íBĞm+u7†%>&Jrİ)U¹©ÀêÕ¨ \$èÛR!Ä( 8š”R:ÃÈj± X+	\$Ê©©\naD&8RaS*8w#ğ¥GtšÅåÄ`¨œ»U	N) ¹æåH{'AHÈ¡\"æùKá)Cˆ© òM­â¥·ñÄA9àÖa	ğiSòä[Ó&š®Ì@sx›WGé³„TˆIµØkÀÂ¯Ö0W–ïÀv\0cïRµ&Á\r1ÀVÊ©5‡vÛ×¨ÎIÈ`98î”’lÌM¸³1Â–ÃRIĞál/„9> (îB F à›^›•vn\rÍ¶‘Q]Ee|-X¸˜AU†	‰-»yaøy	O\\A‹/¶Ø¹Vú€›Š#	â†\":‡+äÄ?Æ¼ØØcÉNƒ,Ğj>Ş<#N[°Ô\nªÖ3âHqÍ1¨¢¸\0ÛÎô8Ú_=ü¯0¥ä%#bäyË“æ²¾¬ÜpÑn\nuX \0¬Vùê=’ğ¡ö\"YßÆ†OŞ3&tmñ2Æí&H»¦“B!›*«P\"“¬v‚N0[ñb¬{ö€ƒ¾† Ú&“d¼¬±®xKV’Ö¢ƒ£Œ¦“»Ó á\0";break;case"pt":$g="T2›DŒÊr:OFø(J.™„0Q9†£7ˆj‘ÀŞs9°Õ§c)°@e7&‚2f4˜ÍSIÈŞ.&Ó	¸Ñ6°Ô'ƒI¶2d—ÌfsXÌl@%9§jTÒl 7Eã&Z!Î8†Ìh5\rÇQØÂz4›ÁFó‘¤Îi7M‘ZÔ»	&))„ç8&›Ì†™X\n\$›py­ò1~4× \"‘–ï^Î&ó¨€Ğa’V#'¬¨Ù2œÄHÉÔàd0ÂvfŒÎÏ¯œÎ²ÍÁÈÂâK\$ğSy¸éxáË`†\\[\rOZãôx¼»ÆNë-Ò&À¢¢ğgM”[Æ<“‹7ÏES<ªn5›çstœä›IÀˆÜ°l0Ê)\r‹T:\"m²<„#¬0æ;®ƒ\"p(.\0ÌÔC#«&©äÃ/ÈK\$a–°R ©ªª`@5(LÃ4œcÈš)ÈÒ6Qº`7\r*Cd8\$­«õ¡jCŒ‹CjPå§ã”r!/\nê¹\nN ÊãŒ¯ˆÊñ%r‹2ßÀê‚\\–¥BÙC3R¹k‹\$œ	ŒËŠ¬[i%ÌPD:ÈãL’º<‰CNô¹Ò³Œ& +¥å Œš}‰ÃxìŒË¬ûh‡\0Ä<¡ HKPÔhJ(<¶ Sô¨^u˜b\n	°Æ:ÑÀPâá•ú\rƒ{½‰ã”n¼¸ÓÈÚ4¡ P‚ë;šJ2Œs³\"…©àÒ½ˆ’ø‚®rä Êä \"¥)[S¤öòL”%Q²oST(Ão¶W¢W!'ÎºG\"@	¢ht)Š`PÈ2ãhÚ‹c,0‹´K_l¹®Sq!CcÄ4m*Yã0Ìõİ)Å¬9%RRrƒÙöb&Ø¤(Âr7¨	èó2C¨Æƒ\$0ê“X«»\$6c–_oêğ§Ô9­2…˜Rœ\nƒxÖ”¦)Áğ;(OZeêğCK Û£¥‹‹T·IÎpË—gê9f±²1¾0nˆ9¦éËNü6C4;:Ã8@ Œ™ª¦pŒ”ƒa‡ˆ ĞÎŒÁèD4ƒ à9‡Ax^;õtmË@+ Î¥z¼¹îh^İ»ró‡xÂo‰ú®	¯¦AğÔ96Q°<o»ñ\"k\r¿Â))e/ŞK;ëMĞt]'MÔu]`ï×r«Âv] İÚ¤íİªğ’6\r~òÃMà'B~¨M¤-}³ Äˆà	é(„áq³wŒŠQèe\$„Ø˜#¤sËq‡qH0†7rCºß\$Æwà¡Ï`9´'Ñš@oi@‚ÀcvÏªN ¯~	°Ùˆ±—\rÅ9’†r\\‚rŠD\$Ü/×ë‘B\n\n ( Ô`¢¨ @’r¼ŒLXs#É\0’ÄhsWL&<æÌÕšÕ¾l_6Fôı Âô±C¹Ô'¾\"˜ĞÂµ»gm)0µÂfg[1²R¡Â v§¤°n\nì¡4+\rá2€…ÙÒ*\$&(aCÍì†s¤cÓ(%¢ÅDcŸá…®h\$tW1‹`\0Ô6¦Î’!‚I¦’\$M\"H(-›†èo\r‘XEy\rbå„??¡b¡øod g•@'…0¨ˆÒš7'‰Õ{\0Î•¦³{	t2RKñ<'ÄfhÊ§ÿ	&‘„Øæâ`Õä‡:æ]²I¢cé¶X¨Øª\"ì˜Q	ˆj;‚ê0T‹¤õP”X¿fë…;Ä‘\0€¥:ŠÙëp-®›êqQŒ&+ØœSs@šé”§‹Áh°Â“D_6'C*¢À\0PC<A°Î@Ò°©1\nAN 1I2[–)-’‰X“§àÂV*,5ğ ÈXÔÂ¨TÀ´pÜá[‘'ÀÎ©â3K‹¤`^öŸ÷.HBö‰ô§#ÄëY»[GH£É‰1a˜<°å°gC™oTöĞ-H0Š5£¨g*¨PôÊ!x\ruÙêÔK!zoä±<ú8Q¤¸0@(&\\kzBiHäa‹¦DQRá±hö(æ?Ã^§ìe†ªvœE¬ÆÈHd«+fñÏuƒÊàØ«ôÎKXüº/&ÊÏR)m=°Éğ9¬\$E\"…j&à";break;case"pt-br":$g="V7˜Øj¡ĞÊmÌ§(1èÂ?	EÃ30€æ\n'0Ôfñ\rR 8Îg6´ìe6¦ã±¤ÂrG%ç©¤ìoŠ†i„ÜhXjÁ¤Û2LSI´pá6šN†šLv>%9§\$\\Ön 7F£†Z)Î\r9†Ìh5\rÇQØÂz4›ÁFó‘¤Îi7M‘‹ªË„&)A„ç9\"™*RğQ\$Üs…šNXHŞÓfƒˆF[ı˜å\"œ–MçQ Ã'°S¯²ÓfÊs‚Ç§!†\r4gà¸½¬ä§‚»føæÎLªo7TÍÇY|«%Š7RA\\¾i”A€Ì_f³¦Ÿ·¯ÀÁDIA—›\$äóĞQTç”*›fãyÜÜ•M8äœˆóÇ;ÊKnØˆ³v¡‰9ëàÈœŠà@35ğĞêÌªz7­ÂÈƒ2æk«\nÚº¦„R†Ï4	È†0Œ‰XÂ\r)qŒÌ¨‘\$	Ct9ªú½#%ĞÚ…¤O\\ç(”v!0Rò\nC,rã+æ÷/±ØˆÏ¸ò°˜¦ĞÄÚ„\\55ÄéXæ¼²éÈ˜Ï±H»\"©/¬‘-/BšV×B+£+3b`Ş¿¿êxäÉZŒ\rêüºÒ¼«ĞJ2òƒ4ıCQ¶àPóT¿€PH…á gX† P ÓŒc­&Œh…„bÁBxå°4jŒ© P‚a”ë¶öŒsÃ(\"…©èÒÁ½–rF:º+ĞƒJ–²•20ÛpØŞ4¥Ib\\¤…Õ-AuÛmÀÌ¾'—Ğ¤±àPÂ3ß€P\$Bhš\nb˜2xÚ6…âØÃ‹Œ\"í¦ÚÚ®ƒªŞà\0PØòMSX—»ÃxÌ3\rŒ\0Êã!ij’å]ŒØ¨7¨)ğó²ã¨Æ…\$c0ê”ØT€æ £–wİû8Ô\n‹¦ƒ˜RœæãZVb˜¤#;éb‡–ŞÎ—<wu'¡¥ëËb»Iî^ó¤­íBĞÉS\$Şë\rsõ}CÓ¸Ü3„É©!z@ÆèNñÊ`-kF3¡Ğ:ƒ€æáxïÏ…ÑÇ¯ƒ8^•…êK©ºxDuMóÒã}˜\$l*È83QÀÃ¤BÜ:˜h£¨ÊÄæáKnbkƒ&„'\nØì7xÑÂá!É¼§-Ìs\\ç=ĞtYwH9tİDêËÏr°	#hàÚî#§iÛN-Ä:(\0èÎZÑßAªIJ)cŒnËá¿r&aÜ¢´~M·=æ´Å­³&½	Ğh;Á„1ºÃşÖÙ)1‰9/µ&—\0aÏA3ónÚDQí>?Ã€wºO!/aKàØÀHÉ\rÍõ§s F:\"r(”Ã\"‚I\0P	A!AX\$¨³#3\$Én&Ä¡+OI¹\$Ím›eAÁÂ?ˆ4Á, î—‰Éñ‡©1† aL\0vleP0·ÒwzD)°ÃvÂ¬zhXs‚Ã@ *!Ë·yPûs\n'PèÈDs&ŠEDr>¿\$‰\n’CDÁá½K ]PÒ&äÀ0¢'˜şcÉ&­DE‰[ş8&àÑ«òÌ†Í9pÎŸğÆ°‘ü8OH3·0 Â˜TD‰M“ÔîàgJÇe—‚º©&'\rJbÉåór…3Ñ•2ì‰ó‹\rÉ3«¨ü´Lê-d³>Ÿóv°‘ÄûEø0¢Üm `©	ò (ÑZMç2‘'@kwDøi×rğG&Ö+“ŠX¦éy_kÀßFZC ÀO¥ªpÓ¦ºvØO\nC'*™fŠ\ntè^µ2'6¥ûT)«g”6¹®Uù\ráˆÑÏ2ì°‰/r)xÉ‚Õ„NB5\r¨}Â6B F áx¸³\$àêZ 4u.ó¿+ˆeR^ÆŠ—d‰	—í#3¢ R<H	w\$ä¤Ì1Å­/;1±„ĞŞdƒ0yaá”1š0æB›’\\1±ş\0ªØ{Ï©S§ì¥`˜\0ÖuŒiJÒjÓ†˜øu`‘Ş¬åñ=ú\"Që)½1@)G#ƒÒ­ŠGc]22Œ^’êHds ç¿*h§¬S¶.ÁÓ’.¡˜á€(!¬%’¢CÄV2Æı°„‡ãé0d	À›‹DcH¾´–ÊÂ'àæ¯ÑkĞ";break;case"ro":$g="S:›†VBlÒ 9šLçS¡ˆƒÁBQpÌÍ¢	´@p:\$\"¸Üc‡œŒf˜ÒÈLšL§#©²>e„LÎÓ1p(/˜Ìæ¢i„ğiL†ÓIÌ@-	NdùéÆe9%´	‘È@n™hõ˜|ôX\nFC1 Ôl7AFsy°o9B&ã\rÙ†7FÔ°É82`uøÙÎZ:LFSa–zE2`xHx(’n9ÌÌ¹Äg’If;ÌÌÓ=,›ãfƒî¾oŞNÆœ©° :n§N,èh¦ğ2YYéNû;Ò¹ÆÎê ˜AÌføìë×2ær'-KŸ£ë û!†{Ğù:<íÙ¸Î\nd& g-ğ(˜¤0`P‚ŞŒ Pª7\rcpŞ;°)˜ä¼'¢#É-@2\ríü­1Ã€à¼+C„*9ëÀÈˆË¨Ş„ ¨:Ã/a6¡îÂò2¡Ä´J©E\nâ„›,Jhèë°ãPÂ¿#Jh¼ÂéÂV9#÷ŠƒJA(0ñèŞ\r,+‚¼´Ñ¡9P“\"õ òøÚ.ÒÈàÁ/q¸) „ÛÊ#Œ£xÚ2lÒ¦¹iÂ¤/Òø1G4=CÇc,zîiëş¬À¬Ã4¼L¬BpÌ8(Fë¨ÂÏ C“:&\rã<nœ	šŠ7RR;J¿´\rbºœANûJŒ”D­@6„Å Pò¬PP¡pHÚAˆ!¡é\r^»¯(éDÛş¦Ç 0(¦Ê¶¢(\ré„×vJĞxÜ4¥\r(ˆœ\r•8¡Z¦‰ô„ò#ŒŠ`ÅKÍÉˆ)lVÈaNMŒ¢·p £c6àb0¶&÷\rj×R¨ê6B@	¢ht)Š`PÉ£h\\-9Èò.ºW£6ôCe6(İ_DÃ0Ø½²ÙèäÍJ˜¼€P¨7·˜ó4¨Æ«c˜Ì¡•kàcšØÃWF1&ï a@æ§¢¦)Î\0Ş5ÒA‘¡#*O\nÍ'Ğä¢ª±nı©³A\0ÆÓëêÂz*6BÅFHKì*^˜9mÜzÃ«ÁâX4<ƒ0z\r è8aĞ^ıÈ\\¥)• ä/8_IğÂù/»xDxÌRö3‡xÂrûú†Ğ¡(|GCÀá'1[D3¡¸ìëô×nƒBñz‰°«öÄq(å`¬^ûÉ„P@ë+®vÉÚ;gpîã£xñá†ç†á	y ˆ©à’›Ìª OEé•œŸÎ8oX'ö·ÆüˆUaV„æ•€Òö‘Ê¡ˆ:“'Êz‰r°/Ü¹pĞN«È0!ÜŞc&MÃ“ŠOë0†gæcœ«aldÙ²†tc9À‡ä ¥'ö­CI}R¦™)‡»I”d+,ÚÆº÷dÜ)zNêáVÀ€&èµÎâ´JÏ=½ÒnĞ\"ÁCáÈ:ã’†Èàll¡Üğ“Ò4ŸÑÊ]T\r-QàæHÚ\"Aëö2˜°ÎyÊ¶‡Æ‰¿âXÑWoÆã“‚öHõDÀ€;œ€Æ\n!\nv&œKW%(\r–dä¤¡”RG’é%W&µ.Ô>½ã|@aÅe‚Ÿ\0\\ˆj/…Ç\$<EÃÉ²}«HİÎAN<Ä:Æ²ZƒÈó¡w¤Î-7ĞÆÙP±Ç9(ˆß?°CQC\n<)…IJ¬‘í<“|*1û¡¨µZ!vÚf/.:QEÕPİ1.!iQ†RfO\$ƒ'hş1´Y`Ne)OqÁ>@¦Ba[G&ì–`¨äïoÉ½ByîŸ‰ËO'€)\\H¬VR£%Œjn/#Fƒ™eª†«\$j³6ªáZšH(ÍV*ªjºUá…CÕ¶6|	™Š¬L6´”HDIq+ki¹¨e˜»ca¶¿Ø?a\rRº5ÍÕ@†ÀW?×Àk&ê búÚ:±U-ÎXÃ3c’\n9\r¡ÖnDtbB F áE³2}‰êÆSI`·øôÅ­Õ¼du.E™{í\$HqŸj?mÎQ“.Á6Æ‡–dV\reØFªq</ÔZ@Qá3Kl+YûChY.¯Ö|W£ÔäÈå	LeÀ!¨ B®ÓXğCÆ»RÖ`ØÆqU:¬)O<*H¬_\0Q5MÊ«•£È°‘Š&¤M`Ø[\$y.²3Rğ.³*°Ã\"×lªĞè„W¼ˆõL‘†t!Ê÷4®ŠìjÆHÊV9¦Wošgç\\à‡@";break;case"ru":$g="ĞI4QbŠ\r ²h-Z(KA{‚„¢á™˜@s4°˜\$hĞX4móEÑFyAg‚ÊÚ†Š\nQBKW2)RöA@Âapz\0]NKWRi›Ay-]Ê!Ğ&‚æ	­èp¤CE#©¢êµyl²Ÿ\n@N'R)û‰\0”	Nd*;AEJ’K¤–©îF°Ç\$ĞVŠ&…'AAæ0¤@\nFC1 Ôl7c+ü&\"IšIĞ·˜ü>Ä¹Œ¤¥K,q¡Ï´Í.ÄÈu’9¢ê †ì¼LÒ¾¢,&²NsDšM‘‘˜ŞŞe!_Ìé‹Z­ÕG*„r;i¬«9Xƒàpdû‘‘÷'ËŒ6ky«}÷VÍì\nêP¤¢†Ø»N’3\0\$¤,°:)ºfó(nB>ä\$e´\n›«mz”û¸ËËÃ!0<=›–”ÁìS<¡lP…*ôEÁióä¦–°;î´(P1 W¥j¡tæ¬EŒB¨Ü5Ãxî7(ä9\rã’\"# Â1#˜ÊƒxÊ9„hè‹£€á*Ìã„º9ò¨Èº“\nc³\n*JÒ\\ÇiT\$°ÉSè[ ³ŠÚ,¢D;Hdnú*Ë’êR-eÚ:hBÅª€Â0ÈS<Y1i«şå¸îfŒ®ï£8šºE<ÃÉv¶;A S»J\nşŒ’•“sA<Éxh‘õâˆä&„:Â±Ã•lDÆ9†&†¹=HíX¢ Ò9Ëcd¾¹¬¢7[¶üÉq\\(ğ:£pæ4÷sÿV×51pŒ¸ã„â@\$2L)Ö#Ì¼ª\$bd÷×Èj£bšıeRà­Kñ#\$óœ–¼1;G¼\nsY¬î¥båc½èĞ¹(ÈÕ§I¨•e‹ëõ—åfƒY™1/}ŒXdL`¡pHèAŠ3‡Y\nd†ôÕävl¼—‰U¬ÏG&„˜Põ.3jjèØÕ®/Ä(©#+A V¤Av’ïÖ*šÕjŸaªè¥Ñ×¢¢¯¶J¥4h§+í^Eèğ\ru_Z\$Š¨‘Ğ0óã¥\0¸æ®ÎQƒ)åğ\\šrÅÈOÏ¿)rÏw1ójrAÏô<z÷ÉU°[à†õY†Ní©Ê?y>YO3\\áÑ ¤“4\0P£(ùhuÅà\\-¯EŸª.È™´\rƒ å\"6Ö\nÅW\$o›ù`´p•ç!G³>8±yEÔÖ®¥@/\\Œl˜¶”lÍªô9\n¬ûœtú\r#¯%M!ÚªTŒÓéL=Ç\$‚,¤xw#ºkåLAœ±ìóQ‚é¨?x&õŸÑB#ÊÉÿ%\0¨‹€§'ñ`Û˜iyr‡\"•X\"P`™á‚¥†Á–kÈ|&Â.Çé	³®„îÍıÂ³•a”UĞœÃCúPÔ7pé½¨ b\\@dq’·Vë¤ITLõ\r6%>XQR4ÅŒ!…0¤l3„‡Ê1g&í£Ãƒ'\$”†,1J‘^Q\$¬ºgW¡\\rğÂGboó(€ ¨\nKL™qUŞƒ8 !6†Ü”` kÅw†D¤àa 9PÌAhĞ80tÁxwš@¸0ËIl”rUà½v‚ôÂ¹¢ğ\rÀ¼é¾–e°g€¼0ƒåF©`<u‡ÑÜš1Ã¶`Ê	ô\nçã‡.`â˜rğJ5¬x|qXéL”MØ‚—R’¢£A«Ğå1(§ğºmÓaÌY2f\\Í™óFiÍYk-Ò´Ú›“u.Åİ8§!rÃA|•š¨9	sµû˜ÃH\\Cœ¹ĞÍ#Ir‚GÎáDòAü¶ö.IDy`oIgÏ‚›>ĞÒ F˜ÕÂã¤x1QÏùÜéLøas…x‚\0îC`l‰/‡¥+m¬¬0†eØ“@ciŒ9†`ë\\ƒ`oòÚ·èKër½K©‹/ƒtá!°9—SªyR9'QˆuG•Xè„çé;.©'ä5Ô\r¡~–‘0*nÛĞÕH(hXú!ó¸°âÈ È”p†¼e½k6D7‚\0àƒHv®”3×»œ›\"ÛL¡½3Ù»ëİ!®œè*ö\rÔÓ^¶Ï‘X0ƒ•(ØZ‚|ôR2°R)Ò’_»S9¦ë,e’À¸8TÚ›ÓˆreaÜ4Çd%¨g™UÍ/à Æepe.ªô’™ãèõ£“9~ä„¥Ï—\\HX+ø½\$°ŸEwp	TP‰ĞY±“ú³jW¤\0·Äî’¡FQ*IÚn\"1>©\"2%Jd 1J±TâNèé´û*É¢Xv‰&¾JYÒyq•Ú¥~Ö)²l‰NĞ†¡¯ÈíB8JÓÓÆDQ‡ «Á¢\"›° \n<)…J&pœY‡\r!ú?8ÑOJr)¹ÖgyüÔc\rR…ĞúJbûq™(O·Ğô-lÏŒKÉ'8|Â6‘SNA²?¨ÛEùoH@’ºÅ×…3¦òº¦Å×½Ìâı3  \naD&H\nUIä*€uÙò)éİ¬(ÖGRÈl((yöDHnFD¹-sF”¿ÊlöøĞÜ'a¢ˆxş¾è.»­İ’q¸ÖP=j”Ly‹K«¢-‡­Aºäoº#~¹Ü{ÙØp,•ÁK9`>ZÅİp¥ê.8k²r<C£uÅwõVºÍñ°Kk¡ÂŞ\r€¬h\n˜ò² 3R…Åw\"æß˜1mdQãDGT&ãj-LÛTßƒrrP¤Ô®¬äÊd‡UØeWÚf2Õ±”„H0@B¤€	Ñ×mA\0‡:¡-Å®®~Ï^èÓ_½­v\"öÌ;)y“®Ã´À-ª‡os—î\"2Ñ¦z¥ÎÜ˜ìÂ,¤]Ò\${½*ÜÓwšHÿ&\\E#äî¥ê)É¶¢9¹˜“Üı9ÚÅC+ï¶mt|À6/ğùNóˆYês«eï-C:at×6ŸÊN‚„*ú'”Bâ0a½æKI_ Ä³Ã”\$b\$|F’,MWz|’bK!#x+]Ò@fÜS–4«b1%<†í8`§éºwµ¯‰4Ìİô™ÿ.ãËhíŞ(Œ‰¢ØÕ-¬½ÆÔmŒJîÎ¾O\$íInˆri\"‚!dñ£ö~mN9A\nn°&Í/¾jÏŞ8dˆ¬ƒB‰Ã¤";break;case"sk":$g="N0›ÏFPü%ÌÂ˜(¦Ã]ç(a„@n2œ\ræC	ÈÒl7ÅÌ&ƒ‘…Š¥‰¦Á¤ÚÃP›\rÑhÑØŞl2›¦±•ˆ¾5›ÎrxdB\$r:ˆ\rFQ\0”æB”Ãâ18¹”Ë-9´¹H€0Œ†cA¨Øn8‚)èÉDÍ&sLêb\nb¯M&}0èa1gæ³Ì¤«k02pQZ@Å_bÔ·‹Õò0 _0’’É¾’hÄÓ\rÒY§83™Nb¤„êp/ÆƒN®şbœa±ùaWw’M\ræ¹+o;I”³ÁCv˜Í\0­ñ¿!À‹·ôF\"<Âlb¨XjØv&êg¦0•ì<šñ§“—zn5èÎæá”ä9\"iHˆ0¶ãæ¦ƒ{T‹ã¢×£C”8@Ã˜î‰Œ‰H¡\0oÚ>ód¥«z’=\nÜ1¹HÊ5©£š¢£*Š»j­+€P¤2¤ï`Æ2ºŒƒÆä¶Iøæ5˜eKX<Èbæ6 Pˆ˜+Pú,ã@ÀP„º¦’à)ÅÌ`2ãhÊ:32³jÀ'ˆA¦mÂ˜§Nh¤ğ«¶Cpæ4óòR- I˜Û'£ ÒÖ@P ÏHElˆŸÀPÕ\$r<4\r‰„ş¢r¨¨994ì”ÒÓ”òsBs£MØ×*„£ @1 ƒ ZÖõÈó]ÖÕÀÔÖÀPòÕMÁpHYÁ‹æ4'ëã”\rc\$^7§éëåBM‘uÆ	‰u#XÆ½¾c„¥kˆ¡kÖB|?Œ²¤‹JÃq,Ô:SO@4I×²…*1‚o9Şò¢t^©µ°Ëy(ø\\áC`Ó†`ã\nu%W˜æ60¸Ân£xîéb/î(¹	Kd’T°	¢ht)Š`T26…ÂÛşÿ‹·mŞ¢’Äª6M€S:¤£ª`Ş3ØÒ0¨¿Éí{U%\r>ÉŠƒzBõÃÈ@:ÏÃ¨Çc˜Ì:@ºOÁcX9lÃÏŠ¿‹®Z6®£«daJR'#7ÖÖ8iÈ@!ŠbŒ3ÃDc2&6î@=4nJS®SºõV¦-c(Ä2Ó‰ìB+ØÈ5©H¨Ğ?\r_4Š³øÜ3„ÉÀ#–íOÃM´Šˆ²H2ŒÁèD4ƒ à9‡Ax^;ûrùâ?Ár&3…éÈ_¦c¥\0007á}ô@8xŒ!ö±¶ıqÖğ»ãìª)Y)Š…Ø!Ä<çİa%\$®D(ÛP¸rbAà8ä‰\\£Í=è½7ªõŞËÛ{¯|º¾Æù_b|')ı@¾Òœ‚Hmá5àèüßªr_á ¸À@zÃYLQnÀÿ6Br¼,?¦±ø~ïKÚ·%š“rr’]°h)„1¾¥¹n.®\"'vš•°aÌÍ¶³TÛ›ƒr\ríÒ0(°Ğk\"ÑM@€1Áuµ\r‹ØlFFQÙt\0	ã‚á¹Vä\\‰÷+AÂ's,SAˆDä1á†/Yd‡‘\"¶E0@\n\ns†A\r@‘ˆºyáñ¥4á”Ô«d\"\rYµAˆ8ï’\0ïk‰@‘:\0 i:ºÊa#ˆåÛŸè„jÈ°sBˆü7àéK¨ppNÁ\nÁlÍ£,pê+sfSaw¡””±˜\n‹ĞÆŠì@õ’Ò^L[+°[‚55Ï¢lN\\wV'Ê£ /O\n#-e\n”âRHXy3ÒEåK²NU´×—P\\8‡STƒƒ2\0Cï	ğI®ãƒ @ñâ]!CR†‹`­cG„)D’‚€O\naRH´ª\rO\n‹A\r(à“‚\$çè=	)ğ· Ò’Šƒ1s§~ª)ÃXtHmš‡4ê':Oá¯\$ğà«ræ˜Q	€‚¨§E‚0T\n7+b]-æ­ ¤T]R>ÉÌ‚³VäÜì‡“Ö@Ë²°\0‹\$¨pjÈu;á¤=IÛ_ÕaO8ì›¸®(IJ¼V'XÈ¢ûH˜v†aä;ŠJBl\r€¬5±£óP‰úS¢´ãíVHšI¡¢0õ\rL‘)Ä|¦9Â³9¦©ü\n¡P#ĞpQY<óµ…×r&±\$óWè¶¨f.ÆXİŞBVZá^;ÊT=çdóV]ûÙ(#½î.‚ø3‚(&‰4GYƒúhæL&İ Ì“¬©Y5åÀ+HJHÒq¯\$¦(Æ/×?\rJI²Xd†¦0èÙˆĞu:èŒ«4Q†1'%!07Ö@ÚDĞBJ¸Ğ¾r›	øJ¤i)¢V*×ÌŸ'’\" “ä¯ÎNXaÙL§”J\nÅ\nkä2ÅÔJ‚Y &0\"ÁK¾¡Œ·ZkT4ŠˆëÍ¾I„é_ü`Ò¨RÊ©8Ãá³€\nk( ";break;case"sl":$g="S:D‘–ib#L&ãHü%ÌÂ˜(6›à¦Ñ¸Âl7±WÆ“¡¤@d0\rğY”]0šÆXI¨Â ™›\r&³yÌé'”ÊÌ²Ñª%9¥äJ²nnÌSé‰†^ #!˜Ğj6 ¨!„ôn7‚£F“9¦<l‹I†”Ù/*ÁL†QZ¨v¾¤Çc”øÒc—–MçQ Ã3›àg#N\0Øe3™Nb	P€êp”@s†ƒNnæbËËÊfƒ”.ù«ÖÃèé†Pl5MBÖz67Q ­†»fnœ_îT9÷n3‚‰'£QŠ¡¾Œ§©Ø(ªp]/…Sq®ĞwäNG(Õ.St0œàFC~k#?9çü)ùÃâ9èĞÈ—Š`æ4¡c<ı¼MÊ¨é¸Ş2\$ğšRÁ÷%Jp@©*‰²^Á;ô1!¸Ö¹\r#‚øb”,0J`è:£¢øBÜ0H`& ©„#Œ£xÚ2ƒ’!*èËÃLÚ4Aòš+R¬°< #t7ÌMS¶\r¯~2Èú5ÄÏP4ÅL”2R@æP(Ò›0¤ğ*5£R<ÉÏì|h'\rğÊ2Œ’Xè‡Âƒb:!-+KŒ4Í65\$´ğAKTh<³@RĞ°\\•xbé:èJø5¨Ã’x8ˆÒKBBd’F‚ Êà(Î“¨õ/‚(Z6Œ#Jà'Œ€P´ÛM‘¤üğ<³À ”-ÂùoÏhZŠ£Âƒ-Ÿh®àMÈ6!iº©\r]7]¤«]ÉíàÙl•5,^ÉĞ]|Ü¨`ÑsŞ˜¡iQ©xô”\r@P\$Bhš\nb˜¡p¶õ½bí”º²ˆ,:% PÙ&LS *#0Ì*\rTš2ÈÎí©@\$Ï*\rì• 7,ôÄ:Œc49ŒÃ¨Ø\$lÅIº(Ã¶¥ÿ4ÃªaLG6.ÔÎ\rék!ŠbŒû¯q4C246éÒ\0@ë PxÖÖ#)@&ã¨æ8g\n<—ŠŒsÊÍîÒïè\r\"·=PPÇ2@ã#Ô‰»X2ŒÁèD4ƒ à9‡Ax^;ösm®=Ar43…ğx^ú#É,È„A÷x”ºà^0‡Ó2m„<	eá¸@©ì7ÁVå´PTFx€¥âkâ€Ë¯ğç\0@Rü\\½n‚ô½?SÕõ½cÙó—l9wĞË0AóÜğ\n@>	!´8t–ƒ£Æy	yI„&ÚĞ(„¦†ƒÜ\n}Bh0Ş/%F\n£\$„™%ğÎÈJYq Æï@wZM<ºÁPääRZ!™ğFÒŸMiíDÚC èÁâ9a…%‚\0Æ^ Ài#áÌ—„\"\noO¡¡CX©6xx!\$¬,¡u[ˆH\n\0€€RGI2‡Á¼Üò^Ó!P…îPÎs,fƒ&?†lÒŸp@GÉ\0wR\rŒœ¨ dÎ{J…g¨HUäšQÇ4rT7¼@ÏúHP;š@ÆÁÓHgu\nŒD˜œ\\1.&ÈšÒŒŞÅ\n2aˆV9›„X9à—Ï0‚Ÿ\"Dƒã³q@ÏMê¨Ùj¥ÒÙ/	\$L<˜²NP¡h?=\nDy\nACˆu3Dü3Â8å¢]‰' 1’ähä)ş3.	²¢xÉ¢ÔgDÜ(ğ¦€Zha¨ëÍ	q.‰øŒÈd’cÀLÑÚ  Dxå#(Ğ†ÙlÖk˜Ï’m!ágL(„À@–‘0Ü#HÖBĞ¢?dş\nNT” ƒ‘3~Éô½)&N¡”‰aºz)˜ó	ê*Rú¢SÅ ¤ŠCÅş¦¥Ê1È–ëıGÔgBWAõey6WT’ø¬\$n±Ëò(¿¥hI¥Ö°ËV%û«uÆ¤Æ‚M\r€¬5ÏcjNÑh'Ñ :›òQ‰xF¥0EÃøjm¨TÀ´%€ÜæËØnåâ9Ìu^ìA†£l*ÚQ}[—šÿ0–ªº\0›ZHm|_¬&Ù’Y%m­uyaÈÂÛæÇHÕw6uâÂè]“ÀMBa¤3’Èm‰Œ(©à)h\\ŸÃš+V5H4NIäY\n‡³@§Ùªåºõ™ÊUbIé%%á07Ñò2AJsX7öĞ\0lV±¶8(Ğ!—`@kCÓN.jNâZ¥;…mğiTVôÃ†\\7qL9Äº½H\0 –HbÀ:qÍÛtJU­Û¡L¢ö#OwƒC?¹ëÀ¾â\0Æq+²\$¡À";break;case"sr":$g="ĞJ4‚í ¸4P-Ak	@ÁÚ6Š\r¢€h/`ãğP”\\33`¦‚†h¦¡ĞE¤¢¾†Cš©\\fÑLJâ°¦‚şe_¤‰ÙDåeh¦àRÆ‚ù ·hQæ	™”jQŸÍĞñ*µ1a1˜CV³9Ôæ%9¨P	u6ccšUãPùíº/œAèBÀPÀb2£a¸às\$_ÅàTù²úI0Œ.\"uÌZîH‘™-á0ÕƒAcYXZç5åV\$Q´4«YŒiq—ÌÂc9m:¡MçQ Âv2ˆ\rÆñÀäi;M†S9”æ :q§!„éÁ:\r<ó¡„ÅËµÉ«èx­b¾˜’xš>Dšq„M«÷|];Ù´RT‰R×Ò”=q0ø!/kVÖ è‚NÚ)\nSü)·ãHÜ3¤<Å‰ÓšÚÆ¨2EÒH•2	»è×Š£pÖáãp@2CŞ9(B#¬ï#›‚2\rîs„7‰¦8Frác¼f2-dâš“²EâšD°ÌN·¡+1 –³¥ê§ˆ\"¬…&,ën² kBÖ€«ëÂÅ4 Š;XM ‰ò`ú&	Épµ”I‘u2QÜÈ§sÖ²>èk%;+\ry H±SÊI6!,¥ª,RÆÕ¶”ÆŒ#Lq NSFl\$„šd§@ä0¼–\0Pˆí»ÎX@´œ^7V®\rq]W(ğëÃ˜Ò7Ø«Z•+-íE4ı\"M»×AJ´*´²ÏƒT‡\$ŠR&ËŠHOÕéÌÍTó¾S­Êúİ\n#l¥Ğ…ÄŒˆ#>ó¡€Mñ}(³-ı|³Ø\n^ó\$’âH¹A j ­w#óW#égt3ì’€‚cikühôı¼õMÖ›C\$5ĞH&f]ÜĞ«Î³íc\"’¨(]:­ÄDÊ’ÒÚ†”\"*£qÃ	=¿d©„6¯ª}şº²*â‚,eŞ¬CRÂòºNÉâ\r6 Av k/jhºkºşË¡,H‚+¶lËikµjû)­)işå ìK6ñ¤­ª3¥ \$	Ğš&‡B˜¦’`Ú6…ÂØóÏ\"ëE›à¹Õ1FK‰Ş\rƒ äÜ·a\0Â98#xÌ3\rÊğ©Ää‹è’İa„\nƒ{ˆ6Œ#pò¶(ê1Œnpæ3£`@6\rã<\$9…€ååŒ#8Ã	%ş6ÂC«®aJÖ¢,s=O9Á\"¦)ÒœZkø²ì‚ÒÙ7n­`Æ•…²4D#&„TµŠ2xOb+¯íá›r„\rò*9á™]UŒ„Á\0A 7#ºÖBÆˆÀÀÂo] f ˆ4@è˜:à¼;ÃĞ\\aºFˆÌ3‚ğÊz<WÑcÄ€D¢QÖBAœğÂ‹[(CKĞˆšÂ\"Í£†DÅ¼²6wºOWzçC-Ô6D¨`1B	¨äë‡#Š’Pr_+8—h“!l/\rÆÃXoaÜ=ğş ÂX†¢,GˆË#¬eÁhÁ\$6‡–b8tŠ±][œù@vCzù: ä³‚R20y”7DšÉ›AtU‰<Še–LO‹¨\"ñm?ˆ2Tm'‘½€¨0pCc‰‹ s”õƒÁÂ)@¾Cf©	è='¨õÃÚEÓXì\0ĞpC˜a”\0€1ÇùBCle¬Ì!ò˜˜i\\C@¢LÈ—Rú[mŒ*tºMÄ( \n (Oú™Sƒ,lsı@R|—JCY–iE3 q1È9G02¯”ˆƒ¡Ï;hı Ï—°ékï&OÉ“Sjc\n’‚ó½Ú>SŸÃšGz/•™^ƒƒêáÍ\$¤¹ªvƒh•¤3ÃPA6gqÁa†;¶˜lÖÙ_CÄe,“SK@¦]i\$æ¹})ß\0ZyR%P±¨j\"Ø\n¡¡ ‰8§d¢JÃô_Õ5\0 ’HCË±¥|œ4‚ƒtª;G=Úês’fFA¶ÈØYS‘pc{êwS4sXª¥NIADQ Â¥u&p™µûºÒÅAU¼”À\"jê(	3[V…»ò•bÉ¨²T¤µP¨ôS…ì\"Röà38ßO‰½hOê*ÄÊR„nåcEÇMìDeWêà \naD&\0ÍJÁÉ…á*QW’¾CLH¯–ĞÚ2DŒ4=4`£µº6DŠ€±UäF‡›‚Kƒ†2Fşl,(pÁqW>ŸÛ¶ÒGWÊõ\\ø:1(Æ6Lİ}m-¯†Şİ¦4ÅëC7¼h}1Z	Áø¸·cH]plq”‰¤Äàn•hº…Ø†Lw†Öİ}€(#_€Û’\r`|T*`ZÜ(²hL¡/û§[Hyå¶oÌŒ·ÌS”¢ãwÎDË:lìl‰•?6ùÂÄg3Lâ¨%n—ŞáaÎU*R£ä‚‘PeHiÁäÇ—WÀeòÜ\r)§t9–\r[,Z˜N`'3£&BÚÃ™k¯2é”t	^©íg¸Ö¼î’'-Jİ\\šµöØ8-Œ²¬bù6—XLªô<¢í2ÌQ¶RŞ4ÏÃqk•æ¬B˜e9sJ–©:<”.èZ ‹sQŠ}g)4Îªy‚şËn7ÏVóJ6zÒIë84Pnf4 vxQ\$H_€";break;case"ta":$g="àW* øiÀ¯FÁ\\Hd_†«•Ğô+ÁBQpÌÌ 9‚¢Ğt\\U„«¤êô@‚W¡à(<É\\±”@1	| @(:œ\r†ó	S.WA•èhtå]†R&Êùœñ\\µÌéÓI`ºD®JÉ\$Ôé:º®TÏ X’³`«*ªÉúrj1k€,êÕ…z@%9«Ò5|–Udƒß jä¦¸ˆ¯CˆÈf4†ãÍ~ùL›âg²Éù”Úp:E5ûe&­Ö@.•î¬£ƒËqu­¢»ƒW[•è¬\"¿+@ñm´î\0µ«,-ô­Ò»[Ü×‹&ó¨€Ğa;Dãx€àr4&Ã)œÊs<´!„éâ:\r?¡„Äö8\nRl‰¬Êü¬Î[zR.ì<›ªË\nú¤8N\"ÀÑ0íêä†AN¬*ÚÃ…q`½Ã	&°BÎá%0dB•‘ªBÊ³­(BÖ¶nK‚æ*Îªä9QÜÄB›À4Ã:¾ä”ÂNr\$ƒÂÅ¢¯‘)2¬ª0©\n*Ã[È;Á\0Ê9Cxä¯³ü0oÈ7½ïŞ:\$\ná5O„à9óPÈàEÈŠ ˆ¯ŒR’ƒ´äZÄ©’\0éBnzŞéAêÄ¥¬J<>ãpæ4ãr€K)T¶±Bğ|%(D‹ëFF¸“\r,t©]T–jrõ¹°¢«DÉø¦:=KW-D4:\0´•È©]_¢4¤bçÂ-Ê,«W¨B¾G \rÃz‹Ä6ìO&ËrÌ¤Ê²pŞİñÕŠ€I‰´GÄÎ=´´:2½éF6JrùZÒ{<¹­î„CM,ös|Ÿ8Ê7£-ÕB#öÿ=‹ûá5LÃv8ñSÙ<2Ô-ERTN6ˆ¶iJéáÍ„J5ÊR²ÚUìD”8òÚ­hg·ìl\n³ˆåe®	?XÇJRR¥BÙ²JÉd—KªÒd[aß¥¶¨ßõ‘]¬‘v¡Yß[5Õ†ÁµM)WV+„£\$e}æ Nó½¥˜{ìhÌ/xòA j„Ÿ «îmÛè2§,6Š›MÄºÛ°\"7œ³ÓşŞı+¾Å\n^ÕêÜµ'R.\0§ôRŸ@Ş•*±<ºµıë[î|uhZÛn	píÙ]qm0Îw\\œ7gÃ«ïQW¹àx^'hµŞ?º².8G±!vığ÷Ñ¢àÄ>z»|÷¸úSf{ÅÅö7wŞˆ_ÀŒ8Ùï%B\0ÖQÑÍA \$ šAĞS\n`(2@^Ch/aæºåP‰¶¨y³óz¢JAJQ­\0006,€vÎèaGˆ7†`Ì@e8(ˆ‹B¬æ×XÉœğ´³<îÙó\r…«Ä@ŞyCha\rÁäUDCc=áÌ3PØ\ngIÌåg) GFşR@u>à 9‚’¼Â˜RÅ Ù˜e^á_×QnÉ‡›Òhç’ÈÉ„)—àÍ!£IÏL§8D¦‹Áh}hiµÉõiäbï%\$áW:D´”qH3åä·¥Âm+\"”*ˆ>U§ãNúa>™UF’A\0A‘à7&xÚÕ*£‰œÀÂw¡Øf ˆ4@è˜:à¼;ÎP\\QfbgÉ¨3‚öTÓ›\"Œ°‚ }<O²Hàğ†|€]Ø·ho¡Î­)´¢!Ëf‰V‹ùd[ÙŠ \"+vy6¸™“¡”\r]³6‡f¢(Ô§	©Àû‡#ÌŸÔroL 8˜v æ¬×\rfmÍÙ¿8gåóeÄ×;'pnÌ¥•ªYê`ğI4ç±Š†àé?'óTü†öô|ª˜a\rgˆ4§ÔÏfmN 2@‡Ğh*“jJ¤ùõ–c0A¥ğ›Q‡¼‰A^\n ñÇ<Õ( ç®3†#Ä<ÄLU½ÍISÌaŒq–3Æ˜é`ÏÉñ¯„€0±P@é{*a„68†¥ØJ¡P‡7¤¤¥TçXŞãr\0PP	@¤V‰\\UİÍÀAî¥K6g³-9€¶g^GÕ,Í¯óèøSÎzOYí­é=‡ è|ÚvO¡‡{¶°dµ¡&‡ ö_Z‘HV•ô\\ËlWfg«‡Âk‡4ı£ªe?5p7õIÃš€PVı0ÑTÃHg› ‚ÃY£ÄÃÆˆFÆØ¯'à«œä”™ ÓZÛë}ïÔD×6D.—k‚GWi–ôk\"—œ7¦ãD™3'äÙ;Eğ˜IÙ/\$£BawÙ0_”ñ/]Å9x«:¥“®'-øÒâbûçH2&+Á\$‡“¸ ioG‘<'İUÏÑğ‡aÄ:ôğ“Hm™4öfÒl™CCN6jğ§ãÜp!g{(Ş…h¥‰zÈÀP	áL*d”C(LVšXˆé‡‹ÙÉ¡p9z·¨œ­(LiUX\nC(•y*YôÉ0z°4Ò‰ ƒ6¡Ê|ØÓî™á¶hiKÔ!’ó\ry†ùŸ&R‚PÊ( šá‹‚\0¦B` ×,tõMpŒ-äZoA¤Ó§ÈëóÉ ‰“©,;xb1\\Jl²nC´Ò#\0ñ%ARKvÂç“ˆò*ëW0FQ½|{—ÙÕqpX—U—Ó€Ü¸/\nŞ+¬“îmš…ÉCä>Â÷Ô‘+ÍıTníşÎõ}’!:WÜõZõ\nX/Ì¤q\rßÇø¾”obKåà±9{ekğ ˆGõ^\r!Œ5‚5Ãµ¤Î·V©Õ`Ò¢å]+ÁìÕ9Œ0¬t\n¡P#ĞpÈf†iI<úiœºu+fÇÓ®qáúÅw-Şã.íGVÜcŠ±cOá²Kš-‹ÒƒÎ¥o\\İÇ–Öì_‡µ_©Wt:w¹ßJ~\"åç‰À“€236ëí.BÂc»‰L0w^\\§(z*Ù™›pÉh?TÈÑ‚oVë~[Ä~é²n6’'~øÛğyÚ1H2†Íz¨šbñşó¡:<\\»|‰g®üêóİE*È„mféZ(ªê¾şÛöÇê§%É\r–øî`¯ ¯ÂˆŞƒjjNI¸bŠŸF‚ÒïâmŠŞ6gª Ä\n4dt‚Fz©vZKÙFS\$ˆä\\ğâÎ:åª:eöÆ‡\nošªş»d–…'Îâ€†(išiçÉVî¯²úäñÍBû\n&‰Nğò£Óì²äV…B\"z‡>,ãBˆZs	Bğ0ˆ\\#rup:ñ>å¼”%8AEÔd¸şiúD¢";break;case"th":$g="à\\! ˆMÀ¹@À0tD\0†Â \nX:&\0§€*à\n8Ş\0­	EÃ30‚/\0ZB (^\0µAàK…2\0ª•À&«‰bâ8¸KGàn‚ŒÄà	I”?J\\£)«Šbå.˜®)ˆ\\ò—S§®\"•¼s\0CÙWJ¤¶_6\\+eV¸6r¸JÃ©5kÒá´]ë³8õÄ@%9«9ªæ4·®fv2° #!˜Ğj65˜Æ:ïi\\ (µzÊ³y¾W eÂj‡\0MLrS«‚{q\0¼×§Ú|\\Iq	¾në[­Rã|¸”é¦›©7;ZÁá4	=j„¸´Ş.óùê°Y7Dƒ	ØÊ 7Ä‘¤ìi6LæS˜€èù£€È0xè4\r/èè0ŒOËÚ¶í‘p—²\0@«-±p¢BP¤,ã»JQpXD1’™«jCb¹2ÂÎ±;èó¤…—\$3€¸\$\rü6¹ÃĞ¼J±¶+šçº.º6»”Qó„Ÿ¨1ÚÚå`P¦ö#pÎ¬¢ª²P.åJVİ!ëó\0ğ0@Pª7\roˆî7(ä9\rã’°\"@`Â9½ã Şş>xèpá8Ïã„î9óˆÉ»iúØƒ+ÅÌÂ¿¶)Ã¤Œ6MJÔŸ¥1lY\$ºO*U @¤ÅÅ,ÇÓ£šœ8nƒx\\5²T(¢6/\n5’Œ8ç» ©BNÍH\\I1rlãH¼àÃ”ÄY;rò|¬¨ÕŒIMä&€‹3I £hğ§¤Ë_ÈQÒB1£·,Ûnm1,µÈ;›,«dƒµE„;˜€&iüdÇà(UZÙb­§©!N’ P‰ÁÍ|N3hİŒ½ìF89cc(ñÃ˜Ò7å0{ÉRÉIéF¬Ü6S’í¹³•wÜ¨ìqp\\NM'1İR²Ÿ×påapÔ:5õ…Lií`³ºIüIKH‚¿Z c#Û‘Si©h,~­CN³*©œ£#¸VK·™/µÛ¬Œ‰3•\r%Êˆ<¿€Sâ¨^|8b¬ MŠ»]ß6úé;hÓ¥iõ‹³d01q¯-²ss­sòT8J+*gKn+´ê»¹£xt²ÂÅÃ¿c9©Û*¬á±q¤»»>ê)ÖJ®ôuRáÌE¥«¼öüÏtƒ‘•L›»u_;v±üÆSÙîúº®ØÄH\$	Ğš&‡B˜§xIÊì)c3ÕvˆP^-µeÁj]“>.))á@4Z‹Å(\n\rĞ9\0£Ğzƒr=á¼3`Ø•C)Å9¢,¡-Å¤ØaY{‰)Ş·®†ÈT\rçÈ6†ÜA\0ue!Ô1†3øÃ0u\r€€6ğÎ•C˜,?ÁÊÎR¨ ‰ •¼†ÔªP((`¦\r0FàÁÎúºVdõS0¦‚3z:¥Áë¤£`m\n©{Iï,rw©¼:H±Í¶\nmÕÊ„h®%@!9¿[„ıÍ¬”4G: ^o|¬CØšèfc¡Õ•%`@C\$N\rÉº!6XÊƒ\"n\0ğ0¸\$è\"\rĞ:\0æx/òÌ3’qà¼2†à^˜øterøé€¨g€¼0ƒâ°á¼ÃU-Ù­¢È¸@Şñ±ê¦?¹5 KQ-MQ³HÑL„s-ñò;£¸ò‚jw@¬‘C5¢“hx¦	(¹G)CD§•2®VÊùc,Ã¼µ–òn\\‡)w/eã'—¬©–ğDbğI\r¡Àü†Ùz&\\Ícty†öòÁ-\rg¼4¨TİdØn…cšƒš¶TéUhjäÁ	¡=¦\r¼0†9„Ë\0w?ì1ğà›¤r£Íä0†iæ !¤6ĞæÃØ~›*R?Õ\r@<ı£á¤0†Å~ñ'I9èj5ª×2¬á:®YY¹ºê¨Õ{pS¤ì­Æ €  ª—æ\\='mĞî.DÅO4&eT«€ †Ë\$İF™'øùCì~Ğeo*9Cú‚Sê­°ô;Ú¤Qdo*ŒéFXùâÍBMÔ°şÊPæ¡¡´JMˆ–àáš‡Q!É¼‡tÃE)\r!U‚\n›XÏxc2R»Æº\\ë³ÊYQ±XÇUa\\éaS©dª¬DXWKav8FÁ0¹ftsNÛ®›ÁG“•¸‘;ˆ]_€’ICÉé4·“âŸÓÀn¤è şÁ âOâÉÀ6Éz(®blpõ<V;`¡İ·Lvåq9ët£]6jÆÉN²¶WIí:)\"à»e¹€HR©&xõcÔ–@³zHd£‚ÔE{ËA*vp·?âÆâ³¥½JàÇ`¬VƒDŠÊ„óá+Ä›=–À‚R†+·O‚ˆLšÔï)B0T±0¹¼†š6¡\"V\"ÄŠ7ääÑÒC_V6úE¡™#ÃpCˆil¨ç:ËÜä|nš^m,^È›„×ÔDmëcS‘”Õ‘Ò1²/R•“°óÕN nG(jæ¶×fTBYDkD‚£\nX p06¼VCkÊD\0çÅˆq™ÉS+n4Ö•€ŸCl”O÷~\$…P¨h8cÒ{\n%b±ÑÛIªcJ=€s`,àØË:½ÙïdÛA[Ìï³ƒeÊ³“Â“Ìu±w9\r¹QÆ…JãîNV€ç¡Âº&¡ºg&zlRÊ<.5ê¢]²Îö™ÎgÓ¨\\iÔ\"m–âÙK/Ñ˜!-¸¿òñV+07ç0ÚœSÊu][L’²®Ñ£cKÁ²97PÜÁ;f°'x\"]æ”ƒ)ù¨Ö¨¬¡GA\rˆ³+8äï»R²Ww¾;êY¬Ú8ü˜JïÕ%¡uäó„æ7_åÜ‘¨9%p¹ò;€";break;case"tr":$g="E6šMÂ	Îi=ÁBQpÌÌ 9‚ˆ†ó™äÂ 3°ÖÆã!”äi6`'“yÈ\\\nb,P!Ú= 2ÀÌ‘H°€Äo<N‡XƒbnŸ§Â)Ì…'‰ÅbæÓ)ØÇ:GX‰ùœ@\nFC1 Ôl7ASv*|%4š F`(¨a1\râ	!®Ã^¦2Q×|%˜O3ã¥Ğßv§‡K…Ês¼ŒfSd†˜kXjyaäÊt5ÁÏXlFó:´Ú‰i–£x½²Æ\\õFša6ˆ3ú¬²]7›F	¸Óº¿™AE=é”É 4É\\¹KªK:åL&àQTÜk7Îğ8ñÊKH0ãFºfe9ˆ<8S™Ôàp’áNÃ™ŞJ2\$ê(@:NØèŸ\rƒ\n„ŸŒÚl4£î0@5»0J€Ÿ©	¢/‰Š¦©ã¢„îS°íBã†:/’B¹l-ĞPÒ45¡\n6»iA`ĞƒH ª`P2ê`éƒHæÆµĞJİ\rÒ‚ˆøÊpÊ<C£rài8™'C±z\$Ø/m Ò1ÈQ<,ŸEEˆ(AC|#BJÊÄ¦.8Ğô¨3¸³>Âq‘bÔ„£\"lÁ€ME-JšİÏìbé„°\\”Øc!¸`PĞÍã º#Èë– ­ƒ1 -JR²²ÎXÊÍ¯¡kğ9±’24#É‹Tà«ë’éˆõú:éÑã-tŠ1Œ‚7e¤x]GQCYgWvŠ3i¥ãe¬,£HÚç¹b˜t\"Ğæˆ‹cÍä<‹¡hÂ0…£8Î\nÉz![”àPÙ%F¦£÷:|²§Ãš}I8¦:ÃªŸ‚éğ×…¬Ø3Ãõ„zv9­ÈÂÍÇ°Ü‘>:,8A\"}kÑâ#ˆ4h¸æà¸a:5¸c–]58ØŒ€#È3Fb˜¤#!\0ÔÏØp@#\$£k2èSí\$â~O”Ñk,Ÿ9&~Ù;y±b“ˆ#\"—èĞ¤Q¹*xz|ÑÔ‰d:²ì\\ZİZx‹Ê3¡Ğ:ƒ€æáxïÍ…ÖÖîÉ(Î¦!zg*ƒK‚„Aó×¾–+*è0å,×—‹+\"È‡xÂl»;r9Å¹Š£;ƒ¨X\r‹Àè3CÚŠŒO=–g‡î’¨ArŒÜsŒø…çƒx÷ë­0#dà¡ÎÑæX³,ÚYÅ„oÈò|¯/Ìóc¿:İˆ tN7:D¸LRüE \"˜LŞKw®ı	¡S¶ö\0Sf¬ Ü‘ÕxJ™Ç3ç’†Äxú…‹r\$ÈùZ'–`º\\ÉÌ*`ØÉm¦¸õ¶¤ÛH»&ƒm¡•ŠvjÆŒcàh]‡#6Ø		eu\$|™0×É8U@¡¹¿êDƒC\$,¼…\0Dcu(¼Ò\n\n+UTfmSô>SŠR*±º”Ëc…\n,©¡hº|Œ9ŒA(-í:ñ>…±!°—EBFyÚ\\OM:Øhƒá¼9\r\$œ*…‚fO‹>?fLÌ0é)¥hp*íwŸàä¢ƒº£hà€†w&Mt¯5Ò\0:ÒNJIY’¤Àò6BÈÈº­|\n÷‚\0ÂÃŠâfà€&<ç @Ğz#Ñ±	 ¨»#	9Š•v‚êçÃ'ËI`(tOkI&Äñ'‡–BíÙéû‹¡¸“±èj^Éx3@'…0¨kHùys,<œvD[(³*\$]&ÅBHÙD¤	PòØ¤%AGÙ³I²ÜJÁR4@ÙI™ b¡)´`¦BcÇ)SV„‹Ã’g¡²§7çÕŠ”ô@Õ!®8†0áÔ™6h‰9¦0„@èÉFo±…#\"ğÉHÆ¸êÒ‡KG&¯ Ïk	º41k-‰¬k+…[„uÒ¯„%O8£´zOB~)™wâghB&¸!¤°Ø\nâéPÔ‰! @B F á	†ğÈ‚_sA°–“èU[”-qª1Ú©¶¹²Ü™ÂÙ¯\nÂÚº-ítÚnhÔ¸—3Úüšè‰ Ù ‚P²>H%• à.!|€O¯ë…†p\$Êè³		#Ğ3\roÏÁ½&ô0Dƒ\nd‰’L²éB‹àY¾	ôé#ü«›yn/\rb+…^T=v[F~Ô˜Emp@K\ráÀ‹'pˆÇ-tî·L”É£p¼.R.ÑŞêW†ƒ}`0’.ı>\"ğm¡\rØ@";break;case"uk":$g="ĞI4‚É ¿h-`­ì&ÑKÁBQpÌÌ 9‚š	Ørñ ¾h-š¸-}[´¹Zõ¢‚•H`Rø¢„˜®dbèÒrbºh d±éZí¢Œ†Gà‹Hü¢ƒ Í\rõMs6@Se+ÈƒE6œJçTd€Jsh\$g\$æG†­fÉj> ”CˆÈf4†ãÌj¾¯SdRêBû\rh¡åSEÕ6\rVG!TI´ÂV±‘ÌĞÔ{Z‚L•¬éòÊ”i%QÏB×ØÜvUXh£ÚÊZ<,›Î¢A„ìeâÈÒv4›¦s)Ì@tåNC	Ót4zÇC	‹¥kK´4\\L+U0\\F½>¿kCß5ˆAø™2@ƒ\$M›à¬4é‹TA¥ŠJ\\G¾ORú¾èò‚¶	‹.©%\nKş§B›Œ4Ã;\\’µ\r'¬²TÏSX5¢¨Ü5¹C¸Ü£ä7Iàˆî¼£æäƒ{ªäã¢0íä”8HC˜ï‹Y\"Õ–Š:’F\n*Xˆ#.h2¬B²Ù)¤7)¢ä¦©‹ŠQ\$¹¢D&jÊÆ,ÃšÖ¶¬Kzº¡%Ë»J˜·‘A Q\$¨B22;`Õ Ñˆ ¨ÑN™¨Râ4J2lòæ›2Rí?\n7ÉÔËTE/dô™Ñ&‡\$óØA+Êì\"<O+¥>„äp7WÎBÏ`V\0Ê<;Ãpæ4örÖP ôå øš\r2¨	„ÌT8—ÒŒ™ìš²(Äb4QÕ„Úø]	‰x·)¤a®¯dÒºÌÌT«C)] ¢c\"Ğ,IxáPvÏaøy\\ñd_S\"4—ÀPH…Á g’†7«D5eÒ4XË\n8Zİ¡(Õ©3\\E*ÚE­l™Oh|h•ÔF©æ\nÍ÷¡hÚ0º-u0ZA­Jš’?ên]¢N\r¯%rÊéNëÖÄ¯k…A´)j•ˆ?L¶¦¦£É&ƒ…ÀJÒÕí*NÃ¶KÒÍˆ°÷‹üª4;»s¼ï{B•¿7|b†¤û…«\r·Ü>ê‡1‹ZSiFoY˜th½GOj(Ò˜ZÜAxuÉÛº<]¥C¢ZˆC`è9N†0N@Ş3Ãd@2Úpz²ƒUİÕç\nƒ{—§ÃÈ@:ÙÃ¨Æ1º£˜Ì:\0Ø7Œñ\0ç0ø~ğÂ3ê¼AÁ„l@:»Á@æ\nKYšr*¤ºÖÂ˜RÆu­9À\\Aã.‰à£Ep#â+…”hˆ’	X·-dGdJßSm‚„ÕÑb„šsˆğ*Tfuƒ2Â«=‚\0‚ĞnG)„1­È „â<ÌAhĞ80tÁxw‹@¸0ÄQàgá”7ôŠ±¢ĞŒ ˆFsºˆ8<á„=ü®˜*dS¸É4¸TDbaEÈ!,Á†Ú]RúaRŠ`6%É!…”.\\dÀÍ”ô2å ƒ'5!å’”šRJˆà<\0ÒòR¬J‰¢'E¥\"´X‹AŞ.Eè…”bŒ‘fFE´Ax\",àø\$†Ğàtƒld‘Ê:,	’x{\n;ä0†³Rz9{“*;è%Û‹Y*d50²ï\\9) Ë±Îh@aq¥h‚\0ît_Hb9ÁÃå2XPaÒy%¾7ÊùßKë}¨Ş{¯<Àa™ €1Ê‰”ClOÌÍ›søè“ºy«¸hãúRSx·@\$\0[GÓ²x6j½>‚Î\ndT\$å9Ì®DÎU'8‘åDGÈ\$äÍBiW „xÖŒBqÀëœ³šsÎ‰Ó¬)&‡ èuHITeõ‡z·\0Hqût)vñ,RÜšCDB§Ÿ;ÑÌ×:Ñ09¥È¹àšá¸8?Ã˜”R˜raAÜğ†0ĞşCHgŠ@‚}PóÃ;z\n¤›“ì]—Ñ(‘ê¸‚ŠQÛñ7¤ÔÕ'!ò“©…\$ÙˆYÃkÔq®FCGô¼J¤)fùv§r»Óé<	\$\\<œ @K\n9I)!†é¤xN³É!Ôê¤ ÌÃl>–Ñ&Á#pÆúÒ¬)@ê‡&ÚÖòb!Ä©™VSEŠøTò•„ğ¦š%€ÕÒİ–~K™dKWâıBgÜ}ÂSÈöâè²W}šS2·I)—X\n@âpTĞ:/&oœf¹Ú}qtDÀÅdA\0S\n!0j²„LÁP(c[­`&2N°zğ„rqÑëUZ©¢s8êv˜á;œ#÷¹ƒ)ÌªhiÙ[yh_eÌ¦PV<ƒÌ	‰\n’²İÒVo˜F ââàœ³ŸÌâÆ6|Öè³­(ÏG=0x_ŸsaĞ9İÊ?—a…¹A\rá†ÀV¤ ªdZÔè¥²Ôåƒ·á•ÌìLĞò)¢Ï-€ Ãl;IVUû…P¨h8Xqè\"xÜÉºpâë/¶•0GŒ:¹[È%Ê7€íö×Ø¥+c¹}”Š›veb¦ãhÚ¹#š¶1¾Ù\nE5lÍZmhé \$WĞ“_&‘Š—á±4ÓfˆiÁäÇ’rêB\rï%Ìh‘d¾\rÑ)¾ÌqW¢Tk¡\0\$©”ÄÃ\r—\\Óbnœ%Ö?p#rM©ÑêÔ¹¡!\rôòæÔ.,Àßj<H‰–òızˆ†€¡MJçS¥ò5gMŒä7(;Áƒê›AG_D½„íÊ™6ŞÁf—Û¨í¦¦ØS§JyÕµÁ˜·QBVÒmgn ˆ×'Ülâ¥ö%°*ke®ş·X„7¡£õĞ(ÎğhºÙ³Æó¬ İMmÁR à";break;case"vi":$g="Bp®”&á†³‚š *ó(J.™„0Q,ĞÃZŒâ¤)vƒ@Tf™\nípj£pº*ÃV˜ÍÃC`á]¦ÌrY<•#\$b\$L2–€@%9¥ÅIÄô×ŒÆÎ“„œ§4Ë…€¡€Äd3\rFÃqÀät9N1 QŠE3Ú¡±hÄj[—J;±ºŠo—ç\nÓ(©Ubµ´da¬®ÆIÂ¾Ri¦Då\0\0A)÷XŞ8@q:g!ÏC½_#yÃÌ¸™6:‚¶ëÑÚ‹Ì.—òŠšíK;×.ğ›­Àƒ}FÊÍ¼S06ÂÁ½†¡Œ÷\\İÅv¯ëàÄN5°ªn5›çx!”är7œ¥ÄC	ĞÂ1#˜Êõã(æÍã¢&:ƒóæ;¿#\"\\! %:8!KÚHÈ+°Úœ0RĞ7±®úwC(\$F]“áÒ]“+°æ0¡Ò9©jjP ˜eî„Fdš²c@êœãJ*Ì#ìÓŠX„\n\npEÉš44…K\nÁd‹Âñ”È@3Êè&È!\0Úï3ZŒì0ß9Ê¤ŒHƒLn1\r?!\0Ê7?ôwBTXÊ<”8æ4Åäø0Ë(œT43ãJV« %hàÃSï*lœ°ù‡Î¢mC)è	RÜ˜„ˆA¯°íDòƒ, ÖõÍB”Eñ*iT\$“E0Ã1PJ2/#Í\"aHÇM¢ˆZvøkR˜Öà—ìRôRÁCpTÏ&DÜ°EÑ^”­G^§ÚI `P¢Ó2´hî¬Uk+¨i’pDĞÃhÂ4“N]Õ3;'I)ÁO<µ`Uj˜S#Y†T1B>6‡ZêmxÈO1[#P+	¢ht)Š`P¶<èƒÈº£hZ2€P±„½l«.ÌCbĞ#{40PŞ3Ãc¶2¥ÓaC3aÅÙOf;ÅèÏÎk†Z¢xš8¢¤Š|î½ C¾Íæ­[46E¡`@‹”s2:õpŠÆêÍYá8a—PPÜÊŒ;,Ûs§¦´(b¦)Û¨ÓÃq4³a¥3šH1J5—EXê—dr;¶ÁCÄP3©cE05Àã5\n:Òk°‚2\r»åÃt¨Ò2>Á\0x¡èÌ„C@è:˜t…ã¿Ì>gœ?#8_C…ã\$PşÒƒp^ß•:;c8xÃ>%ÁAšªRT(™Ú)¤í \$\$:/Ã£¨H)™`+ƒf®‰C)W¡Z¬ê³fê ¦\0K^³Ø{Oqï>Äù0w}¨7gØŸsğQêD7)4PıÊ@>0ç¨ƒ\ndQ Cÿ€,€¦u|AKTLK¡¢×\$‡IyUäMCâZ³aoñ\0îÅay@aÀû<`äC*Ğ!™H¨ĞæCc‹¡˜:ÆØÓAŒätš 4o+]z¹şØ¥‹¡QHq^+\"ôbŒÑªu4ğ»Š€…Ğ·AA@\$hºÑäq¤\"dÑÚEÖ1¿Ò9,€r\r'i@†xâäzOÈ!Hèşã‰Ko\0½÷LğO²˜e9¡ôÜ…›¸8T „ rZİšÄwšŞôj›A '‚ºSTJ-w¤4G	RY ¸‡rs˜¸£AI¡~¯öãKC¥Øâ“ôòÃCD¸\"â¤â„D‰S°s®Ì€»`RK‚I-hõ½9‰s-f¨®š„‚ƒ1ø:o-æÃUGT9è\nS t#/ÛRlFd¡âˆKxP	áL*pêßÜ!9…\rÄÄâD¹‰„GeÄÜœ“³ŠOh‰õz|Åt)eHÃŠd8LÎ Ú;£íÀĞÉ7J¼Y«ğeR¢ì‚¤¦9îÖù`Æ0¡\"dáÃ-PÉ3·dBA6»ã¾î,¤®u®K0›N‰‰)¹È¢ÉìA i40Q=±*‘Âr¤!–ˆÓ`×\\	/ñ9¿–„¯	É0ˆô“‹MS|m±h]’•‘\n¡P#ĞqgÒœ`”õ™'P™µ’’40\0ğÔ,sS²'¤åZ^Î™”´à((‡U1		0\n¸F\"*²úåUû¸ªÖ‹_o,U¾b,Í”SjÓš…¡¾7ª+U0lÓš£t°1¤´Â“‚¡Ş·#I™	jq\"!ÈÈ\$êÇEØ\rÇ\\0Øz&¨ÂÂK¬í¾ØºÅb.•šä\\Ë£	{Ô!Ò•4¨ªæ*é““\0‘»¸ºöI˜ìLÕé² ";break;case"zh":$g="æA*ês•\\šr¤îõâ|%ÌÂ:\$\nr.®„ö2Šr/d²È»[8Ğ S™8€r©!T¡\\¸s¦’I4¢b§r¬ñ•Ğ€Js!J¥“É:Ú2r«STâ¢”\n†Ìh5\rÇSRº9QÉ÷*-Y(eÈ—B†­+²¯Î…òFZI9PªYj^F•X9‘ªê¼Pæ¸ÜÜÉÔ¥2s&Ö’Eƒ¡~™Œª®·yc‘~¨¦#}K•r¶s®Ôûkõ|¿iµ-rÙÍ€Á)c(¸ÊC«İ¦#*ÛJ!A–R\nõk¡P€Œ/Wît¢¢ZœU9ÓêWJQ3ÓWãq¨*é'Os%îdbÊ¯C9Ô¿Mnr;NáPÁ)ÅÁZâ´'1Tœ¥‰*†J;’©§)nY5ªª®¨’ç9XS#%’ÊîœÄAns–%ÙÊO-ç30¥*\\OœÄ¹lt’å¢0]Œñ6r‘²Ê^’-‰8´å\0Jœ¤Ù|r—¥ÊS0Œ9„),„•ò²,‘´¯,Ápi+\r‘»F¼¯kéÊL»ĞJ[¡\$jÒü?D\nÊLÅEé*ä>’¬ù(OìáÊ]—QsÅ¡ã AR–LëI SA b8¥¤8s–’’N]œÄ\"†^‘§9zW%¤s]î‘AÉ±ÑÊEtIÌE•1j¨’IW)©i:R9TŒÙÒQ5L±	fœ¤y#`OA- 6U˜ŸBöí¾@?‹ÁÎG\n£¼\$	Ğš&‡B˜¦cÍü<‹¡pÚ6…Ã ÈX‰E=€PØ:Ijs”ÅÙÎ]ç!tC1¤â E3|ãAëAÏAÏÉ‰bt‘‘X¹1ñ˜“HdzW–ê5ÆDÇI\$¶qÒC£e|Î¼F¬9b˜¤#	9Hs‘\$b”ÿhdm\ro\\\rÊFèêYHWdşOd¬iOE\0;nè‚2\r£HÜ2Y³ütĞL’*\$KÀ—e`x0„@ä2ŒÁèD4ƒ à9‡Ax^;ópÃºnÛÀ\\7C8^2ÁxÈ7Ãè4õxD1LÁX_!„AĞE–)áÒP–§I:Q!„HxŒ!ğ\\Œ„Ó¦ÔBù”g½gË9³ĞŠº3OÜ,YyÄá>¥püOÆñü'ÊòüÏ7ºîã—?Ğô}(Ê< çÖt¡?ĞTƒÅLd\\af%I²Âsp9È…¢0K!±~!]Ú*	tî’”+Äéñ>gÔÆŠÑH9„˜WÅC/\$JÙPŸns‰±pBDa9ƒ”CŠÓØõKAABœºµfdDâÂL¨8¸(“”:@\$ÈÿÒ`ƒ‚¡Ú”w‚õˆÑ‚&¤ÜÏ	áÌ#…*@\rR™AÎ*Åzİf4Ã\"á!ñ‹1¤¾	3ƒ¹ù?g,¹ pxK8æñ½õº+Èà½ÊPû‹Å¢-À®Zâb@´š(è¶¯t˜(ÄM‰Á:Cƒ˜WUĞÄ{Wg”Q	åv(ºÂs	á8cé‰Â\$“ˆææûÂêk¬àsô …hâBgvï]ùW\n<)…D²zÌY1DşY'A#Ê‘a†--7È%-eºc\"VªñÊ\$Hğ»‘íÅ¢NyÊâ#@ GÉ 'è¬E…0¢\na>5ó—1öBœ\$©ÁCk¤çv.a©Œ/lÌ™±,,(ı!£bÈß˜JDØ®BEáq5L(ñ©(Ë\nLhŒ˜‚ôG„6 \rë_I‚T	ñ hG(€’ÂüMÄSL+ˆ¹Bdô[X/a™İ\n¡P#Ğpm„²y*ôÖ\"½º2`ã#¬%aŠ“¦´±««P\\°2T¡išC @‰CL\\„\n•­àì·mËèébú3œ!^ÅÍwª\"ñ\rˆ¡@:ì’3LÒu«	ohòañß%\"\\L	-\"\0¹›'/R=l\"0´¡vrËH¤*	;‹ÈxZ+y„^FB*%H(­)Ò¯1EWª²ŠR,";break;case"zh-tw":$g="ä^¨ê%Ó•\\šr¥ÑÎõâ|%ÌÂ:\$\ns¡.ešUÈ¸E9PK72©(æP¢h)Ê…@º:i	%“Êcè§Je åR)Ü«{º	Nd TâPˆ£\\ªÔÃ•8¨CˆÈf4†ãÌaS@/%Èäû•N‹¦¬’Ndâ%Ğ³C¹’É—B…Q+–¹Öê‡Bñ_MK,ª\$õÆçu»ŞowÔfš‚T9®WK´ÍÊW¹•ˆ§2mizX:P	—*‘½_/Ùg*eSLK¶Ûˆú™Î¹^9×HÌ\rºÛÕ7ºŒZz>‹ êÔ0)È¿Nï\nÙr!U=R\n¤ôÉÖ^¯ÜéJÅÑTçO©](ÅI–Ø^Ü«¥]EÌJ4\$yhr•ä2^?[ ô½eCr‘º^[#åk¢Ö‘g1'¤)ÌT'9jB)#›,§%')näªª»hV’èùdô=OaĞ@§IBO¤òàs¥Â¦K©¤¹Jºç12A\$±&ë8mQd€¨ÁlY»r—%ò\0J£1Ä¡ÌDÇ)*OÌŠTÍ4L°Ô9DÚB+ê°â°¥yÊL«)pYÊ@ÅÔs“%Ú^R©¥pr\$-G´ƒ˜Æ%,M•ÈxÈCÈè2…˜R¦“Ù SA bh©¤8¡®!v]œÄ!*åíBsÄ“öGIê~ƒ¥ÄZ<^“–i\\CD=šMÑÅi te­|[:ÅñtåS¬\\Xº°—\\W‡)]%Ñ\\	zŞêMF¡7Ä]–Ì±ÎG—Ê²°\$	Ğš&‡B˜¦cÎ,<‹¡pÚ6…Ã Ék[™ü“ PØ:L¹Pt“eM…ÑË…átº*T1FŞ×Å.ùŞ¥!c í7 \$	 HatAWğAÇI ª—ÄaÒC‘§ARS`ÈÔ€D&± †)ŠB0@:\rãXÊ70­‘­E^5IŠür’ÅtND'äTÙ^ñ9Oa:€F¥@‚2\r£HÜ2Zvåg1\nW(Ù¹Hµ¤ˆx0„@ä2ŒÁèD4ƒ à9‡Ax^;öpÃÈr\\ \\7C8_·…ã Ş7# Óá…á|s¤ƒD_!„AĞEèd±sÊôé†!à^0‡Áp)Œ£˜çãÚœW¤‘ÒG-n›¶yLæ—eÅE—Ù\$0JéİK«u®½Ø»7jíÜ‹“NíŞ»ğÜïÃÂ§\rÏä\"”›’r¢ÄJ½çÀœD°¹#‚Tt\ná&AœÊ’R‚rˆÁ,eŸ#æ}\rL_£‘@ô…«MI°ö\"qZ)Ğ \nìXv™ÜŸqˆ4s‰´„\$!6ÂH¡¡4ÏÄbõk#”O‹s(ØHÚ%éÑ¢3gğ»Kçı\0‹FÊ€H\n	š1!Ô?D#Ñ)Oqú•\"`L…‰ªk R¡`9ÄL\"4qpPFÆÙb˜‘´D¢s0aR™l¯İE!XĞ.åœs\nxÓQ¬4 A‹ÑÌ«J]JˆôuW©l¢nF’ÃÄ-‘ËJ%äÄ™É´`9…pµHá­”X¤'%	A5¨ –7#š9DP D¦ĞS!Ì'„àæ3,Í—QÊ/„ó.Q»·˜2,Dïnb\\N\$Ñ9¡ûÚ\"tQ` Â˜T \n¢€)Ì\0æl¡É\"ğlS8–Fğ°ÆM‰´››)2¤„™,*ø¯WÇÈ@	§Ø‚ P'dˆYĞtz\"Â˜Q	„àŞÎsœÍ™Â¤Á¥¿I|×ŸÒ‰¢¹üˆr„ø™</äÒšs§Tû~—Õ0ÄœÚ¡PæÜ¾«ü×Uu@gĞˆ1Bö@‰ñ~‰Ä`‰ <!²€ØrëIÑ9ã˜O“ó]4Ò‘”:â¸ÿ‹”NI‡,5S¥AÀª0-›Eô}{R‹òøŒ‘²:ÒY#)b‡™2üu…ı|‚åf‰’¤!…P \r\$OP¡6ªÇLôÃ§ÃÏe¶ò		È¹«İ}?ğD¨úšÓ!â¸Š=\n¬Ñ@ JlĞœâ0è½¢š\"Dœ·³}jÕº(DbùK^æHÈÅ½%ÖA{Ş´ü¦IqW ‡ğs+¥xx¬é²+ReÚD.QÒ<GÀ";break;}$tg=array();foreach(explode("\n",lzw_decompress($g))as$X)$tg[]=(strpos($X,"\t")?explode("\t",$X):$X);return$tg;}if(!$tg){$tg=get_translations($ba);$_SESSION["translations"]=$tg;}if(extension_loaded('pdo')){class
Min_PDO
extends
PDO{var$_result,$server_info,$affected_rows,$errno,$error;function
__construct(){global$b;$Ie=array_search("SQL",$b->operators);if($Ie!==false)unset($b->operators[$Ie]);}function
dsn($Kb,$V,$F,$C=array()){try{parent::__construct($Kb,$V,$F,$C);}catch(Exception$Yb){auth_error(h($Yb->getMessage()));}$this->setAttribute(13,array('Min_PDOStatement'));$this->server_info=@$this->getAttribute(4);}function
query($G,$Bg=false){$I=parent::query($G);$this->error="";if(!$I){list(,$this->errno,$this->error)=$this->errorInfo();if(!$this->error)$this->error=lang(21);return
false;}$this->store_result($I);return$I;}function
multi_query($G){return$this->_result=$this->query($G);}function
store_result($I=null){if(!$I){$I=$this->_result;if(!$I)return
false;}if($I->columnCount()){$I->num_rows=$I->rowCount();return$I;}$this->affected_rows=$I->rowCount();return
true;}function
next_result(){if(!$this->_result)return
false;$this->_result->_offset=0;return@$this->_result->nextRowset();}function
result($G,$p=0){$I=$this->query($G);if(!$I)return
false;$K=$I->fetch();return$K[$p];}}class
Min_PDOStatement
extends
PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch(2);}function
fetch_row(){return$this->fetch(3);}function
fetch_field(){$K=(object)$this->getColumnMeta($this->_offset++);$K->orgtable=$K->table;$K->orgname=$K->name;$K->charsetnr=(in_array("blob",(array)$K->flags)?63:0);return$K;}}}$Hb=array();class
Min_SQL{var$_conn;function
__construct($h){$this->_conn=$h;}function
select($R,$M,$Z,$Fc,$se=array(),$z=1,$D=0,$Ne=false){global$b,$x;$nd=(count($Fc)<count($M));$G=$b->selectQueryBuild($M,$Z,$Fc,$se,$z,$D);if(!$G)$G="SELECT".limit(($_GET["page"]!="last"&&$z!=""&&$Fc&&$nd&&$x=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($Fc&&$nd?"\nGROUP BY ".implode(", ",$Fc):"").($se?"\nORDER BY ".implode(", ",$se):""),($z!=""?+$z:null),($D?$z*$D:0),"\n");$Nf=microtime(true);$J=$this->_conn->query($G);if($Ne)echo$b->selectQuery($G,$Nf,!$J);return$J;}function
delete($R,$H,$z=0){$G="FROM ".table($R);return
queries("DELETE".($z?limit1($R,$G,$H):" $G$H"));}function
update($R,$P,$H,$z=0,$N="\n"){$Pg=array();foreach($P
as$y=>$X)$Pg[]="$y = $X";$G=table($R)." SET$N".implode(",$N",$Pg);return
queries("UPDATE".($z?limit1($R,$G,$H,$N):" $G$H"));}function
insert($R,$P){return
queries("INSERT INTO ".table($R).($P?" (".implode(", ",array_keys($P)).")\nVALUES (".implode(", ",$P).")":" DEFAULT VALUES"));}function
insertUpdate($R,$L,$Le){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($G,$hg){}function
convertSearch($u,$X,$p){return$u;}function
value($X,$p){return(method_exists($this->_conn,'value')?$this->_conn->value($X,$p):(is_resource($X)?stream_get_contents($X):$X));}function
quoteBinary($nf){return
q($nf);}function
warnings(){return'';}function
tableHelp($B){}}$Hb["sqlite"]="SQLite 3";$Hb["sqlite2"]="SQLite 2";if(isset($_GET["sqlite"])||isset($_GET["sqlite2"])){$Je=array((isset($_GET["sqlite"])?"SQLite3":"SQLite"),"PDO_SQLite");define("DRIVER",(isset($_GET["sqlite"])?"sqlite":"sqlite2"));if(class_exists(isset($_GET["sqlite"])?"SQLite3":"SQLiteDatabase")){if(isset($_GET["sqlite"])){class
Min_SQLite{var$extension="SQLite3",$server_info,$affected_rows,$errno,$error,$_link;function
__construct($r){$this->_link=new
SQLite3($r);$Rg=$this->_link->version();$this->server_info=$Rg["versionString"];}function
query($G){$I=@$this->_link->query($G);$this->error="";if(!$I){$this->errno=$this->_link->lastErrorCode();$this->error=$this->_link->lastErrorMsg();return
false;}elseif($I->numColumns())return
new
Min_Result($I);$this->affected_rows=$this->_link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->_link->escapeString($Q)."'":"x'".reset(unpack('H*',$Q))."'");}function
store_result(){return$this->_result;}function
result($G,$p=0){$I=$this->query($G);if(!is_object($I))return
false;$K=$I->_result->fetchArray();return$K[$p];}}class
Min_Result{var$_result,$_offset=0,$num_rows;function
__construct($I){$this->_result=$I;}function
fetch_assoc(){return$this->_result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->_result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$e=$this->_offset++;$U=$this->_result->columnType($e);return(object)array("name"=>$this->_result->columnName($e),"type"=>$U,"charsetnr"=>($U==SQLITE3_BLOB?63:0),);}function
__desctruct(){return$this->_result->finalize();}}}else{class
Min_SQLite{var$extension="SQLite",$server_info,$affected_rows,$error,$_link;function
__construct($r){$this->server_info=sqlite_libversion();$this->_link=new
SQLiteDatabase($r);}function
query($G,$Bg=false){$Vd=($Bg?"unbufferedQuery":"query");$I=@$this->_link->$Vd($G,SQLITE_BOTH,$o);$this->error="";if(!$I){$this->error=$o;return
false;}elseif($I===true){$this->affected_rows=$this->changes();return
true;}return
new
Min_Result($I);}function
quote($Q){return"'".sqlite_escape_string($Q)."'";}function
store_result(){return$this->_result;}function
result($G,$p=0){$I=$this->query($G);if(!is_object($I))return
false;$K=$I->_result->fetch();return$K[$p];}}class
Min_Result{var$_result,$_offset=0,$num_rows;function
__construct($I){$this->_result=$I;if(method_exists($I,'numRows'))$this->num_rows=$I->numRows();}function
fetch_assoc(){$K=$this->_result->fetch(SQLITE_ASSOC);if(!$K)return
false;$J=array();foreach($K
as$y=>$X)$J[($y[0]=='"'?idf_unescape($y):$y)]=$X;return$J;}function
fetch_row(){return$this->_result->fetch(SQLITE_NUM);}function
fetch_field(){$B=$this->_result->fieldName($this->_offset++);$Ee='(\[.*]|"(?:[^"]|"")*"|(.+))';if(preg_match("~^($Ee\\.)?$Ee\$~",$B,$A)){$R=($A[3]!=""?$A[3]:idf_unescape($A[2]));$B=($A[5]!=""?$A[5]:idf_unescape($A[4]));}return(object)array("name"=>$B,"orgname"=>$B,"orgtable"=>$R,);}}}}elseif(extension_loaded("pdo_sqlite")){class
Min_SQLite
extends
Min_PDO{var$extension="PDO_SQLite";function
__construct($r){$this->dsn(DRIVER.":$r","","");}}}if(class_exists("Min_SQLite")){class
Min_DB
extends
Min_SQLite{function
__construct(){parent::__construct(":memory:");$this->query("PRAGMA foreign_keys = 1");}function
select_db($r){if(is_readable($r)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$r)?$r:dirname($_SERVER["SCRIPT_FILENAME"])."/$r")." AS a")){parent::__construct($r);$this->query("PRAGMA foreign_keys = 1");return
true;}return
false;}function
multi_query($G){return$this->_result=$this->query($G);}function
next_result(){return
false;}}}class
Min_Driver
extends
Min_SQL{function
insertUpdate($R,$L,$Le){$Pg=array();foreach($L
as$P)$Pg[]="(".implode(", ",$P).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($L))).") VALUES\n".implode(",\n",$Pg));}function
tableHelp($B){if($B=="sqlite_sequence")return"fileformat2.html#seqtab";if($B=="sqlite_master")return"fileformat2.html#$B";}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
connect(){global$b;list(,,$F)=$b->credentials();if($F!="")return
lang(22);return
new
Min_DB;}function
get_databases(){return
array();}function
limit($G,$Z,$z,$ie=0,$N=" "){return" $G$Z".($z!==null?$N."LIMIT $z".($ie?" OFFSET $ie":""):"");}function
limit1($R,$G,$Z,$N="\n"){global$h;return(preg_match('~^INTO~',$G)||$h->result("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($G,$Z,1,0,$N):" $G WHERE rowid = (SELECT rowid FROM ".table($R).$Z.$N."LIMIT 1)");}function
db_collation($m,$eb){global$h;return$h->result("PRAGMA encoding");}function
engines(){return
array();}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name = 'sqlite_sequence'), name");}function
count_tables($l){return
array();}function
table_status($B=""){global$h;$J=array();foreach(get_rows("SELECT name AS Name, type AS Engine, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K){$K["Rows"]=$h->result("SELECT COUNT(*) FROM ".idf_escape($K["Name"]));$J[$K["Name"]]=$K;}foreach(get_rows("SELECT * FROM sqlite_sequence",null,"")as$K)$J[$K["name"]]["Auto_increment"]=$K["seq"];return($B!=""?$J[$B]:$J);}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){global$h;return!$h->result("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($R){global$h;$J=array();$Le="";foreach(get_rows("PRAGMA table_info(".table($R).")")as$K){$B=$K["name"];$U=strtolower($K["type"]);$zb=$K["dflt_value"];$J[$B]=array("field"=>$B,"type"=>(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":"numeric")))),"full_type"=>$U,"default"=>(preg_match("~'(.*)'~",$zb,$A)?str_replace("''","'",$A[1]):($zb=="NULL"?null:$zb)),"null"=>!$K["notnull"],"privileges"=>array("select"=>1,"insert"=>1,"update"=>1),"primary"=>$K["pk"],);if($K["pk"]){if($Le!="")$J[$Le]["auto_increment"]=false;elseif(preg_match('~^integer$~i',$U))$J[$B]["auto_increment"]=true;$Le=$B;}}$Kf=$h->result("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));preg_match_all('~(("[^"]*+")+|[a-z0-9_]+)\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$Kf,$Md,PREG_SET_ORDER);foreach($Md
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($J[$B])$J[$B]["collation"]=trim($A[3],"'");}return$J;}function
indexes($R,$i=null){global$h;if(!is_object($i))$i=$h;$J=array();$Kf=$i->result("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$Kf,$A)){$J[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$A[1],$Md,PREG_SET_ORDER);foreach($Md
as$A){$J[""]["columns"][]=idf_unescape($A[2]).$A[4];$J[""]["descs"][]=(preg_match('~DESC~i',$A[5])?'1':null);}}if(!$J){foreach(fields($R)as$B=>$p){if($p["primary"])$J[""]=array("type"=>"PRIMARY","columns"=>array($B),"lengths"=>array(),"descs"=>array(null));}}$Lf=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$i);foreach(get_rows("PRAGMA index_list(".table($R).")",$i)as$K){$B=$K["name"];$v=array("type"=>($K["unique"]?"UNIQUE":"INDEX"));$v["lengths"]=array();$v["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$i)as$mf){$v["columns"][]=$mf["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$Lf[$B],$af)){preg_match_all('/("[^"]*+")+( DESC)?/',$af[2],$Md);foreach($Md[2]as$y=>$X){if($X)$v["descs"][$y]='1';}}if(!$J[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$J[""]["columns"]||$v["descs"]!=$J[""]["descs"]||!preg_match("~^sqlite_~",$B))$J[$B]=$v;}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$K){$yc=&$J[$K["id"]];if(!$yc)$yc=$K;$yc["source"][]=$K["from"];$yc["target"][]=$K["to"];}return$J;}function
view($B){global$h;return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',$h->result("SELECT sql FROM sqlite_master WHERE name = ".q($B))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($m){return
false;}function
error(){global$h;return
h($h->error);}function
check_sqlite_name($B){global$h;$fc="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($fc)\$~",$B)){$h->error=lang(23,str_replace("|",", ",$fc));return
false;}return
true;}function
create_database($m,$d){global$h;if(file_exists($m)){$h->error=lang(24);return
false;}if(!check_sqlite_name($m))return
false;try{$_=new
Min_SQLite($m);}catch(Exception$Yb){$h->error=$Yb->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases($l){global$h;$h->__construct(":memory:");foreach($l
as$m){if(!@unlink($m)){$h->error=lang(24);return
false;}}return
true;}function
rename_database($B,$d){global$h;if(!check_sqlite_name($B))return
false;$h->__construct(":memory:");$h->error=lang(24);return@rename(DB,$B);}function
auto_increment(){return" PRIMARY KEY".(DRIVER=="sqlite"?" AUTOINCREMENT":"");}function
alter_table($R,$B,$q,$vc,$ib,$Tb,$d,$Fa,$Be){$Mg=($R==""||$vc);foreach($q
as$p){if($p[0]!=""||!$p[1]||$p[2]){$Mg=true;break;}}$c=array();$ve=array();foreach($q
as$p){if($p[1]){$c[]=($Mg?$p[1]:"ADD ".implode($p[1]));if($p[0]!="")$ve[$p[0]]=$p[1][0];}}if(!$Mg){foreach($c
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$B&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($R,$B,$c,$ve,$vc))return
false;if($Fa)queries("UPDATE sqlite_sequence SET seq = $Fa WHERE name = ".q($B));return
true;}function
recreate_table($R,$B,$q,$ve,$vc,$w=array()){if($R!=""){if(!$q){foreach(fields($R)as$y=>$p){if($w)$p["auto_increment"]=0;$q[]=process_field($p,$p);$ve[$y]=idf_escape($y);}}$Me=false;foreach($q
as$p){if($p[6])$Me=true;}$Jb=array();foreach($w
as$y=>$X){if($X[2]=="DROP"){$Jb[$X[1]]=true;unset($w[$y]);}}foreach(indexes($R)as$sd=>$v){$f=array();foreach($v["columns"]as$y=>$e){if(!$ve[$e])continue
2;$f[]=$ve[$e].($v["descs"][$y]?" DESC":"");}if(!$Jb[$sd]){if($v["type"]!="PRIMARY"||!$Me)$w[]=array($v["type"],$sd,$f);}}foreach($w
as$y=>$X){if($X[0]=="PRIMARY"){unset($w[$y]);$vc[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$sd=>$yc){foreach($yc["source"]as$y=>$e){if(!$ve[$e])continue
2;$yc["source"][$y]=idf_unescape($ve[$e]);}if(!isset($vc[" $sd"]))$vc[]=" ".format_foreign_key($yc);}queries("BEGIN");}foreach($q
as$y=>$p)$q[$y]="  ".implode($p);$q=array_merge($q,array_filter($vc));if(!queries("CREATE TABLE ".table($R!=""?"adminer_$B":$B)." (\n".implode(",\n",$q)."\n)"))return
false;if($R!=""){if($ve&&!queries("INSERT INTO ".table("adminer_$B")." (".implode(", ",$ve).") SELECT ".implode(", ",array_map('idf_escape',array_keys($ve)))." FROM ".table($R)))return
false;$zg=array();foreach(triggers($R)as$xg=>$ig){$wg=trigger($xg);$zg[]="CREATE TRIGGER ".idf_escape($xg)." ".implode(" ",$ig)." ON ".table($B)."\n$wg[Statement]";}if(!queries("DROP TABLE ".table($R)))return
false;queries("ALTER TABLE ".table("adminer_$B")." RENAME TO ".table($B));if(!alter_indexes($B,$w))return
false;foreach($zg
as$wg){if(!queries($wg))return
false;}queries("COMMIT");}return
true;}function
index_sql($R,$U,$B,$f){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($B!=""?$B:uniqid($R."_"))." ON ".table($R)." $f";}function
alter_indexes($R,$c){foreach($c
as$Le){if($Le[0]=="PRIMARY")return
recreate_table($R,$R,array(),array(),array(),$c);}foreach(array_reverse($c)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables($T){return
apply_queries("DELETE FROM",$T);}function
drop_views($Tg){return
apply_queries("DROP VIEW",$Tg);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
move_tables($T,$Tg,$bg){return
false;}function
trigger($B){global$h;if($B=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$yg=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$yg["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",$h->result("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$A);$he=$A[3];return
array("Timing"=>strtoupper($A[1]),"Event"=>strtoupper($A[2]).($he?" OF":""),"Of"=>($he[0]=='`'||$he[0]=='"'?idf_unescape($he):$he),"Trigger"=>$B,"Statement"=>$A[4],);}function
triggers($R){$J=array();$yg=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$K){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$yg["Timing"]).')\s*(.*)\s+ON\b~iU',$K["sql"],$A);$J[$K["name"]]=array($A[1],$A[2]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
begin(){return
queries("BEGIN");}function
last_id(){global$h;return$h->result("SELECT LAST_INSERT_ROWID()");}function
explain($h,$G){return$h->query("EXPLAIN QUERY PLAN $G");}function
found_rows($S,$Z){}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($pf){return
true;}function
create_sql($R,$Fa,$Sf){global$h;$J=$h->result("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$B=>$v){if($B=='')continue;$J.=";\n\n".index_sql($R,$v['type'],$B,"(".implode(", ",array_map('idf_escape',$v['columns'])).")");}return$J;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($k){}function
trigger_sql($R){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){global$h;$J=array();foreach(array("auto_vacuum","cache_size","count_changes","default_cache_size","empty_result_callbacks","encoding","foreign_keys","full_column_names","fullfsync","journal_mode","journal_size_limit","legacy_file_format","locking_mode","page_size","max_page_count","read_uncommitted","recursive_triggers","reverse_unordered_selects","secure_delete","short_column_names","synchronous","temp_store","temp_store_directory","schema_version","integrity_check","quick_check")as$y)$J[$y]=$h->result("PRAGMA $y");return$J;}function
show_status(){$J=array();foreach(get_vals("PRAGMA compile_options")as$qe){list($y,$X)=explode("=",$qe,2);$J[$y]=$X;}return$J;}function
convert_field($p){}function
unconvert_field($p,$J){return$J;}function
support($jc){return
preg_match('~^(columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$jc);}$x="sqlite";$Ag=array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0);$Rf=array_keys($Ag);$Hg=array();$pe=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");$Ec=array("hex","length","lower","round","unixepoch","upper");$Ic=array("avg","count","count distinct","group_concat","max","min","sum");$Mb=array(array(),array("integer|real|numeric"=>"+/-","text"=>"||",));}$Hb["pgsql"]="PostgreSQL";if(isset($_GET["pgsql"])){$Je=array("PgSQL","PDO_PgSQL");define("DRIVER","pgsql");if(extension_loaded("pgsql")){class
Min_DB{var$extension="PgSQL",$_link,$_result,$_string,$_database=true,$server_info,$affected_rows,$error,$timeout;function
_error($Wb,$o){if(ini_bool("html_errors"))$o=html_entity_decode(strip_tags($o));$o=preg_replace('~^[^:]*: ~','',$o);$this->error=$o;}function
connect($O,$V,$F){global$b;$m=$b->database();set_error_handler(array($this,'_error'));$this->_string="host='".str_replace(":","' port='",addcslashes($O,"'\\"))."' user='".addcslashes($V,"'\\")."' password='".addcslashes($F,"'\\")."'";$this->_link=@pg_connect("$this->_string dbname='".($m!=""?addcslashes($m,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->_link&&$m!=""){$this->_database=false;$this->_link=@pg_connect("$this->_string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->_link){$Rg=pg_version($this->_link);$this->server_info=$Rg["server"];pg_set_client_encoding($this->_link,"UTF8");}return(bool)$this->_link;}function
quote($Q){return"'".pg_escape_string($this->_link,$Q)."'";}function
value($X,$p){return($p["type"]=="bytea"?pg_unescape_bytea($X):$X);}function
quoteBinary($Q){return"'".pg_escape_bytea($this->_link,$Q)."'";}function
select_db($k){global$b;if($k==$b->database())return$this->_database;$J=@pg_connect("$this->_string dbname='".addcslashes($k,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($J)$this->_link=$J;return$J;}function
close(){$this->_link=@pg_connect("$this->_string dbname='postgres'");}function
query($G,$Bg=false){$I=@pg_query($this->_link,$G);$this->error="";if(!$I){$this->error=pg_last_error($this->_link);$J=false;}elseif(!pg_num_fields($I)){$this->affected_rows=pg_affected_rows($I);$J=true;}else$J=new
Min_Result($I);if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$J;}function
multi_query($G){return$this->_result=$this->query($G);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($G,$p=0){$I=$this->query($G);if(!$I||!$I->num_rows)return
false;return
pg_fetch_result($I->_result,0,$p);}function
warnings(){return
h(pg_last_notice($this->_link));}}class
Min_Result{var$_result,$_offset=0,$num_rows;function
__construct($I){$this->_result=$I;$this->num_rows=pg_num_rows($I);}function
fetch_assoc(){return
pg_fetch_assoc($this->_result);}function
fetch_row(){return
pg_fetch_row($this->_result);}function
fetch_field(){$e=$this->_offset++;$J=new
stdClass;if(function_exists('pg_field_table'))$J->orgtable=pg_field_table($this->_result,$e);$J->name=pg_field_name($this->_result,$e);$J->orgname=$J->name;$J->type=pg_field_type($this->_result,$e);$J->charsetnr=($J->type=="bytea"?63:0);return$J;}function
__destruct(){pg_free_result($this->_result);}}}elseif(extension_loaded("pdo_pgsql")){class
Min_DB
extends
Min_PDO{var$extension="PDO_PgSQL",$timeout;function
connect($O,$V,$F){global$b;$m=$b->database();$Q="pgsql:host='".str_replace(":","' port='",addcslashes($O,"'\\"))."' options='-c client_encoding=utf8'";$this->dsn("$Q dbname='".($m!=""?addcslashes($m,"'\\"):"postgres")."'",$V,$F);return
true;}function
select_db($k){global$b;return($b->database()==$k);}function
quoteBinary($nf){return
q($nf);}function
query($G,$Bg=false){$J=parent::query($G,$Bg);if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$J;}function
warnings(){return'';}function
close(){}}}class
Min_Driver
extends
Min_SQL{function
insertUpdate($R,$L,$Le){global$h;foreach($L
as$P){$Ig=array();$Z=array();foreach($P
as$y=>$X){$Ig[]="$y = $X";if(isset($Le[idf_unescape($y)]))$Z[]="$y = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$Ig)." WHERE ".implode(" AND ",$Z))&&$h->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($P)).") VALUES (".implode(", ",$P).")")))return
false;}return
true;}function
slowQuery($G,$hg){$this->_conn->query("SET statement_timeout = ".(1000*$hg));$this->_conn->timeout=1000*$hg;return$G;}function
convertSearch($u,$X,$p){return(preg_match('~char|text'.(!preg_match('~LIKE~',$X["op"])?'|date|time(stamp)?|boolean|uuid|'.number_type():'').'~',$p["type"])?$u:"CAST($u AS text)");}function
quoteBinary($nf){return$this->_conn->quoteBinary($nf);}function
warnings(){return$this->_conn->warnings();}function
tableHelp($B){$Ed=array("information_schema"=>"infoschema","pg_catalog"=>"catalog",);$_=$Ed[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$B).".html";}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
connect(){global$b,$Ag,$Rf;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2])){if(min_version(9,0,$h)){$h->query("SET application_name = 'Adminer'");if(min_version(9.2,0,$h)){$Rf[lang(25)][]="json";$Ag["json"]=4294967295;if(min_version(9.4,0,$h)){$Rf[lang(25)][]="jsonb";$Ag["jsonb"]=4294967295;}}}return$h;}return$h->error;}function
get_databases(){return
get_vals("SELECT datname FROM pg_database WHERE has_database_privilege(datname, 'CONNECT') ORDER BY datname");}function
limit($G,$Z,$z,$ie=0,$N=" "){return" $G$Z".($z!==null?$N."LIMIT $z".($ie?" OFFSET $ie":""):"");}function
limit1($R,$G,$Z,$N="\n"){return(preg_match('~^INTO~',$G)?limit($G,$Z,1,0,$N):" $G".(is_view(table_status1($R))?$Z:" WHERE ctid = (SELECT ctid FROM ".table($R).$Z.$N."LIMIT 1)"));}function
db_collation($m,$eb){global$h;return$h->result("SHOW LC_COLLATE");}function
engines(){return
array();}function
logged_user(){global$h;return$h->result("SELECT user");}function
tables_list(){$G="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support('materializedview'))$G.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$G.="
ORDER BY 1";return
get_key_vals($G);}function
count_tables($l){return
array();}function
table_status($B=""){$J=array();foreach(get_rows("SELECT c.relname AS \"Name\", CASE c.relkind WHEN 'r' THEN 'table' WHEN 'm' THEN 'materialized view' ELSE 'view' END AS \"Engine\", pg_relation_size(c.oid) AS \"Data_length\", pg_total_relation_size(c.oid) - pg_relation_size(c.oid) AS \"Index_length\", obj_description(c.oid, 'pg_class') AS \"Comment\", CASE WHEN c.relhasoids THEN 'oid' ELSE '' END AS \"Oid\", c.reltuples as \"Rows\", n.nspname
FROM pg_class c
JOIN pg_namespace n ON(n.nspname = current_schema() AND n.oid = c.relnamespace)
WHERE relkind IN ('r', 'm', 'v', 'f')
".($B!=""?"AND relname = ".q($B):"ORDER BY relname"))as$K)$J[$K["Name"]]=$K;return($B!=""?$J[$B]:$J);}function
is_view($S){return
in_array($S["Engine"],array("view","materialized view"));}function
fk_support($S){return
true;}function
fields($R){$J=array();$xa=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',);$Vc=min_version(10)?"(a.attidentity = 'd')::int":'0';foreach(get_rows("SELECT a.attname AS field, format_type(a.atttypid, a.atttypmod) AS full_type, d.adsrc AS default, a.attnotnull::int, col_description(c.oid, a.attnum) AS comment, $Vc AS identity
FROM pg_class c
JOIN pg_namespace n ON c.relnamespace = n.oid
JOIN pg_attribute a ON c.oid = a.attrelid
LEFT JOIN pg_attrdef d ON c.oid = d.adrelid AND a.attnum = d.adnum
WHERE c.relname = ".q($R)."
AND n.nspname = current_schema()
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$K){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$K["full_type"],$A);list(,$U,$Bd,$K["length"],$sa,$za)=$A;$K["length"].=$za;$Va=$U.$sa;if(isset($xa[$Va])){$K["type"]=$xa[$Va];$K["full_type"]=$K["type"].$Bd.$za;}else{$K["type"]=$U;$K["full_type"]=$K["type"].$Bd.$sa.$za;}if($K['identity'])$K['default']='GENERATED BY DEFAULT AS IDENTITY';$K["null"]=!$K["attnotnull"];$K["auto_increment"]=$K['identity']||preg_match('~^nextval\(~i',$K["default"]);$K["privileges"]=array("insert"=>1,"select"=>1,"update"=>1);if(preg_match('~(.+)::[^)]+(.*)~',$K["default"],$A))$K["default"]=($A[1]=="NULL"?null:(($A[1][0]=="'"?idf_unescape($A[1]):$A[1]).$A[2]));$J[$K["field"]]=$K;}return$J;}function
indexes($R,$i=null){global$h;if(!is_object($i))$i=$h;$J=array();$Zf=$i->result("SELECT oid FROM pg_class WHERE relnamespace = (SELECT oid FROM pg_namespace WHERE nspname = current_schema()) AND relname = ".q($R));$f=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Zf AND attnum > 0",$i);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption , (indpred IS NOT NULL)::int as indispartial FROM pg_index i, pg_class ci WHERE i.indrelid = $Zf AND ci.oid = i.indexrelid",$i)as$K){$bf=$K["relname"];$J[$bf]["type"]=($K["indispartial"]?"INDEX":($K["indisprimary"]?"PRIMARY":($K["indisunique"]?"UNIQUE":"INDEX")));$J[$bf]["columns"]=array();foreach(explode(" ",$K["indkey"])as$dd)$J[$bf]["columns"][]=$f[$dd];$J[$bf]["descs"]=array();foreach(explode(" ",$K["indoption"])as$ed)$J[$bf]["descs"][]=($ed&1?'1':null);$J[$bf]["lengths"]=array();}return$J;}function
foreign_keys($R){global$ke;$J=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = (SELECT pc.oid FROM pg_class AS pc INNER JOIN pg_namespace AS pn ON (pn.oid = pc.relnamespace) WHERE pc.relname = ".q($R)." AND pn.nspname = current_schema())
AND contype = 'f'::char
ORDER BY conkey, conname")as$K){if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$K['definition'],$A)){$K['source']=array_map('trim',explode(',',$A[1]));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$A[2],$Ld)){$K['ns']=str_replace('""','"',preg_replace('~^"(.+)"$~','\1',$Ld[2]));$K['table']=str_replace('""','"',preg_replace('~^"(.+)"$~','\1',$Ld[4]));}$K['target']=array_map('trim',explode(',',$A[3]));$K['on_delete']=(preg_match("~ON DELETE ($ke)~",$A[4],$Ld)?$Ld[1]:'NO ACTION');$K['on_update']=(preg_match("~ON UPDATE ($ke)~",$A[4],$Ld)?$Ld[1]:'NO ACTION');$J[$K['conname']]=$K;}}return$J;}function
view($B){global$h;return
array("select"=>trim($h->result("SELECT view_definition
FROM information_schema.views
WHERE table_schema = current_schema() AND table_name = ".q($B))));}function
collations(){return
array();}function
information_schema($m){return($m=="information_schema");}function
error(){global$h;$J=h($h->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$J,$A))$J=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
nl_br($J);}function
create_database($m,$d){return
queries("CREATE DATABASE ".idf_escape($m).($d?" ENCODING ".idf_escape($d):""));}function
drop_databases($l){global$h;$h->close();return
apply_queries("DROP DATABASE",$l,'idf_escape');}function
rename_database($B,$d){return
queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($R,$B,$q,$vc,$ib,$Tb,$d,$Fa,$Be){$c=array();$Te=array();foreach($q
as$p){$e=idf_escape($p[0]);$X=$p[1];if(!$X)$c[]="DROP $e";else{$Og=$X[5];unset($X[5]);if(isset($X[6])&&$p[0]=="")$X[1]=($X[1]=="bigint"?" big":" ")."serial";if($p[0]=="")$c[]=($R!=""?"ADD ":"  ").implode($X);else{if($e!=$X[0])$Te[]="ALTER TABLE ".table($R)." RENAME $e TO $X[0]";$c[]="ALTER $e TYPE$X[1]";if(!$X[6]){$c[]="ALTER $e ".($X[3]?"SET$X[3]":"DROP DEFAULT");$c[]="ALTER $e ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}}if($p[0]!=""||$Og!="")$Te[]="COMMENT ON COLUMN ".table($R).".$X[0] IS ".($Og!=""?substr($Og,9):"''");}}$c=array_merge($c,$vc);if($R=="")array_unshift($Te,"CREATE TABLE ".table($B)." (\n".implode(",\n",$c)."\n)");elseif($c)array_unshift($Te,"ALTER TABLE ".table($R)."\n".implode(",\n",$c));if($R!=""&&$R!=$B)$Te[]="ALTER TABLE ".table($R)." RENAME TO ".table($B);if($R!=""||$ib!="")$Te[]="COMMENT ON TABLE ".table($B)." IS ".q($ib);if($Fa!=""){}foreach($Te
as$G){if(!queries($G))return
false;}return
true;}function
alter_indexes($R,$c){$qb=array();$Ib=array();$Te=array();foreach($c
as$X){if($X[0]!="INDEX")$qb[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$Ib[]=idf_escape($X[1]);else$Te[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($qb)array_unshift($Te,"ALTER TABLE ".table($R).implode(",",$qb));if($Ib)array_unshift($Te,"DROP INDEX ".implode(", ",$Ib));foreach($Te
as$G){if(!queries($G))return
false;}return
true;}function
truncate_tables($T){return
queries("TRUNCATE ".implode(", ",array_map('table',$T)));return
true;}function
drop_views($Tg){return
drop_tables($Tg);}function
drop_tables($T){foreach($T
as$R){$Pf=table_status($R);if(!queries("DROP ".strtoupper($Pf["Engine"])." ".table($R)))return
false;}return
true;}function
move_tables($T,$Tg,$bg){foreach(array_merge($T,$Tg)as$R){$Pf=table_status($R);if(!queries("ALTER ".strtoupper($Pf["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($bg)))return
false;}return
true;}function
trigger($B,$R=null){if($B=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");if($R===null)$R=$_GET['trigger'];$L=get_rows('SELECT t.trigger_name AS "Trigger", t.action_timing AS "Timing", (SELECT STRING_AGG(event_manipulation, \' OR \') FROM information_schema.triggers WHERE event_object_table = t.event_object_table AND trigger_name = t.trigger_name ) AS "Events", t.event_manipulation AS "Event", \'FOR EACH \' || t.action_orientation AS "Type", t.action_statement AS "Statement" FROM information_schema.triggers t WHERE t.event_object_table = '.q($R).' AND t.trigger_name = '.q($B));return
reset($L);}function
triggers($R){$J=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE event_object_table = ".q($R))as$K)$J[$K["trigger_name"]]=array($K["action_timing"],$K["event_manipulation"]);return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($B,$U){$L=get_rows('SELECT routine_definition AS definition, LOWER(external_language) AS language, *
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($B));$J=$L[0];$J["returns"]=array("type"=>$J["type_udt_name"]);$J["fields"]=get_rows('SELECT parameter_name AS field, data_type AS type, character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = '.q($B).'
ORDER BY ordinal_position');return$J;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($B,$K){$J=array();foreach($K["fields"]as$p)$J[]=$p["type"];return
idf_escape($B)."(".implode(", ",$J).")";}function
last_id(){return
0;}function
explain($h,$G){return$h->query("EXPLAIN $G");}function
found_rows($S,$Z){global$h;if(preg_match("~ rows=([0-9]+)~",$h->result("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$af))return$af[1];return
false;}function
types(){return
get_vals("SELECT typname
FROM pg_type
WHERE typnamespace = (SELECT oid FROM pg_namespace WHERE nspname = current_schema())
AND typtype IN ('b','d','e')
AND typelem = 0");}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){global$h;return$h->result("SELECT current_schema()");}function
set_schema($of){global$h,$Ag,$Rf;$J=$h->query("SET search_path TO ".idf_escape($of));foreach(types()as$U){if(!isset($Ag[$U])){$Ag[$U]=0;$Rf[lang(26)][]=$U;}}return$J;}function
create_sql($R,$Fa,$Sf){global$h;$J='';$kf=array();$yf=array();$Pf=table_status($R);$q=fields($R);$w=indexes($R);ksort($w);$sc=foreign_keys($R);ksort($sc);if(!$Pf||empty($q))return
false;$J="CREATE TABLE ".idf_escape($Pf['nspname']).".".idf_escape($Pf['Name'])." (\n    ";foreach($q
as$kc=>$p){$Ae=idf_escape($p['field']).' '.$p['full_type'].default_value($p).($p['attnotnull']?" NOT NULL":"");$kf[]=$Ae;if(preg_match('~nextval\(\'([^\']+)\'\)~',$p['default'],$Md)){$xf=$Md[1];$Jf=reset(get_rows(min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q($xf):"SELECT * FROM $xf"));$yf[]=($Sf=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $xf;\n":"")."CREATE SEQUENCE $xf INCREMENT $Jf[increment_by] MINVALUE $Jf[min_value] MAXVALUE $Jf[max_value] START ".($Fa?$Jf['last_value']:1)." CACHE $Jf[cache_value];";}}if(!empty($yf))$J=implode("\n\n",$yf)."\n\n$J";foreach($w
as$Yc=>$v){switch($v['type']){case'UNIQUE':$kf[]="CONSTRAINT ".idf_escape($Yc)." UNIQUE (".implode(', ',array_map('idf_escape',$v['columns'])).")";break;case'PRIMARY':$kf[]="CONSTRAINT ".idf_escape($Yc)." PRIMARY KEY (".implode(', ',array_map('idf_escape',$v['columns'])).")";break;}}foreach($sc
as$rc=>$qc)$kf[]="CONSTRAINT ".idf_escape($rc)." $qc[definition] ".($qc['deferrable']?'DEFERRABLE':'NOT DEFERRABLE');$J.=implode(",\n    ",$kf)."\n) WITH (oids = ".($Pf['Oid']?'true':'false').");";foreach($w
as$Yc=>$v){if($v['type']=='INDEX'){$f=array();foreach($v['columns']as$y=>$X)$f[]=idf_escape($X).($v['descs'][$y]?" DESC":"");$J.="\n\nCREATE INDEX ".idf_escape($Yc)." ON ".idf_escape($Pf['nspname']).".".idf_escape($Pf['Name'])." USING btree (".implode(', ',$f).");";}}if($Pf['Comment'])$J.="\n\nCOMMENT ON TABLE ".idf_escape($Pf['nspname']).".".idf_escape($Pf['Name'])." IS ".q($Pf['Comment']).";";foreach($q
as$kc=>$p){if($p['comment'])$J.="\n\nCOMMENT ON COLUMN ".idf_escape($Pf['nspname']).".".idf_escape($Pf['Name']).".".idf_escape($kc)." IS ".q($p['comment']).";";}return
rtrim($J,';');}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
trigger_sql($R){$Pf=table_status($R);$J="";foreach(triggers($R)as$vg=>$ug){$wg=trigger($vg,$Pf['Name']);$J.="\nCREATE TRIGGER ".idf_escape($wg['Trigger'])." $wg[Timing] $wg[Events] ON ".idf_escape($Pf["nspname"]).".".idf_escape($Pf['Name'])." $wg[Type] $wg[Statement];;\n";}return$J;}function
use_sql($k){return"\connect ".idf_escape($k);}function
show_variables(){return
get_key_vals("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
show_status(){}function
convert_field($p){}function
unconvert_field($p,$J){return$J;}function
support($jc){return
preg_match('~^(database|table|columns|sql|indexes|descidx|comment|view|'.(min_version(9.3)?'materializedview|':'').'scheme|routine|processlist|sequence|trigger|type|variables|drop_col|kill|dump)$~',$jc);}function
kill_process($X){return
queries("SELECT pg_terminate_backend(".number($X).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){global$h;return$h->result("SHOW max_connections");}$x="pgsql";$Ag=array();$Rf=array();foreach(array(lang(27)=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),lang(28)=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),lang(25)=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),lang(29)=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),lang(30)=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"txid_snapshot"=>0),lang(31)=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),)as$y=>$X){$Ag+=$X;$Rf[$y]=array_keys($X);}$Hg=array();$pe=array("=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");$Ec=array("char_length","lower","round","to_hex","to_timestamp","upper");$Ic=array("avg","count","count distinct","max","min","sum");$Mb=array(array("char"=>"md5","date|time"=>"now",),array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",));}$Hb["oracle"]="Oracle (beta)";if(isset($_GET["oracle"])){$Je=array("OCI8","PDO_OCI");define("DRIVER","oracle");if(extension_loaded("oci8")){class
Min_DB{var$extension="oci8",$_link,$_result,$server_info,$affected_rows,$errno,$error;function
_error($Wb,$o){if(ini_bool("html_errors"))$o=html_entity_decode(strip_tags($o));$o=preg_replace('~^[^:]*: ~','',$o);$this->error=$o;}function
connect($O,$V,$F){$this->_link=@oci_new_connect($V,$F,$O,"AL32UTF8");if($this->_link){$this->server_info=oci_server_version($this->_link);return
true;}$o=oci_error();$this->error=$o["message"];return
false;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($k){return
true;}function
query($G,$Bg=false){$I=oci_parse($this->_link,$G);$this->error="";if(!$I){$o=oci_error($this->_link);$this->errno=$o["code"];$this->error=$o["message"];return
false;}set_error_handler(array($this,'_error'));$J=@oci_execute($I);restore_error_handler();if($J){if(oci_num_fields($I))return
new
Min_Result($I);$this->affected_rows=oci_num_rows($I);}return$J;}function
multi_query($G){return$this->_result=$this->query($G);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($G,$p=1){$I=$this->query($G);if(!is_object($I)||!oci_fetch($I->_result))return
false;return
oci_result($I->_result,$p);}}class
Min_Result{var$_result,$_offset=1,$num_rows;function
__construct($I){$this->_result=$I;}function
_convert($K){foreach((array)$K
as$y=>$X){if(is_a($X,'OCI-Lob'))$K[$y]=$X->load();}return$K;}function
fetch_assoc(){return$this->_convert(oci_fetch_assoc($this->_result));}function
fetch_row(){return$this->_convert(oci_fetch_row($this->_result));}function
fetch_field(){$e=$this->_offset++;$J=new
stdClass;$J->name=oci_field_name($this->_result,$e);$J->orgname=$J->name;$J->type=oci_field_type($this->_result,$e);$J->charsetnr=(preg_match("~raw|blob|bfile~",$J->type)?63:0);return$J;}function
__destruct(){oci_free_statement($this->_result);}}}elseif(extension_loaded("pdo_oci")){class
Min_DB
extends
Min_PDO{var$extension="PDO_OCI";function
connect($O,$V,$F){$this->dsn("oci:dbname=//$O;charset=AL32UTF8",$V,$F);return
true;}function
select_db($k){return
true;}}}class
Min_Driver
extends
Min_SQL{function
begin(){return
true;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
connect(){global$b;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2]))return$h;return$h->error;}function
get_databases(){return
get_vals("SELECT tablespace_name FROM user_tablespaces");}function
limit($G,$Z,$z,$ie=0,$N=" "){return($ie?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $G$Z) t WHERE rownum <= ".($z+$ie).") WHERE rnum > $ie":($z!==null?" * FROM (SELECT $G$Z) WHERE rownum <= ".($z+$ie):" $G$Z"));}function
limit1($R,$G,$Z,$N="\n"){return" $G$Z";}function
db_collation($m,$eb){global$h;return$h->result("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
engines(){return
array();}function
logged_user(){global$h;return$h->result("SELECT USER FROM DUAL");}function
tables_list(){return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."
UNION SELECT view_name, 'view' FROM user_views
ORDER BY 1");}function
count_tables($l){return
array();}function
table_status($B=""){$J=array();$qf=q($B);foreach(get_rows('SELECT table_name "Name", \'table\' "Engine", avg_row_len * num_rows "Data_length", num_rows "Rows" FROM all_tables WHERE tablespace_name = '.q(DB).($B!=""?" AND table_name = $qf":"")."
UNION SELECT view_name, 'view', 0, 0 FROM user_views".($B!=""?" WHERE view_name = $qf":"")."
ORDER BY 1")as$K){if($B!="")return$K;$J[$K["Name"]]=$K;}return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return
true;}function
fields($R){$J=array();foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)." ORDER BY column_id")as$K){$U=$K["DATA_TYPE"];$Bd="$K[DATA_PRECISION],$K[DATA_SCALE]";if($Bd==",")$Bd=$K["DATA_LENGTH"];$J[$K["COLUMN_NAME"]]=array("field"=>$K["COLUMN_NAME"],"full_type"=>$U.($Bd?"($Bd)":""),"type"=>strtolower($U),"length"=>$Bd,"default"=>$K["DATA_DEFAULT"],"null"=>($K["NULLABLE"]=="Y"),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),);}return$J;}function
indexes($R,$i=null){$J=array();foreach(get_rows("SELECT uic.*, uc.constraint_type
FROM user_ind_columns uic
LEFT JOIN user_constraints uc ON uic.index_name = uc.constraint_name AND uic.table_name = uc.table_name
WHERE uic.table_name = ".q($R)."
ORDER BY uc.constraint_type, uic.column_position",$i)as$K){$Yc=$K["INDEX_NAME"];$J[$Yc]["type"]=($K["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($K["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$J[$Yc]["columns"][]=$K["COLUMN_NAME"];$J[$Yc]["lengths"][]=($K["CHAR_LENGTH"]&&$K["CHAR_LENGTH"]!=$K["COLUMN_LENGTH"]?$K["CHAR_LENGTH"]:null);$J[$Yc]["descs"][]=($K["DESCEND"]?'1':null);}return$J;}function
view($B){$L=get_rows('SELECT text "select" FROM user_views WHERE view_name = '.q($B));return
reset($L);}function
collations(){return
array();}function
information_schema($m){return
false;}function
error(){global$h;return
h($h->error);}function
explain($h,$G){$h->query("EXPLAIN PLAN FOR $G");return$h->query("SELECT * FROM plan_table");}function
found_rows($S,$Z){}function
alter_table($R,$B,$q,$vc,$ib,$Tb,$d,$Fa,$Be){$c=$Ib=array();foreach($q
as$p){$X=$p[1];if($X&&$p[0]!=""&&idf_escape($p[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($p[0])." TO $X[0]");if($X)$c[]=($R!=""?($p[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");else$Ib[]=idf_escape($p[0]);}if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$c)."\n)");return(!$c||queries("ALTER TABLE ".table($R)."\n".implode("\n",$c)))&&(!$Ib||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$Ib).")"))&&($R==$B||queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)));}function
foreign_keys($R){$J=array();$G="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($R);foreach(get_rows($G)as$K)$J[$K['NAME']]=array("db"=>$K['DEST_DB'],"table"=>$K['DEST_TABLE'],"source"=>array($K['SRC_COLUMN']),"target"=>array($K['DEST_COLUMN']),"on_delete"=>$K['ON_DELETE'],"on_update"=>null,);return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Tg){return
apply_queries("DROP VIEW",$Tg);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
last_id(){return
0;}function
schemas(){return
get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX'))");}function
get_schema(){global$h;return$h->result("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($pf){global$h;return$h->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($pf));}function
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
convert_field($p){}function
unconvert_field($p,$J){return$J;}function
support($jc){return
preg_match('~^(columns|database|drop_col|indexes|descidx|processlist|scheme|sql|status|table|variables|view|view_trigger)$~',$jc);}$x="oracle";$Ag=array();$Rf=array();foreach(array(lang(27)=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),lang(28)=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),lang(25)=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),lang(29)=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),)as$y=>$X){$Ag+=$X;$Rf[$y]=array_keys($X);}$Hg=array();$pe=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");$Ec=array("length","lower","round","upper");$Ic=array("avg","count","count distinct","max","min","sum");$Mb=array(array("date"=>"current_date","timestamp"=>"current_timestamp",),array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",));}$Hb["mssql"]="MS SQL (beta)";if(isset($_GET["mssql"])){$Je=array("SQLSRV","MSSQL","PDO_DBLIB");define("DRIVER","mssql");if(extension_loaded("sqlsrv")){class
Min_DB{var$extension="sqlsrv",$_link,$_result,$server_info,$affected_rows,$errno,$error;function
_get_error(){$this->error="";foreach(sqlsrv_errors()as$o){$this->errno=$o["code"];$this->error.="$o[message]\n";}$this->error=rtrim($this->error);}function
connect($O,$V,$F){global$b;$m=$b->database();$lb=array("UID"=>$V,"PWD"=>$F,"CharacterSet"=>"UTF-8");if($m!="")$lb["Database"]=$m;$this->_link=@sqlsrv_connect(preg_replace('~:~',',',$O),$lb);if($this->_link){$fd=sqlsrv_server_info($this->_link);$this->server_info=$fd['SQLServerVersion'];}else$this->_get_error();return(bool)$this->_link;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($k){return$this->query("USE ".idf_escape($k));}function
query($G,$Bg=false){$I=sqlsrv_query($this->_link,$G);$this->error="";if(!$I){$this->_get_error();return
false;}return$this->store_result($I);}function
multi_query($G){$this->_result=sqlsrv_query($this->_link,$G);$this->error="";if(!$this->_result){$this->_get_error();return
false;}return
true;}function
store_result($I=null){if(!$I)$I=$this->_result;if(!$I)return
false;if(sqlsrv_field_metadata($I))return
new
Min_Result($I);$this->affected_rows=sqlsrv_rows_affected($I);return
true;}function
next_result(){return$this->_result?sqlsrv_next_result($this->_result):null;}function
result($G,$p=0){$I=$this->query($G);if(!is_object($I))return
false;$K=$I->fetch_row();return$K[$p];}}class
Min_Result{var$_result,$_offset=0,$_fields,$num_rows;function
__construct($I){$this->_result=$I;}function
_convert($K){foreach((array)$K
as$y=>$X){if(is_a($X,'DateTime'))$K[$y]=$X->format("Y-m-d H:i:s");}return$K;}function
fetch_assoc(){return$this->_convert(sqlsrv_fetch_array($this->_result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->_convert(sqlsrv_fetch_array($this->_result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->_fields)$this->_fields=sqlsrv_field_metadata($this->_result);$p=$this->_fields[$this->_offset++];$J=new
stdClass;$J->name=$p["Name"];$J->orgname=$p["Name"];$J->type=($p["Type"]==1?254:0);return$J;}function
seek($ie){for($s=0;$s<$ie;$s++)sqlsrv_fetch($this->_result);}function
__destruct(){sqlsrv_free_stmt($this->_result);}}}elseif(extension_loaded("mssql")){class
Min_DB{var$extension="MSSQL",$_link,$_result,$server_info,$affected_rows,$error;function
connect($O,$V,$F){$this->_link=@mssql_connect($O,$V,$F);if($this->_link){$I=$this->query("SELECT SERVERPROPERTY('ProductLevel'), SERVERPROPERTY('Edition')");if($I){$K=$I->fetch_row();$this->server_info=$this->result("sp_server_info 2",2)." [$K[0]] $K[1]";}}else$this->error=mssql_get_last_message();return(bool)$this->_link;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($k){return
mssql_select_db($k);}function
query($G,$Bg=false){$I=@mssql_query($G,$this->_link);$this->error="";if(!$I){$this->error=mssql_get_last_message();return
false;}if($I===true){$this->affected_rows=mssql_rows_affected($this->_link);return
true;}return
new
Min_Result($I);}function
multi_query($G){return$this->_result=$this->query($G);}function
store_result(){return$this->_result;}function
next_result(){return
mssql_next_result($this->_result->_result);}function
result($G,$p=0){$I=$this->query($G);if(!is_object($I))return
false;return
mssql_result($I->_result,0,$p);}}class
Min_Result{var$_result,$_offset=0,$_fields,$num_rows;function
__construct($I){$this->_result=$I;$this->num_rows=mssql_num_rows($I);}function
fetch_assoc(){return
mssql_fetch_assoc($this->_result);}function
fetch_row(){return
mssql_fetch_row($this->_result);}function
num_rows(){return
mssql_num_rows($this->_result);}function
fetch_field(){$J=mssql_fetch_field($this->_result);$J->orgtable=$J->table;$J->orgname=$J->name;return$J;}function
seek($ie){mssql_data_seek($this->_result,$ie);}function
__destruct(){mssql_free_result($this->_result);}}}elseif(extension_loaded("pdo_dblib")){class
Min_DB
extends
Min_PDO{var$extension="PDO_DBLIB";function
connect($O,$V,$F){$this->dsn("dblib:charset=utf8;host=".str_replace(":",";unix_socket=",preg_replace('~:(\d)~',';port=\1',$O)),$V,$F);return
true;}function
select_db($k){return$this->query("USE ".idf_escape($k));}}}class
Min_Driver
extends
Min_SQL{function
insertUpdate($R,$L,$Le){foreach($L
as$P){$Ig=array();$Z=array();foreach($P
as$y=>$X){$Ig[]="$y = $X";if(isset($Le[idf_unescape($y)]))$Z[]="$y = $X";}if(!queries("MERGE ".table($R)." USING (VALUES(".implode(", ",$P).")) AS source (c".implode(", c",range(1,count($P))).") ON ".implode(" AND ",$Z)." WHEN MATCHED THEN UPDATE SET ".implode(", ",$Ig)." WHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($P)).") VALUES (".implode(", ",$P).");"))return
false;}return
true;}function
begin(){return
queries("BEGIN TRANSACTION");}}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
connect(){global$b;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2]))return$h;return$h->error;}function
get_databases(){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($G,$Z,$z,$ie=0,$N=" "){return($z!==null?" TOP (".($z+$ie).")":"")." $G$Z";}function
limit1($R,$G,$Z,$N="\n"){return
limit($G,$Z,1,0,$N);}function
db_collation($m,$eb){global$h;return$h->result("SELECT collation_name FROM sys.databases WHERE name = ".q($m));}function
engines(){return
array();}function
logged_user(){global$h;return$h->result("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables($l){global$h;$J=array();foreach($l
as$m){$h->select_db($m);$J[$m]=$h->result("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$J;}function
table_status($B=""){$J=array();foreach(get_rows("SELECT name AS Name, type_desc AS Engine FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K){if($B!="")return$K;$J[$K["Name"]]=$K;}return$J;}function
is_view($S){return$S["Engine"]=="VIEW";}function
fk_support($S){return
true;}function
fields($R){$J=array();foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name, t.name type, CAST(d.definition as text) [default]
FROM sys.all_columns c
JOIN sys.all_objects o ON c.object_id = o.object_id
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.parent_column_id
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.type IN ('S', 'U', 'V') AND o.name = ".q($R))as$K){$U=$K["type"];$Bd=(preg_match("~char|binary~",$U)?$K["max_length"]:($U=="decimal"?"$K[precision],$K[scale]":""));$J[$K["name"]]=array("field"=>$K["name"],"full_type"=>$U.($Bd?"($Bd)":""),"type"=>$U,"length"=>$Bd,"default"=>$K["default"],"null"=>$K["is_nullable"],"auto_increment"=>$K["is_identity"],"collation"=>$K["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),"primary"=>$K["is_identity"],);}return$J;}function
indexes($R,$i=null){$J=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$i)as$K){$B=$K["name"];$J[$B]["type"]=($K["is_primary_key"]?"PRIMARY":($K["is_unique"]?"UNIQUE":"INDEX"));$J[$B]["lengths"]=array();$J[$B]["columns"][$K["key_ordinal"]]=$K["column_name"];$J[$B]["descs"][$K["key_ordinal"]]=($K["is_descending_key"]?'1':null);}return$J;}function
view($B){global$h;return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',$h->result("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($B))));}function
collations(){$J=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$d)$J[preg_replace('~_.*~','',$d)][]=$d;return$J;}function
information_schema($m){return
false;}function
error(){global$h;return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',$h->error)));}function
create_database($m,$d){return
queries("CREATE DATABASE ".idf_escape($m).(preg_match('~^[a-z0-9_]+$~i',$d)?" COLLATE $d":""));}function
drop_databases($l){return
queries("DROP DATABASE ".implode(", ",array_map('idf_escape',$l)));}function
rename_database($B,$d){if(preg_match('~^[a-z0-9_]+$~i',$d))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $d");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($B));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$B,$q,$vc,$ib,$Tb,$d,$Fa,$Be){$c=array();foreach($q
as$p){$e=idf_escape($p[0]);$X=$p[1];if(!$X)$c["DROP"][]=" COLUMN $e";else{$X[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$X[1]);if($p[0]=="")$c["ADD"][]="\n  ".implode("",$X).($R==""?substr($vc[$X[0]],16+strlen($X[0])):"");else{unset($X[6]);if($e!=$X[0])queries("EXEC sp_rename ".q(table($R).".$e").", ".q(idf_unescape($X[0])).", 'COLUMN'");$c["ALTER COLUMN ".implode("",$X)][]="";}}}if($R=="")return
queries("CREATE TABLE ".table($B)." (".implode(",",(array)$c["ADD"])."\n)");if($R!=$B)queries("EXEC sp_rename ".q(table($R)).", ".q($B));if($vc)$c[""]=$vc;foreach($c
as$y=>$X){if(!queries("ALTER TABLE ".idf_escape($B)." $y".implode(",",$X)))return
false;}return
true;}function
alter_indexes($R,$c){$v=array();$Ib=array();foreach($c
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY")$Ib[]=idf_escape($X[1]);else$v[]=idf_escape($X[1])." ON ".table($R);}elseif(!queries(($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")"))return
false;}return(!$v||queries("DROP INDEX ".implode(", ",$v)))&&(!$Ib||queries("ALTER TABLE ".table($R)." DROP ".implode(", ",$Ib)));}function
last_id(){global$h;return$h->result("SELECT SCOPE_IDENTITY()");}function
explain($h,$G){$h->query("SET SHOWPLAN_ALL ON");$J=$h->query($G);$h->query("SET SHOWPLAN_ALL OFF");return$J;}function
found_rows($S,$Z){}function
foreign_keys($R){$J=array();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R))as$K){$yc=&$J[$K["FK_NAME"]];$yc["table"]=$K["PKTABLE_NAME"];$yc["source"][]=$K["FKCOLUMN_NAME"];$yc["target"][]=$K["PKCOLUMN_NAME"];}return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Tg){return
queries("DROP VIEW ".implode(", ",array_map('table',$Tg)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('table',$T)));}function
move_tables($T,$Tg,$bg){return
apply_queries("ALTER SCHEMA ".idf_escape($bg)." TRANSFER",array_merge($T,$Tg));}function
trigger($B){if($B=="")return
array();$L=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($B));$J=reset($L);if($J)$J["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$J["text"]);return$J;}function
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
get_schema(){global$h;if($_GET["ns"]!="")return$_GET["ns"];return$h->result("SELECT SCHEMA_NAME()");}function
set_schema($of){return
true;}function
use_sql($k){return"USE ".idf_escape($k);}function
show_variables(){return
array();}function
show_status(){return
array();}function
convert_field($p){}function
unconvert_field($p,$J){return$J;}function
support($jc){return
preg_match('~^(columns|database|drop_col|indexes|descidx|scheme|sql|table|trigger|view|view_trigger)$~',$jc);}$x="mssql";$Ag=array();$Rf=array();foreach(array(lang(27)=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20),lang(28)=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10),lang(25)=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823),lang(29)=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),)as$y=>$X){$Ag+=$X;$Rf[$y]=array_keys($X);}$Hg=array();$pe=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");$Ec=array("len","lower","round","upper");$Ic=array("avg","count","count distinct","max","min","sum");$Mb=array(array("date|time"=>"getdate",),array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",));}$Hb['firebird']='Firebird (alpha)';if(isset($_GET["firebird"])){$Je=array("interbase");define("DRIVER","firebird");if(extension_loaded("interbase")){class
Min_DB{var$extension="Firebird",$server_info,$affected_rows,$errno,$error,$_link,$_result;function
connect($O,$V,$F){$this->_link=ibase_connect($O,$V,$F);if($this->_link){$Lg=explode(':',$O);$this->service_link=ibase_service_attach($Lg[0],$V,$F);$this->server_info=ibase_server_info($this->service_link,IBASE_SVC_SERVER_VERSION);}else{$this->errno=ibase_errcode();$this->error=ibase_errmsg();}return(bool)$this->_link;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($k){return($k=="domain");}function
query($G,$Bg=false){$I=ibase_query($G,$this->_link);if(!$I){$this->errno=ibase_errcode();$this->error=ibase_errmsg();return
false;}$this->error="";if($I===true){$this->affected_rows=ibase_affected_rows($this->_link);return
true;}return
new
Min_Result($I);}function
multi_query($G){return$this->_result=$this->query($G);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($G,$p=0){$I=$this->query($G);if(!$I||!$I->num_rows)return
false;$K=$I->fetch_row();return$K[$p];}}class
Min_Result{var$num_rows,$_result,$_offset=0;function
__construct($I){$this->_result=$I;}function
fetch_assoc(){return
ibase_fetch_assoc($this->_result);}function
fetch_row(){return
ibase_fetch_row($this->_result);}function
fetch_field(){$p=ibase_field_info($this->_result,$this->_offset++);return(object)array('name'=>$p['name'],'orgname'=>$p['name'],'type'=>$p['type'],'charsetnr'=>$p['length'],);}function
__destruct(){ibase_free_result($this->_result);}}}class
Min_Driver
extends
Min_SQL{}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
connect(){global$b;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2]))return$h;return$h->error;}function
get_databases($tc){return
array("domain");}function
limit($G,$Z,$z,$ie=0,$N=" "){$J='';$J.=($z!==null?$N."FIRST $z".($ie?" SKIP $ie":""):"");$J.=" $G$Z";return$J;}function
limit1($R,$G,$Z,$N="\n"){return
limit($G,$Z,1,0,$N);}function
db_collation($m,$eb){}function
engines(){return
array();}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
tables_list(){global$h;$G='SELECT RDB$RELATION_NAME FROM rdb$relations WHERE rdb$system_flag = 0';$I=ibase_query($h->_link,$G);$J=array();while($K=ibase_fetch_assoc($I))$J[$K['RDB$RELATION_NAME']]='table';ksort($J);return$J;}function
count_tables($l){return
array();}function
table_status($B="",$ic=false){global$h;$J=array();$vb=tables_list();foreach($vb
as$v=>$X){$v=trim($v);$J[$v]=array('Name'=>$v,'Engine'=>'standard',);if($B==$v)return$J[$v];}return$J;}function
is_view($S){return
false;}function
fk_support($S){return
preg_match('~InnoDB|IBMDB2I~i',$S["Engine"]);}function
fields($R){global$h;$J=array();$G='SELECT r.RDB$FIELD_NAME AS field_name,
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
ORDER BY r.RDB$FIELD_POSITION';$I=ibase_query($h->_link,$G);while($K=ibase_fetch_assoc($I))$J[trim($K['FIELD_NAME'])]=array("field"=>trim($K["FIELD_NAME"]),"full_type"=>trim($K["FIELD_TYPE"]),"type"=>trim($K["FIELD_SUB_TYPE"]),"default"=>trim($K['FIELD_DEFAULT_VALUE']),"null"=>(trim($K["FIELD_NOT_NULL_CONSTRAINT"])=="YES"),"auto_increment"=>'0',"collation"=>trim($K["FIELD_COLLATION"]),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),"comment"=>trim($K["FIELD_DESCRIPTION"]),);return$J;}function
indexes($R,$i=null){$J=array();return$J;}function
foreign_keys($R){return
array();}function
collations(){return
array();}function
information_schema($m){return
false;}function
error(){global$h;return
h($h->error);}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($of){return
true;}function
support($jc){return
preg_match("~^(columns|sql|status|table)$~",$jc);}$x="firebird";$pe=array("=");$Ec=array();$Ic=array();$Mb=array();}$Hb["simpledb"]="SimpleDB";if(isset($_GET["simpledb"])){$Je=array("SimpleXML + allow_url_fopen");define("DRIVER","simpledb");if(class_exists('SimpleXMLElement')&&ini_bool('allow_url_fopen')){class
Min_DB{var$extension="SimpleXML",$server_info='2009-04-15',$error,$timeout,$next,$affected_rows,$_result;function
select_db($k){return($k=="domain");}function
query($G,$Bg=false){$E=array('SelectExpression'=>$G,'ConsistentRead'=>'true');if($this->next)$E['NextToken']=$this->next;$I=sdb_request_all('Select','Item',$E,$this->timeout);$this->timeout=0;if($I===false)return$I;if(preg_match('~^\s*SELECT\s+COUNT\(~i',$G)){$Vf=0;foreach($I
as$od)$Vf+=$od->Attribute->Value;$I=array((object)array('Attribute'=>array((object)array('Name'=>'Count','Value'=>$Vf,))));}return
new
Min_Result($I);}function
multi_query($G){return$this->_result=$this->query($G);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}}class
Min_Result{var$num_rows,$_rows=array(),$_offset=0;function
__construct($I){foreach($I
as$od){$K=array();if($od->Name!='')$K['itemName()']=(string)$od->Name;foreach($od->Attribute
as$Ca){$B=$this->_processValue($Ca->Name);$Y=$this->_processValue($Ca->Value);if(isset($K[$B])){$K[$B]=(array)$K[$B];$K[$B][]=$Y;}else$K[$B]=$Y;}$this->_rows[]=$K;foreach($K
as$y=>$X){if(!isset($this->_rows[0][$y]))$this->_rows[0][$y]=null;}}$this->num_rows=count($this->_rows);}function
_processValue($Ob){return(is_object($Ob)&&$Ob['encoding']=='base64'?base64_decode($Ob):(string)$Ob);}function
fetch_assoc(){$K=current($this->_rows);if(!$K)return$K;$J=array();foreach($this->_rows[0]as$y=>$X)$J[$y]=$K[$y];next($this->_rows);return$J;}function
fetch_row(){$J=$this->fetch_assoc();if(!$J)return$J;return
array_values($J);}function
fetch_field(){$td=array_keys($this->_rows[0]);return(object)array('name'=>$td[$this->_offset++]);}}}class
Min_Driver
extends
Min_SQL{public$Le="itemName()";function
_chunkRequest($Wc,$ra,$E,$bc=array()){global$h;foreach(array_chunk($Wc,25)as$Ya){$_e=$E;foreach($Ya
as$s=>$t){$_e["Item.$s.ItemName"]=$t;foreach($bc
as$y=>$X)$_e["Item.$s.$y"]=$X;}if(!sdb_request($ra,$_e))return
false;}$h->affected_rows=count($Wc);return
true;}function
_extractIds($R,$H,$z){$J=array();if(preg_match_all("~itemName\(\) = (('[^']*+')+)~",$H,$Md))$J=array_map('idf_unescape',$Md[1]);else{foreach(sdb_request_all('Select','Item',array('SelectExpression'=>'SELECT itemName() FROM '.table($R).$H.($z?" LIMIT 1":"")))as$od)$J[]=$od->Name;}return$J;}function
select($R,$M,$Z,$Fc,$se=array(),$z=1,$D=0,$Ne=false){global$h;$h->next=$_GET["next"];$J=parent::select($R,$M,$Z,$Fc,$se,$z,$D,$Ne);$h->next=0;return$J;}function
delete($R,$H,$z=0){return$this->_chunkRequest($this->_extractIds($R,$H,$z),'BatchDeleteAttributes',array('DomainName'=>$R));}function
update($R,$P,$H,$z=0,$N="\n"){$_b=array();$jd=array();$s=0;$Wc=$this->_extractIds($R,$H,$z);$t=idf_unescape($P["`itemName()`"]);unset($P["`itemName()`"]);foreach($P
as$y=>$X){$y=idf_unescape($y);if($X=="NULL"||($t!=""&&array($t)!=$Wc))$_b["Attribute.".count($_b).".Name"]=$y;if($X!="NULL"){foreach((array)$X
as$pd=>$W){$jd["Attribute.$s.Name"]=$y;$jd["Attribute.$s.Value"]=(is_array($X)?$W:idf_unescape($W));if(!$pd)$jd["Attribute.$s.Replace"]="true";$s++;}}}$E=array('DomainName'=>$R);return(!$jd||$this->_chunkRequest(($t!=""?array($t):$Wc),'BatchPutAttributes',$E,$jd))&&(!$_b||$this->_chunkRequest($Wc,'BatchDeleteAttributes',$E,$_b));}function
insert($R,$P){$E=array("DomainName"=>$R);$s=0;foreach($P
as$B=>$Y){if($Y!="NULL"){$B=idf_unescape($B);if($B=="itemName()")$E["ItemName"]=idf_unescape($Y);else{foreach((array)$Y
as$X){$E["Attribute.$s.Name"]=$B;$E["Attribute.$s.Value"]=(is_array($Y)?$X:idf_unescape($Y));$s++;}}}}return
sdb_request('PutAttributes',$E);}function
insertUpdate($R,$L,$Le){foreach($L
as$P){if(!$this->update($R,$P,"WHERE `itemName()` = ".q($P["`itemName()`"])))return
false;}return
true;}function
begin(){return
false;}function
commit(){return
false;}function
rollback(){return
false;}function
slowQuery($G,$hg){$this->_conn->timeout=$hg;return$G;}}function
connect(){global$b;list(,,$F)=$b->credentials();if($F!="")return
lang(22);return
new
Min_DB;}function
support($jc){return
preg_match('~sql~',$jc);}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
get_databases(){return
array("domain");}function
collations(){return
array();}function
db_collation($m,$eb){}function
tables_list(){global$h;$J=array();foreach(sdb_request_all('ListDomains','DomainName')as$R)$J[(string)$R]='table';if($h->error&&defined("PAGE_HEADER"))echo"<p class='error'>".error()."\n";return$J;}function
table_status($B="",$ic=false){$J=array();foreach(($B!=""?array($B=>true):tables_list())as$R=>$U){$K=array("Name"=>$R,"Auto_increment"=>"");if(!$ic){$Ud=sdb_request('DomainMetadata',array('DomainName'=>$R));if($Ud){foreach(array("Rows"=>"ItemCount","Data_length"=>"ItemNamesSizeBytes","Index_length"=>"AttributeValuesSizeBytes","Data_free"=>"AttributeNamesSizeBytes",)as$y=>$X)$K[$y]=(string)$Ud->$X;}}if($B!="")return$K;$J[$R]=$K;}return$J;}function
explain($h,$G){}function
error(){global$h;return
h($h->error);}function
information_schema(){}function
is_view($S){}function
indexes($R,$i=null){return
array(array("type"=>"PRIMARY","columns"=>array("itemName()")),);}function
fields($R){return
fields_from_edit();}function
foreign_keys($R){return
array();}function
table($u){return
idf_escape($u);}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
limit($G,$Z,$z,$ie=0,$N=" "){return" $G$Z".($z!==null?$N."LIMIT $z":"");}function
unconvert_field($p,$J){return$J;}function
fk_support($S){}function
engines(){return
array();}function
alter_table($R,$B,$q,$vc,$ib,$Tb,$d,$Fa,$Be){return($R==""&&sdb_request('CreateDomain',array('DomainName'=>$B)));}function
drop_tables($T){foreach($T
as$R){if(!sdb_request('DeleteDomain',array('DomainName'=>$R)))return
false;}return
true;}function
count_tables($l){foreach($l
as$m)return
array($m=>count(tables_list()));}function
found_rows($S,$Z){return($Z?null:$S["Rows"]);}function
last_id(){}function
hmac($wa,$vb,$y,$Xe=false){$Oa=64;if(strlen($y)>$Oa)$y=pack("H*",$wa($y));$y=str_pad($y,$Oa,"\0");$qd=$y^str_repeat("\x36",$Oa);$rd=$y^str_repeat("\x5C",$Oa);$J=$wa($rd.pack("H*",$wa($qd.$vb)));if($Xe)$J=pack("H*",$J);return$J;}function
sdb_request($ra,$E=array()){global$b,$h;list($Sc,$E['AWSAccessKeyId'],$rf)=$b->credentials();$E['Action']=$ra;$E['Timestamp']=gmdate('Y-m-d\TH:i:s+00:00');$E['Version']='2009-04-15';$E['SignatureVersion']=2;$E['SignatureMethod']='HmacSHA1';ksort($E);$G='';foreach($E
as$y=>$X)$G.='&'.rawurlencode($y).'='.rawurlencode($X);$G=str_replace('%7E','~',substr($G,1));$G.="&Signature=".urlencode(base64_encode(hmac('sha1',"POST\n".preg_replace('~^https?://~','',$Sc)."\n/\n$G",$rf,true)));@ini_set('track_errors',1);$mc=@file_get_contents((preg_match('~^https?://~',$Sc)?$Sc:"http://$Sc"),false,stream_context_create(array('http'=>array('method'=>'POST','content'=>$G,'ignore_errors'=>1,))));if(!$mc){$h->error=$php_errormsg;return
false;}libxml_use_internal_errors(true);$eh=simplexml_load_string($mc);if(!$eh){$o=libxml_get_last_error();$h->error=$o->message;return
false;}if($eh->Errors){$o=$eh->Errors->Error;$h->error="$o->Message ($o->Code)";return
false;}$h->error='';$ag=$ra."Result";return($eh->$ag?$eh->$ag:true);}function
sdb_request_all($ra,$ag,$E=array(),$hg=0){$J=array();$Nf=($hg?microtime(true):0);$z=(preg_match('~LIMIT\s+(\d+)\s*$~i',$E['SelectExpression'],$A)?$A[1]:0);do{$eh=sdb_request($ra,$E);if(!$eh)break;foreach($eh->$ag
as$Ob)$J[]=$Ob;if($z&&count($J)>=$z){$_GET["next"]=$eh->NextToken;break;}if($hg&&microtime(true)-$Nf>$hg)return
false;$E['NextToken']=$eh->NextToken;if($z)$E['SelectExpression']=preg_replace('~\d+\s*$~',$z-count($J),$E['SelectExpression']);}while($eh->NextToken);return$J;}$x="simpledb";$pe=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","IS NOT NULL");$Ec=array();$Ic=array("count");$Mb=array(array("json"));}$Hb["mongo"]="MongoDB";if(isset($_GET["mongo"])){$Je=array("mongo","mongodb");define("DRIVER","mongo");if(class_exists('MongoDB')){class
Min_DB{var$extension="Mongo",$server_info=MongoClient::VERSION,$error,$last_id,$_link,$_db;function
connect($Jg,$C){return@new
MongoClient($Jg,$C);}function
query($G){return
false;}function
select_db($k){try{$this->_db=$this->_link->selectDB($k);return
true;}catch(Exception$Yb){$this->error=$Yb->getMessage();return
false;}}function
quote($Q){return$Q;}}class
Min_Result{var$num_rows,$_rows=array(),$_offset=0,$_charset=array();function
__construct($I){foreach($I
as$od){$K=array();foreach($od
as$y=>$X){if(is_a($X,'MongoBinData'))$this->_charset[$y]=63;$K[$y]=(is_a($X,'MongoId')?'ObjectId("'.strval($X).'")':(is_a($X,'MongoDate')?gmdate("Y-m-d H:i:s",$X->sec)." GMT":(is_a($X,'MongoBinData')?$X->bin:(is_a($X,'MongoRegex')?strval($X):(is_object($X)?get_class($X):$X)))));}$this->_rows[]=$K;foreach($K
as$y=>$X){if(!isset($this->_rows[0][$y]))$this->_rows[0][$y]=null;}}$this->num_rows=count($this->_rows);}function
fetch_assoc(){$K=current($this->_rows);if(!$K)return$K;$J=array();foreach($this->_rows[0]as$y=>$X)$J[$y]=$K[$y];next($this->_rows);return$J;}function
fetch_row(){$J=$this->fetch_assoc();if(!$J)return$J;return
array_values($J);}function
fetch_field(){$td=array_keys($this->_rows[0]);$B=$td[$this->_offset++];return(object)array('name'=>$B,'charsetnr'=>$this->_charset[$B],);}}class
Min_Driver
extends
Min_SQL{public$Le="_id";function
select($R,$M,$Z,$Fc,$se=array(),$z=1,$D=0,$Ne=false){$M=($M==array("*")?array():array_fill_keys($M,true));$Gf=array();foreach($se
as$X){$X=preg_replace('~ DESC$~','',$X,1,$pb);$Gf[$X]=($pb?-1:1);}return
new
Min_Result($this->_conn->_db->selectCollection($R)->find(array(),$M)->sort($Gf)->limit($z!=""?+$z:0)->skip($D*$z));}function
insert($R,$P){try{$J=$this->_conn->_db->selectCollection($R)->insert($P);$this->_conn->errno=$J['code'];$this->_conn->error=$J['err'];$this->_conn->last_id=$P['_id'];return!$J['err'];}catch(Exception$Yb){$this->_conn->error=$Yb->getMessage();return
false;}}}function
get_databases($tc){global$h;$J=array();$xb=$h->_link->listDBs();foreach($xb['databases']as$m)$J[]=$m['name'];return$J;}function
count_tables($l){global$h;$J=array();foreach($l
as$m)$J[$m]=count($h->_link->selectDB($m)->getCollectionNames(true));return$J;}function
tables_list(){global$h;return
array_fill_keys($h->_db->getCollectionNames(true),'table');}function
drop_databases($l){global$h;foreach($l
as$m){$gf=$h->_link->selectDB($m)->drop();if(!$gf['ok'])return
false;}return
true;}function
indexes($R,$i=null){global$h;$J=array();foreach($h->_db->selectCollection($R)->getIndexInfo()as$v){$Cb=array();foreach($v["key"]as$e=>$U)$Cb[]=($U==-1?'1':null);$J[$v["name"]]=array("type"=>($v["name"]=="_id_"?"PRIMARY":($v["unique"]?"UNIQUE":"INDEX")),"columns"=>array_keys($v["key"]),"lengths"=>array(),"descs"=>$Cb,);}return$J;}function
fields($R){return
fields_from_edit();}function
found_rows($S,$Z){global$h;return$h->_db->selectCollection($_GET["select"])->count($Z);}$pe=array("=");}elseif(class_exists('MongoDB\Driver\Manager')){class
Min_DB{var$extension="MongoDB",$server_info=MONGODB_VERSION,$error,$last_id;var$_link;var$_db,$_db_name;function
connect($Jg,$C){$ab='MongoDB\Driver\Manager';return
new$ab($Jg,$C);}function
query($G){return
false;}function
select_db($k){$this->_db_name=$k;return
true;}function
quote($Q){return$Q;}}class
Min_Result{var$num_rows,$_rows=array(),$_offset=0,$_charset=array();function
__construct($I){foreach($I
as$od){$K=array();foreach($od
as$y=>$X){if(is_a($X,'MongoDB\BSON\Binary'))$this->_charset[$y]=63;$K[$y]=(is_a($X,'MongoDB\BSON\ObjectID')?'MongoDB\BSON\ObjectID("'.strval($X).'")':(is_a($X,'MongoDB\BSON\UTCDatetime')?$X->toDateTime()->format('Y-m-d H:i:s'):(is_a($X,'MongoDB\BSON\Binary')?$X->bin:(is_a($X,'MongoDB\BSON\Regex')?strval($X):(is_object($X)?json_encode($X,256):$X)))));}$this->_rows[]=$K;foreach($K
as$y=>$X){if(!isset($this->_rows[0][$y]))$this->_rows[0][$y]=null;}}$this->num_rows=$I->count;}function
fetch_assoc(){$K=current($this->_rows);if(!$K)return$K;$J=array();foreach($this->_rows[0]as$y=>$X)$J[$y]=$K[$y];next($this->_rows);return$J;}function
fetch_row(){$J=$this->fetch_assoc();if(!$J)return$J;return
array_values($J);}function
fetch_field(){$td=array_keys($this->_rows[0]);$B=$td[$this->_offset++];return(object)array('name'=>$B,'charsetnr'=>$this->_charset[$B],);}}class
Min_Driver
extends
Min_SQL{public$Le="_id";function
select($R,$M,$Z,$Fc,$se=array(),$z=1,$D=0,$Ne=false){global$h;$M=($M==array("*")?array():array_fill_keys($M,1));if(count($M)&&!isset($M['_id']))$M['_id']=0;$Z=where_to_query($Z);$Gf=array();foreach($se
as$X){$X=preg_replace('~ DESC$~','',$X,1,$pb);$Gf[$X]=($pb?-1:1);}if(isset($_GET['limit'])&&is_numeric($_GET['limit'])&&$_GET['limit']>0)$z=$_GET['limit'];$z=min(200,max(1,(int)$z));$Df=$D*$z;$ab='MongoDB\Driver\Query';$G=new$ab($Z,array('projection'=>$M,'limit'=>$z,'skip'=>$Df,'sort'=>$Gf));$jf=$h->_link->executeQuery("$h->_db_name.$R",$G);return
new
Min_Result($jf);}function
update($R,$P,$H,$z=0,$N="\n"){global$h;$m=$h->_db_name;$Z=sql_query_where_parser($H);$ab='MongoDB\Driver\BulkWrite';$Sa=new$ab(array());if(isset($P['_id']))unset($P['_id']);$cf=array();foreach($P
as$y=>$Y){if($Y=='NULL'){$cf[$y]=1;unset($P[$y]);}}$Ig=array('$set'=>$P);if(count($cf))$Ig['$unset']=$cf;$Sa->update($Z,$Ig,array('upsert'=>false));$jf=$h->_link->executeBulkWrite("$m.$R",$Sa);$h->affected_rows=$jf->getModifiedCount();return
true;}function
delete($R,$H,$z=0){global$h;$m=$h->_db_name;$Z=sql_query_where_parser($H);$ab='MongoDB\Driver\BulkWrite';$Sa=new$ab(array());$Sa->delete($Z,array('limit'=>$z));$jf=$h->_link->executeBulkWrite("$m.$R",$Sa);$h->affected_rows=$jf->getDeletedCount();return
true;}function
insert($R,$P){global$h;$m=$h->_db_name;$ab='MongoDB\Driver\BulkWrite';$Sa=new$ab(array());if(isset($P['_id'])&&empty($P['_id']))unset($P['_id']);$Sa->insert($P);$jf=$h->_link->executeBulkWrite("$m.$R",$Sa);$h->affected_rows=$jf->getInsertedCount();return
true;}}function
get_databases($tc){global$h;$J=array();$ab='MongoDB\Driver\Command';$hb=new$ab(array('listDatabases'=>1));$jf=$h->_link->executeCommand('admin',$hb);foreach($jf
as$xb){foreach($xb->databases
as$m)$J[]=$m->name;}return$J;}function
count_tables($l){$J=array();return$J;}function
tables_list(){global$h;$ab='MongoDB\Driver\Command';$hb=new$ab(array('listCollections'=>1));$jf=$h->_link->executeCommand($h->_db_name,$hb);$fb=array();foreach($jf
as$I)$fb[$I->name]='table';return$fb;}function
drop_databases($l){return
false;}function
indexes($R,$i=null){global$h;$J=array();$ab='MongoDB\Driver\Command';$hb=new$ab(array('listIndexes'=>$R));$jf=$h->_link->executeCommand($h->_db_name,$hb);foreach($jf
as$v){$Cb=array();$f=array();foreach(get_object_vars($v->key)as$e=>$U){$Cb[]=($U==-1?'1':null);$f[]=$e;}$J[$v->name]=array("type"=>($v->name=="_id_"?"PRIMARY":(isset($v->unique)?"UNIQUE":"INDEX")),"columns"=>$f,"lengths"=>array(),"descs"=>$Cb,);}return$J;}function
fields($R){$q=fields_from_edit();if(!count($q)){global$n;$I=$n->select($R,array("*"),null,null,array(),10);while($K=$I->fetch_assoc()){foreach($K
as$y=>$X){$K[$y]=null;$q[$y]=array("field"=>$y,"type"=>"string","null"=>($y!=$n->primary),"auto_increment"=>($y==$n->primary),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,),);}}}return$q;}function
found_rows($S,$Z){global$h;$Z=where_to_query($Z);$ab='MongoDB\Driver\Command';$hb=new$ab(array('count'=>$S['Name'],'query'=>$Z));$jf=$h->_link->executeCommand($h->_db_name,$hb);$og=$jf->toArray();return$og[0]->n;}function
sql_query_where_parser($H){$H=trim(preg_replace('/WHERE[\s]?[(]?\(?/','',$H));$H=preg_replace('/\)\)\)$/',')',$H);$bh=explode(' AND ',$H);$ch=explode(') OR (',$H);$Z=array();foreach($bh
as$Zg)$Z[]=trim($Zg);if(count($ch)==1)$ch=array();elseif(count($ch)>1)$Z=array();return
where_to_query($Z,$ch);}function
where_to_query($Xg=array(),$Yg=array()){global$b;$vb=array();foreach(array('and'=>$Xg,'or'=>$Yg)as$U=>$Z){if(is_array($Z)){foreach($Z
as$cc){list($db,$ne,$X)=explode(" ",$cc,3);if($db=="_id"){$X=str_replace('MongoDB\BSON\ObjectID("',"",$X);$X=str_replace('")',"",$X);$ab='MongoDB\BSON\ObjectID';$X=new$ab($X);}if(!in_array($ne,$b->operators))continue;if(preg_match('~^\(f\)(.+)~',$ne,$A)){$X=(float)$X;$ne=$A[1];}elseif(preg_match('~^\(date\)(.+)~',$ne,$A)){$wb=new
DateTime($X);$ab='MongoDB\BSON\UTCDatetime';$X=new$ab($wb->getTimestamp()*1000);$ne=$A[1];}switch($ne){case'=':$ne='$eq';break;case'!=':$ne='$ne';break;case'>':$ne='$gt';break;case'<':$ne='$lt';break;case'>=':$ne='$gte';break;case'<=':$ne='$lte';break;case'regex':$ne='$regex';break;default:continue
2;}if($U=='and')$vb['$and'][]=array($db=>array($ne=>$X));elseif($U=='or')$vb['$or'][]=array($db=>array($ne=>$X));}}}return$vb;}$pe=array("=","!=",">","<",">=","<=","regex","(f)=","(f)!=","(f)>","(f)<","(f)>=","(f)<=","(date)=","(date)!=","(date)>","(date)<","(date)>=","(date)<=",);}function
table($u){return$u;}function
idf_escape($u){return$u;}function
table_status($B="",$ic=false){$J=array();foreach(tables_list()as$R=>$U){$J[$R]=array("Name"=>$R);if($B==$R)return$J[$R];}return$J;}function
create_database($m,$d){return
true;}function
last_id(){global$h;return$h->last_id;}function
error(){global$h;return
h($h->error);}function
collations(){return
array();}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
connect(){global$b;$h=new
Min_DB;list($O,$V,$F)=$b->credentials();$C=array();if($V.$F!=""){$C["username"]=$V;$C["password"]=$F;}$m=$b->database();if($m!="")$C["db"]=$m;try{$h->_link=$h->connect("mongodb://$O",$C);if($F!=""){$C["password"]="";try{$h->connect("mongodb://$O",$C);return
lang(22);}catch(Exception$Yb){}}return$h;}catch(Exception$Yb){return$Yb->getMessage();}}function
alter_indexes($R,$c){global$h;foreach($c
as$X){list($U,$B,$P)=$X;if($P=="DROP")$J=$h->_db->command(array("deleteIndexes"=>$R,"index"=>$B));else{$f=array();foreach($P
as$e){$e=preg_replace('~ DESC$~','',$e,1,$pb);$f[$e]=($pb?-1:1);}$J=$h->_db->selectCollection($R)->ensureIndex($f,array("unique"=>($U=="UNIQUE"),"name"=>$B,));}if($J['errmsg']){$h->error=$J['errmsg'];return
false;}}return
true;}function
support($jc){return
preg_match("~database|indexes|descidx~",$jc);}function
db_collation($m,$eb){}function
information_schema(){}function
is_view($S){}function
convert_field($p){}function
unconvert_field($p,$J){return$J;}function
foreign_keys($R){return
array();}function
fk_support($S){}function
engines(){return
array();}function
alter_table($R,$B,$q,$vc,$ib,$Tb,$d,$Fa,$Be){global$h;if($R==""){$h->_db->createCollection($B);return
true;}}function
drop_tables($T){global$h;foreach($T
as$R){$gf=$h->_db->selectCollection($R)->drop();if(!$gf['ok'])return
false;}return
true;}function
truncate_tables($T){global$h;foreach($T
as$R){$gf=$h->_db->selectCollection($R)->remove();if(!$gf['ok'])return
false;}return
true;}$x="mongo";$Ec=array();$Ic=array();$Mb=array(array("json"));}$Hb["elastic"]="Elasticsearch (beta)";if(isset($_GET["elastic"])){$Je=array("json + allow_url_fopen");define("DRIVER","elastic");if(function_exists('json_decode')&&ini_bool('allow_url_fopen')){class
Min_DB{var$extension="JSON",$server_info,$errno,$error,$_url;function
rootQuery($De,$nb=array(),$Vd='GET'){@ini_set('track_errors',1);$mc=@file_get_contents("$this->_url/".ltrim($De,'/'),false,stream_context_create(array('http'=>array('method'=>$Vd,'content'=>$nb===null?$nb:json_encode($nb),'header'=>'Content-Type: application/json','ignore_errors'=>1,))));if(!$mc){$this->error=$php_errormsg;return$mc;}if(!preg_match('~^HTTP/[0-9.]+ 2~i',$http_response_header[0])){$this->error=$mc;return
false;}$J=json_decode($mc,true);if($J===null){$this->errno=json_last_error();if(function_exists('json_last_error_msg'))$this->error=json_last_error_msg();else{$mb=get_defined_constants(true);foreach($mb['json']as$B=>$Y){if($Y==$this->errno&&preg_match('~^JSON_ERROR_~',$B)){$this->error=$B;break;}}}}return$J;}function
query($De,$nb=array(),$Vd='GET'){return$this->rootQuery(($this->_db!=""?"$this->_db/":"/").ltrim($De,'/'),$nb,$Vd);}function
connect($O,$V,$F){preg_match('~^(https?://)?(.*)~',$O,$A);$this->_url=($A[1]?$A[1]:"http://")."$V:$F@$A[2]";$J=$this->query('');if($J)$this->server_info=$J['version']['number'];return(bool)$J;}function
select_db($k){$this->_db=$k;return
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
select($R,$M,$Z,$Fc,$se=array(),$z=1,$D=0,$Ne=false){global$b;$vb=array();$G="$R/_search";if($M!=array("*"))$vb["fields"]=$M;if($se){$Gf=array();foreach($se
as$db){$db=preg_replace('~ DESC$~','',$db,1,$pb);$Gf[]=($pb?array($db=>"desc"):$db);}$vb["sort"]=$Gf;}if($z){$vb["size"]=+$z;if($D)$vb["from"]=($D*$z);}foreach($Z
as$X){list($db,$ne,$X)=explode(" ",$X,3);if($db=="_id")$vb["query"]["ids"]["values"][]=$X;elseif($db.$X!=""){$cg=array("term"=>array(($db!=""?$db:"_all")=>$X));if($ne=="=")$vb["query"]["filtered"]["filter"]["and"][]=$cg;else$vb["query"]["filtered"]["query"]["bool"]["must"][]=$cg;}}if($vb["query"]&&!$vb["query"]["filtered"]["query"]&&!$vb["query"]["ids"])$vb["query"]["filtered"]["query"]=array("match_all"=>array());$Nf=microtime(true);$qf=$this->_conn->query($G,$vb);if($Ne)echo$b->selectQuery("$G: ".print_r($vb,true),$Nf,!$qf);if(!$qf)return
false;$J=array();foreach($qf['hits']['hits']as$Rc){$K=array();if($M==array("*"))$K["_id"]=$Rc["_id"];$q=$Rc['_source'];if($M!=array("*")){$q=array();foreach($M
as$y)$q[$y]=$Rc['fields'][$y];}foreach($q
as$y=>$X){if($vb["fields"])$X=$X[0];$K[$y]=(is_array($X)?json_encode($X):$X);}$J[]=$K;}return
new
Min_Result($J);}function
update($U,$Ye,$H,$z=0,$N="\n"){$Ce=preg_split('~ *= *~',$H);if(count($Ce)==2){$t=trim($Ce[1]);$G="$U/$t";return$this->_conn->query($G,$Ye,'POST');}return
false;}function
insert($U,$Ye){$t="";$G="$U/$t";$gf=$this->_conn->query($G,$Ye,'POST');$this->_conn->last_id=$gf['_id'];return$gf['created'];}function
delete($U,$H,$z=0){$Wc=array();if(is_array($_GET["where"])&&$_GET["where"]["_id"])$Wc[]=$_GET["where"]["_id"];if(is_array($_POST['check'])){foreach($_POST['check']as$Ua){$Ce=preg_split('~ *= *~',$Ua);if(count($Ce)==2)$Wc[]=trim($Ce[1]);}}$this->_conn->affected_rows=0;foreach($Wc
as$t){$G="{$U}/{$t}";$gf=$this->_conn->query($G,'{}','DELETE');if(is_array($gf)&&$gf['found']==true)$this->_conn->affected_rows++;}return$this->_conn->affected_rows;}}function
connect(){global$b;$h=new
Min_DB;list($O,$V,$F)=$b->credentials();if($F!=""&&$h->connect($O,$V,""))return
lang(22);if($h->connect($O,$V,$F))return$h;return$h->error;}function
support($jc){return
preg_match("~database|table|columns~",$jc);}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
get_databases(){global$h;$J=$h->rootQuery('_aliases');if($J){$J=array_keys($J);sort($J,SORT_STRING);}return$J;}function
collations(){return
array();}function
db_collation($m,$eb){}function
engines(){return
array();}function
count_tables($l){global$h;$J=array();$I=$h->query('_stats');if($I&&$I['indices']){$cd=$I['indices'];foreach($cd
as$bd=>$Of){$ad=$Of['total']['indexing'];$J[$bd]=$ad['index_total'];}}return$J;}function
tables_list(){global$h;$J=$h->query('_mapping');if($J)$J=array_fill_keys(array_keys($J[$h->_db]["mappings"]),'table');return$J;}function
table_status($B="",$ic=false){global$h;$qf=$h->query("_search",array("size"=>0,"aggregations"=>array("count_by_type"=>array("terms"=>array("field"=>"_type")))),"POST");$J=array();if($qf){$T=$qf["aggregations"]["count_by_type"]["buckets"];foreach($T
as$R){$J[$R["key"]]=array("Name"=>$R["key"],"Engine"=>"table","Rows"=>$R["doc_count"],);if($B!=""&&$B==$R["key"])return$J[$B];}}return$J;}function
error(){global$h;return
h($h->error);}function
information_schema(){}function
is_view($S){}function
indexes($R,$i=null){return
array(array("type"=>"PRIMARY","columns"=>array("_id")),);}function
fields($R){global$h;$I=$h->query("$R/_mapping");$J=array();if($I){$Id=$I[$R]['properties'];if(!$Id)$Id=$I[$h->_db]['mappings'][$R]['properties'];if($Id){foreach($Id
as$B=>$p){$J[$B]=array("field"=>$B,"full_type"=>$p["type"],"type"=>$p["type"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),);if($p["properties"]){unset($J[$B]["privileges"]["insert"]);unset($J[$B]["privileges"]["update"]);}}}}return$J;}function
foreign_keys($R){return
array();}function
table($u){return$u;}function
idf_escape($u){return$u;}function
convert_field($p){}function
unconvert_field($p,$J){return$J;}function
fk_support($S){}function
found_rows($S,$Z){return
null;}function
create_database($m){global$h;return$h->rootQuery(urlencode($m),null,'PUT');}function
drop_databases($l){global$h;return$h->rootQuery(urlencode(implode(',',$l)),array(),'DELETE');}function
alter_table($R,$B,$q,$vc,$ib,$Tb,$d,$Fa,$Be){global$h;$Qe=array();foreach($q
as$gc){$kc=trim($gc[1][0]);$lc=trim($gc[1][1]?$gc[1][1]:"text");$Qe[$kc]=array('type'=>$lc);}if(!empty($Qe))$Qe=array('properties'=>$Qe);return$h->query("_mapping/{$B}",$Qe,'PUT');}function
drop_tables($T){global$h;$J=true;foreach($T
as$R)$J=$J&&$h->query(urlencode($R),array(),'DELETE');return$J;}function
last_id(){global$h;return$h->last_id;}$x="elastic";$pe=array("=","query");$Ec=array();$Ic=array();$Mb=array(array("json"));$Ag=array();$Rf=array();foreach(array(lang(27)=>array("long"=>3,"integer"=>5,"short"=>8,"byte"=>10,"double"=>20,"float"=>66,"half_float"=>12,"scaled_float"=>21),lang(28)=>array("date"=>10),lang(25)=>array("string"=>65535,"text"=>65535),lang(29)=>array("binary"=>255),)as$y=>$X){$Ag+=$X;$Rf[$y]=array_keys($X);}}$Hb["clickhouse"]="ClickHouse (alpha)";if(isset($_GET["clickhouse"])){define("DRIVER","clickhouse");class
Min_DB{var$extension="JSON",$server_info,$errno,$_result,$error,$_url;var$_db='default';function
rootQuery($m,$G){@ini_set('track_errors',1);$mc=@file_get_contents("$this->_url/?database=$m",false,stream_context_create(array('http'=>array('method'=>'POST','content'=>$this->isQuerySelectLike($G)?"$G FORMAT JSONCompact":$G,'header'=>'Content-type: application/x-www-form-urlencoded','ignore_errors'=>1,))));if($mc===false){$this->error=$php_errormsg;return$mc;}if(!preg_match('~^HTTP/[0-9.]+ 2~i',$http_response_header[0])){$this->error=$mc;return
false;}$J=json_decode($mc,true);if($J===null){$this->errno=json_last_error();if(function_exists('json_last_error_msg'))$this->error=json_last_error_msg();else{$mb=get_defined_constants(true);foreach($mb['json']as$B=>$Y){if($Y==$this->errno&&preg_match('~^JSON_ERROR_~',$B)){$this->error=$B;break;}}}}return
new
Min_Result($J);}function
isQuerySelectLike($G){return(bool)preg_match('~^(select|show)~i',$G);}function
query($G){return$this->rootQuery($this->_db,$G);}function
connect($O,$V,$F){preg_match('~^(https?://)?(.*)~',$O,$A);$this->_url=($A[1]?$A[1]:"http://")."$V:$F@$A[2]";$J=$this->query('SELECT 1');return(bool)$J;}function
select_db($k){$this->_db=$k;return
true;}function
quote($Q){return"'".addcslashes($Q,"\\'")."'";}function
multi_query($G){return$this->_result=$this->query($G);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($G,$p=0){$I=$this->query($G);return$I['data'];}}class
Min_Result{var$num_rows,$_rows,$columns,$meta,$_offset=0;function
__construct($I){$this->num_rows=$I['rows'];$this->_rows=$I['data'];$this->meta=$I['meta'];$this->columns=array_column($this->meta,'name');reset($this->_rows);}function
fetch_assoc(){$K=current($this->_rows);next($this->_rows);return$K===false?false:array_combine($this->columns,$K);}function
fetch_row(){$K=current($this->_rows);next($this->_rows);return$K;}function
fetch_field(){$e=$this->_offset++;$J=new
stdClass;if($e<count($this->columns)){$J->name=$this->meta[$e]['name'];$J->orgname=$J->name;$J->type=$this->meta[$e]['type'];}return$J;}}class
Min_Driver
extends
Min_SQL{function
delete($R,$H,$z=0){return
queries("ALTER TABLE ".table($R)." DELETE $H");}function
update($R,$P,$H,$z=0,$N="\n"){$Pg=array();foreach($P
as$y=>$X)$Pg[]="$y = $X";$G=$N.implode(",$N",$Pg);return
queries("ALTER TABLE ".table($R)." UPDATE $G$H");}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
explain($h,$G){return'';}function
found_rows($S,$Z){$L=get_vals("SELECT COUNT(*) FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):""));return
empty($L)?false:$L[0];}function
alter_table($R,$B,$q,$vc,$ib,$Tb,$d,$Fa,$Be){foreach($q
as$p){if($p[1][2]===" NULL")$p[1][1]=" Nullable({$p[1][1]})";unset($p[1][2]);}}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Tg){return
drop_tables($Tg);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
connect(){global$b;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2]))return$h;return$h->error;}function
get_databases($tc){global$h;$I=get_rows('SHOW DATABASES');$J=array();foreach($I
as$K)$J[]=$K['name'];sort($J);return$J;}function
limit($G,$Z,$z,$ie=0,$N=" "){return" $G$Z".($z!==null?$N."LIMIT $z".($ie?", $ie":""):"");}function
limit1($R,$G,$Z,$N="\n"){return
limit($G,$Z,1,0,$N);}function
db_collation($m,$eb){}function
engines(){return
array('MergeTree');}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
tables_list(){$I=get_rows('SHOW TABLES');$J=array();foreach($I
as$K)$J[$K['name']]='table';ksort($J);return$J;}function
count_tables($l){return
array();}function
table_status($B="",$ic=false){global$h;$J=array();$T=get_rows("SELECT name, engine FROM system.tables WHERE database = ".q($h->_db));foreach($T
as$R){$J[$R['name']]=array('Name'=>$R['name'],'Engine'=>$R['engine'],);if($B===$R['name'])return$J[$R['name']];}return$J;}function
is_view($S){return
false;}function
fk_support($S){return
false;}function
convert_field($p){}function
unconvert_field($p,$J){if(in_array($p['type'],array("Int8","Int16","Int32","Int64","UInt8","UInt16","UInt32","UInt64","Float32","Float64")))return"to$p[type]($J)";return$J;}function
fields($R){$J=array();$I=get_rows("SELECT name, type, default_expression FROM system.columns WHERE ".idf_escape('table')." = ".q($R));foreach($I
as$K){$U=trim($K['type']);$ee=strpos($U,'Nullable(')===0;$J[trim($K['name'])]=array("field"=>trim($K['name']),"full_type"=>$U,"type"=>$U,"default"=>trim($K['default_expression']),"null"=>$ee,"auto_increment"=>'0',"privileges"=>array("insert"=>1,"select"=>1,"update"=>0),);}return$J;}function
indexes($R,$i=null){return
array();}function
foreign_keys($R){return
array();}function
collations(){return
array();}function
information_schema($m){return
false;}function
error(){global$h;return
h($h->error);}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($of){return
true;}function
auto_increment(){return'';}function
last_id(){return
0;}function
support($jc){return
preg_match("~^(columns|sql|status|table)$~",$jc);}$x="clickhouse";$Ag=array();$Rf=array();foreach(array(lang(27)=>array("Int8"=>3,"Int16"=>5,"Int32"=>10,"Int64"=>19,"UInt8"=>3,"UInt16"=>5,"UInt32"=>10,"UInt64"=>20,"Float32"=>7,"Float64"=>16,'Decimal'=>38,'Decimal32'=>9,'Decimal64'=>18,'Decimal128'=>38),lang(28)=>array("Date"=>13,"DateTime"=>20),lang(25)=>array("String"=>0),lang(29)=>array("FixedString"=>0),)as$y=>$X){$Ag+=$X;$Rf[$y]=array_keys($X);}$Hg=array();$pe=array("=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");$Ec=array();$Ic=array("avg","count","count distinct","max","min","sum");$Mb=array();}$Hb=array("server"=>"MySQL")+$Hb;if(!defined("DRIVER")){$Je=array("MySQLi","MySQL","PDO_MySQL");define("DRIVER","server");if(extension_loaded("mysqli")){class
Min_DB
extends
MySQLi{var$extension="MySQLi";function
__construct(){parent::init();}function
connect($O="",$V="",$F="",$k=null,$He=null,$Ff=null){global$b;mysqli_report(MYSQLI_REPORT_OFF);list($Sc,$He)=explode(":",$O,2);$Mf=$b->connectSsl();if($Mf)$this->ssl_set($Mf['key'],$Mf['cert'],$Mf['ca'],'','');$J=@$this->real_connect(($O!=""?$Sc:ini_get("mysqli.default_host")),($O.$V!=""?$V:ini_get("mysqli.default_user")),($O.$V.$F!=""?$F:ini_get("mysqli.default_pw")),$k,(is_numeric($He)?$He:ini_get("mysqli.default_port")),(!is_numeric($He)?$He:$Ff),($Mf?64:0));$this->options(MYSQLI_OPT_LOCAL_INFILE,false);return$J;}function
set_charset($Ta){if(parent::set_charset($Ta))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $Ta");}function
result($G,$p=0){$I=$this->query($G);if(!$I)return
false;$K=$I->fetch_array();return$K[$p];}function
quote($Q){return"'".$this->escape_string($Q)."'";}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Min_DB{var$extension="MySQL",$server_info,$affected_rows,$errno,$error,$_link,$_result;function
connect($O,$V,$F){if(ini_bool("mysql.allow_local_infile")){$this->error=lang(32,"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");return
false;}$this->_link=@mysql_connect(($O!=""?$O:ini_get("mysql.default_host")),("$O$V"!=""?$V:ini_get("mysql.default_user")),("$O$V$F"!=""?$F:ini_get("mysql.default_password")),true,131072);if($this->_link)$this->server_info=mysql_get_server_info($this->_link);else$this->error=mysql_error();return(bool)$this->_link;}function
set_charset($Ta){if(function_exists('mysql_set_charset')){if(mysql_set_charset($Ta,$this->_link))return
true;mysql_set_charset('utf8',$this->_link);}return$this->query("SET NAMES $Ta");}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->_link)."'";}function
select_db($k){return
mysql_select_db($k,$this->_link);}function
query($G,$Bg=false){$I=@($Bg?mysql_unbuffered_query($G,$this->_link):mysql_query($G,$this->_link));$this->error="";if(!$I){$this->errno=mysql_errno($this->_link);$this->error=mysql_error($this->_link);return
false;}if($I===true){$this->affected_rows=mysql_affected_rows($this->_link);$this->info=mysql_info($this->_link);return
true;}return
new
Min_Result($I);}function
multi_query($G){return$this->_result=$this->query($G);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($G,$p=0){$I=$this->query($G);if(!$I||!$I->num_rows)return
false;return
mysql_result($I->_result,0,$p);}}class
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
connect($O,$V,$F){global$b;$C=array(PDO::MYSQL_ATTR_LOCAL_INFILE=>false);$Mf=$b->connectSsl();if($Mf)$C+=array(PDO::MYSQL_ATTR_SSL_KEY=>$Mf['key'],PDO::MYSQL_ATTR_SSL_CERT=>$Mf['cert'],PDO::MYSQL_ATTR_SSL_CA=>$Mf['ca'],);$this->dsn("mysql:charset=utf8;host=".str_replace(":",";unix_socket=",preg_replace('~:(\d)~',';port=\1',$O)),$V,$F,$C);return
true;}function
set_charset($Ta){$this->query("SET NAMES $Ta");}function
select_db($k){return$this->query("USE ".idf_escape($k));}function
query($G,$Bg=false){$this->setAttribute(1000,!$Bg);return
parent::query($G,$Bg);}}}class
Min_Driver
extends
Min_SQL{function
insert($R,$P){return($P?parent::insert($R,$P):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,$L,$Le){$f=array_keys(reset($L));$Ke="INSERT INTO ".table($R)." (".implode(", ",$f).") VALUES\n";$Pg=array();foreach($f
as$y)$Pg[$y]="$y = VALUES($y)";$Uf="\nON DUPLICATE KEY UPDATE ".implode(", ",$Pg);$Pg=array();$Bd=0;foreach($L
as$P){$Y="(".implode(", ",$P).")";if($Pg&&(strlen($Ke)+$Bd+strlen($Y)+strlen($Uf)>1e6)){if(!queries($Ke.implode(",\n",$Pg).$Uf))return
false;$Pg=array();$Bd=0;}$Pg[]=$Y;$Bd+=strlen($Y)+2;}return
queries($Ke.implode(",\n",$Pg).$Uf);}function
slowQuery($G,$hg){if(min_version('5.7.8','10.1.2')){if(preg_match('~MariaDB~',$this->_conn->server_info))return"SET STATEMENT max_statement_time=$hg FOR $G";elseif(preg_match('~^(SELECT\b)(.+)~is',$G,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($hg*1000).") */ $A[2]";}}function
convertSearch($u,$X,$p){return(preg_match('~char|text|enum|set~',$p["type"])&&!preg_match("~^utf8~",$p["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($u USING ".charset($this->_conn).")":$u);}function
warnings(){$I=$this->_conn->query("SHOW WARNINGS");if($I&&$I->num_rows){ob_start();select($I);return
ob_get_clean();}}function
tableHelp($B){$Jd=preg_match('~MariaDB~',$this->_conn->server_info);if(information_schema(DB))return
strtolower(($Jd?"information-schema-$B-table/":str_replace("_","-",$B)."-table.html"));if(DB=="mysql")return($Jd?"mysql$B-table/":"system-database.html");}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
connect(){global$b,$Ag,$Rf;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2])){$h->set_charset(charset($h));$h->query("SET sql_quote_show_create = 1, autocommit = 1");if(min_version('5.7.8',10.2,$h)){$Rf[lang(25)][]="json";$Ag["json"]=4294967295;}return$h;}$J=$h->error;if(function_exists('iconv')&&!is_utf8($J)&&strlen($nf=iconv("windows-1250","utf-8",$J))>strlen($J))$J=$nf;return$J;}function
get_databases($tc){$J=get_session("dbs");if($J===null){$G=(min_version(5)?"SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME":"SHOW DATABASES");$J=($tc?slow_query($G):get_vals($G));restart_session();set_session("dbs",$J);stop_session();}return$J;}function
limit($G,$Z,$z,$ie=0,$N=" "){return" $G$Z".($z!==null?$N."LIMIT $z".($ie?" OFFSET $ie":""):"");}function
limit1($R,$G,$Z,$N="\n"){return
limit($G,$Z,1,0,$N);}function
db_collation($m,$eb){global$h;$J=null;$qb=$h->result("SHOW CREATE DATABASE ".idf_escape($m),1);if(preg_match('~ COLLATE ([^ ]+)~',$qb,$A))$J=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$qb,$A))$J=$eb[$A[1]][-1];return$J;}function
engines(){$J=array();foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$J[]=$K["Engine"];}return$J;}function
logged_user(){global$h;return$h->result("SELECT USER()");}function
tables_list(){return
get_key_vals(min_version(5)?"SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME":"SHOW TABLES");}function
count_tables($l){$J=array();foreach($l
as$m)$J[$m]=count(get_vals("SHOW TABLES IN ".idf_escape($m)));return$J;}function
table_status($B="",$ic=false){$J=array();foreach(get_rows($ic&&min_version(5)?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name"):"SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):""))as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($B!="")return$K;$J[$K["Name"]]=$K;}return$J;}function
is_view($S){return$S["Engine"]===null;}function
fk_support($S){return
preg_match('~InnoDB|IBMDB2I~i',$S["Engine"])||(preg_match('~NDB~i',$S["Engine"])&&min_version(5.6));}function
fields($R){$J=array();foreach(get_rows("SHOW FULL COLUMNS FROM ".table($R))as$K){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$K["Type"],$A);$J[$K["Field"]]=array("field"=>$K["Field"],"full_type"=>$K["Type"],"type"=>$A[1],"length"=>$A[2],"unsigned"=>ltrim($A[3].$A[4]),"default"=>($K["Default"]!=""||preg_match("~char|set~",$A[1])?$K["Default"]:null),"null"=>($K["Null"]=="YES"),"auto_increment"=>($K["Extra"]=="auto_increment"),"on_update"=>(preg_match('~^on update (.+)~i',$K["Extra"],$A)?$A[1]:""),"collation"=>$K["Collation"],"privileges"=>array_flip(preg_split('~, *~',$K["Privileges"])),"comment"=>$K["Comment"],"primary"=>($K["Key"]=="PRI"),);}return$J;}function
indexes($R,$i=null){$J=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$i)as$K){$B=$K["Key_name"];$J[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?($K["Index_type"]=="SPATIAL"?"SPATIAL":"INDEX"):"UNIQUE")));$J[$B]["columns"][]=$K["Column_name"];$J[$B]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$B]["descs"][]=null;}return$J;}function
foreign_keys($R){global$h,$ke;static$Ee='(?:`(?:[^`]|``)+`)|(?:"(?:[^"]|"")+")';$J=array();$rb=$h->result("SHOW CREATE TABLE ".table($R),1);if($rb){preg_match_all("~CONSTRAINT ($Ee) FOREIGN KEY ?\\(((?:$Ee,? ?)+)\\) REFERENCES ($Ee)(?:\\.($Ee))? \\(((?:$Ee,? ?)+)\\)(?: ON DELETE ($ke))?(?: ON UPDATE ($ke))?~",$rb,$Md,PREG_SET_ORDER);foreach($Md
as$A){preg_match_all("~$Ee~",$A[2],$Hf);preg_match_all("~$Ee~",$A[5],$bg);$J[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('idf_unescape',$Hf[0]),"target"=>array_map('idf_unescape',$bg[0]),"on_delete"=>($A[6]?$A[6]:"RESTRICT"),"on_update"=>($A[7]?$A[7]:"RESTRICT"),);}}return$J;}function
view($B){global$h;return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',$h->result("SHOW CREATE VIEW ".table($B),1)));}function
collations(){$J=array();foreach(get_rows("SHOW COLLATION")as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$y=>$X)asort($J[$y]);return$J;}function
information_schema($m){return(min_version(5)&&$m=="information_schema")||(min_version(5.5)&&$m=="performance_schema");}function
error(){global$h;return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",$h->error));}function
create_database($m,$d){return
queries("CREATE DATABASE ".idf_escape($m).($d?" COLLATE ".q($d):""));}function
drop_databases($l){$J=apply_queries("DROP DATABASE",$l,'idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($B,$d){$J=false;if(create_database($B,$d)){$df=array();foreach(tables_list()as$R=>$U)$df[]=table($R)." TO ".idf_escape($B).".".table($R);$J=(!$df||queries("RENAME TABLE ".implode(", ",$df)));if($J)queries("DROP DATABASE ".idf_escape(DB));restart_session();set_session("dbs",null);}return$J;}function
auto_increment(){$Ga=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Ga="";break;}if($v["type"]=="PRIMARY")$Ga=" UNIQUE";}}return" AUTO_INCREMENT$Ga";}function
alter_table($R,$B,$q,$vc,$ib,$Tb,$d,$Fa,$Be){$c=array();foreach($q
as$p)$c[]=($p[1]?($R!=""?($p[0]!=""?"CHANGE ".idf_escape($p[0]):"ADD"):" ")." ".implode($p[1]).($R!=""?$p[2]:""):"DROP ".idf_escape($p[0]));$c=array_merge($c,$vc);$Pf=($ib!==null?" COMMENT=".q($ib):"").($Tb?" ENGINE=".q($Tb):"").($d?" COLLATE ".q($d):"").($Fa!=""?" AUTO_INCREMENT=$Fa":"");if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$c)."\n)$Pf$Be");if($R!=$B)$c[]="RENAME TO ".table($B);if($Pf)$c[]=ltrim($Pf);return($c||$Be?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$c).$Be):true);}function
alter_indexes($R,$c){foreach($c
as$y=>$X)$c[$y]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$c));}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Tg){return
queries("DROP VIEW ".implode(", ",array_map('table',$Tg)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('table',$T)));}function
move_tables($T,$Tg,$bg){$df=array();foreach(array_merge($T,$Tg)as$R)$df[]=table($R)." TO ".idf_escape($bg).".".table($R);return
queries("RENAME TABLE ".implode(", ",$df));}function
copy_tables($T,$Tg,$bg){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$B=($bg==DB?table("copy_$R"):idf_escape($bg).".".table($R));if(!queries("CREATE TABLE $B LIKE ".table($R))||!queries("INSERT INTO $B SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K){$wg=$K["Trigger"];if(!queries("CREATE TRIGGER ".($bg==DB?idf_escape("copy_$wg"):idf_escape($bg).".".idf_escape($wg))." $K[Timing] $K[Event] ON $B FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($Tg
as$R){$B=($bg==DB?table("copy_$R"):idf_escape($bg).".".table($R));$Sg=view($R);if(!queries("CREATE VIEW $B AS $Sg[select]"))return
false;}return
true;}function
trigger($B){if($B=="")return
array();$L=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($B));return
reset($L);}function
triggers($R){$J=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K)$J[$K["Trigger"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($B,$U){global$h,$Ub,$hd,$Ag;$xa=array("bool","boolean","integer","double precision","real","dec","numeric","fixed","national char","national varchar");$If="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";$_g="((".implode("|",array_merge(array_keys($Ag),$xa)).")\\b(?:\\s*\\(((?:[^'\")]|$Ub)++)\\))?\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?";$Ee="$If*(".($U=="FUNCTION"?"":$hd).")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$_g";$qb=$h->result("SHOW CREATE $U ".idf_escape($B),2);preg_match("~\\(((?:$Ee\\s*,?)*)\\)\\s*".($U=="FUNCTION"?"RETURNS\\s+$_g\\s+":"")."(.*)~is",$qb,$A);$q=array();preg_match_all("~$Ee\\s*,?~is",$A[1],$Md,PREG_SET_ORDER);foreach($Md
as$ze){$B=str_replace("``","`",$ze[2]).$ze[3];$q[]=array("field"=>$B,"type"=>strtolower($ze[5]),"length"=>preg_replace_callback("~$Ub~s",'normalize_enum',$ze[6]),"unsigned"=>strtolower(preg_replace('~\s+~',' ',trim("$ze[8] $ze[7]"))),"null"=>1,"full_type"=>$ze[4],"inout"=>strtoupper($ze[1]),"collation"=>strtolower($ze[9]),);}if($U!="FUNCTION")return
array("fields"=>$q,"definition"=>$A[11]);return
array("fields"=>$q,"returns"=>array("type"=>$A[12],"length"=>$A[13],"unsigned"=>$A[15],"collation"=>$A[16]),"definition"=>$A[17],"language"=>"SQL",);}function
routines(){return
get_rows("SELECT ROUTINE_NAME AS SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ".q(DB));}function
routine_languages(){return
array();}function
routine_id($B,$K){return
idf_escape($B);}function
last_id(){global$h;return$h->result("SELECT LAST_INSERT_ID()");}function
explain($h,$G){return$h->query("EXPLAIN ".(min_version(5.1)?"PARTITIONS ":"").$G);}function
found_rows($S,$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($of){return
true;}function
create_sql($R,$Fa,$Sf){global$h;$J=$h->result("SHOW CREATE TABLE ".table($R),1);if(!$Fa)$J=preg_replace('~ AUTO_INCREMENT=\d+~','',$J);return$J;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($k){return"USE ".idf_escape($k);}function
trigger_sql($R){$J="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$K)$J.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$J;}function
show_variables(){return
get_key_vals("SHOW VARIABLES");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
show_status(){return
get_key_vals("SHOW STATUS");}function
convert_field($p){if(preg_match("~binary~",$p["type"]))return"HEX(".idf_escape($p["field"]).")";if($p["type"]=="bit")return"BIN(".idf_escape($p["field"])." + 0)";if(preg_match("~geometry|point|linestring|polygon~",$p["type"]))return(min_version(8)?"ST_":"")."AsWKT(".idf_escape($p["field"]).")";}function
unconvert_field($p,$J){if(preg_match("~binary~",$p["type"]))$J="UNHEX($J)";if($p["type"]=="bit")$J="CONV($J, 2, 10) + 0";if(preg_match("~geometry|point|linestring|polygon~",$p["type"]))$J=(min_version(8)?"ST_":"")."GeomFromText($J)";return$J;}function
support($jc){return!preg_match("~scheme|sequence|type|view_trigger|materializedview".(min_version(8)?"":"|descidx".(min_version(5.1)?"":"|event|partitioning".(min_version(5)?"":"|routine|trigger|view")))."~",$jc);}function
kill_process($X){return
queries("KILL ".number($X));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){global$h;return$h->result("SELECT @@max_connections");}$x="sql";$Ag=array();$Rf=array();foreach(array(lang(27)=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),lang(28)=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),lang(25)=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),lang(33)=>array("enum"=>65535,"set"=>64),lang(29)=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),lang(31)=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),)as$y=>$X){$Ag+=$X;$Rf[$y]=array_keys($X);}$Hg=array("unsigned","zerofill","unsigned zerofill");$pe=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");$Ec=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");$Ic=array("avg","count","count distinct","group_concat","max","min","sum");$Mb=array(array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",),array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",));}define("SERVER",$_GET[DRIVER]);define("DB",$_GET["db"]);define("ME",preg_replace('~^[^?]*/([^?]*).*~','\1',$_SERVER["REQUEST_URI"]).'?'.(sid()?SID.'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));$ca="4.7.1";class
Adminer{var$operators=array("<=",">=");var$_values=array();function
name(){return"<a href='https://www.adminer.org/editor/'".target_blank()." id='h1'>".lang(34)."</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($qb=false){return
password_file($qb);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($O){}function
database(){global$h;if($h){$l=$this->databases(false);return(!$l?$h->result("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1)"):$l[(information_schema($l[0])?1:0)]);}}function
schemas(){return
schemas();}function
databases($tc=true){return
get_databases($tc);}function
queryTimeout(){return
5;}function
headers(){}function
csp(){return
csp();}function
head(){return
true;}function
css(){$J=array();$r="adminer.css";if(file_exists($r))$J[]=$r;return$J;}function
loginForm(){echo"<table cellspacing='0' class='layout'>\n",$this->loginFormField('username','<tr><th>'.lang(35).'<td>','<input type="hidden" name="auth[driver]" value="server"><input name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("focus(qs('#username'));")),$this->loginFormField('password','<tr><th>'.lang(36).'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'."\n"),"</table>\n","<p><input type='submit' value='".lang(37)."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],lang(38))."\n";}function
loginFormField($B,$Pc,$Y){return$Pc.$Y;}function
login($Gd,$F){return
true;}function
tableName($Xf){return
h($Xf["Comment"]!=""?$Xf["Comment"]:$Xf["Name"]);}function
fieldName($p,$se=0){return
h(preg_replace('~\s+\[.*\]$~','',($p["comment"]!=""?$p["comment"]:$p["field"])));}function
selectLinks($Xf,$P=""){$a=$Xf["Name"];if($P!==null)echo'<p class="tabs"><a href="'.h(ME.'edit='.urlencode($a).$P).'">'.lang(39)."</a>\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$Wf){$J=array();foreach(get_rows("SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = ".q($this->database())."
AND REFERENCED_TABLE_SCHEMA = ".q($this->database())."
AND REFERENCED_TABLE_NAME = ".q($R)."
ORDER BY ORDINAL_POSITION",null,"")as$K)$J[$K["TABLE_NAME"]]["keys"][$K["CONSTRAINT_NAME"]][$K["COLUMN_NAME"]]=$K["REFERENCED_COLUMN_NAME"];foreach($J
as$y=>$X){$B=$this->tableName(table_status($y,true));if($B!=""){$qf=preg_quote($Wf);$N="(:|\\s*-)?\\s+";$J[$y]["name"]=(preg_match("(^$qf$N(.+)|^(.+?)$N$qf\$)iu",$B,$A)?$A[2].$A[3]:$B);}else
unset($J[$y]);}return$J;}function
backwardKeysPrint($Ja,$K){foreach($Ja
as$R=>$Ia){foreach($Ia["keys"]as$gb){$_=ME.'select='.urlencode($R);$s=0;foreach($gb
as$e=>$X)$_.=where_link($s++,$e,$K[$X]);echo"<a href='".h($_)."'>".h($Ia["name"])."</a>";$_=ME.'edit='.urlencode($R);foreach($gb
as$e=>$X)$_.="&set".urlencode("[".bracket_escape($e)."]")."=".urlencode($K[$X]);echo"<a href='".h($_)."' title='".lang(39)."'>+</a> ";}}}function
selectQuery($G,$Nf,$hc=false){return"<!--\n".str_replace("--","--><!-- ",$G)."\n(".format_time($Nf).")\n-->\n";}function
rowDescription($R){foreach(fields($R)as$p){if(preg_match("~varchar|character varying~",$p["type"]))return
idf_escape($p["field"]);}return"";}function
rowDescriptions($L,$xc){$J=$L;foreach($L[0]as$y=>$X){if(list($R,$t,$B)=$this->_foreignColumn($xc,$y)){$Wc=array();foreach($L
as$K)$Wc[$K[$y]]=q($K[$y]);$Bb=$this->_values[$R];if(!$Bb)$Bb=get_key_vals("SELECT $t, $B FROM ".table($R)." WHERE $t IN (".implode(", ",$Wc).")");foreach($L
as$Zd=>$K){if(isset($K[$y]))$J[$Zd][$y]=(string)$Bb[$K[$y]];}}}return$J;}function
selectLink($X,$p){}function
selectVal($X,$_,$p,$ue){$J=$X;$_=h($_);if(preg_match('~blob|bytea~',$p["type"])&&!is_utf8($X)){$J=lang(40,strlen($ue));if(preg_match("~^(GIF|\xFF\xD8\xFF|\x89PNG\x0D\x0A\x1A\x0A)~",$ue))$J="<img src='$_' alt='$J'>";}if(like_bool($p)&&$J!="")$J=(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?lang(41):lang(42));if($_)$J="<a href='$_'".(is_url($_)?target_blank():"").">$J</a>";if(!$_&&!like_bool($p)&&preg_match(number_type(),$p["type"]))$J="<div class='number'>$J</div>";elseif(preg_match('~date~',$p["type"]))$J="<div class='datetime'>$J</div>";return$J;}function
editVal($X,$p){if(preg_match('~date|timestamp~',$p["type"])&&$X!==null)return
preg_replace('~^(\d{2}(\d+))-(0?(\d+))-(0?(\d+))~',lang(43),$X);return$X;}function
selectColumnsPrint($M,$f){}function
selectSearchPrint($Z,$f,$w){$Z=(array)$_GET["where"];echo'<fieldset id="fieldset-search"><legend>'.lang(44)."</legend><div>\n";$td=array();foreach($Z
as$y=>$X)$td[$X["col"]]=$y;$s=0;$q=fields($_GET["select"]);foreach($f
as$B=>$Ab){$p=$q[$B];if(preg_match("~enum~",$p["type"])||like_bool($p)){$y=$td[$B];$s--;echo"<div>".h($Ab)."<input type='hidden' name='where[$s][col]' value='".h($B)."'>:",(like_bool($p)?" <select name='where[$s][val]'>".optionlist(array(""=>"",lang(42),lang(41)),$Z[$y]["val"],true)."</select>":enum_input("checkbox"," name='where[$s][val][]'",$p,(array)$Z[$y]["val"],($p["null"]?0:null))),"</div>\n";unset($f[$B]);}elseif(is_array($C=$this->_foreignKeyOptions($_GET["select"],$B))){if($q[$B]["null"])$C[0]='('.lang(7).')';$y=$td[$B];$s--;echo"<div>".h($Ab)."<input type='hidden' name='where[$s][col]' value='".h($B)."'><input type='hidden' name='where[$s][op]' value='='>: <select name='where[$s][val]'>".optionlist($C,$Z[$y]["val"],true)."</select></div>\n";unset($f[$B]);}}$s=0;foreach($Z
as$X){if(($X["col"]==""||$f[$X["col"]])&&"$X[col]$X[val]"!=""){echo"<div><select name='where[$s][col]'><option value=''>(".lang(45).")".optionlist($f,$X["col"],true)."</select>",html_select("where[$s][op]",array(-1=>"")+$this->operators,$X["op"]),"<input type='search' name='where[$s][val]' value='".h($X["val"])."'>".script("mixin(qsl('input'), {onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});","")."</div>\n";$s++;}}echo"<div><select name='where[$s][col]'><option value=''>(".lang(45).")".optionlist($f,null,true)."</select>",script("qsl('select').onchange = selectAddRow;",""),html_select("where[$s][op]",array(-1=>"")+$this->operators),"<input type='search' name='where[$s][val]'></div>",script("mixin(qsl('input'), {onchange: function () { this.parentNode.firstChild.onchange(); }, onsearch: selectSearchSearch});"),"</div></fieldset>\n";}function
selectOrderPrint($se,$f,$w){$te=array();foreach($w
as$y=>$v){$se=array();foreach($v["columns"]as$X)$se[]=$f[$X];if(count(array_filter($se,'strlen'))>1&&$y!="PRIMARY")$te[$y]=implode(", ",$se);}if($te){echo'<fieldset><legend>'.lang(46)."</legend><div>","<select name='index_order'>".optionlist(array(""=>"")+$te,($_GET["order"][0]!=""?"":$_GET["index_order"]),true)."</select>","</div></fieldset>\n";}if($_GET["order"])echo"<div style='display: none;'>".hidden_fields(array("order"=>array(1=>reset($_GET["order"])),"desc"=>($_GET["desc"]?array(1=>1):array()),))."</div>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".lang(47)."</legend><div>";echo
html_select("limit",array("","50","100"),$z),"</div></fieldset>\n";}function
selectLengthPrint($eg){}function
selectActionPrint($w){echo"<fieldset><legend>".lang(48)."</legend><div>","<input type='submit' value='".lang(49)."'>","</div></fieldset>\n";}function
selectCommandPrint(){return
true;}function
selectImportPrint(){return
true;}function
selectEmailPrint($Qb,$f){if($Qb){print_fieldset("email",lang(50),$_POST["email_append"]);echo"<div>",script("qsl('div').onkeydown = partialArg(bodyKeydown, 'email');"),"<p>".lang(51).": <input name='email_from' value='".h($_POST?$_POST["email_from"]:$_COOKIE["adminer_email"])."'>\n",lang(52).": <input name='email_subject' value='".h($_POST["email_subject"])."'>\n","<p><textarea name='email_message' rows='15' cols='75'>".h($_POST["email_message"].($_POST["email_append"]?'{$'."$_POST[email_addition]}":""))."</textarea>\n","<p>".script("qsl('p').onkeydown = partialArg(bodyKeydown, 'email_append');","").html_select("email_addition",$f,$_POST["email_addition"])."<input type='submit' name='email_append' value='".lang(11)."'>\n";echo"<p>".lang(53).": <input type='file' name='email_files[]'>".script("qsl('input').onchange = emailFileChange;"),"<p>".(count($Qb)==1?'<input type="hidden" name="email_field" value="'.h(key($Qb)).'">':html_select("email_field",$Qb)),"<input type='submit' name='email' value='".lang(54)."'>".confirm(),"</div>\n","</div></fieldset>\n";}}function
selectColumnsProcess($f,$w){return
array(array(),array());}function
selectSearchProcess($q,$w){$J=array();foreach((array)$_GET["where"]as$y=>$Z){$db=$Z["col"];$ne=$Z["op"];$X=$Z["val"];if(($y<0?"":$db).$X!=""){$jb=array();foreach(($db!=""?array($db=>$q[$db]):$q)as$B=>$p){if($db!=""||is_numeric($X)||!preg_match(number_type(),$p["type"])){$B=idf_escape($B);if($db!=""&&$p["type"]=="enum")$jb[]=(in_array(0,$X)?"$B IS NULL OR ":"")."$B IN (".implode(", ",array_map('intval',$X)).")";else{$fg=preg_match('~char|text|enum|set~',$p["type"]);$Y=$this->processInput($p,(!$ne&&$fg&&preg_match('~^[^%]+$~',$X)?"%$X%":$X));$jb[]=$B.($Y=="NULL"?" IS".($ne==">="?" NOT":"")." $Y":(in_array($ne,$this->operators)||$ne=="="?" $ne $Y":($fg?" LIKE $Y":" IN (".str_replace(",","', '",$Y).")")));if($y<0&&$X=="0")$jb[]="$B IS NULL";}}}$J[]=($jb?"(".implode(" OR ",$jb).")":"1 = 0");}}return$J;}function
selectOrderProcess($q,$w){$Zc=$_GET["index_order"];if($Zc!="")unset($_GET["order"][1]);if($_GET["order"])return
array(idf_escape(reset($_GET["order"])).($_GET["desc"]?" DESC":""));foreach(($Zc!=""?array($w[$Zc]):$w)as$v){if($Zc!=""||$v["type"]=="INDEX"){$Kc=array_filter($v["descs"]);$Ab=false;foreach($v["columns"]as$X){if(preg_match('~date|timestamp~',$q[$X]["type"])){$Ab=true;break;}}$J=array();foreach($v["columns"]as$y=>$X)$J[]=idf_escape($X).(($Kc?$v["descs"][$y]:$Ab)?" DESC":"");return$J;}}return
array();}function
selectLimitProcess(){return(isset($_GET["limit"])?$_GET["limit"]:"50");}function
selectLengthProcess(){return"100";}function
selectEmailProcess($Z,$xc){if($_POST["email_append"])return
true;if($_POST["email"]){$vf=0;if($_POST["all"]||$_POST["check"]){$p=idf_escape($_POST["email_field"]);$Tf=$_POST["email_subject"];$Sd=$_POST["email_message"];preg_match_all('~\{\$([a-z0-9_]+)\}~i',"$Tf.$Sd",$Md);$L=get_rows("SELECT DISTINCT $p".($Md[1]?", ".implode(", ",array_map('idf_escape',array_unique($Md[1]))):"")." FROM ".table($_GET["select"])." WHERE $p IS NOT NULL AND $p != ''".($Z?" AND ".implode(" AND ",$Z):"").($_POST["all"]?"":" AND ((".implode(") OR (",array_map('where_check',(array)$_POST["check"]))."))"));$q=fields($_GET["select"]);foreach($this->rowDescriptions($L,$xc)as$K){$ef=array('{\\'=>'{');foreach($Md[1]as$X)$ef['{$'."$X}"]=$this->editVal($K[$X],$q[$X]);$Pb=$K[$_POST["email_field"]];if(is_mail($Pb)&&send_mail($Pb,strtr($Tf,$ef),strtr($Sd,$ef),$_POST["email_from"],$_FILES["email_files"]))$vf++;}}cookie("adminer_email",$_POST["email_from"]);redirect(remove_from_uri(),lang(55,$vf));}return
false;}function
selectQueryBuild($M,$Z,$Fc,$se,$z,$D){return"";}function
messageQuery($G,$gg,$hc=false){return" <span class='time'>".@date("H:i:s")."</span><!--\n".str_replace("--","--><!-- ",$G)."\n".($gg?"($gg)\n":"")."-->";}function
editFunctions($p){$J=array();if($p["null"]&&preg_match('~blob~',$p["type"]))$J["NULL"]=lang(7);$J[""]=($p["null"]||$p["auto_increment"]||like_bool($p)?"":"*");if(preg_match('~date|time~',$p["type"]))$J["now"]=lang(56);if(preg_match('~_(md5|sha1)$~i',$p["field"],$A))$J[]=strtolower($A[1]);return$J;}function
editInput($R,$p,$Da,$Y){if($p["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$Da value='-1' checked><i>".lang(8)."</i></label> ":"").enum_input("radio",$Da,$p,($Y||isset($_GET["select"])?$Y:0),($p["null"]?"":null));$C=$this->_foreignKeyOptions($R,$p["field"],$Y);if($C!==null)return(is_array($C)?"<select$Da>".optionlist($C,$Y,true)."</select>":"<input value='".h($Y)."'$Da class='hidden'>"."<input value='".h($C)."' class='jsonly'>"."<div></div>".script("qsl('input').oninput = partial(whisper, '".ME."script=complete&source=".urlencode($R)."&field=".urlencode($p["field"])."&value=');
qsl('div').onclick = whisperClick;",""));if(like_bool($p))return'<input type="checkbox" value="1"'.(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?' checked':'')."$Da>";$Qc="";if(preg_match('~time~',$p["type"]))$Qc=lang(57);if(preg_match('~date|timestamp~',$p["type"]))$Qc=lang(58).($Qc?" [$Qc]":"");if($Qc)return"<input value='".h($Y)."'$Da> ($Qc)";if(preg_match('~_(md5|sha1)$~i',$p["field"]))return"<input type='password' value='".h($Y)."'$Da>";return'';}function
editHint($R,$p,$Y){return(preg_match('~\s+(\[.*\])$~',($p["comment"]!=""?$p["comment"]:$p["field"]),$A)?h(" $A[1]"):'');}function
processInput($p,$Y,$Dc=""){if($Dc=="now")return"$Dc()";$J=$Y;if(preg_match('~date|timestamp~',$p["type"])&&preg_match('(^'.str_replace('\$1','(?P<p1>\d*)',preg_replace('~(\\\\\\$([2-6]))~','(?P<p\2>\d{1,2})',preg_quote(lang(43)))).'(.*))',$Y,$A))$J=($A["p1"]!=""?$A["p1"]:($A["p2"]!=""?($A["p2"]<70?20:19).$A["p2"]:gmdate("Y")))."-$A[p3]$A[p4]-$A[p5]$A[p6]".end($A);$J=($p["type"]=="bit"&&preg_match('~^[0-9]+$~',$Y)?$J:q($J));if($Y==""&&like_bool($p))$J="'0'";elseif($Y==""&&($p["null"]||!preg_match('~char|text~',$p["type"])))$J="NULL";elseif(preg_match('~^(md5|sha1)$~',$Dc))$J="$Dc($J)";return
unconvert_field($p,$J);}function
dumpOutput(){return
array();}function
dumpFormat(){return
array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($m){}function
dumpTable(){echo"\xef\xbb\xbf";}function
dumpData($R,$Sf,$G){global$h;$I=$h->query($G,1);if($I){while($K=$I->fetch_assoc()){if($Sf=="table"){dump_csv(array_keys($K));$Sf="INSERT";}dump_csv($K);}}}function
dumpFilename($Uc){return
friendly_url($Uc);}function
dumpHeaders($Uc,$Xd=false){$dc="csv";header("Content-Type: text/csv; charset=utf-8");return$dc;}function
importServerPath(){}function
homepage(){return
true;}function
navigation($Wd){global$ca;echo'<h1>
',$this->name(),' <span class="version">',$ca,'</span>
<a href="https://www.adminer.org/editor/#download"',target_blank(),' id="version">',(version_compare($ca,$_COOKIE["adminer_version"])<0?h($_COOKIE["adminer_version"]):""),'</a>
</h1>
';if($Wd=="auth"){$pc=true;foreach((array)$_SESSION["pwds"]as$Qg=>$_f){foreach($_f[""]as$V=>$F){if($F!==null){if($pc){echo"<ul id='logins'>",script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");$pc=false;}echo"<li><a href='".h(auth_url($Qg,"",$V))."'>".($V!=""?h($V):"<i>".lang(7)."</i>")."</a>\n";}}}}else{$this->databasesPrint($Wd);if($Wd!="db"&&$Wd!="ns"){$S=table_status('',true);if(!$S)echo"<p class='message'>".lang(9)."\n";else$this->tablesPrint($S);}}}function
databasesPrint($Wd){}function
tablesPrint($T){echo"<ul id='tables'>",script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");foreach($T
as$K){echo'<li>';$B=$this->tableName($K);if(isset($K["Engine"])&&$B!="")echo"<a href='".h(ME).'select='.urlencode($K["Name"])."'".bold($_GET["select"]==$K["Name"]||$_GET["edit"]==$K["Name"],"select")." title='".lang(59)."'>$B</a>\n";}echo"</ul>\n";}function
_foreignColumn($xc,$e){foreach((array)$xc[$e]as$wc){if(count($wc["source"])==1){$B=$this->rowDescription($wc["table"]);if($B!=""){$t=idf_escape($wc["target"][0]);return
array($wc["table"],$t,$B);}}}}function
_foreignKeyOptions($R,$e,$Y=null){global$h;if(list($bg,$t,$B)=$this->_foreignColumn(column_foreign_keys($R),$e)){$J=&$this->_values[$bg];if($J===null){$S=table_status($bg);$J=($S["Rows"]>1000?"":array(""=>"")+get_key_vals("SELECT $t, $B FROM ".table($bg)." ORDER BY 2"));}if(!$J&&$Y!==null)return$h->result("SELECT $B FROM ".table($bg)." WHERE $t = ".q($Y));return$J;}}}$b=(function_exists('adminer_object')?adminer_object():new
Adminer);function
page_header($jg,$o="",$Ra=array(),$kg=""){global$ba,$ca,$b,$Hb,$x;page_headers();if(is_ajax()&&$o){page_messages($o);exit;}$lg=$jg.($kg!=""?": $kg":"");$mg=strip_tags($lg.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".$b->name());echo'<!DOCTYPE html>
<html lang="',$ba,'" dir="',lang(60),'">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<title>',$mg,'</title>
<link rel="stylesheet" type="text/css" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=4.7.1"),'">
',script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=4.7.1");if($b->head()){echo'<link rel="shortcut icon" type="image/x-icon" href="',h(preg_replace("~\\?.*~","",ME)."?file=favicon.ico&version=4.7.1"),'">
<link rel="apple-touch-icon" href="',h(preg_replace("~\\?.*~","",ME)."?file=favicon.ico&version=4.7.1"),'">
';foreach($b->css()as$tb){echo'<link rel="stylesheet" type="text/css" href="',h($tb),'">
';}}echo'
<body class="',lang(60),' nojs">
';$r=get_temp_dir()."/adminer.version";if(!$_COOKIE["adminer_version"]&&function_exists('openssl_verify')&&file_exists($r)&&filemtime($r)+86400>time()){$Rg=unserialize(file_get_contents($r));$Re="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";if(openssl_verify($Rg["version"],base64_decode($Rg["signature"]),$Re)==1)$_COOKIE["adminer_version"]=$Rg["version"];}echo'<script',nonce(),'>
mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick',(isset($_COOKIE["adminer_version"])?"":", onload: partial(verifyVersion, '$ca', '".js_escape(ME)."', '".get_token()."')");?>});
document.body.className = document.body.className.replace(/ nojs/, ' js');
var offlineMessage = '<?php echo
js_escape(lang(61)),'\';
var thousandsSeparator = \'',js_escape(lang(5)),'\';
</script>

<div id="help" class="jush-',$x,' jsonly hidden"></div>
',script("mixin(qs('#help'), {onmouseover: function () { helpOpen = 1; }, onmouseout: helpMouseout});"),'
<div id="content">
';if($Ra!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?$_:".").'">'.$Hb[DRIVER].'</a> &raquo; ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$O=$b->serverName(SERVER);$O=($O!=""?$O:lang(62));if($Ra===false)echo"$O\n";else{echo"<a href='".($_?h($_):".")."' accesskey='1' title='Alt+Shift+1'>$O</a> &raquo; ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ra)))echo'<a href="'.h($_."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> &raquo; ';if(is_array($Ra)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> &raquo; ';foreach($Ra
as$y=>$X){$Ab=(is_array($X)?$X[1]:h($X));if($Ab!="")echo"<a href='".h(ME."$y=").urlencode(is_array($X)?$X[0]:$X)."'>$Ab</a> &raquo; ";}}echo"$jg\n";}}echo"<h2>$lg</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($o);$l=&get_session("dbs");if(DB!=""&&$l&&!in_array(DB,$l,true))$l=null;stop_session();define("PAGE_HEADER",1);}function
page_headers(){global$b;header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach($b->csp()as$sb){$Nc=array();foreach($sb
as$y=>$X)$Nc[]="$y $X";header("Content-Security-Policy: ".implode("; ",$Nc));}$b->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self'","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
get_nonce(){static$de;if(!$de)$de=base64_encode(rand_string());return$de;}function
page_messages($o){$Jg=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Td=$_SESSION["messages"][$Jg];if($Td){echo"<div class='message'>".implode("</div>\n<div class='message'>",$Td)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$Jg]);}if($o)echo"<div class='error'>$o</div>\n";}function
page_footer($Wd=""){global$b,$pg;echo'</div>

';switch_lang();if($Wd!="auth"){echo'<form action="" method="post">
<p class="logout">
<input type="submit" name="logout" value="',lang(63),'" id="logout">
<input type="hidden" name="token" value="',$pg,'">
</p>
</form>
';}echo'<div id="menu">
';$b->navigation($Wd);echo'</div>
',script("setupSubmitHighlight(document);");}function
int32($Zd){while($Zd>=2147483648)$Zd-=4294967296;while($Zd<=-2147483649)$Zd+=4294967296;return(int)$Zd;}function
long2str($W,$Vg){$nf='';foreach($W
as$X)$nf.=pack('V',$X);if($Vg)return
substr($nf,0,end($W));return$nf;}function
str2long($nf,$Vg){$W=array_values(unpack('V*',str_pad($nf,4*ceil(strlen($nf)/4),"\0")));if($Vg)$W[]=strlen($nf);return$W;}function
xxtea_mx($gh,$fh,$Vf,$pd){return
int32((($gh>>5&0x7FFFFFF)^$fh<<2)+(($fh>>3&0x1FFFFFFF)^$gh<<4))^int32(($Vf^$fh)+($pd^$gh));}function
encrypt_string($Qf,$y){if($Qf=="")return"";$y=array_values(unpack("V*",pack("H*",md5($y))));$W=str2long($Qf,true);$Zd=count($W)-1;$gh=$W[$Zd];$fh=$W[0];$Se=floor(6+52/($Zd+1));$Vf=0;while($Se-->0){$Vf=int32($Vf+0x9E3779B9);$Lb=$Vf>>2&3;for($xe=0;$xe<$Zd;$xe++){$fh=$W[$xe+1];$Yd=xxtea_mx($gh,$fh,$Vf,$y[$xe&3^$Lb]);$gh=int32($W[$xe]+$Yd);$W[$xe]=$gh;}$fh=$W[0];$Yd=xxtea_mx($gh,$fh,$Vf,$y[$xe&3^$Lb]);$gh=int32($W[$Zd]+$Yd);$W[$Zd]=$gh;}return
long2str($W,false);}function
decrypt_string($Qf,$y){if($Qf=="")return"";if(!$y)return
false;$y=array_values(unpack("V*",pack("H*",md5($y))));$W=str2long($Qf,false);$Zd=count($W)-1;$gh=$W[$Zd];$fh=$W[0];$Se=floor(6+52/($Zd+1));$Vf=int32($Se*0x9E3779B9);while($Vf){$Lb=$Vf>>2&3;for($xe=$Zd;$xe>0;$xe--){$gh=$W[$xe-1];$Yd=xxtea_mx($gh,$fh,$Vf,$y[$xe&3^$Lb]);$fh=int32($W[$xe]-$Yd);$W[$xe]=$fh;}$gh=$W[$Zd];$Yd=xxtea_mx($gh,$fh,$Vf,$y[$xe&3^$Lb]);$fh=int32($W[0]-$Yd);$W[0]=$fh;$Vf=int32($Vf-0x9E3779B9);}return
long2str($W,true);}$h='';$Mc=$_SESSION["token"];if(!$Mc)$_SESSION["token"]=rand(1,1e6);$pg=get_token();$Fe=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($y)=explode(":",$X);$Fe[$y]=$X;}}function
add_invalid_login(){global$b;$Bc=file_open_lock(get_temp_dir()."/adminer.invalid");if(!$Bc)return;$ld=unserialize(stream_get_contents($Bc));$gg=time();if($ld){foreach($ld
as$md=>$X){if($X[0]<$gg)unset($ld[$md]);}}$kd=&$ld[$b->bruteForceKey()];if(!$kd)$kd=array($gg+30*60,0);$kd[1]++;file_write_unlock($Bc,serialize($ld));}function
check_invalid_login(){global$b;$ld=unserialize(@file_get_contents(get_temp_dir()."/adminer.invalid"));$kd=$ld[$b->bruteForceKey()];$ce=($kd[1]>29?$kd[0]-time():0);if($ce>0)auth_error(lang(64,ceil($ce/60)));}$Ea=$_POST["auth"];if($Ea){session_regenerate_id();$Qg=$Ea["driver"];$O=$Ea["server"];$V=$Ea["username"];$F=(string)$Ea["password"];$m=$Ea["db"];set_password($Qg,$O,$V,$F);$_SESSION["db"][$Qg][$O][$V][$m]=true;if($Ea["permanent"]){$y=base64_encode($Qg)."-".base64_encode($O)."-".base64_encode($V)."-".base64_encode($m);$Oe=$b->permanentLogin(true);$Fe[$y]="$y:".base64_encode($Oe?encrypt_string($F,$Oe):"");cookie("adminer_permanent",implode(" ",$Fe));}if(count($_POST)==1||DRIVER!=$Qg||SERVER!=$O||$_GET["username"]!==$V||DB!=$m)redirect(auth_url($Qg,$O,$V,$m));}elseif($_POST["logout"]){if($Mc&&!verify_token()){page_header(lang(63),lang(65));page_footer("db");exit;}else{foreach(array("pwds","db","dbs","queries")as$y)set_session($y,null);unset_permanent();redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),lang(66).' '.lang(67));}}elseif($Fe&&!$_SESSION["pwds"]){session_regenerate_id();$Oe=$b->permanentLogin();foreach($Fe
as$y=>$X){list(,$Za)=explode(":",$X);list($Qg,$O,$V,$m)=array_map('base64_decode',explode("-",$y));set_password($Qg,$O,$V,decrypt_string(base64_decode($Za),$Oe));$_SESSION["db"][$Qg][$O][$V][$m]=true;}}function
unset_permanent(){global$Fe;foreach($Fe
as$y=>$X){list($Qg,$O,$V,$m)=array_map('base64_decode',explode("-",$y));if($Qg==DRIVER&&$O==SERVER&&$V==$_GET["username"]&&$m==DB)unset($Fe[$y]);}cookie("adminer_permanent",implode(" ",$Fe));}function
auth_error($o){global$b,$Mc;$Af=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Af]||$_GET[$Af])&&!$Mc)$o=lang(68);else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$o.='<br>'.lang(69,target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent();}}if(!$_COOKIE[$Af]&&$_GET[$Af]&&ini_bool("session.use_only_cookies"))$o=lang(70);$E=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?$_COOKIE["adminer_key"]:rand_string()),$E["lifetime"]);page_header(lang(37),$o,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".lang(71)."\n";echo"</div>\n";$b->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists("Min_DB")){unset($_SESSION["pwds"][DRIVER]);unset_permanent();page_header(lang(72),lang(73,implode(", ",$Je)),false);page_footer("auth");exit;}stop_session(true);if(isset($_GET["username"])){list($Sc,$He)=explode(":",SERVER,2);if(is_numeric($He)&&$He<1024)auth_error(lang(74));check_invalid_login();$h=connect();$n=new
Min_Driver($h);}$Gd=null;if(!is_object($h)||($Gd=$b->login($_GET["username"],get_password()))!==true){$o=(is_string($h)?h($h):(is_string($Gd)?$Gd:lang(75)));auth_error($o.(preg_match('~^ | $~',get_password())?'<br>'.lang(76):''));}if($Ea&&$_POST["token"])$_POST["token"]=$pg;$o='';if($_POST){if(!verify_token()){$gd="max_input_vars";$Qd=ini_get($gd);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars")as$y){$X=ini_get($y);if($X&&(!$Qd||$X<$Qd)){$gd=$y;$Qd=$X;}}}$o=(!$_POST["token"]&&$Qd?lang(77,"'$gd'"):lang(65).' '.lang(78));}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$o=lang(79,"'post_max_size'");if(isset($_GET["sql"]))$o.=' '.lang(80);}function
email_header($Nc){return"=?UTF-8?B?".base64_encode($Nc)."?=";}function
send_mail($Pb,$Tf,$Sd,$Cc="",$nc=array()){$Vb=(DIRECTORY_SEPARATOR=="/"?"\n":"\r\n");$Sd=str_replace("\n",$Vb,wordwrap(str_replace("\r","","$Sd\n")));$Qa=uniqid("boundary");$Ba="";foreach((array)$nc["error"]as$y=>$X){if(!$X)$Ba.="--$Qa$Vb"."Content-Type: ".str_replace("\n","",$nc["type"][$y]).$Vb."Content-Disposition: attachment; filename=\"".preg_replace('~["\n]~','',$nc["name"][$y])."\"$Vb"."Content-Transfer-Encoding: base64$Vb$Vb".chunk_split(base64_encode(file_get_contents($nc["tmp_name"][$y])),76,$Vb).$Vb;}$La="";$Oc="Content-Type: text/plain; charset=utf-8$Vb"."Content-Transfer-Encoding: 8bit";if($Ba){$Ba.="--$Qa--$Vb";$La="--$Qa$Vb$Oc$Vb$Vb";$Oc="Content-Type: multipart/mixed; boundary=\"$Qa\"";}$Oc.=$Vb."MIME-Version: 1.0$Vb"."X-Mailer: Adminer Editor".($Cc?$Vb."From: ".str_replace("\n","",$Cc):"");return
mail($Pb,email_header($Tf),$La.$Sd.$Ba,$Oc);}function
like_bool($p){return
preg_match("~bool|(tinyint|bit)\\(1\\)~",$p["full_type"]);}$h->select_db($b->database());$ke="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";$Hb[DRIVER]=lang(37);if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["download"])){$a=$_GET["download"];$q=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$I=$n->select($a,$M,array(where($_GET,$q)),$M);$K=($I?$I->fetch_row():array());echo$n->value($K[0],$q[$_GET["field"]]);exit;}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$q=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$q):""):where($_GET,$q));$Ig=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($q
as$B=>$p){if(!isset($p["privileges"][$Ig?"update":"insert"])||$b->fieldName($p)=="")unset($q[$B]);}if($_POST&&!$o&&!isset($_GET["select"])){$Fd=$_POST["referer"];if($_POST["insert"])$Fd=($Ig?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Fd))$Fd=ME."select=".urlencode($a);$w=indexes($a);$Dg=unique_array($_GET["where"],$w);$Ue="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Fd,lang(81),$n->delete($a,$Ue,!$Dg));else{$P=array();foreach($q
as$B=>$p){$X=process_input($p);if($X!==false&&$X!==null)$P[idf_escape($B)]=$X;}if($Ig){if(!$P)redirect($Fd);queries_redirect($Fd,lang(82),$n->update($a,$P,$Ue,!$Dg));if(is_ajax()){page_headers();page_messages($o);exit;}}else{$I=$n->insert($a,$P);$_d=($I?last_id():0);queries_redirect($Fd,lang(83,($_d?" $_d":"")),$I);}}}$K=null;if($_POST["save"])$K=(array)$_POST["fields"];elseif($Z){$M=array();foreach($q
as$B=>$p){if(isset($p["privileges"]["select"])){$_a=convert_field($p);if($_POST["clone"]&&$p["auto_increment"])$_a="''";if($x=="sql"&&preg_match("~enum|set~",$p["type"]))$_a="1*".idf_escape($B);$M[]=($_a?"$_a AS ":"").idf_escape($B);}}$K=array();if(!support("table"))$M=array("*");if($M){$I=$n->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));if(!$I)$o=error();else{$K=$I->fetch_assoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetch_assoc()))$K=null;}}if(!support("table")&&!$q){if(!$Z){$I=$n->select($a,array("*"),$Z,array("*"));$K=($I?$I->fetch_assoc():false);if(!$K)$K=array($n->primary=>"");}if($K){foreach($K
as$y=>$X){if(!$Z)$K[$y]=null;$q[$y]=array("field"=>$y,"null"=>($y!=$n->primary),"auto_increment"=>($y==$n->primary));}}}edit_form($a,$q,$K,$Ig);}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$w=indexes($a);$q=fields($a);$zc=column_foreign_keys($a);$je=$S["Oid"];parse_str($_COOKIE["adminer_import"],$ta);$lf=array();$f=array();$eg=null;foreach($q
as$y=>$p){$B=$b->fieldName($p);if(isset($p["privileges"]["select"])&&$B!=""){$f[$y]=html_entity_decode(strip_tags($B),ENT_QUOTES);if(is_shortable($p))$eg=$b->selectLengthProcess();}$lf+=$p["privileges"];}list($M,$Fc)=$b->selectColumnsProcess($f,$w);$nd=count($Fc)<count($M);$Z=$b->selectSearchProcess($q,$w);$se=$b->selectOrderProcess($q,$w);$z=$b->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Eg=>$K){$_a=convert_field($q[key($K)]);$M=array($_a?$_a:idf_escape(key($K)));$Z[]=where_check($Eg,$q);$J=$n->select($a,$M,$Z,$M);if($J)echo
reset($J->fetch_row());}exit;}$Le=$Gg=null;foreach($w
as$v){if($v["type"]=="PRIMARY"){$Le=array_flip($v["columns"]);$Gg=($M?$Le:array());foreach($Gg
as$y=>$X){if(in_array(idf_escape($y),$M))unset($Gg[$y]);}break;}}if($je&&!$Le){$Le=$Gg=array($je=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($je));}if($_POST&&!$o){$ah=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Xa=array();foreach($_POST["check"]as$Ua)$Xa[]=where_check($Ua,$q);$ah[]="((".implode(") OR (",$Xa)."))";}$ah=($ah?"\nWHERE ".implode(" AND ",$ah):"");if($_POST["export"]){cookie("adminer_import","output=".urlencode($_POST["output"])."&format=".urlencode($_POST["format"]));dump_headers($a);$b->dumpTable($a,"");$Cc=($M?implode(", ",$M):"*").convert_fields($f,$q,$M)."\nFROM ".table($a);$Hc=($Fc&&$nd?"\nGROUP BY ".implode(", ",$Fc):"").($se?"\nORDER BY ".implode(", ",$se):"");if(!is_array($_POST["check"])||$Le)$G="SELECT $Cc$ah$Hc";else{$Cg=array();foreach($_POST["check"]as$X)$Cg[]="(SELECT".limit($Cc,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$q).$Hc,1).")";$G=implode(" UNION ALL ",$Cg);}$b->dumpData($a,"table",$G);exit;}if(!$b->selectEmailProcess($Z,$zc)){if($_POST["save"]||$_POST["delete"]){$I=true;$ua=0;$P=array();if(!$_POST["delete"]){foreach($f
as$B=>$X){$X=process_input($q[$B]);if($X!==null&&($_POST["clone"]||$X!==false))$P[idf_escape($B)]=($X!==false?$X:idf_escape($B));}}if($_POST["delete"]||$P){if($_POST["clone"])$G="INTO ".table($a)." (".implode(", ",array_keys($P)).")\nSELECT ".implode(", ",$P)."\nFROM ".table($a);if($_POST["all"]||($Le&&is_array($_POST["check"]))||$nd){$I=($_POST["delete"]?$n->delete($a,$ah):($_POST["clone"]?queries("INSERT $G$ah"):$n->update($a,$P,$ah)));$ua=$h->affected_rows;}else{foreach((array)$_POST["check"]as$X){$Wg="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$q);$I=($_POST["delete"]?$n->delete($a,$Wg,1):($_POST["clone"]?queries("INSERT".limit1($a,$G,$Wg)):$n->update($a,$P,$Wg,1)));if(!$I)break;$ua+=$h->affected_rows;}}}$Sd=lang(84,$ua);if($_POST["clone"]&&$I&&$ua==1){$_d=last_id();if($_d)$Sd=lang(83," $_d");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$Sd,$I);if(!$_POST["delete"]){edit_form($a,$q,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$o=lang(85);else{$I=true;$ua=0;foreach($_POST["val"]as$Eg=>$K){$P=array();foreach($K
as$y=>$X){$y=bracket_escape($y,1);$P[idf_escape($y)]=(preg_match('~char|text~',$q[$y]["type"])||$X!=""?$b->processInput($q[$y],$X):"NULL");}$I=$n->update($a,$P," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($Eg,$q),!$nd&&!$Le," ");if(!$I)break;$ua+=$h->affected_rows;}queries_redirect(remove_from_uri(),lang(84,$ua),$I);}}elseif(!is_string($mc=get_file("csv_file",true)))$o=upload_error($mc);elseif(!preg_match('~~u',$mc))$o=lang(86);else{cookie("adminer_import","output=".urlencode($ta["output"])."&format=".urlencode($_POST["separator"]));$I=true;$gb=array_keys($q);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$mc,$Md);$ua=count($Md[0]);$n->begin();$N=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$L=array();foreach($Md[0]as$y=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$N]*)$N~",$X.$N,$Nd);if(!$y&&!array_diff($Nd[1],$gb)){$gb=$Nd[1];$ua--;}else{$P=array();foreach($Nd[1]as$s=>$db)$P[idf_escape($gb[$s])]=($db==""&&$q[$gb[$s]]["null"]?"NULL":q(str_replace('""','"',preg_replace('~^"|"$~','',$db))));$L[]=$P;}}$I=(!$L||$n->insertUpdate($a,$L,$Le));if($I)$I=$n->commit();queries_redirect(remove_from_uri("page"),lang(87,$ua),$I);$n->rollback();}}}$Yf=$b->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(49).": $Yf",$o);$P=null;if(isset($lf["insert"])||!support("table")){$P="";foreach((array)$_GET["where"]as$X){if($zc[$X["col"]]&&count($zc[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&!preg_match('~[_%]~',$X["val"]))))$P.="&set".urlencode("[".bracket_escape($X["col"])."]")."=".urlencode($X["val"]);}}$b->selectLinks($S,$P);if(!$f&&support("table"))echo"<p class='error'>".lang(88).($q?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?'<input type="hidden" name="db" value="'.h(DB).'">'.(isset($_GET["ns"])?'<input type="hidden" name="ns" value="'.h($_GET["ns"]).'">':""):"");echo'<input type="hidden" name="select" value="'.h($a).'">',"</div>\n";$b->selectColumnsPrint($M,$f);$b->selectSearchPrint($Z,$f,$w);$b->selectOrderPrint($se,$f,$w);$b->selectLimitPrint($z);$b->selectLengthPrint($eg);$b->selectActionPrint($w);echo"</form>\n";$D=$_GET["page"];if($D=="last"){$Ac=$h->result(count_rows($a,$Z,$nd,$Fc));$D=floor(max(0,$Ac-1)/$z);}$sf=$M;$Gc=$Fc;if(!$sf){$sf[]="*";$ob=convert_fields($f,$q,$M);if($ob)$sf[]=substr($ob,2);}foreach($M
as$y=>$X){$p=$q[idf_unescape($X)];if($p&&($_a=convert_field($p)))$sf[$y]="$_a AS $X";}if(!$nd&&$Gg){foreach($Gg
as$y=>$X){$sf[]=idf_escape($y);if($Gc)$Gc[]=idf_escape($y);}}$I=$n->select($a,$sf,$Z,$Gc,$se,$z,$D,true);if(!$I)echo"<p class='error'>".error()."\n";else{if($x=="mssql"&&$D)$I->seek($z*$D);$Rb=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$L=array();while($K=$I->fetch_assoc()){if($D&&$x=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$z!=""&&$Fc&&$nd&&$x=="sql")$Ac=$h->result(" SELECT FOUND_ROWS()");if(!$L)echo"<p class='message'>".lang(12)."\n";else{$Ka=$b->backwardKeys($a,$Yf);echo"<div class='scrollable'>","<table id='table' cellspacing='0' class='nowrap checkable'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$Fc&&$M?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".lang(89)."</a>");$ae=array();$Ec=array();reset($M);$We=1;foreach($L[0]as$y=>$X){if(!isset($Gg[$y])){$X=$_GET["columns"][key($M)];$p=$q[$M?($X?$X["col"]:current($M)):$y];$B=($p?$b->fieldName($p,$We):($X["fun"]?"*":$y));if($B!=""){$We++;$ae[$y]=$B;$e=idf_escape($y);$Tc=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($y);$Ab="&desc%5B0%5D=1";echo"<th>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});",""),'<a href="'.h($Tc.($se[0]==$e||$se[0]==$y||(!$se&&$nd&&$Fc[0]==$e)?$Ab:'')).'">';echo
apply_sql_function($X["fun"],$B)."</a>";echo"<span class='column hidden'>","<a href='".h($Tc.$Ab)."' title='".lang(90)."' class='text'> â†“</a>";if(!$X["fun"]){echo'<a href="#fieldset-search" title="'.lang(44).'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($y)."');");}echo"</span>";}$Ec[$y]=$X["fun"];next($M);}}$Cd=array();if($_GET["modify"]){foreach($L
as$K){foreach($K
as$y=>$X)$Cd[$y]=max($Cd[$y],min(40,strlen(utf8_decode($X))));}}echo($Ka?"<th>".lang(91):"")."</thead>\n";if(is_ajax()){if($z%2==1&&$D%2==1)odd();ob_end_clean();}foreach($b->rowDescriptions($L,$zc)as$Zd=>$K){$Dg=unique_array($L[$Zd],$w);if(!$Dg){$Dg=array();foreach($L[$Zd]as$y=>$X){if(!preg_match('~^(COUNT\((\*|(DISTINCT )?`(?:[^`]|``)+`)\)|(AVG|GROUP_CONCAT|MAX|MIN|SUM)\(`(?:[^`]|``)+`\))$~',$y))$Dg[$y]=$X;}}$Eg="";foreach($Dg
as$y=>$X){if(($x=="sql"||$x=="pgsql")&&preg_match('~char|text|enum|set~',$q[$y]["type"])&&strlen($X)>64){$y=(strpos($y,'(')?$y:idf_escape($y));$y="MD5(".($x!='sql'||preg_match("~^utf8~",$q[$y]["collation"])?$y:"CONVERT($y USING ".charset($h).")").")";$X=md5($X);}$Eg.="&".($X!==null?urlencode("where[".bracket_escape($y)."]")."=".urlencode($X):"null%5B%5D=".urlencode($y));}echo"<tr".odd().">".(!$Fc&&$M?"":"<td>".checkbox("check[]",substr($Eg,1),in_array(substr($Eg,1),(array)$_POST["check"])).($nd||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$Eg)."' class='edit'>".lang(92)."</a>"));foreach($K
as$y=>$X){if(isset($ae[$y])){$p=$q[$y];$X=$n->value($X,$p);if($X!=""&&(!isset($Rb[$y])||$Rb[$y]!=""))$Rb[$y]=(is_mail($X)?$ae[$y]:"");$_="";if(preg_match('~blob|bytea|raw|file~',$p["type"])&&$X!="")$_=ME.'download='.urlencode($a).'&field='.urlencode($y).$Eg;if(!$_&&$X!==null){foreach((array)$zc[$y]as$yc){if(count($zc[$y])==1||end($yc["source"])==$y){$_="";foreach($yc["source"]as$s=>$Hf)$_.=where_link($s,$yc["target"][$s],$L[$Zd][$Hf]);$_=($yc["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($yc["db"]),ME):ME).'select='.urlencode($yc["table"]).$_;if($yc["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($yc["ns"]),$_);if(count($yc["source"])==1)break;}}}if($y=="COUNT(*)"){$_=ME."select=".urlencode($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Dg))$_.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($Dg
as$pd=>$W)$_.=where_link($s++,$pd,$W);}$X=select_value($X,$_,$p,$eg);$t=h("val[$Eg][".bracket_escape($y)."]");$Y=$_POST["val"][$Eg][bracket_escape($y)];$Nb=!is_array($K[$y])&&is_utf8($X)&&$L[$Zd][$y]==$K[$y]&&!$Ec[$y];$dg=preg_match('~text|lob~',$p["type"]);if(($_GET["modify"]&&$Nb)||$Y!==null){$Jc=h($Y!==null?$Y:$K[$y]);echo"<td>".($dg?"<textarea name='$t' cols='30' rows='".(substr_count($K[$y],"\n")+1)."'>$Jc</textarea>":"<input name='$t' value='$Jc' size='$Cd[$y]'>");}else{$Hd=strpos($X,"<i>â€¦</i>");echo"<td id='$t' data-text='".($Hd?2:($dg?1:0))."'".($Nb?"":" data-warning='".h(lang(93))."'").">$X</td>";}}}if($Ka)echo"<td>";$b->backwardKeysPrint($Ka,$L[$Zd]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($L||$D){$Zb=true;if($_GET["page"]!="last"){if($z==""||(count($L)<$z&&($L||!$D)))$Ac=($D?$D*$z:0)+count($L);elseif($x!="sql"||!$nd){$Ac=($nd?false:found_rows($S,$Z));if($Ac<max(1e4,2*($D+1)*$z))$Ac=reset(slow_query(count_rows($a,$Z,$nd,$Fc)));else$Zb=false;}}$ye=($z!=""&&($Ac===false||$Ac>$z||$D));if($ye){echo(($Ac===false?count($L)+1:$Ac-$D*$z)>$z?'<p><a href="'.h(remove_from_uri("page")."&page=".($D+1)).'" class="loadmore">'.lang(94).'</a>'.script("qsl('a').onclick = partial(selectLoadMore, ".(+$z).", '".lang(95)."â€¦');",""):''),"\n";}}echo"<div class='footer'><div>\n";if($L||$D){if($ye){$Od=($Ac===false?$D+(count($L)>=$z?2:1):floor(($Ac-1)/$z));echo"<fieldset>";if($x!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".lang(96)."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".lang(96)."', '".($D+1)."')); return false; };"),pagination(0,$D).($D>5?" â€¦":"");for($s=max(1,$D-4);$s<min($Od,$D+5);$s++)echo
pagination($s,$D);if($Od>0){echo($D+5<$Od?" â€¦":""),($Zb&&$Ac!==false?pagination($Od,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Od'>".lang(97)."</a>");}}else{echo"<legend>".lang(96)."</legend>",pagination(0,$D).($D>1?" â€¦":""),($D?pagination($D,$D):""),($Od>$D?pagination($D+1,$D).($Od>$D+1?" â€¦":""):"");}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(98)."</legend>";$Fb=($Zb?"":"~ ").$Ac;echo
checkbox("all",1,0,($Ac!==false?($Zb?"":"~ ").lang(99,$Ac):""),"var checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$Fb' : checked); selectCount('selected2', this.checked || !checked ? '$Fb' : checked);")."\n","</fieldset>\n";if($b->selectCommandPrint()){echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>',lang(89),'</legend><div>
<input type="submit" value="',lang(14),'"',($_GET["modify"]?'':' title="'.lang(85).'"'),'>
</div></fieldset>
<fieldset><legend>',lang(100),' <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="',lang(10),'">
<input type="submit" name="clone" value="',lang(101),'">
<input type="submit" name="delete" value="',lang(18),'">',confirm(),'</div></fieldset>
';}$_c=$b->dumpFormat();foreach((array)$_GET["columns"]as$e){if($e["fun"]){unset($_c['sql']);break;}}if($_c){print_fieldset("export",lang(102)." <span id='selected2'></span>");$we=$b->dumpOutput();echo($we?html_select("output",$we,$ta["output"])." ":""),html_select("format",$_c,$ta["format"])," <input type='submit' name='export' value='".lang(102)."'>\n","</div></fieldset>\n";}$b->selectEmailPrint(array_filter($Rb,'strlen'),$f);}echo"</div></div>\n";if($b->selectImportPrint()){echo"<div>","<a href='#import'>".lang(103)."</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"<span id='import' class='hidden'>: ","<input type='file' name='csv_file'> ",html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$ta["format"],1);echo" <input type='submit' name='import' value='".lang(103)."'>","</span>","</div>";}echo"<input type='hidden' name='token' value='$pg'>\n","</form>\n",(!$Fc&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["script"])){if($_GET["script"]=="kill")$h->query("KILL ".number($_POST["kill"]));elseif(list($R,$t,$B)=$b->_foreignColumn(column_foreign_keys($_GET["source"]),$_GET["field"])){$z=11;$I=$h->query("SELECT $t, $B FROM ".table($R)." WHERE ".(preg_match('~^[0-9]+$~',$_GET["value"])?"$t = $_GET[value] OR ":"")."$B LIKE ".q("$_GET[value]%")." ORDER BY 2 LIMIT $z");for($s=1;($K=$I->fetch_row())&&$s<$z;$s++)echo"<a href='".h(ME."edit=".urlencode($R)."&where".urlencode("[".bracket_escape(idf_unescape($t))."]")."=".urlencode($K[0]))."'>".h($K[1])."</a><br>\n";if($K)echo"...\n";}exit;}else{page_header(lang(62),"",false);if($b->homepage()){echo"<form action='' method='post'>\n","<p>".lang(104).": <input type='search' name='query' value='".h($_POST["query"])."'> <input type='submit' value='".lang(44)."'>\n";if($_POST["query"]!="")search_tables();echo"<div class='scrollable'>\n","<table cellspacing='0' class='nowrap checkable'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^tables\[/);",""),'<th>'.lang(105),'<td>'.lang(106),"</thead>\n";foreach(table_status()as$R=>$K){$B=$b->tableName($K);if(isset($K["Engine"])&&$B!=""){echo'<tr'.odd().'><td>'.checkbox("tables[]",$R,in_array($R,(array)$_POST["tables"],true)),"<th><a href='".h(ME).'select='.urlencode($R)."'>$B</a>";$X=format_number($K["Rows"]);echo"<td align='right'><a href='".h(ME."edit=").urlencode($R)."'>".($K["Engine"]=="InnoDB"&&$X?"~ $X":$X)."</a>";}}echo"</table>\n","</div>\n","</form>\n",script("tableCheck();");}}page_footer();