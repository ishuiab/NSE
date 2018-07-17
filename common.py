from datetime import datetime,timedelta
import time
import collections
import sql as s
dbg_sw  	= 1   

def pr(typ,msg,dbg):
	if dbg:
		if dbg_sw:
			print("-"+typ+"- "+msg)
	else:
			print("-"+typ+"- "+msg)
	return
 
def get_timestamp(date):
	tstamp = ""
	dobj   = datetime.strptime(date, "%Y-%m-%d %H:%M:%S")
	tstamp = str(time.mktime(dobj.timetuple()))
	tstamp = tstamp[:-2]
	return tstamp

def fetch_files(raw_path):
	from os import listdir
	from os.path import isfile, join
	onlyfiles = [f for f in listdir(raw_path) if isfile(join(raw_path, f))]
	return onlyfiles

def load_scrips():
	scrips = {}
	pr("I","Loading All Scrips",0)
	scrips = s.sql_hash("scrips","scrip","sector:status:is_fetch:search","WHERE scrip LIKE 'BAJAJ%'")
	return scrips

def fetch_scrip_data(scrip,start,end):
	end_date = ""
	if end:
		end_date = " AND timestamp <= '"+end+"'"

	pr("I","Fetching data for scrip "+scrip,1)
	ret = s.sql_hash(scrip,"timestamp","open:low:high:close:volume","WHERE timestamp >= '"+str(start)+"'"+end_date+" ORDER BY timestamp")
	return ret

def split_data(data,limit):
	odata = collections.OrderedDict(sorted(data.items()))
	prev  = 0
	diff  = 0
	spl   = {}
	ret   = {}
	ctr   = 0
	for cur in data:
		spl[cur] = data[cur]
		if prev:
			diff = (int(cur) - int(prev)) 
		if diff > limit:
			ctr = ctr + 1
			#print("SPLIT PREV --> "+str(prev)+ "  CUR ---> "+str(cur)+"   DIFF ---> "+str(diff)+"  CTR ---> "+str(ctr))
			ret[ctr]  = spl
			spl 	  = {}
			spl[cur]  = data[cur]
		prev = cur
	if len(spl):
		ctr = ctr + 1 
		#print("SPLIT PREV --> "+str(prev)+ "  CUR ---> "+str(cur)+"   DIFF ---> "+str(diff)+"  CTR ---> "+str(ctr))
		ret[ctr] = spl
	#print(len(ret))
	return ret

def get_date(timestamp):
	date = datetime.fromtimestamp(
        int(timestamp)
    ).strftime('%Y-%m-%d %H:%M:%S')
	return date

def get_time(timestamp):
	time = datetime.fromtimestamp(
        int(timestamp)
    ).strftime('%H:%M:%S')
	return time

def get_only_date(timestamp):
	date = datetime.fromtimestamp(
        int(timestamp)
    ).strftime('%Y-%m-%d')
	return date

def intrafy(data):
	ret   = {}
	keys  = list(data.keys())
	first = keys[0]
	last  = keys[len(keys)-2]
	keys.pop()
	#pr("I","FIRST -> "+get_date(first)+" LAST -> "+get_date(last),1)
	for k in keys:
		tm = int((get_time(k).replace(":",""))[:-2])
		if tm >= 915 and tm <= 1515:
			ret[k] = data[k]
			#print(str(tm)+" <--------> "+k)
	return ret