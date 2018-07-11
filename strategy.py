import sql as s
import common as c
import collections
from datetime import datetime,timedelta
from pytz import timezone,utc


#OHL        -> Opening High Low Strategy
#Data Frame -> 5 Mins
# If open = high or open = low for at 15 mins then make position   


def ohl(capital,star_param):
    max_dp = star_param['MAX']
    start  = star_param['START']
    thr    = star_param['THR']
    var    = star_param['VAR']
    c.pr("I","Initialiazing Strategy OHL Max DP -> "+str(max_dp)+" Staring Data Point -> "+str(start),0)
    #Fetch Scrips
    scrips = c.load_scrips()
    #Fetch Data
    for scrip in scrips:
        data     = c.fetch_scrip_data(scrip,start)
        spl_data = c.split_data(data,36000)
        for ctr in spl_data:
            rddata = collections.OrderedDict(sorted(spl_data[ctr].items()))
            iddata = c.intrafy(rddata)
            ohl_process(iddata,thr,var)
        exit()
    return

def ohl_process(data,thr,var):
    #Here you have to identify O -> H or O -> L for first 15 mins with variance of 0.03% 
    keys  = list(data.keys())
    ctr   = 1
    cthr  = 0
    opn   = data[keys[0]]['open']
    hig   = data[keys[0]]['high']
    low   = data[keys[0]]['low']
    topn  = round(opn + (opn * var),1)
    bopn  = round(opn - (opn * var),1)
    #Check if open = High
    while ctr != 15:
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
        c.pr("I","Date "+c.get_date(keys[0])+"   Open -> "+str(opn)+"  T Range -> "+str(topn)+"  B Range -> "+str(bopn)+"  High -> "+str(hig)+"  Low -> "+str(low),1)
        c.pr("I","Threshold ---> "+str(thr)+" Current Result ---> "+str(cthr)+" Analysis --> OPEN = HIGH",1)

    #Check Open = LOW    
    ctr   = 1
    cthr  = 0
    while ctr != 15:
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
   
    if cthr >= thr:
        c.pr("I","Date "+c.get_date(keys[0])+"   Open -> "+str(opn)+"  T Range -> "+str(topn)+"  B Range -> "+str(bopn)+"  High -> "+str(hig)+"  Low -> "+str(low),1)
        c.pr("I","Threshold ---> "+str(thr)+" Current Result ---> "+str(cthr)+" Analysis --> OPEN = LOW",1)

    return