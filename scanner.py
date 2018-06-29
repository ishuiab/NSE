import sql as s
import requests as req
import json as js
import sys
import time
import datetime as dt
import re
import html
import urllib.request
import os
import zipfile
import time
from pytz import timezone,utc
from datetime import datetime,timedelta

def init():
	pr("I","Initializing Script",1)
	pr("I","Reading Path"+raw_path,1)
	files = fetch_files(raw_path)
	for f in files:
		pr("I","Processing file "+f,1)
		print("-I- Start Time -> "+str(datetime.now()))		
		process_file(f)
	return

def process_file(file):
	fobj = open(raw_path+"\\"+file,"r")
	data = (fobj.read()).split("\n")
	sql_hash = []
	for line in data:
		tmp_str  = line.split(",")
		if len(tmp_str) > 6:
			tbl      = tmp_str[0].lower()
			dt		 = tmp_str[1][:4] +"-"+tmp_str[1][4:6]+"-"+tmp_str[1][6:8]+" "+tmp_str[2]+":00"
			o 		 = tmp_str[3]
			l 		 = tmp_str[4]
			h 		 = tmp_str[5]
			c 		 = tmp_str[6]
			dt_obj = datetime.strptime(dt, "%Y-%m-%d %H:%M:%S")
			tstamp = str(time.mktime(dt_obj.timetuple()))[:-2]
			qry    = "('"+dt+"','"+tstamp+"',"+o+","+l+","+h+","+c+",0)"
			sql_hash.append(qry)
	if(len(sql_hash)):
		s.sql_insert(tbl,"time,timestamp,open,low,high,close,volume",sql_hash,500)
		print("-I- End Time -> "+str(datetime.now()))	
		
	print("-I- Sleeping For 2 Seconds")
	time.sleep(2)
	return

def fetch_time(time_key):
	dt_obj = datetime.strptime(time_key, "%Y-%m-%d %H:%M:%S")
	ltz    = timezone('Asia/Kolkata')
	dt_ist = ltz.localize(dt_obj)
	#dt_ist = dt_obj.astimezone(timezone('Asia/Kolkata'))
	tstamp = ""
	if is_dst(dt_obj):
		dt_ist = dt_ist + timedelta(hours=9,minutes=30)
	else:
		dt_ist = dt_ist + timedelta(hours=8,minutes=30)
	dt_str = dt_ist.strftime('%Y-%m-%d %H:%M:%S')
	tstamp = str(time.mktime(dt_ist.timetuple()))
	tstamp = tstamp[:-2]
	return dt_str,tstamp
def fetch_files(raw_path):
	from os import listdir
	from os.path import isfile, join
	onlyfiles = [f for f in listdir(raw_path) if isfile(join(raw_path, f))]
	return onlyfiles

def fetch_url(url):
	ret = {}
	r = req.get(url)
	if(r.status_code == 200):
		pr("I","Fetching Successful",1)
		data = r.text
		ret  = data.split("\n")
	else:
		pr("I","Fetching Failed",1)
		exit()
	return ret

def pr(typ,msg,dbg):
	if dbg:
		if dbg_sw:
			print("-"+typ+"- "+msg)
	else:
			print("-"+typ+"- "+msg)
	return

def load_db_data():
	pr("I","Loading DB Data For Gainers",0)
	db_obj  = sql_conn()
	cursor  = db_obj.cursor()
	qry    = "SELECT * FROM gainers"
	try:
	    cursor.execute(qry)
	    results = cursor.fetchall()
	    for row in results:
	        st = str(row[0])+"_"+str(row[1])
	        #db_gainers[st] = 1
	except:
	    print("-E- Error: unable to fecth data")
	    s.exit()
	pr("I","Loading DB Data For Losers",0)   
	qry    = "SELECT * FROM losers"
	try:
	    cursor.execute(qry)
	    results = cursor.fetchall()
	    for row in results:
	        st = str(row[0])+"_"+str(row[1])
	        #db_losers[st] = 1
	except:
	    print("-E- Error: unable to fecth data")
	    s.exit()
	db_obj.close()
	return

#DB Functions

#Code Execution Starts Here
dbg_sw  	= 1
raw_path = "C:\\Zerodha\\Pi\\Exported"
init()
#print(gainers)
#Code Execution Ends Here