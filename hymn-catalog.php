<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=21600');
$url='https://hymnstogod.org/Hymns-PD/ZZ-CompletePDHymnList.html';
$ctx=stream_context_create(['http'=>['timeout'=>12,'user_agent'=>'LordOfTheSabbathChurch/1.0'],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);
$html=@file_get_contents($url,false,$ctx);
if($html===false){http_response_code(502);echo json_encode(['ok'=>false,'hymns'=>[]]);exit;}
libxml_use_internal_errors(true);
$dom=new DOMDocument();$dom->loadHTML($html);$xp=new DOMXPath($dom);
$out=[];
foreach($xp->query('//a[@href]') as $a){
  $href=trim($a->getAttribute('href'));$title=trim(preg_replace('/\s+/u',' ',$a->textContent));
  if(!$title||stripos($href,'Hymns-PD/')===false&& !preg_match('~(?:^|/)\w+-Hymns/[^/]+\.html(?:$|\?)~i',$href))continue;
  if(stripos($title,'Hymn')!==false&&preg_match('/^(A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z)\s*-\s*Hymns$/i',$title))continue;
  if(stripos($href,'ZZ-CompletePDHymnList')!==false)continue;
  $next=$a->nextSibling;$credit='';
  for($n=0;$n<4&&$next;$n++,$next=$next->nextSibling){$credit.=is_object($next)?$next->textContent:(string)$next;}
  $credit=trim(preg_replace('/\s+/u',' ',$credit));
  if(strlen($credit)>180)$credit=substr($credit,0,180);
  $abs=preg_match('~^https?://~i',$href)?$href:'https://hymnstogod.org/Hymns-PD/'.ltrim($href,'/');
  $key=strtolower($title.'|'.$abs);$out[$key]=['title'=>$title,'credit'=>$credit,'source'=>$abs];
}
$hymns=array_values($out);usort($hymns,fn($a,$b)=>strnatcasecmp($a['title'],$b['title']));
echo json_encode(['ok'=>true,'count'=>count($hymns),'hymns'=>$hymns],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
?>