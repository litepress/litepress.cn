from googletrans import Translator

translator = Translator(service_urls=['translate.google.cn'])

for i in range(5000):
    t = translator.translate('hello', dest='zh-CN')

    print(("%d:%s" %(i,t.text)))
