import os
import re
import time
import pyinotify

suspicious = re.compile("wcgrid|setiathome|distrrtgen|cgminer|coin-miner|minerd")

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
    os.kill(int(processid),9)
    print time.ctime(), processid,cmdname,"has been killed"

def killFirst():
    pids= [processid for processid in os.listdir('/proc') if processid.isdigit()]
    tokill = [processid for processid in pids if isSuspicious(processid)]
    for processid in tokill:
        killSuspicious(processid)

class EventHandler(pyinotify.ProcessEvent):
    def process_IN_CREATE(self, event):
        if event.isdigit and isSuspicious(event):
            killSuspicious(event)

def monitor(path="/proc/"):
    wm = pyinotify.WatchManager()
    mask = pyinotify.IN_CREATE
    hander = EventHandler()
    notifier = pyinotify.Notifier(wm,hander)
    wm.add_watch(path,mask,rec=True)
    notifier.loop()

if __name__ == "__main__":
    killFirst()
    monitor()
