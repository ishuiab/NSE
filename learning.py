import threading
from queue import Queue
import time
from random import randrange

# Create the queue and threader 
q = Queue()
print_lock = threading.Lock()

def init_thread():
    # how many threads are we going to allow for
    for x in range(5):
        t = threading.Thread(target=threader)
        # clasifying as a daemon, so they will die when the main dies
        t.daemon = True
        # begins, must come after daemon definition
        t.start()

    start = time.time()
    # 20 jobs assigned.
    for worker in range(20):
        q.put(worker)

        # wait until the thread terminates.
    q.join()
        # with 10 workers and 20 tasks, with each task being .5 seconds, then the completed job
        # is ~1 second using threading. Normally 20 tasks with .5 seconds each would take 10 seconds.
    print('Entire job took:',time.time() - start)
    return
# The threader thread pulls an worker from the queue and processes it
def threader():
    while True:
        # gets an worker from the queue
        worker = q.get()
        # Run the example job with the avail worker in queue (thread)
        exampleJob(worker)
        # completed with the job
        q.task_done()
    return

def exampleJob(worker):
    sleep_time = randrange(1,10)
    time.sleep(sleep_time) # pretend to do some work.
    with print_lock:
        print("SLEEPING FOR",sleep_time," Seconds ",threading.current_thread().name,worker)
    return

init_thread()