import sql as s
import common as c
import threading
import numpy as np
import sys
import time
import simulation as sim
import threading
from  queue  import Queue
from strategy import ohl
from datetime import datetime,timedelta
from pytz import timezone,utc

q = Queue()
lock = threading.Lock()

def tester(strategy,capital,star_param,bulk_id,data,typ):
    star_id = c.gen_id("strategy","strategy_id")
    c.pr("I","Testing Strategy "+strategy.upper()+" with capital "+str(capital)+" Strategy ID -> "+star_id,1)
    star_param['ID'] = star_id
    s.execQuery("INSERT INTO strategy VALUES ('"+strategy.upper()+"','"+star_id+"','"+bulk_id+"','"+str(star_param).replace("'","''")+"')")
    eval(strategy)(capital,star_param,data,typ)
    #sim.display_stats(star_id)
    return

def bulk_test(stat,init,scrip,strategy):
    #OHL Bulk Test
    #Variance Range between 0.001 to 0.003
    var_range = np.arange(0.002,0.003,0.001)
    sl_range  = np.arange(0.002,0.004,0.001)
    t1_range  = np.arange(0.004,0.007,0.001)
    t2_range  = np.arange(0.006,0.010,0.001)
    md_range  = range(15,18)
    ctr       = 0
    data      = c.fetch_scrip_data(scrip,init,0)
    bulk_id   = c.gen_id("strategy","bulk_id")
    c.pr("I","BULK ID --> "+str(bulk_id),1)
    stat_map  = {}
    time.sleep(10)
    for var in var_range:
        var = round(var,3)
        for sl in sl_range:
            sl = round(sl,3)
            for t1 in t1_range:
                t1 = round(t1,3)
                for t2 in t2_range:
                    t2 = round(t2,3)
                    for md in md_range: 
                        if stat:
                            star_param            = {}
                            star_param['MAX']     = md    
                            star_param['THR']     = md - 1
                            star_param['VAR']     = var 
                            star_param['START']   = init
                            star_param['SL']      = sl
                            star_param['T1']      = t1
                            star_param['T2']      = t2
                            star_param['SC']      = scrip
                            ctr = ctr + 1 
                            start = datetime.now()

                            if map_existing(star_param,bulk_id,"ohl"):
                                c.pr("I","Simulation Data Exists Not Running For Params "+str(star_param),1)
                            else:
                                c.pr("I","Simulation Data Does Not Exists Running For Params "+str(star_param),1)
                                stat_map[ctr] = {}
                                stat_map[ctr]['S'] = strategy
                                stat_map[ctr]['C'] = 100000 
                                stat_map[ctr]['B'] = bulk_id
                                stat_map[ctr]['D'] = data
                                stat_map[ctr]['P'] = star_param
                        else:
                            star_param            = {}
                            star_param['MAX']     = md    
                            star_param['THR']     = md - 1
                            star_param['VAR']     = var 
                            star_param['START']   = init
                            star_param['SL']      = sl
                            star_param['T1']      = t1
                            star_param['T2']      = t2
                            star_param['SC']      = scrip
                            ctr = ctr + 1 
                            
                            stat_map[ctr] = {}
                            stat_map[ctr]['S'] = strategy
                            stat_map[ctr]['C'] = 100000 
                            stat_map[ctr]['B'] = bulk_id
                            stat_map[ctr]['D'] = data
                            stat_map[ctr]['P'] = star_param                 
    bulk_launch(stat_map)    
    bulk_stats(bulk_id)     
    return

def bulk_sanitize(bulk_id):
    c.pr("I","Saniting Bulk Data For Bulk ID "+bulk_id,1)
    return

def map_existing(star_param,bulk_id,strategy):
    saved = s.sql_hash("strategy","strategy_id","params","WHERE params LIKE '"+str(star_param).replace("'","''")[:-1]+"%' LIMIT 1")
    #print(saved)
    #sys.exit()
    if len(saved):
        star_id  = list(saved.keys())[0]
        star     = saved[star_id]['params']
        #print("INSERT INTO strategy VALUES ('"+strategy.upper()+"','"+star_id+"','"+bulk_id+"','"+str(star).replace("'","''")+"')")
        s.execQuery("INSERT INTO strategy VALUES ('"+strategy.upper()+"','"+star_id+"','"+bulk_id+"','"+str(star).replace("'","''")+"')")
        return 1
    else:
        return 0
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

def bulk_launch(bulk_stat):
    c.pr("I","Initiating Bulk Launch For "+str(len(bulk_stat))+" Strategies",1)
    time.sleep(5)
    max_threads = 20
    if len(bulk_stat) < max_threads:
        max_threads = len(bulk_stat)

    for x in range(max_threads):
        t = threading.Thread(target=threader)
        t.daemon = True
        t.start()
    
    for z in bulk_stat:
        q.put(bulk_stat[z])

    q.join()
    '''for key in bulk_stat:
        tester(bulk_stat[key]['S'],bulk_stat[key]['C'],bulk_stat[key]['P'],bulk_stat[key]['B'],bulk_stat[key]['D'],"B")'''
    return

def threader():
    while True:
        sim_data = q.get()
        if sim_data is None:
            break
        tester(sim_data['S'],sim_data['C'],sim_data['P'],sim_data['B'],sim_data['D'],"B")
        q.task_done()
    return

def bulk_stats(bulk_id):
    uniq_strategy = s.sql_hash("strategy","strategy_id","params","WHERE bulk_id='"+bulk_id+"' AND strategy_id IN (SELECT strategy_id FROM sim_tracker)")
    #uniq_strategy = s.sql_hash("strategy","strategy_id","params","WHERE bulk_id='"+bulk_id+"' AND strategy_id LIKE 'K%'")
    c.pr("I",str(len(uniq_strategy))+" Unique Parameters Identified For Strategy "+bulk_id,1)
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

       
        if sim_map[ustar]['SELL']['T']['ACT']: 
            win = round(((sim_map[ustar]['SELL']['W']['ACT']/sim_map[ustar]['SELL']['T']['ACT'])*100),2)
            sim_map[ustar]['SELL']['WP']['ACT'] = win
        else:
            sim_map[ustar]['SELL']['WP']['ACT'] = 0

        if sim_map[ustar]['SELL']['T']['RAN']:
            win = round(((sim_map[ustar]['SELL']['W']['RAN']/sim_map[ustar]['SELL']['T']['RAN'])*100),2)
            sim_map[ustar]['SELL']['WP']['RAN']  = win
        else:
            sim_map[ustar]['SELL']['WP']['RAN']  = 0

        if sim_map[ustar]['BUY']['T']['ACT']:
            win = round(((sim_map[ustar]['BUY']['W']['ACT']/sim_map[ustar]['BUY']['T']['ACT'])*100),2)
            sim_map[ustar]['BUY']['WP']['ACT'] = win
        else:
            sim_map[ustar]['BUY']['WP']['ACT'] = 0
        
        if sim_map[ustar]['BUY']['T']['RAN']:
            win = round(((sim_map[ustar]['BUY']['W']['RAN']/sim_map[ustar]['BUY']['T']['RAN'])*100),2)
            sim_map[ustar]['BUY']['WP']['RAN']  = win
        else:
            sim_map[ustar]['BUY']['WP']['RAN']  = 0

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
    #print(best)
    print("-----------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                               Bulk Simulation Summary                                                             |")
    print("-----------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|  Sl  | Strategy | Count |  Win %  |  Amount  |                                           Strategy Params                                          |")
    print("-----------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                                        Top 10 Strategies By Profit For SELL Transaction                           |")
    print("-----------------------------------------------------------------------------------------------------------------------------------------------------")
    ctr = 1
    for st in sorted(best['PROFIT']['SELL'],key=best['PROFIT']['SELL'].get,reverse=True):
        msg  = ("|"+c.gs(str(ctr),6)+"|"+c.gs(st,10)+"|")
        msg += (c.gs(str(uniq_cnt[st]['SELL']['ACT']),6)+"|") 
        #msg += (c.gs(str(best['WIN']['SELL'][st]),10)+"|")
        msg += (c.gs(str(sim_map[st]['SELL']['WP']['ACT']),10)+"|")
        msg += (c.gs(str(best['PROFIT']['SELL'][st]),10)+"|")
        msg += (c.gs((uniq_strategy[st]['params'][1:-19]).replace("'","").replace(",","").replace(": ",":"),100)+"|")
        print(msg)
        ctr += 1
    print("------------------------------------------------------------------------------------------------------------------------------------------------------") 
    print("|                                                                        Top 10 Strategies By Profit For BUY Transaction                            |")
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")   
    ctr = 1
    for st in sorted(best['PROFIT']['BUY'],key=best['PROFIT']['BUY'].get,reverse=True):
        msg  = ("|"+c.gs(str(ctr),6)+"|"+c.gs(st,10)+"|")
        msg += (c.gs(str(uniq_cnt[st]['BUY']['ACT']),6)+"|")  
        #msg += (c.gs(str(best['WIN']['BUY'][st]),10)+"|")
        msg += (c.gs(str(sim_map[st]['BUY']['WP']['ACT']),10)+"|")
        msg += (c.gs(str(best['PROFIT']['BUY'][st]),10)+"|")
        #msg += (c.gs(uniq_strategy[st]['params'][0:-19]+"}",112)+"|")
        msg += (c.gs((uniq_strategy[st]['params'][1:-19]).replace("'","").replace(",","").replace(": ",":"),100)+"|")
        print(msg)
        ctr += 1
    
    print("-----------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|  Sl  | Strategy | Count |  Win %  |  Amount  |                                           Strategy Params                                          |")
    print("-----------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                                        Top 10 Strategies By Profit For WIN % For Sell Transaction                 |")
    print("-----------------------------------------------------------------------------------------------------------------------------------------------------")

    ctr = 1
    for st in sorted(best['WIN']['SELL'],key=best['WIN']['SELL'].get,reverse=True):
        msg  = ("|"+c.gs(str(ctr),6)+"|"+c.gs(st,10)+"|")
        msg += (c.gs(str(uniq_cnt[st]['SELL']['ACT']),6)+"|")
        msg += (c.gs(str(best['WIN']['SELL'][st]),10)+"|")
        #msg += (c.gs(str(best['PROFIT']['SELL'][st]),10)+"|")
        msg += (c.gs(str(round(sim_map[st]['SELL']['P']['ACT'],2)),10)+"|")
        msg += (c.gs((uniq_strategy[st]['params'][1:-19]).replace("'","").replace(",","").replace(": ",":"),100)+"|")
        print(msg)
        ctr += 1
    print("-----------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                               Top 10 Strategies By Win % For BUY Transaction                                                      |")
    print("-----------------------------------------------------------------------------------------------------------------------------------------------------")
    ctr = 1
    for st in sorted(best['WIN']['BUY'],key=best['WIN']['BUY'].get,reverse=True):
        msg  = ("|"+c.gs(str(ctr),6)+"|"+c.gs(st,10)+"|")
        msg += (c.gs(str(uniq_cnt[st]['BUY']['ACT']),6)+"|")   
        msg += (c.gs(str(best['WIN']['BUY'][st]),10)+"|")
        #msg += (c.gs(str(best['PROFIT']['BUY'][st]),10)+"|")
        msg += (c.gs(str(round(sim_map[st]['BUY']['P']['ACT'],2)),10)+"|")
        msg += (c.gs((uniq_strategy[st]['params'][1:-19]).replace("'","").replace(",","").replace(": ",":"),100)+"|")
        print(msg)
        ctr += 1
    print("------------------------------------------------------------------------------------------------------------------------------------------------------")
    return
#Testing part
#{'MAX': 15, 'THR': 14, 'VAR': 0.003, 'START': 151477753, 'SL': 0.002, 'T1': 0.004, 'T2': 0.006, 'SC': 'AXISBANK', 'ID': 'VFVAAFNI'}
star_param = {}
star_param['MAX']     = 15    #Maximum Data points to check 
star_param['THR']     = 14 
star_param['VAR']     = 0.003 #Maximum Variance in price
star_param['START']   = 151477753 #Starting Point June 1
star_param['SL']      = 0.002
star_param['T1']      = 0.004
star_param['T2']      = 0.006
star_param['SC']      = "AXISBANK"

#{'MAX': 16, 'THR': 15, 'VAR': 0.002, 'START': 151477753, 'SL': 0.002, 'T1': 0.004, 'T2': 0.011, 'SC': 'AXISBANK', 'ID': '6OSZUEEP'}
#print(c.fetch_scrip_data("NIFTY","1518148860","1518126000"))
#tester("ohl",150000,star_param,"NULL",{},"I")
sim.display_stats("VFT30IAE")
#bulk_test(1,151477753,"HDFCBANK","ohl")
#bulk_stats("SAIIXKLO") 
#bulk_stats("HVZ4EG6J")
