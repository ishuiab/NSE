import sql
import requests as req
import json as js
import sys as s
import time
import datetime as dt
import re
import html
import urllib.request
import os
import zipfile

def init():
	pr("I","Initializing Script",1)
	pr("I","Reading Path"+raw_path,1)
	files = fetch_files(raw_path)
	for f in files:
		pr("I","Processing file "+f,1)
		
	return

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