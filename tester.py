import sql as s
import common as c
import sys
import time
from strategy import ohl
from datetime import datetime,timedelta
from pytz import timezone,utc


def tester(strategy,capital,star_param):
    c.pr("I","Testing Strategy "+strategy+" with capital "+str(capital),1)
    eval(strategy)(capital,star_param)
    return

#Testing part
star_param = {}
star_param['MAX']     = 10 
star_param['THR']     = 14 
star_param['VAR']     = 0.001 #Maximum Variance in price
star_param['START']   = 1527823800 #Starting Point June 1
tester("ohl",10000,star_param)