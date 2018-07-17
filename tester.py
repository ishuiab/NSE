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
star_param['MAX']     = 15    #Maximum Data points to check 
star_param['THR']     = 14 
star_param['VAR']     = 0.001 #Maximum Variance in price
star_param['START']   = 1514777537 #Starting Point June 1
star_param['SL']      = 0.0015 
star_param['T1']      = 0.003
star_param['T2']      = 0.004
tester("ohl",150000,star_param)