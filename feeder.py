import sql as s
import common as c

#Load all the scrips first
def load_scrips():
	global scrips
	c.pr("I","Loading All Scrips",0)
	scrips = s.sql_hash("scrips","scrip","sector:status:is_fetch")
	return

def init():
	load_scrips()
	check_tables()
	load_scrips()
	return	

def check_tables():
	c.pr("I","Checking If Destination Table's Exists",0)
	for scrip in scrips:
		c.pr("I","Checking For Table "+scrip,1)
		qry = "SELECT * FROM information_schema.tables WHERE table_schema = 'stocki'  AND table_name = '"+scrip+"' LIMIT 1;"
		if s.rcnt(qry):
			c.pr("I",scrip+" Table Exists",1)
		else:
			c.pr("I",scrip+" Table Needs To Be Created",1)
			s.create_table(scrip,"time:DT,timestamp:TS,open:FL,low:FL,high:FL,close:FL,volume:IN")
	return
#Script Flow Starts Here

scrips 		= {}
init()
#Flow End Here.