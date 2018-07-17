import sql as s
import common as c
import collections
import random
import simulation as sim
from datetime import datetime,timedelta
from pytz import timezone,utc


#Stratgy OHL        -> Opening High Low Strategy
#Data Frame         -> 1 Mins
# If open = high or open = low for at 15 mins then make position   

def ohl(capital,star_param):
    max_dp = star_param['MAX']
    start  = star_param['START']
    thr    = star_param['THR']
    var    = star_param['VAR']
    sl     = star_param['SL']
    t1     = star_param['T1']
    t2     = star_param['T2']
    sims   = {}
    c.pr("I","Initialiazing Strategy OHL Max DP -> "+str(max_dp)+" Staring Data Point -> "+str(start),0)
    #Fetch Scrips
    scrips = c.load_scrips()
    #Fetch Data
    for scrip in scrips:
        data     = c.fetch_scrip_data(scrip,start,0)
        spl_data = c.split_data(data,36000)
        for ctr in spl_data:
            rddata   = collections.OrderedDict(sorted(spl_data[ctr].items()))
            iddata   = c.intrafy(rddata)
            sim_key,sim_data = ohl_process(iddata,thr,var,scrip,capital,max_dp,sl,t1,t2)
            if sim_key:
                sims[sim_key+"_"+scrip] = sim_data
                #sim.simulate(sim_data)
                #exit()
    
    #Call Simulations
    if len(sims):
        c.pr("I",str(len(sims))+" Simulations Will Be Performed",1)
        randomize(spl_data,sims,start,"09:31:00","03:10:00","OHL")
        #for key in sims:
            #print(sims[key])
            #sim.simulate(sims[key])
            #exit()
        #sim.init_sim(sims)
    return

def ohl_process(data,thr,var,scrip,capital,max_dp,sl,t1,t2):
    #Here you have to identify O -> H or O -> L for first 15 mins with variance of 0.03% 
    keys     = list(data.keys())
    ctr      = 1
    cthr     = 0
    opn      = data[keys[0]]['open']
    hig      = data[keys[0]]['high']
    low      = data[keys[0]]['low']
    topn     = round(opn + (opn * var),1)
    bopn     = round(opn - (opn * var),1)
    sim_data = {}
    ran_data = {}
    sim_key  = 0
    #Check if open = High
    while ctr != max_dp:
        time = c.get_date(keys[ctr])
        copn = data[keys[ctr]]['open']
        chig = data[keys[ctr]]['high']
        clow = data[keys[ctr]]['low']
        if  bopn > chig:
            #c.pr("I","YES "+str(ctr)+"  Open -> "+str(opn)+"  Current High -> "+str(chig),1)
            cthr = cthr + 1 
        #else:
            #c.pr("I","NO "+str(ctr)+"  Open -> "+str(opn)+"  Current High -> "+str(chig),1)
        ctr = ctr + 1
    
    if cthr >= thr:
        #c.pr("I","Date "+c.get_date(keys[0])+"   Open -> "+str(opn)+"  T Range -> "+str(topn)+"  B Range -> "+str(bopn)+"  High -> "+str(hig)+"  Low -> "+str(low),1)
        #c.pr("I","Threshold ---> "+str(thr)+" Current Result ---> "+str(cthr)+" Analysis --> OPEN = HIGH",1)
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
        sim_data['TP'] = "ACT"

    #Check Open = LOW    
    ctr   = 1
    cthr  = 0
    while ctr != max_dp:
        time = c.get_date(keys[ctr])
        copn = data[keys[ctr]]['open']
        chig = data[keys[ctr]]['high']
        clow = data[keys[ctr]]['low']
        if  topn < clow:
            #c.pr("I","YES "+str(ctr)+"  Open -> "+str(opn)+"  Current High -> "+str(chig),1)
            cthr = cthr + 1 
        #else:
            #c.pr("I","NO "+str(ctr)+"  Open -> "+str(opn)+"  Current High -> "+str(chig),1)
        ctr = ctr + 1    
    #else:
    #    c.pr("I","Threshold ---> "+str(thr)+" Current Result ---> "+str(cthr)+" Analysis --> OPEN != HIGH",1)    
    #print(cthr)
    if cthr >= thr:
        #c.pr("I","Date "+c.get_date(keys[0])+"   Open -> "+str(opn)+"  T Range -> "+str(topn)+"  B Range -> "+str(bopn)+"  High -> "+str(hig)+"  Low -> "+str(low),1)
        #c.pr("I","Threshold ---> "+str(thr)+" Current Result ---> "+str(cthr)+" Analysis --> OPEN = LOW",1)
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
        sim_data['TP'] = "ACT"
    return sim_key,sim_data

def randomize(data,sim_data,start,st,en,star):
    uniq_scrip = {}
    ran_data   = {}
    #print(sim_data)
    for sim in sim_data:
        #Seperate data as per Scrips
        scrip = sim_data[sim]['SC']
        if scrip not in uniq_scrip:
            uniq_scrip[scrip] = {}
        uniq_scrip[scrip][sim_data[sim]['TS']] = sim_data[sim]
    
    for scrip in uniq_scrip:
        data     = c.fetch_scrip_data(scrip,start,0)
        spl_data = c.split_data(data,36000)
        max_dp   = len(uniq_scrip[scrip])
        dp_avl   = len(spl_data)
        dp_keys  = spl_data.keys()
        if max_dp > (dp_avl - 2):
            max_dp = (dp_avl - 2)
        c.pr("I","Generating Random Data For Scrip -> "+scrip+" DP Needed -> "+str(max_dp)+" DP Available -> "+str(dp_avl),1)
        ctr = 0
        for x in range(1,max_dp+1):
            ctr += 1
            y = True
            while y:
                #Generate Random number
                ran_dp   = random.randrange(1,(dp_avl-1))
                spl_tmp  = spl_data[ran_dp]
                rddata   = collections.OrderedDict(sorted(spl_data[ran_dp].items()))
                iddata   = c.intrafy(rddata)
                tkey     = c.get_timestamp(c.get_only_date(list(iddata.keys())[0])+" "+st)
                dp_check = tkey+"_"+scrip
                if dp_check not in sim_data:
                    if dp_check not in ran_data:
                        ran_data[dp_check] = {}
                        ran_data['NM']     = tkey
                        ran_data['NM']     = star
                        ran_data['TP']     = "RAN"
                        ran_data['EN']     = en
                        ran_data['SC']     = en
                        y = False
        print(len(ran_data))
        exit()
    return