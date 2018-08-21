import sql as s
import common as c
import requests as req
import json as js
import time
from pytz import timezone,utc
from datetime import datetime,timedelta
import sys
import os.path

#Load all the scrips first
def load_scrips():
	global scrips
	c.pr("I","Loading All Scrips",0)
	scrips = s.sql_hash("scrips","scrip","sector:status:is_fetch:search","")
	return

def init():
	#load_scrips()
	#check_tables()
	load_scrips()
	fetch_local_data()
	#fetch_data()
	return	

def fetch_local_data():
	for scrip in scrips:
		typs = {"EQ":scrip,"FUT":scrip+"_FUT"}
		c.pr("I","Processing For scrip --> "+scrip,1)
		for typ in typs:
			#months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC']
			months = ['JUL']
			for mon in months:
				data = fetch_csv(scrip,typ,"2018",mon)
				if(len(data)):
					tbl = typs[typ]
					store_data(data,tbl)
					clean_data(tbl)
	return

def fetch_csv(scrip,typ,year,month):
	sname 	   = scrip
	data_map   = {}
	if typ == "FUT":
		sname = sname + "_F1"
	fpath = "C:\\Users\\ssadiq\\Downloads\\oneminutedata\\"+year+"\\"+month+"\\NIFTY50_"+month+year+"\\"+sname+".txt"
	if os.path.exists(fpath):
		c.pr("I","File Path Exists -> "+fpath,1)
		fobj  = open(fpath,"r")
		lines = (fobj.read()).split("\n")
		for line in lines:
			tmp_str  = line.split(",")
			if len(tmp_str) == 8:
				dt	   = tmp_str[1][:4] +"-"+tmp_str[1][4:6]+"-"+tmp_str[1][6:8]+" "+tmp_str[2]+":00"
				o 	   = tmp_str[3]
				h 	   = tmp_str[4]
				l 	   = tmp_str[5]
				cl 	   = tmp_str[6]
				v      = tmp_str[7]
				dt_obj = datetime.strptime(dt, "%Y-%m-%d %H:%M:%S")
				tstamp = str(time.mktime(dt_obj.timetuple()))[:-2]

				data_map[tstamp]	  = {}
				data_map[tstamp]['D'] = dt
				data_map[tstamp]['O'] = o
				data_map[tstamp]['L'] = l
				data_map[tstamp]['H'] = h
				data_map[tstamp]['C'] = cl
				data_map[tstamp]['V'] = v
				#qry    = "('"+dt+"','"+tstamp+"',"+o+","+l+","+h+","+cl+","+v+")"
				#ret.append(qry)
	else:
		c.pr("W","File Path Does Not Exists -> "+fpath,1)
	
	return data_map

def clean_data(scrip):
	c.pr("I","Performing Clean Up Opearations For Scrip "+scrip,1)
	fix_missing_entries(scrip)
	return

def fix_missing_entries(scrip):
	c.pr("I","Fixing Missing Entries For Scrip "+scrip,1)
	uniq_dates = s.sql_array("SELECT DISTINCT CAST(`time` AS DATE) AS dateonly FROM `"+scrip+"`","dateonly")
	for date in uniq_dates:
		dp_req = fetch_dp_req(str(date),scrip)
		db_dp  = s.sql_hash(scrip,"timestamp","close","WHERE `time` BETWEEN '"+str(date)+" 09:16:00' AND '"+str(date)+" 15:30:00'")
		dp_cur = len(db_dp)
		dp_mis = (dp_req - dp_cur)
		dp_map = {}
		if dp_mis > 1:
			c.pr("I","DATE --> "+str(date)+" DP REQ --> "+str(dp_req)+" DP CUR --> "+str(dp_cur)+" DP MIS --> "+str(dp_mis),1)
			#Here We attempt to fix DP
			dp_min  = int(c.get_timestamp(str(date)+" 09:16:00"))
			dp_max  = int(c.get_timestamp(str(date)+" 15:30:00"))
			#c.pr("I","DP MIN ---> "+str(dp_min)+"  DP MAX ---> "+str(dp_max),1)
			dp_chk  = dp_min
			ctr = 1
			dp_last = 0
			while dp_chk != (dp_max+60):
				if not str(dp_chk) in db_dp:
					#If MIN AND CHK Are Same
					if dp_chk == dp_min:
						 c.pr("I",str(dp_chk)+" ---> MIN MISSING",1)
						 #exit()
					else:
						if str((dp_chk - 60)) in db_dp:
							#Case Where Previous Data point exists
							dp_prev = db_dp[str((dp_chk - 60))]['close']
							#print(str(dp_chk)+"  ---> PREV PRESENT"+" DP PREV ---> "+str(dp_prev))
							dp_map[str(dp_chk)] = process_missing(dp_prev,dp_chk)
						else:
							#print(str(dp_chk)+"  ---> PREV MISSISNG"+" DP PREV ---> "+str(dp_last))
							if dp_last:
								dp_prev = db_dp[str(dp_last)]['close']
								dp_map[str(dp_chk)] = process_missing(dp_prev,dp_chk)
							#print(str(dp_chk)+"  ---> PREV MISSISNG"+" DP PREV ---> "+str(dp_prev))
				else:
					dp_last = dp_chk			
				dp_chk  = (dp_chk+60)
			if len(dp_map):
				store_data(dp_map,scrip)
	return

def process_missing(dv,ts):
	data_map = {}
	#print(str(dv)+" <----> "+str(ts))
	data_map['D'] = str(datetime.fromtimestamp(ts).strftime('%Y-%m-%d %H:%M:%S'))
	data_map['O'] = str(dv)
	data_map['L'] = str(dv)
	data_map['H'] = str(dv)
	data_map['C'] = str(dv)
	data_map['V'] = str(0)
	return data_map

def fetch_dp_req(date,scrip):
	#c.pr("I","Fetching Data Points Required For Scrip "+scrip+" On "+str(date) ,1)
	dp_req 	 = 0
	st_dat   = datetime.today().strftime('%Y-%m-%d')
	if st_dat != date:
		dp_req = 375
	else:
		dp_last  = s.sql_single("SELECT DATE_FORMAT(`time`, '%H:%i:%s') AS tme FROM `"+scrip+"` WHERE `time` > '"+date+" 09:15:00' ORDER BY `time` DESC LIMIT 1")
		dp_delta = datetime.strptime(dp_last,'%H:%M:%S') - datetime.strptime('09:15:00','%H:%M:%S')
		dp_req   = int(dp_delta.seconds/60)
	return dp_req

def fetch_data():
	for scrip in scrips:
		#data = fetch_json(scrip)
		#process_data(data,scrip)
		clean_data(scrip)
	return

def fetch_json(scrip):
	c.pr("I","Fetching Data For Scrip "+scrip,1)
	#Determine output size full or compact
	osize = detos(scrip)
	data  = {}
	if osize != "NONE":
		API_LINK = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&interval=1min&apikey=MCAF9B429I44328U&symbol="+scrips[scrip]['search']+"&outputsize="+osize
		c.pr("I","API Link -> "+API_LINK,1)	
		try:
			r = req.get(API_LINK)
			if(r.status_code == 200):
				data = r.json()
			else:
				c.pr("I","Unable to Fetch Data HTTP STATUS CODE -> "+ r.status_code,0)
		except Exception as e:
			c.pr("E","Exception Occured "+str(e),0)
	else:
		c.pr("I","No Need To Call API As Data Points Are Populated",1)
		data['NONE'] = 1
	return data

def detos(scrip):
	osize = ""
	st_dat   = datetime.today().strftime('%Y-%m-%d')
	nw_dat   = datetime.today().strftime('%H:%M')
	st_dat   = st_dat+' 09:15:00'
	stqry    = "SELECT * FROM `"+scrip+"` WHERE `time` > '"+st_dat+"'"
	dp_cnt   = s.rcnt(stqry)
	dp_delta = datetime.strptime(nw_dat+":00",'%H:%M:%S') - datetime.strptime('09:15:00','%H:%M:%S')
	dp_req   = int(dp_delta.seconds/60)
	if dp_req > 375:
		dp_req = 375
	dp_mis   = int(dp_req - dp_cnt)
	osize = ""
	if dp_cnt == 0:
		osize = "full"

	if dp_mis > 100:
		osize = "full"

	if dp_mis < 100:
		osize = "compact"

	if dp_mis < 1:
		osize = "NONE"

	c.pr("I","Data Points Availiable -> "+str(dp_cnt) +" Data Points Required -> "+str(dp_req)+" Data Points Missing -> "+str(dp_mis)+" Output Size -> "+osize,1)
	sys.exit()
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
			#print("EST Time -> "+tk+"   IST Time -> "+dt_str+"  Time Stamp -> "+tstamp+"  Open -> "+opn+"  High -> "+hig+"  Low -> "+low+" Close -> "+clo+ " Volume -> "+vlm)
			#sys.exit()
			data_map[tstamp]	  = {}
			data_map[tstamp]['D'] = dt_str
			data_map[tstamp]['O'] = opn
			data_map[tstamp]['L'] = low
			data_map[tstamp]['H'] = hig
			data_map[tstamp]['C'] = clo
			data_map[tstamp]['V'] = vlm
		store_data(data_map,scrip)
	else:
		if not "NONE" in data:
			c.pr("I","Results Fetched Failed",1)
			print(data)
		
	return

def store_data(data_map,scrip):
	#Retuns the records which are not in DB
	final_map = sanitize(data_map,scrip)
	c.pr("I","Storing Data For "+scrip,1)
	sql_hash = []
	for key in final_map:
		sql_ins = "('"+data_map[key]['D']+"','"+key+"',"+data_map[key]['O']+","+data_map[key]['L']+","+data_map[key]['H']+","+data_map[key]['C']+","+data_map[key]['V']+")"
		sql_hash.append(sql_ins)
	s.sql_insert(scrip,"time,timestamp,open,low,high,close,volume",sql_hash,500)
	return 

def sanitize(data_map,scrip):
	c.pr("I","Sanitizing Data For "+scrip,1)
	final_map = []
	db_data   = s.sql_hash(scrip,"timestamp","volume","")
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
	dt_ist = dt_obj.astimezone(timezone('Asia/Kolkata'))
	tstamp = ""
	if is_dst(dt_obj):
		dt_ist = dt_ist + timedelta(hours=9,minutes=30)
	else:
		dt_ist = dt_ist + timedelta(hours=8,minutes=30)
	dt_str = dt_ist.strftime('%Y-%m-%d %H:%M:%S')
	tstamp = str(time.mktime(dt_ist.timetuple()))
	tstamp = tstamp[:-2]
	return dt_str,tstamp

def get_table(file):
	fname = file[:-4]
	tname = ""
	scrip = ""
	if fname[-2:] == "F1":
		scrip = fname[:-3]
		tname = fname[:-3].lower()
		tname = tname + "_FUT"
	else:
		scrip = fname
		tname = fname.lower()
	return scrip,tname

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
		
		qry = "SELECT * FROM information_schema.tables WHERE table_schema = 'stocki'  AND table_name = '"+scrip+"_FUT' LIMIT 1;"
		if s.rcnt(qry):
			c.pr("I",scrip+"_FUT Table Exists",1)
		else:
			c.pr("I",scrip+"_FUT Table Needs To Be Created",1)
			s.create_table(scrip+"_FUT","time:DT,timestamp:VC:15,open:FL,low:FL,high:FL,close:FL,volume:IN")
	return
#Script Flow Starts Here
scrips 		= {}
#clean_data("hdil_FUT")
init()
#Flow End Here.

#files 		= c.fetch_files("C:\\Users\\ssadiq\\Downloads\\oneminutedata\\2018\\JAN\\NIFTY50_JAN2018\\NIFTY50_JAN2018")
#for f in files:
	#c.pr("I","Processing file "+f,1)
#	scrip,tname = get_table(f)
#	if tname[-3:] != "FUT":
#		print(scrip+" <----> "+tname)
#		qry = "INSERT INTO scrips VALUES ('"+scrip+"','NONE','YES','yes','"+scrip+"')"
#		print(qry)
#		s.execQuery(qry)