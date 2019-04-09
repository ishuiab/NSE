<?php
require_once("config/config.php");
fetch_inventory();
 /*       $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        $username = 'skastores@gmail.com';
        $password = 'P@$$w0rds';
        $mail_body=  "";
        date_default_timezone_set('Asia/Kolkata');
        /* try to connect 
        
        $inbox  = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
        $emails = imap_search($inbox,'FROM "ebay@ebay.in" SINCE "22 December 2016"');
         foreach($emails as $mail){
                $headerInfo = imap_headerinfo($inbox,$mail);
                $mst_date   = date("Ymd",strtotime($headerInfo->date));
                #print $headerInfo->subject."\n";
                print $headerInfo->date."\n";
                #if($mst_date == $t_date){
                              //$mail_body        .=   "SUBJECT_MAIN ".$headerInfo->subject."\n";
                              //$emailStructure   =    imap_fetchstructure($inbox,$mail);
                              //$mail_body        .=   imap_body($inbox, $mail, FT_PEEK);
                              //$mail_body        .=    "\nMAIL_END_HERE\n";
                #}
                #else{
                #        store_file($t_date,quoted_printable_decode($mail_body));
                #        break;
                #}

            }
*/
?>