<?php
if(!$_SESSION['ID'])
    exit();

include("footer_menu.php");
?>
<h5><?php echo $StrCourseName ?>, National Taiwan University.</h5>
<h5>Instructor: <?php echo $InstName;?>.</h5>
<h5>Programmer: 劉邦鋒 pangfeng, 上官林傑 ericsk, 陳映睿 springgod, 王尹 cindylinz, 蕭俊宏 chhsiao, 鍾以千 ckclark.</h5>
<h5>Art Design: 施光祖 shihkt.</h5>
<h5>有任何使用上的問題請來信詢問現任助教:
<?php 
    $TaNameAry = preg_split('/,\\s*/', $TAnames);
    $TaMailAry = preg_split('/,\\s*/', $TAemails);
    $TaInfo = array();
    for($i = 0; $i < count($TaNameAry); $i++){
        $TaInfo[] = "<a href='mailto:$TaMailAry[$i]'>$TaNameAry[$i]</a>";
    }
    echo implode(",\n ", $TaInfo);
?>
