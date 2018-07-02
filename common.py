from datetime import datetime,timedelta
import time

def pr(typ,msg,dbg):
	if dbg:
		if dbg_sw:
			print("-"+typ+"- "+msg)
	else:
			print("-"+typ+"- "+msg)
	return
dbg_sw  	= 1    

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