import os
import re
import time

suspicious = re.compile("boinc|wcgrid|setiathome|distrrtgen|cgminer|coin-miner|minerd")

def getcmd(id):
    try:
        name=open(os.path.join('/proc', id, 'cmdline'), 'rb').read()
    except:
        name="false"
    return name

def getroot(id):
    try:
        root=os.readlink(os.path.join('/proc',id,'root')).split("/")[-1]
    except:
        root='false'
    return root

def isSuspicious(processid):
    try:
        if suspicious.search(getcmd(processid)).start()>=0:
            return True
    except:
        return False

def killSuspicious(processid):
    cmdname = getcmd(processid)
    root = getroot(processid)
    os.kill(int(processid),9)
    print time.ctime(),",killed",",process = ",processid,",cmd = ",cmdname,",user = ",root

def kill():
    pids = []
    i = 0
    while(i < 60):
        tpids = [processid for processid in os.listdir('/proc') if processid.isdigit()]
        pids = [processid for processid in pids if processid in tpids]
        tokill = [processid for processid in tpids if processid not in pids and isSuspicious(processid)]
        for processid in tokill:
            killSuspicious(processid)
            tpids.remove(processid)
        pids.extend(tpids)
        i+=1
        time.sleep(60)

if __name__ == "__main__":
    while(True):
        kill()
