import sql as s
import common as c
import requests as req
import json as js
import time
from pytz import timezone,utc
from datetime import datetime,timedelta

#Load all the scrips first
def load_scrips():
	global scrips
	c.pr("I","Loading All Scrips",0)
	scrips = s.sql_hash("scrips","scrip","sector:status:is_fetch:search")
	return

def init():
	#load_scrips()
	#check_tables()
	load_scrips()
	fetch_data()
	return	

def fetch_data():
	for scrip in scrips:
		c.pr("I","Fetching Data For Script "+scrip,1)
		#https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=ABB.NS&interval=1min&apikey=MCAF9B429I44328U
		API_LINK = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&interval=1min&apikey=MCAF9B429I44328U&symbol="+scrips[scrip]['search']
		#!!!Replace this code with fetching data from URL
		FP = "C:\\Users\\ssadiq\\Documents\\NSE\\test_data\\ABB.json"
		with open(FP) as f:
			data = js.load(f)
		#!!----------------------------------------------
		c.pr("I","API Link -> "+API_LINK,1)
		process_data(data,scrip)
	return

def process_data(data,scrip):
	#Check if meta data is returned
	if "Meta Data" in data:
		c.pr("I","Results Fetched Successfully",1)
		ac_data   = data["Time Series (1min)"]
		time_keys = ac_data.keys()
		data_map  = {}
		for tk in time_keys:
			dt_str,tstamp 		= fetch_time(tk)
			opn,hig,low,clo,vlm = fetch_param(ac_data[tk])
			#print("Time -> "+dt_str+"  Time Stamp -> "+tstamp+"  Open -> "+opn+"  High -> "+hig+"  Low -> "+low+" Close -> "+clo+ " Volume -> "+vlm)
			data_map[tstamp]	  = {}
			data_map[tstamp]['D'] = dt_str
			data_map[tstamp]['O'] = opn
			data_map[tstamp]['L'] = low
			data_map[tstamp]['H'] = hig
			data_map[tstamp]['C'] = clo
			data_map[tstamp]['V'] = vlm
		store_data(data_map,scrip)
	else:
		c.pr("I","Results Fetched Failed",1)
	exit()	
	return

def store_data(data_map,scrip):
	#Retuns the records which are not in DB
	final_map = sanitize(data_map,scrip)
	c.pr("I","Storing Data For "+scrip,1)
	sql_hash = []
	for key in final_map:
		sql_ins = "('"+data_map[key]['D']+"','"+key+"',"+data_map[key]['O']+","+data_map[key]['L']+","+data_map[key]['H']+","+data_map[key]['C']+","+data_map[key]['V']+")"
		sql_hash.append(sql_ins)
	s.sql_insert(scrip,"time,timestamp,open,low,high,close,volume",sql_hash,20)
	return 

def sanitize(data_map,scrip):
	c.pr("I","Sanitizing Data For "+scrip,1)
	final_map = []
	db_data   = s.sql_hash(scrip,"timestamp","volume")
	for tk in data_map:
		if tk not in db_data:
			final_map.append(tk)
	return final_map

def fetch_param(ac_data):
	opn   = ac_data['1. open']
	hig   = ac_data['2. high']
	low   = ac_data['3. low']
	clo   = ac_data['4. close']
	vlm   = ac_data['5. volume']
	return opn,hig,low,clo,vlm

def fetch_time(time_key):
	dt_obj = datetime.strptime(time_key, "%Y-%m-%d %H:%M:%S")
	dt_est = dt_obj.replace(tzinfo=timezone('US/Eastern'))
	dt_ist = dt_est.astimezone(timezone('Asia/Kolkata'))
	tstamp = ""
	if is_dst(dt_obj):
		dt_ist = dt_ist - timedelta(hours=1)
	dt_str = dt_ist.strftime('%Y-%m-%d %H:%M:%S')
	tstamp = str(time.mktime(dt_ist.timetuple()))
	tstamp = tstamp[:-2]
	return dt_str,tstamp

def is_dst(tzdata):
    tz  = timezone('US/Eastern')
    now = utc.localize(tzdata)
    return now.astimezone(tz).dst() != timedelta(0)

def check_tables():
	c.pr("I","Checking If Destination Table's Exists",0)
	for scrip in scrips:
		#c.pr("I","Checking For Table "+scrip,1)
		qry = "SELECT * FROM information_schema.tables WHERE table_schema = 'stocki'  AND table_name = '"+scrip+"' LIMIT 1;"
		if s.rcnt(qry):
			c.pr("I",scrip+" Table Exists",1)
		else:
			c.pr("I",scrip+" Table Needs To Be Created",1)
			s.create_table(scrip,"time:DT,timestamp:VC:15,open:FL,low:FL,high:FL,close:FL,volume:IN")
	return
#Script Flow Starts Here

scrips 		= {}
init()
#Flow End Here.