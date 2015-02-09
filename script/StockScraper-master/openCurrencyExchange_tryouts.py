from openexchangerates import OpenExchangeRatesClient
import datetime

client = OpenExchangeRatesClient('493d01af9a05443a8028cd20945b82fe')
#client.currencies()
#newyears = datetime.date(2014,12,31)
#old = client.historical(newyears)
"""
>>> old['rates']['SEK']/old['rates']['GBP']
Decimal('12.15932297395406517871331399') 
"""