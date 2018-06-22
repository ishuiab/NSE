import pymysql  as sql
import common as c

def sql_conn():
	db = sql.connect("localhost","root","","stocki")
	return db

def sql_hash(table,key,cols):
    query = ""
    ret   = {}
    vals  = cols.split(":")
    col   = key+","+",".join(vals)
    query = "SELECT "+col+" FROM "+table
    c.pr("S",query,1)

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
        print("-E- Query Failed")   
        print(e)
        db_obj.rollback()
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
    return rows

def execQuery(qry):
    c.pr("S","Executing Query "+qry,1)
    db_obj  = sql_conn()
    cursor  = db_obj.cursor()
    try:
        cursor.execute(qry)
        db_obj.commit()
    except (sql.Error, sql.Warning) as e:
        print("-E- Query Failed")   
        print(e)
        db_obj.rollback() 
    return

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
            size  = tmp[3]
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
    return