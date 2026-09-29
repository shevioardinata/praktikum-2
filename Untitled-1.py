
from datetime import datetime

from dateutil.relativedelta import relativedelta



def hitung_umur(tanggal_lahir):

    lahir = datetime.strptime(tanggal_lahir, "%Y-%m-%d")

    sekarang = datetime.now()



    selisih = relativedelta(sekarang, lahir)



    return selisih.years, selisih.months, selisih.days