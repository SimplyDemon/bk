<?php
session_start();
$msg="";
$error="";
include "conf.php";

$chas = date("H");
$server_date=date("d.m.Y", mktime($chas-$GSM));
$server_time=date("H:i:s", mktime($chas-$GSM));

$data = mysql_connect($base_name, $base_user, $base_pass);
mysql_query("SET CHARSET cp1251");
    if(!mysql_select_db($db_name,$data)
        ){
     print "Ошибка при подключении к БД<BR>";

     echo mysql_error();
     die();
    }
if ($stat[blocked]=="1") echo"<script>top.location='index.php?action=logout'</script>";

if ($stat[t_time]) { header("Location: prison.php"); exit; }
elseif ($stat[battle]) { header("Location: battle.php"); exit; }


else {

//Обвенчать
if ($_POST['obvin']){
    if ($stat['admin_level']!=1)
        $error = "У вас нет прав!";
    elseif (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)|(\<>)|(\|(\<)|(\>)|(\%3B)|(\")|]/",$_POST['muj']))
        $error = "Логин Мужа имеет запрещенные символы.";
    elseif (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)|(\<>)|(\|(\<)|(\>)|(\%3B)|(\")|]/",$_POST['jena']))
        $error = "Логин Жены имеет запрещенные символы.";
    elseif (trim($_POST['muj'])=="" || trim($_POST['jena'])=="")
        $error = "Пустое поле.";
    elseif ($_POST['muj']==$_POST['jena'])
        $error = "Одинаковые Имена";
    else {
        $muj = mysql_fetch_array(mysql_query("SELECT * FROM characters user, sex, semija, credits  where login='$login'"));
        $jena = mysql_fetch_array(mysql_query("select user, sex, semija from characters where login='$login'"));

        if (!$muj['login'])
           $error= 'Игрок "'.$_POST['muj'].'" не найден.';
        elseif (!$jena['login'])
           $error= 'Игрок "'.$_POST['jena'].'" не найден.';
        elseif ($muj['sex']!=1)
           $error = "У Мужа должен быть мужской пол";
        elseif ($jena['sex']!=2)
           $error = "У Жены должен быть женский пол";
        elseif ($muj['semija'])
           $error= 'Игрок "'.$_POST['muj'].'" уже женат';
        elseif ($jena['semija'])
           $error= 'Игрок "'.$_POST['jena'].'" уже замужем';
        elseif ($muj['credits']<10)
           $error = "Нахватает денег";
        else{
            $mu = mysql_query("UPDATE `characters` SET credits=credits-10,semija='".$jena['login']."' WHERE user='".$muj['user']."'");
            $jen = mysql_query("UPDATE `characters` SET semija='".$muj['login']."' WHERE user='".$jena['login']."'");
            if($mu && $jen){
                $msg = "Брак успешна состоялся.";
            }else{
                $error = "Брак не состоялся.";
            }
        }
    }

}
// END


//Развод
if ($_POST['razv']){
    if ($stat['admin_level']!=0)
        $error = "У вас нет прав!";
    elseif (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)|(\<>)|(\|(\<)|(\>)|(\%3B)|(\")|]/",$_POST['login']))
        $error = "Логин имеет запрещенные символы.";
    elseif (trim($_POST['login'])=="")
        $error = "Пустое поле.";
    else {
        $razv = mysql_fetch_array(mysql_query("select login, semija, credits from characters where login='$login'"));

        if (!$razv['login'])
           $error= 'Игрок "'.$_POST['login'].'" не найден.';
        elseif(!$razv['semija'])
           $error= 'Игрок "'.$_POST['login'].'" не состоит в браке.';
        elseif ($razv['credits']<10)
           $error = "Нахватает денег";
        else{
            $raz = mysql_query("UPDATE `characters` SET credits=credits-10,semija='' WHERE user='".$razv['login']."'");
            $raz2 = mysql_query("UPDATE `characters` SET semija='' WHERE user='".$razv['semija']."'");
            if($raz && $raz2){
                $msg = "Развод состоялся.";
            }else{
                $error = "Развод не состоялся.";
            }
        }
    }

}
// END



        echo"<link rel=stylesheet type='text/css' href='i/shop.css'>
        <BODY bgcolor=#EBEDEC leftmargin=0 topmargin=0>

        <table width=100% cellspacing=0 cellpadding=5 border=0>
        <tr>
        <td align=right valign=top>

        <img src='i/refresh.gif' style='CURSOR: Hand' alt='Обновить' onclick='window.location.href=\"brak.php?tmp=\"+Math.random();\"\"'>

        <img src='i/back.gif' style='CURSOR: Hand' alt='Вернуться' onclick='location.href=\"main.php?act=go&room_go=centplosh\"'>&nbsp;

        </td>
        </tr>
        </table>";

        echo"<table width=100% cellspacing=0 cellpadding=3 border=0>
        <tr>
        <td align=right style='padding-left: 20px'>
        <center><font class=title>Дворец Бракосочетания</font></center><br>";

        if (!empty($error)) echo"<center><FONT COLOR=RED><b>$error</b></font></center><BR>";
        if (!empty($msg)) echo"<center><font color=green><b>$msg</b></font></center><br>";

        if ($stat['admin_level']!=0)
            echo "<fieldset style='WIDTH: 100%; margin-right:20; float:left'><br><center><FONT COLOR=RED><b>Регестрировать брак может тока \"Администратор\"</b></font></center><br></fieldset><br><br>";
        else {
            echo "<fieldset style='WIDTH: 45%; margin-right:20; float:left'><legend>Обвенчать</legend>
            <table width=100% cellspacing=0 cellpadding=5>
             <tr>
             <td align=center>
            <form method='POST' action='' method=post style='margin:0; padding:0;'>
                <table cellspacing=0 cellpadding=5 style='border-style: outset; border-width: 2' border=1>
                <tr>
                    <td align='center'>Муж <input type='text' class=input name='muj' size='20'> Жена <input type='text' class=input name='jena' size='20'> <input type='submit' class=input value='Обвенчать' name='obvin'></td>
                </tr>
                <tr>
                    <td align='center'>Стоимость брака <b>10 зм.</b> (Берутся с Мужа)</td>
                </tr>
                </table>
            </form>
            </td>
             </tr>
             </table>
            </fieldset>";
            echo "<fieldset style='WIDTH: 45%; margin-right:20; float:left'><legend>Развести</legend>
            <table width=100% cellspacing=0 cellpadding=5>
             <tr>
             <td align=center>
            <form method='POST' action='' method=post style='margin:0; padding:0;'>
                <table cellspacing=0 cellpadding=5 style='border-style: outset; border-width: 2' border=1>
                <tr>
                    <td align='center'>Логин <input type='text' class=input name='login' size='20'> <input type='submit' class=input value='Развести' name='razv'></td>
                </tr>
                <tr>
                    <td align='center'>Стоимость развода <b>10 зм.</b></td>
                </tr>
                </table>
            </form>
            </td>
             </tr>
             </table>
            </fieldset>";
        }



        echo"
        </td>
        </tr>
        </table>";

}

?>