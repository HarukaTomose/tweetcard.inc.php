<?php
/////////////////////////////////////////////////
// PukiWiki  - Yet another WikiWikiWeb clone.
//
// $Id: tweetcard.inc.php,v 0.22 2026/09/28 Haruka Tomose $
//
// Twitter Card を作成するプラグイン。
// とりあえず「summary」しかサポートしていません。
// 公式（英語）
// https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/summary
// 日本語ではこちらのページの解説がわかりやすい。
// https://saruwakakun.com/html-css/reference/twitter-card
//
// Card の性格上、サイト運営者が事前に内容編集しておく前提。
//----
// 導入前作業
// 定数３つを設定すること。
//サイト設置者のTwitterID。友瀬の場合 @Tomose。
define('PLUGIN_TWEETCARD_TWITTERID', ''); 

// 設置サイトの名称。友瀬の場合 Tomose's Junkyard など）
define('PLUGIN_TWEETCARD_SITENAME', ""); 

// デフォルトで表示するアイコン画像。https://tomose.net/favicong.png のようにフルパス指定
// 設定しないことも許容されるが、その際は各ページに画像添付＆引数わたし。
define('PLUGIN_TWEETCARD_DEFAULT_IMAGE', ''); 


//----
// Usage.
// #tweetcard(img[,description])
// img ：添付ファイル名。これがTweetのアイコンとして表示される。
//       PLUGIN_TWEETCARD_DEFAULT_IMAGE設定時は省略可能。
// description : 当該ページの概要.
// ----
// ・友瀬の pageinfo プラグイン対応。Titleとdescription の自動生成。
// 　desc は上記プラグインでの指定を優先。/

function plugin_tweetcard_convert()
{
	global $head_tags,$vars,$script,$page;

	// 1ページ内での複数使用対応のお約束。
	static $TWEETCARDnumber = array();
	if (! isset($TWEETCARDnumber[$page])) $TWEETCARDnumber[$page] = 0; // Init
	$tweetcard_no= $TWEETCARDnumber[$page]++; // これがこのObject の識別番号になる。

	// ２つ目以降の tweetcard は、何もしない。
	if($tweetcard_no>0) { return ''; }

	$num = func_num_args();
	//if ($num ==0) { return 'Usage: #tweetcard(<img>[,description])'; }

	$work='';
	$args = func_get_args();

	// 第一パラメータに"NULL" と明示した場合、tweetcard は何もしない。
	if($args[0]=='NULL') { return ''; }
	
	//第一パラメータを省略した場合。デフォルト値を利用。
	if(($args[0]=='DEFAULT')or($args[0]=='')) {
		$args[0]= PLUGIN_TWEETCARD_DEFAULT_IMAGE;
		if($args[0]=='') { return 'Usage: #tweetcard(<img>[,description])'; }
	}

	$contents = array_map('htmlsc',$args); // パラメータすべてのサニタイズ。

	if( is_url($contents[0]) ) {
		$_img = $contents[0];
	}else{
		$_img = $script . '?plugin=ref' . '&page=' . rawurlencode($vars['page']) .'&src=' . rawurlencode($contents[0]);
	}

	if (file_exists(PLUGIN_DIR.'pageinfo.inc.php')) {
		require_once PLUGIN_DIR.'pageinfo.inc.php';
		$_title = pageinfo_get_title($vars['page']);
		if($num>1){
			$_desc = $contents[1];
		}else{
			$_desc = pageinfo_get_description($vars['page']);
		}
	}else{
		$_title = $vars['page'];
		$_desc = $contents[1];
	}

// 自分の TwitterIDを設定する（友瀬の場合 @Tomose )
$_site = PLUGIN_TWEETCARD_TWITTERID;
// 設置しているサイトの名称（友瀬の場合 Tomose's Junkyard など）
$_content = PLUGIN_TWEETCARD_SITENAME;

$work =<<<EOD
<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="$_site">
<meta name="twitter:title" content="$_title">
<meta name="twitter:description" content="$_desc">
<meta property="og:title" content="$_title">
<meta property="og:description" content="$_desc">
<meta property="og:image" content="$_img">
<meta property="og:type" content="article">
<meta property="og:site_name" content="$_content">
EOD;

	$head_tags[] = $work; // pukiwiki でヘッダに情報追加したい場合の変数

	// このプラグイン自体は直接表示するものはない。
	return '';
}
?>
