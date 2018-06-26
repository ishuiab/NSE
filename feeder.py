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
def clean_data(scrip):
	c.pr("I","Performing Clean Up Opearations For Scrip "+scrip,1)
	fix_missing_entries(scrip)
	return

def fix_missing_entries(scrip):
	c.pr("I","Fixing Missing Entries For Scrip "+scrip,1)
	return

def fetch_data():
	for scrip in scrips:
		#UNCOMMENT data = fetch_json(scrip)
		#UNCOMMENT process_data(data,scrip)
		clean_data(scrip)
	return

def fetch_json(scrip):
	c.pr("I","Fetching Data For Scrip "+scrip,1)
	#Determine output size full or compact
	osize = detos(scrip)
	data  = {}
	API_LINK = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&interval=1min&apikey=MCAF9B429I44328U&symbol="+scrips[scrip]['search']+"&outputsize="+osize
	c.pr("I","API Link -> "+API_LINK,1)	
	r = req.get(API_LINK)
	if(r.status_code == 200):
		data = r.json()
	else:
		c.pr("I","Unable to Fetch Data HTTP STATUS CODE -> "+ r.status_code,0)
	return data

def detos(scrip):
	osize = ""
	st_dat   = datetime.today().strftime('%Y-%m-%d')
	nw_dat   = datetime.today().strftime('%H:%M')
	st_dat   = st_dat+' 09:00:15'
	stqry    = "SELECT * FROM "+scrip+" WHERE `time` > '"+st_dat+"'"
	dp_cnt   = s.rcnt(stqry)
	dp_delta = datetime.strptime(nw_dat+":00",'%H:%M:%S') - datetime.strptime('09:15:00','%H:%M:%S')
	dp_req   = int(dp_delta.seconds/60)
	dp_mis   = int(dp_req - dp_cnt)
	osize = ""
	if dp_cnt == 0:
		osize = "full"

	if dp_mis > 100:
		osize = "full"

	if dp_mis < 100:
		osize = "compact"
	c.pr("I","Data Points Availiable -> "+str(dp_cnt) +" Data Points Required -> "+str(dp_req)+" Data Points Missing -> "+str(dp_mis)+" Output Size -> "+osize,1)
	return osize

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
	#exit()	
	return

def store_data(data_map,scrip):
	#Retuns the records which are not in DB
	final_map = sanitize(data_map,scrip)
	c.pr("I","Storing Data For "+scrip,1)
	sql_hash = []
	for key in final_map:
		sql_ins = "('"+data_map[key]['D']+"','"+key+"',"+data_map[key]['O']+","+data_map[key]['L']+","+data_map[key]['H']+","+data_map[key]['C']+","+data_map[key]['V']+")"
		sql_hash.append(sql_ins)
	s.sql_insert(scrip,"time,timestamp,open,low,high,close,volume",sql_hash,10)
	return 

def sanitize(data_map,scrip):
	c.pr("I","Sanitizing Data For "+scrip,1)
	final_map = []
	db_data   = s.sql_hash("`"+scrip+"`","timestamp","volume")
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