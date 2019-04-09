<?php
ini_set('max_execution_time', 5000);
require_once("config/config.php");
init();
function init()
    {
		    $t_date = date("Ymd");
        $query = "SELECT * FROM lucifer.runs WHERE status = 'NEW' AND date <= $t_date";
        foreach(select_mysql($query,"date") as $date)
            {
                $months = array("01"=>"January","02"=>"February","03"=>"March","04"=>"April","05"=>"May","06"=>"June","07"=>"July","08"=>"August","09"=>"September","10"=>"October","11"=>"November","12"=>"December");
                $year   = substr($date,0,4);
                $month  = substr($date,4,2);
                $day    = substr($date,6,2);
                $s_date = "$day $months[$month] $year";
                print $s_date."\n";
                $query = "UPDATE lucifer.runs set status='FETCH' WHERE date = '$t_date'";
                #read_mail($s_date,$date);
                exit();
            }
    }
function read_mail($date,$t_date)
    {
        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        $username = 'skastores@gmail.com';
        $password = 'P@$$w0rds';
        $mail_body=  "";
        date_default_timezone_set('Asia/Kolkata');
        /* try to connect */
        #print $date;
        $inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
        $emails = imap_search($inbox,'FROM "ebay@ebay.in" SINCE "'.$date.'"');
         foreach($emails as $mail)
            {
                $headerInfo = imap_headerinfo($inbox,$mail);
                $mst_date   = date("Ymd",strtotime($headerInfo->date));

                if($mst_date == $t_date)
                    {
                              $mail_body        .=   "SUBJECT_MAIN ".$headerInfo->subject."\n";
                              $emailStructure   =    imap_fetchstructure($inbox,$mail);
                              $mail_body        .=   imap_body($inbox, $mail, FT_PEEK);
                              $mail_body        .=    "\nMAIL_END_HERE\n";
                    }
                else
                    {
                        store_file($t_date,$mail_body);
                        break;
                    }

            }

    }
function store_file($t_date,$mail_body)
    {
        $file = fopen("raw\\$t_date","w");
        fwrite($file,"$mail_body");
        fclose($file);
        $query = "UPDATE lucifer.runs set status='RAW' WHERE date = '$t_date'";
        insert_mysql($query);
    }
?>
