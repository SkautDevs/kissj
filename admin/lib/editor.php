<?php
/** Adminer Editor - Compact database editor
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2009 Jakub Vrana
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 4.3.1
*/error_reporting(6135);$gc=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($gc||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$Wf=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($Wf)$$X=$Wf;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");if(isset($_GET["file"])){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");if($_GET["file"]=="favicon.ico"){header("Content-Type: image/x-icon");echo
lzw_decompress("\0\0\0` \0„\0\n @\0´C„è\"\0`EãQ¸àÿ‡?ÀtvM'”JdÁd\\Œb0\0Ä\"™ÀfÓˆ¤îs5›ÏçÑAXPaJ“0„¥‘8„#RŠT©‘z`ˆ#.©ÇcíXÃşÈ€?À-\0¡Im? .«M¶€\0È¯(Ì‰ıÀ/(%Œ\0");}elseif($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("\n1Ì‡“ÙŒŞl7œ‡B1„4vb0˜Ífs‘¼ên2BÌÑ±Ù˜Şn:‡#(¼b.\rDc)ÈÈa7E„‘¤Âl¦Ã±”èi1Ìs˜´ç-4™‡fÓ	ÈÎi7†³é†„ŒFÃ©”vt2‚Ó!–r0Ïãã£t~½U'3M€ÉW„B¦'cÍPÂ:6T\rc£A¾zr_îWK¶\r-¼VNFS%~Ãc²Ùí&›\\^ÊrÀ›­æu‚ÅÃôÙ‹4'7k¶è¯ÂãQÔæhš'g\rFB\ryT7SS¥PĞ1=Ç¤cIèÊ:d”ºm>£S8L†Jœt.M¢Š	Ï‹`'C¡¼ÛĞ889¤È QØıŒî2#8Ğ­£’˜6mú²†ğjˆ¢h«<…Œ°«Œ9/ë˜ç:Jê)Ê‚¤\0d>!\0Z‡ˆvì»në¾ğ¼o(Úó¥ÉkÔ7½sàù>Œî†!ĞR\"*nSı\0@P\"Áè’(‹#[¶¥£@g¹oü­’znş9k¤8†nš™ª1´I*ˆô=Ín²¤ª¸è0«c(ö;¾Ã Ğè!°üë*cì÷>Î¬E7DñLJ© 1ÊJ=ÓÚŞ1L‚û?Ğs=#`Ê3\$4ì€úÈuÈ±ÌÎzGÑC YAt«?;×QÒk&ÇïYP¿uèåÇ¯}UaHV%G;ƒs¼”<A\0\\¼ÔPÑ\\Âœ&ÂªóV¦ğ\n£SUÃtíÅÇrŒêˆÆ2¤	l^íZ6˜ej…Á­³A·dó[İsÕ¶ˆJP”ªÊóˆÒŒŠ8è=»ƒ˜à6#Ë‚74*óŸ¨#eÈÀŞ!Õ7{Æ6“¿<oÍCª9v[–MôÅ-`Óõkö>lÙÚ´‹åIªƒHÚ3xú€›äw0t6¾Ã%MR%³½jhÚB˜<´\0ÉAQ<P<:šãu/¤;\\> Ë-¹„ÊˆÍÁQH\nv¡L+vÖÃ¦ì<ï\rèåvàöî¹\\* àÉçÓ´İ¢gŒnË©¸¹TĞ©2P•\r¨øß‹\"+z 8£ ¶:#€ÊèÃÎ2‹ºJ[i—‚£¨;z˜ûÑô¡rÊ3#¨Ù‰ :ãní\rã½ƒeÙpdİİ è2cˆê4²k¿Š£\rG•æE6_³¢ú=î·SZUÇ·ãŒO—ğÅ?¡éÃ¾27£cİĞÅhnÆ‹Üùu3…E>\$J[Áq[\räIŠ6.ÆJÑ\"EPrèGÌŠGA İW¡³\rº´6Ík†¢½`.-¡ªB2>#ìhØÀˆXµøu\r¡¸=‡Z  b€Å(¡â•ƒ!JZÈ”uªyO’×Z¥M˜Õ6lM[0©ä–€àß!ImñyÂ+pÉ#ag¡ŞŒvW˜:qp\"4ÅôòŸãheî…0 dÆAq-\"¡Êƒ§ÆÂ\"2ßÍÒ@‡)o‘,,”¤”×Rb`@©B@ĞÊÊ¯¤Q\n†èŠ·˜Z§„Â™=(r~‰l©~¯ÄhˆsAllÖ\n7»!1! Ü#é\0KË…A“LH(½!ÔÊ˜agH\0ÄT\ni˜/È\$ôöœ4GaÎIÉ!¸.—Å˜5§ÅM\rÑ2‘‚Ï	Ù;ƒ,öLIJ†äÃd?“ÒºÅí%Õˆ:çN@b.âª2í5’«ôt:FAw²B£E,Ç-\$ù£'ê:Ó©u©?¨tK;kÍàĞ¸¨ä\0ouMD)k_Ph˜Ó5MC}7‚…È2‡w.QB¦8)ìÀ†8(DIù=©éy`Øed\0s,`É•jŒHÄ\"(b³¢Ä\\ÙÖnl’\"Ù‚^Ëì€­eE½\nèáë±X!SqXÔÀ\r©Œ€7A±†0ê£y7pPìºğçaüA˜4‡ƒ(yÖJwm…2…òª.¯ó‰†¬fp°ÏË;Æ„5ÂJÍcÜqŒQz\\\0[Hÿ 3‘f'b¼µFğøÆY¨\nAà9_§IŞà(›fÎÓq‘VÑÅ¨äõ³4µÜò¹‚„RIÂYå&J’ºFñ}£{FTëh9[7‚h\0à‹TÖ^ö´jËÔq×j‹õÕ”§­€cÂWIğ@`_ÑsVDçÃ[¾\"{1áÈ3‡•	Úô»÷¨<…l¼l.±éĞ[¨»Ş#Ä¯º¤b°Şu­¶/Ÿ\0ä3ævaå«‘Dp>‚2½IDWÕš¢kKAŒ»hHš]¨FÆ•ã€W–!]‰Ê÷ltÜÉ•RÌ­4L[äĞÅYC cTj<c;s‡q¸p€’ Ä5ÅtóJ§m6—%J”-\\õÍeB=iß-ğ*%´·¦÷¢TV‹[&M8ó*\r™bÄY\rihˆ	„ÙPŒ9T×-VÉ°ZÔúüÛ³ù49Î²™”ƒp-´`ÙÿÜÌÇGÉÙ›' ì¹ĞôM²:§Å™')0ƒYuÚcí:!«x#×¦è¦-l*®TÉ\nYläù†š³‹*D ÉXë V\\îËØÚ®ó]y¯ƒ\nÖ2r,É†åç,ÎdĞ×~Å³İ÷s³-ç+Ö»uÛ]£\\BÀ¶¥²Iw€Ô!ƒOsØÔ¯lò YCÁĞ‚È:À@ÆœEUË._)Ë9uÿzœµvÏˆSÎ´¬1ï—é_(Sõéqé½¡r¾yuî+¥Z*ê6€uy¿<ÉÇõz\\|ØZK;áe›×–úoYåÀ;°óÃl‘´xöà-7×ô÷4rkYY?ÔÕGWt¡¼÷[KÚšÃåzoØ<¿€Íà	têÏô†¶¾ù—É€gçıjğ‡_!ào…êÊ\$ Iã¹ÀI¿.&Ü5½P\\—›]¥Àè†Æ\nCØ.ïÖ_¹ø;¿çs«iíS/gÖ:ŞPëÉ³auNÍ¨|Æaáå¯á™º¬±¢µÓâ«6ØÓÙ3Ö|÷¾‡Ä{©ceîXòù<°e«p>Ní}´í~âÿO¾¡÷Ò™Bl¿ÂjÊ/¢óKø¼Hdch-Ë¾ıºØšğ/ûîÜşÎäùÈ¶·hÔ0ÀĞŒÈĞ‹ÌúÎH©8 ");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("f:›ŒgCI¼Ü\n0›†S‘Øa9œÅS`°Çˆ“Œ&Ó(°Ên0˜†QIìÒf‰›\$±At^ sG²Étf6eŒ§yŒÊ()LäSÁÀP'…ÂáÌR'Ífq]\"˜s>	)â‘`œH2ŠEq9ˆÊ?ˆ*)‰”t'°Ï§Ø\n	\ræs<ŒPi2INÆ*(=2ÌgXá¸è.3™N„Y4èB<’L—üîi©Ì¥2İ´z=š0HøĞ'·êŒšÃuÆtt:œÂ¡Èêe¹]`pX9ŒŞo5šgòóIœÜ,2O4ãŞÑ…MÆS¸(ˆa…Š#¾Äàç’ïø|¹G‚bèôüxœ^Z[Çä™G¼ÎuTvª(Òm@Vò¸(†¼ÈbN<ŠÈ`æâXä1É+Œä9J8Â2\r£K¶9ğhå	 Áè`…‹ÆëI8ä›±S±ãt÷2ƒ+,£ÆIºã £pæ9m@Ğ:ƒ€æáxï)…ĞüC…Ãxä3…ñ4P7áü-4Çr\"p3Fhà…-5ƒ”U4Í‰¸\\6°ƒ<D\$®l—9ÍR4t7ƒdD3µpŞÎ“kÌ:)\\;° ĞÔğ\r@t…\$4O£<ş†!pdÇÔÚQJ\rÌHî}:&Œ¨ˆÂÈ„Á5YWJ­˜‹±Â`ÓN£èbKNSÉÀÉa§•ƒ´d>2WñÅ…bDj:9[21c„»È€:Xé@ËqË#“›4íL™'J”©+DHeÒ3¬.«O ÇKË°“ˆ…pV…át2Œwp;Æ“…íÿ\r?èOzDq.ª°Ğ-†\"ìZñ®cèX3!/>PúFìsØÉ²±Ã0Í(òóˆ°Ê£€àŒ‚T63sVQo¸€SÎ‘ b²ß…^r\$É@C© r2)©Œ£ “VÀ)+nÜ·zÃÁúålÚè{³K#…À9‹{†Û¯lÀºìmĞQ¨ëh»*É—PÄ:¡c˜]´7ãàø=¡LŸŒi;”2û¿§­ÜÒ<\\Jí¤Øb¥n”…ƒ¥nÁ_iÓ´îJ\n†¢¨âòõC:ª„‘`N4¶Ì–È'Aw:4}ÊÛ£ÁW\080‘ÇL3õÊJ;èiú)\\„=/NŠu=ZV6&ceaè±ÂpŞÖ.[ëvŠtPZŞèX`Ö”õŒ+zú'¦ê9½.\$\$…Ó@\n\ré]_ïÙ®¢Âh¨kk¬Ms>`Ì–ƒj¹%\\9Ğ¶ÆÔ('°jAˆ>BCd\"K\$	CAÆ ä„¤.Â².`‰â.EÑæ´–ÌÃyy\0‹D2Ï8t	Ğ6†Ã8¬FL«´×ŞíâŒB*¬ğ,Ò|\nx\\@ °@¸Ø3r ¬­ğÎWKQb,%…¯´DBfØÈ³D|ÍŒËE0/2>£Y!Ä†'õ™`æf™mHº<BãB0\r*\0Gxò‰nêY4‚¶¾Œ,L²©º–öÅ%SÆ,ıv‡0ê‘–XòQÄ1†HId`‡!.ÔVÊ›H/ÅúÃ—ÀHãù0ÆUÁ¸0Â™©`îLI©8ÖÃkŠ”2Œ4JYNÅ&8xä¥JØk:AKã¡nWØ!¦¿Iï;'ô³\":2ğê‹4Í~óJ„8ô£á’‘¨âG‡™\"MÊ=\rZ'nÇi9F§œ“™rÆ’RÊt‚3\0Ÿ”Ò²Â2µy‚B^òèb'´ÒzÈÉ²(­#”d9Itµ&WØjNa¨ÚC(¥ j”Ä–?h‰ÂØj†¡™©Ö„Z\$0«¡Ò¯´J	A_\n†!TOó4Œ<{aôú?˜æo ú‚-¹–ÃÏ?Hlÿ\"2ƒy™=Úë¨ R©ğœÑ„àš°–ÍŠëP&åG›ÀÁ4ƒË%()¤\r5Mª‚‰ÓLTí\0ÀºxBIç=ltvÄ2Jhvû´~/:èpı×:8\"Ğ´5¡«‰0î#*ì7ªøúÜ\nàq×>è¡G\$°â…):	ƒ»\"ù#ë¦KfI‡!vö+?{¡Íÿ¾Qg¥{ÏR÷Q øCäª}Õ#¸éiIbgà„ÔXàÄÃÂù}ÅË`‹}3—%@îÁ{_kø}0ä±şÈ—Öp !°aï—<7«e•‰ÖF‡?¦¸¡î½XüDù­Ñ, ØÊCk‰ƒíU™ØL>£1‹§ÜÜ‡¥ã‡Œp0#Ä\$²ÅâV)pYs5A˜:°ÊUÈ(9…5×™,F+&Ÿ*{âŒ-£Íìç:÷Ší :7¦ş:Ê™yPãè—´ŠÀXÏ+¤’\nŞI;üş\\s„÷Pà÷1‘‘ìÈr©¦NJËAT'-£”òk?ƒÙY@“¡Ïö±fÇÍbñ’”RîJÏiömÖB~ò©”K\rK«œtª4à÷;OŠKc”9%Hì5àÍd¢3ÙÀe8j¿P÷±[sğ™9,ƒÄ˜—bzK‰µÁòW&e¢d8­ú§)ÄùĞuP°¿¾œ>‘#	P&„ÃP	ƒpbaÀ¨Í¨yñ£æß\$3}ïĞ{»áİhyÊ(ÖdWø±ŠÅÙËĞ_±:°'AØ‚‡PæÃI\"Ù!ï[`ûn8å»i/@ÈäğP	ĞfœĞå†©ˆV	À£•sÑCß8¡˜°Ny‰hÜñtEnAj.-åÄ6£ÀqwJÜ?œÃ¹”AÌhu	è™Ôsé¤AíáO7“·j›æ\n	]¿0›^Œ	ƒ\nYÁš\$„Î–_\rş\$…u*÷Ş¡ÒEx/d¼pdRİdÂõ:¤IÀoDÁ›®‡sQÉ™fàÜI¤öâ8Ñ,óêKÑÏIsM@aq\n/™†ÌM˜R¹ ¢®Cş-aÇa£¾™/·Hº!å4F…óIÿÉÅpÅ”MÏ«Šş_Ø‡HÀ9{‚.´\$WÄûò#{ÌúÒ®Šü·:‰Súƒ£(À'lÕMY»:lÊ¤mD\$°\0¦¬×\0©ê´èº'¢~à¶ Z@º€¶ŒàVâº€L\"ãjnæ¾5€ğNlŠÌşşKšfj&›Mí•OøÓdbÓ°NÓğ´O\$i)ŞNĞ(¿Ğ!P)Ğ0+Ğ6HpN¦¢ØF‚àîĞ–¬?L\nìÁ-h0˜Í,.e­¤\"‡6m#õ	é’ıo&ò°ÒŞë¶gåZÅ@Pşk­&Ìº_ì¼%\\\\'ÀíÌ\0]\$(€5ƒN fqÎ|Ñp¨ `…â<ğîRîÎú úìÍ¸ş˜ìI\0Ñ¥şãL¦|Ç\$ı(Şê¸Å¬TkQ6k°B@0HõŒ˜Pƒ\rÀšˆ#Îušš+ï€Ü²pTşĞZÑ±/ü\r y´Pp%\0^8ÆÒ\r¤Àµ‘˜4¬İ\0‡¡1²¦ğQn*+B8qÂà ÏF§\0Ğ´ÌÜ±Šâ¢\næş«.®âûH’±%Ââ3ñÀ&PF–Ñ„İX¢Ïğ¨`O ±£Ë9R­B´’\r ì10Îì½‡O ¬X«Ì^+¯öïæÌiĞÈArD¬4ëÙ`Ê-š.i`4ò'Å,\$²VÅ,_c~;Bn<’1\$,]%Èlù‰D=âå\$Ñ).1b%gœ»z‰Ğü}ËÊG2¯1]8uPòïìD]	/z îä¼g‘+'„7D\0]ú²¾aäpV’ÒL€ó0÷+`Xpä˜ ÊîdË-hû+h(ÀÔäÀĞ\n„¿²fª“§s2,µ2‡@z Â.I``‡*óÌ1l?±“RËñ±W.ï.c%\$‹¢¿s+4òÑëã6Ã\$Cr‡F)0‚ô\rÓ1-ˆ`„ÓŒ ÈjÆL\r­8–²©l—0È©*.L‡KpÃ\r¤·\r£/rûLÄa8â2KÅ1nêb‚ÿ4“LíÒêË,¶Ë¬¾ïó©\nij–érói#Ç©8»1èbxÓ2à¾\$Nú\re‰ ¨\r\"8ˆ'‘³’³ª\r´-ƒPàÀYñ0°£Yb”S\0¹°ø\\jK+q6V hê1óU€z`pïò¬R±E“CÓî”X»Åöõ%”F	5ñF4f-¶tPåID6\0NFä®Nå4’_Ş0ó©\riL@Ên´¸Pé^‚¬Âîëê¾%'ËLÔ¨êG”€ïôàò\\‘Fèâ€ÊâÄ&ã4ÂĞI*5ÉO†‰OÀ·Pã)8¾)­*L;ğ½4EÌ]´\$óğÌ\0L3ËE ïEk„ÑÃÎIt%eÆ\nbô(ÎëSMª}²Ş7sÇ‹Û¯)gi¤ØFà†&êº-XH° ¼ìğBÃM5~jrPjÌ¾-|Ö¤´9 ¨–p¨¢5Š¸;o–5²õ¶–‰c\ndÒÕuÊ	 ÂÔ\rLÕhú'\nå''ò< O\0ğœe,. ú–\"t\r¯k^Ã{_Ã_gTµı`\0Ñ	Mk?ˆ2®\r:Db%È]UÍ[²1óùcuµ[¡[9]Õà´/ EV>k@éa\r_\"b6]ö E–DöQ^)È™•@Ps€ITr vT\0ØVR™W@ ëiR”2/¢b…,Xr€¬	jç^µï0•õ«ÿk’’üCÇlˆ“fl'8E–É©‘oµW¶¨ûI¤şmpë£¶ı/&ï´+´òıéX¯çk.şğ\\ÿ4Ó0d“r\"“°kb\nH\$Ğ¢†±KCâåo§OoíõG%\r¦äûÏl²–ùëÒö Õ]àP7\"*hPP€\rc¡_€X[`æêöî â7å:`‹˜Uà°ß¨–I¢ƒwêdã¶;·Š	—“x t†À‚8d\0@Ôjw˜v¶ —»{÷Â b	¨pü æñcyíö\n€ , u<	âÕ\"uyE:í÷Z`<LF£ë¨ü2ÑğcwşS¸d†%uw€÷u•ß€#pqNNßÀN’\n·#@ E‚#\"@|d%kwc\"* xò„àw‚˜\0uX.¦Âl&Xe‚ÎM†ÃB'“ @6ChÂ»`S¥‚wÿ‡G Êé¢ÓˆC[V×ø1àß\rÆşb\"Ğ\n\0\n`©JÀ¸º+—a1¦\"lW}z–]zjdO„>!‚ˆG\0[\\å¢ïF|…®¾ À^\0ZJ`î¨b·`#ãŒ5€É`W÷“E;„â(à°¹!`È¯`\"»~Eß’gƒŠhVGrı‚_ï±uåj¢Q‘*d'2g/Ø-\n€h¤ ^Àda)×•E:HØhäËãvEvˆs—Bàí­‡9wƒ\0ÜßMøßĞãŒùW”NLù…• Ë3Àè-ø=#@%øD!ÊXL*ô…êV…¹‰¸;…Ò1„ãÇ˜Ø‡ƒMk‰™X‹ØŠÀñÒâ&ù™Ùr<å[%Uøeq˜WW‰#\$ÛèŒlIVàA†W_GÆVú„šF\"&fĞ(çošdV1ƒšó*wr0±F\"Œ¯¤‰H˜‰ÑvçTq hw*†“†@Œƒˆƒs….¯0g8ú1_€zfö»äA)À¢—+<¯Òu\"­F_lO#Œ®Õân{‚XYwv,ÕÇ–½ HàÓÇ“cÌ{n7á<8ÌYfB°¬Á\0øFe–\ràù:érŠ¸\$gy¬šÌÀ6=pÍ;4ó›9\0öb%a2BÉ Ê\n ¤	(€\rº@GFª@ÙÃû%²€ß²Å 1ÓâĞ“qbÂs+£Zg´%@t%–à š3±€Ô\räD(àLÒ÷v.šğ€\\\rR ^ã€é°	×A2Â\rÖ¾;yvïˆÀ€~ÀUâÂ*¢Ğ@õø<š‰Ëq¢Wa¢·S¢úW¡„=yEš<Vº@<ù±1·³3€Ğ\$»äì\$üPÅD€¾w2UÏØ;Ï]ù¡Ç%!\n¯ó²ÃD)‘¦ó„‚I/h~Çàè‚<Â+ø€î0€ÜåsÓ34‡-´ŠèåGÓØ4ïÂòŒÅT\nÏóu3ëü<TôckşÊÅWÃÎW»âGU—±Çfme÷\\¤D!*vÅx3ƒiû2ªw2§Œ1ª|Œ\$&Ô‰+€†bG\$v!rò*-ù4­quÈ\rÑyLã0üÊtXÆ…Æ·²çÚßI“b dúµSÓï<öaòÎËç¹G´~G¬Ïà„m äg¢x-T’¶ÏÉY’á™–¼ª!(wHÃ\nãš4aäg)`ñƒ%Ó@rüY%’¬(qÜX¿˜à!cÕ\$Dy±]mjöbpR4RõÜRÄïuÜX†äpó/6 h€eâªÕ+ùnëš@ cHĞÉ¹ğ ¹Ÿ{Å”æÅÚ.\\bmVşpPé·`bQrãP€ê\0`\$WÖWÑRq2x%bY—1ÜÛû€¬²º³±>ùÍÌ1æÌìu&b\nVÌo°Nïj\n€ŞâÄEÄ†ØC3âåG\0²ış ep–£Â<~B^ A¾%/9°;åv\\¥[â›Hşl^U»ÔûÇÓh¿š¢.\\YÉ}+	üYtŞÅıqÆ‰6¢\nsüã\0‘¾äü¦c:–3¹*}ÉÜñŒÚØ7z\$ë·d\\Âç\"«· W³£Y³û+²ûIèã³e½¢Ú\0çùSï9©½ºDt[rû×šºCÖ©bÍ~é¦³DÕw/½l—¿ÉL`Õ~ ØU‡øV_àŞ\0\rŞwó)¶bÆ¦8–ï”¿Gò Ü%¼º¾WŞUáöA¾v(ùHFg¨ X©cº¢n9ö2—ô—Õn12lÀ˜\rä?\"tï\0XPÉsùÿ”Ymf±‚‚F?mñÒx5™}Hì_´ìXcûy”áÎºCåÕ.Ä\$¯`¶köd5.rx>Ç¢7şæîsÛn3¼“Ó<¼´g„ˆğ§åO(\\@èWò:PáÏƒ{ó­_Fà†”hgLÓ >°<¦6é~'²K„0Õ?@ãìEAå_Ü Ô8H.LG<øÄíd  Y¬oú¡ÒÜü«€­ÚkF<Òıp‘¾(Ûj\$9ò¬ª˜Ä?¥ÙV P?)ÍòØ¤DŠuŠ°Lb¨­àj¹Áâç}	ğ\0„Kp³ì7ÆÒZsÔ€àõDì)ë\rù:°™JQÖ“}¥”\$¨€bÁ³AØu)»Ü‡\"XÅÁMŞ%pQPÑQÂÍ\$@³¦\\’\0ÚVõ7ªÁ¨TMøX×É*ôò #)G\\ ĞK—«ÂMë0–=¯JÜ&½`¿\"x‹_ÒËb™B`–C?/ˆ´ÅªÚBUuë«83ûNR¤Îñ_]Nî‰TèÜ¿D„ª…òwI¥\n‘2«„€D:Yî‚	ôì­q1°Ğ`B‚à—F!]W5‰,:˜1Ã(­0ÇtQôFÙ	ˆŠpÕ7’'!\"@€Õ8Õ0Ü`œ7ˆ\rhC’\nXÓ¡¥\rA–CÖ	m	Q€Ìß&l&€|cåL‚d¢\"#·\"ø’¨ÂPK‚‚ôß56HÊ„bÊ—&åÄ+Î#f¨V¹/Søˆh(Td±uÀ€ô‹<=ˆxdª8Š:ám!ğ6ü8€,JP~RP–DüZÂÃu€±‘O¡`Å0X¦šŠA¢ˆwØ£0eh^Câ’\$Ä ¢ãÈ8A‚¡PçÅ(ê#+K×N4¡%\n•BÇÄ>âÜ2‡%ìüë{0ùÆ‹¾ò0ÏŸ¢/¦Ñzˆğ Ï	P¢‘“Êš @Égæ¥Dªzö¤D§ÔŠşYà¥“4œ¥¯,%l3WâÍUş²¢ÃúLr[º°øÔ…9H¥ÌgT`@7È\r‚¹N£ï– èÛ€û°>™÷ÖèñÈÁi\0pzŒ”¢Hå¢ääÑr;`IV]EÈöÖGYz\rôó˜EnÈò/{˜Ä¶™Ç	dQsbTj\$g>™~=îËŠ“Jâƒ·ÄûKYˆ£ªZ1Ò…KçRÒ”Ô»ÀÖ6Aî\r¡{”CÒt¸ 5º€=n²şcö¥N)œ”Lº=/ğ<‹<;bŒU¡†VáŸši¼1ã¤~ÕÊ`HıµªEË‡Šé_¹œ‘R<\$b9Æ¿Õ¼\n>{h@›<Ù7¸¶Bçë[„\r€<4IbM),”@Ù%082é¢¹‘˜sCO#ô2A¼ır‘ä†à|oé+Iaï¬Öu¬…Ó+&P3ÈpRM\\lˆR/³¸I¹JR“ŒŒ^j0Šäğ2~JCFdjV!¡K2;“ä‚\$vĞ6ˆ;m¤iÎd\"F		ÈUìòBtP„zµ£Ì");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress(compile_file('','minify_js'));}else{header("Content-Type: image/gif");switch($_GET["file"]){case"plus.gif":echo"GIF89a\0\0\0001îîî\0\0€™™™\0\0\0!ù\0\0\0,\0\0\0\0\0\0!„©ËíMñÌ*)¾oú¯) q•¡eˆµî#ÄòLË\0;";break;case"cross.gif":echo"GIF89a\0\0\0001îîî\0\0€™™™\0\0\0!ù\0\0\0,\0\0\0\0\0\0#„©Ëí#\naÖFo~yÃ._wa”á1ç±JîGÂL×6]\0\0;";break;case"up.gif":echo"GIF89a\0\0\0001îîî\0\0€™™™\0\0\0!ù\0\0\0,\0\0\0\0\0\0 „©ËíMQN\nï}ôa8ŠyšaÅ¶®\0Çò\0;";break;case"down.gif":echo"GIF89a\0\0\0001îîî\0\0€™™™\0\0\0!ù\0\0\0,\0\0\0\0\0\0 „©ËíMñÌ*)¾[Wş\\¢ÇL&ÙœÆ¶•\0Çò\0;";break;case"arrow.gif":echo"GIF89a\0\n\0€\0\0€€€ÿÿÿ!ù\0\0\0,\0\0\0\0\0\n\0\0‚i–±‹”ªÓ²Ş»\0\0;";break;}}exit;}function
connection(){global$h;return$h;}function
adminer(){global$b;return$b;}function
idf_unescape($v){$id=substr($v,-1);return
str_replace($id.$id,$id,substr($v,1,-1));}function
escape_string($X){return
substr(q($X),1,-1);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
remove_slashes($re,$gc=false){if(get_magic_quotes_gpc()){while(list($z,$X)=each($re)){foreach($X
as$Yc=>$W){unset($re[$z][$Yc]);if(is_array($W)){$re[$z][stripslashes($Yc)]=$W;$re[]=&$re[$z][stripslashes($Yc)];}else$re[$z][stripslashes($Yc)]=($gc?$W:stripslashes($W));}}}}function
bracket_escape($v,$Ga=false){static$Hf=array(':'=>':1',']'=>':2','['=>':3','"'=>':4');return
strtr($v,($Ga?array_flip($Hf):$Hf));}function
charset($h){return(version_compare($h->server_info,"5.5.3")>=0?"utf8mb4":"utf8");}function
h($Q){return
str_replace("\0","&#0;",htmlspecialchars($Q,ENT_QUOTES,'utf-8'));}function
nbsp($Q){return(trim($Q)!=""?h($Q):"&nbsp;");}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($D,$Y,$Ua,$ed="",$Pd="",$Ya="",$fd=""){$K="<input type='checkbox' name='$D' value='".h($Y)."'".($Ua?" checked":"").($fd?" aria-labelledby='$fd'":"").($Pd?' onclick="'.h($Pd).'"':'').">";return($ed!=""||$Ya?"<label".($Ya?" class='$Ya'":"").">$K".h($ed)."</label>":$K);}function
optionlist($Ud,$Re=null,$dg=false){$K="";foreach($Ud
as$Yc=>$W){$Vd=array($Yc=>$W);if(is_array($W)){$K.='<optgroup label="'.h($Yc).'">';$Vd=$W;}foreach($Vd
as$z=>$X)$K.='<option'.($dg||is_string($z)?' value="'.h($z).'"':'').(($dg||is_string($z)?(string)$z:$X)===$Re?' selected':'').'>'.h($X);if(is_array($W))$K.='</optgroup>';}return$K;}function
html_select($D,$Ud,$Y="",$Od=true,$fd=""){if($Od)return"<select name='".h($D)."'".(is_string($Od)?' onchange="'.h($Od).'"':"").($fd?" aria-labelledby='$fd'":"").">".optionlist($Ud,$Y)."</select>";$K="";foreach($Ud
as$z=>$X)$K.="<label><input type='radio' name='".h($D)."' value='".h($z)."'".($z==$Y?" checked":"").">".h($X)."</label>";return$K;}function
select_input($Ca,$Ud,$Y="",$ie=""){return($Ud?"<select$Ca><option value=''>$ie".optionlist($Ud,$Y,true)."</select>":"<input$Ca size='10' value='".h($Y)."' placeholder='$ie'>");}function
confirm(){return" onclick=\"return confirm('".lang(0)."');\"";}function
print_fieldset($u,$kd,$kg=false,$Pd=""){echo"<fieldset><legend><a href='#fieldset-$u' onclick=\"".h($Pd)."return !toggle('fieldset-$u');\">$kd</a></legend><div id='fieldset-$u'".($kg?"":" class='hidden'").">\n";}function
bold($Oa,$Ya=""){return($Oa?" class='active $Ya'":($Ya?" class='$Ya'":""));}function
odd($K=' class="odd"'){static$t=0;if(!$K)$t=-1;return($t++%2?$K:'');}function
js_escape($Q){return
addcslashes($Q,"\r\n'\\/");}function
json_row($z,$X=null){static$hc=true;if($hc)echo"{";if($z!=""){echo($hc?"":",")."\n\t\"".addcslashes($z,"\r\n\t\"\\/").'": '.($X!==null?'"'.addcslashes($X,"\r\n\"\\/").'"':'null');$hc=false;}else{echo"\n}\n";$hc=true;}}function
ini_bool($Pc){$X=ini_get($Pc);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
sid(){static$K;if($K===null)$K=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$K;}function
set_password($gg,$O,$V,$H){$_SESSION["pwds"][$gg][$O][$V]=($_COOKIE["adminer_key"]&&is_string($H)?array(encrypt_string($H,$_COOKIE["adminer_key"])):$H);}function
get_password(){$K=get_session("pwds");if(is_array($K))$K=($_COOKIE["adminer_key"]?decrypt_string($K[0],$_COOKIE["adminer_key"]):false);return$K;}function
q($Q){global$h;return$h->quote($Q);}function
get_vals($I,$e=0){global$h;$K=array();$J=$h->query($I);if(is_object($J)){while($L=$J->fetch_row())$K[]=$L[$e];}return$K;}function
get_key_vals($I,$i=null,$_f=0){global$h;if(!is_object($i))$i=$h;$K=array();$i->timeout=$_f;$J=$i->query($I);$i->timeout=0;if(is_object($J)){while($L=$J->fetch_row())$K[$L[0]]=$L[1];}return$K;}function
get_rows($I,$i=null,$n="<p class='error'>"){global$h;$hb=(is_object($i)?$i:$h);$K=array();$J=$hb->query($I);if(is_object($J)){while($L=$J->fetch_assoc())$K[]=$L;}elseif(!$J&&!is_object($i)&&$n&&defined("PAGE_HEADER"))echo$n.error()."\n";return$K;}function
unique_array($L,$x){foreach($x
as$w){if(preg_match("~PRIMARY|UNIQUE~",$w["type"])){$K=array();foreach($w["columns"]as$z){if(!isset($L[$z]))continue
2;$K[$z]=$L[$z];}return$K;}}}function
escape_key($z){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$z,$B))return$B[1].idf_escape(idf_unescape($B[2])).$B[3];return
idf_escape($z);}function
where($Z,$p=array()){global$h,$y;$K=array();foreach((array)$Z["where"]as$z=>$X){$z=bracket_escape($z,1);$e=escape_key($z);$K[]=$e.($y=="sql"&&preg_match('~^[0-9]*\\.[0-9]*$~',$X)?" LIKE ".q(addcslashes($X,"%_\\")):($y=="mssql"?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($p[$z],q($X))));if($y=="sql"&&preg_match('~char|text~',$p[$z]["type"])&&preg_match("~[^ -@]~",$X))$K[]="$e = ".q($X)." COLLATE ".charset($h)."_bin";}foreach((array)$Z["null"]as$z)$K[]=escape_key($z)." IS NULL";return
implode(" AND ",$K);}function
where_check($X,$p=array()){parse_str($X,$Sa);remove_slashes(array(&$Sa));return
where($Sa,$p);}function
where_link($t,$e,$Y,$Rd="="){return"&where%5B$t%5D%5Bcol%5D=".urlencode($e)."&where%5B$t%5D%5Bop%5D=".urlencode(($Y!==null?$Rd:"IS NULL"))."&where%5B$t%5D%5Bval%5D=".urlencode($Y);}function
convert_fields($f,$p,$N=array()){$K="";foreach($f
as$z=>$X){if($N&&!in_array(idf_escape($z),$N))continue;$za=convert_field($p[$z]);if($za)$K.=", $za AS ".idf_escape($z);}return$K;}function
cookie($D,$Y,$nd=2592000){global$aa;return
header("Set-Cookie: $D=".urlencode($Y).($nd?"; expires=".gmdate("D, d M Y H:i:s",time()+$nd)." GMT":"")."; path=".preg_replace('~\\?.*~','',$_SERVER["REQUEST_URI"]).($aa?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
restart_session(){if(!ini_bool("session.use_cookies"))session_start();}function
stop_session(){if(!ini_bool("session.use_cookies"))session_write_close();}function&get_session($z){return$_SESSION[$z][DRIVER][SERVER][$_GET["username"]];}function
set_session($z,$X){$_SESSION[$z][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($gg,$O,$V,$m=null){global$Bb;preg_match('~([^?]*)\\??(.*)~',remove_from_uri(implode("|",array_keys($Bb))."|username|".($m!==null?"db|":"").session_name()),$B);return"$B[1]?".(sid()?SID."&":"").($gg!="server"||$O!=""?urlencode($gg)."=".urlencode($O)."&":"")."username=".urlencode($V).($m!=""?"&db=".urlencode($m):"").($B[2]?"&$B[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($od,$yd=null){if($yd!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($od!==null?$od:$_SERVER["REQUEST_URI"]))][]=$yd;}if($od!==null){if($od=="")$od=".";header("Location: $od");exit;}}function
query_redirect($I,$od,$yd,$_e=true,$Ub=true,$Zb=false,$zf=""){global$h,$n,$b;if($Ub){$hf=microtime(true);$Zb=!$h->query($I);$zf=format_time($hf);}$ff="";if($I)$ff=$b->messageQuery($I,$zf);if($Zb){$n=error().$ff;return
false;}if($_e)redirect($od,$yd.$ff);return
true;}function
queries($I){global$h;static$ue=array();static$hf;if(!$hf)$hf=microtime(true);if($I===null)return
array(implode("\n",$ue),format_time($hf));$ue[]=(preg_match('~;$~',$I)?"DELIMITER ;;\n$I;\nDELIMITER ":$I).";";return$h->query($I);}function
apply_queries($I,$T,$Rb='table'){foreach($T
as$R){if(!queries("$I ".$Rb($R)))return
false;}return
true;}function
queries_redirect($od,$yd,$_e){list($ue,$zf)=queries(null);return
query_redirect($ue,$od,$yd,$_e,false,!$_e,$zf);}function
format_time($hf){return
lang(1,max(0,microtime(true)-$hf));}function
remove_from_uri($be=""){return
substr(preg_replace("~(?<=[?&])($be".(SID?"":"|".session_name()).")=[^&]*&~",'',"$_SERVER[REQUEST_URI]&"),0,-1);}function
pagination($F,$ob){return" ".($F==$ob?$F+1:'<a href="'.h(remove_from_uri("page").($F?"&page=$F".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($F+1)."</a>");}function
get_file($z,$rb=false){$dc=$_FILES[$z];if(!$dc)return
null;foreach($dc
as$z=>$X)$dc[$z]=(array)$X;$K='';foreach($dc["error"]as$z=>$n){if($n)return$n;$D=$dc["name"][$z];$Ff=$dc["tmp_name"][$z];$jb=file_get_contents($rb&&preg_match('~\\.gz$~',$D)?"compress.zlib://$Ff":$Ff);if($rb){$hf=substr($jb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$hf,$Ae))$jb=iconv("utf-16","utf-8",$jb);elseif($hf=="\xEF\xBB\xBF")$jb=substr($jb,3);$K.=$jb."\n\n";}else$K.=$jb;}return$K;}function
upload_error($n){$vd=($n==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($n?lang(2).($vd?" ".lang(3,$vd):""):lang(4));}function
repeat_pattern($ge,$ld){return
str_repeat("$ge{0,65535}",$ld/65535)."$ge{0,".($ld%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\\0-\\x8\\xB\\xC\\xE-\\x1F]~',$X));}function
shorten_utf8($Q,$ld=80,$nf=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$ld).")($)?)u",$Q,$B))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$ld).")($)?)",$Q,$B);return
h($B[1]).$nf.(isset($B[2])?"":"<i>...</i>");}function
format_number($X){return
strtr(number_format($X,0,".",lang(5)),preg_split('~~u',lang(6),-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~[^a-z0-9_]~i','-',$X);}function
hidden_fields($re,$Jc=array()){while(list($z,$X)=each($re)){if(!in_array($z,$Jc)){if(is_array($X)){foreach($X
as$Yc=>$W)$re[$z."[$Yc]"]=$W;}else
echo'<input type="hidden" name="'.h($z).'" value="'.h($X).'">';}}}function
hidden_fields_get(){echo(sid()?'<input type="hidden" name="'.session_name().'" value="'.h(session_id()).'">':''),(SERVER!==null?'<input type="hidden" name="'.DRIVER.'" value="'.h(SERVER).'">':""),'<input type="hidden" name="username" value="'.h($_GET["username"]).'">';}function
table_status1($R,$ac=false){$K=table_status($R,$ac);return($K?$K:array("Name"=>$R));}function
column_foreign_keys($R){global$b;$K=array();foreach($b->foreignKeys($R)as$q){foreach($q["source"]as$X)$K[$X][]=$q;}return$K;}function
enum_input($U,$Ca,$o,$Y,$Mb=null){global$b;preg_match_all("~'((?:[^']|'')*)'~",$o["length"],$C);$K=($Mb!==null?"<label><input type='$U'$Ca value='$Mb'".((is_array($Y)?in_array($Mb,$Y):$Y===0)?" checked":"")."><i>".lang(7)."</i></label>":"");foreach($C[1]as$t=>$X){$X=stripcslashes(str_replace("''","'",$X));$Ua=(is_int($Y)?$Y==$t+1:(is_array($Y)?in_array($t+1,$Y):$Y===$X));$K.=" <label><input type='$U'$Ca value='".($t+1)."'".($Ua?' checked':'').'>'.h($b->editVal($X,$o)).'</label>';}return$K;}function
input($o,$Y,$r){global$h,$Rf,$b,$y;$D=h(bracket_escape($o["field"]));echo"<td class='function'>";if(is_array($Y)&&!$r){$xa=array($Y);if(version_compare(PHP_VERSION,5.4)>=0)$xa[]=JSON_PRETTY_PRINT;$Y=call_user_func_array('json_encode',$xa);$r="json";}$Ee=($y=="mssql"&&$o["auto_increment"]);if($Ee&&!$_POST["save"])$r=null;$vc=(isset($_GET["select"])||$Ee?array("orig"=>lang(8)):array())+$b->editFunctions($o);$Ca=" name='fields[$D]'";if($o["type"]=="enum")echo
nbsp($vc[""])."<td>".$b->editInput($_GET["edit"],$o,$Ca,$Y);else{$hc=0;foreach($vc
as$z=>$X){if($z===""||!$X)break;$hc++;}$Od=($hc?" onchange=\"var f = this.form['function[".h(js_escape(bracket_escape($o["field"])))."]']; if ($hc > f.selectedIndex) f.selectedIndex = $hc;\" onkeyup='keyupChange.call(this);'":"");$Ca.=$Od;$_c=(in_array($r,$vc)||isset($vc[$r]));echo(count($vc)>1?"<select name='function[$D]' onchange='functionChange(this);'".on_help("getTarget(event).value.replace(/^SQL\$/, '')",1).">".optionlist($vc,$r===null||$_c?$r:"")."</select>":nbsp(reset($vc))).'<td>';$Rc=$b->editInput($_GET["edit"],$o,$Ca,$Y);if($Rc!="")echo$Rc;elseif(preg_match('~bool~',$o["type"]))echo"<input type='hidden'$Ca value='0'>"."<input type='checkbox'".(in_array(strtolower($Y),array('1','t','true','y','yes','on'))?" checked='checked'":"")."$Ca value='1'>";elseif($o["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$o["length"],$C);foreach($C[1]as$t=>$X){$X=stripcslashes(str_replace("''","'",$X));$Ua=(is_int($Y)?($Y>>$t)&1:in_array($X,explode(",",$Y),true));echo" <label><input type='checkbox' name='fields[$D][$t]' value='".(1<<$t)."'".($Ua?' checked':'')."$Od>".h($b->editVal($X,$o)).'</label>';}}elseif(preg_match('~blob|bytea|raw|file~',$o["type"])&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$D'$Od>";elseif(($wf=preg_match('~text|lob~',$o["type"]))||preg_match("~\n~",$Y)){if($wf&&$y!="sqlite")$Ca.=" cols='50' rows='12'";else{$M=min(12,substr_count($Y,"\n")+1);$Ca.=" cols='30' rows='$M'".($M==1?" style='height: 1.2em;'":"");}echo"<textarea$Ca>".h($Y).'</textarea>';}elseif($r=="json"||preg_match('~^jsonb?$~',$o["type"]))echo"<textarea$Ca cols='50' rows='12' class='jush-js'>".h($Y).'</textarea>';else{$xd=(!preg_match('~int~',$o["type"])&&preg_match('~^(\\d+)(,(\\d+))?$~',$o["length"],$B)?((preg_match("~binary~",$o["type"])?2:1)*$B[1]+($B[3]?1:0)+($B[2]&&!$o["unsigned"]?1:0)):($Rf[$o["type"]]?$Rf[$o["type"]]+($o["unsigned"]?0:1):0));if($y=='sql'&&$h->server_info>=5.6&&preg_match('~time~',$o["type"]))$xd+=7;echo"<input".((!$_c||$r==="")&&preg_match('~(?<!o)int~',$o["type"])&&!preg_match('~\[\]~',$o["full_type"])?" type='number'":"")." value='".h($Y)."'".($xd?" data-maxlength='$xd'":"").(preg_match('~char|binary~',$o["type"])&&$xd>20?" size='40'":"")."$Ca>";}}}function
process_input($o){global$b;$v=bracket_escape($o["field"]);$r=$_POST["function"][$v];$Y=$_POST["fields"][$v];if($o["type"]=="enum"){if($Y==-1)return
false;if($Y=="")return"NULL";return+$Y;}if($o["auto_increment"]&&$Y=="")return
null;if($r=="orig")return($o["on_update"]=="CURRENT_TIMESTAMP"?idf_escape($o["field"]):false);if($r=="NULL")return"NULL";if($o["type"]=="set")return
array_sum((array)$Y);if($r=="json"){$r="";$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}if(preg_match('~blob|bytea|raw|file~',$o["type"])&&ini_bool("file_uploads")){$dc=get_file("fields-$v");if(!is_string($dc))return
false;return
q($dc);}return$b->processInput($o,$Y,$r);}function
fields_from_edit(){global$Ab;$K=array();foreach((array)$_POST["field_keys"]as$z=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$z];$_POST["fields"][$X]=$_POST["field_vals"][$z];}}foreach((array)$_POST["fields"]as$z=>$X){$D=bracket_escape($z,1);$K[$D]=array("field"=>$D,"privileges"=>array("insert"=>1,"update"=>1),"null"=>1,"auto_increment"=>($z==$Ab->primary),);}return$K;}function
search_tables(){global$b,$h;$_GET["where"][0]["op"]="LIKE %%";$_GET["where"][0]["val"]=$_POST["query"];$rc=false;foreach(table_status('',true)as$R=>$S){$D=$b->tableName($S);if(isset($S["Engine"])&&$D!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$J=$h->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",$b->selectSearchProcess(fields($R),array())),1));if(!$J||$J->fetch_row()){if(!$rc){echo"<ul>\n";$rc=true;}echo"<li>".($J?"<a href='".h(ME."select=".urlencode($R)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$D</a>\n":"$D: <span class='error'>".error()."</span>\n");}}}echo($rc?"</ul>":"<p class='message'>".lang(9))."\n";}function
dump_headers($Hc,$Cd=false){global$b;$K=$b->dumpHeaders($Hc,$Cd);$Zd=$_POST["output"];if($Zd!="text")header("Content-Disposition: attachment; filename=".$b->dumpFilename($Hc).".$K".($Zd!="file"&&!preg_match('~[^0-9a-z]~',$Zd)?".$Zd":""));session_write_close();ob_flush();flush();return$K;}function
dump_csv($L){foreach($L
as$z=>$X){if(preg_match("~[\"\n,;\t]~",$X)||$X==="")$L[$z]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($_POST["format"]=="tsv"?"\t":";")),$L)."\r\n";}function
apply_sql_function($r,$e){return($r?($r=="unixepoch"?"DATETIME($e, '$r')":($r=="count distinct"?"COUNT(DISTINCT ":strtoupper("$r("))."$e)"):$e);}function
get_temp_dir(){$K=ini_get("upload_tmp_dir");if(!$K){if(function_exists('sys_get_temp_dir'))$K=sys_get_temp_dir();else{$ec=@tempnam("","");if(!$ec)return
false;$K=dirname($ec);unlink($ec);}}return$K;}function
password_file($mb){$ec=get_temp_dir()."/adminer.key";$K=@file_get_contents($ec);if($K||!$mb)return$K;$tc=@fopen($ec,"w");if($tc){chmod($ec,0660);$K=rand_string();fwrite($tc,$K);fclose($tc);}return$K;}function
rand_string(){return
md5(uniqid(mt_rand(),true));}function
select_value($X,$A,$o,$xf){global$b,$aa;if(is_array($X)){$K="";foreach($X
as$Yc=>$W)$K.="<tr>".($X!=array_values($X)?"<th>".h($Yc):"")."<td>".select_value($W,$A,$o,$xf);return"<table cellspacing='0'>$K</table>";}if(!$A)$A=$b->selectLink($X,$o);if($A===null){if(is_mail($X))$A="mailto:$X";if($se=is_url($X))$A=(($se=="http"&&$aa)||preg_match('~WebKit|Firefox~i',$_SERVER["HTTP_USER_AGENT"])?$X:"https://www.adminer.org/redirect/?url=".urlencode($X));}$K=$b->editVal($X,$o);if($K!==null){if($K==="")$K="&nbsp;";elseif(!is_utf8($K))$K="\0";elseif($xf!=""&&is_shortable($o))$K=shorten_utf8($K,max(0,+$xf));else$K=h($K);}return$b->selectVal($K,$A,$o,$X);}function
is_mail($Jb){$_a='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$_b='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$ge="$_a+(\\.$_a+)*@($_b?\\.)+$_b";return
is_string($Jb)&&preg_match("(^$ge(,\\s*$ge)*\$)i",$Jb);}function
is_url($Q){$_b='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return(preg_match("~^(https?)://($_b?\\.)+$_b(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q,$B)?strtolower($B[1]):"");}function
is_shortable($o){return
preg_match('~char|text|lob|geometry|point|linestring|polygon|string|bytea~',$o["type"]);}function
count_rows($R,$Z,$Wc,$s){global$y;$I=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($Wc&&($y=="sql"||count($s)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$s).")$I":"SELECT COUNT(*)".($Wc?" FROM (SELECT 1$I$wc) x":$I));}function
slow_query($I){global$b,$Gf;$m=$b->database();$_f=$b->queryTimeout();if(support("kill")&&is_object($i=connect())&&($m==""||$i->select_db($m))){$dd=$i->result(connection_id());echo'<script type="text/javascript">
var timeout = setTimeout(function () {
	ajax(\'',js_escape(ME),'script=kill\', function () {
	}, \'token=',$Gf,'&kill=',$dd,'\');
}, ',1000*$_f,');
</script>
';}else$i=null;ob_flush();flush();$K=@get_key_vals($I,$i,$_f);if($i){echo"<script type='text/javascript'>clearTimeout(timeout);</script>\n";ob_flush();flush();}return
array_keys($K);}function
get_token(){$xe=rand(1,1e6);return($xe^$_SESSION["token"]).":$xe";}function
verify_token(){list($Gf,$xe)=explode(":",$_POST["token"]);return($xe^$_SESSION["token"])==$Gf;}function
lzw_decompress($La){$yb=256;$Ma=8;$ab=array();$Ge=0;$He=0;for($t=0;$t<strlen($La);$t++){$Ge=($Ge<<8)+ord($La[$t]);$He+=8;if($He>=$Ma){$He-=$Ma;$ab[]=$Ge>>$He;$Ge&=(1<<$He)-1;$yb++;if($yb>>$Ma)$Ma++;}}$xb=range("\0","\xFF");$K="";foreach($ab
as$t=>$Za){$Ib=$xb[$Za];if(!isset($Ib))$Ib=$og.$og[0];$K.=$Ib;if($t)$xb[]=$og.$Ib[0];$og=$Ib;}return$K;}function
on_help($eb,$af=0){return" onmouseover='helpMouseover(this, event, ".h($eb).", $af);' onmouseout='helpMouseout(this, event);'";}function
edit_form($a,$p,$L,$Zf){global$b,$y,$Gf,$n;$rf=$b->tableName(table_status1($a,true));page_header(($Zf?lang(10):lang(11)),$n,array("select"=>array($a,$rf)),$rf);if($L===false)echo"<p class='error'>".lang(12)."\n";echo'<form action="" method="post" enctype="multipart/form-data" id="form">
';if(!$p)echo"<p class='error'>".lang(13)."\n";else{echo"<table cellspacing='0' onkeydown='return editingKeydown(event);'>\n";foreach($p
as$D=>$o){echo"<tr><th>".$b->fieldName($o);$sb=$_GET["set"][bracket_escape($D)];if($sb===null){$sb=$o["default"];if($o["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$sb,$Ae))$sb=$Ae[1];}$Y=($L!==null?($L[$D]!=""&&$y=="sql"&&preg_match("~enum|set~",$o["type"])?(is_array($L[$D])?array_sum($L[$D]):+$L[$D]):$L[$D]):(!$Zf&&$o["auto_increment"]?"":(isset($_GET["select"])?false:$sb)));if(!$_POST["save"]&&is_string($Y))$Y=$b->editVal($Y,$o);$r=($_POST["save"]?(string)$_POST["function"][$D]:($Zf&&$o["on_update"]=="CURRENT_TIMESTAMP"?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(preg_match("~time~",$o["type"])&&$Y=="CURRENT_TIMESTAMP"){$Y="";$r="now";}input($o,$Y,$r);echo"\n";}if(!support("table"))echo"<tr>"."<th><input name='field_keys[]' onkeyup='keyupChange.call(this);' onchange='fieldChange(this);' value=''>"."<td class='function'>".html_select("field_funs[]",$b->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($p){echo"<input type='submit' value='".lang(14)."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' name='insert' value='".($Zf?lang(15)."' onclick='return !ajaxForm(this.form, \"".lang(16).'...", this)':lang(17))."' title='Ctrl+Shift+Enter'>\n";}echo($Zf?"<input type='submit' name='delete' value='".lang(18)."'".confirm().">\n":($_POST||!$p?"":"<script type='text/javascript'>focus(document.getElementById('form').getElementsByTagName('td')[1].firstChild);</script>\n"));if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo'<input type="hidden" name="referer" value="',h(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),'">
<input type="hidden" name="save" value="1">
<input type="hidden" name="token" value="',$Gf,'">
</form>
';}global$b,$h,$Bb,$Gb,$Ob,$n,$vc,$xc,$aa,$Qc,$y,$ba,$hd,$Nd,$he,$kf,$Ac,$Gf,$Jf,$Rf,$Yf,$ca;if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";$aa=$_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off");@ini_set("session.use_trans_sid",false);session_cache_limiter("");if(!defined("SID")){session_name("adminer_sid");$G=array(0,preg_replace('~\\?.*~','',$_SERVER["REQUEST_URI"]),"",$aa);if(version_compare(PHP_VERSION,'5.2.0')>=0)$G[]=true;call_user_func_array('session_set_cookie_params',$G);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$gc);if(get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("zend.ze1_compatibility_mode",false);@ini_set("precision",20);$hd=array('en'=>'English','ar'=>'Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©','bg'=>'Ğ‘ÑŠĞ»Ğ³Ğ°Ñ€ÑĞºĞ¸','bn'=>'à¦¬à¦¾à¦‚à¦²à¦¾','bs'=>'Bosanski','ca'=>'CatalÃ ','cs'=>'ÄŒeÅ¡tina','da'=>'Dansk','de'=>'Deutsch','el'=>'Î•Î»Î»Î·Î½Î¹ÎºÎ¬','es'=>'EspaÃ±ol','et'=>'Eesti','fa'=>'ÙØ§Ø±Ø³ÛŒ','fi'=>'Suomi','fr'=>'FranÃ§ais','gl'=>'Galego','hu'=>'Magyar','id'=>'Bahasa Indonesia','it'=>'Italiano','ja'=>'æ—¥æœ¬èª','ko'=>'í•œêµ­ì–´','lt'=>'LietuviÅ³','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'PortuguÃªs','pt-br'=>'PortuguÃªs (Brazil)','ro'=>'Limba RomÃ¢nÄƒ','ru'=>'Ğ ÑƒÑÑĞºĞ¸Ğ¹','sk'=>'SlovenÄina','sl'=>'Slovenski','sr'=>'Ğ¡Ñ€Ğ¿ÑĞºĞ¸','ta'=>'à®¤â€Œà®®à®¿à®´à¯','th'=>'à¸ à¸²à¸©à¸²à¹„à¸—à¸¢','tr'=>'TÃ¼rkÃ§e','uk'=>'Ğ£ĞºÑ€Ğ°Ñ—Ğ½ÑÑŒĞºĞ°','vi'=>'Tiáº¿ng Viá»‡t','zh'=>'ç®€ä½“ä¸­æ–‡','zh-tw'=>'ç¹é«”ä¸­æ–‡',);function
get_lang(){global$ba;return$ba;}function
lang($v,$Jd=null){if(is_string($v)){$ke=array_search($v,get_translations("en"));if($ke!==false)$v=$ke;}global$ba,$Jf;$If=($Jf[$v]?$Jf[$v]:$v);if(is_array($If)){$ke=($Jd==1?0:($ba=='cs'||$ba=='sk'?($Jd&&$Jd<5?1:2):($ba=='fr'?(!$Jd?0:1):($ba=='pl'?($Jd%10>1&&$Jd%10<5&&$Jd/10%10!=1?1:2):($ba=='sl'?($Jd%100==1?0:($Jd%100==2?1:($Jd%100==3||$Jd%100==4?2:3))):($ba=='lt'?($Jd%10==1&&$Jd%100!=11?0:($Jd%10>1&&$Jd/10%10!=1?1:2)):($ba=='bs'||$ba=='ru'||$ba=='sr'||$ba=='uk'?($Jd%10==1&&$Jd%100!=11?0:($Jd%10>1&&$Jd%10<5&&$Jd/10%10!=1?1:2)):1)))))));$If=$If[$ke];}$xa=func_get_args();array_shift($xa);$qc=str_replace("%d","%s",$If);if($qc!=$If)$xa[0]=format_number($Jd);return
vsprintf($qc,$xa);}function
switch_lang(){global$ba,$hd;echo"<form action='' method='post'>\n<div id='lang'>",lang(19).": ".html_select("lang",$hd,$ba,"this.form.submit();")," <input type='submit' value='".lang(20)."' class='hidden'>\n","<input type='hidden' name='token' value='".get_token()."'>\n";echo"</div>\n</form>\n";}if(isset($_POST["lang"])&&verify_token()){cookie("adminer_lang",$_POST["lang"]);$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=array();redirect(remove_from_uri());}$ba="en";if(isset($hd[$_COOKIE["adminer_lang"]])){cookie("adminer_lang",$_COOKIE["adminer_lang"]);$ba=$_COOKIE["adminer_lang"];}elseif(isset($hd[$_SESSION["lang"]]))$ba=$_SESSION["lang"];else{$pa=array();preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$C,PREG_SET_ORDER);foreach($C
as$B)$pa[$B[1]]=(isset($B[3])?$B[3]:1);arsort($pa);foreach($pa
as$z=>$te){if(isset($hd[$z])){$ba=$z;break;}$z=preg_replace('~-.*~','',$z);if(!isset($pa[$z])&&isset($hd[$z])){$ba=$z;break;}}}$Jf=$_SESSION["translations"];if($_SESSION["translations_version"]!=1182797052){$Jf=array();$_SESSION["translations_version"]=1182797052;}function
get_translations($gd){switch($gd){case"en":$g="A9D“yÔ@s:ÀGà¡(¸ffƒ‚Š¦ã	ˆÙ:ÄS°Şa2\"1¦..L'ƒI´êm‘#Çs,†KƒšOP#IÌ@%9¥i4Èo2ÏÆó €Ë,9%ÀPÀb2£a¸àr\n2›NCÈ(Şr4™Í1C`(:Ebç9AÈi:‰&ã™”åy·ˆFó½ĞY‚ˆ\r´\n– 8ZÔS=\$Aœ†¤`Ñ=ËÜŒ²‚0Ê\nÒãdFé	ŒŞn:ZÎ°)­ãQŒµ™öú£°Ak¾ßÄê}äˆe‹çADÍéœêaÊÄ¯ ¢„\\Ã}ö5ğ#|@èhÚ3·ÃN¾}@¡ÑiÕ¦¦t´Œç>•û.y8RmÒóûè\"3ˆz¶#kN!-cä²‰Ã(è;¬ãX#Œ£|ø,¢bzöK²ğ³¾£’):¸çC#–7­Cs¨¿\r­èÊØôZ·£»~Î¨bB1,èÇòô<¿JX!hHÉÁ«P2ÁCĞ7£Èîğ -BÎ¼ÃCkl Œm‹_)¢ó,:Æ)0#0qè¦:ŒCPË5#¢ã+@Íƒè)ÆqTˆ§Mƒ\nLÅ:áû ñš¥!¤#-\n“'Ì[UEÔk`”)c¸\$	Ğš&‡B˜¦cÍT<‹³`ÚŒ‘U4©¬Úâ\rƒÄÁNàÌ3\r‹[G)LhåŒè*ò*\rèÔb7(Úø:Œc’9ŒÃ¨ÙÅ\0XËÁiÏCS+Z~ÌCpêÎ…ÀM™gK6’jÚöÍ·[×¢è\\ı3Aİ7Z’”/c°Ã`¤\"¦)Ì¸Ş5ÆapA@7Îú3,é…øµ¥dYe ö¥¬9ÛÓIk¼´Ê8\r(Éo£²£±|S8ØÜ”%J‹ì8DÑE\"<e™v\$\$cB3¡Ñ=˜t…ã¾¤\$Jü,ã8_ëof9å!xDĞL¨é¤‹è³z5„Ağ’±\"óèèã|Ÿ§Ğ¸Ğ7À—†!²„eMÊy&S„¾%ÊÚ\"ˆ²j”0..¿”­ÎØ@î²ïÅ/é¨@(	ƒÖñì0@(JD€¤X2LÑ`ƒv\r„]G\r8;•%‰rau\r¯èåÍ¢:gÑ\$®@ÈŸ)ö²kÉôn³°µ÷Y’ô/H‚ƒ.é\n–¦¯¸«>Ÿ #‹gÇÓˆ@ Œš·‹S#<ì;íxÊ¥}Z]O\naP+`ÂÄ‚‘5Z†x£ÂFY×óÙ:ğ1¼;“^INpr3»ÊLi(11›Ó2G‰\"á‰…˜IQ“\$a*:doË‹/‘÷ŒÁâˆ'Êbœ„äËÔA1q2¨Â8­¢@IG…!8õpñ„¡A—‚Ãb aWéŞ-.Øº©¦ŠÌÖ1¦XÏ€loØ4†0ÖæÙ¨ væÍ‡8ÆøC2Ñ/¤œ8ÒšWğ \n¡P#ĞpSƒq®.çÊ.¢\\k‡‚2©a#J—‰«–º¶Dœ‰83õZ9Ò0h‰L (2•¬k\rÈ\n!²\rbÀ]ËÈU;Ô¦¬İÌÀm–KEü“èö°ÂĞ4òğ¨¢²A.Xô)™NÂ#é>äô—>‰l7“‚BÊhŒ„Ô2<që@KåêR—•\0•CDÒ\$¸¹#è%€R”yõå¢SÓ»\$!<%ÆbCAKA–¡…\n€";break;case"ar":$g="ÙC¶P‚Â²†l*„\r”,&\nÙA¶í„ø(J.™„0Se\\¶\r…ŒbÙ@¶0´,\nQ,l)ÅÀ¦Âµ°¬†Aòéj_1CĞM…«e€¢S™\ng@ŸOgë¨ô’XÙDMë)˜°0Œ†cA¨Øn8Çe*y#au4¡ ´Ir*;rSÁUµdJ	}‰ÎÑ*zªU@¦ŠX;ai1l(nóÕòıÃ[Óy™dŞu'c(€ÜoF“±¤Øe3™Nb¦ êp2NšS¡ Ó³:LZúz¶PØ\\bæ¼uÄ.•[¶Q`u	!Š)èÍ&ã<Òq)æÖ ˜ÈF>Ø¡Ps7Xì5g5¸K®K¦Â¦àØ÷á—0Ê‡Æ¢¶§\nS ü›r\$ ¯jÄ(î¢v†°Ì¶!Jb¸¡‰q««0\n¸šj\nÙˆé­¥jƒù@Åzšl<\$W¿ÈrØ“£åsœô§Ì†U&…[Í*¯³lƒê (B&÷¾ÆÉè4_!©±b’>ñ,?t[¢	ãë?‰:²X¦Œ3Şœ:îšã•ÊÃ‡MìK¦‰Î¬öÈJÄ*hı¡ª¬›ºÈ’,2 …B€ÈËd4àPH…á gL†)¥›kR<ô‘Jº\"ÓjÚ½£åBh›F¡KDKo;ïUŒ„ØêQÑÂıY%È‹\r5 èóÚƒ¿ĞÊ²[E:|µ¡)‚N@ÊOªŸYËozã6§&Å±0®*]¶ÛJ	S¨%\$	Ğš&‡B˜¦\rCP^6¡x¶0ßƒ»A­¥´y\$*µC! C Ø3Í\0Â94£xÌ3\rØËt.P2ÙPSÂµ8àp4Ô›B ŞÔ\r£Ü<„¨Ü9£ÆÙc0ê6`Ş3»c˜XÚY`Â3Œ.ØA£´`Úí­ØP9…*zN†–A\0†)ŠB2ª#é*ˆê¡tĞ†ËplªF„¿‘¼Ï4ÌëD£0¶ìİ Y\nH¾Q¬jú¶l,[0€Z.¢p¹JÁ«ÈZSf'Ä	¤%zõ0ÎË©>NÄ\"f&Œ#›v95=æ;ã•2€Óˆ›x0´8€Ì„C@è:˜t…ã¿„#&–7£]ÔŒáxÊ7yÃÃw—# 6˜{b:wû‡•\ra|\$£ƒ^6ùã xŒ!ò–Ùı\rèßF6¡R5´£HèÔù9Oƒ¡O?døë¹&ØJOÛ 1¢á07¸JèBM_µÂ¬²ŠÑYV&2	£6jC@plhı˜2*ZÙU\$°a5Î§˜*Cp©ÍÎ'èR×H\"†NdÚ’â†åˆs+bÊ”*¯Ó™(e£Ÿ#–”Óób…Ei§5¦Jœcï&a\$“> iQ†ı‡7ı\rñ³bÄ:›'öƒo\r € ¼f,é\rëÏFci])±&gò§b…\0P	áL*²\"’Zëq0Æ)%4¾£‹ÚS€…‚bp—ĞÁ\r°šóŸ%”hØLÂ„¤ba;ïqÊÊÅkHP	@ÎÅğÆÊ™kæ\rï;@ÄC8 \naD&\0Ìk)­v*	Š”`i|®¥ı¿©cœfF‘å-Ä¾M…?Nj¯‹ê—ÏÑÖsS¬Æ¨iİ9Q–â±ÎÉí\0<@„ è¨yBŒ—1H<M\$™„0èƒ`+¤1†°@õ¦( Á„6FÓhÊ˜iÌ±ı04°ut@€1?Ü¨TÀ´é	Üäé0«™nåšI=âÜ¶Šr:¤9#kÅ±\\Khbğ\n	´†‘­Ê	*a*DKU\n*§*’é C)È Ëu-Á\0\":¨¹8½ƒÕB P‡\$D4%3¯	ÊyE%0ñ£ë\$O°L˜q„î\0«1k©4¦äà¨·&˜j\"lpd|¾ÊåOXÒXS¦¼1›°È‰“®UJ¶%ÄÛ\$‹SÈ,UË5Já›;ÕA;Ä›!i§lk\0";break;case"bg":$g="ĞP´\r›EÑ@4°!Awh Z(&‚Ô~\n‹†faÌĞNÅ`Ñ‚şDˆ…4ĞÕü\"Ğ]4\r;Ae2”­a°µ€¢„œ.aÂèúrpº’@×“ˆ|.W.X4òå«FPµ”Ìâ“Ø\$ªhRàsÉÜÊ}@¨Ğ—pÙĞ”æB¢4”sE²Î¢7fŠ&EŠ, Ói•X\nFC1 Ôl7còØMEo)_G×ÒèÎ_<‡GÓ­}†Íœ,kë†ŠqPX”}F³+9¤¬7i†£Zè´šiíQ¡³_a·–—ZŠË*¨n^¹ÉÕS¦Ü9¾ÿ£YŸVÚ¨~³]ĞX\\Ró‰6±õÔ}±jâ}	¬lê4v±ø=ˆHî·ƒâ’ÀDê²¹%’>L*H›8ß@¤ª¤——P|.Õ3dŠ¯m XúÂé3’‡²ğ!rÔ'HS†˜¹1k6A>éÂ¦”6Ëÿ5	êÜ¸®kJ¾®&êªj½\"Kºüª°Ùß9‰{/¢­Ê^ä:Dfã5Mb(¬<¨ùOÈhù(™G°Zi4=æ„Î›¹-bk¨®1l™#äšÀä©j©Î4ˆúùÉ-j¶/ËhŠC¨ù>•BOÃÇKí	¡G¦hLO<‘¢B«Å¤\0ù'ì\$ÒÀD¯Kb¾¬O±qŒˆkT´¢`HKZÒuÂ¶¿HıT!hHØÁª>Q/„H¬UJË¶‰4ªAãÊ¨!g\"‘Ë¼àšM±*\\:±É©ºpQîJbËB•PŠ£Ò6#äò'e¬}	İó]¡Cšƒ.ÊMõ¿Z%“\"–•Òjª¯`ê„XÌÕÕyB8•³IO¬Ò’K‹R±Ú‰g«¥„ÄháÑ £™rPBÚºÏgèZÓ™ÛgQRêõßÕ.úÉl;Ó Ø:RµÖŸI±’!š\nz¸”6k¶»ÃØ,#P9:>ãhh~ˆ)nÓ=ˆ8	bIKì¯>vë«°[î”¢ƒa¯Ş¡W¸áv†¬Qr­ã4¾È¦GIWÌ+»bDƒíé6ã¡®Nê†Bò&A°ïœöı_;é7’lÜ/Ó,i»(«#ËlZ	ôpŸ¥Ò¢fD¡©ŒBG&rvävºš‘Kğ^E„:ó£©Ã­ÜŸ‘ê‡:Ô;S‚ãƒ5}\nrÔb×1¸*ØÅ¿À^Ê¶¶b˜¿„™|œO!©¿Z©ù´‚(÷IQqOD<¨“rxšPB™D\rà‘\$wàNP£·6dñãÀÂ@r¡˜‚ Ğ p`è‚ğï	Ápa´4†àÊpoAœ†Pİ\rCÀt†ÁÌ4†øn\0t0Ì2‡H:Ãl!¸5‚ |×Œ¹ª*§áÀ^Aó†nMé:FîWÑ>å”ù5ÔàÑ{Q‡…ÁE¨N97‰İ€¾óÀ³ÊIOë8Òš£ºCcÓ6åN*r²sÏF¦Yô£–ºÕYF}°ÖÃZàĞNçy/ „‹B.íI#³HAn%æšÊšÊs´‚)]¹Ie\"XëD-¼˜·3ôJY2?qäç•ùMÏ½j²YWÀ„ZÂªÍ\\†”gî#ÑƒH¦­8~GÄ½rÉ\"46*eM©«<(ÙĞc¸oJäĞ*@€ Â¸[Ûäî0+mæ=3ÄeÏ´j:r˜ó½ôJÉmóÅÇ•ZSœ›jrM\"`òHJŒÅlÑ ì/ÖBÑåümoÈ´R|“æÜ¯Hóz-˜7 aÁ¾/©¢;9L'Iİ.3ZMOÔ[ XD’MÖN¢ÖÖ]Np \naD&rèZÉ¡Æà€#IBè\$kD…-G%»B’4/rdÁI“ÒGĞ³­V\"öE'ÓIŒ{R…Æ´Ñå¡[I<”RphWG	ZÍ©ncÏeP\$CvµÉŒvl0°V¤t^o‹ì ÏX¦2ëÓvVL„X±ûdÛyX²´Ğ×€ †Ó`+/§L˜´TZ«cQ^jÊ@À#ÙòÈc§djhÓHôX*y§g?[†B’\0U\nƒ‡ƒÊT]åÃœah­«Íp«‹lÚĞ;.­nµœ³Ì„¥F•¸€¼Á\$\\’9§ÓDÔğh„ààFIÖTO\"<èõÛÁyâ;kê…—ã2‰Ägm÷”Í¤Ñp¯Ã?ª(_P…[ÍXFœa1	Ò¿Ø\0®RèÜ­ú‚–òqC2¨ÒÛİi³Z\n;QE¢|q­!hÌ…¦ 1:tºªHZ1¤j¢±q-yHë#¡®WŞ³·¤‰¢¾re5é[(X¤9›¸¬ÚõÛC¹Ef5ÆQ}O6–¤Úû©9_„Òúü¿pó¨ƒd«ËcÄf•£´uÔqÀ";break;case"bn":$g="àS)\nt]\0_ˆ 	XD)L¨„@Ğ4l5€ÁBQpÌÌ 9‚ \n¸ú\0‡€,¡ÈhªSEÀ0èb™a%‡. ÑH¶\0¬‡.bÓÅ2n‡‡DÒe*’D¦M¨ŠÉ,OJÃ°„v§˜©”Ñ…\$:IK“Êg5U4¡Lœ	Nd!u>Ï&¶ËÔöå„Òa\\­@'Jx¬ÉS¤Ñí4ĞP²D§±©êêzê¦.SÉõE<ùOS«éékbÊOÌafêhb\0§Bïğør¦ª)—öªå²QŒÁWğ²ëE‹{K§ÔPP~Í9\\§ël*‹_W	ãŞ7ôâÉ¼ê 4NÆQ¸Ş 8'cI°Êg2œÄO9Ôàd0<‡CA§ä:#Üº¸%3–©5Š!n€nJµmk”Åü©,qŸÁî«@á­‹œ(n+Lİ9ˆx£¡ÎkŠIB›Ä4Ã< ŒÀ šâ5mÊnÂ6\0êÀîjÀ€9èzĞ ª,X‘¶í2À§§Î,(_)ìã7*¬è¶n¢\rÁ%3l¥ÃM”ˆ¨ \r²öã¢m¢ä‡KÑKp€LKÂúÙC	‹€S.ëIL•G3ÔW9ÊSÁ°³“TŒJzÜDÉ‹d†¾6­ò[Àí\$ßK’+¬ŒÓl÷CÔT»ODu;t§««tÖIÑTÒˆJ©î}F¶ ñC\rYÔËÄNİÍ5,àSCélB…ÒÈ0·×TdÿX¯sP¶*5ÁO5ÏÊ!B§Ää½TAÓyJªÛò2rÜõ¤Š¯6å…İµ]!y(ØÉ<Vøß÷³µ8À­\\CËâ<ÀPHÁ iŠ†-g(“Š|”¤MÎš×u-•¬n¼I«ÂºEİ\$väMÔ…Ey…cdHl+r¼\"òqÄ@yúÂC…ªúÜ·J}×F5¤=_{ dıAŞ•L_Ûn)@¶F\"·4:Ô 5éc…1úÙ­¸¹Zƒ\\ÑŠ5R`x*óf6ú@A²Û4£´mZÖJ˜må6hÛîNÚŸ44’p\$Bhš\nb˜:Ãh\\-\\èÔ.©èM¥ËìM…œàÑ,Í4Èy`Ø:@SºïŒ#“È7ŒÃ0Ù°%—pÍòÌırl¨XSZz¨7¼ãhÂ7!\0ê7c¨Æ1¾#˜Ì:\0Ø7Œñ æ>c—¢0Œãh}zğÛ¯ĞP9…=ò·=”äB¬b˜¤”êKA*0ˆÒÌ¢Sx®%n¤›8ğÛÂ¸PkÀS<w’«SyÄ+¦Õ”%ÚÜLR6éÕ•u®ØUÉÅÄ8‰¨Áná\\< /o‘ã@Å¯s«&©\r(ÀRœCs?AÈôDæÃxr_á”<\0Òíƒ .\0ğ0lè\"\rĞ:\0æx/ñŒÈûÃpe@º\$†p^CtoéêßAóv§À:E¾€xk@ø\$†Ğà{ƒlp€ğ†|\\O”‰?½Ÿ@@óƒYä\r!ĞôF§Cs˜]%¹:º²¢°]C£G‹-« …Í¹«Ğ-UÂÔ¾l”Ò•i¢V•åH“%tÇi2@#¨@PU¨`Â®µ„YS;1@²ı:\0P\\›JÎEíE8eØ'æ‹6\"˜Ï7øVòàPıÊêQ4…,‰ ¡ÂVŠ©k•RÎÊ±XIQKtØ@»¢Y´ÕÀ¥\0¦Èsf-Î&wOyà@Ò‹ffIˆÌ!¦Bäƒ\0005Ä\0È˜R®n¥7¦0\$‘ğòw\0d\r+üóIÀç\$©ı>NØ8‡Sã'0r\rá´Îï\"!ü\0€1½ún|éÔE>ùQ%€½§h¦€€(ğ¦uY/ª\"Ø¸(Bd`Ô›\nR†hA\0™Ìî¨”YÖBæTê2RºfpQŒ¤¤F¼ã&úNva0vTÀ1¼÷¥!Ã|fT1Î˜Q	€€3ÓÈ{\"¨F\n“)ç¯ğÓ!¢Lœ“vr¡TJnkië!|&³r¾Ì1!É@€8˜£<³O“r8¥ím&âú(„\nİCQLq'jŒœ>nÁé¨£n2«¹æÛÜºT²ØR|­)(°¤Øt¢h8¦ºÎ\"”æ¼Â)EÇ Õ{[‹˜ [Ro5o\nùßè™GA\rØÀWSCHc\r`‚;Ù0@ƒl§ÇÍæÉ@ÒŒ›.¡ÏIpë\0b“A¸P¨h8À1ádj]ozj!¨pÒ†ù)È›-İ`™‚Ã*n‰½7®âÑVê§o‹¿½'•üt×Ù~>iÆ”ä;‰€[™Oºæåd(†o”Õ[<•píy²Lıåj[A7a‰ŠÌ–fEà\\#u›só¼a%â„DR–RhÌË•8®½¥K¢V¨ä‡^bˆ+àÂ …Gew‘2®éL*j·¨)Æf,\"ø0°AÏùƒ€PL²4ÉêW%âIä?\\j/ò>4Épa…çlùsJŠ¶v’Ã)îgè2fòôtÜ\\ÒYyJf'¢Ş3ÄÈhÛåÂõ™•acšwn(Í¼¡Ù>KFä×/cˆ2³ÖÆ";break;case"bs":$g="D0ˆ\r†‘Ìèe‚šLçS‘¸Ò?	EÃ34S6MÆ¨AÂt7ÁÍpˆtp@u9œ¦Ãx¸N0šÆV\"d7Æódpİ™ÀØˆÓLüAH¡a)Ì….€RL¦¸	ºp7Áæ£L¸X\nFC1 Ôl7AG‘„ôn7‚ç(UÂlŒ§¡ĞÂb•˜eÄ“Ñ´Ó>4‚Š¦Ó)Òy½ˆFYÁÛ\n,›Î¢A†f ¸-†“±¤Øe3™NwÓ|œáH„\r]øÅ§—Ì43®XÕİ£w³ÏA!“D‰–6eàiMÆ~ó}Å“á£˜è!Î2Mı!ŠèÅPâIW³I¬K¹í˜’lğÒmş0cL@ğ#A\0Ş24Ë*š¨#é\n¦ <M²+‰p¨© Ï{ö‡(cZù«\r*ò9+`R¢:¿ ìº#Œbò»!«ˆšÉ˜¥ğÂã(ŞÆ‘dn&>N€ªÉ˜„‹HàÃK ç¯Ó‹	‰ÊŠªB„|ËB¢Ú5(ÍÔÏFâpŞË «ğí*° Ä˜€LÒMcRı7MSdÇ£ÀR¬¨\\søb—JÌjÚ3ÆãLÖŒã\"9*®nbc-¨pÇˆ#XÆÃÆì\"ş2®€PŠ6Ğ“Ğ¿BxÈ—ÀÉ`í*®‰`ôÄŠ{Ğ»«ˆŒ¬C¬•ÔªÅN¥ªêÊ2¿Ó¤%^¤aS/ğæÍ«ò>2Ù3}y_ 6}…hØ•¬%Ã˜ÃU‰@t&‰¡Ğ¦)C È\r£h\\-7Èò.°cÕ>ºBJBF\r‘ë0Í'Ã2«75f9U4tÁ,íhÚ)8İiŒcT9ŒÃ¨Ø\rã:Š9…‹èåŒ4BŠâĞTÃ\rÃªaJ\\'¬´jñ ‚¦)ÏÌRÊÖ3Ê?¶*à„2æ6©pª”Œ¯BËì²\\+5º\nÕSú hÎ[/96–¥1¥ P› ã“Ecºç£Àà4²w˜@ #C&3¡Ğ:ƒ€æáxïË…×>qPË˜Î¹İ\0ğƒããHŞ7áû…5#§/¶èĞÖÂHÚ84ì`Ü:xÂAM\\x4@m`AÙ¯ëEPcj·v—>«kıå#ªjş ŠÀÈ9=[7lã—/Kâp¤2 §£Ù¯‹í{‘²4€((ğ?ôªºòAƒ¨p#Lı ¶Q|xˆé9'fáXV6zsjxï¥ñ1°ØC“‚\$\rOäÃı\nÁ=0@€ú3ğoOl!,’*X@d\r(¸FÎ!²6†H2‡êj¡°fD‡à †G6Ş”d°9›fjà©P†L ÒxS\nŒı†µJôV¹G !Íæ•Ãüö¢éÅ hH™“XT[ì.¥àÌBˆ`aSnœ¸B¦|Ìü,3&mK³XK;	N(0¢©¦!”€„`¨şÒ>êò<¨„Û8rbfå3 ’LCÑæ6„hË…9J@e9…iç<ŸÊC<‚Jl%-”Pœã.›Ğ<¦!†8IX¶P\$Á!M„£YŠ¥VÂÊ˜\$“LBè¥[¨CG¡°¶ÓÚc‚,2‘Î>	PvsYAI<rQ\rŒ4T*`Zpnh¢†r\\›Ü,…\r7LˆxGˆ)› pZ¬§\nè3 Õ <w«*ş0LübVai…'Ç@7À\"<±ãb±ÌÄÎyfbO‚J>qÀ½½òK‰ñ›LrNqIÈWHº=•…­§6Â©C?©€6˜ª”™ƒ	ø\rJØPÅÂüš#…¡ÆP0Õ³*Ù­2aé“àéHKüØDÒcóNÃÑŠ®6¡#W²şƒ¢Í3txŒ±´²§ÈÈ\n\nFQ;€ ƒ3¦¹ÄD\0";break;case"ca":$g="E9j˜€æe3NCğP”\\33AD“iÀŞs9šLFÃ(€Âd5MÇC	È@e6Æ“¡àÊr‰†´Òdš`gƒI¶hp—›L§9¡’Q*–K¤Ì5LŒ œÈS,¦W-—ˆ\rÆù<òe4&\"ÀPÀb2£a¸àr\n1e€£yÈÒg4›Œ&ÀQ:¸h4ˆ\rC„à ’M†¡’Xa‰› ç+âûÀàÄ\\>RñÊLK&ó®ÂvÖÄ±ØÓ3ĞñÃ©Âpt0Y\$lË1\"Pò ƒ„ådøé\$ŒSÓŞLà®\$ÓyÉò¨ü†ğËÎ)ínÔ+OoŸŠ§M|°õ)àN°S†,ê,}†ÏtÒD¢£¨â\n2\rÃ\$4ì’ 9ªŠ²’¬I¤4«ë\nb!£îú†\nƒHàù„\nxØ¾cªJ4²ãhÄÊn Â’8ÌêÈKÌN	(ğÈã+Ğ2‹³ &?ŠüZ\"¹‹¬SÄƒœL»B Œ(8Ü<²HÜ4ŒcJhÅ Ê2a–n|Ä4ÌZ‚0ÎøèË´©@Ê¡9Á(ÈCËpÎÓÄõ\r0Ú¶¨^t8c(¥ì(š10ØƒÃzR6\rƒxÆ	ã’Œ½&FZ›MâÇ.Ì“29SÁ¤92“W ˜e”·M¸ P‚Œ¨«q]\$	»Èãs\\øìÓ¿cŠ„î1®µŠOYU­n\"“6\$ö4½fÏ¶…`2ÄÇZVÒäGL€\$Bhš\nb˜2xÚ6…âØÃŒ\"íT2ÕJ‹Å£d‚4m*Jã0ÌşM©ˆ¦—µÃ\$”âZtÛs² Ş®'Ò»òÉµ<Ä31A2¼2OÄ‚<£Ã8Â¼¸Ãuœš¯/ĞÊaJc‘\rnø@!ŠbœÈø2ÁD9/Hõ»c¤Ê8Ìº¬TNà(xìÚM7)&)œÂŒM›L®MÚ:fK®ªëVòğäÍ¹;	²«p“¨æ;®³¼	¸²š€@&ƒC(3¡Ğ:ƒ€æáxïÏ…Èúm6¦¨Î»ıD\nï£0H^1	+b:r¢ú76\ra|Š>³œÜã}¦²\0Ğ7Îã¦H0è®5š Y,İ¯a\"¨obÀú	ÑP‚Äqƒí–'¨ÃÀ4Ç2¯Ì2Â0ÑÙ}=eZ‚Š@ ƒ\rè¶æÂ\0(* ¥¡B,–Íòq§}.—²\n^Zh ië­m§¥‰bÓ+G¡‘Òt(	àm~Fl0‘TĞc2‰ÑbHˆy4;•µš•I»QëT@ !‘Ñ•“tÑƒ™ æ\"8SbÚM‘8(Ïà'…0¨A“#:'Ä`šuÚdšˆR¡:œ¤ˆ`\\/0©ß4¾Q[“=Ù\0´JÈJíU\rº0ÈfaYÑµS\$}«\"î˜Q	… 0Ti±;“×ü³`ÈfˆˆÉš‚`sÖÙ<J‘—tå´¡+IÕšˆå;Ó”hìòI+å1…‘ö\n“—IY…\\*]=5²¶âÔ¿˜(½ËFºÂ1²\$M †ÀWRãVšqÑL¸B²fR\\\$ µL“ŒkàñóY¡ˆ³€ª0-uhàÜåL´\"¥Ö[³³<…	¿˜æ\rIiù3ìÿ4Ï¶Øo“\n«Xeè“&bÜÊ+İ°õ>›ã‹¢h¶1˜óÔ~Ïì¥;ê`0Œ1N“),{MDšpé–Iƒ=Y³pº“ bÕ8\\k´™ò>tª,„œR4BèUO•@„PXPt@jN”3-ÑkVq(©•@CÄ—K3ì9Tà†£X<S[¬¸ÙQt6^S\0¤cÁkÇ6aĞ";break;case"cs":$g="O8Œ'c!Ô~\n‹†faÌN2œ\ræC2i6á¦Q¸Âh90Ô'Hi¼êb7œ…À¢i„ği6È†æ´A;Í†Y¢„@v2›\r&³yÎHs“JGQª8%9¥e:L¦:e2ËèÇZt¬@\nFC1 Ôl7APèÉ4TÚØªùÍ¾j\nb¯dWeH€èa1M†³Ì¬«šN€¢´eŠ¾Å^/Jà‚-{ÂJâpßlPÌDÜÒle2bçcèu:F¯ø×\rÈbÊ»ŒP€Ã77šàLDn¯[?j1F¤U5›/r(ß?y\$ßºâ¡±Š¡»”Í¦Ö´JòMxÃÉŠ‹(¨³So\0ë4š‘Êu¾˜=\n Ü1µc(Ö*\nšª99*Ó^®¯ÀÊ:4ƒĞÆ2¹ïÃXıƒ˜Öa¯£ ò8 QˆF&£˜Ø0B#Z:¾­ûˆ0¡ÂÒM0)¦¦)Jã(Ş6ÂcÓ\nc(ô\r±(ŠÔ¶#”2ÈCj\$±PŞ\n°Ò4:EæŞpÀ¨95’ú8KÓDï§‹¸œÕºˆ“p5Å(ÈCôÏSäıPÜúı.`PÂ7Kct0ÁpHRA‹Ù050Ö2@ÉĞÒ;Gc*,0Ê\0P˜˜2\"×½ƒ€æó¡kÆŒB}89¦±\\9.\"Í@ÚÒ>¹ZŒ-m,×@P„<'d\$Ct=ŒµÂŒÀM–66uo0ÖíjÖvå³m£V|Uh£W¬Œ.i[öbò¸	¢ht)Š`PÈ\r¡p¶9`ƒ»VÕé\"(¿C`é)	J3<3Ê©Œ³F3ZŒ“tÄÈË¤Ír4»á\0ÛF\rÒ\$5£šçg&ólÃ84Á`@=e˜’üIm[şßĞ#lnØ6ÀNC‘ä¹8ß”¸Î_•fSyC8•¹¶q–¿yŞoFÙı§“hq³£Øy\"}¥i™^[§æ(¾=š&º¶s¬¶:Ş}›èŞ„76Ò#]&…îv5§\0†)ŠB0\\Xîúw¬ŒÉHÚÁMäÎí®¥u¶ß©´É»ä14“4;8»)–cœÆ7ÚC´F9N^m6mÑ[¿¬Ì¼óæV¾¹ï1.p\n*I0 m6˜4YÏ_ØŒ<Xxß¤(Ì„C@è:UÁĞ^şè]RoxÈ\\”ŒáxÆ9…êËö7ucp^/Ãä3Â~ ¾1EcpÖÂHÚ	ÚD\rÈ<ıƒÀ^Aó&BdL¦l`ì\0005¤`bd]KíwJüÒ—LËIª|bô’‚tœEI+/%î ôwÑè™j„=ªÇ„C„1ğ±3s€‘ò51†€@@Pº\nÕzçAAP-ú	H<íû¶'1Â†.ˆ¢[mµ#ˆáO/3„ÎÂbvàNg„¤èš¸°´ë¹ .ìú ”¸pCª{\rLéõ·R\$cÕ!@Å\\‰\" ÂP¡ğA„3bşÍ{u(Ä„P¿¦~•éû%m¡2R€`Î P	áL*ÃZóA\0Z€IÁ¬°³~£‰C7t2’ŞÍzu˜4†pêpÄk5!ÉE>¸ƒ™+\nÅ¸ ¦Ba%\$åX6—3„Èá\$Š9†ĞßI°F\n‘(ß“‚lPÜ½g„q2±¢—(ÉĞo1éá>,Ã~ÏzZ/6?§s~§ñ¾\rf¾G†ô\$í\næ}†¡?QY?7ò‚F3VP¤ZJ\n–X\nÚY‹v©óFiW{¤´&®u˜ºNÕ.¤K¶™Ô˜¼@PCaá°‡KÚÉ8HVœBˆÍ¨[‡±µõqŠASm95pMƒ#\$‘‹0ÉDèU\nƒ‚Wçq)E¯Ó4™M–à ®!Ê¹ÉÚbÍéõ(0´¨Œ×§ÿ\\«E<°šŸŠø¨DÈ©°®å×*ãlÌ,RÊ‘ÊVáÉ¤1è¿¬Pv2!,UzDiò n²ùF\"#ì~Ñ‰á\0Zƒ\n›Z)àµ”Rv]…i#òp;DËr§#•ÇxğilBÛ¦S­B™fDéÃÍİ»A­D¡s¤à®h½EF¡+ZOË€\n	de®+\0ñ;ÀSş¿X<©†+‹ùÈ³ PÄP™EƒL…Díq¥7¨ˆ8\0";break;case"da":$g="E9‡QÌÒk5™NCğP”\\33AAD³©¸ÜeAá\"©ÀØo0™#cI°\\\n&˜MpciÔÚ :IM’¤Js:0×#‘”ØsŒB„S™\nNF’™MÂ,¬Ó8…P£FY8€0Œ†cA¨Øn8‚†óh(Şr4™Í&ã	°I7éS	Š|l…IÊFS%¦o7l51Ór¥œ°‹È(‰6˜n7ˆôé13š/”)‰°@a:0˜ì\n•º]—ƒtœe²ëåæó8€Íg:`ğ¢	íöåh¸‚¶FÛşÈA´ŒàwZv \n)Ş0Å3Ëh\n!¦~Çkjv¥-3Še,Ã’k\$SøV¢‰G¤Òä˜)ÎNS:On&^ïn:#‚ş'%Î äÇ4{Ú¦##°µ°8œ2ƒ´\"5¸C*É\n-\0P˜§¦°:öß!K[¸ì¿Ã’Ø;\rÈ˜Ş‘,ğp˜ä-pp …2(¸ÜĞ£ëTZ'-`‹¡£ @1H#(ÈÒBj9CPÂ\"ãPÁxH bè'NàĞ;,ãšÈÍ'î›p¿ÃãhÒÃ	Èñ8\"³6;‚(ZÈ¤ PŒ9Jb¤ŞŸB²ø40ƒ3¦¥AÒb™>3Ê:Ò\\ŒØ)ºFR’ ê	@t&‰¡Ğ¦)C ^—‹fKWV(»BÎí¨ÈĞJccò#z0´£xÌ3NŠ£ …<<fÚ°p¨7ÁQÈò\rÃ˜ê1Œo¨æ3¬ôzµab`9ZcÏL©kZ‡#MÃpêš…˜R“Š£8ò67Í†)ŠB0Z±¡*XZ5ãtkIHÍ¸äâ5°óó„V<±”è§m\r#|t¦ 6hØ.Íeâ/ÆĞÆ¨Ë“¥*€äq|49\$R;9ä \\ƒ@4'£0z\r¸à9‡Ax^;érCv¯+8Î©Z ğšÚ¸ÈÜ„K ä¹š¾1&cpÖÂHÚ6\nTBã|ò„\"Æ‘6Ë:†9ï(@éÓKš„ÉmÏ¾1 ¨èàÜ £ZÛˆÂ(Àç”°¢`’¤ëƒƒ°Òõ¨:çˆoªD9'	Ó.Í„€(¶oEÒ(ABˆãœÜğÑ®Oåz^×ÂüG®LŒ7£ÃÈçƒ?c–X•%‰s?òc2|:Em@ßdc[{Í`sJ7xO#Ş·¢#Í|ta\0Ü7´8B`4)íàÊ8¯«B±/!‘¨èfŠXc#åû¿à¹–1)%nÄ'…0¨@•Ã½{ä)ğ¾5(×(Aµà#ÌDU¸tgÅÜ¡>gZNÉéì]-í˜1¢HÃª€&,h¥s‘K#¤|”–NĞS\n!0–\"YğF\nA‘–ÖšÜs€ø %“@PE\r¤]›S¤MTìbŒh8B’£ce(ô9·ôjIÔrC#ÁW: äBT¢–Qä\\€ÇÁK¤'gä6°Öxƒ[…UÍÑ\nfh[éŸ4&Œ2E–R\rc‘rrTÒÉ€ª08_á”3¶d^‰£ÂF1B˜Æ;ĞÓÃ’!gR\nY˜ÂT¥ÄºS¡,É™k%SÁ…F¨Ö\0¡†!!üÅ¶ ^Éœ;€(Ş—äŠ\r¬İ7àµ:I&¦š&˜‹‰H_O»£?„ÁS\0Î4]ì~|·R£!RE7†,„ËÊ_ÓA'«Y](¡Khg0Á,¢ĞŠJÜ|¹-ó¿…7Í\nYô˜™p±…Bê¡”E@ó>u ";break;case"de":$g="S4›Œ‚”@s4˜ÍSü%ÌĞpQ ß\n6L†Sp€ìo‘'C)¤@f2š\r†s)Î0a–…À¢i„ği6˜M‚ddêb’\$RCIœäÃ[0ÓğcIÌè œÈS:–y7§a”ót\$Ğt™ˆCˆÈf4†ãÈ(Øe†‰ç*,t\n%ÉMĞb¡„Äe6[æ@¢”Âr¿šd†àQfa¯&7‹Ôªn9°Ô‡CÑ–g/ÑÁ¯* )aRA`€êm+G;æ=DYĞë:¦ÖQÌùÂK\n†c\n|j÷']ä²C‚ÿ‡ÄâÁ\\¾</‡ÛærQÓ¯@İš…S´—¬†J97%?,äaäa#‡\\ç”ÎÂ1J*£nªªÅ.2:¨ºÏÛ8âP:®¦—\r	f-;¨ãL:;L(Üş3£’63 0²ù½âĞÂ•=ê^ç pã\0<å ä	Ã+8éCX#Œ£xÛ.ƒ(&BxŠÙƒ|wD‹ˆ 0c˜ï)’XŞ3  T¯,Ëc”.ğ»Ìdz:¡ŠFiô‡ bò’!,ë;¥ PÔ0KÜÂÁpHPÁ‹¶:¹bş6+CŠ¹DËÛÂ¨Èãr7?âz4¤ŒŞ³ª+H±‚(Zš#`+T,(èáŒÃ0 ÃC5NIBÅ!-3=UX¯Ée€1·¡tòØÃ\rZ9·c”…X+ıRè\$Bhš\nb˜°\r¡p¶5]ƒP»Q1mØë-3Qƒ6VnÚ´ƒ (ßZÒÛ>†Ë}#ˆÛ-7C²´·§ƒÌ7¥c`ŞÚd¾€…¢²9ÑˆX\\P\n8C}\$†4Z *‚& /â7\"ãBx€§#e†#Øz†bx®.ƒcS\0İãù=’dÁQ•8Ym÷—æ6&iŞ9ÂÙêf!ŠbŒ\nƒ}„çŒìğ¢äy,@ƒ6H0)\r£ª3TxŠöşZ¶]„™àì\0Èÿb:-2¯3ìU63c Ã6:¶KÉ¥s[In{ğËÀ;\r\$V¢jõAlÇ-K™‚j¥‰÷3‘„àÂĞÖèD¯ƒ€æáxïá…ÈĞÚ€¡t¤3…èØ^2Zá@:0é`éİã27\ra|\$£‚ß#¾:¨Üã|‚_{ª¡ÓGĞÊ6ÖÆy{Œ9'4²nA9Q¤Ì*Ÿ>‹\$æDê…\0hS%¯LÌ@æµ\râaÅlƒ—¢f^^BÅGHÅÃüi	N'J@­™HBNyÔ)ÆDÎ/,#:p™^ĞPTAK®)ä|9\"„ +m¼–pÂØ-	!¹‰¿ÂF¨±T_dä›³Ö\"OŠWé“¸`ÕB€€È—ğä†£s&MAÎ Â®…±0a\\Ó@İXÂ¸ÇŞ&•ÓVG‰N½U”GÙO4EıÍr0VŠSVn†ÌƒA(JLÂ€O\naP™uøt F\rå)‘š—ŸSğ“d#”r’RÁ\0o†9b‘&O²6&’>&gæÌ\"m	dpê}Ğª_D5”€Ü¥›•Rd‚™)Sy&`€#@ hYÉaHĞØº:GÍ„Rñ˜>T¸úŠ¢£N&pèÌò6‰HChËÏ°õ?Q4ÚªíĞIôœ`wŸÍ€’ÃÚÖÒÄ)˜4šbv_Õšÿv`(!«0Ø\nÂZY€€Ü›\nv!ÜÉZ“’8´e§K €*…@ŒAÁ¥—HZf±(*r¡üœ	îVÖuHŸFiÔ¸Q=¨åPVëÌ;ÆY†uKêb •-rTLxÃ‘ñ€¢üz•1F¤ŒÍ,¹ˆ¦'é®ØüüM\"L|±Òúõ	P\$œ¦el–°8J[–á\$Idi%ÒR›ìê=õIé'%›•T3©–†Úu	`b+¡Î­‡2X×ªøIT¤\$àñSªÉÃ1’Ü!VXrštÈmØÁ\0ªÂ`zğN‰%,@^\0";break;case"el":$g="ÎJ³•ìô=ÎZˆ &rÍœ¿g¡Yè{=;	EÃ30€æ\ng%!åè‚F¯’3–,åÌ™i”¬`Ìôd’L½•I¥s…«9e'…A×ó¨›='‡‹¤\nH|™xÎVÃeH56Ï@TĞ‘:ºhÎ§Ïg;B¥=\\EPTD\r‘d‡.g2©MF2AÙV2iì¢q+–‰Nd*S:™d™[h÷Ú²ÒG%ˆÖÊÊ..YJ¥#!˜Ğj62Ö>h\n¬QQ34dÎ%Y_Èìı\\RkÉ_®šU¬[\n•ÉOWÕx¤:ñXÈ +˜\\­g´©+¶[JæŞyó\"Šİô‚Eb“w1uXK;rÒÊàh›ÔŞs3ŠD6%ü±œ®…ï`şY”J¶F((zlÜ¦&sÒÂ’/¡œ´•Ğ2®‰/%ºA¶[ï7°œ[¤ÏJXë¦	ÃÄ‘®KÚº‘¸mëŠ•!iBdA\$š*¬M\n@Pd0ÈÂ0œ7‘ä7®‰lHæ¡®‚W/Jj°¥(\nï>Îr¸™Ï¼bgfyª/.JŒ®?éœPEˆ¢WK¤rC«…º¹)ï”¹/ª£ö§Jª\"½\0*®b×§¥ÒªÊ;\nšÖÁ0¬:Ø·1Š\"¬²ŒTHÂ“JD ±©fy%³)2ª°‘¢‹’Ó: I.²ÅP[¥1t‰KÒ»¼˜%o<Ó¤(e­¨|¶Ş½‹àä\$Ú=*ñ‚\0à™ÑJ¸ãZÅ¤š¬oiœÙÔv…LM:õÖÚE<”±ÍìÌN+éÄé!™×Qv*ó1:³DÉêçÍF|²é£ @1# Êà¸>2£ğ)rÔ5ª>&¡xHãAŒ'–Ï½J…ÂL’vª¢j–º8k‘z¯Öy\"@ÖÕ62\0ºÚñ\"Tå2K‘wI`PŠ£Ò6GÓú@L,Ê{“zÄ0ÚÊ§ª+ş’Š ª¹-a£.‰£\r“õóCëyû²a˜6½¢èûİ²gùüå0¤ŠRg/ÍIRg!Ñg{òDJ\$¡nêax¶‘§¼h»+9òÌ_<‰rFÇ>º¢b\rƒ äNª ë(Bù_¿è´A{§nHîØ1,Ìª}-µ«¢/Xó_0ß º'¦å©¬Ñ¥Äîe‰HYíÂ¼øª”¤,ìÚ=]ñwCh°YË>­-çDr¶¯€Éºı3“lÍKÿ´ZjÏ‘ájJ5ã'IK{ÈµŞc¬Ôú/¤Z^¡½7éëºñöİãİ>Å=ğ'·Äòcå!/­¿uaRÊ†\"GÅ\"%\$œJIhC\naH#âLºÙK©íR¡M\nãN\$Ç ƒèU+­\$i@âÂ“_ÃË(è¸Â‰tA\\¹Õ.eEá¢—¶É’º%i¶#½w¦³àt4%\"°Âˆˆ*}‘I&,‹‘O³R  ×!ì\\ĞÀAB`xA\0hA”3ĞD tÌğ^ä\0.!6†ÜC.\ráÈ3‚ğÊ¤pxaA¸9†ß\$ †FPé‚øb\r„7°D™º4EiØú—%H~Áà/ ù\0¡ÂÊTKIõs\0‚\næ_Ğ	õ\$¨8V?gÂ/Œ)\$€PÃ¸VNÉî_¨Í\"ºyTT¥aöVÍz!Å&Ö\n©øf§oXOIz©KÍÔé›WšIĞuLü¨¢@¦IYOVÈ±? B|à éÉš‹f`cŞª„j!#–° € ¹cÀRúwÈ´Âò¼Å¹¿*‰f)ËrbI‰%g«AÎ¸‰L(¥2GpŠvHŸ\n‰qZ	J ¢¹’}§‰¤û*²*#HTò1Ç­º’dĞbMÙtŠˆ±bœ9^ùƒš&Îğ„ÒÈU]5T5“ú:·‰”ıe\0€ ÈI\r\"\"{©Æ•³,÷J‰I¶z³òPc…‰…MÕ¸¾J¹i4À\n<)…HL3„á':•İ\0—³ƒKÊºp¨%ˆ¹¤Ê¨øª½]¬æ@‚2z¹\n-«Ş£Ä#/*É ^ÌŠ'˜0='ô0ıVª\\Ò˜¬üÄRŞ˜Q	”àª@‚¥ŠTj`V´^f)·µâµ',ì+´µÓ†\"ºuş•Ø©£eÑ€OD²­\råå\$w€°9¬üN\$ë-7À„_%ü’o©÷‚‡d…Ÿ‚~éø-¶Öz:[ùØ5óÀ±¾ÉâTğY#­IV„¯jX*Á4*<åB\r€¬1†ÀÒÃ\\Oˆ–[=/‘5Õ´e®JÅ@ÈÁäQ¥XÄ¿¢šÿ1òHÖáÍµáAD¡¹¤0ª0-(0iêv¬6/8RÓ°Æ#Py‰‘`w²İé…;õlÆQ”ŒKâA2¯Dš[?síqIš‰­˜l{\rĞ®TfC8S!cu“	 ]HAHRå ‰~8G©t\\tÈ¦U^sfWRÊÈUÛ®/)Öéµb¶ÌûÇK=æ…u.A…˜í@ãc\n(É~w‰Š¾“ü]4şŒ\"¥´­ˆçìK	\0¦†2É¯G¼I®\\:\rvØ‡ç¶ÈÎ^ÒåpVf|¿3ˆ³kjJïOÂ*Aâc½iÎıÉ_ù¸ÓÔÎ€,yO7»æoyìh,Ş×^Ä”–•; dT›Øw1bvÚ (FEçh4v´“AºĞˆ¿I\$s·ºt6¶\"º'~Íâ¶§Ï˜…";break;case"es":$g="E9jÌÊg:œãğP”\\33AADãx€Ês\rç3IˆØeM±£‘ĞÂr‹s Òv7‹DYT˜Úaa¬b¦ØâE2H%’é„Z0%9¦P\nÊ[/Š›¢¦YôË2†Ìh5\rÇQ¸Òn3°×U Q¼äi3ÙÌ&ÈNªt2›„hñ„ç2&›Ì†“1¤Ç'Lç(>\")»ŞDËŒMçQ ÂvT£6ó±¦>g‹Şâ§SÃx½Ë£ÈüÈu“ë@­¾æN <ˆfóqÒÏ¸”prcqŞ\n)çìæ}ç#u› Ò]üri¼Ş&fÉËvIÁ›æà¢©ÏP·Ùÿ‰Ö :›Œ\"\n€Ø¿2Ã´4¸J¥¾ê à?j Ò«&BÂ ¿@P¨4£HÂ‚Â¬Îr0’%/Àæä@ˆšÔ6Œ¬¸#©köàpÂO4J)9MàÊõ£ äa•±˜™¤Ãšfå­ÏdF“Ä¢‰/Ã*s!\rëˆÜê·ÉC†âRŒ¦‰£*‹2±óŒ\0Ä<¯\0LÑ5Mˆ¸<ÎK`H…á g<† Tœæ°ÃI†YÊîsš Ñ˜9BŒD†6/‚1Œt0,³\"¦í0\"¬LFâ¹âÈ5?#¢6™Í+7Œ´êT¼ÕÎp]7M5ˆÂÓ# Ó	£iÃË P\$Bhš\nb˜2xÚ6…âØÃkŒ\"è+AíÃÈ=7òÜ4¹ÀPÙ3,ÚVÆxÌ3<ƒr®èE-+œİˆqLº” Èå+Šƒzv0ÎóÒˆ¸æ3©PÙ*¬ã˜X½NÂ3×kÒÎMô€Ü:¯@æ¦XÖÉb˜¤#;#²òVÁ\0­0ƒ³÷ÕµúQ‡ÃMz6ş©PÌ·\r¸l˜˜Ş—õù.Vü&ıËa†T§ÕRd)¢ô²ôÃŒƒ®¸0âqæ˜9#˜Æ·ŒOÜFá?í€có&¯ËÂP8/Ã˜î·W#Àà4±ƒ \\ØĞÆÁèD4ƒ à9‡Ax^;òt'E!rÜ3…ëÏ:<@Hãˆ„KÚNÔœP¾¾ÃXD	)?8¡à^0‡Øâ1‘ŒÓU	¾NˆÌR›ŞN*ePL’.“%ğÄÓR:z0ÿ£ì	y3{<àª¶‰ºoËöşû\nóÑ{\0 \$\n(Ş’\$Ír0)JŒ ŒæF‹1^gµPòÈ¨cm¤€ş†È©g8aÈÆ2LRÃÀc9KıŸó„Á1®M\$ğP“ânÛÌ9#gáÁã©È°_qñlgÄ0‡Whl8\n	\$D<®£šJ“Å Ç4Ø ó&CŠa 'È…Ğ@©œÁ(w¦J²@ïb3x5È(ğ¦á¯|êè,\0@bØ!Aphá®‡£ˆI±8'K´•ÃcÂH ñƒ‚§›BƒˆaHB]R«ıİ#4g9z5Œ=	’´Ô\\\0S\n!1¦ğ@«\0F\nÆ4’TI^\$L=Dz¹™!\n%'”ı+iNAåI;0FÃ,e*°o)dí¿V¬™á\"¡„3\"Ù|­æ±˜DbbLe€f>\r€®gä—cA’åÌø·„ºEY'aäÈ#b@Ñb[Í; €*…@ŒAÁ\rÇÁ–‚d›Ì¤±“ÏÒ[+ˆ™L¼û-Òùî•4>FÈì– Ä}J=³¢30&3•EàÒ€ò¼ÿ©T\n‘ƒl³0¦İ…SŒy Ò!/4v7 Ú\\Ş?f0¹†•À–ƒmzk•‘™j\\äüÀŞ„ÎE¨ğŒ·@ÈÄCSLù—3ú©&dŒcçŒÇ\rÙ#ÓÖ(ax‰ù‡Ÿ£v[õ1Æ~°„}©\$aUJô½QŒ	}•ôU„YÃŒ";break;case"et":$g="K0œÄóa”È 5šMÆC)°~\n‹†faÌF0šM†‘\ry9›&!¤Û\n2ˆIIÙ†µ“cf±p(ša5œæ3#t¤ÍœÎ§S‘Ö%9¦±ˆÔpË‚šN‡S\$ÔX\nFC1 Ôl7AGHñ Ò\n7œ&xTŒØ\n*LPÚ| ¨Ôê³jÂ\n)šNfS™Òÿ9àÍf\\U}:¤“RÉ¼ê 4NÒ“q¾Uj;FŒ¦| €é:œ/ÇIIÒÍÃ ³RœË7…Ãí°˜a¨Ã½a©˜±¶†t“áp¨QŸ–lÛï7×ŒüÕÁ9äóĞQ.SÃwL°Şìëá(L¦èG›ye:^#&X_v ¤RèÓ©‹~2§,X2­Cj*@(Ò2<ªß,…â<1A`Pœ:£Ô  Îê†88#(ìŞ·ãZ‘-!-£ä\nÉxä5„Bz:ëHÖB8Ê7¯èµ/âd\nŠH2pÓÅè0·ÃrL³½#Ş‚B`Ş¶\"¬	Nxí ò\n†K.ª4CPÊˆ ïò¤„³Š2:,ãË3 PHÁ i@† P:'@S\$4¯ŠV‚°LµB€Ó6/ğCH;(Ğó\$¡jVå	q::R£*d„Ò‰¸Ü£C­ªHÜ¤”j©5Í£¨tü/cr<A‚@	¢ht)Š`Rª6…ÂØóg\"ì`‘·Œ`É Â£d†2¬¼\\”ã0Ì§­¤ÍÅòÔ¸—ƒc|¸Jî+<„Z„1Œl æ3£bñ-¡C˜XÓW Â3£/b^8¨SÎ2…˜RÚ\rğ Ú0ªa\0†)ŠB68=OeG<0Í @;-#mê:·cJkİ­ğÙ€;iJ^7'¬*s1#L®ö\rïdR3½j¦?ßëfL¡å²ë2¦©»%HçK°ğ8Mˆ0\\øĞ9£0z\r è8aĞ^ûˆ\\’f£\\´Œá{é½\r`Üh!xDÓEÍ é³‹í“5„Ağ’6,‚7à^0‡ÏÂ µÃzÓŞQ0@©%P&.ò™ç Á}+4’ |“~6?¸Â¨šˆª‚½>CKè2;ìÏ0IRX—›KF4ôolƒŠ@¡Ş÷ï³©@ÿ\rkNÈ5Ñu­ ¯i¼‹§‰kƒwV¡]£È+½kïOhö(J'ƒ¥/ÈH y2À€’s:|L4Æ¼6@âË¸fJÌ¨ ­rP Yô™˜8ñY¤&¡@'…0¨_%,]´3ØFÃI2\r»ìzÙ9ÁQ„-ş>â¶9.&V?¶LR	Ph\0´Á«Pèe c1‹ÕÈ†÷`oĞ gL(„À@'† €#G¦µRNŒøÁ\$€E^vCÌèÃ3R:-YÁ»g`(*²Èy™»9*aÌš„´Ø›¢CA:˜µ´u›Ïj\$¼5w®rWiÕ—œ± áYÇ9(%\$0Ø\nÃY3#†6PØRã	ÆzåÍÑÀª0-,77<sI«%md©ÄëPXa@åÈ†‘Ã\$Fã°e?Ò=`›/Ã0yF,Ì“Ùªš?0h0ãBşÌ\$ú°8²ˆÁJC“çYç9+„“P˜T`k~'8Ğ“¸–‰!ÍK1Y2–’RjËüÇ-Eø¸|LÕ¹SrJÁN\nÍb	lÍ ªs0É+ÿSC(‚8÷Z\$>ZóH‘“£”\\K™o\nH,:—ÉÌB	xi! ";break;case"fa":$g="ÙB¶ğÂ™²†6Pí…›aTÛF6í„ø(J.™„0SeØSÄ›aQ\n’ª\$6ÔMa+XÄ!(A²„„¡¢Ètí^.§2•[\"S¶•-…\\J§ƒÒ)Cfh§›!(iª2o	D6›\n¾sRXÄ¨\0Sm`Û˜¬›k6ÚÑ¶µm­›kvÚá¶¹6Ò	¼C!ZáQ˜dJÉŠ°X¬‘+<NCiWÇQ»Mb\"´ÀÄí*Ì5o#™dìv\\¬Â%ZAôüö#—°g+­…¥>m±c‘ùƒ[—ŸPõvræsö\r¦ZUÍÄs³½LÂv4›ŒıK©\"ÑÊ[˜–±GXU°+)6\r‡*«’>n?a ¥&IYd„—ÈcC1È[fâÁê„U6©	Pœ¶H*|¡jÚ®¬¡\$+TÉ¬ÉZU9KIh‡*°sƒ²i	r)MrTX¿c,×¡É‚vW<ê¢	41\"Èˆ=ÑYP¥?Ä:¢‰–oñÄèR@Í7Lóx–¤hì¨±¢–Ë¾©‹&»¦õ¤Ìœq7DŒÒG\$±ÚB°%vıL.	^Ÿ\"Ã#Õ-@HKA>´#“Í\$;æ»@PH…¡ gJ†¬còÉêXÆ¬iN +L)Æ¬ÂR=îÚˆ•L	rëÊmºÂpªLÁ1:›Æ°¡cÀeJ:š-2¬şÑÎºÂ[2l²_&Ë\r}+Tmr¨”|¯±´Â'\râÕCQòqL–Û‰§ì<˜CA`P\$Bhš\nb˜-8ò.…£hÚŒƒ%q°p´ê•Ù€Pä:\rƒd’”J³ÿu!YA·KK9¬1Ãka¬-\$à²Û\rL¿gcDRî6=Z‰ãô<ê&ÄQWË‰\$XB%mìa!ÉŠ~åpjº³Ìyflşj£\$YÍşYî?‘š\nJıh‰zifD!ŠbŒƒxÖ2Ú‚öTêtL‡ÙÚÂ•2ùú\\¦WEDÄ:–yX\$¸kŠV\$·†D¨Åv¼-BPn®?H^>ÿ j¾ıDÎnœbâÄ­‹²µ06›1‡HÜ·†~ÄûîHŞC‰+:ƒ@4C(Ì„C@è:˜t…ã¿¤# ÚğŒ£\\7C8_º{ãÀéºcHŞ7áà0û£(éäãØ0ÃXDZiÛùÁ@È€xÃ>LN¡Ÿ¡DÆN¿*Iİ­²¦äĞJ+!äEÀt\nHP;”?*\r*bf›â44D‰x­4pÖ[v -á2©äB©ÖO°a_“B©\nkO\n ( …ÍÔLcë™f\0PPÁK‰e)Ñ£ W\0oyÊFHö'´\$¢ƒc.0®*3”QÌ²5)­	\n#¦ LÊËN3È„àÃFŠéœDo®\0”7|qŒ\$67fõ² ’FƒÈo €2•ƒxt#mÑA@ĞC˜ x¡Ä:†Pç\"ƒ0r\rá´¬ö“p\r\rĞ0ØÃ˜e”R@\0ÂÃ,_2IõÄUÆ·\"qÅD´Ú;–°Xå£%Ô¨¸ø³ÙdsGë‘ôvI#²aä‘ü¶–ZI:Ë‚åpú%©Bœ9±Hh…Ÿ¢Â„¦J+Äà)…™/É²}Q¸Ó¦f¨‚¤=tŠ€‰ÍÓPçÄCWd5İÍIÎêÃipééX°|D‘uƒb†\"ÄúÌ›6Y'Ù¯4hª|šeŠ‰/uEİ ¦7ª<ºÅ)L4¬•Æ:Jr‘»OHçj=¦„Ê\rJ\"™ÄÂR‡@ä\\¦\r!Œ5¦(„ë\nã®!Ğ}u.W\0†Â¨TÀ´2ìÒ¨úˆ4Å9Ó™ŠoL	äõ2)‘ÔÕ>Ñ'MhI†´.ŸŒy8©è@S´ wñS¯'¸ÎŸRé‘u~;f|üA•jAÊQ¹§“zŸS§Wbİá] î&¡›†ö~Šº\r©Äê°MiÏ¢Œœ÷&±gÚ\\ŸÉ™E“d¢!v„¬öTÕaú]¤õŒXÖ‹ˆ§“Qg€ÄìÒjêÃ¢%ªQˆÎ„NÉÉ[* õ™T˜ãîpn¹«@²Õ¯·£Nİ¯¥‘ÏU°–,›4F„\\€";break;case"fi":$g="O6N†³x€ìa9L#ğP”\\33`¢¡¤Êd7œÎ†ó€ÊiƒÍ&Hé°Ã\$:GNaØÊl4›eğp(¦u:œ&è”²`t:DH´b4o‚Aùà”æBšÅbñ˜Üv?Kš…€¡€Äd3\rFÃqÀät<š\rL5 *Xk:œ§+dìÊnd“©°êj0ÍI§ZA¬Âa\r';e²ó K­jI©Nw}“G¤ø\r,Òk2h«©ØÓ@Æ©(vÃ¥²†a¾p1IõÜİˆ*mMÛqzaÇM¸C^ÂmÅÊv†Èî‡¼ny›hîúaŒRkz–\n(H£X‚\\Z`\n%Û:Ûo¥Ië×ò™Ø‚œ-“M[c©¬æä¶j’Œ©iã82¡C˜æÙ‰«›Š4¾Csæô=MAHÉ§‹@ò84àPœ:¦C”&(4¯Pæß„>ÄIÛR\rË¸+AÈ #Œ£zd:'L@˜: C¢_	ˆ£#N7§P£ßC	ˆÒ›%ƒÛ½HäÀ£ ê		cdÈæ<µÃ\\­,(1ŠîÄ\n£«ş¡¢ÊÊ†Œxì:!,ê”Îì@Ö<¹Œr>¨\\t8c.3›ÏÌÉc”&?M<„€B“ôÜ\$Iİ“˜¬–4îÚl\n¤&-ƒD‚¶2`P‚4ÕÌHÓK¨V LpJ‰U£Y†[U(ĞÒÓ¶3\\6W’€<…ÓÍ€R\r‰UYSUfZˆƒhCs×.	Ğš&‡B˜¦€\\â…ÂØíyÂìgRØ@”1dx –3É„b¨‰4ÙŠ	b]ƒJ²»a-ê3[\r#\nx6©‰J<8-Š`Üä£sE?îÂØ7LØ“E1cÊt´#K`X)(û:œT/4ûN®FLÑ>«‚ü 6V‹Œãxê­™I“4h“Ôå“;` ¹g1=™¾r4çrÍ’”çóĞA¡'\n\"y£dúLg+UÉšìb˜¤#¨;ÇÁápAV[¯`@6GCAŠìÎšîšŠ¼@ç–Í„c7y Òš‰(ÖK ãu\\7M-Å­K*x™×#s)HC[áXÒ¬Í³«»R¦2”ÁˆèĞ9£0z\r\n\0à9‡Ax^;úrO¡.at3…ë¿²<HCsş¦á^9è/ŒI6XÂ£æĞ¥!à^0‡Ï°æÇc|Œ‰.êÊhëÿMé…1‡bØœØ\ns©¤”ÌJÂ|4‡dR((cÜ9‡?fí7 šiÚÁCH\0\0 øƒGò0@\n\np)3E`2³„*«Å!¦AE°š…fè®Na‹`NQ­³ŒTÃ¡5	eÜ bxí	ùA#ÌŒ6†Òêâ´.†©@u¾à‚ZR ìx„b8ı‰¨J3„:8uš{m=‘d&òé !‘ê£ÆÍÃ™}?f¬RGCşÉ«Kgòœ’\0 Â˜T«ò,–È¶à‚„N6Æ¹~ÓèÎ#À#±–3”æpS0iää‘:CNQJ¡wèÌ‘FÒL¿‘8x„ğ)…˜Â%G'*\0tÛÚÛ7ÅıÓ·ÕÂ`aÄ¼˜’òRTSŠ@§‰gBw6Új/DÙ¾E&Èe”Ç\\ÖMç\\zxhHÎx6¢‰8UÉÛqà\$%Ïç	pŸ%Ö“P†`+6ï-­Ê0åmmùdÀ”,PÊ!ä¬ªà8£K6ÔdKÙ„ËF“ÎC]3)Y´6.İŞE\"OÌAƒ4F –pÊßŠ!6¯%\\‡@pÌ)‡IGÀÕTƒŠaÏÁ;Aæ¸á²sEyìpÒÙ™dî4Õnæ\$“Ê&5YC‡jğQ}Ödè)ByN´ Ç’\nP\\’÷'æˆô8‚™R¡ôLIáPÂJå\rdé¯6(™å3K‹m:cÈiv†’Ø";break;case"fr":$g="ÃE§1iØŞu9ˆfS‘ĞÂi7\n¢‘\0ü%ÌÂ˜(’m8Îg3IˆØeæ™¾IÄcIŒĞi†DÃ‚i6L¦Ä°Ã22@æsY¼2:JeS™\ntL”M&Óƒ‚  ˆPs±†LeCˆÈf4†ãÈ(ìi¤‚¥Æ“<B\n LgSt¢gMæCLÒ7Øj“–?ƒ7Y3™ÔÙ:NŠĞxI¸Na;OB†'„™,f“¤&Bu®›L§K¡†  õØ^ó\rf“Îˆ¦ì­ôç½9¹g!uz¢c7›‘¬Ã'Œíöz\\Ã/;{ºíxúkG'•®œ,shy»¤f3a}á¸ÎîB«¶6\r#›+£ª€“µc¬¦`NÂ%\nJ< LˆÒì¡*¢®¬©Šâ¼¢¹ë@!	†W0¨è¨<\nT @£\nÜBpŞ6ŒLª:\"FÉCv\rK*KğÓB“82Œ#¨#²qÛ&±'	Ü\n#¦ğHĞ9È1Ã¨©-c]0tŠí1©¬m\r¾Ìé7»jÔæìµˆb†Â»C,r²£`@ÉŒ›Ğ:\"ã @7Œhè„´u!I#¨8Ò\rÏH…á gP†2hÊ›!•C¢Ó%‰\n_G˜eÈ*Jä´6¶”ƒHÎ€P§DTïÜÂº\"‡ZĞ P‰A\nc¨ÔİB€Òa•Zá,(Æƒ,}*7IÂ3¥	ì®É«Ãu¸É…Ô¥pÌ—\"ìª79StİeHæ(vÈÜ0½\"@	¢ht)Š`PÔ5ãhÚ‹c\$0‹¬ğ¦B*PÈìÌ´Ïº-C•Räˆ—5ëã\n\nhcSŒ›±aŒª+ÅZ\rã‚¤…c™¬è„æCBab½q[­[~S£«S(¥8;w»ˆÛœgSNfÂæÌT¦ÚRèÈÉ¤Ğî›§º½o×t§B Ş5Œ¬È†)ŠB3Ú²ÀŠK«AJTÍo6æ2™¥Ãk<‡²©ÓÄ ÒX@×Û4é^=£eğ_ƒdrŒï¼Ï1—µÊ¬?p\róBPÖ¤w/9Ï.¨x¨Ì„CCx8aĞ^şH\\0Œ›Šr—á~óéCÊHì…á|0W éàé6\0005„Aò>8Q%MÕxÂ&)èÛ+èSJ¨)£34¦L¨Ülš~` Ñ —DjÏÑÛ5%tØÇ4‡SÕCIğ°–6œrÑ‚B…I¡—®×‚€H\np:‡\\  PŠb¾c™!\rÄ”ä¦×NœÒÂkí„:±ç^§Qá•\\–PÚÎÏ¡ )ç<”™7hdÈ püPÎúÊêWÁ¤:CJiÍI«v‹à7‡•ĞqHˆ0õ’¡PÊQ	/4ˆÆŒQÙA3OÎ4;5Æ‰‚#…!@'…0¨O‹:áÉ€!cª“…Hd.6Ò~á á)My¶Ô£İD”'®#3·î“ñg¸šù“L!\nˆÒÅƒ_@oy„ÀÂ	HŠY[&L(„ÄÔWB0T„i\rë ¨Ò„•i. Et¤Ç\nLIBL¼ê°N¢S¨\r~/s;	•«ów3zp>©Ä¹…Låv0š.ÉØ*_J‹~óÂy fçZßª.q¯iæœWbîkóåÔ/3µ9@m1t!} †Xƒ`+h5¸s:W`Ü¤7Ú‘ÉZc¦éX!%h¦‡#bÃI©¡ã\r’Ä&òv!œ] €*…@ŒAÁ:[è3˜Z<WDÑ!°ú…TDZ£éH^õ*r·B<KI4Äq`+Æ{\$´F*z'%„©Ë`6kˆô…%\nÌˆ\n¢U”Ç \$hY2@ä*æœHcÚ6ææ‰Ò‚nãM\"›¡B?Ö‚Ö¢GEqr(´NŠ^é0}4±µ†Ñi1eA–¦Ú2;@éŸFË¤í˜õ>\ntc­Œ0R‰ÃaÙ1á<Bsùæ‘­Ê©U6ã^RŒ9°Vuˆ&Z`Š<¥5³PÉŸ”";break;case"gl":$g="E9jÌÊg:œãğP”\\33AADãy¸@ÃTˆó™¤Äl2ˆ\r&ØÙÈèa9\râ1¤Æh2šaBàQ<A'6˜XkY¶x‘ÊÌ’l¾c\nNFÓIĞÒd•Æ1\0”æBšM¨³	”¬İh,Ğ@\nFC1 Ôl7AF#‚º\n7œ4uÖ&e7B\rÆƒŞb7˜f„S%6P\n\$› ×£•ÿÃ]EFS™ÔÙ'¨M\"‘c¦r5z;däjQ…0˜Î‡[©¤õ(°Àp°% Â\n#Ê˜ş	Ë‡)ƒA`çY•‡'7T8#DßÀÚq·NJ•ÍƒB;ºPQ\nòrÇ“;°ùTç(^e†·ÈëÉ:àğ¼3„ğÒ²CI†Y²J¨æ¬¥‰r¸¤*Ä4¬‰ †0¨mø¨4£oê†–Ê{Z‰[îì.¸œÌ\rªR8ƒ\nN°„BòßˆNêQBÊ¡BÀÊ7Å# äa•­ûÔİ`S¦¯ Sİ:â+!(êú6RÜ2¶O‚”œËc”h¬¸ĞDÃ{ê°¤HÜ::á(ÈCËJÎS¤ìÎHè¯ÀPH…á gB†/+î1±èª¨ƒA©«ŒÀ=2\"˜ò#Ç@€P¦2¤ÔJ¾²¢*rÕEƒ( ³³{® ŒƒÃó8I\"(ÜÔ±óÈËS‹\rn4Âƒx]<NuØÂÕ?¬p¨@àPÂ3Æ`P\$Bhš\nb˜2xÚ6…âØÃqŒ\"í=PK”Ú*Ü8u Ï²­nÓL²ÚĞ4øäÔª¨¡.0C9NP\"%ŒØ/ëÆÀVŠšCzÁ÷ÀX‘Mø`Â¡\$*üÖÀ*³š˜7³€ƒ'8E™Š@j©ˆ^ğ&'…Íø¾cI^9<ãùV*\rã^8!ŠbŠÈÙÁm¢•©\n©š¥PÔ\\Öª	bò6³Ñ½„_éxŞû¤,*ß\"ÀŒ(„%\n³(êlX\$>C3èù63‡™æ°Eë*JË«NîÓc‡F›n¿¤‡Œ(ĞÍŒÁèD4ƒ à9‡Ax^;ót)©Arò3…ìJÿ°ê*„IºP3Œ£§\$/£î¨ÖÂJI6âÁà^0‡ÉP@Ÿ3†ª«5ÏCç1D*J–êá‹-Vşìx\$42ŒXã8“¥, ÄÕ1ÏãD°@ô²S¢sÖü°Šœ«óu>°@(	‚\n7¤­Ş*ÁKro\nš#dì\$fÊnSkş‚„FÖ‹/kí™7‡ñİh¢…£¬ÃÑO0¥y\"§†OÊ3Q„‘¥òš‡;ón­Ü”†ê›*&Š}š”°\n2É7E\$§)¶ËJ¡+V€”°ÆÊˆaÂÔXÔÏèP	áL*0æJ°¨g=DÎ4”tâM»Q}Ì6	ÃhpØ`	›6A¤;¶EIi=C¦¸ÊÔ¯|”Ÿ”QŠè \naD&38Äu]q9'd­¦PŒ±Ü…„˜º±ˆÈğŒšüI¡£±ö¾rY³¼UÄĞ&È\0ÒQe9!•*í†5³\"dÒ#`xK¢ò¬MÔªN\rÀ3&u‚°æ´UÇô0ÌT»’ÊB\r€¬1´pÆıÃ›xQÕ’—ò‘ÙD( ­”rVùCˆu\$/lÜ&SQVT*`Zjs%¡°Â’7ò…RÄA3ñ…I·ô³H¡#(f’¨WR¤nŞ˜+gy.1ò1FF*U±;©&p½ÄœrL+Gá„=DyÖccá›æ6ÊéáK  ›…äšªoÌÁŸ()e×S¤„Ã•+%\$øP¡ÖŠÓÌû4\n©\$“6gLüñL®íØu?D\"YD*‘%‰şe]ÕgQAÍF•ÚkXá¼7É\$*z,RU¨°1µä";break;case"hu":$g="B4†ó˜€Äe7Œ£ğP”\\33\r¬5	ÌŞd8NF0Q8Êm¦C|€Ìe6kiL Ò 0ˆÑCT¤\\\n ÄŒ'ƒLMBl4Áfj¬MRr2X)\no9¡ÍD©±†©:OF“\\Ü@\nFC1 Ôl7AL5å æ\nL”“LtÒn1ÁeJ°Ã7)£F³)Î\n!aOL5ÑÊíx‚›L¦sT¢ÃV\r–*DAq2QÇ™¹dŞu'c-LŞ 8'cI³'…ëÎ§!†³!4Pd&é–nM„J•6şA»•«ÁpØ<W>do6N›è¡ÌÂ\n)êîæpW7­Ñc\r[è6+*JÎUn\\tó(;‰1º(6?Oàôÿ'ïZ`AJ–‚cJ²92¬3:)é’h6¢²­«¯[5 Œ”5Oëşa–izTVªŞÀ¢ƒh\"\"‰@ô\r##:Ä.è£d·‰9f=7ÀP2¤ªKdï‰Š·:”w‘,!BRPÃEBP«ˆ\"¯£=A\0å­dĞÔªêÌ\0Œˆ2h:3¤ì¢OĞÅ;OºŞ7\ràPHÁ iD† P–¸sşPCC°ğ\rãÄ˜©ræÅ¾£]2«#£Êb–-cmS	m›ÿ/kxŠ£jJƒÉ.¾4 Î<B¼‰óGG-ƒevúÏC-i[C	@×dŒ`]<Î¶ujÚ³ãe¦^YVµ°ÏÙöå¥jZÀPÚ‚TÑDP€t6ÆBØó{\"è\\6…Ã *#“¶6\$ RRÇ4ÍFÖã0Ì !L95ŒuZúL/¨¨7µõ«F£pæ:Œc\n9ŒÃ¬1‚ë¸æ\$#ò˜õ°Ü.ê\\ê6®ãª²aJna‘8¢®ÑÎH@@!ŠbŒ˜C}	H³1@\\	v¤êõIˆÃ4Õ˜Â8\rğ£À®RŒ:.>B5Œ,’¥6ÃT|:f«lçLóJô—CVºã¿{`şc½3l€Ò¤š¸x˜\n@Ì„C@è:˜t…ã¿DWYâ9ÔÈÎ¡`ğ¬äCN¤„Nä3©<À¾¾Œ#pÖÂHÚ86Ûèã}¢JM-: iÓ	œ)HÈå90¨Û·î(B’2hPÖh?d7AHGqe»*ÓYµ%Z7\r.3¼íPr5 Sœ]ßÁ“fi|£™°ègS€¡]lÔ¹°@@P(=ş`PSPs#í\r¢‡fvŒ³7WFø7¡Òr­Iá>(D­Ô¹›*İ*ì¹5pœJÕS;2f|Á@èY7	\$D<šp@É®)PiO„Rˆu0¥(3\"J	¡.ähà4 ÆÁ ÔN ¡À0»‚n|EÒ\"ÏŒ˜…\0Â¡07¡Ğ´µ\"-ƒY„9ÁÍ:µøä{\n1H9MàhÊÌÏò %õæsşUš©lÌ\$7zÈ oWDÀƒÎ˜Q	„ÈÚšÓhLB0T‚Nõ:“ÖÈÀ¡2ŠÄ‚\r1B4…d(NÙ5’ÅÒNeÊŠ¥ªEÒSX—D.ÙMÓÔÁ=%»•—6µæŠq—PÎj””(¸æ”Ü9Óx›„0è|Á\\_\r!ñµ)9!ƒdR7\$H‹†ÌÌĞq7Ò”‰\"â”ßÙÈU\nƒƒ>^'¨nóA:‘`äÎ™/a²g®:D%}¢©ê‹Êê\$æy#ä„‘Åt‰ú°w¤¼˜—²úO,õà(¼’¸òBØRKâ„1†”ºtÃ‘„BHùP*ı9f¤Ù›åDbä”<iPÌå>2&,É\$Å;¤QK¢nv\rê\0gÇaH‹0\$ÔH¡U´ÏB‘Íñß£Šf‹WJæc‰Xs:dĞ#µ?NÚ±;áLË2²¿‚YVj=&7D€SÂ£(œŠ¨å z)i¥â„5©°Ü¿ìÑ~A<Ã0@àZÄµz0GI‰Ô“@";break;case"id":$g="A7\"É„Öi7ÁBQpÌÌ 9‚Š†˜¬A8N‚i”Üg:ÇÌæ@€Äe9Ì'1p(„e9˜NRiD¨ç0Çâæ“Iê*70#d@%9¥²ùL¬@tŠA¨P)l´`1ÆƒQ°Üp9Íç3||+6bUµt0ÉÍ’Òœ†¡f)šNf“…×©ÀÌS+Ô´²o:ˆ\r±”@n7ˆ#IØÒl2™æü‰Ôá:c†‹Õ>ã˜ºM±“p*ó«œÅö4Sq¨ë›7hAŸ]ŒŞëµZÍ•÷{¾ìdùC^ßta'¬D…\$•ôò4ç£2éˆ\$îïÃE’ÌN˜“)¬ç¡7^èòÉtÖœs:À¤¶ë¡Ó(³	HóJ8#Ã;Æè :T‰'03Îâ„ºõ¥ÈC	L\">ïã(Ş¿ËPˆ0ŒË€ä£ Ò:\rñ8¡Àíúµ	©Xê5Q«ğ‹@ƒÚœ£Ñ@İ¤Éê…Œ‰4Vè)€ÈA b„œ¨B/#‰Êê5¢¨äÛ¯Îºàˆ¢hÊ\n¸Ò45Ã¨ä2€TFÊCÈë:‰ƒV4Nğ—@5RB!9N‰ äÅ¯cbvƒ²ƒjZÈîˆ	¢ht)Š`P¶<ÔÈº£hZ2“dÜ’´€ØÿL;™1ÃxÌ3-#pÊº%lN%Îƒªd‰\rìŒ~İ	ğ1Œi€æ3Oa\0Ø7Œè@æ)ãò¡\$h@Ao²HÚ„6ã(P9…)h¨7hğ@!ŠbŒ§­ˆ‚H#d¶ƒ8@3E£l÷@)iEË„hÙaUãäÅŒKÕ±F¼²ìwi6ª[4)š29c¼[\$Œ£Àá@£%ğ cDæ3¡Ğ:ƒ€æáxï…ÊÇ]a±hÎ£Ú ğø/k(^4Ã“4:f\"øÄÚ\ra|\$µlÃr:xÂ>hàÊÑòLY.İ¶ú7Øèğè–ˆ‘ºB‘¸É€ÒÅHÉ¼&4ŒÉhŠ÷iº À-ÑG\0>B€('	ÔT2nIĞP¤7§ªBÜÍğj’b4.ÊŒÌb5`ê6½Æ§GïsUÓñ·ôT§éÍğéP#ƒ0#Z€ù¥¢J<±	Ü’È#cš=³4‰¼æ8©‚63Pø@ Œ™úP§pÇiù^äŒ×s²4Šx¦*)éB‹…¦‰|ÎPnïV¨Kƒ¤ÿ’vœÃYP!åˆ7`ÒH	ò)â†3bg–™C(I)0¢ù—1ÆX„`¨ã	ÊI8h´¢µşõCiW¤¡¿\$3(¢˜:EF,ê@‹›¹Ë†\$íş\$`ÓQ	H†­„\"£_\n-\\ ·Bö:Úct	dÿÀV÷ÃHc\"Å–	‡cÎôLál­ñm¢²Z Ñ:¤l“­ò‚B F â&àÇÈüJI&Êxu!‘<5h´–„v2ƒÈêã-\$”¸\$sÃ0yFå9“¤DGá!œ¶yù“KÅCÉÔ6PK²7lD|:˜2JŒ»¼Ğüï”U˜é4ˆœ&ò†peì\r¨´Ç*Õ\rÑ®X1I¼(Ù”f¡U25æşÃ‚-Mbl¢tµßÁDOÆšGBâ	q„\"!VW\0Ô€ƒ˜";break;case"it":$g="S4˜Î§#xü%ÌÂ˜(†a9@L&Ó)¸èo¦Á˜Òl2ˆ\rÆóp‚\"u9˜Í1qp(˜aŒšb†ã™¦I!6˜NsYÌf7ÈXj\0”æB–’c‘éŠH 2ÍNgC,¶Z0Œ†cA¨Øn8‚ÇS|\\oˆ™Í&ã€NŒ&(Ü‚ZM7™\r1ã„Išb2“M¾¢s:Û\$Æ“9†ZY7Dƒ	ÚC#\"'j	¢ ‹ˆ§!†© 4NzØS¶¯ÛfÊ  1É–³®Ï+k3ëö3	\r¬ç‚ÕJ´R[iÒ\n\"›&V»ñ3½NwîÔÃ0)µ¤Òln4ÑNtš]¡RÓÚ˜j	iOÀ4AECIÃÒ#ÏCvŒ­£`N:¼ª¢Ş:¢ˆˆ\"4Î @´/Â©\nC,#Œ£z(ûº­T\"¯H¸äìÁ/Ğ cºĞ2BŠ·kèôó¿B`Şµ\$£ƒœÑ£ô',ƒ²0Œ©šÌŒ\0Ä<ª€L™'J\nˆ<ÊÉ¡xHËÁŠÁ/«:Æ7#«ô'KÒØ—:pPƒ¦í|‘:Mâ(Zœ£ğzü'Œìğè³/ÊÆ·køÜÎ/Ò˜Ë=Œ(ø@µÑ‹])I´|øRƒKLªC\n4àP\$Bhš\nb˜2xÚ6…âØó[\"è+5Më¤=OLÂpÈ2H‚B7ŒÃ3¥ ¾¬-ˆ¬#ö7£\0òå±ÆÃŒÃª86Gn0XĞVØÂÆ­tšK)­â¨aJZ*\rãZ*b˜¤#)É+déƒx\\C4ê`6&ˆÆÔ¼Œs²Â.J…›\rî*Ü¤¶í¿RZ)Üp†/£\$0Üãf‹\"C’j˜¤R.	©Ò¨931d]6ªÃü9£&&F€3¡ÑA˜t…ã¾¤TWŒ‚9Ë@Î¢ºàğò¦© ^5ƒ•¤‹ã7\ra|\$£‚A%xÂ=A>4/-²0ßT˜èÌÇã‚‹%¢bBãäI#ş2ÈÌÃÁ\$+š>•Óém«°<ïJGè>XÃ'nú}C=üç\n@ †4c‚Ğ¹4áBŒ¦#ŸdôJ\nZ!èĞİ­¼òŸ(Õî‚óp‰¿Ø8 Á#¬ªÑÊÁ‘¥”õ)(XòÈ¦2jGÁôRhèÔµzÒò#¨ Ú#&­ê|÷ØÇr8íCUœ³ŞXŞóOúÃ\n1Öğ¦8Ry\$\r )4€ÈÃ*IOm†€ ÃI¶/Fà’àÎ@Ã\nx\$%Ç¾úIMäT@€˜;ĞÎ˜Q	…\0¦À@Œ{†I¤ÉÙ'Jßj!‹HÁ9¦æ9ÒjÆ™™“!b&ÁÈ¶Ã¡È°o(Ë¸ã‘¬RŒYˆX±2b¦qWz™‹°Æ¸¡c‡Â4XÕÙa-ÊE~àØ\nß¹\rnÀåÀ2ÚõÜ#8\r¥ä½²ÕÉpm%º.à@B F à¨ãr›ƒ9-Je¤˜CÇf„T¼£1‡åI·NU‰ „N\n—\"èx‰ƒŸ1d •&i#0yUç\$Š¼\$†Xbx0²,½Ã¸vÔšã-rfHÒû\"[ÔÀe¤…õ•20ÀŞ¹-Ehğ¿GduCz¢8ºt¤ÙM,¾•F5L°Î~@Qkfäb7Æ2ÌÒ@c*ˆÀ!®Dòî¥9•Ğø¿4É.K©!–ÔDı—2ê_ T÷?Eâf‘#@‚¸";break;case"ja":$g="åW'İ\nc—ƒ/ É˜2-Ş¼O‚„¢á™˜@çS¤N4UÆ‚PÇÔ‘Å\\}%QGqÈB\r[^G0e<	ƒ&ãé0S™8€r©&±Øü…#AÉPKY}t œÈQº\$‚›Iƒ+ÜªÔÃ•8¨ƒB0¤é<†Ìh5\rÇSRº9P¨:¢aKI ĞT\n\n>ŠœYgn4\nê·T:Shiê1zR‚ xL&ˆ±Îg`¢É¼ê 4NÆQ¸Ş 8'cI°Êg2œÄMyÔàd05‡CA§tt0˜¶ÂàS‘~­¦9¼ş†¦s­“=”Ğ(§ª4›Œı>…rt/×®TR‚ò‰E:S*LÒ¡\0èU'¹«Õû(T#d	ƒHûE ÅqÌE”')xZœÅJA—©1Èş Å®ƒè1@ƒ#Ğ 9ªˆò¬£°D	séIUº*òÀƒ±\$ÊzKêÙ.r‘º¨S/äl˜ ÑÎ_')<E§¤©a'¤¹Js,r8H*ìAU*‰¹•dB8WÈ*Ô–EÂ>U#‰ÂRT™8#åÊ8DMC£ğÑ_Çò	lr’j¨HÎ³şA‘*¢^A\n¹f–Ã¸s“P^QôŒPAÒgI@BœäÙ]ÂäáÌDÈJê¼ğ<· S\\ˆ\\uØj”„áÎZNiv]œÄ!4B¤c0¯\$Ama‹ÉJÕ QÒ@—1ıM´YV¼–åqÊC—G!t¼(%	CÅ¹vrdÂ9(ÊEÆtœPÕÕ7YêQ%~_ÅúC48b\"s‘åôeÅ’œªÊ¡ÔxCHÂ4-9ò.…ÃhÚƒ\"©>YÈı\0006ƒ“HÓäÖ\rã0Ì6<#+˜©™iVÓˆı<”R *\ríxÚ0ÃÈ@:Ã˜ê1ŒmÈæ3£`@6\rã;Â9…Øå©#8Âğ„`KW¯êá˜Ræ…Ás°ÑUb˜¤#Nó*8Y±„ÊC¸Eşë20ùÚº- ”#úTğ—iR>[£Š@ÁDá>ùäøA ¬o5O)9/R–œ‡%ÊrÜÀ&Œ#›„96æ;ã•^2€Ó›Œp@-Fn3¡Ğ:ƒ€æáxïñ…ÃÉ¸Ã(äy#8^2ßxğáj£Hßø„MŞlÜÈ¾rš€k@ø\$†Ğàmƒkğ€ğ†|ˆÁ	8½W›È\"CY¬\r!ĞØ>¦úCptgâ	Ğ¥÷Fé\\9&'Â\$ö'e6ÃÚDj ´ƒĞŠB¨¨ƒã@Y1<\n (1Dƒ‚P‡ˆ‚¢CÉœ<„‰7ˆ\"S	„bDÆÉ)¨€hÛË{o¤6æŒ¤‹È®HÉ rˆñ_áØª'Å\0¡/tú¸‰æIÄ˜tc@˜˜ibî\$¨«\$‡(¢!	M(båašq (\$‘`òÍ iUæº7áN)ºfáÄ:›˜8ƒo\r € ¾vzñ#ğ°Iãw(^)¸9@'…0¨Ga1Oæ “QMÄ	\"9¸™€Eû@)¢]aÊ,¢!r„0A \"à€èµ\"Íhë1Ğãº2AËÃFfiÃPjp7¾fjià€)…˜1µ5†Ñê„`¨¨nUá¦<˜9è©•rx9·Ö¶•¥TòrŸäHØadTaR*hT&Å æÊF¹\"TU%£bHsc0ä)¥ê¾“ÒšWN)p\nlÄ6¹hCkŞ{‡`Â%)»iĞP4†f¥Î`F PD:¼)ñƒp \n¡P#Ğp€cª‡ˆæ6ğ@fÉ2’#‚uEÈÅND	#…ù¡±!ÖÚİ[äĞ›˜‚ïê¥V[BeÆŠ(:D°¢9Æ8s™\$e¢ #é’CÙS\"dÈYNb\r²ÔÒOIøç¦8&OY2x€U­ÁµäšÃ€r	›/\"TZ¡ 9„ğœ;Á”Û3„Ê¬ÛEhJL–g°ƒ®	Ï©÷t««+š©`¬4]7-|Yõùq™¸ô,Ê…ÇbË0";break;case"ko":$g="ìE©©dHÚ•L@¥’ØŠZºÑh‡Rå?	EÃ30Ø´D¨Äc±:¼“!#Ét+­Bœu¤Ódª‚<ˆLJĞĞøŒN\$¤H¤’iBvrìZÌˆ2Xê\\,S™\n…%“É–‘å\nÑØVAá*zc±*ŠD‘ú°0Œ†cA¨Øn8È¡´R`ìM¤iëóµXZ:×	JÔêÓ>€Ğ]¨åÃ±N‘¿ —µô,Š	v%çqU°Y7Dƒ	ØÊ 7Ä‘¤ìi6LæS˜€é²:œ†¦¼èh4ïN†æ‚ìP +ê[ÿG§bu,æİ”#±õ¦“qŸ«ÒO){¡şM%K¤#Ëd£©`€Ì«z	Ëú[*KŒÉXvEJôLd£ ÄÉ*é„\n`¾©J<A@p*Ä€?DY8v\"¦9ªê#@N±%ypÄCµ² QÖV2¤ñ ĞÀ'd1*ûäèAğaÚL«ùUÇËü<û‹üPËI§YL©6Fªr\r\"P’Å-È§YTT¥Äêşød¡(v…„GÊÑÖSJ%Éu¾çYdDHeÄd—E»*NÏÑu°@@„áx—&t…AÏÈ9[1/iNF&%\$\$ŒŒi`ÆElª-àØA b„`Ë¥“¤A‘1‘TT&%ªJeXêÃä©{% H\"şBi3eMH^FE›AEqÑXÁ‘–%0¿–Uál¦\$jÅ¨uÚĞIiO„µ\r£iÂ×CAÅÍ#  \$DÃº°#ÒYa&¡AÑÖ[Ò>‹cÎ<‹¡hÚ6…£ É¥‚µ6MÃ`è94íH@0MxŞ3ÃcÈ2¹ä\"`YJ4³;¤Ù\nƒ{d6Œ#pò£pæ:Œcx9ŒÃ¨Ø\rƒxÎòac|9gcÎ0¼«v„kÈ:¸¡@æ¹ì\$Ä]d‚f!ŠbŒƒÖiXÏj…=·I¯iAÃñ\nƒIeä®c™„#*B§']6u”dfâ÷CÏn]JC{Ú_hÂ9¸£“gÍc¸Ş9T#(ğ8\r9É¸‡ƒWŒÁèD4ƒ à9‡Ax^;÷pÂ2kCpÊ9İÎŒ£w<8¹ğÒ7ùA}7c§d/¹¹ÈÖÂHÚ87#o’:xÂDzÈÊãõ¬Œ#[^4Ÿ‡œxCpé”ğğZT¶‘ã•o`É ä „¡ 4H¼Ó\0j= €@R}‹*S‚å	€b.,„x³ÀÄbWĞ@ªl™É¶^¡ù&'0a\nrfR\nRäpDÆ“Ö)Eá\$d^5êOQ@Dpş —Ä)–CĞ’:Xğd\r*„Ø¿Pæò_qÈ7¬„8‡SxıC0r\rá´€ÉÜñÇy €1´˜¾o£Ÿ7e(ğ¦kbé¶ÑZÅa[K\"ôÒ™Å•*m)¥<¨ÄÂ<)ê(BÅ	 §ÒáE\"@^%õ9Åß˜éªlå>\0ŞïØø A¤3‚\0¦B` Æà×›w\\‚¤g*„4½÷Fı_¤ºQ²/‡#\\ñÒ—LhY¡@'\"4&_\nYL!cì,HÍÑF¯‚¨M&’=¯üÍÉ²½¢IA]²ZoÎß\r0Cca°ÇPÒÃX zÄ;Ùó7}¡¤33·èPB4¼k!ÕÎK'æ\0U\nƒ€@òC<³ÉP17¹Ğ[\"dÆOLaŒAŠTıf,á:AÉ°ì':—#›Bèjg?B ş¡h á\"2†XÌ8¡–Ea©f^¥ ²¼Y§„BÅ+™+âÑåU~X×FkÎËTe&%x\$ÎØ²ğ†SrÎ+]Ôı£&î¼—RŸƒ¨\">:ä‘*D1[«š`œŒYW5¹^Õ#*eÀ";break;case"lt":$g="T4šÎFHü%ÌÂ˜(œe8NÇ“Y¼@ÄWšÌ¦Ã¡¤@f‚\râàQ4Âk9šM¦aÔçÅŒ‡“!¦^-	Nd)!Ba—›Œ¦S9êlt:›ÍF €0Œ†cA¨Øn8‚©Ui0‚ç#IœÒn–P!ÌD¼@l2›‘³Kg\$)L†=&:\nb+ uÃÍül·F0j´²o:ˆ\r#(€İ8YÆ›œË/:E§İÌ@t4M´æÂHI®Ì'S9¾ÿ°Pì¶›hñ¤å§b&NqÑÊõ|‰J˜ˆPQO’n3‚·­¯}Wâğ±ãY¤éË,—#H(—,1XIÛ3&òì7÷tÙ»,AuPˆËdtÜº–iÈæ§ézˆ£8jJ–’\nƒ*P:-B°Â94-Ô»4ãJ\"òŠcZ¯,(ˆ0Â»~6 ò\"Ã(Ô2Â:lğ¬ã\\P†ˆã(Ş6Æ\"–æ¹lZ/+ØÖĞ¬‰p	B²”µ\nq@á¥ğŞš¡¢‚È”C« ¿Š\nB;%ÏÔ¶4Ë®‚Tµ=cª–—±C\n¸µ£ @ô»Ê\0:Îè Î‚Os´ğ:.ÀPòÏL¸!hHÑÁ«¤2«ˆªşÍc¨å\" #Jüò‰‰Tò†ŠÃ*9¥hh‚:<r;Ê\"sõ90‚') P‚¹1nù.KCK@ÊéØµ²s;EÄT6BícYkz	J –…¤Y¸c\r«fÒ¬h½½4Ø\$Bhš\nb˜-7ˆò.…£hÚŒƒ%uVF® È¤ÇúSl{#	²£xÌ3\r‹8Ê’\nc(åËwÜ¹/ëø¨7¢ÉXÜ<„õ21Œløæ3£bŞ7¬Ãpæ4ã–@ä,ï¸İj«8ê¹…˜R’!ëšl³Ïéx†)ŠB3Nø®*d!#rfù2©M¦âËÚ7¥÷üv¸µKÛBZH%¥Z_bîƒúÊÊJ£ŒºÜæ³óF¸Oøj•5¥óÀÎ¶%5•ƒ¶êº¸Ë6cRF’ÌX•f9cºÇ;£Ààß/Uyo\r0Ì„C@è:˜t…ã¿\\#&r¡ArÆ3…ër<.ypÒ7ÁxDÕLğéÒín¢5„Ağ’6¿{ªâ:xÂ@AxÔJú_´“²®óõ‰cŞ!¼Âğ\n#fcÍRÓÓHğÅ´¸ˆÄÓŒéËZÛo?oèÒïßÑn(Dxúw~_Z)Gh`(€ ~à—?È\0‚tÏÈr0TÄ¥5&f†ıÉQ2`Ä<·p¬oŒ\"Ê%d¡5’Ò^\\HdJ¥€6¡„tCugìaŠ5;ÆTòL€ &©ØË‡B\\S±¨5G8‡S?ƒ0rG`€ »&oÍAqŒ‘3+‰y3Ä(9\"öm\0P	áL*D¨FŒh \naÔ•Ã0ÜâZó‡k5‘€@„ÏJ³‰qÓPÜ‹+i a½\\„£î>Ñ0hÆÔY	û\rîÆC§rË‚ˆL#æpÊ™µ¼‚¤j)Ø•ÇŸY‹q™‰±\"H`R/?ğ¹?™€­\n³#Ec)ÉÑ¡­M+hØø&a/O‰Å9 0˜fZÔ›gfd•¡8 ÚÊœf-q5X\\d\r!Œ5‚)C±,ŠÏy­“PÌÈñ=•jK§wÂÍÂ¨TÀ´âÒ»D\$“®g‚4G\nb;	Š.GV…+ˆb´ Ğ)cÈío\"EZqr#n’Ñş£è¾-ªšyòòD%q€£Œ^Ëéî	ÅŒ¶¶rqN=I8‡€•ÀÚp\\`u=®&£œƒ6ÖÌ±I‹A‘³,\\”‘™sR	Ğ™(É©ĞU¾R5³‘!a¤B…=Wµ^Ô*˜™'=2™0”ØeeÍ}²DÜOpE%Ù\n§Ÿe²“R¦ò;5øğ€i‰s‚1š¹½:f¯æ:s<¡.t‘â:X";break;case"nl":$g="W2™N‚¨€ÑŒ¦³)È~\n‹†faÌO7Mæs)°Òj5ˆFS™ĞÂn2†X!ÀØo0™¦áp(ša<M§Sl¨Şe2³tŠI&”Ìç#y¼é+Nb)Ì…5!Qäò“q¦;å9¬Ô`1ÆƒQ°Üp9 &pQ¼äi3šMĞ`(¢É¤fË”ĞY;ÃM`¢¤şÃ@™ß°¹ªÈ\n,›à¦ƒ	ÚXn7ˆs±¦å©4'S’‡,:*R£	Šå5'œt)<_u¼¢ÌÄã”ÈåFÄœ¡†àQO;zºnwf8°A®0œÆñ—æ¡§xÿ\"Tê_oæ#‘ÔÓ‹õû}âOÃ7›<!”ğ¢jğæ*ƒš°­%\n2Jê c’2@Ìb’²OcÜ†JPÊ™ËĞÒa•hkø:#‚HÉ\$Ì#\"\"(iãúÀ¼¬:ô00p@,	š,' NKà2ãj»Œ P˜¤¬ˆŠ2\r-På\n¾PÀæíŒ#h+Œ#Ğ:êkŠ	#r^3¼:<5\" ÜHC`È	Êğê;ª)\\¬¥#ò•3ÂE=ÄãÌNĞA j„BÒ~å®® Â¾Šk²W9ŠÌLåBÆ:@@P—Pˆ¡hÛ/¢³zŒ8ÎHá\rJÊñHRÆæOÃ-MT8hhô±&I¤ú”×U;L©¸•ınšÃ¨\$	Ğš&‡B˜¦ƒ%L6…¢ØÕoBë%Ê˜æŒ´úÈƒ¤Ì ®ÊX7ŒÃ2<½¦¢šâB¬›1Ì¬kCSÃÊ	r£Æ’c0ê6ô9º×v0Œñ¢Š½*‰HÚ½©XP9…-ÄÖÉƒ8@!ŠbŒŸ9apAZ\$£¨êã èÌ»'hò\rã‚º2Îi­ü:`PŞã&:\\ãœ–6n\"_&ÿŸ3Ú…9N˜â¡¤°É¼’ã¥.¸¯èğ8F°Şd;ƒD3¡¶:7AĞ^ü\\˜ãëØä.Ã8^™ñoüt4¬AxDÚHàé¼‹í²N5„Ağ’6¥Ã,„7à^0‡Ğ(A!6#zRÕuc\n:´Hn™—Îuiú‰Œ²Í@ÏDHJ>Ã/îı\0+Ì–•”/	` \$\nw˜’ARÓŞ1:8Pªä¨¾O7z8Š6è2õÊš¦éÊvÍQM{78I÷÷9š4)¹RªMBI&hë†’¸Sˆ©3uæÈ© àâJ8 Å0„ÈáYù±8AŒ—®rU\r¤Ô(ğ¦i®\$ÅDÔ’Â–S_Ê´\reÉÇøw‹á€Ç\\¡Åè¯C“ü#g\\–P@ËìÜ¡•âj¼\"9÷5„¼˜‚¸‹À \naD&A#LK\r)ÜÁQê•²¢è³´‚i‡#‰\0´@<¨ã4äÁSQm\$–8:(ŒjŸI?i¤Õ?\$×E‰<zmNåa§WDTãÈenä5Ü\\3>mşœ î÷Oäl&D(•ÂFŒ¸F‹î¬:’RŒTJ(U\nƒ„_bk„¤Ê‚’JHàÃñ 9´D¤i•“È‰çÌ)‰1+&#E’Ge,	.K©Lg\\C0yS«Ù À ˆìMDòœÅ%/‰z\$ Šv§'(ƒ,¤“çe’Èæ‰†\na—’R10—%Ğ&P§z¡Pa\"ğÌ’>dK\n*e&P’0òLPÎjè]I¼\"E0ÒÂÈh\n­½K.³˜çÃ…-›ª8…œ(k^âç1†ÔÎz.s\n**sÔå€";break;case"no":$g="E9‡QÌÒk5™NCğP”\\33AAD³©¸ÜeAá\"a„ætŒÎ˜Òl‰¦\\Úu6ˆ’xéÒA%“ÇØkƒ‘ÈÊl9Æ!B)Ì…)#IÌ¦á–ZiÂ¨q£,¤@\nFC1 Ôl7AGCy´o9Læ“q„Ø\n\$›Œô¹‘„Å?6B¥%#)’Õ\nÌ³hÌZárºŒ&KĞ(‰6˜nW˜úmj4`éqƒ–e>¹ä¶\rKM7'Ğ*\\^ëw6^MÒ’a„Ï>mvò>Œät á4Â	õúç¸İO[¶¬ß½à0´È½Gy›`N-1¬B9{Åmi²Õ¼&½@€Âvœl±”İçH¥S\$Ñc/ß:4;¾õ¡C ò80r`6° Â²zd4ŒúØa”ÍÀœÁƒ²ïã*ÊÁ­-Ê 9kãŒ…-ƒ<ñ!Khì7B‚<ÎP¨˜ç·«dh(!LŠ.7:Cc¶Bpòâ1hhÈô)\0Éã¢şCPÂ\"ãHÁxH bÀ§nğĞ;-èÚÌ¨£´EÖÅ\rÈH\$2C#Ì¹O Ù¡hà7£àPŒÅB Ò›'ô\nú¼ŒñsÔÉÊmô(-HèJrxËMÅ*–2SòãM=‰Ğš&‡B˜¦zb-´ÕÉJÓ´•AòÜ<7#éğZ™hĞ-À²7 ƒœ3ÓúªÀ¡Pó—Ò¡\0ë9\nƒxŞHRZ*9£Æşc0ê6#’=ˆ3ò[l0­Œ*‰'«e¾2…˜R”Š£8ò6/Ë@!ŠbcèJ˜„¼Ÿ £XÔ2²,23ãm˜*’5+„ób<¡Ra“[¥o‰Øàéb–ÜóïFM¢Á\0àáÒR•ª#•\r­šfÔø@!\0ĞŸÁèD4&Ã€æáxï±…Ì]ú‹ArĞ3…ê^Ú<\$ Ò7ÁxDºK˜é­ã;<„Ağ’6Ì\"!Aà^0‡Ïl²1óíÉ8yvÕ±”9HJ]S˜fAjt\\R#†;C\rXç;£\$Ÿ¡”©Jâ¹©–øí\n'êÚ=Ï‡#ÑÒˆ \$\nomÜ>pP¢…*jdd\nZçZø.„èÜy ÃpÌ´2#˜Õº%)Z—¦,è·^2~:FJ`ßmun.=œ©yãÑ4¾İ’\"VB€€7ó¤§Rxt\r@âPâOéÒÁÈÏÈÙÊ\n.#eÍp6Ë\\#B>i\"ÁpáÈ†–2ÆØëREAÔ RúáYŒ\rËĞ¼7øèÉë¹.ÀûĞÖİ	0g¦)ñ7@æ_‰ó2\r(yT˜ğ@Ì‘#ÄÜ‘Bé	ÈS\n!0’eàíÚ¨F\nA£×\n‘¬?F)S%´RËÙ‹.…9‚@ªB”z)….ÃtC!EyÄ\$|JR¢Luæ™Û‡\"§Õ’(nÂJÉrRĞ\0la¬ó†„pË¢Ú/ÎPÓªw1QùtÆÔ!’1‚¨TÀác†PÎC{Ø’	<Å4â3Íü‡“3¡•9ŠHK‹%Q”“òækáê4¦¤Ü6v\$c\0Q|#±5´\0£Š_ÎYà<P5´£eÃß 2¢nˆt&ª9\r2À)»€A±µi'0ô Èl°TÌÅ™qèå§0O—@l}qı@–àÎœ‚ZF›DµC›òá3Ì\0SİªÂçÖl¢.ÅâpĞÀ6ÍQ@È ";break;case"pl":$g="C=D£)Ìèeb¦Ä)ÜÒe7ÁBQpÌÌ 9‚Šæs‘„İ…›\r&³¨€Äyb âù”Úob¯\$Gs(¸M0šÎg“i„Øn0ˆ!ÆSa®`›b!ä29)ÒV%9¦Å	®Y 4Á¥°I°€0Œ†cA¨Øn8‚X1”b2„£i¦<\n!GjÇC\rÀÙ6\"™'C©¨D7™8kÌä@r2ÑFFÌï6ÆÕ§éŞZÅB’³.Æj4ˆ æ­UöˆiŒ'\nÍÊév7v;=¨ƒSF7&ã®A¥<éØ‰ŞÒvwCù»İN¬ A¹g\rÈ(ªs:èD®\\×<˜¡ç#Ğ( r7œÏ\\±…xy¤Àô¦ã)V¹>Óä2½ˆA\n‚¦ª o³|­!êà*2(0ŞšBcÈà>ÌŒÏ\$c'£läOã0¯ğ@1C\n2!\r*\0å\nhz’ã(ßƒ’ì	ŠË„0,Ş9;ÉŒ=£ï8‘%#{v6\rã;Œ9.[š0®®ZøÖh(Õ/	ĞºÈ2C\"&2\$ÌXè„³€Å93¤í92£¸¾<éhÁpHPÁˆ)C¨ÖÅC8È=!ê0Ø¡¼âœ–Â0Ò*ê:H†7(ñØà7ÑéŒ§£HÙŒ,Pî1ÄP¢{P6IIˆBÆ‹`+¥ÚDÃXšR)ó€ËV5p\\Ø¶=IÙVe\\šÚÍŒAY£k\$I²½¢@	¢ht)Š`PÈ\r¡p¶9^ƒº÷U\nbÑ#ËpÙ ¼h*„„xÌ3\$Oâl)À‰¤™`KÓÙ4O²V–¬Sr1XäÊŒA#º#XŒ3™SÔ‘\0Ê¹¨3¨@º¸ƒ£—gâƒ&,«Œ¸Ê<äã¹Igän‚?“eş‡•ùj[˜[Ù êág\nt˜âè~}è:V‰,äš>N9èC™§\$£êi°œ‡³ƒ\n2J£º§\rh€@!ŠbŒÊãnÖØÂüŒÒXÚ:ƒØ…é²‚›bŒ•Ôãm‚Ÿòt\\Ì îÃ<©ndñ#-Ë¤Ï!´&¨Õô^TˆKò4tjÎ”Ï¯ãü¡=\rÀŠĞÈÁèD4ƒ à9‡Ax^;úucš@t–3…ã€ØŒ£Â7Kšh^5£Î20¾1q£pÖÍÒ6ûIr!NxÂ(V8æ•Û3}\$úN_PánIt¥BXP`Q)u«\0º\"€~ˆ3Kl:º\"0™Hw@\$0^Œxo@AéBÆ†(»V'ğ¹Â˜WP4#„§Ô—“f]“:¼hA]+È\\Háô.IÜùˆUø×\0(*\0¥A€èh‹6'qÖ¶ğÊÜ[›ueìi—´VJTĞ(k>ÁÈ³2rNÉé?€\$‰#è©h‡²t\\S&&¾*·`îLä`†¬Œ\$š@‹APºTÀÈù¬)ÌLøÑ2ÓD+µqxJØÓ@ph6•³å“8™)ÎÈ¥J–8Èƒ«!QôLÇòæ]H„\0‰Ç\0003”´GCy\rS	‡7(êcû/{DŒÔ…0¢™0„¹r8áÊTL¡É‡99F\nABÄàŒ_š§E9£õâ‚‘‘Is¾,¯¡}OLAä›O”Ú\\çé)f¬ÂàÒ›\rŸèÉ•\ngùÖgäÚVò?Y’lŸlâ`Qõ§Hh¡‚¤”x4ÒáHŒš}&Á\r †ÀVÊ¬™(N:Y”É9€'\"zNlÌKa\$L’32ØjI9ù-…ğ‡4Ö_Â¨TÀ´jQAè…fÅMÑŸWçÜ¡\"\nH,×éYZ2Ø­ÄËQrø•kotE6â@Ï«ÄIuJœ1Óó-˜ér/…øØQÔ£ˆeˆ!+O—iËuD0©4ûšÆSNA§50\"_ÒÃó©İ{–Œ.G\0?C¡#HÀÁ‡™ê[‚½´Le	Ô†;%Úo1ô¥=\\4Úk™”2À)»¨ZP’Ú¬é\$Rcb*†lªÙ°ŠN§’ 	5éP,U£Ía4ºƒ]IobÊ%Ø\rL×8Ôˆc©A¦)?Xã@jĞ";break;case"pt":$g="T2›DŒÊr:OFø(J.™„0Q9†£7ˆj‘ÀŞs9°Õ§c)°@e7&‚2f4˜ÍSIÈŞ.&Ó	¸Ñ6°Ô'ƒI¶2d—ÌfsXÌl@%9§jTÒl 7Eã&Z!Î8†Ìh5\rÇQØÂz4›ÁFó‘¤Îi7M‘ZÔ»	&))„ç8&›Ì†™X\n\$›py­ò1~4× \"‘–ï^Î&ó¨€Ğa’V#'¬¨Ù2œÄHÉÔàd0ÂvfŒÎÏ¯œÎ²ÍÁÈÂâK\$ğSy¸éxáË`†\\[\rOZõƒ?£ÅåŞ2wYné6M”[Æ<“‹7ÏES<¡tµƒ®L@:§pÙ+ˆK\$a–­ŠÃJ¢d«##R„Ì3IÀ†0Œ‰ Âœ(óe¦pÒ¤6C‚JÚ¹ïZ¤8È±t6 èø\"7.›LºCbğ¡.«¤ê®8ÊøŒ¯:V	ŒËŠ¬[iÈÊÿÅ#LVº;)CNô¹Ã“& +¤¬å Œš}\r‰ÃxìŒÇìûh‡\0Ä<¡ HK8ÎhJ(<¶ PÜ¹¨^tb\n	°Æ:ËÎ0âá”z\rƒ{½‰ã”2¼¸ÒHÚ4¡ P‚ë;šJ2Œk8ô…©àÒ½ˆ’‚®rä Êäd\"¥)[;¤õrL”%PÂo;N(ÃWÀ•í#<p	@t&‰¡Ğ¦)C È£h^-Œ7(Â.Ë-}Ræ¹MÄH\r8Ñ´©d¦7ŒÃ3×\\§0ä•E‰ÊOÓˆ›b£	ÈŞ '£ËşÉ£ÃªMJ®ìXÙXu£eÁğ§Î8 ê„…˜Rœ\nƒxÖ”¦)ÙR¡=j@\\W–+Êü®ƒn,–.-Rİ9Ã-ûƒ¯¨Hå‚C Æø¢x›Ë)ú®	®££Ékõ5¥©CÏ)Ã:6°&°Úr:Ãcºédë:2gaâ43£0z\r è8aĞ^üH]`ˆ8\\ºázQÉ0çUá|9ò.ş/ŒCbz5„Ağ’6\r~:xÂAa`:'ó‹iÙ9~D:#¸{³'\r­A0øÊ’ M‹é9ët‹2ãD'¥Bä'KP	¯Ü´\n@¡ªì,¸*JN¯BlXæDI Øœ=û3˜Œ5#.¼ÙÄFIÀQ8g9ÿ¶’‚PÊ+ÛJf9Ô÷zÓÕ#Åpƒ´HÎÏ»b?gI\0006d¦T¡&‘\"3Tï3¶7†ÈÎ©\"¼~I±!‘Æ’×hÌ•?ÎĞŞ‡¢Ò”@O\naQ#T¤OºÆ/ä2ÎÑød¤,—âxOˆÌ\$0u¤8Jg^l\n9¸˜uşN¹—„ÎæBm”ª‹Áˆ»\0¦BaùUéMb‚\0Œ=N%ò»¸`Õ\$Dá6¤¢¶zÚƒ:R4Ğ;#ıÌt’’†Ğ¨3cÇ‰ÂÀTl0†dŠ±rÈwoTÊgÌh@PC<á°ÃÒ¤\$H(9†ğÄgb©nR¤¶%âN“ƒ-R¥N=»#úï#6T*`Z	8njf,7yBœLên#2ºJùR°'6S„,òpô\niCÎ]ù0eÔª“  'FÑ\0£bÃ0y[ª Î‡2Òd™…Tj\0ş0UJ²î*¯°×%¨y˜„°‚¦œK	ÀP(Le\rŒ1t¼J)r	½Ä‘K)tmÕ8Ã=#¥Té4R‚±i^sA¯Mê™uÉ,Tª«­Örè;N0P6*<Ì®ÕV4ú0fŠO³BgLjI\0¿ÇüÎ€";break;case"pt-br":$g="V7˜Øj¡ĞÊmÌ§(1èÂ?	EÃ30€æ\n'0Ôfñ\rR 8Îg6´ìe6¦ã±¤ÂrG%ç©¤ìoŠ†i„ÜhXjÁ¤Û2LSI´pá6šN†šLv>%9§\$\\Ön 7F£†Z)Î\r9†Ìh5\rÇQØÂz4›ÁFó‘¤Îi7M‘‹ªË„&)A„ç9\"™*RğQ\$Üs…šNXHŞÓfƒˆF[ı˜å\"œ–MçQ Ã'°S¯²ÓfÊs‚Ç§!†\r4gà¸½¬ä§‚»føæÎLªo7TÍÇY|«%Š7RA\\yi¸ÏÛäuL¢bû0Õ4à¢\$ ËŠÍ’rFùè(ªsÊ/‚6¿ö:³\0ê„\rëp² Ì¹†Z¶á°­«ªh@5(ló@œˆcÈ•Œ)ĞÒ·ØÌÀ*‰@”7C˜ê¡¯«Ò2]\r¨ZDö7Ãœ Pè„ÀE‹È)°Ø#Œ¯£Ş¾Ã¢c>Å\"ìŠ¤¾>ÑTz½\ni[\\®ŒrÍ‰ƒzş7Dã’z7%h207«òJõ/A(ÈCÊÍÓ„äÛCÌğ7/A j„\0BƒN1²Øš0I¢\rˆ	ã”6ÀËŠ2¤2B	†S¬NÛÜ1¯4Ä°§£KöÓ©êè¯BÈ<FrÊZÊN¨8ÃU?ƒxÒ•%‰r’N“}uU72øXº’ÇCÏeB@	¢ht)Š`PÈ2ãhÚ‹c\rÌ0‹µkR:«{gCcÔ5Mb^ñã0Ì60+Œ…¥ªK•]³b Ş §ÃÌË£‘ŒÃªSHËã˜X‚XU¥]Ì7ª.šaJsƒ\riX@!ŠbŒò%Šùa„²\\ôW³\$—¯-Šíº7óÀ’·³ƒC!L“{¤l*È83PĞÃ‹¤)ƒú›¦ ê2…°ùŸÌºšÇ É€àÇcºùcƒF2Á\0y\r\rÌ„C@è:˜t…ã¿CY\n/ƒ8^•ñcÄ9Ô¡xDáC;ß¼ãØŸ\ra|\$£ƒk à^0‡ĞsqŠŞ:aKÈ:L3Êã7kã3lŒ­ƒnø5¬]TÉØlâ84B°BWÈ:Ä­ÆĞ3\rÇÁph \$\n·r†jMÀP¬)RÍ\n²CšB7&ÉBrùL£LF1Œ4û\nÀÙ’¨0§\"‹¨t_Ú¢gªxß£¡DÅ=1\$JÃÃIíÄı5cüL\nl.©÷‘PòjĞ)T6.ĞÌ“€p,ÇğÓ€‚10u¬¨1©ëNgrÄä(ğ¦*5@ªéÈ!¢8ÑÉÙ_À @êC‰‰ÃLe\0˜¯h4ĞAÕ3G¼›@s‘gQ/ÕPÔ½!ôLíH¡¨²œğ \naD&ÅTxÕ¸ ÁQî“äŞQš«´*P°úf\0áÔ¤L4‰,Ó«Õ~†Í©Šy!>E&¹²•ù¾:‹ğ‡I9+#yX‘ÌÀó\"RrTÙ“d3øŒ±%Cg+00ÊÙ\$j\0PC=A°ÃPÒ£ˆ°oF(—e\"Ù‰z6H&L©r£¼@ĞÆ3\0@B F á`0Şdô§MæKÉW Ô¯X¦ŠEšdL²‘Ég“’2gHñ \$N@“’“0ºÕ)ÕFÄìÆ9°fûò+x2†3FÈSAGæ5û;ğ2Ïö*rq|)\0Î±“™æĞ—æx\0 ˜Åò4¾‡Fº^E½%Ô4`ı6dğñ›ÓDgíM“®rJ/CØ”·9î†H¥EFAƒ\$¹R*˜\"¶ÙÒ|`Sà3a	\0?B`»Ñ17Æ‘zÉA“UFKÍG Gğş–";break;case"ro":$g="S:›†VBlÒ 9šLçS¡ˆƒÁBQpÌÍ¢	´@p:\$\"¸Üc‡œŒf˜ÒÈLšL§#©²>e„LÎÓ1p(/˜Ìæ¢i„ğiL†ÓIÌ@-	NdùéÆe9%´	‘È@n™hõ˜|ôX\nFC1 Ôl7AFsy°o9B&ã\rÙ†7FÔ°É82`uøÙÎZ:LFSa–zE2`xHx(’n9ÌÌ¹Äg’If;ÌÌÓ=,›ãfƒî¾oŞNÆœ©° :n§N,èh¦ğ2YYéNû;Ò¹ÆÎê ˜AÌføìë×2ær'-Kk{3ùºš>²±1¢`÷½“¢ÈL@Î[àQ2ÁBz2§Ë¨Ş„ ¨:Ã/a6¡îÂò2¡Ä´J©'©û²¡&Ëš::ì8Ô0§¯ÀÒš/!%cÂ1¿P ¨4¤l^·ƒK\nà¯-4 A@PˆÅ%Ë€¤\$´n80KÜ&\nH!6òˆã(Ş6Œ££ZşÄp#0Í/œGƒd|ÅL¢„&ºŒ,ò93¢`Ş3ÂiÀà™¨£pÊ3«û@Ö+©ÀÜÙ3¡(È\rñ¤ºÑTeCÊ°PàPH…Á gN† P†‡¤3Êî¼ ,;¤¼<p#ü¦Ç 0 ¦Ê´s¢(\réƒ#2± äî\r)CJ\"'dâ(OòğêŸ+8È¦Tl¼Ö ‚–Àì…°äÒ*+x\"\n63[®¾Ğª4]Ä6¨c^Fc¨Ú	@t&‰¡Ğ¦)C È\r£h\\-8Hò.ºUÅtöCd¼6(ÜóFÃ4Õ?9©ÒfÏ ‰‹È\nƒ{q>0s@:Œj¸æ9ŒÊÙ;¾eÕ”Œ3ÄÜ.òaJz!ŠbŒà\rã\\şI8Ò1\\a«/)ÒqtJ*«é™54ùj°Æãlšœ¡(4BÃÀá±Yµ†3¡¸ìë»sôh0ëŒËC©èš0«)š?ÂcºñE¬[{É„âX4<ƒ0z\r è8aĞ^ı\\¥)“ğä/8_?õcÄ0„»xDå?rï./ŒVPÜ5„Ağ“³2²âã|¨©Òã7Ñnã¥iŠÔ÷¬Œ3òí#sxœKÃ(Ä:¦[«Ü—OKÇ´§¨Ô-Âí½zdÓ}l[ÎÉ7¿´1 íÄ/?¡)ÃaŠ:à ©4\$Ì…QØtG¨ü¼€ÂFÃ‚oYOÄœsÈvQ ƒ}+Ø””2ŠQÈò/\$ªÖ£¢›\0ÓPB¤±t•¦}p pa¨¼7Âò	èI\"áäÙ\"àÒ¢Ô9Z4!¹æœ‚œyˆu+h3,2<JY{pÇ¥†6f»9É‚&ùÁ€ÂŠP	áL*ACrhÏ‰ä†!Qo6¸¸ˆøuA)ù-hFÒykE<¢hM›É.!hÉ†RfO\"4\r\$í\r‡3aIY8lÌ¥6Æ İ˜Q	…m\n›²X‚ P#‘¦6eXôIlN'!Èºp  gDhÈò6ÈZV•ñ£AÌ²Êó,e›…‹¦¨s5/e€e–EfZ2¥-—Iô&p\n\r ¹ƒkÌ\r\$¸•Á•Úja\"…\\‹šk•©²ffävR0ŒÕÂUæÒğlqYb†²n£\0v/¡½‹§´æÅ§Äç›k-*C¬.2m0À…P¨h8BÄmàÎOTˆr„j-¦‡#\"%£kÊRÀ\n<µ Ù#Ä€‘D&¯Î)ˆA¦DÉ—`›6ƒ0y`%`ÖSÄ\"™RªÊA!¤4©‚´ûŸ³ğÅ’â‰N(\$ŞlZ0\$†÷Î	`\r€!W@¦áBì¦éd_\0Q5EşÏÆüzS´•›2Ôâ§Öj”!¢R\n.vW”‚pJÑäU¨ÅV°HdTLÍ?ŠÛ•a”ÔxÎ„87¡Ê¦¨ØÉI}8Mp\nLflö´€è";break;case"ru":$g="ĞI4QbŠ\r ²h-Z(KA{‚„¢á™˜@s4°˜\$hĞX4móEÑFyAg‚ÊÚ†Š\nQBKW2)RöA@Âapz\0]NKWRi›Ay-]Ê!Ğ&‚æ	­èp¤CE#©¢êµyl²Ÿ\n@N'R)û‰\0”	Nd*;AEJ’K¤–©îF°Ç\$ĞVŠ&…'AAæ0¤@\nFC1 Ôl7c+ü&\"IšIĞ·˜ü>Ä¹Œ¤¥K,q¡Ï´Í.ÄÈu’9¢ê †ì¼LÒ¾¢,&²NsDšM‘‘˜ŞŞe!_Ìé‹Z­ÕG*„r;i¬«9Xƒàpdû‘‘÷'ËŒ6ky«}÷VÍì\nêP¤¢†Ø»N’3\0\$¤,°:)ºfó(nB>ä\$e´\n›«mz”û¸ËËÃ!0<=	óä¦–±¾nZS±LòB„A±zD«Ğ;î´(P1 W¥j¡tæ¬EŒ#\$Â˜ìÂŠ’´ƒ1ÚU	,òTúè#ìâ¶‹#Äh‘Ò¾Š²äº”‹Yvš±j 0Œ2ÏLZjÿ¹n;†™£+»èÎ f„˜‘IĞòA­ãPhîÒ‚¿£\$¥ÜÊï2^\$}\"¢9	¡°¬på1a I¡®BÏ<»TÑ¡\0;-ö\\#.¸á8	\$©ÔËÌ¼ª\$bd÷Òhj£Wô<õ`µ/Ä8Œ“ÎrZğÅÌğ(u³º”3•kºô´é„£#V& MËs¯¯ÕÕs\reÚÄ½ôÙan±€PH…Á g~†(Í“N©’ÓB×Šò^%U[=ÕõÔ9¹2A M©ª{£MÑV”i \n¤Œ¬bÅjDWé)ıZ	¬ú†©÷¹ O¨o¢—1ÜJŠ¾Ù*“y¢œ¯´!@4}'S*¢G+@,KÏiÑ2ªãxV,„§—Áphi+—)¾ú|g¨½Ú~ĞªYµ®iKŞ›AAo‚²İwdîŠ;¶ªš4ûÄŒÑ¥8th)\$ç\0(Ê>ZÊrœkÑgÉ²%ã#ÀÃ`è9Hµ,±PiæşRÍ\$ÿ¨ª\"ÑËJ.,DŞLİU†Ê«Ö™&-†ú°Ë·‘@>îAuÒ´‰Q™¯:{‘#J’˜÷9(%?;’Îë*d–Aípa0—X_ièr}º„PôûfÚ÷Ûà¸~\$¨Xxøá=y›ãŸ(>ƒÂôKÕlÎ8ú÷´M„[İ?Åğ»7ùs¸})õ'z Şô~&)Ó!§èJŸ³Êrï5ı­—üOİÛÔ%p\rì@dî¾ÓNˆc#LXÂS\nA¿Â|:ÑiÍÆ3ârIHb˜d©•ä¼JË© fuqÆ¤û_&&ñl¶‡ö¯^â:OO·ÈfM¹Û0eú„®sŠñÃ„ô˜¡fÊóÖñàYÇlÈ¼Ì)u))}ı°Z¸RËAÑz8‹¦P \rÈ2†`zƒ@tÀ9ƒ ^Ã¼¡Á„2ĞÒƒ(rÁ¼9p^Ct¯ÒX0Òåˆ\"Ğ0ÊĞÊ\$¸_A°0†àÖğĞVÅ@¦¤÷ôBAà/ ùÚÃH\\Cœso¯Á#E2‚GÎáEÑ1Û2•¤IDy`†íåRF²›ÒM¥„ÕÁc¥Ÿ	QĞ¤°—SªyR9'L(u2X`„ã;-•%45µv4+éšÊPÔà(hXú!öñ¨(.@¦ÖÄt\"Âz	½ŒÑgB¡V!Ê‹ë+ºIÅ	uRD”ÏG'—£´\$%.68âB°]­,%„ú\n74PJ lzlxŸÏ¸Ÿ]>'t•\n7UT‰BRLädJ”È¸b”\n&œÄ¯LÈÜ4RÛM%D°ì!MMRÈA”²TÇ5ÂÍãÙõ{iÀ›\"S´!¤¯;Oñ0¤š#‰È*Ç°hˆ¦`€O\naRCœ&xûÄ+õ@)…ØBW|«\r,vPEG§U ¦±p˜‰Âv™]´ÇfD’ÕWajy!äæŸFH!ŠiÈ6Gà•ú(®hé›Fé8ã›’•}-šËdKS\n!2´ªOğ ÁP()S®¶é©OI„Á.'¥©=Èkå(e[d]D¹-j¤œ”»™˜‰ß¡À'aqĞãÚ¡ªİLÀ	q—)ë[OZuFE¼b2×KaëKœ°`ˆ]³Vmx‚³â2Ï‰g£uÅ™â¶ÚÓ1uNDé[aFîw\\³¡¢°”!¹°Ø\nÆ€©†ª}á0Ø,Wl€e5Ì°TÔÈ4DtÚepjnÎKõ3q\"-:I¡5\\Õ<Ï–œl¨Üæ(B!ø \n¡R‚ê&uóNûÚ˜Ù£„cxÍo<_&%}VóR]z¼Å–×¡]õôLZ%º.mğ©+>‚e² ê^ö”ú5ÌÈÒÄ°n	i»°ä	^jœLÙ{&3täÑQËLIîv9]M!•qŸóÏ»+Ïzàğr¥LŸ–5‰çÏík°å(ÖÛbÈ B¯0büÄJ»O?XrA¬iãQ4ªeLà¢ZKóÙ%…™`²\\L7’FÛPHê;bX‘N‹1Sz.û	Á	ŸÒçŒßìY¬[¬&å—2Š‹ZrŠçÚd©™5DÒ9ìA+Í\r8«öVQYjƒöì.AÊ¬¿—îfd(UcÂHíYZRP";break;case"sk":$g="N0›ÏFPü%ÌÂ˜(¦Ã]ç(a„@n2œ\ræC	ÈÒl7ÅÌ&ƒ‘…Š¥‰¦Á¤ÚÃP›\rÑhÑØŞl2›¦±•ˆ¾5›ÎrxdB\$r:ˆ\rFQ\0”æB”Ãâ18¹”Ë-9´¹H€0Œ†cA¨Øn8‚)èÉDÍ&sLêb\nb¯M&}0èa1gæ³Ì¤«k02pQZ@Å_bÔ·‹Õò0 _0’’É¾’hÄÓ\rÒY§83™Nb¤„êp/ÆƒN®şbœa±ùaWw’M\ræ¹+o;I”³ÁCv˜ÍìMÔÎ\nßò±ÛDb#Ì&Æ*…†­¦0•ì<šñ§“—P9P¼æÙçĞÊ96JPÊ·©#Ğ@ Ã4Œ£Zš9ª*2¨«¶ªÒ¸\nC*Nöc+°È<îKdŸcY†TµƒÈà<F!óc`Â‰‚´ş\"Î0Â†ˆKª`9.œÆã(Ş6Œ££2ô I˜Û£ ÒÖ@P ÏDlDŸÀPÕ\$²<4\r‰€æàq˜¨993,PÒÌ“2sBs£MØ×„£ @1 ƒ >ÏôóAÏÔ\0ÔÖÀPòÕMÁpHRÁ‹æ4'ëã”\rc\$7§éëä-\ròT)1‚b])BÖ1¯o˜áSâ(Zõ£àP2(PdeË¯Ä\$“Î\re~¡FL`„›„İ 0§Eê›?µØÂ…ÀM¦6\r6­Û=¶[µàØÂÜIºCk]Ë¥uİ¶ú@;%-’J=KÂ@t&‰¡Ğ¦)C \\6…ÂØå‡Bím\\(ƒ\$m’PÎ©(ê˜7ŒÃ5ÈŒ*/ú{*×ó‰C3±‚ Ş½cpò£pæ:Œpèæ9ŒÃ¨Ø\$¦l5ƒ–f0ŒöøÜ.·XÚº­P9…)HœŒØvËXá§!\0†)ŠB0\\Yï-É\0ŒÈ˜ÛŸGá\0ôÑ¹)NS2åsš˜µŒ£Ë2\$#°@3# Ö:ä¹ØÒ7éc´T9<¯Ã¢N¢²¦M;ûò9¿{và0¥)_&ŠØæ;¢vàğ8\r#”±‡ˆ²H2ŒÁèD4ƒ à9‡Ax^;÷rù§£\\‰Œázsã\r–mÃÁxDÖ#­HéÙíÅ°5„Ağ’6	Ä7à^0‡ĞxAd\rŞ·óŒ#Z™)ïïşbœœ,ÏÄrÃ/¾E\\ÎEƒy(„Ô›“”NJKÉ{/¦`SÎêƒrt	È1ÎVƒƒülËÄFƒ#€9¤^¯sò p­‚œ‚œÕS«X!Ò\r9wÕ¡L\"q®7òq €Ëhõ’Ò^LY“ˆ)&%bp½ƒDJL¢µ±…£ /O*-gèVŸÂœJBI&z”ühÃ¡')ø:CVêƒˆu5Q¤3“øC#À?æ°45ĞÆH™³6­ı¤RR~Y‹\$ñ.\"dPxS\nY‹ÅW¾yR™LbÂ„¿œpD›tW‹%1ô–õRRP@f.aÔâÄ Õ]Pkˆ…™Åö7Øft7—Ã‚ŸË˜ \naD&	TN	á{*‚“ñ.p¥øGT“aÿëÈÈ'´şMÎèy=d»M´õ\0'Á–AÔñ†õç*V›…\0000”é¾Œ‰á»€Â„”¨DòvŒŠ\rëÌ;=ĞòˆÄP%!\r%ÀV× k‚(™Å#ÎqŠD–DMu\$:€CÓß2D¤#ò˜ÿ£Kzi,*…@ŒAÂìA\$lë˜}4ˆšŒ„‹¬=Ôœ¸Wå§x.¥	OEo)> /&˜÷)ÍE„±F£Óê–r	HT0 —*F!#íõ1C&i fE“¼¬šòà¡@¥\$h°×’S}İSn{å\$Ù(²C)cC3#AÔí 6Ğ5u:¤œ¨¤¢`“ÙjZ\rl€ ™/Rn:ö^_Ò'&¡š|OÎá¨ƒ1Œ%¦p%F¡ÔÊ¶¢º1@g‡RpÍdA,9³]=9Oni©µ:G«9†Ş®\"aBt€UZ0&\r…+P‹=v8Øò¤’‚";break;case"sl":$g="S:D‘–ib#L&ãHü%ÌÂ˜(6›à¦Ñ¸Âl7±WÆ“¡¤@d0\rğY”]0šÆXI¨Â ™›\r&³yÌé'”ÊÌ²Ñª%9¥äJ²nnÌSé‰†^ #!˜Ğj6 ¨!„ôn7‚£F“9¦<l‹I†”Ù/*ÁL†QZ¨v¾¤Çc”øÒc—–MçQ Ã3›àg#N\0Øe3™Nb	P€êp”@s†ƒNnæbËËÊfƒ”.ù«ÖÃèé†Pl5MBÖz67Q ¢>Ügâk5Û3tâÿr¡ÏD“Ñ‹(ÅPß	FSÔìU8F®—ÂÊzi6‹3ŞiŠI2Ôósy’Oõ”ÏÂ\nE.š¡¾Ššæ›%ìºï½‹¢ì\rkÒ8/†)@€²Ã¦ƒª8Ú!#\n*!-Ãä†Bj\n‘D‚8Ê7£(è9!1 ¦î#Ãjø¼Œª0ì¡ƒ\\ñPYB6qhi\0ò)³\0Š P¨ÖHó'&±ğ°œ7¿h Ê2E£¢ûIˆè„³\$Ì0ÍPÔ’Í¡Ë7Ë€PòÍK@ÁpHOÁ‹¦ì!+àÖ£Iâtâ#I,¨	BI‘Ê£:n¨ÊÊB(Z6Œ#Jà'Œ€P´Û±Ü–Ê<#r[%¤%pò'£b¢¨ï°ËOÔ+¸(V¨Znª?•ÕyQ*Õı‚Ùl”ÒWuD4…ÖT’×\rÅ‹hXé\0ì—I@Ô	@t&‰¡Ğ¦)C \\6…ÂØåyBí2º²ˆğ:Uãd^1LdD¨ŒÃ2t¨5QxË;Õ\$œ“rˆ¨7²U\0Ü<³Ãpæ:Œc49ŒÃ¨Ø\$n4õEøÀÂù¶¥Ÿ\rÃªaLT®ÒsâÎ\rékš!ŠbŒ±Y´C246äQ@ì>sZ:%òdœ›£˜á…”(óUK[O\nY[h+ÎŒ½Ul¸´J¾€¥âl<€H<9èÕt<BÖˆ&í`Ê3¡Ğ:ƒ€æáxïÇ…Í¶aM…ÈĞÎ¾<Àğ€ãCHŞ7á93#§\n/µXÖÂHÚ82ñhÜ:xÂ?ŒÜÂ4>¹àAP(‰\"MâÏ©XKyaÇ(ÃÅ9\$‰4X7ŒãzøªÀP„‚·£%öı\"Ê–wŞ¼>Hä’Û>óBıyz((B£©4¬;\ríÀß›§2r dÎ3@¤Ä™“Rv¢¢2OyüƒâVÉáÍ„‚±D|_ÛE<ÁÌôöÒ—	¡\nMå9’ğ’DÃÉ‹\$á¤û‚~ÄCqö†ÍPâLÑ?Ç˜À[”Ù£gaŒ1Fi[±™j¤œ“³\$ùOÊ\\}¡@'…0¨ÑÓ\rGa3@qCIú}qH’cÃa¢#Ç=\n>ÓúÀ!PcVæ|`@MÏpgL(„À@•0Ü#GàBÏ±è=„ıáC´X”‘3Q-/¦´N©”d.J&\\ØIéõ*P87IÔÂRĞv/òÕ%çdQiF[izO5@åqñ–¹Ë2ô˜VL¸#rê‘Eœ}¥¢aZ’Ş\\œ¶e’Ï™(¹.ğ†‹Ã`+\rdè5›Rp^‹A>}¡Ôß©r\\‚4wídŸ—S˜¨TÀ´-\0ÜÏ©Ç/â¥i¢}Œ!†KT*ÏÁ}1V\"Ï \$”1Ìº\nHh;ÿY«n…˜r\\h|ı‚j…ZÍİù¨nL’’ä]²Q	³ä3’Èm‰‹Ö©D)`ÃCCPsA *r%ôn‚Oq¢§§YFs¸cÈzXr¸·TBhŒ\"_œ\$•„›Lm\n	¼Ûu!W± ¥\0¹¦9¿@“e A¥8ÑV=[hñ‡Ly‘šĞôÈ‹šbFf\\1JÉQçQü zÂJ„¦qˆü#ÂèËª¶\r	J”+bû\\CÄ™¨88\0";break;case"sr":$g="ĞJ4‚í ¸4P-Ak	@ÁÚ6Š\r¢€h/`ãğP”\\33`¦‚†h¦¡ĞE¤¢¾†Cš©\\fÑLJâ°¦‚şe_¤‰ÙDåeh¦àRÆ‚ù ·hQæ	™”jQŸÍĞñ*µ1a1˜CV³9Ôæ%9¨P	u6ccšUãPùíº/œAèBÀPÀb2£a¸às\$_ÅàTù²úI0Œ.\"uÌZîH‘™-á0ÕƒAcYXZç5åV\$Q´4«YŒiq—ÌÂc9m:¡MçQ Âv2ˆ\rÆñÀäi;M†S9”æ :q§!„éÁ:\r<ó¡„ÅËµÉ«èx­b¾˜’xš>Dšq„M«÷|];Ù´RT‰RÔ)·ãHÜ3½)CØ÷‚öµmjˆ\$í¢¥?ÆƒFÏ1EÁ¢D4æ„8±ª‘t’%L‚nú5æ8¦¤ì‘x‚&‘45-èJÌh%¬éz‚)Å¢«!I‹:Û¬ˆĞµ *úğ±H¨\"Öh\"|˜>‰‚r\\-ed]H\$H·2)ã\\õ¬ºÉJjÄRH±R²I\$¡,_ª,RÆÕ¶”€Œ#LtU;²’i’PÊòX\$ŠTf·µ³ô‰6ï\\¿LJÒÈ_>\rRFÅ‘\nl¸¤„ıœÈt Q1O;å)Tï­T¢6Ê\\»YT(È‚3ï:×Uâ‹!XÛ=a¥ï2I¤‹˜¡pHÚAŠ×S?4J0Î–uS>É(%º–Â¤üÀ¤?o=	V¦ĞÑ\rU	™£wÃUBI³¬ûXÈ¤„ª\nJKq!\$„ôp¡¥Š€¨Ô°ÊN§D5.”*}‚º²*â‚,eŞŒCqªòºJiâ\r% Au‹€ã/jhºc¸şK¬H‚+–dËik•bù))işedK6q…-ª3¥ \$	Ğš&‡B˜¦ƒ \\6¡p¶<ìÈºÑ1—ÊÈå‘ÂÛ—C`è97-Ø@0NŞ3Ãd2¼;:±}à±Åe\nƒ{ˆ6Œ#pò£pæ:Œcœ9ŒÃ¨Ø\rƒxÏac 9qcÎ0ÀAK`°êë…˜Rµ¨‹‰AJx†)ŠB4a˜ı°»B;T5šãj±N\$%Î^\"«YF'Íd8®÷œnµİ0İlˆµˆë[hÑ:Ş²düò{XÕ>uîm3ï¡	£æëN/ä9ãxå]Œ£Àà[«Z\0ğ0›Öêè\"\rĞ:\0æx/ğLÈêCpe@¹ıp^Ct]ÇßAĞn‡4:@¾wÜHk@ø\$†ĞàrÃl €ğ†|@ ‡'d7«³£ƒk8!¤:X4â Èn…­¢fP]:,\"„YGâšE¡{IpAB @Éãİ&å˜µ™„@Sém3ªhG¾„‘Ò<G\r¦0‘…‰¸…@\$àÚ™Dsh)ğ›TºyIò:vÉÙ¡2jmLaRB+]@R¾‡ÈÊ6&²yy=å<gH1®W‰´÷¶¦ ñÆ‰T,iA2¨hH\$UE¦©·ò’²	ZGA\$‡–äJ»8q49ÂŒv{u!ÔçDĞÌƒxm··êva\0 nff	 ılœ6mş]“BxS\n‘ò96?Uj5‘BO’nñ#õJtƒEôX“åú¾x‚É@Ôú—]ÙN¯\$‰E¹ò½as)ˆju¥ÄÎßâ\0s(FèŞ7ã!¸o‚ÍÌ ÒÁ\0S\n!0c”pNLÁRC8•va³ú‰±2šÍ‰µ3C‘Àƒg¥!†6ÈÔéP*(ˆÈpIjiG©ï„ÉCøiª¸¡«) Õ	]\"BX*İTÕ²jšVé0?rÍ”²ºÙSŸ[7xµÅI×Fw]¥mAõš¸êæcJCmá°¾B%@Õzut8»ÈÌĞjê–o4joƒ«ó¥±,7\0ª0-\0‚8Š€ƒ=jWt6QóÊ“Ô	Ug–ÎÉ½¶omÉ“´>VìÙ)0mí¬½·	>É2èiJt[+ÕDÅ¯©:fî©?\" (&ÚğÌLyui«.)ñ Á¬¢TC¶dÕ®Õ/U\"òN35ê3İKÛDã-&´2ŞĞôPxSŠÛ>F!àci?”`Ğyåmõ*“:/¬x>Q=\$JE#¶òãWkz,0õÆ¿J1y¨kŠˆ(»HD0ÿ†S–Î¸dMòAÏ“Ğ…¤Ê¡5§°Õ²»,¡— »°Ù˜é‹“Ä‘-d‚O‚E\"V¸%çÛÔÌD…ø";break;case"ta":$g="àW* øiÀ¯FÁ\\Hd_†«•Ğô+ÁBQpÌÌ 9‚¢Ğt\\U„«¤êô@‚W¡à(<É\\±”@1	| @(:œ\r†ó	S.WA•èhtå]†R&Êùœñ\\µÌéÓI`ºD®JÉ\$Ôé:º®TÏ X’³`«*ªÉúrj1k€,êÕ…z@%9«Ò5|–Udƒß jä¦¸ˆ¯CˆÈf4†ãÍ~ùL›âg²Éù”Úp:E5ûe&­Ö@.•î¬£ƒËqu­¢»ƒW[•è¬\"¿+@ñm´î\0µ«,-ô­Ò»[Ü×‹&ó¨€Ğa;Dãx€àr4&Ã)œÊs<´!„éâ:\r?¡„Äö8\nRl‰¬Êü¬Î[zR.ì<›ªË\nú¤8N\"ÀÑ0íêä†AN¬*ÚÃ…q`½Ã	\no\0Ò7ğ2k,îSD)Y¤,«:Ò„)\rkfä¸.b¬á:®C• ÁlJ¾ä”ÂNr\$ƒÂÅ¢¯‘)2¬ª0©\n¶Ëq\$&‚ í¹±*A\$€:S®·ºPz±Çik\0Ò¸Ü9#xÜ£ ÊU-¬P¼	J8“\r,suY©ËÔBæÀ.Š­'â˜èôE\\µªŠÒW\"¥u,ˆÍ±»Ÿ·(²­J!\nù€7\rê/Ö‘<›-Ë2W*ÉÃ{cQkRÄTÚPãÖ+C£+ c@Ù¥+ä-VÉìòæ·ºæ³Ô­änã(Ş6Œ´ûTãÛíêéÜ­õŸBÒ\"”¬µM)^ôH«T÷õ§¨æ2Uá“ôTÈŞ©P³ajÃ²Y%Î\n´™ÓQ	T×db‘&HÑ‡bRkHÈ\rÉĞSlH`Hæ_£c\$qhÛ\0HKŸR9Y]l6PÆJ¼ÙãËŞ<€PH…á gª†*û„›Gõ²\0L©Ë\rP¦Î‘.U–tx±ÁóyWÄ1DÄ/±r—–D8¼›[“|Ü¥LØ	)ÅJ¬O.­~ÖÇy¾é Tõm áÍÅz¯¦ÕÎœHØz#Ë¯D1ÎIZ†·ÇiO\rñ§µ•ÜgF².7ŠSˆÒìH]¡h’ÊŞõhZ”®ÛÎÃYÙ]’û}Ôh9ÛD2 à&İ'(WcYŒ”\$	Ğš&‡B˜¦ƒ ^6¡x¶<ıƒÈ»­B-·ŒÒWü«z¾1JLÊ¦Ö£e>Ùİ!ÈñğÌƒb+§p‘hU›º ju†gŸÚª-ép›,€yå\r¡„7@SØuaŒ÷‡0ÌC` (a‡0X|C”&!œ0¢°A0mEaÔû‚€æ\nJğC\naH#ƒfa”B²D.¤¤8ÌV›H ¹-ôäÎa¹té5\\ä~hÕs± 5æ3“ÙˆDƒ+”“…ÈH‹#‘¤Ï´Åè³#²ä\"³ÇBtî›± ìe6%RM\"Ì\r9qş,¶!~RI^Q€¾ÅÃF“…r¹vñÂ4²‚\0Ê›Á ¯ĞÂÏ¸r<Ò¼9‡pŞ™ğeÀ4À@Éàa;Ğ3ĞD tÌğ^æ€.(±70À]-Ã8/¡ºm'™¶Ÿğ/GÆàé1‚ùÿ„¬à’iÏbì\rÁĞğÂ‹ùğ]‡ä7³ãä!k<A¤:fæ¬ó@0fEÅ5”hBlQEJT¤ø¤ó£A¥ñÌÆiI›jàM ¢]' ænÅrktD!@h®½ \n\n (Š!#zn“,94§Â-:æôß e§%KÁ“½\r¶”CTJ¼YUÇ“cJÔ\\`PïÑ–ui­fT]æÉ§ˆƒTtms­.\r;øÒ­å	N7n	?F](	İ]COA£ÉÈ¶…\nâM,MÀ¤”&ÉzÅ)Ë\"4O’—­b-õ±·Qg=NVLqA\$‡“¸ igÇ’‚°°İ?Ñğ€Ä:ú\nƒ’ë2MF|CDÛŒ¡°»p~å™î8‹£Ù&YëdzZED(ğ¦,!­’J˜Ù<p_ŠâuçU}Ye®œ¤¸×Tª»›|…_‹íÎï¡•ù@cOÁ˜4†pê²}\rÖ‚^†PÆHe¥·*C!òÙuÖ€|1ÂHNPŠ#>˜AŠú‚\0¦B` ¾‡°©„‚¥5„Œø4šyoAh&¶!´AY¬–³CM%a1ˆÈ…ÕÑq«Õ™è ¨·ŒyìB	³}Z¬\\£ï\"I»ÍÔ1­QzK‚€’}Ãm-@ù(d¬§“]½~+Î›(äd¿2£§‹8¼Ç•âÑ˜ü“Sğ¤3Wg“/UÈ§ñ§Xµ7›ñ³ ŠoL5>]¼\r!Œ5‚ü&v!²ÖŸE?CHf„Ô¯cÖxƒhu–\0€1P;öB F àM»åhQfcw·RÊ¿ªíU…u«.åaelLƒ¦Zƒ)ŠŞêÊÑëOwy«ÔrÎu+\$vâé6KV²¬ïŸa7rS—®C«ëÊ5èGBÒ+íÚD.iş!å˜ß_ÄUØ{“€Îö-;1¦ğD\0 ›¦4Ó˜'PCk`í´nSÁÈ¶ÌmzOÂe\r—å?Dv´•/Yh/zíŒïÎ®}XäB¯„õŸÍ½®“g]—\"†IÇ8Ìà­l¾	+õ°Ù´bBfEh· ”Z-Ïõ?¬öLlæ&\"3¥ér¨?hs—@Ë™LphÅR,Ô£G½éu\n|JÿpÈ’îÉ’T!”9ªÃ„|‡Xû_^6ú]ˆ8»«º“ıµ·…Ò~]ª1n}“*ºÑë…œÆ¡E˜×|6Nñ¬Ü­æâtÖlêï)£#\$:ÿ9¯zéD";break;case"th":$g="à\\! ˆMÀ¹@À0tD\0†Â \nX:&\0§€*à\n8Ş\0­	EÃ30‚/\0ZB (^\0µAàK…2\0ª•À&«‰bâ8¸KGàn‚ŒÄà	I”?J\\£)«Šbå.˜®)ˆ\\ò—S§®\"•¼s\0CÙWJ¤¶_6\\+eV¸6r¸JÃ©5kÒá´]ë³8õÄ@%9«9ªæ4·®fv2° #!˜Ğj65˜Æ:ïi\\ (µzÊ³y¾W eÂj‡\0MLrS«‚{q\0¼×§Ú|\\Iq	¾në[­Rã|¸”é¦›©7;ZÁá4	=j„¸´Ş.óùê°Y7Dƒ	ØÊ 7Ä‘¤ìi6LæS˜€èù£€È0xè4\r/èè0ŒOËÚ¶í‘p—²\0@«-±p¢BP¤,ã»JQpXD1’™«jCb¹2ÂÎ±;èó¤…—\$3€¸\$›Ú4Ã<3«°ô/¬m£Jæ¹î‹®®å†á'ê6¯¹DÚ²Š6ªÉ@»•)[t‡¯ÌÀÁ+.Ú~¶ Êñs0/íŠpé#\r“Rµ'éL[IÎ“Ê•EhD)1q7±óŒhæ§ Ş\rlŸ\n(‹ÂE¤£9ÁîÂÀ¨*P“³>—t\\›8Ò*/¸0äãCÜºŸ+*5NeÄ·	 âÌÀMhÚÚ<)é2×Â2<DA4’ˆ€Vlã,5È;›,+dƒµE„;˜€&iüdÇÛ(UGT6İ­§©œÓ;ªËÉ?IééF¬×U’í¹³•dWÈìq\\×nLÙ1X(UÜ¤Êt=`Ö•Å1'´JÎé'ñ\$!,¢\nı€wŒ™ZÅM¦#€±ø«‹¸2ÄXª¦r´˜áTNİä¾Ò²B2\$ÎT4—* PòşOˆ¡xHèÁŠ²6*í)h×¤Lí£N•µ‡QNÍBÀÆÆ:¶á-Î3¯Iğá(¬©›%j®ÎÊîdÜÒÃCı>æ§lª³rn¥Å)Uañ[¨§S‘*»±aZ²²ì0ùÆí¬o(9AJQ{·Bk‘fée¶%=Pï«ªí>DB@	¢ht)ŠvN ²\$@_¬m;š‹mYpZ‹»ü¸Éù¾Åå(6ƒô=Cä÷ã0Ì6Gƒ+Šæ‘jkUãäb³ÍIûæ_‹CMÈ¨7¾ChÂ7!\0ê7c¨Æ1¿ƒ˜Ì:\0Ø7Œñàæğr}¡„3†x 8%gµT\n\n˜)zëq—­ó¾¤Ôa=Oá)… ŒÎN©pr©<ØE² œÂc;ÊâÀÍWû»IëtÛ©\$ø8I™f2sl­IûZTHh*òvÕ0¸sÀ()¿PÒÃr‚†\rA¢ç2‚‡I4’Æ@ÄSd°õ‹Ãø^o’,kGn%8l‚hah98äÃ¸oLà2‡€à^xdÀ€ÖóÃ0=A :@àÁĞ/áŞJàÂ `n¡ÈG ÎÃ(n”á¿Ÿ(şyÇì:H ¾ƒ_Xk@ø\$†Ğà~Cl¡€ğ†|¥Á¹@á½œù‚CYï\r!ĞùÉ·Õ&ƒptsê€ÔÕdŠ«RFDĞæå9Å„'Hç¡¨R¡šÂ‰N¤éÁ™¤¦œJ…OŠ!—§bvVá8P	@ƒ\nTSæ=D“ÒvÌÎâ½L3lá#BfUJ¹YxO¼S•Ü9„°]˜ö¾WgC\nTp©D–ÆÓ>áaNÉ`ª©ÔXWKav8FÂ3œ“švÛlZeé œ«T’\\	ÜjBê\\¬’JOH ¥œ˜åÅAõç‡ê&`fA¼6‚\0ƒ&¬u@ò„7÷TõSÇíÑ4YZí.¦j¸D£Ñ[+¤öl°‚ìi•­8!J™ÓÊEA\0Ìå\$Š€Q'.uğ©ÑİŒtÀ‚’‘)x[zŠU,\n'£ÏRCë}ÒÜ7ÉpA!ƒiót(„À@Áï>ò#IøúÙÀi–ÑêfL»oVêíPGºN0v`Ç”T#‡)bÄ„ÅİÑ?CˆiY&v¸»ÚÜ9fj–ë©’^bàf'ÊQ“‘cT\\ï‹\nÅ’÷zRmĞJ7¹1Ö×c•¡ˆ·YÎ ó¶¥‚É\r€®³Æìa\09ğYàeòTÊÛL5¥`#[™‚c˜ S*(…P¨h8„1ÌDxÊÄ\ng'm)'¤vÊíş9´N/`D8£E.ô—”jÜùÛ84!=œª!1pf7Ö•5d!2~igvÄR&¾sĞá]Hİ/…xŠúı6)a;˜äÑ._xDç/èÓvPŒ?šÈŠ¯%†–½˜¢,#,%ãX]é)KA2ÖÔ´{¢ôlÁG½ ¶oŒìQ¹ÉÄ¯WOÈc@¡¬¡F¾öMˆ³+5Ğï·B³ZÆ<Ëm5„EäÀWr[/<«(|Ë¦bV¿DËéYu\"°A;€";break;case"tr":$g="E6šMÂ	Îi=ÁBQpÌÌ 9‚ˆ†ó™äÂ 3°ÖÆã!”äi6`'“yÈ\\\nb,P!Ú= 2ÀÌ‘H°€Äo<N‡XƒbnŸ§Â)Ì…'‰ÅbæÓ)ØÇ:GX‰ùœ@\nFC1 Ôl7ASv*|%4š F`(¨a1\râ	!®Ã^¦2Q×|%˜O3ã¥ĞßvMóÃA†\\ 7\\Îó´ÀÎe9ˆ—3©ÀÈa:sFƒNdépÉğ'˜éĞ«ÖËtFKÅèİ!¦vtÓ	´@e×ñĞ#>¿±ÇœÍæã‘„×ßßÌ ¢œ‚%Ö%M†Ã	º™:»§I÷r…?ÏÀÌF˜ù¸Ò 5ö»”	ı\"iñh`tÊtê„2í{äî§Ã†:/’BºŒÊ0Kt 4\r@ñ\r®êPX9ã`Ò*˜#Œ£z˜:A‚cJĞÁh“j½Œ£ÈÒ1ÃîË·01b\n€ŞîDo`Ò²Bb÷±¯!c,LøAÅ‹\\Œ‰³:2¤¬<µÊk€1¬–°\\“8c9³\0Òa”ïXäº#Èë¨ ­’hæŸ>ˆŠ<· Ë8/¡hàŠ4ñ4#É‹^å«ë¥ˆôZ;ËC-\rD\"èÊ7K¤ ÒK2¨ÂØÓT8æÓ£²^6TI:Ê4µ¨)‡B ˆhˆ¶<ØÈºŒ#Z3¸¢²^ˆPe86EKB\rŞ3ĞğÜ2¤â˜ê7ªxaéğÖŸH‚ Ş¹Ã#pò[ãÂ1³˜ÌíÎac49]ãÎ0¥‰d´€Ü\rP9…)<I8#È0ô¦)ÁH@58€\\É(Ú4#[›9¤‘ºN!+, ŸP¼ás]úè0(ÈŸ\$âzv9­ğç³zgX·ğØ¼ƒ2bÍ¹4 ŒHK.oœ¾ Pš0Õhğ@8kƒ˜î’Ê£(ğ8=ƒ(É‡ˆ¸Ğ9£0z\r è8aĞ^ûè]Sáğ\\’ŒázcÃÒå…á52ã¦æ/®.èÖÂHÚ87	ˆèã|¢318Ğ7Ê¬Úç4CK\n8#×o=çú\n¢Ì4*›Şà9è‰öw%¤â«Ä7IL0Ö‰wŠCm b€(:>xè˜=¨ê(¸ÓI8NO{ø§°ª’¨6aÖn\"„>¤<Ä%)Z—#ş{~ŒÃôîZš ”SØ±Z…•3À@cN eÛ•2<‚Î‚IGœ7p’CÉ!<TITÈ˜PæL]I¤3-À8‡Wp0rDÀ€ †GœÃ hiáŒ¹AóFiZû[‹ŒC˜rl^Éx\r`€(ğ¦\r‘ Ëùú”dµĞOŒÜ¨‘x`šœA#dxËcÜ¼Èg[à¡œÕĞy()ğ“˜¢xÎêğs½S­BlC8 \naD&B‚`KCIÁQêÔª­8r0®ºB¦„GŸ¡ªJ0x%bq“…#\"ğ7/C·˜01çJN–ƒ\\“7áÑÊeJµ\rŒ©“2®V‘ÉBd\rë-ó\$1>õÃ‘Lˆi¼S’Ä^{MCE°¤¢ş”`¨\nÑı‘ª²lëHU\nƒ‡îİAÄ”éVY@A1YÎ4çX3É^©ÎP–s ­@×:à(±\$å¼¸—8>œ	LVÁ6p†`òŸÒy;Ä¥YZ„M¡yF2OYzYfÂœô\0™\"ónHb%Lù¤¨}‚>“Dá—òQŞ1	çÄâJDƒ	…éşDÒéMœ¨’s¾XÉ0‚ãBR\n)†RBÍdQáÀ‹\$\0ˆÍY|3İ!¶!=jÜëBSôóø’{ã5c.R‰)bÜ‚KÄº—Ó\0";break;case"uk":$g="ĞI4‚É ¿h-`­ì&ÑKÁBQpÌÌ 9‚š	Ørñ ¾h-š¸-}[´¹Zõ¢‚•H`Rø¢„˜®dbèÒrbºh d±éZí¢Œ†Gà‹Hü¢ƒ Í\rõMs6@Se+ÈƒE6œJçTd€Jsh\$g\$æG†­fÉj> ”CˆÈf4†ãÌj¾¯SdRêBû\rh¡åSEÕ6\rVG!TI´ÂV±‘ÌĞÔ{Z‚L•¬éòÊ”i%QÏB×ØÜvUXh£ÚÊZ<,›Î¢A„ìeâÈÒv4›¦s)Ì@tåNC	Ót4zÇC	‹¥kK´4\\L+U0\\F½>¿kCß5ˆAø™2@ƒ\$M›à¬4é‹TA¥ŠJ\\GB›Œ4Ã;äõ!/«î¿(+`˜²ê’P¤¿ê{\\’µ\r'¬²TÏSX6„‹VZ(è\"I(L©` Œ¹ Ê±\nËf@¦‘\\¦‹’š¦.)Dæ‰™«(S³kZÚ±-êê„—.í*bŞED’¡~ÈHMƒVƒF: ‚£E:f¡FèÑ(É³ËšlÉGÔ4ß'R½’ªdX#Dš#Ïa¯+°a P ó¼Öøš\r2¨	„‚Sdî—Íì™ìš²(Äb4QĞf„øU‰‰x·)¤a®¯dˆºÌÌT«Cé\\Ò ¢c\"Ğ,IxÙĞ¥¢ÏZv­¡[U\rñdWÑú4—ÀPH…Á gt†5ËD5eÒ4XÆj8Zİ¡(Õ1 ÕDàÚE­\"™JÈ},JR±§z+5üP\"…£hÂèµÓ™i	µ*jHÿ©·‘¡=¿³å7c%q¢é<dTäeck„Ç/4)j•j?L¶,¦£É&…ÀJÒÕåªNK˜FñÍj°õ«üª49Ûsçùb•¡7z\"b†¤ù¥3·Ú^r‡1‹ZSa¦oFš˜th»f×L(Ò˜ZÚaxt¡½¸T]†Ã×ÂZˆC`è9N†0N@Ş3Ãd 2­tÅ…Ñ;õo\nƒ{—‰\rÃÈ@:Ã˜ê1Œn¨æ3£`@6\rã< 9Çœ?D0Œø˜ÜB8@6Â«¼aJÖÍj´\"ê†Ù¦)Ìî;°È\"9¡®’šŒºRdbÁS+‹)¢D¡0\"V[­hÔƒDº}\${I®Ì›(R“¦¬+a·6L~œÉq’*hÜ¨ˆÄx‹B4{ÌÄº£¤x›Ó™:dÊ¢‹'è©É›)è}¬!¡Ohagx9ÈDÃ¸oK82‡€à\\hdz ğ0œGè\"\rĞ:\0æx/ñÈğpe@ºp^CtOyÒ†ßA×q‡P:C¾yƒn\r`ˆÚmŠĞğÂ’Ã¿§€7¬ã°ïÃk9¤:È”è#[úXEtK³V8TÈjäYå=À´²RA•ƒ`\"ÛÙ’’T’JÈhæB“_˜P	@ŸÉ0”ÍšŠJà ³‚˜ù	9Nkª¡!I# ¡QğPäÜ‚Aq’„(ı¶T|¬Hx–)m]‹!¢!RYO-l‚]bì¯ˆ!DïÕ¦RĞˆ!½\$s|Ş¦è/’\"“˜\$ÙˆY\r8ÖóÕj-VÀDrJ 9)^†ùX¥\"º÷RÁ<	\$\\<œ @JÎ9Qô9ÅìxN³!ÔêÇĞÌƒxm#¹8Jx\"€ nÂ†z!	¡èjì½Q#ÒI\n™•Y4X«Äö/\0P	áL*/tJó&|ò=,—/¸i¥6*„Ïµ9ôœçíF‚ŒÕ²+¤*¤Êúø•\$9P’S/SY %Äà© t^4Mtçƒ8Âèã@oˆÀ‚† ÒÁ\0S\n!0c¢r„5ÁP(W@Ü³ƒLg…Qö>WÚ1F¨hr8ñ-Œ)”‡\"Ú”±GÏµ°úT²“½˜42Ä­À:/¬ı–(%Ñ>ä6JÈ3:D­•Ú4>W\rñqhÍi±Ú¡Ô-«A´¶æÓ»æÌLV[õ¸\r§KkˆÁZÊÕ··%9‹\"xÜ8li­í¦µÉTçÇ!©ÆÎÈ§ÇM-½¦Dé¨gg€PF¯îü:Â0@£Û»\n¡P#Ğp\"€c¨@3“Æh¥\rÓKV´¥'2<aÔ¢¢A-aàv÷‚TŞ\$ø9­áTÌ­BÙ7bqAhm°æoxI8[5ŒfÌR&\$Ô¹…Öu€lM0­Eb„ÜƒÉÃe„2ZVK–(Ğ=\"É^¢SL—ŠD¨Ö+‚\0IR‰~Ø°Õ¹´•„&ãE‘/öó’czn%ë&ÂòŞ%UyZzúHªRâ#¢4b'NG7 (&W*„sæ~wğ¨äÃÈ³qø\$ë`…ñL´nÑ÷Ti÷ªQe…_ôNc;Á‘RZ2í•S(•›xw‘¬ÙƒÓbıÕ\npT/S2ÜÊ ¯J¦ã£F#æyLúôÑgµ:ntšŞBvâv!â¤AÀ";break;case"vi":$g="Bp®”&á†³‚š *ó(J.™„0Q,ĞÃZŒâ¤)vƒ@Tf™\nípj£pº*ÃV˜ÍÃC`á]¦ÌrY<•#\$b\$L2–€@%9¥ÅIÄô×ŒÆÎ“„œ§4Ë…€¡€Äd3\rFÃqÀät9N1 QŠE3Ú¡±hÄj[—J;±ºŠo—ç\nÓ(©Ubµ´da¬®ÆIÂ¾Ri¦Då\0\0A)÷XŞ8@q:g!ÏC½_#yÃÌ¸™6:‚¶ëÑÚ‹Ì.—òŠšíK;×.ğ€¢™„ìi¶n÷»øì¬ÛÀ€ğÁEƒ{\rB\n'î¹»Ší_ÌÁˆ2œka§‚!W¹&Asv6Î'HáÈŞÆ»ÉÛä÷ ÉvO„IvL®Ã˜Â:‡J8æ¥©©B‚a”kºjÈ!ªpK(«0³N)b()Á7&hĞĞb,+]’/ÄP!\0Ï“ P›k¼<ÈH\n3°Ã|•/Ğ\"1‚'\0øÒ•ªÈ	Z80ÒãÈ›'ëêa¾ò`›/?ŠzT·&! bkëç/B<NË(3¤í/%3öšL©\$*°ÌCŒ‹Èò:¡@æLpÑª PH…¡ gN†³	Xën	~Å/E+ø1§L a”MË]é@ğìÌÒpM¤š	\n,­Ák`°2±S”\\¡¦IÁYÒ6GS!w3GÌK'< ÕS’a+\rfPÅöÒF£©µ_¼¥l<4Dä<º¬X@t&‰¡Ğ¦)BØó\"èZ6¡hÈ2;vCÔÊ²ìÈ6-\0P²7³Cä2„xÌ3\r€Ê—He{f—e=î”#£?%.j‰ÚHâŠ’)òrö‚5ó7z5lĞÙ.QÇ¨ëÓ¡«£5F‘†]?ƒs*0ä’%JœnºĞ¡ˆb˜¤#fƒN‹£Ç±…\0”ÉI ÅlÓò]cª]qC(îXşøÎ—\n¤Ä•»¦§p¼2ù—ã¦ÏG³ìêÙÎéF˜PÃÓYtVÍ¯œáwèÎËù_%¡\0x¡ØÌ„C@è:˜t…ã¿l# Û‘ApŞ9áxÊ7xCÅ 7cKş„Hæ43Œ£§X/ŒCdª5„Aóô Å\nd¢p¡à^0‡Öú?Oh*ÕóEãB\\÷O†¢ù¤kÌØ‰Œ£Ÿ’ÿ¥Ë¢¢†ŸšH¤á ¤“\r|.â€ %t-ÏàP	@š*c4G„¹ı'd™ô\$RÙ¸ g%½²”¶€pãLKèIõ?¬ŸŠ’ŠÀ€7€@–°£?ˆı]«Ö`úËBÖ¥Øâ“ô ³DjB¤âãğ~ãokÈ€·PRK‚I,`õ•ƒyüo\rv/@æ‚C)¨'ğ3ã®]Ó¼&‚†Àßà¬g…èàˆÄ	™\$'Ğ¢À^xS\n†œ:³æ†N]*Ái ‘*B`÷×i7'\$ìâ“Ø’@E¼œ†´RÁq†+°(h™{¹„%Ùy¡€\r)ó•wë'\\²b>ÅØ#H(oŠ3jsd¢`AàÆ1…	€Œ<ó1Lİ—Hôï7y¥\\Âs“Y\"SR	sP@p¹ĞĞÂäRˆ»¨x'1JAh\rÜßª—_ßC>-¹ó’a¡&%ú¤gË°º&5¦òB F ân!™´J)ê2&N@/Y ÿ¢!\$á‡³- fyè9F³®e!p\n\n!Õ*ºL§ù…‰ ¾µ8Äkçé4œïšuÏ™“>ÅØ‹3eÚ–pä•R`cDHá§* fÒR`XíyELÊOGˆtg2I&Cº(!nAp‰ÑHÂD‡Èğ\rÇ00Ìb/Ú\"J¨ğ9XDçÈT”ê‘SRú; ‹C~.ïz„›)cc‘¸‘£U’•HDÜÕ&™Â:€";break;case"zh":$g="ä^¨ês•\\šr¤îõâ|%ÌÂ:\$\nr.®„ö2Šr/d²È»[8Ğ S™8€r©!T¡\\¸s¦’I4¢b§r¬ñ•Ğ€Js!Kd²u´eåV¦©ÅDªX,#!˜Ğj6 §:¥t\nr£“îU:.Z²PË‘.…\rVWd^%äŒµ’r¡T²Ô¼*°s#UÕ`QdŞu'c(€ÜoF“±¤Øe3™Nb¦`êp2N™S¡ Ó£:LYñta~¨&6ÛŠ‹•r¶s®Ôükó{¾”òf“qŸw¹ß-œ×ü\n–2‹Œ #*«B!@éL©N…zµĞ¨@F«÷:QQãW­àÏs¡~™r.“ndJ¥ÊX’¨ËŠ;.ÚM(ìbx¦¥¹dè*Œb KœåaLŒ–K#Üs¹ÎX—g)<·Ì<&Â©q>så±ÒK–ˆÁtF>ÄÙÊDË!zH¸\$âĞC”*r“eñÊ^”Né.º=î2ô¾¤Ì›\rÃ¨I´ä1ÎP+!3—¤«vs„	Ï/ÈóHX¥Ùu±R2™ĞE%ƒËD2àPH…Á g@†©i Nå¤’“—g1¡—¤iÎ^•ÉiÀD{ÆFr\$br‘EB\$seL4ZªÒAÊbZN”§Aî#%ÙÒQ7O	Y¤y#RË¡,è 5}bŸAÖ\r†@,aÎGMŠ \$	Ğš&‡B˜¦cÍÄ<‹¡pÚ6…Ã ÈTµ=S814¨Ø:Lk#“*7ŒÃ0Øæ­ª\\§‘E¢÷œÅ™Pt’H¨7³hÂ7!\0ê7c¨Æ1´C˜Ì:\0Ø7Œî`æ4ƒ–*0Œã˜eö0@6¹ƒ«VaNÂÄ¤@@!ŠbŒ\$å!ÎD‘Š±ÒC‘³]bI‡)JÁXV@C„…@O±‚:4ãtë>Ä<T†F—¦’Å„Ï0j‹¸š0mXäÌîƒ˜î7S Ê<Mô2Á\0x0²7ĞÌ„C@è:˜t…ã¿,#&h7£]¾áxÊ7tCÃWŒl DÒ_-éÆí&5„Ağ’6øÛÑà^0‡Ê3Gİµ£|èÒæcÖÊ#£3Îâ\\àÜ:6ºğæ9õ3©~B%	vtEZW½«²Xs.“øÿ@zâNTä¡_2LÉĞ@(	€Aõ¿òùq†”Ï¨ˆ¢V¯:°…l¤¸˜#MI¹9ˆLs\nájœX*Ç[QD'•\0 xF0\$‘0ò½Ã iN†]ç7FñÍq£_AÄ:š'œƒo\r € ¹¦\0İÍk£‘ÃcI[Á¡6¤ÄUø4ıÂxS\nìÃÀâWÄyA+ÀÅ!Âô ,&…*p@:D*’Rƒ”H‘áv)a2‰±grÜÊøˆ4†p@ÂˆL˜ÏS:â0TLM:—pßsÍ‘q!Ã`äeñhY§xğ\nGº.G8›1eİ‹!Ì&Å¢k”ò¦U’ÉZtÌ‚–r©—vdvåÙâ]¦Áz,G(“¦0!¯@Ø\nâ`ia¬6ƒl‡¦‘ˆ¼`Ò˜«Í6¡F³0êİdÌlAT*`Z£sxæ›Vd]d­¢¼^SSˆà½]ª TEj]@PM›Óv	’¦K@…c @‰AÈ/Ëˆåá'—ÓæùRqÈ\"L_aŠU¥Ã˜Jš9D\0¼1tZ‘:’`¡	À(&Hbsiå>fmğÊš“bœÁø%¢%¬?‚\$Ç0GÍxÏ†3VºÉ‰OG	b~ˆrÏ>çé‚-Q& !Ì¡ÔH¢ŒŠU&1B#…9…ˆ1P";break;case"zh-tw":$g="ä^¨ê%Ó•\\šr¥ÑÎõâ|%ÌÎu:HçB(\\Ë4«‘pŠr –neRQÌ¡D8Ğ S•\nt*.tÒI&”G‘N”ÊAÊ¤S¹V÷:	t%9Sy:\"<r«STâ ,#!˜Ğj61uL\0¼–£“îU:.–²I9“ˆ—BÍæK&]\nDªXç[ªÅ}-,°r¨“ÖûÎöŒ¿‹&ó¨€Ğa;Dãx€àr4&Ã)œÊs3§SÂtÍ\rAĞÂbÒ¥¨E•E1»ŞÔ£Êg:åxç]#0, (§˜4›Œü\r÷ñˆÅG‘qäZ†–¢SÅ )ĞªOLP\0¨ıÎ”«:}µï»áÚr¢òå´yZî¤se¢\\BœÅABs–¤ @¤2*bPr–î\n¦ª²*‰.Ocê÷°D\nt”\$ñÊO-Ç1*\\CJY.R®DùÌLGI,I½IÒ@H‹–Å‘Ğ[°§)r_ «ÂK¯j³–Á•ç)2«¥Áft(qÊWÈĞs“%Ú\\R©epr\$,Á1³#®Ä“ÇIA5er2òØ˜R-8ÎA b©d8¡-Ç!v]œÄ!åììsÄ‘Ê]àRxŸ éi—¤åD@—!QsZH\$kIÏa|C9Tö¡.«'„%p–•ä!ÊC—Il+-ÙVd<(D\rk[—e³\"s‘åò¨«	@t&‰¡Ğ¦)BØóu\"è\\6¡pÈ2Uui&C®«ºò\rƒ äÉ2\0Â93CxÌ3\rˆÊİ…át¹*š/0Ùvh¨7³£hÂ7!\0ê7c¨Æ1´ã˜Ì:\0Ø7Œîˆæ5#–:0Œã¢fá,ğ6º#¬ù?7HÂ4J‘	¤!ŠbŒÔãXÊ7/Ï‘täk¯0‘Ú`¾¤±]‘	ñOÍx«\")ä£HŞ7¡dt\$tb†š–©«0û,ÚÈ‰£æØLöü9ãxå<£Àà4àƒ \\ƒ-‚ÁèD4ƒ à9‡Ax^;ôpÂ2gƒpÊ9Ü0Îéı`ğØdPÜ„MNÓœ°¾ÜccXD	#hàÒ\rºxèã}µ+d7Ï\rPAiÙÀèÏtø×L7ÖĞ9}–Ú_ÄetEYÊJì…¶ŒÀ1APaÆ.DM\$]É'1(+Ç0(€ üP:	Ah4†‚CKïhmša\$G@­‚\$¬ódFÜIq0&M\ra\\-QP¸j…\n‰Á\0OÊ	gj'Í8Q(„	!˜–Jx3\\9´÷¢lÍC!ÔÓ½pÌƒxmÒ0§lšx l®\"š˜à1º\n<)…@@¾KÀ™'âªáÌ.ÙBƒÚ4a%‡B\"†°İæŒLEi!Ê3*ØÛxa½Ñ°\$òC8 \naD&\0ÌhÌÑ¢rA*“ÀixNë†˜\"”EFeÔaRÛÓ˜çN­¹ô>£Äyp²ÂlZtäümÉ¼ÅKùd+¡É,gH©Ëf#ÏØˆ1ôX‘>/ÌˆC_Á°Å°ÒÃX mr8;ÙcÓz¤31ÙLn‚4˜zaÕ¿§“4ÍÂ¨TÀ´çYÒ7M¸rˆÄ¢›YÑ‚DlJ·ì¾%|h/)4Ù×;U™*U†à'£x›Oã¤K‰ÁĞ L*O9§µR¡^Å‘]A~+Ÿğ¹2)È@”¸\\9EğŒ;”ğÉ•E¨ó©‚\\m“¹)b\$@?šd9„òªm1›è:\"øbô–Ğf+Yh:d[ôJ5¡V9”r<ÑÒ‰—ÃÌ(Dd)\"æ(áP­E@";break;}$Jf=array();foreach(explode("\n",lzw_decompress($g))as$X)$Jf[]=(strpos($X,"\t")?explode("\t",$X):$X);return$Jf;}if(!$Jf){$Jf=get_translations($ba);$_SESSION["translations"]=$Jf;}if(extension_loaded('pdo')){class
Min_PDO
extends
PDO{var$_result,$server_info,$affected_rows,$errno,$error;function
__construct(){global$b;$ke=array_search("SQL",$b->operators);if($ke!==false)unset($b->operators[$ke]);}function
dsn($Eb,$V,$H){try{parent::__construct($Eb,$V,$H);}catch(Exception$Sb){auth_error(h($Sb->getMessage()));}$this->setAttribute(13,array('Min_PDOStatement'));$this->server_info=@$this->getAttribute(4);}function
query($I,$Sf=false){$J=parent::query($I);$this->error="";if(!$J){list(,$this->errno,$this->error)=$this->errorInfo();return
false;}$this->store_result($J);return$J;}function
multi_query($I){return$this->_result=$this->query($I);}function
store_result($J=null){if(!$J){$J=$this->_result;if(!$J)return
false;}if($J->columnCount()){$J->num_rows=$J->rowCount();return$J;}$this->affected_rows=$J->rowCount();return
true;}function
next_result(){if(!$this->_result)return
false;$this->_result->_offset=0;return@$this->_result->nextRowset();}function
result($I,$o=0){$J=$this->query($I);if(!$J)return
false;$L=$J->fetch();return$L[$o];}}class
Min_PDOStatement
extends
PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch(2);}function
fetch_row(){return$this->fetch(3);}function
fetch_field(){$L=(object)$this->getColumnMeta($this->_offset++);$L->orgtable=$L->table;$L->orgname=$L->name;$L->charsetnr=(in_array("blob",(array)$L->flags)?63:0);return$L;}}}$Bb=array();class
Min_SQL{var$_conn;function
__construct($h){$this->_conn=$h;}function
select($R,$N,$Z,$s,$E=array(),$_=1,$F=0,$pe=false){global$b,$y;$Wc=(count($s)<count($N));$I=$b->selectQueryBuild($N,$Z,$s,$E,$_,$F);if(!$I)$I="SELECT".limit(($_GET["page"]!="last"&&+$_&&$s&&$Wc&&$y=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$N)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($s&&$Wc?"\nGROUP BY ".implode(", ",$s):"").($E?"\nORDER BY ".implode(", ",$E):""),($_!=""?+$_:null),($F?$_*$F:0),"\n");$hf=microtime(true);$K=$this->_conn->query($I);if($pe)echo$b->selectQuery($I,format_time($hf));return$K;}function
delete($R,$ve,$_=0){$I="FROM ".table($R);return
queries("DELETE".($_?limit1($I,$ve):" $I$ve"));}function
update($R,$P,$ve,$_=0,$Te="\n"){$fg=array();foreach($P
as$z=>$X)$fg[]="$z = $X";$I=table($R)." SET$Te".implode(",$Te",$fg);return
queries("UPDATE".($_?limit1($I,$ve):" $I$ve"));}function
insert($R,$P){return
queries("INSERT INTO ".table($R).($P?" (".implode(", ",array_keys($P)).")\nVALUES (".implode(", ",$P).")":" DEFAULT VALUES"));}function
insertUpdate($R,$M,$ne){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}}$Bb["sqlite"]="SQLite 3";$Bb["sqlite2"]="SQLite 2";if(isset($_GET["sqlite"])||isset($_GET["sqlite2"])){$le=array((isset($_GET["sqlite"])?"SQLite3":"SQLite"),"PDO_SQLite");define("DRIVER",(isset($_GET["sqlite"])?"sqlite":"sqlite2"));if(class_exists(isset($_GET["sqlite"])?"SQLite3":"SQLiteDatabase")){if(isset($_GET["sqlite"])){class
Min_SQLite{var$extension="SQLite3",$server_info,$affected_rows,$errno,$error,$_link;function
__construct($ec){$this->_link=new
SQLite3($ec);$hg=$this->_link->version();$this->server_info=$hg["versionString"];}function
query($I){$J=@$this->_link->query($I);$this->error="";if(!$J){$this->errno=$this->_link->lastErrorCode();$this->error=$this->_link->lastErrorMsg();return
false;}elseif($J->numColumns())return
new
Min_Result($J);$this->affected_rows=$this->_link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->_link->escapeString($Q)."'":"x'".reset(unpack('H*',$Q))."'");}function
store_result(){return$this->_result;}function
result($I,$o=0){$J=$this->query($I);if(!is_object($J))return
false;$L=$J->_result->fetchArray();return$L[$o];}}class
Min_Result{var$_result,$_offset=0,$num_rows;function
__construct($J){$this->_result=$J;}function
fetch_assoc(){return$this->_result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->_result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$e=$this->_offset++;$U=$this->_result->columnType($e);return(object)array("name"=>$this->_result->columnName($e),"type"=>$U,"charsetnr"=>($U==SQLITE3_BLOB?63:0),);}function
__desctruct(){return$this->_result->finalize();}}}else{class
Min_SQLite{var$extension="SQLite",$server_info,$affected_rows,$error,$_link;function
__construct($ec){$this->server_info=sqlite_libversion();$this->_link=new
SQLiteDatabase($ec);}function
query($I,$Sf=false){$Ad=($Sf?"unbufferedQuery":"query");$J=@$this->_link->$Ad($I,SQLITE_BOTH,$n);$this->error="";if(!$J){$this->error=$n;return
false;}elseif($J===true){$this->affected_rows=$this->changes();return
true;}return
new
Min_Result($J);}function
quote($Q){return"'".sqlite_escape_string($Q)."'";}function
store_result(){return$this->_result;}function
result($I,$o=0){$J=$this->query($I);if(!is_object($J))return
false;$L=$J->_result->fetch();return$L[$o];}}class
Min_Result{var$_result,$_offset=0,$num_rows;function
__construct($J){$this->_result=$J;if(method_exists($J,'numRows'))$this->num_rows=$J->numRows();}function
fetch_assoc(){$L=$this->_result->fetch(SQLITE_ASSOC);if(!$L)return
false;$K=array();foreach($L
as$z=>$X)$K[($z[0]=='"'?idf_unescape($z):$z)]=$X;return$K;}function
fetch_row(){return$this->_result->fetch(SQLITE_NUM);}function
fetch_field(){$D=$this->_result->fieldName($this->_offset++);$ge='(\\[.*]|"(?:[^"]|"")*"|(.+))';if(preg_match("~^($ge\\.)?$ge\$~",$D,$B)){$R=($B[3]!=""?$B[3]:idf_unescape($B[2]));$D=($B[5]!=""?$B[5]:idf_unescape($B[4]));}return(object)array("name"=>$D,"orgname"=>$D,"orgtable"=>$R,);}}}}elseif(extension_loaded("pdo_sqlite")){class
Min_SQLite
extends
Min_PDO{var$extension="PDO_SQLite";function
__construct($ec){$this->dsn(DRIVER.":$ec","","");}}}if(class_exists("Min_SQLite")){class
Min_DB
extends
Min_SQLite{function
__construct(){parent::__construct(":memory:");}function
select_db($ec){if(is_readable($ec)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$ec)?$ec:dirname($_SERVER["SCRIPT_FILENAME"])."/$ec")." AS a")){parent::__construct($ec);return
true;}return
false;}function
multi_query($I){return$this->_result=$this->query($I);}function
next_result(){return
false;}}}class
Min_Driver
extends
Min_SQL{function
insertUpdate($R,$M,$ne){$fg=array();foreach($M
as$P)$fg[]="(".implode(", ",$P).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($M))).") VALUES\n".implode(",\n",$fg));}}function
idf_escape($v){return'"'.str_replace('"','""',$v).'"';}function
table($v){return
idf_escape($v);}function
connect(){return
new
Min_DB;}function
get_databases(){return
array();}function
limit($I,$Z,$_,$Ld=0,$Te=" "){return" $I$Z".($_!==null?$Te."LIMIT $_".($Ld?" OFFSET $Ld":""):"");}function
limit1($I,$Z){global$h;return($h->result("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($I,$Z,1):" $I$Z");}function
db_collation($m,$cb){global$h;return$h->result("PRAGMA encoding");}function
engines(){return
array();}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name = 'sqlite_sequence'), name",1);}function
count_tables($l){return
array();}function
table_status($D=""){global$h;$K=array();foreach(get_rows("SELECT name AS Name, type AS Engine FROM sqlite_master WHERE type IN ('table', 'view') ".($D!=""?"AND name = ".q($D):"ORDER BY name"))as$L){$L["Oid"]=1;$L["Auto_increment"]="";$L["Rows"]=$h->result("SELECT COUNT(*) FROM ".idf_escape($L["Name"]));$K[$L["Name"]]=$L;}foreach(get_rows("SELECT * FROM sqlite_sequence",null,"")as$L)$K[$L["name"]]["Auto_increment"]=$L["seq"];return($D!=""?$K[$D]:$K);}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){global$h;return!$h->result("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($R){global$h;$K=array();$ne="";foreach(get_rows("PRAGMA table_info(".table($R).")")as$L){$D=$L["name"];$U=strtolower($L["type"]);$sb=$L["dflt_value"];$K[$D]=array("field"=>$D,"type"=>(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":"numeric")))),"full_type"=>$U,"default"=>(preg_match("~'(.*)'~",$sb,$B)?str_replace("''","'",$B[1]):($sb=="NULL"?null:$sb)),"null"=>!$L["notnull"],"privileges"=>array("select"=>1,"insert"=>1,"update"=>1),"primary"=>$L["pk"],);if($L["pk"]){if($ne!="")$K[$ne]["auto_increment"]=false;elseif(preg_match('~^integer$~i',$U))$K[$D]["auto_increment"]=true;$ne=$D;}}$ff=$h->result("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));preg_match_all('~(("[^"]*+")+|[a-z0-9_]+)\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$ff,$C,PREG_SET_ORDER);foreach($C
as$B){$D=str_replace('""','"',preg_replace('~^"|"$~','',$B[1]));if($K[$D])$K[$D]["collation"]=trim($B[3],"'");}return$K;}function
indexes($R,$i=null){global$h;if(!is_object($i))$i=$h;$K=array();$ff=$i->result("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*")++)~i',$ff,$B)){$K[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$B[1],$C,PREG_SET_ORDER);foreach($C
as$B){$K[""]["columns"][]=idf_unescape($B[2]).$B[4];$K[""]["descs"][]=(preg_match('~DESC~i',$B[5])?'1':null);}}if(!$K){foreach(fields($R)as$D=>$o){if($o["primary"])$K[""]=array("type"=>"PRIMARY","columns"=>array($D),"lengths"=>array(),"descs"=>array(null));}}$gf=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$i);foreach(get_rows("PRAGMA index_list(".table($R).")",$i)as$L){$D=$L["name"];$w=array("type"=>($L["unique"]?"UNIQUE":"INDEX"));$w["lengths"]=array();$w["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($D).")",$i)as$Ke){$w["columns"][]=$Ke["name"];$w["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($D).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$gf[$D],$Ae)){preg_match_all('/("[^"]*+")+( DESC)?/',$Ae[2],$C);foreach($C[2]as$z=>$X){if($X)$w["descs"][$z]='1';}}if(!$K[""]||$w["type"]!="UNIQUE"||$w["columns"]!=$K[""]["columns"]||$w["descs"]!=$K[""]["descs"]||!preg_match("~^sqlite_~",$D))$K[$D]=$w;}return$K;}function
foreign_keys($R){$K=array();foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$L){$q=&$K[$L["id"]];if(!$q)$q=$L;$q["source"][]=$L["from"];$q["target"][]=$L["to"];}return$K;}function
view($D){global$h;return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\\s+~iU','',$h->result("SELECT sql FROM sqlite_master WHERE name = ".q($D))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($m){return
false;}function
error(){global$h;return
h($h->error);}function
check_sqlite_name($D){global$h;$Yb="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($Yb)\$~",$D)){$h->error=lang(21,str_replace("|",", ",$Yb));return
false;}return
true;}function
create_database($m,$d){global$h;if(file_exists($m)){$h->error=lang(22);return
false;}if(!check_sqlite_name($m))return
false;try{$A=new
Min_SQLite($m);}catch(Exception$Sb){$h->error=$Sb->getMessage();return
false;}$A->query('PRAGMA encoding = "UTF-8"');$A->query('CREATE TABLE adminer (i)');$A->query('DROP TABLE adminer');return
true;}function
drop_databases($l){global$h;$h->__construct(":memory:");foreach($l
as$m){if(!@unlink($m)){$h->error=lang(22);return
false;}}return
true;}function
rename_database($D,$d){global$h;if(!check_sqlite_name($D))return
false;$h->__construct(":memory:");$h->error=lang(22);return@rename(DB,$D);}function
auto_increment(){return" PRIMARY KEY".(DRIVER=="sqlite"?" AUTOINCREMENT":"");}function
alter_table($R,$D,$p,$mc,$fb,$Nb,$d,$Ea,$ee){$cg=($R==""||$mc);foreach($p
as$o){if($o[0]!=""||!$o[1]||$o[2]){$cg=true;break;}}$c=array();$Yd=array();foreach($p
as$o){if($o[1]){$c[]=($cg?$o[1]:"ADD ".implode($o[1]));if($o[0]!="")$Yd[$o[0]]=$o[1][0];}}if(!$cg){foreach($c
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$D&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($D)))return
false;}elseif(!recreate_table($R,$D,$c,$Yd,$mc))return
false;if($Ea)queries("UPDATE sqlite_sequence SET seq = $Ea WHERE name = ".q($D));return
true;}function
recreate_table($R,$D,$p,$Yd,$mc,$x=array()){if($R!=""){if(!$p){foreach(fields($R)as$z=>$o){$p[]=process_field($o,$o);$Yd[$z]=idf_escape($z);}}$oe=false;foreach($p
as$o){if($o[6])$oe=true;}$Db=array();foreach($x
as$z=>$X){if($X[2]=="DROP"){$Db[$X[1]]=true;unset($x[$z]);}}foreach(indexes($R)as$bd=>$w){$f=array();foreach($w["columns"]as$z=>$e){if(!$Yd[$e])continue
2;$f[]=$Yd[$e].($w["descs"][$z]?" DESC":"");}if(!$Db[$bd]){if($w["type"]!="PRIMARY"||!$oe)$x[]=array($w["type"],$bd,$f);}}foreach($x
as$z=>$X){if($X[0]=="PRIMARY"){unset($x[$z]);$mc[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$bd=>$q){foreach($q["source"]as$z=>$e){if(!$Yd[$e])continue
2;$q["source"][$z]=idf_unescape($Yd[$e]);}if(!isset($mc[" $bd"]))$mc[]=" ".format_foreign_key($q);}queries("BEGIN");}foreach($p
as$z=>$o)$p[$z]="  ".implode($o);$p=array_merge($p,array_filter($mc));if(!queries("CREATE TABLE ".table($R!=""?"adminer_$D":$D)." (\n".implode(",\n",$p)."\n)"))return
false;if($R!=""){if($Yd&&!queries("INSERT INTO ".table("adminer_$D")." (".implode(", ",$Yd).") SELECT ".implode(", ",array_map('idf_escape',array_keys($Yd)))." FROM ".table($R)))return
false;$Pf=array();foreach(triggers($R)as$Nf=>$Af){$Mf=trigger($Nf);$Pf[]="CREATE TRIGGER ".idf_escape($Nf)." ".implode(" ",$Af)." ON ".table($D)."\n$Mf[Statement]";}if(!queries("DROP TABLE ".table($R)))return
false;queries("ALTER TABLE ".table("adminer_$D")." RENAME TO ".table($D));if(!alter_indexes($D,$x))return
false;foreach($Pf
as$Mf){if(!queries($Mf))return
false;}queries("COMMIT");}return
true;}function
index_sql($R,$U,$D,$f){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($D!=""?$D:uniqid($R."_"))." ON ".table($R)." $f";}function
alter_indexes($R,$c){foreach($c
as$ne){if($ne[0]=="PRIMARY")return
recreate_table($R,$R,array(),array(),array(),$c);}foreach(array_reverse($c)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables($T){return
apply_queries("DELETE FROM",$T);}function
drop_views($jg){return
apply_queries("DROP VIEW",$jg);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
move_tables($T,$jg,$uf){return
false;}function
trigger($D){global$h;if($D=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$v='(?:[^`"\\s]+|`[^`]*`|"[^"]*")+';$Of=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$v\\s*(".implode("|",$Of["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($v))?\\s+ON\\s*$v\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",$h->result("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($D)),$B);$Kd=$B[3];return
array("Timing"=>strtoupper($B[1]),"Event"=>strtoupper($B[2]).($Kd?" OF":""),"Of"=>($Kd[0]=='`'||$Kd[0]=='"'?idf_unescape($Kd):$Kd),"Trigger"=>$D,"Statement"=>$B[4],);}function
triggers($R){$K=array();$Of=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$L){preg_match('~^CREATE\\s+TRIGGER\\s*(?:[^`"\\s]+|`[^`]*`|"[^"]*")+\\s*('.implode("|",$Of["Timing"]).')\\s*(.*)\\s+ON\\b~iU',$L["sql"],$B);$K[$L["name"]]=array($B[1],$B[2]);}return$K;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($D,$U){}function
routines(){}function
routine_languages(){}function
begin(){return
queries("BEGIN");}function
last_id(){global$h;return$h->result("SELECT LAST_INSERT_ROWID()");}function
explain($h,$I){return$h->query("EXPLAIN QUERY PLAN $I");}function
found_rows($S,$Z){}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($Ne){return
true;}function
create_sql($R,$Ea){global$h;$K=$h->result("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$D=>$w){if($D=='')continue;$K.=";\n\n".index_sql($R,$w['type'],$D,"(".implode(", ",array_map('idf_escape',$w['columns'])).")");}return$K;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($k){}function
trigger_sql($R,$lf){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){global$h;$K=array();foreach(array("auto_vacuum","cache_size","count_changes","default_cache_size","empty_result_callbacks","encoding","foreign_keys","full_column_names","fullfsync","journal_mode","journal_size_limit","legacy_file_format","locking_mode","page_size","max_page_count","read_uncommitted","recursive_triggers","reverse_unordered_selects","secure_delete","short_column_names","synchronous","temp_store","temp_store_directory","schema_version","integrity_check","quick_check")as$z)$K[$z]=$h->result("PRAGMA $z");return$K;}function
show_status(){$K=array();foreach(get_vals("PRAGMA compile_options")as$Td){list($z,$X)=explode("=",$Td,2);$K[$z]=$X;}return$K;}function
convert_field($o){}function
unconvert_field($o,$K){return$K;}function
support($bc){return
preg_match('~^(columns|database|drop_col|dump|indexes|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$bc);}$y="sqlite";$Rf=array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0);$kf=array_keys($Rf);$Yf=array();$Sd=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");$vc=array("hex","length","lower","round","unixepoch","upper");$xc=array("avg","count","count distinct","group_concat","max","min","sum");$Gb=array(array(),array("integer|real|numeric"=>"+/-","text"=>"||",));}$Bb["pgsql"]="PostgreSQL";if(isset($_GET["pgsql"])){$le=array("PgSQL","PDO_PgSQL");define("DRIVER","pgsql");if(extension_loaded("pgsql")){class
Min_DB{var$extension="PgSQL",$_link,$_result,$_string,$_database=true,$server_info,$affected_rows,$error;function
_error($Qb,$n){if(ini_bool("html_errors"))$n=html_entity_decode(strip_tags($n));$n=preg_replace('~^[^:]*: ~','',$n);$this->error=$n;}function
connect($O,$V,$H){global$b;$m=$b->database();set_error_handler(array($this,'_error'));$this->_string="host='".str_replace(":","' port='",addcslashes($O,"'\\"))."' user='".addcslashes($V,"'\\")."' password='".addcslashes($H,"'\\")."'";$this->_link=@pg_connect("$this->_string dbname='".($m!=""?addcslashes($m,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->_link&&$m!=""){$this->_database=false;$this->_link=@pg_connect("$this->_string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->_link){$hg=pg_version($this->_link);$this->server_info=$hg["server"];pg_set_client_encoding($this->_link,"UTF8");}return(bool)$this->_link;}function
quote($Q){return"'".pg_escape_string($this->_link,$Q)."'";}function
select_db($k){global$b;if($k==$b->database())return$this->_database;$K=@pg_connect("$this->_string dbname='".addcslashes($k,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($K)$this->_link=$K;return$K;}function
close(){$this->_link=@pg_connect("$this->_string dbname='postgres'");}function
query($I,$Sf=false){$J=@pg_query($this->_link,$I);$this->error="";if(!$J){$this->error=pg_last_error($this->_link);return
false;}elseif(!pg_num_fields($J)){$this->affected_rows=pg_affected_rows($J);return
true;}return
new
Min_Result($J);}function
multi_query($I){return$this->_result=$this->query($I);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($I,$o=0){$J=$this->query($I);if(!$J||!$J->num_rows)return
false;return
pg_fetch_result($J->_result,0,$o);}}class
Min_Result{var$_result,$_offset=0,$num_rows;function
__construct($J){$this->_result=$J;$this->num_rows=pg_num_rows($J);}function
fetch_assoc(){return
pg_fetch_assoc($this->_result);}function
fetch_row(){return
pg_fetch_row($this->_result);}function
fetch_field(){$e=$this->_offset++;$K=new
stdClass;if(function_exists('pg_field_table'))$K->orgtable=pg_field_table($this->_result,$e);$K->name=pg_field_name($this->_result,$e);$K->orgname=$K->name;$K->type=pg_field_type($this->_result,$e);$K->charsetnr=($K->type=="bytea"?63:0);return$K;}function
__destruct(){pg_free_result($this->_result);}}}elseif(extension_loaded("pdo_pgsql")){class
Min_DB
extends
Min_PDO{var$extension="PDO_PgSQL";function
connect($O,$V,$H){global$b;$m=$b->database();$Q="pgsql:host='".str_replace(":","' port='",addcslashes($O,"'\\"))."' options='-c client_encoding=utf8'";$this->dsn("$Q dbname='".($m!=""?addcslashes($m,"'\\"):"postgres")."'",$V,$H);return
true;}function
select_db($k){global$b;return($b->database()==$k);}function
close(){}}}class
Min_Driver
extends
Min_SQL{function
insertUpdate($R,$M,$ne){global$h;foreach($M
as$P){$Zf=array();$Z=array();foreach($P
as$z=>$X){$Zf[]="$z = $X";if(isset($ne[idf_unescape($z)]))$Z[]="$z = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$Zf)." WHERE ".implode(" AND ",$Z))&&$h->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($P)).") VALUES (".implode(", ",$P).")")))return
false;}return
true;}}function
idf_escape($v){return'"'.str_replace('"','""',$v).'"';}function
table($v){return
idf_escape($v);}function
connect(){global$b,$Rf,$kf;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2])){if($h->server_info>=9){$h->query("SET application_name = 'Adminer'");if($h->server_info>=9.2){$kf[lang(23)][]="json";$Rf["json"]=4294967295;if($h->server_info>=9.4){$kf[lang(23)][]="jsonb";$Rf["jsonb"]=4294967295;}}}return$h;}return$h->error;}function
get_databases(){return
get_vals("SELECT datname FROM pg_database WHERE has_database_privilege(datname, 'CONNECT') ORDER BY datname");}function
limit($I,$Z,$_,$Ld=0,$Te=" "){return" $I$Z".($_!==null?$Te."LIMIT $_".($Ld?" OFFSET $Ld":""):"");}function
limit1($I,$Z){return" $I$Z";}function
db_collation($m,$cb){global$h;return$h->result("SHOW LC_COLLATE");}function
engines(){return
array();}function
logged_user(){global$h;return$h->result("SELECT user");}function
tables_list(){$I="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support('materializedview'))$I.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$I.="
ORDER BY 1";return
get_key_vals($I);}function
count_tables($l){return
array();}function
table_status($D=""){$K=array();foreach(get_rows("SELECT c.relname AS \"Name\", CASE c.relkind WHEN 'r' THEN 'table' WHEN 'm' THEN 'materialized view' ELSE 'view' END AS \"Engine\", pg_relation_size(c.oid) AS \"Data_length\", pg_total_relation_size(c.oid) - pg_relation_size(c.oid) AS \"Index_length\", obj_description(c.oid, 'pg_class') AS \"Comment\", c.relhasoids::int AS \"Oid\", c.reltuples as \"Rows\", n.nspname
FROM pg_class c
JOIN pg_namespace n ON(n.nspname = current_schema() AND n.oid = c.relnamespace)
WHERE relkind IN ('r', 'm', 'v')
".($D!=""?"AND relname = ".q($D):"ORDER BY c.oid"))as$L)$K[$L["Name"]]=$L;return($D!=""?$K[$D]:$K);}function
is_view($S){return
in_array($S["Engine"],array("view","materialized view"));}function
fk_support($S){return
true;}function
fields($R){$K=array();$wa=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',);foreach(get_rows("SELECT a.attname AS field, format_type(a.atttypid, a.atttypmod) AS full_type, d.adsrc AS default, a.attnotnull::int, col_description(c.oid, a.attnum) AS comment
FROM pg_class c
JOIN pg_namespace n ON c.relnamespace = n.oid
JOIN pg_attribute a ON c.oid = a.attrelid
LEFT JOIN pg_attrdef d ON c.oid = d.adrelid AND a.attnum = d.adnum
WHERE c.relname = ".q($R)."
AND n.nspname = current_schema()
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$L){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$L["full_type"],$B);list(,$U,$ld,$L["length"],$ra,$ya)=$B;$L["length"].=$ya;$Ta=$U.$ra;if(isset($wa[$Ta])){$L["type"]=$wa[$Ta];$L["full_type"]=$L["type"].$ld.$ya;}else{$L["type"]=$U;$L["full_type"]=$L["type"].$ld.$ra.$ya;}$L["null"]=!$L["attnotnull"];$L["auto_increment"]=preg_match('~^nextval\\(~i',$L["default"]);$L["privileges"]=array("insert"=>1,"select"=>1,"update"=>1);if(preg_match('~(.+)::[^)]+(.*)~',$L["default"],$B))$L["default"]=($B[1][0]=="'"?idf_unescape($B[1]):$B[1]).$B[2];$K[$L["field"]]=$L;}return$K;}function
indexes($R,$i=null){global$h;if(!is_object($i))$i=$h;$K=array();$sf=$i->result("SELECT oid FROM pg_class WHERE relnamespace = (SELECT oid FROM pg_namespace WHERE nspname = current_schema()) AND relname = ".q($R));$f=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $sf AND attnum > 0",$i);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption , (indpred IS NOT NULL)::int as indispartial FROM pg_index i, pg_class ci WHERE i.indrelid = $sf AND ci.oid = i.indexrelid",$i)as$L){$Be=$L["relname"];$K[$Be]["type"]=($L["indispartial"]?"INDEX":($L["indisprimary"]?"PRIMARY":($L["indisunique"]?"UNIQUE":"INDEX")));$K[$Be]["columns"]=array();foreach(explode(" ",$L["indkey"])as$Mc)$K[$Be]["columns"][]=$f[$Mc];$K[$Be]["descs"]=array();foreach(explode(" ",$L["indoption"])as$Nc)$K[$Be]["descs"][]=($Nc&1?'1':null);$K[$Be]["lengths"]=array();}return$K;}function
foreign_keys($R){global$Nd;$K=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = (SELECT pc.oid FROM pg_class AS pc INNER JOIN pg_namespace AS pn ON (pn.oid = pc.relnamespace) WHERE pc.relname = ".q($R)." AND pn.nspname = current_schema())
AND contype = 'f'::char
ORDER BY conkey, conname")as$L){if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$L['definition'],$B)){$L['source']=array_map('trim',explode(',',$B[1]));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$B[2],$sd)){$L['ns']=str_replace('""','"',preg_replace('~^"(.+)"$~','\1',$sd[2]));$L['table']=str_replace('""','"',preg_replace('~^"(.+)"$~','\1',$sd[4]));}$L['target']=array_map('trim',explode(',',$B[3]));$L['on_delete']=(preg_match("~ON DELETE ($Nd)~",$B[4],$sd)?$sd[1]:'NO ACTION');$L['on_update']=(preg_match("~ON UPDATE ($Nd)~",$B[4],$sd)?$sd[1]:'NO ACTION');$K[$L['conname']]=$L;}}return$K;}function
view($D){global$h;return
array("select"=>trim($h->result("SELECT pg_get_viewdef(".q($D).")")));}function
collations(){return
array();}function
information_schema($m){return($m=="information_schema");}function
error(){global$h;$K=h($h->error);if(preg_match('~^(.*\\n)?([^\\n]*)\\n( *)\\^(\\n.*)?$~s',$K,$B))$K=$B[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($B[3]).'})(.*)~','\\1<b>\\2</b>',$B[2]).$B[4];return
nl_br($K);}function
create_database($m,$d){return
queries("CREATE DATABASE ".idf_escape($m).($d?" ENCODING ".idf_escape($d):""));}function
drop_databases($l){global$h;$h->close();return
apply_queries("DROP DATABASE",$l,'idf_escape');}function
rename_database($D,$d){return
queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($D));}function
auto_increment(){return"";}function
alter_table($R,$D,$p,$mc,$fb,$Nb,$d,$Ea,$ee){$c=array();$ue=array();foreach($p
as$o){$e=idf_escape($o[0]);$X=$o[1];if(!$X)$c[]="DROP $e";else{$eg=$X[5];unset($X[5]);if(isset($X[6])&&$o[0]=="")$X[1]=($X[1]=="bigint"?" big":" ")."serial";if($o[0]=="")$c[]=($R!=""?"ADD ":"  ").implode($X);else{if($e!=$X[0])$ue[]="ALTER TABLE ".table($R)." RENAME $e TO $X[0]";$c[]="ALTER $e TYPE$X[1]";if(!$X[6]){$c[]="ALTER $e ".($X[3]?"SET$X[3]":"DROP DEFAULT");$c[]="ALTER $e ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}}if($o[0]!=""||$eg!="")$ue[]="COMMENT ON COLUMN ".table($R).".$X[0] IS ".($eg!=""?substr($eg,9):"''");}}$c=array_merge($c,$mc);if($R=="")array_unshift($ue,"CREATE TABLE ".table($D)." (\n".implode(",\n",$c)."\n)");elseif($c)array_unshift($ue,"ALTER TABLE ".table($R)."\n".implode(",\n",$c));if($R!=""&&$R!=$D)$ue[]="ALTER TABLE ".table($R)." RENAME TO ".table($D);if($R!=""||$fb!="")$ue[]="COMMENT ON TABLE ".table($D)." IS ".q($fb);if($Ea!=""){}foreach($ue
as$I){if(!queries($I))return
false;}return
true;}function
alter_indexes($R,$c){$mb=array();$Cb=array();$ue=array();foreach($c
as$X){if($X[0]!="INDEX")$mb[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$Cb[]=idf_escape($X[1]);else$ue[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($mb)array_unshift($ue,"ALTER TABLE ".table($R).implode(",",$mb));if($Cb)array_unshift($ue,"DROP INDEX ".implode(", ",$Cb));foreach($ue
as$I){if(!queries($I))return
false;}return
true;}function
truncate_tables($T){return
queries("TRUNCATE ".implode(", ",array_map('table',$T)));return
true;}function
drop_views($jg){return
drop_tables($jg);}function
drop_tables($T){foreach($T
as$R){$if=table_status($R);if(!queries("DROP ".strtoupper($if["Engine"])." ".table($R)))return
false;}return
true;}function
move_tables($T,$jg,$uf){foreach(array_merge($T,$jg)as$R){$if=table_status($R);if(!queries("ALTER ".strtoupper($if["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($uf)))return
false;}return
true;}function
trigger($D,$R=null){if($D=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");if($R===null)$R=$_GET['trigger'];$M=get_rows('SELECT t.trigger_name AS "Trigger", t.action_timing AS "Timing", (SELECT STRING_AGG(event_manipulation, \' OR \') FROM information_schema.triggers WHERE event_object_table = t.event_object_table AND trigger_name = t.trigger_name ) AS "Events", t.event_manipulation AS "Event", \'FOR EACH \' || t.action_orientation AS "Type", t.action_statement AS "Statement" FROM information_schema.triggers t WHERE t.event_object_table = '.q($R).' AND t.trigger_name = '.q($D));return
reset($M);}function
triggers($R){$K=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE event_object_table = ".q($R))as$L)$K[$L["trigger_name"]]=array($L["action_timing"],$L["event_manipulation"]);return$K;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routines(){return
get_rows('SELECT p.proname AS "ROUTINE_NAME", p.proargtypes AS "ROUTINE_TYPE", pg_catalog.format_type(p.prorettype, NULL) AS "DTD_IDENTIFIER"
FROM pg_catalog.pg_namespace n
JOIN pg_catalog.pg_proc p ON p.pronamespace = n.oid
WHERE n.nspname = current_schema()
ORDER BY p.proname');}function
routine_languages(){return
get_vals("SELECT langname FROM pg_catalog.pg_language");}function
last_id(){return
0;}function
explain($h,$I){return$h->query("EXPLAIN $I");}function
found_rows($S,$Z){global$h;if(preg_match("~ rows=([0-9]+)~",$h->result("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$Ae))return$Ae[1];return
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
set_schema($Me){global$h,$Rf,$kf;$K=$h->query("SET search_path TO ".idf_escape($Me));foreach(types()as$U){if(!isset($Rf[$U])){$Rf[$U]=0;$kf[lang(24)][]=$U;}}return$K;}function
create_sql($R,$Ea){global$h;$K='';$Ie=array();$Ve=array();$if=table_status($R);$p=fields($R);$x=indexes($R);ksort($x);$kc=foreign_keys($R);ksort($kc);$Pf=triggers($R);if(!$if||empty($p))return
false;$K="CREATE TABLE ".idf_escape($if['nspname']).".".idf_escape($if['Name'])." (\n    ";foreach($p
as$cc=>$o){$de=idf_escape($o['field']).' '.$o['full_type'].(is_null($o['default'])?"":" DEFAULT $o[default]").($o['attnotnull']?" NOT NULL":"");$Ie[]=$de;if(preg_match('~nextval\(\'([^\']+)\'\)~',$o['default'],$C)){$Ue=$C[1];$ef=reset(get_rows("SELECT * FROM $Ue"));$Ve[]="CREATE SEQUENCE $Ue INCREMENT $ef[increment_by] MINVALUE $ef[min_value] MAXVALUE $ef[max_value] START ".($Ea?$ef['last_value']:1)." CACHE $ef[cache_value];";}}if(!empty($Ve))$K=implode("\n\n",$Ve)."\n\n$K";foreach($x
as$Kc=>$w){switch($w['type']){case'UNIQUE':$Ie[]="CONSTRAINT ".idf_escape($Kc)." UNIQUE (".implode(', ',array_map('idf_escape',$w['columns'])).")";break;case'PRIMARY':$Ie[]="CONSTRAINT ".idf_escape($Kc)." PRIMARY KEY (".implode(', ',array_map('idf_escape',$w['columns'])).")";break;}}foreach($kc
as$jc=>$ic)$Ie[]="CONSTRAINT ".idf_escape($jc)." $ic[definition] ".($ic['deferrable']?'DEFERRABLE':'NOT DEFERRABLE');$K.=implode(",\n    ",$Ie)."\n) WITH (oids = ".($if['Oid']?'true':'false').");";foreach($x
as$Kc=>$w){if($w['type']=='INDEX')$K.="\n\nCREATE INDEX ".idf_escape($Kc)." ON ".idf_escape($if['nspname']).".".idf_escape($if['Name'])." USING btree (".implode(', ',array_map('idf_escape',$w['columns'])).");";}if($if['Comment'])$K.="\n\nCOMMENT ON TABLE ".idf_escape($if['nspname']).".".idf_escape($if['Name'])." IS ".q($if['Comment']).";";foreach($p
as$cc=>$o){if($o['comment'])$K.="\n\nCOMMENT ON COLUMN ".idf_escape($if['nspname']).".".idf_escape($if['Name']).".".idf_escape($cc)." IS ".q($o['comment']).";";}foreach($Pf
as$Lf=>$Kf){$Mf=trigger($Lf,$if['Name']);$K.="\n\nCREATE TRIGGER ".idf_escape($Mf['Trigger'])." $Mf[Timing] $Mf[Events] ON ".idf_escape($if["nspname"]).".".idf_escape($if['Name'])." $Mf[Type] $Mf[Statement];";}return
rtrim($K,';');}function
trigger_sql($R,$lf){$K="";return
false;}function
use_sql($k){return"\connect ".idf_escape($k);}function
show_variables(){return
get_key_vals("SHOW ALL");}function
process_list(){global$h;return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".($h->server_info<9.2?"procpid":"pid"));}function
show_status(){}function
convert_field($o){}function
unconvert_field($o,$K){return$K;}function
support($bc){global$h;return
preg_match('~^(database|table|columns|sql|indexes|comment|view|'.($h->server_info>=9.3?'materializedview|':'').'scheme|processlist|sequence|trigger|type|variables|drop_col|kill|dump)$~',$bc);}function
kill_process($X){return
queries("SELECT pg_terminate_backend(".number($X).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){global$h;return$h->result("SHOW max_connections");}$y="pgsql";$Rf=array();$kf=array();foreach(array(lang(25)=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),lang(26)=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),lang(23)=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),lang(27)=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),lang(28)=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"txid_snapshot"=>0),lang(29)=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),)as$z=>$X){$Rf+=$X;$kf[$z]=array_keys($X);}$Yf=array();$Sd=array("=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");$vc=array("char_length","lower","round","to_hex","to_timestamp","upper");$xc=array("avg","count","count distinct","max","min","sum");$Gb=array(array("char"=>"md5","date|time"=>"now",),array("int|numeric|real|money"=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",));}$Bb["oracle"]="Oracle";if(isset($_GET["oracle"])){$le=array("OCI8","PDO_OCI");define("DRIVER","oracle");if(extension_loaded("oci8")){class
Min_DB{var$extension="oci8",$_link,$_result,$server_info,$affected_rows,$errno,$error;function
_error($Qb,$n){if(ini_bool("html_errors"))$n=html_entity_decode(strip_tags($n));$n=preg_replace('~^[^:]*: ~','',$n);$this->error=$n;}function
connect($O,$V,$H){$this->_link=@oci_new_connect($V,$H,$O,"AL32UTF8");if($this->_link){$this->server_info=oci_server_version($this->_link);return
true;}$n=oci_error();$this->error=$n["message"];return
false;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($k){return
true;}function
query($I,$Sf=false){$J=oci_parse($this->_link,$I);$this->error="";if(!$J){$n=oci_error($this->_link);$this->errno=$n["code"];$this->error=$n["message"];return
false;}set_error_handler(array($this,'_error'));$K=@oci_execute($J);restore_error_handler();if($K){if(oci_num_fields($J))return
new
Min_Result($J);$this->affected_rows=oci_num_rows($J);}return$K;}function
multi_query($I){return$this->_result=$this->query($I);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($I,$o=1){$J=$this->query($I);if(!is_object($J)||!oci_fetch($J->_result))return
false;return
oci_result($J->_result,$o);}}class
Min_Result{var$_result,$_offset=1,$num_rows;function
__construct($J){$this->_result=$J;}function
_convert($L){foreach((array)$L
as$z=>$X){if(is_a($X,'OCI-Lob'))$L[$z]=$X->load();}return$L;}function
fetch_assoc(){return$this->_convert(oci_fetch_assoc($this->_result));}function
fetch_row(){return$this->_convert(oci_fetch_row($this->_result));}function
fetch_field(){$e=$this->_offset++;$K=new
stdClass;$K->name=oci_field_name($this->_result,$e);$K->orgname=$K->name;$K->type=oci_field_type($this->_result,$e);$K->charsetnr=(preg_match("~raw|blob|bfile~",$K->type)?63:0);return$K;}function
__destruct(){oci_free_statement($this->_result);}}}elseif(extension_loaded("pdo_oci")){class
Min_DB
extends
Min_PDO{var$extension="PDO_OCI";function
connect($O,$V,$H){$this->dsn("oci:dbname=//$O;charset=AL32UTF8",$V,$H);return
true;}function
select_db($k){return
true;}}}class
Min_Driver
extends
Min_SQL{function
begin(){return
true;}}function
idf_escape($v){return'"'.str_replace('"','""',$v).'"';}function
table($v){return
idf_escape($v);}function
connect(){global$b;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2]))return$h;return$h->error;}function
get_databases(){return
get_vals("SELECT tablespace_name FROM user_tablespaces");}function
limit($I,$Z,$_,$Ld=0,$Te=" "){return($Ld?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $I$Z) t WHERE rownum <= ".($_+$Ld).") WHERE rnum > $Ld":($_!==null?" * FROM (SELECT $I$Z) WHERE rownum <= ".($_+$Ld):" $I$Z"));}function
limit1($I,$Z){return" $I$Z";}function
db_collation($m,$cb){global$h;return$h->result("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
engines(){return
array();}function
logged_user(){global$h;return$h->result("SELECT USER FROM DUAL");}function
tables_list(){return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."
UNION SELECT view_name, 'view' FROM user_views
ORDER BY 1");}function
count_tables($l){return
array();}function
table_status($D=""){$K=array();$Oe=q($D);foreach(get_rows('SELECT table_name "Name", \'table\' "Engine", avg_row_len * num_rows "Data_length", num_rows "Rows" FROM all_tables WHERE tablespace_name = '.q(DB).($D!=""?" AND table_name = $Oe":"")."
UNION SELECT view_name, 'view', 0, 0 FROM user_views".($D!=""?" WHERE view_name = $Oe":"")."
ORDER BY 1")as$L){if($D!="")return$L;$K[$L["Name"]]=$L;}return$K;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return
true;}function
fields($R){$K=array();foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)." ORDER BY column_id")as$L){$U=$L["DATA_TYPE"];$ld="$L[DATA_PRECISION],$L[DATA_SCALE]";if($ld==",")$ld=$L["DATA_LENGTH"];$K[$L["COLUMN_NAME"]]=array("field"=>$L["COLUMN_NAME"],"full_type"=>$U.($ld?"($ld)":""),"type"=>strtolower($U),"length"=>$ld,"default"=>$L["DATA_DEFAULT"],"null"=>($L["NULLABLE"]=="Y"),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),);}return$K;}function
indexes($R,$i=null){$K=array();foreach(get_rows("SELECT uic.*, uc.constraint_type
FROM user_ind_columns uic
LEFT JOIN user_constraints uc ON uic.index_name = uc.constraint_name AND uic.table_name = uc.table_name
WHERE uic.table_name = ".q($R)."
ORDER BY uc.constraint_type, uic.column_position",$i)as$L){$Kc=$L["INDEX_NAME"];$K[$Kc]["type"]=($L["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($L["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$K[$Kc]["columns"][]=$L["COLUMN_NAME"];$K[$Kc]["lengths"][]=($L["CHAR_LENGTH"]&&$L["CHAR_LENGTH"]!=$L["COLUMN_LENGTH"]?$L["CHAR_LENGTH"]:null);$K[$Kc]["descs"][]=($L["DESCEND"]?'1':null);}return$K;}function
view($D){$M=get_rows('SELECT text "select" FROM user_views WHERE view_name = '.q($D));return
reset($M);}function
collations(){return
array();}function
information_schema($m){return
false;}function
error(){global$h;return
h($h->error);}function
explain($h,$I){$h->query("EXPLAIN PLAN FOR $I");return$h->query("SELECT * FROM plan_table");}function
found_rows($S,$Z){}function
alter_table($R,$D,$p,$mc,$fb,$Nb,$d,$Ea,$ee){$c=$Cb=array();foreach($p
as$o){$X=$o[1];if($X&&$o[0]!=""&&idf_escape($o[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($o[0])." TO $X[0]");if($X)$c[]=($R!=""?($o[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");else$Cb[]=idf_escape($o[0]);}if($R=="")return
queries("CREATE TABLE ".table($D)." (\n".implode(",\n",$c)."\n)");return(!$c||queries("ALTER TABLE ".table($R)."\n".implode("\n",$c)))&&(!$Cb||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$Cb).")"))&&($R==$D||queries("ALTER TABLE ".table($R)." RENAME TO ".table($D)));}function
foreign_keys($R){$K=array();$I="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($R);foreach(get_rows($I)as$L)$K[$L['NAME']]=array("db"=>$L['DEST_DB'],"table"=>$L['DEST_TABLE'],"source"=>array($L['SRC_COLUMN']),"target"=>array($L['DEST_COLUMN']),"on_delete"=>$L['ON_DELETE'],"on_update"=>null,);return$K;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($jg){return
apply_queries("DROP VIEW",$jg);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
last_id(){return
0;}function
schemas(){return
get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX'))");}function
get_schema(){global$h;return$h->result("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($Ne){global$h;return$h->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($Ne));}function
show_variables(){return
get_key_vals('SELECT name, display_value FROM v$parameter');}function
process_list(){return
get_rows('SELECT sess.process AS "process", sess.username AS "user", sess.schemaname AS "schema", sess.status AS "status", sess.wait_class AS "wait_class", sess.seconds_in_wait AS "seconds_in_wait", sql.sql_text AS "sql_text", sess.machine AS "machine", sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
show_status(){$M=get_rows('SELECT * FROM v$instance');return
reset($M);}function
convert_field($o){}function
unconvert_field($o,$K){return$K;}function
support($bc){return
preg_match('~^(columns|database|drop_col|indexes|processlist|scheme|sql|status|table|variables|view|view_trigger)$~',$bc);}$y="oracle";$Rf=array();$kf=array();foreach(array(lang(25)=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),lang(26)=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),lang(23)=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),lang(27)=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),)as$z=>$X){$Rf+=$X;$kf[$z]=array_keys($X);}$Yf=array();$Sd=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");$vc=array("length","lower","round","upper");$xc=array("avg","count","count distinct","max","min","sum");$Gb=array(array("date"=>"current_date","timestamp"=>"current_timestamp",),array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",));}$Bb["mssql"]="MS SQL";if(isset($_GET["mssql"])){$le=array("SQLSRV","MSSQL","PDO_DBLIB");define("DRIVER","mssql");if(extension_loaded("sqlsrv")){class
Min_DB{var$extension="sqlsrv",$_link,$_result,$server_info,$affected_rows,$errno,$error;function
_get_error(){$this->error="";foreach(sqlsrv_errors()as$n){$this->errno=$n["code"];$this->error.="$n[message]\n";}$this->error=rtrim($this->error);}function
connect($O,$V,$H){$this->_link=@sqlsrv_connect($O,array("UID"=>$V,"PWD"=>$H,"CharacterSet"=>"UTF-8"));if($this->_link){$Oc=sqlsrv_server_info($this->_link);$this->server_info=$Oc['SQLServerVersion'];}else$this->_get_error();return(bool)$this->_link;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($k){return$this->query("USE ".idf_escape($k));}function
query($I,$Sf=false){$J=sqlsrv_query($this->_link,$I);$this->error="";if(!$J){$this->_get_error();return
false;}return$this->store_result($J);}function
multi_query($I){$this->_result=sqlsrv_query($this->_link,$I);$this->error="";if(!$this->_result){$this->_get_error();return
false;}return
true;}function
store_result($J=null){if(!$J)$J=$this->_result;if(!$J)return
false;if(sqlsrv_field_metadata($J))return
new
Min_Result($J);$this->affected_rows=sqlsrv_rows_affected($J);return
true;}function
next_result(){return$this->_result?sqlsrv_next_result($this->_result):null;}function
result($I,$o=0){$J=$this->query($I);if(!is_object($J))return
false;$L=$J->fetch_row();return$L[$o];}}class
Min_Result{var$_result,$_offset=0,$_fields,$num_rows;function
__construct($J){$this->_result=$J;}function
_convert($L){foreach((array)$L
as$z=>$X){if(is_a($X,'DateTime'))$L[$z]=$X->format("Y-m-d H:i:s");}return$L;}function
fetch_assoc(){return$this->_convert(sqlsrv_fetch_array($this->_result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->_convert(sqlsrv_fetch_array($this->_result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->_fields)$this->_fields=sqlsrv_field_metadata($this->_result);$o=$this->_fields[$this->_offset++];$K=new
stdClass;$K->name=$o["Name"];$K->orgname=$o["Name"];$K->type=($o["Type"]==1?254:0);return$K;}function
seek($Ld){for($t=0;$t<$Ld;$t++)sqlsrv_fetch($this->_result);}function
__destruct(){sqlsrv_free_stmt($this->_result);}}}elseif(extension_loaded("mssql")){class
Min_DB{var$extension="MSSQL",$_link,$_result,$server_info,$affected_rows,$error;function
connect($O,$V,$H){$this->_link=@mssql_connect($O,$V,$H);if($this->_link){$J=$this->query("SELECT SERVERPROPERTY('ProductLevel'), SERVERPROPERTY('Edition')");$L=$J->fetch_row();$this->server_info=$this->result("sp_server_info 2",2)." [$L[0]] $L[1]";}else$this->error=mssql_get_last_message();return(bool)$this->_link;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($k){return
mssql_select_db($k);}function
query($I,$Sf=false){$J=@mssql_query($I,$this->_link);$this->error="";if(!$J){$this->error=mssql_get_last_message();return
false;}if($J===true){$this->affected_rows=mssql_rows_affected($this->_link);return
true;}return
new
Min_Result($J);}function
multi_query($I){return$this->_result=$this->query($I);}function
store_result(){return$this->_result;}function
next_result(){return
mssql_next_result($this->_result->_result);}function
result($I,$o=0){$J=$this->query($I);if(!is_object($J))return
false;return
mssql_result($J->_result,0,$o);}}class
Min_Result{var$_result,$_offset=0,$_fields,$num_rows;function
__construct($J){$this->_result=$J;$this->num_rows=mssql_num_rows($J);}function
fetch_assoc(){return
mssql_fetch_assoc($this->_result);}function
fetch_row(){return
mssql_fetch_row($this->_result);}function
num_rows(){return
mssql_num_rows($this->_result);}function
fetch_field(){$K=mssql_fetch_field($this->_result);$K->orgtable=$K->table;$K->orgname=$K->name;return$K;}function
seek($Ld){mssql_data_seek($this->_result,$Ld);}function
__destruct(){mssql_free_result($this->_result);}}}elseif(extension_loaded("pdo_dblib")){class
Min_DB
extends
Min_PDO{var$extension="PDO_DBLIB";function
connect($O,$V,$H){$this->dsn("dblib:charset=utf8;host=".str_replace(":",";unix_socket=",preg_replace('~:(\\d)~',';port=\\1',$O)),$V,$H);return
true;}function
select_db($k){return$this->query("USE ".idf_escape($k));}}}class
Min_Driver
extends
Min_SQL{function
insertUpdate($R,$M,$ne){foreach($M
as$P){$Zf=array();$Z=array();foreach($P
as$z=>$X){$Zf[]="$z = $X";if(isset($ne[idf_unescape($z)]))$Z[]="$z = $X";}if(!queries("MERGE ".table($R)." USING (VALUES(".implode(", ",$P).")) AS source (c".implode(", c",range(1,count($P))).") ON ".implode(" AND ",$Z)." WHEN MATCHED THEN UPDATE SET ".implode(", ",$Zf)." WHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($P)).") VALUES (".implode(", ",$P).");"))return
false;}return
true;}function
begin(){return
queries("BEGIN TRANSACTION");}}function
idf_escape($v){return"[".str_replace("]","]]",$v)."]";}function
table($v){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($v);}function
connect(){global$b;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2]))return$h;return$h->error;}function
get_databases(){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($I,$Z,$_,$Ld=0,$Te=" "){return($_!==null?" TOP (".($_+$Ld).")":"")." $I$Z";}function
limit1($I,$Z){return
limit($I,$Z,1);}function
db_collation($m,$cb){global$h;return$h->result("SELECT collation_name FROM sys.databases WHERE name =  ".q($m));}function
engines(){return
array();}function
logged_user(){global$h;return$h->result("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables($l){global$h;$K=array();foreach($l
as$m){$h->select_db($m);$K[$m]=$h->result("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$K;}function
table_status($D=""){$K=array();foreach(get_rows("SELECT name AS Name, type_desc AS Engine FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($D!=""?"AND name = ".q($D):"ORDER BY name"))as$L){if($D!="")return$L;$K[$L["Name"]]=$L;}return$K;}function
is_view($S){return$S["Engine"]=="VIEW";}function
fk_support($S){return
true;}function
fields($R){$K=array();foreach(get_rows("SELECT c.*, t.name type, d.definition [default]
FROM sys.all_columns c
JOIN sys.all_objects o ON c.object_id = o.object_id
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.parent_column_id
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.type IN ('S', 'U', 'V') AND o.name = ".q($R))as$L){$U=$L["type"];$ld=(preg_match("~char|binary~",$U)?$L["max_length"]:($U=="decimal"?"$L[precision],$L[scale]":""));$K[$L["name"]]=array("field"=>$L["name"],"full_type"=>$U.($ld?"($ld)":""),"type"=>$U,"length"=>$ld,"default"=>$L["default"],"null"=>$L["is_nullable"],"auto_increment"=>$L["is_identity"],"collation"=>$L["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),"primary"=>$L["is_identity"],);}return$K;}function
indexes($R,$i=null){$K=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$i)as$L){$D=$L["name"];$K[$D]["type"]=($L["is_primary_key"]?"PRIMARY":($L["is_unique"]?"UNIQUE":"INDEX"));$K[$D]["lengths"]=array();$K[$D]["columns"][$L["key_ordinal"]]=$L["column_name"];$K[$D]["descs"][$L["key_ordinal"]]=($L["is_descending_key"]?'1':null);}return$K;}function
view($D){global$h;return
array("select"=>preg_replace('~^(?:[^[]|\\[[^]]*])*\\s+AS\\s+~isU','',$h->result("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($D))));}function
collations(){$K=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$d)$K[preg_replace('~_.*~','',$d)][]=$d;return$K;}function
information_schema($m){return
false;}function
error(){global$h;return
nl_br(h(preg_replace('~^(\\[[^]]*])+~m','',$h->error)));}function
create_database($m,$d){return
queries("CREATE DATABASE ".idf_escape($m).(preg_match('~^[a-z0-9_]+$~i',$d)?" COLLATE $d":""));}function
drop_databases($l){return
queries("DROP DATABASE ".implode(", ",array_map('idf_escape',$l)));}function
rename_database($D,$d){if(preg_match('~^[a-z0-9_]+$~i',$d))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $d");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($D));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$D,$p,$mc,$fb,$Nb,$d,$Ea,$ee){$c=array();foreach($p
as$o){$e=idf_escape($o[0]);$X=$o[1];if(!$X)$c["DROP"][]=" COLUMN $e";else{$X[1]=preg_replace("~( COLLATE )'(\\w+)'~","\\1\\2",$X[1]);if($o[0]=="")$c["ADD"][]="\n  ".implode("",$X).($R==""?substr($mc[$X[0]],16+strlen($X[0])):"");else{unset($X[6]);if($e!=$X[0])queries("EXEC sp_rename ".q(table($R).".$e").", ".q(idf_unescape($X[0])).", 'COLUMN'");$c["ALTER COLUMN ".implode("",$X)][]="";}}}if($R=="")return
queries("CREATE TABLE ".table($D)." (".implode(",",(array)$c["ADD"])."\n)");if($R!=$D)queries("EXEC sp_rename ".q(table($R)).", ".q($D));if($mc)$c[""]=$mc;foreach($c
as$z=>$X){if(!queries("ALTER TABLE ".idf_escape($D)." $z".implode(",",$X)))return
false;}return
true;}function
alter_indexes($R,$c){$w=array();$Cb=array();foreach($c
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY")$Cb[]=idf_escape($X[1]);else$w[]=idf_escape($X[1])." ON ".table($R);}elseif(!queries(($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")"))return
false;}return(!$w||queries("DROP INDEX ".implode(", ",$w)))&&(!$Cb||queries("ALTER TABLE ".table($R)." DROP ".implode(", ",$Cb)));}function
last_id(){global$h;return$h->result("SELECT SCOPE_IDENTITY()");}function
explain($h,$I){$h->query("SET SHOWPLAN_ALL ON");$K=$h->query($I);$h->query("SET SHOWPLAN_ALL OFF");return$K;}function
found_rows($S,$Z){}function
foreign_keys($R){$K=array();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R))as$L){$q=&$K[$L["FK_NAME"]];$q["table"]=$L["PKTABLE_NAME"];$q["source"][]=$L["FKCOLUMN_NAME"];$q["target"][]=$L["PKCOLUMN_NAME"];}return$K;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($jg){return
queries("DROP VIEW ".implode(", ",array_map('table',$jg)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('table',$T)));}function
move_tables($T,$jg,$uf){return
apply_queries("ALTER SCHEMA ".idf_escape($uf)." TRANSFER",array_merge($T,$jg));}function
trigger($D){if($D=="")return
array();$M=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($D));$K=reset($M);if($K)$K["Statement"]=preg_replace('~^.+\\s+AS\\s+~isU','',$K["text"]);return$K;}function
triggers($R){$K=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($R))as$L)$K[$L["name"]]=array($L["Timing"],$L["Event"]);return$K;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){global$h;if($_GET["ns"]!="")return$_GET["ns"];return$h->result("SELECT SCHEMA_NAME()");}function
set_schema($Me){return
true;}function
use_sql($k){return"USE ".idf_escape($k);}function
show_variables(){return
array();}function
show_status(){return
array();}function
convert_field($o){}function
unconvert_field($o,$K){return$K;}function
support($bc){return
preg_match('~^(columns|database|drop_col|indexes|scheme|sql|table|trigger|view|view_trigger)$~',$bc);}$y="mssql";$Rf=array();$kf=array();foreach(array(lang(25)=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20),lang(26)=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10),lang(23)=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823),lang(27)=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),)as$z=>$X){$Rf+=$X;$kf[$z]=array_keys($X);}$Yf=array();$Sd=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");$vc=array("len","lower","round","upper");$xc=array("avg","count","count distinct","max","min","sum");$Gb=array(array("date|time"=>"getdate",),array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",));}$Bb['firebird']='Firebird (alpha)';if(isset($_GET["firebird"])){$le=array("interbase");define("DRIVER","firebird");if(extension_loaded("interbase")){class
Min_DB{var$extension="Firebird",$server_info,$affected_rows,$errno,$error,$_link,$_result;function
connect($O,$V,$H){$this->_link=ibase_connect($O,$V,$H);if($this->_link){$bg=explode(':',$O);$this->service_link=ibase_service_attach($bg[0],$V,$H);$this->server_info=ibase_server_info($this->service_link,IBASE_SVC_SERVER_VERSION);}else{$this->errno=ibase_errcode();$this->error=ibase_errmsg();}return(bool)$this->_link;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($k){return($k=="domain");}function
query($I,$Sf=false){$J=ibase_query($I,$this->_link);if(!$J){$this->errno=ibase_errcode();$this->error=ibase_errmsg();return
false;}$this->error="";if($J===true){$this->affected_rows=ibase_affected_rows($this->_link);return
true;}return
new
Min_Result($J);}function
multi_query($I){return$this->_result=$this->query($I);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($I,$o=0){$J=$this->query($I);if(!$J||!$J->num_rows)return
false;$L=$J->fetch_row();return$L[$o];}}class
Min_Result{var$num_rows,$_result,$_offset=0;function
__construct($J){$this->_result=$J;}function
fetch_assoc(){return
ibase_fetch_assoc($this->_result);}function
fetch_row(){return
ibase_fetch_row($this->_result);}function
fetch_field(){$o=ibase_field_info($this->_result,$this->_offset++);return(object)array('name'=>$o['name'],'orgname'=>$o['name'],'type'=>$o['type'],'charsetnr'=>$o['length'],);}function
__destruct(){ibase_free_result($this->_result);}}}class
Min_Driver
extends
Min_SQL{}function
idf_escape($v){return'"'.str_replace('"','""',$v).'"';}function
table($v){return
idf_escape($v);}function
connect(){global$b;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2]))return$h;return$h->error;}function
get_databases($lc){return
array("domain");}function
limit($I,$Z,$_,$Ld=0,$Te=" "){$K='';$K.=($_!==null?$Te."FIRST $_".($Ld?" SKIP $Ld":""):"");$K.=" $I$Z";return$K;}function
limit1($I,$Z){return
limit($I,$Z,1);}function
db_collation($m,$cb){}function
engines(){return
array();}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
tables_list(){global$h;$I='SELECT RDB$RELATION_NAME FROM rdb$relations WHERE rdb$system_flag = 0';$J=ibase_query($h->_link,$I);$K=array();while($L=ibase_fetch_assoc($J))$K[$L['RDB$RELATION_NAME']]='table';ksort($K);return$K;}function
count_tables($l){return
array();}function
table_status($D="",$ac=false){global$h;$K=array();$pb=tables_list();foreach($pb
as$w=>$X){$w=trim($w);$K[$w]=array('Name'=>$w,'Engine'=>'standard',);if($D==$w)return$K[$w];}return$K;}function
is_view($S){return
false;}function
fk_support($S){return
preg_match('~InnoDB|IBMDB2I~i',$S["Engine"]);}function
fields($R){global$h;$K=array();$I='SELECT r.RDB$FIELD_NAME AS field_name,
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
ORDER BY r.RDB$FIELD_POSITION';$J=ibase_query($h->_link,$I);while($L=ibase_fetch_assoc($J))$K[trim($L['FIELD_NAME'])]=array("field"=>trim($L["FIELD_NAME"]),"full_type"=>trim($L["FIELD_TYPE"]),"type"=>trim($L["FIELD_SUB_TYPE"]),"default"=>trim($L['FIELD_DEFAULT_VALUE']),"null"=>(trim($L["FIELD_NOT_NULL_CONSTRAINT"])=="YES"),"auto_increment"=>'0',"collation"=>trim($L["FIELD_COLLATION"]),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),"comment"=>trim($L["FIELD_DESCRIPTION"]),);return$K;}function
indexes($R,$i=null){$K=array();return$K;}function
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
set_schema($Me){return
true;}function
support($bc){return
preg_match("~^(columns|sql|status|table)$~",$bc);}$y="firebird";$Sd=array("=");$vc=array();$xc=array();$Gb=array();}$Bb["simpledb"]="SimpleDB";if(isset($_GET["simpledb"])){$le=array("SimpleXML");define("DRIVER","simpledb");if(class_exists('SimpleXMLElement')){class
Min_DB{var$extension="SimpleXML",$server_info='2009-04-15',$error,$timeout,$next,$affected_rows,$_result;function
select_db($k){return($k=="domain");}function
query($I,$Sf=false){$G=array('SelectExpression'=>$I,'ConsistentRead'=>'true');if($this->next)$G['NextToken']=$this->next;$J=sdb_request_all('Select','Item',$G,$this->timeout);if($J===false)return$J;if(preg_match('~^\s*SELECT\s+COUNT\(~i',$I)){$of=0;foreach($J
as$Xc)$of+=$Xc->Attribute->Value;$J=array((object)array('Attribute'=>array((object)array('Name'=>'Count','Value'=>$of,))));}return
new
Min_Result($J);}function
multi_query($I){return$this->_result=$this->query($I);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}}class
Min_Result{var$num_rows,$_rows=array(),$_offset=0;function
__construct($J){foreach($J
as$Xc){$L=array();if($Xc->Name!='')$L['itemName()']=(string)$Xc->Name;foreach($Xc->Attribute
as$Ba){$D=$this->_processValue($Ba->Name);$Y=$this->_processValue($Ba->Value);if(isset($L[$D])){$L[$D]=(array)$L[$D];$L[$D][]=$Y;}else$L[$D]=$Y;}$this->_rows[]=$L;foreach($L
as$z=>$X){if(!isset($this->_rows[0][$z]))$this->_rows[0][$z]=null;}}$this->num_rows=count($this->_rows);}function
_processValue($Ib){return(is_object($Ib)&&$Ib['encoding']=='base64'?base64_decode($Ib):(string)$Ib);}function
fetch_assoc(){$L=current($this->_rows);if(!$L)return$L;$K=array();foreach($this->_rows[0]as$z=>$X)$K[$z]=$L[$z];next($this->_rows);return$K;}function
fetch_row(){$K=$this->fetch_assoc();if(!$K)return$K;return
array_values($K);}function
fetch_field(){$cd=array_keys($this->_rows[0]);return(object)array('name'=>$cd[$this->_offset++]);}}}class
Min_Driver
extends
Min_SQL{public$ne="itemName()";function
_chunkRequest($Ic,$qa,$G,$Vb=array()){global$h;foreach(array_chunk($Ic,25)as$Wa){$ce=$G;foreach($Wa
as$t=>$u){$ce["Item.$t.ItemName"]=$u;foreach($Vb
as$z=>$X)$ce["Item.$t.$z"]=$X;}if(!sdb_request($qa,$ce))return
false;}$h->affected_rows=count($Ic);return
true;}function
_extractIds($R,$ve,$_){$K=array();if(preg_match_all("~itemName\(\) = (('[^']*+')+)~",$ve,$C))$K=array_map('idf_unescape',$C[1]);else{foreach(sdb_request_all('Select','Item',array('SelectExpression'=>'SELECT itemName() FROM '.table($R).$ve.($_?" LIMIT 1":"")))as$Xc)$K[]=$Xc->Name;}return$K;}function
select($R,$N,$Z,$s,$E=array(),$_=1,$F=0,$pe=false){global$h;$h->next=$_GET["next"];$K=parent::select($R,$N,$Z,$s,$E,$_,$F,$pe);$h->next=0;return$K;}function
delete($R,$ve,$_=0){return$this->_chunkRequest($this->_extractIds($R,$ve,$_),'BatchDeleteAttributes',array('DomainName'=>$R));}function
update($R,$P,$ve,$_=0,$Te="\n"){$tb=array();$Sc=array();$t=0;$Ic=$this->_extractIds($R,$ve,$_);$u=idf_unescape($P["`itemName()`"]);unset($P["`itemName()`"]);foreach($P
as$z=>$X){$z=idf_unescape($z);if($X=="NULL"||($u!=""&&array($u)!=$Ic))$tb["Attribute.".count($tb).".Name"]=$z;if($X!="NULL"){foreach((array)$X
as$Yc=>$W){$Sc["Attribute.$t.Name"]=$z;$Sc["Attribute.$t.Value"]=(is_array($X)?$W:idf_unescape($W));if(!$Yc)$Sc["Attribute.$t.Replace"]="true";$t++;}}}$G=array('DomainName'=>$R);return(!$Sc||$this->_chunkRequest(($u!=""?array($u):$Ic),'BatchPutAttributes',$G,$Sc))&&(!$tb||$this->_chunkRequest($Ic,'BatchDeleteAttributes',$G,$tb));}function
insert($R,$P){$G=array("DomainName"=>$R);$t=0;foreach($P
as$D=>$Y){if($Y!="NULL"){$D=idf_unescape($D);if($D=="itemName()")$G["ItemName"]=idf_unescape($Y);else{foreach((array)$Y
as$X){$G["Attribute.$t.Name"]=$D;$G["Attribute.$t.Value"]=(is_array($Y)?$X:idf_unescape($Y));$t++;}}}}return
sdb_request('PutAttributes',$G);}function
insertUpdate($R,$M,$ne){foreach($M
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
support($bc){return
preg_match('~sql~',$bc);}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
get_databases(){return
array("domain");}function
collations(){return
array();}function
db_collation($m,$cb){}function
tables_list(){global$h;$K=array();foreach(sdb_request_all('ListDomains','DomainName')as$R)$K[(string)$R]='table';if($h->error&&defined("PAGE_HEADER"))echo"<p class='error'>".error()."\n";return$K;}function
table_status($D="",$ac=false){$K=array();foreach(($D!=""?array($D=>true):tables_list())as$R=>$U){$L=array("Name"=>$R,"Auto_increment"=>"");if(!$ac){$_d=sdb_request('DomainMetadata',array('DomainName'=>$R));if($_d){foreach(array("Rows"=>"ItemCount","Data_length"=>"ItemNamesSizeBytes","Index_length"=>"AttributeValuesSizeBytes","Data_free"=>"AttributeNamesSizeBytes",)as$z=>$X)$L[$z]=(string)$_d->$X;}}if($D!="")return$L;$K[$R]=$L;}return$K;}function
explain($h,$I){}function
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
table($v){return
idf_escape($v);}function
idf_escape($v){return"`".str_replace("`","``",$v)."`";}function
limit($I,$Z,$_,$Ld=0,$Te=" "){return" $I$Z".($_!==null?$Te."LIMIT $_":"");}function
unconvert_field($o,$K){return$K;}function
fk_support($S){}function
engines(){return
array();}function
alter_table($R,$D,$p,$mc,$fb,$Nb,$d,$Ea,$ee){return($R==""&&sdb_request('CreateDomain',array('DomainName'=>$D)));}function
drop_tables($T){foreach($T
as$R){if(!sdb_request('DeleteDomain',array('DomainName'=>$R)))return
false;}return
true;}function
count_tables($l){foreach($l
as$m)return
array($m=>count(tables_list()));}function
found_rows($S,$Z){return($Z?null:$S["Rows"]);}function
last_id(){}function
hmac($va,$pb,$z,$ze=false){$Na=64;if(strlen($z)>$Na)$z=pack("H*",$va($z));$z=str_pad($z,$Na,"\0");$Zc=$z^str_repeat("\x36",$Na);$ad=$z^str_repeat("\x5C",$Na);$K=$va($ad.pack("H*",$va($Zc.$pb)));if($ze)$K=pack("H*",$K);return$K;}function
sdb_request($qa,$G=array()){global$b,$h;list($Fc,$G['AWSAccessKeyId'],$Pe)=$b->credentials();$G['Action']=$qa;$G['Timestamp']=gmdate('Y-m-d\TH:i:s+00:00');$G['Version']='2009-04-15';$G['SignatureVersion']=2;$G['SignatureMethod']='HmacSHA1';ksort($G);$I='';foreach($G
as$z=>$X)$I.='&'.rawurlencode($z).'='.rawurlencode($X);$I=str_replace('%7E','~',substr($I,1));$I.="&Signature=".urlencode(base64_encode(hmac('sha1',"POST\n".preg_replace('~^https?://~','',$Fc)."\n/\n$I",$Pe,true)));@ini_set('track_errors',1);$dc=@file_get_contents((preg_match('~^https?://~',$Fc)?$Fc:"http://$Fc"),false,stream_context_create(array('http'=>array('method'=>'POST','content'=>$I,'ignore_errors'=>1,))));if(!$dc){$h->error=$php_errormsg;return
false;}libxml_use_internal_errors(true);$pg=simplexml_load_string($dc);if(!$pg){$n=libxml_get_last_error();$h->error=$n->message;return
false;}if($pg->Errors){$n=$pg->Errors->Error;$h->error="$n->Message ($n->Code)";return
false;}$h->error='';$tf=$qa."Result";return($pg->$tf?$pg->$tf:true);}function
sdb_request_all($qa,$tf,$G=array(),$_f=0){$K=array();$hf=($_f?microtime(true):0);$_=(preg_match('~LIMIT\s+(\d+)\s*$~i',$G['SelectExpression'],$B)?$B[1]:0);do{$pg=sdb_request($qa,$G);if(!$pg)break;foreach($pg->$tf
as$Ib)$K[]=$Ib;if($_&&count($K)>=$_){$_GET["next"]=$pg->NextToken;break;}if($_f&&microtime(true)-$hf>$_f)return
false;$G['NextToken']=$pg->NextToken;if($_)$G['SelectExpression']=preg_replace('~\d+\s*$~',$_-count($K),$G['SelectExpression']);}while($pg->NextToken);return$K;}$y="simpledb";$Sd=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","IS NOT NULL");$vc=array();$xc=array("count");$Gb=array(array("json"));}$Bb["mongo"]="MongoDB (beta)";if(isset($_GET["mongo"])){$le=array("mongo");define("DRIVER","mongo");if(class_exists('MongoDB')){class
Min_DB{var$extension="Mongo",$error,$last_id,$_link,$_db;function
connect($O,$V,$H){global$b;$m=$b->database();$Ud=array();if($V!=""){$Ud["username"]=$V;$Ud["password"]=$H;}if($m!="")$Ud["db"]=$m;try{$this->_link=@new
MongoClient("mongodb://$O",$Ud);return
true;}catch(Exception$Sb){$this->error=$Sb->getMessage();return
false;}}function
query($I){return
false;}function
select_db($k){try{$this->_db=$this->_link->selectDB($k);return
true;}catch(Exception$Sb){$this->error=$Sb->getMessage();return
false;}}function
quote($Q){return$Q;}}class
Min_Result{var$num_rows,$_rows=array(),$_offset=0,$_charset=array();function
__construct($J){foreach($J
as$Xc){$L=array();foreach($Xc
as$z=>$X){if(is_a($X,'MongoBinData'))$this->_charset[$z]=63;$L[$z]=(is_a($X,'MongoId')?'ObjectId("'.strval($X).'")':(is_a($X,'MongoDate')?gmdate("Y-m-d H:i:s",$X->sec)." GMT":(is_a($X,'MongoBinData')?$X->bin:(is_a($X,'MongoRegex')?strval($X):(is_object($X)?get_class($X):$X)))));}$this->_rows[]=$L;foreach($L
as$z=>$X){if(!isset($this->_rows[0][$z]))$this->_rows[0][$z]=null;}}$this->num_rows=count($this->_rows);}function
fetch_assoc(){$L=current($this->_rows);if(!$L)return$L;$K=array();foreach($this->_rows[0]as$z=>$X)$K[$z]=$L[$z];next($this->_rows);return$K;}function
fetch_row(){$K=$this->fetch_assoc();if(!$K)return$K;return
array_values($K);}function
fetch_field(){$cd=array_keys($this->_rows[0]);$D=$cd[$this->_offset++];return(object)array('name'=>$D,'charsetnr'=>$this->_charset[$D],);}}}class
Min_Driver
extends
Min_SQL{public$ne="_id";function
select($R,$N,$Z,$s,$E=array(),$_=1,$F=0,$pe=false){$N=($N==array("*")?array():array_fill_keys($N,true));$cf=array();foreach($E
as$X){$X=preg_replace('~ DESC$~','',$X,1,$lb);$cf[$X]=($lb?-1:1);}return
new
Min_Result($this->_conn->_db->selectCollection($R)->find(array(),$N)->sort($cf)->limit(+$_)->skip($F*$_));}function
insert($R,$P){try{$K=$this->_conn->_db->selectCollection($R)->insert($P);$this->_conn->errno=$K['code'];$this->_conn->error=$K['err'];$this->_conn->last_id=$P['_id'];return!$K['err'];}catch(Exception$Sb){$this->_conn->error=$Sb->getMessage();return
false;}}}function
connect(){global$b;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2]))return$h;return$h->error;}function
error(){global$h;return
h($h->error);}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
get_databases($lc){global$h;$K=array();$qb=$h->_link->listDBs();foreach($qb['databases']as$m)$K[]=$m['name'];return$K;}function
collations(){return
array();}function
db_collation($m,$cb){}function
count_tables($l){global$h;$K=array();foreach($l
as$m)$K[$m]=count($h->_link->selectDB($m)->getCollectionNames(true));return$K;}function
tables_list(){global$h;return
array_fill_keys($h->_db->getCollectionNames(true),'table');}function
table_status($D="",$ac=false){$K=array();foreach(tables_list()as$R=>$U){$K[$R]=array("Name"=>$R);if($D==$R)return$K[$R];}return$K;}function
information_schema(){}function
is_view($S){}function
drop_databases($l){global$h;foreach($l
as$m){$Fe=$h->_link->selectDB($m)->drop();if(!$Fe['ok'])return
false;}return
true;}function
indexes($R,$i=null){global$h;$K=array();foreach($h->_db->selectCollection($R)->getIndexInfo()as$w){$wb=array();foreach($w["key"]as$e=>$U)$wb[]=($U==-1?'1':null);$K[$w["name"]]=array("type"=>($w["name"]=="_id_"?"PRIMARY":($w["unique"]?"UNIQUE":"INDEX")),"columns"=>array_keys($w["key"]),"lengths"=>array(),"descs"=>$wb,);}return$K;}function
fields($R){return
fields_from_edit();}function
convert_field($o){}function
unconvert_field($o,$K){return$K;}function
foreign_keys($R){return
array();}function
fk_support($S){}function
engines(){return
array();}function
found_rows($S,$Z){global$h;return$h->_db->selectCollection($_GET["select"])->count($Z);}function
alter_table($R,$D,$p,$mc,$fb,$Nb,$d,$Ea,$ee){global$h;if($R==""){$h->_db->createCollection($D);return
true;}}function
drop_tables($T){global$h;foreach($T
as$R){$Fe=$h->_db->selectCollection($R)->drop();if(!$Fe['ok'])return
false;}return
true;}function
truncate_tables($T){global$h;foreach($T
as$R){$Fe=$h->_db->selectCollection($R)->remove();if(!$Fe['ok'])return
false;}return
true;}function
alter_indexes($R,$c){global$h;foreach($c
as$X){list($U,$D,$P)=$X;if($P=="DROP")$K=$h->_db->command(array("deleteIndexes"=>$R,"index"=>$D));else{$f=array();foreach($P
as$e){$e=preg_replace('~ DESC$~','',$e,1,$lb);$f[$e]=($lb?-1:1);}$K=$h->_db->selectCollection($R)->ensureIndex($f,array("unique"=>($U=="UNIQUE"),"name"=>$D,));}if($K['errmsg']){$h->error=$K['errmsg'];return
false;}}return
true;}function
last_id(){global$h;return$h->last_id;}function
table($v){return$v;}function
idf_escape($v){return$v;}function
support($bc){return
preg_match("~database|indexes~",$bc);}$y="mongo";$Sd=array("=");$vc=array();$xc=array();$Gb=array(array("json"));}$Bb["elastic"]="Elasticsearch (beta)";if(isset($_GET["elastic"])){$le=array("json");define("DRIVER","elastic");if(function_exists('json_decode')){class
Min_DB{var$extension="JSON",$server_info,$errno,$error,$_url;function
rootQuery($fe,$jb=array(),$Ad='GET'){@ini_set('track_errors',1);$dc=@file_get_contents($this->_url.'/'.ltrim($fe,'/'),false,stream_context_create(array('http'=>array('method'=>$Ad,'content'=>json_encode($jb),'ignore_errors'=>1,))));if(!$dc){$this->error=$php_errormsg;return$dc;}if(!preg_match('~^HTTP/[0-9.]+ 2~i',$http_response_header[0])){$this->error=$dc;return
false;}$K=json_decode($dc,true);if($K===null){$this->errno=json_last_error();if(function_exists('json_last_error_msg'))$this->error=json_last_error_msg();else{$ib=get_defined_constants(true);foreach($ib['json']as$D=>$Y){if($Y==$this->errno&&preg_match('~^JSON_ERROR_~',$D)){$this->error=$D;break;}}}}return$K;}function
query($fe,$jb=array(),$Ad='GET'){return$this->rootQuery(($this->_db!=""?"$this->_db/":"/").ltrim($fe,'/'),$jb,$Ad);}function
connect($O,$V,$H){preg_match('~^(https?://)?(.*)~',$O,$B);$this->_url=($B[1]?$B[1]:"http://")."$V:$H@$B[2]/";$K=$this->query('');if($K)$this->server_info=$K['version']['number'];return(bool)$K;}function
select_db($k){$this->_db=$k;return
true;}function
quote($Q){return$Q;}}class
Min_Result{var$num_rows,$_rows;function
__construct($M){$this->num_rows=count($this->_rows);$this->_rows=$M;reset($this->_rows);}function
fetch_assoc(){$K=current($this->_rows);next($this->_rows);return$K;}function
fetch_row(){return
array_values($this->fetch_assoc());}}}class
Min_Driver
extends
Min_SQL{function
select($R,$N,$Z,$s,$E=array(),$_=1,$F=0,$pe=false){global$b;$pb=array();$I="$R/_search";if($N!=array("*"))$pb["fields"]=$N;if($E){$cf=array();foreach($E
as$bb){$bb=preg_replace('~ DESC$~','',$bb,1,$lb);$cf[]=($lb?array($bb=>"desc"):$bb);}$pb["sort"]=$cf;}if($_){$pb["size"]=+$_;if($F)$pb["from"]=($F*$_);}foreach($Z
as$X){list($bb,$Qd,$X)=explode(" ",$X,3);if($bb=="_id")$pb["query"]["ids"]["values"][]=$X;elseif($bb.$X!=""){$vf=array("term"=>array(($bb!=""?$bb:"_all")=>$X));if($Qd=="=")$pb["query"]["filtered"]["filter"]["and"][]=$vf;else$pb["query"]["filtered"]["query"]["bool"]["must"][]=$vf;}}if($pb["query"]&&!$pb["query"]["filtered"]["query"]&&!$pb["query"]["ids"])$pb["query"]["filtered"]["query"]=array("match_all"=>array());$hf=microtime(true);$Oe=$this->_conn->query($I,$pb);if($pe)echo$b->selectQuery("$I: ".print_r($pb,true),format_time($hf));if(!$Oe)return
false;$K=array();foreach($Oe['hits']['hits']as$Ec){$L=array();if($N==array("*"))$L["_id"]=$Ec["_id"];$p=$Ec['_source'];if($N!=array("*")){$p=array();foreach($N
as$z)$p[$z]=$Ec['fields'][$z];}foreach($p
as$z=>$X){if($pb["fields"])$X=$X[0];$L[$z]=(is_array($X)?json_encode($X):$X);}$K[]=$L;}return
new
Min_Result($K);}}function
connect(){global$b;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2]))return$h;return$h->error;}function
support($bc){return
preg_match("~database|table|columns~",$bc);}function
logged_user(){global$b;$j=$b->credentials();return$j[1];}function
get_databases(){global$h;$K=$h->rootQuery('_aliases');if($K){$K=array_keys($K);sort($K,SORT_STRING);}return$K;}function
collations(){return
array();}function
db_collation($m,$cb){}function
engines(){return
array();}function
count_tables($l){global$h;$K=$h->query('_mapping');if($K)$K=array_map('count',$K);return$K;}function
tables_list(){global$h;$K=$h->query('_mapping');if($K)$K=array_fill_keys(array_keys($K[$h->_db]["mappings"]),'table');return$K;}function
table_status($D="",$ac=false){global$h;$Oe=$h->query("_search?search_type=count",array("facets"=>array("count_by_type"=>array("terms"=>array("field"=>"_type",)))),"POST");$K=array();if($Oe){foreach($Oe["facets"]["count_by_type"]["terms"]as$R){$K[$R["term"]]=array("Name"=>$R["term"],"Engine"=>"table","Rows"=>$R["count"],);if($D!=""&&$D==$R["term"])return$K[$D];}}return$K;}function
error(){global$h;return
h($h->error);}function
information_schema(){}function
is_view($S){}function
indexes($R,$i=null){return
array(array("type"=>"PRIMARY","columns"=>array("_id")),);}function
fields($R){global$h;$J=$h->query("$R/_mapping");$K=array();if($J){$rd=$J[$R]['properties'];if(!$rd)$rd=$J[$h->_db]['mappings'][$R]['properties'];if($rd){foreach($rd
as$D=>$o){$K[$D]=array("field"=>$D,"full_type"=>$o["type"],"type"=>$o["type"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1),);if($o["properties"]){unset($K[$D]["privileges"]["insert"]);unset($K[$D]["privileges"]["update"]);}}}}return$K;}function
foreign_keys($R){return
array();}function
table($v){return$v;}function
idf_escape($v){return$v;}function
convert_field($o){}function
unconvert_field($o,$K){return$K;}function
fk_support($S){}function
found_rows($S,$Z){return
null;}function
create_database($m){global$h;return$h->rootQuery(urlencode($m),array(),'PUT');}function
drop_databases($l){global$h;return$h->rootQuery(urlencode(implode(',',$l)),array(),'DELETE');}function
drop_tables($T){global$h;$K=true;foreach($T
as$R)$K=$K&&$h->query(urlencode($R),array(),'DELETE');return$K;}$y="elastic";$Sd=array("=","query");$vc=array();$xc=array();$Gb=array(array("json"));}$Bb=array("server"=>"MySQL")+$Bb;if(!defined("DRIVER")){$le=array("MySQLi","MySQL","PDO_MySQL");define("DRIVER","server");if(extension_loaded("mysqli")){class
Min_DB
extends
MySQLi{var$extension="MySQLi";function
__construct(){parent::init();}function
connect($O="",$V="",$H="",$k=null,$je=null,$bf=null){mysqli_report(MYSQLI_REPORT_OFF);list($Fc,$je)=explode(":",$O,2);$K=@$this->real_connect(($O!=""?$Fc:ini_get("mysqli.default_host")),($O.$V!=""?$V:ini_get("mysqli.default_user")),($O.$V.$H!=""?$H:ini_get("mysqli.default_pw")),$k,(is_numeric($je)?$je:ini_get("mysqli.default_port")),(!is_numeric($je)?$je:$bf));return$K;}function
set_charset($Ra){if(parent::set_charset($Ra))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $Ra");}function
result($I,$o=0){$J=$this->query($I);if(!$J)return
false;$L=$J->fetch_array();return$L[$o];}function
quote($Q){return"'".$this->escape_string($Q)."'";}}}elseif(extension_loaded("mysql")&&!(ini_get("sql.safe_mode")&&extension_loaded("pdo_mysql"))){class
Min_DB{var$extension="MySQL",$server_info,$affected_rows,$errno,$error,$_link,$_result;function
connect($O,$V,$H){$this->_link=@mysql_connect(($O!=""?$O:ini_get("mysql.default_host")),("$O$V"!=""?$V:ini_get("mysql.default_user")),("$O$V$H"!=""?$H:ini_get("mysql.default_password")),true,131072);if($this->_link)$this->server_info=mysql_get_server_info($this->_link);else$this->error=mysql_error();return(bool)$this->_link;}function
set_charset($Ra){if(function_exists('mysql_set_charset')){if(mysql_set_charset($Ra,$this->_link))return
true;mysql_set_charset('utf8',$this->_link);}return$this->query("SET NAMES $Ra");}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->_link)."'";}function
select_db($k){return
mysql_select_db($k,$this->_link);}function
query($I,$Sf=false){$J=@($Sf?mysql_unbuffered_query($I,$this->_link):mysql_query($I,$this->_link));$this->error="";if(!$J){$this->errno=mysql_errno($this->_link);$this->error=mysql_error($this->_link);return
false;}if($J===true){$this->affected_rows=mysql_affected_rows($this->_link);$this->info=mysql_info($this->_link);return
true;}return
new
Min_Result($J);}function
multi_query($I){return$this->_result=$this->query($I);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($I,$o=0){$J=$this->query($I);if(!$J||!$J->num_rows)return
false;return
mysql_result($J->_result,0,$o);}}class
Min_Result{var$num_rows,$_result,$_offset=0;function
__construct($J){$this->_result=$J;$this->num_rows=mysql_num_rows($J);}function
fetch_assoc(){return
mysql_fetch_assoc($this->_result);}function
fetch_row(){return
mysql_fetch_row($this->_result);}function
fetch_field(){$K=mysql_fetch_field($this->_result,$this->_offset++);$K->orgtable=$K->table;$K->orgname=$K->name;$K->charsetnr=($K->blob?63:0);return$K;}function
__destruct(){mysql_free_result($this->_result);}}}elseif(extension_loaded("pdo_mysql")){class
Min_DB
extends
Min_PDO{var$extension="PDO_MySQL";function
connect($O,$V,$H){$this->dsn("mysql:charset=utf8;host=".str_replace(":",";unix_socket=",preg_replace('~:(\\d)~',';port=\\1',$O)),$V,$H);return
true;}function
set_charset($Ra){$this->query("SET NAMES $Ra");}function
select_db($k){return$this->query("USE ".idf_escape($k));}function
query($I,$Sf=false){$this->setAttribute(1000,!$Sf);return
parent::query($I,$Sf);}}}class
Min_Driver
extends
Min_SQL{function
insert($R,$P){return($P?parent::insert($R,$P):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,$M,$ne){$f=array_keys(reset($M));$me="INSERT INTO ".table($R)." (".implode(", ",$f).") VALUES\n";$fg=array();foreach($f
as$z)$fg[$z]="$z = VALUES($z)";$nf="\nON DUPLICATE KEY UPDATE ".implode(", ",$fg);$fg=array();$ld=0;foreach($M
as$P){$Y="(".implode(", ",$P).")";if($fg&&(strlen($me)+$ld+strlen($Y)+strlen($nf)>1e6)){if(!queries($me.implode(",\n",$fg).$nf))return
false;$fg=array();$ld=0;}$fg[]=$Y;$ld+=strlen($Y)+2;}return
queries($me.implode(",\n",$fg).$nf);}}function
idf_escape($v){return"`".str_replace("`","``",$v)."`";}function
table($v){return
idf_escape($v);}function
connect(){global$b,$Rf,$kf;$h=new
Min_DB;$j=$b->credentials();if($h->connect($j[0],$j[1],$j[2])){$h->set_charset(charset($h));$h->query("SET sql_quote_show_create = 1, autocommit = 1");if(version_compare($h->server_info,'5.7.8')>=0){$kf[lang(23)][]="json";$Rf["json"]=4294967295;}return$h;}$K=$h->error;if(function_exists('iconv')&&!is_utf8($K)&&strlen($Le=iconv("windows-1250","utf-8",$K))>strlen($K))$K=$Le;return$K;}function
get_databases($lc){global$h;$K=get_session("dbs");if($K===null){$I=($h->server_info>=5?"SELECT SCHEMA_NAME FROM information_schema.SCHEMATA":"SHOW DATABASES");$K=($lc?slow_query($I):get_vals($I));restart_session();set_session("dbs",$K);stop_session();}return$K;}function
limit($I,$Z,$_,$Ld=0,$Te=" "){return" $I$Z".($_!==null?$Te."LIMIT $_".($Ld?" OFFSET $Ld":""):"");}function
limit1($I,$Z){return
limit($I,$Z,1);}function
db_collation($m,$cb){global$h;$K=null;$mb=$h->result("SHOW CREATE DATABASE ".idf_escape($m),1);if(preg_match('~ COLLATE ([^ ]+)~',$mb,$B))$K=$B[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$mb,$B))$K=$cb[$B[1]][-1];return$K;}function
engines(){$K=array();foreach(get_rows("SHOW ENGINES")as$L){if(preg_match("~YES|DEFAULT~",$L["Support"]))$K[]=$L["Engine"];}return$K;}function
logged_user(){global$h;return$h->result("SELECT USER()");}function
tables_list(){global$h;return
get_key_vals($h->server_info>=5?"SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME":"SHOW TABLES");}function
count_tables($l){$K=array();foreach($l
as$m)$K[$m]=count(get_vals("SHOW TABLES IN ".idf_escape($m)));return$K;}function
table_status($D="",$ac=false){global$h;$K=array();foreach(get_rows($ac&&$h->server_info>=5?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($D!=""?"AND TABLE_NAME = ".q($D):"ORDER BY Name"):"SHOW TABLE STATUS".($D!=""?" LIKE ".q(addcslashes($D,"%_\\")):""))as$L){if($L["Engine"]=="InnoDB")$L["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\\1',$L["Comment"]);if(!isset($L["Engine"]))$L["Comment"]="";if($D!="")return$L;$K[$L["Name"]]=$L;}return$K;}function
is_view($S){return$S["Engine"]===null;}function
fk_support($S){global$h;return
preg_match('~InnoDB|IBMDB2I~i',$S["Engine"])||(preg_match('~NDB~i',$S["Engine"])&&version_compare($h->server_info,'5.6')>=0);}function
fields($R){$K=array();foreach(get_rows("SHOW FULL COLUMNS FROM ".table($R))as$L){preg_match('~^([^( ]+)(?:\\((.+)\\))?( unsigned)?( zerofill)?$~',$L["Type"],$B);$K[$L["Field"]]=array("field"=>$L["Field"],"full_type"=>$L["Type"],"type"=>$B[1],"length"=>$B[2],"unsigned"=>ltrim($B[3].$B[4]),"default"=>($L["Default"]!=""||preg_match("~char|set~",$B[1])?$L["Default"]:null),"null"=>($L["Null"]=="YES"),"auto_increment"=>($L["Extra"]=="auto_increment"),"on_update"=>(preg_match('~^on update (.+)~i',$L["Extra"],$B)?$B[1]:""),"collation"=>$L["Collation"],"privileges"=>array_flip(preg_split('~, *~',$L["Privileges"])),"comment"=>$L["Comment"],"primary"=>($L["Key"]=="PRI"),);}return$K;}function
indexes($R,$i=null){$K=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$i)as$L){$D=$L["Key_name"];$K[$D]["type"]=($D=="PRIMARY"?"PRIMARY":($L["Index_type"]=="FULLTEXT"?"FULLTEXT":($L["Non_unique"]?($L["Index_type"]=="SPATIAL"?"SPATIAL":"INDEX"):"UNIQUE")));$K[$D]["columns"][]=$L["Column_name"];$K[$D]["lengths"][]=($L["Index_type"]=="SPATIAL"?null:$L["Sub_part"]);$K[$D]["descs"][]=null;}return$K;}function
foreign_keys($R){global$h,$Nd;static$ge='`(?:[^`]|``)+`';$K=array();$nb=$h->result("SHOW CREATE TABLE ".table($R),1);if($nb){preg_match_all("~CONSTRAINT ($ge) FOREIGN KEY ?\\(((?:$ge,? ?)+)\\) REFERENCES ($ge)(?:\\.($ge))? \\(((?:$ge,? ?)+)\\)(?: ON DELETE ($Nd))?(?: ON UPDATE ($Nd))?~",$nb,$C,PREG_SET_ORDER);foreach($C
as$B){preg_match_all("~$ge~",$B[2],$df);preg_match_all("~$ge~",$B[5],$uf);$K[idf_unescape($B[1])]=array("db"=>idf_unescape($B[4]!=""?$B[3]:$B[4]),"table"=>idf_unescape($B[4]!=""?$B[4]:$B[3]),"source"=>array_map('idf_unescape',$df[0]),"target"=>array_map('idf_unescape',$uf[0]),"on_delete"=>($B[6]?$B[6]:"RESTRICT"),"on_update"=>($B[7]?$B[7]:"RESTRICT"),);}}return$K;}function
view($D){global$h;return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\\s+AS\\s+~isU','',$h->result("SHOW CREATE VIEW ".table($D),1)));}function
collations(){$K=array();foreach(get_rows("SHOW COLLATION")as$L){if($L["Default"])$K[$L["Charset"]][-1]=$L["Collation"];else$K[$L["Charset"]][]=$L["Collation"];}ksort($K);foreach($K
as$z=>$X)asort($K[$z]);return$K;}function
information_schema($m){global$h;return($h->server_info>=5&&$m=="information_schema")||($h->server_info>=5.5&&$m=="performance_schema");}function
error(){global$h;return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",$h->error));}function
create_database($m,$d){return
queries("CREATE DATABASE ".idf_escape($m).($d?" COLLATE ".q($d):""));}function
drop_databases($l){$K=apply_queries("DROP DATABASE",$l,'idf_escape');restart_session();set_session("dbs",null);return$K;}function
rename_database($D,$d){$K=false;if(create_database($D,$d)){$Ce=array();foreach(tables_list()as$R=>$U)$Ce[]=table($R)." TO ".idf_escape($D).".".table($R);$K=(!$Ce||queries("RENAME TABLE ".implode(", ",$Ce)));if($K)queries("DROP DATABASE ".idf_escape(DB));restart_session();set_session("dbs",null);}return$K;}function
auto_increment(){$Fa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$w){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$w["columns"],true)){$Fa="";break;}if($w["type"]=="PRIMARY")$Fa=" UNIQUE";}}return" AUTO_INCREMENT$Fa";}function
alter_table($R,$D,$p,$mc,$fb,$Nb,$d,$Ea,$ee){$c=array();foreach($p
as$o)$c[]=($o[1]?($R!=""?($o[0]!=""?"CHANGE ".idf_escape($o[0]):"ADD"):" ")." ".implode($o[1]).($R!=""?$o[2]:""):"DROP ".idf_escape($o[0]));$c=array_merge($c,$mc);$if=($fb!==null?" COMMENT=".q($fb):"").($Nb?" ENGINE=".q($Nb):"").($d?" COLLATE ".q($d):"").($Ea!=""?" AUTO_INCREMENT=$Ea":"");if($R=="")return
queries("CREATE TABLE ".table($D)." (\n".implode(",\n",$c)."\n)$if$ee");if($R!=$D)$c[]="RENAME TO ".table($D);if($if)$c[]=ltrim($if);return($c||$ee?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$c).$ee):true);}function
alter_indexes($R,$c){foreach($c
as$z=>$X)$c[$z]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$c));}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($jg){return
queries("DROP VIEW ".implode(", ",array_map('table',$jg)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('table',$T)));}function
move_tables($T,$jg,$uf){$Ce=array();foreach(array_merge($T,$jg)as$R)$Ce[]=table($R)." TO ".idf_escape($uf).".".table($R);return
queries("RENAME TABLE ".implode(", ",$Ce));}function
copy_tables($T,$jg,$uf){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$D=($uf==DB?table("copy_$R"):idf_escape($uf).".".table($R));if(!queries("\nDROP TABLE IF EXISTS $D")||!queries("CREATE TABLE $D LIKE ".table($R))||!queries("INSERT INTO $D SELECT * FROM ".table($R)))return
false;}foreach($jg
as$R){$D=($uf==DB?table("copy_$R"):idf_escape($uf).".".table($R));$ig=view($R);if(!queries("DROP VIEW IF EXISTS $D")||!queries("CREATE VIEW $D AS $ig[select]"))return
false;}return
true;}function
trigger($D){if($D=="")return
array();$M=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($D));return
reset($M);}function
triggers($R){$K=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$L)$K[$L["Trigger"]]=array($L["Timing"],$L["Event"]);return$K;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($D,$U){global$h,$Ob,$Qc,$Rf;$wa=array("bool","boolean","integer","double precision","real","dec","numeric","fixed","national char","national varchar");$Qf="((".implode("|",array_merge(array_keys($Rf),$wa)).")\\b(?:\\s*\\(((?:[^'\")]|$Ob)++)\\))?\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?";$ge="\\s*(".($U=="FUNCTION"?"":$Qc).")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$Qf";$mb=$h->result("SHOW CREATE $U ".idf_escape($D),2);preg_match("~\\(((?:$ge\\s*,?)*)\\)\\s*".($U=="FUNCTION"?"RETURNS\\s+$Qf\\s+":"")."(.*)~is",$mb,$B);$p=array();preg_match_all("~$ge\\s*,?~is",$B[1],$C,PREG_SET_ORDER);foreach($C
as$be){$D=str_replace("``","`",$be[2]).$be[3];$p[]=array("field"=>$D,"type"=>strtolower($be[5]),"length"=>preg_replace_callback("~$Ob~s",'normalize_enum',$be[6]),"unsigned"=>strtolower(preg_replace('~\\s+~',' ',trim("$be[8] $be[7]"))),"null"=>1,"full_type"=>$be[4],"inout"=>strtoupper($be[1]),"collation"=>strtolower($be[9]),);}if($U!="FUNCTION")return
array("fields"=>$p,"definition"=>$B[11]);return
array("fields"=>$p,"returns"=>array("type"=>$B[12],"length"=>$B[13],"unsigned"=>$B[15],"collation"=>$B[16]),"definition"=>$B[17],"language"=>"SQL",);}function
routines(){return
get_rows("SELECT ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ".q(DB));}function
routine_languages(){return
array();}function
last_id(){global$h;return$h->result("SELECT LAST_INSERT_ID()");}function
explain($h,$I){return$h->query("EXPLAIN ".($h->server_info>=5.1?"PARTITIONS ":"").$I);}function
found_rows($S,$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($Me){return
true;}function
create_sql($R,$Ea){global$h;$K=$h->result("SHOW CREATE TABLE ".table($R),1);if(!$Ea)$K=preg_replace('~ AUTO_INCREMENT=\\d+~','',$K);return$K;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($k){return"USE ".idf_escape($k);}function
trigger_sql($R,$lf){$K="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$L)$K.="\n".($lf=='CREATE+ALTER'?"DROP TRIGGER IF EXISTS ".idf_escape($L["Trigger"]).";;\n":"")."CREATE TRIGGER ".idf_escape($L["Trigger"])." $L[Timing] $L[Event] ON ".table($L["Table"])." FOR EACH ROW\n$L[Statement];;\n";return$K;}function
show_variables(){return
get_key_vals("SHOW VARIABLES");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
show_status(){return
get_key_vals("SHOW STATUS");}function
replication_status($U){return
get_rows("SHOW $U STATUS");}function
convert_field($o){if(preg_match("~binary~",$o["type"]))return"HEX(".idf_escape($o["field"]).")";if($o["type"]=="bit")return"BIN(".idf_escape($o["field"])." + 0)";if(preg_match("~geometry|point|linestring|polygon~",$o["type"]))return"AsWKT(".idf_escape($o["field"]).")";}function
unconvert_field($o,$K){if(preg_match("~binary~",$o["type"]))$K="UNHEX($K)";if($o["type"]=="bit")$K="CONV($K, 2, 10) + 0";if(preg_match("~geometry|point|linestring|polygon~",$o["type"]))$K="GeomFromText($K)";return$K;}function
support($bc){global$h;return!preg_match("~scheme|sequence|type|view_trigger|materializedview".($h->server_info<5.1?"|event|partitioning".($h->server_info<5?"|routine|trigger|view":""):"")."~",$bc);}function
kill_process($X){return
queries("KILL ".number($X));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){global$h;return$h->result("SELECT @@max_connections");}$y="sql";$Rf=array();$kf=array();foreach(array(lang(25)=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),lang(26)=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),lang(23)=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),lang(30)=>array("enum"=>65535,"set"=>64),lang(27)=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),lang(29)=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),)as$z=>$X){$Rf+=$X;$kf[$z]=array_keys($X);}$Yf=array("unsigned","zerofill","unsigned zerofill");$Sd=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");$vc=array("char_length","date","from_unixtime","lower","round","sec_to_time","time_to_sec","upper");$xc=array("avg","count","count distinct","group_concat","max","min","sum");$Gb=array(array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",),array("(^|[^o])int|float|double|decimal"=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",));}define("SERVER",$_GET[DRIVER]);define("DB",$_GET["db"]);define("ME",preg_replace('~^[^?]*/([^?]*).*~','\\1',$_SERVER["REQUEST_URI"]).'?'.(sid()?SID.'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));$ca="4.3.1";class
Adminer{var$operators=array("<=",">=");var$_values=array();function
name(){return"<a href='https://www.adminer.org/editor/' target='_blank' id='h1'>".lang(31)."</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
permanentLogin($mb=false){return
password_file($mb);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
database(){global$h;if($h){$l=$this->databases(false);return(!$l?$h->result("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1)"):$l[(information_schema($l[0])?1:0)]);}}function
schemas(){return
schemas();}function
databases($lc=true){return
get_databases($lc);}function
queryTimeout(){return
5;}function
headers(){return
true;}function
head(){return
true;}function
loginForm(){echo'<table cellspacing="0">
<tr><th>',lang(32),'<td><input type="hidden" name="auth[driver]" value="server"><input name="auth[username]" id="username" value="',h($_GET["username"]),'" autocapitalize="off">
<tr><th>',lang(33),'<td><input type="password" name="auth[password]">
</table>
<script type="text/javascript">
focus(document.getElementById(\'username\'));
</script>
',"<p><input type='submit' value='".lang(34)."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],lang(35))."\n";}function
login($pd,$H){global$h;$h->query("SET time_zone = ".q(substr_replace(@date("O"),":",-2,0)));return
true;}function
tableName($qf){return
h($qf["Comment"]!=""?$qf["Comment"]:$qf["Name"]);}function
fieldName($o,$E=0){return
h($o["comment"]!=""?$o["comment"]:$o["field"]);}function
selectLinks($qf,$P=""){$a=$qf["Name"];if($P!==null)echo'<p class="tabs"><a href="'.h(ME.'edit='.urlencode($a).$P).'">'.lang(36)."</a>\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$pf){$K=array();foreach(get_rows("SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = ".q($this->database())."
AND REFERENCED_TABLE_SCHEMA = ".q($this->database())."
AND REFERENCED_TABLE_NAME = ".q($R)."
ORDER BY ORDINAL_POSITION",null,"")as$L)$K[$L["TABLE_NAME"]]["keys"][$L["CONSTRAINT_NAME"]][$L["COLUMN_NAME"]]=$L["REFERENCED_COLUMN_NAME"];foreach($K
as$z=>$X){$D=$this->tableName(table_status($z,true));if($D!=""){$Oe=preg_quote($pf);$Te="(:|\\s*-)?\\s+";$K[$z]["name"]=(preg_match("(^$Oe$Te(.+)|^(.+?)$Te$Oe\$)iu",$D,$B)?$B[2].$B[3]:$D);}else
unset($K[$z]);}return$K;}function
backwardKeysPrint($Ia,$L){foreach($Ia
as$R=>$Ha){foreach($Ha["keys"]as$db){$A=ME.'select='.urlencode($R);$t=0;foreach($db
as$e=>$X)$A.=where_link($t++,$e,$L[$X]);echo"<a href='".h($A)."'>".h($Ha["name"])."</a>";$A=ME.'edit='.urlencode($R);foreach($db
as$e=>$X)$A.="&set".urlencode("[".bracket_escape($e)."]")."=".urlencode($L[$X]);echo"<a href='".h($A)."' title='".lang(36)."'>+</a> ";}}}function
selectQuery($I,$zf){return"<!--\n".str_replace("--","--><!-- ",$I)."\n($zf)\n-->\n";}function
rowDescription($R){foreach(fields($R)as$o){if(preg_match("~varchar|character varying~",$o["type"]))return
idf_escape($o["field"]);}return"";}function
rowDescriptions($M,$oc){$K=$M;foreach($M[0]as$z=>$X){if(list($R,$u,$D)=$this->_foreignColumn($oc,$z)){$Ic=array();foreach($M
as$L)$Ic[$L[$z]]=q($L[$z]);$vb=$this->_values[$R];if(!$vb)$vb=get_key_vals("SELECT $u, $D FROM ".table($R)." WHERE $u IN (".implode(", ",$Ic).")");foreach($M
as$Ed=>$L){if(isset($L[$z]))$K[$Ed][$z]=(string)$vb[$L[$z]];}}}return$K;}function
selectLink($X,$o){}function
selectVal($X,$A,$o,$Xd){$K=($X===null?"&nbsp;":$X);$A=h($A);if(preg_match('~blob|bytea~',$o["type"])&&!is_utf8($X)){$K=lang(37,strlen($Xd));if(preg_match("~^(GIF|\xFF\xD8\xFF|\x89PNG\x0D\x0A\x1A\x0A)~",$Xd))$K="<img src='$A' alt='$K'>";}if(like_bool($o)&&$K!="&nbsp;")$K=($X?lang(38):lang(39));if($A)$K="<a href='$A'".(is_url($A)?" rel='noreferrer'":"").">$K</a>";if(!$A&&!like_bool($o)&&preg_match('~int|float|double|decimal~',$o["type"]))$K="<div class='number'>$K</div>";elseif(preg_match('~date~',$o["type"]))$K="<div class='datetime'>$K</div>";return$K;}function
editVal($X,$o){if(preg_match('~date|timestamp~',$o["type"])&&$X!==null)return
preg_replace('~^(\\d{2}(\\d+))-(0?(\\d+))-(0?(\\d+))~',lang(40),$X);return$X;}function
selectColumnsPrint($N,$f){}function
selectSearchPrint($Z,$f,$x){$Z=(array)$_GET["where"];echo'<fieldset id="fieldset-search"><legend>'.lang(41)."</legend><div>\n";$cd=array();foreach($Z
as$z=>$X)$cd[$X["col"]]=$z;$t=0;$p=fields($_GET["select"]);foreach($f
as$D=>$ub){$o=$p[$D];if(preg_match("~enum~",$o["type"])||like_bool($o)){$z=$cd[$D];$t--;echo"<div>".h($ub)."<input type='hidden' name='where[$t][col]' value='".h($D)."'>:",(like_bool($o)?" <select name='where[$t][val]'>".optionlist(array(""=>"",lang(39),lang(38)),$Z[$z]["val"],true)."</select>":enum_input("checkbox"," name='where[$t][val][]'",$o,(array)$Z[$z]["val"],($o["null"]?0:null))),"</div>\n";unset($f[$D]);}elseif(is_array($Ud=$this->_foreignKeyOptions($_GET["select"],$D))){if($p[$D]["null"])$Ud[0]='('.lang(7).')';$z=$cd[$D];$t--;echo"<div>".h($ub)."<input type='hidden' name='where[$t][col]' value='".h($D)."'><input type='hidden' name='where[$t][op]' value='='>: <select name='where[$t][val]'>".optionlist($Ud,$Z[$z]["val"],true)."</select></div>\n";unset($f[$D]);}}$t=0;foreach($Z
as$X){if(($X["col"]==""||$f[$X["col"]])&&"$X[col]$X[val]"!=""){echo"<div><select name='where[$t][col]'><option value=''>(".lang(42).")".optionlist($f,$X["col"],true)."</select>",html_select("where[$t][op]",array(-1=>"")+$this->operators,$X["op"]),"<input type='search' name='where[$t][val]' value='".h($X["val"])."' onkeydown='selectSearchKeydown(this, event);' onsearch='selectSearchSearch(this);'></div>\n";$t++;}}echo"<div><select name='where[$t][col]' onchange='this.nextSibling.nextSibling.onchange();'><option value=''>(".lang(42).")".optionlist($f,null,true)."</select>",html_select("where[$t][op]",array(-1=>"")+$this->operators),"<input type='search' name='where[$t][val]' onchange='selectAddRow(this);' onsearch='selectSearch(this);'></div>\n","</div></fieldset>\n";}function
selectOrderPrint($E,$f,$x){$Wd=array();foreach($x
as$z=>$w){$E=array();foreach($w["columns"]as$X)$E[]=$f[$X];if(count(array_filter($E,'strlen'))>1&&$z!="PRIMARY")$Wd[$z]=implode(", ",$E);}if($Wd){echo'<fieldset><legend>'.lang(43)."</legend><div>","<select name='index_order'>".optionlist(array(""=>"")+$Wd,($_GET["order"][0]!=""?"":$_GET["index_order"]),true)."</select>","</div></fieldset>\n";}if($_GET["order"])echo"<div style='display: none;'>".hidden_fields(array("order"=>array(1=>reset($_GET["order"])),"desc"=>($_GET["desc"]?array(1=>1):array()),))."</div>\n";}function
selectLimitPrint($_){echo"<fieldset><legend>".lang(44)."</legend><div>";echo
html_select("limit",array("","50","100"),$_),"</div></fieldset>\n";}function
selectLengthPrint($xf){}function
selectActionPrint($x){echo"<fieldset><legend>".lang(45)."</legend><div>","<input type='submit' value='".lang(46)."'>","</div></fieldset>\n";}function
selectCommandPrint(){return
true;}function
selectImportPrint(){return
true;}function
selectEmailPrint($Kb,$f){if($Kb){print_fieldset("email",lang(47),$_POST["email_append"]);echo"<div onkeydown=\"eventStop(event); return bodyKeydown(event, 'email');\">\n","<p>".lang(48).": <input name='email_from' value='".h($_POST?$_POST["email_from"]:$_COOKIE["adminer_email"])."'>\n",lang(49).": <input name='email_subject' value='".h($_POST["email_subject"])."'>\n","<p><textarea name='email_message' rows='15' cols='75'>".h($_POST["email_message"].($_POST["email_append"]?'{$'."$_POST[email_addition]}":""))."</textarea>\n","<p onkeydown=\"eventStop(event); return bodyKeydown(event, 'email_append');\">".html_select("email_addition",$f,$_POST["email_addition"])."<input type='submit' name='email_append' value='".lang(11)."'>\n";echo"<p>".lang(50).": <input type='file' name='email_files[]' onchange=\"this.onchange = function () { }; var el = this.cloneNode(true); el.value = ''; this.parentNode.appendChild(el);\">","<p>".(count($Kb)==1?'<input type="hidden" name="email_field" value="'.h(key($Kb)).'">':html_select("email_field",$Kb)),"<input type='submit' name='email' value='".lang(51)."' onclick=\"return this.form['delete'].onclick();\">\n","</div>\n","</div></fieldset>\n";}}function
selectColumnsProcess($f,$x){return
array(array(),array());}function
selectSearchProcess($p,$x){$K=array();foreach((array)$_GET["where"]as$z=>$Z){$bb=$Z["col"];$Qd=$Z["op"];$X=$Z["val"];if(($z<0?"":$bb).$X!=""){$gb=array();foreach(($bb!=""?array($bb=>$p[$bb]):$p)as$D=>$o){if($bb!=""||is_numeric($X)||!preg_match('~int|float|double|decimal~',$o["type"])){$D=idf_escape($D);if($bb!=""&&$o["type"]=="enum")$gb[]=(in_array(0,$X)?"$D IS NULL OR ":"")."$D IN (".implode(", ",array_map('intval',$X)).")";else{$yf=preg_match('~char|text|enum|set~',$o["type"]);$Y=$this->processInput($o,(!$Qd&&$yf&&preg_match('~^[^%]+$~',$X)?"%$X%":$X));$gb[]=$D.($Y=="NULL"?" IS".($Qd==">="?" NOT":"")." $Y":(in_array($Qd,$this->operators)||$Qd=="="?" $Qd $Y":($yf?" LIKE $Y":" IN (".str_replace(",","', '",$Y).")")));if($z<0&&$X=="0")$gb[]="$D IS NULL";}}}$K[]=($gb?"(".implode(" OR ",$gb).")":"0");}}return$K;}function
selectOrderProcess($p,$x){$Lc=$_GET["index_order"];if($Lc!="")unset($_GET["order"][1]);if($_GET["order"])return
array(idf_escape(reset($_GET["order"])).($_GET["desc"]?" DESC":""));foreach(($Lc!=""?array($x[$Lc]):$x)as$w){if($Lc!=""||$w["type"]=="INDEX"){$zc=array_filter($w["descs"]);$ub=false;foreach($w["columns"]as$X){if(preg_match('~date|timestamp~',$p[$X]["type"])){$ub=true;break;}}$K=array();foreach($w["columns"]as$z=>$X)$K[]=idf_escape($X).(($zc?$w["descs"][$z]:$ub)?" DESC":"");return$K;}}return
array();}function
selectLimitProcess(){return(isset($_GET["limit"])?$_GET["limit"]:"50");}function
selectLengthProcess(){return"100";}function
selectEmailProcess($Z,$oc){if($_POST["email_append"])return
true;if($_POST["email"]){$Se=0;if($_POST["all"]||$_POST["check"]){$o=idf_escape($_POST["email_field"]);$mf=$_POST["email_subject"];$yd=$_POST["email_message"];preg_match_all('~\\{\\$([a-z0-9_]+)\\}~i',"$mf.$yd",$C);$M=get_rows("SELECT DISTINCT $o".($C[1]?", ".implode(", ",array_map('idf_escape',array_unique($C[1]))):"")." FROM ".table($_GET["select"])." WHERE $o IS NOT NULL AND $o != ''".($Z?" AND ".implode(" AND ",$Z):"").($_POST["all"]?"":" AND ((".implode(") OR (",array_map('where_check',(array)$_POST["check"]))."))"));$p=fields($_GET["select"]);foreach($this->rowDescriptions($M,$oc)as$L){$De=array('{\\'=>'{');foreach($C[1]as$X)$De['{$'."$X}"]=$this->editVal($L[$X],$p[$X]);$Jb=$L[$_POST["email_field"]];if(is_mail($Jb)&&send_mail($Jb,strtr($mf,$De),strtr($yd,$De),$_POST["email_from"],$_FILES["email_files"]))$Se++;}}cookie("adminer_email",$_POST["email_from"]);redirect(remove_from_uri(),lang(52,$Se));}return
false;}function
selectQueryBuild($N,$Z,$s,$E,$_,$F){return"";}function
messageQuery($I,$zf){return" <span class='time'>".@date("H:i:s")."</span><!--\n".str_replace("--","--><!-- ",$I)."\n".($zf?"($zf)\n":"")."-->";}function
editFunctions($o){$K=array();if($o["null"]&&preg_match('~blob~',$o["type"]))$K["NULL"]=lang(7);$K[""]=($o["null"]||$o["auto_increment"]||like_bool($o)?"":"*");if(preg_match('~date|time~',$o["type"]))$K["now"]=lang(53);if(preg_match('~_(md5|sha1)$~i',$o["field"],$B))$K[]=strtolower($B[1]);return$K;}function
editInput($R,$o,$Ca,$Y){if($o["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$Ca value='-1' checked><i>".lang(8)."</i></label> ":"").enum_input("radio",$Ca,$o,($Y||isset($_GET["select"])?$Y:0),($o["null"]?"":null));$Ud=$this->_foreignKeyOptions($R,$o["field"],$Y);if($Ud!==null)return(is_array($Ud)?"<select$Ca>".optionlist($Ud,$Y,true)."</select>":"<input value='".h($Y)."'$Ca class='hidden'><input value='".h($Ud)."' class='jsonly' onkeyup=\"whisper('".h(ME."script=complete&source=".urlencode($R)."&field=".urlencode($o["field"]))."&value=', this);\"><div onclick='return whisperClick(event, this.previousSibling);'></div>");if(like_bool($o))return'<input type="checkbox" value="'.h($Y?$Y:1).'"'.($Y?' checked':'')."$Ca>";$Dc="";if(preg_match('~time~',$o["type"]))$Dc=lang(54);if(preg_match('~date|timestamp~',$o["type"]))$Dc=lang(55).($Dc?" [$Dc]":"");if($Dc)return"<input value='".h($Y)."'$Ca> ($Dc)";if(preg_match('~_(md5|sha1)$~i',$o["field"]))return"<input type='password' value='".h($Y)."'$Ca>";return'';}function
processInput($o,$Y,$r=""){if($r=="now")return"$r()";$K=$Y;if(preg_match('~date|timestamp~',$o["type"])&&preg_match('(^'.str_replace('\\$1','(?P<p1>\\d*)',preg_replace('~(\\\\\\$([2-6]))~','(?P<p\\2>\\d{1,2})',preg_quote(lang(40)))).'(.*))',$Y,$B))$K=($B["p1"]!=""?$B["p1"]:($B["p2"]!=""?($B["p2"]<70?20:19).$B["p2"]:gmdate("Y")))."-$B[p3]$B[p4]-$B[p5]$B[p6]".end($B);$K=($o["type"]=="bit"&&preg_match('~^[0-9]+$~',$Y)?$K:q($K));if($Y==""&&like_bool($o))$K="0";elseif($Y==""&&($o["null"]||!preg_match('~char|text~',$o["type"])))$K="NULL";elseif(preg_match('~^(md5|sha1)$~',$r))$K="$r($K)";return
unconvert_field($o,$K);}function
dumpOutput(){return
array();}function
dumpFormat(){return
array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($m){}function
dumpTable(){echo"\xef\xbb\xbf";}function
dumpData($R,$lf,$I){global$h;$J=$h->query($I,1);if($J){while($L=$J->fetch_assoc()){if($lf=="table"){dump_csv(array_keys($L));$lf="INSERT";}dump_csv($L);}}}function
dumpFilename($Hc){return
friendly_url($Hc);}function
dumpHeaders($Hc,$Cd=false){$Wb="csv";header("Content-Type: text/csv; charset=utf-8");return$Wb;}function
homepage(){return
true;}function
navigation($Bd){global$ca;echo'<h1>
',$this->name(),' <span class="version">',$ca,'</span>
<a href="https://www.adminer.org/editor/#download" target="_blank" id="version">',(version_compare($ca,$_COOKIE["adminer_version"])<0?h($_COOKIE["adminer_version"]):""),'</a>
</h1>
';if($Bd=="auth"){$hc=true;foreach((array)$_SESSION["pwds"]as$gg=>$Ye){foreach($Ye[""]as$V=>$H){if($H!==null){if($hc){echo"<p id='logins' onmouseover='menuOver(this, event);' onmouseout='menuOut(this);'>\n";$hc=false;}echo"<a href='".h(auth_url($gg,"",$V))."'>".($V!=""?h($V):"<i>".lang(7)."</i>")."</a><br>\n";}}}}else{$this->databasesPrint($Bd);if($Bd!="db"&&$Bd!="ns"){$S=table_status('',true);if(!$S)echo"<p class='message'>".lang(9)."\n";else$this->tablesPrint($S);}}}function
databasesPrint($Bd){}function
tablesPrint($T){echo"<p id='tables' onmouseover='menuOver(this, event);' onmouseout='menuOut(this);'>\n";foreach($T
as$L){$D=$this->tableName($L);if(isset($L["Engine"])&&$D!="")echo"<a href='".h(ME).'select='.urlencode($L["Name"])."'".bold($_GET["select"]==$L["Name"]||$_GET["edit"]==$L["Name"],"select")." title='".lang(56)."'>$D</a><br>\n";}}function
_foreignColumn($oc,$e){foreach((array)$oc[$e]as$nc){if(count($nc["source"])==1){$D=$this->rowDescription($nc["table"]);if($D!=""){$u=idf_escape($nc["target"][0]);return
array($nc["table"],$u,$D);}}}}function
_foreignKeyOptions($R,$e,$Y=null){global$h;if(list($uf,$u,$D)=$this->_foreignColumn(column_foreign_keys($R),$e)){$K=&$this->_values[$uf];if($K===null){$S=table_status($uf);$K=($S["Rows"]>1000?"":array(""=>"")+get_key_vals("SELECT $u, $D FROM ".table($uf)." ORDER BY 2"));}if(!$K&&$Y!==null)return$h->result("SELECT $D FROM ".table($uf)." WHERE $u = ".q($Y));return$K;}}}$b=(function_exists('adminer_object')?adminer_object():new
Adminer);function
page_header($Bf,$n="",$Qa=array(),$Cf=""){global$ba,$ca,$b,$Bb,$y;page_headers();if(is_ajax()&&$n){page_messages($n);exit;}$Df=$Bf.($Cf!=""?": $Cf":"");$Ef=strip_tags($Df.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".$b->name());echo'<!DOCTYPE html>
<html lang="',$ba,'" dir="',lang(57),'">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="robots" content="noindex">
<meta name="referrer" content="origin-when-crossorigin">
<title>',$Ef,'</title>
<link rel="stylesheet" type="text/css" href="',h(preg_replace("~\\?.*~","",ME))."?file=default.css&amp;version=4.3.1",'">
<script type="text/javascript" src="',h(preg_replace("~\\?.*~","",ME))."?file=functions.js&amp;version=4.3.1",'"></script>
';if($b->head()){echo'<link rel="shortcut icon" type="image/x-icon" href="',h(preg_replace("~\\?.*~","",ME))."?file=favicon.ico&amp;version=4.3.1",'">
<link rel="apple-touch-icon" href="',h(preg_replace("~\\?.*~","",ME))."?file=favicon.ico&amp;version=4.3.1",'">
';if(file_exists("adminer.css")){echo'<link rel="stylesheet" type="text/css" href="adminer.css">
';}}echo'
<body class="',lang(57),' nojs" onkeydown="bodyKeydown(event);" onclick="bodyClick(event);"',(isset($_COOKIE["adminer_version"])?"":" onload=\"verifyVersion('$ca');\"");?>>
<script type="text/javascript">
document.body.className = document.body.className.replace(/ nojs/, ' js');
var offlineMessage = '<?php echo
js_escape(lang(58)),'\';
</script>

<div id="help" class="jush-',$y,' jsonly hidden" onmouseover="helpOpen = 1;" onmouseout="helpMouseout(this, event);"></div>

<div id="content">
';if($Qa!==null){$A=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($A?$A:".").'">'.$Bb[DRIVER].'</a> &raquo; ';$A=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$O=(SERVER!=""?h(SERVER):lang(59));if($Qa===false)echo"$O\n";else{echo"<a href='".($A?h($A):".")."' accesskey='1' title='Alt+Shift+1'>$O</a> &raquo; ";if($_GET["ns"]!=""||(DB!=""&&is_array($Qa)))echo'<a href="'.h($A."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> &raquo; ';if(is_array($Qa)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> &raquo; ';foreach($Qa
as$z=>$X){$ub=(is_array($X)?$X[1]:h($X));if($ub!="")echo"<a href='".h(ME."$z=").urlencode(is_array($X)?$X[0]:$X)."'>$ub</a> &raquo; ";}}echo"$Bf\n";}}echo"<h2>$Df</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($n);$l=&get_session("dbs");if(DB!=""&&$l&&!in_array(DB,$l,true))$l=null;stop_session();define("PAGE_HEADER",1);}function
page_headers(){global$b;header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");if($b->headers()){header("X-Frame-Options: deny");header("X-XSS-Protection: 0");}}function
page_messages($n){$ag=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$zd=$_SESSION["messages"][$ag];if($zd){echo"<div class='message'>".implode("</div>\n<div class='message'>",$zd)."</div>\n";unset($_SESSION["messages"][$ag]);}if($n)echo"<div class='error'>$n</div>\n";}function
page_footer($Bd=""){global$b,$Gf;echo'</div>

';switch_lang();if($Bd!="auth"){echo'<form action="" method="post">
<p class="logout">
<input type="submit" name="logout" value="',lang(60),'" id="logout">
<input type="hidden" name="token" value="',$Gf,'">
</p>
</form>
';}echo'<div id="menu">
';$b->navigation($Bd);echo'</div>
<script type="text/javascript">setupSubmitHighlight(document);</script>
';}function
int32($Ed){while($Ed>=2147483648)$Ed-=4294967296;while($Ed<=-2147483649)$Ed+=4294967296;return(int)$Ed;}function
long2str($W,$lg){$Le='';foreach($W
as$X)$Le.=pack('V',$X);if($lg)return
substr($Le,0,end($W));return$Le;}function
str2long($Le,$lg){$W=array_values(unpack('V*',str_pad($Le,4*ceil(strlen($Le)/4),"\0")));if($lg)$W[]=strlen($Le);return$W;}function
xxtea_mx($rg,$qg,$of,$Yc){return
int32((($rg>>5&0x7FFFFFF)^$qg<<2)+(($qg>>3&0x1FFFFFFF)^$rg<<4))^int32(($of^$qg)+($Yc^$rg));}function
encrypt_string($jf,$z){if($jf=="")return"";$z=array_values(unpack("V*",pack("H*",md5($z))));$W=str2long($jf,true);$Ed=count($W)-1;$rg=$W[$Ed];$qg=$W[0];$te=floor(6+52/($Ed+1));$of=0;while($te-->0){$of=int32($of+0x9E3779B9);$Fb=$of>>2&3;for($ae=0;$ae<$Ed;$ae++){$qg=$W[$ae+1];$Dd=xxtea_mx($rg,$qg,$of,$z[$ae&3^$Fb]);$rg=int32($W[$ae]+$Dd);$W[$ae]=$rg;}$qg=$W[0];$Dd=xxtea_mx($rg,$qg,$of,$z[$ae&3^$Fb]);$rg=int32($W[$Ed]+$Dd);$W[$Ed]=$rg;}return
long2str($W,false);}function
decrypt_string($jf,$z){if($jf=="")return"";if(!$z)return
false;$z=array_values(unpack("V*",pack("H*",md5($z))));$W=str2long($jf,false);$Ed=count($W)-1;$rg=$W[$Ed];$qg=$W[0];$te=floor(6+52/($Ed+1));$of=int32($te*0x9E3779B9);while($of){$Fb=$of>>2&3;for($ae=$Ed;$ae>0;$ae--){$rg=$W[$ae-1];$Dd=xxtea_mx($rg,$qg,$of,$z[$ae&3^$Fb]);$qg=int32($W[$ae]-$Dd);$W[$ae]=$qg;}$rg=$W[$Ed];$Dd=xxtea_mx($rg,$qg,$of,$z[$ae&3^$Fb]);$qg=int32($W[0]-$Dd);$W[0]=$qg;$of=int32($of-0x9E3779B9);}return
long2str($W,true);}$h='';$Ac=$_SESSION["token"];if(!$Ac)$_SESSION["token"]=rand(1,1e6);$Gf=get_token();$he=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($z)=explode(":",$X);$he[$z]=$X;}}function
add_invalid_login(){global$b;$ec=get_temp_dir()."/adminer.invalid";$tc=@fopen($ec,"r+");if(!$tc){$tc=@fopen($ec,"w");if(!$tc)return;}flock($tc,LOCK_EX);$Uc=unserialize(stream_get_contents($tc));$zf=time();if($Uc){foreach($Uc
as$Vc=>$X){if($X[0]<$zf)unset($Uc[$Vc]);}}$Tc=&$Uc[$b->bruteForceKey()];if(!$Tc)$Tc=array($zf+30*60,0);$Tc[1]++;$We=serialize($Uc);rewind($tc);fwrite($tc,$We);ftruncate($tc,strlen($We));flock($tc,LOCK_UN);fclose($tc);}$Da=$_POST["auth"];if($Da){$Uc=unserialize(@file_get_contents(get_temp_dir()."/adminer.invalid"));$Tc=$Uc[$b->bruteForceKey()];$Hd=($Tc[1]>30?$Tc[0]-time():0);if($Hd>0)auth_error(lang(61,ceil($Hd/60)));session_regenerate_id();$gg=$Da["driver"];$O=$Da["server"];$V=$Da["username"];$H=(string)$Da["password"];$m=$Da["db"];set_password($gg,$O,$V,$H);$_SESSION["db"][$gg][$O][$V][$m]=true;if($Da["permanent"]){$z=base64_encode($gg)."-".base64_encode($O)."-".base64_encode($V)."-".base64_encode($m);$qe=$b->permanentLogin(true);$he[$z]="$z:".base64_encode($qe?encrypt_string($H,$qe):"");cookie("adminer_permanent",implode(" ",$he));}if(count($_POST)==1||DRIVER!=$gg||SERVER!=$O||$_GET["username"]!==$V||DB!=$m)redirect(auth_url($gg,$O,$V,$m));}elseif($_POST["logout"]){if($Ac&&!verify_token()){page_header(lang(60),lang(62));page_footer("db");exit;}else{foreach(array("pwds","db","dbs","queries")as$z)set_session($z,null);unset_permanent();redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),lang(63));}}elseif($he&&!$_SESSION["pwds"]){session_regenerate_id();$qe=$b->permanentLogin();foreach($he
as$z=>$X){list(,$Xa)=explode(":",$X);list($gg,$O,$V,$m)=array_map('base64_decode',explode("-",$z));set_password($gg,$O,$V,decrypt_string(base64_decode($Xa),$qe));$_SESSION["db"][$gg][$O][$V][$m]=true;}}function
unset_permanent(){global$he;foreach($he
as$z=>$X){list($gg,$O,$V,$m)=array_map('base64_decode',explode("-",$z));if($gg==DRIVER&&$O==SERVER&&$V==$_GET["username"]&&$m==DB)unset($he[$z]);}cookie("adminer_permanent",implode(" ",$he));}function
auth_error($n){global$b,$Ac;$Ze=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Ze]||$_GET[$Ze])&&!$Ac)$n=lang(64);else{add_invalid_login();$H=get_password();if($H!==null){if($H===false)$n.='<br>'.lang(65,'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent();}}if(!$_COOKIE[$Ze]&&$_GET[$Ze]&&ini_bool("session.use_only_cookies"))$n=lang(66);$G=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?$_COOKIE["adminer_key"]:rand_string()),$G["lifetime"]);page_header(lang(34),$n,null);echo"<form action='' method='post'>\n";$b->loginForm();echo"<div>";hidden_fields($_POST,array("auth"));echo"</div>\n","</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])){if(!class_exists("Min_DB")){unset($_SESSION["pwds"][DRIVER]);unset_permanent();page_header(lang(67),lang(68,implode(", ",$le)),false);page_footer("auth");exit;}$h=connect();}$Ab=new
Min_Driver($h);if(!is_object($h)||($pd=$b->login($_GET["username"],get_password()))!==true)auth_error((is_string($h)?h($h):(is_string($pd)?$pd:lang(69))));if($Da&&$_POST["token"])$_POST["token"]=$Gf;$n='';if($_POST){if(!verify_token()){$Pc="max_input_vars";$wd=ini_get($Pc);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars")as$z){$X=ini_get($z);if($X&&(!$wd||$X<$wd)){$Pc=$z;$wd=$X;}}}$n=(!$_POST["token"]&&$wd?lang(70,"'$Pc'"):lang(62).' '.lang(71));}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$n=lang(72,"'post_max_size'");if(isset($_GET["sql"]))$n.=' '.lang(73);}if(!ini_bool("session.use_cookies")||@ini_set("session.use_cookies",false)!==false)session_write_close();function
email_header($Bc){return"=?UTF-8?B?".base64_encode($Bc)."?=";}function
send_mail($Jb,$mf,$yd,$uc="",$fc=array()){$Pb=(DIRECTORY_SEPARATOR=="/"?"\n":"\r\n");$yd=str_replace("\n",$Pb,wordwrap(str_replace("\r","","$yd\n")));$Pa=uniqid("boundary");$Aa="";foreach((array)$fc["error"]as$z=>$X){if(!$X)$Aa.="--$Pa$Pb"."Content-Type: ".str_replace("\n","",$fc["type"][$z]).$Pb."Content-Disposition: attachment; filename=\"".preg_replace('~["\\n]~','',$fc["name"][$z])."\"$Pb"."Content-Transfer-Encoding: base64$Pb$Pb".chunk_split(base64_encode(file_get_contents($fc["tmp_name"][$z])),76,$Pb).$Pb;}$Ka="";$Cc="Content-Type: text/plain; charset=utf-8$Pb"."Content-Transfer-Encoding: 8bit";if($Aa){$Aa.="--$Pa--$Pb";$Ka="--$Pa$Pb$Cc$Pb$Pb";$Cc="Content-Type: multipart/mixed; boundary=\"$Pa\"";}$Cc.=$Pb."MIME-Version: 1.0$Pb"."X-Mailer: Adminer Editor".($uc?$Pb."From: ".str_replace("\n","",$uc):"");return
mail($Jb,email_header($mf),$Ka.$yd.$Aa,$Cc);}function
like_bool($o){return
preg_match("~bool|(tinyint|bit)\\(1\\)~",$o["full_type"]);}$h->select_db($b->database());$Nd="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";$Bb[DRIVER]=lang(34);if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["download"])){$a=$_GET["download"];$p=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$N=array(idf_escape($_GET["field"]));$J=$Ab->select($a,$N,array(where($_GET,$p)),$N);$L=($J?$J->fetch_row():array());echo$L[0];exit;}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$p=fields($a);$Z=(isset($_GET["select"])?(count($_POST["check"])==1?where_check($_POST["check"][0],$p):""):where($_GET,$p));$Zf=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($p
as$D=>$o){if(!isset($o["privileges"][$Zf?"update":"insert"])||$b->fieldName($o)=="")unset($p[$D]);}if($_POST&&!$n&&!isset($_GET["select"])){$od=$_POST["referer"];if($_POST["insert"])$od=($Zf?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$od))$od=ME."select=".urlencode($a);$x=indexes($a);$Uf=unique_array($_GET["where"],$x);$we="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($od,lang(74),$Ab->delete($a,$we,!$Uf));else{$P=array();foreach($p
as$D=>$o){$X=process_input($o);if($X!==false&&$X!==null)$P[idf_escape($D)]=$X;}if($Zf){if(!$P)redirect($od);queries_redirect($od,lang(75),$Ab->update($a,$P,$we,!$Uf));if(is_ajax()){page_headers();page_messages($n);exit;}}else{$J=$Ab->insert($a,$P);$jd=($J?last_id():0);queries_redirect($od,lang(76,($jd?" $jd":"")),$J);}}}$L=null;if($_POST["save"])$L=(array)$_POST["fields"];elseif($Z){$N=array();foreach($p
as$D=>$o){if(isset($o["privileges"]["select"])){$za=convert_field($o);if($_POST["clone"]&&$o["auto_increment"])$za="''";if($y=="sql"&&preg_match("~enum|set~",$o["type"]))$za="1*".idf_escape($D);$N[]=($za?"$za AS ":"").idf_escape($D);}}$L=array();if(!support("table"))$N=array("*");if($N){$J=$Ab->select($a,$N,array($Z),$N,array(),(isset($_GET["select"])?2:1));$L=$J->fetch_assoc();if(!$L)$L=false;if(isset($_GET["select"])&&(!$L||$J->fetch_assoc()))$L=null;}}if(!support("table")&&!$p){if(!$Z){$J=$Ab->select($a,array("*"),$Z,array("*"));$L=($J?$J->fetch_assoc():false);if(!$L)$L=array($Ab->primary=>"");}if($L){foreach($L
as$z=>$X){if(!$Z)$L[$z]=null;$p[$z]=array("field"=>$z,"null"=>($z!=$Ab->primary),"auto_increment"=>($z==$Ab->primary));}}}edit_form($a,$p,$L,$Zf);}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$x=indexes($a);$p=fields($a);$pc=column_foreign_keys($a);$Md="";if($S["Oid"]){$Md=($y=="sqlite"?"rowid":"oid");$x[]=array("type"=>"PRIMARY","columns"=>array($Md));}parse_str($_COOKIE["adminer_import"],$sa);$Je=array();$f=array();$xf=null;foreach($p
as$z=>$o){$D=$b->fieldName($o);if(isset($o["privileges"]["select"])&&$D!=""){$f[$z]=html_entity_decode(strip_tags($D),ENT_QUOTES);if(is_shortable($o))$xf=$b->selectLengthProcess();}$Je+=$o["privileges"];}list($N,$s)=$b->selectColumnsProcess($f,$x);$Wc=count($s)<count($N);$Z=$b->selectSearchProcess($p,$x);$E=$b->selectOrderProcess($p,$x);$_=$b->selectLimitProcess();$uc=($N?implode(", ",$N):"*".($Md?", $Md":"")).convert_fields($f,$p,$N)."\nFROM ".table($a);$wc=($s&&$Wc?"\nGROUP BY ".implode(", ",$s):"").($E?"\nORDER BY ".implode(", ",$E):"");if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Vf=>$L){$za=convert_field($p[key($L)]);$N=array($za?$za:idf_escape(key($L)));$Z[]=where_check($Vf,$p);$K=$Ab->select($a,$N,$Z,$N);if($K)echo
reset($K->fetch_row());}exit;}if($_POST&&!$n){$ng=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Va=array();foreach($_POST["check"]as$Sa)$Va[]=where_check($Sa,$p);$ng[]="((".implode(") OR (",$Va)."))";}$ng=($ng?"\nWHERE ".implode(" AND ",$ng):"");$ne=$Xf=null;foreach($x
as$w){if($w["type"]=="PRIMARY"){$ne=array_flip($w["columns"]);$Xf=($N?$ne:array());break;}}foreach((array)$Xf
as$z=>$X){if(in_array(idf_escape($z),$N))unset($Xf[$z]);}if($_POST["export"]){cookie("adminer_import","output=".urlencode($_POST["output"])."&format=".urlencode($_POST["format"]));dump_headers($a);$b->dumpTable($a,"");if(!is_array($_POST["check"])||$Xf===array())$I="SELECT $uc$ng$wc";else{$Tf=array();foreach($_POST["check"]as$X)$Tf[]="(SELECT".limit($uc,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$p).$wc,1).")";$I=implode(" UNION ALL ",$Tf);}$b->dumpData($a,"table",$I);exit;}if(!$b->selectEmailProcess($Z,$pc)){if($_POST["save"]||$_POST["delete"]){$J=true;$ta=0;$P=array();if(!$_POST["delete"]){foreach($f
as$D=>$X){$X=process_input($p[$D]);if($X!==null&&($_POST["clone"]||$X!==false))$P[idf_escape($D)]=($X!==false?$X:idf_escape($D));}}if($_POST["delete"]||$P){if($_POST["clone"])$I="INTO ".table($a)." (".implode(", ",array_keys($P)).")\nSELECT ".implode(", ",$P)."\nFROM ".table($a);if($_POST["all"]||($Xf===array()&&is_array($_POST["check"]))||$Wc){$J=($_POST["delete"]?$Ab->delete($a,$ng):($_POST["clone"]?queries("INSERT $I$ng"):$Ab->update($a,$P,$ng)));$ta=$h->affected_rows;}else{foreach((array)$_POST["check"]as$X){$mg="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$p);$J=($_POST["delete"]?$Ab->delete($a,$mg,1):($_POST["clone"]?queries("INSERT".limit1($I,$mg)):$Ab->update($a,$P,$mg)));if(!$J)break;$ta+=$h->affected_rows;}}}$yd=lang(77,$ta);if($_POST["clone"]&&$J&&$ta==1){$jd=last_id();if($jd)$yd=lang(76," $jd");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$yd,$J);if(!$_POST["delete"]){edit_form($a,$p,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$n=lang(78);else{$J=true;$ta=0;foreach($_POST["val"]as$Vf=>$L){$P=array();foreach($L
as$z=>$X){$z=bracket_escape($z,1);$P[idf_escape($z)]=(preg_match('~char|text~',$p[$z]["type"])||$X!=""?$b->processInput($p[$z],$X):"NULL");}$J=$Ab->update($a,$P," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($Vf,$p),!($Wc||$Xf===array())," ");if(!$J)break;$ta+=$h->affected_rows;}queries_redirect(remove_from_uri(),lang(77,$ta),$J);}}elseif(!is_string($dc=get_file("csv_file",true)))$n=upload_error($dc);elseif(!preg_match('~~u',$dc))$n=lang(79);else{cookie("adminer_import","output=".urlencode($sa["output"])."&format=".urlencode($_POST["separator"]));$J=true;$db=array_keys($p);preg_match_all('~(?>"[^"]*"|[^"\\r\\n]+)+~',$dc,$C);$ta=count($C[0]);$Ab->begin();$Te=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$M=array();foreach($C[0]as$z=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$Te]*)$Te~",$X.$Te,$td);if(!$z&&!array_diff($td[1],$db)){$db=$td[1];$ta--;}else{$P=array();foreach($td[1]as$t=>$bb)$P[idf_escape($db[$t])]=($bb==""&&$p[$db[$t]]["null"]?"NULL":q(str_replace('""','"',preg_replace('~^"|"$~','',$bb))));$M[]=$P;}}$J=(!$M||$Ab->insertUpdate($a,$M,$ne));if($J)$J=$Ab->commit();queries_redirect(remove_from_uri("page"),lang(80,$ta),$J);$Ab->rollback();}}}$rf=$b->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(46).": $rf",$n);$P=null;if(isset($Je["insert"])||!support("table")){$P="";foreach((array)$_GET["where"]as$X){if(count($pc[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&!preg_match('~[_%]~',$X["val"]))))$P.="&set".urlencode("[".bracket_escape($X["col"])."]")."=".urlencode($X["val"]);}}$b->selectLinks($S,$P);if(!$f&&support("table"))echo"<p class='error'>".lang(81).($p?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?'<input type="hidden" name="db" value="'.h(DB).'">'.(isset($_GET["ns"])?'<input type="hidden" name="ns" value="'.h($_GET["ns"]).'">':""):"");echo'<input type="hidden" name="select" value="'.h($a).'">',"</div>\n";$b->selectColumnsPrint($N,$f);$b->selectSearchPrint($Z,$f,$x);$b->selectOrderPrint($E,$f,$x);$b->selectLimitPrint($_);$b->selectLengthPrint($xf);$b->selectActionPrint($x);echo"</form>\n";$F=$_GET["page"];if($F=="last"){$sc=$h->result(count_rows($a,$Z,$Wc,$s));$F=floor(max(0,$sc-1)/$_);}$Qe=$N;if(!$Qe){$Qe[]="*";if($Md)$Qe[]=$Md;}$kb=convert_fields($f,$p,$N);if($kb)$Qe[]=substr($kb,2);$J=$Ab->select($a,$Qe,$Z,$s,$E,$_,$F,true);if(!$J)echo"<p class='error'>".error()."\n";else{if($y=="mssql"&&$F)$J->seek($_*$F);$Lb=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$M=array();while($L=$J->fetch_assoc()){if($F&&$y=="oracle")unset($L["RNUM"]);$M[]=$L;}if($_GET["page"]!="last"&&+$_&&$s&&$Wc&&$y=="sql")$sc=$h->result(" SELECT FOUND_ROWS()");if(!$M)echo"<p class='message'>".lang(12)."\n";else{$Ja=$b->backwardKeys($a,$rf);echo"<table id='table' cellspacing='0' class='nowrap checkable' onclick='tableClick(event);' ondblclick='tableClick(event, true);' onkeydown='return editingKeydown(event);'>\n","<thead><tr>".(!$s&&$N?"":"<td><input type='checkbox' id='all-page' onclick='formCheck(this, /check/);' class='jsonly'> <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".lang(82)."</a>");$Fd=array();$vc=array();reset($N);$ye=1;foreach($M[0]as$z=>$X){if($z!=$Md){$X=$_GET["columns"][key($N)];$o=$p[$N?($X?$X["col"]:current($N)):$z];$D=($o?$b->fieldName($o,$ye):($X["fun"]?"*":$z));if($D!=""){$ye++;$Fd[$z]=$D;$e=idf_escape($z);$Gc=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($z);$ub="&desc%5B0%5D=1";echo'<th onmouseover="columnMouse(this);" onmouseout="columnMouse(this, \' hidden\');">','<a href="'.h($Gc.($E[0]==$e||$E[0]==$z||(!$E&&$Wc&&$s[0]==$e)?$ub:'')).'">';echo
apply_sql_function($X["fun"],$D)."</a>";echo"<span class='column hidden'>","<a href='".h($Gc.$ub)."' title='".lang(83)."' class='text'> â†“</a>";if(!$X["fun"])echo'<a href="#fieldset-search" onclick="selectSearch(\''.h(js_escape($z)).'\'); return false;" title="'.lang(41).'" class="text jsonly"> =</a>';echo"</span>";}$vc[$z]=$X["fun"];next($N);}}$md=array();if($_GET["modify"]){foreach($M
as$L){foreach($L
as$z=>$X)$md[$z]=max($md[$z],min(40,strlen(utf8_decode($X))));}}echo($Ja?"<th>".lang(84):"")."</thead>\n";if(is_ajax()){if($_%2==1&&$F%2==1)odd();ob_end_clean();}foreach($b->rowDescriptions($M,$pc)as$Ed=>$L){$Uf=unique_array($M[$Ed],$x);if(!$Uf){$Uf=array();foreach($M[$Ed]as$z=>$X){if(!preg_match('~^(COUNT\\((\\*|(DISTINCT )?`(?:[^`]|``)+`)\\)|(AVG|GROUP_CONCAT|MAX|MIN|SUM)\\(`(?:[^`]|``)+`\\))$~',$z))$Uf[$z]=$X;}}$Vf="";foreach($Uf
as$z=>$X){if(($y=="sql"||$y=="pgsql")&&strlen($X)>64){$z=(strpos($z,'(')?$z:idf_escape($z));$z="MD5(".($y=='sql'&&preg_match("~^utf8_~",$p[$z]["collation"])?$z:"CONVERT($z USING ".charset($h).")").")";$X=md5($X);}$Vf.="&".($X!==null?urlencode("where[".bracket_escape($z)."]")."=".urlencode($X):"null%5B%5D=".urlencode($z));}echo"<tr".odd().">".(!$s&&$N?"":"<td>".checkbox("check[]",substr($Vf,1),in_array(substr($Vf,1),(array)$_POST["check"]),"","this.form['all'].checked = false; formUncheck('all-page');").($Wc||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$Vf)."'>".lang(85)."</a>"));foreach($L
as$z=>$X){if(isset($Fd[$z])){$o=$p[$z];if($X!=""&&(!isset($Lb[$z])||$Lb[$z]!=""))$Lb[$z]=(is_mail($X)?$Fd[$z]:"");$A="";if(preg_match('~blob|bytea|raw|file~',$o["type"])&&$X!="")$A=ME.'download='.urlencode($a).'&field='.urlencode($z).$Vf;if(!$A&&$X!==null){foreach((array)$pc[$z]as$q){if(count($pc[$z])==1||end($q["source"])==$z){$A="";foreach($q["source"]as$t=>$df)$A.=where_link($t,$q["target"][$t],$M[$Ed][$df]);$A=($q["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\\1'.urlencode($q["db"]),ME):ME).'select='.urlencode($q["table"]).$A;if($q["ns"])$A=preg_replace('~([?&]ns=)[^&]+~','\\1'.urlencode($q["ns"]),$A);if(count($q["source"])==1)break;}}}if($z=="COUNT(*)"){$A=ME."select=".urlencode($a);$t=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Uf))$A.=where_link($t++,$W["col"],$W["val"],$W["op"]);}foreach($Uf
as$Yc=>$W)$A.=where_link($t++,$Yc,$W);}$X=select_value($X,$A,$o,$xf);$u=h("val[$Vf][".bracket_escape($z)."]");$Y=$_POST["val"][$Vf][bracket_escape($z)];$Hb=!is_array($L[$z])&&is_utf8($X)&&$M[$Ed][$z]==$L[$z]&&!$vc[$z];$wf=preg_match('~text|lob~',$o["type"]);if(($_GET["modify"]&&$Hb)||$Y!==null){$yc=h($Y!==null?$Y:$L[$z]);echo"<td>".($wf?"<textarea name='$u' cols='30' rows='".(substr_count($L[$z],"\n")+1)."'>$yc</textarea>":"<input name='$u' value='$yc' size='$md[$z]'>");}else{$qd=strpos($X,"<i>...</i>");echo"<td id='$u' onclick=\"selectClick(this, event, ".($qd?2:($wf?1:0)).($Hb?"":", '".h(lang(86))."'").");\">$X";}}}if($Ja)echo"<td>";$b->backwardKeysPrint($Ja,$M[$Ed]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n";}if(($M||$F)&&!is_ajax()){$Tb=true;if($_GET["page"]!="last"){if(!+$_)$sc=count($M);elseif($y!="sql"||!$Wc){$sc=($Wc?false:found_rows($S,$Z));if($sc<max(1e4,2*($F+1)*$_))$sc=reset(slow_query(count_rows($a,$Z,$Wc,$s)));else$Tb=false;}}if(+$_&&($sc===false||$sc>$_||$F)){echo"<p class='pages'>";$ud=($sc===false?$F+(count($M)>=$_?2:1):floor(($sc-1)/$_));if($y!="simpledb"){echo'<a href="'.h(remove_from_uri("page"))."\" onclick=\"pageClick(this.href, +prompt('".lang(87)."', '".($F+1)."'), event); return false;\">".lang(87)."</a>:",pagination(0,$F).($F>5?" ...":"");for($t=max(1,$F-4);$t<min($ud,$F+5);$t++)echo
pagination($t,$F);if($ud>0){echo($F+5<$ud?" ...":""),($Tb&&$sc!==false?pagination($ud,$F):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$ud'>".lang(88)."</a>");}echo(($sc===false?count($M)+1:$sc-$F*$_)>$_?' <a href="'.h(remove_from_uri("page")."&page=".($F+1)).'" onclick="return !selectLoadMore(this, '.(+$_).', \''.lang(89).'...\');" class="loadmore">'.lang(90).'</a>':'');}else{echo
lang(87).":",pagination(0,$F).($F>1?" ...":""),($F?pagination($F,$F):""),($ud>$F?pagination($F+1,$F).($ud>$F+1?" ...":""):"");}}echo"<p class='count'>\n",($sc!==false?"(".($Tb?"":"~ ").lang(91,$sc).") ":"");$zb=($Tb?"":"~ ").$sc;echo
checkbox("all",1,0,lang(92),"var checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$zb' : checked); selectCount('selected2', this.checked || !checked ? '$zb' : checked);")."\n";if($b->selectCommandPrint()){echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>',lang(82),'</legend><div>
<input type="submit" value="',lang(14),'"',($_GET["modify"]?'':' title="'.lang(78).'"'),'>
</div></fieldset>
<fieldset><legend>',lang(93),' <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="',lang(10),'">
<input type="submit" name="clone" value="',lang(94),'">
<input type="submit" name="delete" value="',lang(18),'"',confirm(),'>
</div></fieldset>
';}$qc=$b->dumpFormat();foreach((array)$_GET["columns"]as$e){if($e["fun"]){unset($qc['sql']);break;}}if($qc){print_fieldset("export",lang(95)." <span id='selected2'></span>");$Zd=$b->dumpOutput();echo($Zd?html_select("output",$Zd,$sa["output"])." ":""),html_select("format",$qc,$sa["format"])," <input type='submit' name='export' value='".lang(95)."'>\n","</div></fieldset>\n";}echo(!$s&&$N?"":"<script type='text/javascript'>tableCheck();</script>\n");}if($b->selectImportPrint()){print_fieldset("import",lang(96),!$M);echo"<input type='file' name='csv_file'> ",html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$sa["format"],1);echo" <input type='submit' name='import' value='".lang(96)."'>","</div></fieldset>\n";}$b->selectEmailPrint(array_filter($Lb,'strlen'),$f);echo"<p><input type='hidden' name='token' value='$Gf'></p>\n","</form>\n";}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["script"])){if($_GET["script"]=="kill")$h->query("KILL ".number($_POST["kill"]));elseif(list($R,$u,$D)=$b->_foreignColumn(column_foreign_keys($_GET["source"]),$_GET["field"])){$_=11;$J=$h->query("SELECT $u, $D FROM ".table($R)." WHERE ".(preg_match('~^[0-9]+$~',$_GET["value"])?"$u = $_GET[value] OR ":"")."$D LIKE ".q("$_GET[value]%")." ORDER BY 2 LIMIT $_");for($t=1;($L=$J->fetch_row())&&$t<$_;$t++)echo"<a href='".h(ME."edit=".urlencode($R)."&where".urlencode("[".bracket_escape(idf_unescape($u))."]")."=".urlencode($L[0]))."'>".h($L[1])."</a><br>\n";if($L)echo"...\n";}exit;}else{page_header(lang(59),"",false);if($b->homepage()){echo"<form action='' method='post'>\n","<p>".lang(97).": <input name='query' value='".h($_POST["query"])."'> <input type='submit' value='".lang(41)."'>\n";if($_POST["query"]!="")search_tables();echo"<table cellspacing='0' class='nowrap checkable' onclick='tableClick(event);'>\n",'<thead><tr class="wrap"><td><input id="check-all" type="checkbox" onclick="formCheck(this, /^tables\[/);" class="jsonly"><th>'.lang(98).'<td>'.lang(99)."</thead>\n";foreach(table_status()as$R=>$L){$D=$b->tableName($L);if(isset($L["Engine"])&&$D!=""){echo'<tr'.odd().'><td>'.checkbox("tables[]",$R,in_array($R,(array)$_POST["tables"],true),"","formUncheck('check-all');"),"<th><a href='".h(ME).'select='.urlencode($R)."'>$D</a>";$X=format_number($L["Rows"]);echo"<td align='right'><a href='".h(ME."edit=").urlencode($R)."'>".($L["Engine"]=="InnoDB"&&$X?"~ $X":$X)."</a>";}}echo"</table>\n","<script type='text/javascript'>tableCheck();</script>\n","</form>\n";}}page_footer();