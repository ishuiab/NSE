import sql
import common as c

#Load all the scrips first
def load_scrips():
	query = "SELECT * FROM scrips"
	c.pr("I","Loading All Scrips",1)
	
	return



def init():
	load_scrips()
	return	

#Script Flow Starts Here

#scrips 		= {}
init()
#Flow End Here.