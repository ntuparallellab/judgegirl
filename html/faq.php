<?php
include("config.php");

session_start();

if(!isset($_SESSION["ID"]))
    exit("Please <a href='index.htm'>login</a> first.\n");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<title><?php echo $StrCourseName; ?> FAQ</title>
<style type="text/css">
td{
    padding-left: 8px;
    padding-right: 8px;
}
</style>
</head>
<body background="images/back.gif">
<div id="content">
<h2><?php echo $StrCourseName; ?> FAQ</h2>
<?php include("menu.php"); include ("announce.php"); ?>
<hr>
<ul>
<li>批改娘說我的程式碼不符合題目題要求
<p>通常這個問題表示你在寫的是 Debug 題，同時你更動的 byte 數超過限制的上限，所以無法通過測驗。這個機制是希望你們要真的用 bug code 去找出問題得分，而不是自己重寫一個上傳。</p>
<li>更動 byte 數(edit distance) 是怎麼計算的
<p>所謂的 edit distance 是將上傳的原始碼和修改前的視作兩個 byte array 去計算 LCS(longest common subsequence)，扣掉相同的部份，剩下不同之處的數目即是所謂的 edit distance, 因此每增加或刪減一個字元都會列入計算。同時這個計算方式不會因為你在中間插入一個字就讓後面的程式碼也被算成修改。</p>
</li>
<li>我覺得我有符合題目更改 byte 數的限制，但批改娘還是不讓我過...
<p>有些編緝器會會把 Tab 縮排換成空白字元(或是反過來)，基於上述的機制會被當作有更動 byte ，僅管你看起來沒有變化。一個 tab 換成 8 個空白算更動 9 bytes，所以請避免這種情況。<br />
<br />
<strong>Updated</strong>: 經查證後，確認系統在計算 edit distance 之前會把所有的 '\t', '\n', '\r' ' ' 拿掉，所以不會有上述問題。</p>
</ul>
<hr>
<?php include("footnote.php"); ?>
</div>
<?php
$volume = $ProblemVolume;
include("balloon.php");
?>
</body>
</html>
