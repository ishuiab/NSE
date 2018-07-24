import sql as s
import pymysql  as sql
import common as c
import threading
import time
import collections
import math
import secrets
import string
import os
from queue import Queue

q = Queue()
print_lock = threading.Lock()

def init_sim(sims,rans,st_id):
    c.pr("I","Initializing Simulation",0)
    max_threads = 5
    if len(sims) < max_threads:
        max_threads = len(sims)

    for x in range(max_threads):
        t = threading.Thread(target=threader)
        t.daemon = True
        t.start()
    
    for key in sims:
        q.put(sims[key])
    
    for key in rans:
        q.put(rans[key])

    q.join()
    c.pr("I","Simulation Finished",1)
    #os.system('cls')
    display_stats(st_id)
    return

def display_stats(st_id):
    #Step 1 Get the list of stocks traded with strategy
    sim_data    = s.sql_hash("sim_tracker","sim_id","scrip:capital:type:transaction:capital","WHERE strategy_id='"+st_id+"' ORDER BY transaction")
    sim_ids     = list(sim_data.keys())
    sim_ids_str = str(sim_ids).replace("[","(").replace("]",")")
    sim_map     = {}
    query       = "SELECT * FROM sim_results WHERE sim_id IN "+sim_ids_str
    res_map     = {}
    sum_map     = {}
    db_obj      = s.sql_conn()
    cursor      = db_obj.cursor()
    try:
        cursor.execute(query)
        results = cursor.fetchall()
        for row in results: 
            if row[0] not in sum_map:
                sum_map[row[0]] = {}
                sum_map[row[0]]['SC']  = sim_data[row[0]]['scrip']
                sum_map[row[0]]['TR']  = sim_data[row[0]]['transaction']
                sum_map[row[0]]['ST']  = sim_data[row[0]]['type']
                sum_map[row[0]]['EP']  = "9999999999" #MIN OF ALL DP
                sum_map[row[0]]['XP']  = "0" #MAX OF ALL DP
                sum_map[row[0]]['T1H'] = {}
                sum_map[row[0]]['T1H']['EP'] = 0
                sum_map[row[0]]['T1H']['XP'] = 0
                sum_map[row[0]]['T1H']['VL'] = 0
                sum_map[row[0]]['T2H'] = {}
                sum_map[row[0]]['T2H']['EP'] = 0
                sum_map[row[0]]['T2H']['XP'] = 0
                sum_map[row[0]]['T2H']['VL'] = 0
                sum_map[row[0]]['SLH'] = {}
                sum_map[row[0]]['SLH']['EP'] = 0
                sum_map[row[0]]['SLH']['XP'] = 0
                sum_map[row[0]]['SLH']['VL'] = 0
                sum_map[row[0]]['SQF'] = {}
                sum_map[row[0]]['SQF']['EP'] = 0
                sum_map[row[0]]['SQF']['XP'] = 0
                sum_map[row[0]]['SQF']['VL'] = 0
                sum_map[row[0]]['PL']  = 0
                sum_map[row[0]]['VL']  = 0

            if int(sum_map[row[0]]['EP']) > int(row[1]):
                sum_map[row[0]]['EP'] = row[1]
            if int(sum_map[row[0]]['XP']) < int(row[2]):
                sum_map[row[0]]['XP'] = row[2]

            sum_map[row[0]]['PL']  += row[6]
            sum_map[row[0]]['VL']  += row[5]

            sum_map[row[0]][row[7]]['EP'] = row[3]
            sum_map[row[0]][row[7]]['XP'] = row[4]
            sum_map[row[0]][row[7]]['VL'] = row[5]

            if row[0] not in sim_map:
                sim_map[row[0]] = {}

            if row[7] not in sim_map[row[0]]:       
                sim_map[row[0]][row[7]] = {}
            sim_map[row[0]][row[7]]['EN'] = row[1]
            sim_map[row[0]][row[7]]['XT'] = row[2]
            sim_map[row[0]][row[7]]['EP'] = row[3]
            sim_map[row[0]][row[7]]['XP'] = row[4]
            sim_map[row[0]][row[7]]['VL'] = row[5]
            sim_map[row[0]][row[7]]['PL'] = row[6]
    except (sql.Error, sql.Warning) as e:
        print("-E- Query Failed")   
        print(e)
        db_obj.rollback()   
    for sim_id in sim_data:
        scrip   = sim_data[sim_id]['scrip']
        capital = sim_data[sim_id]['capital']
        stype   = sim_data[sim_id]['type']
        trans   = sim_data[sim_id]['transaction']
        if scrip not in res_map:
            res_map[scrip]        = {}
            res_map[scrip]['ACT'] = {}
            res_map[scrip]['RAN'] = {}
        
        if trans not in res_map[scrip][stype]:
            res_map[scrip][stype][trans] = {}
            res_map[scrip][stype][trans]['CP'] = 0
            res_map[scrip][stype][trans]['TD'] = 0
            res_map[scrip][stype][trans]['SR'] = 0
            res_map[scrip][stype][trans]['PL'] = 0
            res_map[scrip][stype][trans]['WN'] = 0
            res_map[scrip][stype][trans]['LS'] = 0
    
        res_map[scrip][stype][trans]['CP']  = capital
        res_map[scrip][stype][trans]['TD'] += 1
        trade_stat = 0
        for ts in sim_map[sim_id]:
            res_map[scrip][stype][trans]['PL'] += sim_map[sim_id][ts]['PL']
            if sim_map[sim_id][ts]['PL'] > 0:
                trade_stat += 1
            else:
                trade_stat -= 1    
        if trade_stat > 0:
            res_map[scrip][stype][trans]['WN'] += 1
        else:
            res_map[scrip][stype][trans]['LS'] += 1
        
        res_map[scrip][stype][trans]['SR'] = round((res_map[scrip][stype][trans]['WN']/res_map[scrip][stype][trans]['TD']) * 100,2)
    print("----------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                             Simulation Summary                                                       |")
    print("----------------------------------------------------------------------------------------------------------------------------------------")
    print("|        Scrip        | Simulation |  Transaction  |   Capital  |   Sims  |  Wins   | Losses |  Success % |     P/L    |  Exit Capital |")
    print("----------------------------------------------------------------------------------------------------------------------------------------")
    for scrip in res_map.keys():
        for sim in res_map[scrip].keys():
            for trans in res_map[scrip][sim].keys():
                #print(trans)
                #c.dump(res_map[scrip][sim])
                msg = "|"+gs(scrip,21)+"|"+gs(sim,12)+"|"+gs(trans,15)+"|"+gs(str(res_map[scrip][sim][trans]['CP']),12)+"|"
                msg = msg+gs(str(res_map[scrip][sim][trans]['TD']),9)+"|"+gs(str(res_map[scrip][sim][trans]['WN']),9)+"|"
                msg = msg+gs(str(res_map[scrip][sim][trans]['LS']),8)+"|"+gs(str(res_map[scrip][sim][trans]['SR'])+"%",12)+"|"
                msg = msg+gs(str(round(res_map[scrip][sim][trans]['PL'],3)),12)+"|"
                msg = msg+gs(str(round(res_map[scrip][sim][trans]['PL'] + res_map[scrip][sim][trans]['CP'],2)),15)+"|"
                print(msg)
    print("----------------------------------------------------------------------------------------------------------------------------------------")
    
    print("\n--------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                                    Detailed Summary Actual                                                           |")
    print("--------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|   Scrip   |    Date    | Entry |  Exit  | Trans | Vlm |         T1         |         T2         |         SL         |         SQ         |    P/L   |")
    print("--------------------------------------------------------------------------------------------------------------------------------------------------------")
    #c.dump(sum_map)
    STC = ['T1H','T2H','SLH','SQF']
    sel_act = ""
    sel_ran = ""
    ranmsg =  ""
    for sim in sum_map:
        msg  = "|"+gs(sum_map[sim]['SC'],11)+"|"
        msg +=  gs(c.get_date(sum_map[sim]['EP'])[0:10],12)+"|"
        msg +=  gs(c.get_date(sum_map[sim]['EP'])[11:-3],7)+"|"
        msg +=  gs(c.get_date(sum_map[sim]['XP'])[11:-3],8)+"|"
        msg +=  gs(sum_map[sim]['TR'],7)+"|"
        msg +=  gs(str(sum_map[sim]['VL']),5)+"|"
        for ST in STC:
            if sum_map[sim][ST]['VL']:
                tst  = str(sum_map[sim][ST]['VL'])+" "+str(sum_map[sim][ST]['EP'])+" "+str(sum_map[sim][ST]['XP'])
                msg += gs(tst,20)+"|"
            else:   
                msg +=  gs("NONE",20)+"|"
        msg +=  gs(str(sum_map[sim]['PL']),10)+"|"
        if sum_map[sim]['ST'] == "ACT":
            if sum_map[sim]['TR'] == "BUY":
                print(msg)
            else:
                sel_act += msg+"\n"
        else:    
            if sum_map[sim]['TR'] == "BUY":
                ranmsg += msg+"\n"
            else:
                sel_ran += msg+"\n"
    print(sel_act[0:-1])
    print("--------------------------------------------------------------------------------------------------------------------------------------------------------")
    
    print("\n--------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|                                                                   Random Walk Summary Actual                                                         |")
    print("--------------------------------------------------------------------------------------------------------------------------------------------------------")
    print("|   Scrip   |    Date    | Entry |  Exit  | Trans | Vlm |         T1         |         T2         |         SL         |         SQ         |    P/L   |")
    print("--------------------------------------------------------------------------------------------------------------------------------------------------------")
    print(ranmsg[0:-1])
    print(sel_ran[0:-1])
    print("--------------------------------------------------------------------------------------------------------------------------------------------------------")
    cursor.close()
    del cursor
    db_obj.close()
    return

def threader():
    while True:
        sim_data = q.get()
        if sim_data is None:
            break
        simulate(sim_data)
        q.task_done()
    return

def simulate(sim_data):
    scrip    = sim_data['SC']
    trans    = sim_data['TP']
    capt     = sim_data['CP']
    sl       = sim_data['SL']
    tar1     = sim_data['T1']
    tar2     = sim_data['T2']
    start    = sim_data['TS']
    end_time = sim_data['EN']
    sim_name = sim_data['NM']
    sim_type = sim_data['ST']
    str_id   = sim_data['ID']
    sl_val  = 0
    t1_val  = 0
    t2_val  = 0
    t1_vol  = 0
    t2_vol  = 0
    vol     = 0
    results = {}
    entry   = 0
    status  = ""
    end     = c.get_timestamp(c.get_only_date(start)+" "+end_time)
    sim_id  = c.gen_id("sim_tracker","sim_id")
    
    c.pr("I","Starting simulation for [SIM ID -> "+sim_id+"] [Scrip -> "+scrip +"] [Type -> "+sim_type+"] [Transaction -> "+trans+"] [Entry Point ->  "+c.get_date(start)+"] [Capital -> "+str(capt)+"] [T1 -> "+str(tar1)+"%] [T2 -> "+str(tar2)+"%] [SL -> "+str(sl)+"%]",1)
    #Step 1 Load the Scrip
    data  = c.fetch_scrip_data(scrip,start,end)
    tkeys = list(data.keys())
    tkeys.sort()
    #Step 2 Take entry at the entry point at average price of first data candle
    entry   = tkeys[0]
    ep_data = data[tkeys[0]]
    #Removing key which corresponds to EP
    tkeys.pop(0)
    avg_ent = round((ep_data['open'] + ep_data['close'] + ep_data['high'] + ep_data['low'])/4,1)
    #Step 3 Calulate the volume which can be undertaken
    vol = math.floor(capt/avg_ent)
    #Step 4 Calculate SL/T1/T2 after entry
    if trans == "SELL":
        sl_val =  round(avg_ent + (round((avg_ent * sl),1)),1)
        t1_val =  round(avg_ent - (round((avg_ent * tar1),1)),1)
        t2_val =  round(avg_ent - (round((avg_ent * tar2),1)),1)
    
    if trans == "BUY":
        sl_val =  round(avg_ent - (round((avg_ent * sl),1)),1)
        t1_val =  round(avg_ent + (round((avg_ent * tar1),1)),1)
        t2_val =  round(avg_ent + (round((avg_ent * tar2),1)),1)

    #Calculate Volume split
    t1_vol = math.ceil(vol * 0.7)
    t2_vol = vol - t1_vol
    
    #Step 4.1 Record the simulation data in DB
    sim_query = "INSERT INTO sim_tracker VALUES ('"+sim_id+"','"+str_id+"','"+scrip+"','"+sim_type+"','"+trans+"',"+str(capt)+","+str(tar1)+","+str(tar2)+","+str(sl)+","+str(t1_vol)+","+str(t2_vol)+",'"+start+"','"+end+"')"
    s.execQuery(sim_query)

    #c.pr("I","First Candle [Open "+str(ep_data['open'])+"] [Low "+str(ep_data['low'])+"] [High "+str(ep_data['high'])+"] [Close "+str(ep_data['close'])+"]",1)
    c.pr("I","[EP AVG(OLHC) "+str(avg_ent)+"] [SL "+str(sl_val)+"] [T1 "+str(t1_val)+"] [T2 "+str(t2_val)+"] [Vol "+str(vol)+"] [T1 Vol "+str(t1_vol)+"] [T2 Vol "+str(t2_vol)+"]" ,1)

    #Step 5 Loop through time keys and check for condition
    for key in tkeys:
        #Check if there is volume to sell
        if vol:
            ep_data = data[key]
            avg_prc = round((ep_data['open'] + ep_data['close'] + ep_data['high'] + ep_data['low'])/4,1)
            if trans == "SELL":
                #Check if this did hit SL
                if sl_val >= avg_prc:
                    if t1_vol:
                        if avg_prc <= t1_val:
                            c.pr("I","Volume Is At "+str(vol)+" On "+c.get_time(key)+" AVG Price "+str(avg_prc)+ " T1 Hit -> Yes" ,1)
                            results[key]       = {}
                            results[key]['EN'] = avg_ent
                            results[key]['EX'] = avg_prc
                            results[key]['VL'] = t1_vol
                            results[key]['ST'] = "T1H"
                            vol                = vol - t1_vol
                            t1_vol             = 0
                            
                    if t1_vol == 0 and t2_vol:
                        if avg_prc <= t2_val:
                            c.pr("I","Volume Is At "+str(vol)+" On "+c.get_time(key)+" AVG Price "+str(avg_prc)+ " T2 Hit -> Yes" ,1)
                            if key in results:
                                results[key]['VL']  += t2_vol
                                results[key]['ST']  = "T2H"
                                vol                 = vol - t2_vol 
                                t2_vol              = 0
                            else:
                                results[key]       = {}
                                results[key]['EN'] = avg_ent
                                results[key]['EX'] = avg_prc
                                results[key]['VL'] = t2_vol
                                results[key]['ST'] = "T2H"
                                vol                = vol - t2_vol 
                                t2_vol             = 0    
                              
                else:  
                    c.pr("I","Volume Is At "+str(vol)+" On "+c.get_time(key)+" AVG Price "+str(avg_prc)+ " SL Hit -> Yes" ,1)
                    results[key]       = {}
                    results[key]['EN'] = avg_ent
                    results[key]['EX'] = avg_prc
                    results[key]['VL'] = vol
                    results[key]['ST'] = "SLH"
                    vol                = 0
            #exit()
            if trans == "BUY":
                if sl_val <= avg_prc:
                    if t1_vol:
                        if avg_prc >= t1_val:
                            c.pr("I","Volume Is At "+str(vol)+" On "+c.get_time(key)+" AVG Price "+str(avg_prc)+ " T1 Hit -> Yes" ,1)
                            results[key]       = {}
                            results[key]['EN'] = avg_ent
                            results[key]['EX'] = avg_prc
                            results[key]['VL'] = t1_vol
                            results[key]['ST'] = "T1H"
                            vol                = vol - t1_vol
                            t1_vol             = 0
                            
                    if t1_vol == 0 and t2_vol:
                        if avg_prc >= t2_val:
                            c.pr("I","Volume Is At "+str(vol)+" On "+c.get_time(key)+" AVG Price "+str(avg_prc)+ " T2 Hit -> Yes" ,1)
                            if key in results:
                                
                                results[key]['VL']  += t2_vol
                                results[key]['ST']  = "T2H"
                                vol                 = vol - t2_vol 
                                t2_vol              = 0
                            else:
                                results[key]       = {}
                                results[key]['EN'] = avg_ent
                                results[key]['EX'] = avg_prc
                                results[key]['VL'] = t2_vol
                                results[key]['ST'] = "T2H"
                                vol                = vol - t2_vol 
                                t2_vol             = 0    
                              
                else:  
                    c.pr("I","Volume Is At "+str(vol)+" On "+c.get_time(key)+" AVG Price "+str(avg_prc)+ " SL Hit -> Yes" ,1)
                    results[key]       = {}
                    results[key]['EN'] = avg_ent
                    results[key]['EX'] = avg_prc
                    results[key]['VL'] = vol
                    results[key]['ST'] = "SLH"
                    vol                = 0

        else:
            c.pr("I","Ending Simulations As Volume is 0",1)
            break   

    #If the volume is still there at 3:10 square off at 3:10
    if vol:
        c.pr("I","Squaring of Position At 03:10 PM",1)
        ed_data = data[key]
        avg_ext = round((ed_data['open'] + ed_data['close'] + ed_data['high'] + ed_data['low'])/4,1)
        results[key]       = {}
        results[key]['EN'] = avg_ent
        results[key]['EX'] = avg_ext
        results[key]['VL'] = vol
        results[key]['ST'] = "SQF"

    #Step 6. Display Result
    c.pr("I","Simulation Resuts",1)
    for res in results:
        PL = 0
        if trans == "BUY":
            PL = round(((results[res]['EX'] - results[res]['EN']) * results[res]['VL']),1)
        if trans == "SELL":
            PL = round(((results[res]['EN'] - results[res]['EX']) * results[res]['VL']),1)

        c.pr("I","[ET -> "+c.get_time(entry)+"] [EP -> "+str(results[res]['EN'])+"] [ET -> "+c.get_time(res)+"] [XP -> "+str(results[res]['EX'])+"] [Volume -> "+str(results[res]['VL'])+"] [P/L -> "+str(PL)+"] [Status -> "+results[res]['ST']+"]",1)
        res_query = "INSERT INTO sim_results VALUES ('"+sim_id+"',"+str(start)+","+res+","+str(results[res]['EN'])+","+str(results[res]['EX'])+","+str(results[res]['VL'])+","+str(PL)+",'"+results[res]['ST']+"')"
        s.execQuery(res_query)
    c.pr("I","--------------------------------------------------------",1)
    return

def gs(val,num):
	ret = val
	lim = (num-len(val))
	if lim < 0:
		lim = 0
    
	x   = 0
	while lim:
		if x:
			ret = ret+" "
			x   =0
		else:
			ret = " "+ret
			x   = 1 
		lim -= 1 
	return ret
