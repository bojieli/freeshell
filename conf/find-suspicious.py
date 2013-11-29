import os
import re
import pyinotify

def getcmd(id):
    try:
        name=open(os.path.join('/proc', id, 'cmdline'), 'rb').read()
    except:
        name="false"
    return name

def getroot(id):
    try:
        root=os.readlink(os.path.join('/proc',id,'root'))
    except:
        root='false'
    return root

def isSuspicious(processid):
    try:
        if suspicious.search(getcmd(processid)).start()>=0:
            return True
    except:
        return False

def killSuspicious(tokill):
    for processid in tokill:
        os.kill(int(processid),0)

pids= [processid for processid in os.listdir('/proc') if processid.isdigit()]
suspicious = re.compile("wcgrid|cgminer|coin-miner")
tokill = [processid for processid in pids if isSuspicious(processid)]

killSuspicious(tokill)




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
    monitor()
