import binascii
import json
import nfc
from os import getenv
import urllib.request

from gpiozero import TonalBuzzer
from gpiozero.tones import Tone
from time import sleep

SYSTEM_CODE = 0xfe00

def on_connect(tag):
        system_codes = tag.request_system_code()
        if SYSTEM_CODE in system_codes:
            data = check_system(tag, SYSTEM_CODE)
    print(data)
#    print(type(json.dumps(data).encode('utf-8')))
    # post_data(data)
    # beep(400)
    # 戻り値はclf.connectのTrueの返却タイミングに関係する
    # Trueにすると、カードが離されてからTrueを返却
    # Falseにすると、すぐにTrueを返却
    return True

def post_data(data):
    # monitoring用ページにPOSTする
    # 標準ライブラリのurllib.requestを使う
    headers = {
        'Content-Type': 'application/json',
    }
    # 環境変数REQUEST_URIにPOST先を指定する
    req = urllib.request.Request(getenv('REQUEST_URI'), data=json.dumps(data).encode('utf-8'), headers=headers, method='POST')
    try:
        with urllib.request.urlopen(req) as res:
            if res.code == 200:
                result = json.loads(res.read().decode('utf-8'))['result']
                if result == 'in':
                    beep(600) # カードをタッチしたのが入室時の場合は、高い音を鳴らす
                else:
                    beep(400) # 退室時には低い音を鳴らす
    except urllib.error.HTTPError as err:
        print("err: ", err.read())

def beep(hertz):
    bz = TonalBuzzer(4) # BCMピン番号 $ gpio readallコマンドで確認できる
    bz.play(Tone(frequency=hertz)) # (Hz)
    sleep(0.2) # 0.2(s)
    bz.stop()

def get_student_id(tag):
        STUDENT_SERVICE_CODE = 0x1a8b
        sc = nfc.tag.tt3.ServiceCode(STUDENT_SERVICE_CODE >> 6, STUDENT_SERVICE_CODE & 0x3f)
        bc = nfc.tag.tt3.BlockCode(0, service=0) # student id
        data = tag.read_without_encryption([sc], [bc])
        return data[2:8].decode("utf-8")

def get_student_name(tag):
    STUDENT_SERVICE_CODE = 0x1a8b
    sc = nfc.tag.tt3.ServiceCode(STUDENT_SERVICE_CODE >> 6, STUDENT_SERVICE_CODE & 0x3f)
    bc = nfc.tag.tt3.BlockCode(1, service=0) # name
    data = tag.read_without_encryption([sc], [bc])
    return data[0:16].decode("shift_jis").strip('\u0000')


def check_connect():
        with nfc.ContactlessFrontend('usb') as clf:
            target_res = clf.sense(nfc.clf.RemoteTarget("212F"), iterations=5 , interval=0.5)
            if not target_res is None:
                tag = nfc.tag.activate(clf, target_res)
                on_connect(tag)

def check_system(tag, system_code):
    idm, pmm = tag.polling(SYSTEM_CODE)
    tag.idm, tag.pmm, tag.sys = idm, pmm, SYSTEM_CODE
    return {"studentId": get_student_id(tag),"name": get_student_name(tag),'idm': binascii.hexlify(tag.idm).decode('utf-8')}


def main():
    with nfc.ContactlessFrontend('usb:054c:06c1') as clf:
        # 212Fで使用するFelicaに限定
        while clf.connect(rdwr={'targets': ['212F'], 'on-connect': on_connect}):
            pass

if __name__ == "__main__":
    main()