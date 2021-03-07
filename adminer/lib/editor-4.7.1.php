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
script_src($Kg){return"<script src='".hTrim($Kg)."'".nonce()."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
hTrim($Q){return
str_replace("\0","&#0;",htmlspecialchars($Q,ENT_QUOTES,'utf-8'));}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($B,$Y,$Wa,$vd="",$me="",$ab="",$wd=""){$J="<input type='checkbox' name='$B' value='".hTrim($Y)."'".($Wa?" checked":"").($wd?" aria-labelledby='$wd'":"").">".($me?script("qsl('input').onclick = function () { $me };",""):"");return($vd!=""||$ab?"<label".($ab?" class='$ab'":"").">$J".hTrim($vd)."</label>":$J);}function
optionlist($C,$tf=null,$Ng=false){$J="";foreach($C
as$pd=>$W){$re=array($pd=>$W);if(is_array($W)){$J.='<optgroup label="'.hTrim($pd).'">';$re=$W;}foreach($re
                                                                                                       as $y=> $X)$J.='<option'.($Ng||is_string($y)?' value="'.hTrim($y).'"':'').(($Ng||is_string($y)?(string)$y:$X)===$tf?' selected':'').'>'.hTrim($X);if(is_array($W))$J.='</optgroup>';}return$J;}function
html_select($B,$C,$Y="",$le=true,$wd=""){if($le)return"<select name='".hTrim($B)."'".($wd?" aria-labelledby='$wd'":"").">".optionlist($C,$Y)."</select>".(is_string($le)?script("qsl('select').onchange = function () { $le };",""):"");$J="";foreach($C
                                                                                                                                                                                                                                                      as $y=> $X)$J.="<label><input type='radio' name='".hTrim($B)."' value='".hTrim($y)."'".($y==$Y?" checked":"").">".hTrim($X)."</label>";return$J;}function
select_input($Da,$C,$Y="",$le="",$Ge=""){$ag=($C?"select":"input");return"<$ag$Da".($C?"><option value=''>$Ge".optionlist($C,$Y,true)."</select>":" size='10' value='".hTrim($Y)."' placeholder='$Ge'>").($le?script("qsl('$ag').onchange = $le;",""):"");}function
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
queries_redirect($Fd,$Sd,$Ze){[$Te,$gg]=queries(null);return
query_redirect($Te,$Fd,$Sd,$Ze,false,!$Ze,$gg);}function
format_time($Nf){return
lang(1,max(0,microtime(true)-$Nf));}function
remove_from_uri($ze=""){return
substr(preg_replace("~(?<=[?&])($ze".(SID?"":"|".session_name()).")=[^&]*&~",'',"$_SERVER[REQUEST_URI]&"),0,-1);}function
pagination($D,$ub){return" ".($D==$ub?$D+1:'<a href="'.hTrim(remove_from_uri("page").($D?"&page=$D".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($D+1)."</a>");}function
get_file($y,$yb=false){$mc=$_FILES[$y];if(!$mc)return
null;foreach($mc
as$y=>$X)$mc[$y]=(array)$X;$J='';foreach($mc["error"]as$y=>$o){if($o)return$o;$B=$mc["name"][$y];$ng=$mc["tmp_name"][$y];$nb=file_get_contents($yb&&preg_match('~\.gz$~',$B)?"compress.zlib://$ng":$ng);if($yb){$Nf=substr($nb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Nf,$af))$nb=iconv("utf-16","utf-8",$nb);elseif($Nf=="\xEF\xBB\xBF")$nb=substr($nb,3);$J.=$nb."\n\n";}else$J.=$nb;}return$J;}function
upload_error($o){$Pd=($o==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($o?lang(2).($Pd?" ".lang(3,$Pd):""):lang(4));}function
repeat_pattern($Ee,$Bd){return
str_repeat("$Ee{0,65535}",$Bd/65535)."$Ee{0,".($Bd%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
shorten_utf8($Q,$Bd=80,$Uf=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$Bd).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$Bd).")($)?)",$Q,$A);return
hTrim($A[1]).$Uf.(isset($A[2])?"":"<i>…</i>");}function
format_number($X){return
strtr(number_format($X,0,".",lang(5)),preg_split('~~u',lang(6),-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~[^a-z0-9_]~i','-',$X);}function
hidden_fields($Pe,$Xc=array()){$J=false;while(list($y,$X)=each($Pe)){if(!in_array($y,$Xc)){if(is_array($X)){foreach($X
as$pd=>$W)$Pe[$y."[$pd]"]=$W;}else{$J=true;echo'<input type="hidden" name="'.hTrim($y).'" value="'.hTrim($X).'">';}}}return$J;}function
hidden_fields_get(){echo(sid()?'<input type="hidden" name="'.session_name().'" value="'.hTrim(session_id()).'">':''),(SERVER!==null?'<input type="hidden" name="'.DRIVER.'" value="'.hTrim(SERVER).'">':""),'<input type="hidden" name="username" value="'.hTrim($_GET["username"]).'">';}function
table_status1($R,$ic=false){$J=table_status($R,$ic);return($J?$J:array("Name"=>$R));}function
column_foreign_keys($R){global$b;$J=array();foreach($b->foreignKeys($R)as$yc){foreach($yc["source"]as$X)$J[$X][]=$yc;}return$J;}function
enum_input($U,$Da,$p,$Y,$Sb=null){global$b;preg_match_all("~'((?:[^']|'')*)'~",$p["length"],$Md);$J=($Sb!==null?"<label><input type='$U'$Da value='$Sb'".((is_array($Y)?in_array($Sb,$Y):$Y===0)?" checked":"")."><i>".lang(7)."</i></label>":"");foreach($Md[1]as$s=>$X){$X=stripcslashes(str_replace("''","'",$X));$Wa=(is_int($Y)?$Y==$s+1:(is_array($Y)?in_array($s+1,$Y):$Y===$X));$J.=" <label><input type='$U'$Da value='".($s+1)."'".($Wa?' checked':'').'>'.hTrim($b->editVal($X,$p)).'</label>';}return$J;}function
input($p,$Y,$Dc){global$Ag,$b,$x;$B=hTrim(bracket_escape($p["field"]));echo"<td class='function'>";if(is_array($Y)&&!$Dc){$ya=array($Y);if(version_compare(PHP_VERSION,5.4)>=0)$ya[]=JSON_PRETTY_PRINT;$Y=call_user_func_array('json_encode',$ya);$Dc="json";}$ff=($x=="mssql"&&$p["auto_increment"]);if($ff&&!$_POST["save"])$Dc=null;$Ec=(isset($_GET["select"])||$ff?array("orig"=>lang(8)):array())+$b->editFunctions($p);$Da=" name='fields[$B]'";if($p["type"]=="enum")echo
hTrim($Ec[""])."<td>".$b->editInput($_GET["edit"],$p,$Da,$Y);else{$Lc=(in_array($Dc,$Ec)||isset($Ec[$Dc]));echo(count($Ec)>1?"<select name='function[$B]'>".optionlist($Ec,$Dc===null||$Lc?$Dc:"")."</select>".on_help("getTarget(event).value.replace(/^SQL\$/, '')",1).script("qsl('select').onchange = functionChange;",""):hTrim(reset($Ec))).'<td>';$id=$b->editInput($_GET["edit"],$p,$Da,$Y);if($id!="")echo$id;elseif(preg_match('~bool~',$p["type"]))echo"<input type='hidden'$Da value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$Da value='1'>";elseif($p["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$p["length"],$Md);foreach($Md[1] as $s=> $X){$X=stripcslashes(str_replace("''","'",$X));$Wa=(is_int($Y)?($Y>>$s)&1:in_array($X,explode(",",$Y),true));echo" <label><input type='checkbox' name='fields[$B][$s]' value='".(1<<$s)."'".($Wa?' checked':'').">".hTrim($b->editVal($X,$p)).'</label>';}}elseif(preg_match('~blob|bytea|raw|file~',$p["type"])&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif(($dg=preg_match('~text|lob~',$p["type"]))||preg_match("~\n~",$Y)){if($dg&&$x!="sqlite")$Da.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$Da.=" cols='30' rows='$L'".($L==1?" style='height: 1.2em;'":"");}echo"<textarea$Da>".hTrim($Y).'</textarea>';}elseif($Dc=="json"||preg_match('~^jsonb?$~',$p["type"]))echo"<textarea$Da cols='50' rows='12' class='jush-js'>".hTrim($Y).'</textarea>';else{$Rd=(!preg_match('~int~',$p["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$p["length"],$A)?((preg_match("~binary~",$p["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$p["unsigned"]?1:0)):($Ag[$p["type"]]?$Ag[$p["type"]]+($p["unsigned"]?0:1):0));if($x=='sql'&&min_version(5.6)&&preg_match('~time~',$p["type"]))$Rd+=7;echo"<input".((!$Lc||$Dc==="")&&preg_match('~(?<!o)int(?!er)~',$p["type"])&&!preg_match('~\[\]~',$p["full_type"])?" type='number'":"")." value='".hTrim($Y)."'".($Rd?" data-maxlength='$Rd'":"").(preg_match('~char|binary~',$p["type"])&&$Rd>20?" size='40'":"")."$Da>";}echo$b->editHint($_GET["edit"],$p,$Y);$pc=0;foreach($Ec
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         as $y=> $X){if($y===""||!$X)break;$pc++;}if($pc)echo
script("mixin(qsl('td'), {onchange: partial(skipOriginal, $pc), oninput: function () { this.onchange(); }});");}}function
process_input($p){global$b,$n;$u=bracket_escape($p["field"]);$Dc=$_POST["function"][$u];$Y=$_POST["fields"][$u];if($p["type"]=="enum"){if($Y==-1)return
false;if($Y=="")return"NULL";return+$Y;}if($p["auto_increment"]&&$Y=="")return
null;if($Dc=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$p["on_update"])?idf_escape($p["field"]):false);if($Dc=="NULL")return"NULL";if($p["type"]=="set")return
array_sum((array)$Y);if($Dc=="json"){$Dc="";$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}if(preg_match('~blob|bytea|raw|file~',$p["type"])&&ini_bool("file_uploads")){$mc=get_file("fields-$u");if(!is_string($mc))return
false;return$n->quoteBinary($mc);}return$b->processInput($p,$Y,$Dc);}function
fields_from_edit(){global$n;$J=array();foreach((array)$_POST["field_keys"]as$y=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$y];$_POST["fields"][$X]=$_POST["field_vals"][$y];}}foreach((array)$_POST["fields"]as$y=>$X){$B=bracket_escape($y,1);$J[$B]=array("field"=>$B,"privileges"=>array("insert"=>1,"update"=>1),"null"=>1,"auto_increment"=>($y==$n->primary),);}return$J;}function
search_tables(){global$b,$h;$_GET["where"][0]["val"]=$_POST["query"];$wf="<ul>\n";foreach(table_status('',true)as$R=>$S){$B=$b->tableName($S);if(isset($S["Engine"])&&$B!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$I=$h->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",$b->selectSearchProcess(fields($R),array())),1));if(!$I||$I->fetch_row()){$Ne="<a href='".hTrim(ME."select=".urlencode($R)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$B</a>";echo"$wf<li>".($I?$Ne:"<p class='error'>$Ne: ".error())."\n";$wf="";}}}echo($wf?"<p class='message'>".lang(9):"</ul>")."\n";}function
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
as$pd=>$W)$J.="<tr>".($X!=array_values($X)?"<th>".hTrim($pd):"")."<td>".select_value($W,$_,$p,$eg);return"<table cellspacing='0'>$J</table>";}if(!$_)$_=$b->selectLink($X,$p);if($_===null){if(is_mail($X))$_="mailto:$X";if(is_url($X))$_=$X;}$J=$b->editVal($X,$p);if($J!==null){if(!is_utf8($J))$J="\0";elseif($eg!=""&&is_shortable($p))$J=shorten_utf8($J,max(0,+$eg));else$J=hTrim($J);}return$b->selectVal($J,$_,$p,$X);}function
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
verify_token(){[$pg,$Ve]=explode(":",$_POST["token"]);return($Ve^$_SESSION["token"])==$pg;}function
lzw_decompress($Ma){$Eb=256;$Na=8;$cb=array();$hf=0;$if=0;for($s=0;$s<strlen($Ma);$s++){$hf=($hf<<8)+ord($Ma[$s]);$if+=8;if($if>=$Na){$if-=$Na;$cb[]=$hf>>$if;$hf&=(1<<$if)-1;$Eb++;if($Eb>>$Na)$Na++;}}$Db=range("\0","\xFF");$J="";foreach($cb
as$s=>$bb){$Ob=$Db[$bb];if(!isset($Ob))$Ob=$dh.$dh[0];$J.=$Ob;if($s)$Db[]=$dh.$Ob[0];$dh=$Ob;}return$J;}function
on_help($hb,$Cf=0){return
script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $hb, $Cf) }, onmouseout: helpMouseout});","");}function
edit_form($a,$q,$K,$Ig){global$b,$x,$pg,$o;$Yf=$b->tableName(table_status1($a,true));page_header(($Ig?lang(10):lang(11)),$o,array("select"=>array($a,$Yf)),$Yf);if($K===false)echo"<p class='error'>".lang(12)."\n";echo'<form action="" method="post" enctype="multipart/form-data" id="form">
';if(!$q)echo"<p class='error'>".lang(13)."\n";else{echo"<table cellspacing='0' class='layout'>".script("qsl('table').onkeydown = editingKeydown;");foreach($q
as$B=>$p){echo"<tr><th>".$b->fieldName($p);$zb=$_GET["set"][bracket_escape($B)];if($zb===null){$zb=$p["default"];if($p["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$zb,$af))$zb=$af[1];}$Y=($K!==null?($K[$B]!=""&&$x=="sql"&&preg_match("~enum|set~",$p["type"])?(is_array($K[$B])?array_sum($K[$B]):+$K[$B]):$K[$B]):(!$Ig&&$p["auto_increment"]?"":(isset($_GET["select"])?false:$zb)));if(!$_POST["save"]&&is_string($Y))$Y=$b->editVal($Y,$p);$Dc=($_POST["save"]?(string)$_POST["function"][$B]:($Ig&&preg_match('~^CURRENT_TIMESTAMP~i',$p["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(preg_match("~time~",$p["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$Dc="now";}input($p,$Y,$Dc);echo"\n";}if(!support("table"))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select("field_funs[]",$b->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($q){echo"<input type='submit' value='".lang(14)."'>\n";if(!isset($_GET["select"])){echo"<input type='submit' name='insert' value='".($Ig?lang(15):lang(16))."' title='Ctrl+Shift+Enter'>\n",($Ig?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".lang(17)."…', this); };"):"");}}echo($Ig?"<input type='submit' name='delete' value='".lang(18)."'>".confirm()."\n":($_POST||!$q?"":script("focus(qsa('td', qs('#form'))[1].firstChild);")));if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo'<input type="hidden" name="referer" value="',hTrim(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),'">
<input type="hidden" name="save" value="1">
<input type="hidden" name="token" value="',$pg,'">
</form>
';}if(isset($_GET["file"])){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");if($_GET["file"]=="favicon.ico"){header("Content-Type: image/x-icon");echo
lzw_decompress("\0\0\0` \0�\0\n @\0�C��\"\0`E�Q����?�tvM'�Jd�d\\�b0\0�\"��fӈ��s5����A�XPaJ�0���8�#R�T��z`�#.��c�X��Ȁ?�-\0�Im?�.�M��\0ȯ(̉��/(%�\0");}elseif($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("\n1̇�ٌ�l7��B1�4vb0��fs���n2B�ѱ٘�n:�#(�b.\rDc)��a7E����l�ñ��i1̎s���-4��f�	��i7������Fé�vt2���!�r0���t~�U�'3M��W�B�'c�P�:6T\rc�A�zr_�WK�\r-�VNFS%~�c���&�\\^�r����u�ŎÞ�ً4'7k����Q��h�'g\rFB\ryT7SS�P�1=ǤcI��:�d��m>�S8L�J��t.M���	ϋ`'C����889�� �Q����2�#8А����6m����j��h�<�����9/��:�J�)ʂ�\0d>!\0Z��v�n��o(���k�7��s��>��!�R\"*nS�\0@P\"��(�#[���@g�o���zn�9k�8�n���1�I*��=�n������0�c(�;�à��!���*c��>Ύ�E7D�LJ��1����`�8(��3M��\"�39�?E�e=Ҭ�~������Ӹ7;�C����E\rd!)�a*�5ajo\0�#`�38�\0��]�e���2�	mk��e]���AZs�StZ�Z!)BR�G+�#Jv2(���c�4<�#sB�0���6YL\r�=���[�73��<�:��bx��J=	m_ ���f�l��t��I��H�3�x*���6`t6��%�U�L�eق�<�\0�AQ<P<:�#u/�:T\\>��-�xJ�͍QH\nj�L+j�z��7���`����\nk��'�N�vX>�C-T˩�����4*L�%Cj>7ߨ�ި���`���;y���q�r�3#��} :#n�\r�^�=C�Aܸ�Ǝ�s&8��K&��*0��t�S���=�[��:�\\]�E݌�/O�>^]�ø�<����gZ�V��q����� ��x\\������޺��\"J�\\î��##���D��x6��5x�������\rH�l ����b��r�7��6���j|����ۖ*�FAquvyO��WeM����D.F��:R�\$-����T!�DS`�8D�~��A`(�em�����T@O1@��X��\nLp�P�����m�yf��)	���GSEI���xC(s(a�?\$`tE�n��,�� \$a��U>,�В\$Z�kDm,G\0��\\��i��%ʹ� n��������g���b	y`��Ԇ�W� 䗗�_C��T\ni��H%�da��i�7�At�,��J�X4n����0o͹�9g\nzm�M%`�'I���О-���7:p�3p��Q�rED������b2]�PF����>e���3j\n�߰t!�?4f�tK;��\rΞи�!�o�u�?���Ph���0uIC}'~��2�v�Q���8)���7�DI�=��y&��ea�s*hɕjlA�(�\"�\\��m^i��M)��^�	|~�l��#!Y�f81RS����!���62P�C��l&���xd!�|��9�`�_OY�=��G�[E�-eL�CvT� )�@�j-5���pSg�.�G=���ZE��\$\0�цKj�U��\$���G'I�P��~�ځ� ;��hNێG%*�Rj�X[�XPf^��|��T!�*N��І�\rU��^q1V!��Uz,�I|7�7�r,���7���ľB���;�+���ߕ�A�p����^���~ؼW!3P�I8]��v�J��f�q�|,���9W�f`\0�q�A�wE���մ�F����T�QՑG���\$0Ǔʠ#�%By7r�i{e�Q���d���Ǉ �B4;ks(�0ݎ�=�1r)_<���;̹��S��r� &Y�,h,��iiك��b�̢A�� ��G��L��z2p(�������0�����L	��S����E���	<���}_#\\f��daʄ�K�3�Y|V+�l@�0`;���Lh���ޯj'������ƙ�Y�+��QZ-i���yv��I�5ړ0O|�P�]F܏�����\0���2�D9͢���n/χQس&��I^�=�l��qfI��= �]xqGR�F�e�7�)��9*�:B�b�>a�z�-���2.����b{��4#�����Uᓍ�L7-��v/;�5��u���H��&�#���j�`�G�8� �7p���ҠYC��~��:�@��EU�J��;v7v]�J'���q1��El��Іi�����/��{k<��֡M�po�}������ٞ,�dæ�_uӗ���p�u޽�����=���tn���	����~�Lx�����{k��߇���\rj~�P+���0�u�ow�yu\$��߷�\nd��m�Zd��8i`�=��g�<���ۓ��͈*+3j����܏<[�\0���/PͭB��r���`�`�#x�+B?#�܏^;Ob\r����4��\n���0\n����0�\\�0>��P�@���2�l��j�O����(_�<�W\$�g���G�tא@�l.�h�Siƾ��PH�\n�J����LD�");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("f:��gCI��\n8��3)��7���81��x:\nOg#)��r7\n\"��`�|2�gSi�H)N�S��\r��\"0��@�)�`(\$s6O!��V/=��' T4�=��iS��6IO��er�x�9�*ź��n3�\rщv�C��`���2G%�Y�����1��f���Ȃl��1�\ny�*pC\r\$�n�T��3=\\�r9O\"�	��l<�\r�\\��I,�s\nA��eh+M�!�q0��f�`(�N{c��+w���Y��p٧3�3��+I��j�����k��n�q���zi#^r�����3���[��o;��(��6�#�Ґ��\":cz>ߣC2v�CX�<�P��c*5\n���/�P97�|F��c0�����!���!���!��\nZ%�ć#CH�!��r8�\$���,�Rܔ2���^0��@�2��(�88P/��݄�\\�\$La\\�;c�H��HX���\nʃt���8A<�sZ�*�;I��3��@�2<���!A8G<�j�-K�({*\r��a1���N4Tc\"\\�!=1^���M9O�:�;j��\r�X��L#H�7�#Tݪ/-���p�;�B \n�2!���t]apΎ��\0R�C�v�M�I,\r���\0Hv��?kT�4����uٱ�;&���+&���\r�X���bu4ݡi88�2B�/⃖4���N8A�A)52������2��s�8�5���p�WC@�:�t�㾴�e��h\"#8_��cp^��I]OH��:zd�3g�(���Ök��\\6����2�ږ��i��7���]\r�xO�n�p�<��p�Q�U�n��|@���#G3��8bA��6�2�67%#�\\8\r��2�c\r�ݟk��.(�	��-�J;��� ��L�� ���W��㧓ѥɤ����n��ҧ���M��9ZНs]�z����y^[��4-�U\0ta��62^��.`���.C�j�[ᄠ% Q\0`d�M8�����\$O0`4���\n\0a\rA�<�@����\r!�:�BA�9�?h>�Ǻ��~̌�6Ȉh�=�-�A7X��և\\�\r��Q<蚧q�'!XΓ2�T �!�D\r��,K�\"�%�H�qR\r�̠��C =�������<c�\n#<�5�M� �E��y�������o\"�cJKL2�&��eR��W�AΐTw�ё;�J���\\`)5��ޜB�qhT3��R	�'\r+\":�����.��ZM'|�et:3%L��#f!�h�׀e����+ļ�N�	��_�CX��G�1��i-ãz�\$�oK@O@T�=&�0�\$	�DA�����D�SJ�x9ׁFȈml��p�Gխ�T�6Rf�@�a�\rs�R�Fgih]��f�.�7+�<nhh�* �SH	P]� :Ғ��a\"�����2�&R�)�B�Pʙ�H/��f {r|�0^�hCA�0�@�M���2�B�@��z�U���O���Cpp��\\�L�%�𛄒y��odå���p3���7E����A\\���K��Xn��i.�Z�� ���s��G�m^�tI�Y�J��ٱ�G1��R��D��c���6�tMih��9��9g��q�RL��Mj-TQ�6i�G_!�.�h�v��cN�����^��0w@n|���V�ܫ�AЭ��3�[��]�	s7�G�P@ :�1т�b� ��ݟ���w�(i��:��z\\��;���A�PU T^�]9�`UX+U��Q+��b���*ϔs������[�ۉxk�F*�ݧ_w.��6~�b��mK�sI�MK�}�ҥ���eHɲ�d�*md�l�Q��eH�2�ԍL���a҂�=��s�P�aM\"ap��:<��GB�\r2Ytx&L}}��A�ԱN�GЬza��D4�t�4Q�vS�ùS\r�;U��������~�pB��{���,���O��t;�J��ZC,&Y�:Y\"�#�����t:\n�h8r����n���h>��>Z��`&�a�pY+�x�U��A�<?�PxWա�W�	i��.�\r`�\$,���Ҿ��V�]�Zr���H��5�f\\�-KƩ�v��Z��A��(�{3�o��l.��J��.�\\t2�;���2\0��>c+�|��*;-0�n��[�t@�ڕ��=cQ\n.z���wC&��@���F�����'cBS7_*rsѨ�?j�3@����!�.@7�s�]Ӫ�L�΁G��@��_�q���&u���t�\nՎ�L�E�T��}gG����w�o�(*�����A�-�����3�mk�����פ��t��S���(�d��A�~�x\n����k�ϣ:D��+�� g��h14 ��\n.��d꫖������AlY��j���jJ���PN+b� D�j������D��P���LQ`Of��@�}�(���6�^nB�4�`�e��\n��	�trp!�lV�'�}b�*�r%|\nr\r#���@w��-�T.Vv�8��\nmF�/�p��`�Y0�����P\r8�Y\r��ݤ�	�Q���%E�/@]\0��{@�Q���\0bR M\r��'|��%0SDr����f/����b:ܭ�����%߀�3H�x\0�l\0���	��W��%�\n�8\r\0}�D���1d#�x��.�jEoHrǢlb���%t�4�p���%�4���k�z2\r�`�W@�%\rJ�1��X���1�D6!��*��{4<E��k.m�4����\r\n�^i��� �!n��!2\$������(�f������k>����N��5\$���2T�,�LĂ� � Z@��*�`^P�P%5%�t�H�W��on���E#f���<�2@K:�o����Ϧ�-��2\\Wi+f�&��g&�n�L�'e�|����nK�2�rڶ�p�*.�n��������*�+�t�Bg* ��Q�1+)1h���^�`Q#�؎�n*h���v�B��\0\\F\n�W�r f\$�=4\$G4ed�b�:J^!�0��_���%2��6�.F���Һ�EQ�����dts\"�����B(�`�\r���c�R����V����X��:R�*2E*s�\$��+�:bXl��tb��-�S>��-�d�=��\$S�\$�2�ʁ7�j�\"[́\"��]�[6��SE_>�q.\$@z`�;�4�3ʼ�CS�*�[���{DO�ުCJj峚P�:'���ȕ QEӖ�`%r��7��G+hW4E*��#TuFj�\n�e�D�^�s��r.��Rk��z@��@���D�`C�V!C���\0��ۊ)3<��Q4@�3SP��ZB�5F�L�~G�5���:���5\$X���}ƞf���I���3S8�\0XԂtd�<\nbtN� Q�;\r��H��P�\0��&\n���\$V�\r:�\0]V5gV���D`�N1:�SS4Q�4�N��5u�5�`x	�<5_FH���}7��)�SV��Ğ#�|��< ռ�˰���\\��-�z2�\0�#�WJU6kv���#��\r�췐����U��i��_��^�UVJ|Y.��ɛ\0u,�������_UQD#�ZJu�Xt��_�&JO,Du`N\r5��`�}ZQM^m�P�G[��a�b�N䞮��re�\n��%�4��o_(�^�q@Y6t;I\nGSM�3��^SAYH�hB��5�fN?NjWU�J����֯Yֳke\"\\B1�؅0� �en���*<�O`S�L�\n��.g�5Zj�\0R\$�h��n�[�\\���r���,�4����cP�p�q@R�rw>�wCK��t��}5_uvh��`/����\$�J)�R�2Du73�d\r�;��w���H�I_\"4�r�����Ͽ+�&0>�_-eqeD��V��n��f�h��\"Z����Z�W�6\\L���ke&�~������i\$ϰ�Mr�i*�����\0�.Q,��8\r���\$׭K��Y� �io�e%t�2�\0�J��~��/I/.�e��n�~x!�8��|f�h�ۄ-H���&�/��o�����.K� �^j��t��>('L\r��HsK1�e�\0��\$&3�\0�in3� o�6�ж�����9�j������1�(b.�vC�ݎ8���:wi��\"�^w�Q����z�o~�/��Ғ���`Y2��D�V����/k�8��7Z�H����]2k2r���ϯh�=�T��]O&�\0�M\0�[8��Ȯ���8&L�Vm�v���j�ך�F��\\��	���&s��Q� \\\"�b��	��\rBs�Iw�	�Y��N �7�C/*����\n\n�H�[����*A���TE�VP.UZ(tz/}\n2��y�S���,#�3�i�~W@yCC\nKT��1\"@|�zC\$��_CZjzHB�LV�,K����O���P�@X���������;D�WZ�W�a���\0ފ�CG8�R �	�\n������P�A��&������,�pfV|@N�b�\$�[�I����������Z�@Zd\\\"�|��+�ۮ��tz�o\$�\0[����y�E���ə�bhU1��,�r\$�o8D���F��V&ځ5�h}��N�ͳ&�絕ef�ǙY��:�^z�VPu	W�Z\"r�:�h�w��h#1��O���K�hq`妄����v|�˧:wD�j�(W�������碌�?�;|Z��%�%ڡ�r@[����B�&������#���ُ��:)��Y6����&��	@�	���I��!����� ���2M���O;���W��)��C��FZ�p!��a��*F�b�I��;���#Ĥ9����S�/S�A�`z�L*�8�+��N���-�M���-kd���Li�J�·�Jn��b��>,�V�SP�8��>�w��\"E.��Rz`��u_����E\\��ɫ�3P��ӥs]���goVS���\n��	*�\r��7)�ʄ�m�PW�UՀ��ǰ���f��ܓi�ƅkЌ\r�('W`�Bd�/h*�A�l�M��_\n�������O��T�5�&A�2é`��\\R�E\"_�_��.7�M�6d;�<?��)(;���}K�[�����Z?��yI ��1p�bu\0�������{��\ri�s�QQ�Y�2��\rה0\0X�\"@q��uMb��uJ�6�NG���^��wF/t���#P�p��!7������囜!û�^V��M�!(⩀8֝�=�\0�@���80N�Sཾ�Q�_T��ĥ�qSz\"�&h�\0R.\0hZ�fx���F9�Q(�b�=�D&xs=X�bu�@o�w�d�5���P�1P>k��H�D6/ڿ�q랼��3�7TЬK�~54�	�t#�M�\rc�tx�g��T��X\r�2\$�<0�y}*��Cbi�^��L�7	�b�o����x71� b�XS`O���0)���\"�/��=Ȭ �l��Q�p�-�!��{��������a��ȕ9bAg�2,1�zf�k��j�h/o(�.4�\r���Tz&nw���7 X!����@,�<�	��`\"@:��7�CX\\	 \$1H\n=ě�O5��&�v�*(	�tH��#�\n�_X/8�k~+t���O&<v��_Yh��.��Me�Hxp�I�a��0�M\nh�`r'B���h�n8q��!	�֠eu��]^TW����d9{��H,㗂8��L�a�,!\0;��B#�#��`�)�����	ńa�Ee�ڑ�/M�P�	�l���a`	�sⲅ<(D\n���9{06�ƈ;A8��5!	���Z[T� hV���ܻ��U@�n`�V�p��h(Rb4�V�Ɖ����Rp��Ҕ\$����D3O����\$�����aQ��0xb�H`����LÔ8i��oC�����#6�x�)XH�!`�������B�%w���o\nx̀h��H���r� ʼc��mJH�LU����e1l`�(�\$\"�h�J�rv���TP�����1uHA\0��H2@(ʡU�\"�Q�@qg]l\"�%���*�\0W�j[� ���e�4���P��N����5\$H\r��IP��'@:\0�\"#t^�D��0���>�(��h� '��F,sZJ��An�#�h��X��.q��Yob�����2��?j��B�I��ߣ��������0�a�(�`Z�C����r��HSQ��\\��W	��XZ��|�E@���TԝŖq�DD:_y��İ��B�~�xP�--e��_�u�|2(�G,��-rR�Kx���d���hH�A|���w�|P�!ǉґ䎬}�T���<��,1��v�g*���z�^������_pi {��G����	LaJJC�T%N1��I:V@Z��%ɂ*�|@NNxL��L�zd \$8b#�!2=cۍ�QD��@�\0�J�dzp��\$A�|ya4)��s%!�BI�Q]d�G�6&E\$��H\$Rj\0���ܗGi\$إ�9ņY��@ʴ0�6Ħ��X�ܞ1&L��&2�	E^��a8�j�#�DEu�\$uT�*R�#&��P2�e��K��'�E%┡�YW�J��	����O`�ʕ��^l+��`�	R�1u�&F���Z[)]J�Z�E��`��FN.\r�=�� ��\0�O~���M,��FAT�b�h�z0��`-bl�\n�ǅZ�'�*I�n�\$�[�,8D��n��`����I0u�0��EJ鸆Xc�e�2P�� b��]���5:겓�'xT	�'bO�Y��V>&��A�.Pp�ŭ\${)9\"i�c���Ǚ�L� P�K�T��9���0wZ\"b	�)���R��&�ɢ���&�X+���s%[�~&aF��i.:�K�a5@���q���pG��hĺn�0y�H,W>�J�!���&�2Y���lAp����-3�]���2C�MZ����H�o�d�1Dl�uS\"��M�Tz\$�h\\c����w<�cO3?z���p%@\0�4\n�Z�ӗ���f*\r���|�ل;3�M�Rm�� �w�X���.Y�L���]Wg]��\r胜1@U8��e3U����D�	z�'���&��#hu�a1C�0�{ph͔\n?��YK�B���Y��A9�,�F��w�");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress('');}else{header("Content-Type: image/gif");switch($_GET["file"]){case"plus.gif":echo'';break;case"cross.gif":echo'';break;case"up.gif":echo'';break;case"down.gif":echo'';break;case"arrow.gif":echo'';break;}}exit;}if($_GET["script"]=="version"){$Bc=file_open_lock(get_temp_dir()."/adminer.version");if($Bc)file_write_unlock($Bc,serialize(array("signature"=>$_POST["signature"],"version"=>$_POST["version"])));exit;}global$b,$h,$n,$Hb,$Mb,$Ub,$o,$Ec,$Ic,$aa,$hd,$x,$ba,$yd,$ke,$Fe,$Rf,$Mc,$pg,$tg,$Ag,$Hg,$ca;if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if($_SERVER["HTTP_X_FORWARDED_PREFIX"])$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];$aa=($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure");@ini_set("session.use_trans_sid",false);if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");$E=array(0,preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),"",$aa);if(version_compare(PHP_VERSION,'5.2.0')>=0)$E[]=true;call_user_func_array('session_set_cookie_params',$E);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$oc);if(get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("zend.ze1_compatibility_mode",false);@ini_set("precision",15);$yd=array('en'=>'English','ar'=>'العربية','bg'=>'Български','bn'=>'বাংলা','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','el'=>'Ελληνικά','es'=>'Español','et'=>'Eesti','fa'=>'فارسی','fi'=>'Suomi','fr'=>'Français','gl'=>'Galego','he'=>'עברית','hu'=>'Magyar','id'=>'Bahasa Indonesia','it'=>'Italiano','ja'=>'日本語','ka'=>'ქართული','ko'=>'한국어','lt'=>'Lietuvių','ms'=>'Bahasa Melayu','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'Português','pt-br'=>'Português (Brazil)','ro'=>'Limba Română','ru'=>'Русский','sk'=>'Slovenčina','sl'=>'Slovenski','sr'=>'Српски','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','tr'=>'Türkçe','uk'=>'Українська','vi'=>'Tiếng Việt','zh'=>'简体中文','zh-tw'=>'繁體中文',);function
get_lang(){global$ba;return$ba;}function
lang($u,$ge=null){if(is_string($u)){$Ie=array_search($u,get_translations("en"));if($Ie!==false)$u=$Ie;}global$ba,$tg;$sg=($tg[$u]?$tg[$u]:$u);if(is_array($sg)){$Ie=($ge==1?0:($ba=='cs'||$ba=='sk'?($ge&&$ge<5?1:2):($ba=='fr'?(!$ge?0:1):($ba=='pl'?($ge%10>1&&$ge%10<5&&$ge/10%10!=1?1:2):($ba=='sl'?($ge%100==1?0:($ge%100==2?1:($ge%100==3||$ge%100==4?2:3))):($ba=='lt'?($ge%10==1&&$ge%100!=11?0:($ge%10>1&&$ge/10%10!=1?1:2)):($ba=='bs'||$ba=='ru'||$ba=='sr'||$ba=='uk'?($ge%10==1&&$ge%100!=11?0:($ge%10>1&&$ge%10<5&&$ge/10%10!=1?1:2)):1)))))));$sg=$sg[$Ie];}$ya=func_get_args();array_shift($ya);$_c=str_replace("%d","%s",$sg);if($_c!=$sg)$ya[0]=format_number($ge);return
vsprintf($_c,$ya);}function
switch_lang(){global$ba,$yd;echo"<form action='' method='post'>\n<div id='lang'>",lang(19).": ".html_select("lang",$yd,$ba,"this.form.submit();")," <input type='submit' value='".lang(20)."' class='hidden'>\n","<input type='hidden' name='token' value='".get_token()."'>\n";echo"</div>\n</form>\n";}if(isset($_POST["lang"])&&verify_token()){cookie("adminer_lang",$_POST["lang"]);$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=array();redirect(remove_from_uri());}$ba="en";if(isset($yd[$_COOKIE["adminer_lang"]])){cookie("adminer_lang",$_COOKIE["adminer_lang"]);$ba=$_COOKIE["adminer_lang"];}elseif(isset($yd[$_SESSION["lang"]]))$ba=$_SESSION["lang"];else{$qa=array();preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$Md,PREG_SET_ORDER);foreach($Md
as$A)$qa[$A[1]]=(isset($A[3])?$A[3]:1);arsort($qa);foreach($qa
as$y=>$Se){if(isset($yd[$y])){$ba=$y;break;}$y=preg_replace('~-.*~','',$y);if(!isset($qa[$y])&&isset($yd[$y])){$ba=$y;break;}}}$tg=$_SESSION["translations"];if($_SESSION["translations_version"]!=633854138){$tg=array();$_SESSION["translations_version"]=633854138;}function
get_translations($xd){switch($xd){case"en":$g="A9D�y�@s:�G�(�ff�����	��:�S���a2\"1�..L'�I��m�#�s,�K��OP#I�@%9��i4�o2ύ���,9�%�P�b2��a��r\n2�NC�(�r4��1C`(�:Eb�9A�i:�&㙔�y��F��Y��\r�\n� 8Z�S=\$A����`�=�܌���0�\n��dF�	��n:Zΰ)��Q���mw����O��mfpQ�΂��q��a�į�#q��w7S�X3������o�\n>Z�M�zi��s;�̒��_�:���#|@�46��:�\r-z|�(j*���0�:-h��/̸�8)+r^1/Л�η,�ZӈKX�9,�p�:>#���(�6�qC���I�|��Ȣ,�(y �,	%b{�K���)B����P޵\rҪ�6��2��K�p�;��\$#�΁!,�7�#�2��A b�����,N1�\0S�<���=�RZ�#b��(�%&��L�����2���Б�a	�r4�9)��1OAH�<�N)�Y\$���W���%�\$	К&�B��cͬ<�������[K)���\r�������3\r�[G@�Lh�-�*�*\r���7(��:�c�9�è�L��X��	�Y�+Z~����;^_�!���J���롈M.�a��ë:�/c��v�\"�)̸�5��pAV���\0�,�N��2����`�@�ź���?.@ ��b���� �\n��Ѐ���D4T���x﹅����8_#�:)I��xDo���|�`p+���J2ah���Xv �%J�*�i����y���mV�:mۆ�n�v�9o[�#�!�	+/U�G��7�,���M/l�0���i�S��*l9�O���C\r%��6����9F��33����i�-�_+�� C�\0cri�4�3`]�sqŸ��#���I�/��\0�Z�H��\nI\$Lȓ\"P�y�|g5�\$e �A����bLɩ(f,�4��l (l0ςF�se/�\\d��\n\$4�G�Z[b�3�1���Q,%�����8���70�P��p�O�{&��\n�c�Z��H��B]�	�WM��M�Q\$�y���d��c#ǎB���eZ��V�\n��!�g�H���(K�B~Q���x��[	%9������!�1���h�vH�\$�M�v~Ba\$AFL��`�a�,O\\�H�f���~�Ft��|�O!�Ep�M�k7�*�#��r���f�ZW&�׼�T�VӁ��isU��,+�O��?��CB��(���\$l�68Z^@i��� �pK�S�bAT*`Z�.�4�+�'��%�ʀR�	A\$�b3N	��&�L�}\\0���SFh�\nláÆ� l���r�O�Le��<���d� ��ͤ�:1�aKD���c�T��\n\n�7��B*l0F���Y8��5A���!�z��A(�Zb]E.o|�U\no^�A~_�=R2�(�Z�Vd�k8��!�\0�";break;case"ar":$g="�C�P���l*�\r�,&\n�A���(J.��0Se\\�\r��b�@�0�,\nQ,l)���µ���A��j_1�C�M��e��S�\ng@�Og���X�DM�)��0��cA��n8�e*y#au4�� �Ir*;rS�U�dJ	}���*z�U�@��X;ai1l(n������[�y�d�u'c(��oF����e3�Nb���p2N�S��ӳ:LZ�z�P�\\b�u�.�[�Q`u	!��Jy��&2��(gT��SњM�x�5g5�K�K�¦����0ʀ(�7\rm8�7(�9\r�f\"7N�9�� ��4�x荶��x�;�#\"�������2ɰW\"J\nB��'hk�ūb�Di�\\@���p���yf���9����V�?�TXW���F��{�3)\"�W9�|��eRhU��Ҫ�1��P�>���\"o{�\"7�^��pL\n7OM*�O��<7cp�4��Rfl�N��SJ���Dŋ�#�����Jr� �>�J��Hsޜ:����ð�UlK���,n�R�*h������Ȓ,2 �B�����d4�PH�� gj�)���kR<�J�\"��ɣ\r/h�P�&�ӨRؑ3��ŗK!T��RN�����'ȍ�YI����x:�[I�l~�!U9H>�}�=�̜��n2�)vF<�1��Qa@�	�ht)�`P�5�h��ct0����[_�z?rb\0P�:\r�S<�#�J7��0���4V�J��T�U��X��@P�7�h�7!\0�E���c0�6`�3�C�X�[H�3�/PA��@����P9�*zN��A\0�)�B2�#�*���uL���a�*�����dLT�Z	+��3�V��@�v2�Ư�g;�4Pf3Oí���Í�6�1ѴX������0z\r��8a�^��(\\0�z���x�7�\0:Q��D~M��3��x���� %�䝆��*�����]zX/J}V;u^���&a5���jP� K!C�\0�ӑ�z�콷����|��󾗚�Ó�}�D>��Ax\")��\$���k�k������AM�oY�'��iCJ@͙���S��>:�ʒ���Lh�E����L h4��1�E��n��҇��IY�3A�0ۛ�rn�ټ H�oM�r#a�\$�\0�\n\"Pi!�9��l�K�n*���\"u����7k@Ԇ����i�0d5�J'2�e1d�(!�כ_ٴ5���`VBh��ߡT/&۰w�=����WN^Q�E��ɴ��(,7���oph�ŀ�L h}ǃ|�DO\r!���\$M(c/��v(�Hs�+b�BBD�B� ���s�rԊ�sҌ�#5^I�4O��\$���g�\0d\r+ ӡt ���6m88�Sd��2\r�&=IځvBFf��b��laVD6l\0�¡\"3U�,CFԊ�YE�H�\"�A	�8S�����g+yϫ+\$l\"�[%�R'^ZD�P��]o-�a҅\0��(�H۷g��b�`�)��4���`�,E0pq\r�:qN��4�)���l,	��XlX�5:~��(6�aZ�J���&���H��h��M6�XP���p��!�@�]A\r!�5�D�ô���\n'� ��LY&a�׌����\n�P#�p�ޅ*=v��)��n�� ��[ײ:�9#sE�z׬PLA6�^75�i�%I!�#�̖��\$Wt��� GU**�DB����M\\��	��Æ��Ph\n�o���B�Jg\n[.j�̧G���\\xq8ߢ�	!���:̄���vXX�(��Q�k��F\"�ׅ^`\n�&�'5D��M�Ķɀ� ";break;case"bg":$g="�P�\r�E�@4�!Awh�Z(&��~\n��fa��N�`���D��4���\"�]4\r;Ae2��a�������.a���rp��@ד�|.W.X4��FP�����\$�hR�s���}@�Зp�Д�B�4�sE�΢7f�&E�,��i�X\nFC1��l7c��MEo)_G����_<�Gӭ}���,k놊qPX�}F�+9���7i��Z贚i�Q��_a���Z��*�n^���S��9���Y�V��~�]�X\\R�6���}�j�}	�l�4�v��=��3	�\0�@D|�¤���[�����^]#�s.�3d\0*��X�7��p@2�C��9(� �:#�9��\0�7���A����8\\z8Fc�������m X���4�;��r�'HS���2�6A>�¦�6��5	�ܸ�kJ��&�j�\"K������9�{.��-�^�:�*U?�+*>S�3z>J&SK�&���hR����&�:��ɒ>I�J���L�H�H�����Eq8�ZV��s[����2�Ø�7ث��έj��/�h�C��?CմKT�Q�	�k�hL�X7&�\n��=��p�K*�i�Y-���U�D02!�R҉�!-�E_��>�#�H�� g����D�	\"�x�\$ҩS����:ںw����8�J��n��6����Ж@\"��h�4���<��K�kB9�i3Y�l��/��'�%�����J��(2�+n��v�َ%��\\�4����^b���hR8th(��怔 P��������\0��9���J�s��c��D6���'�̼���eb���iJ�������!��T��n�=�8	�j�K�>h�n�!�F�����������8A�4�F����N�i�Z�u��eCv�:��0'x��姃�xx+��x�'S�y����S�*���.�L��\\�I��!��&��h�j�|�%��;Z:\n�蹄:n��M�A����5hX�AF�^�;�\$�`�@�Q\n:��:�`��\0A��4��P���X��\0xA\0hA�3�D�t��^��.0���\\��8/X������p/@�2��z��/ �e���lk	`ސ�`�a^�O������\n�̓rx��[�M���'8�NP[�6d�Dx��lO�1N*�x���>E��1F����,h�E�:�U�q�o-hL��f?D�G�S�t�{�P�m<��JB���]��Jѣ��b�\$�-��j��\r�pTە3�U�A�P����@ ��6\0čC�(�a�6�U�C2�H�1�4r�0u���7�x{<�Ht\r\0��#Ug?A\0c�q7Fp���8g=0�iЙ]S04H\\�9t�2���s���-â@P��=����i\0(-����s@yZ;]A\rdC���o�9���(g��M!#�d��z=��0;���&T�j)|�-���PQ1v�蔦xZB5�� 9�J(}��8T��R8r_��4��D��g��� �a�eIF���O�)�B浯i �Z��@E]͐Ԗ]DzO0i�մx([i	��AW,�A�6���'ĥWy�+���C��*�	�5\nz\\@�҅��{[.(�T2��j�(*�2���t�uέ��S^CI\n�z���V�x/l�xid��R|��HNo[�8\$a Ѿi��q %R�|(�yR�Gt�Ih�֛%�_l \naD&r�Zɡ���#Jt�'�%�-)MWt�N�_r�t�9��4��_�H�k}�[�� �\$͑aZ^QP}3dǓ�ͪ��Pms�vǉ��~��z�,�K�4�K7I�=����Q�9��Vɗf\n�,������C`+/�L����.�^(E:�&��0�=�kJ��a�ى�<�4��H���T��@9��YK4(�@�E��R�4P�W�q{�W�l��;�k=�u�����I�zi��2�,�K�y�M-q�����|�����|L!��j<��آk����}��'���\"�G�g�E�p�^�\r�8��~��T��Ŷd�r��\rq�\$��=�ʅ\n���9��T�͛h�z�&�T��ih�t5�<�we���|M�O��*B�\"25�ҵ�6bLQ��	�¯s���h]�:�J�E�@�/6T鶮���q�2�";break;case"bn":$g="�S)\nt]\0_� 	XD)L��@�4l5���BQp�� 9��\n��\0��,��h�SE�0�b�a%�. �H�\0��.b��2n��D�e*�D��M���,OJÐ��v����х\$:IK��g5U4�L�	Nd!u>�&������a\\�@'Jx��S���4�P�D�����z�.S��E<�OS���kb�O�af�hb�\0�B���r��)����Q��W��E�{K��PP~�9\\��l*�_W	��7��ɼ� 4N�Q�� 8�'cI��g2��O9��d0�<�CA��:#ܺ�%3��5�!n�nJ�mk����,q���@ᭋ�(n+L�9�x���k�I��2�L\0I��#Vܦ�#`�������B��4��:�� �,X���2����,(_)��7*�\n�p���p@2�C��9.�#�\0�#��2\r��7���8M���:�c��2@�L�� �S6�\\4�Gʂ\0�/n:&�.Ht��ļ/��0��2�TgPEt̥L�,L5H����L��G��j�%���R�t����-I�04=XK�\$Gf�Jz��R\$�a`(�����+b0�Ȉ�@/r��M�X�v����N����7cH�~Q(L�\$��>��(]x�W�}�YT���W5b�o�H�*|NKՅDJ���3 !��CmG��h�e4��5�Z@�c%=k�HK�C�-��9r/��A l����m��N)�\"�J:k^H�[q��#�\n���	ۑJW7D]�v�c������\0E�K	��r�Y)�-d֐��љ��4S�BVa����g�r��pKPP�dtN_�1���8�2�o�J5hRg��Ss�bUϔ�����G+�&YM���s����\$�\$	К&�B���p�cW�5�~�K�MѺh;�mGǻ8�:@S��#��7��0�&�J��Ҳ�Hǝ\0%����Ш�8m!�<�\0��cg�9�`�\0l\r�&0X|Ô\r!�0��A	��mI�����\nTI[T\"�D`@R�\rE��zSK2�R����Tf��/\n��\nhV��8�tED@��nx�,�C��f^!�~��@C\$*\rɲ�5��C\"l\0�0����\"\r�:\0��x/����o�2��^��t_�8� ~R`g��0���m��e6�Eڼ4T�HqVB��<GZQ����ٔ�g�AJ\$@���~���P�93��K�Q�:���c�}��A�y!�\\�R.F���#W�������C��\r�4:I�:���?���I�CY�\r*	6@����#-�n\\��3%e�EȠ�b�й��hʤ�l�d^<��1�%���������gA�3L����j&�B�h<��0ΰ@�T�\r!�65t�:���F,�������6gP(�������LĲ��؁jjנ��'z)�!xci�a?TYNk�5��2|�9�=g���Vt��t>G�=�ډ��~Q&y�Ă����0��.\0�4��9��A�8N����\r��L �T= ?��4O��� ����0��O�Eu(���A]	Zɭ\"ThCv*�a!�Eԑ\0�L	���W2�!Ά-��t��.t-�1� T�\\�C���\nU���P���>N� ���d���t�?����|S�fM��3͈�hSPc�Iڜ�E|J�J%��AL/\0P	�L*�<^�\\�Ų� TCP�15&��!��B_-�Q�(��@�U�c8�̢��a�R���K������Ӥ7�`AC�L(��@�� =��#J���i�\n��Ha�N)��Ƃ͌1!�@�<h���=��W���@3�5(�=K*�L��o�(�S-	wt3AζGE)]�h1h�6S(��.	�uz_>���T�\r�Z#N���2�5o�Y�WX^0\no�6�\$Ck~:�j����񞁤3@��]B6a\r��>�8LB�F��yF���K��R�K-��5�h՝Ͱ�B4���:V��S��V�@�n������j3�|_-����)��ܳ\\ܢu-��T*����42�ݧ�j��mZY���Q����I��'�I��Ow�]�І��:�Dk&(����V�U�`L��t\n�ƪ��̪΃joN��ui\r&X�Iff�,��rH����o.2�.�J����^/���7x�d�Ou�ܤ¥c���Q�*</���7�(�7X�.*^��,-_��3o���z��Ě�}�ы�HҚ��\0";break;case"bs":$g="D0�\r����e��L�S���?	E�34S6MƨA��t7��p�tp@u9���x�N0���V\"d7����dp���؈�L�A�H�a)̅.�RL��	�p7���L�X\nFC1��l7AG���n7���(U�l�����b��eēѴ�>4����)�y��FY��\n,�΢A�f �-�����e3�Nw�|��H�\r�]�ŧ��43�X�ݣw��A!�D��6e�o7�Y>9���q�\$���iM�pV�tb�q\$�٤�\n%���LIT�k���)�乪��0�h���4	\n\n:�\n��:4P �;�c\"\\&��H�\ro�4����x��@��,�\nl�E��j�+)��\n���C�r�5����ү/�~����;.�����j�&�f)|0�B8�7����,����ŭZ��'��ģ���8�9�#|�	���*�f!\"�81��9��l:���br���P�/��P���J3F53���7��,UF��8Ę��MBTcR�STU%9,#�R���\\u�b�Q�j�3�L֌�\"9G.nbc,��p�,#X���\"���\"(�F�J�	�\"_%���%��(\r�J�\"1<:ŉ]��[�Z���+�]VF���^��C�lڰ�#�-�S�w���D)6~��P�0�B@�	�ht)�`P�\r�h\\-�9h�.�cծ�F�BF\r��0�'��2�7/�f9\\53I\r�hڍ)<�:�cT9�è؁\r�:�9���娌6��u;7�8P9�)p�2�ҳ��b��#C�5�Gߌ;)_k�v˘�:�ªR2�*4ML2��:��|Lܔ(�8@ ��[���s������42c0z\r��8a�^���]�tC�\\���{��	��;�}���x�!�\\+7r\n�Z�=\rh��K�8GNc\"lR��#�'\n������T�u�c����r;�}\n�]�rx/2���@\")��\$���i�`n�=���1�\r<��T J��O*�I�6�Ck]��B���\rfm�=t�G�!�㟰�i�ـ4G���NC1M�%��v���c^�Ȁ4��Bٌ����]Ù./E��£Bs�#����b��tU\n (tL��a%@��\\ӱV��v\r3Ji�J�.a�՛D��,�Q�7G�/��'D�)(���q%0�ퟳe)C�m}/�9\"p�l�U0��ت�c��a%u^���s����ѧ���4v�d����V	�`���zw�\"k�'����xJ'.A��Nl�q�!��!\0��۠w�Z`2X�b��\r�^2�{� P	�L*3X��mD\$x��\"��a|x8�\r#\"l��BL�E��7���Q�iԫw�Tϡ�E��ͬ���	cg���\0�Bc�4�d4��#�D�i!@�'��Mܩ�I�Hz<鈟��<�*��pG>��z���4���uS���V!�V!���&Y��	g���,�[��\$�^���2��lot�\"�AF��B֨�c��\0PF�pl�!��¨T��&�K8.T��`�v��z'~���i1UX�U����v2���*(���0ʆͱ+�����\rP�ﴚ8blm[1'�A�K^᥼�D�ͩ�2�L��+�(��2��R�4�(ЫyQPa\r�yf��/F��\nil�]�rG׍����eV�2<�h��Ja[\r��'a��a5ί��.������FZz�Z�d#(��PA��@����";break;case"ca":$g="E9�j���e3�NC�P�\\33A�D�i��s9�LF�(��d5M�C	�@e6Ɠ���r����d�`g�I�hp��L�9��Q*�K��5L� ��S,�W-��\r��<�e4�&\"�P�b2��a��r\n1e��y��g4��&�Q:�h4�\rC�� �M���Xa����+�����\\>R��LK&��v������3��é�pt��0Y\$l�1\"P� ���d��\$�Ě`o9>U��^y�==��\n)�n�+Oo���M|���*��u���Nr9]x��{d���3j�P(��c��2&\"�:���:��\0��\r�rh�(��8����p�\r#{\$�j����#Ri�*��h����B��8B�D�J4��h��n{��K� !/28,\$�� #��@�:.�j0��`@����ʨ��4����U�P�&��b�\$� �#.�P�L�<�H�4�cJhŠ�2a�o\$4ҍZ�0����˴�@ʡ9�(�C�p��EU1���^u�cA%�(�20؃��zR6\r�x�	㒌�&FZ�S��FҔ�9k�6���\r�0e�e� P����qu\$	9�B(��2�N�;W�V�k�)q��sQ�p}0o��G_�>pH59\\�<蒲@�	�ht)�`P�2�h��c\\0���y�Pu&��\0Ѵ�*:7��4;N�){]\0�Nz������\n�z��\r��4���\$31A��2P���#8¼�ϵZ�\r���aJc��n�@!�b�����;���w��(�2�6�R;���T�yL�l����Z�\$У�#&ؗ�:b2�\$��h42�0z\r��8a�^���\\��i��3���_\0�OdT�A���;��^0�ɊgH��f�+���9?���m4gӺ�	��p�Ah�g{>���uL[��=�k��#�v��/����炉��r������(���y�A3#t�8tja���d���#G�,:P�a	0%\09�PACb~HOR'@��a`�ts^JC��p�*p��ZkDa�,p��!�,\r\"`�P�ٔY簽�Draӄ	z�\\��a��#+��n�P��\r�!'�b\n\n\n�)@ԋ(�|��=��3�3VkMy~f���B\n�J�o@��d�w�\"��Q�u�7�o\r���8��4��h7R�7\$�z��\0001�2l݊�71T¡C~x�4A��rRV�T�'B���faH�-\n(1�EHIa1	\$D<�H���\"�\r�d݂)	�	*?39лب݃�?��H �b�8���(�2�>���@���.I��S��Ѳ+��j�F��+�a���TJ�K�[\\��)�aO��Y\$|�*���Q	� ��0T�*qS���f(f�jD9�a0�5�Q�uE5Ќ�#�a!�jz��&h��IU�q�����0IY�b3�F��5����OVIt�ȑ7t�\\�Q����Œ�\n�m�j0��ՒLB4�&�Q���T��&�c�%[:\$VF�UZg��`7�.`�j}ik]�4mZG��H:�f���Cb�Es,������a�f�5���ԇ�M;K 0�1N�nm#SD����I��@�� �K,T�6�@&^{@w�Z�p=�{\$�\"Ü��U��i�E�2��S�+T�B�7�A,��C�?Q6��\0��J+7M-x�[�x�VE�S�Ahh+�h:\0";break;case"cs":$g="O8�'c!�~\n��fa�N2�\r�C2i6�Q��h90�'Hi��b7����i��i6ȍ���A;͆Y��@v2�\r&�y�Hs�JGQ�8%9��e:L�:e2���Zt�@\nFC1��l7AP��4T�ت�;j\nb�dWeH��a1M��̬���N���e���^/J��-{�J�p�lP���D��le2b��c��u:F���\r��bʻ�P��77��LDn�[?j1F��7�����I61T7r���{�F�E3i����Ǔ^0�b�b���p@c4{�2�&�\0���r\"��JZ�\r(挥b�䢦�k�:�CP�)�z�=\n �1�c(�*\n��99*�^����:4���2��Y����a����8 Q�F&�X�?�|\$߸�\n!\r)���<i��R�B8�7��x�4Ƃ��5���/j�P�'#dά��p���0��c+�0���Զ#�j�F�\$AH�(\"�H��#�z9Ƃ���;���F����.�sV�M�Ȅ�\0�0��HKT�p��WV`蹁C�7�P�pHXA��G�@�2DI��;O(��@Bb`Ȉ#\\f���\"��*0	�`暍�m\rF-@��1we�7�7%�t�b�6��\r�%R2�#\n07��<߷��U�N\n�0�M��_�^\0�b8%���\\.b�8�	�ht)�`P�\r�p�9f���n[λ�M���3�0̡@J��K���;H�7�Z�;A\0�]��\$5����~���!O��`@=k��>�\\6��#l��6�N��'ڝ�8:η�k��CP�ɬ���]��^�m����:��.����^���[/�qV�ƳH���8֞�)�pAr���w3��H��S���%w/5��ɼ14�z4;8�)�?��	���(ܦ�\0�2m�����@2���D4����x���֟����H��c�^2\r���_�/@�M�p�\r\"���|GN����54`椟�?G��O���Tr�;I�J<d +P�iq(5ӔRH���r�%#A�O� |( 9>G��Ps}���?\$����I��A��\0��>	!�8���Z��9�\"�&S\n�b02*��0F�12�Z'Be�iK�Rk/d1A\"HN	Ҡ\$�l�5TVd&(0�\0@0��4�h�!(@��!�\\5:���!	'1��!��\rA�����Pxz;IYv�B �_�[���tx��\0d@���@@P�U��-\$AAP(5F� Ԩ��Ddg������ĥ[��\$��,x'j���P�d|\"�f�N�i(�Z�x�e	��c��u�ѱO��Ga�\"�5(�8�S�%l)�\"\$qa�%�p���ĨI�ܝWd��y�dp��t\$��yU!��'D�z� h���\$�P���~d	��Fk�1F\$3E�=@�1м�+�ȓ�`�P	�L*(_��WS鞫����)\r#AM]��:�C0i����`a�k�4��PQ۩\naD&RNU����	��EGho���#I�oɂ6(ޱ�28�AKA����Ȫ�ٿg�=0���Ᏻ�Л��C	BS@�Y�LJ��]\$���OC\rYB4F���J(S�1b�-~�U�h�+U7�V�.u�4�r��=`IXC��6��Pz�tV�B�ث�G����p��q�D��;��M��\$y��Y�����U\n�����������K��S�A�ɰ�+w���b�F*a<4n�c����2(�H̒�+t�3�\0PZ�EJK+�c�^���c\"�������#��KxoG�M\n�r&��D Y:�?P���;dڮ���H�9�\nw��*zlN˰�A��\"�Y�DJ��T�(���}3\n͈�����OOt@�\0��F\\B�HE=(��Ȫ�X��mT	�AP�ct�1D��W�%Sz���";break;case"da":$g="E9�Q��k5�NC�P�\\33AAD����eA�\"���o0�#cI�\\\n&�Mpci�� :IM���Js:0�#���s�B�S�\nNF��M�,��8�P�FY8�0��cA��n8����h(�r4��&�	�I7�S	�|l�I�FS%�o7l51�r������(�6�n7���13�/�)��@a:0��\n��]���t��e�����8��g:`�	���h���B\r�g�Л����)�0�3��h\n!��pQT�k7���WX�'\"h.��e9�<:�t�=�3��ȓ�.�@;)CbҜ)�X��bD��MB���*ZH��	8�:'����;M��<����9��\r�#j������EBp�:Ѡ�欑���)��+<!#\n#���C(��0�(��b���K�|�-n�߭��܉���	�*��S\"���\n>�Lbp�ж�2�2�!,�?&��5 R.5A l���@ P��;�@쳎k#4��m��+\r�K\$2C\$��� ���k\"��B0�D��2|\n����Κ��Jef�(�P3�`0��-�e�C�\$	К&�B���z^-�e-�s���yW6�#�\r��,�� �ҍ�0�U���ESKj:�\"�����9(����c0��NkX�&�0������s�J7��P9�)8�3�#c|Јb��#����^7MvL�ێN{[48�\\��,e�*\r�V�Hê��ɑ�X��)��!\0О���D42���xﷅ����8^��\$��\r2�^��ؘ�!�^0���!��\nb�\r�����8�7@�<5�,BN���Z5̣+����U�F�pA�l[&ʹ�Vٷn�ּݻ��T�߄J |\$���`�K7�zƑjc�3�Ν4�C�,���~\n�?�ֶ�q�09�%/PΤ�b00�i����F@9�����Ø�b�ѐ?��	�� ���\0�o^`i-�̓�ꃰiA�\0×6���^'�˙�@@P��2�\n\n )u&u�d<�W�CJd]���`�N�i�%�'����>'�Ϛ��_S,e��'3��y!͞!�����)�|���q��-a�����C�|�4�8 �Lɡ.����IIN%���G���tL&�7�����9�hP�o\"!�z�(D|�y�XpF5���O���X��f�JXc#�Ƴ�\\��*>!@'�0�@�S���*K����\r(A�цrD�����0Ք%0AI�;'�A��GF�� g���'�AYK#�|���N0S\n!0���\0F\n�A�����s�m��&\0R�8E\r�]<��ɪԢTRe	�Th��6��I�*y���\"��j�\"�8R��K�h\nhp6�ց�Y�\\�A�h�^��4P�2P��R\ra}fz�Ơ@B�F�2�s��R�(O�(S�Rh<P�1b�C@J�iK�%�\"3Y�*�#8��0�1�Cᘶ�rD��3���ޗ�T���|���U��I�M#\\���_`�r\"����6�˃���B!��sA>#���@��ټ1d&������m�%���I\\O0�\"�	�6�\rH��ܿ�B��EV�H@";break;case"de":$g="S4����@s4��S��%��pQ �\n6L�Sp��o��'C)�@f2�\r�s)�0a����i��i6�M�dd�b�\$RCI���[0��cI�� ��S:�y7�a��t\$�t��C��f4����(�e���*,t\n%�M�b���e6[�@���r��d��Qfa�&7���n9�ԇCіg/���* )aRA`��m+G;�=DY��:�֎Q���K\n�c\n|j�']�C�������\\�<,�:�\r٨U;Iz�d���g#��7%�_,�a�a#�\\��\n�p�7\r�:�Cx�\$k���6#zZ@�x�:����x�;�C\"f!1J*��n���.2:����8�QZ����,�\$	��0��0�s�ΎH�̀�K�Z��C\nT��m{����S��C�'��9\r`P�2��lº����-��AI��8 ф����\$�f&GX���S�#Fr�D��	�x΀�Tx��h;��1�\0�(I89�c���C�H��e\\��CP�/t�H� i^�.���1��حJ*�\$�lc\n�#�����-�ҐF�2:Ψ�\">��jj4��P�l0���3�P�7\rէ6�#\\4!-3X�\rƝ��e�|��7\$瀥�V��S�I�@t&��Ц)�K\0�cVD5��ݎ��5)��e��H:���e����`�޳P؍���t;+S�3\r�X�7��.7���pAHh0��(cH�Bh\n������@�Rx��#`\\�H���h�Hϥ鴲��v�k\n�7�;�N{��э&�4mcvڙ�b��#��}9�;#�(�J�6H0�\r��3\$�I���7w�1�I�z���]�%n����9��R���\0�2\r�\n֌t������a�D������x���ך��pP���xȐ\$v���A��;�6�x�!�>b��ga�0�3��	�(\rƵd��\\^݀arN�\$�MO0r�iN) @��,�'�p��^�C{ρ�>G��Hr}dm�'�N���Q�\$6�ޚ���#����Fb�S� 	��4�6kR�8�Ȝ�ҞnA9X��\0�B�d`	'2'T(��B�-Pb,����![ ����v�� :+����A�*j a�3B��jΨ4��ã��\$T&�@�@G\$�AG��C��1D0�BS���+rx��Hxb�t2&q��X�e	Ӕ�̎���\n`�N#�)�4(H�xy\0�D����y, hiI8��Mف@�� ����,yPM��PZCt�~\$�<�0Mڞ?0��8>���C�mH�0�S��}����)�*�Ĝ�v���@ ˼=%\"v�4:��ȗ��ˣY&M]�Ē�E�0a\\�8x�l��>󈮚��G^[�T����h�� �`���K�i�2���\0� -2���E���SV5.�=D*\\��G(�\$��G���c��~#:CIK �0lOP%��é��&����k� �^�@�*��\$�L`�\r	%Dh�,�6�9V�?P�^A��T��F�\")ܰ2eOiC���a�y\"�Ze�A�F�\$�G�OHg��� � �i��kf�)�.��\n�Z�v��ܛ\nv%�(Bu��IЈ�Q��S`�*�@�A�����9�f�-Ű�V~j#�����3&l��kzGI��GNٔ�z-+��}X�Ĕ�K̘��䀢�y��F��׳�u�E�Qi���:��J�_�4QL��%��%�uJ���@R�P�ˢ�ĉ\"f%��6�BX��sUx�י�\r���Fk�������rm�3�p�׌d<��a���M��X(��]�o�m_��\0";break;case"el":$g="�J����=�Z� �&r͜�g�Y�{=;	E�30��\ng%!��F��3�,�̙i��`��d�L��I�s��9e'�A��='���\nH|�x�V�e�H56�@TБ:�hΧ�g;B�=\\EPTD\r�d�.g2�MF2A�V2i�q+��Nd*S:�d�[h�ڲ�G%����..YJ�#!��j6�2�>h\n�QQ34d�%Y_���\\Rk�_��U�[\n��OW�x�:�X� +�\\�g��+�[J��y��\"���Eb�w1uXK;r���h���s3�D6%������`�Y�J�F((zlܦ&s�/�����2��/%�A�[�7���[��JX�	�đ�Kں��m늕!iBdABpT20�:�%�#���q\\�5)��*@I����\$Ф���6�>�r��ϼ�gfy�/.J��?�*��X�7��p@2�C��9)B �:#�9��\0�7���A5����8�\n8Oc��9��)A\"�\\=.��Q��Z䧾P侪�ڝ*���\0���\\N��J�(�*k[°�b��(l���1Q#\nM)ƥ��l��h�ʪ�Ft�.KM@�\$��@Jyn��Ѽ�/J��`���3N�����B���z�,/���H�<���Nsx�~_�����2�Ø�7�)6�T��`�8&tR�8ث񋦫�g6vv+h�N��X���Gd�,s{3�⾜S��M�������4L���}*g�.�J2�:^���)��5\rj�\\�A j�����p)l��\\\$�'j� F�k�������\$\rm�x��9%NS\$�p|�h�0�#dcU\$�̧�&v_x'����+�����-jC/�\r�NYt|+�j:gM��Vg�p�-;0��Rg/ҩRg!ѝ��~2DJ\$�n���^-�i�.�J���\"\\��ϯ8��C`�9\$����=\n�]O�-g��e�;�dK|J����� ���3���\n�;Cn��W:ŉ�)7�h�+�(n\n��*��� U #�B\$X=��iYʳ{�h��Xu�z�tpL�;`[�z%�%*��ё2�X���L7����f��\$&AХ��S��y����+*YV\$H��t��II-aL)`\\��!K��h�M\n�\$�\\��U�-\\�² ��-ȸ�@�SJ��:����\0A��4���	Ca��8\0xA\0hA�3�D�t��^��.2nN�\0\\��8/a��?�0���p/@�b�y:��/ �tA I�/6�̨äS	[bWAĭLMx�X����`�f}�I&,��e7� ��I�eq�AF�O*e\\����Y�Yo.eܜ���`L)��X[\r�(�����Z�>��g���4�\n��X������%=��d+ ��f��P)\"�w\n�٥o��\$�q\n�>�6�\$8��U?\r���r61\\9�9ހ��Cc��@w�C}	�I� �Z`a�,9(`��s��Ԇ����]\r!�4\n��W�g�U�@�1�lfٛX.I�9~&L��R@�	YO\\(�U ZB�}I �u��f�O�\\�\$k��+K>@P\0����x\nCWG+�����^Yȷ)A\r�I�3��o�9���C(g�-E(%���z��5�;փ~U��e��\$J�⩑ �-*��М�ו�FW\0A^k��\r��:���ÓL�4�:�'<�i����0�%C)��H�B�ڕ9Q.,U\"ڣ�VȤ4HT��C쵈��!VH��zI���7e�r���p��1�m���\$@~�,�G�� :`\\z���#I�yB��F��1G�/Q��0���RPc���@N���x\n�`y{\n<)�H��I9����S��U��#,E��hY�HJ�@d�sі�+=�1+r@͏�!���C�zOՙW�Y�S�C&��A�-��)���J��*[)ƇɎMGE�����Z�(O}��W��*T�~\r+���_�F��eo�̈́H�#Gi/�̭��UN�!?Ol��}��!g�훿n��V�E�����>��ȕ=�H�\\Cĕ�o\r�����\$\nL�[���8la�6`Pח=*U��>��y4��T��Z8~O\"�*�&�{J�G:Uӥ�>���AD�ݳ��0-(4�*v�.�m�\r������[�sRvaN�.1��\"���@{g�b���ܨ�+�k��h)����鄐9r��[E�\"Ă *��K�.:dS������n~�8��Uݖ�x9��\"n1��'�:i��=�(������m�50w'C�,�x�! �C�%�\$��#��1d���z��«�wg�����}܈��v9s{r�/�^ޞ|nxg,Oz�;/5Z\$>�����.�x������ţ���?I�ȩ�}K1n�'s�;����ăN:@Nh�fYC��";break;case"es":$g="�_�NgF�@s2�Χ#x�%��pQ8� 2��y��b6D�lp�t0�����h4����QY(6�Xk��\nx�E̒)t�e�	Nd)�\n�r��b�蹖�2�\0���d3\rF�q��n4��U@Q��i3�L&ȭV�t2�����4&�̆�1��)L�(N\"-��DˌM�Q��v�U#v�Bg����S���x��#W�Ўu��@���R <�f�q�Ӹ�pr�q�߼�n�3t\"O��B�7��(������%�vI��� ���U7�{є�9M��	���9�J�: �bM��;��\"h(-�\0�ϭ�`@:���0�\n@6/̂��.#R�)�ʊ�8�4�	��0�p�*\r(�4���C��\$�\\.9�**a�Ck쎁B0ʗÎз P��H���P�:F[*������\nPA�3:E5B3R���#0&F	@暹ks�\"%20��L�w*��z�7:\r�Tḣ�Xʢp�2���+09�(�C����D��C�P���^uxbPnk4�e��9�*��j�Oh��#�\\W@S�1*r�B ��Ȏ+� ��P�mOb(�ұ(�i������%?s�-25u\r1�:�2�\$	@t&��Ц)�C ���h^-�8h�.�B�`�<��HDcK�\r�2ͥ�d�3� ܬ�ϳJ�7b�I%HB=\\т�ޞ�#s�oȖR29�êX6QKH�L�3�+��4��0�:�@������b��#:��\n�]�\0�K���9\\w�U�Gmz;��`̷\r���9�u	.X�iR�T���*3ϊ5��P��[���R�������D4���9�Ax^;��ti�?+p��!}x�ģp^ݻ���x�&��3f/L8�:�C- �i��9�k{��QA��1�	���A#�����A�:��I�uWX;��'`v]�t���uw@����II\nJ8�ᦒ4���SS�(��H�A�',�⟠���%��#L��Di�;�2�CADg�\0�i�d #g��\$�>C3�@���4��K�7�&�_�p�a�^����<p`\$f�T��8��Z\"�0��\0�8o@���\nJA �9���HB�d����@�i\r1	9Dl�B4��o@�͢s�MjE\"���p���2dd����}�y(�ؑH�9C�:�0��F�RXPؘ�*\\\"�\$PD�t0�(x�f�!��N1�j\$��E�{��;Kd�p@�	S�B!�:��`�\0PI\"a�LI�A�62 �.@Б\r�Q���Kp�f�X�TC���<���g%���чHD����Kf��\$H���BLfq�8��)%�� q43Fp×�X��-T�0�h\\��#H�B�%��bx��ʏ�4SI��ʆ�`\n�fX��#a��J!507���}�EQ\\�\$�x�(j��%k�����ױ�i�6�.a�HbdM�6���	J.9d��@� `�@�駅P��h8#��2�M)���5���f����G]�CjQ�M ���ro��J���`��H̛V�j�7YS\0Pm��Û��q�\$����J�<r��(A*HɆR�X���Й{��Ln\"5Ģ��hx@PP �-*��:Pm�Dc� �����T��S�~��SkK�dV�\r����=��iY\\Z◙z�I�*&t�T���d��#�";break;case"et":$g="K0���a�� 5�M�C)�~\n��fa�F0�M��\ry9�&!��\n2�IIن��cf�p(�a5��3#t����ΧS��%9�����p���N�S\$�X\nFC1��l7AGH��\n7��&xT��\n*LP�|� ���j��\n)�NfS����9��f\\U}:���Rɼ� 4Nғq�Uj;F��| ��:�/�II�����R��7���a�ýa�����t��p���Aߚ�'#<�{�Л��]���a��	��U7�sp��r9Zf�C�)2��ӤWR��O����c�ҽ�	����jx����2�n�v)\nZ�ގ�~2�,X��#j*D(�2<�p��,��<1E`P�:��Ԡ���88#(��!jD0�`P���#�+%��	��JAH#��x���R�\"0K� KK�7L�J���SC�<5�rt7�ɨ�F���4�r7�rL��/�	�z؊�L%8-㬃��jFL�@�9\rC* ��ʐԈ�賏, ΁A l��h�Bx�L��2�Ic\0��kP(\r4��4���2@P��nP�#!���2�HM����4zڤ��I`*��@:�P��7#��X\$	К&�B��*�h\\-�8�.��x���j6L S*�ɩH�3��z�=�ܝF��qH67ˀ��\r�`�Aj�1����:����acL9d�Ό��U��O0�aKh7ƙ*�b��#f���C|T�4�\0촍�@�ݍ)�����ff�%)xܞ��4N̽(�5(�P�8JP9f��!��xߍ��3��:����x�Ʌ�&�9�H�{�6Us�^����x�cHӑ���A\$�Z���On̶k*�H1#*j���zUK��8S�0\\pl�\r�q\\g�r\\�-��.�87s��);��	#h����p���s���\r�;N�	J��=�*z��jwO���H@�a�C�Ԫ6�RC�=�܍3���sI|���k�*e�%��vϠ��4������!��4� �MB)P)���(�c(���΄\r4�p��=BhU�T�R0\ni����i��p4��t\0����ن���r�C����`֮�s��9�F�oÚe���������x�%�\0�Y�hh�ł/!��a\\O��JO	i�d�ه\$�G�)�/O=�3�P�\$?gI����Eɖ����B�\\jp�ů�`�|Z�yr�\nC�`�E�d�(�lzX���WdF�?�V;��pU��n̅��K���pө�����-0k�:It�c(}a���h�Q	��3&�\rA\0F\n�E�F�Ա#I���x��70��i�mtZ��vۀPUk󪑶��T�KS�7�\"L\\�:����y�j�T(�v���pN�0��䢖��C`+\rd̎8�CJ]8�Z��0-	��S�M_J\0\$�������ˑ\r#�H��0ʇ�u'\"�5����@Q�n	�-���L,8�P��:���-L0U9�\"y�K!)�)��~�Q���(�ʯ�\\�;f���p�pP�3]%M�.\$X�n0���Жٓ��'5��֔Y��:!\n9�N��H���.%̷�\$VK�!�4��";break;case"fa":$g="�B����6P텛aT�F6��(J.��0Se�SěaQ\n��\$6�Ma+X�!(A������t�^.�2�[\"S��-�\\�J���)Cfh��!(i�2o	D6��\n�sRXĨ\0Sm`ۘ��k6�Ѷ�m��kv�ᶹ6�	�C!Z�Q�dJɊ�X��+<NCiW�Q�Mb\"����*�5o#�d�v\\��%�ZA���#��g+���>m�c���[��P�vr��s��\r�ZU��s��/��H�r���%�)�NƓq�GXU�+)6\r��*��<�7\rcp�;��\0�9Cx���0�C�2� �2�a:#c��8AP��	c�2+d\"�����%e�_!�y�!m��*�Tڤ%Br� ��9�j�����S&�%hiT�-%���,:ɤ%�@�5�Qb�<̳^�&	�\\�z���\" �7�2��J�&Y�� �9�d(��T7P43CP�(�:�p�4���R��HR@�7L�x��h�n����˾��;�����̜�YI��G'��2B�%v�T�	^�\"�#�O@HKc>�C�դ;�@PH�� gl��c���X�iN��+L!L�t\n;����	r됉�BUKQ�#������~X��qR���M3������̛\0l�ɲÁ�W;\\��%��+�,�������Vc<�d�F@�J��;�Ѱ\$	К&�B��cΌ<��h�6�� �~��\\�x�9�c`�\$�����<�%I\n������m�ֱV�~\"���#@��K��FW�DF(Vc�A&�P�I+�[4�7N{@\\֋.:��x��AoL���o�\rr�p�=�ĴI+�Ʒ�z��B�)�\0�7�t��<�Z�(��wF�쵽^��)�q�Y�f��\"%RK�8�bK����0���C���# ��@�`@1Ҵ�����\r��3��:����x/���Z\n�H��,���\r�����`g��0�ⶍҳF�y��nW�p\r���w��^1�U	@؝��`XAo� �Cʙ��\$)I�+r�oT\";~/���w������0;�X�8.�P2	�� ���(`��/�pIYS\"n�c��]Q�\rx�֨HU�0�%��0�Cȋe ����\"�Hr�Sؙ�B��\"f�i\n�T� �6�@��p@�|9�ʱ�fR��0Ɔ�f�x6��{\$�i����(�%3�~ϼ7A�D~O�\r�;��UJ�E�!0e�!X!4*�1,7@��3���A�\0��@((`��Ve�&��ر�ԬX��e��@�i=j3�y��P�CA�Lyj�9�K��⸴���c�0�>�2�%� �O�9�9_H�,�B��8TD�0rX��4��/8gr}\nR��_e9KȐ&����ej�̬���A1S6�Y��V�b,�1��M�voQð	\$h<���CJ�CdmH�iwB��q�ad_\$W~��9j���\nDt��%����(d-��\"Zm\"#�,v����T]`b)�Bc�u��f\$�n9:���JYi\$�BB�觬�\ny��769�~�\n=��(���Be�&��M�j\n��Rp��D�}�<7�Ǩ�S��ou�y�+�@�/^�5xI�o�8�#jP\"M�J�P��vGy��� ����{��[]������z�Gf��<�I-Յi6Г��lu���M ��\\�2\$��vʑ��T��,sx�0	<4�왋��񉦻��_b<�]�'U��������y8��b'#�W�S�)�ΟRt������He�W�1J79m�����j�\$lV��Y�U�;ˏ�9P]Tm!�+<�����Bx��ΗA4���bI��9�ͷd�r��SƓ�qb�cF�j��dx���uF3��)6�Vi1��]�8��Jjх�t��Ư2n���\"�OM����a ";break;case"fi":$g="O6N��x��a9L#�P�\\33`����d7�Ά���i��&H��\$:GNa��l4�e�p(�u:��&蔲`t:DH�b4o�A����B��b��v?K������d3\rF�q��t<�\rL5 *Xk:��+d��nd����j0�I�ZA��a\r';e�� �K�jI�Nw}�G��\r,�k2�h����@Ʃ(vå��a��p1I��݈*mM�qza��M�C^�m��v���;��c�㞄凃�����P�F����K�u�ҩ��n7��3���5\"p&#T@���@����8>�*V9�c��2&�AH�5�Pޔ�a������X��j����i�82�Pcf&�n(�@�;����x�#�N	êd���P�ҽ0|0��@���)Ӹ�\nъ�(ޙ��\"1o�:�)c�<یS�CP�<��F�i��:�S���##N�\r1�'GI�)����ۼ�H��� �		cd���<��]H(.���\n��F���ʆ��x�:�!-Z���@�<��r>��\\u�cJ5[���c�&C�<�U�P�p�&Ct|2Ub�XӺ��[#T��\r��ɁB�r�#M�2�LMȁ1�*%r\rfmp(�4�5�e�8��]X�� �|�j��\\8<��P��R�@t&��Ц)�C��8�p�;e��[I���1d� �3Ƀ���4\\��	b]�Q�{aI�3v�4X@6��J<8-�`�䎣sE�Dn���S킂�Si�Ѝ-�`@����ql<Zue���2�/�e��Zjy�\rڐ˪����4h���3E!1c��M={V�4�ԍ��CUk��n����yer�k�x!�b������ǅ^�T��4O4�^rj*�c�=�l}TS��C�Ҩ�\\D�eL���\0�2s��1Ή����9�0z\r\n\0�9�Ax^;��rO�����z0m�!L��?���/ ������H)\r˕���D�\n�(M���S�r��D�CxU�Zg5\$����H�_( |������ߋ�~�ܻ?�����oN!�9��S��TB愔���Aj\\\r���r�Md�\n�L���[�P9P�����IXV���Ere���1�x��l�2����	\"��3B�a������x����(HL�\0c}a���؝L|�0�|ݪc�M;�(iࠢ@@@PC��F�AN&h�G���0�4�0�P�Ճ,u{��\"�(g�h 9Db��g0��rj�:�9�-�<u@�	S�@��ٰGC�+� ��9ܵ�~��89*��Hc�� 3���g��6U�r�E��\$�m�3��]C�����+�N��K?����Gi5	Fp��V��@���G��\\\n�{Or�F���5b���5^��sN��\0� -fh�Q�j(��5������H��sT���B�0i��I҈T�;?e���z�R�A<\naD&3U\n` �P(�Dؚ��(1NxEdFP\"�+%�����RTUJx�ǅ�Bwf�p�>���>E,�eL�D�Y�y�xhO�4�7:ު�!.��9Z�UE�^T��d�Y�}툎�jPp�هDH1\"k\0�ѽ7+d*�G^\"��(нV	�*к�xJ�V��]b�qQ6��\nNi�'� ��#K�ev%�Wڼ)�0�A!CU��)�C���h�\r�T���*ȝ��%qq���]�J�&�=I�k*ꭁJ(/����y�!�e��\"���S0d��*I:�#�\\5��Cb��9-���#�DK`";break;case"fr":$g="�E�1i��u9�fS���i7\n��\0�%���(�m8�g3I��e��I�cI��i��D��i6L��İ�22@�sY�2:JeS�\ntL�M&Ӄ��� �Ps��Le�C��f4����(�i���Ɠ<B�\n �LgSt�g�M�CL�7�j��?�7Y3���:N��xI�Na;OB��'��,f��&Bu��L�K������^�\rf�Έ����9�g!uz�c7�����'���z\\ή�����k��n��M<����3�0����3��P�퍏�*��X�7������P�0��rP2\r�T����B���p�;��#D2��NՎ�\$���;	�C(��2#K�������+���\0P�4&\\£���8)Qj��C�'\r�h�ʣ���D�2�B�4ˀP����윲ɬI�%*,��%����*hL�=���I����dK�+@Qp�*�\0S��1\nG20#���1��)>�>�U��!�\n�L���ԍ�&62o�苌��Ɓ�HK^���v���H� j�����C*l�Z�L�C���a� P�9+��X�S��H\nu�����+�!�w �6BS �:�M�(\r&P��.�h0���at��#:P�Ό���2au�^���%A;U�R:b�(݌#�t�����\$	К&�B��\rCP^6��x�0���?*b`�%.������ѡ�UE�)s^�0�Ц�54��ɻ�mu�cx�!ZV��I��ab��am[~Au��:�##=c���l�=3���.ٰ\ryR��H'�������������\n�xד�)�:��.��EMS5�aZ:��\r��ʧLf�M\0CqIJ3O�B 3����[�)*�x�����CCx8a�^��(\\0��r��~L����A��Q���^0���\$�pM\r1�5�bj�3QM�y�*���ܻ{G�87��PkT� '���Ru^�a{Oq�G���#�}A��'���+&P��@|GÂ�*��?��LI�mN�5L���Mʙ&FT7(.�[�\rD褠�#�v�J0)A�������R��(��@r7J�#�\"�gCa���_L�cS��1��n�Q�)*�������\0,%�Y�\0`��R)�!�����x�2GBAAT\"������HCq%9)�.��O�y�\\�ܓ�%z�HI�oV\rQ4����T���C�Lt	�ʖOe9�%B>M0�j�Dh�rnM�q|{є�G�ƛ��Aijf�2��Ckh=Ā���Rd��������DW��g��u��� I�4����.��8�|ʇ��ѤؗO:&�Qڱ3U�ʉ�v\"�B#Jn\0�(�	�cl�97�ʁk!���V	�?��I��VA���ʠP����B{��ד��M5��fK�Di\radvRl6��L  ������`LD%t#I2y��)K��nI�`Rhѯ%p95�\":�W06\r2)=&���P|�ؑS�Սb��\n�h�l���p6#X�,D'�'L6�Y�(�ذm1j����Ef k *6n��FRN�b\r��1�Z,��]�.��tam����(���lCxi5,�a�9�Ɏ�d3��P��h8�j��sf�����6e[kړ�ܾ%���I>N��\"\$��LϢO[�'�8�Y�D�\0U�!�ٱ����2 *�`���a(@��a�BCa7���v��a��	\"r�T��ir}#cF\$���'�\$2T�#*���4	�����-��uv�uީ1�e��jʼ�:�1���0�V˚�D\$Û�#�fSAZ��ZWp��E�";break;case"gl":$g="E9�j��g:����P�\\33AAD�y�@�T���l2�\r&����a9\r�1��h2�aB�Q<A'6�XkY�x��̒l�c\n�NF�I��d��1\0��B�M��	���h,�@\nFC1��l7AF#��\n7��4u�&e7B\rƃ�b7�f�S%6P\n\$��ף���]E�FS���'�M\"�c�r5z;d�jQ�0�·[���(��p�% �\n#���	ˇ)�A`�Y��'7T8N6�Bi�R��hGcK��z&�Q\n�rǓ;��T�*��u�Z�\n9M�=Ӓ�4��肎��K��9���Ț\n�X0�А�䎬\n�k�ҲCI�Y�J�欥�r��*�4����0�m��4�pꆖ��{Z���\\.�\r/ ��\r�R8?i:�\r�~!;	D�\nC*�(�\$����V��\$`0��\n��%,АD�d�D�+�OSt9�Lb���Ot��h��J�`B��+dǊ\nRsF�jP@1��sA#\r�I#p��� @1-(R��K8#�R�7A j���p����Ǣ��\r��4�ʉ���#�D�P�2�t����*r�I�( ��� ���3Qς(�Ա�`�m��\r�4ƃx]U��x��C�بO�)B@�	�ht)�`P�2�h��c,0����GY�p��\0S>ʴi�MLQ�GZZc�R�2��^� ��Wn�(����Щ�9D_���E�*B����S)�p�Q�\"%��`4A���Uh��E������f���b��5���)�0쁍��\\][�Z���:U?�j��/#k=+^�Ve(�����P��*F�\n�#��в:��&��h�B:��!�\n43c0z\r��8a�^���]t���3��\0^�/�r*�A���=a�^0�єh:��Aɳ�����p�R�Ү˳�EѮ�;�g�7�~2{x��҇c����r���ww�����Cs�C�;@�DU��I\$���='�J�>*�æ�z��H%\$��&|YV�r��P�ӌ�'%&1���0{f�k�\0�j��/��������)�{\nU�c<g;����o�zIR�l��%��%Dy:�R&z�Ʉ*h*2@�*�B�H\n7�tiAV&��4`F��\$Ȋ�B���5\$�3�d��{a� �t����Y������/~�]C��#��nⅼ�rR��rh%J�h�u�ppjf��Ӎ�B*�!�3�UNHC�:W�DŇ��S�)^I	*���U�\$G�H���N�)�Q	�M*&&�M���\n2�7E\$�,�V��!%n��)f}=Â��P�	�_Rc��<)�F��W���|����\r���l�Q�i��񛇁�\r�*KHA�Ȓu8��P�Pu������WA\0S\n!1��3�M�	9'd��P�#�כD������/P���r[D\\D�&���Qj��S뽟8�\"d���nt�MuZ�!�3)���d\\Rʺ��\\��la���8��������\\s�j(+e��0�I\r%*`ƔU�)�U\n�����hl0��;:�j\rTl���� �DTH�9�D��-V:�ن7�\$U�:K�}0R�壥��lN�|KF8ʞ�@�C8aSܪ]:VE�t��y6�@Ab���Wd���B�(�KFw�� �A���3���?g����Ӣ�T�02\ncV�34���x��V	a5z��)d���|�����ا�f�@";break;case"he":$g="�J5�\rt��U@ ��a��k���(�ff�P��������<=�R��\rt�]S�F�Rd�~�k�T-t�^q ��`�z�\0�2nI&�A�-yZV\r%��S��`(`1ƃQ��p9��'����K�&cu4���Q��� ��K*�u\r��u�I�Ќ4� MH㖩|���Bjs���=5��.��-���uF�}��D 3�~G=��`1:�F�9�k�)\\���N5�������%�(�n5���sp��r9�B�Q�t0��'3(��o2����d�p8x��Y����\"O��{J�!\ryR���i&���J ��\nҔ�'*����*���-� ӯH�v�&j�\n�A\n7t��.|��Ģ6�'�\\h�-,J�k�(;���)��4�oH���a��\r�t��Jr��<�(�9�#|�2�[W!�˃�� �[��D�Zv�GP�B�1r���k��z{	1����48�\$��M\n6�A b���0�nk�T�l9-��ð)����Ja�nk����D����6��\$�6���,��3T+S%�.�Q�� ����Z U�F��1	*�����\$	К&�B��c��<��h�6�� �P�IT�8���:\r�{&�H�\"�\\�OPJV����z�5��z��IZw��l�[|�p:V��\$�X�0x���tF�ɭK!�	����s�iai5�N�lM��\$΁B�%�\"��s�D�2T\n@�������4�!ah�2\r�H������x0�@�2���D4���9�Ax^;�v�����3���^�ʣ��7�|�!,Y:�}!3kN�1\nV����N��\"�\$���M�����\r\"![>ӵ�~�����]��<D�.��?��zJJ�P��G�rYNd�as�6�䖓O�~' [P��s���0P:���0�4=���LA\0�4��`@1=����m�80�d���cg�9�`���`o�y���=�6\0���\"^p��64]3����\n� \n (\0PR�I�T�!�&����g�!�\0�C�����S�|�a�\r��A�\0��I���\$��əC�m����[@s?�,AH5Cpp�����䣃�h\r!�5������0�S؀�	3[���#\\@Iz�u&��#�\\�b!Dp��U�@�K0ɴ�=�6�Y��Op���`A_�؞��ʟ��atg4�0Xt�I��DN�e�0V�<^ҡ��\0�¤�#����@�L�kZIZ�)��fĜ�1|��D`��1�uW��2�KZm �#HjI������d�S��a-�`���a��\$�vT`�5Q^n\rsNbaiȅ2�^�'�%�qm��J_�r@�?��z�V�%��ۘ�l_X�/�ȧ�d6��Y��3h���h8Y��5�0C��-l �2����D�t�8�Y�����Hԭ�ʸ�z@����2��Ik�1(�^���#����<b���,d�:�dC���f�I�edI��-Z�\$�(�^��mO*�X���K�i��9@+IT믭S\"m�:���-�N�h�\$:\\��";break;case"hu":$g="B4�����e7���P�\\33\r�5	��d8NF0Q8�m�C|��e6kiL � 0��CT�\\\n Č'�LMBl4�fj�MRr2�X)\no9��D����:OF�\\�@\nFC1��l7AL5� �\n�L��Lt�n1�eJ��7)��F�)�\n!aOL5���x��L�sT��V�\r�*DAq2Q�Ǚ�d�u'c-L� 8�'cI�'���Χ!��!4Pd&�nM�J�6�A����p�<W>do6N����\n���\"a�}�c1�=]��\n*J�Un\\t�(;�1�(6B��5��x�73��7�I���8��Z�7*�9�c����;��\"n����̘�R���XҬ�L�玊zd�\r�謫j���mc�#%\rTJ��e�^��������D�<cH�α�(�-�C�\$�M�#��*��;�\"��6�`A3�t�֩���9�²7cH�@&�b������Fr�6H���\$`P��0�K�*モ�k��C�@9\"���M\rI\n���(ȃ&��YV�%m\\U����(�pHX��%�#�?^�#���G�`Ę�r�ž�\\�#��b�-cmq	m��� N�@��jQ��M>6��<�B�����Ge��e���-�yG)@ׂ��`][�xU�ڳ�f^`ؖ(��x��b@Pڂ\\RL��t6�b��\"�\\6��#�0�N�ؒ�IK�5�Z7��2��0SX�]/�<���{_x�a\0�@��c0���:�9���<�=�.�]f6�㪲aJna�#�쫴u�b��#&��3	Qf^!Y���b0��#�0�Q�~�Y�]�:)���@j�'��\0�Ю���1�t\n�=���Ф��D4���9�Ax^;�u�!Pl3��@^�Nå7�}�7�px�!󏭍c\$�)M��/��K*�9�%L������8��`�C�\0��C�p\r%!rj]�x	�<g��S�y�����S��Pod���C��|����d��7\$��^l�X9(�6�G�B\nHe@��7����Y�ȫ���k(l� 7��|�0oYA�\0�mQ��#!�3 �\$��fha�6��{fA\\�Zw�� p䆐��7	�ܼ�\$^F���>������J��3�x��v�@� \n (G�a N�()��9��n�\n�n���dm\r�CVhi��S����lg�ܹ7����K�2�!{�ޏ�9q����4:��r3F����8�QV8!�4 �����0���NW�<'�\0ȕ�:�R�o)Jp���\n�	(nfLϘ#�,&�\$���N\"Z5�\\�+34p�@q��dH{�#Q�6'N�C�\r��rE�Y0�A<)�B`oC�!p�8[�s��`T���b�r�h ��C��l��r ������\$�AퟯjD�� \naD&#jkM�1�RJ�����6��	:߈�6��WePK)9�h��fY*y�g�L��vM��{�%ܬ�'`U}l�g8��V?`�=���|:0WE�Hc�1T�Ӱ�B�['�!��\"�n��I�)�7�0-	��P���\"�����+��u�����.�Cw���>HI%�����÷i{/�X�p�i\0Qy%t��%���c\r*h騳�~�b�`Ԋ���AN\r;�xőY�`�D�9T���2I]���+L=(P�S�9H�@�m�y�7�����F�gL��Dv�Q��'~� ˅��i�2�L�,��,�(zT\r��?qRAYk4���Z�\r���Ӿ�3?�bJ̢����";break;case"id":$g="A7\"Ʉ�i7�BQp�� 9�����A8N�i��g:���@��e9�'1p(�e9�NRiD��0���I�*70#d�@%9����L�@t�A�P)l�`1ƃQ��p9��3||+6bU�t0�͒Ҝ��f)�Nf������S+Դ�o:�\r��@n7�#I��l2������:c����>㘺M��p*���4Sq�����7hA�]��l�7���c'������'�D�\$��H�4�U7�z��o9KH����d7����x���Ng3��Ȗ�C��\$s��**J���H�5�mܽ��b\\��Ϫ��ˠ��,�R<Ҏ����\0Ε\"I�O�A\0�A�r�BS���8�7����\"/M;�@@�HЬ���(�	/k,,��ˀ�� �:=\0P�Er�	�X�5�SK�D��ڜ����!\$ɐꅌ�4��)��A b����Bq/#���5���ۯκ���h12�H�����6O[)�� �T	�V4�Mh�Z5S�!R���ůcbv���jZ�\"@t&��Ц)�B��i�\"�Z6��h�2TJJ�9�d>0�Jd�\r�0̴��*�1�ؗS���\$7�3�t\$/��1���W�`�3��X��Cʄ��\"ϣjی�@�����5��\0�)�B2��\"	 \\V�-���\0��\r�}h��.de���L[�i��ބɋ]��1�ȢP��S��D4���9�Ax^;�r��%s�3���^�@ʂ��A���7��^0��|(ŌK�`��߂�8B[74)���?�X�8V�+��j�Ƶ�k�Ų�6��m;^�7m��=*��	-[0܎���'H#@�>��A�?)E��T֐�n`4�S�o\r#6:41�ǹ(#�/a\$�Q�����,�w�\0��v\r�{2�JѱɊ:u0��\r0L,\"���TnK��ռ���8'IyW�t\n\n@)d��(�ʻ^�Hw�Lʙs2匑�&��� �����%��\0T��hzd��R��\0bE�C��~I[�I�;�@�IX�gk�Ч�G�N��T�m��W�èmA@�K���\rTY�L�/�~NYZ@h4����IhI!�����	C�w����`�I�ǜ6��F�bk!l<��H�b��.\0�(�\ny((�����*.y����I<�'j�5�X�pf\r\$�!B�a��c6&y�2����S\n!1�A�@e�F\n�\$��Ӏ~2��Ax6��C:XIxŝG��̢��D�T'��KB,�\r)\"l��v��!|(��\$��&C�%G�^(�>\\�\r!��Y~�L�3�qݽ��hF�����\"B�F��{5�G�}0fP��ՂN�{@�%�,��1rM�43�nU)!AIS��}S��;h7�5\$QU�AG��&�8����\$��ˆ�B'8\n	��_��{�eZ��|�\"���S� Ƒ-�5�?j�WT���N^��T�O���>�mGNHQ�1T趙zHw� \rH�9�";break;case"it":$g="S4�Χ#x�%���(�a9@L&�)��o����l2�\r��p�\"u9��1qp(�a��b�㙦I!6�NsY�f7��Xj�\0��B��c���H 2�NgC,�Z0��cA��n8���S|\\o���&��N�&(܂ZM7�\r1��I�b2�M��s:�\$Ɠ9�ZY7�D�	�C#\"'j	�� ���!���4Nz��S����fʠ 1�����c0���x-T�E%�� �����\n\"�&V��3��Nw⩸�#;�pPC�����Τ&C~~Ft�h����ts;������#Cb�����l7\r*(椩j\n��4�Q�P%����\r(*\r#��#�Cv���`N:����:����M�пN�\\)�P�2��.��SZ���Ш-��\"��(�<@��I��TT\"�H����0Р��#��1B*ݯ��\r	�zԒ�r7L�М���62�k0J2�3�A� P�D�`PH�� gH�(s����8��П1:��ڕ�Bԛ���N�:jr����3�â� �C+ݯ�s8�P�-\\0���_�Au@XUz9c�-2�(�v7�B@�	�ht)�`P�2�h��c��<��P��7���=@\r3\n69@S �\"	�3Δ�\n�L��\"���ތNcˎ�c3���78Ac@9a����-\rQ��0P9�)h�7�h�@!�b���\$�����qh&b`��mL�;,\$b2�����-�K�bV�T�;�X�#�p@ ��#�iɪ49�`4Z@z\r��9�Ax^;�u��<�@���{ض7i ^���7��^0��\"w5����8��3\"Ȑ䚦)�7��ҩ�>���f����>�3m{h��;�뻭l]�o�v�(\"��|(��6�	��q�PA\r�A�9m���C�%�bB���\$>2�,��\$+�>��Z��1�)(��2�j�)@#7F|X�b�����@�àh5|�����:�\r�Q�%�Ipm'\r̘bv��A�Y�\0����9#N\n\n0)&!�����PIhC\$���5�Bf�I�\$y@B��j�QN>�7�p�~�t\rɨ����̓I}ͥc�a\rd0-��PfS��楠���T	!�09˜6TA!Z	/� 4���`��-�&u���I&D���C���1%���GH mkmuܶH�IC8f�\$�c=�|yD��_�`�£9\nQd���>��0 r��C��b�U�A@���@�\n�H�Q}���h�HoZ��@��@L(6V�`�\n^Ҁ&Q�Ʉ{\$�%�E>&t�t�!�tA�N�t�	�rp�v'�ޑN�%���6+��aJ��,��E�\0	)F,�/��n��k���Z�#/9A��ω��A�v�K�{sL^����J��dT*`ZA�/N ��b�L'�=*�F��*QT`��eB|\\��&dŒ�T\\�Qx/A�<�#�\$S�\nG�U�ZaXȓE�X�Ҋ�L��.y�ľHy,T��,m/vi(\na\r-`M�T�\0�����&E��:21A��~Pe��ΆUI ~<�1uX�\"5����5/Vˬr.v�\r�:�Z�ua��H�\"�.";break;case"ja":$g="�W'�\nc���/�ɘ2-޼O���ᙘ@�S��N4UƂP�ԑ�\\}%QGq�B\r[^G0e<	�&��0S�8�r�&����#A�PKY}t ��Q�\$��I�+ܪ�Õ8��B0��<���h5\r��S�R�9P�:�aKI �T\n\n>��Ygn4\n�T:Shi�1zR��xL&���g`�ɼ� 4N�Q�� 8�'cI��g2��My��d0�5�CA�tt0����S�~���9�����s��=��O�\\�������t\\��m��t�T��BЪOsW��:QP\n�p���p@2�C��99�#��#�X2\r��Z7��\0��\\28B#����bB ��>�h1\\se	�^�1R�e�Lr?h1F��zP ��B*���*�;@��1.��%[��,;L������)K��2�Aɂ\0M��Rr��ZzJ�zK��12�#����eR���iYD#�|έN(�\\#�R8����U8NB#���HA��u8�*4��O�Ä7cH�VD�\n>\\��E�d:?�E��3��) F���gD���%�`��i�`\\;�95J��g���t�)�M��txN�A �������N���:\r[��\\w�j�����ZNiv]��!GGDcC�\$Am����J��Q�@�1��vIV���q�C�G!t�(%�bŹvrd�9&(�FFt��PݗqJa�Q%�g��C-4:b\"s����JS����a��CH�4-�;�.��h��\"�]a��|6��H���\r�0�6\r#p�)vM�m#��RAL؀�7��h�7!\0�V��܎c0�6`�3Xݎ\\��3�;�A��h۾��P9�.c�F��l~@�B�)խ�lar��n�����@�D�@�د	g%�[\n�L��\0�V��8@ ��~�9tc_V��@@-F�3��:����x/���P@.Aa��P�Т���� }N}���|��*#�܎7� Qx���H&%t �)�z�p�Bq.�ţ�y�E�PP��T�9�\$H���p\r-��7���o����,}p(9@�UTU���\"��Hm�6��� 򞍧7��xpk5����߃pt��ABA	�1>'�Z-���E^ot���x ���#XC�Q�v���ӕr��9�<�4�8��4��ch q27����F�#dp���3ƀ�bx@Pj���Tn:�D��9� �\n�xB��!X�a�y�bf�0������F�כfmM�e]�t9Ctq����x;ϧl.ú �H�8U���wuf���4@��Z8��7bl\n#K�;�P�#�i��I�hka|m����؛�(��bd\n�|P\n7Wl��d�I�@�4\n!��!>�)&���%�X��ҒK�ƀ��E��t��v��2��tv8�黇�nP�fAA��ŧ�F�0cs�JZP@n`P	�L*��LU�(\$�@�qC�Ko�&q~G�bNB�����H�@��<:@�MuJ��ԃ�}Zz��Қzb����֖Ҡ@L�|h�B0T\n\rִ��ԇ�]q�hi�Ɨ\n�\\�IS�|IL,�:�.%�\"Dش�Yg�'�DJ���lIc,fm� ��vދ�{/��A\r���W^�Hc\rj}?P�/�|��4�g#`F����\\ꂨT��*�X�)�u��͒e�G�ɩ���\"G�mf�]�6�X�4&�&g4^�ЙyB���,(�q��@�E,�����Lș2Gӑ��wp���/�@�&9��\n�͚3Q����T�Ty='�Z���*3���xN46Ƙ�Ø\\/��n:Z\$|�h�� �:���c&�e�K`iR�',p�q�#3uX'qD��(��";break;case"ka":$g="�A� 	n\0��%`	�j���ᙘ@s@��1��#�		�(�0��\0���T0��V�����4��]A�����C%�P�jX�P����\n9��=A�`�h�Js!O���­A�G�	�,�I#�� 	itA�g�\0P�b2��a��s@U\\)�]�'V@�h]�'�I��.%��ڳ��:Bă�� �UM@T��z�ƕ�duS�*w����y��yO��d�(��OƐNo�<�h�t�2>\\r��֥����;�7HP<�6�%�I��m�s�wi\\�:���\r�P���3ZH>���{�A��:���P\"9 jt�>���M�s��<�.ΚJ��l��*-;.���J��AJK�� ��Z��m�O1K��ӿ��2m�p����vK��^��(��.��䯴�O!F��L��ڪ��R���k��j�A���/9+�e��|�#�w/\n❓�K�+��!L��n=�,�J\0�ͭu4A����ݥN:<���L�a.�sZ��*��(+��9X?I<�[R��L�(�C���);�R���J�M�xݯ�:�H�����b�֤2��%/����J�=��ە���7R�*��,f�Դ��k��PH�� g��*�j]��\0܊��)VO��!BTR9p�3�ܬRpm�O���gdc��vdJ\$��T�2N�ٍt�V�������\0��^b�ôB�U?��n�izEA)Mk��_(��ێpؕXu�%��x�I�ԃ�-�>�V�V�ă`�9n�m{������Y�+ �=����w94:��oö6�pu���|���\r[���{gQ��>����4{G�vͧ#!y�����q��S5!4�J���}!�b��*�y����l�Y�������t��6�ݓ[���#���IV߿�mj'M��+v��NkOs�)	?H�|T� !�6���C�̀.�i�'Xk���,ƴ��ĺ��(7o����r��a�9P�Ah��80t�xw�@�0�H\rpoA��P�� o\r��:���>to�J��<N���2���|�O��?.��1#v��{�u�1Y<2<n�����1������t����'�S�s�)��0�����%vEU�'����Dl:����faa%����_a�5��9C�wa�e�8��_�O���b<�^�e��X�����a_�g��i����}�x�AGļ&v<F�-ȵ<IN��Y\$�W�T�bԎg��4���)�A�/5�&��-\$��S��CeUs�T�S,uy��G�0\"Y9)1�I\$�8��<�a��i�V�q�Sݡ&�h��0��ۈ��(�-:8�\\)�-/5pPW�I�[4j\n4��;�{E�S�}���IMѭE�IGs�j_b�[��?u�+â����i�{)�ڐ�b�\$T:���A���@��,#�,���~NP|�a����I๋�RMĜτ*)���VS�r�s�~ys�3BIM����\0�	v���;�\n�02��R.ϙ�c��ڐue�1wl�mUp\nI�\"d��4�j\$��A�s�h��T�->�v����r�ۨ��ޚ,}3�վ���'�2�ĕX�Q����f�OB9?�eק�A�ǋM]]�wiXx.9JL�A��28�ii�yʼ#@���=�4	ד�\$��<�R���^�h�S�M-r���N�n!�JŞy��R�&ѵ^x�\ruT�g-DB��b10�qQJ�k\$��<k#�_�ۯLm�g�&���6�����`�!���\nԁ'?��0@�ajN�aM\$5_R_����{�`U\n�,_f�RV�wT�O!��.i��ƣI�]�=y)��X����,�SJ7y\0�,e�_�ʕ�B]��<���R]ή,��|Wq&��W�B����]�% ܙ h�Pr�������ah�J.�.뒤7���}S�f ��H\"LrI��OJg��>�\$� |���rQ�g�gIw�i���v�'�@��WVS/e�N�paZ�q����8sL.��IP5h3A�\rٸٙ";break;case"ko":$g="�E��dH�ڕL@����؊Z��h�R�?	E�30�شD���c�:��!#�t+�B�u�Ӑd��<�LJ����N\$�H��iBvr�Z��2X�\\,S�\n�%�ɖ��\n�؞VA�*zc�*��D���0��cA��n8ȡ�R`�M�i��XZ:�	J���>��]��ñN������,�	�v%�qU�Y7�D�	�� 7����i6L�S���:�����h4�N���P +�[�G�bu,�ݔ#������^�hA?�IR���(�X E=i��g̫z	��[*K��XvEH*��[b;��\0�9Cx䠈�#�0�mx�7����:��8BQ\0�c�\$22K����12J�a�X/�*R�P\n� �N��H��j����I^\\#��ǭl�u���<H40	���J��:�bv���Ds�!�\"�&�ӑ�B DS*M��j��M Tn�PP�乍̐BPp�D��9Qc(��Ø�7�*	�U)q:��gY(J�!aL3�u�ӱrBo��YAq+��Qnʓ�܊@�E�P'a8^%ɝ�_X�V��K�S���I�##�X1�i�=C�x6 PH�� gv��d�dL�U	�@꒧Y@V:�!*^������A�gYSp���fĐR��V0dfj���[)����x��A��Koa؄w��\$��2\nDL;�=8�e�#��<�Ⱥ��hZ2��X+UMV6��NԄ�׍�0�6>�+�B&��^��3�M�`P�7�Ch�7!\0�L����c0�6`�3�ØX�[��3�/�A�ea\0���(P9�.{	O�gY ��b��# �6@�s΀�O>M��PE�R\$�Om��+��\"�Y��5:�O�@ �����c9M�x@-^�3��:����x������p����p_\r��7�A���>�8x�>l�TB��ֶX����\$3��I0�H��\$VlP��R��9��V���p\r-}=w���{��W��K�y�9>�����SJp�\"��Hm�6����\n���7��~�k5� ����pt�c���.�`�������߲���8@�kÂx��\"�����!o���'�B\r����^�E�D`�C`s2H��trh\rT4�@\$d� P�ʞ`�������!,�i+58��C�7���cpn�*�D��:Ӓ���p��`��V*����-���!���7�d9�v���iǊa�89�f�Pr[���0��Hg{��7�S^��l���L�H93))���c@�������/3'�T�\$�C��\n\$8��I-p2��lQ\r�8�־C��D��ח���A���!�1�9�(!@'�0�yڛUUR��B�+\n�~���Ԇ���QM)�F���HwRh�(����*f%TeXQ����٪�4�g��g��L(��@�� 6�d#I<���i�H���:j�Py�Be�j*� �Р�FZ3e��Q��\$�	j%U����XN���f�����ҊWWm��tL�6�]<\r!�5���C���2��D����T(!��א�'��\n�P#�p���%>��o1hO��b�d�[CLa�A�\\6����:Aɰ�':�%`�s���?B ��h �\"2�X�@q��a�f\\��]�jCH[\n�{�~�0+ő+�y`  :�S��I��d,(z� �)�Ss&��+��`��IQPW�P1d��]I�H�/E�|y�3ة|/�\$eL�";break;case"lt":$g="T4��FH�%���(�e8NǓY�@�W�̦á�@f�\r��Q4�k9�M�a���Ō��!�^-	Nd)!Ba����S9�lt:��F �0��cA��n8��Ui0���#I��n�P!�D�@l2����Kg\$)L�=&:\nb+�u����l�F0j���o:�\r#(��8Yƛ���/:E����@t4M���HI��'S9���P춛h��b&Nq���|�J��PV�u��o���^<k4�9`��\$�g,�#H(�,1XI�3&�U7��sp��r9X�C	�X�2�k>�6�cF8,c�@��c��#�:���Lͮ.X@��0Xض#�r�Y�#�z���\"��*ZH*�C�����д#R�Ӎ(��)�h\"��<���\r��b	 �� �2�C+����\n�5�Hh�2��l��)`P��5��J,o��ֲ������(��H�:����Š�R�m\nȗQ�n�)KP�%�_\r�(,�H�:�����4#�]ңM.�KT&���P®-A(�=.ʀ�Ղ3���_X���<��S.��Zv8j挪�*��c��9O�ҿ<�bUYF�*9�hh�:<t�\"��tU�1���B\n�ŻD�J\r.<�o+�~Fi�_%C�`\\����-�%��`�If�8f	g1�R��ڂ@�	�ht)�`P�<�Ⱥ��hZ2���+��\"�/DHj9j1�lʍ�0�6,�,�����eKS:�*\r�V7!1ic>9�èط��4�4㖪�,���Z��8깅�S	JV�R�\0�)�B3N7�KDLCܙ�̪S�8�2�6��~m.�-R���1���	F)V���c�2�r��(�/!<,�����\\���\0�2m��s�R2>�\0y��0��C@�:�t�㿤=Ɗ9���!{ڴ#��^�����x�h��\"W\"�R���ζ%7U��q0ڸe��j\$d�N<���������̓�y)�<���z���'��S�tN���|Chp.�:>�֙��R-��4��Pp>�L��Gڨ��`��<�N���bs��*6�Pxh2��1����_FV'b��xa��ߵ���Z�_\$M�E�Pi�)x3��!,u���XT�Hi\r�>�e����v!\$���H\nz@\"\$v\n	�sA\r;�(��!��\r&lΗ��Iy�2�(� ��su&�ЏJ'DY�� rl�<����2t0�����g\r�u���xw5!�4CR�򕁕5T1�0�є)�0��VKIyp\r!�G�\0ڎ�`l�M��bM��՝i\\��XHXy2�u�D7�<p5G8�S>��1�\r��\r��}C\"��(��<I��/f�\0�¤�f��Ƃ\0�I\\�>3��9@�=W�k#\0���MI�34�T7b��cTO��1@�P�:1�n�SFH��<V���G��3lT#I&�y*A��\$CA�\$�>J���˔P0	v7�\"F�=z���9��֚3���0j��b&*n�0�u+�i`�RƷ�.b��C\\��ѐ��Zh>�T;�\r�5�T���\rIr\n�s*�@�A�jw�3�K?a�#�1]4�G���#���wtH��n�a���*W)�.ȍ��J�CM�T�_��J��t��� ���W \n���l��[�m8����@J�m80\n8\ni~����.���6�`o�.B`�RÆg��^�F%Tb\nU\nɐ��`�׏��Y�5s���S#a�*���H�������Q�KPIY��g��wK%�.h|K��1o*��j��G��p9`";break;case"ms":$g="A7\"���t4��BQp�� 9���S	�@n0�Mb4d� 3�d&�p(�=G#�i��s4�N����n3����0r5����h	Nd))W�F��SQ��%���h5\r��Q��s7�Pca�T4� f�\$RH\n*���(1��A7[�0!��i9�`J��Xe6��鱤@k2�!�)��Bɝ/���Bk4���C%�A�4�Js.g��@��	�œ��oF�6�sB�������e9NyCJ|y�`J#h(�G�uH�>�T�k7������r��\"����:7�Nqs|[�8z,��c�����*��<�⌤h���7���)�Z���\"��íBR|� ���3��P�7��z�0��Z��%����p����\n����,X�0�P��>�c�x@�I2[�'I�(��ɂ�ĤҀ䌁B*v:E�sz��4P�B[�(�b(���zr��T�;���0���P�禌0ꅌ�(��!-1Qo��Lh���Zt�jq��ƨ�Z�͂��ɁBB�)z�(\r+k�\"���\"�C�2��cz8\r2�W\räaDI��@�����4&�S�>�\r�3բ@t&��Ц)�B��s�\"�N6�� �V��t��Cd?X (��'#x�3-�pʒ�*��N��/�\"����N0���#sH�1�L�v6aS�7�')\nF\"��/S�D�(��k�4H���(�7�\r؆)�B5�4�-��\r�jY1�\n�ǎm\0�(�;c=aL��'���f΂b��)���X�8�Mir� ���9d��7�ǭ�ч��9�0z\r��8a�^��H\\�m΀\\���z|��J*4�!xDl�#����x�6O�-h(����\"a��?2�ڒ��VU��c�����7S9u�&�o{����|/Ď�^���܇\$7rR�}-{�<h���a\0� rcB���=�0����gJBݾɄJ9NV�\\B1�f�����BH �ԏ����y�Q!�3��D�cg�9�`�G݉��A�:�J}Љ�|���,���p.HPߓ�nI��5~��(��MZh()@�i��N�ACeY�xr��٘ �\n D�4LL�R�H����	y��&9�2��?k���^n�<ou�� d�@ie!��waO�q|�I��r�\r��(N�>0`����Z�%�EĽ\0�+&g�ࠇC��\0PJ#D��ƀc�kHZ��,��l��\r���w_��y�3��`|SxP	�L*<����F\r%�PƭNawG�	��8ENa�YFԤ��@�oIQ��b0F�R�h�D&ERrOP�W3 �`�ѡ����9d��ɹ%PfA�vAHm���HŤfI�5T	B\$�N��_QƽBPWS��tK�\r��V�hcb���c,��ٜm�	Σ8A�d�&��ҖdhB�F���ѳ�(�i�Aɚ� �V�B5��-���uU��M�:��p��\nc'�������2KH�ojiԢ'	�ך�j�ԭ�\\Q�AkFK6�WXt��>��贅�QȈT:[&�8`B)/@51A��y l��U7�(K��;����R��Ps";break;case"nl":$g="W2�N�������)�~\n��fa�O7M�s)��j5�FS���n2�X!��o0���p(�a<M�Sl��e�2�t�I&���#y��+Nb)̅5!Q��q�;�9��`1ƃQ��p9 &pQ��i3�M�`(��ɤf˔�Y;�M`����@�߰���\n,�ঃ	�Xn7�s�����4'S���,:*R�	��5'�t)<_u�������FĜ������'5����>2��v�t+CN��6D�Ͼ��G#��U7�~	ʘr��({S	�X2'�@��m`� c��9��Ț�Oc�.N��c��(�j��*����%\n2J�c�2D�b��O[چJPʙ���a�hl8:#�H�\$�#\"���:���:�0�1p@�,	�,' NK���j���P��6��J.�|Җ*�c�8��\0ұF\"b>��\"(�4�C�k	G��0��P�0�c@���P�7%�;�ã�R(����6�P�������!*R1)XU\$Ul�<��\0�hH�A�-'�Z��+�!���#9@P�1��%�B(Z6ʋ�ޣ3�8JCR�K�#������k�.=,I�iW�7]��*n%�t&�p�	@t&��Ц)�C �k��h�5bP��K#r��.V���\r��̠��X7��2<�����B�J��kCl\r��	��ƒc0�6��9�8�l0�򢊽*�Hڽ�XP9�-�:���8@!�b���9apAr������̻'h�6\n��R��p�8MCx3��c8��{[�:�4�@ ��z�9:#�4��\0x�p��Tp��Ax^;�rc�!�r�3��_\0:֊��A�X����^0�ۭL�q\nXٸ�|�����n�J��)f��&��w�k�����\\�\r�s�9�t�Eį]/Oԍ�L?-N�hD���Hڗ��:v��֘��o%&�-���\rf��:2�x��)?(�\\2�Ţg�	#�*pA�� <f�@����)!���t\nΙ�>h\r��3bJ��sQ�1���V*1/�	:���)Ak\$�(����\$EJ4�#�q�AUj�(!�\"���>4F��R�Ðt*f����K}#(�<�6ẃ�b�����TθsAL��C�~��Q4H-���l�l/\n:�x\\ e&�ܜ��z�J�T����[\r\r5�RjH�y3GI���I�6h�8�R�1L'�!�y,QC/?��٠�8MB�O\naP��M�Q,)e6T.P�\\�b�gd�yk5��\$]��U����\n��\r��3�wi&�ĳh\\��c�xL(�ɁNi:�*D�T_��0�VV�T���g�5f�hҜ#�Rї�MTy'��\0�`�'�g��EN�d2����6<\\�Ba�8!ӄ���ȅM��b��F���V�B�F��'����ii)2�����+�i 'Sc+Q�y������Ո�A#��ڜ�+E�0&� ����d����Gg�\$iyA�y��K�!VL�\$�Oja,�Q!�\">���	���c�~�U�&'|��ˊ,O�(+!���L�\$g䘡�\n�G�q2,����B'�igd4W��,���m_��8O�V?�0ڑ��Q�)(��ÔL�";break;case"no":$g="E9�Q��k5�NC�P�\\33AAD����eA�\"a��t����l��\\�u6��x��A%���k����l9�!B)̅)#I̦��Zi�¨q�,�@\nFC1��l7AGCy�o9L�q��\n\$�������?6B�%#)��\n̳h�Z�r��&K�(�6�nW��mj4`�q���e>�䶁\rKM7'�*\\^�w6^MҒa��>mv�>��t��4�	����j���	�L��w;i��y�`N-1�B9{�Sq��o;�!G+D��a:]�у!�ˢ��gY��8#Ø��H�֍�R>O���6Lb�ͨ����)�2,��\"���8�������	ɀ��=� @�CH�צּL�	��;!N��2����*���h\n�%#\n,�&��@7 �|��*	��8�R�3�����p(@0#r巫d�(!L�.79�c��Bp��1hh�)\0�c\n��CP�\"�H�xH b��n��;-��̨��0���<�(\$2C\$�P8�2�h�7��P��B�қ'������#��Jmw�-H�P��g��*�2Zt�MW�К&�B���zb-��iJ��5n�>|�,Dc(Z���hН�-��7���3՚���R�&N\0�S\n�x�N��*��c�9�è؎Or�X���¶0�%6����aJR*���ؿ.A\0�)�B5�7�*`ZYt䂍cP�Ȱh�ϧ6`P�:OVL�H\r��0iH�42Ik}� ��2f�哌r�� !��\r	���CBl8a�^��(\\����3��X^������A� �La�^0��I�L\rnv&6'c�����t3�z����7P��V��U��9Z�G��a�o[��:p	�qg9q�t�+�#w(���H�8/1|��s\\�!��f�)�#���m[�ά��n��Z�&0���V�Xu�\r�\nV�fP�crE0;�DFXr}!��0���c�͐2&HZ�b�\r�0�\0c8��4��4a˙Lba���5�:�����H\n\0��T.k�AE%4��ƦR˛�aa\r-x�ё�\r0�d�b�P���_Y�9gmm9:���`��a�-@r�FMwh�2��`pbn�\0�7x�h\r!��\0�ߠ|\$#�0�R�JIYO%���bII�tM07��\n�;\\)n���2JBI,�#h���ۄ����}�pf=�ŵ���fJ`cOE]�\$\0\\�A�#�2/W��0ii�9�<�%��>3\\��N^\n��H\n�AhkKD�3�Sۑ~'Ά-�AY�#�x��\"H]&�\naD&�L��w�P(#4���C�.�\n=0Ҥ�B��.�9��@�i剈̑�\$P^�ZR����'\"R�4��9���i�C�������`+\rh\$�;�]q~l�<�Ѧ�RM��4�����B�F�2�s���g���;�3F\r�/����:����K�ERU+r0|M��A�����L`\n/�v|6�qK��<���\"M��#LҚu�U���w����J�'\n�:n���PA��H'�`���|i:ͭ�Α`�[�:�	i�5RZ���p��WN&�6��:�P��ol�&H�2�";break;case"pl":$g="C=D�)��eb��)��e7�BQp�� 9���s�����\r&����yb������ob�\$Gs(�M0��g�i��n0�!�Sa�`�b!�29)�V%9���	�Y 4���I��0��cA��n8��X1�b2���i�<\n!Gj�C\r��6\"�'C��D7�8k��@r2юFF��6�Վ���Z�B��.�j4� �U��i�'\n���v7v;=��SF7&�A�<�؉����r���Z��p��k'��z\n*�κ\0Q+�5Ə&(y���7�����r7���C\r��0�c+D7��`�:#�����\09���ȩ�{�<e��m(�2��Z��Nx��! t*\n����-򴇫�P�ȠϢ�*#��j3<�� P�:��;�=C�;���#�\0/J�9I����B8�7�#��0���6@J�@���\0�4E���9N.8���Ø�7�)����@S��/c ����\$@	H�ݍ�x��ON[�0��Z��@#��K	Ϣ�2C\"&2\$�X���C�58Ue]U2���=)h�pHW��)�C��ŁC8�=!�0ء�\"�S��:H���2�c�4Z��#d�0�C��\"����%&!)QM���i\r{�iJ<��-�0ܡp~_ϜY��w*k��7��n>�&�::��@t&��Ц)�P��o��.�B��p�<�\r�ʂ��L�3�>�\nq:h9=T�&�6M2����܌cB92�A�>���#��Ao��Jx����^\r���Z�2���kŞ��;����꛶�>Q)�V�8��mjژ�~�n���Ik;ָ9���%����pâl'!���p�)�Z b��#�\0�̸�^\$0Ã3�6��`��!|ƛ^��)���~��vc�� �o=P��@ ��<O�Ď�c���2�����D4���9�Ax^;�u��?O��Àl挠D��x\"ƌ��2xa�\0������:�bmq)�e(P�ap\$ԍb�\nAJ\rh��rR�P�!(A���j�A�������_��iY���\0 eN�A='�T�\r!�\0�� aT�E	0ܼ�(e)����_Q�t���BXPcQ)��ܞ����H2kLH:��1MHw@\$lB!�'�Ca&@D09;��U@a�i�0�AC�fx-]I%i��@A�L  �d\$W.��9���c�z%H�]��1E��C��\\K�x�d�C���2�כsx��y��@�_9r?��04��\n\n�)m��3�&N�\$�y%\" H\r�A�Z��8@Hrh\rNViH�KMzB*E��gJgCK�%DљL7�ޤ<�2�G'��n5R&����Љ�(A�4�\nC;�TnW�	�(\rg�)8\nNI�='�% �L�h�=3��I�J!���7V�W�rgF��&\$�@�����)���MaNMg�7�Iv��Pz\$�M��B�m+u7�%>&Jr�)U����ը \$��R!�( 8��R:��j� �X+	\$ʩ�\naD&8RaS*8w#��Gt�Ş��`���U	N)����H{'AHȡ\"��K�)C�����M�⥷��A9��a	�iS��[�&����@sx�WG鳄T�I��k�¯�0W���v\0c�R�&�\r1�Vʩ5�v�ר�I�`98l�M��1�RI��l/�9> (�B�F���^��vn\rͶ�Q]Ee|-X��AU�	�-�ya�y	O\\A�/���V�����#	�\":�+��?Ƽ��c�N�,�j>�<#N[��\n��3�Hq�1���\0���8�_=��0��%#b�y˓沾��p��n\nuX�\0�V��=���\"Y�ƆO�3&tm�2��&H���B!�*�P\"��v�N0[�b�{����� �&�d����xKV�֢������� �\0";break;case"pt":$g="T2�D��r:OF�(J.��0Q9��7�j���s9�էc)�@e7�&��2f4��SI��.&�	��6��'�I�2d��fsX�l@%9��jT�l 7E�&Z!�8���h5\r��Q��z4��F��i7M�ZԞ�	�&))��8&�̆���X\n\$��py��1~4נ\"���^��&��a�V#'��ٞ2��H���d0�vf�����β�����K\$�Sy��x��`�\\[\rOZ��x���N�-�&�����gM�[�<��7�ES�<�n5���st��I��ܰl0�)\r�T:\"m�<�#�0�;��\"p(.�\0��C#�&���/�K\$a��R����`@5(L�4�cȚ)�ҏ6Q�`7\r*Cd8\$�����jC��Cj��P��r!/\n�\nN��㌯���%r�2���\\��B��C3R�k�\$�	����[i%�PD:��L��<�CN��ҳ�&�+�� ��}��x�ˬ�h��\0�<� HKP�hJ(<� S���^u�b\n	��:��P�ፕ�\r�{���n�����4� P��;�J2�s�\"���ҽ������r� ���\"�)[�S���L�%Q�oST(�o�W�W!'κG\"@�	�ht)�`P�2�h��c,0��K_l��Sq!Cc�4m*Y��0���)Ŭ9%RRr���b&ؤ(�r7�	��2C�ƃ\$0�X��\$6c��_o���9�2��R�\n�x֔�)��;(OZ�e��CK�ۣ���T�I�p˗g�9f��1�0n�9���N�6C4;:��8@ ����p���a�� �Ό��D4���9�Ax^;��tm�@+���z����h^ݻr�x�o���	��A��96Q�<o��\"k\r��))e/�K;�M�t]'M�u]`��r��v]�����ݪ�6�\r~��M��'B~�M�-}� Ĉ�	�(��q�w��Q�e\$�ؘ#�s�q�qH0�7rC��\$���w���`9�'њ@oi@��cv�ϪN �~	�����\r�9��r\\�r�D\$�/���B\n\n ( �`�� @�r��LXs#�\0��hsWL&<��՚վl_6F�� ���C��'�\"��µ�gm)0��fg[1�R���v���n\n�4+\r�2����*\$&(aC���s�c�(%��Dc����h\$tW1�`\0��6�Β!�I��\$M\"H(-���o\r��XEy\rb��??��b��od�g��@'�0��Қ7'��{\0Ε��{	t2RK��<'�fhʧ�	&������`��:�]�I�c��X�؁�\"��Q	�j;��0T���P�X�f�;đ\0��:����p-���qQ�&+؜Ss@��锧��h���D_6'C*��\0PC<A��@Ұ�1\nAN 1I2[�)-��X����V*,5� �X�¨T��p��[�'�Ω�3K��`^���.HB����#��Y�[GH�ɉ1a�<��gC�oT��-H0�5��g*�P��!x\ru���K!zo�<�8Q���0@(&\\kz�BiH�a��DQR�h�(�?�^��e��v�E���Hd�+f���u����ث��KX��/&��R)m=���9�\$E\"�j&�";break;case"pt-br":$g="V7��j���m̧(1��?	E�30��\n'0�f�\rR 8�g6��e6�㱤�rG%����o��i��h�Xj���2L�SI�p�6�N��Lv>%9��\$\\�n 7F��Z)�\r9���h5\r��Q��z4��F��i7M�����&)A��9\"�*R�Q\$�s��NXH��f��F[���\"��M�Q��'�S���f��s���!�\r4g฽�䧂�f���L�o7T��Y|�%�7RA\\�i�A��_f�������DIA��\$���QT�*��f�y�ܕM8䜈���;�Kn؎��v���9���Ȝ��@35�����z7��ȃ2�k�\nں��R��4	Ȇ0��X\r)q����\$	Ct9����#%�څ�O\\�(�v!0R�\nC,r�+��/�؈ϸ�򰘦��ڄ\\55��X漲�ȘϱH�\"�/���-/B�V�B+�+3b`޿��x䞍�Z�\r���Ҽ��J2�4�CQ��P�T��PH�� gX� P�ӌc�&�h��b�Bx��4j���P�a�����s�(\"�������rF:�+ЃJ�����20�p��4�Ib\\���-Au�m�̾'��Ф��P�3߀P�\$Bh�\nb�2�x�6���Ë�\"��ڮ����\0P��MSX���x�3\r�\0��!ij��]�ب7�)����ƅ\$c0��T�� ��w�ݏ�8�\n����R���ZVb��#;�b��ށΗ<wu'����b�I�^��B��S\$��\rs�}CӸ�3�ɩ!z@��N��`-kF3��:����x�υ����8^���K��xDuM���}�\$l*�83Q�äB�:�h������Knbk�&�'\n��7x���!���-�s\\�=�tYwH9t�D���r�	#h���#�i�N-Ğ:(\0��Z��A�IJ)c�n��r&aܢ�~M��=�ŭ�&�	�h;��1�����)1�9/�&��\0a�A3�n�DQ�>?Àw��O!/aK���Hɝ\r���s�F:\"r(��\"�I�\0P	A!AX\$��#3\$�n&ġ+OI�\$�m�eA���?�4�, ��1��aL\0vleP0��w�zD)��v��zhXs��@ *!�˷yP�s\n'P��Ds&�EDr�>�\$�\n�CD��K ]P�&��0�'��c�&�DE�[�8&�ѫ�̆�9pΎ��ư��8OH3�0�TD�M����gJ�e����&'\rJb���r�3ѕ2��\r�3����L�-d�>��v����E�0��m�`�	�(�ZM�2�'@kwD�i�r�G&�+��X��y_k��FZC��O��pӦ�v�O\nC'*�f��\n�t�^�2'6��T)�g�6��U�\r���2찞�/r)xɂՄNB5\r�}�6�B�F��x��\$��Z�4u�.�+�eR^Ɗ�d�	��#�3� R<H	w\$��1ŭ/;1�����d�0ya�1�0�B��\\1��\0��{ϩS��`�\0�u��iJ�jӆ��u`�ެ��=�\"Q�)�1@)G#�ҭ��Gc]22�^��Hds��*h���S�.�Ӓ.����(!�%��C�V2��������0d	���DcH�����'�����k�";break;case"ro":$g="S:���VBl� 9�L�S������BQp����	�@p:�\$\"��c���f���L�L�#��>e�L��1p(�/���i��i�L��I�@-	Nd���e9�%�	��@n��h��|�X\nFC1��l7AFsy�o9B�&�\rن�7F԰�82`u���Z:LFSa�zE2`xHx(�n9�̹�g��I�f;���=,��f��o��NƜ��� :n�N,�h��2YY�N�;���΁� �A�f����2�r'-K��� �!�{��:<�ٸ�\nd& g-�(��0`P�ތ�P�7\rcp�;�)��'�#�-@2\r���1À�+C�*9���Ȟ�˨ބ��:�/a6����2�ā�J�E\nℛ,Jh���P�#Jh����V9#���JA(0���\r,+���ѡ9P�\"����ڐ.����/q�) ���#��x�2��lҦ�i¤/��1G4=C�c,z�i������4�L�Bp��8(F��� C�:&\r�<n�	��7RR;J��\rb��AN�J��D�@6��ŠP�PP�pH�A�!��\r^��(�D������0(����(\r��vJ�x�4�\r(��\r�8�Z����#��`�K���)lV�aNM����p �c6�b0�&�\r�jׁR��6�B@�	�ht)�`P���h\\-�9��.�W�6�Ce6(�_D�0ؽ�����J���P�7���4�ƫ�c�̡��k�c���WF1�&� a@����)�\0�5�A��#*O\n��'�䢪�n���A\0�����z*6�B��FHK�*^�9m�zë��X4<�0z\r��8a�^���\\�)���/8_I���/��xDx�R�3��x�r���С(|�GC��'1[D3������n�B�z�������q(�`�^���P@�+�v��;gp��x����	y �����̪ OE����8oX'�����U�aV�敀���ʁ��:�'�z�r�/܁�p�N��0!��c&MÓ�O�0�g�c��aldٲ�tc9��� ��'��CI}R��)��I�d+,����d�)zN��V��&���J�=���n�\"�C��:㒆��ll����4���]T\r-Q��H�\"A��2���yʶ�Ɖ��X�Wo�㓂�H�D��;���\n!\nv&�KW%(\r�d䤡�R�G��%W&�.�>��|@a�e��\0\\�j/��\$<E�ɲ}�H��AN<��:ƲZ���w��-7���P��9(��?�CQC\n<)�IJ���<�|*1�����Z!v�f/.:QE�P�1.!iQ�RfO\$�'h�1�Y�`Ne)Oq�>@�Ba[G&�`���oɽBy�O'�)\\H�VR�%�jn/#F��e���\$j�6��Z�H(�V*�j��U�Cն6|	���L6��HDIq+�ki��e��ca���?a\rR�5��@��W?��k&� b��:�U-�X�3c�\n9\r��nDtbB�F��E�2}���SI`���ŭռdu.E�{�\$Hq�j?m�Q�.�6Ƈ�dV\re�F�q</�Z@Q�3Kl+Y�ChY.�֝|W�����	Le�!� B��X�C��Rց`��qU:�)O<*H�_\0Q5Mʝ�������&�M`�[\$y.�3R�.�*��\"�l���W���L��t!��4���j�H�V9�Wo�g�\\��@";break;case"ru":$g="�I4Qb�\r��h-Z(KA{���ᙘ@s4��\$h�X4m�E�FyAg�����\nQBKW2)R�A@�apz\0]NKWRi�Ay-]�!�&��	���p�CE#���yl��\n@N'R)��\0�	Nd*;AEJ�K����F���\$�V�&�'AA�0�@\nFC1��l7c+�&\"I�Iз��>Ĺ���K,q��ϴ�.��u�9�꠆��L���,&��NsD�M�����e!_��Z��G*�r�;i��9X��p�d����'ˌ6ky�}�V��\n�P����ػN�3\0\$�,�:)�f�(nB>�\$e�\n��mz������!0<=�����S<��lP�*�E�i�䦖�;�(P1�W�j�t�E��B��5��x�7(�9\r㒎\"#��1#���x�9�h苎���*�ㄺ9��Ⱥ�\nc�\n*J�\\�iT\$��S�[�����,��D;Hdn�*˒�R-e�:hBŪ��0�S<Y1i����f���8���E<��v�;�A�S�J\n�����sA<�xh����&�:±ÕlD�9��&��=H�X� �9�cd����7[���q\\(�:�p�4��s�V�51p����@\$2L)�#̼�\$bd���j�b��eR�K�#\$󜖼1;G�\nsY��b�c���й�(�էI��e�����f�Y�1/}�XdL`�pH�A�3�Y\nd����vl���U��G&��P�.3jj���ծ/�(�#+A�V�Av����*��j��a���ע���J�4h�+�^E��\ru_Z\$����0���\0���Q�)��\\�r��OϿ)r�w1��jrA��<z��U�[���Y�N��?y>YO3\\�Ѡ���4\0P�(�hu��\\-�E��.ș��\r���\"6�\n�W\$o��`�p��!G�>8�yE�֮�@/\\�l����lͪ�9\n����t�\r#�%M!ڪT����L=�\$�,�xw#�k�LA����Q��?x&���B#����%\0�����'�`ۘiyr�\"�X\"P`�Ⴅ���k�|&�.��	������³�a�U���C�P�7�p��� b\\@dq��V돤ITL�\r6%>�XQR4Ō!�0��l3���1g&�Ã'\$��,1J��^Q\$���gW�\\r��Gbo�(����\nKL��qU��8 !�6��ܔ�` k�w�D��a�9P�Ah��80t�xw�@�0�Il��rU�v��¹��\r��龖e�g��0��F�`<u��ܚ1ö`�	�\n��.`�r�J5�x|qX�L�M؂�R���A���1(��m�a�Y�2f\\͙�Fi�Yk-Ҵڛ��u.��8�!r�A|����9	�s����H\\C����#Ir�G��D�A���.IDy`�oIgς�>���F����x1Q����L�as�x�\0�C`l�/��+�m��0�e��@ci�9�`�\\�`o�ڷ���K�r�K��/�t�!�9�S�yR9'Q�uG�X���;.�'�5�\r�~��0*n���H(hX�!��� ��p��e�k�6D7�\0��Hv��3׻��\"�L��3ٻ�ݞ!���*�\r��^�ϑX0��(�Z�|�R2�R�)Ғ_�S9��,e���8Tڛӈrea�4��d%�g�U�/� �epe.���������9~䄥ϗ\\HX+��\$��Ewp�	TP��Y����jW�\0��FQ*I�n\"1>�\"2%Jd�1J�T�N���*��Xv�&�JY�yq��ڥ~�)�l�NІ����B8J���DQ� ���\"�� \n<)�J&p�Y�\r!�?8�OJ�r)��gy��c\rR���Jb�q�(O���-lόK�'8|�6�SNA�?���E�oH@�����3���׽���3� \naD&H\nUI�*�u��)�ݬ(�GR�l((y�DHnFD�-sF���l�����'a���x���.��ݒq��P=j��Ly�K��-��A��o�#~��{��p,��K9`>Z��p��.8k�r<C�u�w�V���Kk���\r��h\n��3R��w\"�ߘ1mdQ�DGT&�j-L�T߃rrP�Ԯ���d�U�eW�f2ձ��H0@B��	��mA\0�:�-Ů�~�^��_���v\"��;)y��ô�-��os��\"2Ѧz���ܘ��,�]Ґ\${�*��w�H�&\\E#���)ɶ�9�����9��C+�mt|�6/��N�Y�s��e�-C:at�6��N��*�'�B�0a��KI_ ĳÔ\$b\$|F�,MWz|�bK!#x+]�@f�S�4�b1%<��8`��w���4�����.���h��(�����-����m�J�ξO\$�In�ri\"��!d��~mN9A\nn�&�/�j��8d���B�ä";break;case"sk":$g="N0��FP�%���(��]��(a�@n2�\r�C	��l7��&�����������P�\r�h���l2������5��rxdB\$r:�\rFQ\0��B���18���-9���H�0��cA��n8��)���D�&sL�b\nb�M&}0�a1g�̤�k0��2pQZ@�_bԷ���0 �_0��ɾ�h��\r�Y�83�Nb���p�/ƃN��b�a��aWw�M\r�+o;I���Cv��\0��!����F\"<�lb�Xj�v&�g��0��<���zn5������9\"iH�0����{T���ףC�8@Ø�H�\0oڞ>��d��z�=\n�1�H�5�����*��j�+�P�2��`�2�����I��5�eKX<��b��6 P��+P�,�@�P�����)��`�2��h�:32�j�'�A�m�Nh��Cp�4���R- I��'� �֎@P��HEl���P��\$r<4\r����r��994��Ӕ�sBs���M��*�� @1 ��Z����]����֎�P��M�pHY���4'��\rc\$^7����BM�u�	�u#Xƽ�c��k��k֏�B|?����J�q,�:SO@4Iײ�*1�o9��t^����y(�\\�C`ӆ`�\nu%W���60��n��x��b/�(��	Kd��T��	�ht)�`T26�������mޢ�Ī6M�S:���`�3��0����{U%\r>Ɋ�zB����@:�è��c��:��@�O�cX9l�ϊ���Z6���daJR'#7��8i�@!�b��3ÍDc2&6�@=4nJS�S��V�-c(�2Ӊ�B+��5��H��?\r_4����3���#��O�M����H2���D4���9�Ax^;��r��?�r&3���_�c�\0007�}�@�8x�!����q����)Y)���!�<��a%\$�D(�P�rbA�8��\\��=�7�����{�|����_b|')�@�Ҝ�Hm�5���ߪr_᠍��@z�YLQn��6Br�,?����~�Kڷ%��rr�]�h)��1���n.�\"'v���a����Tۛ�r\r��0(��k\"�M@�1�u�\r��lFFQ�t\0�	��V�\\��+A�'s,SA�D�1�/Yd��\"�E�0@\n\ns�A\r@���y��4�ԫd\"\rY�A�8�\0�k�@�:\0 i:��a#��۟�jȰsB��7��K�ppN�\n�lͣ,p��+sfSaw�����\n��Ɗ�@���^L[+�[�55ϢlN\\wV'����/O\n#-e\n��RHXy3�E�K�NU�חP\\8�ST��2\0C�	�I�� @��]!CR��`�cG��)D����O\naRH��\rO\n�A\r(���\$��=	)��Ғ��1s�~�)�XtHm��4�':O�\$��r��Q	����E�0T\n7+b]-� �T]R>�̂�V��쇓�@˲�\0�\$�pj�u;�=I�_ՐaO8웸�(IJ�V'XȢ�H�v�a�;�JBl\r��5���P��S����VH�I����0�\rL�)�|�9³9���\n�P#�pQY<��r&�\$�W�趨f.�X��BVZ�^;�T=�d�V]��(#��.��3�(&�4GY��h�L&ݠ̓��Y5��+H�JH�q�\$�(�/�?\rJI�Xd��0�و�u:茍�4Q��1'%!07�@�D�BJ�оr�	��J�i)�V*�̟'�\"����NXa�L��J\n�\nk�2��J�Y �&0\"�K����ZkT�4���;I��_�`ҨRʩ8�᳀�\nk( ";break;case"sl":$g="S:D��ib#L&�H�%���(�6�����l7�WƓ��@d0�\r�Y�]0���XI�� ��\r&�y��'��̲��%9���J�nn��S鉆^ #!��j6� �!��n7��F�9�<l�I����/*�L��QZ�v���c���c��M�Q��3���g#N\0�e3�Nb	P��p�@s��Nn�b���f��.������Pl5MB�z67Q�����fn�_�T9�n3��'�Q�������(�p�]/�Sq��w�NG(�.St0��FC~k#?9��)���9���ȗ�`�4��c<��Mʨ��2\$�R����%Jp@�*��^�;��1!��ֹ\r#��b�,0�J`�:�����B�0�H`&���#��x�2���!�*���L�4A�+R��< #t7�MS��\r�~2���5��P4�L�2�R@�P(қ0��*5�R<���|h'\r��2��X�b:!-+K�4�65\$��AKTh<�@R���\\�xb�:�J�5�Òx�8��KB�Bd�F� ��(Γ��/�(Z6�#J�'��P��M����<�����-��o�hZ��-�h��M�6!i��\r]7]��]����l�5,^��]|ܨ`�sޘ�iQ�x��\r@P�\$Bh�\nb���p���b픺��,:%�P�&�LS *#0̝*\rT�2���@\$ϐ*\r애7,��:�c49�è�\$l�I�(�����4�êaLG6.��\r�k�!�b����q4C246��\0@�Px�֎�#)@&��8g\n<���s������\r\"�=PP�2@�#���X2���D4���9�Ax^;�sm�=Ar43��x^�#�,��A�x���^0��2m�<	e�@��7�V��PTFx���k�˯��\0@R�\\�n���?S���c���l9w��0A���\n@>	!�8t����y	yI�&��(��������\n}Bh0�/%�F\n�\$��%���JYq�����@wZM<��P��RZ!��F�ҟMi�D�C ���9a�%�\0�^��i#�̗�\"\noO��CX�6xx!\$�,�u[�H\n\0��RGI2�����^�!P��Pΐs,f�&?�lҟp@G�\0wR\r����d�{J�g��HU��Q�4rT7�@��HP;�@���Hgu\n��D��\\1.&ȚҌ��\n2a�V9��X9���0��\"D��q@�M��j���/	\$L<��NP�h?=\nDy\nAC�u3D�3�8坢]�' 1��h�)�3.	��xɢ�gD�(��Zha���	q.����d��c�L�ڠ Dx�#(І�l�k�ϒm!�gL(��@��0�#H�BТ?d�\nNT����3~���)&N���a�z)��	�*R��S� ��C�����1Ȗ��G�gB�WA�ey6WT���\$n���(���hI���ְ�V%��uƞ���M\r��5�cjN�h'Ѡ:��Q�xF�0E��jm�T��%�����n��9�u^�A��l*�Q}[���0���\0�ZHm|�_�&ْY%m�uya�����H�w6u���]��MBa�3��m��(��)h\\�Ú+V5H4NI�Y\n���@�������UbI�%%�07��2AJsX7�Н\0lV��8(�!�`@kC�N.jN�Z�;�m�iTV�Æ\\7qL9ĺ�H\0��Hb�:q��tJU�ۡL��#Ow�C?�����\0�q+�\$��";break;case"sr":$g="�J4��4P-Ak	@��6�\r��h/`��P�\\33`���h���E����C��\\f�LJⰦ��e_���D�eh��RƂ���hQ�	��jQ����*�1a1�CV�9��%9��P	u6cc�U�P��/�A�B�P�b2��a��s\$_��T���I0�.\"u�Z�H��-�0ՃAcYXZ�5�V\$Q�4�Y�iq���c9m:��M�Q��v2�\r����i;M�S9�� :q�!���:\r<��˵ɫ�x�b���x�>D�q�M��|];ٴRT�R�Ҕ=�q0�!/kV֠�N�)\nS�)��H�3��<��Ӛ�ƨ2E�H�2	��׊�p���p@2�C��9(B#��#��2\r�s�7���8Fr��c�f2-d⚓�E��D��N��+1�������\"��&,�n� kBր����4 �;XM���`�&	�p��I�u2Q�ȧ�sֲ>�k%;+\ry�H�S�I6!�,��,R�ն�ƌ#Lq�NSF�l�\$��d�@�0��\0P���X@��^7V�\rq]W(��Ø�7ثZ�+-�E4�\"M��AJ�*��σT�\$�R�&ˊHO����T�S����\n#l������#>�M�}(�-�|��\n^�\$��H��A j�� �w#�W#�gt3쒀�cik�h�����M֛C\$5�H&f�]�Ыγ�c\"��(]:��Dʒ�چ�\"*�q�	=�d��6���}���*�,e��CR��N��\r6�Av�k/jh�k��ˡ,H�+�l�ik�j�)�)i���K6񤭪�3��\$	К&�B���`�6����Ϗ\"�E���1FK���\r���ܷa\0�98#x�3\r��������a�\n�{�6�#p��(�1�np�3�`@6\r�<\$9�����#8�	%�6�C��aJ֢,s=O9�\"�)Ҝ�Zk�����7n�`ƕ��4D#&�T��2xO�b+���r�\r�*9�]U���\0A��7#��B�����o]�f��4@��:�;��\\a�F��3����z<W��cĀD�Q�BA����[(CKЈ��\"���Dż�6w�OWz�C-Ԟ6D��`1B	���#��Pr_+8�h�!l/\r��Xoa�=�� �X��,G��#�e��h�\$6��b8t��][��@vCz�: ����R20y�7D�ɛAtU�<�e�LO��\"�m?�2Tm'�����0pCc�� s�����)@�Cf��	�='�����E�X�\0�pC�a�\0�1��BCle��!�i�\\C@�L��R�[m��*t�M�( \n (O��S�,ls�@R|�JCY�iE3�q1�9G02������;h� ϗ��k�&O��Sjc\n����>S�ÚGz/��^�����\$���v�h���3�PA6gq�a�;��l��_C�e,�SK@�]i\$�})�\0ZyR%P��j\"�\n�� �8�d��J��_��5\0��HC˱��|�4���t�;G=��s�fFA���YS�pc{�wS4�sX��NIADQ �¥u&p������AU���\"j�(	3[V���bɨ�T��P��S��\"R��38�O��hO�*��R�n��cE�M�DeW�� \naD&\0�J�Ʌ�*QW��CL�H����2D�4=4`���6D���U�F���K��2F��l,(p�qW>�۶�GW��\\�:1(�6L�}m-���ݦ4��C7�h}1Z	����cH]plq�����n�h��؆Lw���}��(#_���\r`|�T*`Z�(�hL�/��[Hy�ǒ��S���w�D�:l�l��?6���g3L�%n����a�U*R�䂑PeHi��ǗW�e��\r)�t9�\r[,Z�N`'3�&B�Ùk�2�t	�^��g�ּ�'-J�\\����8-���b�6�X�L��<��2��Q�R�4��qk��B�e9sJ��:<�.�Z��sQ�}g)4Ϊy���n7�V�J6z�I�84�Pnf4 vxQ\$H_�";break;case"ta":$g="�W* �i��F�\\Hd_�����+�BQp�� 9���t\\U�����@�W��(<�\\��@1	|�@(:�\r��	�S.WA��ht�]�R&����\\�����I`�D�J�\$��:��TϠX��`�*���rj1k�,�Յz@%9���5|�Ud�ߠj䦸��C��f4����~�L��g�����p:E5�e&���@.�����qu����W[��\"�+@�m��\0��,-��һ[�׋&��a;D�x��r4��&�)��s<�!���:\r?����8\nRl�������[zR.�<���\n��8N\"��0���AN�*�Åq`��	�&�B��%0dB���Bʳ�(B�ֶnK��*���9Q�āB��4��:�����Nr\$��Ţ��)2��0�\n*��[�;��\0�9Cx����0�o�7���:\$\n�5O��9��P��EȊ����R����Zĩ�\0�Bnz��A����J<>�p�4��r��K)T��B�|%(D��FF��\r,t�]T�jr�����D���:=KW-D4:\0��ȩ]_�4�b��-�,�W�B�G \r�z��6�O&�r̤ʲp���Պ�I��G��=��:2��F6Jr�Z�{<���CM,�s|�8�7��-��B#��=���5L�v8�S�<2�-ERTN6��iJ��̈́J5�R��U�D�8�ڭhg��l\n���e�	?X�JRR�BٲJ�d�K��d[a�������]��v�Y�[5Ն��M)WV�+��\$e}� N󽥘{�h��/x�A j��� ��m��2�,6��Mĺ۰\"7������+��\n^��ܵ'�R.\0��R�@ޕ*�<����[�|uhZ�n	p��]qm0�w\\�7�g�����QW��x^'h��?��.8G�!v���Ѣ���>z�|���Sf{���7wވ_��8��%B\0�Q��A \$��A�S\n`(2@^Ch/a����P���y��z�JAJQ�\0006,�v��aG�7�`�@e8(��B���Xɜ�<���\r���@�yCha\r��UDCc=��3P�\ngI��g) GF�R@u>�9���R� ٘e^�_�Qnɇ��h���Ʉ)����!�I�L�8D����h}hi���i�b�%\$�W:D��qH3�䷥�m+\"��*�>U��N�a>��UF�A\0A��7&x��*�����w��f��4@��:�;�P\\Qfbgɨ3��Tӛ\"��� }<O�H���|�]طho�έ)��!�f��V��d[ي \"+vy6�����\r]�6�f�(ԧ	����#̟�roL�8�v���\rfm�ٿ8g��e��;'pn�̥��Y�`�I4籊���?'�T����|��a\rg�4���fmN�2�@��h*�jJ�����c0A��Q���A^\n����<�( �3�#�<�LU��IS�a�q�3Ƙ�`��񯄀0�P@�{*�a�68���J��P�7���T�X��r\0PP	@�V�\\U���A�K6g�-9��g^G��,ͯ����S�zOY���=� �|�vO���{���d���&���_Z�HV��\\�lWfg���k�4���e?5p7�IÚ�PV�0�T�Hg����Y���ƈF�د'૜���� �Z��}��D�6D.�k�GWi��k\"��7��D�3'��;E��I�/\$��Baw�0_��/]�9x�:����'-���b��H2&+�\$���� ioG�<'�U����a�:����Hm�4�f�l�CCN6j���p!g{(ޅh��z��P	�L*d�C�(L�V�X�釋��ɡp9z����(LiUX\nC(�y*Y��0z�4����6��|����hiK�!��\ry���&R�P�( ���\0�B` �,t�Mp�-�ZoA�ӧ���� ���,;xb1\\Jl�nC��#\0�%ARKv�瓈�*�W0FQ�|{���qpX�U�Ӏܸ/\n�+���m���C�>��ԑ+��Tn����}�!:W��Z�\nX/̤q\r�������obK��9{ek� �G��^�\r!�5�5õ�ηV��`���]+���9��0�t\n�P#�p�f�iI<��i��u+f�Ӯq���w-��.�GV�c��cO�K�-�҃Υo\\�ǖ��_��_�Wt:w��J~\"������236��.B�c��L0w^\\�(z*ٙ�p�h?T���oV�~[�~�n6��'~�ہ�y�1H2��z��b���:<\\�|�g������E*Ȅmf�Z(�������%�\r���`� �ރjjNI�b��F����m��6g� �\n4dt�Fz�vZK�FS\$��\\���:��:e�Ƈ\no����d��'� (i�i���V�����B�\n&�N�����V�B\"z�>,�B�Zs	B�0�\\#rup:�>弔%8AE�d��i�D�";break;case"th":$g="�\\! �M��@�0tD\0�� \nX:&\0��*�\n8�\0�	E�30�/\0ZB�(^\0�A�K�2\0���&��b�8�KG�n����	I�?J\\�)��b�.��)�\\�S��\"��s\0C�WJ��_6\\+eV�6r�Jé5k���]�8��@%9��9��4��fv2� #!��j6�5��:�i\\�(�zʳy�W e�j�\0MLrS��{q\0�ק�|\\Iq	�n�[�R�|��馛��7;Z��4	=j����.����Y7�D�	�� 7����i6L�S�������0��x�4\r/��0�O�ڶ�p��\0@�-�p�BP�,�JQpXD1���jCb�2�α;�󤅗\$3��\$\r�6��мJ���+��.�6��Q󄟨1���`P���#pά����P.�JV�!��\0�0@P�7\ro��7(�9\r㒰\"@�`�9�� ��>x�p�8���9����i�؃+��¿�)ä�6MJԟ�1lY\$�O*U�@���,�����8n�x\\5�T(�6/\n5��8����BN�H\\I1rl�H��Ô�Y;r�|��ՌIM�&��3I �h��_�Q�B1��,�nm1,��;�,�d��E�;��&i�d��(UZ�b����!N��P����|N3h݌��F89cc(��Ø�7�0{�R�I�F��6S����wܨ�qp\\NM'1�R���p�ap�:5��Li�`��I�IKH��Z �c#ۑSi�h,~�CN�*���#�VK��/�۬���3�\r%ʈ<��S���^|8b��M��]�6��;hӥ�i���d01�q�-�ss�s�T8J+*gKn+�껹�xt��Őÿc9��*�᝱q���>�)�J��uR��E������t���L��u_;v���S�������H\$	К&�B��xI��)c3�v�P^-�e�j]�>.))�@4Z��(\n\r��9\0��z�r=�3`ؕC)�9�,�-Ť�aY{�)޷����T\r��6��A\0ue!�1�3��0u\r��6�ΕC�,?���R� � ���ԪP((`�\r0F�����Vd�S0��3z:��뤣`m\n�{I��,rw��:H�͝�\nm���h��%@!9�[��ͬ�4G:�^o|�Cؚ�fc�Օ%`@C\$N\rɺ!6Xʃ\"n\0�0��\$��\"\r�:\0��x/��3�q�2��^���ter�送�g��0����U-٭�ȸ@���?�5 KQ-MQ�H�L�s-��;����jw@��C5��hx�	(�G)CD��2�V��c,ü���n\\�)w/e�'�����Db�I\r�����z&\\�cty����-\rg�4�T�d�n��c����T�Uhj��	�=��\r�0�9��\0w?�1�����r���0�i�!�6�����~�*R?�\r@<���0��~�'I�9�j5��2��:�YY����{pS��Ɛ��  ���\\�='m��.D�O4&eT�����\$�F�'��C�~�eo*9C��S����;ڤQdo*��FX���BM԰��P桡�JM������Q!ɼ�t�E)\r!�U�\n�X�xc2R�ƺ\\��YQ�X�Ua\\�aS�d��DXWKav8F�0�ftsNۮ���G�����;��]_��IC���4�����n�� �� �O���6�z(�blp�<V;`��ݷLv�q9�t�]6j��N��WI�:)\"�e��HR�&x�cԖ@�zHd���E{˞A*vp�?��⳥�J�Ǟ`�V�D�ʄ��+ě=���R�+�O��L���)B0T�0����6�\"V\"Ċ7����C_V6�E��#�pC�il��:���|n�^m,^ț���Dm�cS��Ց�1�/R�����N�nG�(j�םfTBY�DkD��\nX p06�VCk�D\0�ňq��S+n4֕���Cl�O�~\$�P��h8c�{\n%b���I�cJ=�s`,���:���dہA[���eʳ���u�w9\r�QƅJ��NV��º&��g�&zlRʝ<.5�]�����gӨ\\i�\"m���K/ј!-����V+07�0ڜS�u][L���ѣcK��97P��;f�'x\"]攃)��֨��GA\r��+8��R�Ww�;�Y��8��J��%�u���7_�ܑ�9%p��;�";break;case"tr":$g="E6�M�	�i=�BQp�� 9������ 3����!��i6`'�y�\\\nb,P!�= 2�̑H���o<�N�X�bn���)̅'��b��)��:GX���@\nFC1��l7ASv*|%4��F`(�a1\r�	!���^�2Q�|%�O3���v��K��s��fSd��kXjya��t5��XlF�:�ډi��x���\\�F�a6�3���]7��F	�Ӻ��AE=�� 4�\\�K�K:�L&�QT�k7��8��KH0�F��fe9�<8S���p��NÙ�J2\$�(@:�N��\r�\n�����l4��0@5�0J���	�/�����㢐��S��B��:/�B��l-�P�45�\n6�iA`Ѝ�H �`P�2��`��H�Ƶ�J�\r҂���p�<C�r��i8�'C�z\$�/m��1�Q<,�EE�(AC|#BJ�Ħ.8���3��>�q�bԄ�\"l��ME�-J����b鄁�\\��c!�`P��� �#�떠��1�-JR���X�ͯ�k�9��24�#ɋT�������:���-t�1��7e�x]GQCYgWv�3i��e�,�H��b�t\"��戋c��<��h�0��8�\n�z![��P�%�F���:|��Ú}�I8�:�ê�����ׅ��3���zv9����Ǎ�ܑ>:,8A\"}k��#�4�h���a:5�c�]58،��#�3Fb��#!\0��؁p@#\$�k2�S�\$�~O��k,�9&~�;y�b��#\"��ФQ�*xz|�ԉd:��\\Z�Z�x����3��:����x�ͅ����(��!zg*��K��A�׾�+*�0��,ח�+\"ȇ�x�l�;r9Ź��;��X\r���3C���O=�g�Ar��s������x��0#d����X�,�Yńo��|�/��c�:݈�tN�7:D�LR�E \"�L�Kw��	�S��\0Sf��ܑ�xJ��3����x���r\$��Z'�`��\\��*`�Ɂm������H�&�m���vjƌc�h]�#6�		eu\$|��0��8U@����D��C\$,���\0�Dcu(���\n\n+UTfmS�>S��R*�����c�\n,��h�|�9�A(-�:�>���!��EBFy�\\OM:�h��9\r\$�*��fO�>?fL̐0�)�hp*�w��䢃��h���w&M�t�5�\0:��NJIY����6B�Ⱥ�|\n��\0����f��&<�@�z#ѱ	 ��#	9��v���Ð'��I`(tOkI&��'��B����������j^�x3@'�0�kH�ys,<�vD[(�*\$]&�BH�D�	P�ؤ%AGٳI��J�R4@�I� b�)�`�Bc�)SV��Òg���7�Պ��@�!�8�0�ԙ6h�9�0�@��Fo��#\"��HƸ�҇KG&� �k	�41k-��k+�[�uү�%O8��zOB~)�w�ghB&�!���\n��P��!�@B�F��	��Ȃ_sA����U[�-q�1ک���ܙ�ٯ\n�ں-�t�nhԸ�3�����������P�>H%� �.!|��O�����p\$��		#�3\ro���&�0D�\nd��L��B��Y�	��#���yn/\rb+�^T=v[F~ԘEmp@K\r���'p��-t�L�ɣ�p��.R.���W��}`0�.�>\"�m�\r�@";break;case"uk":$g="�I4�ɠ�h-`��&�K�BQp�� 9��	�r�h-��-}[��Z����H`R������db��rb�h�d��Z��G��H�����\r�Ms6@Se+ȃE6�J�Td�Jsh\$g�\$�G��f�j>���C��f4����j��SdR�B�\rh��SE�6\rV�G!TI��V�����{Z�L����ʔi%Q�B���vUXh���Z<,�΢A��e�����v4��s)�@t�NC	Ӑt4z�C	��kK�4\\L+U0\\F�>�kC�5�A��2@�\$M��4�TA��J\\G�OR����	�.�%\nK���B��4��;\\��\r�'��T��SX5���5�C�����7�I�����{���0��8HC���Y\"Ֆ�:�F\n*X�#.h2�B�ِ)�7)�䦩��Q\$��D&j��,Úֶ�Kz��%˻J���A�Q\$�B22;`ՠс� ��N��R�4J2l��2R�?\n7���TE/d���&�\$��A+��\"<O+�>��p7W�B�`�V\0�<;�p�4��r�P��� ��\r2�	�̍T�8�Ҍ��욲�(�b4Q����]	�x�)�a��dҺ��T�C)]��c\"�,Ix�Pv�a��y\\��d_S\"4��PH�� g��7�D5e�4X�\n8Zݡ(թ3\\�E*�E�l�Oh|h��F��\n���h�0�-u0ZA�J��?�n]�N\r��%r��N��įk�A�)j��?L�����&���J���*NöK��͈�����4;�s��{B��7|b�����\r��>�1�ZSiF�oY�th�GOj(ҘZ�Axu���<]�C�Z�C`�9N�0�N@�3�d@2�pz��U���\n�{�����@:�è�1����:��\0�7��\0�0�~��3��A��l@:��@�\nKY�r*����R�u�9�\\A�.��Ep#�+��h���	X�-dG�dJ�Sm����b��s��*Tfu�2��=�\0��nG)�1��� ���<��Ah��80t�xw�@�0���Q�g�7��Ќ��Fs��8<�=���*dS��4�TDbaE�!,���]R�aR�`�6%�!��.\\d�͔�2���'�5!咔�RJ��<\0��R�J���'E�\"�X�A�.E��b���fFE��Ax\",��\$���t�ld��:,	�x{\n;�0���Rz9{�*;�%ۋY*d5�0���\\9) ˱�h@aq�h�\0�t_Hb9�Ð�2XPa�y%�7���K�}��{��<�a� �1ʉ�ClO�͛s�蓺y���h��RSx�@\$\0[GӲx6j�>��\ndT\$�9̮D�U'8���DG�\$��BiW �x֌B�q�뜳�sΉ��)&� �u�HITe��z�\0Hq�t)�v��,RܚCDB��;���:�09������8?Ø�R�raA���0��CHg�@�}P��;z\n�����]��(�����Q��7����'!�򓩅\$وY�k�q�FCG��J�)f�v�r���<	\$\\<� @K\n9I)!��xN��!�ꤠ̎�l>��&�#p����)@��&���b!Đ��VSE��T���%�����~K�dKW��Bg�}�S�����W}�S2�I)�X\n@��pT�:/&�o��f��}qtD��dA\0S\n!0j��L�P(c[�`&2N�z��rq��UZ��s8�v��;�#���)̪hi�[�yh_e̦PV<��	�\n��ݐ�Vo�F����������6|�賭(�G=0x_�s�a�9��?��a��A\r��V� ��dZ�����僷����L��)��-�����l;IVU��P��h8Xq�\"x�ɺp��/��0G�:�[�%�7�����إ+c�}���veb��hڹ#��1��\nE5l�Zmh� \$WГ_&����4��f�i��ǒr�B\r�%�h�d�\r�)��qW�Tk�\0\$����\r�\\�bn�%�?p#rM���Թ��!\r����.,�ߍj<H����z����MJ�S��5gM��7(�;��ꝛAG_D���ʐ�6��f�ۨ����S�Jyյ���QBV�mgn ��'�l��%�*ke����X�7���Ѝ(��h�ٳ�� �Mm�R �";break;case"vi":$g="Bp��&������ *�(J.��0Q,��Z���)v��@Tf�\n�pj�p�*�V���C`�]��rY<�#\$b\$L2��@%9���I�����Γ���4˅����d3\rF�q��t9N1�Q�E3ڡ�h�j[�J;���o��\n�(�Ub��da���I¾Ri��D�\0\0�A)�X�8@q:�g!�C�_#y�̸�6:����ڋ�.���K;�.���}F��ͼS0��6�������\\��v����N5��n5���x!��r7���C	��1#�����(�͍�&:����;�#\"\\!�%:8!K�H�+�ڜ0R�7���wC(\$F]���]�+��0��Ҏ9�jjP��e�Fd��c@��J*�#�ӊX�\n\npE�ɚ44�K\n�d����@3��&�!\0��3Z���0�9ʤ�H�Ln1\r��?!\0�7?�wBTX�<�8�4���0�(�T43�JV� %h��S�*l����΢mC)�	Rܘ���A���D�,����B�E�*iT\$�E0�1PJ2/#�\"aH�M���Zv�kR������R�R�CpT�&DܰE�^��G^��I�`P���2�h��Uk+�i�pD��h�4��N]�3;'I)�O<�`Uj�S#Y�T1B>6�Z�mx�O1[#��P+�	�ht)�`P�<�Ⱥ��hZ2�P���l�.́Cb�#{40�P�3�c�2��aC3a��Of;����k��Z�x�8���|�C���[46E�`@��s2:�p����Y�8a�PP�ʌ;,�s���(b�)ۨ��q4�a�3�H1J5�EX�dr;��C�P3�cE05��5\n:�k��2\r���t��2>�\0x�����C@�:�t���>g�?#8_C��\$P�҃p^ߕ:;c8x�>%�A��RT(��)�� \$\$:/ã�H)�`+�f��C�)W�Z��fꠦ\0K^��{Oq�>��0w}�7g��s�Q�D7)4P��@>0稃\ndQ C��,���u|AKTLK���\$�IyU�MC�Z�ao�\0��ay@a��<`�C*�!�H���Cc���:Ɛ��A��t� 4o+]z���إ��QHq^+\"�b�Ѫu4����зAA@\$h���q�\"d��E�1��9,�r\r'i@�x��zO�!H���Ko\0���L�O��e9��܅��8T ���rZݚ�w���j�A� '��S�TJ-w�4G	RY���rs���AI�~���KC������CD�\"���D�S�s�̀�`RK�I-h��9�s-f������1�:o-��UGT9��\nS�t#/��RlFd���KxP	�L*p���!9�\r���D���Ge�ܜ���Oh��z|�t)eH��d8L� �;�����7J�Y��eR�����9���`�0�\"d���-P�3�dBA6���,��u��K0�N��)�Ȣ��A i40Q=�*��r�!���`�\\	/�9����	�0����MS|m�h]���\n�P#�qg���`���'P����40�\0��,sS�'��Z^Ι���((�U1		0\n�F\"*���U�����_o,U�b,͔SjӚ���7��+U0lӚ�t�1������#I�	jq\"!��\$��E؍\r�\\0�z&���K��غ�b.���\\ˣ	�{�!ҕ4���*铓\0�����I��L�� ";break;case"zh":$g="�A*�s�\\�r����|%��:�\$\nr.���2�r/d�Ȼ[8� S�8�r�!T�\\�s���I4�b�r��ЀJs!J���:�2�r�ST⢔\n���h5\r��S�R�9Q��*�-Y(eȗB��+��΅�FZ�I9P�Yj^F�X9���P������2�s&֒E��~�����yc�~���#}K�r�s���k��|�i�-r�̀�)c(��C�ݦ#*�J!A�R�\n�k�P��/W�t��Z�U9��WJQ3�W�q�*�'Os%�dbʯC9��Mnr;N�P�)��Z�'1T���*�J;���)nY5������9XS#%����Ans�%��O-�30�*\\O�Ĺlt��0]��6r���^�-�8���\0J���|r���S0�9�),���,���,�pi+\r��F��k��L��J[�\$j��?D\n�L�E�*�>���(O���]�Qsš� AR�L�I SA b��8���8s���N]��\"�^��9zW%�s]�Aɱ��E�t�I�E�1j��IW)�i:R�9T���Q5L�	f��y#`OA-��6U��B��@?���G\n��\$	К&�B��c��<��p�6�� �X�E=�P�:Ijs����]�!tC1��E3|�A��A�A�ɉbt��X�1���HdzW��5�D�I\$�q�C�e|μF�9b��#	9Hs�\$b��hdm\ro\\\r�F��YHWd�Od�iO��E\0;n�2\r�H�2�Y��t�L�*\$K��e`x0�@�2���D4���9�Ax^;�pún��\\7�C8^2��x�7���4�xD1L�X_!�A�E�)��P��I:Q!�Hx�!�\\��Ӧ�B��g�g�9�Њ�3O�,Yy��>�p�O����'����7���?��}(�<� ��t��?�T���Ld�\\�af%I��sp9ȅ�0K!�~!]�*	t��+���>g�Ɗ�H9���W�C/\$J�P�ns��pBDa9��C����KAAB���f�dD��L�8�(��:@\$���`����ڔw���т&���	��#�*@\rR�A�*�z�f4�\"�!�1��	3����?g,���pxK8����+���P��Ţ-��Z�b@��(��t�(�M��:C��WU��{Wg�Q	�v(��s	�8c�鐉�\$�������k��s���h�Bgv�]�W\n<)�D�z�Y1D�Y'A#��a�--7�%-e�c\"V���\$H���ŢNy��#@�G��'�E�0�\na>5��1�B�\$��Ck��v.a��/l̙�,,(�!�b�ߘJDخBE�q5L(��(�\nLh����G�6 \r�_I�T	� hG(����M�SL+���Bd�[X/a��\n�P#�pm��y*��\"��2`�#�%a�������P\\��2T�i�C�@�CL\\�\n����m���b�3�!^��w�\"�\r��@:�3L�u�	oh�a��%\"\\L	-\"\0��'/R=l\"0��vr�H�*	;��xZ+y�^F�B*%H(�)ү1EW���R,";break;case"zh-tw":$g="�^��%ӕ\\�r�����|%��:�\$\ns�.e�UȸE9PK72�(�P�h)ʅ@�:i	%��c�Je �R)ܫ{��	Nd T�P���\\��Õ8�C��f4����aS@/%����N����Nd�%гC��ɗB�Q+����B�_MK,�\$���u��ow�f��T9�WK��ʏW����2mizX:P	�*��_/�g*eSLK�ۈ��ι^9�H�\r���7��Zz>�����0)ȿN�\n�r!U=R�\n����^���J��T�O�](��I��^ܫ�]E�J4\$yhr��2^?[���eC�r��^[#�k�֑g1'��)�T'9jB)#�,�%')n䪪�hV���d�=Oa�@�IBO���s�¦K���J��12A\$�&�8mQd���lY�r�%�\0J�1ġ�D�)*O̊T�4L��9D�B+�Ⱕy�L�)pY��@��s�%�^R���pr\$-G����%,M��x�C��2��R��� SA b��h���8��!v]��!*��Bsē�G�I�~���Z<^��i\\CD=�M��i t�e�|[:��t�S�\\X�����\\W��)]%�\\	z��MF��7�]�̱�G�ʲ�\$	К&�B��c�,<��p�6�� �k[��� P�:L�Pt�eM�����t�*T1F���.�ޥ!c �7 �\$	�HatAW�A�I ���a�C��ARS`�ԀD&� �)�B0@:\r�X�70����E^5I��r��tND'�Tٞ^�9O�a:�F�@�2\r�H�2�Zv��g1\nW(ٹH���x0�@�2���D4���9�Ax^;��p��r\\�\\7�C8_��� �7#����|s��D_!�A�E�d�s����!�^0��p)�����ڜW���G-n��yL�e�E��\$0J��K�u��ػ7j�܋�N�޻����§\rϝ�\"���r��J����D��#�Tt\n�&A�ʒR�r��,e�#�}\rL_��@��MI��\"qZ)Ѝ \n�Xv���q�4s���\$!6�H��4��b�k#�O�s(�H�%���3g�K��\0�F��H\n	�1!�?D#�)Oq��\"`L���k R�`9�L\"4qpPF��b���D�s0aR�l��E!X�.�s\nxӍQ�4�A��̫J�]J��uW�l�nF���-��J%�ęɴ`9�p�H᭔X�'%	A5� �7#�9DP�D��S!�'���3,͗Q�/��.Q���2,D�nb\\N\$�9���\"tQ`�T��\n��)�\0�l��\"�lS8�F��M����)2���,*��W��@	����P'd�Y�tz\"Q	����s�͙�����I|ןҞ������r���</�Қs�T�~��0ĜڡP�ܾ���Uu@gА�1B�@��~��`� <!���r�I�9�O��]4ґ�:����NI�,5S�A��0-�E�}{R������:�Y#)b��2�u��|��f���!�P \r\$OP�6��L�çÏ�e��		ȹ��}?�D����!⸊=\n��@ JlМ�0轢�\"D���}jպ(Db�K^掁H���%�A{޴��IqW���s+�xx��+Re�D.Q�<G�";break;}$tg=array();foreach(explode("\n",lzw_decompress($g))as$X)$tg[]=(strpos($X,"\t")?explode("\t",$X):$X);return$tg;}if(!$tg){$tg=get_translations($ba);$_SESSION["translations"]=$tg;}if(extension_loaded('pdo')){class
Min_PDO
extends
PDO{var$_result,$server_info,$affected_rows,$errno,$error;function
__construct(){global$b;$Ie=array_search("SQL",$b->operators);if($Ie!==false)unset($b->operators[$Ie]);}function
dsn($Kb,$V,$F,$C=array()){try{parent::__construct($Kb,$V,$F,$C);}catch(Exception$Yb){auth_error(hTrim($Yb->getMessage()));}$this->setAttribute(13,array('Min_PDOStatement'));$this->server_info=@$this->getAttribute(4);}function
query($G,$Bg=false){$I=parent::query($G);$this->error="";if(!$I){[,$this->errno,$this->error]=$this->errorInfo();if(!$this->error)$this->error=lang(21);return
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
connect(){global$b;[,,$F]=$b->credentials();if($F!="")return
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
hTrim($h->error);
}

    function
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
show_status(){$J=array();foreach(get_vals("PRAGMA compile_options")as$qe){[$y,$X]=explode("=",$qe,2);$J[$y]=$X;}return$J;}function
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
hTrim(pg_last_notice($this->_link));}}class
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
ORDER BY a.attnum")as$K){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$K["full_type"],$A);[,$U,$Bd,$K["length"],$sa,$za]=$A;$K["length"].=$za;$Va=$U.$sa;if(isset($xa[$Va])){$K["type"]=$xa[$Va];$K["full_type"]=$K["type"].$Bd.$za;}else{$K["type"]=$U;$K["full_type"]=$K["type"].$Bd.$sa.$za;}if($K['identity'])$K['default']='GENERATED BY DEFAULT AS IDENTITY';$K["null"]=!$K["attnotnull"];$K["auto_increment"]=$K['identity']||preg_match('~^nextval\(~i',$K["default"]);$K["privileges"]=array("insert"=>1,"select"=>1,"update"=>1);if(preg_match('~(.+)::[^)]+(.*)~',$K["default"],$A))$K["default"]=($A[1]=="NULL"?null:(($A[1][0]=="'"?idf_unescape($A[1]):$A[1]).$A[2]));$J[$K["field"]]=$K;}return$J;}function
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
error(){global$h;$J=hTrim($h->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$J,$A))$J=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
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
hTrim($h->error);}function
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
nl_br(hTrim(preg_replace('~^(\[[^]]*])+~m','',$h->error)));}function
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
hTrim($h->error);}function
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
connect(){global$b;[,,$F]=$b->credentials();if($F!="")return
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
hTrim($h->error);}function
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
sdb_request($ra,$E=array()){global$b,$h;[$Sc,$E['AWSAccessKeyId'],$rf]=$b->credentials();$E['Action']=$ra;$E['Timestamp']=gmdate('Y-m-d\TH:i:s+00:00');$E['Version']='2009-04-15';$E['SignatureVersion']=2;$E['SignatureMethod']='HmacSHA1';ksort($E);$G='';foreach($E
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
as$cc){[$db,$ne,$X]=explode(" ",$cc,3);if($db=="_id"){$X=str_replace('MongoDB\BSON\ObjectID("',"",$X);$X=str_replace('")',"",$X);$ab='MongoDB\BSON\ObjectID';$X=new$ab($X);}if(!in_array($ne,$b->operators))continue;if(preg_match('~^\(f\)(.+)~',$ne,$A)){$X=(float)$X;$ne=$A[1];}elseif(preg_match('~^\(date\)(.+)~',$ne,$A)){$wb=new
DateTime($X);$ab='MongoDB\BSON\UTCDatetime';$X=new$ab($wb->getTimestamp()*1000);$ne=$A[1];}switch($ne){case'=':$ne='$eq';break;case'!=':$ne='$ne';break;case'>':$ne='$gt';break;case'<':$ne='$lt';break;case'>=':$ne='$gte';break;case'<=':$ne='$lte';break;case'regex':$ne='$regex';break;default:continue
2;}if($U=='and')$vb['$and'][]=array($db=>array($ne=>$X));elseif($U=='or')$vb['$or'][]=array($db=>array($ne=>$X));}}}return$vb;}$pe=array("=","!=",">","<",">=","<=","regex","(f)=","(f)!=","(f)>","(f)<","(f)>=","(f)<=","(date)=","(date)!=","(date)>","(date)<","(date)>=","(date)<=",);}function
table($u){return$u;}function
idf_escape($u){return$u;}function
table_status($B="",$ic=false){$J=array();foreach(tables_list()as$R=>$U){$J[$R]=array("Name"=>$R);if($B==$R)return$J[$R];}return$J;}function
create_database($m,$d){return
true;}function
last_id(){global$h;return$h->last_id;}function
error(){global$h;return
hTrim($h->error);}function
collations() {
    return
array();}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
connect(){global$b;$h=new
Min_DB;[$O,$V,$F]=$b->credentials();$C=array();if($V.$F!=""){$C["username"]=$V;$C["password"]=$F;}$m=$b->database();if($m!="")$C["db"]=$m;try{$h->_link=$h->connect("mongodb://$O",$C);if($F!=""){$C["password"]="";try{$h->connect("mongodb://$O",$C);return
lang(22);}catch(Exception$Yb){}}return$h;}catch(Exception$Yb){return$Yb->getMessage();}}function
alter_indexes($R,$c){global$h;foreach($c
as$X){[$U,$B,$P]=$X;if($P=="DROP")$J=$h->_db->command(array("deleteIndexes"=>$R,"index"=>$B));else{$f=array();foreach($P
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
as$X){[$db,$ne,$X]=explode(" ",$X,3);if($db=="_id")$vb["query"]["ids"]["values"][]=$X;elseif($db.$X!=""){$cg=array("term"=>array(($db!=""?$db:"_all")=>$X));if($ne=="=")$vb["query"]["filtered"]["filter"]["and"][]=$cg;else$vb["query"]["filtered"]["query"]["bool"]["must"][]=$cg;}}if($vb["query"]&&!$vb["query"]["filtered"]["query"]&&!$vb["query"]["ids"])$vb["query"]["filtered"]["query"]=array("match_all"=>array());$Nf=microtime(true);$qf=$this->_conn->query($G,$vb);if($Ne)echo$b->selectQuery("$G: ".print_r($vb,true),$Nf,!$qf);if(!$qf)return
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
Min_DB;[$O,$V,$F]=$b->credentials();if($F!=""&&$h->connect($O,$V,""))return
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
hTrim($h->error);}function
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
hTrim($h->error);}function
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
connect($O="",$V="",$F="",$k=null,$He=null,$Ff=null){global$b;mysqli_report(MYSQLI_REPORT_OFF);[$Sc,$He]=explode(":",$O,2);$Mf=$b->connectSsl();if($Mf)$this->ssl_set($Mf['key'],$Mf['cert'],$Mf['ca'],'','');$J=@$this->real_connect(($O!=""?$Sc:ini_get("mysqli.default_host")),($O.$V!=""?$V:ini_get("mysqli.default_user")),($O.$V.$F!=""?$F:ini_get("mysqli.default_pw")),$k,(is_numeric($He)?$He:ini_get("mysqli.default_port")),(!is_numeric($He)?$He:$Ff),($Mf?64:0));$this->options(MYSQLI_OPT_LOCAL_INFILE,false);return$J;}function
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
hTrim(preg_replace('~^You have an error.*syntax to use~U', "Syntax error",$h->error));}function
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
loginForm(){echo"<table cellspacing='0' class='layout'>\n",$this->loginFormField('username','<tr><th>'.lang(35).'<td>','<input type="hidden" name="auth[driver]" value="server"><input name="auth[username]" id="username" value="'.hTrim($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("focus(qs('#username'));")),$this->loginFormField('password','<tr><th>'.lang(36).'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'."\n"),"</table>\n","<p><input type='submit' value='".lang(37)."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],lang(38))."\n";}function
loginFormField($B,$Pc,$Y){return$Pc.$Y;}function
login($Gd,$F){return
true;}function
tableName($Xf){return
hTrim($Xf["Comment"]!=""?$Xf["Comment"]:$Xf["Name"]);}function
fieldName($p,$se=0){return
hTrim(preg_replace('~\s+\[.*\]$~','',($p["comment"]!=""?$p["comment"]:$p["field"])));}function
selectLinks($Xf,$P=""){$a=$Xf["Name"];if($P!==null)echo'<p class="tabs"><a href="'.hTrim(ME.'edit='.urlencode($a).$P).'">'.lang(39)."</a>\n";
    }

    function
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
as$e=>$X)$_.=where_link($s++,$e,$K[$X]);echo"<a href='".hTrim($_)."'>".hTrim($Ia["name"])."</a>";$_=ME.'edit='.urlencode($R);foreach($gb
                                                                                                                                     as $e=> $X)$_.="&set".urlencode("[".bracket_escape($e)."]")."=".urlencode($K[$X]);echo"<a href='".hTrim($_)."' title='".lang(39)."'>+</a> ";}}}function
selectQuery($G, $Nf, $hc=false){return"<!--\n".str_replace("--","--><!-- ",$G)."\n(".format_time($Nf).")\n-->\n";}function
rowDescription($R){foreach(fields($R)as$p){if(preg_match("~varchar|character varying~",$p["type"]))return
idf_escape($p["field"]);}return"";}function
rowDescriptions($L,$xc){$J=$L;foreach($L[0]as$y=>$X){if(list($R,$t,$B)=$this->_foreignColumn($xc,$y)){$Wc=array();foreach($L
as$K)$Wc[$K[$y]]=q($K[$y]);$Bb=$this->_values[$R];if(!$Bb)$Bb=get_key_vals("SELECT $t, $B FROM ".table($R)." WHERE $t IN (".implode(", ",$Wc).")");foreach($L
as$Zd=>$K){if(isset($K[$y]))$J[$Zd][$y]=(string)$Bb[$K[$y]];}}}return$J;}function
selectLink($X,$p){}function
selectVal($X,$_,$p,$ue){$J=$X;$_=hTrim($_);if(preg_match('~blob|bytea~',$p["type"])&&!is_utf8($X)){$J=lang(40,strlen($ue));if(preg_match("~^(GIF|\xFF\xD8\xFF|\x89PNG\x0D\x0A\x1A\x0A)~",$ue))$J="<img src='$_' alt='$J'>";}if(like_bool($p)&&$J!="")$J=(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?lang(41):lang(42));if($_)$J="<a href='$_'".(is_url($_)?target_blank():"").">$J</a>";if(!$_&&!like_bool($p)&&preg_match(number_type(),$p["type"]))$J="<div class='number'>$J</div>";elseif(preg_match('~date~',$p["type"]))$J="<div class='datetime'>$J</div>";return$J;}function
editVal($X,$p){if(preg_match('~date|timestamp~',$p["type"])&&$X!==null)return
preg_replace('~^(\d{2}(\d+))-(0?(\d+))-(0?(\d+))~',lang(43),$X);return$X;}function
selectColumnsPrint($M,$f){}function
selectSearchPrint($Z,$f,$w){$Z=(array)$_GET["where"];echo'<fieldset id="fieldset-search"><legend>'.lang(44)."</legend><div>\n";$td=array();foreach($Z
as$y=>$X)$td[$X["col"]]=$y;$s=0;$q=fields($_GET["select"]);foreach($f
as$B=>$Ab){$p=$q[$B];if(preg_match("~enum~",$p["type"])||like_bool($p)){$y=$td[$B];$s--;echo"<div>".hTrim($Ab)."<input type='hidden' name='where[$s][col]' value='".hTrim($B)."'>:",(like_bool($p)?" <select name='where[$s][val]'>".optionlist(array(""=>"",lang(42),lang(41)),$Z[$y]["val"],true)."</select>":enum_input("checkbox"," name='where[$s][val][]'",$p,(array)$Z[$y]["val"],($p["null"]?0:null))),"</div>\n";unset($f[$B]);}elseif(is_array($C=$this->_foreignKeyOptions($_GET["select"],$B))){if($q[$B]["null"])$C[0]='('.lang(7).')';$y=$td[$B];$s--;echo"<div>".hTrim($Ab)."<input type='hidden' name='where[$s][col]' value='".hTrim($B)."'><input type='hidden' name='where[$s][op]' value='='>: <select name='where[$s][val]'>".optionlist($C,$Z[$y]["val"],true)."</select></div>\n";unset($f[$B]);}}$s=0;foreach($Z
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      as $X){if(($X["col"]==""||$f[$X["col"]])&&"$X[col]$X[val]"!=""){echo"<div><select name='where[$s][col]'><option value=''>(".lang(45).")".optionlist($f,$X["col"],true)."</select>",html_select("where[$s][op]",array(-1=>"")+$this->operators,$X["op"]),"<input type='search' name='where[$s][val]' value='".hTrim($X["val"])."'>".script("mixin(qsl('input'), {onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});","")."</div>\n";$s++;}}echo"<div><select name='where[$s][col]'><option value=''>(".lang(45).")".optionlist($f,null,true)."</select>",script("qsl('select').onchange = selectAddRow;",""),html_select("where[$s][op]",array(-1=>"")+$this->operators),"<input type='search' name='where[$s][val]'></div>",script("mixin(qsl('input'), {onchange: function () { this.parentNode.firstChild.onchange(); }, onsearch: selectSearchSearch});"),"</div></fieldset>\n";}function
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
selectEmailPrint($Qb,$f){if($Qb){print_fieldset("email",lang(50),$_POST["email_append"]);echo"<div>",script("qsl('div').onkeydown = partialArg(bodyKeydown, 'email');"),"<p>".lang(51).": <input name='email_from' value='".hTrim($_POST?$_POST["email_from"]:$_COOKIE["adminer_email"])."'>\n",lang(52).": <input name='email_subject' value='".hTrim($_POST["email_subject"])."'>\n","<p><textarea name='email_message' rows='15' cols='75'>".hTrim($_POST["email_message"].($_POST["email_append"]?'{$'."$_POST[email_addition]}":""))."</textarea>\n","<p>".script("qsl('p').onkeydown = partialArg(bodyKeydown, 'email_append');","").html_select("email_addition",$f,$_POST["email_addition"])."<input type='submit' name='email_append' value='".lang(11)."'>\n";echo"<p>".lang(53).": <input type='file' name='email_files[]'>".script("qsl('input').onchange = emailFileChange;"),"<p>".(count($Qb)==1?'<input type="hidden" name="email_field" value="'.hTrim(key($Qb)).'">':html_select("email_field",$Qb)),"<input type='submit' name='email' value='".lang(54)."'>".confirm(),"</div>\n","</div></fieldset>\n";}}function
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
editInput($R,$p,$Da,$Y){if($p["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$Da value='-1' checked><i>".lang(8)."</i></label> ":"").enum_input("radio",$Da,$p,($Y||isset($_GET["select"])?$Y:0),($p["null"]?"":null));$C=$this->_foreignKeyOptions($R,$p["field"],$Y);if($C!==null)return(is_array($C)?"<select$Da>".optionlist($C,$Y,true)."</select>":"<input value='".hTrim($Y)."'$Da class='hidden'>"."<input value='".hTrim($C)."' class='jsonly'>"."<div></div>".script("qsl('input').oninput = partial(whisper, '".ME."script=complete&source=".urlencode($R)."&field=".urlencode($p["field"])."&value=');
qsl('div').onclick = whisperClick;",""));if(like_bool($p))return'<input type="checkbox" value="1"'.(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?' checked':'')."$Da>";$Qc="";if(preg_match('~time~',$p["type"]))$Qc=lang(57);if(preg_match('~date|timestamp~',$p["type"]))$Qc=lang(58).($Qc?" [$Qc]":"");if($Qc)return"<input value='".hTrim($Y)."'$Da> ($Qc)";if(preg_match('~_(md5|sha1)$~i',$p["field"]))return"<input type='password' value='".hTrim($Y)."'$Da>";return'';}function
editHint($R,$p,$Y){return(preg_match('~\s+(\[.*\])$~',($p["comment"]!=""?$p["comment"]:$p["field"]),$A)?hTrim(" $A[1]"):'');}function
processInput($p,$Y,$Dc=""){
        if ($Dc=="now")return"$Dc()";$J=$Y;if(preg_match('~date|timestamp~',$p["type"])&&preg_match('(^'.str_replace('\$1','(?P<p1>\d*)',preg_replace('~(\\\\\\$([2-6]))~','(?P<p\2>\d{1,2})',preg_quote(lang(43)))).'(.*))',$Y,$A))$J=($A["p1"]!=""?$A["p1"]:($A["p2"]!=""?($A["p2"]<70?20:19).$A["p2"]:gmdate("Y")))."-$A[p3]$A[p4]-$A[p5]$A[p6]".end($A);$J=($p["type"]=="bit"&&preg_match('~^[0-9]+$~',$Y)?$J:q($J));if($Y==""&&like_bool($p))$J="'0'";elseif($Y==""&&($p["null"]||!preg_match('~char|text~',$p["type"])))$J="NULL";elseif(preg_match('~^(md5|sha1)$~',$Dc))$J="$Dc($J)";return
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
<a href="https://www.adminer.org/editor/#download"',target_blank(),' id="version">',(version_compare($ca,$_COOKIE["adminer_version"])<0?hTrim($_COOKIE["adminer_version"]):""),'</a>
</h1>
';if($Wd =="auth"){$pc=true;foreach((array)$_SESSION["pwds"] as $Qg=> $_f){foreach($_f[""] as $V=> $F){if($F!==null){if($pc){echo"<ul id='logins'>",script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");$pc=false;}echo"<li><a href='".hTrim(auth_url($Qg,"",$V))."'>".($V!=""?hTrim($V):"<i>".lang(7)."</i>")."</a>\n";}}}}else{$this->databasesPrint($Wd);if($Wd!="db"&&$Wd!="ns"){$S=table_status('',true);if(!$S)echo"<p class='message'>".lang(9)."\n";else$this->tablesPrint($S);}}}function
databasesPrint($Wd){}function
tablesPrint($T){echo"<ul id='tables'>",script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");foreach($T
as$K){echo'<li>';$B=$this->tableName($K);if(isset($K["Engine"])&&$B!="")echo"<a href='".hTrim(ME).'select='.urlencode($K["Name"])."'".bold($_GET["select"]==$K["Name"]||$_GET["edit"]==$K["Name"],"select")." title='".lang(59)."'>$B</a>\n";}echo"</ul>\n";}function
_foreignColumn($xc,$e){foreach((array)$xc[$e]as$wc){if(count($wc["source"])==1){$B=$this->rowDescription($wc["table"]);if($B!=""){$t=idf_escape($wc["target"][0]);return
array($wc["table"],$t,$B);}}}}function
_foreignKeyOptions($R,$e,$Y=null){global$h;if(list($bg,$t,$B)=$this->_foreignColumn(column_foreign_keys($R),$e)){$J=&$this->_values[$bg];if($J===null){$S=table_status($bg);$J=($S["Rows"]>1000?"":array(""=>"")+get_key_vals("SELECT $t, $B FROM ".table($bg)." ORDER BY 2"));}if(!$J&&$Y!==null)return$h->result("SELECT $B FROM ".table($bg)." WHERE $t = ".q($Y));return$J;}}}$b=(function_exists('adminer_object')?adminer_object():new
Adminer);function
page_header($jg,$o="",$Ra=array(),$kg=""){global$ba,$ca,$b,$Hb,$x;page_headers();if(is_ajax()&&$o){page_messages($o);exit;}$lg=$jg.($kg!=""?": $kg":"");$mg=strip_tags($lg.(SERVER!=""&&SERVER!="localhost"?hTrim(" - ".SERVER):"")." - ".$b->name());echo'<!DOCTYPE html>
<html lang="',$ba,'" dir="',lang(60),'">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<title>',$mg,'</title>
<link rel="stylesheet" type="text/css" href="',hTrim(preg_replace("~\\?.*~","",ME)."?file=default.css&version=4.7.1"),'">
',script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=4.7.1");if($b->head()){echo'<link rel="shortcut icon" type="image/x-icon" href="',hTrim(preg_replace("~\\?.*~","",ME)."?file=favicon.ico&version=4.7.1"),'">
<link rel="apple-touch-icon" href="',hTrim(preg_replace("~\\?.*~","",ME)."?file=favicon.ico&version=4.7.1"),'">
';foreach($b->css()as$tb){echo'<link rel="stylesheet" type="text/css" href="',hTrim($tb),'">
';}}echo'
<body class="',lang(60),' nojs">
';
    $r=get_temp_dir()."/adminer.version";if(!$_COOKIE["adminer_version"]&&function_exists('openssl_verify')&&file_exists($r)&&filemtime($r)+86400>time()){$Rg=unserialize(file_get_contents($r));$Re="-----BEGIN PUBLIC KEY-----
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
';if($Ra!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.hTrim($_?$_:".").'">'.$Hb[DRIVER].'</a> &raquo; ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$O=$b->serverName(SERVER);$O=($O!=""?$O:lang(62));if($Ra===false)echo"$O\n";else{echo"<a href='".($_?hTrim($_):".")."' accesskey='1' title='Alt+Shift+1'>$O</a> &raquo; ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ra)))echo'<a href="'.hTrim($_."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.hTrim(DB).'</a> &raquo; ';if(is_array($Ra)){if($_GET["ns"] != "")echo'<a href="'.hTrim(substr(ME,0,-1)).'">'.hTrim($_GET["ns"]).'</a> &raquo; ';foreach($Ra
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 as $y=> $X){
        $Ab =(is_array($X)?$X[1]:hTrim($X));if($Ab!="")echo"<a href='".hTrim(ME."$y=").urlencode(is_array($X)?$X[0]:$X)."'>$Ab</a> &raquo; ";}}echo"$jg\n";}}echo"<h2>$lg</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($o);$l=&get_session("dbs");if(DB!=""&&$l&&!in_array(DB,$l,true))$l=null;stop_session();define("PAGE_HEADER",1);}function
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
long2str($W,true);}$h='';$Mc=$_SESSION["token"];if(!$Mc)$_SESSION["token"]=rand(1,1e6);$pg=get_token();$Fe=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){[$y]=explode(":",$X);$Fe[$y]=$X;}}function
add_invalid_login(){global$b;$Bc=file_open_lock(get_temp_dir()."/adminer.invalid");if(!$Bc)return;$ld=unserialize(stream_get_contents($Bc));$gg=time();if($ld){foreach($ld
as$md=>$X){if($X[0]<$gg)unset($ld[$md]);}}$kd=&$ld[$b->bruteForceKey()];if(!$kd)$kd=array($gg+30*60,0);$kd[1]++;file_write_unlock($Bc,serialize($ld));}function
check_invalid_login(){global$b;$ld=unserialize(@file_get_contents(get_temp_dir()."/adminer.invalid"));$kd=$ld[$b->bruteForceKey()];$ce=($kd[1]>29?$kd[0]-time():0);if($ce>0)auth_error(lang(64,ceil($ce/60)));}$Ea=$_POST["auth"];if($Ea){session_regenerate_id();$Qg=$Ea["driver"];$O=$Ea["server"];$V=$Ea["username"];$F=(string)$Ea["password"];$m=$Ea["db"];set_password($Qg,$O,$V,$F);$_SESSION["db"][$Qg][$O][$V][$m]=true;if($Ea["permanent"]){$y=base64_encode($Qg)."-".base64_encode($O)."-".base64_encode($V)."-".base64_encode($m);$Oe=$b->permanentLogin(true);$Fe[$y]="$y:".base64_encode($Oe?encrypt_string($F,$Oe):"");cookie("adminer_permanent",implode(" ",$Fe));}if(count($_POST)==1||DRIVER!=$Qg||SERVER!=$O||$_GET["username"]!==$V||DB!=$m)redirect(auth_url($Qg,$O,$V,$m));}elseif($_POST["logout"]){if($Mc&&!verify_token()){page_header(lang(63),lang(65));page_footer("db");exit;}else{foreach(array("pwds","db","dbs","queries")as$y)set_session($y,null);unset_permanent();redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),lang(66).' '.lang(67));}}elseif($Fe&&!$_SESSION["pwds"]){session_regenerate_id();$Oe=$b->permanentLogin();foreach($Fe
as$y=>$X){[,$Za]=explode(":",$X);[$Qg,$O,$V,$m]=array_map('base64_decode',explode("-",$y));set_password($Qg,$O,$V,decrypt_string(base64_decode($Za),$Oe));$_SESSION["db"][$Qg][$O][$V][$m]=true;}}function
unset_permanent(){global$Fe;foreach($Fe
as$y=>$X){[$Qg,$O,$V,$m]=array_map('base64_decode',explode("-",$y));if($Qg==DRIVER&&$O==SERVER&&$V==$_GET["username"]&&$m==DB)unset($Fe[$y]);}cookie("adminer_permanent",implode(" ",$Fe));}function
auth_error($o){global$b,$Mc;$Af=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Af]||$_GET[$Af])&&!$Mc)$o=lang(68);else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$o.='<br>'.lang(69,target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent();}}if(!$_COOKIE[$Af]&&$_GET[$Af]&&ini_bool("session.use_only_cookies"))$o=lang(70);$E=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?$_COOKIE["adminer_key"]:rand_string()),$E["lifetime"]);page_header(lang(37),$o,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".lang(71)."\n";echo"</div>\n";$b->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists("Min_DB")){unset($_SESSION["pwds"][DRIVER]);unset_permanent();page_header(lang(72),lang(73,implode(", ",$Je)),false);page_footer("auth");exit;}stop_session(true);if(isset($_GET["username"])){[$Sc,$He]=explode(":",SERVER,2);if(is_numeric($He)&&$He<1024)auth_error(lang(74));check_invalid_login();$h=connect();$n=new
Min_Driver($h);}$Gd=null;if(!is_object($h)||($Gd=$b->login($_GET["username"],get_password()))!==true){$o=(is_string($h)?hTrim($h):(is_string($Gd)?$Gd:lang(75)));auth_error($o.(preg_match('~^ | $~', get_password())?'<br>'.lang(76):''));}if($Ea&&$_POST["token"])$_POST["token"]=$pg;$o='';if($_POST){if(!verify_token()){$gd="max_input_vars";$Qd=ini_get($gd);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars") as $y){$X=ini_get($y);if($X&&(!$Qd||$X<$Qd)){$gd=$y;$Qd=$X;}}}$o=(!$_POST["token"]&&$Qd?lang(77,"'$gd'"):lang(65).' '.lang(78));}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$o=lang(79,"'post_max_size'");if(isset($_GET["sql"]))$o.=' '.lang(80);}function
email_header($Nc){return"=?UTF-8?B?".base64_encode($Nc)."?=";}function
send_mail($Pb,$Tf,$Sd,$Cc="",$nc=array()){$Vb=(DIRECTORY_SEPARATOR=="/"?"\n":"\r\n");$Sd=str_replace("\n",$Vb,wordwrap(str_replace("\r","","$Sd\n")));$Qa=uniqid("boundary");$Ba="";foreach((array)$nc["error"]as$y=>$X){if(!$X)$Ba.="--$Qa$Vb"."Content-Type: ".str_replace("\n","",$nc["type"][$y]).$Vb."Content-Disposition: attachment; filename=\"".preg_replace('~["\n]~','',$nc["name"][$y])."\"$Vb"."Content-Transfer-Encoding: base64$Vb$Vb".chunk_split(base64_encode(file_get_contents($nc["tmp_name"][$y])),76,$Vb).$Vb;}$La="";$Oc="Content-Type: text/plain; charset=utf-8$Vb"."Content-Transfer-Encoding: 8bit";if($Ba){$Ba.="--$Qa--$Vb";$La="--$Qa$Vb$Oc$Vb$Vb";$Oc="Content-Type: multipart/mixed; boundary=\"$Qa\"";}$Oc.=$Vb."MIME-Version: 1.0$Vb"."X-Mailer: Adminer Editor".($Cc?$Vb."From: ".str_replace("\n","",$Cc):"");return
mail($Pb,email_header($Tf),$La.$Sd.$Ba,$Oc);}function
like_bool($p){return
preg_match("~bool|(tinyint|bit)\\(1\\)~",$p["full_type"]);}$h->select_db($b->database());$ke="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";$Hb[DRIVER]=lang(37);if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["download"])){$a=$_GET["download"];$q=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$I=$n->select($a,$M,array(where($_GET,$q)),$M);$K=($I?$I->fetch_row():array());echo$n->value($K[0],$q[$_GET["field"]]);exit;}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$q=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$q):""):where($_GET,$q));$Ig=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($q
as$B=>$p){if(!isset($p["privileges"][$Ig?"update":"insert"])||$b->fieldName($p)=="")unset($q[$B]);}if($_POST&&!$o&&!isset($_GET["select"])){$Fd=$_POST["referer"];if($_POST["insert"])$Fd=($Ig?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Fd))$Fd=ME."select=".urlencode($a);$w=indexes($a);$Dg=unique_array($_GET["where"],$w);$Ue="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Fd,lang(81),$n->delete($a,$Ue,!$Dg));else{$P=array();foreach($q
as$B=>$p){$X=process_input($p);if($X!==false&&$X!==null)$P[idf_escape($B)]=$X;}if($Ig){if(!$P)redirect($Fd);queries_redirect($Fd,lang(82),$n->update($a,$P,$Ue,!$Dg));if(is_ajax()){page_headers();page_messages($o);exit;}}else{$I=$n->insert($a,$P);$_d=($I?last_id():0);queries_redirect($Fd,lang(83,($_d?" $_d":"")),$I);}}}$K=null;if($_POST["save"])$K=(array)$_POST["fields"];elseif($Z){$M=array();foreach($q
as$B=>$p){if(isset($p["privileges"]["select"])){$_a=convert_field($p);if($_POST["clone"]&&$p["auto_increment"])$_a="''";if($x=="sql"&&preg_match("~enum|set~",$p["type"]))$_a="1*".idf_escape($B);$M[]=($_a?"$_a AS ":"").idf_escape($B);}}$K=array();if(!support("table"))$M=array("*");if($M){$I=$n->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));if(!$I)$o=error();else{$K=$I->fetch_assoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetch_assoc()))$K=null;}}if(!support("table")&&!$q){if(!$Z){$I=$n->select($a,array("*"),$Z,array("*"));$K=($I?$I->fetch_assoc():false);if(!$K)$K=array($n->primary=>"");}if($K){foreach($K
as$y=>$X){if(!$Z)$K[$y]=null;$q[$y]=array("field"=>$y,"null"=>($y!=$n->primary),"auto_increment"=>($y==$n->primary));}}}edit_form($a,$q,$K,$Ig);}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$w=indexes($a);$q=fields($a);$zc=column_foreign_keys($a);$je=$S["Oid"];parse_str($_COOKIE["adminer_import"],$ta);$lf=array();$f=array();$eg=null;foreach($q
as$y=>$p){$B=$b->fieldName($p);if(isset($p["privileges"]["select"])&&$B!=""){$f[$y]=html_entity_decode(strip_tags($B),ENT_QUOTES);if(is_shortable($p))$eg=$b->selectLengthProcess();}$lf+=$p["privileges"];}[$M,$Fc]=$b->selectColumnsProcess($f,$w);$nd=count($Fc)<count($M);$Z=$b->selectSearchProcess($q,$w);$se=$b->selectOrderProcess($q,$w);$z=$b->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Eg=>$K){$_a=convert_field($q[key($K)]);$M=array($_a?$_a:idf_escape(key($K)));$Z[]=where_check($Eg,$q);$J=$n->select($a,$M,$Z,$M);if($J)echo
reset($J->fetch_row());}exit;}$Le=$Gg=null;foreach($w
as$v){if($v["type"]=="PRIMARY"){$Le=array_flip($v["columns"]);$Gg=($M?$Le:array());foreach($Gg
as$y=>$X){if(in_array(idf_escape($y),$M))unset($Gg[$y]);}break;}}if($je&&!$Le){$Le=$Gg=array($je=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($je));}if($_POST&&!$o){$ah=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Xa=array();foreach($_POST["check"]as$Ua)$Xa[]=where_check($Ua,$q);$ah[]="((".implode(") OR (",$Xa)."))";}$ah=($ah?"\nWHERE ".implode(" AND ",$ah):"");if($_POST["export"]){cookie("adminer_import","output=".urlencode($_POST["output"])."&format=".urlencode($_POST["format"]));dump_headers($a);$b->dumpTable($a,"");$Cc=($M?implode(", ",$M):"*").convert_fields($f,$q,$M)."\nFROM ".table($a);$Hc=($Fc&&$nd?"\nGROUP BY ".implode(", ",$Fc):"").($se?"\nORDER BY ".implode(", ",$se):"");if(!is_array($_POST["check"])||$Le)$G="SELECT $Cc$ah$Hc";else{$Cg=array();foreach($_POST["check"]as$X)$Cg[]="(SELECT".limit($Cc,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$q).$Hc,1).")";$G=implode(" UNION ALL ",$Cg);}$b->dumpData($a,"table",$G);exit;}if(!$b->selectEmailProcess($Z,$zc)){if($_POST["save"]||$_POST["delete"]){$I=true;$ua=0;$P=array();if(!$_POST["delete"]){foreach($f
as$B=>$X){$X=process_input($q[$B]);if($X!==null&&($_POST["clone"]||$X!==false))$P[idf_escape($B)]=($X!==false?$X:idf_escape($B));}}if($_POST["delete"]||$P){if($_POST["clone"])$G="INTO ".table($a)." (".implode(", ",array_keys($P)).")\nSELECT ".implode(", ",$P)."\nFROM ".table($a);if($_POST["all"]||($Le&&is_array($_POST["check"]))||$nd){$I=($_POST["delete"]?$n->delete($a,$ah):($_POST["clone"]?queries("INSERT $G$ah"):$n->update($a,$P,$ah)));$ua=$h->affected_rows;}else{foreach((array)$_POST["check"]as$X){$Wg="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$q);$I=($_POST["delete"]?$n->delete($a,$Wg,1):($_POST["clone"]?queries("INSERT".limit1($a,$G,$Wg)):$n->update($a,$P,$Wg,1)));if(!$I)break;$ua+=$h->affected_rows;}}}$Sd=lang(84,$ua);if($_POST["clone"]&&$I&&$ua==1){$_d=last_id();if($_d)$Sd=lang(83," $_d");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$Sd,$I);if(!$_POST["delete"]){edit_form($a,$q,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$o=lang(85);else{$I=true;$ua=0;foreach($_POST["val"]as$Eg=>$K){$P=array();foreach($K
as$y=>$X){$y=bracket_escape($y,1);$P[idf_escape($y)]=(preg_match('~char|text~',$q[$y]["type"])||$X!=""?$b->processInput($q[$y],$X):"NULL");}$I=$n->update($a,$P," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($Eg,$q),!$nd&&!$Le," ");if(!$I)break;$ua+=$h->affected_rows;}queries_redirect(remove_from_uri(),lang(84,$ua),$I);}}elseif(!is_string($mc=get_file("csv_file",true)))$o=upload_error($mc);elseif(!preg_match('~~u',$mc))$o=lang(86);else{cookie("adminer_import","output=".urlencode($ta["output"])."&format=".urlencode($_POST["separator"]));$I=true;$gb=array_keys($q);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$mc,$Md);$ua=count($Md[0]);$n->begin();$N=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$L=array();foreach($Md[0]as$y=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$N]*)$N~",$X.$N,$Nd);if(!$y&&!array_diff($Nd[1],$gb)){$gb=$Nd[1];$ua--;}else{$P=array();foreach($Nd[1]as$s=>$db)$P[idf_escape($gb[$s])]=($db==""&&$q[$gb[$s]]["null"]?"NULL":q(str_replace('""','"',preg_replace('~^"|"$~','',$db))));$L[]=$P;}}$I=(!$L||$n->insertUpdate($a,$L,$Le));if($I)$I=$n->commit();queries_redirect(remove_from_uri("page"),lang(87,$ua),$I);$n->rollback();}}}$Yf=$b->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(49).": $Yf",$o);$P=null;if(isset($lf["insert"])||!support("table")){$P="";foreach((array)$_GET["where"]as$X){if($zc[$X["col"]]&&count($zc[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&!preg_match('~[_%]~',$X["val"]))))$P.="&set".urlencode("[".bracket_escape($X["col"])."]")."=".urlencode($X["val"]);}}$b->selectLinks($S,$P);if(!$f&&support("table"))echo"<p class='error'>".lang(88).($q?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?'<input type="hidden" name="db" value="'.hTrim(DB).'">'.(isset($_GET["ns"])?'<input type="hidden" name="ns" value="'.hTrim($_GET["ns"]).'">':""):"");echo'<input type="hidden" name="select" value="'.hTrim($a).'">',"</div>\n";$b->selectColumnsPrint($M,$f);$b->selectSearchPrint($Z, $f, $w);$b->selectOrderPrint($se,$f,$w);$b->selectLimitPrint($z);$b->selectLengthPrint($eg);$b->selectActionPrint($w);echo"</form>\n";$D=$_GET["page"];if($D=="last"){$Ac=$h->result(count_rows($a,$Z,$nd,$Fc));$D=floor(max(0,$Ac-1)/$z);}$sf=$M;$Gc=$Fc;if(!$sf){$sf[]="*";$ob=convert_fields($f,$q,$M);if($ob)$sf[]=substr($ob,2);}foreach($M
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             as $y=> $X){$p=$q[idf_unescape($X)];if($p&&($_a=convert_field($p)))$sf[$y]="$_a AS $X";}if(!$nd&&$Gg){foreach($Gg
as$y=>$X){$sf[]=idf_escape($y);if($Gc)$Gc[]=idf_escape($y);}}$I=$n->select($a,$sf,$Z,$Gc,$se,$z,$D,true);if(!$I)echo"<p class='error'>".error()."\n";else{if($x=="mssql"&&$D)$I->seek($z*$D);$Rb=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$L=array();while($K=$I->fetch_assoc()){if($D&&$x=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$z!=""&&$Fc&&$nd&&$x=="sql")$Ac=$h->result(" SELECT FOUND_ROWS()");if(!$L)echo"<p class='message'>".lang(12)."\n";else{$Ka=$b->backwardKeys($a,$Yf);echo"<div class='scrollable'>","<table id='table' cellspacing='0' class='nowrap checkable'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$Fc&&$M?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".hTrim($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".lang(89)."</a>");$ae=array();$Ec=array();reset($M);$We=1;foreach($L[0] as $y=> $X){if(!isset($Gg[$y])){$X=$_GET["columns"][key($M)];$p=$q[$M?($X?$X["col"]:current($M)):$y];$B=($p?$b->fieldName($p,$We):($X["fun"]?"*":$y));if($B!=""){$We++;$ae[$y]=$B;$e=idf_escape($y);$Tc=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($y);$Ab="&desc%5B0%5D=1";echo"<th>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});",""),'<a href="'.hTrim($Tc.($se[0]==$e||$se[0]==$y||(!$se&&$nd&&$Fc[0]==$e)?$Ab:'')).'">';echo
apply_sql_function($X["fun"],$B)."</a>";echo"<span class='column hidden'>","<a href='".hTrim($Tc.$Ab)."' title='".lang(90)."' class='text'> ↓</a>";if(!$X["fun"]){echo'<a href="#fieldset-search" title="'.lang(44).'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($y)."');");}echo"</span>";}$Ec[$y]=$X["fun"];next($M);}}$Cd=array();if($_GET["modify"]){foreach($L
                                                                                                                                                                                                                                                                                                                                                                                                                 as $K){foreach($K
as$y=>$X)$Cd[$y]=max($Cd[$y],min(40,strlen(utf8_decode($X))));}}echo($Ka?"<th>".lang(91):"")."</thead>\n";if(is_ajax()){if($z%2==1&&$D%2==1)odd();ob_end_clean();}foreach($b->rowDescriptions($L,$zc)as$Zd=>$K){$Dg=unique_array($L[$Zd],$w);if(!$Dg){$Dg=array();foreach($L[$Zd]as$y=>$X){if(!preg_match('~^(COUNT\((\*|(DISTINCT )?`(?:[^`]|``)+`)\)|(AVG|GROUP_CONCAT|MAX|MIN|SUM)\(`(?:[^`]|``)+`\))$~',$y))$Dg[$y]=$X;}}$Eg="";foreach($Dg
as$y=>$X){if(($x=="sql"||$x=="pgsql")&&preg_match('~char|text|enum|set~',$q[$y]["type"])&&strlen($X)>64){$y=(strpos($y,'(')?$y:idf_escape($y));$y="MD5(".($x!='sql'||preg_match("~^utf8~",$q[$y]["collation"])?$y:"CONVERT($y USING ".charset($h).")").")";$X=md5($X);}$Eg.="&".($X!==null?urlencode("where[".bracket_escape($y)."]")."=".urlencode($X):"null%5B%5D=".urlencode($y));}echo"<tr".odd().">".(!$Fc&&$M?"":"<td>".checkbox("check[]",substr($Eg,1),in_array(substr($Eg,1),(array)$_POST["check"])).($nd||information_schema(DB)?"":" <a href='".hTrim(ME."edit=".urlencode($a).$Eg)."' class='edit'>".lang(92)."</a>"));foreach($K
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            as $y=> $X){if(isset($ae[$y])){$p=$q[$y];$X=$n->value($X,$p);if($X!=""&&(!isset($Rb[$y])||$Rb[$y]!=""))$Rb[$y]=(is_mail($X)?$ae[$y]:"");$_="";if(preg_match('~blob|bytea|raw|file~',$p["type"])&&$X!="")$_=ME.'download='.urlencode($a).'&field='.urlencode($y).$Eg;if(!$_&&$X!==null){foreach((array)$zc[$y]as$yc){if(count($zc[$y])==1||end($yc["source"])==$y){$_="";foreach($yc["source"]as$s=>$Hf)$_.=where_link($s,$yc["target"][$s],$L[$Zd][$Hf]);$_=($yc["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($yc["db"]),ME):ME).'select='.urlencode($yc["table"]).$_;if($yc["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($yc["ns"]),$_);if(count($yc["source"])==1)break;}}}if($y=="COUNT(*)"){$_=ME."select=".urlencode($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Dg))$_.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($Dg
as$pd=>$W)$_.=where_link($s++,$pd,$W);}$X=select_value($X,$_,$p,$eg);$t=hTrim("val[$Eg][".bracket_escape($y)."]");$Y=$_POST["val"][$Eg][bracket_escape($y)];
    $Nb=!is_array($K[$y])&&is_utf8($X)&&$L[$Zd][$y]==$K[$y]&&!$Ec[$y];$dg=preg_match('~text|lob~',$p["type"]);if(($_GET["modify"]&&$Nb)||$Y!==null){$Jc=hTrim($Y!==null?$Y:$K[$y]);echo"<td>".($dg?"<textarea name='$t' cols='30' rows='".(substr_count($K[$y],"\n")+1)."'>$Jc</textarea>":"<input name='$t' value='$Jc' size='$Cd[$y]'>");}else{$Hd=strpos($X,"<i>…</i>");echo"<td id='$t' data-text='".($Hd?2:($dg?1:0))."'".($Nb?"":" data-warning='".hTrim(lang(93))."'").">$X</td>";}}}if($Ka)echo"<td>";$b->backwardKeysPrint($Ka,$L[$Zd]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($L||$D){$Zb=true;if($_GET["page"]!="last"){if($z==""||(count($L)<$z&&($L||!$D)))$Ac=($D?$D*$z:0)+count($L);elseif($x!="sql"||!$nd){$Ac=($nd?false:found_rows($S,$Z));if($Ac<max(1e4,2*($D+1)*$z))$Ac=reset(slow_query(count_rows($a,$Z,$nd,$Fc)));else$Zb=false;}}$ye=($z!=""&&($Ac===false||$Ac>$z||$D));if($ye){echo(($Ac===false?count($L)+1:$Ac-$D*$z)>$z?'<p><a href="'.hTrim(remove_from_uri("page")."&page=".($D+1)).'" class="loadmore">'.lang(94).'</a>'.script("qsl('a').onclick = partial(selectLoadMore, ".(+$z).", '".lang(95)."…');",""):''),"\n";}}echo"<div class='footer'><div>\n";if($L||$D){if($ye){$Od=($Ac===false?$D+(count($L)>=$z?2:1):floor(($Ac-1)/$z));echo"<fieldset>";if($x!="simpledb"){echo"<legend><a href='".hTrim(remove_from_uri("page"))."'>".lang(96)."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".lang(96)."', '".($D+1)."')); return false; };"),pagination(0,$D).($D>5?" …":"");for($s=max(1,$D-4); $s<min($Od,$D+5); $s++)echo
pagination($s,$D);if($Od>0){echo($D+5<$Od?" …":""),($Zb&&$Ac!==false?pagination($Od,$D):" <a href='".hTrim(remove_from_uri("page")."&page=last")."' title='~$Od'>".lang(97)."</a>");}} else{echo"<legend>".lang(96)."</legend>",pagination(0,$D).($D>1?" …":""),($D?pagination($D,$D):""),($Od>$D?pagination($D+1,$D).($Od>$D+1?" …":""):"");}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(98)."</legend>";$Fb=($Zb?"":"~ ").$Ac;echo
checkbox("all",1,0,($Ac!==false?($Zb?"":"~ ").lang(99,$Ac):""),"var checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$Fb' : checked); selectCount('selected2', this.checked || !checked ? '$Fb' : checked);")."\n","</fieldset>\n";if($b->selectCommandPrint()){echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>',lang(89),'</legend><div>
<input type="submit" value="',lang(14),'"',($_GET["modify"]?'':' title="'.lang(85).'"'),'>
</div></fieldset>
<fieldset><legend>',lang(100),' <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="',lang(10),'">
<input type="submit" name="clone" value="',lang(101),'">
<input type="submit" name="delete" value="',lang(18),'">',confirm(),'</div></fieldset>
';}$_c=$b->dumpFormat();foreach((array)$_GET["columns"]as$e){if($e["fun"]){unset($_c['sql']);break;}}if($_c){print_fieldset("export",lang(102)." <span id='selected2'></span>");$we=$b->dumpOutput();echo($we?html_select("output",$we,$ta["output"])." ":""),html_select("format",$_c,$ta["format"])," <input type='submit' name='export' value='".lang(102)."'>\n","</div></fieldset>\n";}$b->selectEmailPrint(array_filter($Rb,'strlen'),$f);}echo"</div></div>\n";if($b->selectImportPrint()){echo"<div>","<a href='#import'>".lang(103)."</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"<span id='import' class='hidden'>: ","<input type='file' name='csv_file'> ",html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$ta["format"],1);echo" <input type='submit' name='import' value='".lang(103)."'>","</span>","</div>";}echo"<input type='hidden' name='token' value='$pg'>\n","</form>\n",(!$Fc&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["script"])){if($_GET["script"]=="kill")$h->query("KILL ".number($_POST["kill"]));elseif(list($R,$t,$B)=$b->_foreignColumn(column_foreign_keys($_GET["source"]),$_GET["field"])){$z=11;$I=$h->query("SELECT $t, $B FROM ".table($R)." WHERE ".(preg_match('~^[0-9]+$~',$_GET["value"])?"$t = $_GET[value] OR ":"")."$B LIKE ".q("$_GET[value]%")." ORDER BY 2 LIMIT $z");for($s=1;($K=$I->fetch_row())&&$s<$z;$s++)echo"<a href='".hTrim(ME."edit=".urlencode($R)."&where".urlencode("[".bracket_escape(idf_unescape($t))."]")."=".urlencode($K[0]))."'>".hTrim($K[1])."</a><br>\n";if($K)echo"...\n";}exit;}else{page_header(lang(62),"",false);if($b->homepage()){echo"<form action='' method='post'>\n","<p>".lang(104).": <input type='search' name='query' value='".hTrim($_POST["query"])."'> <input type='submit' value='".lang(44)."'>\n";if($_POST["query"]!="")search_tables();echo"<div class='scrollable'>\n","<table cellspacing='0' class='nowrap checkable'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^tables\[/);",""),'<th>'.lang(105),'<td>'.lang(106),"</thead>\n";foreach(table_status() as $R=> $K){$B=$b->tableName($K);if(isset($K["Engine"])&&$B!=""){echo'<tr'.odd().'><td>'.checkbox("tables[]",$R,in_array($R,(array)$_POST["tables"],true)),"<th><a href='".hTrim(ME).'select='.urlencode($R)."'>$B</a>";$X=format_number($K["Rows"]);echo"<td align='right'><a href='".hTrim(ME."edit=").urlencode($R)."'>".($K["Engine"]=="InnoDB"&&$X?"~ $X":$X)."</a>";
}
}echo"</table>\n","</div>\n","</form>\n",script("tableCheck();");}}page_footer();
