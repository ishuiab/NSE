import pymysql  as sql
import common as c

def sql_conn():
	db = sql.connect("localhost","root","","stocki")
	return db

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