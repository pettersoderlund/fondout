import sys
import openpyxl.reader.excel

filename = str(sys.argv[1])
#sheetName = "Sheet1"
sheetName = "SP std holdings"

workbook = openpyxl.load_workbook(filename = filename, use_iterators = True)
worksheet = workbook.get_sheet_by_name(sheetName)
noneCounter = 0
i = 0
for row in worksheet.iter_rows():
    if (row[0].value is not None):
        
        value = row[0].value
        print value
        x = 1

        result = {
          'Fundamental Info': lambda x: x * 100,
          'Fund name': lambda x: x * 5,
          'b': lambda x: x + 7,
          'c': lambda x: x - 2,
          None : lambda x: x - 1002
        }[value](x)
        print result

    else:   
        #print noneCounter
        noneCounter = noneCounter + 1   
        if (noneCounter > 10):
            print "break"
            break