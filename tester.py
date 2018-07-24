import sql as s
import common as c
import threading
import numpy as np
import sys
import time
from strategy import ohl
from datetime import datetime,timedelta
from pytz import timezone,utc

def tester(strategy,capital,star_param,bulk_id):
    star_id = c.gen_id("strategy","strategy_id")
    c.pr("I","Testing Strategy "+strategy.upper()+" with capital "+str(capital)+" Strategy ID -> "+star_id,1)
    star_param['ID'] = star_id
    s.execQuery("INSERT INTO strategy VALUES ('"+strategy.upper()+"','"+star_id+"','"+bulk_id+"','"+str(star_param).replace("'","''")+"')")
    eval(strategy)(capital,star_param)
    return

def bulk_test():
    #OHL Bulk Test
    #Variance Range between 0.001 to 0.003
    var_range = np.arange(0.002,0.004,0.001)
    sl_range  = np.arange(0.002,0.004,0.001)
    t1_range  = np.arange(0.004,0.010,0.001)
    t2_range  = np.arange(0.006,0.012,0.001)
    md_range  = range(15,20)
    ctr       = 0
    bulk_id   = c.gen_id("strategy","bulk_id")
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
                        star_param['SC']      = "SBIN"
                        ctr = ctr + 1 
                        start = datetime.now()
                        tester("ohl",100000,star_param,bulk_id)
                        end = datetime.now()
                        runtime  = end-start
                        msg = ("Strategy -> "+bulk_id+" Combination No -> "+str(ctr)+" Start -> "+str(start)+" End -> "+str(end)+" Runtime -> "+str(runtime))
                        time.sleep(5)
                        c.pr("I",msg,1)
                        
    return

def check_best(sim_map,val,sim,ustar):
    sim_keys     = list(sim_map.keys())
    lval         = val
    lkey         = ""
    for sk in sim_keys:
        if sim_map[sk] < val:
            sim_map[ustar] = round(val,2)
        
        if lval > sim_map[sk]:
            lval = sim_map[sk]
            lkey = sk
    if len(sim_map) > 10:        
        del sim_map[lkey]
    return sim_map

def bulk_stats(bulk_id):
    uniq_strategy = s.sql_hash("strategy","strategy_id","params","WHERE bulk_id='"+bulk_id+"'")
    c.pr("I",str(len(uniq_strategy))+" Unique Parameters Identified",1)
    uniq_cnt       = {}
    sim_map        = {}
    best           = {}
    best['WIN']    = {}
    best['PROFIT'] = {}

    best['WIN']['BUY']  = {}
    best['WIN']['SELL'] = {}

    best['PROFIT']['BUY']  = {}
    best['PROFIT']['SELL'] = {}

    for ustar in uniq_strategy:
        uniq_sims       = s.sql_hash("sim_tracker","sim_id","type:scrip:transaction:capital:start_time:end_time","WHERE strategy_id='"+ustar+"'")
        #c.pr("I",str(len(uniq_sims))+" Unique Simulations Were Performed For Strategy "+ustar,1)
        sim_ids             = list(uniq_sims.keys())
        sim_ids_str         = str(sim_ids).replace("[","(").replace("]",")")
        uniq_cnt[ustar]     = {}

        sim_map[ustar]      = {}
        sim_map[ustar]['R'] = {}
        typ = ['BUY','SELL']
        sts = ['W','L','P','WP','T']
        trn = ['RAN','ACT']

        for tp in typ:
            sim_map[ustar][tp]  = {}
            uniq_cnt[ustar][tp] = {}
            for st in sts:
                sim_map[ustar][tp][st] = {}
                for tr in trn:
                    uniq_cnt[ustar][tp][tr]    = 0
                    sim_map[ustar][tp][st][tr] = 0

        sim_res             = s.sql_hash("sim_results","sim_id","SUM(result)","WHERE sim_id IN "+sim_ids_str+" GROUP BY sim_id")

        for sim in sim_res:
            trn = uniq_sims[sim]['transaction']
            typ = uniq_sims[sim]['type']
            res = sim_res[sim]['SUM(result)']

            sim_map[ustar]['R'][sim]            = {}
            sim_map[ustar]['R'][sim]['TR']      = trn
            sim_map[ustar]['R'][sim]['TP']      = typ
            sim_map[ustar]['R'][sim]['PL']      = res

            sim_map[ustar][trn]['P'][typ]       += res
            sim_map[ustar][trn]['T'][typ]       += 1
            
            uniq_cnt[ustar][trn][typ]           += 1

            if res > 0:
                sim_map[ustar][trn]['W'][typ]   += 1
            else:   
                sim_map[ustar][trn]['L'][typ]   += 1   

        win = round(((sim_map[ustar]['SELL']['W']['ACT']/sim_map[ustar]['SELL']['T']['ACT'])*100),2)
        sim_map[ustar]['SELL']['WP']['ACT'] = win
        win = round(((sim_map[ustar]['SELL']['W']['RAN']/sim_map[ustar]['SELL']['T']['RAN'])*100),2)
        sim_map[ustar]['SELL']['WP']['RAN']  = win

        win = round(((sim_map[ustar]['BUY']['W']['ACT']/sim_map[ustar]['BUY']['T']['ACT'])*100),2)
        sim_map[ustar]['BUY']['WP']['ACT'] = win
        win = round(((sim_map[ustar]['BUY']['W']['RAN']/sim_map[ustar]['BUY']['T']['RAN'])*100),2)
        sim_map[ustar]['BUY']['WP']['RAN']  = win

        #Identify the best strategy by % and profit
        if len(best['WIN']['SELL']) < 10:
            best['WIN']['SELL'][ustar] = sim_map[ustar]['SELL']['WP']['ACT']
        else:
            best['WIN']['SELL'] = check_best(best['WIN']['SELL'],sim_map[ustar]['SELL']['WP']['ACT'],sim,ustar)
            
        if len(best['WIN']['BUY']) < 10:
           best['WIN']['BUY'][ustar] = sim_map[ustar]['BUY']['WP']['ACT']
        else:
            best['WIN']['BUY'] = check_best(best['WIN']['BUY'],sim_map[ustar]['BUY']['WP']['ACT'],sim,ustar)
        
        
        
        if len(best['PROFIT']['SELL']) < 10:
            best['PROFIT']['SELL'][ustar] = round(sim_map[ustar]['SELL']['P']['ACT'],2)
        else:
            best['PROFIT']['SELL'] = check_best(best['PROFIT']['SELL'],sim_map[ustar]['SELL']['P']['ACT'],sim,ustar)
            
        if len(best['PROFIT']['BUY']) < 10:
            best['PROFIT']['BUY'][ustar] = round(sim_map[ustar]['BUY']['P']['ACT'],2)
        else:
             best['PROFIT']['BUY'] = check_best(best['PROFIT']['BUY'],sim_map[ustar]['BUY']['P']['ACT'],sim,ustar)

    #Printing part below

    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                               Bulk Simulation Summary                                                              |")
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|  Sl  | Strategy | Count | Amount  |                                                 Strategy Params                                                |")
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                Top 10 Strategies By Profit For SELL Transaction                                                    |")
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
    ctr = 1
    for st in sorted(best['PROFIT']['SELL'],key=best['PROFIT']['SELL'].get,reverse=True):
        msg  = ("|"+c.gs(str(ctr),6)+"|"+c.gs(st,10)+"|")
        msg += (c.gs(str(uniq_cnt[st]['SELL']['ACT']),6)+"|") 
        msg += (c.gs(str(best['PROFIT']['SELL'][st]),10)+"|")
        msg += (c.gs(uniq_strategy[st]['params'][0:-19]+"}",112)+"|")
        print(msg)
        ctr += 1
    print("------------------------------------------------------------------------------------------------------------------------------------------------------") 
    print("|                                                Top 10 Strategies By Profit For BUY Transaction                                                     |")
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")   
    ctr = 1
    for st in sorted(best['PROFIT']['BUY'],key=best['PROFIT']['BUY'].get,reverse=True):
        msg  = ("|"+c.gs(str(ctr),6)+"|"+c.gs(st,10)+"|")
        msg += (c.gs(str(uniq_cnt[st]['BUY']['ACT']),6)+"|")  
        msg += (c.gs(str(best['PROFIT']['BUY'][st]),10)+"|")
        msg += (c.gs(uniq_strategy[st]['params'][0:-19]+"}",112)+"|")
        print(msg)
        ctr += 1
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|  Sl  | Strategy | Count |   Win%  |                                                 Strategy Params                                                |")
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                Top 10 Strategies By Win % For SELL Transaction                                                     |")
    print("------------------------------------------------------------------------------------------------------------- ----------------------------------------")
    ctr = 1
    for st in sorted(best['WIN']['SELL'],key=best['WIN']['SELL'].get,reverse=True):
        msg  = ("|"+c.gs(str(ctr),6)+"|"+c.gs(st,10)+"|")
        msg += (c.gs(str(uniq_cnt[st]['SELL']['ACT']),6)+"|")
        msg += (c.gs(str(best['WIN']['SELL'][st]),10)+"|")
        msg += (c.gs(uniq_strategy[st]['params'][0:-19]+"}",112)+"|")
        print(msg)
        ctr += 1
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                Top 10 Strategies By Win % For BUY Transaction                                                      |")
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
    ctr = 1
    for st in sorted(best['WIN']['BUY'],key=best['WIN']['BUY'].get,reverse=True):
        msg  = ("|"+c.gs(str(ctr),6)+"|"+c.gs(st,10)+"|")
        msg += (c.gs(str(uniq_cnt[st]['BUY']['ACT']),6)+"|")   
        msg += (c.gs(str(best['WIN']['BUY'][st]),10)+"|")
        msg += (c.gs(uniq_strategy[st]['params'][0:-19]+"}",112)+"|")
        print(msg)
        ctr += 1
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
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
#bulk_test()
bulk_stats("QI2XBGAN")
#bulk_stats("6UW90DRQ")
#FVDPYVRC
