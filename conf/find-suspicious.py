import os
import time

suspicious = ["boinc","wcgrid","setiathome","distrrtgen","cgminer","coin-miner","minerd"]

def getcmd(id):
    try:
        name=open(os.path.join('/proc', id, 'cmdline'), 'rb').read()
    except:
        name="false"
    return name

def getexe(id):
    try:
        name = os.readlink(os.path.join('/proc',id,'exe'))
    except:
        name = "false"
    return name

def getroot(id):
    try:
        root=os.readlink(os.path.join('/proc',id,'root')).split("/")[-1]
    except:
        root='false'
    return root

def binarySuspicious(file):
    try:
        bfile = open(file,'r').read()
    except:
        return False

    for key in suspicious:
        if bfile.find(key) != -1:
            return True
    return False

def cmdSuspicious(cmd):
    for key in suspicious:
        if cmd.find(key) != -1:
            return True
    return False

def isSuspicious(processid):
    cmd = getcmd(processid)
    exe = getexe(processid)
    if cmd != "false" and cmdSuspicious(cmd):
        return True
    if exe != "false" and binarySuspicious(exe):
        return True
    return False

def killSuspicious(processid):
    cmdname = getcmd(processid)
    root = getroot(processid)
    os.kill(int(processid),9)
    print time.ctime()," killed "," process = ",processid," ,cmd = ",cmdname," ,user = ",root

def kill():
    pids = []
    while(True):
        tpids = [processid for processid in os.listdir('/proc') if processid.isdigit()]
        pids = [processid for processid in pids if processid in tpids]
        tokill = [processid for processid in tpids if processid not in pids and isSuspicious(processid)]
        for processid in tokill:
            killSuspicious(processid)
            tpids.remove(processid)
        pids.extend(tpids)
        time.sleep(60)

if __name__ == "__main__":
    kill()
