import pymysql  as sql
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

	return


def fetch_url(url):
	ret = {}
	r = req.get(url)
	if(r.status_code == 200):
		pr("I","Fetching Successful",1);
		data = r.text
		ret  = data.split("\n")
	else:
		pr("I","Fetching Failed",1);
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
	qry    = "SELECT * FROM gainers";
	try:
	    cursor.execute(qry)
	    results = cursor.fetchall()
	    for row in results:
	        st = str(row[0])+"_"+str(row[1])
	        db_gainers[st] = 1
	except:
	    print("-E- Error: unable to fecth data")
	    s.exit()
	pr("I","Loading DB Data For Losers",0)   
	qry    = "SELECT * FROM losers";
	try:
	    cursor.execute(qry)
	    results = cursor.fetchall()
	    for row in results:
	        st = str(row[0])+"_"+str(row[1])
	        db_losers[st] = 1
	except:
	    print("-E- Error: unable to fecth data")
	    s.exit()
	db_obj.close()
	return

#DB Functions
def sql_conn():
	db = sql.connect("localhost","root","","stock")
	return db

def rcnt(qry):
    db_obj  = sql_conn()
    cursor  = db_obj.cursor()
    rows    = 0
    try:
        cursor.execute(qry)
        rows = cursor.rowcount
    except (sql.Error, sql.Warning) as e:
        print("-E- Query Failed")   
        print(e)
        db_obj.rollback()
    return rows

def execQuery(qry):
    pr("S","Executing Query "+qry,1)
    db_obj  = sql_conn()
    cursor  = db_obj.cursor()
    try:
        cursor.execute(qry)
        db_obj.commit()
    except (sql.Error, sql.Warning) as e:
        print("-E- Query Failed")   
        print(e)
        db_obj.rollback() 
    return
#Code Execution Starts Here
dbg_sw  	= 1
prs_pg  	= 1
prs_rc  	= {}
db_gainers 	= {}
db_losers  	= {}

gainers 	= {}
losers  	= {}
init()
#print(gainers)
#Code Execution Ends Here