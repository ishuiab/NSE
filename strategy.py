import sql as s
import common as c
import collections
import random
import time
import simulation as sim
import threading
import sys
from datetime import datetime,timedelta
from pytz import timezone,utc

#Stratgy OHL        -> Opening High Low Strategy
#Data Frame         -> 1 Mins
# If open = high or open = low for at 15 mins then make position   

def ohl(capital,star_param,cdata,typ):
    max_dp = star_param['MAX']
    start  = star_param['START']
    thr    = star_param['THR']
    var    = star_param['VAR']
    sl     = star_param['SL']
    t1     = star_param['T1']
    t2     = star_param['T2']
    st_id  = star_param['ID']
    scr    = star_param['SC']
    sims   = {}
    scrips = {}
    data   = {}
    c.pr("I","Initialiazing Strategy OHL Max DP -> "+str(max_dp)+" Staring Data Point -> "+str(start),0)
    #Fetch Scrips
    if scr == "ALL":
        scrips = c.load_scrips()
    else:
        scrips[scr] = 1
    #Fetch Data
    for scrip in scrips:
        if scr != "ALL":
            if len(cdata):
                data = cdata
            else:
                data = c.fetch_scrip_data(scrip,start,0)
        else:
            data     = c.fetch_scrip_data(scrip,start,0)
        spl_data = c.split_data(data,36000)
        for ctr in spl_data:
            rddata   = collections.OrderedDict(sorted(spl_data[ctr].items()))
            iddata   = c.intrafy(rddata)
            sim_key,sim_data = ohl_process(iddata,thr,var,scrip,capital,max_dp,sl,t1,t2,st_id)
            if sim_key:
                sims[sim_key+"_"+scrip] = sim_data
    #Call Simulations
    if len(sims):
        rans = randomize(spl_data,sims,start,"09:31:00","15:10:00","OHL",capital,sl,t1,t2,st_id)
        c.pr("I",str(len(sims))+" Actual Simulations Will Be Performed",1)
        c.pr("I",str(len(rans))+" Random Simulations Will Be Performed",1)
        if typ == "B":
            for key in sims:
                sim.simulate(sims[key])
            for key in rans:
                sim.simulate(rans[key])
        else:
            sim.init_sim(sims,rans,st_id)
    return

def ohl_process(data,thr,var,scrip,capital,max_dp,sl,t1,t2,st_id):
    #Here you have to identify O -> H or O -> L for first 15 mins with variance of var
    keys     = list(data.keys())
    opn      = data[keys[0]]['open']
    hig      = data[keys[0]]['high']
    low      = data[keys[0]]['low']
    avg_opn  = ((opn+hig+low)/3)
    avg_opn  = opn    
    topn     = round(avg_opn + (avg_opn * var),1)
    bopn     = round(avg_opn - (avg_opn * var),1)
    sim_data = {}
    ran_data = {}
    sim_key  = 0

    #Check if open = High
    ctr      = 1
    cthr     = 0
    while ctr != max_dp:
        time = c.get_date(keys[ctr])
        copn = data[keys[ctr]]['open']
        chig = data[keys[ctr]]['high']
        clow = data[keys[ctr]]['low']
        cavg = ((copn+chig+clow)/3)
        if  bopn > cavg:
            cthr = cthr + 1 
        ctr = ctr + 1

    if cthr >= thr:
        sim_key        = keys[ctr]
        sim_data['SC'] = scrip
        sim_data['TP'] = "SELL"
        sim_data['SL'] = sl
        sim_data['T1'] = t1
        sim_data['T2'] = t2
        sim_data['CP'] = capital
        sim_data['TS'] = sim_key
        sim_data['EN'] = "15:10:00"
        sim_data['NM'] = "OHL"
        sim_data['ST'] = "ACT"
        sim_data['ID'] = st_id

    #Check Open = LOW    
    ctr   = 1
    cthr  = 0

    while ctr != max_dp:
        time = c.get_date(keys[ctr])
        copn = data[keys[ctr]]['open']
        chig = data[keys[ctr]]['high']
        clow = data[keys[ctr]]['low']
        cavg = ((copn+chig+clow)/3)
        if  topn < cavg:
            cthr = cthr + 1 
        ctr = ctr + 1    

    if cthr >= thr:
        sim_key        = keys[ctr]
        sim_data['SC'] = scrip
        sim_data['TP'] = "BUY"
        sim_data['SL'] = sl
        sim_data['T1'] = t1
        sim_data['T2'] = t2
        sim_data['CP'] = capital
        sim_data['TS'] = sim_key
        sim_data['EN'] = "15:10:00"
        sim_data['NM'] = "OHL"
        sim_data['ST'] = "ACT"
        sim_data['ID'] = st_id
    sim_data['DATA'] = data
    
    return sim_key,sim_data

def orb(capital,star_param,cdata,typ):
    start  = star_param['START']
    sl     = star_param['SL']
    t1     = star_param['T1']
    t2     = star_param['T2']
    st_id  = star_param['ID']
    scr    = star_param['SC']
    sims   = {}
    scrips = {}
    data   = {}
    c.pr("I","Initialiazing Strategy ORB Staring Data Point -> "+str(start),0)
    #Fetch Scrips
    if scr == "ALL":
        scrips = c.load_scrips()
    else:
        scrips[scr] = 1
        #Fetch Data
    for scrip in scrips:
        if scr != "ALL":
            if len(cdata):
                data = cdata
            else:
                data = c.fetch_scrip_data(scrip,start,0)
        else:
            data     = c.fetch_scrip_data(scrip,start,0)
        spl_data = c.split_data(data,36000)
        for ctr in spl_data:
            rddata   = collections.OrderedDict(sorted(spl_data[ctr].items()))
            iddata   = c.intrafy(rddata)
            sim_key,sim_data = orb_process(iddata,capital,star_param,scrip,sl,t1,t2,st_id)
            if sim_key:
                sims[sim_key+"_"+scrip] = sim_data 
    #Call Simulations
    if len(sims):
        rans = randomize(spl_data,sims,start,"09:31:00","15:10:00","ORB",capital,sl,t1,t2,st_id)
        c.pr("I",str(len(sims))+" Actual Simulations Will Be Performed",0)
        c.pr("I",str(len(rans))+" Random Simulations Will Be Performed",0)
        if typ == "B":
            for key in sims:
                sim.simulate(sims[key])
            for key in rans:
                sim.simulate(rans[key])
        else:
            sim.init_sim(sims,rans,st_id)           
    return

def orb_process(data,capital,star_param,scrip,sl,t1,t2,st_id):
    sim_data  = {}
    spl_data  = {}
    sim_key   = 0
    ctr       = 1
    breakout  = 0 #0 -> No Breakout 1 -> Up range 2 -> Down Range 
    candle    = (star_param['CL'] * 5) # Size of a candle
    vol_chg   = star_param['VC']       # Change of Volume
    time_frm  = star_param['TF']       # Number of Candles
    break_per = star_param['BP']       # % of breakout
    max_can   = star_param['MC']       # Max candles to be checked   
    #Data dictionary contains data for a given day
    #Split the candle into chunks
    c.pr("I","Candle -> "+str(candle)+" Volume Change -> "+str(vol_chg) + " Time Frame -> "+str(time_frm) + " Breakout Percentage -> "+str(break_per)+ " Max Candles To Check -> "+str(max_can),1)
    spl_data  = c.chunk_time(data,candle)
    up_range,down_range,avg_vol = get_opr_range(spl_data,time_frm)
    req_vol   = int(avg_vol * vol_chg)
    c.pr("I","Up Range -> "+str(up_range)+" Down Range -> "+str(down_range)+" Average Volume ->"+str(avg_vol)+" Required Volume -> "+str(req_vol),1)
    for sd in spl_data:
        if ctr <= max_can:
            close =  spl_data[sd]['close']
            vol   =  spl_data[sd]['volume']
            vchk  =  False
            #Check If the volume is more than Threshold
            if  vol > req_vol:
                #Volume is more than threshold
                vchk = True
                #Check if the close is greater than up ranger or lower than down range.
                #Upper Breakout
                if close >= up_range:
                    c.pr("I","[Counter -> "+str(ctr)+"] [Date -> "+str(c.get_date(sd))+"] [Up Range -> "+str(up_range)+"] [Down Range -> " +str(down_range)+"] [Close -> "+str(close)+ "] [Present Volume -> "+str(vol) +"] [Action -> Buy]",1)
                    sim_key        = sd
                    sim_data['SC'] = scrip
                    sim_data['TP'] = "BUY"
                    sim_data['SL'] = sl
                    sim_data['T1'] = t1
                    sim_data['T2'] = t2
                    sim_data['CP'] = capital
                    sim_data['TS'] = sim_key
                    sim_data['EN'] = "15:10:00"
                    sim_data['NM'] = "ORB"
                    sim_data['ST'] = "ACT"
                    sim_data['ID'] = st_id
                    break
                #Lower Breakout
                if close <= down_range:
                    c.pr("I","[Counter -> "+str(ctr)+"] [Date -> "+str(c.get_date(sd))+"] [Up Range -> "+str(up_range)+"] [Down Range -> " +str(down_range)+"] [Close -> "+str(close)+ "] [Present Volume -> "+str(vol) +"] [Action -> Sell]",1)
                    sim_key        = sd
                    sim_data['SC'] = scrip
                    sim_data['TP'] = "SELL"
                    sim_data['SL'] = sl
                    sim_data['T1'] = t1
                    sim_data['T2'] = t2
                    sim_data['CP'] = capital
                    sim_data['TS'] = sim_key
                    sim_data['EN'] = "15:10:00"
                    sim_data['NM'] = "ORB"
                    sim_data['ST'] = "ACT"
                    sim_data['ID'] = st_id
                    break   
            ctr = ctr + 1
    sim_data['DATA'] = data
    return sim_key,sim_data

#Internal function used by orb_process to establish a range
#Returns up range,down range and average volume
def get_opr_range(spl_data,time_frm):
    dkeys      = list(spl_data.keys())[0:int(time_frm)]
    up_range   = spl_data[dkeys[0]]['high']
    down_range = spl_data[dkeys[0]]['low']
    avg_vol    = spl_data[dkeys[0]]['volume']
    dkeys.remove(dkeys[0])
    for tkey in dkeys:
        avg_vol += spl_data[tkey]['volume']
        if spl_data[tkey]['high'] > up_range:
            up_range = spl_data[tkey]['high']

        if spl_data[tkey]['low'] < down_range:
            down_range = spl_data[tkey]['low']  

    avg_vol = int(avg_vol/time_frm)
    round(up_range,2)
    round(down_range,2)
    return up_range,down_range,avg_vol

def randomize(data,sim_data,start,st,en,star,capital,sl,t1,t2,st_id):
    uniq_scrip  = {}
    trans_order = {}
    ran_data    = {}
    random.seed(100)
    #print(sim_data)
    for sim in sim_data:
        #Seperate data as per Scrips
        scrip = sim_data[sim]['SC']
        if scrip not in uniq_scrip:
            uniq_scrip[scrip]  = {}
            trans_order[scrip] = []
        uniq_scrip[scrip][sim_data[sim]['TS']] = sim_data[sim]
        trans_order[scrip].append(uniq_scrip[scrip][sim_data[sim]['TS']]['TP'] )
    for scrip in uniq_scrip:
        data     = c.fetch_scrip_data(scrip,start,0)
        spl_data = c.split_data(data,36000)
        max_dp   = len(uniq_scrip[scrip])
        dp_avl   = (len(spl_data)-len(sim_data))
        dp_keys  = spl_data.keys()
        if max_dp > (dp_avl - 2):
            max_dp = (dp_avl - 2)
            c.pr("I","Generating Random Data For Scrip -> "+scrip+" DP Needed -> "+str(max_dp)+" DP Available -> "+str(dp_avl)+" Total Sims -> "+str(len(sim_data)),0)
            ctr = 0
            for ran_dp in dp_keys:
                if ctr == max_dp:
                    break
                spl_tmp  = spl_data[ran_dp]
                rddata   = collections.OrderedDict(sorted(spl_data[ran_dp].items()))
                iddata   = c.intrafy(rddata)
                tkey     = c.get_timestamp(c.get_only_date(list(iddata.keys())[0])+" "+st)
                dp_check = tkey+"_"+scrip
                #if dp_check not in sim_data:
                if dp_check not in ran_data:
                    ran_data[dp_check] = {}
                    ran_data[dp_check]['TS']     = tkey
                    ran_data[dp_check]['NM']     = star
                    ran_data[dp_check]['ST']     = "RAN"
                    ran_data[dp_check]['EN']     = en
                    ran_data[dp_check]['SC']     = scrip
                    ran_data[dp_check]['TP']     = trans_order[scrip][ctr]
                    ran_data[dp_check]['CP']     = capital
                    ran_data[dp_check]['SL']     = sl 
                    ran_data[dp_check]['T1']     = t1
                    ran_data[dp_check]['T2']     = t2
                    ran_data[dp_check]['ID']     = st_id
                    ran_data[dp_check]['DATA']   = data
                    ctr                         += 1        
                    
        else:
            c.pr("I","Generating Random Data For Scrip -> "+scrip+" DP Needed -> "+str(max_dp)+" DP Available -> "+str(dp_avl)+" Total Sims -> "+str(len(sim_data)),0)
            ctr = 0
            for x in range(1,max_dp+1):
                y = True
                while y:
                    #Generate Random number
                    ran_dp   = random.randrange(1,(dp_avl-1))
                    spl_tmp  = spl_data[ran_dp]
                    rddata   = collections.OrderedDict(sorted(spl_data[ran_dp].items()))
                    iddata   = c.intrafy(rddata)
                    tkey     = c.get_timestamp(c.get_only_date(list(iddata.keys())[0])+" "+st)
                    dp_check = tkey+"_"+scrip
                    if dp_check not in ran_data:
                        ran_data[dp_check] = {}
                        ran_data[dp_check]['TS']     = tkey
                        ran_data[dp_check]['NM']     = star
                        ran_data[dp_check]['ST']     = "RAN"
                        ran_data[dp_check]['EN']     = en
                        ran_data[dp_check]['SC']     = scrip
                        ran_data[dp_check]['TP']     = trans_order[scrip][ctr]
                        ran_data[dp_check]['CP']     = capital
                        ran_data[dp_check]['SL']     = sl 
                        ran_data[dp_check]['T1']     = t1
                        ran_data[dp_check]['T2']     = t2
                        ran_data[dp_check]['ID']     = st_id
                        ran_data[dp_check]['DATA']   = data
                        ctr                         += 1
                        y = False
    return ran_data