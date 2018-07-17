import sql as s
import common as c
import threading
import time
import collections
import math
import secrets
import string
from queue import Queue

q = Queue()
print_lock = threading.Lock()

def init_sim(sims):
    c.pr("I","Initializing Simulation",0)
    max_threads = 10
    if len(sims) < max_threads:
        max_threads = len(sims)

    for x in range(max_threads):
        t = threading.Thread(target=threader)
        t.daemon = True
        t.start()
    
    for key in sims:
        q.put(sims[key])
    
    q.join()
    c.pr("I","Simulation Finished",1)
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
    sim_id  = gen_id()
    
    
    c.pr("I","Starting simulation for [SIM ID -> "+sim_id+"] [Scrip -> "+scrip +"] [Transaction -> "+trans+"] [Entry Point ->  "+c.get_date(start)+"] [Capital -> "+str(capt)+"] [T1 -> "+str(tar1)+"%] [T2 -> "+str(tar2)+"%] [SL -> "+str(sl)+"%]",1)
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
    sim_query = "INSERT INTO sim_tracker VALUES ('"+sim_id+"','"+sim_name+"','"+scrip+"','"+trans+"',"+str(capt)+","+str(tar1)+","+str(tar2)+","+str(sl)+","+str(t1_vol)+","+str(t2_vol)+",'"+start+"','"+end+"')"
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
#Function to generate 8 digit unique and randon ID
def gen_id():
    ran_id = ''.join(secrets.choice(string.ascii_uppercase + string.digits) for _ in range(8))
    if s.rcnt("SELECT * FROM sim_tracker WHERE sim_id='"+ran_id+"'"):
       gen_id() 
    return ran_id