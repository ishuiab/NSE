import sql as s
import common as c
import sys
import time
import simulation as sim
import numpy as np
from strategy import ohl
from datetime import datetime,timedelta
from pytz import timezone,utc


def tester(strategy,capital,star_param,bulk_id):
    star_id = c.gen_id("strategy","strategy_id")
    c.pr("I","Testing Strategy "+strategy.upper()+" with capital "+str(capital)+" Strategy ID -> "+star_id,1)
    s.execQuery("INSERT INTO strategy VALUES ('"+strategy.upper()+"','"+star_id+"','"+bulk_id+"','"+str(star_param).replace("'","''")+"')")
    star_param['ID'] = star_id
    eval(strategy)(capital,star_param)
    return

#Testing part

star_param = {}
star_param['MAX']     = 15    #Maximum Data points to check 
star_param['THR']     = 14 
star_param['VAR']     = 0.001 #Maximum Variance in price
star_param['START']   = 151477753 #Starting Point June 1
star_param['SL']      = 0.004
star_param['T1']      = 0.008
star_param['T2']      = 0.010
star_param['SC']      = "NIFTY"
#print(c.fetch_scrip_data("NIFTY","1518148860","1518126000"))
#tester("ohl",150000,star_param,"NULL")
#sim.display_stats("QTJE0UFK")

def bulk_test():
    #OHL Bulk Test
    #Variance Range between 0.001 to 0.003
    var_range = np.arange(0.001,0.004,0.001)
    sl_range  = np.arange(0.002,0.006,0.001)
    t1_range  = np.arange(0.004,0.010,0.001)
    t2_range  = np.arange(0.006,0.012,0.001)
    md_range  = range(10,20)
    ctr       = 0
    for var in var_range:
        var = round(var,3)
        for sl in sl_range:
            sl = round(sl,3)
            for t1 in t1_range:
                t1 = round(t1,3)
                for t2 in t2_range:
                    t2 = round(t2,3)
                    for md in md_range: 
                        star_param['MAX']     = md    
                        star_param['THR']     = md - 1
                        star_param['VAR']     = var 
                        star_param['START']   = 151477753
                        star_param['SL']      = sl
                        star_param['T1']      = t1
                        star_param['T2']      = t2
                        star_param['SC']      = "NIFTY"
                        ctr = ctr + 1
    print(str(ctr))
    return

bulk_test()
