import pymysql  as sql
import common as c

def sql_conn():
	db = sql.connect("localhost","root","","stocki")
	return db

def sql_array(qry,key):
    ret_arr = []
    db_obj  = sql_conn()
    cursor  = db_obj.cursor(sql.cursors.DictCursor)
    try:
        cursor.execute(qry)
        results = cursor.fetchall()
        for row in results:
            if key in row:
                ret_arr.append(row[key])
    except (sql.Error, sql.Warning) as e:
        print("-E- Query Failed")   
        print(e)
        db_obj.rollback()
    cursor.close()
    return ret_arr

def sql_insert(table,keys,data,limit):
    c.pr("I","Initiating Insert Operation On Table -> "+table+" Query Limit -> "+str(limit)+" Columns -> "+str(len(data)),0)
    ctr = 0
    dap = ""
    for clm in data:
        if ctr == limit:
            dap = dap[1:]
            qry = "INSERT INTO `"+table+"` ("+keys+") VALUES "+dap
            execQuery(qry)
            dap = ""
            ctr = 0
        ctr = ctr + 1
        dap = dap+","+clm
    if ctr > 1:
        dap = dap[1:]
        qry = "INSERT INTO `"+table+"` ("+keys+") VALUES "+dap
        execQuery(qry)
    cursor.close()
    del cursor
    db_obj.close()
    return

def sql_hash(table,key,cols,whr):
    query = ""
    ret   = {}
    vals  = cols.split(":")
    col   = key+","+",".join(vals)
    query = "SELECT "+col+" FROM `"+table+"` "+whr
    #c.pr("S",query,1)
    db_obj  = sql_conn()
    cursor  = db_obj.cursor()
    try:
        cursor.execute(query)
        results = cursor.fetchall()
        for row in results:
            k       = row[0]
            ct      = 1
            ret[k]  = {}  
            for cl in vals:
                ret[k][cl] = row[ct]
                ct = ct + 1 
    except (sql.Error, sql.Warning) as e:
        print("-E- Query Failed -> "+query)   
        print(e)
        db_obj.rollback()
    cursor.close()
    del cursor
    db_obj.close()
    return ret

def rcnt(qry):
    db_obj  = sql_conn()
    cursor  = db_obj.cursor()
    rows    = 0
    try:
        cursor.execute(qry)
        rows = cursor.rowcount
    except (sql.Error, sql.Warning) as e:
        print("-E- Query Failed")   
        print(e)
        db_obj.rollback()
    cursor.close()
    del cursor
    db_obj.close()
    return rows

def execQuery(qry):
    #c.pr("S","Executing Query "+qry,1)
    db_obj  = sql_conn()
    cursor  = db_obj.cursor()
    try:
        cursor.execute(qry)
        db_obj.commit()
    except (sql.Error, sql.Warning) as e:
        print("-E- Query Failed")   
        print(e)
        db_obj.rollback() 
    cursor.close()
    del cursor
    db_obj.close()
    return

def sql_single(qry):
    ret_str = ""
    db_obj  = sql_conn()
    cursor  = db_obj.cursor()
    try:
        cursor.execute(qry)
        results = cursor.fetchall()
        for row in results:
            ret_str = row[0]
    except (sql.Error, sql.Warning) as e:
        print("-E- Query Failed")   
        print(e)
        db_obj.rollback()
    cursor.close()
    del cursor
    db_obj.close()
    return ret_str

def create_table(name,schema):
    c.pr("I","Creating Table "+name,1)
    var_map = {"FL" : "FLOAT" , "IN" : "INT(11)" , "DT": "DATETIME" ,"TS" : "TIMESTAMP" ,"VC" : "VARCHAR","TX":"TEXT"}
    query   = "CREATE TABLE `"+name+"` ("
    scharr  = schema.split(",")
    for sch in scharr:
        tmp = sch.split(":")
        col = tmp[0]
        dt  = tmp[1]
        if dt == "VC":
            size  = tmp[2]
            query = query+"`"+col+"` "+var_map[dt] + "("+size+") NOT NULL,"
        else:
            query = query+"`"+col+"` "+var_map[dt] + " NOT NULL,\n"
    query = query[0:-2]+")"
    
    qry = "SELECT * FROM information_schema.tables WHERE table_schema = 'stocki'  AND table_name = '"+name+"' LIMIT 1;"
    execQuery(query)
    if rcnt(qry):
        c.pr("I",str(name+" Table Created"),1)
        execQuery("UPDATE scrips SET status='YES' WHERE scrip='"+name+"'")
    else:
        c.pr("W",str(name+" Table Not Created"),1)
    cursor.close()
    del cursor
    db_obj.close()
    return