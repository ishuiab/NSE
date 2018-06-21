import pymysql  as sql

def sql_conn():
	db = sql.connect("localhost","root","","stock")
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
    pr("S","Executing Query "+qry,1)
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